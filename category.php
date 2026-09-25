<?php
/**
 * Category archive template.
 */

get_header();

$term = get_queried_object();
$slug = $term instanceof WP_Term ? $term->slug : '';
$lane = proof_get_lane_config( $slug );

$featured_ids = proof_get_lane_featured_post_ids( $slug, $lane );
$current_page = max( 1, (int) get_query_var( 'paged' ) );
$is_first_page = 1 === $current_page;
?>

<div class="proof-container proof-archive-layout">
	<?php
	set_query_var( 'proof_lane_data', $lane );
	set_query_var( 'proof_archive_compact', ! $is_first_page );
	set_query_var( 'proof_show_feed_category', false );
	get_template_part( 'template-parts/archive/archive-intro' );

	if ( $is_first_page ) {
		set_query_var( 'proof_featured_ids', $featured_ids );
		get_template_part( 'template-parts/archive/featured-posts' );
	}
	?>

	<section class="proof-block">
		<div class="proof-section-heading">
			<h2><?php esc_html_e( 'Recent Posts', 'proof' ); ?></h2>
		</div>

		<?php proof_the_posts_grid( $GLOBALS['wp_query'] ); ?>
		<?php
		set_query_var( 'proof_show_feed_category', null );
		$proof_pagination = paginate_links(
			array(
				'total'   => max( 1, (int) $GLOBALS['wp_query']->max_num_pages ),
				'current' => $current_page,
			)
		);

		if ( $proof_pagination ) :
			?>
			<nav class="proof-pagination" aria-label="<?php esc_attr_e( 'Posts pagination', 'proof' ); ?>">
				<?php echo wp_kses_post( $proof_pagination ); ?>
			</nav>
			<?php
		endif;
		set_query_var( 'proof_archive_compact', null );
		set_query_var( 'proof_featured_ids', null );
		?>
	</section>
</div>

<?php
wp_reset_postdata();
get_footer();
