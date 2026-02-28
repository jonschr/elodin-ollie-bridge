<?php

/**
 * Build root-level spacing variable CSS.
 *
 * @return string
 */
function elodin_bridge_build_spacing_variables_css() {
	if ( ! elodin_bridge_is_spacing_variables_enabled() ) {
		return '';
	}

	$aliases = elodin_bridge_get_spacing_variable_aliases();
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

		$declarations[] = '--space-' . $token . ':' . $value;
	}

	if ( empty( $declarations ) ) {
		return '';
	}

	return ':root{' . implode( ';', $declarations ) . ';}';
}

/**
 * Enqueue spacing variable styles on front-end and in block editor content.
 */
function elodin_bridge_enqueue_spacing_variables_styles() {
	$css = elodin_bridge_build_spacing_variables_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-spacing-variables';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_spacing_variables_styles' );
