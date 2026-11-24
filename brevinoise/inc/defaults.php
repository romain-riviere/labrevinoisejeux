<?php
/**
 * Valeurs par défaut des réglages du thème (Personnaliser).
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tous les réglages modifiables et leur valeur de départ.
 *
 * @return array
 */
function brev_defaults() {
	return array(
		'logo_size'        => 96,
		'show_name'        => true,
		'color_primary'    => '#11336a',
		'color_dark'       => '#001148',
		'color_accent'     => '#f7c31b',
		'color_soft'       => '#eef2f8',
		'shop_url'         => 'https://www.labrevinoise.fr/ou-trouver-nos-jeux/',
		'item_btn'         => 'Voir dans la boutique',
		'play_btn'         => 'Tester le jeu',
		'video_btn'        => 'Voir la vidéo',
		'hero_image'       => '',
		'hero_title'       => 'Des jeux drôles, rapides et fédérateurs, créés à Saint-Brevin',
		'hero_text1'       => "La Brévinoise Jeux est la section spécialisée dans l'édition de jeux de société de l'association culturelle La Brévinoise, basée à Saint-Brévin-les-Pins en Loire-Atlantique.",
		'hero_text2'       => "En partenariat avec Tipim Studio, nous développons et éditons des jeux familiaux accessibles à tous, avec une petite touche d'éducatif. Nos jeux sont développés en France, fabriqués en Europe et distribués en France, en Belgique et en Suisse.",
		'hero_btn1_label'  => 'Découvrir nos jeux',
		'hero_btn1_link'   => '',
		'hero_btn2_label'  => 'Nous contacter',
		'hero_btn2_link'   => '',
		'fest_title'       => 'Les festivals',
		'fest_text'        => 'Survolez une affiche pour voir la présentation et les jeux que nous y animons.',
		'fest_btn'         => 'Tous les festivals →',
		'jeux_title'       => 'Les jeux de société',
		'jeux_text'        => 'Nos dernières créations, à découvrir en famille ou entre amis.',
		'jeux_btn'         => 'Voir tous les jeux →',
		'escape_title'     => 'Escape Box & accessoires',
		'escape_text'      => "Prolongez l'aventure avec nos escape box et nos accessoires.",
		'escape_btn'       => 'Voir les escape box →',
		'bd_title'         => 'Les bandes dessinées',
		'bd_text'          => 'Les albums coédités par La Brévinoise Jeux.',
		'bd_btn'           => 'Voir toutes les BD →',
		'title_festival'   => 'Festivals',
		'title_jeu'        => 'Les jeux',
		'title_escape'     => 'Escape Box & accessoires',
		'title_bd'         => 'Les BD',
		'title_adherent'   => 'Les adhérents',
		'intro_festival'   => "Toutes les affiches des festivals et animations où l'association est présente. Survolez (ou touchez) une affiche pour voir la présentation et les jeux associés.",
		'intro_jeu'        => "Des jeux familiaux accessibles à tous, drôles, rapides et fédérateurs, avec une petite touche d'éducatif.",
		'intro_escape'     => "Nos escape box et tous les accessoires pour prolonger l'aventure autour de nos jeux.",
		'intro_bd'         => "Albums de bandes dessinées coédités par La Brévinoise Jeux, souvent accompagnés d'un cahier de jeux.",
		'intro_adherent'   => "Les membres qui font vivre l'association. Survolez (ou touchez) une photo pour savoir qui fait quoi.",
		'contact_intro'    => 'Une question sur nos jeux, une commande pour votre boutique ou une animation pour votre événement ? Écrivez-nous.',
		'contact_email'    => '',
		'contact1_title'   => 'La Brévinoise Jeux',
		'contact1_text'    => "Chef de projets : Damien Marquis\ncontact@labrevinoise.fr",
		'contact2_title'   => 'Association La Brévinoise',
		'contact2_text'    => "Président : Yvonnick Cheraud\n17 avenue de la Prinais\n44250 Saint-Brévin-les-Pins\nlabrevinoise@yahoo.fr",
		'contact3_title'   => 'Tipim Studio',
		'contact3_text'    => "8 rue des Platanes\n44680 Saint-Mars-de-Coutais\nDéveloppement et illustration des jeux",
		'footer_about'     => "Section d'édition de jeux de société de l'association culturelle La Brévinoise, en partenariat avec Tipim Studio.",
		'footer_address'   => "17 avenue de la Prinais\n44250 Saint-Brévin-les-Pins\ncontact@labrevinoise.fr",
		'footer_extra_title' => 'Distribution',
		'footer_extra'     => "Boutique en ligne\nMAD Distribution : plus de 700 boutiques en France, Belgique et Suisse",
		'footer_legal'     => '© La Brévinoise Jeux',
	);
}

