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
function wp_attachment_is_image( $id ) { return in_array( $id, array( 2586, 2615 ), true ); }
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
$books = proof_get_identity_published_books();
check( 2 === count( $books ), 'Exactly two default books' );
check( array( 2586, 2615 ) === array_column( $books, 'image_id' ), 'Approved media IDs' );
$html = render_books();
check( 2 === substr_count( $html, '<article ' ), 'Two rendered features' );
check( false !== strpos( $html, 'View All Books' ), 'Catalog link visible' );
check( false !== strpos( $html, 'aria-label="Explore Book: The PERIOD Code"' ), 'First book accessible name contains the visible label and title' );
check( false !== strpos( $html, 'aria-label="Explore Book: The Disciplined Operator"' ), 'Second book accessible name contains the visible label and distinct title' );
check( 2 === substr_count( $html, '>Explore Book</a>' ), 'Both visible book labels remain unchanged' );
foreach ( $books as $book ) {
	check( false !== strpos( $html, 'href="' . esc_url( $book['url'] ) . '" aria-label="Explore Book: ' . esc_attr( $book['title'] ) . '"' ), 'Each book retains its configured destination' );
}
check( false === strpos( $html, 'amazon.com' ), 'Homepage links to book pages rather than adding checkout buttons' );
check( false === strpos( $html, 'Useless Eaters' ), 'No excluded title' );
$GLOBALS['proof_test_mods']['proof_identity_published_books_one_title'] = '';
check( 1 === count( proof_get_identity_published_books() ), 'Blank title hides slot' );
$GLOBALS['proof_test_mods']['proof_identity_published_books_two_url'] = '';
check( '' === trim( render_books() ), 'Empty section suppressed' );
$GLOBALS['proof_test_mods'] = array( 'proof_identity_published_books_one_image_id' => 999 );
check( 1 === count( proof_get_identity_published_books() ), 'Non-image cover hides slot' );
$GLOBALS['proof_test_mods'] = array( 'proof_identity_published_books_one_url' => 'javascript:alert(1)' );
check( 1 === count( proof_get_identity_published_books() ), 'Unsafe link suppressed' );
$GLOBALS['proof_test_mods'] = array( 'proof_identity_published_books_one_title' => '<script>unsafe</script>', 'proof_identity_published_books_one_description' => '<img onerror=alert(1)>' );
$html = render_books();
check( false === strpos( $html, '<script>' ) && false !== strpos( $html, '&lt;script&gt;' ), 'Title escaped' );
check( false === strpos( $html, '<img onerror=' ), 'Description escaped' );
check( false !== strpos( $html, '(max-width: 380px) 90px, 112px' ), 'Responsive cover sizes' );
$GLOBALS['proof_test_mods'] = array();
$identity = file_get_contents( dirname( __DIR__ ) . '/template-parts/home/identity-hub.php' );
check( strpos( $identity, "'template-parts/home/published-books'" ) < strpos( $identity, "'template-parts/home/latest-work'" ), 'Book template before Latest Work' );
$css = file_get_contents( dirname( __DIR__ ) . '/assets/css/homepage-books.css' );
check( false !== strpos( $css, 'grid-template-columns: 112px minmax(0, 1fr)' ), 'Approved desktop book geometry' );
check( false !== strpos( $css, '@media (max-width: 760px)' ), 'Mobile stacking breakpoint' );
echo 'PASS: ' . $checks . " homepage book regression checks.\n";
