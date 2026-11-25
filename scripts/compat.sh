#!/usr/bin/env bash
# Vérifie qu'une nouvelle version du thème ne casse pas les contenus du site en production.
#
# Compare le « contrat » (types de contenu, champs, réglages, options, menus, adresses…)
# du thème actuel avec celui d'une version publiée. Tout élément retiré ou renommé
# rendrait des articles, réglages ou images orphelins : le script échoue.
# Les ajouts sont autorisés.
#
# Usage : scripts/compat.sh [référence Git]   (par défaut : dernier tag vX.Y.Z)

. "$(dirname "$0")/lib/commun.sh"

ref="${1:-$(dernier_tag)}"
[ -n "$ref" ] || stop "Aucun tag de version trouvé : préciser une référence, ex. scripts/compat.sh v1.1.3"

docker_pret
etape "Compatibilité avec $ref (contenus et réglages du site en production)"

ancien_dir="$(extraire_ref "$ref")"
trap 'rm -rf "$(dirname "$ancien_dir")"' EXIT

ancien="$(php_run "$PHP_MIN" php scripts/lib/contrat.php "$(chemin_conteneur "$ancien_dir")")" \
	|| stop "Impossible de lire le contrat de $ref"
nouveau="$(php_run "$PHP_MIN" php scripts/lib/contrat.php brevinoise)" \
	|| stop "Le thème actuel est incohérent (voir ci-dessus)"

retires="$(comm -23 <(printf '%s\n' "$ancien") <(printf '%s\n' "$nouveau"))"
ajouts="$(comm -13 <(printf '%s\n' "$ancien") <(printf '%s\n' "$nouveau"))"

if [ -n "$ajouts" ]; then
	echo "Ajouts depuis $ref :"
	printf '%s\n' "$ajouts" | sed 's/^/  + /'
fi

if [ -n "$retires" ]; then
	erreur "Éléments retirés ou renommés depuis $ref :"
	printf '%s\n' "$retires" | sed 's/^/  - /' >&2
	cat >&2 <<'EOF'

Ces noms relient le thème aux données du site (articles, champs, réglages,
menus, adresses). Les retirer ou les renommer ferait disparaître des contenus
en production. Remettre l'ancien nom, ou garder l'ancien en plus du nouveau.
EOF
	exit 1
fi

ok "Aucun élément retiré : $(printf '%s\n' "$nouveau" | wc -l | tr -d ' ') éléments du contrat conservés."
