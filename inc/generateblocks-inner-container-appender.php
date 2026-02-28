<?php

/**
 * Enqueue editor override for GenerateBlocks inner-container appender behavior.
 *
 * Allows the "Add Inner Container" action for root-level GenerateBlocks elements
 * that are direct children of core/post-content in block themes.
 */
function elodin_bridge_enqueue_generateblocks_inner_container_appender_override() {
	if ( ! elodin_bridge_is_generateblocks_available() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-generateblocks-inner-container-appender.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/editor-generateblocks-inner-container-appender.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	$deps = array(
		'wp-hooks',
		'wp-components',
		'wp-element',
		'wp-data',
		'wp-blocks',
		'wp-i18n',
	);

	if ( wp_script_is( 'generateblocks-editor', 'registered' ) || wp_script_is( 'generateblocks-editor', 'enqueued' ) ) {
		$deps[] = 'generateblocks-editor';
	}

	wp_enqueue_script(
		'elodin-bridge-editor-generateblocks-inner-container-appender',
		$script_url,
		$deps,
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_generateblocks_inner_container_appender_override' );
