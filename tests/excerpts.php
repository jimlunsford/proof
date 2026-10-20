<?php
/**
 * Real-WordPress excerpt regressions. Empty disposable local sites only.
 * Run with PROOF_TEST_WP_ROOT, as documented in README.md.
 */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}
$wordpress_root = getenv( 'PROOF_TEST_WP_ROOT' );
if ( ! $wordpress_root || ! is_readable( $wordpress_root . '/wp-load.php' ) ) {
	throw new RuntimeException( 'Set PROOF_TEST_WP_ROOT to an empty disposable WordPress install.' );
}
require $wordpress_root . '/wp-load.php';
if ( ! defined( 'PROOF_DISPOSABLE_TEST_SITE' ) || true !== PROOF_DISPOSABLE_TEST_SITE || 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'Refusing fixture writes without an explicitly disposable local test site.' );
}
if ( realpath( get_template_directory() ) !== realpath( dirname( __DIR__ ) ) ) {
	throw new RuntimeException( 'Activate this Proof checkout before running its tests.' );
}
if ( get_posts( array( 'post_type' => 'any', 'post_status' => array_keys( get_post_stati() ), 'numberposts' => 1 ) ) ) {
	throw new RuntimeException( 'Refusing to run with existing content.' );
}

$checks = 0;
$post_ids = array();
$term_ids = array();
$original_query = $GLOBALS['wp_query'];
$original_main_query = $GLOBALS['wp_the_query'];
$original_user = get_current_user_id();
$cookie_name = 'wp-postpass_' . COOKIEHASH;
$original_cookie = isset( $_COOKIE[ $cookie_name ] ) ? $_COOKIE[ $cookie_name ] : null;

function proof_excerpt_check( $condition, $message ) {
	global $checks;
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	$checks++;
}

