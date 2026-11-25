<?php
/**
 * Page de présentation d'un jeu, d'une bande dessinée, d'une escape box ou d'un accessoire.
 *
 * @package Brevinoise
 */

get_header();
the_post();

$p     = get_post();
$k     = brev_key_of( $p->post_type );
$title = get_the_title( $p );
$link  = get_post_meta( $p->ID, '_brev_link', true );
$kind  = get_post_meta( $p->ID, '_brev_kind', true );
$play  = brev_play_url( $p );
$img   = brev_img( $p );
$tags  = brev_tags( $p );
$gal   = brev_gallery_ids( $p );
$vurl  = get_post_meta( $p->ID, '_brev_video', true );
$vhtml = brev_video_html( $vurl, $title );

brev_pagehead( $title );
?>
<div class="band"><div class="wrap">
	<article class="fiche">
		<div class="fiche-cover">
			<div class="cv">
				<?php if ( $img ) : ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
				<?php else : ?>
					<span class="soon"><?php echo esc_html( $title ); ?><br><small>Visuel à venir</small></span>
				<?php endif; ?>
			</div>
			<?php if ( $tags ) : ?>
				<div class="tags">
					<?php foreach ( $tags as $t ) : ?>
						<span class="tag"><?php echo esc_html( $t ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="cbtns">
				<?php if ( $link ) : ?>
					<a class="btn shop" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener"><?php echo esc_html( brev_mod( 'item_btn' ) ); ?> →</a>
				<?php endif; ?>
				<?php if ( $play ) : ?>
					<a class="btn play" href="<?php echo esc_url( $play ); ?>">▶ <?php echo esc_html( brev_mod( 'play_btn' ) ); ?></a>
				<?php endif; ?>
				<a class="btn alt" href="<?php echo esc_url( get_post_type_archive_link( $p->post_type ) ); ?>">← <?php echo esc_html( brev_mod( 'title_' . $k ) ); ?></a>
			</div>
		</div>
		<div class="fiche-body">
			<?php if ( $kind ) : ?>
				<p class="fiche-kind"><?php echo esc_html( $kind ); ?></p>
			<?php endif; ?>
			<div class="prose fiche-text"><?php the_content(); ?></div>
			<?php if ( $gal ) : ?>
				<div class="fiche-gallery" role="region" aria-label="<?php echo esc_attr( 'Images : ' . $title ); ?>">
					<div class="carousel" data-carousel>
						<button type="button" class="car-btn prev" aria-label="Image précédente">‹</button>
						<ul class="car-track" tabindex="0" aria-label="Carrousel d’images">
							<?php foreach ( $gal as $n => $gid ) : ?>
								<?php $alt = trim( (string) get_post_meta( $gid, '_wp_attachment_image_alt', true ) ); ?>
								<li class="car-slide"><?php echo wp_get_attachment_image( $gid, 'large', false, array( 'alt' => $alt ? $alt : $title . ' : image ' . ( $n + 1 ), 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php endforeach; ?>
						</ul>
						<button type="button" class="car-btn next" aria-label="Image suivante">›</button>
					</div>
					<p class="car-count" aria-live="polite"></p>
				</div>
			<?php endif; ?>
		</div>
	</article>

	<?php
	$tabs = array();
	if ( $vurl ) {
		$tabs['video'] = 'Vidéo';
	}
	if ( $tabs ) :
		$first = key( $tabs );
		?>
		<section class="medias">
			<div class="tablist" role="tablist" aria-label="Vidéo">
				<?php foreach ( $tabs as $id => $label ) : ?>
					<button type="button" role="tab" class="tab<?php echo $id === $first ? ' on' : ''; ?>" id="tab-<?php echo esc_attr( $id ); ?>" aria-controls="<?php echo esc_attr( $id ); ?>" aria-selected="<?php echo $id === $first ? 'true' : 'false'; ?>" tabindex="<?php echo $id === $first ? '0' : '-1'; ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>

			<?php if ( $vurl ) : ?>
				<div role="tabpanel" class="tabpanel" id="video" aria-labelledby="tab-video"<?php echo 'video' === $first ? '' : ' hidden'; ?>>
					<?php if ( $vhtml ) : ?>
						<?php echo $vhtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<noscript><p><a class="btn video" href="<?php echo esc_url( $vurl ); ?>" target="_blank" rel="noopener">🎬 <?php echo esc_html( brev_mod( 'video_btn' ) ); ?></a></p></noscript>
					<?php else : ?>
						<p><a class="btn video" href="<?php echo esc_url( $vurl ); ?>" target="_blank" rel="noopener">🎬 <?php echo esc_html( brev_mod( 'video_btn' ) ); ?></a></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>
</div></div>
<?php
get_footer();
