const assert = require( 'node:assert/strict' );
const isDirectMp4Url = require( '../assets/editor-direct-cover-video-urls.js' );

assert.equal( isDirectMp4Url( 'https://pub.example.r2.dev/video.mp4' ), true );
assert.equal( isDirectMp4Url( 'https://pub.example.r2.dev/video.MP4?token=123' ), true );
assert.equal( isDirectMp4Url( 'javascript:alert(1).mp4' ), false );
assert.equal( isDirectMp4Url( 'https://example.com/video\" onerror=\"alert(1).mp4' ), false );
assert.equal( isDirectMp4Url( 'https://user:password@example.com/video.mp4' ), false );
assert.equal( isDirectMp4Url( 'https://www.youtube.com/watch?v=123' ), false );
