<?php

/**
 * Build child theme-only gradient and duotone presets from palette colors.
 *
 * This runs only when the child theme's theme.json does not define those groups.
 */
function elodin_bridge_get_child_theme_theme_json_data() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array();
	$stylesheet_directory = get_stylesheet_directory();
	if ( ! is_string( $stylesheet_directory ) || '' === $stylesheet_directory ) {
		return $cache;
	}

	$theme_json_file = trailingslashit( $stylesheet_directory ) . 'theme.json';
	if ( ! is_readable( $theme_json_file ) ) {
		return $cache;
	}

	$decoded = wp_json_file_decode( $theme_json_file, array( 'associative' => true ) );
	if ( ! is_array( $decoded ) ) {
		return $cache;
	}

	$cache = $decoded;
	return $cache;
}

/**
 * Read palette colors defined in the child theme's theme.json.
 *
 * @return array<int,array{name:string,slug:string,color:string}>
 */
function elodin_bridge_get_child_theme_palette_from_theme_json() {
	$theme_data = elodin_bridge_get_child_theme_theme_json_data();
	if ( ! isset( $theme_data['settings']['color']['palette'] ) || ! is_array( $theme_data['settings']['color']['palette'] ) ) {
		return array();
	}

	return elodin_bridge_sanitize_theme_json_color_palette( $theme_data['settings']['color']['palette'] );
}

/**
 * Build color lookup map by palette slug.
 *
 * @return array{palette_by_slug:array<string,array{name:string,color:string}>}
 */
function elodin_bridge_build_child_theme_palette_lookup_maps() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array(
		'palette_by_slug' => array(),
	);

	$palette = elodin_bridge_get_child_theme_palette_from_theme_json();
	if ( empty( $palette ) ) {
		return $cache;
	}

	foreach ( $palette as $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}

		$slug = sanitize_key( $entry['slug'] ?? '' );
		$color = sanitize_text_field( $entry['color'] ?? '' );
		if ( '' === $slug || '' === $color ) {
			continue;
		}

		$cache['palette_by_slug'][ $slug ] = array(
			'name'  => sanitize_text_field( $entry['name'] ?? $slug ),
			'color' => $color,
		);
	}

	return $cache;
}

/**
 * Child-theme gradient defaults sourced from plugin memory (Ollie default slugs).
 *
 * @return array<int,array{name:string,slug:string,from:string,to:string,angle:string}>
 */
function elodin_bridge_get_child_theme_default_gradient_pairs() {
	return array(
		array(
			'name'  => __( 'Brand Accent to Brand Alt', 'elodin-bridge' ),
			'slug'  => 'primary-accent-to-primary-alt',
			'from'  => 'primary-accent',
			'to'    => 'primary-alt',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Brand Accent to Tint', 'elodin-bridge' ),
			'slug'  => 'primary-accent-to-tertiary',
			'from'  => 'primary-accent',
			'to'    => 'tertiary',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Brand Accent to Base', 'elodin-bridge' ),
			'slug'  => 'primary-accent-to-base',
			'from'  => 'primary-accent',
			'to'    => 'base',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Brand Alt to Tint', 'elodin-bridge' ),
			'slug'  => 'primary-alt-to-tertiary',
			'from'  => 'primary-alt',
			'to'    => 'tertiary',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Brand Alt to Base', 'elodin-bridge' ),
			'slug'  => 'primary-alt-to-base',
			'from'  => 'primary-alt',
			'to'    => 'base',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Tint to Base', 'elodin-bridge' ),
			'slug'  => 'tertiary-to-base',
			'from'  => 'tertiary',
			'to'    => 'base',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Brand to Contrast', 'elodin-bridge' ),
			'slug'  => 'primary-to-main',
			'from'  => 'primary',
			'to'    => 'main',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Brand to Brand Alt Accent', 'elodin-bridge' ),
			'slug'  => 'primary-to-primary-alt-accent',
			'from'  => 'primary',
			'to'    => 'primary-alt-accent',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Contrast to Brand Alt Accent', 'elodin-bridge' ),
			'slug'  => 'main-to-primary-alt-accent',
			'from'  => 'main',
			'to'    => 'primary-alt-accent',
			'angle' => '325deg',
		),
		array(
			'name'  => __( 'Contrast to Tint Accent', 'elodin-bridge' ),
			'slug'  => 'main-to-tertiary-accent',
			'from'  => 'main',
			'to'    => 'tertiary-accent',
			'angle' => '325deg',
		),
	);
}

