/** Real WordPress + TSF HTTP regression. No existing site or database is accepted. */
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, resolve } from 'node:path';
import { createServer } from 'node:http';
import { fileURLToPath } from 'node:url';
import { createNodeFsMountHandler, loadNodeRuntime } from '@php-wasm/node';
import { bootWordPressAndRequestHandler } from '@wp-playground/wordpress';
import { installPlugin } from '@wp-playground/blueprints';

const here = dirname(fileURLToPath(import.meta.url));
const theme = resolve(here, '../..');
const temp = mkdtempSync(join(tmpdir(), 'proof-seo-'));
const evidence = process.env.PROOF_TEST_RESULTS ? resolve(process.env.PROOF_TEST_RESULTS) : join(temp, 'results');
mkdirSync(evidence, { recursive: true });
const historicalCommit = 'ba684c6cd37d30c2292a88916f48c3ca519f19fe';
const plugins = [
  ['autodescription.5.2.0.zip', '36292cd74e727453441670ec153b93c20392dbb2443789683d8bca863f89fb4f'],
  ['redirection.5.10.1.zip', 'f68a4be447ecba00c64e8a39d7517807ed49cc18e77b7a364c20cb31848235b7'],
];
let assertions = 0;
let status = 'FAIL';
const ledger = [];
function check(condition, message) {
  assert.ok(condition, message);
  assertions++;
}
function same(actual, expected, message) {
  assert.deepEqual(actual, expected, message);
  assertions++;
}

