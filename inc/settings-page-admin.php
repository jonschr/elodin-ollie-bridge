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

	$feature_video_script_path = ELODIN_BRIDGE_DIR . '/assets/admin-feature-videos.js';
	$feature_video_script_url = ELODIN_BRIDGE_URL . 'assets/admin-feature-videos.js';
	if ( file_exists( $feature_video_script_path ) ) {
		wp_enqueue_script(
			'elodin-bridge-admin-feature-videos',
			$feature_video_script_url,
			array(),
			(string) filemtime( $feature_video_script_path ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_admin_assets' );

/**
 * Get the library of feature walkthrough videos for the Bridge settings UI.
 *
 * @return array<string,array{url:string,title:string,aspect_ratio?:string}>
 */
function elodin_bridge_get_feature_videos() {
	$videos = array(
		'balanced_text' => array(
			'url'          => 'https://www.loom.com/embed/f0f44c08584c4efd94d1261cc6e24520',
			'title'        => __( 'Balanced text feature walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'heading_paragraph_overrides' => array(
			'url'          => 'https://www.loom.com/embed/7e19ac22e9eb4bf38fa53e37465edd53',
			'title'        => __( 'Heading and paragraph style overrides walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'edit_site_admin_bar_links' => array(
			'url'          => 'https://www.loom.com/embed/547f756e6160489cb276ae7e9e6882ef',
			'title'        => __( 'Edit Site admin bar shortcut links walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'site_editor_admin_bar' => array(
			'url'          => 'https://www.loom.com/embed/66392e10c52a4722888f67d1950f30a5',
			'title'        => __( 'Show WP admin bar in Site Editor walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'editor_group_border' => array(
			'url'          => 'https://www.loom.com/embed/044ba4428a3848b7b47f1fc053e250d4',
			'title'        => __( 'Core block boundary highlights walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'editor_fullscreen_show_template' => array(
			'url'          => 'https://www.loom.com/embed/6a5f02ab693c4eeb8eb6eeb7b23382fb',
			'title'        => __( 'Editor fullscreen and Show template walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'css_variable_autowrap' => array(
			'url'          => 'https://www.loom.com/embed/4613c2bdd96c445fb795765fe181084d',
			'title'        => __( 'Auto-wrap spacing/font variables walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
		'remove_ollie_color_palettes' => array(
			'url'          => 'https://www.loom.com/embed/d1e8c447d0e54ca6b2a3e1954e2fbe7d',
			'title'        => __( 'Remove Ollie color palettes walkthrough', 'elodin-bridge' ),
			'aspect_ratio' => '16:9',
		),
	);

	/**
	 * Filter the feature video library used in the Bridge settings UI.
	 *
	 * @param array<string,array{url:string,title:string,aspect_ratio?:string}> $videos Feature video map.
	 */
	return apply_filters( 'elodin_bridge_feature_videos', $videos );
}

/**
 * Get normalized feature video data for a specific settings feature key.
 *
 * @param string $feature_key Feature key.
 * @return array{url:string,title:string,aspect_ratio:string}
 */
function elodin_bridge_get_feature_video( $feature_key ) {
	$feature_key = sanitize_key( (string) $feature_key );
	if ( '' === $feature_key ) {
		return array();
	}

	$videos = elodin_bridge_get_feature_videos();
	if ( ! isset( $videos[ $feature_key ] ) || ! is_array( $videos[ $feature_key ] ) ) {
		return array();
	}

	$video = $videos[ $feature_key ];
	$url = esc_url_raw( (string) ( $video['url'] ?? '' ) );
	if ( '' === $url ) {
		return array();
	}

	$title = sanitize_text_field( (string) ( $video['title'] ?? __( 'Feature walkthrough', 'elodin-bridge' ) ) );
	if ( '' === $title ) {
		$title = __( 'Feature walkthrough', 'elodin-bridge' );
	}

	$aspect_ratio = (string) ( $video['aspect_ratio'] ?? '16:9' );
	if ( ! preg_match( '/^\s*\d+\s*[:\/]\s*\d+\s*$/', $aspect_ratio ) ) {
		$aspect_ratio = '16:9';
	}
	$aspect_ratio = preg_replace( '/\s+/', '', $aspect_ratio );
	if ( ! is_string( $aspect_ratio ) || '' === $aspect_ratio ) {
		$aspect_ratio = '16:9';
	}

	return array(
		'url'          => $url,
		'title'        => $title,
		'aspect_ratio' => (string) $aspect_ratio,
	);
}

/**
 * Render a reusable "Learn about this feature" video trigger button.
 *
 * @param string $feature_key Feature key in the video map.
 */
function elodin_bridge_render_feature_video_trigger( $feature_key ) {
	$video = elodin_bridge_get_feature_video( $feature_key );
	if ( empty( $video['url'] ) ) {
		return;
	}
	?>
	<div class="elodin-bridge-admin__feature-actions">
		<button
			type="button"
			class="elodin-bridge-admin__learn-link"
			data-elodin-video-open
			data-elodin-video-url="<?php echo esc_url( $video['url'] ); ?>"
			data-elodin-video-title="<?php echo esc_attr( $video['title'] ); ?>"
			data-elodin-video-aspect-ratio="<?php echo esc_attr( $video['aspect_ratio'] ); ?>"
		>
			<span class="elodin-bridge-admin__learn-link-icon elodin-bridge-admin__learn-link-icon--play" aria-hidden="true">
				<svg viewBox="0 0 12 12" role="presentation" focusable="false" aria-hidden="true">
					<path d="M3 2.2v7.6L9 6 3 2.2z"></path>
				</svg>
			</span>
			<?php esc_html_e( 'See this in action', 'elodin-bridge' ); ?>
		</button>
	</div>
	<?php
}

/**
 * Render the Ollie Bridge admin page under Appearance.
 */
function elodin_bridge_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$balanced_text_enabled = elodin_bridge_is_balanced_text_enabled();
	$heading_paragraph_overrides_enabled = elodin_bridge_is_heading_paragraph_overrides_enabled();
	$heading_non_first_margin_top_enabled = elodin_bridge_is_heading_non_first_margin_top_enabled();
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
	$site_editor_admin_bar_enabled = elodin_bridge_is_site_editor_admin_bar_enabled();
	$mobile_fixed_background_repair_enabled = elodin_bridge_is_mobile_fixed_background_repair_enabled();
	$remove_ollie_color_palettes_enabled = elodin_bridge_is_remove_ollie_color_palettes_enabled();
	$child_theme_gradient_duotone_autogen_enabled = elodin_bridge_is_child_theme_gradient_duotone_autogen_enabled();
	$nested_group_shortcut_enabled = elodin_bridge_is_nested_group_shortcut_enabled();
	$ollie_pro_available = class_exists( 'olpo\\Helper' );
	$block_edge_class_settings = elodin_bridge_get_block_edge_class_settings();
	$block_edge_classes_enabled = elodin_bridge_is_block_edge_classes_enabled();
	$image_sizes_settings = elodin_bridge_get_image_sizes_settings();
	$image_size_rows = array_values( $image_sizes_settings['sizes'] );
	$using_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	$editor_show_template_default_available = $using_block_theme;
	$site_editor_admin_bar_available = $using_block_theme;

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