/**
 * Child-theme duotone defaults sourced from plugin memory (Ollie default slugs).
 *
 * @return array<int,array{name:string,slug:string,from:string,to:string}>
 */
function elodin_bridge_get_child_theme_default_duotone_pairs() {
	return array(
		array(
			'name' => __( 'Brand / Brand Accent', 'elodin-bridge' ),
			'slug' => 'brand-brand-accent',
			'from' => 'primary',
			'to'   => 'primary-accent',
		),
		array(
			'name' => __( 'Brand / Brand Alt', 'elodin-bridge' ),
			'slug' => 'brand-brand-alt',
			'from' => 'primary',
			'to'   => 'primary-alt',
		),
		array(
			'name' => __( 'Brand Accent / Brand Alt Accent', 'elodin-bridge' ),
			'slug' => 'brand-accent-brand-alt-accent',
			'from' => 'primary-accent',
			'to'   => 'primary-alt-accent',
		),
		array(
			'name' => __( 'Brand Alt / Brand Alt Accent', 'elodin-bridge' ),
			'slug' => 'brand-alt-brand-alt-accent',
			'from' => 'primary-alt',
			'to'   => 'primary-alt-accent',
		),
		array(
			'name' => __( 'Contrast / Contrast Accent', 'elodin-bridge' ),
			'slug' => 'contrast-contrast-accent',
			'from' => 'main',
			'to'   => 'main-accent',
		),
		array(
			'name' => __( 'Contrast Accent / Tint', 'elodin-bridge' ),
			'slug' => 'contrast-accent-tint',
			'from' => 'main-accent',
			'to'   => 'tertiary',
		),
		array(
			'name' => __( 'Contrast / Brand', 'elodin-bridge' ),
			'slug' => 'contrast-brand',
			'from' => 'main',
			'to'   => 'primary',
		),
		array(
			'name' => __( 'Contrast / Brand Alt', 'elodin-bridge' ),
			'slug' => 'contrast-brand-alt',
			'from' => 'main',
			'to'   => 'primary-alt',
		),
		array(
			'name' => __( 'Contrast / Base Accent', 'elodin-bridge' ),
			'slug' => 'contrast-base-accent',
			'from' => 'main',
			'to'   => 'secondary',
		),
		array(
			'name' => __( 'Contrast / Base', 'elodin-bridge' ),
			'slug' => 'contrast-base',
			'from' => 'main',
			'to'   => 'base',
		),
		array(
			'name' => __( 'Base Accent / Base', 'elodin-bridge' ),
			'slug' => 'base-base-accent',
			'from' => 'secondary',
			'to'   => 'base',
		),
		array(
			'name' => __( 'Border Base / Border Contrast', 'elodin-bridge' ),
			'slug' => 'border-base-border-contrast',
			'from' => 'border-light',
			'to'   => 'border-dark',
		),
	);
}

/**
 * Check whether the child theme theme.json defines gradient presets.
 *
 * @param array<string,mixed> $theme_data Decoded theme.json data.
 * @return bool
 */
function elodin_bridge_child_theme_has_gradient_presets( $theme_data ) {
	if ( ! is_array( $theme_data ) ) {
		return false;
	}

	$settings = $theme_data['settings'] ?? array();
	if ( ! is_array( $settings ) ) {
		return false;
	}

	$color_settings = $settings['color'] ?? array();
	if ( ! is_array( $color_settings ) ) {
		return false;
	}

	return ! empty( $color_settings['gradients'] ) && is_array( $color_settings['gradients'] );
}

/**
 * Check whether the child theme theme.json defines duotone presets.
 *
 * @param array<string,mixed> $theme_data Decoded theme.json data.
 * @return bool
 */
function elodin_bridge_child_theme_has_duotone_presets( $theme_data ) {
	if ( ! is_array( $theme_data ) ) {
		return false;
	}

	$settings = $theme_data['settings'] ?? array();
	if ( ! is_array( $settings ) ) {
		return false;
	}

	$color_settings = $settings['color'] ?? array();
	if ( ! is_array( $color_settings ) ) {
		return false;
	}

	return ! empty( $color_settings['duotone'] ) && is_array( $color_settings['duotone'] );
}

/**
 * Check if a palette color value is variable-based instead of a concrete color.
 *
 * @param mixed $value Raw palette color value.
 * @return bool
 */
