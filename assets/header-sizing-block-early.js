( function () {
	'use strict';

	const selectors = document.currentScript.dataset.selectors || '*.site-header, #wpadminbar';
	let elements;

	try {
		elements = document.querySelectorAll( selectors );
	} catch ( error ) {
		elements = document.querySelectorAll( '*.site-header, #wpadminbar' );
	}

	const measure = function ( includeAdminBar ) {
		return Array.from( elements ).reduce( function ( total, element ) {
			return total + ( ( includeAdminBar || 'wpadminbar' !== element.id ) && element.getClientRects().length
				? Math.ceil( element.getBoundingClientRect().height )
				: 0 );
		}, 0 );
	};

	const height = `${ measure( true ) }px`;
	const sizingHeight = `${ measure( false ) }px`;

	document.documentElement.style.setProperty( '--elodin-bridge-header-height', height );
	document.documentElement.style.setProperty( '--elodin-bridge-header-sizing-height', sizingHeight );
	document.body.style.setProperty( '--elodin-bridge-header-height', height );
	document.body.style.setProperty( '--elodin-bridge-header-sizing-height', sizingHeight );
}() );
