<?php
/**
 * Recent published work section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$latest_query = get_query_var( 'proof_latest_query' );

if ( ! ( $latest_query instanceof WP_Query ) ) {
	$latest_query = proof_get_recent_work_query();
}
?>
<section class="proof-home-latest proof-block">
	<div class="proof-section-heading">
		<h2><?php esc_html_e( 'Recent Published Work', 'proof' ); ?></h2>
		<p><?php esc_html_e( 'These are the most recent pieces published on the site, including long-form articles, shorter notes, and other new writing as it goes live.', 'proof' ); ?></p>
	</div>

	<?php if ( $latest_query instanceof WP_Query ) : ?>
		<?php
		set_query_var( 'proof_show_feed_category', false );
		proof_the_posts_grid( $latest_query );
		set_query_var( 'proof_show_feed_category', null );
		?>
	<?php endif; ?>
</section>
