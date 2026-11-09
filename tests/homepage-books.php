<?php
/** Focused CLI regression tests without loading or writing a WordPress site. */
if ( 'cli' !== PHP_SAPI ) { exit; }
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['proof_test_mods'] = array();
function get_theme_mod( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['proof_test_mods'] ) ? $GLOBALS['proof_test_mods'][ $key ] : $default; }
function home_url( $path = '' ) { return 'https://jimlunsford.com' . $path; }
class WP_Term { public $slug; public function __construct( $slug ) { $this->slug = $slug; } }
function get_category_by_slug( $slug ) { return 'law-enforcement-corrections' === $slug ? new WP_Term( $slug ) : false; }
function get_category_link( $term ) { return home_url( '/category/' . $term->slug . '/' ); }
function absint( $value ) { return abs( (int) $value ); }
function esc_url( $value ) { return preg_match( '#^https?://#', $value ) ? htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ) : ''; }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function __( $value, $domain = '' ) { return $value; }
function esc_html_e( $value, $domain = '' ) { echo esc_html( $value ); }
function wp_attachment_is_image( $id ) { return in_array( $id, array( 2586, 2690, 2698, 2615, 9001, 9002, 9003, 9004 ), true ); }
function wp_get_attachment_image( $id, $size, $icon, $attributes ) { return '<img src="https://jimlunsford.com/test-' . $id . '.jpg" alt="' . esc_attr( $attributes['alt'] ) . '" sizes="' . esc_attr( $attributes['sizes'] ) . '" loading="' . esc_attr( $attributes['loading'] ) . '" />'; }
require dirname( __DIR__ ) . '/inc/helpers.php';
$checks = 0;
function check( $condition, $message ) { global $checks; if ( ! $condition ) { throw new RuntimeException( $message ); } $checks++; }
function render_books() { ob_start(); include dirname( __DIR__ ) . '/template-parts/home/published-books.php'; return ob_get_clean(); }
$defaults = proof_get_identity_destination_defaults();
$keys = array_keys( $defaults );
check( array_search( 'law_enforcement', $keys, true ) === array_search( 'projects', $keys, true ) + 1, 'Law Enforcement follows Projects' );
check( array_search( 'core_frameworks', $keys, true ) === array_search( 'law_enforcement', $keys, true ) + 1, 'Core Frameworks follows Law Enforcement' );
check( home_url( '/category/law-enforcement-corrections/' ) === $defaults['law_enforcement']['url'], 'Canonical category destination' );
check( array_search( 'book', $keys, true ) === array_search( 'core_frameworks', $keys, true ) + 1, 'Books follows Core Frameworks' );
check( array_search( 'about', $keys, true ) === array_search( 'book', $keys, true ) + 1, 'About follows Books' );
check( 'Books' === $defaults['book']['title'], 'Books default title' );
check( home_url( '/books/' ) === $defaults['book']['url'], 'Catalog destination' );
foreach ( array( 'microblog', 'articles', 'contact' ) as $key ) { $GLOBALS['proof_test_mods'][ 'proof_identity_destination_' . $key . '_url' ] = ''; }
$GLOBALS['proof_test_mods']['proof_identity_destination_about_title'] = 'About Jim Lunsford';
$links = proof_get_identity_destination_links();
check( array( 'The Work', 'Projects', 'Law Enforcement & Corrections', 'Core Frameworks', 'Books', 'About Jim Lunsford' ) === array_column( $links, 'title' ), 'Approved six destination order' );
$GLOBALS['proof_test_mods']['proof_identity_destination_law_enforcement_url'] = '';
check( array( 'The Work', 'Projects', 'Core Frameworks', 'Books', 'About Jim Lunsford' ) === array_column( proof_get_identity_destination_links(), 'title' ), 'Blank saved Law Enforcement URL hides just that card' );
unset( $GLOBALS['proof_test_mods']['proof_identity_destination_law_enforcement_url'] );
$GLOBALS['proof_test_mods']['proof_identity_destination_book_url'] = '';
check( 5 === count( proof_get_identity_destination_links() ), 'Existing blank book override preserved' );
unset( $GLOBALS['proof_test_mods']['proof_identity_destination_book_url'] );
$GLOBALS['proof_test_mods'] = array();
$expected = array(
 'one' => array( 'title' => 'The PERIOD Code', 'description' => 'Six non-negotiable values to take back your life and keep it.', 'url' => home_url( '/period-code/' ), 'image_id' => 2586 ),
 'three' => array( 'title' => 'How to Rebuild Yourself', 'description' => 'Raise standards, build structure, create proof, and become someone you can trust.', 'url' => home_url( '/how-to-rebuild-yourself/' ), 'image_id' => 2690 ),
 'four' => array( 'title' => 'The Discipline Loop', 'description' => 'Repeated action creates proof, rebuilds self-trust, and stabilizes identity.', 'url' => home_url( '/discipline-loop/' ), 'image_id' => 2698 ),
 'two' => array( 'title' => 'The Disciplined Operator', 'description' => 'A manifesto for self-command.', 'url' => 'https://disciplinedoperator.com/', 'image_id' => 2615 ),
);
check( $expected === proof_get_identity_published_books_defaults(), 'Exact defaults, order, and persistent setting identities' );
$books = proof_get_identity_published_books();
check( array_values( $expected ) === $books, 'Exactly four valid default books in approved order' );
$html = render_books();
check( 4 === substr_count( $html, '<article ' ), 'Four rendered features' );
check( false !== strpos( $html, 'href="https://jimlunsford.com/books/">View All Books</a>' ), 'Catalog link retains its label and destination' );
check( 4 === substr_count( $html, '>Explore Book</a>' ), 'All four visible book labels remain unchanged' );
foreach ( $books as $book ) {
 check( false !== strpos( $html, 'href="' . esc_url( $book['url'] ) . '" aria-label="Explore Book: ' . esc_attr( $book['title'] ) . '"' ), 'Each accessible link retains its configured destination and title' );
 check( false !== strpos( $html, 'alt="' . esc_attr( $book['title'] . ' book cover by Jim Lunsford' ) . '"' ), 'Each cover has its distinct accessible description' );
 check( false !== strpos( $html, esc_html( $book['description'] ) ), 'Each description renders' );
}
check( ! preg_match( '/amazon\.|amzn\./i', $html ), 'Homepage links to book pages, without Amazon checkout links' );
check( false === strpos( $html, 'Useless Eaters' ), 'No excluded title' );

