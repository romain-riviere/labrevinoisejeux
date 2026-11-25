# La Brévinoise Jeux — maquette du thème WordPress (version 1.2.0)

Prototype d'origine du site, antérieur au thème WordPress (`../brevinoise/`). Il sert de référence pour le rendu attendu, mais **n'est pas mis à jour** avec le thème.

## Ouvrir le site

Double-cliquer sur `index.html` (ou lancer `make maquette` à la racine du dépôt) : le site s'ouvre dans le navigateur.

Aucune installation n'est nécessaire. La connexion internet ne sert qu'à charger les polices.

## Administration (temporaire)

Cliquer sur « Administration » à droite du menu. On y trouve, comme dans WordPress :

- **Festivals (affiches), Jeux de société, Escape Box & accessoires, Bandes dessinées, Adhérents** : ajouter, modifier, réordonner, supprimer, avec une image et un lien de redirection pour chaque élément ;
- **Apparence** : logo et sa taille, couleurs, menu (renommer, masquer, réordonner), lien de la boutique, boutons ;
- **Textes** de l'accueil et des pages, Contact et pied de page ;
- **Mises à jour** : exporter et importer les contenus, réinitialiser la démo.

Les modifications sont enregistrées dans le navigateur utilisé (Chrome, Edge, Firefox…). Pour les retrouver sur un autre ordinateur, utiliser **Mises à jour › Exporter**, puis **Importer**.

## Mettre à jour la maquette

Quand une nouvelle version arrive, remplacer les fichiers du dossier (`index.html`, `assets`, `img`).

Les contenus sont conservés : ils sont stockés à part, et les nouveaux réglages s'ajoutent tout seuls. Par précaution, faire un export avant de remplacer les fichiers.

## Organisation des fichiers

Chaque fichier préfigure une partie du thème WordPress.

| Fichier | Rôle | Dans le thème |
|---|---|---|
| `index.html` | la page | les modèles du thème |
| `assets/style.css` | les styles | `style.css` |
| `assets/site.js` | l'affichage du site | modèles PHP et `assets/theme.js` |
| `assets/admin.js` | l'aperçu de l'administration | l'administration WordPress |
| `assets/data.js` | les contenus de départ | `brev_defaults()` et le contenu de démonstration |
| `img/` | les images | `demo/img/` |
| `jeux/` | les jeux jouables dans le navigateur (Gare au Chacal), réglables dans Administration › Jeux en ligne | `jeux/` |
