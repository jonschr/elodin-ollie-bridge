<?php

function add_action() {}
function add_filter() {}
function elodin_bridge_is_cover_min_heights_enabled() { return true; }
function wp_has_noncharacters() { return false; }
function wp_kses_uri_attributes() { return array(); }

$wp_includes = dirname( __DIR__, 4 ) . '/wp-includes/';
require $wp_includes . 'class-wp-token-map.php';
foreach ( array(
	'html5-named-character-references.php',
	'class-wp-html-attribute-token.php',
	'class-wp-html-span.php',
	'class-wp-html-doctype-info.php',
	'class-wp-html-text-replacement.php',
	'class-wp-html-decoder.php',
	'class-wp-html-tag-processor.php',
) as $file ) {
	require $wp_includes . 'html-api/' . $file;
}

require dirname( __DIR__ ) . '/inc/cover-min-heights.php';

$markup = '<div class="wp-block-cover" style="min-height:25px;padding:1em"></div>';
$rendered = elodin_bridge_preserve_cover_min_height( $markup, array( 'attrs' => array( 'minHeight' => 25 ) ) );
assert( str_contains( $rendered, 'min-height:25px!important' ) );
assert( str_contains( $rendered, 'padding:1em' ) );

$zero = elodin_bridge_preserve_cover_min_height(
	'<div class="wp-block-cover"></div>',
	array( 'attrs' => array( 'minHeight' => 0, 'minHeightUnit' => 'px' ) )
);
assert( str_contains( $zero, 'min-height:0px!important' ) );

$unset = elodin_bridge_preserve_cover_min_height( '<div class="wp-block-cover"></div>', array( 'attrs' => array() ) );
assert( '<div class="wp-block-cover"></div>' === $unset );
