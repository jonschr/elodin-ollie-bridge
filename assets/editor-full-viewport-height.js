( function ( global ) {
	'use strict';

	function getDimensions( name, attrs ) {
		const dimensions = { ...attrs.style?.dimensions };
		if ( name === 'core/cover' && dimensions.minHeight === undefined && attrs.minHeight !== undefined ) {
			dimensions.minHeight = `${ attrs.minHeight }${ attrs.minHeightUnit || 'px' }`;
		}
		const result = {};
		for ( const [ attribute, property ] of Object.entries( { height: 'height', minHeight: 'min-height' } ) ) {
			const match = typeof dimensions[ attribute ] === 'string' && dimensions[ attribute ].trim().match( /^100(?:\.0+)?(dvh|svh|lvh|vh)$/i );
			if ( match ) {
				result[ property ] = match[ 1 ].toLowerCase();
			}
		}
		return result;
	}
	if ( typeof module === 'object' && module.exports ) {
		module.exports = getDimensions;
	}
	if ( ! global.wp ) {
		return;
	}

	const { hooks, compose, element } = global.wp;
	const selectors = global.elodinBridgeFullViewportHeight?.selectors || '*.site-header, #wpadminbar';

	hooks.addFilter( 'editor.BlockListBlock', 'elodin-bridge/full-viewport-height', compose.createHigherOrderComponent( ( BlockListBlock ) => ( props ) => {
		const dimensions = getDimensions( props.name, props.attributes || {} );
		const active = Object.keys( dimensions ).length > 0;
		element.useEffect( () => {
			if ( ! active ) {
				return undefined;
			}
			const documents = [ global.document, ...Array.from( global.document.querySelectorAll( 'iframe[name="editor-canvas"]' ), ( frame ) => frame.contentDocument ).filter( Boolean ) ];
			const block = documents.map( ( doc ) => doc.getElementById( `block-${ props.clientId }` ) ).find( Boolean );
			if ( ! block ) {
				return undefined;
			}
			const doc = block.ownerDocument;
			const view = doc.defaultView;
			const scope = view.frameElement ? doc : block.closest( '.editor-styles-wrapper' ) || doc;
			let frame = null;
			let measured = [];
			const measure = () => {
				const height = measured.reduce( ( total, header ) => total + ( header.getClientRects().length ? Math.ceil( header.getBoundingClientRect().height ) : 0 ), 0 );
				doc.documentElement.style.setProperty( '--elodin-bridge-full-height-offset', `${ height }px` );
			};
			const observer = 'ResizeObserver' in view ? new view.ResizeObserver( measure ) : null;
			const refresh = () => {
				let headers;
				try {
					headers = Array.from( scope.querySelectorAll( selectors ) );
				} catch ( error ) {
					headers = Array.from( scope.querySelectorAll( '*.site-header, #wpadminbar' ) );
				}
				const adminBar = scope.querySelector( '#wpadminbar' );
				measured = Array.from( new Set( headers.concat( adminBar ? [ adminBar ] : [] ) ) );
				if ( observer ) {
					observer.disconnect();
					measured.forEach( ( header ) => observer.observe( header ) );
				}
				measure();
			};
			const requestRefresh = () => {
				if ( frame === null ) {
					frame = view.requestAnimationFrame( () => { frame = null; refresh(); } );
				}
			};
			refresh();
			view.addEventListener( 'resize', requestRefresh );
			const mutations = new view.MutationObserver( requestRefresh );
			mutations.observe( scope, { childList: true, subtree: true } );
			return () => {
				view.removeEventListener( 'resize', requestRefresh );
				if ( frame !== null ) { view.cancelAnimationFrame( frame ); }
				observer?.disconnect();
				mutations.disconnect();
			};
		}, [ active, props.clientId ] );

		if ( ! active ) {
			return element.createElement( BlockListBlock, props );
		}
		const wrapperProps = { ...props.wrapperProps, style: { ...props.wrapperProps?.style } };
		wrapperProps.className = [ wrapperProps.className, ...Object.keys( dimensions ).map( ( property ) => `elodin-bridge-full-viewport-${ property }` ) ].filter( Boolean ).join( ' ' );
		for ( const [ property, unit ] of Object.entries( dimensions ) ) {
			wrapperProps.style[ `--elodin-bridge-full-viewport-${ property }` ] = `max(0px,calc(100${ unit } - var(--elodin-bridge-full-height-offset,0px)))`;
		}
		return element.createElement( BlockListBlock, { ...props, wrapperProps } );
	}, 'withFullViewportHeight' ) );
} )( typeof window === 'undefined' ? globalThis : window );
