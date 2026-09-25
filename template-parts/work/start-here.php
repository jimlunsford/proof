<?php
/**
 * The Work page start-here section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="proof-work-start-here proof-home-start-here proof-block">
	<div class="proof-section-heading">
		<h2><?php echo esc_html( proof_get_start_here_heading() ); ?></h2>
	</div>

	<div class="proof-start-grid">
		<?php foreach ( proof_get_start_here_cards() as $card ) : ?>
			<?php if ( empty( $card['title'] ) && empty( $card['description'] ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>

			<article class="proof-start-card">
				<?php if ( ! empty( $card['title'] ) ) : ?>
					<h3>
						<?php if ( ! empty( $card['url'] ) ) : ?>
							<a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $card['title'] ); ?>
						<?php endif; ?>
					</h3>
				<?php endif; ?>

				<?php if ( ! empty( $card['description'] ) ) : ?>
					<p><?php echo wp_kses_post( $card['description'] ); ?></p>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
</section>
