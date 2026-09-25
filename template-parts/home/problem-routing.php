<?php
/**
 * Front page problem routing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="proof-home-problems proof-block">
	<div class="proof-section-heading">
		<h2><?php echo esc_html( proof_get_problem_routing_heading() ); ?></h2>
		<p><?php echo esc_html( proof_get_problem_routing_intro() ); ?></p>
	</div>

	<div class="proof-route-grid">
		<?php foreach ( proof_get_problem_routes() as $route ) : ?>
			<article class="proof-route-card">
				<h3><a href="<?php echo esc_url( $route['url'] ); ?>"><?php echo esc_html( $route['title'] ); ?></a></h3>
				<p><?php echo esc_html( $route['description'] ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
</section>
