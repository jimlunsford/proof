# Testing Proof

Proof has quick offline checks and deeper disposable WordPress integration and browser tests. The suites answer different questions; a passing offline test does not establish real WordPress routing, plugin integration, or browser behavior.

**Never run fixture-writing tests against JimLunsford.com or any existing WordPress installation containing real content.**

## Test inventory

| Check | Command | What it verifies |
| --- | --- | --- |
| Published Books, offline | `php tests/homepage-books.php` | Settings defaults, selection, escaping, accessible link names, output contracts |
| Law Enforcement, offline | `php tests/law-enforcement-category.php` | Stubbed category query and template contracts |
| Projects pagination/SEO, offline | `php tests/projects-pagination-seo.php` | Route mapping and metadata transformations using fixture arrays |
| Latest Work, WordPress | `PROOF_TEST_WP_ROOT=/path/to/disposable/wordpress php tests/homepage-latest-work.php` | Actual post selection, publication dates, featured/sticky behavior, statuses, query restoration |
| Excerpts, WordPress | `PROOF_TEST_WP_ROOT=/path/to/disposable/wordpress php tests/excerpts.php` | Password access, public summaries, nested Gutenberg blocks, shortcode handling |
| WordPress + SEO Framework HTTP | `npm --prefix tests/seo-integration test` | Real HTTP status, canonical, robots, Open Graph, schema, redirects, and membership |
| Chromium browser acceptance | `npm --prefix tests/seo-integration run browser` | Keyboard behavior, accessible link names, destinations, ordering, responsive layouts |

Run the commands from the repository root, except where the setup instructions below use `cd`.

## Quick offline checks

These suites use stubbed WordPress functions and need an appropriate local PHP CLI environment:

```sh
php tests/homepage-books.php
php tests/law-enforcement-category.php
php tests/projects-pagination-seo.php
```

The `Theme checks` GitHub Actions workflow runs PHP lint, JavaScript syntax checks, and the three offline suites on PHP 7.4 and 8.3. It does **not** run WordPress integration or browser acceptance.

## WordPress CLI integration tests

Use a genuinely disposable, empty local WordPress installation with the current Proof checkout active. These tests need PHP CLI and the PHP DOM extension.

The test-only `wp-config.php` must establish:

- `WP_ENVIRONMENT_TYPE` as `local`.
- `PROOF_DISPOSABLE_TEST_SITE` as boolean `true`.

The suites refuse to run when that flag is absent, the environment is not local, the Proof checkout is not active, or existing content is present. They create and remove synthetic posts, categories, and other test state in that **disposable database**.

```sh
PROOF_TEST_WP_ROOT=/absolute/path/to/disposable/wordpress php tests/homepage-latest-work.php
PROOF_TEST_WP_ROOT=/absolute/path/to/disposable/wordpress php tests/excerpts.php
```

The Latest Work suite covers publication order, featured and sticky posts, category eligibility, unpublished statuses, live query restoration, saved legacy settings, and rendering.

The excerpt suite covers missing, incorrect, and valid native password cookies; public excerpts; relevant listing surfaces; protected deck/source fields; nested block filtering; safe shortcode removal; and presentation escaping.

Use a fresh disposable installation for each suite if the test harness requires an empty database. Do not put the disposable-test flag on a real site.

## Real HTTP and browser integration with WordPress Playground

Requirements: Node.js 24.18 or newer, npm 11.16 or newer, Git, and tar. The test-only packages are declared and version-pinned in `tests/seo-integration/package.json` and its lockfile.

```sh
cd tests/seo-integration
npm ci
npm test
npx playwright install chromium
npm run browser
```

The runners create local WordPress Playground instances and dispose of them afterward. They do not use an existing site, database, or production credentials.

The HTTP runner exercises WordPress 7.1.3 with PHP 8.3, SQLite, The SEO Framework 5.2.0, and Redirection 5.10.1. WordPress and plugin packages are checked against pinned SHA-256 values. The runner uses the supported `cgi-fcgi` SAPI so Redirection handles actual HTTP redirects.

It covers Law Enforcement's sole-feature, additional-article, and unpublished-feature states; valid and invalid Projects pages; legacy category aliases; an unrelated archive; real canonical/indexability output; schema references; and exact article membership.

The test also uses the exact original failing 2.0.14 source commit as a historical negative control. Keep the relevant Git history available. An expected negative-control failure is not a failure of the current candidate.

The browser runner uses Chromium on disposable content at widths 1365, 768, 390, and 360 pixels. It exercises real Tab, Enter, Escape, and click interactions, book-link accessible names, search focus, section order, and horizontal overflow.

### Optional test inputs and outputs

- `PROOF_TEST_WORDPRESS_ZIP`: Supply the exact expected WordPress ZIP instead of downloading it.
- `PROOF_TEST_PLUGIN_CACHE`: Directory with the pinned plugin ZIPs.
- `PROOF_TEST_RESULTS`: Directory for saved HTTP test evidence.
- `PROOF_BROWSER_OUTPUT`: Directory for browser results and screenshots.

Only use local, disposable test data in these outputs.

## Scope and interpretation

These checks are useful regression evidence, not a complete production certification. The Playground integration is SQLite-based, so it does not establish native MariaDB/MySQL or the site's production cache/PHP-FPM behavior. The Chromium checks do not replace full screen-reader or WCAG acceptance.

The accepted 2.0.16 test evidence is preserved in [the version-specific verification record](evidence/2.0.16.md). Historical findings and logs belong with that evidence or the [changelog](../CHANGELOG.md), rather than in the public project introduction.
