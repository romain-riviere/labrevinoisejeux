<?php
/**
 * Template Name: Contact
 *
 * Page Contact : blocs de coordonnées et formulaire.
 *
 * @package Brevinoise
 */

get_header();
the_post();
brev_pagehead( get_the_title(), brev_mod( 'contact_intro' ) );
$sent = isset( $_GET['envoye'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$err  = isset( $_GET['erreur'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="band"><div class="wrap contact">
	<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
		<?php if ( brev_mod( 'contact' . $i . '_title' ) ) : ?>
			<div class="box"><h3><?php echo esc_html( brev_mod( 'contact' . $i . '_title' ) ); ?></h3><p><?php echo brev_lines( brev_mod( 'contact' . $i . '_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p></div>
		<?php endif; ?>
	<?php endfor; ?>
	<?php if ( trim( get_the_content() ) ) : ?>
		<div class="box prose" style="grid-column:1/-1"><?php the_content(); ?></div>
	<?php endif; ?>
	<div class="box" id="formulaire" style="grid-column:1/-1">
		<h3>Nous écrire</h3>
		<?php if ( $sent ) : ?><p class="ok">Merci, votre message a bien été envoyé.</p><?php endif; ?>
		<?php if ( $err ) : ?><p class="ko">Le message n’a pas pu être envoyé. Vérifiez les champs et réessayez.</p><?php endif; ?>
		<form class="sform" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="brev_contact">
			<?php wp_nonce_field( 'brev_contact', 'brev_nonce' ); ?>
			<p class="hp"><label>Site web <input name="site_web" tabindex="-1" autocomplete="off"></label></p>
			<label for="nom">Nom</label><input id="nom" name="nom" required autocomplete="name">
			<label for="email">E-mail</label><input id="email" name="email" type="email" required autocomplete="email">
			<label for="message">Message</label><textarea id="message" name="message" rows="6" required></textarea>
			<p style="margin-top:16px"><button class="btn" type="submit">Envoyer</button></p>
		</form>
	</div>
</div></div>
<?php
get_footer();
