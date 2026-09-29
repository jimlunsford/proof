<?php
/**
 * Helper functions for the Proof theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function proof_get_editorial_note() {
	$default = 'This site holds experience-based writing, projects, lessons, and tools from a rebuilt life. It is personal and educational, not medical or clinical care.';
	return trim( get_theme_mod( 'proof_editorial_note', $default ) );
}

function proof_get_how_written_url() {
	return home_url( '/how-this-content-is-written/' );
}

/**
 * Determine which editorial note, if any, belongs on a single post.
 *
 * Existing site taxonomy remains the primary signal. Project writing and
 * Builder Receipts intentionally receive no recovery boilerplate. Recovery
 * lanes receive the recovery safety note. Articles use a narrow post-name
 * fallback so clearly recovery- or health-adjacent long-form pieces keep the
 * appropriate safety language even when their lane is simply "Articles".
 *
 * @param int $post_id Post ID.
 * @return string One of: recovery, general, none.
 */
function proof_get_about_writing_context( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	if ( ! $post_id ) {
		return 'general';
	}

	$post_name = (string) get_post_field( 'post_name', $post_id );

	if ( 0 === strpos( $post_name, 'builder-receipt-' ) ) {
		return 'none';
	}

	$category_slugs = array();
	$categories     = get_the_category( $post_id );

	foreach ( $categories as $category ) {
		if ( $category instanceof WP_Term && ! empty( $category->slug ) ) {
			$category_slugs[] = $category->slug;
		}
	}

	$project_slug = proof_get_projects_writing_category_slug();

	if ( $project_slug && in_array( $project_slug, $category_slugs, true ) ) {
		return 'none';
	}

	foreach ( $category_slugs as $category_slug ) {
		if ( 'recovery' === $category_slug || 0 === strpos( $category_slug, 'recovery-' ) ) {
			return 'recovery';
		}
	}

	$recovery_terms = array(
		'recovery',
		'addiction',
		'sobriety',
		'sober',
		'relapse',
		'mental-health',
		'depression',
		'anxiety',
		'trauma',
	);

	if ( has_tag( $recovery_terms, $post_id ) ) {
		return 'recovery';
	}

	if ( in_array( 'articles', $category_slugs, true ) || in_array( 'notes', $category_slugs, true ) ) {
		foreach ( $recovery_terms as $term ) {
			if ( false !== strpos( $post_name, $term ) ) {
				return 'recovery';
			}
		}
	}

	return 'general';
}

function proof_get_about_url() {
	return home_url( '/about-jim-lunsford-discipline/' );
}

function proof_get_the_work_url() {
	return home_url( '/the-work/' );
}

function proof_get_projects_url() {
	return home_url( '/projects/' );
}

function proof_get_microblog_url() {
	return 'https://jimlunsford.net';
}

function proof_get_projects_description() {
	$default = 'Software, tools, websites, and systems I am building around ownership, structure, practical use, and real problems.';
	return trim( get_theme_mod( 'proof_projects_description', $default ) );
}

function proof_get_projects_start_here_url() {
	$default = proof_get_about_url();
	return esc_url( get_theme_mod( 'proof_projects_start_here_url', $default ) );
}

function proof_get_project_card_defaults() {
	return array(
		'bonumark_stream' => array(
			'enabled'     => true,
			'title'       => 'Bonumark Stream',
			'description' => 'A microblog and short-form publishing system for people who want their thoughts, updates, photos, and notes on a site they control instead of trapped inside social networks.',
			'link_label'  => '',
			'url'         => '',
		),
		'carceris' => array(
			'enabled'     => true,
			'title'       => 'Carceris',
			'description' => 'A corrections-focused tool shaped by real jail experience and practical facility needs. It is being built for clear records, usable workflows, accountability, and pressure-tested use.',
			'link_label'  => '',
			'url'         => '',
		),
		'bonumark' => array(
			'enabled'     => false,
			'title'       => 'Bonumark',
			'description' => 'A portable publishing system built around Markdown, clean content, ownership, and shared-hosting usability. The goal is clean publishing without unnecessary dependence.',
			'link_label'  => '',
			'url'         => '',
		),
		'capsarium' => array(
			'enabled'     => false,
			'title'       => 'Capsarium',
			'description' => 'A configurable, people-centered record and note system for clients, inmates, coaching contacts, referrals, and other human-centered work.',
			'link_label'  => '',
			'url'         => '',
		),
	);
}

function proof_get_project_cards() {
	$cards = proof_get_project_card_defaults();

	foreach ( $cards as $card_key => &$card ) {
		$card['enabled']     = (bool) get_theme_mod( 'proof_project_' . $card_key . '_enabled', $card['enabled'] );
		$card['title']       = trim( get_theme_mod( 'proof_project_' . $card_key . '_title', $card['title'] ) );
		$card['description'] = trim( get_theme_mod( 'proof_project_' . $card_key . '_description', $card['description'] ) );
		$card['link_label']  = trim( get_theme_mod( 'proof_project_' . $card_key . '_link_label', $card['link_label'] ) );
		$card['url']         = esc_url( get_theme_mod( 'proof_project_' . $card_key . '_url', $card['url'] ) );
	}

	unset( $card );

	return $cards;
}


function proof_get_projects_writing_heading() {
	$default = 'Project Writing';
	return trim( get_theme_mod( 'proof_projects_writing_heading', $default ) );
}
function proof_get_projects_writing_category_slug() {
	$slug = sanitize_title( get_theme_mod( 'proof_projects_writing_category_slug', 'projects' ) );

	return $slug ? $slug : 'projects';
}

function proof_get_projects_writing_posts_per_page() {
	return 6;
}

function proof_get_projects_writing_current_page() {
	return max(
		1,
		absint( get_query_var( 'paged' ) ),
		absint( get_query_var( 'page' ) )
	);
}

function proof_get_projects_writing_query() {
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => proof_get_projects_writing_posts_per_page(),
			'category_name'       => proof_get_projects_writing_category_slug(),
			'paged'               => proof_get_projects_writing_current_page(),
			'ignore_sticky_posts' => true,
		)
	);
}

function proof_get_projects_writing_archive_url() {
	$term = get_category_by_slug( proof_get_projects_writing_category_slug() );

	if ( $term instanceof WP_Term ) {
		return get_category_link( $term );
	}

	return '';
}

function proof_get_the_work_description() {
	$default = 'A guide to the writing, frameworks, recovery perspective, discipline, standards, and lessons that shape the work on this site.';
	return trim( get_theme_mod( 'proof_the_work_description', $default ) );
}

function proof_get_the_work_start_here_url() {
	$default = proof_get_post_url_by_path( 'start-here-raise-your-standards' );
	return esc_url( get_theme_mod( 'proof_the_work_start_here_url', $default ) );
}

