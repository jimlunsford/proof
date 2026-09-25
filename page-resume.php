<?php
/**
 * Template Name: Resume
 * Template Post Type: page
 *
 * Resume page template.
 */

get_header();
?>

<div class="proof-container proof-resume-layout">
	<?php
	while ( have_posts() ) :
		the_post();

		$proof_resume_page_content = trim( get_the_content() );
		?>
		<article id="page-<?php the_ID(); ?>" <?php post_class( 'proof-resume-page' ); ?>>
			<header class="proof-resume-header proof-archive-header proof-block">
				<h1 class="proof-page-title"><?php the_title(); ?></h1>
				<p class="proof-resume-headline"><?php echo esc_html( proof_get_resume_headline() ); ?></p>
				<p class="proof-resume-summary"><?php echo esc_html( proof_get_resume_summary() ); ?></p>
				<p class="proof-resume-availability"><?php echo esc_html( proof_get_resume_interest_line() ); ?></p>

				<p class="proof-link-row proof-resume-actions">
					<a href="<?php echo esc_url( proof_get_contact_url() ); ?>"><?php esc_html_e( 'Contact', 'proof' ); ?></a>
					<a href="https://github.com/jimlunsford"><?php esc_html_e( 'GitHub', 'proof' ); ?></a>
					<a href="<?php echo esc_url( proof_get_projects_url() ); ?>"><?php esc_html_e( 'Projects', 'proof' ); ?></a>
					<a href="<?php echo esc_url( proof_get_the_work_url() ); ?>"><?php esc_html_e( 'The Work', 'proof' ); ?></a>
				</p>
			</header>

			<?php if ( '' !== $proof_resume_page_content ) : ?>
				<div class="proof-entry-content proof-resume-editor-note proof-block">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<section class="proof-resume-section proof-block" aria-labelledby="proof-resume-capabilities-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-capabilities-heading"><?php esc_html_e( 'Core Capabilities', 'proof' ); ?></h2>
				</div>
				<div class="proof-resume-capability-grid">
					<?php foreach ( proof_get_resume_capabilities() as $capability ) : ?>
						<section class="proof-resume-capability">
							<h3><?php echo esc_html( $capability['title'] ); ?></h3>
							<ul>
								<?php foreach ( $capability['items'] as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="proof-resume-section proof-block" aria-labelledby="proof-resume-selected-work-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-selected-work-heading"><?php esc_html_e( 'Selected Work', 'proof' ); ?></h2>
					<p><?php esc_html_e( 'Current web, publishing, and product work that can be inspected directly.', 'proof' ); ?></p>
				</div>
				<div class="proof-resume-work-list">
					<?php foreach ( proof_get_resume_selected_work() as $work ) : ?>
						<article class="proof-resume-work-item">
							<p class="proof-resume-label"><?php echo esc_html( $work['label'] ); ?></p>
							<div class="proof-resume-work-copy">
								<h3><?php echo esc_html( $work['title'] ); ?></h3>
								<p><?php echo esc_html( $work['description'] ); ?></p>
								<?php if ( ! empty( $work['links'] ) ) : ?>
									<p class="proof-resume-inline-links">
										<?php foreach ( $work['links'] as $index => $link ) : ?>
											<?php if ( 0 < $index ) : ?><span aria-hidden="true"> · </span><?php endif; ?>
											<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
										<?php endforeach; ?>
									</p>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="proof-resume-section proof-block" aria-labelledby="proof-resume-experience-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-experience-heading"><?php esc_html_e( 'Professional Experience', 'proof' ); ?></h2>
				</div>
				<div class="proof-resume-experience-list">
					<?php foreach ( proof_get_resume_experience() as $job ) : ?>
						<article class="proof-resume-job">
							<header class="proof-resume-job-header">
								<div>
									<h3><?php echo esc_html( $job['role'] ); ?></h3>
									<p class="proof-resume-employer"><?php echo esc_html( $job['employer'] ); ?></p>
								</div>
								<p class="proof-resume-job-meta"><?php echo esc_html( $job['dates'] ); ?><br><?php echo esc_html( $job['location'] ); ?></p>
							</header>
							<ul>
								<?php foreach ( $job['bullets'] as $bullet ) : ?>
									<li><?php echo esc_html( $bullet ); ?></li>
								<?php endforeach; ?>
							</ul>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="proof-resume-section proof-block" aria-labelledby="proof-resume-technical-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-technical-heading"><?php esc_html_e( 'Earlier Technical Experience', 'proof' ); ?></h2>
					<p><?php esc_html_e( 'The technical foundation behind my current web, publishing, support, and product work.', 'proof' ); ?></p>
				</div>
				<div class="proof-resume-experience-list">
					<?php foreach ( proof_get_resume_technical_experience() as $job ) : ?>
						<article class="proof-resume-job">
							<header class="proof-resume-job-header">
								<div>
									<h3><?php echo esc_html( $job['role'] ); ?></h3>
									<p class="proof-resume-employer"><?php echo esc_html( $job['employer'] ); ?></p>
								</div>
								<p class="proof-resume-job-meta"><?php echo esc_html( $job['dates'] ); ?><br><?php echo esc_html( $job['location'] ); ?></p>
							</header>
							<ul>
								<?php foreach ( $job['bullets'] as $bullet ) : ?>
									<li><?php echo esc_html( $bullet ); ?></li>
								<?php endforeach; ?>
							</ul>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="proof-resume-section proof-block" aria-labelledby="proof-resume-receipts-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-receipts-heading"><?php esc_html_e( 'Proof of Work', 'proof' ); ?></h2>
					<p><?php esc_html_e( 'Builder Receipts document the problem, decisions, implementation direction, testing, corrections, and verified result behind substantial work.', 'proof' ); ?></p>
				</div>
				<ul class="proof-resume-proof-list">
					<?php foreach ( proof_get_resume_receipts() as $receipt ) : ?>
						<li class="proof-resume-proof-item">
							<a class="proof-resume-proof-link" href="<?php echo esc_url( $receipt['url'] ); ?>">
								<span class="proof-resume-label"><?php echo esc_html( $receipt['label'] ); ?></span>
								<span class="proof-resume-proof-copy">
									<span class="proof-resume-proof-title"><?php echo esc_html( $receipt['title'] ); ?></span>
									<span class="proof-resume-proof-description"><?php echo esc_html( $receipt['description'] ); ?></span>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="proof-resume-section proof-block" aria-labelledby="proof-resume-training-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-training-heading"><?php esc_html_e( 'Education & Training', 'proof' ); ?></h2>
				</div>
				<ul class="proof-resume-training-list">
					<?php foreach ( proof_get_resume_training() as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="proof-resume-closing proof-block" aria-labelledby="proof-resume-closing-heading">
				<div class="proof-section-heading">
					<h2 id="proof-resume-closing-heading"><?php esc_html_e( 'Let\'s Talk About the Right Role', 'proof' ); ?></h2>
				</div>
				<p>I am currently interested in opportunities involving web publishing, WordPress and CMS platforms, product or technical support, implementation, documentation, QA and testing, technical operations, and practical AI-assisted workflows.</p>
				<p>I bring an unusual combination of technical experience, publishing work, product judgment, documentation, user support, operational experience, and the ability to work calmly through problems that do not arrive with clean instructions.</p>
				<p class="proof-link-row proof-resume-actions">
					<a class="proof-button proof-button-primary" href="<?php echo esc_url( proof_get_contact_url() ); ?>"><?php esc_html_e( 'Contact Jim', 'proof' ); ?></a>
					<a class="proof-button proof-button-secondary" href="<?php echo esc_url( proof_get_projects_url() ); ?>"><?php esc_html_e( 'View Projects', 'proof' ); ?></a>
				</p>
			</section>
		</article>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
