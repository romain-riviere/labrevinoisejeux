#!/usr/bin/env bash
# Répétition générale de la mise à jour du site, dans un WordPress jetable (Docker).
#
#  1. installe la version en production (dernier tag, ou --depuis <ref>) ;
#  2. remplit le site : démo, textes et couleurs personnalisés, réglages du jeu, fiche complétée ;
#  3. photographie les données (fiches, champs, réglages, menus, pages, adresses) ;
#  4. met à jour le thème PAR LA MISE À JOUR AUTOMATIQUE, comme en production
#     (un petit serveur joue le rôle de GitHub Releases) ;
#  5. compare la photographie : rien ne doit avoir été perdu ou modifié ;
#  6. visite toutes les pages publiques : réponse 200, aucune erreur PHP du thème,
#     textes personnalisés toujours affichés.
#
# Usage : scripts/test-wp.sh [--php 7.4|8.3] [--depuis <ref>] [--garder]
#   --php     version de PHP (7.4 = WordPress 6.1, minimum du thème ; 8.3 = WordPress récent). Défaut : 8.3
#   --depuis  version de départ (défaut : dernier tag vX.Y.Z = version en production)
#   --garder  laisse le site démarré pour l'explorer (identifiant admin / admin)
#
# Teste le dossier brevinoise/ tel qu'il est (modifications non commitées comprises).

. "$(dirname "$0")/lib/commun.sh"

phpv="8.3"; depuis=""; garder=0
while [ $# -gt 0 ]; do
	case "$1" in
		--php)    phpv="$2"; shift 2 ;;
		--depuis) depuis="$2"; shift 2 ;;
		--garder) garder=1; shift ;;
		*) stop "Option inconnue : $1" ;;
	esac
done
case "$phpv" in 7.4|8.3) ;; *) stop "--php : 7.4 ou 8.3" ;; esac
depuis="${depuis:-$(dernier_tag)}"
[ -n "$depuis" ] || stop "Aucun tag de version : préciser --depuis <ref>"

docker_pret
cd "$RACINE"

port="80${phpv/./}"           # 8074 ou 8083
url="http://localhost:$port"
projet="brevtest${phpv/./}"
TEST="$RACINE/.wp-test"
export PHPV="$phpv" PORT="$port"
dc() { docker compose -p "$projet" -f "$RACINE/scripts/docker/compose.yml" "$@"; }
wp() { dc exec -T cli wp "$@"; }

arreter() {
	if [ "$garder" -eq 1 ]; then
		echo
		ok "Site laissé démarré : $url  (admin / admin)"
		echo "  Arrêter : docker compose -p $projet -f scripts/docker/compose.yml down -v"
	else
		dc down -v --remove-orphans >/dev/null 2>&1 || true
	fi
}

nouvelle="$(version_theme)"
ancienne_dir="$(extraire_ref "$depuis")"
ancienne="$(version_theme "$ancienne_dir")"

etape "Préparation : $depuis ($ancienne) → dossier actuel ($nouvelle), PHP $phpv"
rm -rf "$TEST"
mkdir -p "$TEST/maj"
(cd "$(dirname "$ancienne_dir")" && zip -qrX "$TEST/ancienne.zip" "$SLUG")
rm -rf "$(dirname "$ancienne_dir")"
tmp="$(mktemp -d "${TMPDIR:-/tmp}/brev-test.XXXXXX")"
rsync -a --exclude '.DS_Store' "$THEME/" "$tmp/$SLUG/"
(cd "$tmp" && zip -qrX "$TEST/maj/$SLUG-$nouvelle.zip" "$SLUG")
rm -rf "$tmp"
cat > "$TEST/maj/$SLUG-update.json" <<EOF
{ "version": "$nouvelle", "package": "http://maj/$SLUG-$nouvelle.zip", "url": "http://maj/" }
EOF

dc down -v --remove-orphans >/dev/null 2>&1 || true
trap arreter EXIT
dc up -d --quiet-pull >/dev/null 2>&1 || { dc up -d; stop "Impossible de démarrer les conteneurs"; }

etape "Installation de WordPress"
for _ in $(seq 1 60); do
	dc exec -T cli test -f /var/www/html/wp-config.php 2>/dev/null && wp db query 'SELECT 1' >/dev/null 2>&1 && break
	sleep 2
done
wp db query 'SELECT 1' >/dev/null 2>&1 || stop "La base de données ne répond pas."
wp core install --url="$url" --title="La Brévinoise (test)" --admin_user=admin --admin_password=admin \
	--admin_email=test@example.test --skip-email --locale=fr_FR >/dev/null
wp rewrite structure '/%postname%/' >/dev/null
# Autorise le téléchargement depuis le faux serveur de publication (adresse interne à Docker).
dc exec -T cli sh -c 'mkdir -p wp-content/mu-plugins && cat > wp-content/mu-plugins/brev-test.php' <<'EOF'
<?php // Test uniquement : le faux GitHub Releases est sur une adresse privée.
add_filter( 'http_request_host_is_external', '__return_true' );
EOF
ok "WordPress $(wp core version) / PHP $phpv : $url"

