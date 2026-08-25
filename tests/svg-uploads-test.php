<?php

function add_filter() {}
function elodin_bridge_is_svg_uploads_enabled() { return true; }
function current_user_can() { return true; }
function user_can() { return false; }
function __($message) { return $message; }

require dirname(__DIR__) . '/inc/svg-uploads.php';

$safe = tempnam(sys_get_temp_dir(), 'svg');
$unsafe = tempnam(sys_get_temp_dir(), 'svg');
$external = tempnam(sys_get_temp_dir(), 'svg');
file_put_contents($safe, '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10z"/></svg>');
file_put_contents($unsafe, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
file_put_contents($external, '<svg xmlns="http://www.w3.org/2000/svg"><path fill="url(https://example.com/a.svg)"/></svg>');

assert(isset(elodin_bridge_allow_svg_uploads(array(), null)['svg']));
assert(empty(elodin_bridge_validate_svg_upload(array('name' => 'safe.svg', 'tmp_name' => $safe))['error']));
assert(!empty(elodin_bridge_validate_svg_upload(array('name' => 'unsafe.svg', 'tmp_name' => $unsafe))['error']));
assert(!empty(elodin_bridge_validate_svg_upload(array('name' => 'external.svg', 'tmp_name' => $external))['error']));

unlink($safe);
unlink($unsafe);
unlink($external);
