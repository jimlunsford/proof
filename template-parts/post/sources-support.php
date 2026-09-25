<?php
/**
 * Sources and support block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sources_support = get_post_meta( get_the_ID(), 'proof_sources_support', true );

if ( empty( $sources_support ) ) {
	return;
}
?>
<section class="proof-post-footer-block proof-sources-support">
	<h2><?php esc_html_e( 'Sources and Support', 'proof' ); ?></h2>
	<div class="proof-sources-content">
		<?php echo wp_kses_post( wpautop( $sources_support ) ); ?>
	</div>
</section>
