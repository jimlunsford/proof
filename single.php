<?php
/**
 * Single post template.
 */

get_header();
?>

<div class="proof-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'proof-single-layout' ); ?>>
			<?php get_template_part( 'template-parts/post/meta-rail' ); ?>

			<div class="proof-single-center">
				<div class="proof-single-title">
					<?php get_template_part( 'template-parts/post/title-block' ); ?>
				</div>

				<div class="proof-single-main">
					<div class="proof-entry-content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="proof-page-links" aria-label="' . esc_attr__( 'Post pages', 'proof' ) . '">',
								'after'  => '</nav>',
							)
						);
						?>
					</div>

					<?php get_template_part( 'template-parts/post/about-writing' ); ?>

					<?php get_template_part( 'template-parts/post/sources-support' ); ?>
				</div>
			</div>

			<aside class="proof-context-rail" aria-label="<?php esc_attr_e( 'Post context', 'proof' ); ?>">
				<section class="proof-context-block proof-author-block">
					<?php echo wp_kses_post( proof_get_avatar_markup() ); ?>
					<h2><?php echo esc_html( proof_get_author_block_heading() ); ?></h2>
					<p><?php echo esc_html( proof_get_author_block_text() ); ?></p>
					<?php if ( proof_get_author_block_link_label() && proof_get_author_block_link_url() ) : ?>
						<p><a href="<?php echo esc_url( proof_get_author_block_link_url() ); ?>"><?php echo esc_html( proof_get_author_block_link_label() ); ?></a></p>
					<?php endif; ?>
				</section>
			</aside>

		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
