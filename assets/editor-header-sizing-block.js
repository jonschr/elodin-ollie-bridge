( function ( blocks, element, i18n ) {
	'use strict';
	const config = window.elodinBridgeHeaderSizingBlock || {};
	const selectors = config.selectors || '*.site-header, #wpadminbar';

	const HeaderSizingPreview = function () {
		const previewRef = element.useRef();
		const [ height, setHeight ] = element.useState( 0 );

		element.useEffect( function () {
			const preview = previewRef.current;
			if ( ! preview ) {
				return undefined;
			}

			const previewDocument = preview.ownerDocument;
			const previewWindow = previewDocument.defaultView;
			const scope = previewWindow.frameElement
				? previewDocument
				: preview.closest( '.editor-styles-wrapper' ) || previewDocument;
			let measuredElements = [];
			let animationFrame = null;
			const resizeObserver = 'ResizeObserver' in previewWindow
				? new previewWindow.ResizeObserver( function () {
					measure();
				} )
				: null;

			const resolveMeasuredElements = function () {
				try {
					measuredElements = Array.from( scope.querySelectorAll( selectors ) );
				} catch ( error ) {
					measuredElements = Array.from( scope.querySelectorAll( '*.site-header, #wpadminbar' ) );
				}
			};

			const measure = function () {
				setHeight(
					measuredElements.reduce( function ( total, header ) {
						if ( ! header.getClientRects().length ) {
							return total;
						}

						return 'wpadminbar' === header.id
							? total
							: total + Math.ceil( header.getBoundingClientRect().height );
					}, 0 )
				);
			};

			const refresh = function () {
				if ( resizeObserver ) {
					resizeObserver.disconnect();
				}

				resolveMeasuredElements();
				measuredElements.forEach( function ( header ) {
					if ( resizeObserver ) {
						resizeObserver.observe( header );
					}
				} );
				measure();
			};

			const requestRefresh = function () {
				if ( animationFrame ) {
					return;
				}

				animationFrame = previewWindow.requestAnimationFrame( function () {
					animationFrame = null;
					refresh();
				} );
			};

			refresh();
			previewWindow.addEventListener( 'resize', requestRefresh );
			const mutationObserver = new previewWindow.MutationObserver( requestRefresh );
			mutationObserver.observe( scope, { childList: true, subtree: true } );

			return function () {
				previewWindow.removeEventListener( 'resize', requestRefresh );
				if ( animationFrame ) {
					previewWindow.cancelAnimationFrame( animationFrame );
				}
				mutationObserver.disconnect();
				if ( resizeObserver ) {
					resizeObserver.disconnect();
				}
			};
		}, [] );

		return element.createElement(
			'div',
			{
				ref: previewRef,
				className: 'wp-block-elodin-bridge-header-sizing',
				style: {
					alignItems: 'center',
					backgroundColor: 'rgba(0, 0, 0, 0.5)',
					boxSizing: 'border-box',
					color: '#fff',
					display: 'flex',
					fontSize: '14px',
					fontWeight: '600',
					height: `${ Math.max( height, 32 ) }px`,
					justifyContent: 'center',
					lineHeight: '1.2',
					margin: 0,
					minHeight: '32px',
					outline: '1px dashed #8c8f94',
					padding: 0,
					position: 'relative',
					textAlign: 'center',
					textShadow: '0 1px 1px rgba(0, 0, 0, 0.5)',
					width: '100%',
					zIndex: 5,
				},
			},
			i18n.__( 'Header height placeholder', 'elodin-bridge' )
		);
	};

	blocks.registerBlockType( 'elodin-bridge/header-sizing', {
		apiVersion: 3,
		title: i18n.__( 'Header Sizing', 'elodin-bridge' ),
		description: i18n.__( 'Adds space equal to the measured page chrome.', 'elodin-bridge' ),
		icon: 'editor-contract',
		category: 'design',
		supports: { html: false, inserter: 0 !== config.enabled },
		edit: function () {
			return element.createElement( HeaderSizingPreview );
		},
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.i18n ) );
