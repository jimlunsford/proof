<?php
/**
 * Theme header.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$proof_hide_front_header = is_front_page() && (bool) get_theme_mod( 'proof_identity_hide_front_nav', true );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script data-no-defer="1">document.documentElement.classList.add('js');</script>
	<?php if ( ! $proof_hide_front_header ) : ?>
		<?php get_template_part( 'template-parts/site/navigation-recovery' ); ?>
	<?php endif; ?>
	<?php if ( defined( 'LITESPEED_GUEST' ) && LITESPEED_GUEST ) : ?>
		<script id="proof-guest-navigation" data-no-defer="1"><?php require get_template_directory() . '/assets/js/guest-navigation.js'; ?></script>
	<?php endif; ?>
	<?php wp_head(); ?>
	<?php if ( ! $proof_hide_front_header ) : ?>
		<style id="proof-masthead-css" data-no-optimize="1"><?php require get_template_directory() . '/assets/css/masthead.css'; ?></style>
	<?php endif; ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'proof' ); ?></a>

<div id="page" class="site proof-site-shell">
	<?php
	if ( ! $proof_hide_front_header ) :
		get_template_part( 'template-parts/site/masthead' );
	endif;
	?>
	<main id="content" class="site-content proof-site-content">
