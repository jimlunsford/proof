<?php
/**
 * 404 template.
 */

get_header();
?>

<div class="proof-container proof-error-layout">
	<section class="proof-block">
		<h1 class="proof-page-title"><?php esc_html_e( 'Page not found.', 'proof' ); ?></h1>
		<p class="proof-error-intro"><?php esc_html_e( 'The link may be broken, the page may have moved, or the URL may be off. Use search or start with one of these paths.', 'proof' ); ?></p>
		<div class="proof-404-search">
			<?php get_search_form(); ?>
		</div>
	</section>

	<section class="proof-block">
		<div class="proof-section-heading">
			<h2><?php esc_html_e( 'Get Back on Track', 'proof' ); ?></h2>
			<p><?php esc_html_e( 'These are the clearest ways back into the site.', 'proof' ); ?></p>
		</div>

		<div class="proof-route-grid">
			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( proof_get_post_url_by_path( 'start-here-raise-your-standards' ) ); ?>"><?php esc_html_e( 'Start Here', 'proof' ); ?></a></h3>
				<p><?php esc_html_e( 'Begin with the core idea behind the work and the clearest way into the site.', 'proof' ); ?></p>
			</article>

			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( proof_get_category_archive_url( 'articles' ) ); ?>"><?php esc_html_e( 'Articles', 'proof' ); ?></a></h3>
				<p><?php esc_html_e( 'Read the long-form articles that teach the deeper frameworks and ideas.', 'proof' ); ?></p>
			</article>

			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( proof_get_category_archive_url( 'recovery-standards' ) ); ?>"><?php esc_html_e( 'Recovery Standards', 'proof' ); ?></a></h3>
				<p><?php esc_html_e( 'Go straight to short, direct standards for recovery, structure, and self-governance.', 'proof' ); ?></p>
			</article>

			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( proof_get_category_archive_url( 'discipline-dispatch' ) ); ?>"><?php esc_html_e( 'Discipline Dispatch', 'proof' ); ?></a></h3>
				<p><?php esc_html_e( 'Find shorter writing on discipline, ownership, identity, and standards.', 'proof' ); ?></p>
			</article>

			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( proof_get_category_archive_url( 'recovery-beyond-aa' ) ); ?>"><?php esc_html_e( 'Recovery Beyond AA', 'proof' ); ?></a></h3>
				<p><?php esc_html_e( 'Read the essays for a recovery path built on autonomy instead of dependency.', 'proof' ); ?></p>
			</article>

			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( home_url( '/about-jim-lunsford-discipline/' ) ); ?>"><?php esc_html_e( 'About Jim Lunsford', 'proof' ); ?></a></h3>
				<p><?php esc_html_e( 'Get the background behind the writing, the mission, and the person behind the site.', 'proof' ); ?></p>
			</article>
		</div>
	</section>
</div>

<?php
get_footer();
