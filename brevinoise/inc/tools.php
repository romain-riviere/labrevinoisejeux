<?php
/**
 * Apparence › La Brévinoise : contenu de démonstration, sauvegarde des réglages et mises à jour.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'brev_tools_menu' );
/**
 * Page « La Brévinoise » sous Apparence.
 */
function brev_tools_menu() {
	add_theme_page( 'La Brévinoise : démo et mises à jour', 'La Brévinoise : outils', 'manage_options', 'brev-tools', 'brev_tools_page' );
}

add_action( 'after_switch_theme', 'brev_after_switch' );
/**
 * Après activation : adresses à jour et invitation à importer la démo.
 */
function brev_after_switch() {
	flush_rewrite_rules( false );
	update_option( 'brev_rewrite_version', '' );
}

add_action( 'admin_notices', 'brev_demo_notice' );
/**
 * Invitation à importer le contenu de démonstration.
 */
function brev_demo_notice() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'brev_demo_done' ) || ( isset( $_GET['page'] ) && 'brev-tools' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	echo '<div class="notice notice-info"><p><b>Thème La Brévinoise activé.</b> Pour démarrer avec les jeux, BD, affiches et adhérents de démonstration, <a href="' . esc_url( admin_url( 'themes.php?page=brev-tools' ) ) . '">importez le contenu de démonstration</a>.</p></div>';
}

/**
 * Page des outils.
 */
function brev_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$done = get_option( 'brev_demo_done' );
	$msg  = isset( $_GET['brev_msg'] ) ? sanitize_key( $_GET['brev_msg'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$msgs = array(
		'demo'      => 'Contenu de démonstration importé : jeux, BD, Escape Box, affiches, adhérents, page Contact et menu.',
		'imported'  => 'Réglages importés.',
		'badfile'   => 'Fichier illisible : choisissez un fichier exporté depuis cette page.',
		'urlsaved'  => 'Adresse de mise à jour enregistrée.',
	);
	?>
	<div class="wrap">
		<h1>La Brévinoise : démo et mises à jour</h1>
		<?php if ( isset( $msgs[ $msg ] ) ) : ?>
			<div class="notice <?php echo 'badfile' === $msg ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $msgs[ $msg ] ); ?></p></div>
		<?php endif; ?>

		<div class="card brev-tool">
			<h2>Où modifier quoi ?</h2>
			<ul class="ul-disc">
				<li><b>Festivals, Jeux, Escape Box & accessoires, BD, Adhérents</b> : menus du même nom à gauche (ajouter, modifier, image, lien, ordre).</li>
				<li><b>Couleurs, taille du logo, lien de la boutique, boutons, textes</b> : <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=brev' ) ); ?>">Apparence › Personnaliser › La Brévinoise</a>. Le logo : Personnaliser › Identité du site.</li>
				<li><b>Onglets du menu</b> : <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">Apparence › Menus</a>.</li>
				<li><b>Jeu en ligne (bots, cartes, table, boutique de fin de partie)</b> : <a href="<?php echo esc_url( admin_url( 'admin.php?page=brev-gac' ) ); ?>">Gare au Chacal</a>.</li>
			</ul>
		</div>

		<div class="card brev-tool">
			<h2>Contenu de démonstration</h2>
			<p>Crée les jeux, BD, Escape Box, affiches des festivals, adhérents, la page Contact et le menu, avec leurs images. Vos contenus existants ne sont pas supprimés.</p>
			<?php if ( $done ) : ?><p><b>Déjà importé le <?php echo esc_html( wp_date( 'j F Y à H:i', (int) $done ) ); ?>.</b> Un nouvel import ajoute une deuxième copie.</p><?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="brev_import_demo"><?php wp_nonce_field( 'brev_import_demo' ); ?>
				<?php submit_button( $done ? 'Importer à nouveau' : 'Importer le contenu de démonstration', $done ? 'secondary' : 'primary', 'submit', false ); ?>
			</form>
		</div>

		<div class="card brev-tool">
			<h2>Mises à jour du thème</h2>
			<p>Version installée : <b><?php echo esc_html( BREV_VERSION ); ?></b>.</p>
			<p>Vos contenus (fiches, images) et vos réglages (Personnaliser, menu, jeu) sont enregistrés dans WordPress, pas dans le thème. Une mise à jour ne les touche pas.</p>
			<p><b>Pour installer une nouvelle version :</b> Apparence › Thèmes › Ajouter › Téléverser un thème, choisissez le nouveau fichier <code>brevinoise.zip</code>, puis cliquez sur « Remplacer l’actuel par la version téléversée ».</p>
			<p><b>Mises à jour automatiques (facultatif) :</b> si vous publiez un petit fichier <code>.json</code> décrivant la dernière version, WordPress proposera la mise à jour dans Tableau de bord › Mises à jour, comme pour n’importe quel thème. Format : <code>{"version":"1.3.0","package":"https://…/brevinoise.zip"}</code></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="brev_update_url"><?php wp_nonce_field( 'brev_update_url' ); ?>
				<p><label for="brev_uurl">Adresse du fichier de mise à jour</label><br><input class="large-text" type="url" id="brev_uurl" name="brev_update_url" value="<?php echo esc_attr( get_option( 'brev_update_url', '' ) ); ?>" placeholder="https://…/brevinoise-update.json"></p>
				<?php submit_button( 'Enregistrer', 'secondary', 'submit', false ); ?>
			</form>
		</div>

		<div class="card brev-tool">
			<h2>Sauvegarder ou transférer les réglages</h2>
			<p>Exporte les réglages de Personnaliser et du jeu (textes, couleurs, liens) dans un fichier, pour les sauvegarder ou les copier sur un autre site. Les fiches et images se transfèrent avec Outils › Exporter de WordPress.</p>
			<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=brev_export' ), 'brev_export' ) ); ?>">Exporter les réglages (.json)</a></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="brev_import_settings"><?php wp_nonce_field( 'brev_import_settings' ); ?>
				<p><label for="brev_file">Importer un fichier de réglages</label><br><input type="file" id="brev_file" name="brev_file" accept=".json,application/json"></p>
				<?php submit_button( 'Importer', 'secondary', 'submit', false ); ?>
			</form>
		</div>
	</div>
	<?php
}

/**
 * Retour vers la page des outils avec un message.
 *
 * @param string $m Message.
 */
function brev_tools_back( $m ) {
	wp_safe_redirect( admin_url( 'themes.php?page=brev-tools&brev_msg=' . $m ) );
	exit;
}

add_action( 'admin_post_brev_update_url', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'brev_update_url' ) ) {
		wp_die( 'Accès refusé.' );
	}
	update_option( 'brev_update_url', esc_url_raw( isset( $_POST['brev_update_url'] ) ? wp_unslash( $_POST['brev_update_url'] ) : '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	delete_site_transient( 'update_themes' );
	brev_tools_back( 'urlsaved' );
} );

