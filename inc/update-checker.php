<?php

/**
 * Load Plugin Update Checker with static release metadata.
 */
function elodin_bridge_boot_update_checker() {
	$update_checker_file = ELODIN_BRIDGE_DIR . '/vendor/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $update_checker_file ) ) {
		return;
	}

	require_once $update_checker_file;

	if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
		return;
	}

	\YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://raw.githubusercontent.com/jonschr/elodin-ollie-bridge/master/update.json',
		ELODIN_BRIDGE_PLUGIN_FILE,
		'elodin-ollie-bridge'
	);
}
add_action( 'plugins_loaded', 'elodin_bridge_boot_update_checker', 5 );
