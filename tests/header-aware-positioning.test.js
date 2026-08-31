const assert = require( 'node:assert/strict' );
const getSelfAwareOffset = require( '../assets/header-aware-positioning.js' );

assert.equal( getSelfAwareOffset( 179, 64, true ), '115px' );
assert.equal(
	getSelfAwareOffset( 179, 64, false ),
	'calc(115px + var(--elodin-bridge-sticky-below-header-bonus-offset,var(--wp--preset--spacing--large,3rem)))'
);
assert.equal( getSelfAwareOffset( 50, 64, true ), '0px' );
