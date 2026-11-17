/** A private WordPress install with the real LiteSpeed guest endpoint. */
import { readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createNodeFsMountHandler, loadNodeRuntime } from '@php-wasm/node';
import { installPlugin } from '@wp-playground/blueprints';
import { bootWordPressAndRequestHandler } from '@wp-playground/wordpress';

const archive = (path) => new File([readFileSync(path)], 'distribution.zip');

export async function createSite(origin, theme, cache) {
	if (new URL(origin).hostname !== '127.0.0.1') throw new Error('Loopback required');
	const sqlite = join(
		dirname(fileURLToPath(import.meta.resolve('@wp-playground/cli'))),
		'sqlite-database-integration.zip',
	);
	const site = await bootWordPressAndRequestHandler({
		siteUrl: origin,
		sapiName: 'cgi-fcgi',
		phpVersion: '8.3',
		maxPhpInstances: 1,
		cookieStore: false,
		createPhpRuntime: () => loadNodeRuntime('8.3', { emscriptenOptions: { processId: process.pid } }),
		wordPressZip: archive(join(cache, 'wordpress-7.1.4.zip')),
		sqliteIntegrationPluginZip: archive(sqlite),
		constants: { WP_ENVIRONMENT_TYPE: 'local', DISABLE_WP_CRON: true, WP_HTTP_BLOCK_EXTERNAL: true },
		hooks: {
			beforeDatabaseSetup: async (php) => {
				php.mkdir('/wordpress/wp-content/themes/proof');
				await php.mount('/wordpress/wp-content/themes/proof', createNodeFsMountHandler(theme));
			},
		},
	});
	try {
		const php = await site.getPrimaryPhp();
		await installPlugin(php, {
			pluginData: archive(join(cache, 'litespeed-cache.7.9.3.zip')),
			options: { activate: true },
		});
		const result = await php.run({
			code: `<?php require '/wordpress/wp-load.php';
			switch_theme('proof'); update_option('blogname','Private Proof test');
			update_option('litespeed.conf.guest',true); update_option('litespeed.conf.guest_optm',true);
			update_option('litespeed.conf.optm-js_defer',1);
			$GLOBALS['wp_rewrite']->set_permalink_structure('/%postname%/');
			foreach(['Books','Contact','Self-trust field notes'] as $t) wp_insert_post([
				'post_title'=>$t,'post_name'=>$t==='Contact'?'contact-jim-lunsford':sanitize_title($t),
				'post_type'=>$t==='Self-trust field notes'?'post':'page','post_status'=>'publish','post_content'=>'Private test content.'
			]);
			$m=wp_create_nav_menu('Primary');
			foreach(['Books'=>'/books/','Contact'=>'/contact-jim-lunsford/','Home'=>'/'] as $t=>$path)
				wp_update_nav_menu_item($m,0,['menu-item-title'=>$t,'menu-item-url'=>home_url($path),'menu-item-type'=>'custom','menu-item-status'=>'publish']);
			set_theme_mod('nav_menu_locations',['primary'=>$m]); flush_rewrite_rules(); wp_mkdir_p(WPMU_PLUGIN_DIR);
			echo json_encode([$wp_version,PHP_VERSION,wp_get_theme()->get('Version')]);`,
		});
		if (result.exitCode) throw new Error(result.text);
		php.writeFile(
			'/wordpress/wp-content/mu-plugins/navigation-fixture.php',
			`<?php
			add_filter('pre_http_request', function() {return new WP_Error('offline','Loopback only');},PHP_INT_MAX);
			if(isset($_GET['visible_fixture'])) add_filter('theme_mod_proof_identity_hide_front_nav','__return_false');
			if(isset($_SERVER['HTTP_X_PROOF_PLAIN_GUEST'])) define('LITESPEED_GUEST',true);
			add_action('wp_footer',function(){echo '<script>window.unrelatedFixture=1;</script>';},30);`,
		);
		php.writeFile(
			'/wordpress/wp-content/.litespeed_conf.php',
			'<?php exit; // LiteSpeed Cache runtime configuration. Do not edit. ?>\n' +
				JSON.stringify({ hash: 'disposable-navigation-test', guest_cookie: { path: '/', domain: '' } }),
		);
		return {
			handler: site,
			versions: JSON.parse(result.text),
			generatedFile(path) {
				const name = '/wordpress' + path;
				return php.fileExists(name) ? php.readFileAsBuffer(name) : null;
			},
		};
	} catch (error) {
		await site[Symbol.asyncDispose]();
		throw error;
	}
}
