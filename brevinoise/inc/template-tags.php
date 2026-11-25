<?php
/**
 * Morceaux d'affichage réutilisés par les modèles.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fiches d'un type, dans l'ordre d'affichage.
 *
 * @param string $k Clé du type.
 * @param int    $n Nombre (-1 = toutes).
 * @return WP_Post[]
 */
function brev_query( $k, $n = -1 ) {
	return get_posts(
		array(
			'post_type'   => brev_types()[ $k ]['pt'],
			'numberposts' => $n,
			'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);
}

/**
 * Description en texte simple.
 *
 * @param WP_Post $p Fiche.
 * @return string
 */
function brev_desc( $p ) {
	return trim( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ) );
}

/**
 * Lien d'image à la une.
 *
 * @param WP_Post $p    Fiche.
 * @param string  $size Taille.
 * @return string
 */
function brev_img( $p, $size = 'large' ) {
	$u = get_the_post_thumbnail_url( $p, $size );
	return $u ? $u : '';
}

/**
 * Adresse du bouton « Tester le jeu ».
 *
 * @param WP_Post $p Fiche.
 * @return string
 */
function brev_play_url( $p ) {
	$u = get_post_meta( $p->ID, '_brev_play_url', true );
	if ( $u ) {
		return $u;
	}
	$g = get_post_meta( $p->ID, '_brev_play', true );
	return $g ? brev_game_url( $g ) : '';
}

/**
 * Initiales d'un nom.
 *
 * @param string $name Nom.
 * @return string
 */
function brev_initials( $name ) {
	$out = '';
	foreach ( preg_split( '/\s+/u', (string) $name ) as $w ) {
		if ( preg_match( '/^\p{L}/u', $w ) ) {
			$out .= mb_strtoupper( mb_substr( $w, 0, 1 ) );
		}
		if ( mb_strlen( $out ) >= 2 ) {
			break;
		}
	}
	return $out ? $out : '?';
}

/**
 * Étiquettes d'une fiche.
 *
 * @param WP_Post $p Fiche.
 * @return string[]
 */
function brev_tags( $p ) {
	return array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $p->ID, '_brev_tags', true ) ) ) );
}

/**
 * Carte d'un jeu, d'une BD ou d'un accessoire.
 *
 * @param WP_Post $p      Fiche.
 * @param bool    $is_new Badge « Nouveau ».
 */
function brev_card( $p, $is_new = false ) {
	$link  = get_post_meta( $p->ID, '_brev_link', true );
	$kind  = get_post_meta( $p->ID, '_brev_kind', true );
	$play  = brev_play_url( $p );
	$video = get_post_meta( $p->ID, '_brev_video', true );
	$img   = brev_img( $p );
	$title = get_the_title( $p );
	$badge = ( $is_new ? '<span class="new">Nouveau</span>' : '' ) . ( $kind ? '<span class="kind">' . esc_html( $kind ) . '</span>' : '' );
	$pic   = $img
		? '<div class="cv"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" loading="lazy">' . $badge . '</div>'
		: '<div class="cv"><span class="soon">' . esc_html( $title ) . '<br><small>Visuel à venir</small></span>' . $badge . '</div>';
	$pic = '<a href="' . esc_url( get_permalink( $p ) ) . '" tabindex="-1" aria-hidden="true">' . $pic . '</a>';
	$search = mb_strtolower( $title . ' ' . brev_desc( $p ) . ' ' . implode( ' ', brev_tags( $p ) ) );
	echo '<article class="card" data-search="' . esc_attr( $search ) . '" data-kind="' . esc_attr( $kind ) . '">' . $pic . '<div class="body">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<h3><a class="ctitle" href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $title ) . '</a></h3><p>' . esc_html( wp_trim_words( brev_desc( $p ), 45 ) ) . '</p><div class="tags">';
	foreach ( brev_tags( $p ) as $t ) {
		echo '<span class="tag">' . esc_html( $t ) . '</span>';
	}
	echo '</div>';
	if ( $link || $play || $video ) {
		echo '<div class="cbtns">';
		if ( $link ) {
			echo '<a class="btn sm shop" href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( brev_mod( 'item_btn' ) ) . ' →</a>';
		}
		if ( $play ) {
			echo '<a class="btn sm play" href="' . esc_url( $play ) . '">▶ ' . esc_html( brev_mod( 'play_btn' ) ) . '</a>';
		}
		if ( $video ) {
			echo '<a class="btn sm video" href="' . esc_url( get_permalink( $p ) . '#video' ) . '">🎬 ' . esc_html( brev_mod( 'video_btn' ) ) . '</a>';
		}
		echo '</div>';
	}
	echo '</div></article>';
}

