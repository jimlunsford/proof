<?php
/** Synthetic fixtures, invoked only through the isolated Playground runner. */
if ( ! defined( 'PROOF_SEO_FIXTURE_RUNNER' ) || true !== PROOF_SEO_FIXTURE_RUNNER || ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}
if ( ! defined( 'PROOF_DISPOSABLE_TEST_SITE' ) || true !== PROOF_DISPOSABLE_TEST_SITE || 'local' !== wp_get_environment_type() ) {
	throw new RuntimeException( 'An explicitly disposable local WordPress site is required.' );
}
if ( ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) {
	throw new RuntimeException( 'Fixtures refuse non-loopback sites.' );
}

function proof_seo_fixture_post( $title, $slug, $term, $day ) {
	$id = wp_insert_post( array(
		'post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish',
		'post_content' => '<p>Synthetic regression article. No production content.</p>',
		'post_category' => array( $term ),
		'post_date' => gmdate( 'Y-m-d H:i:s', time() - $day * DAY_IN_SECONDS ),
	), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	return $id;
}

function proof_seo_fixture_seed() {
	if ( get_option( 'proof_seo_fixture' ) || get_posts( array( 'post_type' => 'any', 'post_status' => array_keys( get_post_stati() ), 'numberposts' => 1 ) ) ) {
		throw new RuntimeException( 'Fixture seed requires an empty disposable database.' );
	}
	if ( 'proof' !== get_stylesheet() || '7.1.3' !== $GLOBALS['wp_version'] || '5.2.0' !== THE_SEO_FRAMEWORK_VERSION || '5.10.1' !== REDIRECTION_VERSION ) {
		throw new RuntimeException( 'The active theme or pinned WordPress/plugin versions do not match.' );
	}
	update_option( 'blog_public', 1 );
	$GLOBALS['wp_rewrite']->set_permalink_structure( '/%postname%/' );
	update_option( 'show_on_front', 'posts' );
	update_option( 'posts_per_page', 10 );
	$fixture = array( 'projects' => array(), 'law_recent' => array(), 'notes' => array() );
	foreach ( array( 'projects', 'law-enforcement-corrections', 'notes' ) as $slug ) {
		$term = wp_insert_term( ucwords( str_replace( '-', ' ', $slug ) ), 'category', array( 'slug' => $slug ) );
		if ( is_wp_error( $term ) ) {
			throw new RuntimeException( $term->get_error_message() );
		}
		$fixture['terms'][ $slug ] = $term['term_id'];
	}
	$fixture['page'] = wp_insert_post( array( 'post_title' => 'Projects', 'post_name' => 'projects', 'post_type' => 'page', 'post_status' => 'publish' ), true );
	if ( is_wp_error( $fixture['page'] ) ) {
		throw new RuntimeException( $fixture['page']->get_error_message() );
	}
	for ( $i = 0; $i < 25; $i++ ) {
		$fixture['projects'][] = proof_seo_fixture_post( 'Project fixture ' . $i, 'project-fixture-' . $i, $fixture['terms']['projects'], $i + 1 );
	}
	$fixture['feature'] = proof_seo_fixture_post( 'Why I Loved the Work', 'why-i-loved-the-work', $fixture['terms']['law-enforcement-corrections'], 40 );
	$fixture['notes'][] = proof_seo_fixture_post( 'Unrelated note', 'unrelated-note', $fixture['terms']['notes'], 1 );
	// This exact page-one alias belongs to Redirection, not to Proof.
	$installed = \Redirection\Database\Database::get_latest_database()->install();
	if ( is_wp_error( $installed ) ) {
		throw new RuntimeException( $installed->get_error_message() );
	}
	$group = Red_Group::create( 'Disposable fixture aliases', 1 );
	if ( is_wp_error( $group ) || ! $group ) {
		throw new RuntimeException( 'Could not create the disposable Redirection group.' );
	}
	$redirect = Red_Item::create( array(
		'url' => '/category/projects/', 'regex' => false, 'group_id' => $group->get_id(),
		'match_type' => 'url', 'action_type' => 'url', 'action_code' => 301,
		'action_data' => array( 'url' => '/projects/' ),
	) );
	if ( is_wp_error( $redirect ) ) {
		throw new RuntimeException( $redirect->get_error_message() );
	}
	flush_rewrite_rules( false );
	if ( $fixture['feature'] !== url_to_postid( get_permalink( $fixture['feature'] ) ) ) {
		throw new RuntimeException( 'Fixture permalink routing is not ready.' );
	}
	update_option( 'proof_seo_fixture', $fixture );
	return $fixture;
}

function proof_seo_fixture_add_law_stories() {
	$fixture = get_option( 'proof_seo_fixture' );
	if ( ! $fixture || $fixture['law_recent'] ) {
		throw new RuntimeException( 'Expected the sole-feature fixture state.' );
	}
	for ( $i = 0; $i < 14; $i++ ) {
		$fixture['law_recent'][] = proof_seo_fixture_post( 'Law fixture ' . $i, 'law-fixture-' . $i, $fixture['terms']['law-enforcement-corrections'], $i + 1 );
	}
	update_option( 'proof_seo_fixture', $fixture );
	return $fixture;
}

function proof_seo_fixture_unpublish_feature() {
	$fixture = get_option( 'proof_seo_fixture' );
	$result = wp_update_post( array( 'ID' => $fixture['feature'], 'post_status' => 'draft' ), true );
	if ( is_wp_error( $result ) || 'draft' !== get_post_status( $fixture['feature'] ) ) {
		throw new RuntimeException( 'Could not unpublish the disposable feature.' );
	}
	return $fixture;
}
