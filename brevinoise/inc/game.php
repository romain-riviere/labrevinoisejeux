<?php
/**
 * Jeux en ligne : page de réglages « Gare au Chacal » (admin) et affichage du jeu.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cartes personnalisables.
 *
 * @return array clé => [nom, emoji]
 */
function brev_gac_cards() {
	return array(
		'b0'   => array( 'Cheval', '🐴' ),
		'b1'   => array( 'Vache', '🐄' ),
		'b2'   => array( 'Mouton', '🐑' ),
		'b3'   => array( 'Chèvre', '🐐' ),
		'b4'   => array( 'Cochon', '🐖' ),
		'b5'   => array( 'Lapin', '🐇' ),
		'b6'   => array( 'Canard', '🦆' ),
		'b7'   => array( 'Poule', '🐔' ),
		'b8'   => array( 'Poussin', '🐤' ),
		'c'    => array( 'Chacal', '🐺' ),
		'jd'   => array( 'Chien de garde', '🐕' ),
		'jf'   => array( 'Renard', '🦊' ),
		'back' => array( 'Dos des cartes', '🐾' ),
	);
}

/**
 * Réglages par défaut du jeu.
 *
 * @return array
 */
function brev_gac_defaults() {
	return array(
		'shop_url' => 'https://www.sandramoreiraeditions.com',
		'win_msg'  => 'Tu as bien aimé le jeu ? Tu peux maintenant le commander :',
		'win_btn'  => 'Commander Gare au Chacal',
		'lose_msg' => 'Mais tu peux te refaire en achetant le jeu ici :',
		'lose_btn' => 'Acheter le jeu',
		'table'    => 'miel',
		'speed'    => 1,
		'bots'     => array(
			'skinner' => array( 'name' => 'SkinR', 'tag' => '★ Débutant', 'desc' => 'Joue un peu au hasard et oublie souvent ses Jokers.', 'av' => 0, 'emo' => '🐔' ),
			'nico'    => array( 'name' => 'Nicodrën', 'tag' => '★★ Moyen', 'desc' => 'Pose ses bêtes avec bon sens, sans trop calculer.', 'av' => 0, 'emo' => '🐄' ),
			'tan'     => array( 'name' => 'Than', 'tag' => '★★★ Costaud', 'desc' => 'Combine ses cartes et sort le Chacal au bon moment.', 'av' => 0, 'emo' => '🦊' ),
			'tipim'   => array( 'name' => 'Tipim', 'tag' => '★★★★ Redoutable', 'desc' => 'Calcule chaque coup. Gare à tes élevages !', 'av' => 0, 'emo' => '🐺' ),
		),
		'cards'    => array(),
	);
}

/**
 * Réglages actuels du jeu (complétés par les valeurs par défaut).
 *
 * @return array
 */
function brev_gac() {
	$d = brev_gac_defaults();
	$o = get_option( 'brev_gac', array() );
	$o = is_array( $o ) ? $o : array();
	$r = array_merge( $d, $o );
	foreach ( $d['bots'] as $id => $b ) {
		$r['bots'][ $id ] = array_merge( $b, isset( $o['bots'][ $id ] ) ? (array) $o['bots'][ $id ] : array() );
	}
	$r['cards'] = isset( $o['cards'] ) ? (array) $o['cards'] : array();
	return $r;
}

add_action( 'admin_init', 'brev_gac_register' );
/**
 * Déclare l'option du jeu.
 */
function brev_gac_register() {
	register_setting( 'brev_gac_group', 'brev_gac', array( 'type' => 'array', 'sanitize_callback' => 'brev_gac_sanitize' ) );
}

/**
 * Nettoie les réglages envoyés.
 *
 * @param array $in Valeurs.
 * @return array
 */