function elodin_bridge_is_variable_palette_color_value( $value ) {
	$value = strtolower( trim( (string) $value ) );
	if ( '' === $value ) {
		return false;
	}

	return 0 === strpos( $value, 'var(' )
		|| 0 === strpos( $value, 'var:preset|' )
		|| 0 === strpos( $value, 'var:custom|' );
}

/**
 * Resolve a child-theme palette slug to a concrete color value.
 *
 * Resolves simple references to other palette slugs and rejects unresolved
 * variable-based values.
 *
 * @param string                           $slug            Palette slug to resolve.
 * @param array<string,array{name:string,color:string}> $palette_by_slug Palette lookup map.
 * @param array<string,bool>               $visited         Internal recursion guard.
 * @return string
 */
function elodin_bridge_resolve_child_theme_palette_color_value( $slug, $palette_by_slug, $visited = array() ) {
	$slug = sanitize_key( $slug );
	if ( '' === $slug || isset( $visited[ $slug ] ) ) {
		return '';
	}

	if ( ! is_array( $palette_by_slug ) || ! isset( $palette_by_slug[ $slug ]['color'] ) ) {
		return '';
	}

	$visited[ $slug ] = true;

	$raw_color = $palette_by_slug[ $slug ]['color'];
	$color = function_exists( 'elodin_bridge_sanitize_theme_json_css_value' )
		? elodin_bridge_sanitize_theme_json_css_value( $raw_color )
		: sanitize_text_field( (string) $raw_color );
	if ( '' === $color ) {
		return '';
	}

	if ( ! elodin_bridge_is_variable_palette_color_value( $color ) ) {
		return $color;
	}

	if ( preg_match( '/^var:preset\|color\|([a-zA-Z0-9-]+)$/i', $color, $matches ) ) {
		return elodin_bridge_resolve_child_theme_palette_color_value( $matches[1], $palette_by_slug, $visited );
	}

	if ( preg_match( '/^var\(\s*--wp--preset--color--([a-zA-Z0-9-]+)\s*(?:,[^)]+)?\)$/i', $color, $matches ) ) {
		return elodin_bridge_resolve_child_theme_palette_color_value( $matches[1], $palette_by_slug, $visited );
	}

	return '';
}

/**
 * Build gradient presets from configured palette color pairs.
 *
 * @param array<int,array{name:string,slug:string,from:string,to:string,angle:string}> $palette_pairs Pair definitions.
 * @param array<string,array{name:string,color:string}>                                 $palette_by_slug Palette lookup map.
 * @return array<int,array{name:string,slug:string,gradient:string}>
 */
function elodin_bridge_build_child_theme_gradient_presets( $palette_pairs, $palette_by_slug ) {
	$gradient_presets = array();
	$seen_slugs = array();
	if ( empty( $palette_pairs ) ) {
		return $gradient_presets;
	}

	foreach ( $palette_pairs as $pair ) {
		if ( ! is_array( $pair ) || empty( $pair['from'] ) || empty( $pair['to'] ) ) {
			continue;
		}

		$from_slug = sanitize_key( $pair['from'] );
		$to_slug   = sanitize_key( $pair['to'] );
		if ( '' === $from_slug || '' === $to_slug ) {
			continue;
		}
		if ( ! isset( $palette_by_slug[ $from_slug ] ) || ! isset( $palette_by_slug[ $to_slug ] ) ) {
			continue;
		}
		$from_color = elodin_bridge_resolve_child_theme_palette_color_value( $from_slug, $palette_by_slug );
		$to_color   = elodin_bridge_resolve_child_theme_palette_color_value( $to_slug, $palette_by_slug );
		if ( '' === $from_color || '' === $to_color ) {
			continue;
		}

		$slug = ! empty( $pair['slug'] ) ? sanitize_title( $pair['slug'] ) : sanitize_title( $from_slug . '-to-' . $to_slug );
		if ( isset( $seen_slugs[ $slug ] ) ) {
			continue;
		}
		$seen_slugs[ $slug ] = true;

		$from_name = isset( $palette_by_slug[ $from_slug ]['name'] ) && '' !== $palette_by_slug[ $from_slug ]['name']
			? $palette_by_slug[ $from_slug ]['name']
			: ucwords( str_replace( '-', ' ', $from_slug ) );
		$to_name   = isset( $palette_by_slug[ $to_slug ]['name'] ) && '' !== $palette_by_slug[ $to_slug ]['name']
			? $palette_by_slug[ $to_slug ]['name']
			: ucwords( str_replace( '-', ' ', $to_slug ) );
		$angle = ! empty( $pair['angle'] ) ? sanitize_text_field( (string) $pair['angle'] ) : '90deg';
		if ( function_exists( 'elodin_bridge_sanitize_theme_json_css_value' ) ) {
			$angle = elodin_bridge_sanitize_theme_json_css_value( $angle );
		}
		if ( '' === $angle ) {
			$angle = '90deg';
		}

		$gradient_presets[] = array(
			'name'     => sprintf(
				/* translators: %1$s: color name, %2$s: color name */
				__( '%1$s to %2$s', 'elodin-bridge' ),
				$from_name,
				$to_name
			),
			'slug'     => $slug,
			'gradient' => sprintf(
				'linear-gradient( %3$s, %1$s, %2$s )',
				$from_color,
				$to_color,
				$angle
			),
		);
	}

	return $gradient_presets;
}

