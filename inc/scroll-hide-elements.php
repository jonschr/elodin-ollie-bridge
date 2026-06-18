<?php

/**
 * Enqueue scroll-hide element assets on the front end.
 */
function elodin_bridge_enqueue_scroll_hide_elements_assets() {
	$settings = elodin_bridge_get_scroll_hide_elements_settings();
	if ( empty( $settings['enabled'] ) || empty( $settings['rules'] ) ) {
		return;
	}

	$handle = 'elodin-bridge-scroll-hide-elements';
	$css = '.elodin-bridge-scroll-hide-target{overflow:hidden;transition:height 200ms ease-out,padding 200ms ease-out,margin 200ms ease-out,opacity 200ms ease-out;}.elodin-bridge-scroll-hide-target.is-elodin-bridge-scroll-hidden{border-top:0!important;border-bottom:0!important;height:0!important;line-height:0!important;margin-top:0!important;margin-bottom:0!important;max-height:0!important;min-height:0!important;opacity:0;overflow:hidden!important;pointer-events:none;padding-top:0!important;padding-bottom:0!important;}';

	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );

	$script_path = ELODIN_BRIDGE_DIR . '/assets/scroll-hide-elements.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		$handle,
		ELODIN_BRIDGE_URL . 'assets/scroll-hide-elements.js',
		array(),
		(string) filemtime( $script_path ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	wp_add_inline_script(
		$handle,
		'window.elodinBridgeScrollHideElements = ' . wp_json_encode(
			array(
				'rules' => $settings['rules'],
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_scroll_hide_elements_assets' );
