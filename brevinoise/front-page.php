<?php
/**
 * Page d'accueil : présentation, festivals, jeux, BD.
 *
 * @package Brevinoise
 */

get_header();
$hero    = brev_mod( 'hero_image' ) ? brev_mod( 'hero_image' ) : BREV_URI . '/assets/img/setting.webp';
$btn1    = brev_mod( 'hero_btn1_link' ) ? brev_mod( 'hero_btn1_link' ) : get_post_type_archive_link( 'brev_jeu' );
$contact = brev_contact_page_id();
$btn2    = brev_mod( 'hero_btn2_link' ) ? brev_mod( 'hero_btn2_link' ) : ( $contact ? get_permalink( $contact ) : home_url( '/' ) );
$blocks  = array(
	'festival' => array( 'fest', 'fgrid', '' ),
	'jeu'      => array( 'jeux', 'grid', 'alt-bg' ),
	'bd'       => array( 'bd', 'grid', '' ),
);
?>
<div class="band hero"><div class="wrap">
	<div class="art"><img src="<?php echo esc_url( $hero ); ?>" alt=""></div>
	<div>
		<h1><?php echo esc_html( brev_mod( 'hero_title' ) ); ?></h1>
		<p><?php echo esc_html( brev_mod( 'hero_text1' ) ); ?></p>
		<?php if ( brev_mod( 'hero_text2' ) ) : ?><p><?php echo esc_html( brev_mod( 'hero_text2' ) ); ?></p><?php endif; ?>
		<?php if ( brev_mod( 'hero_btn1_label' ) ) : ?><a class="btn" href="<?php echo esc_url( $btn1 ); ?>"><?php echo esc_html( brev_mod( 'hero_btn1_label' ) ); ?></a><?php endif; ?>
		<?php if ( brev_mod( 'hero_btn2_label' ) ) : ?><a class="btn alt" href="<?php echo esc_url( $btn2 ); ?>"><?php echo esc_html( brev_mod( 'hero_btn2_label' ) ); ?></a><?php endif; ?>
	</div>
</div></div>
<?php foreach ( $blocks as $k => $b ) : ?>
	<div class="band <?php echo esc_attr( $b[2] ); ?>"><div class="wrap">
		<div class="head">
			<div><h2><?php echo esc_html( brev_mod( $b[0] . '_title' ) ); ?></h2><p><?php echo esc_html( brev_mod( $b[0] . '_text' ) ); ?></p></div>
			<a class="btn" href="<?php echo esc_url( get_post_type_archive_link( brev_types()[ $k ]['pt'] ) ); ?>"><?php echo esc_html( brev_mod( $b[0] . '_btn' ) ); ?></a>
		</div>
		<div class="<?php echo esc_attr( $b[1] ); ?>">
			<?php
			$items = brev_query( $k, 3 );
			if ( ! $items ) {
				echo '<p class="empty">Rien pour le moment.</p>';
			}
			foreach ( $items as $n => $p ) {
				if ( 'festival' === $k ) {
					brev_fest_card( $p );
				} else {
					brev_card( $p, 'jeu' === $k || 0 === $n );
				}
			}
			?>
		</div>
	</div></div>
<?php endforeach; ?>
<?php
get_footer();
