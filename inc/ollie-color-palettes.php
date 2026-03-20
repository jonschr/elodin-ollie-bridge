<?php

/**
 * Remove Ollie Pro's theme.json palette filter so child theme palettes are preserved.
 */
function elodin_bridge_remove_ollie_pro_color_palette_filter() {
	if ( ! elodin_bridge_is_remove_ollie_color_palettes_enabled() ) {
		return;
	}

	remove_filter( 'wp_theme_json_data_theme', array( 'olpo\\Helper', 'filter_theme_json_data' ), 10 );
}

/**
 * Remove Ollie Pro's theme JSON filter immediately after Ollie Pro registers it.
 *
 * Ollie Pro attaches its callback on `plugins_loaded` (priority 10), so we run at
 * a later priority on the same hook to guarantee removal before later boot steps.
 */
function elodin_bridge_remove_ollie_pro_color_palette_filter_on_plugins_loaded() {
	elodin_bridge_remove_ollie_pro_color_palette_filter();
}
add_action( 'plugins_loaded', 'elodin_bridge_remove_ollie_pro_color_palette_filter_on_plugins_loaded', 11 );

// Fallback passes for contexts where callbacks may be re-attached later.
add_action( 'after_setup_theme', 'elodin_bridge_remove_ollie_pro_color_palette_filter', 1 );
add_action( 'init', 'elodin_bridge_remove_ollie_pro_color_palette_filter', 1 );

/**
 * Normalize a theme.json color palette payload.
 *
 * @param mixed $raw_palette Raw palette value.
 * @return array<int,array{name:string,slug:string,color:string}>
 */
function elodin_bridge_sanitize_theme_json_color_palette( $raw_palette ) {
	if ( ! is_array( $raw_palette ) ) {
		return array();
	}

	$palette = array();
	$seen = array();
	foreach ( $raw_palette as $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}

		$slug = sanitize_key( $entry['slug'] ?? '' );
		$raw_color = $entry['color'] ?? '';
		$color = function_exists( 'elodin_bridge_sanitize_theme_json_css_value' )
			? elodin_bridge_sanitize_theme_json_css_value( $raw_color )
			: sanitize_text_field( (string) $raw_color );
		if ( '' === $slug || '' === $color || isset( $seen[ $slug ] ) ) {
			continue;
		}

		$name = sanitize_text_field( $entry['name'] ?? $slug );
		$palette[] = array(
			'name'  => '' === $name ? $slug : $name,
			'slug'  => $slug,
			'color' => $color,
		);
		$seen[ $slug ] = true;
	}

	return $palette;
}

/**
 * Get a child-theme palette candidate from child theme.json or child color styles.
 *
 * @return array<int,array{name:string,slug:string,color:string}>
 */
function elodin_bridge_get_child_theme_color_palette_candidate() {
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
	if ( is_readable( $theme_json_file ) ) {
		$decoded = wp_json_file_decode( $theme_json_file, array( 'associative' => true ) );
		$palette = elodin_bridge_sanitize_theme_json_color_palette( $decoded['settings']['color']['palette'] ?? array() );
		if ( ! empty( $palette ) ) {
			$cache = $palette;
			return $cache;
		}
	}

	$color_style_files = glob( trailingslashit( $stylesheet_directory ) . 'styles/colors/*.json' );
	if ( ! is_array( $color_style_files ) || empty( $color_style_files ) ) {
		return $cache;
	}

	sort( $color_style_files, SORT_STRING );
	foreach ( $color_style_files as $file ) {
		if ( ! is_string( $file ) || ! is_readable( $file ) ) {
			continue;
		}

		$decoded = wp_json_file_decode( $file, array( 'associative' => true ) );
		$palette = elodin_bridge_sanitize_theme_json_color_palette( $decoded['settings']['color']['palette'] ?? array() );
		if ( ! empty( $palette ) ) {
			$cache = $palette;
			return $cache;
		}
	}

	return $cache;
}

/**
 * Get child theme style-variation titles (theme scope only).
 *
 * @return array<int,string>
 */
