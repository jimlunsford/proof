<?php
/**
 * Context-aware "About This Writing" block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$proof_about_writing_context = proof_get_about_writing_context( get_the_ID() );

if ( 'none' === $proof_about_writing_context ) {
	return;
}
?>
<section class="proof-post-footer-block proof-about-writing">
	<h2><?php esc_html_e( 'About This Writing', 'proof' ); ?></h2>
	<p>
		<?php if ( 'recovery' === $proof_about_writing_context ) : ?>
			<?php esc_html_e( 'This recovery-focused writing is experience-based and written for education and reflection, not as medical, therapeutic, or crisis advice.', 'proof' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'This writing is experience-based and written for education and reflection. It reflects personal observation, practical work, and lessons learned.', 'proof' ); ?>
		<?php endif; ?>
		<a href="<?php echo esc_url( proof_get_how_written_url() ); ?>"><?php esc_html_e( 'Read how this content is written.', 'proof' ); ?></a>
	</p>
</section>