function proof_get_subscribe_url() {
	return home_url( '/subscribe/' );
}

function proof_get_contact_url() {
	return home_url( '/contact-jim-lunsford/' );
}

function proof_get_identity_name() {
	$default = 'Jim Lunsford';
	return trim( get_theme_mod( 'proof_identity_name', $default ) );
}

function proof_get_identity_line() {
	$default = 'I build tools, write when something is worth saying, and share work from a life I had to rebuild.';
	return trim( get_theme_mod( 'proof_identity_line', $default ) );
}

function proof_get_identity_bio() {
	$default = 'My work comes from recovery, discipline, ownership, fatherhood, fitness, night shifts, software projects, and the daily choice to keep becoming the man I said I was going to be.';
	return trim( get_theme_mod( 'proof_identity_bio', $default ) );
}

function proof_get_identity_primary_button_label() {
	$default = 'Visit the microblog';
	return trim( get_theme_mod( 'proof_identity_primary_button_label', $default ) );
}

function proof_get_identity_primary_button_url() {
	$default = proof_get_microblog_url();
	return esc_url( get_theme_mod( 'proof_identity_primary_button_url', $default ) );
}

function proof_get_identity_secondary_button_label() {
	$default = 'Contact';
	return trim( get_theme_mod( 'proof_identity_secondary_button_label', $default ) );
}

function proof_get_identity_secondary_button_url() {
	$default = proof_get_contact_url();
	return esc_url( get_theme_mod( 'proof_identity_secondary_button_url', $default ) );
}

/**
 * Return optimized front-page identity avatar markup.
 *
 * Proof keeps the Customizer-selected attachment as the source of truth, then
 * creates a few small lossy WebP derivatives in the uploads directory. This
 * avoids serving a heavy lossless source file for a portrait that is rendered
 * at a small size while preserving the ability to change the image later.
 *
 * @param int $attachment_id Image attachment ID.
 * @return string Image markup.
 */
function proof_get_identity_avatar_image( $attachment_id ) {
	$attachment_id = absint( $attachment_id );

	if ( ! $attachment_id ) {
		return '';
	}

	$sizes = '(max-width: 420px) 46vw, (max-width: 720px) 188px, 140px';
	$alt   = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

	if ( '' === $alt ) {
		$alt = proof_get_identity_name();
	}

	$attrs = array(
		'class'         => 'proof-identity-avatar-image',
		'alt'           => $alt,
		'loading'       => 'eager',
		'decoding'      => 'async',
		'fetchpriority' => 'high',
		'sizes'         => $sizes,
	);

	$fallback = wp_get_attachment_image( $attachment_id, 'medium', false, $attrs );
	$mime     = get_post_mime_type( $attachment_id );

	if ( ! in_array( $mime, array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
		return $fallback;
	}

	$source_path = get_attached_file( $attachment_id );

	if ( ! $source_path || ! is_readable( $source_path ) ) {
		return $fallback;
	}

	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
		return $fallback;
	}

	$cache_dir = trailingslashit( $uploads['basedir'] ) . 'proof-cache';
	$cache_url = trailingslashit( $uploads['baseurl'] ) . 'proof-cache';

	if ( ! is_dir( $cache_dir ) && ! wp_mkdir_p( $cache_dir ) ) {
		return $fallback;
	}

	$source_mtime = filemtime( $source_path );
	$source_mtime = $source_mtime ? (int) $source_mtime : 0;
	$cache_key    = 'proof-avatar-v1-' . $attachment_id . '-' . $source_mtime;
	$targets      = array( 160, 200, 400 );
	$generated    = array();

	foreach ( $targets as $size ) {
		$filename = $cache_key . '-' . $size . '.webp';
		$path     = trailingslashit( $cache_dir ) . $filename;
		$url      = trailingslashit( $cache_url ) . $filename;

		if ( ! file_exists( $path ) ) {
			$editor = wp_get_image_editor( $source_path );

			if ( is_wp_error( $editor ) ) {
				continue;
			}

			$resized = $editor->resize( $size, $size, true );

			if ( is_wp_error( $resized ) ) {
				continue;
			}

			$quality = $editor->set_quality( 75 );

			if ( is_wp_error( $quality ) ) {
				continue;
			}

			$saved = $editor->save( $path, 'image/webp' );

			if ( is_wp_error( $saved ) ) {
				continue;
			}
		}

		if ( file_exists( $path ) ) {
			$generated[ $size ] = $url;
		}
	}

	if ( empty( $generated[160] ) || empty( $generated[200] ) || empty( $generated[400] ) ) {
		return $fallback;
	}

	return sprintf(
		'<img src="%1$s" srcset="%2$s 160w, %3$s 200w, %4$s 400w" sizes="%5$s" width="200" height="200" class="proof-identity-avatar-image" alt="%6$s" loading="eager" decoding="async" fetchpriority="high">',
		esc_url( $generated[200] ),
		esc_url( $generated[160] ),
		esc_url( $generated[200] ),
		esc_url( $generated[400] ),
		esc_attr( $sizes ),
		esc_attr( $alt )
	);
}

function proof_get_identity_profile_image_id() {
	$image_id = absint( get_theme_mod( 'proof_identity_profile_image_id', 0 ) );

	if ( $image_id ) {
		return $image_id;
	}

	$image_id = absint( get_theme_mod( 'proof_author_block_image_id', 0 ) );

	if ( $image_id ) {
		return $image_id;
	}

	return absint( get_theme_mod( 'custom_logo', 0 ) );
}

function proof_get_identity_destinations_heading() {
	$default = 'Start Here';
	return trim( get_theme_mod( 'proof_identity_destinations_heading', $default ) );
}

function proof_get_articles_url() {
	return home_url( '/articles/' );
}

function proof_get_core_frameworks_url() {
	return home_url( '/core-frameworks/' );
}

