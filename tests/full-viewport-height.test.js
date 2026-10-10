const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const getDimensions = require( '../assets/editor-full-viewport-height.js' );

assert.deepEqual( getDimensions( 'core/cover', { minHeight: 100, minHeightUnit: 'vh' } ), { 'min-height': 'vh' } );
assert.deepEqual( getDimensions( 'core/cover', { minHeight: 100 } ), {} );
assert.deepEqual( getDimensions( 'core/cover', { minHeight: 100, minHeightUnit: 'vh', style: { dimensions: { minHeight: '90vh' } } } ), {} );
for ( const unit of [ 'vh', 'dvh', 'svh', 'lvh' ] ) {
	assert.deepEqual( getDimensions( 'core/group', { style: { dimensions: { height: `100${ unit }`, minHeight: `100.0${ unit }` } } } ), { height: unit, 'min-height': unit } );
}
for ( const value of [ '100%', '90vh', '100px', 'calc(100vh - 20px)', 'var(--custom-height)', '100vw' ] ) {
	assert.deepEqual( getDimensions( 'core/group', { style: { dimensions: { minHeight: value } } } ), {} );
}

// Run the shared measurement service with a real-sized, missing, or hidden admin bar.
const source = fs.readFileSync( require.resolve( '../assets/header-aware-positioning.js' ), 'utf8' );
for ( const [ present, selected, visible, expected ] of [ [ true, true, true, '82px' ], [ true, false, true, '82px' ], [ false, false, false, '50px' ], [ true, true, false, '50px' ] ] ) {
	const values = new Map();
	const style = { setProperty: ( key, value ) => values.set( key, value ), removeProperty() {} };
	const makeElement = ( id, height, shown ) => ( { id, style, matches: () => false, getClientRects: () => shown ? [ {} ] : [], getBoundingClientRect: () => ( { height } ) } );
	const header = makeElement( 'header', 50, true );
	const admin = present ? makeElement( 'wpadminbar', 32, visible ) : null;
	const fakeWindow = {
		document: {
			documentElement: { style }, body: { style },
			getElementById: () => admin,
			querySelectorAll: () => selected && admin ? [ header, admin ] : [ header ],
			dispatchEvent() {},
		},
		addEventListener() {},
		elodinBridgeHeaderAwarePositioning: { selectors: '.site-header' },
	};
	vm.runInNewContext( source, { window: fakeWindow, CustomEvent: class {} } );
	assert.equal( values.get( '--elodin-bridge-full-height-offset' ), expected );
}
