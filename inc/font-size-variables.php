<?php

/**
 * Build root-level font-size variable CSS.
 *
 * @return string
 */
function elodin_bridge_build_font_size_variables_css() {
	if ( ! elodin_bridge_is_font_size_variables_enabled() ) {
		return '';
	}

	$aliases = elodin_bridge_get_font_size_variable_aliases();
	if ( empty( $aliases ) ) {
		return '';
	}

	$declarations = array();
	foreach ( $aliases as $alias ) {
		$token = sanitize_key( $alias['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$value = trim( (string) ( $alias['value'] ?? '' ) );
		if ( '' === $value ) {
			continue;
		}

		$declarations[] = '--font-' . $token . ':' . $value;
	}

	if ( empty( $declarations ) ) {
		return '';
	}

	return ':root{' . implode( ';', $declarations ) . ';}';
}

/**
 * Enqueue font-size variable styles on front-end and in block editor content.
 */
function elodin_bridge_enqueue_font_size_variables_styles() {
	$css = elodin_bridge_build_font_size_variables_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-font-size-variables';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_font_size_variables_styles' );
