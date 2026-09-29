<?php
/**
 * Curated selected work section for the identity hub front page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$latest_work_items   = proof_get_identity_latest_work_items();
$latest_work_heading = proof_get_identity_latest_work_heading();

if ( empty( $latest_work_items ) ) {
	return;
}
?>
<section class="proof-identity-section proof-identity-latest-work" <?php echo $latest_work_heading ? 'aria-labelledby="proof-identity-latest-work-title"' : 'aria-label="' . esc_attr__( 'Selected work', 'proof' ) . '"'; ?>>
	<?php if ( $latest_work_heading ) : ?>
		<div class="proof-identity-section-heading">
			<h2 id="proof-identity-latest-work-title"><?php echo esc_html( $latest_work_heading ); ?></h2>
		</div>
	<?php endif; ?>

	<ul class="proof-identity-latest-list">
		<?php foreach ( $latest_work_items as $item ) : ?>
			<li class="proof-identity-latest-item">
				<a class="proof-identity-latest-link" href="<?php echo esc_url( $item['url'] ); ?>">
					<?php if ( ! empty( $item['label'] ) ) : ?>
						<span class="proof-identity-latest-label"><?php echo esc_html( $item['label'] ); ?></span>
					<?php endif; ?>

					<span class="proof-identity-latest-copy">
						<span class="proof-identity-latest-title"><?php echo esc_html( $item['title'] ); ?></span>
						<?php if ( ! empty( $item['description'] ) ) : ?>
								<span class="proof-identity-latest-description"><?php echo wp_kses( $item['description'], proof_card_description_allowed_html() ); ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
