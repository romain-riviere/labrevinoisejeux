<?php
/**
 * Extrait le « contrat » d'un thème La Brévinoise : tous les noms qui relient
 * le thème aux données stockées dans WordPress (types de contenu, champs,
 * réglages, options, menus, adresses…).
 *
 * Le code du thème est chargé tel quel, avec de fausses fonctions WordPress
 * qui enregistrent ce que le thème déclare. Aucune base de données n'est utilisée.
 *
 * Usage : php contrat.php <dossier du thème>
 * Sortie : une ligne par élément, triées. Une ligne qui disparaît d'une version
 * à l'autre = des contenus ou réglages du site en production deviennent orphelins.
 *
 * Code de sortie 1 si le thème est incohérent (ex. réglage Personnaliser sans valeur par défaut).
 */

error_reporting( E_ALL );

if ( $argc < 2 || ! is_file( $argv[1] . '/functions.php' ) ) {
	fwrite( STDERR, "Usage : php contrat.php <dossier contenant functions.php>\n" );
	exit( 2 );
}
$theme = rtrim( realpath( $argv[1] ), '/' );

/* ----- Fausses fonctions WordPress ----- */

$GLOBALS['brev_rec'] = array(
	'filters'    => array(),
	'post_types' => array(),
	'meta'       => array(),
	'menus'      => array(),
	'rewrite'    => array(),
	'settings'   => array(),
);

define( 'ABSPATH', '/tmp/' );
define( 'BREV_VERSION', '0.0.0' );
define( 'BREV_DIR', $theme );
define( 'BREV_URI', 'https://example.test/wp-content/themes/brevinoise' );

function add_action( $hook, $cb, $prio = 10, $args = 1 ) {
	$GLOBALS['brev_rec']['filters'][ $hook ][] = $cb;
	return true;
}
function add_filter( $hook, $cb, $prio = 10, $args = 1 ) {
	return add_action( $hook, $cb, $prio, $args );
}
function brev_apply( $hook, $value ) {
	foreach ( isset( $GLOBALS['brev_rec']['filters'][ $hook ] ) ? $GLOBALS['brev_rec']['filters'][ $hook ] : array() as $cb ) {
		$value = call_user_func( $cb, $value );
	}
	return $value;
}
function register_post_type( $pt, $args ) {
	$GLOBALS['brev_rec']['post_types'][ $pt ] = $args;
}
function register_post_meta( $pt, $key, $args ) {
	$GLOBALS['brev_rec']['meta'][ $pt ][] = $key;
}
function register_nav_menus( $menus ) {
	$GLOBALS['brev_rec']['menus'] = array_merge( $GLOBALS['brev_rec']['menus'], array_keys( $menus ) );
}
function add_rewrite_rule( $regex, $query, $after = 'bottom' ) {
	$GLOBALS['brev_rec']['rewrite'][] = $regex . ' => ' . $query;
}
function register_setting( $group, $name, $args = array() ) {
	$GLOBALS['brev_rec']['settings'][] = $name;
}
function load_theme_textdomain() {}
function add_theme_support() {}

/** Faux gestionnaire Personnaliser : enregistre les réglages déclarés. */
class Brev_Fake_Customizer {
	public $settings = array();
	public function add_setting( $id, $args = array() ) {
		$this->settings[ $id ] = $args;
	}
	public function __call( $name, $args ) {}
}
class WP_Customize_Control {
	public function __construct() {}
}
class WP_Customize_Color_Control extends WP_Customize_Control {}
class WP_Customize_Image_Control extends WP_Customize_Control {}

/* ----- Chargement du thème ----- */

/*
 * Fichiers de inc/ : à plat (versions ≤ 1.2.0) ou rangés en sous-dossiers (core/, content/…).
 * Les deux organisations doivent être lisibles, car on compare avec le dernier tag publié.
 */
$inc_files = array_merge( glob( $theme . '/inc/*.php' ), glob( $theme . '/inc/*/*.php' ) );
foreach ( $inc_files as $f ) {
	require_once $f;
}

$errors = array();
$out    = array();
$need   = function ( $fn ) use ( &$errors ) {
	if ( ! function_exists( $fn ) ) {
		$errors[] = "fonction $fn() introuvable";
		return false;
	}
	return true;
};

