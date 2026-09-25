<?php
/**
 * Search results template.
 */

get_header();
?>

<div class="proof-container proof-search-layout">
	<header class="proof-section-heading proof-block">
		<h1 class="proof-page-title"><?php esc_html_e( 'Search Results', 'proof' ); ?></h1>
		<?php if ( get_search_query() ) : ?>
			<p class="proof-section-intro">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Showing results for “%s”.', 'proof' ),
					esc_html( get_search_query() )
				);
				?>
			</p>
		<?php else : ?>
			<p class="proof-section-intro"><?php esc_html_e( 'Search articles, notes, standards, and essays.', 'proof' ); ?></p>
		<?php endif; ?>
		<div class="proof-search-results-form">
			<?php get_search_form(); ?>
		</div>
	</header>

	<?php if ( have_posts() ) : ?>
		<section class="proof-block">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Search results', 'proof' ); ?></h2>
			<div class="proof-post-feed">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/archive/post-feed' );
			endwhile;
			?>
			</div>
		</section>

		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<section class="proof-block">
			<p><?php esc_html_e( 'Nothing matched your search.', 'proof' ); ?></p>
		</section>
	<?php endif; ?>
</div>

<?php
get_footer();
