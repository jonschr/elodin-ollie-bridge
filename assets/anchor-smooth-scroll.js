( function () {
	'use strict';

	const getInitialHashTarget = function () {
		if ( ! window.location.hash || window.location.hash.length < 2 ) {
			return null;
		}

		let targetName = window.location.hash.slice( 1 );
		try {
			targetName = decodeURIComponent( targetName );
		} catch ( error ) {
			// Use the literal hash value when it is not valid URI-encoded text.
		}

		return document.getElementById( targetName ) || document.getElementsByName( targetName )[ 0 ] || null;
	};

	const correctInitialHashPosition = function () {
		const target = getInitialHashTarget();
		if ( ! target ) {
			return;
		}

		window.requestAnimationFrame( function () {
			const root = document.documentElement;
			const previousBehavior = root.style.getPropertyValue( 'scroll-behavior' );
			const previousPriority = root.style.getPropertyPriority( 'scroll-behavior' );

			root.style.setProperty( 'scroll-behavior', 'auto', 'important' );
			target.scrollIntoView( { block: 'start' } );

			if ( previousBehavior ) {
				root.style.setProperty( 'scroll-behavior', previousBehavior, previousPriority );
			} else {
				root.style.removeProperty( 'scroll-behavior' );
			}
		} );
	};

	if ( document.readyState === 'complete' ) {
		correctInitialHashPosition();
	} else {
		window.addEventListener( 'load', correctInitialHashPosition, { once: true } );
	}
}() );
