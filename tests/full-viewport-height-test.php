<?php

require __DIR__ . '/cover-min-heights-test.php';

$full_height_enabled = true;
function elodin_bridge_get_header_aware_positioning_settings() {
	return array( 'full_height' => array( 'enabled' => $GLOBALS['full_height_enabled'] ) );
}
require dirname( __DIR__ ) . '/inc/full-viewport-height.php';

$cover = array( 'blockName' => 'core/cover', 'attrs' => array( 'minHeight' => 100, 'minHeightUnit' => 'vh' ) );
$markup = '<div class="wp-block-cover" style="min-height:100vh!important;padding:1em"><div style="height:100vh"></div></div>';
$result = elodin_bridge_render_full_viewport_height( $markup, $cover );
assert( str_contains( $result, 'min-height:max(0px,calc(100vh - var(--elodin-bridge-full-height-offset,0px)))!important' ) );
assert( str_contains( $result, 'padding:1em' ) );
assert( str_contains( $result, '<div style="height:100vh">' ) );
assert( 1 === substr_count( $result, 'min-height:' ) );

foreach ( array( '100vh', '100.0dvh', '100svh', '100lvh' ) as $value ) {
	$group = array( 'blockName' => 'core/group', 'attrs' => array( 'style' => array( 'dimensions' => array( 'height' => $value, 'minHeight' => $value ) ) ) );
	$result = elodin_bridge_render_full_viewport_height( '<section style="color:red"></section>', $group );
	assert( str_contains( $result, ';height:max(' ) );
	assert( str_contains( $result, ';min-height:max(' ) );
	assert( str_contains( $result, 'color:red' ) );
}

foreach ( array( '100%', '90vh', '100px', 'calc(100vh - 20px)', 'var(--custom-height)', '100vw' ) as $value ) {
	$group = array( 'blockName' => 'core/group', 'attrs' => array( 'style' => array( 'dimensions' => array( 'minHeight' => $value ) ) ) );
	assert( $markup === elodin_bridge_render_full_viewport_height( $markup, $group ) );
}
$cover['attrs']['style']['dimensions']['minHeight'] = '90vh';
assert( $markup === elodin_bridge_render_full_viewport_height( $markup, $cover ) );
unset( $cover['attrs']['style'] );
$full_height_enabled = false;
assert( $markup === elodin_bridge_render_full_viewport_height( $markup, $cover ) );
