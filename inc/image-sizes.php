<?php

/**
 * Register Bridge image sizes.
 */
function elodin_bridge_register_image_sizes() {
	if ( ! elodin_bridge_is_image_sizes_enabled() ) {
		return;
	}

	$sizes = elodin_bridge_get_registered_bridge_image_sizes();
	foreach ( $sizes as $slug => $size ) {
		$width = isset( $size['width'] ) ? absint( $size['width'] ) : 0;
		$height = isset( $size['height'] ) ? absint( $size['height'] ) : 0;
		if ( $width < 1 || $height < 1 ) {
			continue;
		}

		add_image_size( $slug, $width, $height, ! empty( $size['crop'] ) );
	}
}
add_action( 'after_setup_theme', 'elodin_bridge_register_image_sizes', 20 );

/**
 * Remove legacy theme gallery-size filter when Bridge image sizes are enabled.
 */
function elodin_bridge_remove_legacy_gallery_size_filter() {
	if ( ! elodin_bridge_is_image_sizes_enabled() ) {
		return;
	}

	// Remove across a priority range so older themes with custom priorities are covered.
	for ( $priority = 1; $priority <= 100; $priority++ ) {
		remove_filter( 'image_size_names_choose', 'ettt_add_gallery_sizes', $priority );
	}
}
add_action( 'after_setup_theme', 'elodin_bridge_remove_legacy_gallery_size_filter', 100 );

/**
 * Add allowed Bridge sizes to the gallery/image size chooser.
 *
 * @param array<string,string> $sizes Existing size labels.
 * @return array<string,string>
 */
function elodin_bridge_add_gallery_sizes( $sizes ) {
	if ( ! elodin_bridge_is_image_sizes_enabled() ) {
		return $sizes;
	}

	foreach ( elodin_bridge_get_registered_bridge_image_sizes() as $slug => $size ) {
		$label = ! empty( $size['label'] ) ? (string) $size['label'] : ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
		$sizes[ $slug ] = $label;
	}

	return $sizes;
}
add_filter( 'image_size_names_choose', 'elodin_bridge_add_gallery_sizes', 100 );

/**
 * Ensure Bridge sizes are included in block editor image settings.
 *
 * @param array<string,mixed> $settings      Editor settings.
 * @param mixed               $editor_context Editor context object.
 * @return array<string,mixed>
 */
function elodin_bridge_inject_image_sizes_into_block_editor_settings( $settings, $editor_context ) {
	unset( $editor_context );

	if ( ! elodin_bridge_is_image_sizes_enabled() ) {
		return $settings;
	}

	$bridge_sizes = elodin_bridge_get_registered_bridge_image_sizes();
	if ( empty( $bridge_sizes ) ) {
		return $settings;
	}

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$image_sizes = isset( $settings['imageSizes'] ) && is_array( $settings['imageSizes'] ) ? $settings['imageSizes'] : array();
	$image_dimensions = isset( $settings['imageDimensions'] ) && is_array( $settings['imageDimensions'] ) ? $settings['imageDimensions'] : array();
	$registered_sizes = wp_get_registered_image_subsizes();

	foreach ( $bridge_sizes as $slug => $size ) {
		$has_dimensions = isset( $image_dimensions[ $slug ] ) && is_array( $image_dimensions[ $slug ] );
		if ( ! $has_dimensions && isset( $registered_sizes[ $slug ] ) && is_array( $registered_sizes[ $slug ] ) ) {
			$image_dimensions[ $slug ] = $registered_sizes[ $slug ];
			$has_dimensions = true;
		}

		if ( ! $has_dimensions && isset( $size['width'], $size['height'] ) ) {
			$image_dimensions[ $slug ] = array(
				'width'  => absint( $size['width'] ),
				'height' => absint( $size['height'] ),
				'crop'   => ! empty( $size['crop'] ),
			);
			$has_dimensions = true;
		}

		if ( ! $has_dimensions ) {
			continue;
		}

		$label = ! empty( $size['label'] ) ? (string) $size['label'] : ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
		$already_registered = false;
		foreach ( $image_sizes as $existing_size ) {
			if ( isset( $existing_size['slug'] ) && $existing_size['slug'] === $slug ) {
				$already_registered = true;
				break;
			}
		}

		if ( ! $already_registered ) {
			$image_sizes[] = array(
				'slug' => $slug,
				'name' => $label,
			);
		}
	}

	if ( ! empty( $image_sizes ) ) {
		$settings['imageSizes'] = $image_sizes;
	}

	if ( ! empty( $image_dimensions ) ) {
		$settings['imageDimensions'] = $image_dimensions;
	}

	return $settings;
}
add_filter( 'block_editor_settings_all', 'elodin_bridge_inject_image_sizes_into_block_editor_settings', 999, 2 );
