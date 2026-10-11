<?php
/**
 * Compact book discovery between Start Here and Selected Work.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$books = proof_get_identity_published_books();
if ( empty( $books ) ) {
	return;
}
?>
<section class="proof-identity-section proof-identity-books" aria-labelledby="proof-identity-books-title">
	<div class="proof-identity-section-heading proof-identity-books-heading">
		<h2 id="proof-identity-books-title"><?php esc_html_e( 'Published Books', 'proof' ); ?></h2>
		<a class="proof-identity-books-catalog-link" href="<?php echo esc_url( home_url( '/books/' ) ); ?>"><?php esc_html_e( 'View All Books', 'proof' ); ?></a>
	</div>
	<div class="proof-identity-books-grid">
		<?php foreach ( $books as $book ) : ?>
			<article class="proof-identity-book-card">
				<?php
				echo wp_get_attachment_image(
					$book['image_id'],
					'medium',
					false,
					array(
						'alt'      => sprintf( __( '%s book cover by Jim Lunsford', 'proof' ), $book['title'] ),
						'sizes'    => '(max-width: 380px) 90px, 112px',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
				?>
				<div class="proof-identity-book-copy">
					<h3><?php echo esc_html( $book['title'] ); ?></h3>
					<?php if ( '' !== $book['description'] ) : ?>
						<p><?php echo esc_html( $book['description'] ); ?></p>
					<?php endif; ?>
					<a href="<?php echo esc_url( $book['url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Explore %s', 'proof' ), $book['title'] ) ); ?>"><?php esc_html_e( 'Explore Book', 'proof' ); ?></a>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