function proof_excerpt_fixture( $args ) {
	global $post_ids;
	$id = wp_insert_post( wp_slash( array_merge( array(
		'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Excerpt fixture',
		'post_date' => gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ),
	), $args ) ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	$post_ids[] = $id;
	return $id;
}

function proof_excerpt_render( $path, $query_args, $vars = array() ) {
	global $post;
	$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'] = new WP_Query( $query_args );
	$post = $GLOBALS['wp_query']->post;
	if ( $post ) {
		setup_postdata( $post );
	}
	foreach ( $vars as $key => $value ) {
		set_query_var( $key, $value );
	}
	ob_start();
	include dirname( __DIR__ ) . '/' . $path;
	return ob_get_clean();
}

function proof_excerpt_paragraph( $text ) {
	return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
}

function proof_excerpt_group( $children ) {
	return '<!-- wp:group --><div class="wp-block-group">' . implode( "\n", $children ) . '</div><!-- /wp:group -->';
}

try {
	wp_set_current_user( 0 );
	foreach ( array( 'projects', 'excerpt-checks' ) as $slug ) {
		$term = wp_insert_term( $slug, 'category', array( 'slug' => $slug ) );
		if ( is_wp_error( $term ) ) {
			throw new RuntimeException( $term->get_error_message() );
		}
		$term_ids[] = $term['term_id'];
	}
	$protected = proof_excerpt_fixture( array(
		'post_name' => 'how-to-rebuild-your-identity-after-addiction',
		'post_content' => '<p>PROTECTED_BODY useful private narrative.</p>',
		'post_password' => 'disposable-test-password', 'post_category' => $term_ids,
	) );
	$public = proof_excerpt_fixture( array(
		'post_name' => 'identity-after-rock-bottom', 'post_category' => $term_ids,
		'post_content' => '<p>PUBLIC_BODY ordinary public narrative.</p>',
		'post_excerpt' => 'PUBLIC_EXCERPT ordinary public summary.',
	) );
	update_post_meta( $protected, 'proof_deck', 'PROTECTED_DECK private introduction.' );
	update_post_meta( $protected, 'proof_sources_support', 'PROTECTED_SOURCES private references.' );
	update_post_meta( $public, 'proof_deck', 'PUBLIC_DECK public introduction.' );
	update_post_meta( $public, 'proof_sources_support', 'PUBLIC_SOURCES public references.' );
	$projects_page = proof_excerpt_fixture( array( 'post_type' => 'page', 'post_name' => 'projects', 'post_title' => 'Projects' ) );
	$hub_page = proof_excerpt_fixture( array( 'post_type' => 'page', 'post_name' => 'identity-rebuild-after-addiction' ) );
	$posts_query = array( 'post_type' => 'post', 'post_status' => 'publish', 'post__in' => array( $protected, $public ) );
	$surfaces = array(
		'Latest Work' => array( 'template-parts/home/latest-work.php', array( 'page_id' => $projects_page ) ),
		'category' => array( 'category.php', array( 'category_name' => 'excerpt-checks' ) ),
		'Projects' => array( 'page-projects.php', array( 'page_id' => $projects_page ) ),
		'generic archive' => array( 'archive.php', $posts_query ),
		'search' => array( 'search.php', array( 's' => 'Excerpt fixture', 'post_type' => 'post' ) ),
		'curated featured cards' => array( 'template-parts/archive/featured-posts.php', $posts_query, array( 'proof_featured_ids' => array( $protected, $public ) ) ),
		'topic hub cards' => array( 'templates/template-topic-hub.php', array( 'page_id' => $hub_page ) ),
		'Recent Published Work' => array( 'template-parts/home/latest-writing.php', $posts_query, array( 'proof_latest_query' => new WP_Query( $posts_query ) ) ),
	);
	require_once ABSPATH . WPINC . '/class-phpass.php';
	$hasher = new PasswordHash( 8, true ); // The same cookie format used by wp-login.php's postpass action.
	$cookies = array( 'missing' => null, 'wrong' => $hasher->HashPassword( 'wrong-password' ), 'valid' => $hasher->HashPassword( 'disposable-test-password' ) );
	foreach ( array( 'body' => '', 'manual' => 'PROTECTED_EXCERPT private manual summary.' ) as $source => $excerpt ) {
		wp_update_post( array( 'ID' => $protected, 'post_excerpt' => $excerpt ) );
		$marker = 'manual' === $source ? 'PROTECTED_EXCERPT' : 'PROTECTED_BODY';
		foreach ( $cookies as $access => $cookie ) {
			unset( $_COOKIE[ $cookie_name ] );
			if ( null !== $cookie ) {
				$_COOKIE[ $cookie_name ] = $cookie;
			}
			$allowed = 'valid' === $access;
			proof_excerpt_check( $allowed === ! post_password_required( $protected ), "$source/$access native password state" );
			proof_excerpt_check( $allowed ? false !== strpos( proof_get_clean_excerpt( $protected ), $marker ) : '' === proof_get_clean_excerpt( $protected ), "$source/$access helper respects native access" );
			foreach ( $surfaces as $name => $surface ) {
				$html = proof_excerpt_render( $surface[0], $surface[1], isset( $surface[2] ) ? $surface[2] : array() );
				proof_excerpt_check( false !== strpos( $html, 'PUBLIC_EXCERPT' ), "$source/$access/$name retains the public summary" );
				// WordPress deliberately excludes protected posts from anonymous search,
				// even with a valid post password. Leave that native selection unchanged.
				if ( $allowed && 'search' !== $name ) {
					proof_excerpt_check( false !== strpos( $html, $marker ), "$source/$access/$name retains authorized summary" );
				} else {
					proof_excerpt_check( false === strpos( $html, 'PROTECTED_BODY' ) && false === strpos( $html, 'PROTECTED_EXCERPT' ), "$source/$access/$name has no protected summary" );
				}
			}
			$html = proof_excerpt_render( 'single.php', array( 'p' => $protected ) );
			foreach ( array( 'PROTECTED_BODY', 'PROTECTED_DECK', 'PROTECTED_SOURCES' ) as $private_marker ) {
				proof_excerpt_check( $allowed === ( false !== strpos( $html, $private_marker ) ), "$source/$access/single protects $private_marker" );
			}
			$html = proof_excerpt_render( 'single.php', array( 'p' => $public ) );
			foreach ( array( 'PUBLIC_BODY', 'PUBLIC_DECK', 'PUBLIC_SOURCES' ) as $public_marker ) {
				proof_excerpt_check( false !== strpos( $html, $public_marker ), "$source/$access/single retains $public_marker" );
			}
		}
	}

	// Make every serialization warning fail the nested-block checks.
	set_error_handler( function( $severity, $message, $file, $line ) {
		throw new ErrorException( $message, 0, $severity, $file, $line );
	} );
	try {
		$narrative = 'I built a newsletter archive to make older published writing easier to find.';
		$keep = proof_excerpt_paragraph( $narrative );
		$cta = proof_excerpt_paragraph( 'Get the Work' );
		foreach ( array( array( $cta, $keep ), array( $keep, $cta ), array( $keep, $cta, proof_excerpt_paragraph( 'A useful ending.' ) ) ) as $index => $children ) {
			$text = proof_clean_excerpt_source( proof_excerpt_group( $children ) );
			proof_excerpt_check( false !== strpos( $text, $narrative ) && false === strpos( $text, 'Get the Work' ), "Nested child removal at position $index preserves narrative without warnings" );
		}
		$nested = proof_excerpt_group( array( $cta, '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column">' . proof_excerpt_group( array( $cta, $keep, $cta ) ) . '</div><!-- /wp:column --></div><!-- /wp:columns -->', $cta ) );
		proof_excerpt_check( $narrative === proof_clean_excerpt_source( $nested ), 'Deep groups and columns retain narrative without serialization warnings' );
		proof_excerpt_check( '' === proof_clean_excerpt_source( proof_excerpt_group( array( $cta, proof_excerpt_group( array( $cta ) ) ) ) ), 'All-noise nested groups become empty without warnings' );
		foreach ( array( $narrative, 'The newsletter records an email address only when a reader subscribes.', 'I used Get the Work as the title of an earlier project.' ) as $text ) {
			proof_excerpt_check( $text === proof_clean_excerpt_source( proof_excerpt_group( array( proof_excerpt_paragraph( $text ) ) ) ), 'Ordinary narrative is not classified by newsletter or CTA keywords' );
		}
		foreach ( array( 'jetpack/subscriptions', 'mailpoet/subscription-form', 'wpforms/form-selector' ) as $block_name ) {
			$form = '<!-- wp:' . $block_name . ' /-->';
			proof_excerpt_check( $narrative === proof_clean_excerpt_source( proof_excerpt_group( array( $form, $keep, $form ) ) ), "Actual $block_name blocks are removed without dropping narrative" );
		}
		$html_form = '<!-- wp:html --><p>Useful opening.</p><form><label>Email Address</label><button>FORM_SENTINEL</button></form><p>Useful ending.</p><!-- /wp:html -->';
		$text = proof_clean_excerpt_source( proof_excerpt_group( array( $html_form ) ) );
		proof_excerpt_check( false !== strpos( $text, 'Useful opening.' ) && false !== strpos( $text, 'Useful ending.' ) && false === strpos( $text, 'FORM_SENTINEL' ), 'Mixed HTML preserves narrative while excluding the actual form' );
		$combined_cta = "<!-- wp:html -->\n<p>I built a newsletter archive to make older writing easier to find.</p>\n<h2>Get the Work</h2>\n<p>Articles on discipline, recovery, identity, and ownership.</p>\n<p>Delivered when published.</p>\n<form><label>Email Address</label><button>Subscribe</button></form>\n<p>A useful narrative ending.</p>\n<!-- /wp:html -->";
		proof_excerpt_check( 'I built a newsletter archive to make older writing easier to find. A useful narrative ending.' === proof_clean_excerpt_source( $combined_cta ), 'Combined HTML removes the exact known subscription CTA and form while preserving both narrative paragraphs' );
		proof_excerpt_check( 'I built a newsletter archive to make older writing easier to find. A useful narrative ending.' === proof_clean_excerpt_source( str_replace( "\n", '', $combined_cta ) ), 'Compact HTML removes the same exact CTA without relying on whitespace between block tags' );
		$cta_group = proof_excerpt_group( array( $keep, $cta, proof_excerpt_paragraph( 'Articles on discipline, recovery, identity, and ownership.' ), proof_excerpt_paragraph( 'Delivered when published.' ), proof_excerpt_paragraph( 'Email Address' ), proof_excerpt_paragraph( 'Subscribe' ) ) );
		proof_excerpt_check( $narrative === proof_clean_excerpt_source( $cta_group ), 'Existing subscription CTA pieces remain excluded' );
		foreach ( array( 'Subscribe to my newsletter', 'Sign up for newsletter' ) as $label ) {
			$button = '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link">' . $label . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->';
			proof_excerpt_check( $narrative === proof_clean_excerpt_source( proof_excerpt_group( array( $keep, $button ) ) ), "Subscription button $label is excluded beside narrative" );
		}
	} finally {
		restore_error_handler();
	}

	$shortcode_calls = 0;
	add_shortcode( 'proof_excerpt_probe', function() use ( &$shortcode_calls ) { $shortcode_calls++; return 'EXECUTED_SHORTCODE'; } );
	try {
		$text = proof_clean_excerpt_source( proof_excerpt_group( array( proof_excerpt_paragraph( 'Useful narrative.' ), '<!-- wp:shortcode -->[proof_excerpt_probe]<!-- /wp:shortcode -->', '<!-- wp:shortcode -->[mailpoet_form id="1"]<!-- /wp:shortcode -->' ) ) );
		proof_excerpt_check( 0 === $shortcode_calls && 'Useful narrative.' === $text, 'Excerpt cleanup strips shortcodes without executing them' );
	} finally {
		remove_shortcode( 'proof_excerpt_probe' );
	}
	wp_update_post( array( 'ID' => $public, 'post_excerpt' => '', 'post_content' => proof_excerpt_group( array( $keep, $cta ) ) ) );
	$stored = get_post_field( 'post_content', $public );
	proof_excerpt_check( $narrative === proof_get_clean_excerpt( $public ), 'Public body excerpt survives nested cleanup' );
	proof_excerpt_check( $stored === get_post_field( 'post_content', $public ), 'Generating excerpts never rewrites stored blocks' );
	wp_update_post( array( 'ID' => $public, 'post_excerpt' => '[mailpoet_form id="1"]' ) );
	proof_excerpt_check( $narrative === proof_get_clean_excerpt( $public ), 'Noise-only manual excerpt falls back to the public body' );
	wp_update_post( array( 'ID' => $public, 'post_excerpt' => implode( ' ', array_fill( 0, 40, 'word' ) ) ) );
	proof_excerpt_check( implode( ' ', array_fill( 0, 28, 'word' ) ) . '&hellip;' === proof_get_clean_excerpt( $public ), 'Default 28-word limit is unchanged' );
	proof_excerpt_check( 'word word word&hellip;' === proof_get_clean_excerpt( $public, 3 ), 'Caller word limit is unchanged' );
	wp_update_post( array( 'ID' => $public, 'post_excerpt' => 'Readable &amp; useful &lt;img src=x onerror=alert(1)&gt;' ) );
	proof_excerpt_check( 'Readable & useful' === proof_get_clean_excerpt( $public ), 'Native word trimming removes decoded markup before presentation' );
	foreach ( array( 'template-parts/home/latest-work.php', 'template-parts/archive/post-feed.php' ) as $path ) {
		$html = proof_excerpt_render( $path, array( 'p' => $public ) );
		proof_excerpt_check( false !== strpos( $html, 'Readable &amp; useful' ) && false === strpos( $html, '<img src=x' ) && false === strpos( $html, 'onerror' ), "$path preserves escaped public presentation" );
	}
	echo 'PASS: ' . $checks . ' excerpt integration checks (WordPress ' . $wp_version . ', PHP ' . PHP_VERSION . ").\n";
} finally {
	foreach ( $post_ids as $id ) {
		wp_delete_post( $id, true );
	}
	foreach ( $term_ids as $id ) {
		wp_delete_term( $id, 'category' );
	}
	if ( null === $original_cookie ) {
		unset( $_COOKIE[ $cookie_name ] );
	} else {
		$_COOKIE[ $cookie_name ] = $original_cookie;
	}
	wp_set_current_user( $original_user );
	$GLOBALS['wp_query'] = $original_query;
	$GLOBALS['wp_the_query'] = $original_main_query;
	wp_reset_postdata();
}
