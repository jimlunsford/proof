<?php
/**
 * Offline regression test for Projects-only pagination SEO helpers.
 *
 * Run: php tests/projects-pagination-seo.php
 */
define( 'ABSPATH', __DIR__ );
$GLOBALS['mock_page'] = 'projects';
$GLOBALS['mock_404'] = false;
$GLOBALS['mock_admin'] = false;
$GLOBALS['mock_query_vars'] = [ 'paged' => 0, 'page' => 1 ];
$GLOBALS['mock_post_count'] = 25;
$GLOBALS['mock_headers'] = [];
$GLOBALS['mock_removed_actions'] = [];
$GLOBALS['mock_filters'] = [];

class WP_Term {
	public $count;
	public function __construct( $count ) {
		$this->count = $count;
	}
}

class Proof_Mock_Main_Query {
	public function set_404() {
		$GLOBALS['mock_404'] = true;
	}
}

$GLOBALS['wp_query'] = new Proof_Mock_Main_Query();

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['mock_filters'][ $hook ] = $callback;
}
function add_action( $hook, $callback, $priority = 10 ) {
	$GLOBALS['mock_filters'][ $hook ] = $callback;
}
function remove_action( $hook, $callback ) {
	$GLOBALS['mock_removed_actions'][] = [ $hook, $callback ];
}
function absint( $value ) {
	return abs( (int) $value );
}
function is_admin() {
	return $GLOBALS['mock_admin'];
}
function is_page( $slug ) {
	return ! $GLOBALS['mock_404'] && $GLOBALS['mock_page'] === $slug;
}
function is_category( $slug ) {
	return $GLOBALS['mock_page'] === "category:$slug";
}
function is_404() {
	return $GLOBALS['mock_404'];
}
function get_query_var( $name ) {
	return $GLOBALS['mock_query_vars'][ $name ] ?? 0;
}
function proof_get_projects_writing_category_slug() {
	return 'projects';
}
function proof_get_projects_writing_posts_per_page() {
	return 6;
}
function get_category_by_slug( $slug ) {
	return $slug === 'projects' ? new WP_Term( $GLOBALS['mock_post_count'] ) : null;
}
function get_queried_object_id() {
	return 1984;
}
function get_permalink( $id ) {
	return $id === 1984 ? 'https://jimlunsford.com/projects/' : '';
}
function trailingslashit( $str ) {
	return rtrim( $str, '/' ) . '/';
}
function home_url( $path ) {
	return 'https://jimlunsford.com' . $path;
}
function status_header( $code ) {
	$GLOBALS['mock_headers'][] = $code;
}
function nocache_headers() {
	$GLOBALS['mock_headers'][] = 'nocache';
}

require dirname( __DIR__ ) . '/inc/projects-pagination-seo.php';

function proof_check( $ok, $name ) {
	if ( ! $ok ) {
		fwrite( STDERR, "FAIL: $name\n" );
		exit( 1 );
	}
	echo "PASS: $name\n";
}

proof_check( proof_projects_listing_page_number() === 1, 'first listing page' );
proof_check( proof_projects_listing_page_count() === 5, '25 posts => 5 pages' );
proof_check( proof_projects_listing_page_url( 1 ) === 'https://jimlunsford.com/projects/', 'page one URL' );
proof_check( proof_projects_listing_page_url( 4 ) === 'https://jimlunsford.com/projects/page/4/', 'deep URL' );
proof_check( proof_projects_listing_paged_seo_url() === '', 'unchanged page one SEO' );

$GLOBALS['mock_query_vars'] = [ 'paged' => 3, 'page' => 0 ];
$url = 'https://jimlunsford.com/projects/page/3/';
proof_check( proof_projects_listing_paged_seo_url() === $url, 'page three URL' );

$tags = [
	'canonical' => [ 'tag' => 'link', 'attributes' => [ 'rel' => 'canonical', 'href' => 'https://jimlunsford.com/projects/' ] ],
	'og:url' => [ 'attributes' => [ 'property' => 'og:url', 'content' => 'https://jimlunsford.com/projects/' ] ],
	'og:title' => [ 'attributes' => [ 'content' => 'Projects' ] ],
];
$changed = proof_projects_fix_paged_meta_urls( $tags );
proof_check( $changed['canonical']['attributes']['href'] === $url, 'canonical self URL' );
proof_check( $changed['og:url']['attributes']['content'] === $url, 'Open Graph self URL' );
proof_check( $changed['og:title'] === $tags['og:title'], 'unrelated metadata preserved' );

$graph = [
	[ '@type' => 'WebSite', '@id' => 'https://jimlunsford.com/#site', 'url' => 'https://jimlunsford.com/' ],
	[ '@type' => 'WebPage', '@id' => 'https://jimlunsford.com/projects/', 'url' => 'https://jimlunsford.com/projects/', 'potentialAction' => [ '@type' => 'ReadAction', 'target' => 'https://jimlunsford.com/projects/' ] ],
];
$fixed = proof_projects_fix_paged_schema_url( $graph, null );
proof_check( $fixed[1]['@id'] === $url, 'schema WebPage id' );
proof_check( $fixed[1]['url'] === $url, 'schema WebPage URL' );
proof_check( $fixed[1]['potentialAction']['target'] === $url, 'schema ReadAction target' );
proof_check( $fixed[0] === $graph[0], 'schema WebSite untouched' );
proof_check( proof_projects_fix_paged_schema_url( $graph, [ 'id' => 1984 ] ) === $graph, 'related graph with args untouched' );

$GLOBALS['mock_page'] = 'notes';
proof_check( proof_projects_fix_paged_meta_urls( $tags ) === $tags, 'other category metadata untouched' );
proof_check( proof_projects_fix_paged_schema_url( $graph, null ) === $graph, 'other category schema untouched' );
$GLOBALS['mock_page'] = 'projects';

foreach ( [ [1, ''], [2, 'https://jimlunsford.com/projects/page/3/'], [3, 'https://jimlunsford.com/projects/page/5/'], [4, 'https://jimlunsford.com/projects/page/7/'] ] as $case ) {
	proof_check( proof_projects_legacy_category_page_target( $case[0] ) === $case[1], 'legacy category page ' . $case[0] . ' mapping' );
}

$GLOBALS['mock_query_vars'] = [ 'paged' => 0, 'page' => 99 ];
proof_check( proof_projects_listing_paged_seo_url() === '', 'no SEO canonical for invalid page' );
proof_projects_reject_out_of_range_pagination();
proof_check( $GLOBALS['mock_404'] === true, 'invalid Projects page marked 404' );
proof_check( in_array( 404, $GLOBALS['mock_headers'], true ), 'invalid Projects HTTP 404 status' );
proof_check( in_array( [ 'template_redirect', 'redirect_canonical' ], $GLOBALS['mock_removed_actions'], true ), 'no invalid-page guess redirect' );

echo "RESULT: PROJECTS_PAGINATION_OFFLINE_PASS\n";
