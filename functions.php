<?php
/**
 * Proof theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/helpers.php';

function proof_setup() {
	load_theme_textdomain( 'proof', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 120, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	add_editor_style( 'assets/css/editor-style.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'proof' ),
			'footer'  => __( 'Footer Menu', 'proof' ),
		)
	);
}
add_action( 'after_setup_theme', 'proof_setup' );

function proof_content_width() {
	$GLOBALS['content_width'] = 840;
}
add_action( 'after_setup_theme', 'proof_content_width', 0 );

function proof_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );
	$theme_css     = get_template_directory() . '/assets/css/theme.css';
	$style_version = $theme_version . '-' . filemtime( get_stylesheet_directory() . '/style.css' );
	$asset_version = file_exists( $theme_css ) ? $theme_version . '-' . filemtime( $theme_css ) : $theme_version;

	wp_enqueue_style( 'proof-style', get_stylesheet_uri(), array(), $style_version );
	wp_enqueue_style( 'proof-theme', get_template_directory_uri() . '/assets/css/theme.css', array( 'proof-style' ), $asset_version );
	wp_add_inline_style( 'proof-theme', proof_get_identity_hub_critical_css() );

	wp_enqueue_script(
		'proof-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	wp_localize_script(
		'proof-navigation',
		'proofLabels',
		array(
			'menuOpen'    => __( 'Menu', 'proof' ),
			'menuClose'   => __( 'Close', 'proof' ),
			'searchLabel' => __( 'Search', 'proof' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'proof_enqueue_assets' );

/**
 * Keep category routing and the displayed feed on the same main query.
 * Proof owns the 12-post category page size, independent of Reading settings.
 */
function proof_configure_category_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) {
		return;
	}

	$term = $query->get_queried_object();
	$slug = $term instanceof WP_Term ? $term->slug : '';
	$featured_ids = proof_get_lane_featured_post_ids( $slug );
	$excluded_ids = array_map( 'absint', (array) $query->get( 'post__not_in' ) );

	$query->set( 'post_type', 'post' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'posts_per_page', 12 );
	$query->set( 'ignore_sticky_posts', true );
	$query->set( 'post__not_in', array_values( array_unique( array_merge( $excluded_ids, $featured_ids ) ) ) );
}
add_action( 'pre_get_posts', 'proof_configure_category_query' );

