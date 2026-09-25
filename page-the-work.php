<?php
/**
 * Template Name: The Work
 * Template Post Type: page
 *
 * The Work page template.
 */

get_header();
?>

<div class="proof-container proof-work-layout">
	<?php
	while ( have_posts() ) :
		the_post();

		$proof_page_content = trim( get_the_content() );
		$proof_latest_query  = proof_get_recent_work_query();
		?>
		<article id="page-<?php the_ID(); ?>" <?php post_class( 'proof-work-page' ); ?>>
			<header class="proof-work-header proof-archive-header proof-block">
				<h1 class="proof-page-title"><?php the_title(); ?></h1>

				<?php if ( proof_get_the_work_description() ) : ?>
					<p class="proof-archive-description"><?php echo esc_html( proof_get_the_work_description() ); ?></p>
				<?php endif; ?>

				<p class="proof-link-row">
					<a href="<?php echo esc_url( proof_get_the_work_start_here_url() ); ?>"><?php esc_html_e( 'Start here', 'proof' ); ?></a>
					<a href="<?php echo esc_url( proof_get_subscribe_url() ); ?>"><?php esc_html_e( 'Subscribe', 'proof' ); ?></a>
				</p>
			</header>

			<?php if ( '' !== $proof_page_content ) : ?>
				<div class="proof-entry-content proof-block">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="proof-page-links" aria-label="' . esc_attr__( 'Page sections', 'proof' ) . '">',
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/work/start-here' ); ?>
			<?php get_template_part( 'template-parts/home/problem-routing' ); ?>
			<?php get_template_part( 'template-parts/work/pillars' ); ?>

			<?php
			set_query_var( 'proof_latest_query', $proof_latest_query );
			get_template_part( 'template-parts/home/latest-writing' );
			set_query_var( 'proof_latest_query', null );
			wp_reset_postdata();
			?>
		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
