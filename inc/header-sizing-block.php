<?php

/**
 * Register the Header Sizing spacer block.
 */
function elodin_bridge_register_header_sizing_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$handle = 'elodin-bridge-header-sizing-block-editor';
	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-header-sizing-block.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_register_script(
		$handle,
		ELODIN_BRIDGE_URL . 'assets/editor-header-sizing-block.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n' ),
		(string) filemtime( $script_path ),
		true
	);
	wp_add_inline_script(
		$handle,
		'window.elodinBridgeHeaderSizingBlock = ' . wp_json_encode(
			array(
				'selectors' => elodin_bridge_get_header_aware_positioning_settings()['selectors'],
				'enabled'   => elodin_bridge_is_header_sizing_block_enabled() ? 1 : 0,
			)
		) . ';',
		'before'
	);

	register_block_type(
		'elodin-bridge/header-sizing',
		array(
			'api_version'     => 3,
			'editor_script'   => $handle,
			'render_callback' => 'elodin_bridge_render_header_sizing_block',
		)
	);
}
add_action( 'init', 'elodin_bridge_register_header_sizing_block' );

/**
 * Render the Header Sizing spacer block.
 *
 * @return string
 */
function elodin_bridge_render_header_sizing_block() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	$script_path = ELODIN_BRIDGE_DIR . '/assets/header-sizing-block-early.js';
	$script = '';

	if ( file_exists( $script_path ) ) {
		$script = wp_get_script_tag(
			array(
				'src'            => add_query_arg( 'ver', (string) filemtime( $script_path ), ELODIN_BRIDGE_URL . 'assets/header-sizing-block-early.js' ),
				'data-selectors' => $settings['selectors'],
			)
		);
	}

	return $script . '<div class="wp-block-elodin-bridge-header-sizing" aria-hidden="true"></div>';
}

/**
 * Enqueue Header Sizing block frontend styles.
 */
function elodin_bridge_enqueue_header_sizing_block_styles() {
	$handle = 'elodin-bridge-header-sizing-block';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, '.wp-block-elodin-bridge-header-sizing{height:var(--elodin-bridge-header-sizing-height,0px);margin:0!important;padding:0!important;width:100%;}' );
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_header_sizing_block_styles' );
