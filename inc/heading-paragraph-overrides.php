<?php

/**
 * Get parent theme theme.json data.
 *
 * @return array<string,mixed>
 */
function elodin_bridge_get_parent_theme_json_data() {
	$stylesheet_directory = wp_normalize_path( (string) get_stylesheet_directory() );
	$template_directory = wp_normalize_path( (string) get_template_directory() );
	if ( '' === $template_directory || $template_directory === $stylesheet_directory ) {
		return array();
	}

	$path = trailingslashit( $template_directory ) . 'theme.json';
	return elodin_bridge_get_theme_json_data_from_path( $path );
}

/**
 * Normalize theme.json typography settings into CSS declarations.
 *
 * @param mixed $raw_typography Raw typography settings.
 * @return array<string,string>
 */
function elodin_bridge_normalize_theme_json_typography_declarations( $raw_typography ) {
	if ( ! is_array( $raw_typography ) ) {
		return array();
	}

	$property_map = array(
		'fontFamily'     => 'font-family',
		'fontWeight'     => 'font-weight',
		'textTransform'  => 'text-transform',
		'textDecoration' => 'text-decoration',
		'fontStyle'      => 'font-style',
		'fontSize'       => 'font-size',
		'lineHeight'     => 'line-height',
		'letterSpacing'  => 'letter-spacing',
	);

	$declarations = array();
	foreach ( $property_map as $theme_key => $css_property ) {
		if ( ! array_key_exists( $theme_key, $raw_typography ) ) {
			continue;
		}

		$raw_value = $raw_typography[ $theme_key ];
		if ( is_array( $raw_value ) || is_object( $raw_value ) ) {
			continue;
		}

		$value = elodin_bridge_normalize_theme_json_css_value( $raw_value );
		if ( '' === $value ) {
			continue;
		}

		$declarations[ $css_property ] = $value;
	}

	return $declarations;
}

/**
 * Merge multiple typography declaration layers.
 *
 * @param array<int,array<string,string>> $layers Declarations in low-to-high specificity order.
 * @return array<string,string>
 */
function elodin_bridge_merge_theme_json_typography_layers( $layers ) {
	$merged = array();
	foreach ( $layers as $layer ) {
		if ( ! is_array( $layer ) ) {
			continue;
		}

		foreach ( $layer as $property => $value ) {
			$property = trim( (string) $property );
			$value = trim( (string) $value );
			if ( '' === $property || '' === $value ) {
				continue;
			}

			$merged[ $property ] = $value;
		}
	}

	return $merged;
}

/**
 * Build class-based paragraph/heading typography presets from theme.json styles.
 *
 * @return array<string,array<string,string>>
 */
