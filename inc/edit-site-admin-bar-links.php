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

	if ( ! current_user_can( 'edit_theme_options' ) || is_admin() ) {
		return;
	}

	$edit_site_node = $wp_admin_bar->get_node( 'site-editor' );
	if ( ! $edit_site_node ) {
		return;
	}

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
		'patterns'   => array(
			'title' => __( 'Patterns', 'elodin-bridge' ),
			'path'  => '/pattern',
		),
	);

	foreach ( $sections as $section_key => $section ) {
		$wp_admin_bar->add_node(
			array(
				'parent' => 'site-editor',
				'id'     => 'elodin-bridge-site-editor-' . $section_key,
				'title'  => $section['title'],
				'href'   => add_query_arg(
					array(
						'p' => $section['path'],
					),
					admin_url( 'site-editor.php' )
				),
			)
		);
	}
}
add_action( 'admin_bar_menu', 'elodin_bridge_add_edit_site_admin_bar_links', 60 );
