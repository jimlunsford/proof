<?php
/**
 * Projects page pagination and SEO routing.
 *
 * The Projects page is a curated static page plus a six-post secondary query.
 * Unlike WordPress's main category queries, The SEO Framework cannot infer
 * its pagination automatically. Keep these exceptions Projects-only.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The current page of the public Projects listing, or 0 outside that page.
 */
function proof_projects_listing_page_number() {
	if ( is_admin() || ! is_page( 'projects' ) ) {
		return 0;
	}

	return max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
}

/**
 * Compute the number of pages from the same published category and page size
 * used by the Projects secondary query.
 */
function proof_projects_listing_page_count() {
	$term = get_category_by_slug( proof_get_projects_writing_category_slug() );

	if ( ! $term instanceof WP_Term ) {
		return 1;
	}

	return max( 1, (int) ceil( (int) $term->count / proof_get_projects_writing_posts_per_page() ) );
}

/**
 * Return the Projects listing page URL (no query strings or fragments).
 */
function proof_projects_listing_page_url( $number ) {
	$base = get_permalink( get_queried_object_id() );

	if ( ! is_string( $base ) || ! $base ) {
		return '';
	}

	$base = trailingslashit( $base );
	$number = absint( $number );

	return $number > 1 ? $base . 'page/' . $number . '/' : $base;
}

/**
 * Only valid, deeper Projects listing pages need a special SEO URL.
 */
function proof_projects_listing_paged_seo_url() {
	$page = proof_projects_listing_page_number();

	if ( $page <= 1 || $page > proof_projects_listing_page_count() ) {
		return '';
	}

	return proof_projects_listing_page_url( $page );
}

/**
 * Keep the existing The SEO Framework canonical and Open Graph generators.
 * Change only their rendered URL values for valid Projects pagination.
 */
function proof_projects_fix_paged_meta_urls( $tags ) {
	$url = proof_projects_listing_paged_seo_url();

	if ( ! $url ) {
		return $tags;
	}

	if ( isset( $tags['canonical']['attributes']['href'] ) ) {
		$tags['canonical']['attributes']['href'] = $url;
	}

	if ( isset( $tags['og:url']['attributes']['content'] ) ) {
		$tags['og:url']['attributes']['content'] = $url;
	}

	return $tags;
}
add_filter( 'the_seo_framework_meta_render_data', 'proof_projects_fix_paged_meta_urls', 20 );

/**
 * Keep The SEO Framework's WebPage graph URL, identity, and ReadAction target
 * in agreement with the canonical of each valid Projects listing page.
 */
function proof_projects_fix_paged_schema_url( $graph, $args ) {
	if ( null !== $args ) {
		return $graph;
	}

	$url = proof_projects_listing_paged_seo_url();

	if ( ! $url ) {
		return $graph;
	}

	foreach ( $graph as &$entity ) {
		if ( ! in_array( 'WebPage', (array) ( $entity['@type'] ?? array() ), true ) ) {
			continue;
		}

		$prior_url = $entity['@id'] ?? '';
		$entity['@id'] = $url;
		$entity['url'] = $url;

		if ( isset( $entity['potentialAction']['target'] ) && $entity['potentialAction']['target'] === $prior_url ) {
			$entity['potentialAction']['target'] = $url;
		}
		break;
	}
	unset( $entity );

	return $graph;
}
add_filter( 'the_seo_framework_schema_graph_data', 'proof_projects_fix_paged_schema_url', 20, 2 );

/**
 * Prevent empty custom Projects pages from being indexed as valid page views.
 */
function proof_projects_reject_out_of_range_pagination() {
	$page = proof_projects_listing_page_number();

	if ( $page <= 1 || $page <= proof_projects_listing_page_count() ) {
		return;
	}

	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();

	// A nonexistent Projects page must not be guessed back into a redirect.
	remove_action( 'template_redirect', 'redirect_canonical' );
}
add_action( 'template_redirect', 'proof_projects_reject_out_of_range_pagination', 1 );

/**
 * A legacy category page holds 12 posts, whereas a Projects page holds 6.
 * Map legacy page 2 to Projects page 3, page 3 to Projects page 5, etc.
 * Each destination begins with the same article as the old category page.
 */
function proof_projects_legacy_category_page_target( $page ) {
	$page = absint( $page );
	if ( $page <= 1 ) {
		return '';
	}

	$first_post_offset = ( $page - 1 ) * 12;
	$projects_page = 1 + (int) floor( $first_post_offset / proof_get_projects_writing_posts_per_page() );

	return home_url( '/projects/page/' . $projects_page . '/' );
}

/**
 * Keep the existing Redirection plugin rule for category page one.
 * Redirect only real, deeper legacy category pages to the custom listing.
 */
function proof_projects_redirect_legacy_category_pagination() {
	if ( is_admin() || is_404() || ! is_category( 'projects' ) ) {
		return;
	}

	$target = proof_projects_legacy_category_page_target( get_query_var( 'paged' ) );

	if ( $target ) {
		wp_safe_redirect( $target, 301, 'Proof Projects pagination' );
		exit;
	}
}
add_action( 'template_redirect', 'proof_projects_redirect_legacy_category_pagination', 1 );
