<?php

/**
 * Enable direct MP4 URLs in Cover backgrounds and Ollie Pro button modals.
 */
function elodin_bridge_enqueue_direct_cover_video_urls() {
	if ( ! elodin_bridge_is_direct_cover_video_urls_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/editor-direct-cover-video-urls.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-editor-direct-cover-video-urls',
		ELODIN_BRIDGE_URL . 'assets/editor-direct-cover-video-urls.js',
		array( 'wp-data' ),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_direct_cover_video_urls' );

/**
 * Check whether a URL points to an HTTP(S) MP4 file.
 *
 * @param mixed $url URL to check.
 * @return bool
 */
function elodin_bridge_is_direct_mp4_url( $url ) {
	if ( ! is_string( $url ) ) {
		return false;
	}

	$url = trim( $url );
	if ( preg_match( '/[\x00-\x20\x7F\"\'<>]/', $url ) ) {
		return false;
	}

	$parts = wp_parse_url( $url );

	return is_array( $parts )
		&& ! empty( $parts['host'] )
		&& isset( $parts['scheme'], $parts['path'] )
		&& ! isset( $parts['user'] )
		&& ! isset( $parts['pass'] )
		&& in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true )
		&& 1 === preg_match( '/\.mp4$/i', $parts['path'] );
}

/**
 * Let Ollie Pro's button modal play a direct MP4 entered in its YouTube field.
 *
 * @param string $block_content Rendered block markup.
 * @param array  $block         Parsed block data.
 * @return string
 */
function elodin_bridge_render_direct_button_video_modal( $block_content, $block ) {
	if (
		! elodin_bridge_is_direct_cover_video_urls_enabled()
		|| 'core/button' !== ( $block['blockName'] ?? '' )
		|| ! function_exists( 'ollie_pro_video_modal_enqueue_assets' )
	) {
		return $block_content;
	}

	$attributes = $block['attrs'] ?? array();
	$video_url  = $attributes['ollieVideoUrl'] ?? '';

	if ( 'youtube' !== ( $attributes['ollieVideoSource'] ?? 'youtube' ) || ! elodin_bridge_is_direct_mp4_url( $video_url ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( 'a' ) ) {
		return $block_content;
	}

	ollie_pro_video_modal_enqueue_assets();

	$processor->add_class( 'ollie-video-modal-trigger' );
	$processor->set_attribute( 'data-video-source', 'library' );
	$processor->set_attribute( 'data-video-url', esc_url( trim( $video_url ) ) );
	$video_autoplay = isset( $attributes['ollieVideoAutoplay'] ) ? (bool) $attributes['ollieVideoAutoplay'] : true;
	$processor->set_attribute( 'data-video-autoplay', $video_autoplay ? 'true' : 'false' );
	$processor->set_attribute( 'aria-label', __( 'Play video', 'elodin-bridge' ) );

	return $processor->get_updated_html();
}
add_filter( 'render_block', 'elodin_bridge_render_direct_button_video_modal', 11, 2 );