/**
 * Noms des jeux associés à une fiche.
 *
 * @param WP_Post $p Fiche.
 * @return string[]
 */
function brev_linked_names( $p ) {
	$out = array();
	foreach ( (array) get_post_meta( $p->ID, '_brev_games', true ) as $id ) {
		if ( $id && 'publish' === get_post_status( (int) $id ) ) {
			$out[] = get_the_title( (int) $id );
		}
	}
	return $out;
}

/**
 * Contenu de la fenêtre d'infos (affiche ou adhérent).
 *
 * @param WP_Post $p Fiche.
 * @param string  $k Type.
 */
function brev_pop_template( $p, $k ) {
	$names = brev_linked_names( $p );
	$img   = brev_img( $p );
	$list  = $names ? '<p class="plbl">' . ( 'festival' === $k ? 'Jeux associés' : 'Jeux et projets' ) . '</p><ul class="pgames"><li>' . implode( '</li><li>', array_map( 'esc_html', $names ) ) . '</li></ul>' : '';
	$desc  = wpautop( esc_html( brev_desc( $p ) ) );
	echo '<template id="pop-' . (int) $p->ID . '">';
	if ( 'festival' === $k ) {
		$link = get_post_meta( $p->ID, '_brev_link', true );
		if ( $img ) {
			echo '<img class="pimg" src="' . esc_url( $img ) . '" alt="">';
		}
		echo '<h3>' . esc_html( get_the_title( $p ) ) . '</h3><p class="pmeta"><b>' . esc_html( get_post_meta( $p->ID, '_brev_date', true ) ) . '</b><br>' . esc_html( get_post_meta( $p->ID, '_brev_place', true ) ) . '</p>';
		echo $desc . $list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $link ) {
			echo '<a class="btn sm" href="' . esc_url( $link ) . '" target="_blank" rel="noopener">En savoir plus →</a>';
		}
	} else {
		$face = $img ? '<img src="' . esc_url( brev_img( $p, 'medium' ) ) . '" alt="">' : '<span class="avatar">' . esc_html( brev_initials( get_the_title( $p ) ) ) . '</span>';
		echo '<div class="phead">' . $face . '<div><h3>' . esc_html( get_the_title( $p ) ) . '</h3><p class="pmeta"><b>' . esc_html( get_post_meta( $p->ID, '_brev_role', true ) ) . '</b></p></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $desc . $list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</template>';
}

/**
 * Affiche de festival.
 *
 * @param WP_Post $p Fiche.
 */
function brev_fest_card( $p ) {
	$img   = brev_img( $p );
	$title = get_the_title( $p );
	$date  = (string) get_post_meta( $p->ID, '_brev_date', true );
	echo '<div><article class="fest" tabindex="0" data-pop="' . (int) $p->ID . '" aria-haspopup="dialog" aria-label="' . esc_attr( $title . ' : afficher les détails' ) . '">';
	echo $img ? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( 'Affiche : ' . $title ) . '" loading="lazy">' : '<div class="avatar fph">' . esc_html( $title ) . '</div>';
	echo '<span class="peek">Infos</span></article>';
	brev_pop_template( $p, 'festival' );
	$short = explode( ',', $date );
	echo '<div class="fcap"><span>' . esc_html( $title ) . '</span><span>' . esc_html( $short[0] ) . '</span></div></div>';
}

