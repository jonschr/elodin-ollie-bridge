<?php

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get default checkerboard control values.
 *
 * @return array<string,int|float|string>
 */
function elodin_bridge_get_checkerboard_pattern_defaults() {
	return array(
		'localContentWidth'        => 'var(--wp--style--global--wide-size)',
		'sectionPaddingHorizontal' => 'var:preset|spacing|x-large',
		'sectionPaddingVertical'   => 'var:preset|spacing|x-large',
		'widthRatioLeft'           => 0.5,
		'widthRatioRight'          => 0.5,
	);
}

/**
 * Get the custom checkerboard attributes added to the Cover block.
 *
 * @return array<string,array<string,int|float|string>>
 */
function elodin_bridge_get_checkerboard_pattern_attribute_schema() {
	$defaults = elodin_bridge_get_checkerboard_pattern_defaults();

	return array(
		'elodinCheckerboardLocalContentWidth'        => array(
			'type'    => 'string',
			'default' => $defaults['localContentWidth'],
		),
		'elodinCheckerboardSectionPaddingHorizontal' => array(
			'type'    => 'string',
			'default' => $defaults['sectionPaddingHorizontal'],
		),
		'elodinCheckerboardSectionPaddingVertical'   => array(
			'type'    => 'string',
			'default' => $defaults['sectionPaddingVertical'],
		),
		'elodinCheckerboardWidthRatioLeft'           => array(
			'type'    => 'number',
			'default' => $defaults['widthRatioLeft'],
		),
		'elodinCheckerboardWidthRatioRight'          => array(
			'type'    => 'number',
			'default' => $defaults['widthRatioRight'],
		),
	);
}

/**
 * Add checkerboard attributes to the core Cover block registration.
 *
 * @param array<string,mixed> $args Block type args.
 * @param string              $block_type Block type name.
 * @return array<string,mixed>
 */
function elodin_bridge_register_checkerboard_cover_attributes( $args, $block_type ) {
	if ( 'core/cover' !== $block_type ) {
		return $args;
	}

	$args['attributes'] = array_merge(
		is_array( $args['attributes'] ?? null ) ? $args['attributes'] : array(),
		elodin_bridge_get_checkerboard_pattern_attribute_schema()
	);

	return $args;
}
add_filter( 'register_block_type_args', 'elodin_bridge_register_checkerboard_cover_attributes', 10, 2 );

/**
 * Sanitize a checkerboard CSS value for runtime use.
 *
 * @param mixed  $value Raw value.
 * @param string $fallback Fallback value.
 * @return string
 */
function elodin_bridge_sanitize_checkerboard_css_value( $value, $fallback ) {
	return elodin_bridge_sanitize_css_value( $value, $fallback );
}

/**
 * Convert a checkerboard spacing token or raw CSS value to a runtime CSS value.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function elodin_bridge_get_checkerboard_spacing_css_value( $value ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $value ) {
		return '';
	}

	if ( preg_match( '/^var:preset\|spacing\|([a-z0-9-]+)$/i', $value, $matches ) ) {
		return 'var(--wp--preset--spacing--' . $matches[1] . ')';
	}

	return elodin_bridge_sanitize_css_value( $value, '' );
}

/**
 * Normalize a checkerboard spacing value with fallback.
 *
 * @param mixed  $value Raw value.
 * @param string $fallback Fallback value.
 * @return string
 */
function elodin_bridge_normalize_checkerboard_spacing_value( $value, $fallback ) {
	$normalized = elodin_bridge_get_checkerboard_spacing_css_value( $value );
	if ( '' !== $normalized ) {
		return $normalized;
	}

	return elodin_bridge_get_checkerboard_spacing_css_value( $fallback );
}

/**
 * Sanitize a checkerboard ratio value for runtime use.
 *
 * @param mixed $value Raw value.
 * @param float $fallback Fallback value.
 * @return float
 */
function elodin_bridge_sanitize_checkerboard_ratio_value( $value, $fallback ) {
	$value = is_numeric( $value ) ? (float) $value : $fallback;

	if ( $value < 0 ) {
		return 0.0;
	}

	if ( $value > 1 ) {
		return 1.0;
	}

	return $value;
}

/**
 * Get checkerboard CSS variable overrides from Cover block attributes.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return array<string,string>
 */
