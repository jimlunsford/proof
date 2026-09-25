<?php
/**
 * Footer meta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="colophon" class="site-footer proof-footer">
	<div class="proof-container proof-footer-inner">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="proof-footer-nav" aria-label="<?php esc_attr_e( 'Footer menu', 'proof' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'proof-footer-menu',
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="proof-footer-meta">
			<p class="proof-footer-copyright">
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>.
			</p>
			<p class="proof-footer-links">
				<a href="<?php echo esc_url( proof_get_contact_url() ); ?>"><?php esc_html_e( 'Contact', 'proof' ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( home_url( '/jim-lunsford-privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'proof' ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( home_url( '/jim-lunsford-disclaimer/' ) ); ?>"><?php esc_html_e( 'Disclaimer', 'proof' ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( proof_get_how_written_url() ); ?>"><?php esc_html_e( 'How This Content Is Written', 'proof' ); ?></a>
			</p>
			<p class="proof-footer-sites">
				<a href="https://jimlunsford.net" rel="noopener noreferrer">JimLunsford.net</a>
				<span aria-hidden="true">|</span>
				<a href="https://phoenix233.com" rel="noopener noreferrer">Phoenix 2:33 LLC</a>
			</p>
			<p class="proof-footer-note"><?php echo esc_html( proof_get_editorial_note() ); ?></p>
		</div>
	</div>
</footer>
