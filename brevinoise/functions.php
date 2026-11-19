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

require BREV_DIR . '/inc/defaults.php';
require BREV_DIR . '/inc/setup.php';
require BREV_DIR . '/inc/post-types.php';
require BREV_DIR . '/inc/customizer.php';
require BREV_DIR . '/inc/template-tags.php';
require BREV_DIR . '/inc/contact.php';
require BREV_DIR . '/inc/game.php';
require BREV_DIR . '/inc/tools.php';
