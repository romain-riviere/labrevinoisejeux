<?php
/**
 * Test : remplit le site comme le ferait l'association (wp eval-file).
 *
 * Contenu de démonstration + réglages personnalisés + champs remplis à la main.
 * Les valeurs « TEST » servent de repères : elles doivent rester visibles
 * sur le site après la mise à jour du thème.
 */

brev_import_demo();

/* Textes et couleurs personnalisés (Apparence › Personnaliser). */
$mods = array(
	'hero_title'    => 'Des jeux drôles, rapides et fédérateurs, créés à Saint-Mars-De-Coutais TEST',
	'jeux_title'    => 'Les jeux de société TEST',
	'footer_legal'  => '© La Brévinoise Jeux TEST',
	'contact_intro' => 'Une question sur nos jeux, une commande pour votre boutique ou une animation pour votre événement ? Écrivez-nous. TEST',
	'color_primary' => '#11336a',
	'logo_size'     => 96,
);
foreach ( $mods as $k => $v ) {
	set_theme_mod( 'brev_' . $k, $v );
}

/* Réglages du jeu Gare au Chacal. */
$gac                          = brev_gac();
$gac['shop_url']              = 'https://exemple.test/boutique-chacal';
$gac['win_msg']               = 'Bravo TEST';
$gac['table']                 = 'vert';
$gac['bots']['nico']['name']  = 'NicoTEST';
update_option( 'brev_gac', brev_gac_sanitize( $gac ) );

/* Une fiche complétée à la main : étiquettes, vidéo, galerie. */
$surf = get_posts( array( 'post_type' => 'brev_jeu', 'title' => 'Surf and Cats', 'numberposts' => 1 ) );
if ( ! $surf ) {
	WP_CLI::error( 'Fiche « Surf and Cats » introuvable après import de la démo.' );
}
$id  = $surf[0]->ID;
$img = get_posts( array( 'post_type' => 'attachment', 'numberposts' => 2, 'fields' => 'ids', 'post_status' => 'inherit' ) );
update_post_meta( $id, '_brev_tags', '2–5 joueurs, Étiquette TEST' );
update_post_meta( $id, '_brev_video', 'https://www.youtube.com/watch?v=TESTvideo01' );
update_post_meta( $id, '_brev_gallery', implode( ',', $img ) );

/* Adresse de mise à jour automatique (Apparence › La Brévinoise : outils). */
update_option( 'brev_update_url', 'http://maj/brevinoise-update.json' );

WP_CLI::success( 'Site rempli.' );