function brev_gac_sanitize( $in ) {
	$d   = brev_gac_defaults();
	$in  = is_array( $in ) ? $in : array();
	$out = array(
		'shop_url' => esc_url_raw( isset( $in['shop_url'] ) ? $in['shop_url'] : '' ),
		'table'    => isset( $in['table'] ) && in_array( $in['table'], array( 'miel', 'sombre', 'vert', 'nappe' ), true ) ? $in['table'] : 'miel',
		'speed'    => isset( $in['speed'] ) ? max( 0, min( 2, (int) $in['speed'] ) ) : 1,
		'bots'     => array(),
		'cards'    => array(),
	);
	foreach ( array( 'win_msg', 'win_btn', 'lose_msg', 'lose_btn' ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : $d[ $k ];
	}
	foreach ( $d['bots'] as $id => $b ) {
		$v                  = isset( $in['bots'][ $id ] ) ? (array) $in['bots'][ $id ] : array();
		$out['bots'][ $id ] = array(
			'name' => isset( $v['name'] ) && '' !== trim( $v['name'] ) ? sanitize_text_field( $v['name'] ) : $b['name'],
			'tag'  => isset( $v['tag'] ) ? sanitize_text_field( $v['tag'] ) : $b['tag'],
			'desc' => isset( $v['desc'] ) ? sanitize_text_field( $v['desc'] ) : $b['desc'],
			'av'   => isset( $v['av'] ) ? absint( $v['av'] ) : 0,
		);
	}
	foreach ( array_keys( brev_gac_cards() ) as $k ) {
		if ( ! empty( $in['cards'][ $k ] ) ) {
			$out['cards'][ $k ] = absint( $in['cards'][ $k ] );
		}
	}
	return $out;
}

add_action( 'admin_menu', 'brev_gac_menu' );
/**
 * Menu « Gare au Chacal » de l'administration (réservé aux administrateurs).
 */
function brev_gac_menu() {
	$hook = add_menu_page( 'Gare au Chacal : jeu en ligne', 'Gare au Chacal', 'manage_options', 'brev-gac', 'brev_gac_page', 'dashicons-pets', 11 );
	add_action(
		'admin_print_scripts-' . $hook,
		function () {
			wp_enqueue_media();
			wp_enqueue_script( 'brev-admin-media', BREV_URI . '/assets/admin-media.js', array( 'jquery' ), BREV_VERSION, true );
		}
	);
}

/**
 * Sélecteur d'image de la médiathèque.
 *
 * @param string $name    Nom du champ.
 * @param int    $id      Image actuelle.
 * @param string $emoji   Image d'origine.
 * @param string $shape   card|round.
 */
function brev_media_field( $name, $id, $emoji, $shape ) {
	$url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	echo '<div class="brev-media ' . esc_attr( $shape ) . '" data-emoji="' . esc_attr( $emoji ) . '">';
	echo '<div class="brev-prev">' . ( $url ? '<img src="' . esc_url( $url ) . '" alt="">' : '<span>' . esc_html( $emoji ) . '</span>' ) . '</div>';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $id ? $id : '' ) . '">';
	echo '<button type="button" class="button brev-pick">Choisir une image</button> ';
	echo '<button type="button" class="button-link brev-clear"' . ( $url ? '' : ' hidden' ) . '>Image d’origine</button></div>';
}

/**
 * Page de réglages du jeu.
 */
