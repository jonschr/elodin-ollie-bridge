## Version 0.6.3

- Added a new default-on `Patterns` settings category and checkerboard pattern toggle.
- Added a checkerboard block pattern built from Cover, Columns, and inner Group blocks, matching saved block markup so it inserts cleanly in the editor.
- Added per-instance checkerboard layout controls on the parent Cover block for local content width, section padding, and left/right width ratios.
- Added checkerboard front-end and editor styling with runtime CSS-variable overrides so each inserted checkerboard instance can be configured independently.
- Updated checkerboard section padding controls to use WordPress spacing-preset style values and a core-like editor experience for preset/custom spacing entry.
- Updated the `Edit Site` admin-bar shortcut feature so the `Edit Site` menu appears anywhere the admin bar is shown, not just on the front end.
- Tightened the Ollie palette-removal cleanup so it removes only persisted palette data and no longer deletes user-created gradient or duotone settings.

## Version 0.6.2

- Configured plugin updates via `plugin-update-checker` against the GitHub repository (`jonschr/elodin-ollie-bridge`).
- Set update checks to target the `master` branch by default.
- Updated branch allowlist behavior so the configured default branch is permitted without extra filter setup.
- Fixed auto-generated gradients/duotones to use concrete theme palette color values instead of `var(--wp--preset--color--...)` references, preventing invalid preset output.

## Version 0.6.1

- Refactored the Nested Group Shortcut feature into standard plugin module locations (`inc/` + `assets/`) and removed its standalone plugin header so it no longer appears as a separate plugin entry.
- Repositioned the Nested Group Shortcut toggle within the Editor settings list so it follows existing settings order.
- Removed the Balanced Text toggle feature and related setting/module assets because equivalent functionality now exists in WordPress core.

## Version 0.6.0

- Added a standalone, default-off Style Tweaks setting for default non-first heading spacing.
- Added heading spacing defaults scoped to non-first children: `h2`/`.h2` use `var(--wp--preset--spacing--large)` and `h3`/`h4`/`.h3`/`.h4` use `var(--wp--preset--spacing--medium)`.
- Explicitly excluded `h1`/`.h1` from this new heading spacing behavior.
- Updated selector output to target block flow layout spacing (`margin-block-start`) with fallback `margin-top`, while keeping editor Styles overrides possible.

## Version 0.5.2

- Updated image size registration and editor integration so custom image sizes are always available in editor image-size pickers, including FSE/featured image workflows.
- Added a stronger fallback injection into block editor settings to ensure custom sizes have both labels and dimensions in `imageSizes` / `imageDimensions`.
- Simplified image-size UI by removing the redundant per-size gallery toggle and treating custom sizes as always exposed.
- Added an editor setting to auto-generate missing child-theme gradient and duotone presets from color-variable pair combinations declared in the child theme's existing presets.

## Version 0.5

- Added reusable backend feature walkthrough videos with a settings-card trigger (`See this in action`) and a shared lightbox player.
- Added walkthrough video entries for Balanced Text, heading/paragraph overrides, "Edit Site" admin-bar shortcuts, Site Editor admin-bar visibility, core block boundary highlights, CSS variable auto-wrap, and remove Ollie palettes.
- Added shared video support so one video can be reused across multiple settings (used for both editor fullscreen mode and default "Show template" settings).
- Updated the walkthrough button styling to a smaller, modern control with a play icon and refined hover/focus behavior.
- Updated the video modal to open centered at large viewport size, preserve per-video aspect ratio, and reduce autoplay flicker via loading-state masking.
- Added Loom embed parameter handling to reduce player chrome in modal playback.
- Updated heading/paragraph style overrides so paragraph style now explicitly defaults to `text-transform: none` when no paragraph text-transform is defined, preventing heading uppercase styles from persisting when `.p` is applied.

## Version 0.4

- Extended CSS variable auto-wrap support to Ollie Pro Class Manager editors (`.ollie-css-editor-wrapper`) so spacing/font tokens are expanded in those CodeMirror fields.
- Changed the default for "Repair fixed-position background images on mobile" to off.
- Added a new default-on Editor tweak that injects `Styles`, `Navigation`, `Pages`, `Templates`, and `Patterns` shortcut links under the core `Edit Site` toolbar item.
- Added an experimental default-off Editor tweak to show the top WordPress admin bar in Site Editor (`site-editor.php`) by forcing fullscreen mode off in that context.
- Refined the experimental Site Editor admin-bar tweak so `#adminmenumain` stays hidden and the editor interface/canvas no longer shifts right to reserve sidebar space.

## Version 0.3

- Added balanced text toolbar toggle support for `Post Title` (`core/post-title`) and `Post Excerpt` (`core/post-excerpt`) blocks.
- Expanded balanced text CSS handling so the `.balanced` class also applies to post title and post excerpt output.
- Updated the balanced text setting description to reflect the newly supported blocks.

## Version 0.2

- Added a new default-on setting to remove Ollie Pro color palette overrides.
- Improved enforcement so child theme palettes remain active in frontend, admin, REST, and global styles responses.
- Added cleanup for cached/stored global styles color settings tied to prior palette data.

## Version 0.1

- Initial commit for Ollie.
