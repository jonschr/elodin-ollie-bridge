<?php

/**
 * Sanitize a yes/no toggle value.
 *
 * @param mixed $value Raw setting value.
 * @return int
 */
function elodin_bridge_sanitize_toggle( $value ) {
	return ! empty( $value ) ? 1 : 0;
}

/**
 * Build main settings page URL.
 *
 * @param string $anchor Optional hash anchor.
 * @return string
 */
function elodin_bridge_get_settings_page_url( $anchor = '' ) {
	$url = add_query_arg(
		array(
			'page' => 'elodin-bridge',
		),
		admin_url( 'themes.php' )
	);

	if ( '' !== $anchor ) {
		$url .= '#' . rawurlencode( ltrim( (string) $anchor, '#' ) );
	}

	return $url;
}

/**
 * Sanitize a single CSS length/expression field used in settings.
 *
 * @param mixed  $value    Raw setting value.
 * @param string $fallback Fallback value.
 * @return string
 */
function elodin_bridge_sanitize_css_value( $value, $fallback = '' ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $value ) {
		return $fallback;
	}

	// Prevent malformed payloads while allowing values like 4em, var(--space), and clamp(...).
	if ( false !== strpbrk( $value, ';{}\\' ) ) {
		return $fallback;
	}

	if ( ! preg_match( '/^[a-zA-Z0-9%().,_+*\/\-\s]+$/', $value ) ) {
		return $fallback;
	}

	return preg_replace( '/\s+/', ' ', $value );
}

/**
 * Check whether GenerateBlocks is available.
 *
 * @return bool
 */
function elodin_bridge_is_generateblocks_available() {
	return defined( 'GENERATEBLOCKS_VERSION' );
}

/**
 * Seed image sizes that Bridge pre-populates.
 *
 * @return array<int,array<string,mixed>>
 */
function elodin_bridge_get_seed_image_sizes() {
	return array(
		array(
			'slug'    => 'square',
			'label'   => __( 'Square', 'elodin-bridge' ),
			'width'   => 500,
			'height'  => 500,
			'crop'    => 1,
			'gallery' => 1,
		),
	);
}

/**
 * Default values for Bridge image size settings.
 *
 * @return array{enabled:int,sizes:array<int,array<string,mixed>>}
 */
function elodin_bridge_get_image_sizes_defaults() {
	return array(
		'enabled' => 0,
		'sizes'   => elodin_bridge_get_seed_image_sizes(),
	);
}

/**
 * Sanitize one custom image size row.
 *
 * @param mixed                    $row           Raw row value.
 * @param array<string,bool>       $blocked_slugs Slugs that are already used.
 * @return array<string,mixed>
 */
function elodin_bridge_sanitize_image_size_row( $row, $blocked_slugs = array() ) {
	if ( ! is_array( $row ) ) {
		return array();
	}

	$slug = sanitize_key( $row['slug'] ?? '' );
	if ( '' === $slug || isset( $blocked_slugs[ $slug ] ) ) {
		return array();
	}

	$width = absint( $row['width'] ?? 0 );
	$height = absint( $row['height'] ?? 0 );
	if ( $width < 1 || $height < 1 ) {
		return array();
	}

	$label = sanitize_text_field( $row['label'] ?? '' );
	if ( '' === $label ) {
		$label = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
	}

	return array(
		'slug'    => $slug,
		'label'   => $label,
		'width'   => $width,
		'height'  => $height,
		'crop'    => elodin_bridge_sanitize_toggle( $row['crop'] ?? 0 ),
		'gallery' => 1,
	);
}

/**
 * Sanitize Bridge image size settings.
 *
 * @param mixed $value Raw setting value.
 * @return array<int,array<string,mixed>>
 */
function elodin_bridge_get_legacy_image_size_rows( $value ) {
	$value = is_array( $value ) ? $value : array();
	$legacy_custom_sizes = isset( $value['custom_sizes'] ) && is_array( $value['custom_sizes'] ) ? $value['custom_sizes'] : array();
	$legacy_builtin_gallery = isset( $value['builtin_gallery'] ) && is_array( $value['builtin_gallery'] ) ? $value['builtin_gallery'] : array();
	$rows = elodin_bridge_get_seed_image_sizes();

	foreach ( $rows as $index => $row ) {
		$slug = $row['slug'] ?? '';
		if ( '' === $slug || ! array_key_exists( $slug, $legacy_builtin_gallery ) ) {
			continue;
		}

		$rows[ $index ]['gallery'] = elodin_bridge_sanitize_toggle( $legacy_builtin_gallery[ $slug ] );
	}

	foreach ( $legacy_custom_sizes as $row ) {
		$rows[] = $row;
	}

	return $rows;
}

