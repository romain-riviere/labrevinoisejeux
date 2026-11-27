# Thème WordPress « La Brévinoise » — version 1.3.0

Guide pour les personnes qui gèrent le site. Pour modifier le code du thème ou publier une version, voir le [README du dépôt](https://github.com/romain-riviere/labrevinoisejeux#readme).

## Installation (5 minutes)

1. Dans WordPress : **Apparence › Thèmes › Ajouter › Téléverser un thème**.
2. Choisir le fichier `brevinoise.zip` (ne pas le décompresser), puis **Installer** et **Activer**.
3. **Apparence › La Brévinoise : outils › « Importer le contenu de démonstration »**.
   Cela crée les jeux, BD, Escape Box, affiches de festivals, adhérents, la page Contact et le menu, avec leurs images. On peut ensuite tout modifier ou supprimer.
4. **Réglages › Permaliens** : vérifier que « Titre de la publication » est choisi (adresses du type `/jeux/`, `/festivals/`, `/jouer/gare-au-chacal/`).

## Où modifier quoi

Uniquement une fois connecté en administrateur.

| Je veux modifier… | Où |
|---|---|
| Festivals (affiches), Jeux de société, Escape Box & accessoires, Bandes dessinées, Adhérents | Menus du même nom à gauche. Titre, description, image (à droite), liens, jeux associés, « Ordre » (0 = en premier). À ordre égal, les fiches les plus récentes passent en premier. |
| Couleurs, taille du logo, lien de la boutique, textes des boutons, de l'accueil, des pages, du contact et du pied de page | **Apparence › Personnaliser › La Brévinoise** |
| Logo | **Apparence › Personnaliser › Identité du site** |
| Onglets du menu | **Apparence › Menus** |
| Jeu en ligne Gare au Chacal : lien boutique de fin de partie, messages, bots, avatars, images des cartes, table, vitesse | Menu **« Gare au Chacal »**. Les visiteurs ne peuvent rien personnaliser dans le jeu. |
| Bouton « Tester le jeu » sur une fiche | Champ « Jeu en ligne associé » de la fiche |

### Page de présentation

Un clic sur l'image ou le titre d'un jeu, d'une BD, d'une escape box ou d'un accessoire ouvre sa page de présentation. On y trouve l'image, la description, les étiquettes, le bouton boutique, le carrousel et l'onglet Vidéo.

- Le carrousel se remplit avec le champ « Galerie d'images » de la fiche.
- La vidéo vient du champ « Lien vidéo ».

## Formulaire de contact

Les messages sont envoyés à l'adresse indiquée dans **Personnaliser › La Brévinoise › Contact & pied de page**. Si ce champ est vide, ils partent vers l'e-mail de l'administrateur.

Si les e-mails n'arrivent pas, installer une extension d'envoi SMTP (par exemple « WP Mail SMTP »).

## Mises à jour

Les contenus et les réglages sont stockés dans WordPress, pas dans le thème : une mise à jour ne les efface pas.

- **Automatique** : renseigner une seule fois l'adresse de mise à jour dans **Apparence › La Brévinoise : outils**. Les nouvelles versions apparaissent alors dans **Tableau de bord › Mises à jour**.
- **À la main** : **Apparence › Thèmes › Ajouter › Téléverser**, choisir le nouveau `brevinoise.zip`, puis « Remplacer l'actuel par la version téléversée ».

La page **Apparence › La Brévinoise : outils** permet aussi d'exporter et d'importer les réglages. Faire un export avant chaque mise à jour, par précaution.

## Organisation des fichiers

| Fichier | Rôle |
|---|---|
| `style.css` | styles du site (et en-tête du thème) |
| `functions.php` | chargement des fonctions (dossier `inc/`) |
| `inc/` | le code, rangé par rôle : `core/` (socle), `content/` (types de contenu, affichage), `features/` (contact, jeu), `admin/` (Personnaliser, outils et mises à jour) |
| `assets/` | scripts (`js/`), styles de l'administration (`css/`) et images du thème (`img/`) |
| `front-page.php` | page d'accueil |
| `archive.php` | pages Festivals, Jeux, Escape Box, BD, Adhérents |
| `single-fiche.php` | page de présentation d'un jeu, d'une BD ou d'une escape box |
| `template-contact.php` | modèle de la page Contact |
| `jeux/gare-au-chacal/` | le jeu en ligne |
| `demo/img/` | images du contenu de démonstration |
