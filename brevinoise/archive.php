<?php
/**
 * Pages Festivals, Jeux, Escape Box & accessoires, BD, Adhérents (et archives classiques).
 *
 * @package Brevinoise
 */

get_header();
$pt = get_query_var( 'post_type' );
$k  = is_string( $pt ) && $pt ? brev_key_of( $pt ) : null;

if ( ! $k ) {
	brev_pagehead( wp_strip_all_tags( get_the_archive_title() ), wp_strip_all_tags( get_the_archive_description() ) );
	echo '<div class="band"><div class="wrap prose">';
	while ( have_posts() ) {
		the_post();
		echo '<article class="entry"><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>' . wp_kses_post( get_the_excerpt() ) . '</article>';
	}
	the_posts_pagination();
	echo '</div></div>';
	get_footer();
	return;
}

brev_pagehead( brev_mod( 'title_' . $k ), brev_mod( 'intro_' . $k ) );
global $wp_query;
$posts = $wp_query->posts;
?>
<div class="band"><div class="wrap">
	<?php if ( in_array( $k, array( 'jeu', 'bd', 'escape' ), true ) ) : ?>
		<div class="tools">
			<input id="q" type="search" placeholder="Rechercher…" aria-label="Rechercher">
			<?php if ( 'escape' === $k ) : ?>
				<?php $kinds = array_unique( array_filter( array_map( function ( $p ) { return get_post_meta( $p->ID, '_brev_kind', true ); }, $posts ) ) ); ?>
				<div class="chips" id="kinds"><button class="chip on" data-k="">Tout</button><?php foreach ( $kinds as $kind ) : ?><button class="chip" data-k="<?php echo esc_attr( $kind ); ?>"><?php echo esc_html( $kind ); ?></button><?php endforeach; ?></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr( 'festival' === $k ? 'fgrid' : ( 'adherent' === $k ? 'mgrid' : 'grid' ) ); ?>" id="list">
		<?php
		if ( ! $posts ) {
			echo '<p class="empty">Rien pour le moment.</p>';
		}
		foreach ( $posts as $n => $p ) {
			if ( 'festival' === $k ) {
				brev_fest_card( $p );
			} elseif ( 'adherent' === $k ) {
				brev_member_card( $p );
			} else {
				brev_card( $p, $n < ( 'bd' === $k ? 1 : 3 ) );
			}
		}
		?>
		<p class="empty" id="noresult" hidden>Aucun résultat pour cette recherche.</p>
	</div>
</div></div>
<?php
get_footer();
