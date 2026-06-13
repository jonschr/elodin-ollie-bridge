<?php

/**
 * Add Site Editor section shortcuts under the core "Edit Site" admin bar item.
 *
 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
 */
function elodin_bridge_add_edit_site_admin_bar_links( $wp_admin_bar ) {
	if ( ! elodin_bridge_is_edit_site_admin_bar_links_enabled() ) {
		return;
	}

	if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$edit_site_node = $wp_admin_bar->get_node( 'site-editor' );
	if ( ! $edit_site_node ) {
		$wp_admin_bar->add_node(
			array(
				'id'    => 'site-editor',
				'title' => __( 'Edit Site', 'elodin-bridge' ),
				'href'  => admin_url( 'site-editor.php' ),
			)
		);

		$edit_site_node = $wp_admin_bar->get_node( 'site-editor' );
		if ( ! $edit_site_node ) {
			return;
		}
	}

	$template_part_base_path = '/wp_template_part/' . get_stylesheet() . '//';

	$sections = array(
		'styles'     => array(
			'title' => __( 'Styles', 'elodin-bridge' ),
			'path'  => '/styles',
		),
		'navigation' => array(
			'title' => __( 'Navigation', 'elodin-bridge' ),
			'path'  => '/navigation',
		),
		'pages'      => array(
			'title' => __( 'Pages', 'elodin-bridge' ),
			'path'  => '/page',
		),
		'templates'  => array(
			'title' => __( 'Templates', 'elodin-bridge' ),
			'path'  => '/template',
		),
		'header'     => array(
			'title'      => __( '- Header', 'elodin-bridge' ),
			'path'       => $template_part_base_path . 'header',
			'canvas'     => 'edit',
			'focus_mode' => 'true',
		),
		'footer'     => array(
			'title'      => __( '- Footer', 'elodin-bridge' ),
			'path'       => $template_part_base_path . 'footer',
			'canvas'     => 'edit',
			'focus_mode' => 'true',
		),
		'menu-parts' => array(
			'title'       => __( '- Menus', 'elodin-bridge' ),
			'path'        => '/pattern',
			'post_type'   => 'wp_template_part',
			'category_id' => 'menu',
		),
		'patterns'   => array(
			'title' => __( 'Patterns', 'elodin-bridge' ),
			'path'  => '/pattern',
		),
		'my-patterns' => array(
			'title'       => __( '- My Patterns', 'elodin-bridge' ),
			'path'        => '/pattern',
			'post_type'   => 'wp_block',
			'category_id' => 'my-patterns',
		),
	);

	foreach ( $sections as $section_key => $section ) {
		$wp_admin_bar->add_node(
			array(
				'parent' => 'site-editor',
				'id'     => 'elodin-bridge-site-editor-' . $section_key,
				'title'  => $section['title'],
				'href'   => add_query_arg(
					array_filter(
						array(
							'p'          => $section['path'],
							'postType'   => $section['post_type'] ?? null,
							'categoryId' => $section['category_id'] ?? null,
							'canvas'     => $section['canvas'] ?? null,
							'focusMode'  => $section['focus_mode'] ?? null,
						)
					),
					admin_url( 'site-editor.php' )
				),
			)
		);
	}
}
add_action( 'admin_bar_menu', 'elodin_bridge_add_edit_site_admin_bar_links', 60 );
