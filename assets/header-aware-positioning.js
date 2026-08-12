( function () {
	'use strict';

	const config = window.elodinBridgeHeaderAwarePositioning || {};
	const root = document.documentElement;
	const body = document.body;
	const selectors = config.selectors || '*.site-header, #wpadminbar';
	const fixedSelectors = config.fixedSelectors || '';
	let animationFrame = null;
	let scrollAnimationFrame = null;
	let currentHeight = null;
	let measuredElements = [];
	let fixedElements = [];
	let observedElements = [];
	const pendingElementHeights = new Map();

	if ( ! root || ! body ) {
		return;
	}

	try {
		measuredElements = Array.from( document.querySelectorAll( selectors ) );
	} catch ( error ) {
		measuredElements = Array.from( document.querySelectorAll( '*.site-header, #wpadminbar' ) );
	}

	if ( fixedSelectors ) {
		try {
			fixedElements = Array.from( document.querySelectorAll( fixedSelectors ) );
		} catch ( error ) {
			fixedElements = [];
		}

	}

	observedElements = Array.from( new Set( measuredElements.concat( fixedElements ) ) );

	const getElementHeight = function ( element, height ) {
		if ( ! element.getClientRects().length ) {
			return 0;
		}

		return Math.ceil( height );
	};

	const getObservedHeight = function ( entry ) {
		const borderBoxSize = Array.isArray( entry.borderBoxSize )
			? entry.borderBoxSize[ 0 ]
			: entry.borderBoxSize;

		return getElementHeight(
			entry.target,
			borderBoxSize ? borderBoxSize.blockSize : entry.contentRect.height
		);
	};

	const measureElementHeight = function ( element ) {
		return element
			? getElementHeight( element, element.getBoundingClientRect().height )
			: 0;
	};

	const measureSelectedElementsHeight = function () {
		return measuredElements.reduce( function ( total, element ) {
			const pendingHeight = pendingElementHeights.get( element );

			return total + ( pendingHeight ?? measureElementHeight( element ) );
		}, 0 );
	};

	const measureHeaderSizingHeight = function () {
		return measuredElements.reduce( function ( total, element ) {
			if ( 'wpadminbar' === element.id ) {
				return total;
			}

			const pendingHeight = pendingElementHeights.get( element );
			return total + ( pendingHeight ?? measureElementHeight( element ) );
		}, 0 );
	};

	const setHeaderHeight = function ( height, sizingHeight ) {
		if ( currentHeight && height === currentHeight.height && sizingHeight === currentHeight.sizing ) {
			return;
		}

		currentHeight = { height, sizing: sizingHeight };
		const value = `${ height }px`;
		const sizingValue = `${ sizingHeight }px`;

		root.style.setProperty( '--elodin-bridge-header-height', value );
		root.style.setProperty( '--elodin-bridge-header-sizing-height', sizingValue );
		body.style.setProperty( '--elodin-bridge-header-height', value );
		body.style.setProperty( '--elodin-bridge-header-sizing-height', sizingValue );
		document.dispatchEvent(
			new CustomEvent( 'elodinBridgeHeaderHeightChange', {
				detail: { height },
			} )
		);
	};

	const update = function () {
		animationFrame = null;
		const height = measureSelectedElementsHeight();
		const sizingHeight = measureHeaderSizingHeight();

		pendingElementHeights.clear();
		setHeaderHeight( height, sizingHeight );
	};

	const requestUpdate = function () {
		if ( animationFrame ) {
			return;
		}

		animationFrame = window.requestAnimationFrame( update );
	};

	const updateScrolledElements = function () {
		const isScrolled = window.scrollY > 0;
		let changed = false;

		fixedElements.forEach( function ( element ) {
			if ( element.classList.contains( 'is-scrolled' ) === isScrolled ) {
				return;
			}

			element.classList.toggle( 'is-scrolled', isScrolled );
			changed = true;
		} );

		if ( changed ) {
			pendingElementHeights.clear();
			requestUpdate();
		}
	};

	const requestScrolledElementsUpdate = function () {
		if ( scrollAnimationFrame ) {
			return;
		}

		scrollAnimationFrame = window.requestAnimationFrame( function () {
			scrollAnimationFrame = null;
			updateScrolledElements();
		} );
	};

	window.elodinBridgeHeaderOffset = {
		getHeight: function () {
			return currentHeight ? currentHeight.height : 0;
		},
		refresh: requestUpdate,
	};

	// Establish the CSS contract before the browser performs initial hash navigation.
	update();
	updateScrolledElements();
	window.addEventListener( 'load', requestUpdate, { once: true } );
	window.addEventListener( 'scroll', requestScrolledElementsUpdate, { passive: true } );
	window.addEventListener( 'resize', function () {
		pendingElementHeights.clear();
		requestUpdate();
	}, { passive: true } );

	if ( 'ResizeObserver' in window ) {
		const resizeObserver = new ResizeObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				pendingElementHeights.set( entry.target, getObservedHeight( entry ) );
			} );

			requestUpdate();
		} );

		observedElements.forEach( function ( element ) {
			resizeObserver.observe( element );
		} );
	}
}() );
