/** Run with npm run navigation-release, PROOF_NAV_BASE and PROOF_TEST_PLUGIN_CACHE. */
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { createServer } from 'node:http';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { setTimeout as pause } from 'node:timers/promises';
import { chromium } from 'playwright';
import { createSite } from './navigation-site.mjs';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const cache = resolve(process.env.PROOF_TEST_PLUGIN_CACHE);
const old = resolve(process.env.PROOF_NAV_BASE);
assert.notEqual(root, old);
const output = resolve(process.env.PROOF_NAV_OUTPUT || 'navigation-results');
mkdirSync(output, { recursive: true });
const sha = (bytes) => createHash('sha256').update(bytes).digest('hex');
const prefix = '/wp-content/themes/proof/';
const menu = '.proof-menu-toggle',
	nav = '.proof-primary-nav',
	search = '.proof-nav-search-link',
	field = '#proof-search-panel input.search-field';
const UA = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36';
const oldJS = 'assets/js/navigation.js',
	newJS = 'assets/js/navigation-ready.js';
const reports = [],
	sites = {},
	snapshots = {},
	generated = new Map();
let active = 'old',
	policy,
	record,
	browser,
	origin;

function latch() {
	let release;
	const promise = new Promise((resolve) => {
		release = resolve;
	});
	return { promise, release };
}
function fresh() {
	return {
		holdReply: false,
		arrived: latch(),
		release: latch(),
		documentSeen: latch(),
		allowDocument: latch(),
		scriptSeen: latch(),
		allowScript: latch(),
		docs: 0,
	};
}
function check(ok, label) {
	assert.ok(ok, label);
	record.checks.push(label);
}
function waitGate(gate, label) {
	return Promise.race([
		gate.promise,
		pause(8500, null, { ref: false }).then(() => {
			throw Error('Timeout ' + label);
		}),
	]);
}
const server = createServer(async (req, res) => {
	const path = new URL(req.url, origin).pathname,
		mode = policy,
		row = record,
		release = active;
	const info = { time: Date.now(), method: req.method, path: req.url, release };
	if (row) row.requests.push(info);
	try {
		const headers = { ...req.headers };
		if (path.startsWith(prefix)) {
			const rel = path.slice(prefix.length);
			if (!/^(assets\/(css|js)\/[\w.-]+|style\.css)$/.test(rel)) {
				res.writeHead(404);
				res.end();
				return;
			}
			if (rel === newJS && mode.blockScript) {
				info.status = 403;
				res.writeHead(403);
				res.end();
				return;
			}
			if (rel === newJS && mode.holdScript) {
				mode.scriptSeen.release();
				await mode.allowScript.promise;
			}
			const file = join(release === 'old' ? old : root, rel);
			if (!existsSync(file)) {
				info.status = 404;
				res.writeHead(404, { 'cache-control': 'no-store' });
				res.end();
				return;
			}
			const bytes = readFileSync(file);
			info.sha256 = sha(bytes);
			info.bytes = bytes.length;
			const isOldJS = rel === oldJS;
			const etag = isOldJS ? '"6af1fa30-b0f"' : '"' + info.sha256 + '"';
			const assetHeaders = {
				'Content-Type': rel.endsWith('.js') ? 'application/javascript' : 'text/css',
				ETag: etag,
				'Last-Modified': isOldJS ? 'Mon, 09 Nov 2026 16:52:00 GMT' : 'Mon, 16 Nov 2026 12:00:00 GMT',
			};
			if (mode.longCache) assetHeaders['Cache-Control'] = 'public, max-age=86400';
			info.conditional = headers['if-none-match'] || null;
			info.status = info.conditional === etag ? 304 : 200;
			res.writeHead(info.status, assetHeaders);
			res.end(req.method === 'HEAD' || info.status === 304 ? undefined : bytes);
			return;
		}
		const endpoint = path.endsWith('/guest.vary.php');
		const doc = headers['sec-fetch-dest'] === 'document';
		if (doc) {
			mode.docs++;
			if ((mode.holdDoc === 'replacement' && mode.docs === 2) || mode.holdDoc === req.url) {
				mode.documentSeen.release();
				await mode.allowDocument.promise;
			}
		}
		if (mode.ordinary || (endpoint && mode.reply === 'empty'))
			headers.cookie = (headers.cookie || '') + '; _lscache_vary=fixture-existing-vary';
		if (mode.plainGuest) headers['x-proof-plain-guest'] = '1';
		if (/^\/wp-content\/litespeed\/(?:js|css)\/[a-zA-Z0-9._-]+$/.test(path)) {
			let content = sites[release].generatedFile(path);
			if (content) generated.set(req.url, Buffer.from(content));
			else if (!mode.discardGenerated && generated.has(req.url)) {
				content = generated.get(req.url);
				info.retainedGenerated = true;
			}
			info.status = content ? 200 : 404;
			if (content) {
				info.bytes = content.length;
				info.sha256 = sha(content);
			}
			res.writeHead(info.status, {
				'Content-Type': path.endsWith('.js') ? 'application/javascript' : 'text/css',
			});
			res.end(content || '');
			return;
		}
		const frozen = doc && mode.stale;
		const response = frozen
			? snapshots[mode.stale]
			: await sites[release].handler.request({ url: req.url, method: req.method, headers });
		let bytes = Buffer.from(response.bytes);
		const returned = { ...response.headers };
		if (doc) {
			if (mode.save) {
				snapshots[mode.save] = response;
				writeFileSync(join(output, mode.save + '.html'), bytes);
			}
			if (mode.duplicate) {
				const html = bytes.toString();
				const tag = html.match(/<script data-no-optimize="1">var litespeed_vary=[\s\S]*?<\/script>/)?.[0];
				assert.ok(tag, 'real guest bootstrap');
				bytes = Buffer.from(html.replace(tag, tag + tag));
			}
			if (row)
				row.html.push({
					time: Date.now(),
					path: req.url,
					release,
					frozen: frozen || null,
					hash: sha(bytes),
					protected: bytes.includes('id="proof-masthead-css"'),
					decisionGuard: bytes.includes('&&document.dispatchEvent(new Event("proof:guest-reload"'),
					delayedScripts: (bytes.toString().match(/type="litespeed\/javascript"/g) || []).length,
				});
			returned['cache-control'] = mode.cacheHTML ? 'public,max-age=600' : 'no-store';
			delete returned['content-length'];
		}
		if (endpoint) {
			info.vendorStatus = response.httpStatusCode;
			info.body = bytes.toString();
			info.endpointReady = Date.now();
			info.varyCookie = Boolean(returned['set-cookie']);
			mode.arrived.release();
			if (mode.holdReply) await mode.release.promise;
			info.released = Date.now();
			if (mode.reply === 'failed') {
				info.fault = 'connection reset';
				res.destroy();
				return;
			}
			if (mode.reply === 'unavailable') {
				info.status = 503;
				res.writeHead(503, { 'Content-Type': 'application/json' });
				res.end('{}');
				return;
			}
		}
		info.status = response.httpStatusCode;
		res.writeHead(response.httpStatusCode, returned);
		res.end(bytes);
	} catch (error) {
		info.error = String(error);
		if (!res.headersSent) res.writeHead(500);
		res.end();
	}
});

