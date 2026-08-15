( function ( root ) {
	const selector = '.ollie-video-modal video, video.wp-block-cover__video-background';

	function preventContextMenu( event ) {
		event.preventDefault();
	}

	function protectVideo( video ) {
		video.setAttribute( 'controlslist', 'nodownload' );
		video.addEventListener( 'contextmenu', preventContextMenu );
	}

	if ( 'object' === typeof module && module.exports ) {
		module.exports = protectVideo;
	}

	const { document, MutationObserver } = root;
	if ( ! document || ! MutationObserver ) {
		return;
	}

	function protectVideos( node ) {
		if ( node.matches && node.matches( selector ) ) {
			protectVideo( node );
		}
		if ( node.querySelectorAll ) {
			node.querySelectorAll( selector ).forEach( protectVideo );
		}
	}

	protectVideos( document );
	new MutationObserver( function ( records ) {
		records.forEach( function ( record ) {
			record.addedNodes.forEach( protectVideos );
		} );
	} ).observe( document.documentElement, { childList: true, subtree: true } );
} )( 'undefined' === typeof window ? globalThis : window );
