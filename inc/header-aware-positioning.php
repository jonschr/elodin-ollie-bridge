<?php

/**
 * Enqueue the shared page-chrome measurement service.
 */
function elodin_bridge_enqueue_header_aware_positioning_assets() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['sticky']['enabled'] ) && empty( $settings['smooth_scroll']['enabled'] ) ) {
		return;
	}

	$handle = 'elodin-bridge-header-aware-positioning';
	$script_path = ELODIN_BRIDGE_DIR . '/assets/header-aware-positioning.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		$handle,
		ELODIN_BRIDGE_URL . 'assets/header-aware-positioning.js',
		array(),
		(string) filemtime( $script_path ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_add_inline_script(
		$handle,
		'window.elodinBridgeHeaderAwarePositioning = ' . wp_json_encode(
			array(
				'selectors' => $settings['selectors'],
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_header_aware_positioning_assets' );
