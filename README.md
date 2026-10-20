# Proof

Proof is the custom classic WordPress theme powering [JimLunsford.com](https://jimlunsford.com/). It is a text-first publishing system built around long-form reading, an identity-focused homepage, distinct content lanes, and a growing body of writing and project work. The site is public and actively used; this repository holds the theme's development source.

**Current repository version:** 2.0.16 candidate.

## What Proof was built to do

JimLunsford.com has to make different kinds of work easy to find without forcing them into one feed. Proof gives the homepage a clear introduction and paths into [The Work](https://jimlunsford.com/the-work/), [Projects](https://jimlunsford.com/projects/), frameworks, and the writing archive. Category pages combine introductions and featured posts with the ordinary archive feed. Topic hubs and curated links guide readers toward useful starting points, while individual articles keep the reading experience central.

The same theme presents project notes, a site-specific [résumé](https://jimlunsford.com/resume/), search, responsive navigation, and mobile layouts. WordPress Customizer settings let Jim maintain selected homepage, project, category, and footer content without changing templates for every editorial update. Portrait handling uses responsive image sizes and can generate cached WebP derivatives when supported.

## Built for a real publishing site

The theme's structure follows the site's editorial structure. Its category rules, curated post paths, page templates, and defaults are deliberate choices for work that is already published, read, and revised. Featured content is separated from the ordinary category feed through the WordPress main query, so archive routing and pagination share the same result set. Curated post cards resolve published posts only.

This repository documents the source and its changes. A merge to `main` establishes an accepted repository version; a production deployment is a separate decision.

## JimLunsford.com

[JimLunsford.com](https://jimlunsford.com/) is Jim Lunsford's durable home for writing, frameworks, projects, and technical work. Recovery and discipline are part of that work, alongside rebuilding, identity, standards, personal experience, publishing, WordPress, and software. [The Work](https://jimlunsford.com/the-work/) organizes essays and frameworks around problems readers face. [Projects](https://jimlunsford.com/projects/) collects software, sites, project writing, and Builder Receipts that explain decisions and implementation. The [About page](https://jimlunsford.com/about-jim-lunsford-discipline/) gives the wider context behind both.

The site includes long-form articles, Recovery Standards, Discipline Dispatch, frameworks, and writing about what it takes to build and maintain useful things. Proof gives that varied body of work one coherent way to be published and explored.

## Technical architecture

- Classic PHP templates and `template-parts/` render the homepage, posts, pages, search, archives, and site-specific hubs.
- `functions.php` registers theme support, assets, menus, Customizer controls, and the main category query rules. `inc/helpers.php` handles content selection, routes, and shared presentation helpers.
- `assets/css/theme.css` contains the main front-end design; `style.css` holds the WordPress theme header and base screen-reader styles. `assets/css/editor-style.css` supports the editor. `assets/js/navigation.js` handles menu and search controls without a JavaScript framework.
- The homepage portrait can use generated WebP sizes cached in WordPress uploads under `proof-cache/`. Those generated images are site data, not repository source.

The theme header requires WordPress 6.4 or later and PHP 7.4 or later. Its `Tested up to` field is 7.1.3, based on the bounded candidate acceptance recorded in [the verification report](tests/evidence/2.0.16.md). The repository contains the theme source, not the site's database, uploaded media, or WordPress configuration.

## Homepage book discovery

Books appears in Start Here immediately before About, preserving the existing `proof_identity_destination_book_*` setting IDs. The homepage-only Published Books section sits between Start Here and Latest Work. Its two compact features link to the individual book pages, not directly to checkout. Full book copy and purchase links remain on the book pages and `/books/`.

Maintain the two curated features under **Customizer > Homepage: Published Books**. Titles, descriptions, URLs, and cover media are editable. A blank title, URL, or missing/non-image cover hides that slot; clearing both hides the section. Current defaults use JimLunsford.com media IDs 2586 and 2615. No saved theme mods are rewritten by a theme update.

The section stylesheet is scoped to the book classes and added only on the homepage.

## Automatic Latest Work

Latest Work displays the five newest published WordPress posts after Published Books and before the optional image row and Follow and Connect. Every category is eligible, including Projects and posts featured elsewhere. Entries use the existing category and clean-excerpt helpers, the published title, and the canonical post permalink, with one accessible link per item. The existing desktop two-column and mobile single-column list styles are retained.

The dedicated `proof_get_identity_latest_work_query()` orders by original publication date, newest first. Editing an older post or making it sticky does not promote it. Drafts, pending posts, scheduled posts not yet published, private posts, trash, pages, and custom post types are excluded. Fewer than five eligible posts produces a shorter list; no eligible posts hides the section. Publishing a post updates the list through the normal WordPress query, without manual slots, synchronization, or an additional plugin.

The heading is fixed as **Latest Work**. The retired Selected Work Customizer section, heading, and three manual slots are no longer registered or read. Existing `proof_identity_latest_work_*` theme mods remain stored unchanged; no migration or database cleanup runs. Other Customizer settings, the separate recent-writing feed, and category archive exclusions and pagination retain their existing behavior.

## Law Enforcement & Corrections

The WordPress category at /category/law-enforcement-corrections/ is a first-person story archive. Its editable WordPress category description provides the introduction, and the published post Why I Loved the Work is both its Start Here destination and initial Featured Stories entry. The feature resolves only while the post is published. When it is the sole published article, WordPress keeps it in the main archive query so the category retains canonical/indexable SEO metadata; the Recent Stories display is suppressed to prevent duplication. Once additional stories are published, the featured article is excluded from the normal 12-post-per-page feed as usual. New published stories enter the normal 12-post-per-page category query automatically.

The homepage Start Here section provides an editable Law Enforcement & Corrections destination directly after Projects and before Core Frameworks. It uses existing destination cards, responsive styling, and Customizer settings; the theme does not modify a WordPress navigation menu. Existing category descriptions, featured selections, and headings are preserved. Categories without a configured description can now display their editable WordPress category descriptions.

## Projects taxonomy boundary

Projects currently assumes a flat category model. Its listing includes descendant posts, but its pagination/404 count uses the Projects category's direct published count. Do not introduce child categories without separately correcting and testing that shared boundary. The 2.0.16 audit correction does not change Projects queries, pagination, redirects, or SEO behavior.

## Development

`main` is the accepted source. Focused changes are developed on branches and reviewed through pull requests before merging. Repository acceptance does not automatically update JimLunsford.com; production deployment is handled separately.

The standalone suites use stubbed WordPress functions for unit-level contracts. They are offline checks, not evidence of real WordPress routing or SEO plugin behavior.

| Check | Command | Environment and scope |
| --- | --- | --- |
| Published Books | `php tests/homepage-books.php` | Offline settings, selection, escaping, book link names, and presentation contracts |
| Law Enforcement category | `php tests/law-enforcement-category.php` | Offline helper/template contracts with stubbed queries |
| Projects pagination/SEO | `php tests/projects-pagination-seo.php` | Offline route mapping and metadata transformations using fixture arrays |
| Latest Work | `PROOF_TEST_WP_ROOT=/absolute/path/to/disposable/wordpress php tests/homepage-latest-work.php` | Real WordPress queries, rendering, publication state, featured/sticky selection, and loop restoration |
| Excerpt protection and cleanup | `PROOF_TEST_WP_ROOT=/absolute/path/to/disposable/wordpress php tests/excerpts.php` | Real WordPress password cookies, template surfaces, nested block serialization, and shortcode suppression |
| WordPress/SEO Framework HTTP | `npm --prefix tests/seo-integration test` | Disposable WordPress 7.1.3, PHP 8.3, TSF 5.2.0, and Redirection 5.10.1; actual responses and a historical negative control |
| Browser acceptance | `npm --prefix tests/seo-integration run browser` | Disposable WordPress and Chromium; keyboard interaction, computed link names, destinations, section order, and responsive overflow |

The `Theme checks` GitHub Actions workflow runs PHP lint, JavaScript syntax validation, and all three offline suites on PHP 7.4 and 8.3. It does not claim integration or browser coverage.

Latest Work integration coverage uses real WordPress queries and templates, plus the PHP DOM extension for markup assertions. On an empty, disposable local WordPress install with Proof active, `WP_ENVIRONMENT_TYPE` set to `local`, and `PROOF_DISPOSABLE_TEST_SITE` defined as `true` in its test-only `wp-config.php`, run:

```sh
PROOF_TEST_WP_ROOT=/absolute/path/to/disposable/wordpress php tests/homepage-latest-work.php
```

These CLI integration tests require the PHP DOM extension and create/remove synthetic fixture content only in the empty disposable database. They refuse an unmarked or non-local WordPress environment and an inactive checkout. Never enable the disposable flag on a real site. Latest Work covers publication order, all category lanes, featured and sticky posts, excluded statuses and post types, automatic publication, output escaping, legacy settings, Customizer registration, and main-loop restoration. Excerpts covers missing/wrong/valid password cookies, public content, every shared excerpt surface, custom deck/source fields, nested blocks, and shortcode non-execution.

For repeatable HTTP and browser integration, use Node 24.18 or newer, npm 11.16 or newer, Git, and tar. Keep full Git history so the harness can test the pinned original 2.0.14 canonical regression. Install the test-only dependencies and Chromium:

```sh
cd tests/seo-integration
npm ci
npm test
npx playwright install chromium
npm run browser
```

These runners create fresh WordPress Playground instances with SQLite and dispose of them afterward. They accept no existing WordPress URL or database. HTTP integration uses the supported web SAPI (`cgi-fcgi`) so the unmodified Redirection plugin executes its real HTTP redirect path. WordPress and plugin downloads are pinned by version and SHA-256; `PROOF_TEST_PLUGIN_CACHE` can point to a directory containing the exact plugin ZIPs, and `PROOF_TEST_WORDPRESS_ZIP` can supply the exact WordPress ZIP. Use `PROOF_TEST_RESULTS` for retained HTTP evidence and `PROOF_BROWSER_OUTPUT` for browser results/screenshots. These dependencies are testing tools, not a theme build pipeline.

The HTTP suite checks real status, canonical, robots, Open Graph, schema identities/references, and exact ordered membership for Law Enforcement's sole-feature, additional-story, and unpublished-feature states; all five Projects pages, nonexistent pages, legacy category aliases, and an unrelated category. The page-one alias is installed through the real Redirection plugin in the disposable fixture only. The same canonical assertion must reject the original failing 2.0.14 tree. Existing offline tests remain useful but cannot establish these integration contracts.

Browser acceptance uses real Chromium Tab, Enter, Escape, and click interaction at 1365, 768, 390, and 360 pixels. It verifies visible focus restoration, normal Tab progression without a trap, search autofocus, click-away, expanded states, both book names and destinations, and homepage/Projects/article overflow. It is a bounded acceptance check, not a complete screen-reader or WCAG certification. Playground results do not establish MySQL/MariaDB or production FPM/cache parity.

## Deployment prerequisites

Repository acceptance is separate from deployment approval. Before a future approved deployment, close the audit's outstanding current production byte attestation (including extra/untracked theme files), durable operations-evidence backup coverage, current backup/SQL importability, and rollback availability checks. Preserve historical audit/deployment records and the existing source pins, protected-state checks, timestamp safeguards, and rollback procedure. This development candidate performs none of those production operations.

## Scope and license

Proof is intentionally site-specific. Its source is public for inspection and development, but the theme is not maintained as a general-purpose WordPress theme or distributed through WordPress.org.

Current source is licensed under **AGPL-3.0-or-later**. See [LICENSE](LICENSE).

## Version history

See [CHANGELOG.md](CHANGELOG.md) for the complete version history.
