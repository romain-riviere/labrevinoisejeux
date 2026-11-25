# Fonctions partagées par les scripts (à charger avec « . scripts/lib/commun.sh »).
# Compatible bash 3.2 (macOS).

set -euo pipefail

RACINE="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
THEME="$RACINE/brevinoise"
DIST="$RACINE/dist"
SLUG="brevinoise"
DEPOT_GITHUB="${DEPOT_GITHUB:-romain-riviere/labrevinoisejeux}"
PHP_MIN="7.4"
PHP_MAX="8.3"

if [ -t 1 ]; then
	ROUGE=$'\033[31m'; VERT=$'\033[32m'; JAUNE=$'\033[33m'; GRAS=$'\033[1m'; FIN=$'\033[0m'
else
	ROUGE=""; VERT=""; JAUNE=""; GRAS=""; FIN=""
fi

etape()  { printf '\n%s▶ %s%s\n' "$GRAS" "$*" "$FIN"; }
ok()     { printf '%s✔ %s%s\n' "$VERT" "$*" "$FIN"; }
alerte() { printf '%s⚠ %s%s\n' "$JAUNE" "$*" "$FIN" >&2; }
erreur() { printf '%s✘ %s%s\n' "$ROUGE" "$*" "$FIN" >&2; }
stop()   { erreur "$*"; exit 1; }

# Vérifie que Docker répond.
docker_pret() {
	command -v docker >/dev/null 2>&1 || stop "Docker n'est pas installé."
	docker info >/dev/null 2>&1 || stop "Docker ne répond pas : démarrer Docker Desktop puis relancer."
}

# php_run <version> <commande…> : exécute PHP dans Docker, dossier du dépôt monté sur /app.
# Les chemins passés doivent être relatifs à la racine du dépôt (ou sous /tmp, monté aussi).
php_run() {
	local v="$1"; shift
	docker run --rm -v "$RACINE":/app -v "${TMPDIR:-/tmp}":/hosttmp -w /app "php:${v}-cli" "$@"
}

# Version déclarée dans style.css d'un dossier de thème (par défaut : brevinoise/).
version_theme() {
	sed -n 's/^Version:[[:space:]]*//p' "${1:-$THEME}/style.css" | head -1 | tr -d '[:space:]'
}

# Dernier tag de version publié (vX.Y.Z), vide s'il n'y en a pas.
dernier_tag() {
	git -C "$RACINE" tag --list 'v[0-9]*' | sort -V | tail -1
}

# version_sup A B : vrai si A > B (tri de versions).
version_sup() {
	[ "$1" != "$2" ] && [ "$(printf '%s\n%s\n' "$1" "$2" | sort -V | tail -1)" = "$1" ]
}

# Extrait le thème d'une référence Git dans un dossier temporaire, affiche son chemin.
extraire_ref() {
	local ref="$1" d
	git -C "$RACINE" rev-parse --verify --quiet "$ref^{commit}" >/dev/null || stop "Référence Git inconnue : $ref"
	d="$(mktemp -d "${TMPDIR:-/tmp}/brev-ref.XXXXXX")"
	git -C "$RACINE" archive "$ref" "$SLUG" | tar -x -C "$d"
	printf '%s\n' "$d/$SLUG"
}

# Chemin vu depuis le conteneur pour un fichier temporaire de l'hôte.
chemin_conteneur() {
	local tmp="${TMPDIR:-/tmp}"
	tmp="${tmp%/}"
	case "$1" in
		"$RACINE"/*) printf '/app/%s\n' "${1#"$RACINE"/}" ;;
		"$tmp"/*)    printf '/hosttmp/%s\n' "${1#"$tmp"/}" ;;
		*)           stop "Chemin hors du dépôt et du dossier temporaire : $1" ;;
	esac
}
