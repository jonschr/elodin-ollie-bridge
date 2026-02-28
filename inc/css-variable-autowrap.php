<?php

/**
 * Enqueue CSS variable token auto-wrap script in wp-admin.
 */
function elodin_bridge_enqueue_css_variable_autowrap_admin_script() {
	elodin_bridge_enqueue_css_variable_autowrap_script();
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_css_variable_autowrap_admin_script', 100 );

/**
 * Enqueue CSS variable token auto-wrap script in block editor contexts.
 */
function elodin_bridge_enqueue_css_variable_autowrap_editor_script() {
	elodin_bridge_enqueue_css_variable_autowrap_script();
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_css_variable_autowrap_editor_script', 100 );

/**
 * Register and enqueue the CSS variable token auto-wrap script.
 */
function elodin_bridge_enqueue_css_variable_autowrap_script() {
	static $is_enqueued = false;
	if ( $is_enqueued ) {
		return;
	}

	if ( ! elodin_bridge_is_css_variable_autowrap_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/admin-css-variable-autowrap.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/admin-css-variable-autowrap.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-css-variable-autowrap',
		$script_url,
		array(),
		(string) filemtime( $script_path ),
		true
	);

	wp_add_inline_script(
		'elodin-bridge-css-variable-autowrap',
		'window.elodinBridgeCssVariableAutowrap = ' . wp_json_encode(
			array(
				'enabled' => true,
				'tokens'  => elodin_bridge_get_css_variable_autowrap_tokens(),
				'aliases' => elodin_bridge_get_css_variable_autowrap_aliases(),
			)
		) . ';',
		'before'
	);

	$is_enqueued = true;
}
