( function () {
	const config = window.elodinBridgeScrollHideElements || {};
	const body = document.body;
	const rules = Array.isArray( config.rules ) ? config.rules : [];
	let isTicking = false;

	if ( ! body || ! rules.length ) {
		return;
	}

	const resolveScrollDistance = function ( value ) {
		const rawValue = String( value || '' ).trim();
		const cssValue = rawValue === '' ? '0px' : rawValue;
		const normalizedValue = /^-?\d+(\.\d+)?$/.test( cssValue )
			? cssValue + 'px'
			: cssValue;

		if ( ! resolveScrollDistance.measureElement ) {
			resolveScrollDistance.measureElement = document.createElement( 'div' );
			resolveScrollDistance.measureElement.style.cssText =
				'position:absolute;visibility:hidden;pointer-events:none;width:0;height:0;overflow:hidden;';
			body.appendChild( resolveScrollDistance.measureElement );
		}

		resolveScrollDistance.measureElement.style.height = '0px';
		resolveScrollDistance.measureElement.style.height = normalizedValue;

		const pixels = Number.parseFloat(
			window.getComputedStyle( resolveScrollDistance.measureElement ).height
		);

		return Number.isFinite( pixels ) ? Math.max( pixels, 0 ) : 0;
	};

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
				threshold: resolveScrollDistance( rule.threshold ),
				isHidden: false,
				showThreshold: resolveScrollDistance( rule.show_threshold ),
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
