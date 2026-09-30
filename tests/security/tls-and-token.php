<?php
/**
 * Regression check: BetterStack requests verify TLS, and the source token is
 * sanitized on save and escaped on the settings screen.
 *
 * Usage (from the sandbox root; the plugin does not need to be active):
 *   ddev wp eval-file wp-content/plugins/betterstack-logger/tests/security/tls-and-token.php
 *
 * No real request is sent: pre_http_request returns a mocked 202 response.
 *
 * @package BetterStackLogger
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- test script run via wp eval-file.

if ( ! class_exists( 'BetterStackLogger' ) ) {
	require_once dirname( __DIR__, 2 ) . '/includes/BetterStackLogger.php';
}
require_once ABSPATH . 'wp-admin/includes/template.php';

$betterstack_failures = array();
$betterstack_payload  = '"><script>alert(1)</script>';

// 1. TLS verification on every request the logger sends.
$betterstack_seen = array();
$betterstack_capture = static function ( $parsed_args, $url ) use ( &$betterstack_seen ) {
	if ( str_contains( $url, 'betterstack.com' ) ) {
		$betterstack_seen[] = array(
			'url'       => $url,
			'sslverify' => $parsed_args['sslverify'],
		);
	}
	return $parsed_args;
};
$betterstack_mock = static function ( $pre, $parsed_args, $url ) {
	if ( str_contains( $url, 'betterstack.com' ) ) {
		return array(
			'headers'  => array(),
			'body'     => '',
			'response' => array(
				'code'    => 202,
				'message' => 'Accepted',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}
	return $pre;
};
$betterstack_key = static fn() => 'test-source-token';
add_filter( 'http_request_args', $betterstack_capture, PHP_INT_MAX, 2 );
add_filter( 'pre_http_request', $betterstack_mock, 10, 3 );
add_filter( 'pre_option_betterstack_api_key', $betterstack_key );

$betterstack_logger = new BetterStackLogger();
$betterstack_result = $betterstack_logger->log_error( 'tls regression test' );

remove_filter( 'http_request_args', $betterstack_capture, PHP_INT_MAX );
remove_filter( 'pre_http_request', $betterstack_mock, 10 );
remove_filter( 'pre_option_betterstack_api_key', $betterstack_key );

WP_CLI::log( 'log_error(): ' . $betterstack_result );
WP_CLI::log( 'request args seen by http_request_args: ' . wp_json_encode( $betterstack_seen ) );
if ( empty( $betterstack_seen ) ) {
	$betterstack_failures[] = 'no BetterStack request was made';
}
foreach ( $betterstack_seen as $betterstack_req ) {
	if ( true !== $betterstack_req['sslverify'] ) {
		$betterstack_failures[] = 'sslverify is not true for ' . $betterstack_req['url'];
	}
}

// 2. Token sanitized on save (options.php runs sanitize_option()).
$betterstack_logger->settings_init();
$betterstack_saved = sanitize_option( 'betterstack_api_key', '  ' . $betterstack_payload . 'abc123  ' );
WP_CLI::log( 'sanitize_option( betterstack_api_key ): ' . var_export( $betterstack_saved, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
if ( str_contains( $betterstack_saved, '<' ) || str_contains( $betterstack_saved, '"' ) || $betterstack_saved !== trim( $betterstack_saved ) ) {
	$betterstack_failures[] = 'token is not sanitized on save';
}

// 3. Token escaped on the settings screen, even if an unsanitized value is already stored.
$betterstack_stored = static fn() => $betterstack_payload;
add_filter( 'pre_option_betterstack_api_key', $betterstack_stored );
ob_start();
do_settings_sections( 'betterstack-logger' );
$betterstack_html = ob_get_clean();
remove_filter( 'pre_option_betterstack_api_key', $betterstack_stored );
preg_match( '/<input[^>]*name="betterstack_api_key"[^>]*>/', $betterstack_html, $betterstack_input );
WP_CLI::log( 'rendered token field: ' . ( $betterstack_input[0] ?? '(not found)' ) );
if ( str_contains( $betterstack_html, '<script>alert(1)</script>' ) ) {
	$betterstack_failures[] = 'token is printed unescaped';
}

if ( $betterstack_failures ) {
	WP_CLI::error( implode( '; ', $betterstack_failures ) );
}
WP_CLI::success( 'TLS is verified and the token is sanitized and escaped.' );
