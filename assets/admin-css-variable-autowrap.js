( function () {
	'use strict';

	const config = window.elodinBridgeCssVariableAutowrap || {};
	const rawTokens = Array.isArray( config.tokens ) ? config.tokens : [];
	const rawAliases = config.aliases && 'object' === typeof config.aliases ? config.aliases : {};
	if ( ! config.enabled || 0 === rawTokens.length ) {
		return;
	}

	const allowedTokens = new Set();
	rawTokens.forEach( ( token ) => {
		const normalized = String( token || '' ).trim().toLowerCase();
		if ( /^--[a-z0-9-]+$/.test( normalized ) ) {
			allowedTokens.add( normalized );
		}
	} );
	if ( 0 === allowedTokens.size ) {
		return;
	}

	const aliasMap = new Map();
	Object.keys( rawAliases ).forEach( ( alias ) => {
		const normalizedAlias = String( alias || '' ).trim().toLowerCase();
		const normalizedTarget = String( rawAliases[ alias ] || '' ).trim().toLowerCase();
		if ( ! /^--[a-z0-9-]+$/.test( normalizedAlias ) || ! allowedTokens.has( normalizedTarget ) ) {
			return;
		}

		aliasMap.set( normalizedAlias, normalizedTarget );
	} );

	const tokenPattern = /--[a-z0-9-]+/gi;
	const textInputTypes = new Set( [ 'text', 'search', 'url', 'email', 'tel' ] );
	const codeMirrorUpdateGuards = new WeakSet();
	const boundCodeMirrorEditors = new WeakSet();
	const inputValueSetter =
		window.HTMLInputElement &&
		window.Object.getOwnPropertyDescriptor( window.HTMLInputElement.prototype, 'value' ) &&
		window.Object.getOwnPropertyDescriptor( window.HTMLInputElement.prototype, 'value' ).set;
	const textareaValueSetter =
		window.HTMLTextAreaElement &&
		window.Object.getOwnPropertyDescriptor( window.HTMLTextAreaElement.prototype, 'value' ) &&
		window.Object.getOwnPropertyDescriptor( window.HTMLTextAreaElement.prototype, 'value' ).set;

	const isBoundaryChar = ( char ) => {
		return ! char || ! /[a-z0-9_-]/i.test( char );
	};

	const isStandaloneToken = ( value, startIndex, endIndex ) => {
		const before = startIndex > 0 ? value.charAt( startIndex - 1 ) : '';
		const after = endIndex < value.length ? value.charAt( endIndex ) : '';
		return isBoundaryChar( before ) && isBoundaryChar( after );
	};

	const getFunctionNameBeforeParen = ( value, parenIndex ) => {
		let pointer = parenIndex - 1;
		while ( pointer >= 0 && /\s/.test( value.charAt( pointer ) ) ) {
			pointer -= 1;
		}

		const end = pointer;
		while ( pointer >= 0 && /[a-zA-Z-]/.test( value.charAt( pointer ) ) ) {
			pointer -= 1;
		}

		if ( end < pointer + 1 ) {
			return '';
		}

		return value.slice( pointer + 1, end + 1 ).toLowerCase();
	};

	const isInsideVarFunction = ( value, index ) => {
		const stack = [];

		for ( let i = 0; i < index; i += 1 ) {
			const char = value.charAt( i );
			if ( '(' === char ) {
				stack.push( getFunctionNameBeforeParen( value, i ) );
				continue;
			}

			if ( ')' === char && stack.length > 0 ) {
				stack.pop();
			}
		}

		return stack.includes( 'var' );
	};

	const transformValue = ( value, cursorPosition ) => {
		if ( 'string' !== typeof value || -1 === value.indexOf( '--' ) ) {
			return {
				changed: false,
				value: value,
				cursor: cursorPosition,
			};
		}

		tokenPattern.lastIndex = 0;
		let match = null;
		let hasChanges = false;
		let nextValue = '';
		let lastIndex = 0;
		let cursorDelta = 0;

		while ( ( match = tokenPattern.exec( value ) ) ) {
			const token = match[ 0 ];
			const normalized = token.toLowerCase();
			const startIndex = match.index;
			const endIndex = startIndex + token.length;
			const targetVariable = allowedTokens.has( normalized ) ? normalized : aliasMap.get( normalized );
			let replacement = token;

			if (
				'string' === typeof targetVariable &&
				isStandaloneToken( value, startIndex, endIndex ) &&
				! isInsideVarFunction( value, startIndex )
			) {
				replacement = 'var(' + targetVariable + ')';
			}

			nextValue += value.slice( lastIndex, startIndex ) + replacement;
			if ( replacement !== token ) {
				hasChanges = true;
				if ( null !== cursorPosition && startIndex < cursorPosition ) {
					cursorDelta += replacement.length - token.length;
				}
			}

			lastIndex = endIndex;
		}

		if ( ! hasChanges ) {
			return {
				changed: false,
				value: value,
				cursor: cursorPosition,
			};
		}

		nextValue += value.slice( lastIndex );
		const nextCursor = null === cursorPosition ? null : Math.max( 0, cursorPosition + cursorDelta );

		return {
			changed: true,
			value: nextValue,
			cursor: nextCursor,
		};
	};

	const getCodeMirrorEditorFromTarget = ( target ) => {
		if ( ! target || ! ( target instanceof HTMLElement ) ) {
			return null;
		}

		const wrapper = target.closest( '.CodeMirror' );
		if ( ! wrapper || ! wrapper.CodeMirror ) {
			return null;
		}

		return wrapper.CodeMirror;
	};

	const shouldHandleCodeMirrorEditor = ( editor ) => {
		if ( ! editor || 'function' !== typeof editor.getTextArea ) {
			return false;
		}

		const sourceField = editor.getTextArea();
		if (
			sourceField instanceof HTMLTextAreaElement &&
			sourceField.classList.contains( 'ollie-css-textarea' )
		) {
			return true;
		}

		if ( 'function' !== typeof editor.getWrapperElement ) {
			return false;
		}

		const wrapper = editor.getWrapperElement();
		return !! ( wrapper && wrapper.closest( '.ollie-class-manager-editor-content' ) );
	};

	const getCodeMirrorCursorIndex = ( editor ) => {
		if (
			! editor ||
			'function' !== typeof editor.getCursor ||
			'function' !== typeof editor.indexFromPos
		) {
			return null;
		}

		try {
			return editor.indexFromPos( editor.getCursor() );
		} catch ( error ) {
			return null;
		}
	};

	const setCodeMirrorCursorFromIndex = ( editor, index ) => {
		if (
			! editor ||
			'number' !== typeof index ||
			index < 0 ||
			'function' !== typeof editor.posFromIndex ||
			'function' !== typeof editor.setCursor
		) {
			return;
		}

		try {
			editor.setCursor( editor.posFromIndex( index ) );
		} catch ( error ) {
			// Cursor positioning should not block value transformation.
		}
	};

	const transformCodeMirrorValue = ( editor ) => {
		if ( ! shouldHandleCodeMirrorEditor( editor ) || codeMirrorUpdateGuards.has( editor ) ) {
			return;
		}

		if ( 'function' !== typeof editor.getValue || 'function' !== typeof editor.setValue ) {
			return;
		}

		const currentValue = editor.getValue();
		const cursorIndex = getCodeMirrorCursorIndex( editor );
		const result = transformValue( currentValue, cursorIndex );
		if ( ! result.changed || result.value === currentValue ) {
			return;
		}

		codeMirrorUpdateGuards.add( editor );
		try {
			if ( 'function' === typeof editor.operation ) {
				editor.operation( () => {
					editor.setValue( result.value );
					setCodeMirrorCursorFromIndex( editor, result.cursor );
				} );
			} else {
				editor.setValue( result.value );
				setCodeMirrorCursorFromIndex( editor, result.cursor );
			}

			if ( 'function' === typeof editor.save ) {
				editor.save();
			}
		} finally {
			codeMirrorUpdateGuards.delete( editor );
		}
	};

	const bindCodeMirrorEditor = ( editor ) => {
		if (
			! shouldHandleCodeMirrorEditor( editor ) ||
			boundCodeMirrorEditors.has( editor ) ||
			'function' !== typeof editor.on
		) {
			return;
		}

		boundCodeMirrorEditors.add( editor );
		editor.on( 'change', ( instance, change ) => {
			if ( change && 'setValue' === change.origin ) {
				return;
			}

			transformCodeMirrorValue( instance );
		} );
	};

	const bindCodeMirrorEditorsInNode = ( node ) => {
		if ( ! node || ! ( node instanceof HTMLElement ) ) {
			return;
		}

		if ( node.classList.contains( 'CodeMirror' ) && node.CodeMirror ) {
			bindCodeMirrorEditor( node.CodeMirror );
		}

		if ( 'function' !== typeof node.querySelectorAll ) {
			return;
		}

		node.querySelectorAll( '.CodeMirror' ).forEach( ( wrapper ) => {
			if ( wrapper && wrapper.CodeMirror ) {
				bindCodeMirrorEditor( wrapper.CodeMirror );
			}
		} );
	};

	const initializeCodeMirrorBindings = () => {
		if ( ! document || ! document.documentElement ) {
			return;
		}

		bindCodeMirrorEditorsInNode( document.documentElement );

		const observer = new MutationObserver( ( mutations ) => {
			mutations.forEach( ( mutation ) => {
				if ( ! mutation.addedNodes || 0 === mutation.addedNodes.length ) {
					return;
				}

				mutation.addedNodes.forEach( ( addedNode ) => {
					if ( addedNode instanceof HTMLElement ) {
						bindCodeMirrorEditorsInNode( addedNode );
					}
				} );
			} );
		} );

		observer.observe( document.documentElement, {
			childList: true,
			subtree: true,
		} );
	};

	initializeCodeMirrorBindings();

	const shouldHandleField = ( target ) => {
		if ( ! target || ! ( target instanceof HTMLElement ) ) {
			return false;
		}

		if ( target.closest( '.CodeMirror, .cm-editor, .components-code-editor, .block-editor-code-editor, .ace_editor' ) ) {
			return false;
		}

		if ( target.hasAttribute( 'data-elodin-disable-css-variable-autowrap' ) ) {
			return false;
		}

		if ( target instanceof HTMLTextAreaElement ) {
			return ! target.disabled && ! target.readOnly;
		}

		if ( target instanceof HTMLInputElement ) {
			if ( target.disabled || target.readOnly ) {
				return false;
			}

			const fieldType = ( target.type || 'text' ).toLowerCase();
			return textInputTypes.has( fieldType );
		}

		return false;
	};

	const setFieldValue = ( target, value ) => {
		if ( target instanceof HTMLInputElement && 'function' === typeof inputValueSetter ) {
			inputValueSetter.call( target, value );
			return;
		}

		if ( target instanceof HTMLTextAreaElement && 'function' === typeof textareaValueSetter ) {
			textareaValueSetter.call( target, value );
			return;
		}

		target.value = value;
	};

	document.addEventListener(
		'input',
		( event ) => {
			if ( event.isComposing ) {
				return;
			}

			const target = event.target;
			const codeMirrorEditor = getCodeMirrorEditorFromTarget( target );
			if ( codeMirrorEditor ) {
				transformCodeMirrorValue( codeMirrorEditor );
				return;
			}

			if ( ! shouldHandleField( target ) ) {
				return;
			}

			const cursorPosition = 'number' === typeof target.selectionStart ? target.selectionStart : null;
			const result = transformValue( target.value, cursorPosition );
			if ( ! result.changed || result.value === target.value ) {
				return;
			}

			setFieldValue( target, result.value );
			if (
				document.activeElement === target &&
				null !== result.cursor &&
				'function' === typeof target.setSelectionRange
			) {
				target.setSelectionRange( result.cursor, result.cursor );
			}

			target.dispatchEvent(
				new Event( 'input', {
					bubbles: true,
				} )
			);
		},
		true
	);
} )();
