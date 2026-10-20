# Proof theme maintenance

This document records site-specific behavior for maintainers of Proof. It describes theme code and WordPress-facing settings, not server access, production deployment, backups, or recovery procedures.

Proof is designed for [JimLunsford.com](https://jimlunsford.com/). The latest source code and actual saved WordPress settings are authoritative when defaults or published content change.

## Homepage and Customizer

The homepage is an identity and discovery page. Start Here links direct readers into the site's major areas. The Published Books section appears after Start Here and before Latest Work. Follow and Connect and optional imagery remain separate parts of the layout.

The book destination in Start Here uses the existing `proof_identity_destination_book_*` theme-mod keys. The two homepage book features are maintained under **Appearance > Customize > Homepage: Published Books**. Their setting prefixes are `proof_identity_published_books_one_` and `proof_identity_published_books_two_`, each with `title`, `description`, `url`, and `image_id` fields.

A book feature is hidden if its title, destination URL, or valid cover image is missing. Its link goes to the book's own page rather than directly to a purchase checkout. The full descriptions and purchase links belong on those pages.

The source defaults at version 2.0.16 use WordPress media attachment IDs **2586** and **2615**. These are site-specific defaults, not portable assets or required IDs for another installation. Saved Customizer settings take precedence, and updating the theme must not silently rewrite them.

## Automatic Latest Work

`proof_get_identity_latest_work_query()` selects up to five published WordPress posts, ordered by original publication date, newest first. Every category, including Projects and featured posts, is eligible. Sticky status and later edits do not override that publication ordering.

Drafts, pending posts, scheduled posts not yet published, private posts, pages, custom post types, and trashed posts do not enter Latest Work. The section is omitted when no eligible posts exist. Queries restore WordPress post data after rendering.

Latest Work is distinct from Recent Work, curated featured posts, and category archives. The old manually curated Selected Work controls are no longer registered or read. Existing `proof_identity_latest_work_*` values remain stored but are not migrated or removed by theme updates.

## Law Enforcement & Corrections

The archive is the normal WordPress category at `/category/law-enforcement-corrections/`. The editable category description supplies its introduction. The published post with slug `why-i-loved-the-work` serves as its initial Start Here destination and featured story.

When that story is the category's only published article, it stays in the main WordPress category query to preserve indexability and canonical metadata. The page renders it once under Featured Stories and does not display an empty Recent Stories section.

When more articles are published, the featured story is excluded from the ordinary recent feed and normal 12-post pagination resumes. An unpublished featured story is not displayed as a published recommendation.

This is an intentional interaction with WordPress's main query and The SEO Framework; do not replace it with a separate query without equivalent route and metadata tests.

## Projects

The custom `/projects/` listing displays six published articles per page, separate from its curated project cards. Its special paginated canonical, Open Graph, schema, and 404 behavior lives in `inc/projects-pagination-seo.php`. The Redirection plugin owns the page-one legacy alias from `/category/projects/`; deeper legacy offsets are handled by the theme.

**Current taxonomy boundary:** The Projects listing can include descendant category posts, but its page-count and invalid-page checks use the parent category's direct published count. The site's current category structure is flat. Introducing child categories requires a separate correction and tests for the shared eligibility/count rule.

## Excerpts and protected content

The shared clean-excerpt helper in `inc/helpers.php` honors WordPress password access before reading raw post text or manually supplied excerpts. Custom article decks and source/support fields use the same protection boundary in their template parts.

Nested Gutenberg excerpt cleanup excludes known subscription forms and CTAs without dropping ordinary narrative or executing shortcodes. Preserve the intended distinctions when revising this helper.

## Source and testing references

- `functions.php`: Theme setup, Customizer settings, and main-query hooks.
- `inc/helpers.php`: Shared helpers, excerpts, and content selection.
- `inc/projects-pagination-seo.php`: Projects-specific metadata and pagination handling.
- `template-parts/`: Homepage, archive, and single-post presentation.
- [Testing guide](../tests/README.md): How these behaviors are verified in disposable environments.

Do not store production credentials, privileged server instructions, or private recovery material in this public repository.
