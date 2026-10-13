# Proof

Proof is the custom classic WordPress theme powering [JimLunsford.com](https://jimlunsford.com/). It is a text-first publishing system built around long-form reading, an identity-focused homepage, distinct content lanes, and a growing body of writing and project work. The site is public and actively used; this repository holds the theme's development source.

**Current repository version:** 2.0.13.

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

The theme header requires WordPress 6.4 or later and PHP 7.4 or later. Its `Tested up to` field is 6.9. The repository contains the theme source, not the site's database, uploaded media, or WordPress configuration.

## Homepage book discovery

Books appears in Start Here immediately before About, preserving the existing `proof_identity_destination_book_*` setting IDs. The homepage-only Published Books section sits between Start Here and Latest Work. Its two compact features link to the individual book pages, not directly to checkout. Full book copy and purchase links remain on the book pages and `/books/`.

Maintain the two curated features under **Customizer > Homepage: Published Books**. Titles, descriptions, URLs, and cover media are editable. A blank title, URL, or missing/non-image cover hides that slot; clearing both hides the section. Current defaults use JimLunsford.com media IDs 2586 and 2615. No saved theme mods are rewritten by a theme update.

The section stylesheet is scoped to the book classes and added only on the homepage.

## Automatic Latest Work

Latest Work displays the five newest published WordPress posts after Published Books and before the optional image row and Follow and Connect. Every category is eligible, including Projects and posts featured elsewhere. Entries use the existing category and clean-excerpt helpers, the published title, and the canonical post permalink, with one accessible link per item. The existing desktop two-column and mobile single-column list styles are retained.

The dedicated `proof_get_identity_latest_work_query()` orders by original publication date, newest first. Editing an older post or making it sticky does not promote it. Drafts, pending posts, scheduled posts not yet published, private posts, trash, pages, and custom post types are excluded. Fewer than five eligible posts produces a shorter list; no eligible posts hides the section. Publishing a post updates the list through the normal WordPress query, without manual slots, synchronization, or an additional plugin.

The heading is fixed as **Latest Work**. The retired Selected Work Customizer section, heading, and three manual slots are no longer registered or read. Existing `proof_identity_latest_work_*` theme mods remain stored unchanged; no migration or database cleanup runs. Other Customizer settings, the separate recent-writing feed, and category archive exclusions and pagination retain their existing behavior.

## Development

`main` is the accepted source. Focused changes are developed on branches and reviewed through pull requests before merging. Repository acceptance does not automatically update JimLunsford.com; production deployment is handled separately.

Run the standalone Published Books regression with `php tests/homepage-books.php`.

Latest Work integration coverage uses real WordPress queries and templates, plus the PHP DOM extension for markup assertions. On an empty, disposable local WordPress install with Proof active, `WP_ENVIRONMENT_TYPE` set to `local`, and `PROOF_DISPOSABLE_TEST_SITE` defined as `true` in its test-only `wp-config.php`, run:

```sh
PROOF_TEST_WP_ROOT=/absolute/path/to/disposable/wordpress php tests/homepage-latest-work.php
```

This CLI test creates and removes fixture posts, categories, sticky status, and theme mods in that disposable database. It refuses to run without the explicit test-site flag or with existing content. Never enable that flag on a real site. Coverage includes publication order, all category lanes, featured and sticky posts, excluded statuses and post types, automatic publication, output escaping, legacy settings, Customizer registration, and main-loop restoration. Browser acceptance should additionally check 1365px, 390px, and 360px widths, section order, adjacent sections, navigation, footer, and overflow.

## Scope and license

Proof is intentionally site-specific. Its source is public for inspection and development, but the theme is not maintained as a general-purpose WordPress theme or distributed through WordPress.org.

Current source is licensed under **AGPL-3.0-or-later**. See [LICENSE](LICENSE).

## Version history

See [CHANGELOG.md](CHANGELOG.md) for the complete version history.