/**
 * Sanitize Bridge image size settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,sizes:array<int,array<string,mixed>>}
 */
function elodin_bridge_sanitize_image_sizes_settings( $value ) {
	$defaults = elodin_bridge_get_image_sizes_defaults();
	$value = is_array( $value ) ? $value : array();
	$enabled = elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] );
	$has_sizes_payload = array_key_exists( 'sizes', $value ) && is_array( $value['sizes'] );
	$raw_sizes = $has_sizes_payload ? $value['sizes'] : array();

	// If the feature is being disabled and row inputs are not present in the request,
	// keep previously saved rows instead of wiping them out.
	if ( ! $has_sizes_payload && 0 === $enabled ) {
		$existing_settings = get_option( ELODIN_BRIDGE_OPTION_IMAGE_SIZES, array() );
		if ( is_array( $existing_settings ) && isset( $existing_settings['sizes'] ) && is_array( $existing_settings['sizes'] ) ) {
			$raw_sizes = $existing_settings['sizes'];
		}
	}

	if ( empty( $raw_sizes ) && ( isset( $value['custom_sizes'] ) || isset( $value['builtin_gallery'] ) ) ) {
		$raw_sizes = elodin_bridge_get_legacy_image_size_rows( $value );
	}

	$reserved_core_slugs = array(
		'thumbnail',
		'medium',
		'medium_large',
		'large',
		'post-thumbnail',
		'1536x1536',
		'2048x2048',
	);
	$blocked_slugs = array_fill_keys( $reserved_core_slugs, true );
	$sizes = array();
	foreach ( $raw_sizes as $raw_size ) {
		$size = elodin_bridge_sanitize_image_size_row( $raw_size, $blocked_slugs );
		if ( empty( $size ) ) {
			continue;
		}

		$sizes[] = $size;
		$blocked_slugs[ $size['slug'] ] = true;
	}

	return array(
		'enabled' => $enabled,
		'sizes'   => $sizes,
	);
}

/**
 * Get normalized Bridge image size settings.
 *
 * @return array{enabled:int,sizes:array<int,array<string,mixed>>}
 */
function elodin_bridge_get_image_sizes_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_IMAGE_SIZES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_image_sizes_defaults();
	}

	return elodin_bridge_sanitize_image_sizes_settings( $saved );
}

/**
 * Check if Bridge image sizes are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_image_sizes_enabled() {
	$settings = elodin_bridge_get_image_sizes_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Get all Bridge image sizes with gallery flags.
 *
 * @return array<string,array<string,mixed>>
 */
function elodin_bridge_get_registered_bridge_image_sizes() {
	$settings = elodin_bridge_get_image_sizes_settings();
	$sizes = array();

	foreach ( $settings['sizes'] as $size ) {
		if ( empty( $size['slug'] ) ) {
			continue;
		}

		$sizes[ $size['slug'] ] = $size;
	}

	return $sizes;
}

/**
 * Default values for first/last block body class settings.
 *
 * @return array{enabled:int,enable_first:int,enable_last:int,enable_debug:int,section_blocks:array<int,string>}
 */
function elodin_bridge_get_block_edge_class_defaults() {
	return array(
		'enabled'        => 0,
		'enable_first'   => 1,
		'enable_last'    => 1,
		'enable_debug'   => 0,
		'section_blocks' => array(
			'core/cover',
			'core/block',
			'generateblocks/element',
		),
	);
}

/**
 * Normalize a block-name list from textarea/array input.
 *
 * @param mixed $value Raw block list value.
 * @return array<int,string>
 */
function elodin_bridge_sanitize_block_name_list( $value ) {
	$items = array();
	$seen = array();
	$raw_items = is_array( $value ) ? $value : preg_split( '/[\r\n,]+/', (string) $value );
	if ( ! is_array( $raw_items ) ) {
		return $items;
	}

	foreach ( $raw_items as $item ) {
		$item = strtolower( trim( (string) $item ) );
		if ( '' === $item ) {
			continue;
		}

		$item = preg_replace( '/[^a-z0-9_\/-]+/', '', $item );
		$item = trim( (string) $item, "-/\t\n\r\0\x0B" );
		if ( '' === $item || isset( $seen[ $item ] ) ) {
			continue;
		}

		$items[] = $item;
		$seen[ $item ] = true;
	}

	return $items;
}

/**
 * Sanitize first/last block body class settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int,enable_first:int,enable_last:int,enable_debug:int,section_blocks:array<int,string>}
 */
