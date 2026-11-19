<?php
/**
 * Mise en place du thème : supports, menus, styles, ordre d'affichage.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'brev_setup' );
/**
 * Fonctions WordPress utilisées par le thème.
 */
function brev_setup() {
	load_theme_textdomain( 'brevinoise', BREV_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 300, 'width' => 300, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	register_nav_menus( array( 'primary' => 'Menu principal' ) );
}

add_action( 'wp_enqueue_scripts', 'brev_assets' );
/**
 * Polices, styles et scripts du site public.
 */
function brev_assets() {
	wp_enqueue_style( 'brev-fonts', 'https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'brev-style', get_stylesheet_uri(), array(), BREV_VERSION );
	wp_add_inline_style( 'brev-style', brev_inline_css() );
	wp_enqueue_script( 'brev-theme', BREV_URI . '/assets/theme.js', array(), BREV_VERSION, true );
}

/**
 * Couleurs et taille du logo choisies dans Personnaliser.
 *
 * @return string
 */
function brev_inline_css() {
	$hex  = function ( $k ) {
		$v = sanitize_hex_color( brev_mod( $k ) );
		$d = brev_defaults();
		return $v ? $v : $d[ $k ];
	};
	$size = max( 40, min( 260, absint( brev_mod( 'logo_size' ) ) ) );
	return sprintf(
		':root{--primary:%s;--dark:%s;--accent:%s;--soft:%s;--logo:%dpx}',
		$hex( 'color_primary' ),
		$hex( 'color_dark' ),
		$hex( 'color_accent' ),
		$hex( 'color_soft' ),
		$size
	);
}

add_action( 'pre_get_posts', 'brev_order_queries' );
/**
 * Pages d'archives : tout afficher, dans l'ordre choisi (champ « Ordre »), puis du plus récent au plus ancien.
 *
 * @param WP_Query $q Requête.
 */
function brev_order_queries( $q ) {
	$pts = wp_list_pluck( brev_types(), 'pt' );
	if ( is_admin() ) {
		if ( $q->is_main_query() && in_array( $q->get( 'post_type' ), $pts, true ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		}
		return;
	}
	if ( $q->is_main_query() && $q->is_post_type_archive( $pts ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}

add_action( 'init', 'brev_maybe_flush', 99 );
/**
 * Recalcule les adresses (permaliens) après installation ou mise à jour du thème.
 */
function brev_maybe_flush() {
	if ( get_option( 'brev_rewrite_version' ) !== BREV_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'brev_rewrite_version', BREV_VERSION );
	}
}

add_filter( 'nav_menu_css_class', 'brev_menu_classes', 10, 2 );
/**
 * Met en valeur le lien Boutique et l'onglet de la page en cours.
 *
 * @param array   $classes Classes.
 * @param WP_Post $item    Élément de menu.
 * @return array
 */
function brev_menu_classes( $classes, $item ) {
	$shop = untrailingslashit( (string) brev_mod( 'shop_url' ) );
	if ( $shop && untrailingslashit( (string) $item->url ) === $shop ) {
		$classes[] = 'shop';
	}
	if ( 'custom' === $item->type ) {
		$here = untrailingslashit( set_url_scheme( home_url( add_query_arg( array() ) ) ) );
		if ( untrailingslashit( set_url_scheme( $item->url ) ) === $here ) {
			$classes[] = 'current-menu-item';
		}
	}
	return $classes;
}

add_filter( 'nav_menu_link_attributes', 'brev_menu_link_attrs', 10, 2 );
/**
 * Le lien Boutique s'ouvre dans un nouvel onglet.
 *
 * @param array   $atts Attributs.
 * @param WP_Post $item Élément.
 * @return array
 */
function brev_menu_link_attrs( $atts, $item ) {
	if ( in_array( 'shop', (array) $item->classes, true ) || untrailingslashit( (string) $item->url ) === untrailingslashit( (string) brev_mod( 'shop_url' ) ) ) {
		$atts['target'] = '_blank';
		$atts['rel']    = 'noopener';
	}
	return $atts;
}

/**
 * Menu par défaut, tant qu'aucun menu n'est choisi dans Apparence › Menus.
 */
function brev_fallback_menu() {
	$items   = brev_default_menu_items();
	$current = untrailingslashit( set_url_scheme( home_url( add_query_arg( array() ) ) ) );
	echo '<ul class="menu-list">';
	foreach ( $items as $it ) {
		$cls = array();
		if ( ! empty( $it['shop'] ) ) {
			$cls[] = 'shop';
		}
		if ( untrailingslashit( $it['url'] ) === $current ) {
			$cls[] = 'current-menu-item';
		}
		printf(
			'<li class="%s"><a href="%s"%s>%s</a></li>',
			esc_attr( implode( ' ', $cls ) ),
			esc_url( $it['url'] ),
			! empty( $it['shop'] ) ? ' target="_blank" rel="noopener"' : '',
			esc_html( $it['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Onglets du menu par défaut (aussi utilisés par l'import de démonstration).
 *
 * @return array
 */
function brev_default_menu_items() {
	$t     = brev_types();
	$items = array( array( 'label' => 'Accueil', 'url' => home_url( '/' ) ) );
	foreach ( array( 'festival', 'jeu', 'escape', 'bd', 'adherent' ) as $k ) {
		$link = get_post_type_archive_link( $t[ $k ]['pt'] );
		if ( $link ) {
			$items[] = array( 'label' => brev_mod( 'title_' . $k ), 'url' => $link );
		}
	}
	$items[] = array( 'label' => 'Boutique', 'url' => brev_mod( 'shop_url' ), 'shop' => true );
	$contact = brev_contact_page_id();
	if ( $contact ) {
		$items[] = array( 'label' => 'Contact', 'url' => get_permalink( $contact ) );
	}
	return $items;
}

/**
 * Page qui utilise le modèle « Contact ».
 *
 * @return int
 */
function brev_contact_page_id() {
	$p = get_posts(
		array(
			'post_type'   => 'page',
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'template-contact.php',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	return $p ? (int) $p[0] : 0;
}
