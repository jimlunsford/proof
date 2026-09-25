<?php
/**
 * Template Name: Projects
 * Template Post Type: page
 *
 * Projects page template.
 */

get_header();
?>

<div class="proof-container proof-archive-layout proof-projects-layout">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="page-<?php the_ID(); ?>" <?php post_class( 'proof-projects-page' ); ?>>
			<header class="proof-projects-header proof-archive-header proof-block">
				<h1 class="proof-page-title"><?php the_title(); ?></h1>

				<?php if ( proof_get_projects_description() ) : ?>
					<p class="proof-archive-description"><?php echo esc_html( proof_get_projects_description() ); ?></p>
				<?php endif; ?>

				<p class="proof-link-row">
					<a href="<?php echo esc_url( proof_get_projects_start_here_url() ); ?>"><?php esc_html_e( 'Start here', 'proof' ); ?></a>
					<a href="<?php echo esc_url( proof_get_subscribe_url() ); ?>"><?php esc_html_e( 'Subscribe', 'proof' ); ?></a>
				</p>
			</header>

			<section class="proof-project-cards proof-block" aria-labelledby="proof-project-cards-heading">
				<div class="proof-section-heading">
					<h2 id="proof-project-cards-heading"><?php esc_html_e( 'What I Am Building', 'proof' ); ?></h2>
				</div>

				<div class="proof-project-grid">
					<?php foreach ( proof_get_project_cards() as $card ) : ?>
						<?php if ( empty( $card['enabled'] ) || ( empty( $card['title'] ) && empty( $card['description'] ) ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>

						<article class="proof-project-card">
							<?php if ( ! empty( $card['title'] ) ) : ?>
								<h3>
									<?php if ( ! empty( $card['url'] ) ) : ?>
										<a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $card['title'] ); ?>
									<?php endif; ?>
								</h3>
							<?php endif; ?>

							<?php if ( ! empty( $card['description'] ) ) : ?>
								<div class="proof-project-card-description">
									<?php echo wp_kses_post( wpautop( $card['description'] ) ); ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $card['url'] ) && ! empty( $card['link_label'] ) ) : ?>
								<p class="proof-project-card-link"><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['link_label'] ); ?></a></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</section>


			<section class="proof-project-writing proof-block" aria-labelledby="proof-project-writing-heading">
				<div class="proof-section-heading">
					<h2 id="proof-project-writing-heading"><?php echo esc_html( proof_get_projects_writing_heading() ); ?></h2>
				</div>

				<?php
				$proof_project_writing_query = proof_get_projects_writing_query();
				set_query_var( 'proof_show_feed_category', false );
				proof_the_posts_grid( $proof_project_writing_query );
				set_query_var( 'proof_show_feed_category', null );

				$proof_project_writing_pagination = paginate_links(
					array(
						'total'   => max( 1, (int) $proof_project_writing_query->max_num_pages ),
						'current' => proof_get_projects_writing_current_page(),
					)
				);

				if ( $proof_project_writing_pagination ) :
					?>
					<nav class="proof-pagination" aria-label="<?php esc_attr_e( 'Project articles pagination', 'proof' ); ?>">
						<?php echo wp_kses_post( $proof_project_writing_pagination ); ?>
					</nav>
					<?php
				endif;

				wp_reset_postdata();
				?>
			</section>

		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
