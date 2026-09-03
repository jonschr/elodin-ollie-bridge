<?php

/**
 * Check whether a URL is a YouTube embed URL.
 *
 * @param mixed $url URL to check.
 * @return bool
 */
function elodin_bridge_is_youtube_embed_url( $url ) {
	if ( ! is_string( $url ) ) {
		return false;
	}

	$parts = wp_parse_url( html_entity_decode( $url, ENT_QUOTES | ENT_HTML5 ) );
	if (
		! is_array( $parts )
		|| empty( $parts['scheme'] )
		|| ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true )
		|| empty( $parts['host'] )
		|| empty( $parts['path'] )
	) {
		return false;
	}

	$host = strtolower( $parts['host'] );
	$is_youtube_host = 'youtube.com' === $host
		|| str_ends_with( $host, '.youtube.com' )
		|| 'youtube-nocookie.com' === $host
		|| str_ends_with( $host, '.youtube-nocookie.com' );

	return $is_youtube_host && str_starts_with( $parts['path'], '/embed/' );
}

/**
 * Apply configured YouTube background behavior to Cover blocks.
 *
 * @param string $block_content Rendered Cover markup.
 * @param array  $block         Parsed block data.
 * @return string
 */
function elodin_bridge_render_cover_youtube_backgrounds( $block_content, $block ) {
	$hide_controls = elodin_bridge_is_cover_youtube_controls_hidden();
	$cover_video = elodin_bridge_is_cover_youtube_video_cover_enabled();
	if (
		( ! $hide_controls && ! $cover_video )
		|| 'embed-video' !== ( $block['attrs']['backgroundType'] ?? '' )
		|| ! class_exists( 'WP_HTML_Tag_Processor' )
	) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag( 'iframe' ) ) {
		return $block_content;
	}

	$src = $processor->get_attribute( 'src' );
	if ( ! elodin_bridge_is_youtube_embed_url( $src ) ) {
		return $block_content;
	}

	if ( $hide_controls ) {
		$processor->set_attribute(
			'src',
			add_query_arg(
				array(
					'controls'       => 0,
					'disablekb'      => 1,
					'fs'             => 0,
					'iv_load_policy' => 3,
					'playsinline'     => 1,
				),
				html_entity_decode( $src, ENT_QUOTES | ENT_HTML5 )
			)
		);
		$processor->add_class( 'elodin-bridge-cover-youtube-controls-hidden' );
	}

	if ( $cover_video ) {
		$processor->add_class( 'elodin-bridge-cover-youtube-cover' );
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/cover', 'elodin_bridge_render_cover_youtube_backgrounds', 10, 2 );

/**
 * Add frontend sizing for YouTube Cover backgrounds.
 */
function elodin_bridge_enqueue_cover_youtube_background_styles() {
	if ( ! elodin_bridge_is_cover_youtube_video_cover_enabled() ) {
		return;
	}

	$handle = 'elodin-bridge-cover-youtube-backgrounds';
	$css = '.wp-block-cover .wp-block-cover__embed-background iframe.elodin-bridge-cover-youtube-cover{aspect-ratio:16/9;height:auto;min-height:100%;min-width:100%;width:auto;}';

	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_cover_youtube_background_styles' );
