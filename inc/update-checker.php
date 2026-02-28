<?php

/**
 * Store an update-checker warning message for admin notice output.
 *
 * @param string $message Notice text.
 */
function elodin_bridge_set_update_checker_notice( $message ) {
	$message = trim( (string) $message );
	if ( '' === $message ) {
		return;
	}

	$GLOBALS['elodin_bridge_update_checker_notice'] = $message;
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( '[Ollie Bridge] ' . $message );
	}
}

/**
 * Render update-checker warning notices for administrators.
 */
function elodin_bridge_render_update_checker_notice() {
	if ( ! is_admin() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$message = isset( $GLOBALS['elodin_bridge_update_checker_notice'] ) ? (string) $GLOBALS['elodin_bridge_update_checker_notice'] : '';
	if ( '' === $message ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html( $message )
	);
}
add_action( 'admin_notices', 'elodin_bridge_render_update_checker_notice' );

/**
 * Normalize a GitHub repository URL used by the update checker.
 *
 * @param mixed $repository Raw repository value.
 * @return string
 */
function elodin_bridge_normalize_update_checker_repository( $repository ) {
	$repository = trim( (string) $repository );
	if ( '' === $repository ) {
		return '';
	}

	$parts = wp_parse_url( $repository );
	if ( ! is_array( $parts ) ) {
		return '';
	}

	$scheme = strtolower( (string) ( $parts['scheme'] ?? '' ) );
	$host = strtolower( (string) ( $parts['host'] ?? '' ) );
	$path = trim( (string) ( $parts['path'] ?? '' ), '/' );
	if ( 'https' !== $scheme || '' === $host || '' === $path ) {
		return '';
	}

	if ( ! in_array( $host, array( 'github.com', 'www.github.com' ), true ) ) {
		return '';
	}

	$path_segments = explode( '/', $path );
	if ( 2 !== count( $path_segments ) ) {
		return '';
	}

	$owner = $path_segments[0];
	$repo = $path_segments[1];
	if ( ! preg_match( '/^[A-Za-z0-9_.-]+$/', $owner ) || ! preg_match( '/^[A-Za-z0-9_.-]+$/', $repo ) ) {
		return '';
	}

	return 'https://github.com/' . strtolower( $owner ) . '/' . strtolower( $repo );
}

/**
 * Return allowlisted update repositories.
 *
 * @return array<int,string>
 */
function elodin_bridge_get_allowed_update_checker_repositories() {
	$defaults = array( ELODIN_BRIDGE_UPDATE_REPOSITORY );
	$raw_allowed = apply_filters( 'elodin_bridge_update_checker_allowed_repositories', $defaults );
	$raw_allowed = is_array( $raw_allowed ) ? $raw_allowed : $defaults;

	$normalized_allowed = array();
	foreach ( $raw_allowed as $candidate ) {
		$normalized = elodin_bridge_normalize_update_checker_repository( $candidate );
		if ( '' === $normalized ) {
			continue;
		}

		$normalized_allowed[ $normalized ] = true;
	}

	return array_keys( $normalized_allowed );
}

/**
 * Validate branch policy for update checks.
 *
 * Empty branch means tags/releases only. Non-empty branches must be explicitly allowlisted.
 *
 * @param mixed $branch Raw branch value.
 * @return string|null
 */
function elodin_bridge_validate_update_checker_branch( $branch ) {
	$branch = trim( (string) $branch );
	if ( '' === $branch ) {
		return '';
	}

	if ( ! preg_match( '/^[A-Za-z0-9._\/-]+$/', $branch ) ) {
		return null;
	}

	$raw_allowed = apply_filters( 'elodin_bridge_update_checker_allowed_branches', array() );
	if ( ! is_array( $raw_allowed ) ) {
		$raw_allowed = array();
	}

	$allowed_lookup = array();
	foreach ( $raw_allowed as $allowed_branch ) {
		$allowed_branch = trim( (string) $allowed_branch );
		if ( '' !== $allowed_branch ) {
			$allowed_lookup[ $allowed_branch ] = true;
		}
	}

	return isset( $allowed_lookup[ $branch ] ) ? $branch : null;
}

/**
 * Load Plugin Update Checker and wire GitHub updates for Ollie Bridge.
 */
function elodin_bridge_boot_update_checker() {
	$update_checker_file = ELODIN_BRIDGE_DIR . '/vendor/plugin-update-checker/plugin-update-checker.php';
	if ( ! file_exists( $update_checker_file ) ) {
		return;
	}

	require_once $update_checker_file;

	if ( ! class_exists( 'Puc_v4_Factory' ) ) {
		return;
	}

	$repository = apply_filters( 'elodin_bridge_update_checker_repository', ELODIN_BRIDGE_UPDATE_REPOSITORY );
	$repository = elodin_bridge_normalize_update_checker_repository( $repository );
	$allowed_repositories = elodin_bridge_get_allowed_update_checker_repositories();
	if ( '' === $repository || ! in_array( $repository, $allowed_repositories, true ) ) {
		elodin_bridge_set_update_checker_notice( 'Ollie Bridge update checker is disabled because the repository source is not in the approved allowlist.' );
		return;
	}

	$branch = apply_filters( 'elodin_bridge_update_checker_branch', ELODIN_BRIDGE_UPDATE_BRANCH );
	$branch = elodin_bridge_validate_update_checker_branch( $branch );
	if ( null === $branch ) {
		elodin_bridge_set_update_checker_notice( 'Ollie Bridge update checker is disabled because the configured update branch is not allowlisted.' );
		return;
	}

	$update_checker = Puc_v4_Factory::buildUpdateChecker(
		$repository . '/',
		ELODIN_BRIDGE_DIR . '/elodin-bridge.php',
		'elodin-bridge'
	);

	if ( '' !== $branch && method_exists( $update_checker, 'setBranch' ) ) {
		$update_checker->setBranch( $branch );
	}
}
add_action( 'plugins_loaded', 'elodin_bridge_boot_update_checker', 5 );
