<?php

/**
 * Make an explicitly configured Cover minimum height authoritative.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $block         Parsed block data.
 * @return string
 */
function elodin_bridge_preserve_cover_min_height( $block_content, $block ) {
	if ( ! elodin_bridge_is_cover_min_heights_enabled() || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
	$dimensions = is_array( $attrs['style']['dimensions'] ?? null ) ? $attrs['style']['dimensions'] : array();
	$uses_style_attribute = array_key_exists( 'minHeight', $dimensions );

	if ( ! $uses_style_attribute && ! array_key_exists( 'minHeight', $attrs ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( array( 'class_name' => 'wp-block-cover' ) ) ) {
		return $block_content;
	}

	$style = (string) $processor->get_attribute( 'style' );
	$style = preg_replace(
		'/(^|;)\s*min-height\s*:\s*([^;!]+?)\s*(?:!important)?\s*(?=;|$)/i',
		'$1min-height:$2!important',
		$style,
		1,
		$replacement_count
	);

	if ( null === $style ) {
		return $block_content;
	}

	// Core omits the inline declaration for zero, even though it preserves the attribute.
	if ( 0 === $replacement_count ) {
		$min_height = $uses_style_attribute ? $dimensions['minHeight'] : $attrs['minHeight'];
		if ( is_numeric( $min_height ) ) {
			$unit = $uses_style_attribute ? 'px' : ( $attrs['minHeightUnit'] ?? 'px' );
			if ( ! is_string( $unit ) || ! preg_match( '/^(?:%|[a-z]+)$/i', $unit ) ) {
				return $block_content;
			}
			$min_height = (string) $min_height . $unit;
		}

		if ( ! is_string( $min_height ) || ! preg_match( '/^(?:0|(?:\d+(?:\.\d+)?|\.\d+)(?:%|[a-z]+))$/i', trim( $min_height ) ) ) {
			return $block_content;
		}

		$style = rtrim( $style );
		$style .= '' === $style || str_ends_with( $style, ';' ) ? '' : ';';
		$style .= 'min-height:' . trim( $min_height ) . '!important';
	}

	$processor->set_attribute( 'style', $style );

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/cover', 'elodin_bridge_preserve_cover_min_height', 20, 2 );

/**
 * Allow pixel minimum heights below WordPress's 50px editor floor.
 */
function elodin_bridge_enqueue_cover_min_height_editor_script() {
	if ( ! elodin_bridge_is_cover_min_heights_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-cover-min-heights.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-cover-min-heights',
		ELODIN_BRIDGE_URL . 'assets/editor-cover-min-heights.js',
		array( 'wp-dom-ready' ),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_cover_min_height_editor_script' );