function elodin_bridge_get_heading_paragraph_typography_presets() {
	static $cached_presets = null;
	if ( null !== $cached_presets ) {
		return $cached_presets;
	}

	$child_data = elodin_bridge_get_active_theme_json_data();
	$parent_data = elodin_bridge_get_parent_theme_json_data();
	if ( empty( $child_data ) && empty( $parent_data ) ) {
		$child_data = elodin_bridge_get_plugin_theme_defaults_data();
	}

	$child_styles = isset( $child_data['styles'] ) && is_array( $child_data['styles'] ) ? $child_data['styles'] : array();
	$parent_styles = isset( $parent_data['styles'] ) && is_array( $parent_data['styles'] ) ? $parent_data['styles'] : array();

	$child_elements = isset( $child_styles['elements'] ) && is_array( $child_styles['elements'] ) ? $child_styles['elements'] : array();
	$parent_elements = isset( $parent_styles['elements'] ) && is_array( $parent_styles['elements'] ) ? $parent_styles['elements'] : array();

	$child_blocks = isset( $child_styles['blocks'] ) && is_array( $child_styles['blocks'] ) ? $child_styles['blocks'] : array();
	$parent_blocks = isset( $parent_styles['blocks'] ) && is_array( $parent_styles['blocks'] ) ? $parent_styles['blocks'] : array();

	$child_core_heading = isset( $child_blocks['core/heading'] ) && is_array( $child_blocks['core/heading'] ) ? $child_blocks['core/heading'] : array();
	$parent_core_heading = isset( $parent_blocks['core/heading'] ) && is_array( $parent_blocks['core/heading'] ) ? $parent_blocks['core/heading'] : array();
	$child_core_heading_elements = isset( $child_core_heading['elements'] ) && is_array( $child_core_heading['elements'] ) ? $child_core_heading['elements'] : array();
	$parent_core_heading_elements = isset( $parent_core_heading['elements'] ) && is_array( $parent_core_heading['elements'] ) ? $parent_core_heading['elements'] : array();

	$child_core_paragraph = isset( $child_blocks['core/paragraph'] ) && is_array( $child_blocks['core/paragraph'] ) ? $child_blocks['core/paragraph'] : array();
	$parent_core_paragraph = isset( $parent_blocks['core/paragraph'] ) && is_array( $parent_blocks['core/paragraph'] ) ? $parent_blocks['core/paragraph'] : array();

	$global_typography = elodin_bridge_merge_theme_json_typography_layers(
		array(
			elodin_bridge_normalize_theme_json_typography_declarations( $parent_styles['typography'] ?? array() ),
			elodin_bridge_normalize_theme_json_typography_declarations( $child_styles['typography'] ?? array() ),
		)
	);
	$heading_base_typography = elodin_bridge_merge_theme_json_typography_layers(
		array(
			$global_typography,
			elodin_bridge_merge_theme_json_typography_layers(
				array(
					elodin_bridge_normalize_theme_json_typography_declarations( $parent_elements['heading']['typography'] ?? array() ),
					elodin_bridge_normalize_theme_json_typography_declarations( $child_elements['heading']['typography'] ?? array() ),
				)
			),
			elodin_bridge_merge_theme_json_typography_layers(
				array(
					elodin_bridge_normalize_theme_json_typography_declarations( $parent_core_heading['typography'] ?? array() ),
					elodin_bridge_normalize_theme_json_typography_declarations( $child_core_heading['typography'] ?? array() ),
				)
			),
		)
	);

	$paragraph_typography = elodin_bridge_merge_theme_json_typography_layers(
		array(
			$global_typography,
			elodin_bridge_merge_theme_json_typography_layers(
				array(
					elodin_bridge_normalize_theme_json_typography_declarations( $parent_elements['p']['typography'] ?? array() ),
					elodin_bridge_normalize_theme_json_typography_declarations( $child_elements['p']['typography'] ?? array() ),
				)
			),
			elodin_bridge_merge_theme_json_typography_layers(
				array(
					elodin_bridge_normalize_theme_json_typography_declarations( $parent_core_paragraph['typography'] ?? array() ),
					elodin_bridge_normalize_theme_json_typography_declarations( $child_core_paragraph['typography'] ?? array() ),
				)
			),
		)
	);

	$presets = array();
	if ( ! empty( $paragraph_typography ) ) {
		$presets['p'] = $paragraph_typography;
	}

	for ( $level = 1; $level <= 6; $level++ ) {
		$key = 'h' . (string) $level;
		$heading_typography = elodin_bridge_merge_theme_json_typography_layers(
			array(
				$heading_base_typography,
				elodin_bridge_merge_theme_json_typography_layers(
					array(
						elodin_bridge_normalize_theme_json_typography_declarations( $parent_elements[ $key ]['typography'] ?? array() ),
						elodin_bridge_normalize_theme_json_typography_declarations( $child_elements[ $key ]['typography'] ?? array() ),
					)
				),
				elodin_bridge_merge_theme_json_typography_layers(
					array(
						elodin_bridge_normalize_theme_json_typography_declarations( $parent_core_heading_elements[ $key ]['typography'] ?? array() ),
						elodin_bridge_normalize_theme_json_typography_declarations( $child_core_heading_elements[ $key ]['typography'] ?? array() ),
					)
				),
			)
		);

		if ( ! empty( $heading_typography ) ) {
			$presets[ $key ] = $heading_typography;
		}
	}

	$cached_presets = $presets;
	return $cached_presets;
}

/**
 * Build toolbar controls for heading/paragraph style overrides.
 *
 * @return array<int,array{className:string,label:string}>
 */
function elodin_bridge_get_heading_paragraph_type_override_controls() {
	$presets = elodin_bridge_get_heading_paragraph_typography_presets();
	if ( empty( $presets ) ) {
		return array();
	}

	$definitions = array(
		'p'  => __( 'Paragraph style', 'elodin-bridge' ),
		'h1' => __( 'H1 style', 'elodin-bridge' ),
		'h2' => __( 'H2 style', 'elodin-bridge' ),
		'h3' => __( 'H3 style', 'elodin-bridge' ),
		'h4' => __( 'H4 style', 'elodin-bridge' ),
		'h5' => __( 'H5 style', 'elodin-bridge' ),
		'h6' => __( 'H6 style', 'elodin-bridge' ),
	);

	$controls = array();
	foreach ( $definitions as $class_name => $label ) {
		if ( empty( $presets[ $class_name ] ) ) {
			continue;
		}

		$controls[] = array(
			'className' => $class_name,
			'label'     => $label,
		);
	}

	return $controls;
}

