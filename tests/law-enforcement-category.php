<?php
/**
 * Offline story-archive regression. No WordPress database or site writes.
 * Run with: php tests/law-enforcement-category.php
 */
if ( 'cli' !== PHP_SAPI ) {
    exit;
}
define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'OBJECT', 'OBJECT' );
class WP_Term {
    public $term_id;
    public $slug;
    public $name;
    public function __construct( $id, $slug, $name ) {
        $this->term_id = $id;
        $this->slug = $slug;
        $this->name = $name;
    }
}
class WP_Post {
    public $ID;
    public $post_type = 'post';
    public $post_status;
    public $post_name;
    public $post_title;
    public function __construct( $id, $name, $title, $status = 'publish' ) {
        $this->ID = $id;
        $this->post_name = $name;
        $this->post_title = $title;
        $this->post_status = $status;
    }
}
class Proof_Archive_Test_Query {
    public $posts;
    public $max_num_pages;
    private $offset = 0;
    public function __construct( $posts, $pages = 1 ) {
        $this->posts = $posts;
        $this->max_num_pages = $pages;
    }
    public function have_posts() {
        return $this->offset < count( $this->posts );
    }
    public function the_post() {
        $GLOBALS['post'] = $this->posts[ $this->offset++ ];
    }
}
$GLOBALS['proof_test_posts'] = array(
    2630 => new WP_Post( 2630, 'why-i-loved-the-work', 'Why I Loved the Work' ),
    3001 => new WP_Post( 3001, 'the-work-before-the-arrest', 'The Work Before the Arrest' ),
);
$GLOBALS['proof_test_term'] = new WP_Term( 46, 'law-enforcement-corrections', 'Law Enforcement & Corrections' );
$GLOBALS['proof_query_vars'] = array();
$GLOBALS['proof_test_mods'] = array();
$checks = 0;
function law_check( $condition, $reason ) {
    global $checks;
    if ( ! $condition ) {
        throw new RuntimeException( $reason );
    }
    $checks++;
}
function home_url( $path = '' ) { return 'https://jimlunsford.com' . $path; }
function absint( $value ) { return abs( (int) $value ); }
function get_theme_mod( $name, $default = false ) { return array_key_exists( $name, $GLOBALS['proof_test_mods'] ) ? $GLOBALS['proof_test_mods'][ $name ] : $default; }
function get_category_by_slug( $slug ) { return $slug === $GLOBALS['proof_test_term']->slug ? $GLOBALS['proof_test_term'] : false; }
function get_category_link( $term ) { return home_url( '/category/' . $term->slug . '/' ); }
function get_page_by_path( $path, $output = OBJECT, $type = 'post' ) {
    foreach ( $GLOBALS['proof_test_posts'] as $post ) {
        if ( $post->post_name === $path && $post->post_type === $type ) {
            return $post;
        }
    }
    return null;
}
function get_post( $id ) { return $GLOBALS['proof_test_posts'][ $id ] ?? null; }
function get_permalink( $id ) {
    $post = get_post( $id );
    return $post ? home_url( '/' . $post->post_name . '/' ) : false;
}
function url_to_postid( $url ) {
    foreach ( $GLOBALS['proof_test_posts'] as $post ) {
        if ( get_permalink( $post->ID ) === $url ) {
            return $post->ID;
        }
    }
    return 0;
}
function get_queried_object() { return $GLOBALS['proof_test_term']; }
function term_description( $term_id ) {
    return 46 === $term_id ? '<p>Firsthand accounts from my years in law enforcement and <strong>corrections</strong>.</p>' : '<p>Editable description for other categories.</p>';
}
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function get_query_var( $key, $default = '' ) { return $GLOBALS['proof_query_vars'][ $key ] ?? $default; }
function set_query_var( $key, $value ) {
    if ( null === $value ) {
        unset( $GLOBALS['proof_query_vars'][ $key ] );
    } else {
        $GLOBALS['proof_query_vars'][ $key ] = $value;
    }
}
function single_cat_title( $prefix = '', $display = true ) {
    $name = $GLOBALS['proof_test_term']->name;
    if ( $display ) { echo $prefix . $name; }
    return $name;
}
function __( $value, $domain = '' ) { return $value; }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain = '' ) { return esc_html( $value ); }
function esc_html_e( $value, $domain = '' ) { echo esc_html( $value ); }
function esc_attr_e( $value, $domain = '' ) { echo esc_html( $value ); }
function esc_url( $value ) { return preg_match( '#^https?://#', (string) $value ) ? esc_html( $value ) : ''; }
function esc_url_raw( $value ) { return preg_match( '#^https?://#', (string) $value ) ? $value : ''; }
function wp_kses_post( $value ) { return (string) $value; }
function get_header() {}
function get_footer() {}
function setup_postdata( $post ) { $GLOBALS['post'] = $post; }
function wp_reset_postdata() {}
function paginate_links( $args ) { return $args['total'] > 1 ? '<a href="/page/2/">2</a>' : ''; }
function get_template_part( $path ) {
    if ( 'template-parts/archive/post-feed' === $path ) {
        $post = $GLOBALS['post'];
        echo '<article data-post-id="' . $post->ID . '">' . esc_html( $post->post_title ) . '</article>';
        return;
    }
    include dirname( __DIR__ ) . '/' . $path . '.php';
}
require dirname( __DIR__ ) . '/inc/helpers.php';
function law_render_category( $posts, $page = 1, $pages = 1 ) {
    $GLOBALS['wp_query'] = new Proof_Archive_Test_Query( $posts, $pages );
    set_query_var( 'paged', $page );
    ob_start();
    include dirname( __DIR__ ) . '/category.php';
    return ob_get_clean();
}
$defaults = proof_get_identity_destination_defaults();
$keys = array_keys( $defaults );
law_check( array_search( 'law_enforcement', $keys, true ) === array_search( 'projects', $keys, true ) + 1, 'Homepage law destination must follow Projects.' );
law_check( array_search( 'core_frameworks', $keys, true ) === array_search( 'law_enforcement', $keys, true ) + 1, 'Core Frameworks must remain after the new destination.' );
law_check( home_url( '/category/law-enforcement-corrections/' ) === $defaults['law_enforcement']['url'], 'Destination must use the existing category URL.' );
$lane = proof_get_lane_config( 'law-enforcement-corrections' );
law_check( 'Law Enforcement & Corrections' === $lane['title'], 'Category lane title.' );
law_check( '' === $lane['description'], 'Editable WordPress description is the source of truth.' );
law_check( get_permalink( 2630 ) === $lane['start_here_url'], 'Published start-here permalink.' );
law_check( array( 2630 ) === proof_get_lane_featured_post_ids( 'law-enforcement-corrections', $lane ), 'Published article is the single feature.' );

