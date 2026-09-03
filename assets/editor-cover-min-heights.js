( () => {
	const selector = 'input[id^="block-cover-height-input-"]';
	const lowerMinimum = ( node ) => {
		if ( node.matches?.( selector ) && node.min !== '0' ) {
			node.min = '0';
		}
		node.querySelectorAll?.( selector ).forEach( ( input ) => {
			if ( input.min !== '0' ) {
				input.min = '0';
			}
		} );
	};

	wp.domReady( () => {
		lowerMinimum( document );
		new MutationObserver( ( records ) => records.forEach( ( record ) => {
			lowerMinimum( record.target );
			record.addedNodes.forEach( lowerMinimum );
		} ) ).observe( document.body, {
			attributeFilter: [ 'min' ],
			attributes: true,
			childList: true,
			subtree: true,
		} );
	} );
} )();
