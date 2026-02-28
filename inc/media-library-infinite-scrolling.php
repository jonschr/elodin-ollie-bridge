<?php

/**
 * Optionally force media library infinite scrolling.
 *
 * @param bool $enabled Current media library infinite scrolling state.
 * @return bool
 */
function elodin_bridge_filter_media_library_infinite_scrolling( $enabled ) {
	if ( elodin_bridge_is_media_library_infinite_scrolling_enabled() ) {
		return true;
	}

	return (bool) $enabled;
}
add_filter( 'media_library_infinite_scrolling', 'elodin_bridge_filter_media_library_infinite_scrolling', 20 );