/**
 * Build duotone presets from configured palette color pairs.
 *
 * @param array<int,array{name:string,slug:string,from:string,to:string}> $palette_pairs Pair definitions.
 * @param array<string,array{name:string,color:string}>                   $palette_by_slug Palette lookup map.
 * @return array<int,array{name:string,slug:string,colors:array<int,string>}>
 */
function elodin_bridge_build_child_theme_duotone_presets( $palette_pairs, $palette_by_slug ) {
	$duotone_presets = array();
	$seen_slugs = array();
	if ( empty( $palette_pairs ) ) {
		return $duotone_presets;
	}

	foreach ( $palette_pairs as $pair ) {
		if ( ! is_array( $pair ) || empty( $pair['from'] ) || empty( $pair['to'] ) ) {
			continue;
		}

		$from_slug = sanitize_key( $pair['from'] );
		$to_slug   = sanitize_key( $pair['to'] );
		if ( '' === $from_slug || '' === $to_slug ) {
			continue;
		}
		if ( ! isset( $palette_by_slug[ $from_slug ] ) || ! isset( $palette_by_slug[ $to_slug ] ) ) {
			continue;
		}
		$from_color = elodin_bridge_resolve_child_theme_palette_color_value( $from_slug, $palette_by_slug );
		$to_color   = elodin_bridge_resolve_child_theme_palette_color_value( $to_slug, $palette_by_slug );
		if ( '' === $from_color || '' === $to_color ) {
			continue;
		}

		$slug = ! empty( $pair['slug'] ) ? sanitize_title( $pair['slug'] ) : sanitize_title( $from_slug . '-with-' . $to_slug );
		if ( isset( $seen_slugs[ $slug ] ) ) {
			continue;
		}
		$seen_slugs[ $slug ] = true;

		$from_name = isset( $palette_by_slug[ $from_slug ]['name'] ) && '' !== $palette_by_slug[ $from_slug ]['name']
			? $palette_by_slug[ $from_slug ]['name']
			: ucwords( str_replace( '-', ' ', $from_slug ) );
		$to_name   = isset( $palette_by_slug[ $to_slug ]['name'] ) && '' !== $palette_by_slug[ $to_slug ]['name']
			? $palette_by_slug[ $to_slug ]['name']
			: ucwords( str_replace( '-', ' ', $to_slug ) );

		$duotone_presets[] = array(
			'name'   => sprintf(
				/* translators: %1$s: color name, %2$s: color name */
				__( '%1$s with %2$s', 'elodin-bridge' ),
				$from_name,
				$to_name
			),
			'slug'  => $slug,
			'colors' => array(
				$from_color,
				$to_color,
			),
		);
	}

	return $duotone_presets;
}

/**
 * Merge existing presets with generated defaults without duplicating slugs.
 *
 * Existing presets are preserved when slugs match generated presets so backend
 * edits stay intact.
 *
 * @param array<int,mixed> $existing_presets Presets from incoming theme JSON data.
 * @param array<int,mixed> $generated_presets Presets generated by the plugin.
 * @return array<int,array>
 */
