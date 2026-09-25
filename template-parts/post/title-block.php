<?php
/**
 * Post title block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$deck = get_post_meta( get_the_ID(), 'proof_deck', true );
?>
<header class="proof-post-header">
	<h1 class="proof-page-title"><?php the_title(); ?></h1>

	<?php if ( $deck ) : ?>
		<p class="proof-post-deck"><?php echo esc_html( $deck ); ?></p>
	<?php endif; ?>
</header>
