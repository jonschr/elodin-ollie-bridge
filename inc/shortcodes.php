<?php

/**
 * Shortcode: [year]
 *
 * @return string
 */
function elodin_bridge_year_shortcode() {
	if ( function_exists( 'wp_date' ) ) {
		return wp_date( 'Y' );
	}

	return date( 'Y' );
}

/**
 * Shortcode: [c]
 *
 * @return string
 */
function elodin_bridge_copyright_shortcode() {
	return '&copy;';
}

/**
 * Shortcode: [tm]
 *
 * @return string
 */
function elodin_bridge_trademark_shortcode() {
	return '<sup>&trade;</sup>';
}

/**
 * Shortcode: [r]
 *
 * @return string
 */
function elodin_bridge_registered_shortcode() {
	return '<sup>&reg;</sup>';
}

/**
 * Register Bridge shortcodes when enabled.
 */
function elodin_bridge_register_shortcodes() {
	if ( ! elodin_bridge_is_shortcodes_enabled() ) {
		return;
	}

	add_shortcode( 'year', 'elodin_bridge_year_shortcode' );
	add_shortcode( 'c', 'elodin_bridge_copyright_shortcode' );
	add_shortcode( 'tm', 'elodin_bridge_trademark_shortcode' );
	add_shortcode( 'r', 'elodin_bridge_registered_shortcode' );
}
add_action( 'init', 'elodin_bridge_register_shortcodes' );
