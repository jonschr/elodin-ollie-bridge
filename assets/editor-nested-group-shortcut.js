( function () {
	if (
		! window.wp ||
		! window.wp.keycodes ||
		! window.wp.blocks ||
		! window.wp.data ||
		! window.wp.i18n
	) {
		return;
	}

	const { select, dispatch } = window.wp.data;
	const { __ } = window.wp.i18n;
	const store = 'core/block-editor';
	const shortcutStore = 'core/keyboard-shortcuts';
	const patternStore = 'core';
	const shortcutName = 'nested-group-shortcut/insert';
	const defaultPatternName = '';
	const boundDocuments = new Set();
	const showTemporaryDebugNotice = false;

	function notifyTemporaryDebug( message, status = 'info' ) {
		if ( ! showTemporaryDebugNotice ) {
			return;
		}

		try {
			const noticesDispatch = dispatch( 'core/notices' );
			if (
				noticesDispatch &&
				'function' === typeof noticesDispatch.createNotice
			) {
				noticesDispatch.createNotice( status, message, {
					type: 'snackbar',
					isDismissible: true,
				} );
				return;
			}
		} catch ( error ) {
			// Fallback below.
		}

		try {
			window.console.info( '[Nested Group Shortcut]', message );
		} catch ( error ) {
			// No-op.
		}
	}

	function getPatternId() {
		const config = window.elodinBridgeNestedGroupShortcut;
		if (
			config &&
			'number' === typeof config.patternId &&
			config.patternId > 0
		) {
			return config.patternId;
		}

		return 0;
	}

	function getPatternName() {
		const config = window.elodinBridgeNestedGroupShortcut;
		if (
			config &&
			'string' === typeof config.patternName &&
			'' !== config.patternName
		) {
			return config.patternName;
		}

		const patternId = getPatternId();
		if ( patternId > 0 ) {
			return `core/block/${ patternId }`;
		}

		return defaultPatternName;
	}

	function getPatternFallbackContent() {
		const config = window.elodinBridgeNestedGroupShortcut;
		if ( config && 'string' === typeof config.patternContent ) {
			return config.patternContent;
		}

		return '';
	}

	function findPatternByName( patterns, name, id ) {
		if ( ! Array.isArray( patterns ) ) {
			return null;
		}

		const matchedPattern = patterns.find(
			( pattern ) =>
				pattern &&
				(
					( name && pattern.name === name ) ||
					( id > 0 && pattern.id === id )
				)
		);

		return matchedPattern || null;
	}

	function getPatternRecord() {
		const patternName = getPatternName();
		const patternId = getPatternId();

		try {
			const coreSelector = select( patternStore );
			if (
				coreSelector &&
				'function' === typeof coreSelector.getBlockPatterns
			) {
				const restPatterns = coreSelector.getBlockPatterns();
				const restPattern = findPatternByName(
					restPatterns,
					patternName,
					patternId
				);
				if ( restPattern ) {
					return restPattern;
				}
			}
		} catch ( error ) {
			// Continue with block editor settings fallback.
		}

		const editorSelector = select( store );
		if (
			! editorSelector ||
			'function' !== typeof editorSelector.getSettings
		) {
			return null;
		}

		const settings = editorSelector.getSettings() || {};
		const settingPatterns = [
			settings.__experimentalAdditionalBlockPatterns,
			settings.__experimentalBlockPatterns,
		];

		for ( let index = 0; index < settingPatterns.length; index++ ) {
			const settingPattern = findPatternByName(
				settingPatterns[ index ],
				patternName,
				patternId
			);

			if ( settingPattern ) {
				return settingPattern;
			}
		}

		return null;
	}

	function getPatternBlocks() {
		const pattern = getPatternRecord();
		let patternContent = '';
		if ( pattern && 'string' === typeof pattern.content ) {
			patternContent = pattern.content;
		}

		if ( '' === patternContent.trim() ) {
			patternContent = getPatternFallbackContent();
		}

		if ( '' === patternContent.trim() ) {
			return [];
		}

		if ( 'function' !== typeof window.wp.blocks.parse ) {
			return [];
		}

		try {
			const blocks = window.wp.blocks.parse( patternContent );
			return Array.isArray( blocks ) ? blocks : [];
		} catch ( error ) {
			return [];
		}
	}

	function getUnlockedBlocks( blocks ) {
		if ( ! Array.isArray( blocks ) ) {
			return [];
		}

		return blocks.map( ( block ) => {
			if ( ! block || 'object' !== typeof block ) {
				return block;
			}

			const attributes = block.attributes
				? { ...block.attributes }
				: {};
			delete attributes.lock;
			delete attributes.templateLock;

			return {
				...block,
				attributes,
				innerBlocks: getUnlockedBlocks( block.innerBlocks ),
			};
		} );
	}

	function runInsert() {
		const selector = select( store );
		const action = dispatch( store );
		if ( ! selector || ! action ) {
			return false;
		}

		const {
			getSelectedBlockCount,
			getSelectedBlockClientIds,
			getSelectedBlockClientId,
			getBlockRootClientId,
			getBlockIndex,
			canInsertBlockType,
		} = selector;
		const { replaceBlocks, insertBlocks, selectBlock } = action;

		const newBlocks = getUnlockedBlocks( getPatternBlocks() );
		if ( newBlocks.length < 1 ) {
			return false;
		}

		const insertedRootBlock = newBlocks[ 0 ];
		const insertedRootName =
			insertedRootBlock && insertedRootBlock.name
				? insertedRootBlock.name
				: 'core/group';

		const selectedCount =
			'function' === typeof getSelectedBlockCount
				? getSelectedBlockCount()
				: 0;
		if ( selectedCount > 0 ) {
			const selectedBlockIds =
				'function' === typeof getSelectedBlockClientIds
					? getSelectedBlockClientIds()
					: [];
			if ( selectedBlockIds.length < 1 ) {
				return false;
			}

			const selectedRootClientId = getBlockRootClientId(
				selectedBlockIds[ 0 ]
			);
			if (
				'function' === typeof canInsertBlockType &&
				! canInsertBlockType(
					insertedRootName,
					selectedRootClientId || ''
				)
			) {
				return false;
			}

			replaceBlocks( selectedBlockIds, newBlocks );
			selectBlock( insertedRootBlock.clientId, selectedRootClientId || '' );
			return true;
		}

		const selectedClientId =
			'function' === typeof getSelectedBlockClientId
				? getSelectedBlockClientId()
				: null;
		const rootClientId = selectedClientId
			? getBlockRootClientId( selectedClientId )
			: '';

		if (
			'function' === typeof canInsertBlockType &&
			! canInsertBlockType( insertedRootName, rootClientId || '' )
		) {
			return false;
		}

		let insertIndex = 0;
		if ( selectedClientId && 'function' === typeof getBlockIndex ) {
			const selectedIndex = getBlockIndex( selectedClientId, rootClientId );
			insertIndex = selectedIndex >= 0 ? selectedIndex + 1 : 0;
		}

		insertBlocks( newBlocks, rootClientId || '', insertIndex );
		selectBlock( insertedRootBlock.clientId, rootClientId || '' );
		return true;
	}

		function registerShortcutMetadata() {
			const config = {
				name: shortcutName,
				description: __(
					'Insert nested group (Cmd+Option+G)',
					'elodin-bridge'
				),
			keywords: [ 'group', 'nested' ],
			category: 'main',
			keyCombination: {
				modifier: 'primaryAlt',
				character: 'g',
			},
		};

		try {
			const keyboardDispatch = dispatch( shortcutStore );
			if (
				keyboardDispatch &&
				'function' === typeof keyboardDispatch.registerShortcut
			) {
				keyboardDispatch.registerShortcut( config );
				return true;
			}
		} catch ( error ) {
			// Continue with fallback below.
		}

		try {
			if (
				window.wp.keyboardShortcuts &&
				'function' ===
					typeof window.wp.keyboardShortcuts.registerShortcut
			) {
				window.wp.keyboardShortcuts.registerShortcut( config );
				return true;
			}
		} catch ( error ) {
			// No-op.
		}

		return false;
	}

	function isShortcutPressed( event ) {
		if (
			! event ||
			event.defaultPrevented ||
			event.repeat ||
			event.isComposing
		) {
			return false;
		}

		if ( ! ( ( event.metaKey || event.ctrlKey ) && event.altKey ) ) {
			return false;
		}

		if ( event.shiftKey || event.altGraphKey ) {
			return false;
		}

		if ( event.code === 'KeyG' ) {
			return true;
		}

		const key = ( event.key || '' ).toLowerCase();
		const keyCode = event.keyCode || 0;
		return (
			key === 'g' ||
			( keyCode > 0 && keyCode === 'g'.charCodeAt( 0 ) )
		);
	}

	function isIgnoredTarget( target ) {
		if ( ! target || ! target.nodeType ) {
			return false;
		}

		const element = target.nodeType === 1 ? target : target.parentElement;
		if ( ! element ) {
			return false;
		}

		return (
			element.matches( 'input,textarea,select,button' ) ||
			element.closest?.( 'input,textarea,select,button' ) !== null
		);
	}

	function handleShortcut( event ) {
		if ( ! isShortcutPressed( event ) ) {
			return;
		}

		if ( isIgnoredTarget( event.target ) ) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		const inserted = runInsert();
		if ( inserted ) {
			notifyTemporaryDebug(
				'Nested Group Shortcut: Cmd+Option+G triggered and inserted.',
				'success'
			);
			return;
		}

		notifyTemporaryDebug(
			'Nested Group Shortcut: key detected, but insertion was blocked.',
			'warning'
		);
	}

	function bindShortcutListenerToDocument( targetDocument ) {
		if (
			! targetDocument ||
			boundDocuments.has( targetDocument ) ||
			! targetDocument.addEventListener
		) {
			return;
		}

		targetDocument.addEventListener( 'keydown', handleShortcut, {
			capture: true,
		} );
		boundDocuments.add( targetDocument );
	}

	function attachToEditors() {
		bindShortcutListenerToDocument( window.document );

		const iframes = window.document.querySelectorAll( 'iframe' );
		iframes.forEach( ( iframe ) => {
			try {
				if ( iframe.contentDocument ) {
					bindShortcutListenerToDocument( iframe.contentDocument );
				}
			} catch ( error ) {
				// Intentionally ignore cross-document access issues.
			}
		} );
	}

	attachToEditors();
	if ( ! registerShortcutMetadata() ) {
		notifyTemporaryDebug(
			'Nested Group Shortcut loaded, but shortcut metadata could not be registered.',
			'warning'
		);
	}

	notifyTemporaryDebug(
		'Nested Group Shortcut loaded. Press Cmd+Option+G in Post/Page or Site Editor.',
		'info'
	);

	if ( window.MutationObserver ) {
		const observer = new window.MutationObserver( () => {
			attachToEditors();
		} );

		observer.observe( window.document.body || window.document.documentElement, {
			childList: true,
			subtree: true,
		} );
	}
} )( );
