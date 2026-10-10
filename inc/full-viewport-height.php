<?php

/**
 * Resolve only explicit full-viewport block dimensions.
 *
 * @param array $block Parsed block data.
 * @return array<string,string> CSS properties mapped to viewport units.
 */
function elodin_bridge_get_full_viewport_dimensions( $block ) {
	$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
	$dimensions = is_array( $attrs['style']['dimensions'] ?? null ) ? $attrs['style']['dimensions'] : array();
	if ( 'core/cover' === ( $block['blockName'] ?? '' ) && ! isset( $dimensions['minHeight'] ) && isset( $attrs['minHeight'] ) ) {
		$dimensions['minHeight'] = $attrs['minHeight'] . ( $attrs['minHeightUnit'] ?? 'px' );
	}

	$result = array();
	foreach ( array( 'height' => 'height', 'minHeight' => 'min-height' ) as $attribute => $property ) {
		$value = $dimensions[ $attribute ] ?? null;
		if ( is_string( $value ) && preg_match( '/^100(?:\.0+)?(dvh|svh|lvh|vh)$/i', trim( $value ), $match ) ) {
			$result[ $property ] = strtolower( $match[1] );
		}
	}
	return $result;
}

/**
 * Adjust rendered wrappers without changing native saved block values.
 */
function elodin_bridge_render_full_viewport_height( $block_content, $block ) {
	$dimensions = elodin_bridge_get_full_viewport_dimensions( $block );
	if ( ! $dimensions || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['full_height']['enabled'] ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag() || in_array( $processor->get_tag(), array( 'SCRIPT', 'STYLE' ), true ) ) {
		return $block_content;
	}
	$style = (string) $processor->get_attribute( 'style' );
	foreach ( $dimensions as $property => $unit ) {
		$style = preg_replace( '/(^|;)\s*' . $property . '\s*:[^;]*(?=;|$)/i', '$1', $style );
		$style = rtrim( $style, "; \t\n\r" ) . ';' . $property . ':max(0px,calc(100' . $unit . ' - var(--elodin-bridge-full-height-offset,0px)))!important';
	}
	$processor->set_attribute( 'style', ltrim( $style, ';' ) );
	return $processor->get_updated_html();
}
add_filter( 'render_block', 'elodin_bridge_render_full_viewport_height', 30, 2 );

/**
 * Keep native height controls and the editor canvas in agreement.
 */
function elodin_bridge_enqueue_full_viewport_height_editor_script() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['full_height']['enabled'] ) ) {
		return;
	}
	$handle = 'elodin-bridge-editor-full-viewport-height';
	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-full-viewport-height.js';
	wp_enqueue_script(
		$handle,
		ELODIN_BRIDGE_URL . 'assets/editor-full-viewport-height.js',
		array( 'wp-hooks', 'wp-element', 'wp-compose' ),
		(string) filemtime( $script_path ),
		true
	);
	wp_add_inline_script( $handle, 'window.elodinBridgeFullViewportHeight = ' . wp_json_encode( array( 'selectors' => $settings['selectors'] ) ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_full_viewport_height_editor_script' );

/**
 * Override editor inline heights without altering their underlying attributes.
 */
function elodin_bridge_enqueue_full_viewport_height_editor_styles() {
	if ( ! is_admin() ) {
		return;
	}
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['full_height']['enabled'] ) ) {
		return;
	}
	$handle = 'elodin-bridge-editor-full-viewport-height';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, '.elodin-bridge-full-viewport-height{height:var(--elodin-bridge-full-viewport-height)!important;}.elodin-bridge-full-viewport-min-height{min-height:var(--elodin-bridge-full-viewport-min-height)!important;}' );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_full_viewport_height_editor_styles' );
