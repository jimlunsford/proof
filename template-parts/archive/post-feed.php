<?php
/**
 * Archive feed item.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$category           = proof_get_primary_category( get_the_ID() );
$show_feed_category = get_query_var( 'proof_show_feed_category', true );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'proof-feed-item' ); ?>>
	<?php if ( $category && $show_feed_category ) : ?>
		<p class="proof-kicker proof-kicker-linked">
			<a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
		</p>
	<?php endif; ?>

	<h3 class="proof-feed-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

	<div class="proof-feed-meta">
		<span><?php echo esc_html( get_the_date() ); ?></span>
		<span><?php echo esc_html( proof_get_read_time() ); ?></span>
	</div>

	<?php $proof_clean_excerpt = proof_get_clean_excerpt( get_the_ID() ); ?>
	<?php if ( '' !== $proof_clean_excerpt ) : ?>
		<div class="proof-feed-excerpt">
			<p><?php echo esc_html( $proof_clean_excerpt ); ?></p>
		</div>
	<?php endif; ?>

	<p class="proof-read-more">
		<a href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Read more', 'proof' ); ?><span class="screen-reader-text"> <?php echo esc_html( sprintf( __( 'about %s', 'proof' ), get_the_title() ) ); ?></span></a>
	</p>
</article>
