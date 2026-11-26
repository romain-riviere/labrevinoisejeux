<?php
/**
 * Apparence › Personnaliser : couleurs, logo, liens, boutons et textes.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', 'brev_customize' );
/**
 * Panneau « La Brévinoise » du Personnaliseur.
 *
 * @param WP_Customize_Manager $c Personnaliseur.
 */
function brev_customize( $c ) {
	$d = brev_defaults();
	$c->add_panel( 'brev', array( 'title' => 'La Brévinoise', 'priority' => 20 ) );

	$add = function ( $section, $key, $label, $type = 'text', $desc = '' ) use ( $c, $d ) {
		$sanit = array(
			'text'     => 'sanitize_text_field',
			'textarea' => 'sanitize_textarea_field',
			'url'      => 'esc_url_raw',
			'link'     => 'brev_sanitize_link',
			'email'    => 'sanitize_email',
			'checkbox' => 'brev_sanitize_bool',
			'range'    => 'absint',
			'color'    => 'sanitize_hex_color',
			'image'    => 'esc_url_raw',
		);
		$c->add_setting( 'brev_' . $key, array( 'default' => $d[ $key ], 'sanitize_callback' => $sanit[ $type ] ) );
		$args = array( 'label' => $label, 'section' => $section, 'description' => $desc );
		if ( 'color' === $type ) {
			$c->add_control( new WP_Customize_Color_Control( $c, 'brev_' . $key, $args ) );
		} elseif ( 'image' === $type ) {
			$c->add_control( new WP_Customize_Image_Control( $c, 'brev_' . $key, $args ) );
		} else {
			$args['type'] = in_array( $type, array( 'link' ), true ) ? 'text' : $type;
			if ( 'range' === $type ) {
				$args['input_attrs'] = array( 'min' => 48, 'max' => 220, 'step' => 2 );
			}
			$c->add_control( 'brev_' . $key, $args );
		}
	};

	/* Logo & identité (le logo lui-même se choisit dans « Identité du site ») */
	$c->add_section( 'brev_identity', array( 'title' => 'Logo & identité', 'panel' => 'brev', 'description' => 'Le logo se change dans « Identité du site » (menu précédent).' ) );
	$add( 'brev_identity', 'logo_size', 'Taille du logo (en pixels)', 'range', 'Petit 72 · Moyen 96 · Grand 140 · Très grand 180' );
	$add( 'brev_identity', 'show_name', 'Afficher le nom du site à côté du logo', 'checkbox' );

	/* Couleurs */
	$c->add_section( 'brev_colors', array( 'title' => 'Couleurs', 'panel' => 'brev' ) );
	$add( 'brev_colors', 'color_primary', 'Couleur principale (menu, titres)', 'color' );
	$add( 'brev_colors', 'color_dark', 'Couleur foncée (pied de page)', 'color' );
	$add( 'brev_colors', 'color_accent', "Couleur d'accent (boutique, badges)", 'color' );
	$add( 'brev_colors', 'color_soft', 'Fond des sections claires', 'color' );

	/* Boutique & boutons */
	$c->add_section( 'brev_links', array( 'title' => 'Boutique & boutons', 'panel' => 'brev', 'description' => 'Les liens de chaque jeu, BD, accessoire et affiche se règlent dans leur fiche. Les onglets du menu se règlent dans Apparence › Menus.' ) );
	$add( 'brev_links', 'shop_url', 'Adresse de la boutique en ligne', 'url', "L'onglet du menu qui pointe vers cette adresse est mis en valeur." );
	$add( 'brev_links', 'item_btn', 'Bouton sur les jeux, BD et accessoires' );
	$add( 'brev_links', 'play_btn', 'Bouton des jeux en ligne' );
	$add( 'brev_links', 'video_btn', 'Bouton vidéo des fiches' );

	/* Accueil */
	$c->add_section( 'brev_home', array( 'title' => "Page d'accueil", 'panel' => 'brev' ) );
	$add( 'brev_home', 'hero_title', 'Titre (centré, au-dessus)' );
	$add( 'brev_home', 'hero_image', 'Image de droite', 'image' );
	$add( 'brev_home', 'hero_art_label', "Texte au-dessus de l'image", 'text', 'Bandeau blanc sur fond de la couleur principale. Vide = pas de bandeau.' );
	$add( 'brev_home', 'hero_text1', 'Paragraphe 1', 'textarea' );
	$add( 'brev_home', 'hero_text2', 'Paragraphe 2', 'textarea' );
	$add( 'brev_home', 'hero_btn1_label', 'Texte du 1er bouton' );
	$add( 'brev_home', 'hero_btn1_link', 'Lien du 1er bouton', 'link', 'Vide = page des jeux.' );
	$add( 'brev_home', 'hero_btn2_label', 'Texte du 2e bouton' );
	$add( 'brev_home', 'hero_btn2_link', 'Lien du 2e bouton', 'link', 'Vide = page Contact.' );
	foreach ( array( 'fest' => 'festivals', 'jeux' => 'jeux', 'escape' => 'escape box', 'bd' => 'BD' ) as $s => $l ) {
		$add( 'brev_home', $s . '_title', 'Section ' . $l . ' : titre' );
		$add( 'brev_home', $s . '_text', 'Section ' . $l . ' : texte', 'textarea' );
		$add( 'brev_home', $s . '_btn', 'Section ' . $l . ' : bouton' );
	}

	/* Pages */
	$c->add_section( 'brev_pages', array( 'title' => 'Titres et introductions des pages', 'panel' => 'brev' ) );
	foreach ( brev_types() as $k => $t ) {
		$add( 'brev_pages', 'title_' . $k, 'Page « ' . $t['name'] . ' » : titre' );
		$add( 'brev_pages', 'intro_' . $k, 'Page « ' . $t['name'] . ' » : introduction', 'textarea' );
	}

	/* Contact & pied de page */
	$c->add_section( 'brev_contact', array( 'title' => 'Contact & pied de page', 'panel' => 'brev' ) );
	$add( 'brev_contact', 'contact_intro', 'Page Contact : introduction', 'textarea' );
	$add( 'brev_contact', 'contact_email', 'Adresse qui reçoit les messages du formulaire', 'email', "Vide = adresse e-mail de l'administrateur du site." );
	for ( $i = 1; $i <= 3; $i++ ) {
		$add( 'brev_contact', 'contact' . $i . '_title', 'Bloc ' . $i . ' : titre', 'text', 'Laisser vide pour masquer le bloc.' );
		$add( 'brev_contact', 'contact' . $i . '_text', 'Bloc ' . $i . ' : texte', 'textarea', 'Une information par ligne.' );
	}
	$add( 'brev_contact', 'footer_about', 'Pied de page : présentation', 'textarea' );
	$add( 'brev_contact', 'footer_address', 'Pied de page : coordonnées', 'textarea' );
	$add( 'brev_contact', 'footer_extra_title', 'Pied de page : titre de la 4e colonne' );
	$add( 'brev_contact', 'footer_extra', 'Pied de page : 4e colonne', 'textarea' );
	$add( 'brev_contact', 'footer_legal', 'Mention en bas de page' );
}

/**
 * Accepte une adresse complète, une ancre (#…) ou un chemin (/…).
 *
 * @param string $v Valeur.
 * @return string
 */
function brev_sanitize_link( $v ) {
	$v = trim( (string) $v );
	if ( '' === $v ) {
		return '';
	}
	if ( '#' === $v[0] || '/' === $v[0] ) {
		return sanitize_text_field( $v );
	}
	return esc_url_raw( $v );
}

/**
 * Case à cocher.
 *
 * @param mixed $v Valeur.
 * @return bool
 */
function brev_sanitize_bool( $v ) {
	return (bool) $v;
}