// Initial live state: the sole published post is shown as Featured Stories, not duplicated or followed by an empty feed.
$html = law_render_category( array() );
law_check( false !== strpos( $html, '<h1 class="proof-page-title">' ), 'Category title is present.' );
law_check( false !== strpos( $html, 'Firsthand accounts from my years in law enforcement and corrections.' ), 'WP category description is visible as plain text.' );
law_check( false === strpos( $html, '<strong>corrections</strong>' ), 'Category description strips unsafe/unwanted HTML.' );
law_check( false !== strpos( $html, 'href="' . get_permalink( 2630 ) . '"'), 'Start Here points to the published story.' );
law_check( false !== strpos( $html, '<h2>Featured Stories</h2>' ), 'The law-specific featured heading is visible.' );
law_check( 1 === substr_count( $html, 'data-post-id="2630"' ), 'The feature is rendered exactly once.' );
law_check( false === strpos( $html, 'Recent Stories' ) && false === strpos( $html, 'Nothing published here yet.' ), 'Empty recent feed is not displayed.' );
law_check( ! isset( $GLOBALS['proof_query_vars']['proof_featured_ids'] ) && ! isset( $GLOBALS['proof_query_vars']['proof_featured_heading'] ) && ! isset( $GLOBALS['proof_query_vars']['proof_show_feed_category'] ), 'Archive-specific query vars are cleared, even with no recent posts.' );

// Subsequent published stories appear in Recent Stories automatically, without duplicating the feature.
$html = law_render_category( array( $GLOBALS['proof_test_posts'][3001] ) );
law_check( false !== strpos( $html, '<h2>Featured Stories</h2>' ) && false !== strpos( $html, '<h2>Recent Stories</h2>' ), 'Both named sections render when there is a recent story.' );
law_check( 1 === substr_count( $html, 'data-post-id="2630"' ) && 1 === substr_count( $html, 'data-post-id="3001"' ), 'The two stories appear exactly once each.' );

// If the starting article is unpublished, no dead Start Here link or featured card appears.
$GLOBALS['proof_test_posts'][2630]->post_status = 'draft';
$lane = proof_get_lane_config( 'law-enforcement-corrections' );
law_check( '' === $lane['start_here_url'] && array() === proof_get_lane_featured_post_ids( 'law-enforcement-corrections', $lane ), 'Unpublished start article suppresses both links and features.' );
$html = law_render_category( array( $GLOBALS['proof_test_posts'][3001] ) );
law_check( false === strpos( $html, '<h2>Featured Stories</h2>' ) && false === strpos( $html, get_permalink( 2630 ) ), 'No stale feature or link when the post is unpublished.' );
law_check( false !== strpos( $html, '<h2>Recent Stories</h2>' ), 'Other published work remains discoverable.' );
$GLOBALS['proof_test_posts'][2630]->post_status = 'publish';

// Page 2 keeps the lean archive header and excludes first-page feature/intro, while listing the regular feed.
$html = law_render_category( array( $GLOBALS['proof_test_posts'][3001] ), 2, 2 );
law_check( false === strpos( $html, 'Firsthand accounts from my years' ) && false === strpos( $html, '<h2>Featured Stories</h2>' ), 'Paginated archive omits repeated intro and feature.' );
law_check( false !== strpos( $html, '<h2>Recent Stories</h2>' ), 'Paginated archive still identifies recent stories.' );

// Existing categories keep their longstanding Recent Posts heading and configured lane copy.
$GLOBALS['proof_test_term'] = new WP_Term( 2, 'articles', 'Articles' );
$html = law_render_category( array( $GLOBALS['proof_test_posts'][3001] ) );
law_check( false !== strpos( $html, '<h2>Recent Posts</h2>' ) && false === strpos( $html, '<h2>Recent Stories</h2>' ), 'Unrelated categories keep their archive headings.' );
law_check( false !== strpos( $html, 'Long-form writing that fully teaches a concept' ) && false === strpos( $html, 'Editable description for other categories' ), 'Configured legacy category description keeps precedence.' );

echo 'PASS: ' . $checks . " Law Enforcement category regression checks.\n";
