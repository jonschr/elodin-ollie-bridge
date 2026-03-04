<?php

/**
 * Check whether the current admin request is for the Site Editor.
 *
 * @return bool
 */
function elodin_bridge_is_site_editor_request() {
	if ( ! is_admin() ) {
		return false;
	}

	global $pagenow;
	if ( 'site-editor.php' === (string) $pagenow ) {
		return true;
	}

	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && 'site-editor' === (string) $screen->base ) {
			return true;
		}
	}

	return false;
}

/**
 * Force the WP admin bar to be visible in the Site Editor.
 *
 * @param bool $show Current admin bar visibility.
 * @return bool
 */
function elodin_bridge_force_site_editor_admin_bar_visibility( $show ) {
	if ( ! elodin_bridge_is_site_editor_admin_bar_enabled() ) {
		return $show;
	}

	if ( ! elodin_bridge_is_site_editor_request() ) {
		return $show;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return $show;
	}

	return true;
}
add_filter( 'show_admin_bar', 'elodin_bridge_force_site_editor_admin_bar_visibility', 100 );

/**
 * Remove the fullscreen body class on the Site Editor screen.
 *
 * @param string $classes Admin body class list.
 * @return string
 */
function elodin_bridge_remove_site_editor_fullscreen_class( $classes ) {
	if ( ! elodin_bridge_is_site_editor_admin_bar_enabled() ) {
		return $classes;
	}

	if ( ! elodin_bridge_is_site_editor_request() ) {
		return $classes;
	}

	$class_tokens = preg_split( '/\s+/', trim( (string) $classes ) );
	if ( ! is_array( $class_tokens ) ) {
		return $classes;
	}

	$class_tokens = array_values(
		array_filter(
			$class_tokens,
			static function ( $token ) {
				return 'is-fullscreen-mode' !== (string) $token;
			}
		)
	);

	return implode( ' ', $class_tokens );
}
add_filter( 'admin_body_class', 'elodin_bridge_remove_site_editor_fullscreen_class', 20 );

/**
 * Enqueue Site Editor assets that keep fullscreen mode disabled and expose the top admin bar.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function elodin_bridge_enqueue_site_editor_admin_bar_assets( $hook_suffix ) {
	if ( ! elodin_bridge_is_site_editor_admin_bar_enabled() ) {
		return;
	}

	if ( 'site-editor.php' !== (string) $hook_suffix ) {
		return;
	}

	$style_handle = 'elodin-bridge-site-editor-admin-bar';
	wp_register_style(
		$style_handle,
		false,
		array( 'wp-edit-site' ),
		ELODIN_BRIDGE_VERSION
	);
	wp_enqueue_style( $style_handle );
	wp_add_inline_style(
		$style_handle,
		'body.js.site-editor-php #wpadminbar{display:block!important;}
		body.js.site-editor-php #adminmenumain{display:none!important;}
		body.js.site-editor-php #wpcontent,body.js.site-editor-php #wpfooter{margin-left:0!important;}
		body.js.site-editor-php .edit-site .interface-interface-skeleton,body.js.site-editor-php .editor-editor-interface .edit-site-editor__editor-interface .interface-interface-skeleton{left:0!important;right:0!important;}
		body.js.site-editor-php #wpbody{padding-top:var(--wp-admin--admin-bar--height,32px)!important;}
		body.js.site-editor-php .edit-site{top:var(--wp-admin--admin-bar--height,32px)!important;height:calc(100vh - var(--wp-admin--admin-bar--height,32px))!important;}'
	);

	$script_handle = 'elodin-bridge-site-editor-admin-bar-script';
	wp_register_script(
		$script_handle,
		false,
		array( 'wp-data', 'wp-dom-ready' ),
		ELODIN_BRIDGE_VERSION,
		true
	);
	wp_enqueue_script( $script_handle );
	wp_add_inline_script(
		$script_handle,
		'( function( wp ) {
			if ( ! wp || ! wp.data || ! wp.domReady ) {
				return;
			}

			const applySiteEditorPrefs = function() {
				if ( document && document.body ) {
					document.body.classList.remove( "is-fullscreen-mode" );
				}

				const selectPreferences = wp.data.select( "core/preferences" );
				const dispatchPreferences = wp.data.dispatch( "core/preferences" );
				if ( ! selectPreferences || ! dispatchPreferences || typeof selectPreferences.get !== "function" || typeof dispatchPreferences.set !== "function" ) {
					return;
				}

				if ( false !== selectPreferences.get( "core", "fullscreenMode" ) ) {
					dispatchPreferences.set( "core", "fullscreenMode", false );
				}

				if ( false !== selectPreferences.get( "core/edit-site", "fullscreenMode" ) ) {
					dispatchPreferences.set( "core/edit-site", "fullscreenMode", false );
				}
			};

			wp.domReady( function() {
				applySiteEditorPrefs();

				let applying = false;
				wp.data.subscribe( function() {
					if ( applying ) {
						return;
					}

					applying = true;
					applySiteEditorPrefs();
					applying = false;
				} );
			} );
		} )( window.wp );'
	);
}
add_action( 'admin_enqueue_scripts', 'elodin_bridge_enqueue_site_editor_admin_bar_assets', 20 );
