<?php
/**
 * Featured posts list.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured_ids = get_query_var( 'proof_featured_ids' );

if ( empty( $featured_ids ) ) {
	return;
}
?>
<section class="proof-block">
	<div class="proof-section-heading">
		<h2><?php esc_html_e( 'Core Posts', 'proof' ); ?></h2>
	</div>

	<div class="proof-post-feed proof-post-feed-featured">
		<?php foreach ( $featured_ids as $post_id ) : ?>
			<?php
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}
			setup_postdata( $post );
			get_template_part( 'template-parts/archive/post-feed' );
			?>
		<?php endforeach; ?>
	</div>

	<?php wp_reset_postdata(); ?>
</section>
