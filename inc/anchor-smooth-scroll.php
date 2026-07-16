<?php

/**
 * Enqueue header-aware anchor smooth-scrolling assets.
 */
function elodin_bridge_enqueue_anchor_smooth_scroll_assets() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['smooth_scroll']['enabled'] ) ) {
		return;
	}

	$handle = 'elodin-bridge-anchor-smooth-scroll';
	$offset = $settings['smooth_scroll']['offset'];
	$css = sprintf(
		':root{--elodin-bridge-anchor-scroll-bonus-offset:%1$s;--elodin-bridge-anchor-scroll-offset:calc(var(--elodin-bridge-header-height,0px) + var(--elodin-bridge-anchor-scroll-bonus-offset,0px));}html{scroll-behavior:smooth;scroll-padding-top:var(--elodin-bridge-anchor-scroll-offset);}@media (prefers-reduced-motion:reduce){html{scroll-behavior:auto;}}',
		$offset
	);

	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );

	$script_path = ELODIN_BRIDGE_DIR . '/assets/anchor-smooth-scroll.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		$handle,
		ELODIN_BRIDGE_URL . 'assets/anchor-smooth-scroll.js',
		array( 'elodin-bridge-header-aware-positioning' ),
		(string) filemtime( $script_path ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_anchor_smooth_scroll_assets' );
