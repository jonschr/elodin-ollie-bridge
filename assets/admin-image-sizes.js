( function () {
	'use strict';

	const categoryButtons = Array.from( document.querySelectorAll( '.elodin-bridge-admin__category-button[data-bridge-category]' ) );
	const categoryPanels = Array.from(
		document.querySelectorAll( '.elodin-bridge-admin__card[data-bridge-category], .elodin-bridge-admin__category-section[data-bridge-category]' )
	);
	if ( categoryButtons.length && categoryPanels.length ) {
		const allowedCategories = new Set(
			categoryButtons
				.map( ( button ) => button.getAttribute( 'data-bridge-category' ) )
				.filter( ( value ) => !! value )
		);

		const setActiveCategory = ( category, updateHash ) => {
			if ( ! allowedCategories.has( category ) ) {
				return;
			}

			categoryButtons.forEach( ( button ) => {
				const isActive = button.getAttribute( 'data-bridge-category' ) === category;
				button.classList.toggle( 'is-active', isActive );
				button.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
			} );

			categoryPanels.forEach( ( panel ) => {
				panel.hidden = panel.getAttribute( 'data-bridge-category' ) !== category;
			} );

			if ( updateHash && window.history && typeof window.history.replaceState === 'function' ) {
				window.history.replaceState( null, '', `#${ category }` );
			}
		};

		const hashCategory = window.location.hash.replace( '#', '' );
		const initialCategory = allowedCategories.has( hashCategory )
			? hashCategory
			: categoryButtons[ 0 ].getAttribute( 'data-bridge-category' );

		setActiveCategory( initialCategory, false );

		categoryButtons.forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const category = button.getAttribute( 'data-bridge-category' );
				if ( category ) {
					setActiveCategory( category, true );
				}
			} );
		} );
	}

	const template = document.getElementById( 'elodin-bridge-image-size-row-template' );
	if ( ! template ) {
		return;
	}

	const builders = document.querySelectorAll( '.elodin-bridge-admin__image-size-builder' );
	if ( ! builders.length ) {
		return;
	}

	builders.forEach( ( builder ) => {
		const tableBody = builder.querySelector( '.elodin-bridge-admin__custom-image-sizes' );
		const addButton = builder.querySelector( '.elodin-bridge-admin__add-image-size' );
		if ( ! tableBody || ! addButton ) {
			return;
		}

		let nextIndex = parseInt( builder.getAttribute( 'data-next-index' ) || '0', 10 );
		if ( Number.isNaN( nextIndex ) || nextIndex < 0 ) {
			nextIndex = 0;
		}

		addButton.addEventListener( 'click', () => {
			const html = template.innerHTML.split( '__INDEX__' ).join( String( nextIndex ) );
			nextIndex += 1;
			tableBody.insertAdjacentHTML( 'beforeend', html );
			builder.setAttribute( 'data-next-index', String( nextIndex ) );
			document.dispatchEvent( new CustomEvent( 'elodinBridgeSettingsChanged' ) );
		} );

		tableBody.addEventListener( 'click', ( event ) => {
			const target = event.target;
			if ( ! target || ! target.classList || ! target.classList.contains( 'elodin-bridge-admin__remove-image-size' ) ) {
				return;
			}

			const row = target.closest( '.elodin-bridge-admin__image-size-row' );
			if ( row ) {
				row.remove();
				document.dispatchEvent( new CustomEvent( 'elodinBridgeSettingsChanged' ) );
			}
		} );
	} );
} )();