function elodin_bridge_get_checkerboard_style_overrides( $attributes ) {
	$attributes = is_array( $attributes ) ? $attributes : array();
	$defaults = elodin_bridge_get_checkerboard_pattern_defaults();

	$values = array(
		'--local-content-width'        => elodin_bridge_sanitize_checkerboard_css_value(
			$attributes['elodinCheckerboardLocalContentWidth'] ?? '',
			(string) $defaults['localContentWidth']
		),
		'--section-padding-horizontal' => elodin_bridge_normalize_checkerboard_spacing_value(
			$attributes['elodinCheckerboardSectionPaddingHorizontal'] ?? '',
			(string) $defaults['sectionPaddingHorizontal']
		),
		'--section-padding-vertical'   => elodin_bridge_normalize_checkerboard_spacing_value(
			$attributes['elodinCheckerboardSectionPaddingVertical'] ?? '',
			(string) $defaults['sectionPaddingVertical']
		),
		'--width-ratio-left'           => (string) elodin_bridge_sanitize_checkerboard_ratio_value(
			$attributes['elodinCheckerboardWidthRatioLeft'] ?? null,
			(float) $defaults['widthRatioLeft']
		),
		'--width-ratio-right'          => (string) elodin_bridge_sanitize_checkerboard_ratio_value(
			$attributes['elodinCheckerboardWidthRatioRight'] ?? null,
			(float) $defaults['widthRatioRight']
		),
	);

	$default_values = array(
		'--local-content-width'        => elodin_bridge_sanitize_checkerboard_css_value(
			$defaults['localContentWidth'],
			''
		),
		'--section-padding-horizontal' => elodin_bridge_normalize_checkerboard_spacing_value(
			$defaults['sectionPaddingHorizontal'],
			''
		),
		'--section-padding-vertical'   => elodin_bridge_normalize_checkerboard_spacing_value(
			$defaults['sectionPaddingVertical'],
			''
		),
		'--width-ratio-left'           => (string) elodin_bridge_sanitize_checkerboard_ratio_value(
			$defaults['widthRatioLeft'],
			(float) $defaults['widthRatioLeft']
		),
		'--width-ratio-right'          => (string) elodin_bridge_sanitize_checkerboard_ratio_value(
			$defaults['widthRatioRight'],
			(float) $defaults['widthRatioRight']
		),
	);

	$overrides = array();
	foreach ( $values as $property => $value ) {
		if ( ! isset( $default_values[ $property ] ) || $value === $default_values[ $property ] ) {
			continue;
		}

		$overrides[ $property ] = $value;
	}

	return $overrides;
}

/**
 * Inject checkerboard CSS variable overrides into the rendered Cover block.
 *
 * @param string               $block_content Rendered block content.
 * @param array<string,mixed>  $block Parsed block data.
 * @return string
 */
