<?php
/**
 * Types de contenu : Festivals, Jeux, Escape Box & accessoires, BD, Adhérents.
 *
 * @package Brevinoise
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'brev_register_types' );
/**
 * Enregistre les types de contenu et leurs champs.
 */
function brev_register_types() {
	$pos = 5;
	foreach ( brev_types() as $k => $t ) {
		register_post_type(
			$t['pt'],
			array(
				'labels'        => array(
					'name'               => $t['name'],
					'singular_name'      => $t['one'],
					'menu_name'          => $t['name'],
					'add_new'            => 'Ajouter',
					'add_new_item'       => 'Ajouter : ' . $t['one'],
					'edit_item'          => 'Modifier : ' . $t['one'],
					'new_item'           => 'Nouveau : ' . $t['one'],
					'view_item'          => 'Voir',
					'search_items'       => 'Rechercher',
					'not_found'          => 'Aucun élément',
					'not_found_in_trash' => 'Aucun élément dans la corbeille',
					'all_items'          => 'Tous',
					'featured_image'     => $t['img'],
					'set_featured_image' => 'Choisir : ' . strtolower( $t['img'] ),
					'remove_featured_image' => 'Retirer l’image',
					'use_featured_image' => 'Utiliser comme ' . strtolower( $t['img'] ),
				),
				'public'        => true,
				'has_archive'   => $t['slug'],
				'rewrite'       => array( 'slug' => $t['slug'], 'with_front' => false ),
				'menu_icon'     => $t['icon'],
				'menu_position' => $pos++,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
				'show_in_rest'  => false,
			)
		);
		foreach ( array_keys( $t['fields'] ) as $f ) {
			register_post_meta(
				$t['pt'],
				'_brev_' . $f,
				array(
					'single'        => true,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}

add_filter( 'use_block_editor_for_post_type', 'brev_classic_editor', 10, 2 );
/**
 * Éditeur simple pour nos fiches (titre, description, image, champs).
 *
 * @param bool   $use Utiliser l'éditeur de blocs.
 * @param string $pt  Type.
 * @return bool
 */
function brev_classic_editor( $use, $pt ) {
	return in_array( $pt, wp_list_pluck( brev_types(), 'pt' ), true ) ? false : $use;
}

/**
 * Clé courte d'un type de contenu.
 *
 * @param string $pt Type WordPress.
 * @return string|null
 */
function brev_key_of( $pt ) {
	foreach ( brev_types() as $k => $t ) {
		if ( $t['pt'] === $pt ) {
			return $k;
		}
	}
	return null;
}

add_action( 'add_meta_boxes', 'brev_meta_boxes' );
/**
 * Encadré « Informations » sous la description.
 */
function brev_meta_boxes() {
	foreach ( brev_types() as $k => $t ) {
		add_meta_box( 'brev_fields', 'Informations', 'brev_render_fields', $t['pt'], 'normal', 'high', array( 'key' => $k ) );
	}
}

/**
 * Affiche les champs d'une fiche.
 *
 * @param WP_Post $post Fiche.
 * @param array   $box  Encadré.
 */
function brev_render_fields( $post, $box ) {
	$t = brev_types()[ $box['args']['key'] ];
	wp_nonce_field( 'brev_save_fields', 'brev_fields_nonce' );
	echo '<p class="description">La description se tape dans le grand champ au-dessus, l’image se choisit à droite (« ' . esc_html( $t['img'] ) . ' »). Le champ « Ordre » (encadré Attributs) règle l’ordre d’affichage : 0 en premier.</p>';
	echo '<table class="form-table brev-fields"><tbody>';
	foreach ( $t['fields'] as $f => $def ) {
		list( $label, $type, $help ) = $def;
		$val = get_post_meta( $post->ID, '_brev_' . $f, true );
		$id  = 'brev_' . $f;
		echo '<tr><th><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( 'games' === $type ) {
			$val = array_map( 'intval', (array) $val );
			echo '<div class="brev-checks">';
			foreach ( array( 'jeu', 'escape', 'bd' ) as $gk ) {
				$items = get_posts( array( 'post_type' => brev_types()[ $gk ]['pt'], 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => array( 'publish', 'draft' ) ) );
				if ( ! $items ) {
					continue;
				}
				echo '<strong>' . esc_html( brev_types()[ $gk ]['name'] ) . '</strong>';
				foreach ( $items as $it ) {
					printf( '<label><input type="checkbox" name="brev_games[]" value="%d" %s> %s</label>', (int) $it->ID, checked( in_array( (int) $it->ID, $val, true ), true, false ), esc_html( get_the_title( $it ) ) );
				}
			}
			echo '</div>';
		} elseif ( 'play' === $type ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '"><option value="">Aucun</option>';
			foreach ( brev_nav_games() as $gid => $g ) {
				printf( '<option value="%s" %s>%s (version navigateur)</option>', esc_attr( $gid ), selected( $val, $gid, false ), esc_html( $g['title'] ) );
			}
			echo '</select>';
		} elseif ( 'kind' === $type ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">';
			foreach ( array( 'Escape Box', 'Accessoire' ) as $o ) {
				printf( '<option %s>%s</option>', selected( $val ? $val : 'Escape Box', $o, false ), esc_html( $o ) );
			}
			echo '</select>';
		} else {
			printf(
				'<input type="%s" class="large-text" id="%s" name="%s" value="%s"%s>',
				'url' === $type ? 'url' : 'text',
				esc_attr( $id ),
				esc_attr( $id ),
				esc_attr( $val ),
				'url' === $type ? ' placeholder="https://"' : ''
			);
		}
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

add_action( 'save_post', 'brev_save_fields', 10, 2 );
/**
 * Enregistre les champs d'une fiche.
 *
 * @param int     $post_id Identifiant.
 * @param WP_Post $post    Fiche.
 */
function brev_save_fields( $post_id, $post ) {
	$k = brev_key_of( $post->post_type );
	if ( ! $k || ! isset( $_POST['brev_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brev_fields_nonce'] ) ), 'brev_save_fields' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( brev_types()[ $k ]['fields'] as $f => $def ) {
		$type = $def[1];
		if ( 'games' === $type ) {
			$v = isset( $_POST['brev_games'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['brev_games'] ) ) ) ) : array();
		} else {
			$raw = isset( $_POST[ 'brev_' . $f ] ) ? wp_unslash( $_POST[ 'brev_' . $f ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( 'url' === $type ) {
				$v = esc_url_raw( trim( (string) $raw ) );
			} elseif ( 'play' === $type ) {
				$v = array_key_exists( $raw, brev_nav_games() ) ? $raw : '';
			} elseif ( 'kind' === $type ) {
				$v = 'Accessoire' === $raw ? 'Accessoire' : 'Escape Box';
			} else {
				$v = sanitize_text_field( $raw );
			}
		}
		update_post_meta( $post_id, '_brev_' . $f, $v );
	}
}

add_action( 'admin_enqueue_scripts', 'brev_admin_assets' );
/**
 * Petits styles de l'administration.
 */
function brev_admin_assets() {
	wp_enqueue_style( 'brev-admin', BREV_URI . '/assets/admin.css', array(), BREV_VERSION );
}

/* Colonnes « Image » et « Lien » dans les listes. */
add_action(
	'init',
	function () {
		foreach ( brev_types() as $k => $t ) {
			add_filter(
				'manage_' . $t['pt'] . '_posts_columns',
				function ( $cols ) use ( $k ) {
					$new = array();
					foreach ( $cols as $c => $l ) {
						if ( 'title' === $c ) {
							$new['brev_img'] = 'Image';
						}
						$new[ $c ] = $l;
						if ( 'title' === $c ) {
							$new['brev_info'] = 'adherent' === $k ? 'Fonction' : ( 'festival' === $k ? 'Date' : 'Lien' );
							$new['menu_order'] = 'Ordre';
						}
					}
					return $new;
				}
			);
			add_action(
				'manage_' . $t['pt'] . '_posts_custom_column',
				function ( $col, $id ) use ( $k ) {
					if ( 'brev_img' === $col ) {
						echo get_the_post_thumbnail( $id, array( 56, 56 ) ) ?: '<span class="brev-noimg">—</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} elseif ( 'brev_info' === $col ) {
						$m = 'adherent' === $k ? 'role' : ( 'festival' === $k ? 'date' : 'link' );
						$v = get_post_meta( $id, '_brev_' . $m, true );
						echo 'link' === $m && $v ? '<a href="' . esc_url( $v ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $v, PHP_URL_HOST ) ) . '</a>' : esc_html( $v ? $v : '—' );
					} elseif ( 'menu_order' === $col ) {
						echo (int) get_post_field( 'menu_order', $id );
					}
				},
				10,
				2
			);
		}
	}
);
