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
$is_law_archive = 'law-enforcement-corrections' === $slug;
?>

<div class="proof-container proof-archive-layout">
	<?php
	set_query_var( 'proof_lane_data', $lane );
	set_query_var( 'proof_archive_compact', ! $is_first_page );
	set_query_var( 'proof_show_feed_category', false );
	get_template_part( 'template-parts/archive/archive-intro' );

	if ( $is_first_page ) {
		set_query_var( 'proof_featured_ids', $featured_ids );
		set_query_var( 'proof_featured_heading', $is_law_archive ? __( 'Featured Stories', 'proof' ) : __( 'Core Posts', 'proof' ) );
		get_template_part( 'template-parts/archive/featured-posts' );
	}
	?>

	<?php
	$has_recent = ! $is_law_archive || $GLOBALS['wp_query']->have_posts();
	if ( $is_law_archive && $is_first_page && $has_recent ) {
		// With only one published story it stays in WordPress's main query for
		// SEO, but appears exclusively in Featured Stories, not twice.
		$recent_ids = array_map( 'absint', array_column( $GLOBALS['wp_query']->posts, 'ID' ) );
		$has_recent = (bool) array_diff( $recent_ids, $featured_ids );
	}
	if ( $has_recent ) :
	?>
	<section class="proof-block">
		<div class="proof-section-heading">
			<h2><?php echo $is_law_archive ? esc_html__( 'Recent Stories', 'proof' ) : esc_html__( 'Recent Posts', 'proof' ); ?></h2>
		</div>

		<?php proof_the_posts_grid( $GLOBALS['wp_query'] ); ?>
		<?php
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
		?>
	</section>
	<?php endif; ?>
	<?php
	set_query_var( 'proof_show_feed_category', null );
	set_query_var( 'proof_archive_compact', null );
	set_query_var( 'proof_featured_ids', null );
	set_query_var( 'proof_featured_heading', null );
	?>
</div>

<?php
wp_reset_postdata();
get_footer();
