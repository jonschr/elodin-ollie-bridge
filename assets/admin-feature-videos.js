( function () {
	'use strict';

	const modal = document.querySelector( '[data-elodin-video-modal]' );
	if ( ! modal ) {
		return;
	}

	const iframe = modal.querySelector( '[data-elodin-video-iframe]' );
	const frameWrap = modal.querySelector( '[data-elodin-video-frame-wrap]' );
	const aspectFrame = modal.querySelector( '[data-elodin-video-aspect]' );
	const titleElement = modal.querySelector( '[data-elodin-video-modal-title]' );
	const closeElements = modal.querySelectorAll( '[data-elodin-video-close]' );
	const openElements = document.querySelectorAll( '[data-elodin-video-open]' );
	const closeButton = modal.querySelector( '.elodin-bridge-admin__video-modal-close' );

	if ( ! iframe || ! frameWrap || ! aspectFrame || 0 === openElements.length ) {
		return;
	}

	const defaultTitle = titleElement ? titleElement.textContent.trim() : 'Feature walkthrough';
	const defaultAspectRatio = 16 / 9;
	let lastTrigger = null;
	let activeAspectRatio = defaultAspectRatio;
	let revealTimeout = null;

	const parseAspectRatio = ( rawAspectRatio ) => {
		const normalized = String( rawAspectRatio || '' ).trim();
		if ( '' === normalized ) {
			return defaultAspectRatio;
		}

		if ( /^[0-9]+(?:\.[0-9]+)?$/.test( normalized ) ) {
			const numericRatio = Number( normalized );
			return numericRatio > 0 ? numericRatio : defaultAspectRatio;
		}

		const match = normalized.match( /^([0-9]+(?:\.[0-9]+)?)\s*[:/]\s*([0-9]+(?:\.[0-9]+)?)$/ );
		if ( ! match ) {
			return defaultAspectRatio;
		}

		const width = Number( match[ 1 ] );
		const height = Number( match[ 2 ] );
		if ( width <= 0 || height <= 0 ) {
			return defaultAspectRatio;
		}

		return width / height;
	};

	const resizeAspectFrame = () => {
		if ( modal.hidden ) {
			return;
		}

		const bounds = frameWrap.getBoundingClientRect();
		const maxWidth = bounds.width;
		const maxHeight = bounds.height;
		if ( maxWidth <= 0 || maxHeight <= 0 ) {
			return;
		}

		let fittedWidth = maxWidth;
		let fittedHeight = fittedWidth / activeAspectRatio;

		if ( fittedHeight > maxHeight ) {
			fittedHeight = maxHeight;
			fittedWidth = fittedHeight * activeAspectRatio;
		}

		aspectFrame.style.width = `${Math.round( fittedWidth )}px`;
		aspectFrame.style.height = `${Math.round( fittedHeight )}px`;
	};

	const setLoadingState = ( isLoading ) => {
		modal.classList.toggle( 'is-loading', isLoading );
	};

	const buildPlayableUrl = ( rawUrl ) => {
		const normalizedUrl = String( rawUrl || '' ).trim();
		if ( '' === normalizedUrl ) {
			return '';
		}

		try {
			const url = new URL( normalizedUrl, window.location.origin );

			const isLoomHost = /(^|\.)loom\.com$/i.test( url.hostname );
			if ( isLoomHost ) {
				const preferredParams = {
					autoplay: '1',
					hide_owner: 'true',
					hide_share: 'true',
					hide_title: 'true',
					hideEmbedTopBar: 'true',
					minimal_player: 'true',
				};

				Object.entries( preferredParams ).forEach( ( [ key, value ] ) => {
					if ( ! url.searchParams.has( key ) ) {
						url.searchParams.set( key, value );
					}
				} );
			} else if ( ! url.searchParams.has( 'autoplay' ) ) {
				url.searchParams.set( 'autoplay', '1' );
			}

			return url.toString();
		} catch ( error ) {
			return normalizedUrl;
		}
	};

	const closeModal = () => {
		if ( modal.hidden ) {
			return;
		}

		if ( revealTimeout ) {
			window.clearTimeout( revealTimeout );
			revealTimeout = null;
		}

		modal.hidden = true;
		setLoadingState( false );
		iframe.setAttribute( 'src', '' );
		aspectFrame.style.width = '';
		aspectFrame.style.height = '';
		document.body.classList.remove( 'elodin-bridge-admin--video-open' );

		if ( lastTrigger && 'function' === typeof lastTrigger.focus ) {
			lastTrigger.focus();
		}
		lastTrigger = null;
	};

	const openModal = ( trigger ) => {
		const rawUrl = trigger.getAttribute( 'data-elodin-video-url' ) || '';
		const embedUrl = buildPlayableUrl( rawUrl );
		if ( '' === embedUrl ) {
			return;
		}

		lastTrigger = trigger;
		activeAspectRatio = parseAspectRatio( trigger.getAttribute( 'data-elodin-video-aspect-ratio' ) || '' );
		const videoTitle = trigger.getAttribute( 'data-elodin-video-title' ) || defaultTitle;

		if ( titleElement ) {
			titleElement.textContent = videoTitle;
		}

		setLoadingState( true );
		modal.hidden = false;
		resizeAspectFrame();

		iframe.setAttribute( 'title', videoTitle );
		iframe.setAttribute( 'src', embedUrl );
		document.body.classList.add( 'elodin-bridge-admin--video-open' );

		if ( closeButton && 'function' === typeof closeButton.focus ) {
			closeButton.focus();
		}
	};

	iframe.addEventListener( 'load', () => {
		if ( modal.hidden ) {
			return;
		}

		if ( revealTimeout ) {
			window.clearTimeout( revealTimeout );
		}

		revealTimeout = window.setTimeout( () => {
			setLoadingState( false );
		}, 400 );
	} );

	window.addEventListener( 'resize', resizeAspectFrame );

	openElements.forEach( ( trigger ) => {
		trigger.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			openModal( trigger );
		} );
	} );

	closeElements.forEach( ( element ) => {
		element.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			closeModal();
		} );
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' !== event.key || modal.hidden ) {
			return;
		}

		event.preventDefault();
		closeModal();
	} );
} )();
