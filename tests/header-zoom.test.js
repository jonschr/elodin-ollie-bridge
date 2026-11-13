// CSS sticky offsets must not depend on whether ResizeObserver or a refresh measured them.
const assert = require( 'node:assert/strict' );
const fs = require( 'node:fs' );
const vm = require( 'node:vm' );
const path = require( 'node:path' );
const source = fs.readFileSync( path.join( __dirname, '../assets/header-aware-positioning.js' ), 'utf8' );
const early = fs.readFileSync( path.join( __dirname, '../assets/header-sizing-block-early.js' ), 'utf8' );
const style = () => ( { values: {}, setProperty( k, v ) { this.values[ k ] = v; }, removeProperty( k ) { delete this.values[ k ]; } } );
for ( const scale of [ 0.5, 0.6875, 1, 1.25 ] ) {
 const make = ( id, height, sticky = false ) => ( {
  id, offsetHeight: Math.round( height ), style: style(), classList: { contains: () => sticky },
  matches: () => sticky, getClientRects: () => [ {} ],
  getBoundingClientRect: () => ( { height: height * scale } ),
 } );
 const bar = make( 'wpadminbar', 32 ), header = make( 'header', 98.72 ), nav = make( 'subnav', 61.76, true );
 const elements = [ bar, header, nav ], root = { style: style() }, body = { style: style() };
 const queue = [], listeners = {}; let observed;
 const flush = () => { while ( queue.length ) queue.shift()(); };
 const context = { console, document: { documentElement: root, body,
  currentScript: { dataset: {} }, querySelectorAll: () => elements,
  getElementById: id => elements.find( e => e.id === id ), dispatchEvent: () => {},
 }, CustomEvent: class {}, scrollY: 0,
 requestAnimationFrame: callback => { queue.push( callback ); return queue.length; },
 addEventListener: ( event, callback ) => { listeners[ event ] = callback; },
 ResizeObserver: class { constructor( callback ) { observed = callback; } observe() {} },
 };
 context.window = context;
 vm.runInNewContext( early, context );
 assert.equal( root.style.values[ '--elodin-bridge-header-height' ], '193px' );
 vm.runInNewContext( source, context );
 const check = () => {
  assert.equal( root.style.values[ '--elodin-bridge-header-height' ], '193px' );
  assert.equal( body.style.values[ '--elodin-bridge-header-sizing-height' ], '161px' );
  assert.equal( nav.style.values[ '--elodin-bridge-sticky-below-header-self-aware-offset' ], '131px' );
 };
 check();
 // Fractional, unscaled border-box values agree with offsetHeight rounding.
 observed( elements.map( ( target, i ) => ( { target, borderBoxSize: [ { blockSize: [ 32, 98.72, 61.76 ][ i ] } ] } ) ) ); flush(); check();
 listeners.resize(); flush(); check();
 context.elodinBridgeHeaderOffset.refresh(); flush(); check();
 // Older observers have contentRect only; offsets still require the full border box.
 observed( elements.map( target => ( { target, contentRect: { height: target.offsetHeight - 10 } } ) ) ); flush(); check();
 listeners.load(); flush(); check();
 assert.equal( context.scrollY, 0 );
}
console.log( 'PASS Zoom-independent header/subnav CSS offsets across initial sizing, observer, resize and refresh; no scrolling.' );