function elodin_bridge_sanitize_block_edge_class_settings( $value ) {
	$defaults = elodin_bridge_get_block_edge_class_defaults();
	$value = is_array( $value ) ? $value : array();

	$section_blocks = array_key_exists( 'section_blocks', $value )
		? elodin_bridge_sanitize_block_name_list( $value['section_blocks'] )
		: $defaults['section_blocks'];

	return array(
		'enabled'        => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
		'enable_first'   => elodin_bridge_sanitize_toggle( $value['enable_first'] ?? $defaults['enable_first'] ),
		'enable_last'    => elodin_bridge_sanitize_toggle( $value['enable_last'] ?? $defaults['enable_last'] ),
		'enable_debug'   => elodin_bridge_sanitize_toggle( $value['enable_debug'] ?? $defaults['enable_debug'] ),
		'section_blocks' => $section_blocks,
	);
}

/**
 * Get normalized first/last block body class settings.
 *
 * @return array{enabled:int,enable_first:int,enable_last:int,enable_debug:int,section_blocks:array<int,string>}
 */
function elodin_bridge_get_block_edge_class_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_block_edge_class_defaults();
	}

	return elodin_bridge_sanitize_block_edge_class_settings( $saved );
}

/**
 * Check if first/last block body class feature is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_block_edge_classes_enabled() {
	$settings = elodin_bridge_get_block_edge_class_settings();
	return ! empty( $settings['enabled'] );
}

/**
 * Get spacing alias tokens and their expected theme.json slugs.
 *
 * @return array<int,array{token:string,label:string,source_slugs:array<int,string>}>
 */
function elodin_bridge_get_spacing_variable_scale() {
	return array(
		array(
			'token'        => '2xs',
			'label'        => __( '2XS', 'elodin-bridge' ),
			'source_slugs' => array( 'xx-small', '2xs', 'xxs' ),
		),
		array(
			'token'        => 'xs',
			'label'        => __( 'Extra Small', 'elodin-bridge' ),
			'source_slugs' => array( 'x-small', 'xs' ),
		),
		array(
			'token'        => 's',
			'label'        => __( 'Small', 'elodin-bridge' ),
			'source_slugs' => array( 'small', 's' ),
		),
		array(
			'token'        => 'b',
			'label'        => __( 'Base', 'elodin-bridge' ),
			'source_slugs' => array( 'base', 'b' ),
		),
		array(
			'token'        => 'm',
			'label'        => __( 'Medium', 'elodin-bridge' ),
			'source_slugs' => array( 'medium', 'm' ),
		),
		array(
			'token'        => 'l',
			'label'        => __( 'Large', 'elodin-bridge' ),
			'source_slugs' => array( 'large', 'l' ),
		),
		array(
			'token'        => 'xl',
			'label'        => __( 'Extra Large', 'elodin-bridge' ),
			'source_slugs' => array( 'x-large', 'xl' ),
		),
		array(
			'token'        => '2xl',
			'label'        => __( '2XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xx-large', '2xl', 'xxl' ),
		),
		array(
			'token'        => '3xl',
			'label'        => __( '3XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xxx-large', '3xl', 'xxxl' ),
		),
		array(
			'token'        => '4xl',
			'label'        => __( '4XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xxxx-large', '4xl', 'xxxxl' ),
		),
	);
}

/**
 * Get the active theme.json file path.
 *
 * Prefers the active stylesheet theme, then falls back to parent template theme.
 *
 * @return string
 */
function elodin_bridge_get_active_theme_json_path() {
	$stylesheet_json = trailingslashit( get_stylesheet_directory() ) . 'theme.json';
	if ( file_exists( $stylesheet_json ) ) {
		return $stylesheet_json;
	}

	$template_json = trailingslashit( get_template_directory() ) . 'theme.json';
	if ( file_exists( $template_json ) ) {
		return $template_json;
	}

	return '';
}

/**
 * Get the plugin theme-defaults.json file path.
 *
 * @return string
 */
function elodin_bridge_get_plugin_theme_defaults_path() {
	$path = trailingslashit( ELODIN_BRIDGE_DIR ) . 'theme-defaults.json';
	if ( file_exists( $path ) ) {
		return $path;
	}

	return '';
}

/**
 * Sanitize the theme.json source mode setting.
 *
 * @param mixed $value Raw setting value.
 * @return string
 */
function elodin_bridge_sanitize_theme_json_source_mode( $value ) {
	$value = sanitize_key( (string) $value );
	if ( 'plugin' === $value ) {
		return 'plugin';
	}

	return 'theme';
}

/**
 * Get normalized theme.json source mode.
 *
 * @return string theme|plugin
 */
function elodin_bridge_get_theme_json_source_mode() {
	return 'theme';
}

/**
 * Check whether the active theme has a readable theme.json file.
 *
 * @return bool
 */
