<?php

/**
 * Build balanced text CSS.
 *
 * @return string
 */
function elodin_bridge_build_balanced_text_css() {
	return ':is(p,h1,h2,h3,h4,h5,h6).balanced{text-wrap:balance!important;}';
}

/**
 * Enqueue balanced text styles for front-end and block editor content.
 */
function elodin_bridge_enqueue_balanced_text_styles() {
	if ( ! elodin_bridge_is_balanced_text_enabled() ) {
		return;
	}

	$css = elodin_bridge_build_balanced_text_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-balanced-text';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_balanced_text_styles' );

/**
 * Enqueue balanced text button in the block toolbar.
 */
function elodin_bridge_enqueue_editor_balanced_text_toolbar() {
	if ( ! elodin_bridge_is_balanced_text_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-balanced-text-toggle.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/editor-balanced-text-toggle.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-balanced-text-toggle',
		$script_url,
		array( 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_balanced_text_toolbar' );
