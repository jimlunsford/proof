<?php
/**
 * The Work page pillars section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="proof-work-pillars proof-home-pillars proof-block">
	<div class="proof-section-heading">
		<h2><?php echo esc_html( proof_get_pillars_heading() ); ?></h2>
		<p><?php echo esc_html( proof_get_pillars_intro() ); ?></p>
	</div>

	<div class="proof-pillar-grid">
		<?php foreach ( proof_get_pillar_data() as $pillar ) : ?>
			<section class="proof-pillar-card">
				<h3><?php echo esc_html( $pillar['title'] ); ?></h3>
				<p><?php echo esc_html( $pillar['description'] ); ?></p>
				<ul>
					<?php foreach ( $pillar['links'] as $link ) : ?>
						<?php if ( empty( $link['label'] ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>
	</div>
</section>