function elodin_bridge_get_child_theme_style_variation_titles() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array();
	$styles_directory = trailingslashit( get_stylesheet_directory() ) . 'styles';
	if ( ! is_dir( $styles_directory ) ) {
		return $cache;
	}

	$seen = array();
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $styles_directory, FilesystemIterator::SKIP_DOTS )
	);

	foreach ( $iterator as $file ) {
		if ( ! ( $file instanceof SplFileInfo ) || ! $file->isFile() ) {
			continue;
		}

		if ( 'json' !== strtolower( (string) $file->getExtension() ) ) {
			continue;
		}

		$decoded = wp_json_file_decode( $file->getPathname(), array( 'associative' => true ) );
		if ( ! is_array( $decoded ) ) {
			continue;
		}

		// Match core's "theme" scope behavior: style variations without blockTypes.
		if ( isset( $decoded['blockTypes'] ) ) {
			continue;
		}

		$title = sanitize_text_field( $decoded['title'] ?? '' );
		if ( '' === $title ) {
			$title = sanitize_text_field( $file->getBasename( '.json' ) );
		}
		if ( '' === $title || isset( $seen[ $title ] ) ) {
			continue;
		}

		$cache[] = $title;
		$seen[ $title ] = true;
	}

	return $cache;
}

/**
 * Enforce the child theme color palette at the theme.json theme layer.
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data object.
 * @return WP_Theme_JSON_Data
 */
function elodin_bridge_enforce_child_theme_color_palette( $theme_json ) {
	if ( ! elodin_bridge_is_remove_ollie_color_palettes_enabled() ) {
		return $theme_json;
	}

	$palette = elodin_bridge_get_child_theme_color_palette_candidate();
	if ( empty( $palette ) ) {
		return $theme_json;
	}

	$data = $theme_json->get_data();
	if ( ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
		$data['settings'] = array();
	}
	if ( ! isset( $data['settings']['color'] ) || ! is_array( $data['settings']['color'] ) ) {
		$data['settings']['color'] = array();
	}

	$data['settings']['color']['defaultPalette'] = false;
	$data['settings']['color']['palette'] = $palette;

	return $theme_json->update_with( $data );
}
add_filter( 'wp_theme_json_data_theme', 'elodin_bridge_enforce_child_theme_color_palette', 9999 );

/**
 * Remove user-layer color palettes so old global styles posts cannot re-add Ollie palettes.
 *
 * @param WP_Theme_JSON_Data $theme_json User theme JSON data object.
 * @return WP_Theme_JSON_Data
 */
function elodin_bridge_remove_user_layer_color_palette( $theme_json ) {
	if ( ! elodin_bridge_is_remove_ollie_color_palettes_enabled() ) {
		return $theme_json;
	}

	$data = $theme_json->get_data();
	if (
		! isset( $data['settings'] ) ||
		! is_array( $data['settings'] ) ||
		! isset( $data['settings']['color'] ) ||
		! is_array( $data['settings']['color'] )
	) {
		return $theme_json;
	}

	$did_change = false;
	if ( array_key_exists( 'palette', $data['settings']['color'] ) ) {
		unset( $data['settings']['color']['palette'] );
		$did_change = true;
	}

	if ( ! $did_change ) {
		return $theme_json;
	}

	return $theme_json->update_with( $data );
}
add_filter( 'wp_theme_json_data_user', 'elodin_bridge_remove_user_layer_color_palette', 9999 );

/**
 * Remove color-only style variations from the Site Editor variations response.
 *
 * @param mixed           $response The response object or error.
 * @param array           $handler  Route handler attributes.
 * @param WP_REST_Request $request  Current request.
 * @return mixed
 */
function elodin_bridge_filter_global_styles_variations_response( $response, $handler, $request ) {
	if ( ! elodin_bridge_is_remove_ollie_color_palettes_enabled() ) {
		return $response;
	}

	if ( ! ( $response instanceof WP_REST_Response ) || ! ( $request instanceof WP_REST_Request ) ) {
		return $response;
	}

	if ( 'GET' !== $request->get_method() ) {
		return $response;
	}

	$route = (string) $request->get_route();
	if ( ! preg_match( '#^/wp/v2/global-styles/themes/.+/variations$#', $route ) ) {
		return $response;
	}

	$stylesheet = $request->get_param( 'stylesheet' );
	if ( is_string( $stylesheet ) && '' !== $stylesheet && get_stylesheet() !== $stylesheet ) {
		return $response;
	}

	$data = $response->get_data();
	if ( ! is_array( $data ) ) {
		return $response;
	}

	$filtered = array_values(
		array_filter(
			$data,
			static function ( $variation ) {
				if ( ! is_array( $variation ) ) {
					return false;
				}

				// Keep only non-color variations so the editor shows a single palette source.
				return empty( $variation['settings']['color']['palette'] );
			}
		)
	);

	$response->set_data( $filtered );
	return $response;
}
add_filter( 'rest_request_after_callbacks', 'elodin_bridge_filter_global_styles_variations_response', 10, 3 );

