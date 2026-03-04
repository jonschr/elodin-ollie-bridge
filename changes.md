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
