<?php
/**
 * Pied de page : présentation, coordonnées, navigation.
 *
 * @package Brevinoise
 */

?>
</main>
<footer class="band foot">
	<div class="wrap">
		<div><h3><?php bloginfo( 'name' ); ?></h3><p><?php echo esc_html( brev_mod( 'footer_about' ) ); ?></p></div>
		<div><h3>Coordonnées</h3><p><?php echo brev_lines( brev_mod( 'footer_address' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p></div>
		<div><h3>Navigation</h3>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'foot-menu',
					'fallback_cb'    => 'brev_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</div>
		<div><h3><?php echo esc_html( brev_mod( 'footer_extra_title' ) ); ?></h3><p><?php echo brev_lines( brev_mod( 'footer_extra' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p></div>
	</div>
	<div class="wrap legal"><?php echo esc_html( brev_mod( 'footer_legal' ) ); ?></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