function proof_get_identity_destination_defaults() {
	return array(
		'microblog'          => array(
			'title'       => 'Microblog',
			'description' => 'Short posts, project updates, photos, training notes, cats, code progress, and the parts of life that do not need to become full articles.',
			'url'         => proof_get_microblog_url(),
		),
		'the_work'           => array(
			'title'       => 'The Work',
			'description' => 'The bigger picture behind what I write, build, teach, and publish.',
			'url'         => proof_get_the_work_url(),
		),
		'articles'           => array(
			'title'       => 'Articles',
			'description' => 'Longform writing on discipline, recovery, ownership, resilience, standards, identity, and rebuilding a life you do not want to escape.',
			'url'         => proof_get_articles_url(),
		),
		'projects'           => array(
			'title'       => 'Projects',
			'description' => 'Software, websites, tools, and systems I am building around ownership, portability, structure, and practical use.',
			'url'         => proof_get_projects_url(),
		),
		'core_frameworks'    => array(
			'title'       => 'Core Frameworks',
			'description' => 'The ideas behind the work: higher standards, discipline, self-trust, identity rebuild, ownership, and recovery without dependency.',
			'url'         => proof_get_core_frameworks_url(),
		),
		'about'              => array(
			'title'       => 'About',
			'description' => 'The longer version of who I am, what shaped me, and why this work exists.',
			'url'         => proof_get_about_url(),
		),
		'contact'            => array(
			'title'       => 'Contact',
			'description' => 'Reach out if something I am building, writing, or sharing connects with you.',
			'url'         => proof_get_contact_url(),
		),
		'recovery_beyond_aa' => array(
			'title'       => 'Recovery Beyond AA',
			'description' => 'Writing for people who want recovery rooted in ownership, autonomy, standards, and self-governance.',
			'url'         => '',
		),
		'book'               => array(
			'title'       => 'Book',
			'description' => 'Ten Things I’ve Learned in 10 Years of Sobriety.',
			'url'         => '',
		),
	);
}

function proof_get_identity_destination_links() {
	$links = proof_get_identity_destination_defaults();

	foreach ( $links as $link_key => &$link ) {
		$link['title']       = trim( get_theme_mod( 'proof_identity_destination_' . $link_key . '_title', $link['title'] ) );
		$link['description'] = trim( get_theme_mod( 'proof_identity_destination_' . $link_key . '_description', $link['description'] ) );
		$link['url']         = esc_url( get_theme_mod( 'proof_identity_destination_' . $link_key . '_url', $link['url'] ) );
	}

	unset( $link );

	return array_values(
		array_filter(
			$links,
			function( $link ) {
				return ! empty( $link['title'] ) && ! empty( $link['url'] );
			}
		)
	);
}

function proof_get_identity_latest_work_heading() {
	$default = 'Selected Work';
	return trim( get_theme_mod( 'proof_identity_latest_work_heading', $default ) );
}

function proof_get_identity_latest_work_defaults() {
	return array(
		'one' => array(
			'label'       => 'Project',
			'title'       => 'Bonumark Stream v0.6.0 Is Here',
			'description' => 'Bonumark Stream v0.6.0 adds public profiles, Theme Architecture 2.0, and a rebuilt Midnight Ledger theme for richer self-hosted publishing.',
			'url'         => home_url( '/bonumark-stream-v0-6-0-is-here/' ),
		),
		'two' => array(
			'label'       => 'Builder Receipt',
			'title'       => 'Builder Receipt: Bonumark’s Theme Architecture',
			'description' => 'How Bonumark Stream’s theme architecture evolved while Core kept ownership of behavior, security, data, publishing, and rendering.',
			'url'         => home_url( '/builder-receipt-bonumarks-theme-architecture/' ),
		),
		'three' => array(
			'label'       => 'Recovery Standard',
			'title'       => 'Recovery Standard: Strong Boundaries',
			'description' => 'A better life requires stronger boundaries around time, energy, sleep, routines, standards, and recovery.',
			'url'         => home_url( '/recovery-standard-strong-boundaries/' ),
		),
	);
}

function proof_get_identity_latest_work_items() {
	$items = proof_get_identity_latest_work_defaults();

	foreach ( $items as $item_key => &$item ) {
		$item['label']       = trim( get_theme_mod( 'proof_identity_latest_work_' . $item_key . '_label', $item['label'] ) );
		$item['title']       = trim( get_theme_mod( 'proof_identity_latest_work_' . $item_key . '_title', $item['title'] ) );
		$item['description'] = trim( get_theme_mod( 'proof_identity_latest_work_' . $item_key . '_description', $item['description'] ) );
		$item['url']         = esc_url( get_theme_mod( 'proof_identity_latest_work_' . $item_key . '_url', $item['url'] ) );
	}

	unset( $item );

	return array_values(
		array_filter(
			$items,
			function( $item ) {
				return ! empty( $item['title'] ) && ! empty( $item['url'] );
			}
		)
	);
}

function proof_get_identity_gallery_image_ids() {
	$image_ids = array();

	for ( $index = 1; $index <= 4; $index++ ) {
		$image_id = absint( get_theme_mod( 'proof_identity_gallery_image_' . $index . '_id', 0 ) );

		if ( $image_id ) {
			$image_ids[] = $image_id;
		}
	}

	return $image_ids;
}

function proof_get_identity_social_heading() {
	$default = 'Follow and Connect';
	return trim( get_theme_mod( 'proof_identity_social_heading', $default ) );
}

function proof_get_identity_social_defaults() {
	return array(
		'microblog' => array(
			'label' => 'Microblog',
			'url'   => proof_get_microblog_url(),
		),
		'x'         => array(
			'label' => 'X',
			'url'   => 'https://x.com/jimlunsford',
		),
		'github'    => array(
			'label' => 'GitHub',
			'url'   => 'https://github.com/jimlunsford',
		),
		'linkedin'  => array(
			'label' => 'LinkedIn',
			'url'   => 'https://linkedin.com/in/jim-lunsford-63421a6',
		),
		'contact'   => array(
			'label' => 'Contact',
			'url'   => proof_get_contact_url(),
		),
		'facebook'  => array(
			'label' => 'Facebook',
			'url'   => '',
		),
	);
}

function proof_get_identity_social_links() {
	$links = proof_get_identity_social_defaults();

	foreach ( $links as $link_key => &$link ) {
		$link['label'] = trim( get_theme_mod( 'proof_identity_social_' . $link_key . '_label', $link['label'] ) );
		$link['url']   = esc_url( get_theme_mod( 'proof_identity_social_' . $link_key . '_url', $link['url'] ) );
	}

	unset( $link );

	return array_values(
		array_filter(
			$links,
			function( $link ) {
				return ! empty( $link['label'] ) && ! empty( $link['url'] );
			}
		)
	);
}

function proof_get_category_archive_url( $slug ) {
	$term = get_category_by_slug( $slug );

	if ( $term instanceof WP_Term ) {
		return get_category_link( $term );
	}

	return home_url( '/' );
}

function proof_get_post_id_by_path( $path, $post_type = 'post' ) {
	$post = get_page_by_path( $path, OBJECT, $post_type );
	return $post instanceof WP_Post && proof_is_published_post( $post->ID, $post_type ) ? (int) $post->ID : 0;
}

/**
 * Curated links on public theme pages may only resolve published content.
 *
 * @param int    $post_id   Candidate post ID.
 * @param string $post_type Expected post type.
 * @return bool Whether this ID is a published post of the expected type.
 */
