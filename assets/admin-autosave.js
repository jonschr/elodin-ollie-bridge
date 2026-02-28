( function () {
	'use strict';

	const form = document.querySelector( '.elodin-bridge-admin__form' );
	if ( ! form ) {
		return;
	}

	const statusElement = form.querySelector( '[data-bridge-save-status]' );
	if ( ! statusElement ) {
		return;
	}
	const debugWrap = form.querySelector( '[data-bridge-save-debug-wrap]' );
	const debugElement = form.querySelector( '[data-bridge-save-debug]' );

	const statusMessages = {
		idle: 'Changes save automatically.',
		saving: 'Saving...',
		saved: 'Saved.',
		error: 'Could not save. Change a setting to retry.',
	};

	let saveTimer = 0;
	let resetStatusTimer = 0;
	let isSaving = false;
	let shouldSaveAgain = false;
	let lastSerializedState = '';
	const actionAttribute = form.getAttribute( 'action' ) || '';
	const saveEndpoint = actionAttribute ? new URL( actionAttribute, window.location.href ).toString() : window.location.href;

	const setStatus = ( state ) => {
		statusElement.setAttribute( 'data-state', state );
		statusElement.textContent = statusMessages[ state ] || statusMessages.idle;
	};

	const clearDebug = () => {
		if ( debugWrap ) {
			debugWrap.hidden = true;
		}
		if ( debugElement ) {
			debugElement.textContent = '';
		}
	};

	const setDebug = ( message ) => {
		if ( ! debugWrap || ! debugElement ) {
			return;
		}

		debugElement.textContent = message;
		debugWrap.hidden = false;
	};

	const serializeForm = () => {
		const formData = new FormData( form );
		const params = new URLSearchParams();

		for ( const [ key, value ] of formData.entries() ) {
			if ( typeof value === 'string' ) {
				params.append( key, value );
			}
		}

		return {
			params,
			serialized: params.toString(),
		};
	};

	const queueSave = ( delay = 500 ) => {
		window.clearTimeout( saveTimer );
		saveTimer = window.setTimeout( () => {
			void saveSettings();
		}, delay );
	};

	const buildError = ( message, bridgeDebug ) => {
		const error = new Error( message );
		error.bridgeDebug = bridgeDebug;
		return error;
	};

	const saveSettings = async () => {
		if ( isSaving ) {
			shouldSaveAgain = true;
			return;
		}

		const payload = serializeForm();
		if ( payload.serialized === lastSerializedState ) {
			return;
		}

		isSaving = true;
		shouldSaveAgain = false;
		window.clearTimeout( resetStatusTimer );
		setStatus( 'saving' );
		clearDebug();

		try {
			const response = await fetch( saveEndpoint, {
				method: 'POST',
				credentials: 'same-origin',
				redirect: 'follow',
				body: payload.params.toString(),
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
			} );

			const responseText = await response.text();
			const responseSnippet = responseText
				.replace( /<script[\s\S]*?<\/script>/gi, ' ' )
				.replace( /<style[\s\S]*?<\/style>/gi, ' ' )
				.replace( /<[^>]+>/g, ' ' )
				.replace( /\s+/g, ' ' )
				.trim()
				.slice( 0, 700 );
			const bridgeDebug = {
				status: response.status,
				redirected: response.redirected,
				url: response.url,
				snippet: responseSnippet,
			};

			if ( ! response.ok ) {
				throw buildError( 'Save request failed.', bridgeDebug );
			}

			if (
				/not allowed to manage options/i.test( responseText ) ||
				/are you sure you want to do this\?/i.test( responseText ) ||
				/sorry, you are not allowed/i.test( responseText )
			) {
				throw buildError( 'Save request rejected.', bridgeDebug );
			}

			lastSerializedState = payload.serialized;
			setStatus( 'saved' );
			clearDebug();
			resetStatusTimer = window.setTimeout( () => {
				if ( ! isSaving && ! shouldSaveAgain ) {
					setStatus( 'idle' );
				}
			}, 1500 );
		} catch ( error ) {
			const errorMessage = error instanceof Error ? error.message : 'Unknown autosave error.';
			const diagnostics = [
				`time: ${ new Date().toLocaleString() }`,
				`message: ${ errorMessage }`,
				`action: ${ saveEndpoint }`,
			];

			if ( error && error.bridgeDebug ) {
				const bridgeDebug = error.bridgeDebug;
				diagnostics.push( `status: ${ String( bridgeDebug.status || '' ) }` );
				diagnostics.push( `redirected: ${ String( !! bridgeDebug.redirected ) }` );
				if ( bridgeDebug.url ) {
					diagnostics.push( `response url: ${ bridgeDebug.url }` );
				}
				if ( bridgeDebug.snippet ) {
					diagnostics.push( '', 'response snippet:', bridgeDebug.snippet );
				}
			}

			setDebug( diagnostics.join( '\n' ) );
			console.error( '[Ollie Bridge] Autosave failed', diagnostics.join( '\n' ) );
			setStatus( 'error' );
		} finally {
			isSaving = false;
			if ( shouldSaveAgain ) {
				queueSave( 250 );
			}
		}
	};

	const isSaveableInput = ( target ) => {
		if ( ! target || ! ( target instanceof HTMLElement ) ) {
			return false;
		}

		if ( ! target.closest( '.elodin-bridge-admin__form' ) ) {
			return false;
		}

		const fieldName = target.getAttribute( 'name' );
		if ( ! fieldName ) {
			return false;
		}

		if ( target.matches( 'button, [type="button"]' ) ) {
			return false;
		}

		return true;
	};

	form.addEventListener( 'change', ( event ) => {
		if ( ! isSaveableInput( event.target ) ) {
			return;
		}

		queueSave( 250 );
	} );

	form.addEventListener( 'input', ( event ) => {
		const target = event.target;
		if ( ! ( target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement ) ) {
			return;
		}

		if ( ! isSaveableInput( target ) ) {
			return;
		}

		if ( 'checkbox' === target.type || 'radio' === target.type || 'hidden' === target.type ) {
			return;
		}

		queueSave( 700 );
	} );

	document.addEventListener( 'elodinBridgeSettingsChanged', () => {
		queueSave( 250 );
	} );

	lastSerializedState = serializeForm().serialized;
	setStatus( 'idle' );
	clearDebug();
} )();
