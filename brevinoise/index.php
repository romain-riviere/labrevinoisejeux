<?php
/**
 * Modèle de secours : articles, pages, recherche, page introuvable.
 *
 * @package Brevinoise
 */

get_header();
if ( is_404() ) {
	brev_pagehead( 'Page introuvable', 'Cette page n’existe pas ou a été déplacée.' );
	echo '<div class="band"><div class="wrap"><a class="btn" href="' . esc_url( home_url( '/' ) ) . '">Retour à l’accueil</a></div></div>';
} elseif ( is_singular() ) {
	the_post();
	brev_pagehead( get_the_title() );
	echo '<div class="band"><div class="wrap prose">';
	if ( has_post_thumbnail() && ! is_page() ) {
		the_post_thumbnail( 'large', array( 'class' => 'single-img' ) );
	}
	$k = brev_key_of( get_post_type() );
	if ( $k ) {
		$p = get_post();
		if ( get_post_meta( $p->ID, '_brev_role', true ) ) {
			echo '<p><b>' . esc_html( get_post_meta( $p->ID, '_brev_role', true ) ) . '</b></p>';
		}
		if ( get_post_meta( $p->ID, '_brev_date', true ) ) {
			echo '<p><b>' . esc_html( get_post_meta( $p->ID, '_brev_date', true ) ) . '</b><br>' . esc_html( get_post_meta( $p->ID, '_brev_place', true ) ) . '</p>';
		}
	}
	the_content();
	if ( $k ) {
		$link = get_post_meta( get_the_ID(), '_brev_link', true );
		$play = brev_play_url( get_post() );
		echo '<p class="cbtns">';
		if ( $link ) {
			echo '<a class="' . ( 'festival' === $k ? 'btn' : 'btn shop' ) . '" href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . esc_html( 'festival' === $k ? 'En savoir plus' : brev_mod( 'item_btn' ) ) . ' →</a>';
		}
		if ( $play ) {
			echo '<a class="btn play" href="' . esc_url( $play ) . '">▶ ' . esc_html( brev_mod( 'play_btn' ) ) . '</a>';
		}
		echo '<a class="btn alt" href="' . esc_url( get_post_type_archive_link( get_post_type() ) ) . '">← ' . esc_html( brev_mod( 'title_' . $k ) ) . '</a></p>';
	}
	echo '</div></div>';
} else {
	brev_pagehead( is_search() ? 'Recherche : ' . get_search_query() : get_bloginfo( 'name' ) );
	echo '<div class="band"><div class="wrap prose">';
	if ( ! have_posts() ) {
		echo '<p>Aucun résultat.</p>';
	}
	while ( have_posts() ) {
		the_post();
		echo '<article class="entry"><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>' . wp_kses_post( get_the_excerpt() ) . '</article>';
	}
	the_posts_pagination();
	echo '</div></div>';
}
get_footer();