function elodin_bridge_render_checkerboard_cover_block( $block_content, $block ) {
	if ( ! is_string( $block_content ) || '' === $block_content ) {
		return $block_content;
	}

	$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
	$class_name = (string) ( $attrs['className'] ?? '' );
	if ( false === strpos( ' ' . $class_name . ' ', ' checkerboard ' ) ) {
		return $block_content;
	}

	$overrides = elodin_bridge_get_checkerboard_style_overrides( $attrs );
	if ( empty( $overrides ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( 'div' ) ) {
		return $block_content;
	}

	$existing_style = trim( (string) $processor->get_attribute( 'style' ) );
	$style_fragments = array();
	if ( '' !== $existing_style ) {
		$style_fragments[] = rtrim( $existing_style, ';' );
	}

	foreach ( $overrides as $property => $value ) {
		$style_fragments[] = $property . ':' . $value;
	}

	$processor->set_attribute( 'style', implode( ';', $style_fragments ) . ';' );

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/cover', 'elodin_bridge_render_checkerboard_cover_block', 10, 2 );

/**
 * Get checkerboard pattern content.
 *
 * @return string
 */
function elodin_bridge_get_checkerboard_pattern_content() {
	return <<<'HTML'
<!-- wp:cover {"dimRatio":0,"isUserOverlayColor":true,"isDark":false,"sizeSlug":"large","align":"full","className":"checkerboard","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull is-light checkerboard" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:columns {"align":"full","style":{"spacing":{"blockGap":{"left":"0"},"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-columns alignfull" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:column {"className":"checkerboard__column"} -->
<div class="wp-block-column checkerboard__column"><!-- wp:cover {"dimRatio":0,"isUserOverlayColor":true,"contentPosition":"top center","isDark":false,"sizeSlug":"large","className":"checkerboard__cover-background","layout":{"type":"default"}} -->
<div class="wp-block-cover is-light has-custom-content-position is-position-top-center checkerboard__cover-background"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"content-area content-area-a checkerboard__content-area","layout":{"type":"default"}} -->
<div class="wp-block-group content-area content-area-a checkerboard__content-area"><!-- wp:heading -->
<h2 class="wp-block-heading">This is a heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Cras mattis consectetur purus sit amet fermentum. Etiam porta sem malesuada magna mollis euismod. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Nulla vitae elit libero, a pharetra augue. Nullam quis risus eget urna mollis ornare vel eu leo.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column {"backgroundColor":"tertiary","className":"checkerboard__column"} -->
<div class="wp-block-column checkerboard__column has-tertiary-background-color has-background"><!-- wp:cover {"dimRatio":0,"isUserOverlayColor":true,"contentPosition":"top center","isDark":false,"sizeSlug":"large","className":"checkerboard__cover-background","layout":{"type":"default"}} -->
<div class="wp-block-cover is-light has-custom-content-position is-position-top-center checkerboard__cover-background"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"checkerboard__content-area","layout":{"type":"default"}} -->
<div class="wp-block-group checkerboard__content-area"><!-- wp:heading -->
<h2 class="wp-block-heading">This is a heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Cras mattis consectetur purus sit amet fermentum. Etiam porta sem malesuada magna mollis euismod. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Nulla vitae elit libero, a pharetra augue. Nullam quis risus eget urna mollis ornare vel eu leo.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></div>
<!-- /wp:cover -->
HTML;
}

/**
 * Register checkerboard pattern category and pattern.
 */
function elodin_bridge_register_checkerboard_pattern() {
	if ( ! function_exists( 'register_block_pattern' ) || ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category(
		'elodin-ollie-bridge',
		array(
			'label' => __( 'Elodin Ollie Bridge', 'elodin-bridge' ),
		)
	);

	if ( ! elodin_bridge_is_checkerboard_pattern_enabled() ) {
		return;
	}

	register_block_pattern(
		'elodin-ollie-bridge/checkerboard',
		array(
			'title'       => __( 'Checkerboard', 'elodin-bridge' ),
			'description' => __( 'Two-column checkerboard cover layout with parent block controls for content width, section padding, and column ratios.', 'elodin-bridge' ),
			'categories'  => array( 'elodin-ollie-bridge' ),
			'keywords'    => array(
				__( 'checkerboard', 'elodin-bridge' ),
				__( 'cover', 'elodin-bridge' ),
				__( 'columns', 'elodin-bridge' ),
			),
			'content'     => elodin_bridge_get_checkerboard_pattern_content(),
		)
	);
}
add_action( 'init', 'elodin_bridge_register_checkerboard_pattern', 20 );

/**
 * Enqueue checkerboard styles for front-end and editor content.
 */
function elodin_bridge_enqueue_checkerboard_pattern_styles() {
	$style_path = ELODIN_BRIDGE_DIR . '/assets/checkerboard-pattern.css';
	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-checkerboard-pattern',
		ELODIN_BRIDGE_URL . 'assets/checkerboard-pattern.css',
		array(),
		(string) filemtime( $style_path )
	);
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_checkerboard_pattern_styles' );

/**
 * Enqueue checkerboard editor controls.
 */
function elodin_bridge_enqueue_checkerboard_pattern_editor_assets() {
	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-checkerboard-pattern.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-checkerboard-pattern',
		ELODIN_BRIDGE_URL . 'assets/editor-checkerboard-pattern.js',
		array(
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
		),
		(string) filemtime( $script_path ),
		true
	);

	wp_add_inline_script(
		'elodin-bridge-editor-checkerboard-pattern',
		'window.elodinBridgeCheckerboardPattern = ' . wp_json_encode(
			array(
				'className' => 'checkerboard',
				'defaults'  => elodin_bridge_get_checkerboard_pattern_defaults(),
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_checkerboard_pattern_editor_assets' );
