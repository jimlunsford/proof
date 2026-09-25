<?php
/**
 * Site masthead.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header id="masthead" class="site-header proof-masthead">
	<div class="proof-container proof-masthead-inner">
		<div class="proof-branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="proof-logo"><?php the_custom_logo(); ?></div>
			<?php endif; ?>

			<div class="proof-brand-copy">
				<p class="proof-site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
				</p>

				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="proof-site-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
				<?php else : ?>
					<p class="proof-site-description"><?php esc_html_e( 'Building things. Living life. Helping where I can.', 'proof' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="proof-header-actions">
			<button class="proof-menu-toggle" type="button" aria-expanded="false" aria-controls="proof-primary-nav">
				<?php esc_html_e( 'Menu', 'proof' ); ?>
			</button>
		</div>

		<?php get_template_part( 'template-parts/site/nav-primary' ); ?>

		<div id="proof-search-panel" class="proof-search-panel" hidden>
			<?php get_search_form(); ?>
		</div>
	</div>
</header>