/**
 * Build a CSS rule from declarations.
 *
 * @param string               $selector     CSS selector.
 * @param array<string,string> $declarations CSS declarations.
 * @return string
 */
function elodin_bridge_build_heading_paragraph_override_css_rule( $selector, $declarations ) {
	if ( empty( $declarations ) ) {
		return '';
	}

	$selector = trim( (string) $selector );
	if ( '' === $selector ) {
		return '';
	}

	$rule = $selector . '{';
	foreach ( $declarations as $property => $value ) {
		$property = trim( (string) $property );
		$value = trim( (string) $value );
		if ( '' === $property || '' === $value ) {
			continue;
		}

		$rule .= $property . ':' . $value . ';';
	}
	$rule .= '}';

	return $rule;
}

/**
 * Build heading/paragraph override CSS from theme.json typography styles.
 *
 * @return string
 */
function elodin_bridge_build_heading_paragraph_override_css() {
	$presets = elodin_bridge_get_heading_paragraph_typography_presets();
	if ( empty( $presets ) ) {
		return '';
	}

	$css = ':where(p,h1,h2,h3,h4,h5,h6).h1,:where(p,h1,h2,h3,h4,h5,h6).h2,:where(p,h1,h2,h3,h4,h5,h6).h3,:where(p,h1,h2,h3,h4,h5,h6).h4,:where(p,h1,h2,h3,h4,h5,h6).h5,:where(p,h1,h2,h3,h4,h5,h6).h6{margin-top:0;}';
	$source_selector = ':where(p,h1,h2,h3,h4,h5,h6)';

	foreach ( $presets as $class_name => $declarations ) {
		$class_name = sanitize_html_class( $class_name );
		if ( '' === $class_name ) {
			continue;
		}

		$font_size_value = '';
		if ( isset( $declarations['font-size'] ) ) {
			$font_size_value = trim( (string) $declarations['font-size'] );
			unset( $declarations['font-size'] );
		}

		$css .= elodin_bridge_build_heading_paragraph_override_css_rule(
			$source_selector . '.' . $class_name,
			$declarations
		);

		// Keep font-size as a default only: if a local block font-size class exists,
		// do not force the preset class-size from this feature.
		if ( '' !== $font_size_value ) {
			$css .= elodin_bridge_build_heading_paragraph_override_css_rule(
				$source_selector . '.' . $class_name . ':not([class*="-font-size"])',
				array(
					'font-size' => $font_size_value,
				)
			);
		}
	}

	return $css;
}

/**
 * Enqueue heading/paragraph override styles on front-end and in block editor content.
 */
function elodin_bridge_enqueue_heading_paragraph_override_styles() {
	if ( ! elodin_bridge_is_heading_paragraph_overrides_enabled() ) {
		return;
	}

	$css = elodin_bridge_build_heading_paragraph_override_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-heading-paragraph-overrides';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_heading_paragraph_override_styles' );

/**
 * Enqueue heading/paragraph override controls in the block toolbar.
 */
function elodin_bridge_enqueue_editor_heading_paragraph_overrides_toolbar() {
	if ( ! elodin_bridge_is_heading_paragraph_overrides_enabled() ) {
		return;
	}

	$controls = elodin_bridge_get_heading_paragraph_type_override_controls();
	if ( empty( $controls ) ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-heading-paragraph-overrides.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/editor-heading-paragraph-overrides.js';
	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-heading-paragraph-overrides.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/editor-heading-paragraph-overrides.css';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-heading-paragraph-overrides',
		$script_url,
		array( 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-i18n' ),
		(string) filemtime( $script_path ),
		true
	);
	wp_add_inline_script(
		'elodin-bridge-editor-heading-paragraph-overrides',
		'window.elodinBridgeHeadingParagraphOverrides = ' . wp_json_encode(
			array(
				'typeOverrideControls' => $controls,
			)
		) . ';',
		'before'
	);

	if ( file_exists( $style_path ) ) {
		wp_enqueue_style(
			'elodin-bridge-editor-heading-paragraph-overrides',
			$style_url,
			array(),
			(string) filemtime( $style_path )
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_heading_paragraph_overrides_toolbar' );