/**
 * Lit un réglage du thème, avec sa valeur par défaut.
 *
 * @param string $key Nom du réglage (sans préfixe).
 * @return mixed
 */
function brev_mod( $key ) {
	$d = brev_defaults();
	return get_theme_mod( 'brev_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * Les types de contenu du thème : clé courte => réglages.
 *
 * @return array
 */
function brev_types() {
	return array(
		'festival' => array(
			'pt'     => 'brev_festival',
			'slug'   => 'festivals',
			'name'   => 'Festivals (affiches)',
			'one'    => 'Affiche de festival',
			'icon'   => 'dashicons-format-image',
			'img'    => "Image de l'affiche",
			'fields' => array(
				'date'  => array( 'Date et horaires', 'text', 'Ex. : Sam. 17 et dim. 18 octobre 2026, 10 h – 18 h' ),
				'place' => array( 'Lieu', 'text', '' ),
				'link'  => array( 'Lien de redirection', 'url', 'Page du festival, billetterie… Laisser vide pour aucun bouton.' ),
				'games' => array( 'Jeux associés', 'games', 'Affichés dans la fenêtre d’infos.' ),
			),
		),
		'jeu'      => array(
			'pt'     => 'brev_jeu',
			'slug'   => 'jeux',
			'name'   => 'Jeux de société',
			'one'    => 'Jeu',
			'icon'   => 'dashicons-games',
			'img'    => 'Visuel de la boîte',
			'fields' => array(
				'tags'     => array( 'Étiquettes', 'text', 'Séparées par des virgules : 2–5 joueurs, 8 ans +, 30 min' ),
				'link'     => array( 'Lien vers la boutique', 'url', 'Quand on clique sur le jeu, on arrive sur cette adresse.' ),
				'video'    => array( 'Lien vidéo', 'url', 'YouTube, Vimeo ou autre : ajoute le bouton « Voir la vidéo » sur la fiche. Vide = pas de bouton.' ),
				'play'     => array( 'Jeu en ligne associé', 'play', 'Ajoute le bouton « Tester le jeu » sur la fiche.' ),
				'play_url' => array( "Ou adresse d'un autre jeu en ligne", 'url', 'Facultatif : remplace le choix ci-dessus.' ),
			),
		),
		'escape'   => array(
			'pt'     => 'brev_escape',
			'slug'   => 'escape-box',
			'name'   => 'Escape Box & accessoires',
			'one'    => 'Escape Box ou accessoire',
			'icon'   => 'dashicons-lock',
			'img'    => 'Visuel',
			'fields' => array(
				'kind'     => array( 'Catégorie', 'kind', '' ),
				'tags'     => array( 'Étiquettes', 'text', 'Séparées par des virgules' ),
				'link'     => array( 'Lien vers la boutique', 'url', 'Quand on clique, on arrive sur cette adresse.' ),
				'video'    => array( 'Lien vidéo', 'url', 'YouTube, Vimeo ou autre : ajoute le bouton « Voir la vidéo » sur la fiche. Vide = pas de bouton.' ),
				'play'     => array( 'Jeu en ligne associé', 'play', 'Ajoute le bouton « Tester le jeu ».' ),
				'play_url' => array( "Ou adresse d'un autre jeu en ligne", 'url', '' ),
			),
		),
		'bd'       => array(
			'pt'     => 'brev_bd',
			'slug'   => 'bandes-dessinees',
			'name'   => 'Bandes dessinées',
			'one'    => 'Bande dessinée',
			'icon'   => 'dashicons-book-alt',
			'img'    => 'Couverture',
			'fields' => array(
				'tags' => array( 'Étiquettes', 'text', 'Auteurs, nombre de pages, date…' ),
				'link' => array( 'Lien vers la boutique', 'url', "Quand on clique sur l'album, on arrive sur cette adresse." ),
				'video' => array( 'Lien vidéo', 'url', 'YouTube, Vimeo ou autre : ajoute le bouton « Voir la vidéo » sur la fiche. Vide = pas de bouton.' ),
			),
		),
		'adherent' => array(
			'pt'     => 'brev_adherent',
			'slug'   => 'adherents',
			'name'   => 'Adhérents',
			'one'    => 'Adhérent',
			'icon'   => 'dashicons-groups',
			'img'    => 'Photo',
			'fields' => array(
				'role'  => array( "Fonction dans l'association", 'text', '' ),
				'games' => array( 'Jeux et projets associés', 'games', '' ),
			),
		),
	);
}

/**
 * Jeux jouables dans le navigateur fournis par le thème.
 *
 * @return array
 */
function brev_nav_games() {
	return array(
		'gac' => array(
			'title' => 'Gare au Chacal',
			'slug'  => 'gare-au-chacal',
			'file'  => BREV_DIR . '/jeux/gare-au-chacal/game.html',
		),
	);
}
