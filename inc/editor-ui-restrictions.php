<?php

/**
 * Remove theme-level editor callbacks so Bridge controls editor behavior.
 */
function elodin_bridge_detach_theme_editor_callbacks() {
	if ( ! elodin_bridge_is_editor_ui_restrictions_enabled() ) {
		return;
	}

	remove_action( 'enqueue_block_editor_assets', 'elodin_disable_fullscreen_mode' );
}
add_action( 'after_setup_theme', 'elodin_bridge_detach_theme_editor_callbacks', 100 );

/**
 * Enqueue inline JS that disables fullscreen mode and the publish sidebar.
 */
function elodin_bridge_enqueue_editor_ui_restrictions() {
	$disable_fullscreen = elodin_bridge_is_editor_ui_restrictions_enabled();
	$disable_publish_sidebar = elodin_bridge_is_editor_publish_sidebar_restriction_enabled();
	$enable_show_template_default = elodin_bridge_is_editor_show_template_default_enabled();
	$is_block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	if ( ! $disable_fullscreen && ! $disable_publish_sidebar && ! $enable_show_template_default ) {
		return;
	}

	$handle = 'elodin-bridge-editor-ui-restrictions';
	wp_register_script(
		$handle,
		false,
		array( 'wp-data', 'wp-dom-ready' ),
		ELODIN_BRIDGE_VERSION,
		true
	);
	wp_enqueue_script( $handle );
	wp_add_inline_script(
		$handle,
		'window.elodinBridgeEditorUiRestrictions = ' . wp_json_encode(
			array(
				'disableFullscreen'     => $disable_fullscreen,
				'disablePublishSidebar' => $disable_publish_sidebar,
				'enableShowTemplateDefault' => $enable_show_template_default,
				'isBlockTheme'          => $is_block_theme,
			)
		) . ';',
		'before'
	);

	wp_add_inline_script(
		$handle,
		'( function( wp ) {
				if ( ! wp || ! wp.data || ! wp.domReady ) {
					return;
				}

					const config = window.elodinBridgeEditorUiRestrictions || {};

				wp.domReady( function() {
					if ( config.disableFullscreen ) {
						const dispatchPreferences = wp.data.dispatch( "core/preferences" );
						if ( dispatchPreferences && typeof dispatchPreferences.set === "function" ) {
							dispatchPreferences.set( "core", "fullscreenMode", false );
							dispatchPreferences.set( "core/edit-post", "fullscreenMode", false );
						}
					}

					if ( config.disablePublishSidebar ) {
						const dispatchEditor = wp.data.dispatch( "core/editor" );
						if ( dispatchEditor && typeof dispatchEditor.disablePublishSidebar === "function" ) {
							dispatchEditor.disablePublishSidebar();
						}
					}

						if ( config.enableShowTemplateDefault ) {
							let renderingModeRequested = false;
							const applyShowTemplateDefault = function() {
								const selectEditor = wp.data.select( "core/editor" );
								const selectPreferences = wp.data.select( "core/preferences" );
								const selectCore = wp.data.select( "core" );
							const dispatchEditor = wp.data.dispatch( "core/editor" );
							const dispatchPreferences = wp.data.dispatch( "core/preferences" );

							if ( ! selectEditor || ! selectPreferences || ! selectCore || ! dispatchEditor || ! dispatchPreferences ) {
								return false;
							}

							if ( typeof selectEditor.getCurrentPostType !== "function" ) {
								return false;
							}

							const editorSettings = typeof selectEditor.getEditorSettings === "function"
								? ( selectEditor.getEditorSettings() || {} )
								: {};
							const editorFlag = !! editorSettings.__unstableIsBlockBasedTheme;
							const templateId = typeof selectEditor.getCurrentTemplateId === "function"
								? selectEditor.getCurrentTemplateId()
								: null;
							const templateSuggestsBlockTheme = typeof templateId === "string" && templateId.indexOf( "//" ) !== -1;

							if ( ! config.isBlockTheme && ! editorFlag && typeof templateId === "undefined" ) {
								return false;
							}

							const isBlockThemeContext = !! config.isBlockTheme || editorFlag || templateSuggestsBlockTheme;
							if ( ! isBlockThemeContext ) {
								return true;
							}

							const postType = selectEditor.getCurrentPostType();
							if ( ! postType ) {
								return false;
							}

							const blockedPostTypes = {
								wp_template: true,
								wp_template_part: true,
								wp_navigation: true,
							};
							if ( Object.prototype.hasOwnProperty.call( blockedPostTypes, postType ) ) {
								return true;
							}

							if ( typeof selectCore.getCurrentTheme !== "function" || typeof selectPreferences.get !== "function" || typeof dispatchPreferences.set !== "function" ) {
								return false;
							}

							const currentTheme = selectCore.getCurrentTheme() || {};
							const stylesheet = currentTheme && typeof currentTheme.stylesheet === "string" ? currentTheme.stylesheet : "";
							if ( ! stylesheet ) {
								return false;
							}

							const savedRenderingModes = selectPreferences.get( "core", "renderingModes" );
							const renderingModes = savedRenderingModes && typeof savedRenderingModes === "object" ? savedRenderingModes : {};
							const themeModes = renderingModes[ stylesheet ] && typeof renderingModes[ stylesheet ] === "object"
								? renderingModes[ stylesheet ]
								: {};

							if ( themeModes[ postType ] !== "template-locked" ) {
								const newRenderingModes = {
									...renderingModes,
									[ stylesheet ]: {
										...themeModes,
										[ postType ]: "template-locked",
									},
								};
								dispatchPreferences.set( "core", "renderingModes", newRenderingModes );
							}

								if ( typeof dispatchEditor.setRenderingMode === "function" && ! renderingModeRequested ) {
									const currentRenderingMode = typeof selectEditor.getRenderingMode === "function"
										? selectEditor.getRenderingMode()
										: null;

									if ( "template-locked" !== currentRenderingMode ) {
										renderingModeRequested = true;
										dispatchEditor.setRenderingMode( "template-locked" );
									}
								}

							return true;
						};

							const resolvedImmediately = applyShowTemplateDefault();
							if ( ! resolvedImmediately ) {
								let attempts = 0;
								let isApplying = false;
								const maxAttempts = 200;
								const unsubscribe = wp.data.subscribe( function() {
									if ( isApplying ) {
										return;
									}

									attempts += 1;
									isApplying = true;
									const resolved = applyShowTemplateDefault();
									isApplying = false;

									if ( resolved || attempts >= maxAttempts ) {
										unsubscribe();
									}
								} );
								}
							}
						} );
				} )( window.wp );'
	);
}
add_action( 'enqueue_block_editor_assets', 'elodin_bridge_enqueue_editor_ui_restrictions' );
