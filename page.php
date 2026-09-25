<?php
/**
 * Page template.
 */

get_header();
?>

<div class="proof-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="page-<?php the_ID(); ?>" <?php post_class( array( 'proof-page-layout', 'proof-page-no-sidebar' ) ); ?>>
			<div class="proof-page-main">
				<header class="proof-section-heading">
					<h1 class="proof-page-title"><?php the_title(); ?></h1>
				</header>

				<div class="proof-entry-content">
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
			</div>
		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
