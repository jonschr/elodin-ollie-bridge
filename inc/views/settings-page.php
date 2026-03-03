<div class="wrap elodin-bridge-admin">
	<div class="elodin-bridge-admin__hero">
		<h1 class="elodin-bridge-admin__title">
			<?php esc_html_e( 'Ollie Bridge', 'elodin-bridge' ); ?>
			<span class="elodin-bridge-admin__version"><?php echo esc_html( sprintf( 'v%s', ELODIN_BRIDGE_VERSION ) ); ?></span>
		</h1>

	</div>

		<form action="options.php" method="post" class="elodin-bridge-admin__form">
			<?php settings_fields( 'elodin_bridge_settings' ); ?>

				<div class="elodin-bridge-admin__toolbar">
					<div class="elodin-bridge-admin__category-nav" role="tablist" aria-label="<?php esc_attr_e( 'Bridge settings categories', 'elodin-bridge' ); ?>">
						<button type="button" class="elodin-bridge-admin__category-button is-active" data-bridge-category="editor" aria-pressed="true"><?php esc_html_e( 'Editor Tweaks', 'elodin-bridge' ); ?></button>
						<button type="button" class="elodin-bridge-admin__category-button" data-bridge-category="style" aria-pressed="false"><?php esc_html_e( 'Style Tweaks', 'elodin-bridge' ); ?></button>
						<button type="button" class="elodin-bridge-admin__category-button" data-bridge-category="misc" aria-pressed="false"><?php esc_html_e( 'Miscellaneous', 'elodin-bridge' ); ?></button>
					</div>
				<div class="elodin-bridge-admin__toolbar-save">
					<span class="elodin-bridge-admin__save-status" data-bridge-save-status data-state="idle" role="status" aria-live="polite">
						<?php esc_html_e( 'Changes save automatically.', 'elodin-bridge' ); ?>
					</span>
					<div class="elodin-bridge-admin__save-debug" data-bridge-save-debug-wrap hidden>
						<strong><?php esc_html_e( 'Autosave debug', 'elodin-bridge' ); ?></strong>
						<pre data-bridge-save-debug></pre>
					</div>
					<noscript>
						<button type="submit" name="submit" id="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'elodin-bridge' ); ?></button>
					</noscript>
				</div>
			</div>

			<div class="elodin-bridge-admin__cards">

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature <?php echo $balanced_text_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_BALANCED_TEXT ); ?>"
							value="1"
							<?php checked( $balanced_text_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable balanced text toggle', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds a separate block toolbar button to toggle the .balanced class on paragraphs, headings, post titles, and post excerpts. When active, that class applies text-wrap: balance.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature <?php echo $heading_paragraph_overrides_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_HEADING_PARAGRAPH_OVERRIDES ); ?>"
							value="1"
							<?php checked( $heading_paragraph_overrides_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable heading and paragraph style overrides', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds a toolbar style override picker for paragraph and heading blocks. Override values come from your active theme.json typography styles, with missing pieces filled from the parent theme.json when available.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $remove_ollie_color_palettes_enabled ? 'is-enabled' : ''; ?> <?php echo ! $ollie_pro_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_REMOVE_OLLIE_COLOR_PALETTES ); ?>"
							value="1"
							<?php checked( $remove_ollie_color_palettes_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Remove Ollie\'s color palettes', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires Ollie Pro', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Removes Ollie Pro\'s theme.json palette filter so your child theme or active theme color palette is not overwritten.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $ollie_pro_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting only takes effect when Ollie Pro is active.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

				<div class="elodin-bridge-admin__card" data-bridge-category="style">
					<div class="elodin-bridge-admin__feature <?php echo $mobile_fixed_background_repair_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MOBILE_FIXED_BACKGROUND_REPAIR ); ?>"
							value="1"
							<?php checked( $mobile_fixed_background_repair_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Repair fixed-position background images on mobile', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'On mobile breakpoints, scans for elements using fixed background attachments and switches them to non-fixed to avoid known browser rendering bugs.', 'elodin-bridge' ); ?>
						</p>
						<p class="elodin-bridge-admin__note">
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: Can I Use URL for background-attachment support. */
									__( 'Compatibility note: <a href="%s" target="_blank" rel="noopener noreferrer">background-attachment browser support</a> is mixed on mobile. At present, Safari on iOS and the Android Browser show partial to no support.', 'elodin-bridge' ),
									esc_url( 'https://caniuse.com/background-attachment' )
								)
							);
							?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature <?php echo $editor_ui_restrictions_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_UI_RESTRICTIONS ); ?>"
							value="1"
							<?php checked( $editor_ui_restrictions_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Disable fullscreen mode in the editor', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Turns fullscreen mode off in the block editor.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature <?php echo $editor_publish_sidebar_restriction_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_PUBLISH_SIDEBAR_RESTRICTION ); ?>"
							value="1"
							<?php checked( $editor_publish_sidebar_restriction_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Disable publish sidebar in the editor', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Disables the publish sidebar in the block editor.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="editor">
				<div class="elodin-bridge-admin__feature has-requirement <?php echo $editor_show_template_default_enabled ? 'is-enabled' : ''; ?> <?php echo ! $editor_show_template_default_available ? 'is-unavailable' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_SHOW_TEMPLATE_DEFAULT ); ?>"
							value="1"
							<?php checked( $editor_show_template_default_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Default "Show template" to on in block themes', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires Block Theme', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'For block themes, forces the editor to load with Show template on for each editor load. Users can still switch it off while editing.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $editor_show_template_default_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting only takes effect when a block theme is active.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="misc">
				<div class="elodin-bridge-admin__feature <?php echo $media_library_infinite_scrolling_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_MEDIA_LIBRARY_INFINITE_SCROLLING ); ?>"
							value="1"
							<?php checked( $media_library_infinite_scrolling_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable media library infinite scrolling', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Forces Media Library infinite scrolling on (equivalent to adding the media_library_infinite_scrolling filter).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="misc">
				<div class="elodin-bridge-admin__feature <?php echo $shortcodes_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SHORTCODES ); ?>"
							value="1"
							<?php checked( $shortcodes_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable footer and copyright shortcodes', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Registers [year], [c], [tm], and [r] shortcodes. Trademark and registered outputs use superscript markup.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="misc">
				<div class="elodin-bridge-admin__feature <?php echo $svg_uploads_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_SVG_UPLOADS ); ?>"
							value="1"
							<?php checked( $svg_uploads_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable SVG uploads', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Allows SVG files in the Media Library by registering the image/svg+xml MIME type.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="elodin-bridge-admin__card" data-bridge-category="misc">
				<div class="elodin-bridge-admin__feature <?php echo $css_variable_autowrap_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_CSS_VARIABLE_AUTOWRAP ); ?>"
							value="1"
							<?php checked( $css_variable_autowrap_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Auto-wrap spacing/font variables with var()', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'When you type a supported token like --space-m or --font-l (or shorthands like --sm and --f2xl) in backend text fields, Bridge expands it to WordPress preset variables (for example, var(--wp--preset--spacing--medium)).', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

				<div class="elodin-bridge-admin__card" data-bridge-category="editor">
					<div class="elodin-bridge-admin__feature has-requirement <?php echo $generateblocks_boundary_highlights_enabled ? 'is-enabled' : ''; ?> <?php echo ! $generateblocks_available ? 'is-unavailable' : ''; ?>">
						<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_GENERATEBLOCKS_BOUNDARY_HIGHLIGHTS ); ?>"
							value="1"
							<?php checked( $generateblocks_boundary_highlights_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable GenerateBlocks boundary highlights in the editor', 'elodin-bridge' ); ?></span>
					</label>
					<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'Requires GenerateBlocks', 'elodin-bridge' ); ?></span>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds dashed outlines around GenerateBlocks containers/elements in the block editor to make block boundaries easier to identify while editing.', 'elodin-bridge' ); ?>
						</p>
						<?php if ( ! $generateblocks_available ) : ?>
							<p class="elodin-bridge-admin__note">
								<?php esc_html_e( 'This setting only takes effect when GenerateBlocks is active.', 'elodin-bridge' ); ?>
							</p>
						<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="elodin-bridge-admin__card" data-bridge-category="editor">
					<div class="elodin-bridge-admin__feature has-requirement <?php echo $editor_group_border_enabled ? 'is-enabled' : ''; ?>">
						<label class="elodin-bridge-admin__feature-header" for="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER ); ?>">
							<input
								type="hidden"
								name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER ); ?>"
								value="0"
							/>
							<input
								type="checkbox"
								class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
								id="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER ); ?>"
								name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_ENABLE_EDITOR_GROUP_BORDER ); ?>"
								value="1"
								<?php checked( $editor_group_border_enabled ); ?>
							/>
							<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
								<span class="elodin-bridge-admin__toggle-thumb"></span>
							</span>
							<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable core block boundary highlights in the editor', 'elodin-bridge' ); ?></span>
						</label>
						<span class="elodin-bridge-admin__requirement-tag elodin-bridge-admin__requirement-tag--corner"><?php esc_html_e( 'FSE', 'elodin-bridge' ); ?></span>

						<div class="elodin-bridge-admin__feature-body">
							<p class="elodin-bridge-admin__description">
								<?php esc_html_e( 'Attempts to highlight the boundaries of core blocks in the editor, including Group, Columns, and Cover blocks.', 'elodin-bridge' ); ?>
							</p>
						</div>
					</div>
				</div>

				<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="misc">
					<div class="elodin-bridge-admin__feature <?php echo ! empty( $image_sizes_settings['enabled'] ) ? 'is-enabled' : ''; ?>">
						<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-image-sizes-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-image-sizes-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[enabled]"
							value="1"
							<?php checked( ! empty( $image_sizes_settings['enabled'] ) ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable additional image sizes and gallery size controls', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Registers custom image sizes for your site. Gallery checkboxes add selected custom sizes to the size picker in addition to WordPress defaults.', 'elodin-bridge' ); ?>
						</p>
							<div class="elodin-bridge-admin__image-size-section">
								<h3 class="elodin-bridge-admin__subheading"><?php esc_html_e( 'Custom Image Sizes', 'elodin-bridge' ); ?></h3>
								<p class="elodin-bridge-admin__note">
									<?php esc_html_e( 'Use unique slugs. Width and height must be positive numbers.', 'elodin-bridge' ); ?>
							</p>
							<div
								class="elodin-bridge-admin__image-size-builder"
								data-next-index="<?php echo esc_attr( (string) count( $image_size_rows ) ); ?>"
							>
								<div class="elodin-bridge-admin__custom-image-sizes">
									<?php foreach ( $image_size_rows as $index => $size ) : ?>
										<div class="elodin-bridge-admin__image-size-row">
											<div class="elodin-bridge-admin__image-size-row-main">
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--slug">
													<span><?php esc_html_e( 'Slug', 'elodin-bridge' ); ?></span>
													<input
														type="text"
														class="regular-text code"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][slug]"
														value="<?php echo esc_attr( $size['slug'] ?? '' ); ?>"
														placeholder="hero_large"
													/>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--label">
													<span><?php esc_html_e( 'Label', 'elodin-bridge' ); ?></span>
													<input
														type="text"
														class="regular-text"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][label]"
														value="<?php echo esc_attr( $size['label'] ?? '' ); ?>"
														placeholder="<?php esc_attr_e( 'Hero Large', 'elodin-bridge' ); ?>"
													/>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--width">
													<span><?php esc_html_e( 'Width', 'elodin-bridge' ); ?></span>
													<input
														type="number"
														class="small-text"
														min="1"
														step="1"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][width]"
														value="<?php echo esc_attr( isset( $size['width'] ) ? (string) $size['width'] : '' ); ?>"
													/>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--height">
													<span><?php esc_html_e( 'Height', 'elodin-bridge' ); ?></span>
													<input
														type="number"
														class="small-text"
														min="1"
														step="1"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][height]"
														value="<?php echo esc_attr( isset( $size['height'] ) ? (string) $size['height'] : '' ); ?>"
													/>
												</label>
											</div>
											<div class="elodin-bridge-admin__image-size-row-options">
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--crop">
													<input
														type="hidden"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][crop]"
														value="0"
													/>
													<span class="elodin-bridge-admin__image-size-switch">
														<input
															type="checkbox"
															class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input"
															name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][crop]"
															value="1"
															<?php checked( ! empty( $size['crop'] ) ); ?>
														/>
														<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
															<span class="elodin-bridge-admin__toggle-thumb"></span>
														</span>
													</span>
													<span><?php esc_html_e( 'Hard Crop', 'elodin-bridge' ); ?></span>
												</label>
												<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--gallery">
													<input
														type="hidden"
														name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][gallery]"
														value="0"
													/>
													<span class="elodin-bridge-admin__image-size-switch">
														<input
															type="checkbox"
															class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input"
															name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][<?php echo esc_attr( (string) $index ); ?>][gallery]"
															value="1"
															<?php checked( ! empty( $size['gallery'] ) ); ?>
														/>
														<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
															<span class="elodin-bridge-admin__toggle-thumb"></span>
														</span>
													</span>
													<span><?php esc_html_e( 'Allow In Galleries', 'elodin-bridge' ); ?></span>
												</label>
												<div class="elodin-bridge-admin__image-size-actions">
													<button type="button" class="button-link-delete elodin-bridge-admin__remove-image-size"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></button>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
									<button type="button" class="button button-secondary elodin-bridge-admin__add-image-size"><?php esc_html_e( 'Add Custom Size', 'elodin-bridge' ); ?></button>
								</div>
							</div>

								<script type="text/template" id="elodin-bridge-image-size-row-template">
								<div class="elodin-bridge-admin__image-size-row">
								<div class="elodin-bridge-admin__image-size-row-main">
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--slug">
										<span><?php esc_html_e( 'Slug', 'elodin-bridge' ); ?></span>
										<input type="text" class="regular-text code" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][slug]" placeholder="hero_large" />
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--label">
										<span><?php esc_html_e( 'Label', 'elodin-bridge' ); ?></span>
										<input type="text" class="regular-text" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][label]" placeholder="<?php esc_attr_e( 'Hero Large', 'elodin-bridge' ); ?>" />
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--width">
										<span><?php esc_html_e( 'Width', 'elodin-bridge' ); ?></span>
										<input type="number" class="small-text" min="1" step="1" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][width]" />
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--height">
										<span><?php esc_html_e( 'Height', 'elodin-bridge' ); ?></span>
										<input type="number" class="small-text" min="1" step="1" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][height]" />
									</label>
								</div>
								<div class="elodin-bridge-admin__image-size-row-options">
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--crop">
										<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][crop]" value="0" />
										<span class="elodin-bridge-admin__image-size-switch">
											<input type="checkbox" class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][crop]" value="1" />
											<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
												<span class="elodin-bridge-admin__toggle-thumb"></span>
											</span>
										</span>
										<span><?php esc_html_e( 'Hard Crop', 'elodin-bridge' ); ?></span>
									</label>
									<label class="elodin-bridge-admin__image-size-field elodin-bridge-admin__image-size-field--checkbox elodin-bridge-admin__image-size-field--gallery">
										<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][gallery]" value="0" />
										<span class="elodin-bridge-admin__image-size-switch">
											<input type="checkbox" class="elodin-bridge-admin__toggle-input elodin-bridge-admin__image-size-toggle-input" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_IMAGE_SIZES ); ?>[sizes][__INDEX__][gallery]" value="1" />
											<span class="elodin-bridge-admin__toggle-track elodin-bridge-admin__toggle-track--small" aria-hidden="true">
												<span class="elodin-bridge-admin__toggle-thumb"></span>
											</span>
										</span>
										<span><?php esc_html_e( 'Allow In Galleries', 'elodin-bridge' ); ?></span>
									</label>
									<div class="elodin-bridge-admin__image-size-actions">
										<button type="button" class="button-link-delete elodin-bridge-admin__remove-image-size"><?php esc_html_e( 'Remove', 'elodin-bridge' ); ?></button>
									</div>
								</div>
							</div>
						</script>

						<p class="elodin-bridge-admin__note">
							<strong><?php esc_html_e( 'Important:', 'elodin-bridge' ); ?></strong>
							<?php esc_html_e( 'after enabling or changing image sizes, regenerate thumbnails before those sizes appear in galleries or are available for existing images.', 'elodin-bridge' ); ?>
						</p>
					</div>
				</div>
			</div>

				<div class="elodin-bridge-admin__card elodin-bridge-admin__card--wide" data-bridge-category="style">
					<div class="elodin-bridge-admin__feature <?php echo $block_edge_classes_enabled ? 'is-enabled' : ''; ?>">
					<label class="elodin-bridge-admin__feature-header" for="elodin-bridge-block-edge-classes-enabled">
						<input
							type="hidden"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enabled]"
							value="0"
						/>
						<input
							type="checkbox"
							class="elodin-bridge-admin__toggle-input elodin-bridge-admin__feature-toggle"
							id="elodin-bridge-block-edge-classes-enabled"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enabled]"
							value="1"
							<?php checked( $block_edge_classes_enabled ); ?>
						/>
						<span class="elodin-bridge-admin__toggle-track" aria-hidden="true">
							<span class="elodin-bridge-admin__toggle-thumb"></span>
						</span>
						<span class="elodin-bridge-admin__feature-title"><?php esc_html_e( 'Enable first/last block body classes', 'elodin-bridge' ); ?></span>
					</label>

					<div class="elodin-bridge-admin__feature-body">
						<p class="elodin-bridge-admin__description">
							<?php esc_html_e( 'Adds body classes for the first and/or last top-level block (for example: first-block-is-section, last-block-is-section, first-block-is-core-group, last-block-is-generateblocks-container). These sorts of body classes are useful for conditional styling. For example, you might want a transparent header and apply styles for that only when your first block is full-width, which can be inferred by whether a "section" style block is first or last.', 'elodin-bridge' ); ?>
						</p>

						<div class="elodin-bridge-admin__edge-toggle-list">
							<label class="elodin-bridge-admin__edge-toggle-item">
								<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_first]" value="0" />
								<input
									type="checkbox"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_first]"
									value="1"
									<?php checked( ! empty( $block_edge_class_settings['enable_first'] ) ); ?>
								/>
								<span><?php esc_html_e( 'Enable first block classes', 'elodin-bridge' ); ?></span>
							</label>
							<label class="elodin-bridge-admin__edge-toggle-item">
								<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_last]" value="0" />
								<input
									type="checkbox"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_last]"
									value="1"
									<?php checked( ! empty( $block_edge_class_settings['enable_last'] ) ); ?>
								/>
								<span><?php esc_html_e( 'Enable last block classes', 'elodin-bridge' ); ?></span>
							</label>
							<label class="elodin-bridge-admin__edge-toggle-item">
								<input type="hidden" name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_debug]" value="0" />
								<input
									type="checkbox"
									name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[enable_debug]"
									value="1"
									<?php checked( ! empty( $block_edge_class_settings['enable_debug'] ) ); ?>
								/>
								<span><?php esc_html_e( 'Show front-end top-level block debug panel', 'elodin-bridge' ); ?></span>
							</label>
						</div>

						<label class="elodin-bridge-admin__edge-textarea-label" for="elodin-bridge-section-blocks">
							<?php esc_html_e( 'Blocks that count as sections (shared for first and last block checks)', 'elodin-bridge' ); ?>
						</label>
						<textarea
							id="elodin-bridge-section-blocks"
							class="large-text code elodin-bridge-admin__edge-textarea"
							rows="8"
							name="<?php echo esc_attr( ELODIN_BRIDGE_OPTION_BLOCK_EDGE_CLASSES ); ?>[section_blocks]"
						><?php echo esc_textarea( implode( "\n", $block_edge_class_settings['section_blocks'] ) ); ?></textarea>
						<p class="elodin-bridge-admin__note">
							<?php esc_html_e( 'Enter one block name per line (example: core/group or generateblocks/container).', 'elodin-bridge' ); ?>
						</p>
						</div>
					</div>
				</div>

			</div>

		</form>
	</div>
