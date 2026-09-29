<?php

// Run from the plugin directory: wp eval-file tests/update-checker-test.php
$metadata = json_decode( file_get_contents( dirname( __DIR__ ) . '/update.json' ), true, 512, JSON_THROW_ON_ERROR );
$url      = 'https://raw.githubusercontent.com/jonschr/elodin-ollie-bridge/master/update.json';
$found    = array();

foreach ( $GLOBALS['wp_filter']['site_transient_update_plugins']->callbacks ?? array() as $callbacks ) {
	foreach ( $callbacks as $callback ) {
		$handler = $callback['function'];
		if ( is_array( $handler ) && is_object( $handler[0] ) && ( $handler[0]->slug ?? null ) === 'elodin-ollie-bridge' ) {
			$found[ spl_object_id( $handler[0] ) ] = $handler[0];
		}
	}
}

if ( 1 !== count( $found ) ) {
	throw new RuntimeException( 'Ollie Bridge update checker was not registered exactly once.' );
}

$checker = reset( $found );
if ( $url !== $checker->metadataUrl || plugin_basename( ELODIN_BRIDGE_PLUGIN_FILE ) !== $checker->pluginFile ) {
	throw new RuntimeException( 'Ollie Bridge update checker has the wrong source or plugin file.' );
}

add_filter( 'pre_http_request', static function ( $pre, $args, $request_url ) use ( $url ) {
	if ( ! str_starts_with( $request_url, $url ) ) {
		return $pre;
	}
	return array(
		'headers'  => array(),
		'body'     => file_get_contents( dirname( __DIR__ ) . '/update.json' ),
		'response' => array( 'code' => 200, 'message' => 'OK' ),
		'cookies'  => array(),
	);
}, 10, 3 );

$update = $checker->requestUpdate();
if ( ! $update || $metadata['version'] !== $update->version || $metadata['download_url'] !== $update->download_url ) {
	throw new RuntimeException( 'PUC could not read the local release metadata.' );
}

echo "Ollie Bridge update checker and JSON metadata OK\n";
