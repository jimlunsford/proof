// Run with PROOF_CONTACT_FIXTURE pointing to a loopback copy of Contact with both plugins loaded.
// node tests/seo-integration/contact-contrast.mjs
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';
import { chromium } from 'playwright';

const url = new URL(process.env.PROOF_CONTACT_FIXTURE);
assert.ok(['127.0.0.1', 'localhost', '[::1]'].includes(url.hostname));
const browser = await chromium.launch({ executablePath: process.env.PROOF_BROWSER_EXECUTABLE });
const stylesheet = new URL('../../assets/css/theme-layout.css', import.meta.url);
const fields = '.ff-el-form-control, .noptin-form-field__email';
const submit = '.ff-btn-submit';

function luminance(rgb) {
	return rgb.map(value => value / 255)
		.map(value => value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4)
		.reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index], 0);
}

async function checkContrast(control, property, minimum) {
	const pair = await control.evaluate(async (element, property) => {
		await Promise.all(element.getAnimations().map(animation => animation.finished));
		const rgb = color => color.match(/[\d.]+/g).map(Number);
		const blend = (top, bottom) => bottom.map((value, i) => value + (top[i] - value) * (top[3] ?? 1));
		const layers = [];
		for (let node = element.parentElement; node; node = node.parentElement) {
			layers.unshift(rgb(getComputedStyle(node).backgroundColor));
		}
		const outside = layers.reduce((under, over) => blend(over, under), [255, 255, 255]);
		const css = getComputedStyle(element);
		const inside = blend(rgb(css.backgroundColor), outside);
		const background = property === 'color' ? inside : outside;
		const foreground = blend(rgb(css[property]), background);
		// Plugin hover and disabled styles can fade the whole control into its surround.
		const visible = color => blend([...color, Number(css.opacity)], outside);
		return [visible(foreground), visible(background)];
	}, property);
	const [low, high] = pair.map(luminance).sort((a, b) => a - b);
	const ratio = (high + 0.05) / (low + 0.05);
	assert.ok(ratio >= minimum, `${property}: ${ratio.toFixed(2)}:1, expected at least ${minimum}:1`);
}

try {
	for (const width of [1366, 390, 320]) {
		const page = await browser.newPage({ viewport: { width, height: 850 }, serviceWorkers: 'block' });
		let newsletterReply = { success: false, data: 'Email required', errors: { '.noptin-form-field__email': 'Email required' } };
		let themeLoaded = false;
		await page.route('**/*', async route => {
			const asset = new URL(route.request().url());
			if (asset.origin !== url.origin) return route.abort();
			if (route.request().method() === 'POST' && asset.pathname.endsWith('/wp-json/noptin/v1/form')) {
				return route.fulfill({ json: newsletterReply });
			}
			if (route.request().method() !== 'GET') return route.abort();
			if (asset.pathname.endsWith('/themes/proof/assets/css/theme-layout.css')) {
				themeLoaded = true;
				return route.fulfill({ path: fileURLToPath(stylesheet), contentType: 'text/css' });
			}
			return route.continue();
		});
		await page.goto(url.href);
		assert.ok(themeLoaded, 'Fixture must enqueue the Proof theme stylesheet.');
		const controls = page.locator(fields);
		assert.equal(await controls.count(), 5, 'Four contact fields and one newsletter email field');
		for (const field of await controls.all()) {
			await checkContrast(field, 'borderTopColor', 3);
			const box = await field.boundingBox();
			assert.ok(box && box.x >= 0 && box.x + box.width <= width);
		}
		const button = page.locator(submit);
		await checkContrast(button, 'color', 4.5);
		await button.hover();
		await checkContrast(button, 'color', 4.5);
		await page.mouse.move(0, 0);
		const focused = page.locator(':focus');
		const reached = new Set();
		for (let i = 0; i < 100 && reached.size < 6; i++) {
			await page.keyboard.press('Tab');
			if (!await focused.count() || !await focused.evaluate(el => el.matches('.ff-el-form-control,.ff-btn-submit,.noptin-form-field__email'))) continue;
			reached.add(await focused.evaluate(el => el.id || el.className));
			assert.ok(await focused.evaluate(el => el.matches(':focus-visible') && getComputedStyle(el).outlineStyle === 'solid'));
			await checkContrast(focused, 'outlineColor', 3);
			if (await focused.evaluate(el => el.matches('.ff-btn-submit'))) await checkContrast(focused, 'color', 4.5);
		}
		assert.equal(reached.size, 6, 'Tab reaches every corrected control.');
		await button.click();
		await page.waitForSelector('.ff-el-is-error .ff-el-form-control');
		const invalid = page.locator('.ff-el-is-error .ff-el-form-control');
		assert.equal(await invalid.count(), 2, 'Empty email and message trigger Fluent Forms validation.');
		for (const field of await invalid.all()) await checkContrast(field, 'borderTopColor', 3);
		const newsletter = page.locator('.noptin-optin-form');
		const email = newsletter.locator('.noptin-form-field__email');
		const subscribe = newsletter.locator('.noptin-form-submit');
		await subscribe.click();
		await newsletter.locator('.noptin-field--is-invalid[aria-invalid="true"]').waitFor();
		await checkContrast(email, 'borderTopColor', 3);
		await email.fill('fixture@example.invalid');
		assert.equal(await email.getAttribute('aria-invalid'), 'false');
		newsletterReply = { success: true, data: { msg: 'Local fixture success' } };
		await subscribe.click();
		await newsletter.locator('.noptin-response').getByText('Local fixture success').waitFor();
		assert.equal(await email.inputValue(), '');
		await page.close();
		console.log(`PASS: Contact contrast, keyboard, required fields and control bounds at ${width}px`);
	}
} finally {
	await browser.close();
}
