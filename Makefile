# Raccourcis pour les scripts du thème (voir README.md).
.PHONY: aide lint compat test test-74 test-83 verifier build site release patch minor major maquette

aide:
	@echo "make lint       syntaxe PHP/JS, en-tête, repères du jeu"
	@echo "make compat     aucun type de contenu / champ / réglage retiré depuis la version en production"
	@echo "make test       répétition de la mise à jour dans WordPress (PHP 7.4 et 8.3)"
	@echo "make verifier   lint + compat + test"
	@echo "make site       WordPress local avec le thème actuel, laissé démarré (admin / admin)"
	@echo "make build      fabrique dist/brevinoise.zip et l'annonce de mise à jour"
	@echo "make patch|minor|major   change la version du thème"
	@echo "make release    vérifie tout puis publie sur GitHub Releases"
	@echo "make maquette   ouvre la maquette statique dans le navigateur"

lint:
	@scripts/lint.sh

compat:
	@scripts/compat.sh

test: test-74 test-83

test-74:
	@scripts/test-wp.sh --php 7.4

test-83:
	@scripts/test-wp.sh --php 8.3

verifier: lint compat test

site:
	@scripts/test-wp.sh --php 8.3 --garder

build:
	@scripts/build.sh

patch minor major:
	@scripts/version.sh $@

release:
	@scripts/release.sh

maquette:
	@open maquette/index.html