function proof_is_published_post( $post_id, $post_type = 'post' ) {
	$post_id = absint( $post_id );

	if ( ! $post_id ) {
		return false;
	}

	$post = get_post( $post_id );

	return $post instanceof WP_Post && $post_type === $post->post_type && 'publish' === $post->post_status;
}

function proof_get_post_url_by_path( $path, $post_type = 'post' ) {
	$post_id = proof_get_post_id_by_path( $path, $post_type );
	return $post_id ? get_permalink( $post_id ) : home_url( '/' );
}

function proof_get_featured_post_ids_by_path( $paths, $post_type = 'post' ) {
	$ids = array();

	foreach ( $paths as $path ) {
		$post_id = proof_get_post_id_by_path( $path, $post_type );
		if ( $post_id ) {
			$ids[] = $post_id;
		}
	}

	return $ids;
}

function proof_get_home_featured_paths() {
	return array(
		'start-here-raise-your-standards',
		'ownership-in-recovery',
		'how-to-rebuild-self-trust-in-recovery',
		'how-to-rebuild-your-identity-after-addiction',
	);
}

function proof_get_home_featured_ids() {
	return proof_get_featured_post_ids_by_path( proof_get_home_featured_paths() );
}

function proof_get_recent_work_query( $args = array() ) {
	$defaults         = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 8,
		'post__not_in'        => proof_get_home_featured_ids(),
		'ignore_sticky_posts' => true,
	);
	$project_category = get_category_by_slug( proof_get_projects_writing_category_slug() );

	$parsed_args = wp_parse_args( $args, $defaults );

	if ( $project_category instanceof WP_Term ) {
		$excluded_categories = isset( $parsed_args['category__not_in'] ) ? (array) $parsed_args['category__not_in'] : array();
		$excluded_categories[] = (int) $project_category->term_id;

		$parsed_args['category__not_in'] = array_values( array_unique( array_filter( $excluded_categories ) ) );
	}

	return new WP_Query( $parsed_args );
}

function proof_get_start_here_heading() {
	$default = 'New here? Start here.';
	return trim( get_theme_mod( 'proof_start_here_heading', $default ) );
}

function proof_get_start_here_card_defaults() {
	return array(
		'primary'   => array(
			'title'       => 'Start Here: Raise Your Standards',
			'description' => 'A direct introduction to the core idea behind this work: higher standards, discipline, proof, and rebuilding without dependency.',
			'url'         => proof_get_post_url_by_path( 'start-here-raise-your-standards' ),
		),
		'about'     => array(
			'title'       => 'About Jim Lunsford',
			'description' => 'The life behind the work: addiction, trauma, loss, law enforcement, recovery, family, hard resets, standards, and what I am building now.',
			'url'         => proof_get_about_url(),
		),
		'optional_1' => array(
			'title'       => '',
			'description' => '',
			'url'         => '',
		),
		'optional_2' => array(
			'title'       => '',
			'description' => '',
			'url'         => '',
		),
	);
}

function proof_get_start_here_cards() {
	$cards = proof_get_start_here_card_defaults();

	foreach ( $cards as $card_key => &$card ) {
		$card['title']       = trim( get_theme_mod( 'proof_start_here_' . $card_key . '_title', $card['title'] ) );
		$card['description'] = trim( get_theme_mod( 'proof_start_here_' . $card_key . '_description', $card['description'] ) );
		$card['url']         = esc_url( get_theme_mod( 'proof_start_here_' . $card_key . '_url', $card['url'] ) );
	}

	unset( $card );

	return $cards;
}

function proof_get_pillars_heading() {
	$default = 'The Five Pillars';
	return trim( get_theme_mod( 'proof_pillars_heading', $default ) );
}

function proof_get_pillars_intro() {
	$default = 'These are the core ideas behind this work: Higher Standards, Discipline, Self-Trust, Identity Rebuild, and Recovery Without Dependency. Together, they show people how to rebuild themselves with structure, proof, and long-term strength.';
	return trim( get_theme_mod( 'proof_pillars_intro', $default ) );
}

function proof_get_pillar_defaults() {
	return array(
		'standards' => array(
			'title'       => 'Higher Standards',
			'description' => 'Change starts when excuses lose authority and the minimum standard rises.',
			'links'       => array(
				array(
					'label' => 'Start Here: Raise Your Standards',
					'url'   => proof_get_post_url_by_path( 'start-here-raise-your-standards' ),
				),
				array(
					'label' => 'How to Raise Your Standards in Recovery',
					'url'   => proof_get_post_url_by_path( 'how-to-raise-your-standards-in-recovery' ),
				),
				array(
					'label' => 'What Are Recovery Standards?',
					'url'   => proof_get_post_url_by_path( 'what-are-recovery-standards' ),
				),
			),
		),
		'discipline' => array(
			'title'       => 'Discipline',
			'description' => 'Discipline is the operating system that replaces drift and keeps change moving.',
			'links'       => array(
				array(
					'label' => 'What Discipline Really Is',
					'url'   => proof_get_post_url_by_path( 'what-discipline-really-is' ),
				),
				array(
					'label' => 'The Discipline Loop',
					'url'   => proof_get_post_url_by_path( 'the-discipline-loop' ),
				),
				array(
					'label' => 'The Discipline Dispatch',
					'url'   => proof_get_category_archive_url( 'discipline-dispatch' ),
				),
			),
		),
		'self-trust' => array(
			'title'       => 'Self-Trust',
			'description' => 'Self-trust is rebuilt through proof, repeated action, and visible patterns.',
			'links'       => array(
				array(
					'label' => 'How to Rebuild Self-Trust in Recovery',
					'url'   => proof_get_post_url_by_path( 'how-to-rebuild-self-trust-in-recovery' ),
				),
				array(
					'label' => 'Ownership in Recovery',
					'url'   => proof_get_post_url_by_path( 'ownership-in-recovery' ),
				),
				array(
					'label' => 'Recovery Standard: Self-Trust Is Built',
					'url'   => proof_get_post_url_by_path( 'recovery-standard-evidence-over-promises' ),
				),
			),
		),
		'identity'   => array(
			'title'       => 'Identity Rebuild',
			'description' => 'Lasting change requires identity reconstruction, not just behavior management.',
			'links'       => array(
				array(
					'label' => 'How to Rebuild Your Identity After Addiction',
					'url'   => proof_get_post_url_by_path( 'how-to-rebuild-your-identity-after-addiction' ),
				),
				array(
					'label' => 'Why Life Feels Empty After Early Recovery',
					'url'   => proof_get_post_url_by_path( 'why-life-feels-empty-after-early-recovery' ),
				),
				array(
					'label' => 'Identity After Rock Bottom',
					'url'   => proof_get_post_url_by_path( 'identity-after-rock-bottom' ),
				),
			),
		),
		'dependency' => array(
			'title'       => 'Recovery Without Dependency',
			'description' => 'Real recovery should increase freedom, not permanent reliance on an institution or label.',
			'links'       => array(
				array(
					'label' => 'Start Here: What Is Recovery Beyond AA?',
					'url'   => proof_get_post_url_by_path( 'start-here-what-is-recovery-beyond-aa' ),
				),
				array(
					'label' => 'Recovery Beyond AA',
					'url'   => proof_get_category_archive_url( 'recovery-beyond-aa' ),
				),
				array(
					'label' => 'False Success and Real Failure',
					'url'   => proof_get_post_url_by_path( 'recovery-beyond-aa-false-success-and-real-failure' ),
				),
			),
		),
	);
}

