( function () {
	'use strict';

	const config = window.elodinBridgeHeaderAwarePositioning || {};
	const root = document.documentElement;
	const body = document.body;
	const selectors = config.selectors || 'header, #wpadminbar';
	let animationFrame = null;
	let currentHeight = null;
	let measuredElements = [];
	const pendingElementHeights = new Map();

	if ( ! root || ! body ) {
		return;
	}

	try {
		measuredElements = Array.from( document.querySelectorAll( selectors ) );
	} catch ( error ) {
		measuredElements = Array.from( document.querySelectorAll( 'header, #wpadminbar' ) );
	}

	const getObservedHeight = function ( entry ) {
		const borderBoxSize = Array.isArray( entry.borderBoxSize )
			? entry.borderBoxSize[ 0 ]
			: entry.borderBoxSize;

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

	const setHeaderHeight = function ( height ) {
		if ( height === currentHeight ) {
			return;
		}

		currentHeight = height;
		const value = `${ height }px`;

		root.style.setProperty( '--elodin-bridge-header-height', value );
		body.style.setProperty( '--elodin-bridge-header-height', value );
		document.dispatchEvent(
			new CustomEvent( 'elodinBridgeHeaderHeightChange', {
				detail: { height },
			} )
		);
	};

	const update = function () {
		animationFrame = null;
		const height = measureSelectedElementsHeight();

		pendingElementHeights.clear();
		setHeaderHeight( height );
	};

	const requestUpdate = function () {
		if ( animationFrame ) {
			return;
		}

		animationFrame = window.requestAnimationFrame( update );
	};

	window.elodinBridgeHeaderOffset = {
		getHeight: function () {
			return currentHeight || 0;
		},
		refresh: requestUpdate,
	};

	// Establish the CSS contract before the browser performs initial hash navigation.
	update();
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