function elodin_bridge_is_active_theme_json_available() {
	$path = elodin_bridge_get_active_theme_json_path();
	return '' !== $path && is_readable( $path );
}

/**
 * Get effective theme.json source mode after runtime availability checks.
 *
 * @return string theme|plugin
 */
function elodin_bridge_get_effective_theme_json_source_mode() {
	$mode = elodin_bridge_get_theme_json_source_mode();
	if ( 'theme' === $mode && ! elodin_bridge_is_active_theme_json_available() ) {
		return 'plugin';
	}

	return $mode;
}

/**
 * Get decoded theme.json data from a specific path.
 *
 * @param string $path theme.json file path.
 * @return array<string,mixed>
 */
function elodin_bridge_get_theme_json_data_from_path( $path ) {
	static $cache = array();

	$path = (string) $path;
	if ( '' === $path ) {
		return array();
	}

	if ( isset( $cache[ $path ] ) && is_array( $cache[ $path ] ) ) {
		return $cache[ $path ];
	}

	if ( ! is_readable( $path ) ) {
		$cache[ $path ] = array();
		return $cache[ $path ];
	}

	$raw_json = file_get_contents( $path );
	if ( false === $raw_json || '' === $raw_json ) {
		$cache[ $path ] = array();
		return $cache[ $path ];
	}

	$parsed = json_decode( $raw_json, true );
	$cache[ $path ] = is_array( $parsed ) ? $parsed : array();

	return $cache[ $path ];
}

/**
 * Get decoded active theme.json data.
 *
 * @return array<string,mixed>
 */
function elodin_bridge_get_active_theme_json_data() {
	return elodin_bridge_get_theme_json_data_from_path( elodin_bridge_get_active_theme_json_path() );
}

/**
 * Get decoded plugin theme-defaults.json data.
 *
 * @return array<string,mixed>
 */
function elodin_bridge_get_plugin_theme_defaults_data() {
	return elodin_bridge_get_theme_json_data_from_path( elodin_bridge_get_plugin_theme_defaults_path() );
}

/**
 * Sanitize a theme.json CSS value used for read-only style output.
 *
 * @param mixed $value Raw theme.json value.
 * @return string
 */
function elodin_bridge_sanitize_theme_json_css_value( $value ) {
	$value = trim( wp_strip_all_tags( (string) $value ) );
	if ( '' === $value ) {
		return '';
	}

	if ( false !== strpbrk( $value, ';{}\\' ) ) {
		return '';
	}

	if ( ! preg_match( '/^[a-zA-Z0-9#%().,_+*\/\-\s\'"]+$/', $value ) ) {
		return '';
	}

	return preg_replace( '/\s+/', ' ', $value );
}

/**
 * Normalize a theme.json CSS value into a value safe for inline output.
 *
 * Supports `var:preset|...` and `var:custom|...` shorthands.
 *
 * @param mixed $value Raw theme.json value.
 * @return string
 */
function elodin_bridge_normalize_theme_json_css_value( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	$value = preg_replace_callback(
		'/var:preset\|([a-zA-Z0-9-]+)\|([a-zA-Z0-9-]+)/',
		static function ( $matches ) {
			$group = sanitize_key( $matches[1] ?? '' );
			$slug = sanitize_key( $matches[2] ?? '' );
			if ( '' === $group || '' === $slug ) {
				return '';
			}

			return 'var(--wp--preset--' . $group . '--' . $slug . ')';
		},
		$value
	);

	$value = preg_replace_callback(
		'/var:custom\|([a-zA-Z0-9|_-]+)/',
		static function ( $matches ) {
			$raw_path = trim( (string) ( $matches[1] ?? '' ) );
			if ( '' === $raw_path ) {
				return '';
			}

			$path_segments = explode( '|', $raw_path );
			$sanitized_segments = array();
			foreach ( $path_segments as $segment ) {
				$segment = sanitize_key( $segment );
				if ( '' !== $segment ) {
					$sanitized_segments[] = $segment;
				}
			}

			if ( empty( $sanitized_segments ) ) {
				return '';
			}

			return 'var(--wp--custom--' . implode( '--', $sanitized_segments ) . ')';
		},
		$value
	);

	return elodin_bridge_sanitize_theme_json_css_value( $value );
}

/**
 * Read spacing presets from theme.json data.
 *
 * @param array<string,mixed> $decoded Decoded theme.json data.
 * @return array<string,array{slug:string,name:string,size:string}>
 */
