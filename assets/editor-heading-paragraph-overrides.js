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
	const { ToolbarDropdownMenu, ToolbarGroup } = wp.components;
	const { dispatch } = wp.data;
	const { __, sprintf } = wp.i18n;

	const toolbarConfig = window.elodinBridgeHeadingParagraphOverrides || {};
	const allowedBlocks = new Set( [ 'core/paragraph', 'core/heading' ] );
	const defaultControls = [
		{ className: 'p', label: __( 'Paragraph style', 'elodin-bridge' ) },
		{ className: 'h1', label: __( 'H1 style', 'elodin-bridge' ) },
		{ className: 'h2', label: __( 'H2 style', 'elodin-bridge' ) },
		{ className: 'h3', label: __( 'H3 style', 'elodin-bridge' ) },
		{ className: 'h4', label: __( 'H4 style', 'elodin-bridge' ) },
		{ className: 'h5', label: __( 'H5 style', 'elodin-bridge' ) },
		{ className: 'h6', label: __( 'H6 style', 'elodin-bridge' ) },
	];

	const configuredControls = Array.isArray( toolbarConfig.typeOverrideControls )
		? toolbarConfig.typeOverrideControls
				.map( ( control ) => {
					if ( ! control || 'object' !== typeof control ) {
						return null;
					}

					const className = String( control.className || '' ).trim();
					const label = String( control.label || '' ).trim();
					if ( ! className || ! label || ! /^[a-z0-9-]+$/i.test( className ) ) {
						return null;
					}

					return {
						className,
						label,
					};
				} )
				.filter( Boolean )
		: [];

	const controls = configuredControls.length > 0 ? configuredControls : defaultControls;
	if ( controls.length < 1 ) {
		return;
	}

	const managedClasses = controls.map( ( control ) => control.className );
	const managedClassSet = new Set( managedClasses );

	function parseClasses( className ) {
		return ( className || '' ).split( /\s+/ ).filter( Boolean );
	}

	function getNextClassName( currentClassName, targetClass ) {
		const currentClasses = parseClasses( currentClassName );
		const hasTargetClass = currentClasses.includes( targetClass );
		const preservedClasses = currentClasses.filter( ( className ) => ! managedClassSet.has( className ) );
		const nextClasses = hasTargetClass ? preservedClasses : preservedClasses.concat( targetClass );

		return nextClasses.length > 0 ? nextClasses.join( ' ' ) : undefined;
	}

	function getActiveManagedClass( className ) {
		const classes = parseClasses( className );
		return managedClasses.find( ( candidate ) => classes.includes( candidate ) ) || '';
	}

	function getTypeIcon( isActive ) {
		return el(
			'span',
			{
				className: 'elodin-bridge-type-icon' + ( isActive ? ' is-active' : '' ),
				'aria-hidden': 'true',
			},
			el( 'strong', null, 'A' ),
			el( 'span', null, 'A' )
		);
	}

	function buildControls( props ) {
		const currentClassName = props.attributes?.className || '';
		const activeManagedClass = getActiveManagedClass( currentClassName );

		return controls.map( ( control ) => ( {
			title: control.label,
			isActive: control.className === activeManagedClass,
			onClick: () => {
				const nextClassName = getNextClassName( currentClassName, control.className );
				dispatch( blockEditorStore ).updateBlockAttributes( props.clientId, {
					className: nextClassName,
				} );
			},
		} ) );
	}

	const withElodinHeadingParagraphToolbar = createHigherOrderComponent(
		( BlockEdit ) => {
			return function WrappedBlockEdit( props ) {
				if ( ! props.isSelected || ! allowedBlocks.has( props.name ) ) {
					return el( BlockEdit, props );
				}

				const currentClassName = props.attributes?.className || '';
				const activeManagedClass = getActiveManagedClass( currentClassName );
				const activeManagedControl = controls.find( ( control ) => control.className === activeManagedClass ) || null;
				const activeManagedClassLabel = activeManagedClass
					? activeManagedControl && activeManagedControl.label
						? activeManagedControl.label
						: activeManagedClass.toUpperCase()
					: '';

				const dropdownLabel = activeManagedClass
					? sprintf( __( 'Typography override: %s', 'elodin-bridge' ), activeManagedClassLabel )
					: __( 'Typography override', 'elodin-bridge' );

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
							el( ToolbarDropdownMenu, {
								icon: getTypeIcon( !! activeManagedClass ),
								label: dropdownLabel,
								text: null,
								controls: buildControls( props ),
								popoverProps: {
									className: 'elodin-bridge-type-menu',
								},
								toggleProps: {
									isPressed: !! activeManagedClass,
									showTooltip: true,
									className: 'elodin-bridge-toolbar-toggle elodin-bridge-toolbar-toggle--type',
								},
							} )
						)
					)
				);
			};
		},
		'withElodinHeadingParagraphToolbar'
	);

	addFilter(
		'editor.BlockEdit',
		'elodin-bridge/heading-paragraph-toolbar',
		withElodinHeadingParagraphToolbar
	);
} )( window.wp );