etape "Installation de la version en production ($ancienne)"
wp theme install /test/ancienne.zip --activate >/dev/null
[ "$(wp theme get $SLUG --field=version)" = "$ancienne" ] || stop "Version $ancienne non installée"
wp eval-file /scripts/wp-donnees.php
wp eval-file /scripts/wp-instantane.php > "$TEST/avant.json"
jq -e '.fiches | length > 10' "$TEST/avant.json" >/dev/null || stop "Photographie « avant » incomplète"
ok "$(jq '.fiches | length' "$TEST/avant.json") fiches, $(jq '.reglages | length' "$TEST/avant.json") réglages enregistrés"

etape "Mise à jour automatique vers $nouvelle"
dc exec -T cli sh -c ': > wp-content/debug.log' 2>/dev/null || true
if version_sup "$nouvelle" "$ancienne"; then
	wp theme list --update=available --field=name | grep -qx "$SLUG" \
		|| stop "WordPress ne voit pas la mise à jour $nouvelle (brev_check_update / annonce JSON)"
	wp theme update "$SLUG" >/dev/null
else
	alerte "Même version ($nouvelle) : WordPress ne proposerait pas de mise à jour."
	alerte "Remplacement forcé du thème (équivalent du téléversement manuel). Penser à scripts/version.sh."
	wp theme install "/test/maj/$SLUG-$nouvelle.zip" --force >/dev/null
fi
[ "$(wp theme get $SLUG --field=version)" = "$nouvelle" ] || stop "La version installée n'est pas $nouvelle"
[ "$(wp option get stylesheet)" = "$SLUG" ] || stop "Le thème n'est plus actif après la mise à jour"
ok "Thème $nouvelle installé et actif"

etape "Comparaison des données avant / après"
wp eval-file /scripts/wp-instantane.php > "$TEST/apres.json"
if diff <(jq -S . "$TEST/avant.json") <(jq -S . "$TEST/apres.json") > "$TEST/differences.txt"; then
	ok "Fiches, champs, réglages, menus, pages et adresses identiques"
else
	cat "$TEST/differences.txt" >&2
	stop "Des données ont changé avec la mise à jour (détail : .wp-test/differences.txt)"
fi

etape "Visite des pages publiques"
echecs=0
visiter() { # visiter <adresse> [texte attendu…]
	local u="$1" code corps; shift
	corps="$(curl -s -o - -w "\n%{http_code}" "$u")"
	code="${corps##*$'\n'}"
	corps="${corps%$'\n'*}"
	if [ "$code" != "200" ]; then erreur "$code  $u"; echecs=$((echecs + 1)); return; fi
	if grep -Eq 'Fatal error|Parse error|Warning</b>:|Notice</b>:|erreur critique|critical error' <<<"$corps"; then
		erreur "Erreur PHP affichée : $u"; echecs=$((echecs + 1)); return
	fi
	local t
	for t in "$@"; do
		grep -qF -- "$t" <<<"$corps" || { erreur "« $t » absent de $u"; echecs=$((echecs + 1)); }
	done
}
n=0
for u in $(jq -r '.adresses[]' "$TEST/apres.json"); do visiter "$u"; n=$((n + 1)); done
visiter "$url/" "Titre accueil TEST" "--primary:#123456" "--logo:120px" "Mentions TEST"
visiter "$url/jeux/" "Surf and Cats" "Mentions TEST"
visiter "$(jq -r '.fiches[] | select(.titre == "Surf and Cats") | .adresse' "$TEST/apres.json")" "Étiquette TEST" "TESTvideo01"
visiter "$url/jouer/gare-au-chacal/" "BREV_GAME_CFG" "NicoTEST" "Bravo TEST"
visiter "$url/contact/" "Écrivez-nous TEST"
[ "$echecs" -eq 0 ] && ok "$n pages visitées, textes personnalisés présents"

etape "Journal d'erreurs PHP"
journal="$(dc exec -T cli sh -c 'cat wp-content/debug.log 2>/dev/null' | grep "/themes/$SLUG/" || true)"
if [ -n "$journal" ]; then
	printf '%s\n' "$journal" | sort | uniq -c | sort -rn | head -20 >&2
	echecs=$((echecs + 1))
	erreur "Le thème produit des erreurs ou avertissements PHP"
else
	ok "Aucune erreur PHP provenant du thème"
fi

echo
[ "$echecs" -eq 0 ] || stop "Test de mise à jour en échec ($echecs problème(s)). Relancer avec --garder pour explorer le site."
ok "Mise à jour $ancienne → $nouvelle sans perte (PHP $phpv, WordPress $(wp core version))."