function elodin_bridge_get_theme_spacing_size_presets_from_data( $decoded ) {
	if ( ! is_array( $decoded ) ) {
		return array();
	}

	$raw_presets = $decoded['settings']['spacing']['spacingSizes'] ?? array();
	if ( ! is_array( $raw_presets ) ) {
		return array();
	}

	$presets = array();
	foreach ( $raw_presets as $preset ) {
		if ( ! is_array( $preset ) ) {
			continue;
		}

		$slug = sanitize_key( $preset['slug'] ?? '' );
		$size = elodin_bridge_sanitize_css_value( $preset['size'] ?? '', '' );
		if ( '' === $slug || '' === $size ) {
			continue;
		}

		$name = sanitize_text_field( $preset['name'] ?? '' );
		if ( '' === $name ) {
			$name = $slug;
		}

		$presets[ $slug ] = array(
			'slug' => $slug,
			'name' => $name,
			'size' => $size,
		);
	}

	return $presets;
}

/**
 * Check whether theme.json data includes usable spacing presets.
 *
 * @param array<string,mixed> $decoded Decoded theme.json data.
 * @return bool
 */
function elodin_bridge_theme_json_has_spacing_presets( $decoded ) {
	return ! empty( elodin_bridge_get_theme_spacing_size_presets_from_data( $decoded ) );
}

/**
 * Read font-size presets from theme.json data.
 *
 * @param array<string,mixed> $decoded Decoded theme.json data.
 * @return array<string,array{slug:string,name:string,size:string}>
 */
function elodin_bridge_get_theme_font_size_presets_from_data( $decoded ) {
	if ( ! is_array( $decoded ) ) {
		return array();
	}

	$raw_presets = $decoded['settings']['typography']['fontSizes'] ?? array();
	if ( ! is_array( $raw_presets ) ) {
		return array();
	}

	$presets = array();
	foreach ( $raw_presets as $preset ) {
		if ( ! is_array( $preset ) ) {
			continue;
		}

		$slug = sanitize_key( $preset['slug'] ?? '' );
		$size = elodin_bridge_sanitize_css_value( $preset['size'] ?? '', '' );
		if ( '' === $slug || '' === $size ) {
			continue;
		}

		$name = sanitize_text_field( $preset['name'] ?? '' );
		if ( '' === $name ) {
			$name = $slug;
		}

		$presets[ $slug ] = array(
			'slug' => $slug,
			'name' => $name,
			'size' => $size,
		);
	}

	return $presets;
}

/**
 * Check whether theme.json data includes usable font-size presets.
 *
 * @param array<string,mixed> $decoded Decoded theme.json data.
 * @return bool
 */
function elodin_bridge_theme_json_has_font_size_presets( $decoded ) {
	return ! empty( elodin_bridge_get_theme_font_size_presets_from_data( $decoded ) );
}

/**
 * Select theme.json data for a class, with optional class-specific fallback.
 *
 * @param string $class spacing|font_sizes
 * @return array<string,mixed>
 */
function elodin_bridge_get_theme_json_data_for_class( $class ) {
	$mode = elodin_bridge_get_effective_theme_json_source_mode();
	if ( 'plugin' === $mode ) {
		return elodin_bridge_get_plugin_theme_defaults_data();
	}

	$theme_data = elodin_bridge_get_active_theme_json_data();
	if ( ! is_array( $theme_data ) ) {
		$theme_data = array();
	}

	$has_class_data = false;
	if ( 'spacing' === $class ) {
		$has_class_data = elodin_bridge_theme_json_has_spacing_presets( $theme_data );
	} elseif ( 'font_sizes' === $class ) {
		$has_class_data = elodin_bridge_theme_json_has_font_size_presets( $theme_data );
	}

	if ( $has_class_data ) {
		return $theme_data;
	}

	$defaults_data = elodin_bridge_get_plugin_theme_defaults_data();
	return is_array( $defaults_data ) ? $defaults_data : array();
}

/**
 * Read spacing presets from the selected theme.json source.
 *
 * @return array<string,array{slug:string,name:string,size:string}>
 */
function elodin_bridge_get_theme_spacing_size_presets() {
	return elodin_bridge_get_theme_spacing_size_presets_from_data(
		elodin_bridge_get_theme_json_data_for_class( 'spacing' )
	);
}

/**
 * Build alias mappings from preset definitions.
 *
 * @param array<int,array{token:string,label:string,source_slugs:array<int,string>}> $definitions Alias definitions.
 * @param array<string,array{slug:string,name:string,size:string}>                    $presets Source presets.
 * @return array<int,array{token:string,label:string,source_slug:string,source_name:string,value:string}>
 */
