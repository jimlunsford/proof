<?php
/**
 * Template Name: Topic Hub
 * Template Post Type: page
 */

get_header();

$hub = proof_get_topic_hub_data( get_post_field( 'post_name', get_the_ID() ) );
$post_ids = ! empty( $hub['paths'] ) ? proof_get_featured_post_ids_by_path( $hub['paths'] ) : array();
?>

<div class="proof-container proof-hub-layout">
	<article id="page-<?php the_ID(); ?>" <?php post_class( 'proof-page-main' ); ?>>
		<header class="proof-section-heading proof-block">
			<p class="proof-kicker"><?php esc_html_e( 'Topic hub', 'proof' ); ?></p>
			<h1 class="proof-page-title"><?php the_title(); ?></h1>
			<?php if ( ! empty( $hub['intro'] ) ) : ?>
				<p class="proof-archive-description"><?php echo esc_html( $hub['intro'] ); ?></p>
			<?php endif; ?>
		</header>

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

		<?php if ( ! empty( $post_ids ) ) : ?>
			<section class="proof-block">
				<div class="proof-section-heading">
					<p class="proof-kicker"><?php esc_html_e( 'Start here path', 'proof' ); ?></p>
					<h2><?php esc_html_e( 'Core reading', 'proof' ); ?></h2>
				</div>
				<div class="proof-post-feed">
					<?php foreach ( $post_ids as $post_id ) : ?>
						<?php
						$post = get_post( $post_id );
						if ( ! $post ) {
							continue;
						}
						setup_postdata( $post );
						get_template_part( 'template-parts/archive/post-feed' );
						?>
					<?php endforeach; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			</section>
		<?php endif; ?>
	</article>
</div>

<?php
get_footer();
