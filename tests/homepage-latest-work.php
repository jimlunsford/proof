<?php
/**
 * Real-WordPress regression tests. See README for disposable-site setup.
 * Never run against production: this creates and removes fixture content.
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}

$wordpress_root = getenv( 'PROOF_TEST_WP_ROOT' );
if ( ! $wordpress_root || ! is_readable( $wordpress_root . '/wp-load.php' ) ) {
	fwrite( STDERR, "Set PROOF_TEST_WP_ROOT to an empty, disposable local WordPress install.\n" );
	exit( 1 );
}
require $wordpress_root . '/wp-load.php';

if ( ! defined( 'PROOF_DISPOSABLE_TEST_SITE' ) || true !== PROOF_DISPOSABLE_TEST_SITE || 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Refusing fixture writes without an explicitly disposable local test site.' );
}
if ( realpath( get_template_directory() ) !== realpath( dirname( __DIR__ ) ) ) {
	throw new RuntimeException( 'Activate this Proof checkout on the disposable test site first.' );
}
$existing = get_posts( array( 'post_type' => 'any', 'post_status' => array_keys( get_post_stati() ), 'numberposts' => 1 ) );
if ( $existing ) {
	throw new RuntimeException( 'Refusing to run with existing content. Use an empty disposable site.' );
}

$checks        = 0;
$post_ids      = array();
$term_ids      = array();
$original_mods = get_theme_mods();
$original_sticky = get_option( 'sticky_posts' );
$original_user = get_current_user_id();
$original_query = $GLOBALS['wp_query'];
$original_main_query = $GLOBALS['wp_the_query'];

function proof_latest_check( $condition, $message ) {
	global $checks;
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	$checks++;
}

function proof_latest_fixture( $args ) {
	global $post_ids;
	$id = wp_insert_post( wp_slash( array_merge( array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => 'Latest Work fixture',
		'post_content' => '<p>A useful, ordinary fixture excerpt.</p>',
		'post_date'    => gmdate( 'Y-m-d H:i:s', time() - 20 * DAY_IN_SECONDS ),
	), $args ) ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	$post_ids[] = $id;
	return $id;
}

function proof_latest_ids() {
	return array_map( 'intval', wp_list_pluck( proof_get_identity_latest_work_query()->posts, 'ID' ) );
}

function proof_latest_render() {
	ob_start();
	include dirname( __DIR__ ) . '/template-parts/home/latest-work.php';
	return ob_get_clean();
}

function proof_latest_dom( $html ) {
	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$document->loadHTML( '<?xml encoding="utf-8" ?><!DOCTYPE html><html><body>' . $html . '</body></html>' );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	return new DOMXPath( $document );
}

register_post_type( 'proof_fixture', array( 'public' => true ) );
try {
	// Keep an outer page loop active, as the homepage does in WordPress.
	$page_id = proof_latest_fixture( array( 'post_type' => 'page', 'post_title' => 'Outer page' ) );
	$GLOBALS['wp_query'] = new WP_Query( array( 'page_id' => $page_id ) );
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	$GLOBALS['wp_query']->the_post();
	$outer_post = $GLOBALS['post'];
	$outer_query = $GLOBALS['wp_query'];
	$outer_vars = array();
	foreach ( array( 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' ) as $key ) {
		$outer_vars[ $key ] = $GLOBALS[ $key ];
	}
	proof_latest_check( '' === trim( proof_latest_render() ), 'Zero eligible posts must suppress the entire section.' );
	proof_latest_check( $outer_post === $GLOBALS['post'], 'Empty results must leave the outer post untouched.' );

	$eligible = array();
	$categories = array( 'Articles', 'Projects', 'Law Enforcement & Corrections', 'Recovery Standards', 'Discipline Dispatch', 'Notes', 'Recovery Beyond AA' );
	foreach ( $categories as $index => $name ) {
		$term = wp_insert_term( $name, 'category' );
		if ( is_wp_error( $term ) ) {
			throw new RuntimeException( $term->get_error_message() );
		}
		$term_ids[] = $term['term_id'];
		$eligible[] = proof_latest_fixture( array(
			'post_title'    => 'Fixture in ' . $name,
			'post_name'     => 1 === $index ? proof_get_home_featured_paths()[0] : sanitize_title( 'fixture ' . $name ),
			'post_category' => array( $term['term_id'] ),
			'post_date'     => gmdate( 'Y-m-d H:i:s', time() - ( 10 - $index ) * DAY_IN_SECONDS ),
		) );
		$expected = array_slice( array_reverse( $eligible ), 0, 5 );
		proof_latest_check( $expected === proof_latest_ids(), $name . ' must be eligible in publication order.' );
		$dom = proof_latest_dom( proof_latest_render() );
		proof_latest_check( min( 5, count( $eligible ) ) === $dom->query( '//li' )->length, 'Render exactly the available posts, capped at five.' );
		proof_latest_check( $name === $dom->query( '//span[@class="proof-identity-latest-label"]' )->item( 0 )->textContent, 'Use the real category label for ' . $name );
	}
	proof_latest_check( in_array( $eligible[1], proof_get_home_featured_ids(), true ), 'Projects fixture must also exercise the existing featured-post exclusion.' );
	proof_latest_check( in_array( $eligible[1], proof_latest_ids(), true ), 'Featured Projects post must remain in Latest Work.' );

	$older = proof_latest_fixture( array( 'post_title' => 'Older sticky post' ) );
	stick_post( $older );
	stick_post( $eligible[2] );
	proof_latest_check( $expected === proof_latest_ids(), 'Sticky posts inside and outside the top five must not change order or displace a newer post.' );
	$original_date = get_post_field( 'post_date', $older );
	wp_update_post( array( 'ID' => $older, 'post_content' => 'Updated today, originally published earlier.' ) );
	proof_latest_check( $original_date === get_post_field( 'post_date', $older ), 'Editing preserves original publication date.' );
	proof_latest_check( get_post_field( 'post_modified', $older ) > get_post_field( 'post_date', end( $eligible ) ), 'Older fixture was modified more recently than the newest post.' );
	proof_latest_check( $expected === proof_latest_ids(), 'Last-modified date must not affect Latest Work order.' );

	foreach ( array( 'draft', 'pending', 'future', 'private', 'trash' ) as $status ) {
		$id = proof_latest_fixture( array(
			'post_status' => $status,
			'post_title'  => 'Excluded ' . $status,
			'post_date'   => gmdate( 'Y-m-d H:i:s', time() + ( 'future' === $status ? DAY_IN_SECONDS : -HOUR_IN_SECONDS ) ),
		) );
		proof_latest_check( $status === get_post_status( $id ), 'Fixture has expected ' . $status . ' status.' );
		proof_latest_check( $expected === proof_latest_ids(), $status . ' must be excluded.' );
	}
	proof_latest_fixture( array( 'post_type' => 'page', 'post_date' => gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS ) ) );
	proof_latest_fixture( array( 'post_type' => 'proof_fixture', 'post_date' => gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS ) ) );
	proof_latest_check( $expected === proof_latest_ids(), 'Published pages and custom post types must be excluded.' );
	wp_set_current_user( 1 );
	proof_latest_check( current_user_can( 'read_private_posts' ) && $expected === proof_latest_ids(), 'Even an administrator must see only published posts.' );
	wp_set_current_user( 0 );

	// A real status transition must invalidate the cached query naturally.
	$new_id = proof_latest_fixture( array( 'post_status' => 'draft', 'post_title' => 'Newly published', 'post_date' => gmdate( 'Y-m-d H:i:s', time() - MINUTE_IN_SECONDS ) ) );
	proof_latest_check( $expected === proof_latest_ids(), 'New draft does not enter Latest Work.' );
	wp_update_post( array( 'ID' => $new_id, 'post_status' => 'publish' ) );
	array_unshift( $expected, $new_id );
	$expected = array_slice( $expected, 0, 5 );
	proof_latest_check( $expected === proof_latest_ids(), 'Publishing must insert the new post and remove the oldest displayed post.' );

	$before_legacy = proof_latest_render();
	set_theme_mod( 'proof_identity_latest_work_heading', 'Obsolete heading' );
	foreach ( array( 'one', 'two', 'three' ) as $slot ) {
		foreach ( array( 'label', 'title', 'description', 'url' ) as $field ) {
			set_theme_mod( 'proof_identity_latest_work_' . $slot . '_' . $field, 'Legacy ' . $slot . ' ' . $field );
		}
	}
	$saved_mods = get_theme_mods();
	proof_latest_check( $before_legacy === proof_latest_render(), 'Legacy manual slots and heading must not affect output.' );
	proof_latest_check( $saved_mods === get_theme_mods(), 'Rendering must not rewrite saved theme mods.' );
	set_theme_mod( 'proof_identity_latest_work_heading', '' );
	proof_latest_check( $before_legacy === proof_latest_render(), 'A saved blank heading cannot hide Latest Work.' );

	$dom = proof_latest_dom( $before_legacy );
	proof_latest_check( 'Latest Work' === $dom->query( '//h2' )->item( 0 )->textContent, 'Exact visible heading.' );
	proof_latest_check( 1 === $dom->query( '//section[@aria-labelledby="proof-identity-latest-work-title"]//h2[@id="proof-identity-latest-work-title"]' )->length, 'Section has an accessible heading.' );
	$links = $dom->query( '//li/a[@class="proof-identity-latest-link"]' );
	proof_latest_check( 5 === $links->length && 5 === $dom->query( '//a' )->length && 0 === $dom->query( '//a//a' )->length, 'Exactly one accessible link per list entry, without nested anchors.' );
	foreach ( $expected as $index => $id ) {
		$link = $links->item( $index );
		proof_latest_check( get_permalink( $id ) === $link->getAttribute( 'href' ) && $id === url_to_postid( $link->getAttribute( 'href' ) ), 'Link resolves to its actual published post.' );
		proof_latest_check( get_the_title( $id ) === $dom->query( './/span[@class="proof-identity-latest-title"]', $link )->item( 0 )->textContent, 'Display the published title in query order.' );
	}

	// Inject hostile filtered values at rendering boundaries, after WP storage sanitization.
	$title_filter = function( $title, $id ) use ( $new_id ) { return $new_id === $id ? '<img src=x onerror=alert(1)> " & Title' : $title; };
	$category_filter = function( $terms, $id ) use ( $new_id ) {
		if ( $new_id === $id ) {
			$terms[0] = clone $terms[0];
			$terms[0]->name = '<svg onload=alert(1)> & Category';
		}
		return $terms;
	};
	add_filter( 'the_title', $title_filter, 99, 2 );
	add_filter( 'get_the_categories', $category_filter, 99, 2 );
	kses_remove_filters();
	wp_update_post( wp_slash( array( 'ID' => $new_id, 'post_excerpt' => '<a href="https://example.test/">Readable &amp; useful</a> <form>Discard form</form> [mailpoet_form id="1"] &lt;img src=x onerror=alert(1)&gt;' ) ) );
	kses_init_filters();
	$html = proof_latest_render();
	$dom = proof_latest_dom( $html );
	proof_latest_check( false !== strpos( $html, '&lt;img src=x onerror=alert(1)&gt;' ), 'Unsafe title or decoded excerpt markup is escaped.' );
	proof_latest_check( false !== strpos( $html, '&lt;svg onload=alert(1)&gt;' ), 'Category label is escaped.' );
	proof_latest_check( 0 === $dom->query( '//img|//svg|//script|//form|//*[@onerror or @onload or @onclick]' )->length, 'No injected elements or event attributes.' );
	proof_latest_check( 5 === $dom->query( '//a' )->length, 'Excerpt links become plain text.' );
	proof_latest_check( false === strpos( $html, 'Discard form' ) && false === strpos( $html, '[mailpoet_form' ) && false !== strpos( $html, 'Readable &amp; useful' ), 'Existing clean-excerpt helper removes form and shortcode noise.' );
	remove_filter( 'the_title', $title_filter, 99 );
	remove_filter( 'get_the_categories', $category_filter, 99 );
	$url_filter = function( $url, $post ) use ( $new_id ) { return $new_id === $post->ID ? 'javascript:alert(1)' : $url; };
	add_filter( 'post_link', $url_filter, 99, 2 );
	proof_latest_check( false === strpos( proof_latest_render(), 'javascript:' ), 'Unsafe URL protocols are escaped.' );
	remove_filter( 'post_link', $url_filter, 99 );
	$url_filter = function( $url, $post ) use ( $new_id ) { return $new_id === $post->ID ? $url . '&probe=" onclick="alert(1)' : $url; };
	add_filter( 'post_link', $url_filter, 99, 2 );
	$html = proof_latest_render();
	proof_latest_check( false !== strpos( $html, '&#038;probe=' ) && 0 === proof_latest_dom( $html )->query( '//*[@onclick]' )->length, 'URL query separators and quotes cannot escape href.' );
	remove_filter( 'post_link', $url_filter, 99 );
	wp_update_post( array( 'ID' => $new_id, 'post_excerpt' => implode( ' ', array_fill( 0, 40, 'word' ) ) ) );
	$dom = proof_latest_dom( proof_latest_render() );
	proof_latest_check( implode( ' ', array_fill( 0, 28, 'word' ) ) . '…' === $dom->query( '//span[@class="proof-identity-latest-description"]' )->item( 0 )->textContent, 'Excerpts stay short with a correctly rendered ellipsis.' );

	proof_latest_check( $GLOBALS['post'] === $outer_post && get_the_ID() === $page_id, 'Template restores the outer post and template ID.' );
	proof_latest_check( $GLOBALS['wp_query'] === $outer_query && $GLOBALS['wp_the_query'] === $outer_query && 0 === $outer_query->current_post, 'Secondary loop preserves main query identity and position.' );
	foreach ( $outer_vars as $key => $value ) {
		proof_latest_check( $GLOBALS[ $key ] == $value, 'Restore WordPress loop global: ' . $key );
	}

	require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
	$customizer = new WP_Customize_Manager();
	proof_customize_register( $customizer );
	proof_latest_check( null === $customizer->get_section( 'proof_identity_latest_work' ), 'Retired Customizer section is absent.' );
	foreach ( array_keys( $saved_mods ) as $key ) {
		if ( 0 === strpos( $key, 'proof_identity_latest_work_' ) ) {
			proof_latest_check( null === $customizer->get_setting( $key ) && null === $customizer->get_control( $key ), 'Retired setting and control absent: ' . $key );
		}
	}
	foreach ( array( 'proof_identity_published_books_one_title', 'proof_identity_published_books_two_image_id', 'proof_identity_destination_book_url', 'proof_identity_profile_image_id', 'proof_identity_gallery_image_1_id', 'proof_identity_social_heading' ) as $key ) {
		proof_latest_check( null !== $customizer->get_setting( $key ) && null !== $customizer->get_control( $key ), 'Unrelated Customizer control remains: ' . $key );
	}
	proof_latest_check( 'Legacy one title' === get_theme_mod( 'proof_identity_latest_work_one_title' ), 'Customizer registration preserves stored legacy values.' );
	echo 'PASS: ' . $checks . ' Latest Work integration checks (WordPress ' . $wp_version . ', PHP ' . PHP_VERSION . ").\n";
} finally {
	foreach ( $post_ids as $id ) {
		wp_delete_post( $id, true );
	}
	foreach ( $term_ids as $id ) {
		wp_delete_term( $id, 'category' );
	}
	update_option( 'theme_mods_' . get_option( 'stylesheet' ), $original_mods );
	update_option( 'sticky_posts', $original_sticky );
	wp_set_current_user( $original_user );
	$GLOBALS['wp_query'] = $original_query;
	$GLOBALS['wp_the_query'] = $original_main_query;
	wp_reset_postdata();
	unregister_post_type( 'proof_fixture' );
}
