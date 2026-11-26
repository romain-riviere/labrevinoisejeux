<?php
/**
 * Thème La Brévinoise Jeux — fonctions principales.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BREV_VERSION', wp_get_theme( get_template() )->get( 'Version' ) ? wp_get_theme( get_template() )->get( 'Version' ) : '1.0.0' );
define( 'BREV_DIR', get_template_directory() );
define( 'BREV_URI', get_template_directory_uri() );

/*
 * Organisation de inc/ :
 *   core/      socle : valeurs par défaut (registre central) et mise en place du thème
 *   content/   types de contenu et fonctions d'affichage
 *   features/  fonctionnalités : formulaire de contact, jeu en ligne
 *   admin/     administration : Personnaliser, page d'outils
 * L'ordre ci-dessous compte : defaults.php doit passer en premier.
 */
require BREV_DIR . '/inc/core/defaults.php';
require BREV_DIR . '/inc/core/setup.php';
require BREV_DIR . '/inc/content/post-types.php';
require BREV_DIR . '/inc/admin/customizer.php';
require BREV_DIR . '/inc/content/template-tags.php';
require BREV_DIR . '/inc/features/contact.php';
require BREV_DIR . '/inc/features/game.php';
require BREV_DIR . '/inc/admin/tools.php';