function proof_get_pillar_data() {
	$pillars = proof_get_pillar_defaults();

	foreach ( $pillars as $pillar_key => &$pillar ) {
		$pillar['title']       = trim( get_theme_mod( 'proof_pillar_' . $pillar_key . '_title', $pillar['title'] ) );
		$pillar['description'] = trim( get_theme_mod( 'proof_pillar_' . $pillar_key . '_description', $pillar['description'] ) );

		foreach ( $pillar['links'] as $index => &$link ) {
			$link_number   = $index + 1;
			$default_label = $link['label'];
			$default_url   = $link['url'];

			$link['label'] = trim( get_theme_mod( 'proof_pillar_' . $pillar_key . '_link_' . $link_number . '_label', $default_label ) );
			$link['url']   = esc_url( get_theme_mod( 'proof_pillar_' . $pillar_key . '_link_' . $link_number . '_url', $default_url ) );
		}
	}

	unset( $link, $pillar );

	return $pillars;
}

function proof_get_problem_routing_heading() {
	$default = 'Start with the Problem You Are Facing';
	return trim( get_theme_mod( 'proof_problem_routing_heading', $default ) );
}

function proof_get_problem_routing_intro() {
	$default = 'If you already know the problem you are trying to solve, start there. These paths help new readers find the part of the work that matches what they are facing right now.';
	return trim( get_theme_mod( 'proof_problem_routing_intro', $default ) );
}

function proof_get_problem_route_defaults() {
	return array(
		'relapse' => array(
			'title'       => 'Relapse and instability',
			'description' => 'Start with structure that holds when life gets stressful.',
			'url'         => proof_get_post_url_by_path( 'prevent-relapse-in-recovery' ),
		),
		'empty' => array(
			'title'       => 'Empty early recovery',
			'description' => 'Read this when quiet feels more like a void than peace.',
			'url'         => proof_get_post_url_by_path( 'why-life-feels-empty-after-early-recovery' ),
		),
		'trust' => array(
			'title'       => 'Broken self-trust',
			'description' => 'Rebuild trust through proof, not promises.',
			'url'         => proof_get_post_url_by_path( 'how-to-rebuild-self-trust-in-recovery' ),
		),
		'identity' => array(
			'title'       => 'Identity collapse',
			'description' => 'Start here when the old identity is gone and nothing stable has replaced it yet.',
			'url'         => proof_get_post_url_by_path( 'how-to-rebuild-your-identity-after-addiction' ),
		),
		'dependency' => array(
			'title'       => 'Recovery without dependency',
			'description' => 'Read the essays that challenge permanent powerlessness and borrowed stability.',
			'url'         => proof_get_category_archive_url( 'recovery-beyond-aa' ),
		),
		'discipline' => array(
			'title'       => 'Need more structure and discipline',
			'description' => 'Start here if you need stronger daily structure, clearer standards, and a more disciplined way to rebuild.',
			'url'         => proof_get_post_url_by_path( 'what-discipline-really-is' ),
		),
	);
}

function proof_get_problem_routes() {
	$routes = proof_get_problem_route_defaults();

	foreach ( $routes as $route_key => &$route ) {
		$route['title']       = trim( get_theme_mod( 'proof_problem_route_' . $route_key . '_title', $route['title'] ) );
		$route['description'] = trim( get_theme_mod( 'proof_problem_route_' . $route_key . '_description', $route['description'] ) );
		$route['url']         = esc_url( get_theme_mod( 'proof_problem_route_' . $route_key . '_url', $route['url'] ) );
	}

	unset( $route );

	return array_values( $routes );
}

function proof_get_lane_data() {
	return array(
		'articles'             => array(
			'title'          => 'Articles',
			'description'    => 'Long-form writing that fully teaches a concept, framework, or hard-earned lesson.',
			'start_here_url' => proof_get_post_url_by_path( 'start-here-raise-your-standards' ),
			'featured_paths' => array(
				'how-to-raise-your-standards-in-recovery',
				'ownership-in-recovery',
				'how-to-rebuild-self-trust-in-recovery',
				'how-to-rebuild-your-identity-after-addiction',
			),
		),
		'notes'                => array(
			'title'          => 'Notes',
			'description'    => 'Working thoughts, reflections, and observations shared for those who value clarity, awareness, and continual refinement.',
			'start_here_url' => proof_get_post_url_by_path( 'start-here-raise-your-standards' ),
			'featured_paths' => array(
				'why-this-site-looks-the-way-it-does',
				'stop-waiting-for-life-to-get-easier',
				'if-its-meant-to-be-is-a-lie',
				'why-discipline-isnt-about-willpower',
			),
		),
		'recovery-standards'   => array(
			'title'          => 'Recovery Standards',
			'description'    => 'Direct writing on stability, standards, self-governance, and the conditions that protect long-term recovery.',
			'start_here_url' => proof_get_post_url_by_path( 'what-are-recovery-standards' ),
			'featured_paths' => array(
				'what-are-recovery-standards',
				'recovery-standard-standards-replace-rules',
				'recovery-standard-evidence-over-promises',
				'recovery-standard-structure-before-insight',
			),
		),
		'discipline-dispatch'  => array(
			'title'          => 'Discipline Dispatch',
			'description'    => 'Direct execution-focused writing on discipline, ownership, identity, and standards.',
			'start_here_url' => proof_get_post_url_by_path( 'what-is-the-discipline-dispatch' ),
			'featured_paths' => array(
				'what-is-the-discipline-dispatch',
				'discipline-dispatch-do-something-today',
				'discipline-dispatch-discipline-is-not-a-mood',
				'discipline-dispatch-change-your-inputs',
			),
		),
		'recovery-beyond-aa'   => array(
			'title'          => 'Recovery Beyond AA',
			'description'    => 'Long-form essays challenging dependency-based recovery models and arguing for ownership, discipline, and autonomy.',
			'start_here_url' => proof_get_post_url_by_path( 'start-here-what-is-recovery-beyond-aa' ),
			'featured_paths' => array(
				'start-here-what-is-recovery-beyond-aa',
				'recovery-beyond-aa-false-success-and-real-failure',
				'recovery-beyond-aa-the-lie-of-powerlessness',
				'recovery-beyond-aa-dependency-culture',
			),
		),
	);
}

