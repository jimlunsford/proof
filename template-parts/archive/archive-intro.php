<?php
/**
 * Archive intro.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lane    = get_query_var( 'proof_lane_data' );
$compact = (bool) get_query_var( 'proof_archive_compact', false );
?>
<header class="proof-archive-header proof-block">
	<h1 class="proof-page-title">
		<?php echo ! empty( $lane['title'] ) ? esc_html( $lane['title'] ) : esc_html( single_cat_title( '', false ) ); ?>
	</h1>

	<?php if ( ! $compact && ! empty( $lane['description'] ) ) : ?>
		<p class="proof-archive-description"><?php echo esc_html( $lane['description'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! $compact && ! empty( $lane['start_here_url'] ) ) : ?>
		<p class="proof-link-row">
			<a href="<?php echo esc_url( $lane['start_here_url'] ); ?>"><?php esc_html_e( 'Start here', 'proof' ); ?></a>
			<a href="<?php echo esc_url( proof_get_subscribe_url() ); ?>"><?php esc_html_e( 'Subscribe', 'proof' ); ?></a>
		</p>
	<?php endif; ?>
</header>
