( function () {
	const config = window.elodinBridgeStickyBelowHeader || {};
	const body = document.body;
	const selectors = config.selectors || 'header, #wpadminbar';
	const offset = config.offset || 'var(--wp--preset--spacing--large, 3rem)';
	let animationFrame = null;
	let currentOffsetHeight = null;
	let measuredElements = [];
	const pendingElementHeights = new Map();

	if ( ! body ) {
		return;
	}

	try {
		measuredElements = Array.from( document.querySelectorAll( selectors ) );
	} catch ( error ) {
		measuredElements = Array.from( document.querySelectorAll( 'header, #wpadminbar' ) );
	}

	const getObservedHeight = function ( entry ) {
		const borderBoxSize = entry.borderBoxSize && entry.borderBoxSize[ 0 ];

		return Math.ceil(
			borderBoxSize ? borderBoxSize.blockSize : entry.contentRect.height
		);
	};

	const measureElementHeight = function ( element ) {
		return element ? Math.ceil( element.getBoundingClientRect().height ) : 0;
	};

	const measureSelectedElementsHeight = function () {
		return measuredElements.reduce( function ( total, element ) {
			const pendingHeight = pendingElementHeights.get( element );

			return total + ( pendingHeight ?? measureElementHeight( element ) );
		}, 0 );
	};

	const setHeaderOffset = function ( offsetHeight ) {
		animationFrame = null;

		if ( offsetHeight === currentOffsetHeight ) {
			return;
		}

		currentOffsetHeight = offsetHeight;

		const offsetHeightValue = `${ offsetHeight }px`;

		body.style.setProperty( '--elodin-bridge-header-height', offsetHeightValue );
		body.style.setProperty(
			'--elodin-bridge-sticky-below-header-bonus-offset',
			offset
		);
		body.style.setProperty(
			'--elodin-bridge-sticky-below-header-offset',
			`calc(${ offsetHeightValue } + ${ offset })`
		);
	};

	const requestUpdate = function () {
		if ( animationFrame ) {
			return;
		}

		animationFrame = window.requestAnimationFrame( function () {
			const elementsHeight = measureSelectedElementsHeight();

			pendingElementHeights.clear();
			setHeaderOffset( elementsHeight );
		} );
	};

	requestUpdate();
	window.addEventListener( 'load', requestUpdate, { once: true } );

	if ( 'ResizeObserver' in window ) {
		const resizeObserver = new ResizeObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				pendingElementHeights.set( entry.target, getObservedHeight( entry ) );
			} );

			requestUpdate();
		} );

		measuredElements.forEach( function ( element ) {
			resizeObserver.observe( element );
		} );
	} else {
		window.addEventListener( 'resize', requestUpdate, { passive: true } );
	}
}() );
