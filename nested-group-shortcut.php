<?php
/**
 * Plugin Name: Nested Group Shortcut
 * Description: Cmd+Option+G inserts an unsynced nested-group pattern.
 * Version: 1.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the user pattern slug.
 *
 * @return string
 */
function nested_group_shortcut_get_pattern_slug() {
	return 'elodin-bridge-nested-group-shortcut';
}

/**
 * Get the nested group block pattern content.
 *
 * @return string
 */
function nested_group_shortcut_get_pattern_content() {
	return '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|x-large","bottom":"var:preset|spacing|x-large","left":"var:preset|spacing|medium","right":"var:preset|spacing|medium"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="margin-top:0;margin-bottom:0;padding-top:var(--wp--preset--spacing--x-large);padding-right:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--x-large);padding-left:var(--wp--preset--spacing--medium)"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * Ensure the unsynced user pattern exists.
 *
 * @return int Pattern post ID.
 */
function nested_group_shortcut_ensure_user_pattern() {
	$pattern_version = 2;
	static $pattern_id = null;

	if ( null !== $pattern_id ) {
		return $pattern_id;
	}

	$pattern_id = 0;
	if ( ! post_type_exists( 'wp_block' ) ) {
		return $pattern_id;
	}

	$pattern = get_page_by_path(
		nested_group_shortcut_get_pattern_slug(),
		OBJECT,
		'wp_block'
	);

	if ( $pattern instanceof WP_Post ) {
		$pattern_id = (int) $pattern->ID;
	} else {
		$created_pattern_id = wp_insert_post(
			array(
				'post_type'    => 'wp_block',
				'post_status'  => 'publish',
				'post_title'   => __( 'Nested Group Shortcut Pattern', 'elodin-bridge' ),
				'post_name'    => nested_group_shortcut_get_pattern_slug(),
				'post_content' => nested_group_shortcut_get_pattern_content(),
			),
			true
		);

		if ( is_wp_error( $created_pattern_id ) ) {
			return 0;
		}

		$pattern_id = (int) $created_pattern_id;
	}

	if ( $pattern_id < 1 ) {
		return 0;
	}

	$current_version = (int) get_post_meta(
		$pattern_id,
		'elodin_bridge_nested_group_pattern_version',
		true
	);

	if ( $current_version < $pattern_version ) {
		wp_update_post(
			array(
				'ID'           => $pattern_id,
				'post_content' => nested_group_shortcut_get_pattern_content(),
			)
		);
		update_post_meta(
			$pattern_id,
			'elodin_bridge_nested_group_pattern_version',
			$pattern_version
		);
	}

	if ( 'unsynced' !== get_post_meta( $pattern_id, 'wp_pattern_sync_status', true ) ) {
		update_post_meta( $pattern_id, 'wp_pattern_sync_status', 'unsynced' );
	}

	if ( taxonomy_exists( 'wp_pattern_category' ) ) {
		$category_id = 0;
		$category    = term_exists( 'elodin-ollie-bridge', 'wp_pattern_category' );

		if ( is_array( $category ) && isset( $category['term_id'] ) ) {
			$category_id = (int) $category['term_id'];
		} elseif ( is_int( $category ) ) {
			$category_id = $category;
		} else {
			$legacy_category = term_exists( 'elodin-bridge', 'wp_pattern_category' );
			if ( is_array( $legacy_category ) && isset( $legacy_category['term_id'] ) ) {
				$category_id = (int) $legacy_category['term_id'];
			} elseif ( is_int( $legacy_category ) ) {
				$category_id = $legacy_category;
			}

			if ( $category_id > 0 ) {
				wp_update_term(
					$category_id,
					'wp_pattern_category',
					array(
						'name' => __( 'Elodin Ollie Bridge', 'elodin-bridge' ),
						'slug' => 'elodin-ollie-bridge',
					)
				);
			} else {
				$inserted_category = wp_insert_term(
					__( 'Elodin Ollie Bridge', 'elodin-bridge' ),
					'wp_pattern_category',
					array(
						'slug' => 'elodin-ollie-bridge',
					)
				);

				if ( is_array( $inserted_category ) && isset( $inserted_category['term_id'] ) ) {
					$category_id = (int) $inserted_category['term_id'];
				}
			}
		}

		if ( $category_id > 0 ) {
			wp_set_object_terms(
				$pattern_id,
				array( $category_id ),
				'wp_pattern_category',
				false
			);
		}
	}

	return $pattern_id;
}
add_action( 'init', 'nested_group_shortcut_ensure_user_pattern', 20 );

/**
 * Get the inserter pattern name from the user pattern ID.
 *
 * @return string
 */
function nested_group_shortcut_get_pattern_name() {
	$pattern_id = nested_group_shortcut_ensure_user_pattern();
	if ( $pattern_id > 0 ) {
		return 'core/block/' . $pattern_id;
	}

	return '';
}

/**
 * Get data passed to the editor shortcut script.
 *
 * @return array<string,mixed>
 */
function nested_group_shortcut_get_script_data() {
	$pattern_id = nested_group_shortcut_ensure_user_pattern();

	return array(
		'patternId'      => $pattern_id,
		'patternName'    => nested_group_shortcut_get_pattern_name(),
		'patternContent' => nested_group_shortcut_get_pattern_content(),
	);
}

/**
 * Register and enqueue the editor shortcut script.
 */
function nested_group_shortcut_enqueue_assets() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	if (
		function_exists( 'elodin_bridge_is_nested_group_shortcut_enabled' ) &&
		! elodin_bridge_is_nested_group_shortcut_enabled()
	) {
		return;
	}

	wp_enqueue_script(
		'nested-group-shortcut',
		plugin_dir_url( __FILE__ ) . 'nested-group-shortcut.js',
		array(
			'wp-blocks',
			'wp-block-editor',
			'wp-core-data',
			'wp-data',
			'wp-keyboard-shortcuts',
			'wp-keycodes',
			'wp-dom',
		),
		(string) filemtime( plugin_dir_path( __FILE__ ) . 'nested-group-shortcut.js' ),
		true
	);

	wp_add_inline_script(
		'nested-group-shortcut',
		'window.elodinBridgeNestedGroupShortcut = ' . wp_json_encode(
			nested_group_shortcut_get_script_data()
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'nested_group_shortcut_enqueue_assets' );
