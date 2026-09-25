<?php
/**
 * Identity hub front page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$profile_image_id       = proof_get_identity_profile_image_id();
$name                   = proof_get_identity_name();
$identity_line          = proof_get_identity_line();
$bio                    = proof_get_identity_bio();
$primary_button_label   = proof_get_identity_primary_button_label();
$primary_button_url     = proof_get_identity_primary_button_url();
$secondary_button_label = proof_get_identity_secondary_button_label();
$secondary_button_url   = proof_get_identity_secondary_button_url();
$destinations           = proof_get_identity_destination_links();
$gallery_image_ids      = proof_get_identity_gallery_image_ids();
$social_links           = proof_get_identity_social_links();
?>
<div class="proof-container proof-identity-home">
	<section class="proof-identity-card" aria-labelledby="proof-identity-title">
		<div class="proof-identity-profile">
			<?php if ( $profile_image_id ) : ?>
				<div class="proof-identity-avatar">
					<?php echo proof_get_identity_avatar_image( $profile_image_id ); ?>
				</div>
			<?php endif; ?>

			<div class="proof-identity-copy">
				<?php if ( $name ) : ?>
					<h1 id="proof-identity-title" class="proof-identity-name"><?php echo esc_html( $name ); ?></h1>
				<?php endif; ?>

				<?php if ( $identity_line ) : ?>
					<p class="proof-identity-line"><?php echo esc_html( $identity_line ); ?></p>
				<?php endif; ?>


				<?php if ( $bio ) : ?>
					<p class="proof-identity-bio"><?php echo wp_kses_post( $bio ); ?></p>
				<?php endif; ?>

				<?php if ( ( $primary_button_label && $primary_button_url ) || ( $secondary_button_label && $secondary_button_url ) ) : ?>
					<div class="proof-identity-actions" aria-label="<?php esc_attr_e( 'Primary links', 'proof' ); ?>">
						<?php if ( $primary_button_label && $primary_button_url ) : ?>
							<a class="proof-button proof-button-primary" href="<?php echo esc_url( $primary_button_url ); ?>"><?php echo esc_html( $primary_button_label ); ?></a>
						<?php endif; ?>

						<?php if ( $secondary_button_label && $secondary_button_url ) : ?>
							<a class="proof-button proof-button-secondary" href="<?php echo esc_url( $secondary_button_url ); ?>"><?php echo esc_html( $secondary_button_label ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $destinations ) ) : ?>
		<?php $destinations_heading = proof_get_identity_destinations_heading(); ?>
		<section class="proof-identity-section proof-identity-destinations" <?php echo $destinations_heading ? 'aria-labelledby="proof-identity-destinations-title"' : 'aria-label="' . esc_attr__( 'Homepage destination links', 'proof' ) . '"'; ?>>
			<?php if ( $destinations_heading ) : ?>
				<div class="proof-identity-section-heading">
					<h2 id="proof-identity-destinations-title"><?php echo esc_html( $destinations_heading ); ?></h2>
				</div>
			<?php endif; ?>

			<ul class="proof-identity-link-list">
				<?php foreach ( $destinations as $destination ) : ?>
					<li class="proof-identity-destination-item">
						<a class="proof-identity-destination-link" href="<?php echo esc_url( $destination['url'] ); ?>">
							<span class="proof-identity-destination-title"><?php echo esc_html( $destination['title'] ); ?></span>
							<?php if ( ! empty( $destination['description'] ) ) : ?>
								<span class="proof-identity-destination-description"><?php echo wp_kses( $destination['description'], proof_card_description_allowed_html() ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/home/latest-work' ); ?>

	<?php if ( ! empty( $gallery_image_ids ) ) : ?>
		<section class="proof-identity-image-strip" aria-label="<?php esc_attr_e( 'Life and work images', 'proof' ); ?>">
			<?php foreach ( $gallery_image_ids as $image_id ) : ?>
				<div class="proof-identity-strip-item">
					<?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'class' => 'proof-identity-strip-image' ) ); ?>
				</div>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $social_links ) ) : ?>
		<?php $social_heading = proof_get_identity_social_heading(); ?>
		<section class="proof-identity-section proof-identity-social" <?php echo $social_heading ? 'aria-labelledby="proof-identity-social-title"' : 'aria-label="' . esc_attr__( 'Social and contact links', 'proof' ) . '"'; ?>>
			<?php if ( $social_heading ) : ?>
				<h2 id="proof-identity-social-title"><?php echo esc_html( $social_heading ); ?></h2>
			<?php endif; ?>

			<ul class="proof-identity-social-list">
				<?php foreach ( $social_links as $social_link ) : ?>
					<li class="proof-identity-social-item">
						<a class="proof-identity-social-link" href="<?php echo esc_url( $social_link['url'] ); ?>">
							<span><?php echo esc_html( $social_link['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
</div>
