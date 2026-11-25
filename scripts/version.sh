#!/usr/bin/env bash
# Change la version du thème (style.css et 1re ligne de LISEZMOI.txt).
#
# WordPress, le cache des navigateurs et la mise à jour automatique se basent
# sur ce numéro : il doit augmenter à chaque publication.
#
# Usage : scripts/version.sh X.Y.Z
#         scripts/version.sh patch|minor|major   (calculé depuis la version actuelle)
#   patch : correction ou retouche d'affichage (1.1.3 → 1.1.4)
#   minor : nouvelle fonction, nouveau champ, nouvelle page (1.1.3 → 1.2.0)
#   major : changement profond (2.0.0) — doit rester compatible avec les contenus existants

. "$(dirname "$0")/lib/commun.sh"

[ $# -eq 1 ] || stop "Usage : scripts/version.sh X.Y.Z | patch | minor | major"

actuelle="$(version_theme)"
IFS=. read -r ma mi pa <<EOF
$actuelle
EOF
case "$1" in
	patch) nouvelle="$ma.$mi.$((pa + 1))" ;;
	minor) nouvelle="$ma.$((mi + 1)).0" ;;
	major) nouvelle="$((ma + 1)).0.0" ;;
	*)     nouvelle="$1" ;;
esac

printf '%s\n' "$nouvelle" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+$' || stop "Version invalide : $nouvelle (attendu X.Y.Z)"

tag="$(dernier_tag)"
if [ -n "$tag" ] && ! version_sup "$nouvelle" "${tag#v}" && [ "$nouvelle" != "${tag#v}" ]; then
	stop "$nouvelle n'est pas supérieure à la dernière version publiée (${tag#v})."
fi
if [ -n "$tag" ] && [ "$nouvelle" = "${tag#v}" ] && [ "$nouvelle" != "$actuelle" ]; then
	stop "$nouvelle est déjà publiée ($tag)."
fi

# style.css : ligne « Version: »
sed -i.bak -E "s/^Version:[[:space:]]*.*/Version: $nouvelle/" "$THEME/style.css"
# LISEZMOI.txt : titre et soulignement de même longueur
perl -CSD -i.bak -pe '
	if ($. == 1) { s/version\s+\S+\s*$/version '"$nouvelle"'\n/; $len = length($_) - 1 }
	elsif ($. == 2 && /^=+$/) { $_ = ("=" x $len) . "\n" }
' "$THEME/LISEZMOI.txt"
rm -f "$THEME/style.css.bak" "$THEME/LISEZMOI.txt.bak"

[ "$(version_theme)" = "$nouvelle" ] || stop "La version de style.css n'a pas pu être modifiée."
ok "Version : $actuelle → $nouvelle"
