<?php
/**
 * Fallback index template.
 */

get_header();
?>

<div class="proof-container">
	<div class="proof-standard-loop">
		<?php if ( have_posts() ) : ?>
			<header class="proof-archive-header">
				<h1 class="proof-page-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			</header>

			<section class="proof-block">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Recent posts', 'proof' ); ?></h2>
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
</div>

<?php
get_footer();
