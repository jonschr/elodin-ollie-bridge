( function ( wp ) {
	if (
		! wp ||
		! wp.hooks ||
		! wp.compose ||
		! wp.element ||
		! wp.blockEditor ||
		! wp.components ||
		! wp.i18n
	) {
		return;
	}

	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { createElement: el, Fragment } = wp.element;
	const {
		InspectorControls,
		__experimentalSpacingSizesControl: SpacingSizesControl,
	} = wp.blockEditor;
	const { PanelBody, RangeControl, TextControl } = wp.components;
	const { __ } = wp.i18n;

	const config = window.elodinBridgeCheckerboardPattern || {};
	const defaults = Object.assign(
		{
			localContentWidth: 'var(--wp--style--global--wide-size)',
			sectionPaddingHorizontal: 'var:preset|spacing|x-large',
			sectionPaddingVertical: 'var:preset|spacing|x-large',
			widthRatioLeft: 0.5,
			widthRatioRight: 0.5,
		},
		config.defaults || {}
	);
	const checkerboardClass = String( config.className || 'checkerboard' ).trim() || 'checkerboard';

	const ATTRIBUTE_KEYS = {
		localContentWidth: 'elodinCheckerboardLocalContentWidth',
		sectionPaddingHorizontal: 'elodinCheckerboardSectionPaddingHorizontal',
		sectionPaddingVertical: 'elodinCheckerboardSectionPaddingVertical',
		widthRatioLeft: 'elodinCheckerboardWidthRatioLeft',
		widthRatioRight: 'elodinCheckerboardWidthRatioRight',
	};

	function parseClasses( className ) {
		return String( className || '' )
			.split( /\s+/ )
			.filter( Boolean );
	}

	function hasCheckerboardClass( className ) {
		return parseClasses( className ).includes( checkerboardClass );
	}

	function sanitizeCssValue( value, fallback ) {
		const normalized = String( value || '' ).trim();
		if ( ! normalized ) {
			return fallback;
		}

		if ( /[;{}\\]/.test( normalized ) ) {
			return fallback;
		}

		if ( ! /^[a-zA-Z0-9%().,_+*/\-\s]+$/.test( normalized ) ) {
			return fallback;
		}

		return normalized.replace( /\s+/g, ' ' );
	}

	function getSpacingPresetToCssValue( value ) {
		const normalized = String( value || '' ).trim();
		const match = normalized.match( /^var:preset\|spacing\|([a-z0-9-]+)$/i );
		if ( ! match ) {
			return '';
		}

		return `var(--wp--preset--spacing--${ match[ 1 ] })`;
	}

	function getSpacingCssVarToPresetValue( value ) {
		const normalized = String( value || '' ).trim();
		const match = normalized.match( /^var\(--wp--preset--spacing--([a-z0-9-]+)\)$/i );
		if ( ! match ) {
			return '';
		}

		return `var:preset|spacing|${ match[ 1 ] }`;
	}

	function normalizeSpacingAttributeValue( value, fallback ) {
		const normalized = String( value || '' ).trim();
		if ( ! normalized ) {
			return fallback;
		}

		const presetValue = getSpacingCssVarToPresetValue( normalized );
		if ( presetValue ) {
			return presetValue;
		}

		return normalized;
	}

	function normalizeSpacingCssValue( value, fallback ) {
		const normalized = normalizeSpacingAttributeValue( value, fallback );
		const presetCssValue = getSpacingPresetToCssValue( normalized );
		if ( presetCssValue ) {
			return presetCssValue;
		}

		return sanitizeCssValue( normalized, sanitizeCssValue( fallback, '' ) );
	}

	function sanitizeRatioValue( value, fallback ) {
		const parsed = Number.parseFloat( value );
		if ( Number.isNaN( parsed ) ) {
			return fallback;
		}

		if ( parsed < 0 ) {
			return 0;
		}

		if ( parsed > 1 ) {
			return 1;
		}

		return parsed;
	}

	function getCheckerboardStyleValues( attributes ) {
		return {
			'--local-content-width': sanitizeCssValue(
				attributes?.[ ATTRIBUTE_KEYS.localContentWidth ],
				defaults.localContentWidth
			),
			'--section-padding-horizontal': normalizeSpacingCssValue(
				attributes?.[ ATTRIBUTE_KEYS.sectionPaddingHorizontal ],
				defaults.sectionPaddingHorizontal
			),
			'--section-padding-vertical': normalizeSpacingCssValue(
				attributes?.[ ATTRIBUTE_KEYS.sectionPaddingVertical ],
				defaults.sectionPaddingVertical
			),
			'--width-ratio-left': String(
				sanitizeRatioValue(
					attributes?.[ ATTRIBUTE_KEYS.widthRatioLeft ],
					defaults.widthRatioLeft
				)
			),
			'--width-ratio-right': String(
				sanitizeRatioValue(
					attributes?.[ ATTRIBUTE_KEYS.widthRatioRight ],
					defaults.widthRatioRight
				)
			),
		};
	}

	function getCheckerboardStyleOverrides( attributes ) {
		const values = getCheckerboardStyleValues( attributes );
		const overrides = {};

		if ( values['--local-content-width'] !== sanitizeCssValue( defaults.localContentWidth, '' ) ) {
			overrides['--local-content-width'] = values['--local-content-width'];
		}

		if (
			values['--section-padding-horizontal'] !==
			normalizeSpacingCssValue( defaults.sectionPaddingHorizontal, '' )
		) {
			overrides['--section-padding-horizontal'] = values['--section-padding-horizontal'];
		}

		if (
			values['--section-padding-vertical'] !==
			normalizeSpacingCssValue( defaults.sectionPaddingVertical, '' )
		) {
			overrides['--section-padding-vertical'] = values['--section-padding-vertical'];
		}

		if (
			sanitizeRatioValue( attributes?.[ ATTRIBUTE_KEYS.widthRatioLeft ], defaults.widthRatioLeft ) !==
			sanitizeRatioValue( defaults.widthRatioLeft, defaults.widthRatioLeft )
		) {
			overrides['--width-ratio-left'] = values['--width-ratio-left'];
		}

		if (
			sanitizeRatioValue( attributes?.[ ATTRIBUTE_KEYS.widthRatioRight ], defaults.widthRatioRight ) !==
			sanitizeRatioValue( defaults.widthRatioRight, defaults.widthRatioRight )
		) {
			overrides['--width-ratio-right'] = values['--width-ratio-right'];
		}

		return overrides;
	}

	const managedBlockName = 'core/cover';

	const withCheckerboardInspectorControls = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if (
					managedBlockName !== props.name ||
					! props.isSelected ||
					! hasCheckerboardClass( props.attributes?.className )
				) {
					return el( BlockEdit, props );
				}

				return el(
					Fragment,
					null,
					el( BlockEdit, props ),
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{
								title: __( 'Checkerboard Layout', 'elodin-bridge' ),
								initialOpen: true,
							},
							el( TextControl, {
								label: __( 'Local content width', 'elodin-bridge' ),
								help: __( 'CSS value used for the aligned content calculation.', 'elodin-bridge' ),
								value:
									props.attributes?.[ ATTRIBUTE_KEYS.localContentWidth ] ||
									defaults.localContentWidth,
								onChange: ( value ) =>
									props.setAttributes( {
										[ ATTRIBUTE_KEYS.localContentWidth ]: value,
									} ),
							} ),
								SpacingSizesControl
									? el( SpacingSizesControl, {
										label: __( 'Horizontal section padding', 'elodin-bridge' ),
										showSideInLabel: false,
										sides: [ 'horizontal' ],
										values: {
											left: normalizeSpacingAttributeValue(
												props.attributes?.[ ATTRIBUTE_KEYS.sectionPaddingHorizontal ],
												defaults.sectionPaddingHorizontal
											),
											right: normalizeSpacingAttributeValue(
												props.attributes?.[ ATTRIBUTE_KEYS.sectionPaddingHorizontal ],
												defaults.sectionPaddingHorizontal
											),
										},
										onChange: ( nextValues ) =>
											props.setAttributes( {
												[ ATTRIBUTE_KEYS.sectionPaddingHorizontal ]:
													nextValues?.left ||
													nextValues?.right ||
													defaults.sectionPaddingHorizontal,
											} ),
									} )
								: el( TextControl, {
										label: __( 'Horizontal section padding', 'elodin-bridge' ),
										value: normalizeSpacingAttributeValue(
											props.attributes?.[ ATTRIBUTE_KEYS.sectionPaddingHorizontal ],
											defaults.sectionPaddingHorizontal
										),
										onChange: ( value ) =>
											props.setAttributes( {
												[ ATTRIBUTE_KEYS.sectionPaddingHorizontal ]: value,
											} ),
									} ),
								SpacingSizesControl
									? el( SpacingSizesControl, {
										label: __( 'Vertical section padding', 'elodin-bridge' ),
										showSideInLabel: false,
										sides: [ 'vertical' ],
										values: {
											top: normalizeSpacingAttributeValue(
												props.attributes?.[ ATTRIBUTE_KEYS.sectionPaddingVertical ],
												defaults.sectionPaddingVertical
											),
											bottom: normalizeSpacingAttributeValue(
												props.attributes?.[ ATTRIBUTE_KEYS.sectionPaddingVertical ],
												defaults.sectionPaddingVertical
											),
										},
										onChange: ( nextValues ) =>
											props.setAttributes( {
												[ ATTRIBUTE_KEYS.sectionPaddingVertical ]:
													nextValues?.top ||
													nextValues?.bottom ||
													defaults.sectionPaddingVertical,
											} ),
									} )
								: el( TextControl, {
										label: __( 'Vertical section padding', 'elodin-bridge' ),
										value: normalizeSpacingAttributeValue(
											props.attributes?.[ ATTRIBUTE_KEYS.sectionPaddingVertical ],
											defaults.sectionPaddingVertical
										),
										onChange: ( value ) =>
											props.setAttributes( {
												[ ATTRIBUTE_KEYS.sectionPaddingVertical ]: value,
											} ),
									} ),
							el( RangeControl, {
								label: __( 'Left width ratio', 'elodin-bridge' ),
								value: sanitizeRatioValue(
									props.attributes?.[ ATTRIBUTE_KEYS.widthRatioLeft ],
									defaults.widthRatioLeft
								),
								onChange: ( value ) =>
									props.setAttributes( {
										[ ATTRIBUTE_KEYS.widthRatioLeft ]: sanitizeRatioValue(
											value,
											defaults.widthRatioLeft
										),
									} ),
								min: 0,
								max: 1,
								step: 0.05,
							} ),
							el( RangeControl, {
								label: __( 'Right width ratio', 'elodin-bridge' ),
								help: __( 'Left and right ratios usually add up to 1.0.', 'elodin-bridge' ),
								value: sanitizeRatioValue(
									props.attributes?.[ ATTRIBUTE_KEYS.widthRatioRight ],
									defaults.widthRatioRight
								),
								onChange: ( value ) =>
									props.setAttributes( {
										[ ATTRIBUTE_KEYS.widthRatioRight ]: sanitizeRatioValue(
											value,
											defaults.widthRatioRight
										),
									} ),
								min: 0,
								max: 1,
								step: 0.05,
							} )
						)
					)
				);
			};
		},
		'withCheckerboardInspectorControls'
	);

	addFilter(
		'editor.BlockEdit',
		'elodin-bridge/checkerboard-inspector-controls',
		withCheckerboardInspectorControls
	);

	const withCheckerboardPreviewStyles = createHigherOrderComponent(
		( BlockListBlock ) => {
			return function WrappedBlockListBlock( props ) {
				if ( managedBlockName !== props.name || ! hasCheckerboardClass( props.attributes?.className ) ) {
					return el( BlockListBlock, props );
				}

				const checkerboardStyleOverrides = getCheckerboardStyleOverrides( props.attributes );
				if ( 0 === Object.keys( checkerboardStyleOverrides ).length ) {
					return el( BlockListBlock, props );
				}

				const wrapperProps = Object.assign( {}, props.wrapperProps || {} );
				wrapperProps.style = Object.assign(
					{},
					wrapperProps.style || {},
					checkerboardStyleOverrides
				);

				return el(
					BlockListBlock,
					Object.assign( {}, props, {
						wrapperProps,
					} )
				);
			};
		},
		'withCheckerboardPreviewStyles'
	);

	addFilter(
		'editor.BlockListBlock',
		'elodin-bridge/checkerboard-preview-styles',
		withCheckerboardPreviewStyles
	);
} )( window.wp );
