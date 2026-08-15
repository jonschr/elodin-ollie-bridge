const assert = require( 'node:assert/strict' );
const protectVideo = require( '../assets/video-download-deterrents.js' );

let control;
let eventName;
let listener;
const video = {
	setAttribute: ( name, value ) => {
		assert.equal( name, 'controlslist' );
		control = value;
	},
	addEventListener: ( name, callback ) => {
		eventName = name;
		listener = callback;
	},
};

protectVideo( video );
assert.equal( control, 'nodownload' );
assert.equal( eventName, 'contextmenu' );

let prevented = false;
listener( { preventDefault: () => prevented = true } );
assert.equal( prevented, true );
