<?php
/**
 * Search form template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$proof_search_id = wp_unique_id( 'proof-search-field-' );
?>
<form role="search" method="get" class="search-form proof-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $proof_search_id ); ?>"><?php esc_html_e( 'Search for:', 'proof' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $proof_search_id ); ?>" class="search-field" placeholder="<?php echo esc_attr__( 'Search articles, notes, standards, and essays', 'proof' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="search-submit"><?php esc_html_e( 'Search', 'proof' ); ?></button>
</form>
