<?php

/**
 * Check whether the current context is a supported block editor surface.
 *
 * @param mixed $editor_context Block editor context.
 * @return bool
 */
function elodin_bridge_is_content_type_behavior_supported_editor_context( $editor_context = null ) {
	if ( is_object( $editor_context ) && isset( $editor_context->name ) ) {
		return in_array( (string) $editor_context->name, array( 'core/edit-post', 'core/edit-site' ), true );
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return false;
	}

	if ( ! empty( $screen->is_block_editor ) ) {
		return true;
	}

	return 'site-editor' === (string) $screen->base;
}

/**
 * Resolve the current editor post object from block editor context or request globals.
 *
 * @param mixed $editor_context Block editor context.
 * @return WP_Post|null
 */
function elodin_bridge_get_content_type_behavior_editor_post( $editor_context = null ) {
	if ( is_object( $editor_context ) && isset( $editor_context->post ) && is_object( $editor_context->post ) ) {
		if ( ! empty( $editor_context->post->ID ) ) {
			$post = get_post( (int) $editor_context->post->ID );
			if ( $post instanceof WP_Post ) {
				return $post;
			}
		}

		if ( ! empty( $editor_context->post->post_type ) ) {
			return $editor_context->post;
		}
	}

	$post_id = 0;
	if ( isset( $_GET['post'] ) ) {
		$post_id = absint( wp_unslash( $_GET['post'] ) );
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = absint( wp_unslash( $_POST['post_ID'] ) );
	}

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
		if ( $post instanceof WP_Post ) {
			return $post;
		}
	}

	if ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) {
		return $GLOBALS['post'];
	}

	return null;
}

/**
 * Resolve the current editor post type from block editor context, post object, or screen.
 *
 * @param mixed $editor_context Block editor context.
 * @return string
 */
function elodin_bridge_get_content_type_behavior_post_type( $editor_context = null ) {
	$post = elodin_bridge_get_content_type_behavior_editor_post( $editor_context );
	if ( $post instanceof WP_Post && ! empty( $post->post_type ) && post_type_exists( $post->post_type ) ) {
		return (string) $post->post_type;
	}

	if ( is_object( $editor_context ) && isset( $editor_context->post_type ) && ! empty( $editor_context->post_type ) ) {
		$post_type = (string) $editor_context->post_type;
		if ( post_type_exists( $post_type ) ) {
			return $post_type;
		}
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return '';
	}

	$screen = get_current_screen();
	if ( ! $screen || empty( $screen->post_type ) || ! post_type_exists( $screen->post_type ) ) {
		return '';
	}

	return (string) $screen->post_type;
}

/**
 * Get the behavior class for a post type in hybrid themes.
 *
 * @param string $post_type Post type key.
 * @return string
 */
function elodin_bridge_get_content_type_behavior_class( $post_type ) {
	if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
		return '';
	}

	return elodin_bridge_is_post_type_page_like( $post_type )
		? 'elodin-bridge-page-like-title'
		: 'elodin-bridge-post-like-title';
}

/**
 * Get the active behavior class for the current editor context.
 *
 * @param mixed $editor_context Block editor context.
 * @return string
 */
function elodin_bridge_get_content_type_behavior_class_for_context( $editor_context = null ) {
	if ( ! elodin_bridge_is_content_type_behavior_supported_editor_context( $editor_context ) ) {
		return '';
	}

	if ( ! elodin_bridge_is_content_type_behavior_enabled() ) {
		return '';
	}

	$post_type = elodin_bridge_get_content_type_behavior_post_type( $editor_context );
	return elodin_bridge_get_content_type_behavior_class( $post_type );
}

/**
 * Get iframe scope selector for content type behavior.
 *
 * @param mixed $editor_context Block editor context.
 * @return string
 */
function elodin_bridge_get_content_type_behavior_iframe_scope_selector( $editor_context = null ) {
	if ( ! elodin_bridge_is_content_type_behavior_enabled() ) {
		return '';
	}

	$post_type = elodin_bridge_get_content_type_behavior_post_type( $editor_context );
	if ( '' !== $post_type ) {
		return '.post-type-' . sanitize_html_class( $post_type );
	}

	return '';
}

/**
 * Add an admin body class for page-like/post-like editor behavior.
 *
 * @param string $classes Existing admin body class string.
 * @return string
 */
function elodin_bridge_filter_admin_body_class( $classes ) {
	$behavior_class = elodin_bridge_get_content_type_behavior_class_for_context();
	if ( '' === $behavior_class ) {
		return $classes;
	}

	return trim( $classes . ' ' . $behavior_class );
}
add_filter( 'admin_body_class', 'elodin_bridge_filter_admin_body_class' );

/**
 * Enqueue editor styles for page-like/post-like behavior.
 */
function elodin_bridge_enqueue_editor_page_like_title_styles() {
	if ( ! elodin_bridge_is_content_type_behavior_enabled() ) {
		return;
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-page-like-title.css';
	$style_url = ELODIN_BRIDGE_URL . 'assets/editor-page-like-title.css';

	if ( ! file_exists( $style_path ) ) {
		return;
	}

	wp_enqueue_style(
		'elodin-bridge-editor-page-like-title',
		$style_url,
		array( 'wp-edit-blocks' ),
		(string) filemtime( $style_path )
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_page_like_title_styles' );

/**
 * Build iframe-scoped behavior CSS for content type mapping.
 *
 * @param string $scope_selector Iframe scope selector.
 * @param string $behavior_class Active behavior class.
 * @return string
 */
function elodin_bridge_get_content_type_behavior_iframe_css( $scope_selector, $behavior_class ) {
	$scope_selector = trim( (string) $scope_selector );
	if ( '' === $scope_selector ) {
		return '';
	}

	$style_path = ELODIN_BRIDGE_DIR . '/assets/editor-page-like-title.css';
	if ( ! file_exists( $style_path ) ) {
		return '';
	}

	$css = trim( (string) file_get_contents( $style_path ) );
	if ( '' === $css ) {
		return '';
	}

	$page_like_class = '.elodin-bridge-page-like-title';
	$post_like_class = '.elodin-bridge-post-like-title';

	$active_class = ( 'elodin-bridge-page-like-title' === $behavior_class ) ? $page_like_class : $post_like_class;
	$inactive_class = ( $active_class === $page_like_class ) ? $post_like_class : $page_like_class;

	$css = str_replace( $active_class, $scope_selector, $css );
	$css = str_replace( $inactive_class, '.elodin-bridge-content-type-behavior-inactive', $css );

	return $css;
}

/**
 * Inject page-like/post-like behavior styles into block editor iframe settings.
 *
 * @param array<string,mixed> $settings Block editor settings.
 * @param mixed               $editor_context Block editor context.
 * @return array<string,mixed>
 */
function elodin_bridge_inject_content_type_behavior_into_editor_settings( $settings, $editor_context ) {
	$behavior_class = elodin_bridge_get_content_type_behavior_class_for_context( $editor_context );
	if ( '' === $behavior_class ) {
		return $settings;
	}

	$scope_selector = elodin_bridge_get_content_type_behavior_iframe_scope_selector( $editor_context );
	if ( '' === $scope_selector ) {
		return $settings;
	}

	$css = elodin_bridge_get_content_type_behavior_iframe_css( $scope_selector, $behavior_class );
	if ( '' === $css ) {
		return $settings;
	}

	if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}

	$settings['styles'][] = array(
		'css' => $css,
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'elodin_bridge_inject_content_type_behavior_into_editor_settings', 100, 2 );
