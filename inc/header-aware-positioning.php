<?php

/**
 * Enqueue the shared page-chrome measurement service.
 */
function elodin_bridge_enqueue_header_aware_positioning_assets() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
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
				'selectors'      => $settings['selectors'],
				'fixedSelectors' => empty( $settings['fixed_position']['enabled'] ) ? '' : $settings['fixed_position']['selectors'],
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_header_aware_positioning_assets' );

/**
 * Force configured page-chrome elements to use fixed positioning.
 */
function elodin_bridge_enqueue_header_fixed_position_styles() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['fixed_position']['enabled'] ) ) {
		return;
	}

	$handle = 'elodin-bridge-header-fixed-position';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $settings['fixed_position']['selectors'] . '{box-sizing:border-box;position:fixed!important;width:100%!important;z-index:50!important;}' );
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_header_fixed_position_styles' );
