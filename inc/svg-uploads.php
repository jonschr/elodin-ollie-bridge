<?php

/**
 * Allow SVG uploads when the Bridge setting is enabled.
 *
 * @param array<string,string> $mime_types Allowed mime types.
 * @param int|WP_User|null     $user       User ID, user object, or null.
 * @return array<string,string>
 */
function elodin_bridge_allow_svg_uploads( $mime_types, $user ) {
	unset( $user );

	if ( ! elodin_bridge_is_svg_uploads_enabled() ) {
		return $mime_types;
	}

	$mime_types['svg'] = 'image/svg+xml';
	$mime_types['svgz'] = 'image/svg+xml';

	return $mime_types;
}
add_filter( 'upload_mimes', 'elodin_bridge_allow_svg_uploads', 10, 2 );

/**
 * Mirror SVG Support's upload check fallback when WordPress reports an empty type.
 *
 * @param array{ext:string|false,type:string|false,proper_filename:string|false} $checked  File type data.
 * @param string                                                                  $file     Full path to uploaded file.
 * @param string                                                                  $filename Original uploaded filename.
 * @param array<string,string>|null                                               $mimes    Allowed mime mappings.
 * @return array{ext:string|false,type:string|false,proper_filename:string|false}
 */
function elodin_bridge_svg_upload_check( $checked, $file, $filename, $mimes ) {
	unset( $file );

	if ( ! elodin_bridge_is_svg_uploads_enabled() ) {
		return $checked;
	}

	if ( ! empty( $checked['type'] ) ) {
		return $checked;
	}

	$check_filetype = wp_check_filetype( $filename, $mimes );
	$ext = $check_filetype['ext'] ?? false;
	$type = $check_filetype['type'] ?? false;
	$proper_filename = $filename;

	if (
		( 'svg' === $ext || 'svgz' === $ext ) &&
		'image/svg+xml' === $type
	) {
		$checked = compact( 'ext', 'type', 'proper_filename' );
	}

	return $checked;
}
add_filter( 'wp_check_filetype_and_ext', 'elodin_bridge_svg_upload_check', 10, 4 );

/**
 * Parse a numeric SVG dimension from raw attribute values like "120", "120px", etc.
 *
 * @param string $value Raw dimension attribute.
 * @return int
 */
function elodin_bridge_parse_svg_dimension( $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return 0;
	}

	if ( ! preg_match( '/([0-9]+(?:\.[0-9]+)?)/', $value, $matches ) ) {
		return 0;
	}

	return max( 0, (int) round( (float) $matches[1] ) );
}

/**
 * Read SVG dimensions from a local file path.
 *
 * @param string $svg_path Full file path to SVG.
 * @return array{width:int,height:int}
 */
function elodin_bridge_get_svg_dimensions( $svg_path ) {
	$dimensions = array(
		'width'  => 0,
		'height' => 0,
	);

	if ( ! is_string( $svg_path ) || '' === $svg_path || ! is_readable( $svg_path ) ) {
		return $dimensions;
	}

	$svg_content = file_get_contents( $svg_path );
	if ( false === $svg_content || '' === trim( $svg_content ) ) {
		return $dimensions;
	}

	$previous_libxml_state = libxml_use_internal_errors( true );
	$svg = simplexml_load_string( $svg_content );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_libxml_state );

	if ( false === $svg ) {
		return $dimensions;
	}

	$attributes = $svg->attributes();
	$width = isset( $attributes->width ) ? elodin_bridge_parse_svg_dimension( (string) $attributes->width ) : 0;
	$height = isset( $attributes->height ) ? elodin_bridge_parse_svg_dimension( (string) $attributes->height ) : 0;

	if ( ( $width < 1 || $height < 1 ) && isset( $attributes->viewBox ) ) {
		$viewbox_values = preg_split( '/[\s,]+/', trim( (string) $attributes->viewBox ) );
		if ( is_array( $viewbox_values ) && 4 === count( $viewbox_values ) ) {
			if ( $width < 1 ) {
				$width = max( 0, (int) round( (float) $viewbox_values[2] ) );
			}
			if ( $height < 1 ) {
				$height = max( 0, (int) round( (float) $viewbox_values[3] ) );
			}
		}
	}

	$dimensions['width'] = $width;
	$dimensions['height'] = $height;

	return $dimensions;
}

/**
 * Ensure SVG attachments include a `full` size in media modal responses.
 *
 * @param array<string,mixed> $response   Prepared attachment response.
 * @param WP_Post             $attachment Attachment object.
 * @param array<string,mixed> $meta       Attachment metadata.
 * @return array<string,mixed>
 */
function elodin_bridge_prepare_svg_attachment_for_js( $response, $attachment, $meta ) {
	unset( $meta );

	if ( ! elodin_bridge_is_svg_uploads_enabled() ) {
		return $response;
	}

	if ( ! is_array( $response ) || 'image/svg+xml' !== ( $response['mime'] ?? '' ) ) {
		return $response;
	}

	if ( ! empty( $response['sizes'] ) ) {
		return $response;
	}

	$svg_path = get_attached_file( $attachment->ID );
	$dimensions = elodin_bridge_get_svg_dimensions( $svg_path );

	$orientation = 'landscape';
	if ( $dimensions['height'] > $dimensions['width'] ) {
		$orientation = 'portrait';
	} elseif ( $dimensions['height'] === $dimensions['width'] && $dimensions['width'] > 0 ) {
		$orientation = 'square';
	}

	$response['sizes'] = array(
		'full' => array(
			'url'         => $response['url'] ?? '',
			'width'       => $dimensions['width'],
			'height'      => $dimensions['height'],
			'orientation' => $orientation,
		),
	);

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'elodin_bridge_prepare_svg_attachment_for_js', 10, 3 );