/**
 * Remove persisted global-styles palette settings for the active stylesheet.
 *
 * @return bool True when at least one post was updated.
 */
function elodin_bridge_remove_persisted_global_styles_color_settings() {
	if ( ! elodin_bridge_is_remove_ollie_color_palettes_enabled() ) {
		return false;
	}

	$stylesheet = get_stylesheet();
	if ( ! is_string( $stylesheet ) || '' === $stylesheet ) {
		return false;
	}

	$global_styles_posts = get_posts(
		array(
			'post_type'      => 'wp_global_styles',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
		)
	);
	if ( ! is_array( $global_styles_posts ) || empty( $global_styles_posts ) ) {
		return false;
	}

	$expected_names = array(
		'wp-global-styles-' . $stylesheet,
		'wp-global-styles-' . urlencode( $stylesheet ),
	);
	$did_update = false;

	foreach ( $global_styles_posts as $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}

		$matches_post_name = in_array( (string) $post->post_name, $expected_names, true );
		$matches_wp_theme_term = false;
		$theme_terms = wp_get_post_terms( $post->ID, 'wp_theme', array( 'fields' => 'slugs' ) );
		if ( is_array( $theme_terms ) && in_array( $stylesheet, $theme_terms, true ) ) {
			$matches_wp_theme_term = true;
		}

		if ( ! $matches_post_name && ! $matches_wp_theme_term ) {
			continue;
		}

		$decoded = json_decode( (string) $post->post_content, true );
		if ( ! is_array( $decoded ) ) {
			continue;
		}

		if (
			! isset( $decoded['settings']['color'] ) ||
			! is_array( $decoded['settings']['color'] ) ||
			! array_key_exists( 'palette', $decoded['settings']['color'] )
		) {
			continue;
		}

		unset( $decoded['settings']['color']['palette'] );
		if ( empty( $decoded['settings']['color'] ) ) {
			unset( $decoded['settings']['color'] );
		}

		if ( isset( $decoded['settings'] ) && is_array( $decoded['settings'] ) && empty( $decoded['settings'] ) ) {
			unset( $decoded['settings'] );
		}

		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => wp_json_encode( $decoded ),
			)
		);
		$did_update = true;
	}

	if ( $did_update && function_exists( 'wp_clean_theme_json_cache' ) ) {
		wp_clean_theme_json_cache();
	}

	return $did_update;
}

/**
 * Clear theme.json caches when the palette-removal toggle changes.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $new_value New option value.
 */
function elodin_bridge_handle_remove_ollie_color_palettes_setting_change( $old_value, $new_value ) {
	if ( (bool) $old_value === (bool) $new_value ) {
		return;
	}

	$flag_option = 'elodin_bridge_remove_ollie_color_palettes_cache_reset_done';
	if ( (bool) $new_value ) {
		elodin_bridge_remove_persisted_global_styles_color_settings();
		update_option( $flag_option, 1, false );
	} else {
		delete_option( $flag_option );
	}

	if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
		wp_clean_theme_json_cache();
	}
}
add_action(
	'update_option_' . ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES,
	'elodin_bridge_handle_remove_ollie_color_palettes_setting_change',
	10,
	2
);

/**
 * Clear theme.json cache once after feature rollout so existing installs pick up removal.
 */
function elodin_bridge_maybe_prime_remove_ollie_color_palettes_cache_reset() {
	if ( ! elodin_bridge_is_remove_ollie_color_palettes_enabled() ) {
		return;
	}

	if ( ! function_exists( 'wp_clean_theme_json_cache' ) ) {
		return;
	}

	$flag_option = 'elodin_bridge_remove_ollie_color_palettes_cache_reset_done';
	if ( get_option( $flag_option, 0 ) ) {
		return;
	}

	elodin_bridge_remove_persisted_global_styles_color_settings();
	wp_clean_theme_json_cache();
	update_option( $flag_option, 1, false );
}
add_action( 'init', 'elodin_bridge_maybe_prime_remove_ollie_color_palettes_cache_reset', 20 );
