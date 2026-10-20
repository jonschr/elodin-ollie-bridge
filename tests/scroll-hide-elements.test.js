const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );

const source = fs.readFileSync( require.resolve( '../assets/scroll-hide-elements.js' ), 'utf8' );
const hiddenClass = 'is-elodin-bridge-scroll-hidden';

for ( const [ value, priority, initialScroll ] of [
	[ '1px', 'important', 0 ],
	[ '50vh', '', 0 ],
	[ '', '', 0 ],
	[ '1px', 'important', 300 ],
] ) {
	const values = new Map();
	const priorities = new Map();
	const classes = new Set();
	const style = {
		getPropertyValue: ( name ) => values.get( name ) || '',
		getPropertyPriority: ( name ) => priorities.get( name ) || '',
		setProperty( name, nextValue, nextPriority = '' ) {
			values.set( name, nextValue );
			priorities.set( name, nextPriority );
		},
		removeProperty( name ) {
			values.delete( name );
			priorities.delete( name );
		},
	};
	if ( value ) {
		style.setProperty( 'min-height', value, priority );
	}
	style.setProperty( 'color', 'red' );
	const element = {
		style,
		classList: {
			add: ( name ) => classes.add( name ),
			toggle: ( name, active ) => active ? classes.add( name ) : classes.delete( name ),
		},
	};
	const listeners = {};
	const window = {
		scrollY: initialScroll,
		elodinBridgeScrollHideElements: { rules: [ { selectors: '.preheader', threshold: '150px', show_threshold: '25px' } ] },
		getComputedStyle: ( measure ) => ( { height: measure.style.height } ),
		requestAnimationFrame: ( callback ) => callback(),
		addEventListener: ( name, callback ) => { listeners[ name ] = callback; },
	};
	vm.runInNewContext( source, {
		window,
		document: {
			body: { appendChild() {} },
			createElement: () => ( { style: {} } ),
			querySelectorAll: () => [ element ],
		},
	} );
	const scrollTo = ( y ) => {
		window.scrollY = y;
		listeners.scroll();
	};
	const check = ( hidden ) => {
		assert.equal( classes.has( hiddenClass ), hidden );
		assert.equal( style.getPropertyValue( 'min-height' ), hidden ? '0px' : value );
		assert.equal( style.getPropertyPriority( 'min-height' ), hidden ? 'important' : priority );
		assert.equal( style.getPropertyValue( 'color' ), 'red' );
	};
	check( initialScroll > 150 );
	for ( let cycle = 0; cycle < 3; cycle++ ) {
		scrollTo( 0 );
		check( false );
		scrollTo( 100 );
		check( false );
		scrollTo( 300 );
		check( true );
		scrollTo( 400 );
		check( true );
		scrollTo( 100 );
		check( true );
		scrollTo( 0 );
		check( false );
	}
	// Capture the current visible setting on the next hide, rather than a stale startup value.
	style.setProperty( 'min-height', '12px', 'important' );
	scrollTo( 300 );
	scrollTo( 0 );
	assert.equal( style.getPropertyValue( 'min-height' ), '12px' );
	assert.equal( style.getPropertyPriority( 'min-height' ), 'important' );
}

console.log( 'Scroll-hide minimum-height regression checks passed.' );