function elodin_bridge_build_variable_aliases_from_presets( $definitions, $presets ) {
	$aliases = array();

	foreach ( $definitions as $definition ) {
		$token = sanitize_key( $definition['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$label = sanitize_text_field( $definition['label'] ?? '' );
		$source_slugs = isset( $definition['source_slugs'] ) && is_array( $definition['source_slugs'] ) ? $definition['source_slugs'] : array();
		$matched_preset = array();

		foreach ( $source_slugs as $source_slug ) {
			$source_slug = sanitize_key( $source_slug );
			if ( '' === $source_slug || ! isset( $presets[ $source_slug ] ) ) {
				continue;
			}

			$matched_preset = $presets[ $source_slug ];
			break;
		}

		if ( empty( $matched_preset ) && isset( $presets[ $token ] ) ) {
			$matched_preset = $presets[ $token ];
		}

		$aliases[] = array(
			'token'       => $token,
			'label'       => $label,
			'source_slug' => isset( $matched_preset['slug'] ) ? (string) $matched_preset['slug'] : '',
			'source_name' => isset( $matched_preset['name'] ) ? (string) $matched_preset['name'] : '',
			'value'       => isset( $matched_preset['size'] ) ? (string) $matched_preset['size'] : '',
		);
	}

	return $aliases;
}

/**
 * Check whether any variable alias currently has a mapped value.
 *
 * @param array<int,array{token:string,label:string,source_slug:string,source_name:string,value:string}> $aliases Alias rows.
 * @return bool
 */
function elodin_bridge_variable_aliases_have_values( $aliases ) {
	foreach ( $aliases as $alias ) {
		$value = trim( (string) ( $alias['value'] ?? '' ) );
		if ( '' !== $value ) {
			return true;
		}
	}

	return false;
}

/**
 * Build alias mappings from theme spacing presets to short variable names.
 *
 * @return array<int,array{token:string,label:string,source_slug:string,source_name:string,value:string}>
 */
function elodin_bridge_get_spacing_variable_aliases() {
	$definitions = elodin_bridge_get_spacing_variable_scale();
	$presets = elodin_bridge_get_theme_spacing_size_presets();
	$aliases = elodin_bridge_build_variable_aliases_from_presets( $definitions, $presets );

	if (
		'theme' === elodin_bridge_get_theme_json_source_mode() &&
		! elodin_bridge_variable_aliases_have_values( $aliases )
	) {
		$fallback_presets = elodin_bridge_get_theme_spacing_size_presets_from_data(
			elodin_bridge_get_plugin_theme_defaults_data()
		);
		$aliases = elodin_bridge_build_variable_aliases_from_presets( $definitions, $fallback_presets );
	}

	return $aliases;
}

/**
 * Get font-size alias tokens and their expected theme.json slugs.
 *
 * @return array<int,array{token:string,label:string,source_slugs:array<int,string>}>
 */
function elodin_bridge_get_font_size_variable_scale() {
	return array(
		array(
			'token'        => 'xs',
			'label'        => __( 'Extra Small', 'elodin-bridge' ),
			'source_slugs' => array( 'x-small', 'xs' ),
		),
		array(
			'token'        => 's',
			'label'        => __( 'Small', 'elodin-bridge' ),
			'source_slugs' => array( 'small', 's' ),
		),
		array(
			'token'        => 'b',
			'label'        => __( 'Base', 'elodin-bridge' ),
			'source_slugs' => array( 'base', 'b' ),
		),
		array(
			'token'        => 'm',
			'label'        => __( 'Medium', 'elodin-bridge' ),
			'source_slugs' => array( 'medium', 'm' ),
		),
		array(
			'token'        => 'l',
			'label'        => __( 'Large', 'elodin-bridge' ),
			'source_slugs' => array( 'large', 'l' ),
		),
		array(
			'token'        => 'xl',
			'label'        => __( 'Extra Large', 'elodin-bridge' ),
			'source_slugs' => array( 'x-large', 'xl' ),
		),
		array(
			'token'        => '2xl',
			'label'        => __( '2XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xx-large', '2xl', 'xxl' ),
		),
		array(
			'token'        => '3xl',
			'label'        => __( '3XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xxx-large', '3xl', 'xxxl' ),
		),
		array(
			'token'        => '4xl',
			'label'        => __( '4XL', 'elodin-bridge' ),
			'source_slugs' => array( 'xxxx-large', '4xl', 'xxxxl' ),
		),
	);
}

/**
 * Build a WordPress preset variable name for CSS auto-wrap mappings.
 *
 * @param string $group       Preset group slug (spacing|font-size).
 * @param string $source_slug Resolved preset source slug.
 * @param string $token       Bridge shorthand token fallback.
 * @return string
 */
function elodin_bridge_get_css_variable_autowrap_preset_variable( $group, $source_slug, $token ) {
	$group = sanitize_key( (string) $group );
	if ( 'spacing' !== $group && 'font-size' !== $group ) {
		return '';
	}

	$source_slug = sanitize_key( (string) $source_slug );
	$token = sanitize_key( (string) $token );
	if ( '' === $source_slug ) {
		$source_slug = $token;
	}

	if ( '' === $source_slug ) {
		return '';
	}

	return '--wp--preset--' . $group . '--' . $source_slug;
}

/**
 * Get supported CSS variable tokens for auto-wrap expansion.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_css_variable_autowrap_tokens() {
	$tokens = array();
	$seen = array();

	foreach ( elodin_bridge_get_spacing_variable_aliases() as $alias ) {
		$token = sanitize_key( $alias['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$variable = elodin_bridge_get_css_variable_autowrap_preset_variable(
			'spacing',
			(string) ( $alias['source_slug'] ?? '' ),
			$token
		);
		if ( '' === $variable ) {
			continue;
		}

		if ( isset( $seen[ $variable ] ) ) {
			continue;
		}

		$tokens[] = $variable;
		$seen[ $variable ] = true;
	}

	foreach ( elodin_bridge_get_font_size_variable_aliases() as $alias ) {
		$token = sanitize_key( $alias['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$variable = elodin_bridge_get_css_variable_autowrap_preset_variable(
			'font-size',
			(string) ( $alias['source_slug'] ?? '' ),
			$token
		);
		if ( '' === $variable ) {
			continue;
		}

		if ( isset( $seen[ $variable ] ) ) {
			continue;
		}

		$tokens[] = $variable;
		$seen[ $variable ] = true;
	}

	return $tokens;
}

/**
 * Get shorthand aliases for CSS variable token auto-wrap expansion.
 *
 * Example: --sm => --wp--preset--spacing--medium, --f2xl => --wp--preset--font-size--xx-large.
 *
 * @return array<string,string>
 */
function elodin_bridge_get_css_variable_autowrap_aliases() {
	$aliases = array();

	foreach ( elodin_bridge_get_spacing_variable_aliases() as $alias ) {
		$token = sanitize_key( $alias['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$target = elodin_bridge_get_css_variable_autowrap_preset_variable(
			'spacing',
			(string) ( $alias['source_slug'] ?? '' ),
			$token
		);
		if ( '' === $target ) {
			continue;
		}

		$aliases[ '--s' . $token ] = $target;
		$aliases[ '--space-' . $token ] = $target;
	}

	foreach ( elodin_bridge_get_font_size_variable_aliases() as $alias ) {
		$token = sanitize_key( $alias['token'] ?? '' );
		if ( '' === $token ) {
			continue;
		}

		$target = elodin_bridge_get_css_variable_autowrap_preset_variable(
			'font-size',
			(string) ( $alias['source_slug'] ?? '' ),
			$token
		);
		if ( '' === $target ) {
			continue;
		}

		$aliases[ '--f' . $token ] = $target;
		$aliases[ '--font-' . $token ] = $target;
	}

	return $aliases;
}

/**
 * Read font-size presets from the selected theme.json source.
 *
 * @return array<string,array{slug:string,name:string,size:string}>
 */
function elodin_bridge_get_theme_font_size_presets() {
	return elodin_bridge_get_theme_font_size_presets_from_data(
		elodin_bridge_get_theme_json_data_for_class( 'font_sizes' )
	);
}

/**
 * Build alias mappings from theme font-size presets to short variable names.
 *
 * @return array<int,array{token:string,label:string,source_slug:string,source_name:string,value:string}>
 */
function elodin_bridge_get_font_size_variable_aliases() {
	$definitions = elodin_bridge_get_font_size_variable_scale();
	$presets = elodin_bridge_get_theme_font_size_presets();
	$aliases = elodin_bridge_build_variable_aliases_from_presets( $definitions, $presets );

	if (
		'theme' === elodin_bridge_get_theme_json_source_mode() &&
		! elodin_bridge_variable_aliases_have_values( $aliases )
	) {
		$fallback_presets = elodin_bridge_get_theme_font_size_presets_from_data(
			elodin_bridge_get_plugin_theme_defaults_data()
		);
		$aliases = elodin_bridge_build_variable_aliases_from_presets( $definitions, $fallback_presets );
	}

	return $aliases;
}

/**
 * Get default values for spacing variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_spacing_variables_defaults() {
	return array(
		'enabled' => 1,
	);
}

/**
 * Sanitize spacing variable settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int}
 */
function elodin_bridge_sanitize_spacing_variables_settings( $value ) {
	$defaults = elodin_bridge_get_spacing_variables_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
	);
}

/**
 * Get normalized spacing variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_spacing_variables_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_SPACING_VARIABLES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_spacing_variables_defaults();
	}

	return elodin_bridge_sanitize_spacing_variables_settings( $saved );
}

/**
 * Check if spacing variables output is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_spacing_variables_enabled() {
	return true;
}

/**
 * Get default values for font-size variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_font_size_variables_defaults() {
	return array(
		'enabled' => 1,
	);
}

/**
 * Sanitize font-size variable settings.
 *
 * @param mixed $value Raw setting value.
 * @return array{enabled:int}
 */
function elodin_bridge_sanitize_font_size_variables_settings( $value ) {
	$defaults = elodin_bridge_get_font_size_variables_defaults();
	$value = is_array( $value ) ? $value : array();

	return array(
		'enabled' => elodin_bridge_sanitize_toggle( $value['enabled'] ?? $defaults['enabled'] ),
	);
}

/**
 * Get normalized font-size variable settings.
 *
 * @return array{enabled:int}
 */
function elodin_bridge_get_font_size_variables_settings() {
	$saved = get_option( ELODIN_BRIDGE_OPTION_FONT_SIZE_VARIABLES, null );
	if ( null === $saved || false === $saved ) {
		return elodin_bridge_get_font_size_variables_defaults();
	}

	return elodin_bridge_sanitize_font_size_variables_settings( $saved );
}

/**
 * Check if font-size variables output is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_font_size_variables_enabled() {
	return true;
}

/**
 * Check if balanced text toolbar feature is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_balanced_text_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT, 1 );
}

/**
 * Check if heading/paragraph style overrides are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_heading_paragraph_overrides_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES, 1 );
}

/**
 * Check if editor fullscreen restriction is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_editor_ui_restrictions_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS, 1 );
}

/**
 * Check if editor publish sidebar restriction is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_editor_publish_sidebar_restriction_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION, 1 );
}

/**
 * Check if block-theme editor "Show template" default is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_editor_show_template_default_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT, 1 );
}

/**
 * Check if media library infinite scrolling is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_media_library_infinite_scrolling_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING, 1 );
}

/**
 * Check if Bridge shortcodes are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_shortcodes_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES, 1 );
}

/**
 * Check if SVG uploads are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_svg_uploads_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS, 1 );
}

/**
 * Check if CSS variable token auto-wrap is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_css_variable_autowrap_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP, 1 );
}

/**
 * Check if GenerateBlocks boundary highlights are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_generateblocks_boundary_highlights_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS, 0 );
}

/**
 * Check if editor Group block border highlights are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_editor_group_border_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER, 1 );
}

/**
 * Check if Edit Site admin-bar shortcuts are enabled.
 *
 * @return bool
 */
function elodin_bridge_is_edit_site_admin_bar_links_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_EDIT_SITE_ADMIN_BAR_LINKS, 1 );
}