add_action( 'admin_post_brev_export', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'brev_export' ) ) {
		wp_die( 'Accès refusé.' );
	}
	$mods = array();
	foreach ( array_keys( brev_defaults() ) as $k ) {
		$v = get_theme_mod( 'brev_' . $k, null );
		if ( null !== $v ) {
			$mods[ $k ] = $v;
		}
	}
	$data = array( 'theme' => 'brevinoise', 'version' => BREV_VERSION, 'mods' => $mods, 'game' => get_option( 'brev_gac', array() ) );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=brevinoise-reglages-' . gmdate( 'Y-m-d' ) . '.json' );
	echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
} );

add_action( 'admin_post_brev_import_settings', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'brev_import_settings' ) ) {
		wp_die( 'Accès refusé.' );
	}
	if ( empty( $_FILES['brev_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['brev_file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		brev_tools_back( 'badfile' );
	}
	$data = json_decode( (string) file_get_contents( $_FILES['brev_file']['tmp_name'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.WP.AlternativeFunctions
	if ( ! is_array( $data ) || ( $data['theme'] ?? '' ) !== 'brevinoise' ) {
		brev_tools_back( 'badfile' );
	}
	$d = brev_defaults();
	foreach ( (array) ( $data['mods'] ?? array() ) as $k => $v ) {
		if ( ! array_key_exists( $k, $d ) || is_array( $v ) ) {
			continue;
		}
		if ( 0 === strpos( $k, 'color_' ) ) {
			$v = sanitize_hex_color( $v );
		} elseif ( in_array( $k, array( 'shop_url', 'hero_image' ), true ) ) {
			$v = esc_url_raw( $v );
		} elseif ( 'logo_size' === $k ) {
			$v = absint( $v );
		} elseif ( 'show_name' === $k ) {
			$v = (bool) $v;
		} else {
			$v = sanitize_textarea_field( $v );
		}
		set_theme_mod( 'brev_' . $k, $v );
	}
	if ( ! empty( $data['game'] ) && is_array( $data['game'] ) ) {
		update_option( 'brev_gac', brev_gac_sanitize( $data['game'] ) );
	}
	brev_tools_back( 'imported' );
} );

add_filter( 'pre_set_site_transient_update_themes', 'brev_check_update' );
/**
 * Propose une nouvelle version si le fichier de mise à jour l'annonce.
 *
 * @param object $t Données de mise à jour.
 * @return object
 */
function brev_check_update( $t ) {
	$url = get_option( 'brev_update_url' );
	if ( ! $url || ! is_object( $t ) ) {
		return $t;
	}
	$r = wp_remote_get( $url, array( 'timeout' => 8 ) );
	if ( is_wp_error( $r ) || 200 !== wp_remote_retrieve_response_code( $r ) ) {
		return $t;
	}
	$j = json_decode( wp_remote_retrieve_body( $r ), true );
	if ( ! empty( $j['version'] ) && ! empty( $j['package'] ) && version_compare( $j['version'], BREV_VERSION, '>' ) ) {
		$t->response[ get_template() ] = array(
			'theme'       => get_template(),
			'new_version' => sanitize_text_field( $j['version'] ),
			'url'         => esc_url_raw( $j['url'] ?? $url ),
			'package'     => esc_url_raw( $j['package'] ),
		);
	}
	return $t;
}

/* ----- Contenu de démonstration ----- */

add_action( 'admin_post_brev_import_demo', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'brev_import_demo' ) ) {
		wp_die( 'Accès refusé.' );
	}
	brev_import_demo();
	brev_tools_back( 'demo' );
} );

