<?php
/**
 * En-tête : logo et menu.
 *
 * @package Brevinoise
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#app">Aller au contenu</a>
<div class="top">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" rel="home">
		<?php
		$logo = get_theme_mod( 'custom_logo' );
		if ( $logo ) {
			echo wp_get_attachment_image( $logo, 'medium', false, array( 'class' => 'logo', 'alt' => '' ) );
		} else {
			echo '<img class="logo" src="' . esc_url( BREV_URI . '/assets/img/logo.webp' ) . '" alt="">';
		}
		if ( brev_mod( 'show_name' ) ) {
			echo '<span class="sitename">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
		}
		?>
	</a>
</div>
<header class="bar">
	<div class="nav">
		<button class="burger" id="burger" aria-expanded="false" aria-controls="menu">Menu</button>
		<nav class="menu" id="menu" aria-label="Menu principal">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu-list',
					'fallback_cb'    => 'brev_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	</div>
</header>
<main id="app">