async function state(page) {
	return page.evaluate(() => {
		const get = (s) => document.querySelector(s),
			rect = (e) => e?.getBoundingClientRect().width || 0;
		const m = get('.proof-menu-toggle'),
			n = get('.proof-primary-nav'),
			s = get('.proof-nav-search-link'),
			f = get('#proof-search-panel input.search-field');
		return {
			doc: performance.timeOrigin,
			ms: performance.now(),
			url: location.pathname + location.search,
			type: performance.getEntriesByType('navigation')[0].type,
			ready: document.documentElement.classList.contains('proof-nav-ready'),
			leaving: document.documentElement.classList.contains('proof-guest-reloading'),
			menu: m?.getAttribute('aria-expanded'),
			search: s?.getAttribute('aria-expanded'),
			menuVisible: !!m && getComputedStyle(m).visibility !== 'hidden',
			navWidth: rect(n),
			fieldWidth: rect(f),
			value: f?.value,
			focus: document.activeElement?.className,
			bodyFont: getComputedStyle(document.body).fontFamily,
			themeInk: getComputedStyle(document.documentElement).getPropertyValue('--proof-text').trim(),
			layoutWidth: rect(document.querySelector('.proof-container')),
			recoveredLayout: Boolean(document.getElementById('proof-layout-recovery')?.sheet),
			selection: f && [f.selectionStart, f.selectionEnd],
			keys: Object.keys(sessionStorage),
			varyGuard: sessionStorage.getItem('litespeed_reloaded'),
			assets: performance
				.getEntriesByType('resource')
				.filter((e) => /\/assets\/(js|css)\/|\/themes\/proof\/style.css/.test(e.name))
				.map((e) => ({
					url: new URL(e.name).pathname + new URL(e.name).search,
					transfer: e.transferSize,
					encoded: e.encodedBodySize,
				})),
		};
	});
}
async function instrument(context) {
	const page = await context.newPage(),
		row = record;
	const cdp = await context.newCDPSession(page);
	await cdp.send('Network.enable');
	cdp.on('Network.responseReceived', ({ response }) => {
		if (response.url.includes(prefix))
			row.cdp.push({
				time: Date.now(),
				url: new URL(response.url).pathname + new URL(response.url).search,
				status: response.status,
				disk: response.fromDiskCache,
				prefetch: response.fromPrefetchCache,
			});
	});
	const bodies = [];
	page.on('response', (response) => {
		if (!response.url().includes(prefix) || response.request().method() !== 'GET') return;
		bodies.push(
			(async () => {
				try {
					const bytes = await response.body();
					row.bytes.push({
						url: response.url().slice(origin.length),
						status: response.status(),
						bytes: bytes.length,
						sha256: sha(bytes),
					});
				} catch (error) {
					row.bytes.push({
						url: response.url().slice(origin.length),
						error: String(error).split('\n')[0],
					});
				}
			})(),
		);
	});
	page.on('pageerror', (e) => row.exceptions.push(String(e)));
	await page.exposeFunction('logNavEvidence', (data) => row.events.push(data));
	await page.addInitScript(() => {
		const log = (kind, extra) =>
			window.logNavEvidence({ kind, doc: performance.timeOrigin, ms: performance.now(), ...extra });
		log('document', { url: location.pathname + location.search });
		document.addEventListener('proof:guest-reload', (event) => {
			queueMicrotask(() => {
				const links = [...document.querySelectorAll('.proof-primary-nav a')];
				const link = links.find((a) => a.textContent === 'Contact'),
					nativeSearch = links.find((a) => a.textContent === 'Search');
				const searchBox = nativeSearch?.getBoundingClientRect();
				const box = link?.getBoundingClientRect(),
					toggle = document.querySelector('.proof-menu-toggle');
				log('proof:guest-reload', {
					prevented: event.defaultPrevented,
					leaving: document.documentElement.classList.contains('proof-guest-reloading'),
					menuVisible: toggle && getComputedStyle(toggle).visibility !== 'hidden',
					width: box?.width,
					x: box && box.x + 4,
					y: box && box.y + box.height / 2,
					searchX: searchBox && searchBox.x + 4,
					searchY: searchBox && searchBox.y + searchBox.height / 2,
				});
			});
		});
		for (const event of [
			'click',
			'pointerdown',
			'keydown',
			'input',
			'submit',
			'focusin',
			'pageshow',
			'pagehide',
		]) {
			window.addEventListener(
				event,
				(e) =>
					log(event, {
						trusted: e.isTrusted,
						target: e.target.className || e.target.nodeName,
						key: e.key,
						prevented: e.defaultPrevented,
						persisted: e.persisted,
					}),
				true,
			);
		}
	});
	page.setDefaultTimeout(7000);
	return { page, bodies };
}
async function activate(page, selector, input = 'mouse') {
	const element = page.locator(selector);
	if (input === 'touch') await element.tap();
	else if (input === 'keyboard') {
		for (let i = 0; i < 48 && !(await element.evaluate((e) => e === document.activeElement)); i++)
			await page.keyboard.press('Tab');
		assert.ok(await element.evaluate((e) => e === document.activeElement));
		await page.keyboard.press('Enter');
	} else await element.click();
}
async function openSearch(page, input) {
	await activate(page, menu, input);
	await activate(page, search, input);
	await page.waitForFunction(() => document.activeElement.matches('#proof-search-panel .search-field'));
	await page.keyboard.type('self-trust');
}
async function ready(page) {
	await page.waitForFunction(() => document.documentElement.classList.contains('proof-nav-ready'));
}
async function run(name, fn, width = 390) {
	policy = fresh();
	record = { name, width, checks: [], requests: [], html: [], events: [], cdp: [], bytes: [], exceptions: [] };
	reports.push(record);
	const context = await browser.newContext({
		viewport: { width, height: 860 },
		userAgent: UA,
		hasTouch: true,
		serviceWorkers: 'block',
	});
	try {
		const { page, bodies } = await instrument(context);
		await Promise.race([
			fn(page, context),
			pause(18000, null, { ref: false }).then(() => {
				throw Error('Case deadline ' + name);
			}),
		]);
		await Promise.allSettled(bodies);
		check(!record.requests.some((r) => r.error), 'fixture served requests without an internal failure');
		if (name === 'network-failed')
			check(
				record.exceptions.length === 1 && /Failed to fetch/i.test(record.exceptions[0]),
				'only the native LiteSpeed rejected fetch was observed',
			);
		else check(!record.exceptions.length, 'no unexpected page exception');
		record.status = 'PASS';
	} catch (error) {
		record.status = 'FAIL';
		record.error = String(error).split('\n').slice(0, 3).join('\n');
	} finally {
		policy.release.release();
		policy.allowDocument.release();
		policy.allowScript.release();
		await context.close();
	}
	console.log(record.status, name, record.error || '');
}