/* Types de contenu et champs. */
if ( $need( 'brev_register_types' ) && $need( 'brev_types' ) ) {
	brev_register_types();
	foreach ( $GLOBALS['brev_rec']['post_types'] as $pt => $a ) {
		$slug  = isset( $a['rewrite']['slug'] ) ? $a['rewrite']['slug'] : $pt;
		$out[] = "post_type $pt slug=$slug archive=" . ( isset( $a['has_archive'] ) ? var_export( $a['has_archive'], true ) : 'false' );
	}
	foreach ( brev_types() as $k => $t ) {
		$out[] = "type_key $k => {$t['pt']}";
		foreach ( $t['fields'] as $f => $def ) {
			$out[] = "meta {$t['pt']} _brev_$f type={$def[1]}";
		}
	}
}

/* Réglages Personnaliser (theme_mods) : la clé et sa présence dans brev_defaults(). */
if ( $need( 'brev_defaults' ) ) {
	$defaults = brev_defaults();
	foreach ( array_keys( $defaults ) as $k ) {
		$out[] = "theme_mod brev_$k";
	}
	if ( $need( 'brev_customize' ) ) {
		$c = new Brev_Fake_Customizer();
		brev_customize( $c );
		foreach ( array_keys( $c->settings ) as $id ) {
			if ( ! array_key_exists( substr( $id, 5 ), $defaults ) ) {
				$errors[] = "réglage Personnaliser « $id » absent de brev_defaults()";
			}
		}
	}
}

/* Menus, adresses, variables d'URL. */
if ( $need( 'brev_setup' ) ) {
	brev_setup();
}
foreach ( $GLOBALS['brev_rec']['menus'] as $m ) {
	$out[] = "menu $m";
}
foreach ( isset( $GLOBALS['brev_rec']['filters']['init'] ) ? $GLOBALS['brev_rec']['filters']['init'] : array() as $cb ) {
	if ( is_string( $cb ) && false !== strpos( $cb, 'rewrite' ) ) {
		call_user_func( $cb );
	}
}
foreach ( $GLOBALS['brev_rec']['rewrite'] as $r ) {
	$out[] = "rewrite $r";
}
foreach ( brev_apply( 'query_vars', array() ) as $v ) {
	$out[] = "query_var $v";
}

/* Jeu en ligne : adresse publique, réglages enregistrés (bots, cartes). */
if ( $need( 'brev_nav_games' ) ) {
	foreach ( brev_nav_games() as $id => $g ) {
		$out[] = "nav_game $id slug={$g['slug']}";
		if ( ! is_readable( $g['file'] ) ) {
			$errors[] = "fichier du jeu $id introuvable : " . str_replace( $theme . '/', '', $g['file'] );
		}
	}
}
if ( $need( 'brev_gac_defaults' ) ) {
	foreach ( array_keys( brev_gac_defaults() ) as $k ) {
		$out[] = "gac_setting $k";
	}
	foreach ( array_keys( brev_gac_defaults()['bots'] ) as $k ) {
		$out[] = "gac_bot $k";
	}
}
if ( $need( 'brev_gac_cards' ) ) {
	foreach ( array_keys( brev_gac_cards() ) as $k ) {
		$out[] = "gac_card $k";
	}
}

/* Options WordPress (wp_options) lues ou écrites par le thème. */
if ( $need( 'brev_gac_register' ) ) {
	brev_gac_register();
}
$options = $GLOBALS['brev_rec']['settings'];
$files   = array_merge( glob( $theme . '/*.php' ), $inc_files );
foreach ( $files as $f ) {
	if ( preg_match_all( "~_option\(\s*'(brev_[a-z0-9_]+)'~", file_get_contents( $f ), $m ) ) {
		$options = array_merge( $options, $m[1] );
	}
}
foreach ( array_unique( $options ) as $o ) {
	$out[] = "option $o";
}

/* Modèles de page choisis dans l'administration (enregistrés dans _wp_page_template). */
foreach ( glob( $theme . '/*.php' ) as $f ) {
	if ( preg_match( '~Template Name:~', file_get_contents( $f ) ) ) {
		$out[] = 'page_template ' . basename( $f );
	}
}

if ( $errors ) {
	foreach ( $errors as $e ) {
		fwrite( STDERR, "ERREUR contrat : $e\n" );
	}
	exit( 1 );
}

$out = array_unique( $out );
sort( $out );
echo implode( "\n", $out ), "\n";
