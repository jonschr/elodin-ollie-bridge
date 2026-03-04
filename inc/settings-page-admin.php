<?php

/**
 * Enqueue admin styles for the Bridge settings page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function elodin_bridge_enqueue_admin_assets( $hook_suffix ) {
	$allowed_hooks = array(
		'appearance_page_elodin-bridge',
	);
	if ( ! in_array( $hook_suffix, $allowed_hooks, true ) ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/admin-settings.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/admin-settings.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-admin',
		$style_url,
		array(),
		(string) filemtime( $style_path )
	);

	$script_path = ELODIN_BRIDGE_DIR . '/assets/admin-image-sizes.js';
	$script_url = ELODIN_BRIDGE_URL . 'assets/admin-image-sizes.js';
	if ( file_exists( $script_path ) ) {
		wp_enqueue_script(
			'elodin-bridge-admin-image-sizes',
			$script_url,
			array(),
			(string) filemtime( $script_path ),
			true
		);
	}

	$autosave_script_path = ELODIN_BRIDGE_DIR . '/assets/admin-autosave.js';
	$autosave_script_url = ELODIN_BRIDGE_URL . 'assets/admin-autosave.js';
	if ( file_exists( $autosave_script_path ) ) {
		wp_enqueue_script(
			'elodin-bridge-admin-autosave',
			$autosave_script_url,
			array(),
			(string) filemtime( $autosave_script_path ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_admin_assets' );

/**
 * Render the Ollie Bridge admin page under Appearance.
 */
function elodin_bridge_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$balanced_text_enabled = elodin_bridge_is_balanced_text_enabled();
	$heading_paragraph_overrides_enabled = elodin_bridge_is_heading_paragraph_overrides_enabled();
	$generateblocks_available = elodin_bridge_is_generateblocks_available();
	$editor_ui_restrictions_enabled = elodin_bridge_is_editor_ui_restrictions_enabled();
	$editor_publish_sidebar_restriction_enabled = elodin_bridge_is_editor_publish_sidebar_restriction_enabled();
	$editor_show_template_default_enabled = elodin_bridge_is_editor_show_template_default_enabled();
	$media_library_infinite_scrolling_enabled = elodin_bridge_is_media_library_infinite_scrolling_enabled();
	$shortcodes_enabled = elodin_bridge_is_shortcodes_enabled();
	$svg_uploads_enabled = elodin_bridge_is_svg_uploads_enabled();
	$css_variable_autowrap_enabled = elodin_bridge_is_css_variable_autowrap_enabled();
	$generateblocks_boundary_highlights_enabled = elodin_bridge_is_generateblocks_boundary_highlights_enabled();
	$editor_group_border_enabled = elodin_bridge_is_editor_group_border_enabled();
	$edit_site_admin_bar_links_enabled = elodin_bridge_is_edit_site_admin_bar_links_enabled();
	$mobile_fixed_background_repair_enabled = elodin_bridge_is_mobile_fixed_background_repair_enabled();
	$remove_ollie_color_palettes_enabled = elodin_bridge_is_remove_ollie_color_palettes_enabled();
	$ollie_pro_available = class_exists( 'olpo\\Helper' );
	$block_edge_class_settings = elodin_bridge_get_block_edge_class_settings();
	$block_edge_classes_enabled = elodin_bridge_is_block_edge_classes_enabled();
	$image_sizes_settings = elodin_bridge_get_image_sizes_settings();
	$image_size_rows = array_values( $image_sizes_settings['sizes'] );
	$using_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	$editor_show_template_default_available = $using_block_theme;

	$template_path = ELODIN_BRIDGE_DIR . '/inc/views/settings-page.php';
	if ( ! file_exists( $template_path ) ) {
		return;
	}

	require $template_path;
}

/**
 * Register the Ollie Bridge settings page in the Appearance menu.
 */
function elodin_bridge_register_admin_menu() {
	add_theme_page(
		__( 'Ollie Bridge', 'elodin-bridge' ),
		__( 'Ollie Bridge', 'elodin-bridge' ),
		'edit_theme_options',
		'elodin-bridge',
		'elodin_bridge_render_admin_page'
	);
}
add_action( 'admin_menu', 'elodin_bridge_register_admin_menu', 999 );

/**
 * Align settings save capability with the Bridge settings page capability.
 *
 * @return string
 */
function elodin_bridge_settings_page_capability() {
	return 'edit_theme_options';
}
add_filter( 'option_page_capability_elodin_bridge_settings', 'elodin_bridge_settings_page_capability' );
