/** Boot a fresh local WordPress site, run actual browser checks, then dispose it. */
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { mkdirSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { runCLI } from '@wp-playground/cli';

const here = dirname(fileURLToPath(import.meta.url));
const theme = resolve(here, '../..');
const temp = mkdtempSync(join(tmpdir(), 'proof-browser-'));
const output = process.env.PROOF_BROWSER_OUTPUT ? resolve(process.env.PROOF_BROWSER_OUTPUT) : join(temp, 'results');
mkdirSync(output, { recursive: true });
const blueprint = {
	steps: [
		{ step: 'defineWpConfigConsts', consts: { WP_ENVIRONMENT_TYPE: 'local', PROOF_DISPOSABLE_TEST_SITE: true, DISABLE_WP_CRON: true } },
		{ step: 'runPHP', code: `<?php
require '/wordpress/wp-load.php';
if ('local' !== wp_get_environment_type() || !defined('PROOF_DISPOSABLE_TEST_SITE') || true !== PROOF_DISPOSABLE_TEST_SITE) { throw new RuntimeException('Disposable local site required'); }
foreach (get_posts(['post_type'=>'any','post_status'=>array_keys(get_post_stati()),'numberposts'=>-1]) as $post) { wp_delete_post($post->ID,true); }
switch_theme('proof');
require '/proof-tests/browser-fixture.php';` },
	],
};
let server;
try {
	server = await runCLI({
		command: 'server', wp: '7.1.3', php: '8.3', port: 9482, blueprint,
		mount: [
			{ hostPath: theme, vfsPath: '/wordpress/wp-content/themes/proof' },
			{ hostPath: here, vfsPath: '/proof-tests' },
		],
	});
	const status = await new Promise((resolveExit, reject) => {
		const child = spawn(process.execPath, [resolve(here, '../browser-navigation.mjs')], {
			stdio: 'inherit',
			env: {
				...process.env,
				PROOF_TEST_BASE_URL: server.serverUrl + '/',
				PROOF_DISPOSABLE_BROWSER_TEST: '1',
				PROOF_PLAYWRIGHT_MODULE: process.env.PROOF_PLAYWRIGHT_MODULE || import.meta.resolve('playwright'),
				PROOF_BROWSER_OUTPUT: output,
			},
		});
		child.on('error', reject);
		child.on('exit', resolveExit);
	});
	assert.equal(status, 0, 'Browser acceptance child must succeed.');
	console.log(`Browser evidence: ${output}`);
} finally {
	if (server) await server[Symbol.asyncDispose]();
	// Explicit output directories persist; otherwise preserve evidence in temp.
	if (process.env.PROOF_BROWSER_OUTPUT) rmSync(temp, { recursive: true, force: true });
}