const nativeReplies = () =>
	record.requests.filter((r) => r.method === 'POST' && r.path.endsWith('/guest.vary.php'));
const actualYes = () =>
	check(
		nativeReplies().some((r) => r.vendorStatus === 200 && r.body === '{"reload":"yes"}' && r.varyCookie),
		'installed endpoint issued its affirmative response and vary cookie',
	);
async function inGuest(page, options = {}) {
	Object.assign(policy, { holdReply: true }, options);
	await page.goto(origin + '/books/', { waitUntil: 'commit' });
	await waitGate(policy.arrived, 'real guest POST');
	await ready(page);
	record.before = await state(page);
}
async function releaseDecision(page) {
	policy.release.release();
	await page.waitForFunction(() =>
		performance.getEntriesByType('resource').some((r) => r.name.endsWith('/guest.vary.php')),
	);
	await pause(100);
}
function opened(s, kind) {
	return kind === 'menu'
		? s.menu === 'true' && s.navWidth > 0
		: s.search === 'true' && s.fieldWidth > 0 && s.value === 'self-trust' && /search-field/.test(s.focus);
}
async function continuity(page, kind, input, settings = {}) {
	await inGuest(page, settings);
	if (kind === 'search') {
		await openSearch(page, input);
		await page.keyboard.press('Shift+ArrowLeft');
	} else await activate(page, menu, input);
	record.accepted = await state(page);
	check(opened(record.accepted, kind), 'first trusted action accepted');
	actualYes();
	await releaseDecision(page);
	record.final = await state(page);
	check(
		record.final.doc === record.accepted.doc && opened(record.final, kind),
		'original active document and interaction survived',
	);
	if (settings.plainGuest)
		check(
			!record.requests.some((r) => r.path.includes('/litespeed/css/')),
			'ordinary CSS and native guest POST on the same document',
		);
	else
		check(
			record.requests.some((r) => r.path.includes('/litespeed/css/')),
			'real locally optimized guest stylesheet requested',
		);
	if (kind === 'search' && input === 'keyboard' && !settings.plainGuest && record.width === 320)
		await page.screenshot({ path: join(output, 'guest-search-retained.png') });
	if (kind === 'search')
		check(
			String(record.final.selection) === String(record.accepted.selection),
			'caret and selected text retained',
		);
	check(
		record.events.some((e) => e.kind === 'click' && e.trusted),
		'browser-generated trusted activation',
	);
	check(
		record.events.some((e) => e.kind === 'proof:guest-reload' && e.prevented),
		'only the affirmative replacement was vetoed',
	);
	check(
		!record.final.keys.some((k) => k.startsWith('proof')) && !record.final.varyGuard,
		'no query or simulated reload recorded in session storage',
	);
	await page.keyboard.press('Escape');
	check((await state(page)).focus.includes('proof-menu-toggle'), 'Escape restores the visible Menu focus');
}
async function nativeDestination(page, kind) {
	await inGuest(page);
	const target = kind === 'link' ? '/contact-jim-lunsford/' : '/?s=self-trust';
	policy.holdDoc = target;
	if (kind === 'link') {
		await activate(page, menu);
		await page.locator(nav + ' a', { hasText: 'Contact' }).click({ noWaitAfter: true });
	} else {
		await openSearch(page, 'keyboard');
		await page.keyboard.press('Enter');
	}
	await waitGate(policy.documentSeen, 'native GET');
	policy.release.release();
	await pause(140);
	policy.allowDocument.release();
	await page.waitForURL(origin + target);
	await page.waitForLoadState('domcontentloaded');
	record.final = await state(page);
	actualYes();
	check(
		record.final.url === target && record.final.doc !== record.before.doc,
		'pending native destination won the race',
	);
	check(
		record.requests.filter((r) => r.path === target && r.method === 'GET').length === 1,
		'exactly one native destination GET',
	);
	if (kind === 'get')
		check((await page.locator('.proof-post-feed article').count()) === 1, 'actual WordPress search result');
}
async function settled(page, hold) {
	policy.holdReply = hold;
	await page.goto(origin + '/books/', { waitUntil: 'commit' });
	if (hold) {
		await waitGate(policy.arrived, 'affirmative');
		await pause(250);
		policy.release.release();
	}
	await page.waitForFunction(() => performance.getEntriesByType('navigation')[0].type === 'reload');
	await ready(page);
	await activate(page, menu);
	record.final = await state(page);
	actualYes();
	check(
		opened(record.final, 'menu') && record.final.varyGuard === '1',
		'idle refresh completed and first Menu on the replacement worked',
	);
	check(record.events.filter((e) => e.kind === 'document').length === 2, 'two distinct documents');
	await page.goto(origin + '/contact-jim-lunsford/');
	await ready(page);
	await activate(page, menu);
	check(
		nativeReplies().length === 1 && opened(await state(page), 'menu'),
		'returning visit uses negotiated cookie without another POST',
	);
}
async function loadRelease(page, path, expected) {
	const response = await page.goto(origin + path, { waitUntil: 'load' });
	check(response.status() === 200, 'document HTTP 200');
	if (expected === 'new') await ready(page);
	const s = await state(page);
	const file = expected === 'new' ? newJS : oldJS;
	check(
		s.assets.some((r) => r.url.includes('/' + file + '?ver=')),
		'rendered document requested its release-specific script',
	);
	return s;
}
async function toggleLegacy(page) {
	await page.locator(menu).click();
	check((await state(page)).menu === 'true', 'settled original controller still opens Menu');
	await page.keyboard.press('Escape');
}
async function releaseMatrix(page, ctx, longCache) {
	policy.ordinary = true;
	policy.longCache = longCache;
	active = 'old';
	record.cold = await loadRelease(page, '/books/', 'old');
	await toggleLegacy(page);
	const tab = await instrument(ctx);
	record.warmOld = await loadRelease(tab.page, '/contact-jim-lunsford/', 'old');
	check(
		record.warmOld.assets.find((a) => a.url.includes('/' + oldJS)).transfer === 0,
		'old script reused from the actual HTTP cache',
	);
	active = 'new';
	await toggleLegacy(tab.page);
	record.upgrade = await loadRelease(page, '/contact-jim-lunsford/', 'new');
	await openSearch(page, 'mouse');
	check(opened(await state(page), 'search'), 'first Search on a different page after upgrade works');
	record.warmNew = await loadRelease(page, '/books/', 'new');
	await activate(page, menu);
	check(opened(await state(page), 'menu'), 'corrected warm page opens Menu');
	await page.goBack();
	await ready(page);
	check(page.url().endsWith('/contact-jim-lunsford/'), 'back reached candidate Contact');
	await page.goForward();
	await ready(page);
	check(page.url().endsWith('/books/'), 'forward reached candidate Books');
	await page.reload();
	await ready(page);
	active = 'old';
	await activate(page, menu);
	await activate(page, search);
	check((await state(page)).fieldWidth > 0, 'already-open candidate tab works across reversal');
	record.reversal = await loadRelease(page, '/contact-jim-lunsford/', 'old');
	await toggleLegacy(page);
	await Promise.allSettled(tab.bodies);
	const originalHash = sha(readFileSync(join(old, oldJS))),
		correctedHash = sha(readFileSync(join(root, newJS)));
	check(
		record.bytes.some((r) => r.url.includes('/' + oldJS) && r.sha256 === originalHash) &&
			record.bytes.some((r) => r.url.includes('/' + newJS) && r.sha256 === correctedHash),
		'browser response bytes match both release files',
	);
	check(
		record.bytes
			.filter((r) => r.status === 200 && r.url.includes('/assets/js/'))
			.every((r) => r.sha256 === (r.url.includes('/' + oldJS) ? originalHash : correctedHash)),
		'no cross-release script bytes under either key',
	);
}
async function captureStale(page, which, guest) {
	active = which;
	policy.ordinary = !guest;
	policy.save = which + (guest ? '-guest' : '-plain');
	policy.holdReply = guest;
	await page.goto(origin + '/books/', { waitUntil: 'load' });
	if (guest) await waitGate(policy.arrived, 'guest response');
	if (which === 'old' && guest) {
		await page.mouse.move(20, 40);
		await page.locator(menu).click();
		await pause(250);
	}
	record.captured = await state(page);
	check(Boolean(snapshots[policy.save]), 'saved exact WordPress response bytes');
	if (guest) {
		await page.goto('about:blank');
		policy.release.release();
	}
}
async function checkStale(page, from, guest, discard = false) {
	policy.discardGenerated = discard;
	active = from === 'new' ? 'old' : 'new';
	policy.ordinary = !guest;
	policy.stale = from + (guest ? '-guest' : '-plain');
	policy.holdReply = guest;
	await page.goto(origin + '/books/', { waitUntil: 'load' });
	if (from === 'new') {
		await ready(page);
		await openSearch(page, 'mouse');
		record.accepted = await state(page);
		if (guest) {
			await waitGate(policy.arrived, 'stale guest POST');
			await releaseDecision(page);
			actualYes();
		}
		record.final = await state(page);
		check(
			opened(record.final, 'search'),
			'stale corrected document has working Search on the reversed server',
		);
		check(
			record.requests.some((r) => r.path.includes(newJS) && r.method === 'GET' && r.status === 404) &&
				record.requests.some((r) => r.path.includes(newJS) && r.method === 'HEAD' && r.status === 404),
			'true missing release file confirmed before document fallback',
		);
		check(
			!record.requests.some((r) => r.path.includes(oldJS)),
			'only one controller in the stale corrected document',
		);
		if (guest && !discard)
			check(
				!record.requests.some((r) => r.path.includes('/wp-content/litespeed/') && r.status === 404),
				'theme-only rollback retains the generated dependencies',
			);
		if (guest && discard) {
			check(
				record.requests.some((r) => r.path.includes('/wp-content/litespeed/') && r.status === 404),
				'discarded optimized payload explicitly detected',
			);
			const retained = reports.find((r) => r.name === 'cold-stale-new-guest').final;
			check(
				record.final.recoveredLayout &&
					retained.themeInk !== '' &&
					record.final.themeInk === retained.themeInk &&
					record.final.bodyFont === retained.bodyFont &&
					record.final.layoutWidth === retained.layoutWidth,
				'Proof layout, font and color restored despite discarded generated CSS',
			);
		}
		if (!guest)
			check(
				record.requests.some((r) => r.path.includes('theme-layout.css') && r.status === 404) &&
					record.requests.some((r) => r.path.includes('assets/css/theme.css') && r.status === 200),
				'missing release stylesheet recovered through original stylesheet',
			);
	} else {
		if (guest) {
			await page.mouse.move(20, 40);
			await page.locator(menu).click();
			await pause(250);
			record.oldInteraction = await state(page);
			const original = reports.find((r) => r.name === 'capture-old-guest');
			const regularJS = (r) =>
				r.requests
					.filter((q) => q.path.includes('/litespeed/js/') && q.status === 200)
					.map((q) => q.sha256);
			check(
				regularJS(original).length > 0 && String(regularJS(original)) === String(regularJS(record)),
				'stale original guest retrieved the same optimized JS bytes as the original release',
			);
			check(
				record.oldInteraction.menu === original.captured.menu &&
					record.oldInteraction.navWidth === original.captured.navWidth,
				'stale old guest reproduces the original first-click behavior',
			);
			await page.goto('about:blank');
			policy.release.release();
			check(record.html[0].protected === false, 'stale original guest document retains its original markup');
		} else {
			await toggleLegacy(page);
			record.final = await state(page);
		}
		if (!guest)
			check(
				record.requests.some(
					(r) =>
						r.path.includes(oldJS) &&
						r.status === 200 &&
						r.sha256 === sha(readFileSync(join(old, oldJS))),
				),
				'legacy URL returned original bytes on a cold miss',
			);
	}
}
try {
	await new Promise((r) => server.listen(0, '127.0.0.1', r));
	origin = 'http://127.0.0.1:' + server.address().port;
	sites.old = await createSite(origin, old, cache);
	sites.new = await createSite(origin, root, cache);
	browser = await chromium.launch({
		executablePath: process.env.PROOF_BROWSER_EXECUTABLE,
		args: ['--host-resolver-rules=MAP * ~NOTFOUND, EXCLUDE 127.0.0.1'],
	});
	active = 'new';
	for (const [kind, input, width, plain] of [
		['menu', 'mouse', 390, false],
		['search', 'keyboard', 320, false],
		['search', 'touch', 768, false],
		['menu', 'touch', 1366, true],
		['search', 'mouse', 390, true],
	]) {
		await run(
			[kind, input, plain ? 'plain' : 'optimized', width].join('-'),
			(p) => continuity(p, kind, input, { plainGuest: plain }),
			width,
		);
	}
	await run('fast-before-input', (p) => settled(p, false));
	await run('delayed-before-input', (p) => settled(p, true));
	await run('native-link-before-commit', (p) => nativeDestination(p, 'link'));
	await run('native-get-before-commit', (p) => nativeDestination(p, 'get'));
	for (const reply of ['empty', 'failed', 'unavailable', 'nonresponding'])
		await run(
			reply === 'failed' ? 'network-failed' : reply === 'unavailable' ? 'endpoint-unavailable' : reply,
			async (p) => {
				await inGuest(p, { reply });
				await openSearch(p, 'keyboard');
				record.accepted = await state(p);
				if (reply === 'nonresponding') await pause(1350);
				else {
					policy.release.release();
					await pause(200);
				}
				record.final = await state(p);
				check(
					opened(record.final, 'search') && record.final.doc === record.before.doc,
					'typing and focus survive the endpoint condition',
				);
				if (reply === 'empty')
					check(nativeReplies()[0].body === '[]', 'installed endpoint returned native empty array');
				if (reply === 'nonresponding') {
					check(!nativeReplies()[0].released, 'first interaction completed while reply still pending');
					await p.goto('about:blank');
				}
			},
		);
	await run('duplicate-pending-guest', async (p) => {
		await continuity(p, 'search', 'mouse', { duplicate: true });
		check(
			nativeReplies().length === 2,
			'two real overlapping requests from deliberately repeating the native bootstrap',
		);
	});
	await run('slow-script-native-link', async (p) => {
		policy.holdScript = true;
		await p.goto(origin + '/books/', { waitUntil: 'commit' });
		await waitGate(policy.scriptSeen, 'script');
		const link = p.locator(nav + ' a', { hasText: 'Contact' });
		check((await link.isVisible()) && !(await p.locator(menu).isVisible()), 'slow script offers native link');
		await link.click({ noWaitAfter: true });
		policy.allowScript.release();
		await p.waitForURL('**/contact-jim-lunsford/');
		check(p.url().endsWith('/contact-jim-lunsford/'), 'trusted native link worked ahead of the footer bundle');
	});
	for (const destination of ['contact', 'search'])
		await run('slow-idle-replacement-' + destination, async (p) => {
			const cdp = await p.context().newCDPSession(p);
			policy.holdReply = true;
			policy.holdDoc = 'replacement';
			await p.goto(origin + '/books/', { waitUntil: 'commit' });
			await waitGate(policy.arrived, 'POST');
			await ready(p);
			policy.release.release();
			await waitGate(policy.documentSeen, 'replacement');
			for (let i = 0; i < 60 && !record.events.some((e) => e.kind === 'proof:guest-reload'); i++)
				await pause(20);
			record.during = record.events.find((e) => e.kind === 'proof:guest-reload');
			check(
				record.during?.leaving && !record.during.menuVisible && record.during.width > 0,
				'only native links exposed when idle refresh starts',
			);
			// Chromium can hold a navigation response across the entire input. Use
			// protocol mouse input so the test runner never waits for a pending load before pressing.
			const x = destination === 'contact' ? record.during.x : record.during.searchX,
				y = destination === 'contact' ? record.during.y : record.during.searchY;
			await cdp.send('Input.dispatchMouseEvent', {
				type: 'mousePressed',
				button: 'left',
				clickCount: 1,
				x,
				y,
			});
			await cdp.send('Input.dispatchMouseEvent', {
				type: 'mouseReleased',
				button: 'left',
				clickCount: 1,
				x,
				y,
			});
			const path = destination === 'contact' ? '/contact-jim-lunsford/' : '/?s=';
			await p.waitForURL(origin + path, { waitUntil: 'commit', timeout: 4000 });
			policy.allowDocument.release();
			await p.waitForLoadState('domcontentloaded');
			record.final = await state(p);
			check(record.final.url === path, 'native link superseded the pending automatic refresh');
			if (destination === 'search')
				check(
					await p.locator('.proof-search-results-form input').isVisible(),
					'standalone native Search form available',
				);
		});
	for (const longCache of [false, true])
		await run('upgrade-and-reversal-' + (longCache ? 'long-freshness' : 'heuristic'), (p, c) =>
			releaseMatrix(p, c, longCache),
		);
	await run('cold-reversed-release', async (p) => {
		active = 'old';
		policy.ordinary = true;
		record.final = await loadRelease(p, '/books/', 'old');
		await toggleLegacy(p);
	});
	for (const from of ['old', 'new'])
		for (const guest of [false, true]) {
			const suffix = from + '-' + (guest ? 'guest' : 'plain');
			await run('capture-' + suffix, (p) => captureStale(p, from, guest));
			await run('cold-stale-' + suffix, (p) => checkStale(p, from, guest));
		}
	await run('stale-candidate-guest-generated-files-discarded', (p) => checkStale(p, 'new', true, true));
	await run('browser-cached-html-after-upgrade', async (p, ctx) => {
		active = 'old';
		policy.ordinary = true;
		policy.cacheHTML = true;
		await loadRelease(p, '/books/', 'old');
		const count = record.html.length;
		active = 'new';
		const second = await instrument(ctx);
		await loadRelease(second.page, '/books/', 'old');
		await toggleLegacy(second.page);
		record.final = await state(second.page);
		await Promise.allSettled(second.bodies);
		check(
			record.html.length === count && !record.final.ready,
			'old HTML was reused by the browser itself, with its matching old controller',
		);
	});
	await run('browser-cached-html-after-reversal', async (p, ctx) => {
		active = 'new';
		policy.ordinary = true;
		policy.cacheHTML = true;
		await loadRelease(p, '/books/', 'new');
		const count = record.html.length;
		active = 'old';
		const second = await instrument(ctx);
		await loadRelease(second.page, '/books/', 'new');
		await openSearch(second.page, 'mouse');
		record.final = await state(second.page);
		await Promise.allSettled(second.bodies);
		check(
			record.html.length === count && opened(record.final, 'search'),
			'browser-cached corrected HTML retains its corrected controller on rollback',
		);
	});
	for (const [label, path, target] of [
		['default-home-native-link', '/', '/books/'],
		['results-page-native-get', '/?s=quartzzyzx', '/?s=self-trust'],
	]) {
		await run(label, async (p) => {
			active = 'new';
			policy.holdReply = true;
			await p.goto(origin + path, { waitUntil: 'commit' });
			await waitGate(policy.arrived, 'guest POST');
			policy.holdDoc = target;
			if (path === '/') {
				check(
					(await p.locator(menu).count()) === 0 &&
						(await p.locator('#proof-masthead-css').count()) === 0,
					'default home still omits masthead and its style',
				);
				await p.locator('main a[href$="/books/"]').first().click({ noWaitAfter: true });
			} else {
				const input = p.locator('.proof-search-results-form input.search-field');
				await input.click();
				await p.keyboard.press('ControlOrMeta+a');
				await p.keyboard.type('self-trust');
				await p.keyboard.press('Enter');
			}
			await waitGate(policy.documentSeen, 'outgoing GET');
			policy.release.release();
			await pause(150);
			policy.allowDocument.release();
			await p.waitForURL(origin + target);
			record.final = await state(p);
			actualYes();
			check(
				record.requests.filter((r) => r.path === target && r.method === 'GET').length === 1,
				'one native GET survived the pending decision',
			);
		});
	}
	await run('blocked-controller-native-link', async (p) => {
		active = 'new';
		policy.holdReply = true;
		policy.blockScript = true;
		await p.goto(origin + '/books/', { waitUntil: 'commit' });
		await waitGate(policy.arrived, 'native POST');
		check(!(await p.locator(menu).isVisible()), 'blocked controller keeps the enhanced toggle hidden');
		policy.holdDoc = '/contact-jim-lunsford/';
		await p.locator(nav + ' a', { hasText: 'Contact' }).click({ noWaitAfter: true });
		await waitGate(policy.documentSeen, 'native GET');
		policy.release.release();
		await pause(100);
		policy.allowDocument.release();
		await p.waitForURL('**/contact-jim-lunsford/');
		actualYes();
		check(
			record.requests.filter((r) => r.path === '/contact-jim-lunsford/').length === 1,
			'native fallback link navigates once',
		);
	});
	await run('after-veto-next-visit', async (p, ctx) => {
		active = 'new';
		await continuity(p, 'menu', 'mouse');
		const cookies = await ctx.cookies();
		check(
			cookies.some((c) => c.name === '_lscache_vary' && c.httpOnly),
			'negotiated HTTP-only cookie applied despite retaining the document',
		);
		await p.goto(origin + '/contact-jim-lunsford/');
		await ready(p);
		await pause(120);
		check(
			nativeReplies().length === 1 && !record.html.at(-1).decisionGuard,
			'next real request served the negotiated ordinary document',
		);
	});
	await run('optional-home-guest', async (p) => {
		active = 'new';
		policy.holdReply = true;
		await p.goto(origin + '/?visible_fixture=1', { waitUntil: 'commit' });
		await waitGate(policy.arrived, 'optional home POST');
		await ready(p);
		record.before = await state(p);
		await openSearch(p, 'touch');
		await releaseDecision(p);
		record.final = await state(p);
		actualYes();
		check(
			record.before.doc === record.final.doc && opened(record.final, 'search'),
			'optional homepage retains focused Search',
		);
	});
	await run('without-navigation-api', async (p) => {
		active = 'new';
		await p.addInitScript(() => Object.defineProperty(window, 'navigation', { value: undefined }));
		await continuity(p, 'menu', 'keyboard');
	});
	await run('warm-upgrade-with-pending-guest', async (p, ctx) => {
		active = 'old';
		policy.ordinary = true;
		record.cold = await loadRelease(p, '/contact-jim-lunsford/', 'old');
		await ctx.clearCookies();
		await p.evaluate(() => {
			sessionStorage.removeItem('litespeed_reloaded');
			sessionStorage.removeItem('litespeed_docref');
		});
		active = 'new';
		policy.ordinary = false;
		await continuity(p, 'search', 'keyboard');
		check(
			record.bytes.some((r) => r.url.includes(oldJS)) && record.bytes.some((r) => r.url.includes(newJS)),
			'old cache and corrected negotiation tested in one browser',
		);
	});
} finally {
	if (browser) await browser.close();
	server.closeAllConnections();
	await new Promise((r) => server.close(r));
	for (const site of Object.values(sites)) await site.handler[Symbol.asyncDispose]();
	let closed = false;
	try {
		await fetch(origin, { signal: AbortSignal.timeout(700) });
	} catch {
		closed = true;
	}
	writeFileSync(
		join(output, 'release-transitions.json'),
		JSON.stringify(
			{
				browser: browser?.version(),
				origin,
				closed,
				versions: Object.fromEntries(Object.entries(sites).map(([k, v]) => [k, v.versions])),
				reports,
			},
			null,
			2,
		),
	);
}
if (reports.some((r) => r.status !== 'PASS') || !reports.length) process.exitCode = 1;
