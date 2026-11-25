#!/usr/bin/env bash
# Vérifications rapides du thème, sans WordPress :
#  - syntaxe PHP sur la version minimale (7.4) et récente (8.3) ;
#  - syntaxe JavaScript (Node, ou Docker si Node est absent) ;
#  - en-tête de style.css et version identique dans le titre de brevinoise/README.md ;
#  - repères du jeu Gare au Chacal remplacés par WordPress (inc/game.php) ;
#  - pas de fichiers parasites (.DS_Store, __MACOSX…).
#
# Usage : scripts/lint.sh

. "$(dirname "$0")/lib/commun.sh"

docker_pret
cd "$RACINE"
echecs=0
echec() { erreur "$*"; echecs=$((echecs + 1)); }
# section_ok <message> : affiche ✔ si la section courante n'a ajouté aucun échec.
debut=0
section() { etape "$*"; debut=$echecs; }
section_ok() { [ "$echecs" -eq "$debut" ] && ok "$*" || true; }

section "En-tête du thème"
for champ in "Theme Name" "Version" "Requires PHP" "Text Domain"; do
	grep -q "^$champ:" brevinoise/style.css || echec "style.css : champ « $champ » manquant"
done
v="$(version_theme)"
printf '%s\n' "$v" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+$' || echec "Version « $v » invalide (attendu X.Y.Z)"
sed -n 1p brevinoise/README.md | grep -q "^# .*version $v\$" \
	|| echec "brevinoise/README.md : le titre (1re ligne) doit indiquer « version $v » (lancer scripts/version.sh $v)"
section_ok "Version $v"

for pv in "$PHP_MIN" "$PHP_MAX"; do
	section "Syntaxe PHP $pv"
	if sortie="$(php_run "$pv" sh -c 'find brevinoise scripts -name "*.php" -print0 | xargs -0 -n1 php -l' 2>&1)"; then
		ok "$(printf '%s\n' "$sortie" | grep -c 'No syntax errors') fichiers PHP valides"
	else
		printf '%s\n' "$sortie" | grep -v 'No syntax errors' >&2
		echec "Erreur de syntaxe PHP $pv"
	fi
done

section "Contrat du thème (cohérence Personnaliser / valeurs par défaut, fichiers du jeu)"
if php_run "$PHP_MAX" php -d error_reporting=-1 -d display_errors=stderr scripts/lib/contrat.php brevinoise >/dev/null; then
	ok "Contrat lisible sur PHP $PHP_MAX, sans avertissement bloquant"
else
	echec "Contrat illisible"
fi

section "Syntaxe JavaScript"
js_fichiers="$(find brevinoise/assets -name '*.js')"
if command -v node >/dev/null 2>&1; then
	for f in $js_fichiers; do node --check "$f" || echec "JS invalide : $f"; done
else
	docker run --rm -v "$RACINE":/app -w /app node:20-alpine sh -c 'for f in "$@"; do node --check "$f" || exit 1; done' _ $js_fichiers \
		|| echec "JS invalide"
fi
# Scripts intégrés au jeu (HTML autonome).
jeu="brevinoise/jeux/gare-au-chacal/game.html"
tmpjs="$(mktemp "${TMPDIR:-/tmp}/brev-jeu.XXXXXX")"
trap 'rm -f "$tmpjs"' EXIT
perl -0ne 'while (/<script(?![^>]*\bsrc=)[^>]*>(.*?)<\/script>/sg) { print "$1\n;\n" }' "$jeu" > "$tmpjs"
if command -v node >/dev/null 2>&1; then
	node --check --input-type=module < "$tmpjs" 2>/dev/null || node --check < "$tmpjs" || echec "JS invalide dans $jeu"
fi
section_ok "JavaScript valide"

section "Jeu Gare au Chacal : repères remplacés par WordPress"
for repere in '<script src="../../assets/data.js"></script>' '../../index.html#jeux'; do
	grep -qF "$repere" "$jeu" || echec "$jeu : repère « $repere » introuvable (inc/game.php ne pourra plus injecter les réglages)"
	grep -qF "$repere" brevinoise/inc/game.php || echec "inc/game.php ne remplace plus « $repere »"
done
section_ok "Repères présents"

section "Fichiers parasites"
parasites="$(find brevinoise \( -name '.DS_Store' -o -name '__MACOSX' -o -name 'Thumbs.db' -o -name '*.orig' -o -name '*~' \) -print)"
if [ -n "$parasites" ]; then
	printf '%s\n' "$parasites" >&2
	echec "Fichiers parasites dans brevinoise/"
else
	ok "Aucun"
fi

echo
[ "$echecs" -eq 0 ] || stop "$echecs vérification(s) en échec."
ok "Lint terminé sans erreur."