function brev_gac_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o      = brev_gac();
	$url    = brev_game_url( 'gac' );
	$linked = get_posts(
		array(
			'post_type'   => array( 'brev_jeu', 'brev_escape' ),
			'meta_key'    => '_brev_play',
			'meta_value'  => 'gac',
			'numberposts' => -1,
		)
	);
	?>
	<div class="wrap brev-gac">
		<h1 class="wp-heading-inline">Gare au Chacal : jeu en ligne</h1>
		<a class="page-title-action" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">Ouvrir le jeu ↗</a>
		<hr class="wp-header-end">
		<?php settings_errors(); ?>
		<div class="notice notice-info inline"><p>
			Adresse du jeu : <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $url ); ?></a><br>
			Bouton « Tester le jeu » affiché sur :
			<b><?php echo $linked ? esc_html( implode( ', ', wp_list_pluck( $linked, 'post_title' ) ) ) : 'aucune fiche pour le moment'; ?></b>
			(champ « Jeu en ligne associé » d’une fiche de jeu).<br>
			Les visiteurs ne peuvent rien personnaliser : avatars, cartes et table se règlent uniquement ici.
		</p></div>
		<form method="post" action="options.php">
			<?php settings_fields( 'brev_gac_group' ); ?>
			<h2>Fin de partie et boutique</h2>
			<table class="form-table"><tbody>
				<tr><th><label for="gac_shop">Lien de la boutique</label></th><td><input class="large-text" type="url" id="gac_shop" name="brev_gac[shop_url]" value="<?php echo esc_attr( $o['shop_url'] ); ?>"><p class="description">Gagnant ou perdant, le joueur est invité à acheter le jeu à cette adresse.</p></td></tr>
				<tr><th><label for="gac_wm">Message si le joueur gagne</label></th><td><input class="large-text" id="gac_wm" name="brev_gac[win_msg]" value="<?php echo esc_attr( $o['win_msg'] ); ?>"></td></tr>
				<tr><th><label for="gac_wb">Bouton si le joueur gagne</label></th><td><input class="regular-text" id="gac_wb" name="brev_gac[win_btn]" value="<?php echo esc_attr( $o['win_btn'] ); ?>"></td></tr>
				<tr><th><label for="gac_lm">Message si le joueur perd</label></th><td><input class="large-text" id="gac_lm" name="brev_gac[lose_msg]" value="<?php echo esc_attr( $o['lose_msg'] ); ?>"></td></tr>
				<tr><th><label for="gac_lb">Bouton si le joueur perd</label></th><td><input class="regular-text" id="gac_lb" name="brev_gac[lose_btn]" value="<?php echo esc_attr( $o['lose_btn'] ); ?>"></td></tr>
				<tr><th><label for="gac_t">Table de jeu</label></th><td><select id="gac_t" name="brev_gac[table]">
					<?php foreach ( array( 'miel' => 'Bois miel', 'sombre' => 'Bois sombre', 'vert' => 'Tapis vert', 'nappe' => 'Nappe à carreaux' ) as $v => $l ) : ?>
						<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $o['table'], $v ); ?>><?php echo esc_html( $l ); ?></option>
					<?php endforeach; ?></select></td></tr>
				<tr><th><label for="gac_s">Vitesse des adversaires</label></th><td><select id="gac_s" name="brev_gac[speed]">
					<?php foreach ( array( 0 => 'Tranquille', 1 => 'Normale', 2 => 'Rapide' ) as $v => $l ) : ?>
						<option value="<?php echo (int) $v; ?>" <?php selected( (int) $o['speed'], $v ); ?>><?php echo esc_html( $l ); ?></option>
					<?php endforeach; ?></select></td></tr>
			</tbody></table>

			<h2>Adversaires (bots)</h2>
			<table class="widefat striped brev-bots"><thead><tr><th>Avatar</th><th>Nom</th><th>Niveau affiché</th><th>Présentation</th></tr></thead><tbody>
			<?php foreach ( $o['bots'] as $id => $b ) : ?>
				<tr>
					<td><?php brev_media_field( 'brev_gac[bots][' . $id . '][av]', (int) $b['av'], $b['emo'], 'round' ); ?></td>
					<td><input class="regular-text" name="brev_gac[bots][<?php echo esc_attr( $id ); ?>][name]" value="<?php echo esc_attr( $b['name'] ); ?>" aria-label="Nom"></td>
					<td><input class="regular-text" name="brev_gac[bots][<?php echo esc_attr( $id ); ?>][tag]" value="<?php echo esc_attr( $b['tag'] ); ?>" aria-label="Niveau"></td>
					<td><input class="large-text" name="brev_gac[bots][<?php echo esc_attr( $id ); ?>][desc]" value="<?php echo esc_attr( $b['desc'] ); ?>" aria-label="Présentation"></td>
				</tr>
			<?php endforeach; ?>
			</tbody></table>

			<h2>Design des cartes</h2>
			<p class="description">Une image par type de carte (cadrée automatiquement au format carte, 5 × 7). Sans image, la carte d’origine est utilisée.</p>
			<div class="brev-cards">
			<?php foreach ( brev_gac_cards() as $k => $c ) : ?>
				<div class="brev-card"><b><?php echo esc_html( $c[0] ); ?></b><?php brev_media_field( 'brev_gac[cards][' . $k . ']', isset( $o['cards'][ $k ] ) ? (int) $o['cards'][ $k ] : 0, $c[1], 'cardshape' ); ?></div>
			<?php endforeach; ?>
			</div>
			<?php submit_button( 'Enregistrer les réglages du jeu' ); ?>
		</form>
	</div>
	<?php
}

