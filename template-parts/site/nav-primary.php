<?php
/**
 * Primary navigation.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<nav id="proof-primary-nav" class="proof-primary-nav" aria-label="<?php esc_attr_e( 'Primary menu', 'proof' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'proof-primary-menu',
			'fallback_cb'    => 'proof_primary_menu_fallback',
		)
	);
	?>
	<button class="proof-nav-search-link" type="button" aria-expanded="false" aria-controls="proof-search-panel">
		<?php esc_html_e( 'Search', 'proof' ); ?>
	</button>
</nav>
