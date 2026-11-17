<?php
/** Recover a stale masthead document after its private release files have gone. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script data-no-defer="1">
(function () {
	// A stale candidate document can outlive its files after an exact release reversal.
	document.addEventListener('error', function (event) {
		const asset = event.target;
		if (asset.tagName === 'LINK' && asset.relList.contains('stylesheet')) {
			const url = new URL(asset.href);
			const generated = url.origin === location.origin && /\/litespeed\/(?:u?css)\//.test(url.pathname);
			const layout = asset.id === 'proof-theme-css' && url.pathname.endsWith('/assets/css/theme-layout.css');
			if ((layout || generated) && !document.getElementById('proof-layout-recovery')) {
				// Insert before the failed link. The protected disclosure CSS stays last.
				if (generated && !document.getElementById('proof-style-css')) {
					const base = document.createElement('link');
					base.rel = 'stylesheet';
					base.href = <?php echo wp_json_encode( get_stylesheet_uri() ); ?>;
					asset.before(base);
				}
				const fallback = document.createElement('link');
				fallback.id = 'proof-layout-recovery';
				fallback.rel = 'stylesheet';
				fallback.href = <?php echo wp_json_encode( get_template_directory_uri() . '/assets/css/theme.css' ); ?>;
				asset.before(fallback);
			}
		}
		if (asset.id !== 'proof-navigation-js' || !asset.src) {
			return;
		}
		// Only a confirmed missing release file permits the document-local copy.
		// A blocked or failed script keeps the normal native navigation fallback.
		fetch(asset.src, { method: 'HEAD', cache: 'no-store', mode: 'same-origin' }).then(function (response) {
			if (response.status === 404 || response.status === 410) {
				restoreController();
			}
		}).catch(function () {});
	}, true);

	function restoreController() {
		<?php require get_template_directory() . '/assets/js/navigation-ready.js'; ?>
	}
}());
</script>
