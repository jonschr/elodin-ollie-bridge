<?php

/**
 * Register the Sticky Below Header block style.
 */
function elodin_bridge_register_sticky_below_header_block_style() {
	if ( ! elodin_bridge_is_sticky_below_header_block_style_enabled() || ! function_exists( 'register_block_style' ) || ! class_exists( 'WP_Block_Type_Registry' ) ) {
		return;
	}

	$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
	$block_names = array_keys( $block_types );
	if ( empty( $block_names ) ) {
		return;
	}

	register_block_style(
		$block_names,
		array(
			'name'  => 'sticky-below-header',
			'label' => __( 'Sticky Below Header', 'elodin-bridge' ),
		)
	);
}
add_action( 'init', 'elodin_bridge_register_sticky_below_header_block_style' );

/**
 * Enqueue Sticky Below Header frontend assets.
 */
function elodin_bridge_enqueue_sticky_below_header_block_style_assets() {
	$settings = elodin_bridge_get_header_aware_positioning_settings();
	if ( empty( $settings['sticky']['enabled'] ) ) {
		return;
	}

	$handle = 'elodin-bridge-sticky-below-header-block-style';
	$css = sprintf(
		':root{--elodin-bridge-sticky-below-header-bonus-offset:%1$s;--elodin-bridge-sticky-below-header-offset:calc(var(--elodin-bridge-header-height,0px) + var(--elodin-bridge-sticky-below-header-bonus-offset,var(--wp--preset--spacing--large,3rem)));}.is-style-sticky-below-header{position:sticky;top:var(--elodin-bridge-sticky-below-header-offset);z-index:2;}',
		$settings['sticky']['offset']
	);

	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_sticky_below_header_block_style_assets' );