/**
 * Portrait d'un adhérent.
 *
 * @param WP_Post $p Fiche.
 */
function brev_member_card( $p ) {
	$img   = brev_img( $p, 'medium_large' );
	$title = get_the_title( $p );
	echo '<div><article class="member" tabindex="0" data-pop="' . (int) $p->ID . '" aria-haspopup="dialog" aria-label="' . esc_attr( $title . ' : afficher la fiche' ) . '">';
	echo $img ? '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" loading="lazy">' : '<div class="avatar">' . esc_html( brev_initials( $title ) ) . '</div>';
	echo '</article>';
	brev_pop_template( $p, 'adherent' );
	echo '<div class="mcap"><b>' . esc_html( $title ) . '</b><span>' . esc_html( get_post_meta( $p->ID, '_brev_role', true ) ) . '</span></div></div>';
}

/**
 * Bandeau de titre d'une page.
 *
 * @param string $title Titre.
 * @param string $intro Introduction.
 */
function brev_pagehead( $title, $intro = '' ) {
	echo '<div class="band pagehead"><div class="wrap"><h1>' . esc_html( $title ) . '</h1>';
	if ( $intro ) {
		echo '<p>' . esc_html( $intro ) . '</p>';
	}
	echo '</div></div>';
}

/**
 * Texte sur plusieurs lignes.
 *
 * @param string $t Texte.
 * @return string
 */
function brev_lines( $t ) {
	return nl2br( esc_html( (string) $t ) );
}

/**
 * Images de la galerie d'une fiche (identifiants).
 *
 * @param WP_Post|int $p Fiche.
 * @return int[]
 */
function brev_gallery_ids( $p ) {
	$id  = is_object( $p ) ? $p->ID : (int) $p;
	$ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $id, '_brev_gallery', true ) ) ) );
	return array_values(
		array_filter(
			$ids,
			function ( $i ) {
				return wp_attachment_is_image( $i );
			}
		)
	);
}

/**
 * Lecteur vidéo d'une adresse (YouTube, Vimeo, fichier vidéo ou autre adresse prise en charge par WordPress).
 *
 * @param string $url   Adresse de la vidéo.
 * @param string $title Titre (accessibilité).
 * @return string HTML, vide si l'adresse n'est pas lisible sur la page.
 */
function brev_video_html( $url, $title = '' ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	$src = '';
	if ( preg_match( '~(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $m ) ) {
		$src = 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0';
	} elseif ( preg_match( '~vimeo\.com/(?:video/)?(\d+)(?:/([a-f0-9]+))?~i', $url, $m ) ) {
		$src = 'https://player.vimeo.com/video/' . $m[1] . ( ! empty( $m[2] ) ? '?h=' . $m[2] : '' );
	}
	if ( $src ) {
		return '<div class="vframe"><iframe data-src="' . esc_url( $src ) . '" title="' . esc_attr( 'Vidéo : ' . $title ) . '" loading="lazy" allow="accelerometer; encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
	}
	if ( preg_match( '~\.(mp4|webm|ogv|m4v)(\?.*)?$~i', $url ) ) {
		return '<div class="vframe"><video controls preload="none" src="' . esc_url( $url ) . '"></video></div>';
	}
	$html = wp_oembed_get( $url, array( 'width' => 1000 ) );
	return $html ? '<div class="vframe">' . $html . '</div>' : '';
}

add_filter( 'single_template', 'brev_single_fiche_template' );
/**
 * Page de présentation pour les jeux, BD, escape box et accessoires.
 *
 * @param string $tpl Modèle prévu par WordPress.
 * @return string
 */
function brev_single_fiche_template( $tpl ) {
	$k = brev_key_of( get_post_type() );
	if ( $k && in_array( $k, array( 'jeu', 'escape', 'bd' ), true ) && file_exists( BREV_DIR . '/single-fiche.php' ) ) {
		return BREV_DIR . '/single-fiche.php';
	}
	return $tpl;
}