function proof_get_identity_hub_critical_css() {
	return '
		.proof-identity-home{width:min(calc(100% - 2.5rem),880px);padding:3rem 0 4.25rem;margin-left:auto;margin-right:auto;}
		.proof-front-header-hidden .proof-identity-home{padding-top:3.75rem;}
		.proof-identity-card{padding:0 0 2.65rem;border-bottom:2px solid var(--proof-rule,#202020);}
		.proof-identity-profile{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:start;gap:1.3rem 1.8rem;}
		.proof-identity-avatar{width:clamp(6.5rem,14vw,8.75rem);aspect-ratio:1;border:1px solid var(--proof-soft-border,#d7d7cf);border-radius:999px;overflow:hidden;background:var(--proof-surface,#fff);flex:none;box-shadow:0 0 0 4px var(--proof-surface,#fff);}
		.proof-identity-avatar-image{width:100%!important;height:100%!important;object-fit:cover;display:block;}
		.proof-identity-name{margin:0;font-size:clamp(2.4rem,6vw,4rem);font-weight:800;line-height:.96;letter-spacing:-.045em;color:var(--proof-text,#171717);text-wrap:balance;}
		.proof-identity-line{max-width:52ch;margin:.65rem 0 0;font-size:clamp(1.05rem,2.1vw,1.25rem);font-weight:800;line-height:1.33;color:var(--proof-text,#171717);text-wrap:balance;}
		.proof-identity-bio{max-width:62ch;margin:1.35rem 0 0;color:var(--proof-text,#171717);line-height:1.68;}
		.proof-identity-actions{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.35rem;}
		.proof-identity-section{padding:2.5rem 0 0;}
		.proof-identity-section-heading{margin-bottom:1.15rem;}
		.proof-identity-section h2,.proof-identity-section-heading h2{margin:0;font-size:clamp(1.35rem,2.4vw,1.75rem);line-height:1.12;letter-spacing:-.025em;}
		.proof-identity-section-heading p{max-width:58ch;margin:.65rem 0 0;color:var(--proof-secondary,#4a4a4a);line-height:1.62;}
		.proof-identity-link-list{display:grid;gap:.7rem;margin:0;padding:0;list-style:none;}
		.proof-identity-social-list{display:flex;flex-wrap:wrap;gap:.55rem .75rem;margin:0;padding:0;list-style:none;}
		.proof-identity-destination-item,.proof-identity-social-item{margin:0;padding:0;list-style:none;}
		.proof-identity-destination-link{position:relative;display:grid;gap:.4rem;padding:1rem 2.35rem 1rem 1rem;border:1px solid var(--proof-soft-border,#d7d7cf);color:var(--proof-text,#171717);text-decoration:none;background:var(--proof-surface,#fff);}
		.proof-identity-destination-link:after{content:"\\2192";position:absolute;top:1rem;right:1rem;font-weight:800;color:var(--proof-muted,#646464);}
		
		.proof-identity-destination-link:hover,.proof-identity-destination-link:focus{border-color:var(--proof-text,#171717);color:var(--proof-link,#0d67c6);}
		.proof-identity-destination-title{display:block;font-weight:800;line-height:1.22;}
		.proof-identity-destination-description{display:block;max-width:66ch;margin:0;font-size:.96rem;line-height:1.55;color:var(--proof-secondary,#4a4a4a);}
		.proof-identity-destination-link:hover .proof-identity-destination-description,.proof-identity-destination-link:focus .proof-identity-destination-description{color:var(--proof-text,#171717);}
		.proof-identity-latest-list{margin:0;padding:0;border-top:1px solid var(--proof-soft-border,#d7d7cf);list-style:none;}
		.proof-identity-latest-item{margin:0;padding:0;border-bottom:1px solid var(--proof-soft-border,#d7d7cf);list-style:none;}
		.proof-identity-latest-link{position:relative;display:grid;grid-template-columns:minmax(7.5rem,.26fr) minmax(0,1fr);gap:.45rem 1.2rem;padding:1rem 2.35rem 1rem .15rem;color:var(--proof-text,#171717);text-decoration:none;}
		.proof-identity-latest-link:after{content:"\2192";position:absolute;top:50%;right:.15rem;transform:translateY(-50%);font-weight:800;color:var(--proof-muted,#646464);}
		.proof-identity-latest-label{display:block;padding-top:.18rem;color:var(--proof-muted,#646464);font-size:.76rem;font-weight:800;line-height:1.25;letter-spacing:.075em;text-transform:uppercase;}
		.proof-identity-latest-copy{display:grid;gap:.28rem;min-width:0;}
		.proof-identity-latest-title{display:block;font-size:1.03rem;font-weight:800;line-height:1.28;}
		.proof-identity-latest-description{display:block;max-width:62ch;color:var(--proof-secondary,#4a4a4a);font-size:.94rem;line-height:1.5;}
		.proof-identity-image-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem;padding:2.5rem 0 0;}
		.proof-identity-strip-item{aspect-ratio:1;overflow:hidden;border:1px solid var(--proof-soft-border,#d7d7cf);background:var(--proof-surface,#fff);}
		.proof-identity-strip-image{width:100%!important;height:100%!important;object-fit:cover;display:block;}
		.proof-identity-social{padding-top:2.2rem;}
		.proof-identity-social h2{margin-bottom:.9rem;}
		.proof-identity-social-link{display:inline-flex;align-items:center;min-height:2.25rem;padding:.35rem .65rem;border:1px solid var(--proof-soft-border,#d7d7cf);color:var(--proof-text,#171717);font-size:.95rem;font-weight:800;text-decoration:none;background:var(--proof-surface,#fff);}
		.proof-identity-social-link:hover,.proof-identity-social-link:focus{border-color:var(--proof-text,#171717);color:var(--proof-link,#0d67c6);}
		.proof-front-nav-minimal .proof-header-actions,.proof-front-nav-minimal .proof-primary-nav,.proof-front-nav-minimal .proof-search-panel{display:none!important;}
		@media (max-width:720px){.proof-identity-home{width:min(calc(100% - 2rem),880px);padding-top:1.9rem;padding-bottom:3rem;}.proof-front-header-hidden .proof-identity-home{padding-top:1.7rem;}.proof-identity-card{padding-bottom:2rem;}.proof-identity-profile{grid-template-columns:1fr;justify-items:center;text-align:center;gap:1rem;}.proof-identity-avatar{width:clamp(9.5rem,46vw,11.75rem);margin-left:auto;margin-right:auto;}.proof-identity-copy{width:100%;text-align:center;}.proof-identity-name{font-size:clamp(2.3rem,13vw,3.25rem);}.proof-identity-line{margin-top:.6rem;margin-left:auto;margin-right:auto;font-size:clamp(1.05rem,4.5vw,1.2rem);line-height:1.34;}.proof-identity-bio{margin-top:1.05rem;text-align:left;}.proof-identity-actions{gap:.8rem;}.proof-identity-actions .proof-button{width:100%;}.proof-identity-section{padding-top:2.3rem;}.proof-identity-latest-link{grid-template-columns:1fr;gap:.3rem;padding:.95rem 2.2rem .95rem 0;}.proof-identity-latest-label{padding-top:0;}.proof-identity-image-strip{grid-template-columns:repeat(2,minmax(0,1fr));}.proof-identity-destination-link{padding:1rem 2.2rem 1rem 1rem;}.proof-identity-social-list{display:flex;flex-wrap:wrap;gap:.55rem .65rem;}.proof-identity-social-link{display:inline-flex;width:auto;justify-content:center;min-height:2.15rem;}.proof-footer{padding:1.65rem 0 2.1rem;}.proof-footer-meta{font-size:.88rem;line-height:1.55;}.proof-footer-social,.proof-footer-copyright,.proof-footer-links,.proof-footer-sites,.proof-footer-note{margin:.28rem 0;}}
		@media (max-width:420px){.proof-identity-name{font-size:clamp(2.15rem,14vw,2.8rem);}.proof-identity-line{font-size:1.04rem;}}
	';
}

function proof_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'proof-front-page';
		$classes[] = 'proof-identity-hub-front';

		if ( (bool) get_theme_mod( 'proof_identity_hide_front_nav', true ) ) {
			$classes[] = 'proof-front-nav-minimal';
			$classes[] = 'proof-front-header-hidden';
		}
	}

	if ( is_single() ) {
		$classes[] = 'proof-single';
	}

	if ( is_category() ) {
		$classes[] = 'proof-category';
	}

	if ( is_page_template( 'page-projects.php' ) || is_page( 'projects' ) ) {
		$classes[] = 'proof-projects-page-body';
	}

	if ( is_page_template( 'page-resume.php' ) || is_page( 'resume' ) ) {
		$classes[] = 'proof-resume-page-body';
	}

	return $classes;
}
add_filter( 'body_class', 'proof_body_classes' );

function proof_excerpt_more( $more ) {
	if ( is_admin() ) {
		return $more;
	}

	return '&hellip;';
}
add_filter( 'excerpt_more', 'proof_excerpt_more' );

function proof_custom_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	return 28;
}
add_filter( 'excerpt_length', 'proof_custom_excerpt_length' );

function proof_archive_title( $title ) {
	if ( is_category() ) {
		return single_cat_title( '', false );
	}

	if ( is_tag() ) {
		return single_tag_title( '', false );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'proof_archive_title' );


function proof_sanitize_text_input( $value ) {
	return sanitize_text_field( $value );
}

function proof_sanitize_textarea_input( $value ) {
	return sanitize_textarea_field( $value );
}


function proof_sanitize_rich_text_input( $value ) {
	return wp_kses(
		$value,
		array(
			'a'      => array(
				'href'   => array(),
				'title'  => array(),
				'target' => array(),
				'rel'    => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		)
	);
}

/**
 * Card descriptions sit inside the card's single link. Keep emphasis and
 * line breaks, but preserve only the text of any previously embedded links.
 */
function proof_card_description_allowed_html() {
	return array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
	);
}

function proof_sanitize_card_description_input( $value ) {
	return wp_kses( $value, proof_card_description_allowed_html() );
}

function proof_sanitize_url_input( $value ) {
	return esc_url_raw( $value );
}


function proof_sanitize_checkbox_input( $checked ) {
	return ( isset( $checked ) && true === (bool) $checked );
}

function proof_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'proof_theme_options',
		array(
			'title'    => __( 'Proof Theme', 'proof' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_section(
		'proof_identity_hub',
		array(
			'title'    => __( 'Homepage: Identity', 'proof' ),
			'priority' => 31,
		)
	);

	$wp_customize->add_section(
		'proof_identity_destinations',
		array(
			'title'       => __( 'Homepage: Start Here Links', 'proof' ),
			'description' => __( 'Use these numbered link slots in any order. Leave a link URL blank to remove that link from the front page without leaving a gap.', 'proof' ),
			'priority'    => 32,
		)
	);

	$wp_customize->add_section(
		'proof_identity_latest_work',
		array(
			'title'       => __( 'Homepage: Latest Work', 'proof' ),
			'description' => __( 'Curate up to three current pieces that show what you are writing and building now. Leave a title or URL blank to hide a slot.', 'proof' ),
			'priority'    => 33,
		)
	);

	$wp_customize->add_section(
		'proof_identity_images',
		array(
			'title'    => __( 'Homepage: Image Row', 'proof' ),
			'priority' => 34,
		)
	);

	$wp_customize->add_section(
		'proof_identity_social',
		array(
			'title'       => __( 'Homepage: Follow and Connect', 'proof' ),
			'description' => __( 'Use these numbered social link slots in any order. Leave a link URL blank to remove that link from the front page without leaving a gap.', 'proof' ),
			'priority'    => 35,
		)
	);




	$wp_customize->add_section(
		'proof_the_work_header',
		array(
			'title'    => __( 'The Work Page: Header', 'proof' ),
			'priority' => 34,
		)
	);

	$wp_customize->add_section(
		'proof_projects_header',
		array(
			'title'    => __( 'Projects Page: Header', 'proof' ),
			'priority' => 35,
		)
	);

	$wp_customize->add_section(
		'proof_projects_cards',
		array(
			'title'    => __( 'Projects Page: Cards', 'proof' ),
			'priority' => 36,
		)
	);


	$wp_customize->add_section(
		'proof_projects_writing',
		array(
			'title'       => __( 'Projects Page: Writing', 'proof' ),
			'description' => __( 'Controls the normal WordPress posts shown under the project cards.', 'proof' ),
			'priority'    => 37,
		)
	);

	$wp_customize->add_section(
		'proof_front_page_start_here',
		array(
			'title'    => __( 'The Work Page: Start Here', 'proof' ),
			'priority' => 37,
		)
	);

	$wp_customize->add_section(
		'proof_front_page_pillars',
		array(
			'title'    => __( 'The Work Page: Five Pillars', 'proof' ),
			'priority' => 38,
		)
	);

	$wp_customize->add_section(
		'proof_front_page_problem_routing',
		array(
			'title'    => __( 'The Work Page: Problem Routing', 'proof' ),
			'priority' => 39,
		)
	);

	$wp_customize->add_section(
		'proof_archive_core_posts',
		array(
			'title'    => __( 'Archive Pages: Core Posts', 'proof' ),
			'priority' => 40,
		)
	);

	$wp_customize->add_section(
		'proof_single_post_author_block',
		array(
			'title'    => __( 'Single Posts: Author Block', 'proof' ),
			'priority' => 41,
		)
	);

	$settings = array(
		'proof_identity_name'          => array(
			'label'             => __( 'Name', 'proof' ),
			'default'           => 'Jim Lunsford',
			'type'              => 'text',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_identity_line'          => array(
			'label'             => __( 'Identity line', 'proof' ),
			'default'           => 'I build tools, write when something is worth saying, and share work from a life I had to rebuild.',
			'type'              => 'textarea',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_identity_bio'           => array(
			'label'             => __( 'Short bio', 'proof' ),
			'default'           => 'My work comes from recovery, discipline, ownership, fatherhood, fitness, night shifts, software projects, and the daily choice to keep becoming the man I said I was going to be.',
			'type'              => 'textarea',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_identity_primary_button_label' => array(
			'label'             => __( 'Primary button label', 'proof' ),
			'default'           => 'Visit the microblog',
			'type'              => 'text',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_identity_primary_button_url' => array(
			'label'             => __( 'Primary button URL', 'proof' ),
			'default'           => proof_get_microblog_url(),
			'type'              => 'url',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_url_input',
		),
		'proof_identity_secondary_button_label' => array(
			'label'             => __( 'Secondary button label', 'proof' ),
			'default'           => 'Contact',
			'type'              => 'text',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_identity_secondary_button_url' => array(
			'label'             => __( 'Secondary button URL', 'proof' ),
			'default'           => proof_get_contact_url(),
			'type'              => 'url',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_url_input',
		),
		'proof_identity_destinations_heading' => array(
			'label'             => __( 'Destination section heading', 'proof' ),
			'default'           => 'Start Here',
			'type'              => 'text',
			'section'           => 'proof_identity_destinations',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),

		'proof_identity_latest_work_heading' => array(
			'label'             => __( 'Latest Work section heading', 'proof' ),
			'default'           => 'Latest Work',
			'type'              => 'text',
			'section'           => 'proof_identity_latest_work',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),

		'proof_identity_social_heading' => array(
			'label'             => __( 'Social section heading', 'proof' ),
			'default'           => 'Follow and Connect',
			'type'              => 'text',
			'section'           => 'proof_identity_social',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_identity_hide_front_nav' => array(
			'label'             => __( 'Hide the normal site header on the front page', 'proof' ),
			'default'           => true,
			'type'              => 'checkbox',
			'section'           => 'proof_identity_hub',
			'sanitize_callback' => 'proof_sanitize_checkbox_input',
		),






		'proof_the_work_description' => array(
			'label'             => __( 'The Work page description', 'proof' ),
			'default'           => 'A guide to the writing, frameworks, recovery perspective, discipline, standards, and lessons that shape the work on this site.',
			'type'              => 'textarea',
			'section'           => 'proof_the_work_header',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_the_work_start_here_url' => array(
			'label'             => __( 'The Work page Start here URL', 'proof' ),
			'default'           => proof_get_post_url_by_path( 'start-here-raise-your-standards' ),
			'type'              => 'url',
			'section'           => 'proof_the_work_header',
			'sanitize_callback' => 'proof_sanitize_url_input',
		),
		'proof_projects_description' => array(
			'label'             => __( 'Projects page description', 'proof' ),
			'default'           => 'Software, tools, websites, and systems I am building around ownership, structure, practical use, and real problems.',
			'type'              => 'textarea',
			'section'           => 'proof_projects_header',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_projects_start_here_url' => array(
			'label'             => __( 'Projects page Start here URL', 'proof' ),
			'default'           => proof_get_about_url(),
			'type'              => 'url',
			'section'           => 'proof_projects_header',
			'sanitize_callback' => 'proof_sanitize_url_input',
		),

		'proof_projects_writing_heading' => array(
			'label'             => __( 'Project writing heading', 'proof' ),
			'default'           => 'Project Writing',
			'type'              => 'text',
			'section'           => 'proof_projects_writing',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),		'proof_projects_writing_category_slug' => array(
			'label'             => __( 'Project writing category slug', 'proof' ),
			'default'           => 'projects',
			'type'              => 'text',
			'section'           => 'proof_projects_writing',
			'sanitize_callback' => 'sanitize_title',
		),
		'proof_editorial_note'         => array(
			'label'             => __( 'Footer editorial note', 'proof' ),
			'default'           => 'This site holds experience-based writing, projects, lessons, and tools from a rebuilt life. It is personal and educational, not medical or clinical care.',
			'type'              => 'textarea',
			'section'           => 'proof_theme_options',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_start_here_heading'      => array(
			'label'             => __( 'The Work section heading', 'proof' ),
			'default'           => 'New here? Start here.',
			'type'              => 'text',
			'section'           => 'proof_front_page_start_here',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_pillars_heading'        => array(
			'label'             => __( 'The Work section heading', 'proof' ),
			'default'           => 'The Five Pillars',
			'type'              => 'text',
			'section'           => 'proof_front_page_pillars',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_pillars_intro'          => array(
			'label'             => __( 'The Work section intro', 'proof' ),
			'default'           => 'These are the core ideas behind this work: Higher Standards, Discipline, Self-Trust, Identity Rebuild, and Recovery Without Dependency. Together, they show people how to rebuild themselves with structure, proof, and long-term strength.',
			'type'              => 'textarea',
			'section'           => 'proof_front_page_pillars',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_problem_routing_heading' => array(
			'label'             => __( 'The Work problem routing heading', 'proof' ),
			'default'           => 'Start with the Problem You Are Facing',
			'type'              => 'text',
			'section'           => 'proof_front_page_problem_routing',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_problem_routing_intro'   => array(
			'label'             => __( 'The Work problem routing intro', 'proof' ),
			'default'           => 'If you already know the problem you are trying to solve, start there. These paths help new readers find the part of the work that matches what they are facing right now.',
			'type'              => 'textarea',
			'section'           => 'proof_front_page_problem_routing',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_author_block_heading'   => array(
			'label'             => __( 'Author block heading', 'proof' ),
			'default'           => 'Jim Lunsford',
			'type'              => 'text',
			'section'           => 'proof_single_post_author_block',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_author_block_text'      => array(
			'label'             => __( 'Author block text', 'proof' ),
			'default'           => 'Jim Lunsford builds tools, writes when something matters, keeps his standards, and shares lessons from recovery, law enforcement, corrections, fitness, and real life.',
			'type'              => 'textarea',
			'section'           => 'proof_single_post_author_block',
			'sanitize_callback' => 'proof_sanitize_textarea_input',
		),
		'proof_author_block_link_label' => array(
			'label'             => __( 'Author block link label', 'proof' ),
			'default'           => 'Read the full about page',
			'type'              => 'text',
			'section'           => 'proof_single_post_author_block',
			'sanitize_callback' => 'proof_sanitize_text_input',
		),
		'proof_author_block_link_url'   => array(
			'label'             => __( 'Author block link URL', 'proof' ),
			'default'           => proof_get_about_url(),
			'type'              => 'url',
			'section'           => 'proof_single_post_author_block',
			'sanitize_callback' => 'proof_sanitize_url_input',
		),
	);

	foreach ( $settings as $setting_id => $args ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $args['default'],
				'sanitize_callback' => $args['sanitize_callback'],
			)
		);

		$wp_customize->add_control(
			$setting_id,
			array(
				'section' => $args['section'],
				'label'   => $args['label'],
				'type'    => $args['type'],
			)
		);
	}

	$wp_customize->add_setting(
		'proof_author_block_image_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'proof_author_block_image_id',
			array(
				'section'   => 'proof_single_post_author_block',
				'label'     => __( 'Author block image', 'proof' ),
				'mime_type' => 'image',
			)
		)
	);


	$wp_customize->add_setting(
		'proof_identity_profile_image_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'proof_identity_profile_image_id',
			array(
				'section'   => 'proof_identity_hub',
				'label'     => __( 'Homepage profile image', 'proof' ),
				'mime_type' => 'image',
			)
		)
	);

	for ( $index = 1; $index <= 4; $index++ ) {
		$wp_customize->add_setting(
			'proof_identity_gallery_image_' . $index . '_id',
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'proof_identity_gallery_image_' . $index . '_id',
				array(
					'section'   => 'proof_identity_images',
					'label'     => sprintf( __( 'Image row image %d', 'proof' ), $index ),
					'mime_type' => 'image',
				)
			)
		);
	}

	$identity_destination_defaults = proof_get_identity_destination_defaults();
	$link_number                   = 1;

	foreach ( $identity_destination_defaults as $link_key => $link ) {

		$wp_customize->add_setting(
			'proof_identity_destination_' . $link_key . '_title',
			array(
				'default'           => $link['title'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_destination_' . $link_key . '_title',
			array(
				'section'     => 'proof_identity_destinations',
				'label'       => sprintf( __( 'Link %d title', 'proof' ), $link_number ),
				'type'        => 'text',
				'description' => __( 'Use this slot for any homepage destination. Leave the title or URL blank to hide it.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_identity_destination_' . $link_key . '_description',
			array(
				'default'           => $link['description'],
				'sanitize_callback' => 'proof_sanitize_card_description_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_destination_' . $link_key . '_description',
			array(
				'section'     => 'proof_identity_destinations',
				'label'       => sprintf( __( 'Link %d description', 'proof' ), $link_number ),
				'type'        => 'textarea',
				'description' => __( 'Optional. The whole card is one link, so links inside this description are shown as text.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_identity_destination_' . $link_key . '_url',
			array(
				'default'           => $link['url'],
				'sanitize_callback' => 'proof_sanitize_url_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_destination_' . $link_key . '_url',
			array(
				'section'     => 'proof_identity_destinations',
				'label'       => sprintf( __( 'Link %d URL', 'proof' ), $link_number ),
				'type'        => 'url',
				'description' => __( 'Leave blank to remove this link. Remaining links close the gap automatically.', 'proof' ),
			)
		);
		$link_number++;
	}

	$identity_latest_work_defaults = proof_get_identity_latest_work_defaults();
	$latest_work_number           = 1;

	foreach ( $identity_latest_work_defaults as $item_key => $item ) {
		$wp_customize->add_setting(
			'proof_identity_latest_work_' . $item_key . '_label',
			array(
				'default'           => $item['label'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_latest_work_' . $item_key . '_label',
			array(
				'section'     => 'proof_identity_latest_work',
				'label'       => sprintf( __( 'Item %d type label', 'proof' ), $latest_work_number ),
				'type'        => 'text',
				'description' => __( 'Examples: Project, Builder Receipt, Recovery Standard.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_identity_latest_work_' . $item_key . '_title',
			array(
				'default'           => $item['title'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_latest_work_' . $item_key . '_title',
			array(
				'section' => 'proof_identity_latest_work',
				'label'   => sprintf( __( 'Item %d title', 'proof' ), $latest_work_number ),
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'proof_identity_latest_work_' . $item_key . '_description',
			array(
				'default'           => $item['description'],
				'sanitize_callback' => 'proof_sanitize_card_description_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_latest_work_' . $item_key . '_description',
			array(
				'section'     => 'proof_identity_latest_work',
				'label'       => sprintf( __( 'Item %d description', 'proof' ), $latest_work_number ),
				'type'        => 'textarea',
				'description' => __( 'Keep this short. The whole item is one link, so links inside this description are shown as text.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_identity_latest_work_' . $item_key . '_url',
			array(
				'default'           => $item['url'],
				'sanitize_callback' => 'proof_sanitize_url_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_latest_work_' . $item_key . '_url',
			array(
				'section'     => 'proof_identity_latest_work',
				'label'       => sprintf( __( 'Item %d URL', 'proof' ), $latest_work_number ),
				'type'        => 'url',
				'description' => __( 'Leave blank to remove this item. Remaining items close the gap automatically.', 'proof' ),
			)
		);

		$latest_work_number++;
	}

	$identity_social_defaults = proof_get_identity_social_defaults();
	$link_number              = 1;

	foreach ( $identity_social_defaults as $link_key => $link ) {

		$wp_customize->add_setting(
			'proof_identity_social_' . $link_key . '_label',
			array(
				'default'           => $link['label'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_social_' . $link_key . '_label',
			array(
				'section'     => 'proof_identity_social',
				'label'       => sprintf( __( 'Link %d label', 'proof' ), $link_number ),
				'type'        => 'text',
				'description' => __( 'Use this slot for any social or outside link. Leave the label or URL blank to hide it.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_identity_social_' . $link_key . '_url',
			array(
				'default'           => $link['url'],
				'sanitize_callback' => 'proof_sanitize_url_input',
			)
		);

		$wp_customize->add_control(
			'proof_identity_social_' . $link_key . '_url',
			array(
				'section'     => 'proof_identity_social',
				'label'       => sprintf( __( 'Link %d URL', 'proof' ), $link_number ),
				'type'        => 'url',
				'description' => __( 'Leave blank to remove this link. Remaining links close the gap automatically.', 'proof' ),
			)
		);
		$link_number++;
	}



	$start_here_card_defaults = proof_get_start_here_card_defaults();

	foreach ( array_keys( $start_here_card_defaults ) as $index => $card_key ) {
		$card = $start_here_card_defaults[ $card_key ];
		$card_name = ! empty( $card['title'] ) ? $card['title'] : sprintf( __( 'Start Here card %d', 'proof' ), $index + 1 );

		$wp_customize->add_setting(
			'proof_start_here_' . $card_key . '_title',
			array(
				'default'           => $card['title'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_start_here_' . $card_key . '_title',
			array(
				'section' => 'proof_front_page_start_here',
				'label'   => sprintf( __( '%s title', 'proof' ), $card_name ),
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'proof_start_here_' . $card_key . '_description',
			array(
				'default'           => $card['description'],
				'sanitize_callback' => 'proof_sanitize_rich_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_start_here_' . $card_key . '_description',
			array(
				'section'     => 'proof_front_page_start_here',
				'label'       => sprintf( __( '%s description', 'proof' ), $card_name ),
				'type'        => 'textarea',
				'description' => __( 'Basic links are allowed in this field.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_start_here_' . $card_key . '_url',
			array(
				'default'           => $card['url'],
				'sanitize_callback' => 'proof_sanitize_url_input',
			)
		);

		$wp_customize->add_control(
			'proof_start_here_' . $card_key . '_url',
			array(
				'section' => 'proof_front_page_start_here',
				'label'   => sprintf( __( '%s URL', 'proof' ), $card_name ),
				'type'    => 'url',
			)
		);
	}


	$project_card_defaults = proof_get_project_card_defaults();

	foreach ( $project_card_defaults as $card_key => $card ) {
		$card_name = ! empty( $card['title'] ) ? $card['title'] : ucwords( str_replace( '_', ' ', $card_key ) );

		$wp_customize->add_setting(
			'proof_project_' . $card_key . '_enabled',
			array(
				'default'           => ! empty( $card['enabled'] ),
				'sanitize_callback' => 'proof_sanitize_checkbox_input',
			)
		);

		$wp_customize->add_control(
			'proof_project_' . $card_key . '_enabled',
			array(
				'section' => 'proof_projects_cards',
				'label'   => sprintf( __( 'Show %s card', 'proof' ), $card_name ),
				'type'    => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			'proof_project_' . $card_key . '_title',
			array(
				'default'           => $card['title'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_project_' . $card_key . '_title',
			array(
				'section' => 'proof_projects_cards',
				'label'   => sprintf( __( '%s title', 'proof' ), $card_name ),
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'proof_project_' . $card_key . '_description',
			array(
				'default'           => $card['description'],
				'sanitize_callback' => 'proof_sanitize_rich_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_project_' . $card_key . '_description',
			array(
				'section'     => 'proof_projects_cards',
				'label'       => sprintf( __( '%s description', 'proof' ), $card_name ),
				'type'        => 'textarea',
				'description' => __( 'Basic links are allowed in this field.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_project_' . $card_key . '_link_label',
			array(
				'default'           => $card['link_label'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_project_' . $card_key . '_link_label',
			array(
				'section'     => 'proof_projects_cards',
				'label'       => sprintf( __( '%s link label', 'proof' ), $card_name ),
				'type'        => 'text',
				'description' => __( 'Optional. Shows only when a URL is also set.', 'proof' ),
			)
		);

		$wp_customize->add_setting(
			'proof_project_' . $card_key . '_url',
			array(
				'default'           => $card['url'],
				'sanitize_callback' => 'proof_sanitize_url_input',
			)
		);

		$wp_customize->add_control(
			'proof_project_' . $card_key . '_url',
			array(
				'section' => 'proof_projects_cards',
				'label'   => sprintf( __( '%s URL', 'proof' ), $card_name ),
				'type'    => 'url',
			)
		);
	}

	$pillar_defaults = proof_get_pillar_defaults();

	foreach ( $pillar_defaults as $pillar_key => $pillar ) {
		$pillar_name = $pillar['title'];

		$wp_customize->add_setting(
			'proof_pillar_' . $pillar_key . '_title',
			array(
				'default'           => $pillar['title'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_pillar_' . $pillar_key . '_title',
			array(
				'section' => 'proof_front_page_pillars',
				'label'   => sprintf( __( '%s title', 'proof' ), $pillar_name ),
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'proof_pillar_' . $pillar_key . '_description',
			array(
				'default'           => $pillar['description'],
				'sanitize_callback' => 'proof_sanitize_textarea_input',
			)
		);

		$wp_customize->add_control(
			'proof_pillar_' . $pillar_key . '_description',
			array(
				'section' => 'proof_front_page_pillars',
				'label'   => sprintf( __( '%s description', 'proof' ), $pillar_name ),
				'type'    => 'textarea',
			)
		);

		foreach ( $pillar['links'] as $index => $link ) {
			$link_number = $index + 1;

			$wp_customize->add_setting(
				'proof_pillar_' . $pillar_key . '_link_' . $link_number . '_label',
				array(
					'default'           => $link['label'],
					'sanitize_callback' => 'proof_sanitize_text_input',
				)
			);

			$wp_customize->add_control(
				'proof_pillar_' . $pillar_key . '_link_' . $link_number . '_label',
				array(
					'section' => 'proof_front_page_pillars',
					'label'   => sprintf( __( '%1$s link %2$d label', 'proof' ), $pillar_name, $link_number ),
					'type'    => 'text',
				)
			);

			$wp_customize->add_setting(
				'proof_pillar_' . $pillar_key . '_link_' . $link_number . '_url',
				array(
					'default'           => $link['url'],
					'sanitize_callback' => 'proof_sanitize_url_input',
				)
			);

			$wp_customize->add_control(
				'proof_pillar_' . $pillar_key . '_link_' . $link_number . '_url',
				array(
					'section' => 'proof_front_page_pillars',
					'label'   => sprintf( __( '%1$s link %2$d URL', 'proof' ), $pillar_name, $link_number ),
					'type'    => 'url',
				)
			);
		}
	}


	$lane_defaults = proof_get_lane_data();

	foreach ( $lane_defaults as $lane_key => $lane ) {
		$lane_name = $lane['title'];

		foreach ( $lane['featured_paths'] as $index => $featured_path ) {
			$link_number = $index + 1;
			$default_url = proof_get_post_url_by_path( $featured_path );

			$wp_customize->add_setting(
				'proof_archive_' . $lane_key . '_core_post_' . $link_number . '_url',
				array(
					'default'           => $default_url,
					'sanitize_callback' => 'proof_sanitize_url_input',
				)
			);

			$wp_customize->add_control(
				'proof_archive_' . $lane_key . '_core_post_' . $link_number . '_url',
				array(
					'section' => 'proof_archive_core_posts',
					'label'   => sprintf( __( '%1$s core post %2$d URL', 'proof' ), $lane_name, $link_number ),
					'type'    => 'url',
				)
			);
		}
	}

	$problem_route_defaults = proof_get_problem_route_defaults();

	foreach ( $problem_route_defaults as $route_key => $route ) {
		$route_name = $route['title'];

		$wp_customize->add_setting(
			'proof_problem_route_' . $route_key . '_title',
			array(
				'default'           => $route['title'],
				'sanitize_callback' => 'proof_sanitize_text_input',
			)
		);

		$wp_customize->add_control(
			'proof_problem_route_' . $route_key . '_title',
			array(
				'section' => 'proof_front_page_problem_routing',
				'label'   => sprintf( __( '%s title', 'proof' ), $route_name ),
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'proof_problem_route_' . $route_key . '_description',
			array(
				'default'           => $route['description'],
				'sanitize_callback' => 'proof_sanitize_textarea_input',
			)
		);

		$wp_customize->add_control(
			'proof_problem_route_' . $route_key . '_description',
			array(
				'section' => 'proof_front_page_problem_routing',
				'label'   => sprintf( __( '%s description', 'proof' ), $route_name ),
				'type'    => 'textarea',
			)
		);

		$wp_customize->add_setting(
			'proof_problem_route_' . $route_key . '_url',
			array(
				'default'           => $route['url'],
				'sanitize_callback' => 'proof_sanitize_url_input',
			)
		);

		$wp_customize->add_control(
			'proof_problem_route_' . $route_key . '_url',
			array(
				'section' => 'proof_front_page_problem_routing',
				'label'   => sprintf( __( '%s URL', 'proof' ), $route_name ),
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'proof_customize_register' );
function proof_primary_menu_fallback() {
	echo '<ul class="proof-primary-menu">';
	echo '<li><a href="' . esc_url( proof_get_post_url_by_path( 'start-here-raise-your-standards' ) ) . '">' . esc_html__( 'Start Here', 'proof' ) . '</a></li>';
	echo '<li><a href="' . esc_url( proof_get_category_archive_url( 'articles' ) ) . '">' . esc_html__( 'Articles', 'proof' ) . '</a></li>';
	echo '<li><a href="' . esc_url( proof_get_category_archive_url( 'notes' ) ) . '">' . esc_html__( 'Notes', 'proof' ) . '</a></li>';
	echo '<li><a href="' . esc_url( proof_get_category_archive_url( 'recovery-standards' ) ) . '">' . esc_html__( 'Recovery Standards', 'proof' ) . '</a></li>';
	echo '<li><a href="' . esc_url( proof_get_category_archive_url( 'discipline-dispatch' ) ) . '">' . esc_html__( 'Discipline Dispatch', 'proof' ) . '</a></li>';
	echo '<li><a href="' . esc_url( proof_get_category_archive_url( 'recovery-beyond-aa' ) ) . '">' . esc_html__( 'Recovery Beyond AA', 'proof' ) . '</a></li>';
	echo '<li><a href="' . esc_url( proof_get_about_url() ) . '">' . esc_html__( 'About', 'proof' ) . '</a></li>';
	echo '</ul>';
}