/**
 * Copie une image de démonstration dans la médiathèque.
 *
 * @param string $file Fichier dans demo/img.
 * @param string $title Titre.
 * @return int
 */
function brev_demo_image( $file, $title ) {
	$path = BREV_DIR . '/demo/img/' . $file;
	if ( ! $file || ! is_readable( $path ) ) {
		return 0;
	}
	$up = wp_upload_bits( $file, null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$type = wp_check_filetype( $up['file'] );
	$id   = wp_insert_attachment( array( 'post_title' => $title, 'post_mime_type' => $type['type'] ? $type['type'] : 'image/webp', 'post_status' => 'inherit' ), $up['file'] );
	if ( $id && ! is_wp_error( $id ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
		return (int) $id;
	}
	return 0;
}

/**
 * Crée une fiche de démonstration.
 *
 * @param string $k     Type.
 * @param array  $a     Données.
 * @param int    $order Ordre.
 * @return int
 */
function brev_demo_post( $k, $a, $order ) {
	$id = wp_insert_post(
		array(
			'post_type'    => brev_types()[ $k ]['pt'],
			'post_status'  => 'publish',
			'post_title'   => $a['title'],
			'post_content' => $a['desc'],
			'menu_order'   => $order,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( $a as $f => $v ) {
		if ( ! in_array( $f, array( 'title', 'desc', 'img' ), true ) ) {
			update_post_meta( $id, '_brev_' . $f, $v );
		}
	}
	if ( ! empty( $a['img'] ) ) {
		$att = brev_demo_image( $a['img'], $a['title'] );
		if ( $att ) {
			set_post_thumbnail( $id, $att );
		}
	}
	return (int) $id;
}

/**
 * Importe tout le contenu de démonstration.
 */
function brev_import_demo() {
	$jeux = array(
		'chacal' => array( 'title' => 'Gare au Chacal', 'desc' => 'Monte les plus beaux élevages du village… et garde un œil sur le chacal ! Notre petit dernier, à tester directement en ligne.', 'tags' => 'Nouveauté, Jouable en ligne', 'link' => '', 'play' => 'gac' ),
		'surf'   => array( 'title' => 'Surf and Cats', 'img' => 'surf.webp', 'desc' => "Qui a dit que les chats n'aimaient pas l'eau ? Un stop ou encore à objectifs avec des chats trop cools qui surfent une déferlante d'enfer.", 'tags' => '2–5 joueurs, 8 ans +, 30 min, Stop ou encore', 'link' => 'https://www.labrevinoise.fr/surf-and-cats/' ),
		'uhe25'  => array( 'title' => 'Une Histoire Extraordinaire 2025', 'img' => 'uhe25.webp', 'desc' => "Un jeu coopératif hilarant : racontez ensemble l'histoire la plus longue possible avec vos cartes mots, en vous aidant de mimes.", 'tags' => '1–8 joueurs, 7 ans +, 30 min, Coopératif', 'link' => 'https://www.labrevinoise.fr/une-histoire-extraordinaire-2025/' ),
		'uhe22'  => array( 'title' => 'Une Histoire Extraordinaire 2022', 'img' => 'uhe22.webp', 'desc' => "La première édition du jeu de mémoire, d'observation et de coopération qui aide aussi les plus jeunes à la lecture.", 'tags' => 'Famille, Coopératif, 1ʳᵉ édition', 'link' => 'https://www.labrevinoise.fr/jeux-de-societe-une-histoire-extraordinaire/' ),
	);
	$ids = array();
	$i   = 0;
	foreach ( $jeux as $key => $a ) {
		$ids[ $key ] = brev_demo_post( 'jeu', $a, $i++ );
	}
	$ids['escape'] = brev_demo_post( 'escape', array( 'title' => 'Escape Box LaboTV', 'img' => 'escape.webp', 'desc' => "Une escape box dans l'univers de la BD LaboTV, animée lors de nos festivals : cadenas, énigmes et professeur mégalo.", 'kind' => 'Escape Box', 'tags' => 'Escape game, En animation', 'link' => '' ), 0 );
	brev_demo_post( 'escape', array( 'title' => 'Accessoire à venir', 'desc' => 'Emplacement réservé pour un futur accessoire (tapis de jeu, sacoche, cartes bonus…). À remplacer depuis l’administration.', 'kind' => 'Accessoire', 'tags' => 'Bientôt', 'link' => '' ), 1 );
	brev_demo_post( 'bd', array( 'title' => 'Pépino, tome 2 : Tranches de vie', 'img' => 'pepino.webp', 'desc' => "Pépino, jeune pirate fraîchement diplômé, se retrouve au chômage. Découvrez des tranches de sa vie, racontées par un collectif d'auteurs.", 'tags' => 'Publication collective, Septembre 2026', 'link' => 'https://www.labrevinoise.fr/pepino-tome-2-tranches-de-vie/' ), 0 );
	$ids['labotv'] = brev_demo_post( 'bd', array( 'title' => 'LaboTV', 'img' => 'labotv.webp', 'desc' => 'Une émission de vulgarisation scientifique avec un professeur super-mégalo et un assistant maladroit. Un album humoristique avec cahier de jeux.', 'tags' => 'Tipim, 72 pages, Décembre 2024', 'link' => 'https://www.labrevinoise.fr/labo-tv/' ), 1 );

	$all  = array( $ids['uhe25'], $ids['chacal'], $ids['surf'], $ids['escape'] );
	$fest = array(
		array( 'title' => 'Ambazac terre de jeux (87)', 'img' => 'ambazac.webp', 'date' => 'Sam. 17 et dim. 18 octobre 2026, 10 h – 18 h', 'place' => 'Rue Anna Beillot, Ambazac', 'desc' => "Présentation et animation de nos jeux au festival des jeux d'Ambazac, avec l'escape box LaboTV.", 'games' => $all, 'link' => 'https://www.labrevinoise.fr/ambazac-terre-de-jeux-87-2/' ),
		array( 'title' => 'Fête du Jeu de Ruffiac (56)', 'img' => 'ruffiac.webp', 'date' => 'Dim. 20 septembre 2026, 10 h – 18 h', 'place' => 'Salle polyvalente, Ruffiac', 'desc' => '10ᵉ édition de la fête du jeu : escape box, animations et dédicaces.', 'games' => $all, 'link' => 'https://www.labrevinoise.fr/fete-du-jeu-de-ruffiac-56/' ),
		array( 'title' => 'Forum des assos de Saint-Mars-de-Coutais (44)', 'img' => 'forum.webp', 'date' => 'Sam. 5 septembre 2026, 10 h – 13 h', 'place' => 'Saint-Mars-de-Coutais', 'desc' => 'Présentation et animation de nos jeux au forum des associations.', 'games' => $all, 'link' => '' ),
		array( 'title' => "Tournoi d'impro du Pays de Retz (44)", 'img' => 'impro.webp', 'date' => 'Sam. 29 août 2026', 'place' => 'Théâtre de Saint-Mars-de-Coutais', 'desc' => "Animation de nos jeux pendant le tournoi d'improvisation du Pays de Retz.", 'games' => array( $ids['uhe25'], $ids['surf'] ), 'link' => '' ),
		array( 'title' => 'Parc de la Guérivière (44)', 'img' => 'gueriviere.webp', 'date' => 'Ven. 7 août 2026, 14 h – 17 h', 'place' => 'Villeneuve-en-Retz', 'desc' => 'Animation et dédicaces au parc de la Guérivière.', 'games' => array( $ids['uhe25'], $ids['chacal'], $ids['surf'] ), 'link' => '' ),
	);
	foreach ( $fest as $n => $a ) {
		brev_demo_post( 'festival', $a, $n );
	}
	$mem = array(
		array( 'title' => 'Yvonnick Cheraud', 'role' => "Président de l'association La Brévinoise", 'desc' => "Préside l'association culturelle La Brévinoise à Saint-Brévin-les-Pins.", 'games' => array() ),
		array( 'title' => 'Damien Marquis (Tipim)', 'role' => 'Chef de projets La Brévinoise Jeux, auteur et illustrateur', 'desc' => "Dirige Tipim Studio, qui développe et illustre les jeux de l'association. Auteur de jeux et de bandes dessinées.", 'games' => array( $ids['uhe25'], $ids['uhe22'], $ids['surf'], $ids['labotv'] ) ),
		array( 'title' => 'Lÿa', 'role' => 'Illustratrice', 'desc' => 'Co-illustratrice de Surf and Cats avec Tipim.', 'games' => array( $ids['surf'] ) ),
		array( 'title' => 'Prénom Nom', 'role' => 'Trésorier·ère (exemple)', 'desc' => 'Fiche exemple : remplacez-la depuis Adhérents.', 'games' => array() ),
		array( 'title' => 'Prénom Nom', 'role' => 'Animation des festivals (exemple)', 'desc' => 'Fiche exemple : remplacez-la depuis Adhérents.', 'games' => array( $ids['escape'], $ids['surf'] ) ),
	);
	foreach ( $mem as $n => $a ) {
		brev_demo_post( 'adherent', $a, $n );
	}

	if ( ! brev_contact_page_id() ) {
		$cid = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Contact', 'post_name' => 'contact', 'post_content' => '' ) );
		if ( $cid && ! is_wp_error( $cid ) ) {
			update_post_meta( $cid, '_wp_page_template', 'template-contact.php' );
		}
	}
	$hero = brev_demo_image( 'setting.webp', 'Une Histoire Extraordinaire, partie en cours' );
	if ( $hero && ! get_theme_mod( 'brev_hero_image' ) ) {
		set_theme_mod( 'brev_hero_image', wp_get_attachment_image_url( $hero, 'large' ) );
	}
	if ( ! has_custom_logo() ) {
		$logo = brev_demo_image( 'logo.webp', 'Logo La Brévinoise Jeux' );
		if ( $logo ) {
			set_theme_mod( 'custom_logo', $logo );
		}
	}
	$locs = get_nav_menu_locations();
	if ( empty( $locs['primary'] ) ) {
		$menu = wp_create_nav_menu( 'Menu principal' );
		if ( $menu && ! is_wp_error( $menu ) ) {
			flush_rewrite_rules( false );
			$pos   = 1;
			$items = array( array( 'menu-item-title' => 'Accueil', 'menu-item-type' => 'custom', 'menu-item-url' => home_url( '/' ) ) );
			foreach ( array( 'festival', 'jeu', 'escape', 'bd', 'adherent' ) as $k ) {
				$items[] = array( 'menu-item-title' => brev_mod( 'title_' . $k ), 'menu-item-type' => 'post_type_archive', 'menu-item-object' => brev_types()[ $k ]['pt'] );
			}
			$items[] = array( 'menu-item-title' => 'Boutique', 'menu-item-type' => 'custom', 'menu-item-url' => brev_mod( 'shop_url' ), 'menu-item-classes' => 'shop', 'menu-item-target' => '_blank' );
			if ( brev_contact_page_id() ) {
				$items[] = array( 'menu-item-title' => 'Contact', 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => brev_contact_page_id() );
			}
			foreach ( $items as $it ) {
				wp_update_nav_menu_item( $menu, 0, array_merge( $it, array( 'menu-item-status' => 'publish', 'menu-item-position' => $pos++ ) ) );
			}
			$locs['primary'] = $menu;
			set_theme_mod( 'nav_menu_locations', $locs );
		}
	}
	update_option( 'brev_demo_done', time() );
}
