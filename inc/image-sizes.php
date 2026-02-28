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
		if ( empty( $size['gallery'] ) ) {
			continue;
		}

		$label = ! empty( $size['label'] ) ? (string) $size['label'] : ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
		$sizes[ $slug ] = $label;
	}

	return $sizes;
}
add_filter( 'image_size_names_choose', 'elodin_bridge_add_gallery_sizes' );
