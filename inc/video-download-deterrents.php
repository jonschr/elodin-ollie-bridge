<?php

/**
 * Enqueue the optional frontend video download deterrents.
 */
function elodin_bridge_enqueue_video_download_deterrents() {
	if ( ! elodin_bridge_is_video_download_deterrents_enabled() ) {
		return;
	}

	$script_path = ELODIN_BRIDGE_DIR . '/assets/video-download-deterrents.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'elodin-bridge-video-download-deterrents',
		ELODIN_BRIDGE_URL . 'assets/video-download-deterrents.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'elodin_bridge_enqueue_video_download_deterrents' );
