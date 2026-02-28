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