function elodin_bridge_merge_child_theme_preset_arrays( $existing_presets, $generated_presets ) {
	$merged = array();

	if ( is_array( $existing_presets ) ) {
		foreach ( $existing_presets as $preset ) {
			if ( ! is_array( $preset ) || ! isset( $preset['slug'] ) || ! is_string( $preset['slug'] ) ) {
				continue;
			}

			$slug = sanitize_title( $preset['slug'] );
			if ( '' === $slug ) {
				continue;
			}
			if ( ! isset( $merged[ $slug ] ) ) {
				$merged[ $slug ] = $preset;
			}
		}
	}

	if ( is_array( $generated_presets ) ) {
		foreach ( $generated_presets as $preset ) {
			if ( ! is_array( $preset ) || ! isset( $preset['slug'] ) || ! is_string( $preset['slug'] ) ) {
				continue;
			}

			$slug = sanitize_title( $preset['slug'] );
			if ( '' === $slug || isset( $merged[ $slug ] ) ) {
				continue;
			}
			$merged[ $slug ] = $preset;
		}
	}

	return array_values( $merged );
}

/**
 * Inject generated gradient and duotone presets into theme JSON when missing in child theme.
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data object.
 * @return WP_Theme_JSON_Data
 */
function elodin_bridge_maybe_inject_child_theme_gradient_duotone_presets( $theme_json ) {
	if ( ! elodin_bridge_is_child_theme_gradient_duotone_autogen_enabled() ) {
		return $theme_json;
	}

	if ( ! class_exists( 'olpo\\Helper' ) ) {
		return $theme_json;
	}

	$theme_json_data = elodin_bridge_get_child_theme_theme_json_data();
	if ( ! is_array( $theme_json_data ) || empty( $theme_json_data ) ) {
		return $theme_json;
	}

	$palette_lookup = elodin_bridge_build_child_theme_palette_lookup_maps();
	if ( empty( $palette_lookup['palette_by_slug'] ) ) {
		return $theme_json;
	}

	$has_child_gradients = elodin_bridge_child_theme_has_gradient_presets( $theme_json_data );
	$has_child_duotones = elodin_bridge_child_theme_has_duotone_presets( $theme_json_data );
	if ( $has_child_gradients && $has_child_duotones ) {
		return $theme_json;
	}

	$default_gradient_pairs = elodin_bridge_get_child_theme_default_gradient_pairs();
	$default_duotone_pairs = elodin_bridge_get_child_theme_default_duotone_pairs();
	if ( empty( $default_gradient_pairs ) && empty( $default_duotone_pairs ) ) {
		return $theme_json;
	}

	$data = $theme_json->get_data();
	if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
		$data['settings'] = array();
	}
	if ( ! isset( $data['settings']['color'] ) || ! is_array( $data['settings']['color'] ) ) {
		$data['settings']['color'] = array();
	}

	if ( ! $has_child_gradients ) {
		$generated_gradients = elodin_bridge_build_child_theme_gradient_presets(
			$default_gradient_pairs,
			$palette_lookup['palette_by_slug']
		);
		$data['settings']['color']['gradients'] = elodin_bridge_merge_child_theme_preset_arrays(
			$data['settings']['color']['gradients'] ?? array(),
			$generated_gradients
		);
	}

	if ( ! $has_child_duotones ) {
		$generated_duotones = elodin_bridge_build_child_theme_duotone_presets(
			$default_duotone_pairs,
			$palette_lookup['palette_by_slug']
		);
		$data['settings']['color']['duotone'] = elodin_bridge_merge_child_theme_preset_arrays(
			$data['settings']['color']['duotone'] ?? array(),
			$generated_duotones
		);
	}

	return $theme_json->update_with( $data );
}
add_filter( 'wp_theme_json_data_theme', 'elodin_bridge_maybe_inject_child_theme_gradient_duotone_presets', 10000 );

/**
 * Clear theme JSON cache after feature setting changes.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $new_value New option value.
 */
function elodin_bridge_handle_child_theme_gradient_duotone_autogen_setting_change( $old_value, $new_value ) {
	if ( (bool) $old_value === (bool) $new_value ) {
		return;
	}
	if ( ! function_exists( 'wp_clean_theme_json_cache' ) ) {
		return;
	}

	wp_clean_theme_json_cache();
}
add_action(
	'update_option_' . ELODIN_BRIDGE_OPTION_ENABLE_CHILD_THEME_GRADIENT_DUOTONE_AUTOGEN,
	'elodin_bridge_handle_child_theme_gradient_duotone_autogen_setting_change',
	10,
	2
);