function proof_get_lane_config( $slug = '' ) {
	$lanes = proof_get_lane_data();
	return $slug && isset( $lanes[ $slug ] ) ? $lanes[ $slug ] : array();
}

function proof_get_lane_featured_urls( $slug, $lane = array() ) {
	if ( empty( $slug ) ) {
		return array();
	}

	if ( empty( $lane ) ) {
		$lane = proof_get_lane_config( $slug );
	}

	if ( empty( $lane['featured_paths'] ) || ! is_array( $lane['featured_paths'] ) ) {
		return array();
	}

	$urls = array();

	foreach ( $lane['featured_paths'] as $index => $featured_path ) {
		$link_number = $index + 1;
		$default_url = proof_get_post_url_by_path( $featured_path );
		$custom_url  = trim( (string) get_theme_mod( 'proof_archive_' . $slug . '_core_post_' . $link_number . '_url', $default_url ) );

		$urls[] = $custom_url ? esc_url_raw( $custom_url ) : $default_url;
	}

	return array_values( array_filter( $urls ) );
}

function proof_get_lane_featured_post_ids( $slug, $lane = array() ) {
	$urls = proof_get_lane_featured_urls( $slug, $lane );
	$ids  = array();

	foreach ( $urls as $url ) {
		$post_id = url_to_postid( $url );

		if ( proof_is_published_post( $post_id ) ) {
			$ids[] = (int) $post_id;
		}
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

function proof_get_topic_hub_data( $slug ) {
	$hubs = array(
		'relapse-prevention-and-stability' => array(
			'title'       => 'Relapse Prevention and Stability',
			'intro'       => 'Relapse prevention comes from standards, structure, identity, and systems, not willpower alone.',
			'paths'       => array(
				'prevent-relapse-in-recovery',
				'why-relapse-after-recovery-happens-when-life-gets-better',
				'what-recovery-requires-after-sobriety',
				'why-life-feels-empty-after-early-recovery',
				'recovery-standard-structure-before-insight',
				'recovery-standard-stability-over-intensity',
				'recovery-standard-stress-proves-standards',
				'recovery-standard-stability-is-proof',
			),
		),
		'identity-rebuild-after-addiction' => array(
			'title'       => 'Identity Rebuild After Addiction',
			'intro'       => 'The deeper battle is identity. The work is not only to stop destroying yourself, but to become someone different through proof.',
			'paths'       => array(
				'how-to-rebuild-your-identity-after-addiction',
				'identity-after-rock-bottom',
				'how-to-rebuild-self-trust-in-recovery',
				'why-life-feels-empty-after-early-recovery',
				'how-to-build-purpose-in-recovery',
				'recovery-standard-evidence-over-promises',
				'recovery-standard-readiness-is-behavioral',
				'recovery-standard-progress-is-quiet',
			),
		),
		'discipline-as-the-operating-system' => array(
			'title'       => 'Discipline as the Operating System',
			'intro'       => 'Discipline is the structure that reduces negotiation and keeps change moving after the decision has been made.',
			'paths'       => array(
				'what-discipline-really-is',
				'the-discipline-loop',
				'comfort-is-the-silent-killer',
				'freedom-and-discipline',
				'discipline-dispatch-do-something-today',
				'discipline-dispatch-discipline-is-not-a-mood',
				'recovery-standard-discipline-internalized',
				'recovery-standard-stop-waiting-for-motivation',
			),
		),
		'recovery-beyond-aa-hub' => array(
			'title'       => 'Recovery Beyond AA',
			'intro'       => 'These essays challenge dependency, powerlessness, and borrowed stability, then point toward recovery built on ownership and discipline.',
			'paths'       => array(
				'start-here-what-is-recovery-beyond-aa',
				'recovery-beyond-aa-false-success-and-real-failure',
				'recovery-beyond-aa-the-lie-of-powerlessness',
				'recovery-beyond-aa-the-meeting-mentality',
				'recovery-beyond-aa-dogma-over-discipline',
				'recovery-beyond-aa-the-religion-problem',
				'recovery-beyond-aa-identity-in-chains',
				'recovery-beyond-aa-dependency-culture',
			),
		),
	);

	return isset( $hubs[ $slug ] ) ? $hubs[ $slug ] : array();
}

function proof_clean_excerpt_source( $content ) {
	$content = (string) $content;

	if ( '' === trim( $content ) ) {
		return '';
	}

	if ( function_exists( 'parse_blocks' ) && function_exists( 'serialize_blocks' ) && has_blocks( $content ) ) {
		$content = serialize_blocks( proof_filter_excerpt_blocks( parse_blocks( $content ) ) );
	}

	$content = preg_replace( '/<form\b[^>]*>.*?<\/form>/is', ' ', $content );
	$content = preg_replace( '/\[(mailpoet_form|mailpoet_signup|newsletter_form|wpforms|contact-form-7|gravityform|formidable|fluentform|subscribe)[^\]]*\]/i', ' ', $content );
	$content = strip_shortcodes( $content );
	$content = preg_replace( '/<!--\s*\/?wp:[^>]*-->/i', ' ', $content );
	$content = wp_strip_all_tags( $content, true );
	$content = html_entity_decode( $content, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$content = proof_remove_excerpt_noise_phrases( $content );
	$content = preg_replace( '/\s+/', ' ', $content );

	return trim( $content );
}

function proof_filter_excerpt_blocks( $blocks ) {
	if ( empty( $blocks ) || ! is_array( $blocks ) ) {
		return array();
	}

	$filtered = array();

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = proof_filter_excerpt_blocks( $block['innerBlocks'] );
		}

		if ( proof_is_excerpt_noise_block( $block ) ) {
			continue;
		}

		$filtered[] = $block;
	}

	return $filtered;
}

function proof_is_excerpt_noise_block( $block ) {
	$block_name = isset( $block['blockName'] ) ? strtolower( (string) $block['blockName'] ) : '';
	$haystack   = wp_strip_all_tags( serialize_block( $block ), true );
	$haystack   = preg_replace( '/\s+/', ' ', $haystack );

	if ( '' === trim( $haystack ) ) {
		return false;
	}

	$markers = array(
		'get the work',
		'delivered when published',
		'email address',
		'mailpoet',
		'wpforms',
		'contact-form-7',
		'gravityform',
		'newsletter',
	);

	$has_marker = false;

	foreach ( $markers as $marker ) {
		if ( false !== stripos( $haystack, $marker ) ) {
			$has_marker = true;
			break;
		}
	}

	if ( ! $has_marker ) {
		return false;
	}

	$noise_block_names = array(
		'core/html',
		'core/shortcode',
		'core/group',
		'core/buttons',
		'core/button',
		'jetpack/subscriptions',
		'mailpoet/subscription-form',
		'wpforms/form-selector',
	);

	if ( in_array( $block_name, $noise_block_names, true ) ) {
		return true;
	}

	return preg_match( '/\b(email address|delivered when published|get the work)\b/i', $haystack ) && strlen( $haystack ) < 500;
}

function proof_remove_excerpt_noise_phrases( $text ) {
	$text = (string) $text;

	$patterns = array(
		'/\bGet the Work\s+Articles on discipline, recovery, identity, and ownership\.\s+Delivered when published\.\s+Email Address\s*(Subscribe)?\b/i',
		'/\bGet the Work\s+.*?\s+Delivered when published\.\s+Email Address\s*(Subscribe)?\b/i',
		'/\bDelivered when published\.\s+Email Address\s*(Subscribe)?\b/i',
	);

	foreach ( $patterns as $pattern ) {
		$text = preg_replace( $pattern, ' ', $text );
	}

	return trim( preg_replace( '/\s+/', ' ', $text ) );
}

function proof_get_clean_excerpt( $post_id = 0, $word_limit = 28 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return '';
	}

	$source = has_excerpt( $post_id ) ? get_post_field( 'post_excerpt', $post_id ) : get_post_field( 'post_content', $post_id );
	$text   = proof_clean_excerpt_source( $source );

	if ( '' === $text && has_excerpt( $post_id ) ) {
		$text = proof_clean_excerpt_source( get_post_field( 'post_content', $post_id ) );
	}

	if ( '' === $text ) {
		return '';
	}

	return wp_trim_words( $text, absint( $word_limit ), '&hellip;' );
}