// Parse only the ordinary HTML tag/attribute forms emitted by WP and TSF.
// Metadata values are read from actual HTTP HTML, never supplied by fixture arrays.
function attrs(tag) {
  return Object.fromEntries([...tag.matchAll(/([\w:-]+)\s*=\s*(?:"([^"]*)"|'([^']*)')/g)]
    .map(([, key, double, single]) => [key.toLowerCase(), (double ?? single).replaceAll('&amp;', '&').replaceAll('&#038;', '&')]));
}
function parse(html) {
  const links = [...html.matchAll(/<link\b[^>]*>/gi)].map(([tag]) => attrs(tag));
  const metas = [...html.matchAll(/<meta\b[^>]*>/gi)].map(([tag]) => attrs(tag));
  const graphs = [...html.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script>/gi)]
    .filter(([, attributes]) => attrs(attributes).type === 'application/ld+json')
    .flatMap(([, , json]) => { const parsed = JSON.parse(json); return parsed['@graph'] ?? [parsed]; });
  return {
    canonical: links.filter(tag => tag.rel === 'canonical').map(tag => tag.href),
    og: metas.filter(tag => tag.property === 'og:url').map(tag => tag.content),
    robots: metas.filter(tag => tag.name === 'robots').flatMap(tag => tag.content.toLowerCase().split(/\s*,\s*/)).sort(),
    graph: graphs,
    ids: [...html.matchAll(/<article\b[^>]*\bid=["']post-(\d+)["']/g)].map(([, id]) => Number(id)),
  };
}
async function request(server, path, label) {
  const url = new URL(path, server.serverUrl).href;
  check(['127.0.0.1', 'localhost'].includes(new URL(url).hostname), 'HTTP tests stay on loopback');
  const response = await fetch(url, { redirect: 'manual' });
  const html = await response.text();
  const result = { path, url, status: response.status, location: response.headers.get('location'), ...parse(html) };
  writeFileSync(join(evidence, `${label}.html`), html);
  ledger.push({ label, ...result });
  return result;
}
function valid(result, expectedIds, type = 'CollectionPage') {
  same(result.status, 200, `${result.path}: HTTP 200`);
  same(result.location, null, `${result.path}: no redirect`);
  same(result.canonical, [result.url], `${result.path}: exactly one self canonical`);
  same(result.og, [result.url], `${result.path}: exactly one self Open Graph URL`);
  check(result.robots.length > 0 && !result.robots.includes('noindex'), `${result.path}: indexable robots`);
  same(result.ids, expectedIds, `${result.path}: exact ordered article membership`);
  same(new Set(result.ids).size, result.ids.length, `${result.path}: no duplicate articles`);
  const pages = result.graph.filter(entity => [].concat(entity['@type'] ?? []).includes(type));
  same(pages.length, 1, `${result.path}: one ${type} entity`);
  same(pages[0]['@id'], result.url, `${result.path}: schema identity`);
  same(pages[0].url, result.url, `${result.path}: schema URL`);
  const site = result.graph.find(entity => [].concat(entity['@type'] ?? []).includes('WebSite'));
  check(site?.['@id'] && site.url === new URL('/', result.url).href, `${result.path}: unchanged WebSite identity`);
  same(pages[0].isPartOf?.['@id'], site['@id'], `${result.path}: schema reference resolves to WebSite`);
  if (type === 'WebPage') {
    same(pages[0].potentialAction?.['@type'], 'ReadAction', `${result.path}: schema ReadAction`);
    same(pages[0].potentialAction?.target, result.url, `${result.path}: ReadAction target`);
  }
}
function missing(result, requireNoindex = false) {
  same(result.status, 404, `${result.path}: genuine HTTP 404`);
  same(result.location, null, `${result.path}: no guessed redirect`);
  same(result.canonical, [], `${result.path}: no canonical`);
  same(result.og, [], `${result.path}: no Open Graph URL`);
  same(result.ids, [], `${result.path}: no article membership`);
  check(result.robots.length > 0, `${result.path}: actual robots captured`);
  if (requireNoindex) check(result.robots.includes('noindex'), `${result.path}: noindex`);
  const pages = result.graph.filter(entity => [].concat(entity['@type'] ?? []).some(type => ['WebPage', 'CollectionPage'].includes(type)));
  check(pages.length > 0 && pages.every(entity => !entity['@id'] && !entity.url), `${result.path}: no nonexistent schema page identity`);
}
async function fixture(server, action) {
  const response = await server.playground.run({ code: `<?php define('PROOF_SEO_FIXTURE_RUNNER', true); require '/wordpress/wp-load.php'; require '/proof-tests/fixtures.php'; echo json_encode(${action}());` });
  check(response.exitCode === 0, `${action}: PHP completed successfully`);
  return JSON.parse(response.text);
}
async function start(themePath) {
  let handler;
  const http = createServer(async (request, response) => {
    try {
      const result = await handler.request({ url: request.url, method: request.method, headers: request.headers });
      response.writeHead(result.httpStatusCode, result.headers);
      response.end(Buffer.from(result.bytes));
    } catch (error) { response.writeHead(500); response.end(String(error)); }
  });
  await new Promise(resolve => http.listen(0, '127.0.0.1', resolve));
  const serverUrl = `http://127.0.0.1:${http.address().port}`;
  try {
    // The CLI hardcodes the CLI SAPI even for HTTP, which correctly makes
    // Redirection skip redirects. The supported boot API supplies a web SAPI.
    handler = await bootWordPressAndRequestHandler({
      siteUrl: serverUrl, sapiName: 'cgi-fcgi', phpVersion: '8.3', maxPhpInstances: 1,
      createPhpRuntime: () => loadNodeRuntime('8.3', { emscriptenOptions: { processId: process.pid } }), cookieStore: false,
      wordPressZip: new File([readFileSync(join(temp, 'wordpress-7.1.3.zip'))], 'wordpress.zip'),
      sqliteIntegrationPluginZip: new File([readFileSync(new URL('sqlite-database-integration.zip', import.meta.resolve('@wp-playground/cli')))], 'sqlite.zip'),
      constants: { WP_ENVIRONMENT_TYPE: 'local', PROOF_DISPOSABLE_TEST_SITE: true, DISABLE_WP_CRON: true },
      hooks: { beforeDatabaseSetup: async php => {
        php.mkdir('/wordpress/wp-content/themes/proof');
        await php.mount('/wordpress/wp-content/themes/proof', createNodeFsMountHandler(themePath));
        php.mkdir('/proof-tests');
        await php.mount('/proof-tests', createNodeFsMountHandler(here));
      } },
    });
    const php = await handler.getPrimaryPhp();
    const initialized = await php.run({ code: `<?php require '/wordpress/wp-load.php'; foreach (get_posts(['post_type'=>'any','post_status'=>array_keys(get_post_stati()),'numberposts'=>-1]) as $post) { if (!in_array($post->post_name, ['hello-world','sample-page','privacy-policy'], true)) { throw new RuntimeException('Unexpected pre-existing content'); } wp_delete_post($post->ID,true); } switch_theme('proof');` });
    assert.equal(initialized.exitCode, 0, 'Disposable site initialization');
    for (const [name] of plugins) await installPlugin(php, { pluginData: new File([readFileSync(join(temp, name))], name), options: { activate: true } });
    return { playground: php, serverUrl, async [Symbol.asyncDispose]() {
      await new Promise(resolve => http.close(resolve));
      await handler[Symbol.asyncDispose]();
    } };
  } catch (error) {
    await new Promise(resolve => http.close(resolve));
    if (handler) await handler[Symbol.asyncDispose]();
    throw error;
  }
}

try {
  const wpCache = process.env.PROOF_TEST_WORDPRESS_ZIP;
  const wpResponse = wpCache ? null : await fetch('https://wordpress.org/wordpress-7.1.3.zip');
  if (wpResponse) assert.equal(wpResponse.status, 200, 'Download pinned WordPress');
  const wpBytes = wpCache ? readFileSync(wpCache) : Buffer.from(await wpResponse.arrayBuffer());
  assert.equal(createHash('sha256').update(wpBytes).digest('hex'), 'eb58a90ed55147f90f7bd55d0d3334960a69b7e8a4cc142c8ddfea8965218227', 'Pinned WordPress checksum');
  writeFileSync(join(temp, 'wordpress-7.1.3.zip'), wpBytes);
  for (const [name, digest] of plugins) {
    const cached = process.env.PROOF_TEST_PLUGIN_CACHE && join(process.env.PROOF_TEST_PLUGIN_CACHE, name);
    let bytes;
    if (cached && existsSync(cached)) bytes = readFileSync(cached);
    else {
      const response = await fetch(`https://downloads.wordpress.org/plugin/${name}`);
      assert.equal(response.status, 200, `Download pinned plugin ${name}`);
      bytes = Buffer.from(await response.arrayBuffer());
    }
    assert.equal(createHash('sha256').update(bytes).digest('hex'), digest, `Pinned plugin checksum ${name}`);
    writeFileSync(join(temp, name), bytes);
  }
  const server = await start(theme);
  try {
    let data = await fixture(server, 'proof_seo_fixture_seed');
    const runtime = await server.playground.run({ code: `<?php require '/wordpress/wp-load.php'; echo json_encode(['wordpress'=>$wp_version,'php'=>PHP_VERSION,'sapi'=>PHP_SAPI,'tsf'=>THE_SEO_FRAMEWORK_VERSION,'redirection'=>REDIRECTION_VERSION,'database'=>'SQLite via WordPress Playground']);` });
    console.log(`RUNTIME: ${runtime.text}`);
    ledger.push({ runtime: JSON.parse(runtime.text) });
    const law = '/category/law-enforcement-corrections/';
    const sole = await request(server, law, 'law-sole');
    valid(sole, [data.feature]);
    const soleHtml = readFileSync(join(evidence, 'law-sole.html'), 'utf8');
    check(soleHtml.includes('Featured Stories') && !soleHtml.includes('Recent Stories'), 'Sole feature has no duplicate recent section');
    missing(await request(server, `${law}page/2/`, 'law-sole-page2'), true);
    valid(await request(server, '/category/notes/', 'notes-before'), data.notes);

    const allProjects = [];
    for (let page = 1; page <= 5; page++) {
      const route = page === 1 ? '/projects/' : `/projects/page/${page}/`;
      const result = await request(server, route, `projects-${page}`);
      valid(result, data.projects.slice((page - 1) * 6, page * 6), 'WebPage');
      allProjects.push(...result.ids);
    }
    same(allProjects, data.projects, 'All 25 Projects posts appear exactly once across five pages');
    for (const page of [6, 999]) missing(await request(server, `/projects/page/${page}/`, `projects-missing-${page}`));
    for (const [source, destination, offset] of [
      ['/category/projects/', '/projects/', 0],
      ['/category/projects/page/2/', '/projects/page/3/', 12],
      ['/category/projects/page/3/', '/projects/page/5/', 24],
    ]) {
      const label = `alias-${offset}`;
      const redirect = await request(server, source, label);
      same(redirect.status, 301, `${source}: permanent alias redirect`);
      same(new URL(redirect.location, server.serverUrl).href, new URL(destination, server.serverUrl).href, `${source}: exact preserved offset destination`);
      valid(await request(server, destination, `${label}-destination`), data.projects.slice(offset, offset + 6), 'WebPage');
    }
    missing(await request(server, '/category/projects/page/4/', 'alias-missing'), true);

    data = await fixture(server, 'proof_seo_fixture_add_law_stories');
    valid(await request(server, law, 'law-many'), [data.feature, ...data.law_recent.slice(0, 12)]);
    valid(await request(server, `${law}page/2/`, 'law-many-page2'), data.law_recent.slice(12));
    missing(await request(server, `${law}page/3/`, 'law-many-page3'), true);
    data = await fixture(server, 'proof_seo_fixture_unpublish_feature');
    valid(await request(server, law, 'law-unpublished'), data.law_recent.slice(0, 12));
    valid(await request(server, `${law}page/2/`, 'law-unpublished-page2'), data.law_recent.slice(12));
    const unpublishedHtml = readFileSync(join(evidence, 'law-unpublished.html'), 'utf8');
    check(!unpublishedHtml.includes('Featured Stories') && !unpublishedHtml.includes('/why-i-loved-the-work/'), 'Draft featured article exposes neither card nor Start Here link');
    valid(await request(server, '/category/notes/', 'notes-after'), data.notes);
  } finally { await server[Symbol.asyncDispose](); }

  // Run the SAME canonical assertion against the actual original failing tree.
  // This is a separate temporary checkout, never a change to the candidate.
  const historical = join(temp, 'historical-proof');
  mkdirSync(historical);
  const archive = execFileSync('git', ['archive', historicalCommit], { cwd: theme, maxBuffer: 20 * 1024 * 1024 });
  execFileSync('tar', ['-x', '-C', historical], { input: archive });
  const old = await start(historical);
  try {
    const data = await fixture(old, 'proof_seo_fixture_seed');
    const result = await request(old, '/category/law-enforcement-corrections/', 'historical-law-sole');
    let rejection;
    try { valid(result, [data.feature]); } catch (error) { rejection = error; }
    check(rejection?.message.includes('exactly one self canonical'), 'Original 2.0.14 is rejected by the real canonical assertion');
    same(result.canonical, [], 'Historical sole-feature regression actually omits canonical');
    check(result.robots.includes('noindex'), 'Historical sole-feature regression actually emits noindex');
    console.log(`EXPECTED NEGATIVE CONTROL: ${historicalCommit}: ${rejection.message.split('\n')[0]}`);
  } finally { await old[Symbol.asyncDispose](); }
  status = 'PASS';
  console.log(`PASS: ${assertions} real WordPress/TSF HTTP integration assertions, including historical negative control.`);
} finally {
  writeFileSync(join(evidence, 'http-results.json'), JSON.stringify({ status, assertions, results: ledger }, null, 2));
  rmSync(temp, { recursive: true, force: true });
}
