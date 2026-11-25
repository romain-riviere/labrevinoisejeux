#!/usr/bin/env bash
# Publie une nouvelle version du thème sur GitHub Releases.
# Le site en production la propose ensuite dans Tableau de bord › Mises à jour.
#
# Étapes : vérifications Git → lint → compatibilité → test de mise à jour (PHP 7.4 et 8.3)
#          → construction → confirmation → tag + push + release GitHub.
#
# Avant : changer la version (scripts/version.sh patch|minor|major) et commiter.
#
# Usage : scripts/release.sh [--sans-test-wp]
#   --sans-test-wp  saute la répétition dans WordPress (déconseillé)

. "$(dirname "$0")/lib/commun.sh"

test_wp=1
[ "${1:-}" = "--sans-test-wp" ] && test_wp=0

cd "$RACINE"
v="$(version_theme)"
tag="v$v"
precedent="$(dernier_tag)"

etape "Vérifications avant publication de $v"
command -v gh >/dev/null 2>&1 || stop "GitHub CLI absent : brew install gh && gh auth login"
gh auth status >/dev/null 2>&1 || stop "GitHub CLI non connecté : gh auth login"
[ "$(git branch --show-current)" = "main" ] || stop "Publier depuis la branche main."
[ -z "$(git status --porcelain)" ] || stop "Modifications non commitées : commiter ou annuler avant de publier."
git fetch -q origin main --tags
[ "$(git rev-parse HEAD)" = "$(git rev-parse origin/main)" ] || [ -z "$(git log HEAD..origin/main --oneline)" ] \
	|| stop "origin/main contient des commits absents en local : git pull d'abord."
git rev-parse -q --verify "refs/tags/$tag" >/dev/null && stop "$tag existe déjà : changer la version (scripts/version.sh patch)."
if [ -n "$precedent" ]; then
	version_sup "$v" "${precedent#v}" || stop "$v n'est pas supérieure à la dernière version publiée (${precedent#v}) : scripts/version.sh patch"
fi
visibilite="$(gh repo view "$DEPOT_GITHUB" --json visibility -q .visibility)"
[ "$visibilite" = "PUBLIC" ] || stop "Le dépôt $DEPOT_GITHUB est $visibilite : WordPress ne pourra pas télécharger la mise à jour sans connexion. Le rendre public (Settings › General › Danger Zone) ou publier à la main (dist/brevinoise.zip)."
ok "Git propre, version $v > ${precedent:-aucune}, dépôt public"

"$RACINE/scripts/lint.sh"
"$RACINE/scripts/compat.sh" ${precedent:+"$precedent"}
if [ "$test_wp" -eq 1 ]; then
	"$RACINE/scripts/test-wp.sh" --php "$PHP_MIN"
	"$RACINE/scripts/test-wp.sh" --php "$PHP_MAX"
else
	alerte "Test dans WordPress sauté (--sans-test-wp)."
fi
"$RACINE/scripts/build.sh"

etape "Résumé"
echo "  Version     : ${precedent:-aucune} → $tag"
echo "  Dépôt       : https://github.com/$DEPOT_GITHUB"
echo "  Fichiers    : dist/$SLUG-$v.zip, dist/$SLUG.zip, dist/$SLUG-update.json"
echo "  Changements :"
git log --format='    - %s' ${precedent:+"$precedent..HEAD"} -- "$SLUG" | head -30
echo
printf '%sPublier %s ? Le site en production proposera la mise à jour. [o/N] %s' "$GRAS" "$tag" "$FIN"
read -r reponse
case "$reponse" in o|O|oui|OUI) ;; *) stop "Publication annulée (rien n'a été envoyé)." ;; esac

etape "Publication"
notes="$(mktemp "${TMPDIR:-/tmp}/brev-notes.XXXXXX")"
trap 'rm -f "$notes"' EXIT
{
	echo "Thème WordPress La Brévinoise $v."
	echo
	echo "## Changements"
	git log --format='- %s' ${precedent:+"$precedent..HEAD"} -- "$SLUG"
	echo
	echo "## Installer"
	echo "- **Automatique** : Tableau de bord › Mises à jour (si l'adresse de mise à jour est réglée dans Apparence › La Brévinoise : outils)."
	echo "- **À la main** : télécharger \`$SLUG.zip\`, puis Apparence › Thèmes › Ajouter › Téléverser › « Remplacer l'actuel par la version téléversée »."
	echo
	echo "Les articles, images, menus et réglages du site sont conservés."
} > "$notes"

git tag -a "$tag" -m "La Brévinoise $v"
git push -q origin main
git push -q origin "$tag"
gh release create "$tag" --repo "$DEPOT_GITHUB" --title "La Brévinoise $v" --notes-file "$notes" --latest \
	"$DIST/$SLUG-$v.zip" "$DIST/$SLUG.zip" "$DIST/$SLUG-update.json"

ok "Version $v publiée : https://github.com/$DEPOT_GITHUB/releases/tag/$tag"
echo "  Adresse de mise à jour (à régler une seule fois sur le site) :"
echo "  https://github.com/$DEPOT_GITHUB/releases/latest/download/$SLUG-update.json"
