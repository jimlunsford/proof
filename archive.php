<?php
/**
 * Generic archive template.
 */

get_header();
?>

<div class="proof-container proof-archive-layout">
	<header class="proof-section-heading proof-block">
		<p class="proof-kicker"><?php esc_html_e( 'Archive', 'proof' ); ?></p>
		<h1 class="proof-page-title"><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="proof-archive-description">', '</div>' ); ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<section class="proof-block">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Archived posts', 'proof' ); ?></h2>
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
		<p><?php esc_html_e( 'Nothing found.', 'proof' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