// All fields remain independently configurable. Legacy two is still Operator,
// even though it now occupies display position four.
foreach ( array_keys( $expected ) as $index => $key ) {
 $prefix = 'proof_identity_published_books_' . $key . '_';
 $custom = array( 'title' => 'Saved ' . $key, 'description' => 'Saved description ' . $key, 'url' => 'https://example.com/' . $key . '/', 'image_id' => 9001 + $index );
 foreach ( $custom as $field => $value ) {
  $GLOBALS['proof_test_mods'] = array( $prefix . $field => $value );
  $changed = array_values( $expected );
  $changed[$index][$field] = $value;
  check( $changed === proof_get_identity_published_books(), $key . ': independent saved ' . $field . ', other slots unchanged' );
 }
 foreach ( array( array('title',''), array('title','   '), array('url',''), array('url','javascript:alert(1)'), array('url','data:text/html,unsafe'), array('image_id',0), array('image_id',999), array('image_id','not-a-number') ) as $invalid ) {
  $GLOBALS['proof_test_mods'] = array( $prefix . $invalid[0] => $invalid[1] );
  $remaining = $expected; unset( $remaining[$key] );
  check( array_values( $remaining ) === proof_get_identity_published_books(), $key . ': invalid ' . $invalid[0] . ' hides only this slot and closes its gap' );
  check( 3 === substr_count( render_books(), '<article ' ), $key . ': exactly three remaining cards render' );
 }
 $GLOBALS['proof_test_mods'] = array( $prefix . 'description' => '' );
 check( 4 === count( proof_get_identity_published_books() ), $key . ': optional blank description retains card' );
 $GLOBALS['proof_test_mods'] = array( $prefix . 'title' => '<script>unsafe</script> "quoted"', $prefix . 'description' => '<img onerror=alert(1)> & text', $prefix . 'url' => 'https://example.com/?q=" onmouseover="unsafe' );
 $html = render_books();
 check( false === strpos( $html, '<script>' ) && false !== strpos( $html, '&lt;script&gt;' ), $key . ': title escaped' );
 check( false === strpos( $html, '<img onerror=' ) && false !== strpos( $html, '&lt;img onerror=alert(1)&gt; &amp; text' ), $key . ': description escaped' );
 check( false === strpos( $html, '" onmouseover="' ), $key . ': URL attribute escaped' );
 check( false !== strpos( $html, 'aria-label="Explore Book: &lt;script&gt;unsafe&lt;/script&gt; &quot;quoted&quot;"' ), $key . ': accessible link name escaped' );
 check( false !== strpos( $html, '(max-width: 380px) 90px, 112px' ), 'Responsive cover sizes retained' );
}
$GLOBALS['proof_test_mods'] = array();
$all_saved = array();
foreach ( array_keys( $expected ) as $index => $key ) {
 $saved = array( 'title' => 'Saved ' . $key, 'description' => 'Description ' . $key, 'url' => 'https://example.com/' . $key . '/', 'image_id' => 9001 + $index );
 foreach ( $saved as $field => $value ) { $GLOBALS['proof_test_mods']['proof_identity_published_books_' . $key . '_' . $field] = $value; }
 $all_saved[] = $saved;
}
check( $all_saved === proof_get_identity_published_books(), 'All four saved entries coexist in display order' );
foreach ( array_keys( $expected ) as $key ) { $GLOBALS['proof_test_mods']['proof_identity_published_books_' . $key . '_title'] = ''; }
check( '' === trim( render_books() ), 'All invalid entries suppress the empty section and catalog link' );
$GLOBALS['proof_test_mods'] = array();
check( 'Books on discipline, ownership, self-trust, and rebuilding your life.' === $defaults['book']['description'], 'Durable Start Here Books default' );
$original_links = proof_get_identity_destination_links();
$GLOBALS['proof_test_mods']['proof_identity_destination_book_description'] = 'My saved catalog description';
$expected_links = $original_links;
foreach ( $expected_links as &$link ) { if ( 'Books' === $link['title'] ) { $link['description'] = 'My saved catalog description'; } }
unset( $link );
check( $expected_links === proof_get_identity_destination_links(), 'Saved Start Here description retained, all other destinations unchanged' );
$GLOBALS['proof_test_mods'] = array();
$identity = file_get_contents( dirname( __DIR__ ) . '/template-parts/home/identity-hub.php' );
check( strpos( $identity, 'proof-identity-destinations' ) < strpos( $identity, "'template-parts/home/published-books'" ), 'Books remain after Start Here' );
check( strpos( $identity, "'template-parts/home/published-books'" ) < strpos( $identity, "'template-parts/home/latest-work'" ), 'Book template before Latest Work' );
$css = file_get_contents( dirname( __DIR__ ) . '/assets/css/homepage-books.css' );
check( false !== strpos( $css, 'grid-template-columns: 112px minmax(0, 1fr)' ), 'Approved desktop book geometry' );
check( false !== strpos( $css, '@media (max-width: 760px)' ), 'Mobile stacking breakpoint' );
echo 'PASS: ' . $checks . " homepage book regression checks.\n";
