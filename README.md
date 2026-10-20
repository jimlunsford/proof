# Proof

Proof is a custom, text-first WordPress theme built for [JimLunsford.com](https://jimlunsford.com/). It gives long-form writing, books, frameworks, and software projects a consistent home without forcing everything into one feed.

The theme is designed for a real, actively maintained publishing site. It is intentionally site-specific, not a general-purpose WordPress theme or a WordPress.org marketplace product.

## What Proof does

- **Reading-first pages:** Clean typography and focused layouts for articles, notes, essays, and long-form work.
- **A homepage with direction:** An introduction, Start Here destinations, published books, and useful ways into the site's writing and projects.
- **Latest Work:** The five newest published posts appear automatically, regardless of category.
- **Distinct editorial archives:** Category introductions, featured stories, and paginated writing lists for the site's different content families.
- **Projects and books:** Curated project cards, a browsable Projects archive, and book features linking to their full pages.
- **Responsive navigation:** Mobile-friendly menus, search, visible keyboard focus, and accessible controls.
- **WordPress-native publishing:** Gutenberg content, menus, media, and Customizer settings remain editable through WordPress.

## Technical approach

Proof is a classic theme built with PHP templates, reusable template parts, CSS, and a small navigation script. It does not require a frontend framework or a build pipeline.

- `functions.php` handles theme support, asset loading, menus, Customizer controls, and WordPress hooks.
- `inc/` contains shared presentation and query helpers, including the focused integration for paginated Projects SEO.
- `template-parts/` keeps major sections reusable and easier to maintain.
- `assets/css/theme.css` and `assets/css/editor-style.css` provide site and editor styling.
- `assets/js/navigation.js` handles navigation and search interaction.

WordPress continues to own publishing, permissions, media, and ordinary content management. [The SEO Framework](https://theseoframework.com/) handles the site's standard SEO metadata.

## Requirements

- WordPress 6.4 or newer. Theme metadata is tested up to WordPress 7.1.3.
- PHP 7.4 or newer.
- Current source version: **2.0.16**.

Proof's source repository does not contain the live site's database, uploaded media, or WordPress configuration.

## Documentation

- [Theme maintenance notes](docs/THEME-MAINTENANCE.md): Site-specific features, Customizer conventions, and important behavior boundaries.
- [Testing guide](tests/README.md): Offline, WordPress integration, SEO, and browser testing.
- [Version history](CHANGELOG.md): Changes made to the theme over time.

## License

Proof is licensed under **AGPL-3.0-or-later**. See [LICENSE](LICENSE).
