<?php

/**
 * Build FSE root content direct-child top margin reset CSS.
 *
 * @return string
 */
function elodin_bridge_build_fse_content_top_margin_reset_css() {
	if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
		return '';
	}

	$selectors = array(
		'.is-root-container > .wp-block-group',
		'.is-root-container > .wp-block-generateblocks-element',
		'.is-root-container > :is([class^=\'gb-element-\'], [class*=\' gb-element-\'])',
		'.is-root-container > div:not([class])',
		'.wp-block-post-content > .wp-block-group',
		'.wp-block-post-content > .wp-block-generateblocks-element',
		'.wp-block-post-content > :is([class^=\'gb-element-\'], [class*=\' gb-element-\'])',
		'.wp-block-post-content > div:not([class])',
	);

	$selector_list = implode( ',', $selectors );
	if ( '' === $selector_list ) {
		return '';
	}

	$css = $selector_list . '{margin-top:0;margin-block-start:0;}';

	$entry_following_header_selectors = array(
		'.site-header + .entry-content',
		'.site-header + .entry-contet',
		'.wp-block-template-part + .wp-block-post-content',
	);
	$entry_following_header_selector_list = implode( ',', $entry_following_header_selectors );
	if ( '' !== $entry_following_header_selector_list ) {
		$css .= $entry_following_header_selector_list . '{margin-top:0;margin-block-start:0;}';
	}

	$editor_post_content_after_template_part_selectors = array(
		'[data-type="core/template-part"] + [data-type="core/post-content"]',
		'.block-editor-block-list__block[data-type="core/template-part"] + .block-editor-block-list__block[data-type="core/post-content"]',
	);
	$editor_post_content_after_template_part_selector_list = implode( ',', $editor_post_content_after_template_part_selectors );
	if ( '' !== $editor_post_content_after_template_part_selector_list ) {
		$css .= $editor_post_content_after_template_part_selector_list . '{margin-top:0;margin-block-start:0;}';
	}

	return $css;
}

/**
 * Enqueue FSE root content direct-child top margin reset styles.
 */
function elodin_bridge_enqueue_fse_content_top_margin_reset_styles() {
	$css = elodin_bridge_build_fse_content_top_margin_reset_css();
	if ( '' === $css ) {
		return;
	}

	$handle = 'elodin-bridge-fse-content-top-margin-reset';
	wp_register_style( $handle, false, array(), ELODIN_BRIDGE_VERSION );
	wp_enqueue_style( $handle );
	wp_add_inline_style( $handle, $css );
}
add_action( 'enqueue_block_assets', 'elodin_bridge_enqueue_fse_content_top_margin_reset_styles' );
