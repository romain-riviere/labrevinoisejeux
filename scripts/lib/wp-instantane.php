<?php
/**
 * Test : photographie des données du site liées au thème (wp eval-file), en JSON.
 *
 * Comparée avant / après la mise à jour : toute différence signale une perte
 * ou une modification de contenus, réglages, menus ou adresses.
 */

$types = array( 'brev_festival', 'brev_jeu', 'brev_escape', 'brev_bd', 'brev_adherent' );
$snap  = array(
	'fiches'   => array(),
	'reglages' => get_option( 'theme_mods_brevinoise', array() ),
	'options'  => array(
		'brev_gac'        => get_option( 'brev_gac' ),
		'brev_update_url' => get_option( 'brev_update_url' ),
		'brev_demo_done'  => (bool) get_option( 'brev_demo_done' ),
	),
	'menus'    => array(),
	'pages'    => array(),
	'adresses' => array(),
);

foreach ( get_posts( array( 'post_type' => $types, 'post_status' => 'any', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $p ) {
	$meta = array();
	foreach ( get_post_meta( $p->ID ) as $k => $v ) {
		if ( 0 === strpos( $k, '_brev_' ) || '_thumbnail_id' === $k ) {
			$meta[ $k ] = maybe_unserialize( $v[0] );
		}
	}
	ksort( $meta );
	/* Valeurs telles que le formulaire « Informations » les affiche : un champ
	   renommé dans brev_types() y apparaîtrait vide, et serait effacé au prochain enregistrement. */
	$form = array();
	$k    = function_exists( 'brev_key_of' ) ? brev_key_of( $p->post_type ) : null;
	if ( $k ) {
		foreach ( array_keys( brev_types()[ $k ]['fields'] ) as $f ) {
			$v = get_post_meta( $p->ID, '_brev_' . $f, true );
			if ( '' !== $v && array() !== $v ) {
				$form[ $f ] = $v;
			}
		}
	}
	ksort( $form );
	$snap['fiches'][ $p->ID ] = array(
		'type'       => $p->post_type,
		'titre'      => $p->post_title,
		'texte'      => md5( $p->post_content ),
		'statut'     => $p->post_status,
		'ordre'      => (int) $p->menu_order,
		'meta'       => $meta,
		'formulaire' => $form,
		'adresse'    => get_permalink( $p ),
	);
}

foreach ( get_nav_menu_locations() as $loc => $menu_id ) {
	$items = array();
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $it ) {
		$items[] = array( 'titre' => $it->title, 'adresse' => $it->url, 'objet' => $it->object );
	}
	$snap['menus'][ $loc ] = $items;
}

foreach ( get_pages() as $pg ) {
	$snap['pages'][ $pg->post_name ] = array(
		'modele'  => get_page_template_slug( $pg ),
		'adresse' => get_permalink( $pg ),
	);
}

/* Adresses publiques à visiter. */
$urls = array( home_url( '/' ) );
foreach ( $types as $pt ) {
	$urls[] = get_post_type_archive_link( $pt );
}
foreach ( $snap['fiches'] as $f ) {
	$urls[] = $f['adresse'];
}
foreach ( $snap['pages'] as $pg ) {
	$urls[] = $pg['adresse'];
}
if ( function_exists( 'brev_game_url' ) ) {
	$urls[] = brev_game_url( 'gac' );
}
$snap['adresses'] = array_values( array_unique( array_filter( $urls ) ) );

echo wp_json_encode( $snap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), "\n";