function proof_get_read_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) ceil( $words / 220 ) );

	return sprintf(
		/* translators: %s: reading time in minutes */
		_n( '%s minute', '%s minutes', $minutes, 'proof' ),
		number_format_i18n( $minutes )
	);
}

function proof_get_primary_category( $post_id = 0 ) {
	$post_id    = $post_id ? $post_id : get_the_ID();
	$categories = get_the_category( $post_id );

	if ( ! empty( $categories ) && $categories[0] instanceof WP_Term ) {
		return $categories[0];
	}

	return null;
}

function proof_the_posts_grid( $query ) {
	if ( ! $query->have_posts() ) {
		echo '<p>' . esc_html__( 'Nothing published here yet.', 'proof' ) . '</p>';
		return;
	}

	echo '<div class="proof-post-feed">';

	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/archive/post-feed' );
	}

	echo '</div>';
	wp_reset_postdata();
}

function proof_get_author_block_heading() {
	$default = 'Jim Lunsford';
	return trim( get_theme_mod( 'proof_author_block_heading', $default ) );
}

function proof_get_author_block_text() {
	$default = 'Jim Lunsford builds tools, writes when something matters, keeps his standards, and shares lessons from recovery, law enforcement, corrections, fitness, and real life.';
	return trim( get_theme_mod( 'proof_author_block_text', $default ) );
}

function proof_get_author_block_link_label() {
	$default = 'Read the full about page';
	return trim( get_theme_mod( 'proof_author_block_link_label', $default ) );
}

function proof_get_author_block_link_url() {
	$default = proof_get_about_url();
	return esc_url_raw( trim( get_theme_mod( 'proof_author_block_link_url', $default ) ) );
}

function proof_get_avatar_markup() {
	$image_id = absint( get_theme_mod( 'proof_author_block_image_id', 0 ) );

	if ( $image_id ) {
		$image = wp_get_attachment_image(
			$image_id,
			'large',
			false,
			array(
				'class' => 'proof-author-avatar',
				'alt'   => proof_get_author_block_heading(),
			)
		);

		if ( $image ) {
			return $image;
		}
	}

	$user = get_user_by( 'email', get_bloginfo( 'admin_email' ) );

	if ( ! $user ) {
		return '';
	}

	return get_avatar( $user->ID, 144, '', proof_get_author_block_heading(), array( 'class' => 'proof-author-avatar' ) );
}

/**
 * Resume page helpers.
 */
function proof_get_resume_headline() {
	return 'Web Publishing | WordPress | Product Support | Technical Operations';
}

function proof_get_resume_summary() {
	return 'I work at the intersection of web publishing, technical support, product implementation, and practical AI-assisted workflows. My background includes government IT and computer repair, years of operational work in high-pressure environments, and current hands-on experience running WordPress sites and directing the development, testing, documentation, and production use of a self-hosted publishing platform.';
}

function proof_get_resume_interest_line() {
	return 'Open to remote opportunities and the right role in the Columbus, Indiana area.';
}

function proof_get_resume_capabilities() {
	return array(
		array(
			'title' => 'Web & Publishing',
			'items' => array(
				'WordPress and CMS administration',
				'Web publishing and content workflows',
				'Markdown and content architecture',
				'Responsive publishing and site maintenance',
				'On-page SEO, metadata, and internal linking',
			),
		),
		array(
			'title' => 'Product & Implementation',
			'items' => array(
				'Requirements definition and workflow design',
				'Functional and regression testing',
				'UX evaluation and responsive QA',
				'Accessibility review',
				'Release validation and implementation testing',
			),
		),
		array(
			'title' => 'Support & Technical Operations',
			'items' => array(
				'Technical troubleshooting and issue triage',
				'User guidance, onboarding, and training',
				'Process support and documentation',
				'Escalation and reliable follow-through',
				'Operational problem solving under pressure',
			),
		),
		array(
			'title' => 'AI-Assisted Workflows',
			'items' => array(
				'Directed AI-assisted development',
				'Repeatable editorial and technical workflows',
				'ChatGPT, Git, and GitHub',
				'Browser developer tools',
				'Implementation review, testing, and iterative refinement',
			),
		),
	);
}

