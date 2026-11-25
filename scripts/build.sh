#!/usr/bin/env bash
# Fabrique les fichiers à publier dans dist/ :
#   brevinoise.zip              zip à téléverser à la main dans WordPress
#   brevinoise-X.Y.Z.zip        même contenu, nom versionné (archive)
#   brevinoise-update.json      annonce lue par la mise à jour automatique du thème
#
# Le zip est fabriqué depuis Git (fichiers suivis uniquement) : rien de local
# ou de non commité n'y entre. Utiliser --local pour tester des modifications non commitées.
#
# Usage : scripts/build.sh [--local]

. "$(dirname "$0")/lib/commun.sh"

local_mode=0
[ "${1:-}" = "--local" ] && local_mode=1

cd "$RACINE"
v="$(version_theme)"
etape "Construction du thème $v"

if [ "$local_mode" -eq 0 ] && [ -n "$(git status --porcelain -- "$SLUG")" ]; then
	stop "Des modifications de $SLUG/ ne sont pas commitées. Commiter, ou utiliser --local pour un essai."
fi

rm -rf "$DIST"
mkdir -p "$DIST"
zip_v="$DIST/$SLUG-$v.zip"

if [ "$local_mode" -eq 1 ]; then
	alerte "Mode --local : le zip contient l'état actuel du dossier, commité ou non."
	tmp="$(mktemp -d "${TMPDIR:-/tmp}/brev-build.XXXXXX")"
	trap 'rm -rf "$tmp"' EXIT
	rsync -a --exclude '.DS_Store' --exclude '__MACOSX' "$SLUG/" "$tmp/$SLUG/"
	(cd "$tmp" && zip -qrX "$zip_v" "$SLUG")
else
	git archive --format=zip -9 -o "$zip_v" HEAD "$SLUG"
fi
cp "$zip_v" "$DIST/$SLUG.zip"

etape "Contrôle du zip"
liste="$(unzip -Z1 "$zip_v")"
racines="$(printf "%s\n" "$liste" | cut -d/ -f1 | sort -u)"
[ "$racines" = "$SLUG" ] || stop "Le zip doit contenir un seul dossier « $SLUG/ » (trouvé : $racines)"
for f in style.css functions.php index.php jeux/gare-au-chacal/game.html; do
	printf "%s\n" "$liste" | grep -qx "$SLUG/$f" || stop "Fichier manquant dans le zip : $SLUG/$f"
done
vz="$(unzip -p "$zip_v" "$SLUG/style.css" | sed -n "s/^Version:[[:space:]]*//p" | sed -n 1p | tr -d "[:space:]")"
[ "$vz" = "$v" ] || stop "Version du zip ($vz) différente de style.css ($v)"
printf "%s\n" "$liste" | grep -Eq '(\.DS_Store|__MACOSX)' && stop "Fichiers parasites dans le zip"
ok "$(printf "%s\n" "$liste" | grep -vc '/$') fichiers, dossier racine « $SLUG/ », version $v"

etape "Annonce de mise à jour"
base="https://github.com/$DEPOT_GITHUB/releases"
cat > "$DIST/$SLUG-update.json" <<EOF
{
  "version": "$v",
  "package": "$base/download/v$v/$SLUG-$v.zip",
  "url": "$base/tag/v$v"
}
EOF
ok "$DIST/$SLUG-update.json → $base/download/v$v/$SLUG-$v.zip"

echo
ls -lh "$DIST" | sed 1d | awk '{print "  " $5 "\t" $NF}'
