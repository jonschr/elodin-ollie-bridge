<?php

/**
 * Output block-theme duotone definitions before the blocks that reference them.
 */
function elodin_bridge_output_duotone_filters_early() {
	$callback = array( 'WP_Duotone', 'output_footer_assets' );
	if ( ! elodin_bridge_is_early_duotone_filters_enabled() || ! wp_is_block_theme() || ! is_callable( $callback ) ) {
		return;
	}

	$priority = has_action( 'wp_footer', $callback );
	if ( false === $priority ) {
		return;
	}

	// Block themes render their template before wp_head, so the used filters are already collected.
	remove_action( 'wp_footer', $callback, $priority );
	WP_Duotone::output_footer_assets();
}
add_action( 'wp_body_open', 'elodin_bridge_output_duotone_filters_early', 0 );
