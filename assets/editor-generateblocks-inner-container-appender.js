( function ( wp ) {
	if (
		! wp ||
		! wp.hooks ||
		! wp.components ||
		! wp.element ||
		! wp.data ||
		! wp.blocks ||
		! wp.i18n
	) {
		return;
	}

	const { addFilter } = wp.hooks;
	const { ToolbarButton } = wp.components;
	const { createElement, Fragment } = wp.element;
	const { createBlock } = wp.blocks;
	const { __ } = wp.i18n;
	const STORE = 'core/block-editor';

	function getSelectedClientIds() {
		const select = wp.data.select( STORE );
		if ( ! select || 'function' !== typeof select.getSelectedBlockClientIds ) {
			return [];
		}

		const selected = select.getSelectedBlockClientIds();
		return Array.isArray( selected ) ? selected : [];
	}

	function isSingleSelectedBlock( clientId ) {
		const selected = getSelectedClientIds();
		return 1 === selected.length && selected[ 0 ] === clientId;
	}

	function getBlock( clientId ) {
		const select = wp.data.select( STORE );
		if ( ! select || 'function' !== typeof select.getBlock ) {
			return null;
		}

		return select.getBlock( clientId );
	}

	function hasNoInnerBlocks( clientId ) {
		const block = getBlock( clientId );
		if ( ! block || ! Array.isArray( block.innerBlocks ) ) {
			return false;
		}

		return 0 === block.innerBlocks.length;
	}

	function isDirectChildOfPostContent( clientId ) {
		const select = wp.data.select( STORE );
		if ( ! select || 'function' !== typeof select.getBlockRootClientId ) {
			return false;
		}

		const parentClientId = select.getBlockRootClientId( clientId );
		if ( ! parentClientId ) {
			return false;
		}

		const parentBlock = getBlock( parentClientId );
		return !! parentBlock && 'core/post-content' === parentBlock.name;
	}

	function hasInnerContainerButton( node ) {
		if ( ! node ) {
			return false;
		}

		if ( Array.isArray( node ) ) {
			return node.some( hasInnerContainerButton );
		}

		if ( 'object' !== typeof node ) {
			return false;
		}

		const props = node.props || {};
		if ( 'string' === typeof props.label && /add inner container/i.test( props.label ) ) {
			return true;
		}

		return hasInnerContainerButton( props.children );
	}

	function insertInnerContainer( clientId ) {
		const dispatch = wp.data.dispatch( STORE );
		if ( ! dispatch || 'function' !== typeof dispatch.insertBlocks ) {
			return;
		}

		dispatch.insertBlocks(
			createBlock( 'generateblocks/element', {
				styles: {
					maxWidth: 'var(--gb-container-width)',
					marginLeft: 'auto',
					marginRight: 'auto',
				},
			} ),
			undefined,
			clientId
		);
	}

	addFilter(
		'generateblocks.editor.toolbarAppenders',
		'elodin-bridge/generateblocks-post-content-inner-container',
		function ( buttons, props ) {
			const clientId = props && props.clientId ? props.clientId : '';
			if ( ! clientId || ! props || 'generateblocks/element' !== props.name ) {
				return buttons;
			}

			if ( hasInnerContainerButton( buttons ) ) {
				return buttons;
			}

			if (
				! isSingleSelectedBlock( clientId ) ||
				! hasNoInnerBlocks( clientId ) ||
				! isDirectChildOfPostContent( clientId )
			) {
				return buttons;
			}

			return createElement(
				Fragment,
				null,
				createElement( ToolbarButton, {
					icon: 'plus-alt2',
					label: __( 'Add Inner Container', 'generateblocks' ),
					onClick: function () {
						insertInnerContainer( clientId );
					},
					showTooltip: true,
				} ),
				buttons
			);
		}
	);
} )( window.wp );
