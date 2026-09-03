<?php

function add_action() {}
function add_filter() {}
function wp_parse_url( $url ) {
	return parse_url( $url );
}

require dirname( __DIR__ ) . '/inc/cover-youtube-backgrounds.php';

assert( elodin_bridge_is_youtube_embed_url( 'https://www.youtube.com/embed/abc123?controls=0' ) );
assert( elodin_bridge_is_youtube_embed_url( 'https://www.youtube-nocookie.com/embed/abc123' ) );
assert( ! elodin_bridge_is_youtube_embed_url( 'https://www.youtube.com/watch?v=abc123' ) );
assert( ! elodin_bridge_is_youtube_embed_url( '//www.youtube.com/embed/abc123' ) );
assert( ! elodin_bridge_is_youtube_embed_url( 'https://example.com/embed/abc123' ) );
