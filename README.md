# Proof

Proof is the custom classic WordPress theme powering [JimLunsford.com](https://jimlunsford.com/). It is a text-first publishing system built around long-form reading, an identity-focused homepage, distinct content lanes, and a growing body of writing and project work. The site is public and actively used; this repository holds the theme's development source.

**Current repository version:** 2.0.10.

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

## Development

`main` is the accepted source. Focused changes are developed on branches and reviewed through pull requests before merging. Repository acceptance does not automatically update JimLunsford.com; production deployment is handled separately.

## Scope and license

Proof is intentionally site-specific. Its source is public for inspection and development, but the theme is not maintained as a general-purpose WordPress theme or distributed through WordPress.org.

Current source is licensed under **AGPL-3.0-or-later**. See [LICENSE](LICENSE).

## Version history

See [CHANGELOG.md](CHANGELOG.md) for the complete version history.
