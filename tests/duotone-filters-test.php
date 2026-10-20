<?php

// Run with assertions enabled, optionally passing --without-duotone to check older core compatibility.
$block_theme = true;
$early_filters_enabled = true;
$footer_priority = 10;
$output_count = 0;
$actions = array();

function wp_is_block_theme() {
	return $GLOBALS['block_theme'];
}
function elodin_bridge_is_early_duotone_filters_enabled() {
	return $GLOBALS['early_filters_enabled'];
}
function add_action( $hook, $callback, $priority = 10 ) {
	$GLOBALS['actions'][] = array( $hook, $callback, $priority );
}
function has_action( $hook, $callback ) {
	assert( 'wp_footer' === $hook );
	assert( array( 'WP_Duotone', 'output_footer_assets' ) === $callback );
	return $GLOBALS['footer_priority'];
}
function remove_action( $hook, $callback, $priority ) {
	assert( 'wp_footer' === $hook );
	assert( array( 'WP_Duotone', 'output_footer_assets' ) === $callback );
	assert( $GLOBALS['footer_priority'] === $priority );
	$GLOBALS['footer_priority'] = false;
}

if ( ! in_array( '--without-duotone', $argv, true ) ) {
	class WP_Duotone {
		public static function output_footer_assets() {
			++$GLOBALS['output_count'];
		}
	}
}

require dirname( __DIR__ ) . '/inc/duotone-filters.php';
assert( array( array( 'wp_body_open', 'elodin_bridge_output_duotone_filters_early', 0 ) ) === $actions );

// Classic themes still collect their filters while rendering the body, so keep their footer output.
$block_theme = false;
elodin_bridge_output_duotone_filters_early();
assert( 10 === $footer_priority && 0 === $output_count );

$block_theme = true;
$early_filters_enabled = false;
elodin_bridge_output_duotone_filters_early();
assert( 10 === $footer_priority && 0 === $output_count );

$early_filters_enabled = true;
elodin_bridge_output_duotone_filters_early();
if ( class_exists( 'WP_Duotone' ) ) {
	assert( false === $footer_priority && 1 === $output_count );
	// Do not emit duplicates if the footer callback has already been relocated.
	elodin_bridge_output_duotone_filters_early();
	assert( 1 === $output_count );
} else {
	assert( 10 === $footer_priority && 0 === $output_count );
}

echo "Duotone filter placement checks passed.\n";
