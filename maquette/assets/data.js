/* La Brévinoise — contenus et réglages par défaut du thème.
   Dans WordPress, ces contenus deviendront des articles (types de contenu personnalisés)
   et les réglages des options du Customizer. Ici ils servent de point de départ :
   les modifications faites dans l'Administration sont enregistrées dans le navigateur. */
window.BREV_THEME_VERSION = '1.2.0';
window.BREV_DEFAULTS = {
  settings: {
    siteName: 'La Brévinoise Jeux',
    showSiteName: true,
    logo: 'img/logo.webp',
    logoSize: 96,
    colors: { primary: '#11336a', dark: '#001148', accent: '#f7c31b', soft: '#eef2f8' },
    shopUrl: 'https://www.google.com',
    shopLabel: 'Boutique',
    menu: [
      { id: 'accueil', label: 'Accueil', visible: true },
      { id: 'festivals', label: 'Festivals', visible: true },
      { id: 'jeux', label: 'Les jeux', visible: true },
      { id: 'escape', label: 'Escape Box & accessoires', visible: true },
      { id: 'bd', label: 'Les BD', visible: true },
      { id: 'adherents', label: 'Les adhérents', visible: true },
      { id: 'boutique', label: 'Boutique', visible: true },
      { id: 'contact', label: 'Contact', visible: true }
    ],
    hero: {
      image: 'img/setting.webp',
      title: 'Des jeux drôles, rapides et fédérateurs, créés à Saint-Brevin',
      text1: "La Brévinoise Jeux est la section spécialisée dans l'édition de jeux de société de l'association culturelle La Brévinoise, basée à Saint-Brévin-les-Pins en Loire-Atlantique.",
      text2: "En partenariat avec Tipim Studio, nous développons et éditons des jeux familiaux accessibles à tous, avec une petite touche d'éducatif. Nos jeux sont développés en France, fabriqués en Europe et distribués en France, en Belgique et en Suisse.",
      btn1Label: 'Découvrir nos jeux', btn1Link: '#jeux',
      btn2Label: 'Nous contacter', btn2Link: '#contact'
    },
    sections: {
      festTitle: 'Les festivals', festText: 'Survolez une affiche pour voir la présentation et les jeux que nous y animons.', festBtn: 'Tous les festivals →',
      jeuxTitle: 'Les jeux de société', jeuxText: 'Nos dernières créations, à découvrir en famille ou entre amis.', jeuxBtn: 'Voir tous les jeux →',
      bdTitle: 'Les bandes dessinées', bdText: 'Les albums coédités par La Brévinoise Jeux.', bdBtn: 'Voir toutes les BD →',
      itemBtn: 'Voir dans la boutique',
      playBtn: 'Tester le jeu'
    },
    pages: {
      festivals: "Toutes les affiches des festivals et animations où l'association est présente. Survolez (ou touchez) une affiche pour voir la présentation et les jeux associés.",
      jeux: "Des jeux familiaux accessibles à tous, drôles, rapides et fédérateurs, avec une petite touche d'éducatif.",
      escape: "Nos escape box et tous les accessoires pour prolonger l'aventure autour de nos jeux.",
      bd: "Albums de bandes dessinées coédités par La Brévinoise Jeux, souvent accompagnés d'un cahier de jeux.",
      adherents: "Les membres qui font vivre l'association. Survolez (ou touchez) une photo pour savoir qui fait quoi.",
      contact: "Une question sur nos jeux, une commande pour votre boutique ou une animation pour votre événement ? Écrivez-nous."
    },
    contact: [
      { title: 'La Brévinoise Jeux', text: 'Chef de projets : Damien Marquis\ncontact@labrevinoise.fr' },
      { title: 'Association La Brévinoise', text: 'Président : Yvonnick Cheraud\n17 avenue de la Prinais\n44250 Saint-Brévin-les-Pins\nlabrevinoise@yahoo.fr' },
      { title: 'Tipim Studio', text: '8 rue des Platanes\n44680 Saint-Mars-de-Coutais\nDéveloppement et illustration des jeux' }
    ],
    footer: {
      about: "Section d'édition de jeux de société de l'association culturelle La Brévinoise, en partenariat avec Tipim Studio.",
      address: '17 avenue de la Prinais\n44250 Saint-Brévin-les-Pins\ncontact@labrevinoise.fr',
      extra: 'Boutique en ligne\nMAD Distribution : plus de 700 boutiques en France, Belgique et Suisse',
      legal: '© 2026 La Brévinoise Jeux'
    }
  },
  festivals: [
    { id: 'f1', title: 'Ambazac terre de jeux (87)', img: 'img/ambazac.webp', date: 'Sam. 17 et dim. 18 octobre 2026, 10 h – 18 h', place: 'Rue Anna Beillot, Ambazac', desc: "Présentation et animation de nos jeux au festival des jeux d'Ambazac, avec l'escape box LaboTV.", games: ['j2', 'j3', 'j4', 'e1'], link: 'https://www.labrevinoise.fr/ambazac-terre-de-jeux-87-2/' },
    { id: 'f2', title: 'Fête du Jeu de Ruffiac (56)', img: 'img/ruffiac.webp', date: 'Dim. 20 septembre 2026, 10 h – 18 h', place: 'Salle polyvalente, Ruffiac', desc: '10ᵉ édition de la fête du jeu : escape box, animations et dédicaces.', games: ['j2', 'j3', 'j4', 'e1'], link: 'https://www.labrevinoise.fr/fete-du-jeu-de-ruffiac-56/' },
    { id: 'f3', title: 'Forum des assos de Saint-Mars-de-Coutais (44)', img: 'img/forum.webp', date: 'Sam. 5 septembre 2026, 10 h – 13 h', place: 'Saint-Mars-de-Coutais', desc: 'Présentation et animation de nos jeux au forum des associations.', games: ['j2', 'j3', 'j4', 'e1'], link: '' },
    { id: 'f4', title: "Tournoi d'impro du Pays de Retz (44)", img: 'img/impro.webp', date: 'Sam. 29 août 2026', place: 'Théâtre de Saint-Mars-de-Coutais', desc: "Animation de nos jeux pendant le tournoi d'improvisation du Pays de Retz.", games: ['j2', 'j3'], link: '' },
    { id: 'f5', title: 'Parc de la Guérivière (44)', img: 'img/gueriviere.webp', date: 'Ven. 7 août 2026, 14 h – 17 h', place: 'Villeneuve-en-Retz', desc: 'Animation et dédicaces au parc de la Guérivière.', games: ['j2', 'j3', 'j4'], link: '' }
  ],
  jeux: [
    { id: 'j4', title: 'Gare au Chacal', img: '', desc: 'Monte les plus beaux élevages du village… et garde un œil sur le chacal ! Notre petit dernier, à tester directement en ligne.', tags: 'Nouveauté, Jouable en ligne', link: '', play: 'gac' },
    { id: 'j2', title: 'Surf and Cats', img: 'img/surf.webp', desc: "Qui a dit que les chats n'aimaient pas l'eau ? Un stop ou encore à objectifs avec des chats trop cools qui surfent une déferlante d'enfer.", tags: '2–5 joueurs, 8 ans +, 30 min, Stop ou encore', link: 'https://www.labrevinoise.fr/surf-and-cats/' },
    { id: 'j3', title: 'Une Histoire Extraordinaire 2025', img: 'img/uhe25.webp', desc: "Un jeu coopératif hilarant : racontez ensemble l'histoire la plus longue possible avec vos cartes mots, en vous aidant de mimes.", tags: '1–8 joueurs, 7 ans +, 30 min, Coopératif', link: 'https://www.labrevinoise.fr/une-histoire-extraordinaire-2025/' },
    { id: 'j1', title: 'Une Histoire Extraordinaire 2022', img: 'img/uhe22.webp', desc: "La première édition du jeu de mémoire, d'observation et de coopération qui aide aussi les plus jeunes à la lecture.", tags: 'Famille, Coopératif, 1ʳᵉ édition', link: 'https://www.labrevinoise.fr/jeux-de-societe-une-histoire-extraordinaire/' }
  ],
  escape: [
    { id: 'e1', title: 'Escape Box LaboTV', kind: 'Escape Box', img: 'img/escape.webp', desc: "Une escape box dans l'univers de la BD LaboTV, animée lors de nos festivals : cadenas, énigmes et professeur mégalo.", tags: 'Escape game, En animation', link: '' },
    { id: 'e2', title: 'Accessoire à venir', kind: 'Accessoire', img: '', desc: "Emplacement réservé pour un futur accessoire (tapis de jeu, sacoche, cartes bonus…). À créer depuis l'Administration.", tags: 'Bientôt', link: '' }
  ],
  bd: [
    { id: 'b2', title: 'Pépino, tome 2 : Tranches de vie', img: 'img/pepino.webp', desc: "Pépino, jeune pirate fraîchement diplômé, se retrouve au chômage. Découvrez des tranches de sa vie, racontées par un collectif d'auteurs.", tags: 'Publication collective, Septembre 2026', link: 'https://www.labrevinoise.fr/pepino-tome-2-tranches-de-vie/' },
    { id: 'b1', title: 'LaboTV', img: 'img/labotv.webp', desc: 'Une émission de vulgarisation scientifique avec un professeur super-mégalo et un assistant maladroit. Un album humoristique avec cahier de jeux.', tags: 'Tipim, 72 pages, Décembre 2024', link: 'https://www.labrevinoise.fr/labo-tv/' }
  ],
  /* Jeux jouables dans le navigateur, reliés à une fiche de jeu par le bouton « Tester le jeu » */
  navGames: [
    { id: 'gac', title: 'Gare au Chacal', path: 'jeux/gare-au-chacal/index.html',
      shopUrl: 'https://www.sandramoreiraeditions.com',
      winMsg: 'Tu as bien aimé le jeu ? Tu peux maintenant le commander :', winBtn: 'Commander Gare au Chacal',
      loseMsg: 'Mais tu peux te refaire en achetant le jeu ici :', loseBtn: 'Acheter le jeu',
      table: 'miel',
      bots: [
        { id: 'skinner', name: 'SkinR', tag: '★ Débutant', desc: 'Joue un peu au hasard et oublie souvent ses Jokers.' },
        { id: 'nico', name: 'Nicodrën', tag: '★★ Moyen', desc: 'Pose ses bêtes avec bon sens, sans trop calculer.' },
        { id: 'tan', name: 'Than', tag: '★★★ Costaud', desc: 'Combine ses cartes et sort le Chacal au bon moment.' },
        { id: 'tipim', name: 'Tipim', tag: '★★★★ Redoutable', desc: 'Calcule chaque coup. Gare à tes élevages !' }
      ],
      av: {}, cards: {} }
  ],
  adherents: [
    { id: 'a1', title: 'Yvonnick Cheraud', role: "Président de l'association La Brévinoise", img: '', desc: "Préside l'association culturelle La Brévinoise à Saint-Brévin-les-Pins.", games: [] },
    { id: 'a2', title: 'Damien Marquis (Tipim)', role: 'Chef de projets La Brévinoise Jeux, auteur et illustrateur', img: '', desc: "Dirige Tipim Studio, qui développe et illustre les jeux de l'association. Auteur de jeux et de bandes dessinées.", games: ['j3', 'j1', 'j2', 'b1'] },
    { id: 'a3', title: 'Lÿa', role: 'Illustratrice', img: '', desc: 'Co-illustratrice de Surf and Cats avec Tipim.', games: ['j2'] },
    { id: 'a4', title: 'Prénom Nom', role: 'Trésorier·ère (exemple)', img: '', desc: 'Fiche exemple : remplacez-la depuis Administration › Adhérents.', games: [] },
    { id: 'a5', title: 'Prénom Nom', role: 'Animation des festivals (exemple)', img: '', desc: 'Fiche exemple : remplacez-la depuis Administration › Adhérents.', games: ['e1', 'j2'] },
    { id: 'a6', title: 'Prénom Nom', role: 'Secrétaire (exemple)', img: '', desc: 'Fiche exemple : remplacez-la depuis Administration › Adhérents.', games: [] }
  ]
};
