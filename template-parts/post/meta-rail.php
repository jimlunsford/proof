<?php
/**
 * Left metadata rail.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$category = proof_get_primary_category();
$updated  = get_the_modified_time( 'U' ) > get_the_time( 'U' );
?>
<aside class="proof-meta-rail" aria-label="<?php esc_attr_e( 'Post metadata', 'proof' ); ?>">
	<div class="proof-meta-desktop">
		<div class="proof-meta-block">
			<p class="proof-meta-label"><?php esc_html_e( 'Published', 'proof' ); ?></p>
			<p><?php echo esc_html( get_the_date() ); ?></p>
		</div>

		<div class="proof-meta-block">
			<p class="proof-meta-label"><?php esc_html_e( 'Reading time', 'proof' ); ?></p>
			<p><?php echo esc_html( proof_get_read_time() ); ?></p>
		</div>

		<?php if ( $category ) : ?>
			<div class="proof-meta-block">
				<p class="proof-meta-label"><?php esc_html_e( 'Category', 'proof' ); ?></p>
				<p><a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></p>
			</div>
		<?php endif; ?>

		<?php if ( $updated ) : ?>
			<div class="proof-meta-block">
				<p class="proof-meta-label"><?php esc_html_e( 'Updated', 'proof' ); ?></p>
				<p><?php echo esc_html( get_the_modified_date() ); ?></p>
			</div>
		<?php endif; ?>
	</div>

	<div class="proof-meta-compact">
		<p class="proof-meta-compact-row">
			<span><strong><?php esc_html_e( 'Published:', 'proof' ); ?></strong> <?php echo esc_html( get_the_date() ); ?></span>
			<span class="proof-meta-separator" aria-hidden="true">&middot;</span>
			<span><strong><?php esc_html_e( 'Reading time:', 'proof' ); ?></strong> <?php echo esc_html( proof_get_read_time() ); ?></span>
		</p>

		<p class="proof-meta-compact-row">
			<?php if ( $category ) : ?>
				<span><strong><?php esc_html_e( 'Category:', 'proof' ); ?></strong> <a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></span>
			<?php endif; ?>

			<?php if ( $updated ) : ?>
				<?php if ( $category ) : ?>
					<span class="proof-meta-separator" aria-hidden="true">&middot;</span>
				<?php endif; ?>
				<span><strong><?php esc_html_e( 'Updated:', 'proof' ); ?></strong> <?php echo esc_html( get_the_modified_date() ); ?></span>
			<?php endif; ?>
		</p>
	</div>
</aside>