/**
 * Check if the experimental Site Editor admin bar override is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_site_editor_admin_bar_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_SITE_EDITOR_ADMIN_BAR, 1 );
}

/**
 * Check if mobile fixed-background repair is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_mobile_fixed_background_repair_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR, 0 );
}

/**
 * Check if Ollie Pro color palette overrides should be removed.
 *
 * @return bool
 */
function elodin_bridge_is_remove_ollie_color_palettes_enabled() {
	return (bool) get_option( ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES, 1 );
}

/**
 * Check if content type behavior mapping is enabled.
 *
 * @return bool
 */
function elodin_bridge_is_content_type_behavior_enabled() {
	return ! ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() );
}

/**
 * Check if a content type is configured as page-like.
 *
 * @param string $post_type Post type key.
 * @return bool
 */
function elodin_bridge_is_post_type_page_like( $post_type ) {
	if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
		return false;
	}

	return is_post_type_hierarchical( $post_type );
}

/**
 * Register plugin settings.
 */
function elodin_bridge_register_settings() {
	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 0,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_EDIT_SITE_ADMIN_BAR_LINKS,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_SITE_EDITOR_ADMIN_BAR,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 0,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES,
		array(
			'type'              => 'boolean',
			'sanitize_callback' => 'elodin_bridge_sanitize_toggle',
			'default'           => 1,
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_block_edge_class_settings',
			'default'           => elodin_bridge_get_block_edge_class_defaults(),
		)
	);

	register_setting(
		'elodin_bridge_settings',
		ELODIN_BRIDGE_OPTION_IMAGE_SIZES,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'elodin_bridge_sanitize_image_sizes_settings',
			'default'           => elodin_bridge_get_image_sizes_defaults(),
		)
	);
}
add_action( 'admin_init', 'elodin_bridge_register_settings' );

require_once ELODIN_BRIDGE_DIR . '/inc/settings-page-admin.php';