function proof_get_resume_selected_work() {
	return array(
		array(
			'label'       => 'Product',
			'title'       => 'Bonumark Stream',
			'description' => 'An independent, open-source, self-hosted publishing platform built around fast publishing, content ownership, and portable data. I direct product strategy, requirements, implementation priorities, testing, documentation, release decisions, and production use.',
			'links'       => array(
				array( 'label' => 'GitHub', 'url' => 'https://github.com/jimlunsford/bonumarkstream' ),
				array( 'label' => 'Live production use', 'url' => 'https://jimlunsford.net/' ),
			),
		),
		array(
			'label'       => 'WordPress',
			'title'       => 'JimLunsford.com',
			'description' => 'My primary long-form publishing and professional site. I manage the WordPress publishing workflow, site structure, theme development and evaluation, SEO packaging, responsive presentation, accessibility review, internal linking, and ongoing maintenance.',
			'links'       => array(
				array( 'label' => 'Visit site', 'url' => home_url( '/' ) ),
			),
		),
		array(
			'label'       => 'Production implementation',
			'title'       => 'JimLunsford.net',
			'description' => 'My personal short-form publishing stream and the primary real-world Bonumark Stream installation. It gives me a production environment for testing publishing workflows, profiles, media, themes, responsive behavior, upgrades, and other product decisions under actual use.',
			'links'       => array(
				array( 'label' => 'Visit stream', 'url' => 'https://jimlunsford.net/' ),
			),
		),
	);
}

function proof_get_resume_experience() {
	return array(
		array(
			'role'     => 'Jail Officer',
			'employer' => 'Brown County Sheriff\'s Office',
			'dates'    => '2018 to 2020, 2023 to 2025, 2026 to Present',
			'location' => 'Nashville, Indiana',
			'bullets'  => array(
				'Work independently in a high-consequence environment requiring sound judgment, accurate documentation, policy compliance, de-escalation, rapid problem solving, and reliable follow-through.',
				'Coordinate with officers, courts, medical providers, and other staff while handling sensitive information and communicating clearly under pressure.',
			),
		),
		array(
			'role'     => 'Recovery Specialist',
			'employer' => 'Centerstone',
			'dates'    => 'March 2025 to March 2026',
			'location' => 'Columbus, Indiana',
			'bullets'  => array(
				'Led individual and group sessions and translated complex recovery and life-skill concepts into clear, practical instruction.',
				'Documented client progress and collaborated with multidisciplinary teams while maintaining structure, accountability, and consistent follow-through.',
				'Worked with people from varied backgrounds and levels of understanding, building trust while communicating difficult information clearly.',
			),
		),
		array(
			'role'     => 'Field Officer / Field Training Officer',
			'employer' => 'Johnson County Court Services',
			'dates'    => 'November 2020 to June 2023',
			'location' => 'Franklin, Indiana',
			'bullets'  => array(
				'Managed an independent caseload involving court requirements, investigations, compliance monitoring, detailed documentation, and coordination across courts, law enforcement, treatment providers, and community resources.',
				'Served as a Field Training Officer responsible for training and evaluating new officers.',
				'Translated policies and field procedures into repeatable operational practice while documenting performance and maintaining compliance requirements.',
			),
		),
		array(
			'role'     => 'Reserve Police Officer',
			'employer' => 'Town of Prince\'s Lakes',
			'dates'    => 'September 2021 to September 2024',
			'location' => 'Prince\'s Lakes, Indiana',
			'bullets'  => array(
				'Handled investigations, emergency response, interviews, evidence collection, court testimony, and public-facing problem solving in unpredictable situations.',
				'Worked independently and with other responders while maintaining documentation, communication, and sound judgment under pressure.',
			),
		),
		array(
			'role'     => 'Loss Prevention Officer',
			'employer' => 'Fresh Encounter Inc.',
			'dates'    => 'March 2020 to February 2021',
			'location' => 'Indiana',
			'bullets'  => array(
				'Conducted investigations and surveillance, documented incidents, coordinated with law enforcement, and responded to changing operational and safety conditions.',
			),
		),
		array(
			'role'     => 'Custodian / Night Custodial Foreman / Building Manager / Lead Custodian',
			'employer' => 'Bartholomew Consolidated School Corporation',
			'dates'    => '1997 to 2005, 2007 to 2011',
			'location' => 'Columbus, Indiana',
			'bullets'  => array(
				'Held progressively responsible operational roles supporting active school environments, staff, facilities, daily workflows, building standards, and problem resolution.',
			),
		),
	);
}

function proof_get_resume_technical_experience() {
	return array(
		array(
			'role'     => 'Network Technician',
			'employer' => 'Bartholomew County Government',
			'dates'    => 'February 2011 to February 2012',
			'location' => 'Columbus, Indiana',
			'bullets'  => array(
				'Provided end-user technical support for computers, printers, workstations, user access, and network-related issues in a government environment.',
				'Troubleshot day-to-day technology problems and helped users understand solutions while supporting reliable desktop and peripheral operation.',
			),
		),
		array(
			'role'     => 'Computer Repair Technician',
			'employer' => 'Midwest Computer Solutions',
			'dates'    => '2005 to 2007',
			'location' => 'Indiana',
			'bullets'  => array(
				'Diagnosed and repaired computer hardware and software problems.',
				'Installed and configured operating systems, software, and peripherals.',
				'Resolved performance problems and provided customer-facing technical support.',
			),
		),
	);
}

function proof_get_resume_receipts() {
	return array(
		array(
			'label'       => 'Infrastructure',
			'title'       => 'Builder Receipt: Bonumark Hosting Portability',
			'description' => 'Hosting, deployment, portability, and the work required to keep Bonumark usable across different environments.',
			'url'         => home_url( '/builder-receipt-bonumark-hosting-portability/' ),
		),
		array(
			'label'       => 'Architecture',
			'title'       => 'Builder Receipt: Bonumark\'s Theme Architecture',
			'description' => 'How the theme system evolved while core retained ownership of behavior, security, data, publishing, and rendering.',
			'url'         => home_url( '/builder-receipt-bonumarks-theme-architecture/' ),
		),
		array(
			'label'       => 'Product development',
			'title'       => 'Builder Receipt: Reworking Bonumark Stream',
			'description' => 'A record of iterative product work driven by real use, testing, corrections, and another development pass.',
			'url'         => home_url( '/builder-receipt-reworking-bonumark-stream/' ),
		),
		array(
			'label'       => 'AI-assisted systems',
			'title'       => 'Builder Receipt: Rebuilding Cypher\'s Core',
			'description' => 'A deeper system rebuild documenting requirements, implementation direction, testing, and verification.',
			'url'         => home_url( '/builder-receipt-rebuilding-cyphers-core/' ),
		),
	);
}

function proof_get_resume_training() {
	return array(
		'High School Diploma',
		'Certified Peer Recovery Coach - Associate',
		'Moral Reconation Therapy Facilitator',
		'Goodwill Career Coach & Navigator',
		'NASM Fitness & Nutrition specializations',
	);
}
