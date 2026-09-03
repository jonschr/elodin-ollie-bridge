<?php
/*
	Plugin Name: Ollie Bridge
	Plugin URI: https://elod.in
    Description: Just another plugin
	Version: 0.7
    Author: Jon Schroeder
    Author URI: https://elod.in

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/

/* Prevent direct access to the plugin */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Sorry, you are not allowed to access this page directly.' );
}

// Plugin constants.
define( 'ELODIN_BRIDGE_PLUGIN_FILE', __FILE__ );
define( 'ELODIN_BRIDGE_DIR', dirname( __FILE__ ) );
define( 'ELODIN_BRIDGE_URL', plugin_dir_url( __FILE__ ) );
define( 'ELODIN_BRIDGE_VERSION', '0.7' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES', 'elodin_bridge_enable_heading_paragraph_overrides' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_HEADING_NON_FIRST_MARGIN_TOP', 'elodin_bridge_enable_heading_non_first_margin_top' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS', 'elodin_bridge_enable_editor_ui_restrictions' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION', 'elodin_bridge_enable_editor_publish_sidebar_restriction' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT', 'elodin_bridge_enable_editor_show_template_default' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_LIST_VIEW_DEFAULT', 'elodin_bridge_enable_editor_list_view_default' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING', 'elodin_bridge_enable_media_library_infinite_scrolling' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES', 'elodin_bridge_enable_shortcodes' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS', 'elodin_bridge_enable_svg_uploads' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_COVER_MIN_HEIGHTS', 'elodin_bridge_enable_cover_min_heights' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_DIRECT_COVER_VIDEO_URLS', 'elodin_bridge_enable_direct_cover_video_urls' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_DIRECT_VIDEO_MODAL_URLS', 'elodin_bridge_enable_direct_video_modal_urls' );
define( 'ELODIN_BRIDGE_OPTION_HIDE_COVER_YOUTUBE_CONTROLS', 'elodin_bridge_hide_cover_youtube_controls' );
define( 'ELODIN_BRIDGE_OPTION_COVER_YOUTUBE_VIDEOS', 'elodin_bridge_cover_youtube_videos' );
define( 'ELODIN_BRIDGE_OPTION_DISABLE_VIDEO_DOWNLOADS', 'elodin_bridge_disable_video_downloads' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER', 'elodin_bridge_enable_editor_group_border' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_EDIT_SITE_ADMIN_BAR_LINKS', 'elodin_bridge_enable_edit_site_admin_bar_links' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_SITE_EDITOR_ADMIN_BAR', 'elodin_bridge_enable_site_editor_admin_bar' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR', 'elodin_bridge_enable_mobile_fixed_background_repair' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_STICKY_BELOW_HEADER_BLOCK_STYLE', 'elodin_bridge_enable_sticky_below_header_block_style' );
define( 'ELODIN_BRIDGE_OPTION_HEADER_AWARE_POSITIONING', 'elodin_bridge_header_aware_positioning' );
define( 'ELODIN_BRIDGE_OPTION_SCROLL_HIDE_ELEMENTS', 'elodin_bridge_scroll_hide_elements' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES', 'elodin_bridge_enable_remove_ollie_color_palettes' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_CHILD_THEME_GRADIENT_DUOTONE_AUTOGEN', 'elodin_bridge_enable_child_theme_gradient_duotone_autogen' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_NESTED_GROUP_SHORTCUT', 'elodin_bridge_enable_nested_group_shortcut' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_CHECKERBOARD_PATTERN', 'elodin_bridge_enable_checkerboard_pattern' );
define( 'ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES', 'elodin_bridge_block_edge_classes' );
define( 'ELODIN_BRIDGE_OPTION_IMAGE_SIZES', 'elodin_bridge_image_sizes' );
define( 'ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP', 'elodin_bridge_enable_css_variable_autowrap' );
define( 'ELODIN_BRIDGE_UPDATE_REPOSITORY', 'https://github.com/jonschr/elodin-ollie-bridge' );
define( 'ELODIN_BRIDGE_UPDATE_BRANCH', 'master' );

require_once ELODIN_BRIDGE_DIR . '/inc/settings-page.php';
require_once ELODIN_BRIDGE_DIR . '/inc/heading-paragraph-overrides.php';
require_once ELODIN_BRIDGE_DIR . '/inc/content-type-behavior.php';
require_once ELODIN_BRIDGE_DIR . '/inc/editor-ui-restrictions.php';
require_once ELODIN_BRIDGE_DIR . '/inc/media-library-infinite-scrolling.php';
require_once ELODIN_BRIDGE_DIR . '/inc/shortcodes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/svg-uploads.php';
require_once ELODIN_BRIDGE_DIR . '/inc/cover-min-heights.php';
require_once ELODIN_BRIDGE_DIR . '/inc/direct-cover-video-urls.php';
require_once ELODIN_BRIDGE_DIR . '/inc/cover-youtube-backgrounds.php';
require_once ELODIN_BRIDGE_DIR . '/inc/video-download-deterrents.php';
require_once ELODIN_BRIDGE_DIR . '/inc/mobile-fixed-background-repair.php';
require_once ELODIN_BRIDGE_DIR . '/inc/header-aware-positioning.php';
require_once ELODIN_BRIDGE_DIR . '/inc/header-sizing-block.php';
require_once ELODIN_BRIDGE_DIR . '/inc/sticky-below-header-block-style.php';
require_once ELODIN_BRIDGE_DIR . '/inc/anchor-smooth-scroll.php';
require_once ELODIN_BRIDGE_DIR . '/inc/scroll-hide-elements.php';
require_once ELODIN_BRIDGE_DIR . '/inc/ollie-color-palettes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/auto-child-theme-gradient-duotones.php';
require_once ELODIN_BRIDGE_DIR . '/inc/editor-group-border.php';
require_once ELODIN_BRIDGE_DIR . '/inc/prettier-widgets.php';
require_once ELODIN_BRIDGE_DIR . '/inc/block-edge-classes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/image-sizes.php';
require_once ELODIN_BRIDGE_DIR . '/inc/spacing-variables.php';
require_once ELODIN_BRIDGE_DIR . '/inc/font-size-variables.php';
require_once ELODIN_BRIDGE_DIR . '/inc/css-variable-autowrap.php';
require_once ELODIN_BRIDGE_DIR . '/inc/generateblocks-inner-container-appender.php';
require_once ELODIN_BRIDGE_DIR . '/inc/fse-content-top-margin-reset.php';
require_once ELODIN_BRIDGE_DIR . '/inc/edit-site-admin-bar-links.php';
require_once ELODIN_BRIDGE_DIR . '/inc/site-editor-admin-bar.php';
require_once ELODIN_BRIDGE_DIR . '/inc/update-checker.php';
require_once ELODIN_BRIDGE_DIR . '/inc/nested-group-shortcut.php';
require_once ELODIN_BRIDGE_DIR . '/inc/checkerboard-pattern.php';
