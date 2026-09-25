<?php
/**
 * Theme header.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.classList.add('js');</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'proof' ); ?></a>

<div id="page" class="site proof-site-shell">
	<?php
	$proof_hide_front_header = is_front_page() && (bool) get_theme_mod( 'proof_identity_hide_front_nav', true );

	if ( ! $proof_hide_front_header ) :
		get_template_part( 'template-parts/site/masthead' );
	endif;
	?>
	<main id="content" class="site-content proof-site-content">
