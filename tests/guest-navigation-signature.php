<?php
/** CLI contract test against guest.min.js from the pinned LiteSpeed distribution. */
if ( 'cli' !== PHP_SAPI || 4 !== $argc ) {
	exit( 1 );
}
define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'LITESPEED_GUEST', 'guest' === $argv[3] );
define( 'LSCWP_V', $argv[2] );
define( 'LSWCP_PLUGIN_URL', 'https://fixture.invalid/subsite/content/plugins/litespeed-cache/' );
function add_action() {}
function add_filter() {}
function get_template_directory() { return dirname( __DIR__ ); }
function wp_parse_url( $url, $component ) { return parse_url( $url, $component ); }
function esc_url( $url ) { return $url; }
require get_template_directory() . '/functions.php';

$vendor   = file_get_contents( $argv[1] );
$endpoint = wp_parse_url( LSWCP_PLUGIN_URL . 'guest.vary.php', PHP_URL_PATH );
$tag      = '<script data-no-optimize="1">' . str_replace( 'litespeed_url', $endpoint, $vendor ) . '</script>';
$listener = '<script id="proof-guest-navigation"></script>';
$branch   = 'e.hasOwnProperty("reload")&&"yes"==e.reload&&';
$event    = 'document.dispatchEvent(new Event("proof:guest-reload",{cancelable:true}))&&';
$expected = str_replace( $branch, $branch . $event, $tag );
$checks   = 0;

function expect_same_html( $reason, $input, $expected ) {
	global $checks;
	if ( proof_guard_guest_navigation( $input ) !== $expected ) {
		throw new RuntimeException( $reason );
	}
	++$checks;
}

if ( ! LITESPEED_GUEST || '7.9.3' !== LSCWP_V ) {
	expect_same_html( 'Unsupported context must pass through', $listener . $tag, $listener . $tag );
} else {
	if ( $expected === $tag ) {
		throw new RuntimeException( 'The installed emitter no longer has the tested affirmative branch' );
	}
	expect_same_html( 'Exactly one insertion before native bookkeeping', $listener . $tag, $listener . $expected );
	expect_same_html( 'Idempotent filter', $listener . $expected, $listener . $expected );
	expect_same_html( 'No listener', $tag, $tag );
	expect_same_html( 'Repeated genuine bootstrap', $listener . $tag . $tag, $listener . $expected . $expected );
	$changes = array(
		'URL'       => array( $endpoint, '/another-plugin/guest.vary.php' ),
		'wrapper'   => array( 'data-no-optimize="1"', 'data-no-optimize="0"' ),
		'cookie'    => array( '_lscache_vary', '_another_vary' ),
		'transport' => array( 'redirect:"follow"', 'redirect:"manual"' ),
		'predicate' => array( '"yes"==e.reload', '"no"==e.reload' ),
		'reload'    => array( 'window.location.reload(!0)', 'window.location.reload()' ),
	);
	foreach ( $changes as $label => $pair ) {
		$altered = str_replace( $pair[0], $pair[1], $tag );
		if ( $altered === $tag ) {
			throw new RuntimeException( 'Missing negative-control input for ' . $label );
		}
		expect_same_html( $label, $listener . $altered . $tag, $listener . $altered . $expected );
	}
}
printf( "PASS %d guest signature checks (%s, %s)\n", $checks, LSCWP_V, $argv[3] );
