<?php

/**
 * Get mobile media query used by the fixed-background repair script.
 *
 * @return string
 */
function elodin_bridge_get_mobile_fixed_background_repair_media_query() {
	if ( function_exists( 'generate_get_media_query' ) ) {
		return (string) generate_get_media_query( 'mobile' );
	}

	return '(max-width: 768px)';
}

/**
 * Enqueue mobile fixed-background repair script on the front-end.
 */
function elodin_bridge_enqueue_mobile_fixed_background_repair_script() {
	if ( is_admin() || ! elodin_bridge_is_mobile_fixed_background_repair_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/fixed-background-mobile-fix.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/fixed-background-mobile-fix.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	$handle = 'elodin-bridge-mobile-fixed-background-repair';
	wp_enqueue_script(
		$handle,
		$script_url,
		array(),
		(string) filemtime( $script_path ),
		true
	);

	wp_add_inline_script(
		$handle,
		'window.elodinBridgeMobileFixedBackgroundRepair = ' . wp_json_encode(
			array(
				'mobileQuery' => elodin_bridge_get_mobile_fixed_background_repair_media_query(),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_mobile_fixed_background_repair_script' );
