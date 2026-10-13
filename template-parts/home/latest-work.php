<?php
/**
 * Five newest published posts for the identity hub front page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$latest_work_query = proof_get_identity_latest_work_query();

if ( ! $latest_work_query->have_posts() ) {
	return;
}
?>
<section class="proof-identity-section proof-identity-latest-work" aria-labelledby="proof-identity-latest-work-title">
	<div class="proof-identity-section-heading">
		<h2 id="proof-identity-latest-work-title"><?php esc_html_e( 'Latest Work', 'proof' ); ?></h2>
	</div>

	<ul class="proof-identity-latest-list">
		<?php while ( $latest_work_query->have_posts() ) : ?>
			<?php
			$latest_work_query->the_post();
			$category = proof_get_primary_category();
			$excerpt  = proof_get_clean_excerpt();
			?>
			<li class="proof-identity-latest-item">
				<a class="proof-identity-latest-link" href="<?php echo esc_url( get_permalink() ); ?>">
					<?php if ( $category ) : ?>
						<span class="proof-identity-latest-label"><?php echo esc_html( $category->name ); ?></span>
					<?php endif; ?>

					<span class="proof-identity-latest-copy">
						<span class="proof-identity-latest-title"><?php echo esc_html( get_the_title() ); ?></span>
						<?php if ( '' !== $excerpt ) : ?>
								<span class="proof-identity-latest-description"><?php echo esc_html( $excerpt ); ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endwhile; ?>
	</ul>
</section>
<?php wp_reset_postdata(); ?>