/* ----- Affichage public du jeu ----- */

add_action( 'init', 'brev_game_rewrite' );
/**
 * Adresse /jouer/gare-au-chacal/.
 */
function brev_game_rewrite() {
	add_rewrite_rule( '^jouer/([a-z0-9-]+)/?$', 'index.php?brev_game=$matches[1]', 'top' );
}

add_filter(
	'query_vars',
	function ( $v ) {
		$v[] = 'brev_game';
		return $v;
	}
);

/**
 * Adresse publique d'un jeu en ligne.
 *
 * @param string $id Identifiant (gac).
 * @return string
 */
function brev_game_url( $id ) {
	$g = brev_nav_games();
	if ( ! isset( $g[ $id ] ) ) {
		return '';
	}
	return get_option( 'permalink_structure' ) ? home_url( '/jouer/' . $g[ $id ]['slug'] . '/' ) : add_query_arg( 'brev_game', $g[ $id ]['slug'], home_url( '/' ) );
}

add_action( 'template_redirect', 'brev_game_output' );
/**
 * Affiche le jeu avec les réglages de l'administration.
 */
function brev_game_output() {
	$slug = get_query_var( 'brev_game' );
	if ( ! $slug ) {
		return;
	}
	$game = null;
	foreach ( brev_nav_games() as $id => $g ) {
		if ( $g['slug'] === $slug ) {
			$game = $g;
		}
	}
	if ( ! $game || ! is_readable( $game['file'] ) ) {
		return;
	}
	$o   = brev_gac();
	$img = function ( $id, $size ) {
		$u = $id ? wp_get_attachment_image_url( (int) $id, $size ) : '';
		return $u ? $u : '';
	};
	$cfg = array(
		'title'   => $game['title'],
		'locked'  => true,
		'shopUrl' => $o['shop_url'],
		'winMsg'  => $o['win_msg'],
		'winBtn'  => $o['win_btn'],
		'loseMsg' => $o['lose_msg'],
		'loseBtn' => $o['lose_btn'],
		'table'   => $o['table'],
		'speed'   => (int) $o['speed'],
		'bots'    => array(),
		'av'      => array(),
		'cards'   => array(),
	);
	foreach ( $o['bots'] as $id => $b ) {
		$cfg['bots'][] = array( 'id' => $id, 'name' => $b['name'], 'tag' => $b['tag'], 'desc' => $b['desc'] );
		if ( $img( $b['av'], 'thumbnail' ) ) {
			$cfg['av'][ $id ] = $img( $b['av'], 'thumbnail' );
		}
	}
	foreach ( $o['cards'] as $k => $id ) {
		if ( $img( $id, 'medium_large' ) ) {
			$cfg['cards'][ $k ] = $img( $id, 'medium_large' );
		}
	}
	$back = get_post_type_archive_link( 'brev_jeu' );
	$html = file_get_contents( $game['file'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$html = str_replace(
		'<script src="../../assets/data.js"></script>',
		'<script>window.BREV_GAME_CFG=' . wp_json_encode( $cfg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';</script>',
		$html
	);
	$html = str_replace( '../../index.html#jeux', esc_url( $back ? $back : home_url( '/' ) ), $html );
	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
