<?php

/**
 * Remove legacy theme first-block callback so Bridge controls this behavior.
 */
function elodin_bridge_detach_theme_first_block_callback() {
	remove_action( 'wp_head', 'elodin_first_block_logic' );
}
add_action( 'after_setup_theme', 'elodin_bridge_detach_theme_first_block_callback', 100 );

/**
 * Convert a block name into a safe class suffix.
 *
 * @param string $block_name Block name like core/group.
 * @return string
 */
function elodin_bridge_block_name_to_class_slug( $block_name ) {
	$block_name = strtolower( trim( (string) $block_name ) );
	if ( '' === $block_name ) {
		return '';
	}

	$block_name = str_replace( '/', '-', $block_name );
	$block_name = preg_replace( '/[^a-z0-9_-]+/', '-', $block_name );
	$block_name = str_replace( '_', '-', (string) $block_name );

	return trim( (string) $block_name, '-' );
}

/**
 * Get top-level block names for the current singular post.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_current_top_level_block_names() {
	if ( ! is_singular() || ! function_exists( 'has_blocks' ) || ! function_exists( 'parse_blocks' ) ) {
		return array();
	}

	$post_id = get_queried_object_id();
	if ( $post_id < 1 ) {
		return array();
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || ! has_blocks( $post->post_content ) ) {
		return array();
	}

	$blocks = (array) parse_blocks( $post->post_content );
	$names = array();

	foreach ( $blocks as $block ) {
		if ( empty( $block['blockName'] ) || ! is_string( $block['blockName'] ) ) {
			continue;
		}

		$names[] = $block['blockName'];
	}

	return $names;
}

/**
 * Convert configured section blocks into a lookup table keyed by class slug.
 *
 * @return array<string,bool>
 */
function elodin_bridge_get_section_block_slug_lookup() {
	$settings = elodin_bridge_get_block_edge_class_settings();
	$lookup = array();

	foreach ( $settings['section_blocks'] as $block_name ) {
		$slug = elodin_bridge_block_name_to_class_slug( $block_name );
		if ( '' === $slug ) {
			continue;
		}

		$lookup[ $slug ] = true;
	}

	return $lookup;
}

/**
 * Add first/last block body classes based on settings.
 *
 * @param array<int,string> $classes Existing body classes.
 * @return array<int,string>
 */
function elodin_bridge_add_first_last_block_body_classes( $classes ) {
	$settings = elodin_bridge_get_block_edge_class_settings();
	if ( empty( $settings['enabled'] ) ) {
		return $classes;
	}

	if ( empty( $settings['enable_first'] ) && empty( $settings['enable_last'] ) ) {
		return $classes;
	}

	$top_level_blocks = elodin_bridge_get_current_top_level_block_names();
	if ( empty( $top_level_blocks ) ) {
		return $classes;
	}

	$section_lookup = elodin_bridge_get_section_block_slug_lookup();
	$first_block = $top_level_blocks[0];
	$last_block = $top_level_blocks[ count( $top_level_blocks ) - 1 ];

	if ( ! empty( $settings['enable_first'] ) ) {
		$first_slug = elodin_bridge_block_name_to_class_slug( $first_block );
		if ( '' !== $first_slug ) {
			$classes[] = 'first-block-is-' . sanitize_html_class( $first_slug );

			if ( ! empty( $section_lookup[ $first_slug ] ) ) {
				$classes[] = 'first-block-is-section';
			}
		}
	}

	if ( ! empty( $settings['enable_last'] ) ) {
		$last_slug = elodin_bridge_block_name_to_class_slug( $last_block );
		if ( '' !== $last_slug ) {
			$classes[] = 'last-block-is-' . sanitize_html_class( $last_slug );

			if ( ! empty( $section_lookup[ $last_slug ] ) ) {
				$classes[] = 'last-block-is-section';
			}
		}
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'body_class', 'elodin_bridge_add_first_last_block_body_classes' );

/**
 * Render a small front-end debug panel listing top-level blocks.
 */
function elodin_bridge_render_top_level_block_debug_panel() {
	$settings = elodin_bridge_get_block_edge_class_settings();
	if ( empty( $settings['enabled'] ) || empty( $settings['enable_debug'] ) ) {
		return;
	}

	if ( is_admin() || ! is_singular() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$blocks = elodin_bridge_get_current_top_level_block_names();
	?>
	<div style="position:fixed;right:12px;bottom:12px;z-index:99999;max-width:360px;background:rgba(15,16,19,0.94);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:8px;padding:10px 12px;font:12px/1.4 -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;box-shadow:0 8px 30px rgba(0,0,0,0.35);">
		<div style="font-weight:600;margin-bottom:6px;"><?php esc_html_e( 'Top-Level Blocks', 'elodin-bridge' ); ?></div>
		<?php if ( empty( $blocks ) ) : ?>
			<div style="opacity:0.8;"><?php esc_html_e( 'No top-level blocks found.', 'elodin-bridge' ); ?></div>
		<?php else : ?>
			<ol style="margin:0;padding-left:18px;max-height:200px;overflow:auto;">
				<?php foreach ( $blocks as $block_name ) : ?>
					<li style="margin:0 0 2px;"><?php echo esc_html( $block_name ); ?></li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'wp_footer', 'elodin_bridge_render_top_level_block_debug_panel', 999 );
