( function ( wp ) {
	if (
		! wp ||
		! wp.hooks ||
		! wp.compose ||
		! wp.element ||
		! wp.blockEditor ||
		! wp.components ||
		! wp.data ||
		! wp.i18n
	) {
		return;
	}

	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { createElement: el, Fragment } = wp.element;
	const { BlockControls, store: blockEditorStore } = wp.blockEditor;
	const { ToolbarButton, ToolbarGroup } = wp.components;
	const { dispatch } = wp.data;
	const { __ } = wp.i18n;

	const allowedBlocks = new Set( [ 'core/paragraph', 'core/heading' ] );
	const balancedClass = 'balanced';

	function parseClasses( className ) {
		return ( className || '' ).split( /\s+/ ).filter( Boolean );
	}

	function hasClass( className, targetClass ) {
		return parseClasses( className ).includes( targetClass );
	}

	function toggleClass( className, targetClass ) {
		const classes = parseClasses( className );
		const nextClasses = hasClass( className, targetClass )
			? classes.filter( ( candidate ) => candidate !== targetClass )
			: classes.concat( targetClass );

		return nextClasses.length ? nextClasses.join( ' ' ) : undefined;
	}

	const withElodinBalancedTextToolbar = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if ( ! props.isSelected || ! allowedBlocks.has( props.name ) ) {
					return el( BlockEdit, props );
				}

				const currentClassName = props.attributes?.className || '';
				const isBalanced = hasClass( currentClassName, balancedClass );

				return el(
					Fragment,
					null,
					el( BlockEdit, props ),
					el(
						BlockControls,
						{ group: 'block' },
						el(
							ToolbarGroup,
							null,
							el( ToolbarButton, {
								icon: isBalanced ? 'editor-justify' : 'editor-alignleft',
								label: isBalanced
									? __( 'Disable balanced text', 'elodin-bridge' )
									: __( 'Enable balanced text', 'elodin-bridge' ),
								isPressed: isBalanced,
								showTooltip: true,
								onClick: () => {
									dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
										className: toggleClass( currentClassName, balancedClass ),
									} );
								},
							} )
						)
					)
				);
			};
		},
		'withElodinBalancedTextToolbar'
	);

	addFilter(
		'editor.BlockEdit',
		'elodin-bridge/balanced-text-toolbar',
		withElodinBalancedTextToolbar
	);
} )( window.wp );
