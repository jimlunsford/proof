<?php
/**
 * Posts index template.
 */

get_header();
?>

<div class="proof-container proof-home-index">
	<header class="proof-section-heading">
		<p class="proof-kicker"><?php esc_html_e( 'Latest writing', 'proof' ); ?></p>
		<h1 class="proof-page-title"><?php esc_html_e( 'The latest published work', 'proof' ); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<section class="proof-block">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Published posts', 'proof' ); ?></h2>
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
		<p><?php esc_html_e( 'Nothing published here yet.', 'proof' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
