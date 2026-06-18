( function () {
	const config = window.elodinBridgeScrollHideElements || {};
	const body = document.body;
	const rules = Array.isArray( config.rules ) ? config.rules : [];
	let isTicking = false;

	if ( ! body || ! rules.length ) {
		return;
	}

	const entries = rules
		.map( function ( rule ) {
			const selectors = rule.selectors || '';
			let elements = [];

			try {
				elements = Array.from( document.querySelectorAll( selectors ) );
			} catch ( error ) {
				elements = [];
			}

			elements.forEach( function ( element ) {
				element.classList.add( 'elodin-bridge-scroll-hide-target' );
			} );

			return {
				elements,
				threshold: Math.max( Number.parseInt( rule.threshold, 10 ) || 0, 0 ),
				isHidden: false,
				showThreshold: Math.max(
					Number.parseInt( rule.show_threshold, 10 ) || 0,
					0
				),
			};
		} )
		.filter( function ( entry ) {
			return entry.elements.length;
		} );

	if ( ! entries.length ) {
		return;
	}

	const updateHiddenElements = function () {
		const scrollY = window.scrollY;

		entries.forEach( function ( entry ) {
			const showThreshold = Math.min( entry.showThreshold, entry.threshold );
			let isHidden = entry.isHidden;

			if ( ! isHidden && scrollY > entry.threshold ) {
				isHidden = true;
			} else if ( isHidden && scrollY < showThreshold ) {
				isHidden = false;
			}

			entry.isHidden = isHidden;

			entry.elements.forEach( function ( element ) {
				element.classList.toggle(
					'is-elodin-bridge-scroll-hidden',
					isHidden
				);
			} );
		} );
	};

	const requestUpdate = function () {
		if ( isTicking ) {
			return;
		}

		isTicking = true;
		window.requestAnimationFrame( function () {
			updateHiddenElements();
			isTicking = false;
		} );
	};

	updateHiddenElements();
	window.addEventListener( 'scroll', requestUpdate, { passive: true } );
}() );
