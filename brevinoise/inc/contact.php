<?php
/**
 * Formulaire de contact (envoi par e-mail, sans extension).
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_nopriv_brev_contact', 'brev_contact_send' );
add_action( 'admin_post_brev_contact', 'brev_contact_send' );
/**
 * Envoie le message du formulaire.
 */
function brev_contact_send() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'envoye', 'erreur' ), $back );
	if ( ! isset( $_POST['brev_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brev_nonce'] ) ), 'brev_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'erreur', 1, $back ) . '#formulaire' );
		exit;
	}
	// Champ piège invisible : rempli uniquement par les robots.
	if ( ! empty( $_POST['site_web'] ) ) {
		wp_safe_redirect( add_query_arg( 'envoye', 1, $back ) . '#formulaire' );
		exit;
	}
	$name  = isset( $_POST['nom'] ) ? sanitize_text_field( wp_unslash( $_POST['nom'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$msg   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	if ( ! $name || ! is_email( $email ) || ! $msg ) {
		wp_safe_redirect( add_query_arg( 'erreur', 1, $back ) . '#formulaire' );
		exit;
	}
	$to   = brev_mod( 'contact_email' ) ? brev_mod( 'contact_email' ) : get_option( 'admin_email' );
	$sent = wp_mail(
		$to,
		'[' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . '] Message de ' . $name,
		"Nom : $name\nE-mail : $email\n\n$msg",
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);
	wp_safe_redirect( add_query_arg( $sent ? 'envoye' : 'erreur', 1, $back ) . '#formulaire' );
	exit;
}
