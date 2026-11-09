/** Real Chromium acceptance against an explicitly disposable local WP fixture. */
import assert from 'node:assert/strict';
import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';

const base = new URL(process.env.PROOF_TEST_BASE_URL || 'http://127.0.0.1:9482/');
assert.equal(process.env.PROOF_DISPOSABLE_BROWSER_TEST, '1', 'Opt in to disposable browser checks.');
assert.ok(['127.0.0.1', 'localhost', '[::1]'].includes(base.hostname), 'Only a local fixture is allowed.');
assert.equal(base.protocol, 'http:', 'Use the isolated local HTTP fixture.');
const { chromium } = await import(process.env.PROOF_PLAYWRIGHT_MODULE || 'playwright');
const browser = await chromium.launch({
	...(process.env.PROOF_BROWSER_EXECUTABLE ? { executablePath: process.env.PROOF_BROWSER_EXECUTABLE } : {}),
	args: ['--no-sandbox', '--disable-dev-shm-usage'],
});
const output = process.env.PROOF_BROWSER_OUTPUT;
if (output) await mkdir(output, { recursive: true });
const results = { browser: browser.version(), checks: [], pages: [] };
const check = (condition, message) => {
	assert.ok(condition, message);
	results.checks.push(message);
};

try {
	for (const width of [1365, 768, 430, 390, 360]) {
		const context = await browser.newContext({ viewport: { width, height: 900 } });
		const page = await context.newPage();
		const errors = [];
		page.on('pageerror', error => errors.push(error.message));
		const visit = async route => {
			const response = await page.goto(new URL(route, base).href, { waitUntil: 'networkidle' });
			assert.equal(response.status(), 200, `${route} must return 200`);
			assert.equal(response.headers()['x-proof-disposable-test-site'], '1', 'Disposable fixture marker required.');
		};
		await visit('/projects/');
		const menu = page.locator('.proof-menu-toggle');
		const nav = page.locator('.proof-primary-nav');
		const searchToggle = page.locator('.proof-nav-search-link');
		const panel = page.locator('.proof-search-panel');
		const input = panel.locator('.search-field');
		const focused = locator => locator.evaluate(element => element === document.activeElement);
		const openMenu = async () => {
			await menu.focus();
			await page.keyboard.press('Enter');
			assert.equal(await menu.getAttribute('aria-expanded'), 'true');
		};
		const keyboardSearch = async () => {
			await openMenu();
			for (let count = 0; count < 30 && !await focused(searchToggle); count++) {
				await page.keyboard.press('Tab');
			}
			assert.ok(await focused(searchToggle), 'Search is keyboard reachable.');
			await page.keyboard.press('Enter');
		};

		await openMenu();
		await page.keyboard.press('Tab');
		check(await focused(nav.locator('a').first()), `${width}: Tab enters the first menu link`);
		await page.keyboard.press('Escape');
		check(await focused(menu) && await menu.getAttribute('aria-expanded') === 'false' && !await nav.isVisible(), `${width}: menu Escape restores visible Menu focus and closes disclosure`);
		await page.keyboard.press('Tab');
		check(!await focused(menu) && await page.evaluate(() => !document.querySelector('.proof-primary-nav').contains(document.activeElement)), `${width}: next Tab advances beyond the closed menu`);

		await openMenu();
		let leftNav = false;
		for (let count = 0; count < 30; count++) {
			await page.keyboard.press('Tab');
			if (await page.evaluate(() => !document.querySelector('.proof-primary-nav').contains(document.activeElement))) {
				leftNav = true;
				break;
			}
		}
		check(leftNav && await menu.getAttribute('aria-expanded') === 'true', `${width}: Tab leaves open menu without a focus trap`);
		await page.keyboard.press('Escape');

		await keyboardSearch();
		await page.waitForFunction(() => document.activeElement === document.querySelector('.proof-search-panel .search-field'));
		check(await focused(input) && await searchToggle.getAttribute('aria-expanded') === 'true' && await menu.getAttribute('aria-expanded') === 'false', `${width}: search autofocus and both expanded states are correct`);
		await page.keyboard.press('Escape');
		check(await focused(menu) && !await panel.isVisible() && await searchToggle.getAttribute('aria-expanded') === 'false', `${width}: search Escape returns to visible Menu control`);

		await keyboardSearch();
		await page.keyboard.press('Escape');
		await page.waitForTimeout(80); // Exceeds the 30 ms autofocus timer being tested.
		check(await focused(menu) && !await panel.isVisible(), `${width}: rapid Escape cancels pending search autofocus`);

		await keyboardSearch();
		await page.waitForFunction(() => document.activeElement === document.querySelector('.proof-search-panel .search-field'));
		await page.keyboard.press('Tab');
		check(await panel.locator('.search-submit').evaluate(element => element === document.activeElement), `${width}: search Tab reaches submit`);
		await page.keyboard.press('Tab');
		check(await page.evaluate(() => !document.querySelector('.proof-search-panel').contains(document.activeElement)), `${width}: search Tab continues into page without trapping`);
		await menu.click();
		check(await nav.isVisible() && !await panel.isVisible() && await searchToggle.getAttribute('aria-expanded') === 'false', `${width}: opening menu closes search`);
		await menu.click();
		check(!await nav.isVisible() && await menu.getAttribute('aria-expanded') === 'false', `${width}: menu toggles closed`);

		await openMenu();
		await page.keyboard.press('Tab');
		await page.locator('#proof-project-writing-heading').click();
		check(!await nav.isVisible() && !await focused(menu), `${width}: menu click-away closes without forcing Menu focus`);
		await keyboardSearch();
		await page.waitForFunction(() => document.activeElement === document.querySelector('.proof-search-panel .search-field'));
		await page.locator('#proof-project-writing-heading').click();
		check(!await panel.isVisible() && !await focused(menu), `${width}: search click-away closes without forcing Menu focus`);
		const mainLink = page.locator('main a').first();
		await mainLink.focus();
		await page.keyboard.press('Escape');
		check(await focused(mainLink), `${width}: unrelated Escape preserves focus`);

		for (const [name, route] of [['home', '/'], ['projects', '/projects/'], ['article', process.env.PROOF_TEST_ARTICLE_PATH || '/browser-article/']]) {
			await visit(route);
			const size = await page.evaluate(() => ({
				viewport: window.innerWidth, document: document.documentElement.scrollWidth, body: document.body.scrollWidth,
				url: location.href, heading: document.querySelector('main h1')?.textContent.trim(),
				overflow: [...document.querySelectorAll('body *')].filter(element => {
					const box = element.getBoundingClientRect();
					return box.width > 0 && box.right > innerWidth + 1;
				}).slice(0, 12).map(element => ({ tag: element.tagName, class: element.className, right: element.getBoundingClientRect().right })),
			}));
			results.pages.push({ width, route, ...size });
			if (output) await page.screenshot({ path: path.join(output, `${name}-${width}.png`), fullPage: true });
			check(new URL(page.url()).pathname === route, `${width}: ${name} stays on its intended route`);
			if (name === 'article') {
				check((await page.locator('main h1').textContent()).trim() === 'Browser Acceptance Article' && await page.locator('main pre.wp-block-code').count() === 1, `${width}: the actual article template and Gutenberg code block render`);
			}
			check(size.document <= size.viewport && size.body <= size.viewport, `${width}: ${name} has no horizontal overflow`);
			if (name === 'home') {
				const books = page.locator('.proof-identity-book-card');
				check(await books.count() === 4, `${width}: all four Published Books cards render`);
				const titles = await books.locator('h3').allTextContents();
				check(JSON.stringify(titles) === JSON.stringify(['The PERIOD Code', 'How to Rebuild Yourself', 'The Discipline Loop', 'The Disciplined Operator']), `${width}: exact four-book order`);
				await books.last().scrollIntoViewIfNeeded();
				await page.waitForFunction(() => [...document.querySelectorAll('.proof-identity-book-card img')].length === 4 && [...document.querySelectorAll('.proof-identity-book-card img')].every(img => img.complete && img.naturalWidth > 0));
				check(await books.locator('img').evaluateAll(images => images.every(img => img.complete && img.naturalWidth > 0)), `${width}: all four covers load`);
				const geometry = await books.evaluateAll(cards => cards.map(card => {
					const b=card.getBoundingClientRect(), title=card.querySelector('h3'), t=title.getBoundingClientRect(), s=getComputedStyle(card);
					return { x:b.x, y:b.y, width:b.width, padding:s.padding, gap:s.gap, titleFits:title.scrollWidth <= title.clientWidth && t.right <= b.right };
				}));
				check(geometry.every(card => card.titleFits && card.padding === geometry[0].padding && card.gap === geometry[0].gap && Math.abs(card.width - geometry[0].width) < 1), `${width}: readable titles and consistent card spacing`);
				check(width > 760 ? geometry[0].y === geometry[1].y && geometry[2].y === geometry[3].y && geometry[2].y > geometry[0].y : geometry.every((card, i) => i === 0 || card.y > geometry[i-1].y), `${width}: preserved two-column desktop and single-column mobile layout`);
				const catalog=page.getByRole('link', {name:'View All Books',exact:true});
				check(await catalog.getAttribute('href') === new URL('/books/',base).href, `${width}: catalog destination preserved`);
				await catalog.focus();
				for (let i=0;i<4;i++) {
					await page.keyboard.press('Tab');
					check(await books.nth(i).locator('a').evaluate(el => el === document.activeElement), `${width}: Tab reaches book ${i+1} in order`);
				}
				if (output) await page.locator('.proof-identity-books').screenshot({path:path.join(output,`books-${width}.png`)});
				const accessibility = await context.newCDPSession(page);
				const { nodes } = await accessibility.send('Accessibility.getFullAXTree');
				await accessibility.detach();
				for (let index = 0; index < 4; index++) {
					const book = books.nth(index);
					const title = (await book.locator('h3').textContent()).trim();
					const link = book.getByRole('link', { name: `Explore Book: ${title}`, exact: true });
					check(await link.count() === 1 && (await link.textContent()).trim() === 'Explore Book', `${width}: ${title} accessible name contains its visible label`);
					check(nodes.some(node => node.role?.value === 'link' && node.name?.value === `Explore Book: ${title}`), `${width}: Chromium accessibility tree reports the complete ${title} link name`);
					const expectedPath = ['/period-code/', '/how-to-rebuild-yourself/', '/discipline-loop/', '/disciplined-operator/'][index];
					check(await link.getAttribute('href') === new URL(expectedPath, base).href, `${width}: ${title} retains its fixture book destination`);
					await link.focus();
					check(await focused(link), `${width}: ${title} link is focusable`);
					await Promise.all([
						page.waitForURL(new URL(expectedPath, base).href),
						page.keyboard.press('Enter'),
					]);
					check(page.url() === new URL(expectedPath, base).href, `${width}: ${title} activates with Enter`);
					await visit('/');
				}
				const order = await page.locator('.proof-identity-section > .proof-identity-section-heading > h2').allTextContents();
				check(order.indexOf('Start Here') >= 0 && order.indexOf('Published Books') === order.indexOf('Start Here') + 1 && order.indexOf('Latest Work') === order.indexOf('Published Books') + 1, `${width}: Start Here, Published Books, Latest Work order preserved`);
			}
		}
		check(errors.length === 0, `${width}: no browser JavaScript exceptions`);
		await context.close();
	}
	results.result = 'PASS';
	console.log(`PASS: ${results.checks.length} real browser checks (${results.browser}).`);
} finally {
	if (output) await writeFile(path.join(output, 'browser-results.json'), JSON.stringify(results, null, 2) + '\n');
	await browser.close();
}
