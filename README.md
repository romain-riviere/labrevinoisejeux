# La Brévinoise — thème WordPress

Thème du site de **La Brévinoise Jeux**, section d'édition de jeux de société de l'association culturelle La Brévinoise (Saint-Brévin-les-Pins).

Il couvre les festivals (affiches), les jeux de société, les escape box et accessoires, les bandes dessinées, les adhérents, la page Contact et le jeu en ligne « Gare au Chacal ». Tout se modifie depuis l'administration WordPress.

- **Pour les personnes qui gèrent le site** (ajouter un jeu, changer un texte…) : voir [`brevinoise/LISEZMOI.txt`](brevinoise/LISEZMOI.txt).
- **Ce document** s'adresse à qui modifie le code du thème et publie de nouvelles versions.

## Sommaire

1. [Organisation du dépôt](#1-organisation-du-dépôt)
2. [Prérequis](#2-prérequis)
3. [Modifier le thème](#3-modifier-le-thème)
4. [Ne jamais casser le site en production](#4-ne-jamais-casser-le-site-en-production)
5. [Tester](#5-tester)
6. [Publier une nouvelle version](#6-publier-une-nouvelle-version)
7. [Réglage du site en production (une seule fois)](#7-réglage-du-site-en-production-une-seule-fois)
8. [Architecture du thème](#8-architecture-du-thème)
9. [Historique](#9-historique)

---

## 1. Organisation du dépôt

```
brevinoise/          le thème WordPress (c'est ce dossier qui est zippé et installé)
maquette/            maquette statique d'origine (HTML/JS), ouvrir maquette/index.html
scripts/             vérifications, tests et publication (voir sections 5 et 6)
  lib/               outils utilisés par les scripts
  docker/            WordPress jetable pour les tests
Makefile             raccourcis : make lint, make test, make release…
dist/                fichiers construits (ignoré par Git)
.wp-test/            fichiers du dernier test WordPress (ignoré par Git)
```

Chaque version publiée porte un tag Git : `v1.0.0`, `v1.1.0`… La version en production au moment de la mise en place de ce dépôt est **1.1.3**.

## 2. Prérequis

| Outil | Pour quoi | Installation (macOS) |
|---|---|---|
| Git | historique | déjà présent |
| **Docker Desktop** | PHP et WordPress pour les tests (rien à installer d'autre) | <https://www.docker.com/products/docker-desktop/> |
| GitHub CLI (`gh`) | publication | `brew install gh` puis `gh auth login` |
| `jq`, `zip`, `rsync` | scripts | `brew install jq rsync` si absents |
| Node.js | vérification JavaScript (sinon via Docker) | facultatif |

Docker Desktop doit être **démarré** avant `make lint`, `make test` ou `make release`.

## 3. Modifier le thème

Le code se modifie directement dans `brevinoise/`. Le code, les commentaires et les textes de l'administration sont **en français**.

Cycle habituel :

```sh
git pull
# … modifier brevinoise/ …
make site          # WordPress local avec le thème modifié : http://localhost:8083 (admin / admin)
make lint          # vérifications rapides
git commit -am "Fiches : …"
```

`make site` installe la version en production, importe la démo, applique vos modifications et laisse le site ouvert pour cliquer partout. Arrêter ensuite avec la commande affichée.

Où intervenir selon le besoin :

| Je veux… | Fichier(s) |
|---|---|
| ajouter un champ à une fiche (jeu, BD, festival…) | `inc/defaults.php` → `brev_types()`, champ `fields` |
| ajouter un texte réglable dans Personnaliser | `inc/defaults.php` → `brev_defaults()` **et** `inc/customizer.php` |
| changer l'affichage d'une carte ou d'une fenêtre d'infos | `inc/template-tags.php` |
| changer la page d'accueil, une page de liste, une fiche | `front-page.php`, `archive.php`, `single-fiche.php` |
| changer les styles | `style.css` (couleurs : variables `--primary`, `--dark`, `--accent`, `--soft`) |
| comportement côté navigateur (menu, filtres, carrousel, onglets) | `assets/theme.js` |
| le jeu Gare au Chacal | `jeux/gare-au-chacal/game.html`, réglages dans `inc/game.php` |

Pensez à mettre à jour `brevinoise/LISEZMOI.txt` si la manière de gérer le site change pour les administrateurs.

## 4. Ne jamais casser le site en production

Mettre à jour le thème **remplace uniquement le dossier `brevinoise/`** sur le serveur. Les articles, images, menus et réglages sont dans la base de données WordPress et restent en place.

Ces données sont rattachées au thème **par des noms**. Si un nom change dans le code, les données correspondantes deviennent invisibles : elles sont toujours en base, mais le thème ne les lit plus. Pire, elles sont effacées au prochain enregistrement de la fiche.

### Noms à ne jamais renommer ni supprimer

| Nom | Ce qui y est rattaché |
|---|---|
| dossier `brevinoise` (nom du thème) | tous les réglages Personnaliser (`theme_mods_brevinoise`) |
| types `brev_festival`, `brev_jeu`, `brev_escape`, `brev_bd`, `brev_adherent` | toutes les fiches |
| adresses `festivals`, `jeux`, `escape-box`, `bandes-dessinees`, `adherents` | liens, menus, référencement |
| champs `_brev_*` (`tags`, `link`, `video`, `gallery`, `play`, `games`, `date`, `place`, `role`, `kind`…) | contenu des fiches |
| réglages `brev_*` de `brev_defaults()` | textes et couleurs saisis dans Personnaliser |
| options `brev_gac`, `brev_update_url`, `brev_demo_done`, `brev_rewrite_version` | jeu en ligne, mise à jour automatique |
| bots `skinner`, `nico`, `tan`, `tipim` et cartes `b0`…`back` du jeu | avatars et images choisis par l'admin |
| emplacement de menu `primary` | menu principal |
| modèle `template-contact.php` | page Contact |
| adresse `/jouer/gare-au-chacal/` | liens « Tester le jeu » |
| repères `<script src="../../assets/data.js"></script>` et `../../index.html#jeux` dans `game.html` | injection des réglages du jeu par WordPress |

### Ce qui est permis

- **Ajouter** un champ, un réglage, un type de contenu, une page : sans risque.
- **Changer un libellé** affiché (le 1er élément de `array( 'Étiquettes', 'text', … )`) : sans risque, seule la clé compte.
- **Ne plus utiliser** un champ : le laisser déclaré, simplement ne plus l'afficher.
- **Renommer malgré tout** : garder l'ancien nom et prévoir une migration des données. C'est rare, à éviter.

Ces règles sont vérifiées automatiquement par `make compat` et `make test` (voir ci-dessous) : la publication est refusée si l'une d'elles n'est pas respectée.

### Le numéro de version

Il est dans l'en-tête de `brevinoise/style.css` (ligne `Version:`) et **doit augmenter à chaque publication**. Il sert à trois choses :

- les navigateurs rechargent CSS et JS (sinon les visiteurs gardent l'ancien style en cache) ;
- WordPress recalcule les adresses (nécessaire si un type de contenu ou une adresse est ajouté) ;
- la mise à jour automatique n'est proposée que si la version publiée est supérieure à celle installée.

```sh
make patch    # 1.1.3 → 1.1.4 : correction, retouche d'affichage
make minor    # 1.1.3 → 1.2.0 : nouvelle fonction, nouveau champ, nouvelle page
make major    # 1.1.3 → 2.0.0 : refonte (toujours compatible avec les contenus existants)
```

`make patch|minor|major` met à jour `style.css` et la 1re ligne de `LISEZMOI.txt`.

## 5. Tester

| Commande | Durée | Vérifie |
|---|---|---|
| `make lint` | quelques secondes | syntaxe PHP 7.4 et 8.3, JavaScript (y compris le jeu), en-tête du thème, version dans `LISEZMOI.txt`, repères du jeu, fichiers parasites |
| `make compat` | quelques secondes | aucun nom de la section 4 retiré depuis la dernière version publiée |
| `make test` | ~1 min | répétition générale de la mise à jour (ci-dessous), sur PHP 7.4 / WordPress 6.1 puis PHP 8.3 / WordPress récent |
| `make verifier` | ~1 min | les trois |

### `make compat`

Lit le code du thème (sans WordPress) et liste les 121 noms qui le relient aux données : types, champs, réglages, options, menu, adresses, modèles. La commande échoue si un nom présent dans la dernière version publiée a disparu.

Pour comparer à une autre version : `scripts/compat.sh v1.1.0`.

### `make test` : la répétition générale

Le script `scripts/test-wp.sh` démarre un WordPress jetable dans Docker, puis :

1. installe **la version en production** (dernier tag Git) ;
2. remplit le site : contenu de démonstration, textes et couleurs personnalisés, réglages du jeu, fiche complétée avec étiquettes, vidéo et galerie ;
3. photographie toutes les données liées au thème ;
4. met à jour le thème **par la mise à jour automatique**, exactement comme en production (un petit serveur joue le rôle de GitHub) ;
5. compare la photographie : la moindre fiche, valeur de champ, réglage, entrée de menu ou adresse modifiée fait échouer le test ;
6. visite toutes les pages publiques (accueil, listes, fiches, jeu, contact) : réponse 200, aucune erreur PHP venant du thème, textes personnalisés toujours affichés.

Le test porte sur le dossier `brevinoise/` tel qu'il est, modifications non commitées comprises.

Options :

```sh
scripts/test-wp.sh --php 7.4              # une seule version de PHP
scripts/test-wp.sh --depuis v1.1.0        # partir d'une autre version
scripts/test-wp.sh --garder               # laisser le site ouvert après le test (admin / admin)
```

En cas d'échec, le détail des différences est dans `.wp-test/differences.txt`.

## 6. Publier une nouvelle version

Les versions sont publiées sur **GitHub Releases**. Le site en production les détecte et les propose dans **Tableau de bord › Mises à jour**, comme pour n'importe quel thème.

> ⚠️ Le dépôt GitHub doit être **public**, sinon WordPress ne peut pas télécharger le zip. Le thème est sous licence GPL : cela ne pose pas de problème. Ne jamais commiter de mot de passe ou de donnée personnelle.

### Étapes

```sh
git pull
make minor                     # ou patch / major
git commit -am "Version 1.2.0"
make release
```

`make release` :

1. vérifie que Git est propre, à jour avec GitHub, sur `main`, et que la version a augmenté ;
2. lance `lint`, `compat` et le test de mise à jour sur les deux versions de PHP ;
3. construit `dist/brevinoise-X.Y.Z.zip`, `dist/brevinoise.zip` et `dist/brevinoise-update.json` (fichiers commités uniquement) ;
4. affiche un résumé et **demande confirmation** ;
5. crée le tag `vX.Y.Z`, pousse sur GitHub et publie la release avec les trois fichiers.

Si une étape échoue, rien n'est envoyé.

### Côté site

Dans les heures qui suivent, ou tout de suite via **Tableau de bord › Mises à jour › Vérifier à nouveau**, la nouvelle version apparaît. Cliquer sur « Mettre à jour ». Si les mises à jour automatiques des thèmes sont activées, WordPress l'installe seul.

Après la mise à jour : ouvrir l'accueil, une fiche de jeu et le jeu en ligne pour un contrôle visuel rapide.

### Publication manuelle (secours)

Si la mise à jour automatique n'est pas disponible :

1. `make build` (ou télécharger `brevinoise.zip` depuis la page de la release GitHub) ;
2. WordPress : **Apparence › Thèmes › Ajouter › Téléverser un thème**, choisir `dist/brevinoise.zip` ;
3. choisir **« Remplacer l'actuel par la version téléversée »**.

Le zip doit s'appeler `brevinoise.zip` et contenir un seul dossier `brevinoise/` : sinon WordPress installe un second thème au lieu de remplacer le premier, et les réglages Personnaliser semblent perdus.

### Revenir à une version précédente

Télécharger `brevinoise.zip` depuis la release précédente sur GitHub et le téléverser comme ci-dessus. Les données ne sont pas affectées, puisque les versions sont compatibles entre elles.

## 7. Réglage du site en production (une seule fois)

1. **Sauvegarde** : **Apparence › La Brévinoise : outils › Exporter les réglages**, et conserver le fichier JSON. Une sauvegarde complète du site par l'hébergeur est aussi recommandée avant la première mise à jour.
2. **Adresse de mise à jour** : dans **Apparence › La Brévinoise : outils**, champ « Adresse du fichier de mise à jour », coller :

   ```
   https://github.com/romain-riviere/labrevinoisejeux/releases/latest/download/brevinoise-update.json
   ```

   Cette adresse pointe toujours vers la dernière release : elle n'est jamais à changer.
3. *(Facultatif)* **Apparence › Thèmes › La Brévinoise › Activer les mises à jour automatiques.**

Tant que le dépôt est privé ou qu'aucune release n'est publiée, cette adresse ne répond pas. WordPress l'ignore simplement, sans erreur.

## 8. Architecture du thème

`functions.php` définit trois constantes et charge les fichiers de `inc/` dans l'ordre :

- `BREV_VERSION` : lue dans l'en-tête de `style.css` ;
- `BREV_DIR` : chemin du thème ;
- `BREV_URI` : adresse du thème.

Toutes les fonctions du thème commencent par `brev_`.

### `inc/defaults.php` : le registre central

Presque tout le thème est piloté par trois listes :

- **`brev_defaults()`** : chaque réglage de Personnaliser avec sa valeur par défaut. Ils se lisent avec `brev_mod( 'cle' )`, qui appelle `get_theme_mod( 'brev_cle' )`.
- **`brev_types()`** : les cinq types de contenu (`festival`, `jeu`, `escape`, `bd`, `adherent`). Chacun donne son type WordPress `brev_*`, son adresse, ses libellés et ses champs `nom => [libellé, type, aide]`. À partir de cette liste sont générés automatiquement :
  - l'enregistrement des types et des champs (`_brev_<nom>`) ;
  - le formulaire « Informations » sous chaque fiche, et son enregistrement ;
  - les colonnes de la liste dans l'administration ;
  - l'import de démonstration.

  Types de champs disponibles : `text`, `url`, `games`, `gallery`, `play`, `kind`. Un nouveau type demande de compléter l'affichage et l'enregistrement dans `inc/post-types.php`.
- **`brev_nav_games()`** : les jeux jouables dans le navigateur, fournis dans `jeux/`.

### Autres fichiers de `inc/`

| Fichier | Rôle |
|---|---|
| `setup.php` | fonctions WordPress du thème, styles et scripts, couleurs Personnaliser injectées en variables CSS, ordre d'affichage (champ « Ordre » puis plus récent), recalcul des adresses à chaque changement de version, menu |
| `post-types.php` | types de contenu, formulaire « Informations », enregistrement des champs, éditeur classique forcé |
| `customizer.php` | panneau « La Brévinoise » de Personnaliser ; chaque clé doit exister dans `brev_defaults()` (vérifié par `make lint`) |
| `template-tags.php` | fonctions d'affichage : cartes, fenêtres d'infos, en-têtes de page, galerie, lecteur vidéo ; renvoie les fiches jeu / escape / BD vers `single-fiche.php` |
| `contact.php` | envoi du formulaire de contact par e-mail (jeton de sécurité, champ piège anti-robots `site_web`) |
| `game.php` | page d'administration « Gare au Chacal » (option `brev_gac`) et affichage public du jeu |
| `tools.php` | page « La Brévinoise : outils » : import de démo, export / import des réglages, adresse et vérification des mises à jour |

### Le jeu en ligne Gare au Chacal

`jeux/gare-au-chacal/game.html` est un fichier HTML autonome, partagé avec la maquette. Quand un visiteur ouvre `/jouer/gare-au-chacal/` :

1. une règle d'adresse (`inc/game.php`) intercepte la page ;
2. le thème lit `game.html` et remplace `<script src="../../assets/data.js"></script>` par les réglages de l'administration (`window.BREV_GAME_CFG`) ;
3. il remplace aussi `../../index.html#jeux` par l'adresse de la page des jeux.

Hors WordPress (maquette), le jeu lit `window.BREV_DEFAULTS` et le stockage du navigateur.

### Mise à jour automatique

`brev_check_update()` (`inc/tools.php`) s'accroche à la vérification des mises à jour de WordPress. Elle lit le JSON dont l'adresse est enregistrée dans l'option `brev_update_url` :

```json
{ "version": "1.2.0", "package": "https://…/brevinoise-1.2.0.zip", "url": "https://…" }
```

Si `version` est supérieure à la version installée, WordPress propose la mise à jour et télécharge `package`. Ce fichier est produit par `make build` et publié par `make release`.

### Gabarits et ressources

| Fichier | Rôle |
|---|---|
| `front-page.php` | accueil (encadré principal, festivals, jeux, escape box, BD) |
| `archive.php` | pages de liste des cinq types |
| `single-fiche.php` | page de présentation d'un jeu, d'une BD ou d'une escape box : image, étiquettes, boutons, carrousel, onglet vidéo |
| `template-contact.php` | modèle de page « Contact » |
| `header.php`, `footer.php`, `index.php` | en-tête, pied de page, gabarit de secours |
| `assets/theme.js` | JavaScript sans dépendance : menu mobile, recherche et filtres, fenêtres d'infos (contenu dans des `<template id="pop-…">`), carrousels `[data-carousel]`, onglets |
| `assets/admin-*.js`, `assets/admin.css` | sélecteur d'images du jeu, galerie des fiches |
| `demo/img/` | images du contenu de démonstration |

### La maquette (`maquette/`)

C'est le prototype d'origine, ouvrable sans serveur (`make maquette`). Son administration enregistre les données dans le navigateur.

- `assets/data.js` correspond à `brev_defaults()` et au contenu de démonstration ;
- `assets/site.js` correspond aux gabarits.

Elle sert de référence pour le rendu attendu, mais n'est pas mise à jour avec le thème.

## 9. Historique

Les premières versions ont été livrées sous forme de zips. Elles sont reprises comme commits, à la date de chaque zip :

| Tag | Changement |
|---|---|
| — | maquette statique |
| `v1.0.0` | premier thème WordPress |
| — | encart « Escape Box & accessoires » sur l'accueil (version restée 1.0.0) |
| — | champ et bouton « Lien vidéo » (version restée 1.0.0) |
| `v1.1.0` | pages de présentation des jeux, BD et escape box, galerie d'images |
| `v1.1.1` | carrousel dans la colonne de droite |
| `v1.1.2` | hauteur fixe du carrousel, images sans déformation |
| `v1.1.3` | étiquettes et boutons sous l'image (**version en production**) |

Voir `git log` pour le détail.
