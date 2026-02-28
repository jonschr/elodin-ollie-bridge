<?php

/**
 * Get core block boundary highlight editor CSS.
 *
 * @return string
 */
function elodin_bridge_get_editor_group_border_css() {
	static $cached_css = null;
	if ( null !== $cached_css ) {
		return $cached_css;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-group-border.css';
	if ( ! file_exists( $style_path ) || ! is_readable( $style_path ) ) {
		$cached_css = '';
		return $cached_css;
	}

	$css = file_get_contents( $style_path );
	$cached_css = is_string( $css ) ? trim( $css ) : '';
	return $cached_css;
}

/**
 * Inject core block boundary highlight styles into editor iframe settings.
 *
 * @param array<string,mixed> $settings Block editor settings.
 * @param mixed               $editor_context Block editor context.
 * @return array<string,mixed>
 */
function elodin_bridge_inject_editor_group_border_styles_into_editor_settings( $settings, $editor_context ) {
	if ( ! elodin_bridge_is_editor_group_border_enabled() ) {
		return $settings;
	}

	$context_name = '';
	if ( is_object( $editor_context ) && isset( $editor_context->name ) ) {
		$context_name = (string) $editor_context->name;
	}

	if ( ! in_array( $context_name, array( 'core/edit-post', 'core/edit-site' ), true ) ) {
		return $settings;
	}

	$css = elodin_bridge_get_editor_group_border_css();
	if ( '' === $css ) {
		return $settings;
	}

	if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}

	$settings['styles'][] = array(
		'css' => $css,
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'elodin_bridge_inject_editor_group_border_styles_into_editor_settings', 120, 2 );
