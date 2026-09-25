# Proof Theme

**Proof** is the custom classic WordPress theme built for JimLunsford.com. It powers a text-first publishing site with an identity hub, long-form writing, category lanes, topic hubs, project writing, The Work page, and a site-specific résumé.

**Current source version:** 2.0.7 candidate. This candidate has not been published to GitHub or deployed by this preparation pass.

## Project scope

Proof is intentionally built around JimLunsford.com. Its templates, curated post paths, category names, author copy, and Customizer defaults reflect the site's actual publishing needs. The planned public GitHub repository will make the source readable and provide a development home. It does not make Proof a general-purpose theme, a WordPress.org submission, or a commitment to provide support or packaged public releases.

The theme is licensed under **GPL-2.0-or-later**; see `LICENSE` and the header in `style.css`. The theme screenshot contains a portrait whose redistribution rights must be confirmed before the repository becomes public.

## Architecture and requirements

- Classic PHP templates and `template-parts/` handle the front page, posts, pages, search, archives, and site-specific hubs.
- `functions.php` registers theme support, menus, assets, Customizer settings, and the main category query rules. `inc/helpers.php` supplies the site-specific content and query helpers.
- `assets/css/theme.css` contains front-end styles; `style.css` holds the WordPress theme header and base screen-reader rules. `assets/js/navigation.js` manages the menu and search controls without a JavaScript framework.
- Curated public post cards only use published posts. Category pages use the main WordPress query, with twelve ordinary feed posts per page and separately presented featured posts excluded from that feed.
- The selected homepage portrait may generate WebP derivatives in WordPress uploads under `proof-cache/`. Generated files are runtime data, not repository source.
- The theme header declares WordPress 6.4 or later and PHP 7.4 or later. The `Tested up to` header records 6.9; that claim has not been independently reverified in this source preparation pass.

## Setup and development notes

After activating the theme on a disposable or authorized WordPress installation:

1. Assign the Primary Menu and Footer Menu.
2. Review Appearance > Customize > Homepage: Identity and configure the portrait, text, links, and optional image row.
3. Review the other Proof Customizer sections for The Work page, Projects, featured category posts, and footer or author content.
4. Create or assign the relevant pages, categories, and published posts for the site's curated paths. Missing curated post slugs fall back to the site homepage for ordinary links; unpublished posts are never rendered as curated cards.
5. Test navigation, search, single posts, pages, category pagination, topic hubs, and the front page at desktop and mobile widths.

The repository source is not a copy of the WordPress database, uploaded media, site configuration, or SEO plugin output. No build step or bundled vendor dependency is required for the shipped assets.

## Version history

### 2.0.7 candidate

- Restricted curated post ID resolution and featured-card rendering to published posts.
- Kept homepage destination and Latest Work cards as single links; link markup in their descriptions now retains its text without creating nested anchors.
- Moved category page size and featured-post exclusions into the WordPress main query, so routing, the feed, and pagination share one result set.
- Clarified public-source scope and setup in this README; added the GPL license text and a focused `.gitignore`.

## 2.0.6

Homepage identity portrait alt-text fix.

- Uses the WordPress Media Library attachment alt text for the homepage identity portrait.
- Falls back to the configured identity name when the attachment alt text is blank.
- Removed `aria-hidden` from the identity portrait wrapper so the meaningful portrait is available to assistive technology.
- Kept image sizing, generated WebP derivatives, loading behavior, visual treatment, and all other theme behavior unchanged.

## 2.0.5

SEO and semantic cleanup pass.

- Replaced the duplicated desktop/mobile “About This Writing” output with one semantic instance in the single-post reading flow.
- Made the editorial note context-aware: recovery-oriented posts keep recovery-specific safety language, general writing gets a broader editorial note, and Projects/Builder Receipts omit unrelated recovery boilerplate.
- Kept explicit recovery and health-adjacent Articles recognizable through their existing taxonomy plus a small recovery-intent fallback for clearly named article slugs.
- Reduced paginated category archives to a lean header and unique post listing on page 2 and later while preserving the full introduction, Start Here links, Core Posts, and recent posts on page 1.
- Preserved featured-post exclusion from the normal archive feed, existing pagination, heading hierarchy, homepage, Projects, Resume, responsive image behavior, and SEO-plugin boundaries.
- Added no theme-level schema, canonical, or robots output.

## 2.0.4

Resume refinement pass.

- Removed the "Professional Resume" kicker above the Resume page title.
- Changed the closing heading to "Let's Talk About the Right Role" so the final call to action is more employment-focused.
- Tightened the Brown County Sheriff's Office entry from three bullets to two so the current jail role does not visually outweigh the career direction the page is meant to support.
- Corrected the Town of Prince's Lakes Reserve Police Officer end date to September 2024.
- Existing Resume layout, selected work, proof links, responsive behavior, and other site templates remain unchanged.

## 2.0.3

Resume page pass.

- Added a native `Resume` page template for the `/resume/` professional hub.
- Added a concise professional header, current positioning, availability line, and direct links to Contact, GitHub, Projects, and The Work.
- Added a responsive four-part Core Capabilities grid for web publishing, product implementation, support/technical operations, and AI-assisted workflows.
- Added Selected Work for Bonumark Stream, JimLunsford.com, and JimLunsford.net.
- Added structured professional experience plus a separate Earlier Technical Experience section so the technical foundation remains visible.
- Added a curated Proof of Work section linking directly to four current Builder Receipts.
- Added Education & Training and a closing employment-focused contact call to action.
- Kept the page inside Proof's existing text-first visual system with rules, restrained typography, editorial rows, and mobile-first responsive behavior.
- The Resume template automatically applies to a page using the `resume` slug and can also be selected manually in the page editor.
- Existing site templates and homepage behavior are unchanged.

## 2.0.2

Homepage portrait performance pass.

- Kept the homepage portrait dimensions and visual treatment unchanged.
- Added responsive `sizes` information for the identity portrait.
- Added a small on-demand avatar cache that creates 160px, 200px, and 400px lossy WebP derivatives from the Customizer-selected portrait.
- The generated derivatives are cached in the uploads directory and reused on later requests.
- Added eager loading and high fetch priority for the above-the-fold identity portrait.
- Falls back to WordPress attachment markup if image optimization is unavailable.


## Version 2.0.1

Homepage Latest Work pass.

- Added a native curated Latest Work section to the identity hub homepage between Start Here and the optional image row.
- Added three editable Latest Work slots in the Theme Customizer with type label, title, short description, and URL controls.
- Preloaded the new slots with Bonumark Stream v0.6.0, the Bonumark Theme Architecture Builder Receipt, and Recovery Standard: Strong Boundaries.
- Styled Latest Work as a lighter editorial list with rules instead of another stack of boxed navigation cards, preserving the homepage visual hierarchy.
- Added responsive behavior so each item becomes a clean stacked row on mobile.
- Added matching critical CSS to avoid a first-paint layout shift on the identity hub.
- Bumped the theme version to 2.0.1.

## Version 2.0.0

Major identity release polish pass.

- Bumped the theme version to 2.0.0.
- Changed the theme author to Jim Lunsford.
- Replaced the bundled WordPress theme screenshot with the current identity hub homepage screenshot.
- Left the v1.0.63 work page project exclusion behavior intact.

## Version 1.0.63

Work page project exclusion pass.

- Updated Recent Published Work on The Work page so posts assigned to the selected Project Writing category are excluded.
- Kept project posts available on the Projects page and their normal individual post URLs.
- Reused the existing Project Writing category slug setting, so the exclusion follows the same category used by the Projects page.
- Left Articles, Notes, Discipline Dispatch, Projects, and category archive behavior unchanged.

## Version 1.0.62

Footer link cleanup pass.

- Removed the hardcoded JimL.fyi link from the footer site-links row.
- Kept JimLunsford.net and Phoenix 2:33 LLC footer links intact.
- Left the rest of the footer structure unchanged.

## Version 1.0.61

Full header description width pass.

- Let archive/category page descriptions use the full available header container width instead of the extra 820px limit.
- Applied the same full-width description behavior to Projects and The Work because they use the shared archive header description class.
- Kept descriptions inside the existing page container, so desktop gains room while mobile stays contained naturally.

## Version 1.0.60

- Widened archive/category page descriptions so the intro text uses more of the available desktop layout.
- Applied the same archive header description width behavior to the Projects and The Work page headers.
- Kept the intro text left-aligned and readable instead of forcing it full-width.
- Added an explicit mobile reset so descriptions stay contained and do not overflow on small screens.

## Version 1.0.59

Projects Customizer cleanup pass.

- Removed the unused Project Writing description Customizer control.
- Removed the unused Project Writing description helper because the Projects page no longer renders that paragraph.
- Kept the Project Writing heading and category slug controls intact.
- Kept the Projects page archive-style layout and pagination behavior intact.

## Version 1.0.58

Projects pagination consistency pass.

- Removed the custom View all project articles link from the Projects page.
- Added real numbered pagination for Project Writing posts using the same proof-pagination markup as category archives.
- Made the Project Writing query respect paged URLs, so additional project article pages can render properly.
- Kept the Projects page hub structure and category-sourced post behavior intact.

## Version 1.0.57

Projects archive consistency pass.

- Refined the Projects page so it visually follows the same archive rhythm as category archive pages.
- Kept the Projects page as a hub while making project-card titles behave like archive/feed titles.
- Removed the custom Project Writing intro paragraph from the rendered Projects template for a cleaner category-style section.
- Restyled the Project Writing archive link to sit closer to the theme pagination/link treatment.

## Version 1.0.56

Project writing integration pass.

- Removed the hardcoded longform Projects page body copy from the page template.
- Kept the Projects page header and project cards intact.
- Added a Project Writing section under the project cards.
- The new section pulls normal WordPress posts from the `projects` category by default.
- Added Customizer controls for the Project Writing heading, description, and category slug.
- Added a View all project articles link to the selected category archive when the category exists.
- Kept project articles as normal WordPress posts instead of creating a custom post type.
- Preserved the existing archive/card styling and empty-state behavior.

## Version 1.0.55

Homepage Customizer cleanup pass.

- Removed legacy homepage Customizer sections and controls that belonged to the old hero, Explore the Site, Current Work, and Follow Along homepage structure.
- Removed the Start Here intro paragraph from the identity hub front page and removed its Customizer control.
- Kept the current identity hub Customizer controls focused on identity, destination links, optional image row, and Follow and Connect links.
- Renamed homepage Customizer sections to Homepage: Identity, Homepage: Start Here Links, Homepage: Image Row, and Homepage: Follow and Connect.
- Preserved generic numbered link slots for both destination links and social/contact links.
- Preserved clean hiding behavior for blank buttons, blank destination links, blank social links, and empty image rows.
- Removed unused legacy homepage template parts and helper functions after confirming they were no longer referenced by active templates.
- Preserved the current homepage design and interior page layouts.

## Version 1.0.54

Homepage professional polish follow-up pass.

- Widened the homepage identity container slightly so the desktop layout breathes better without becoming a wide corporate layout.
- Enlarged and rebalanced the desktop profile image so it carries more visual weight beside the name.
- Rewrote the default Start Here intro so it no longer implies Microblog must also be the first destination card.
- Removed the special first-card treatment from homepage destination links so the cards read as consistent paths instead of a selected state.
- Changed mobile social links back to compact wrapping pills instead of heavy full-width rows.
- Tightened the mobile hero spacing and profile image size slightly while preserving the centered image-above-text layout.
- Softened the mobile footer density with smaller type, tighter line height, and reduced spacing.
- Removed the unused identity location helper left behind after the location field was removed from the homepage.

## Version 1.0.53

Homepage and design system polish pass.

- Refined the identity hub homepage spacing, hierarchy, buttons, and mobile rhythm while keeping the desktop image-left layout and mobile centered image stack.
- Removed the unused Location control from the Homepage: Identity Hub Customizer section because the location line no longer displays on the front page.
- Capitalized the default homepage section headings to Start Here and Follow and Connect.
- Turned the homepage destination links into stronger destination cards with clearer titles, more readable descriptions, a subtle arrow cue, and a restrained first-link emphasis for the primary path.
- Made the social links quieter and more compact so they support the page instead of competing with the main destinations.
- Improved the image row spacing and retained clean hiding behavior when no images are set.
- Added light design-system polish across cards, feeds, projects, framework blocks, buttons, pagination, and key text contrast so interior pages feel connected to the new homepage without being rebuilt.
- Updated the theme screenshot and refreshed theme metadata.

## Version 1.0.52

- Changed Homepage: Identity Links controls to generic numbered fields, Link 1, Link 2, Link 3, and so on, instead of naming the slots after the default destination.
- Changed Homepage: Social Links controls to generic numbered fields so each slot can be repurposed without the Customizer implying a fixed platform.
- Clarified that leaving a title/label or URL blank removes that link from the homepage.
- Reindexed displayed identity and social links after filtering so removed links do not leave layout gaps.

## Version 1.0.51

- Centered the mobile identity heading under the centered profile image.
- Kept the profile image stacked above the identity text on mobile.
- Kept the longer bio paragraph left-aligned for readability.

## Version 1.0.50

- Fixed the inline identity hub critical CSS that was still forcing the compact mobile image-left layout from v1.0.48.
- Mobile now correctly uses the stacked layout with the profile image centered above the identity text.
- Kept the location line removed from the homepage identity area.

## Version 1.0.49

- Restored the mobile identity hub to a stacked hero layout.
- Centered the profile image above the identity text on mobile.
- Preserved the removed location field and the desktop left-image layout from v1.0.48.

## Version 1.0.48

Identity hub compact mobile pass.

- Removed the location line from the front-page identity area so the hero stays focused on name, identity, bio, and action.
- Kept the desktop left-image identity layout intact.
- Changed the mobile identity hero from a stacked poster layout to a compact image-left, copy-right layout.
- Reduced mobile profile image size and tightened spacing so the homepage reads more like a personal identity card.

## Version 1.0.47

Homepage header removal pass.

- Hid the normal site masthead on the identity hub front page by default so the page starts with the personal identity card instead of repeating the site title.
- Kept the normal Proof header intact everywhere else on the site.
- Reused the existing Homepage: Identity Hub setting so the header can be restored from the Customizer if needed.
- Added a front-page body class and small spacing refinements for desktop and mobile when the header is hidden.

## Version 1.0.46

Identity hub mobile hardening pass.

- Converted homepage destination and social links to real list markup so mobile has structure even if cached CSS lags behind.
- Added cache-busting asset versions based on the theme file modification time.
- Added critical identity hub layout styles inline after the main theme stylesheet so the profile image, destination rows, social links, and mobile layout render correctly after updates.
- Tightened mobile destination and social spacing while preserving the identity-card front page direction.

## Version 1.0.45

Identity hub front page pass.

- Replaced the old section-heavy front page with a profile-style identity hub.
- Added a prominent name, profile image, identity line, location, short bio, and primary action buttons.
- Added customizable destination rows for Microblog, The Work, Articles, Projects, Core Frameworks, About, Contact, and optional future links.
- Made the Microblog the first default destination.
- Added an optional four-image homepage row controlled through the Customizer.
- Added a lower social links section with Microblog, X, GitHub, LinkedIn, Contact, and optional Facebook.
- Added a setting to minimize the heavy top navigation on the front page so the identity hub carries first impression.
- Kept articles, archives, projects, pages, framework pages, and the previous homepage settings intact for that release.
- Updated the theme screenshot, metadata, CSS, and Customizer controls.

## Version 1.0.43

- Matched The Work and Projects page layout width to category archive pages.
- Removed the extra custom max-width restrictions from The Work and Projects wrappers.
- Let Projects body copy use the archive-style content width instead of a centered narrow reading column.

## Version 1.0.42

- Reverted the mobile card grid collapse added in 1.0.41 because the previous mobile card layout looked better on the live site.
- Kept the masthead fallback tagline update from 1.0.41.

## Version 1.0.41

- Fixed mobile card grids so homepage, The Work, and Projects card grids collapse to one column on smaller screens.
- Updated the masthead fallback tagline from the old role-based wording to the current site tagline.

## Version 1.0.40
- Removed hardcoded footer links for X, Facebook, LinkedIn, Amazon, and Disciplined Recovery.

## Version 1.0.39

- Tightened homepage hero title line spacing on mobile while preserving forced line breaks.
- Kept each hero title sentence on its own line without the large vertical gaps.

## Version 1.0.36

Homepage Follow Along section.

- Added a customizable Homepage Follow Along section below Current Work.
- Added editable Microblog and Subscribe cards with title, description, link label, and URL controls.
- Kept the section intro width consistent with Explore the Site and Current Work.

## Version 1.0.35

Homepage Current Work intro spacing refinement.

- Widened the homepage section intro text for Explore the Site and Current Work so it uses the available section width consistently.
- Kept the card sections and Customizer settings unchanged.

## Version 1.0.34

Homepage Current Work section release.

- Added a customizable Homepage Current Work section under Explore the Site.
- Added four editable cards for Bonumark Stream, Carceris, The Writing, and The Microblog.
- Added editable link labels and URLs for each Current Work card.
- Set default Current Work links to bonumark.org, carceris.org, /the-work/, and jimlunsford.net.

## Version 1.0.33

Homepage section naming and spacing refinement.

- Renamed the homepage card section default from Start Here to Explore the Site.
- Renamed the Customizer section from Homepage: Start Here to Homepage: Explore the Site.
- Widened the homepage section intro text so it uses the available space instead of sitting in a narrow left column.

## Version 1.0.32

Homepage Start Here section.

- Added a dedicated Homepage: Start Here Customizer section.
- Added a new front-page Start Here section under the hero.
- Added editable homepage cards for About Jim, The Work, Projects, and Microblog.
- Kept The Work page Start Here settings separate so the homepage does not reuse or overwrite those links.

## Version 1.0.31

Projects page public-copy cleanup.

- Removed internal implementation language from the Projects page body copy.
- Replaced references to Customizer/card logic with public-facing language about letting the page grow as projects are ready.

## Version 1.0.30

Projects page card visibility and layout refinement.

- Added Customizer show/hide controls for each Projects page card.
- Set Bonumark Stream and Carceris as the default visible project cards.
- Kept Bonumark and Capsarium available in the Customizer for later use, but hidden by default.
- Updated Projects body copy so it does not present unreleased ideas as released projects.
- Reworked Projects body markup and CSS so the reading copy centers like normal page body content while section dividers stay full-width.

## Version 1.0.29
- Corrected Projects page card heading capitalization.
- Added a wide layout variable used by The Work and Projects templates.
- Changed Projects body layout so the section divider spans the page width while the reading text is centered.

## Version 1.0.28

Projects page template release.

- Added a dedicated Projects page template for the `projects` page slug.
- Matched the same archive-style header pattern used by The Work page.
- Added Customizer controls for the Projects page header description and Start here URL.
- Added editable project cards for Bonumark, Bonumark Stream, Carceris, and Capsarium.
- Added hardcoded Projects page body copy below the editable cards.
- Added Projects page layout and card styling.

## Version 1.0.27

- Added clean excerpt handling for post feed cards.
- Recent Published Work cards now strip subscription forms, signup shortcodes, block comments, and repeated email signup CTA text before generating excerpts.
- Updated the archive feed item template to use the cleaned excerpt helper instead of raw `the_excerpt()`.

## Version 1.0.26

- Added an archive-style header to the The Work page template.
- The Work page now shows the page title, a Customizer-editable description, Start here and Subscribe links, and then the standard section divider.
- Added Customizer controls for The Work page header description and Start here URL.

## Version 1.0.25

The Work page relocation release.

- Added a dedicated The Work page template for the `the-work` page slug.
- Moved the former below-hero homepage sections into The Work page template.
- Kept Start Here, Problem Routing, and Five Pillars powered by the existing Customizer settings.
- Moved Recent Published Work into a reusable dynamic query helper so it can render on The Work page.
- Simplified the front page so it now renders the hero only.
- Updated the default hero button URL to `/the-work/` for new installs or unset Customizer values.
- Renamed the relevant Customizer section labels from Front Page to The Work Page while preserving saved setting IDs.

## Version 1.0.24

Front-page hero title line break release.

- Split the front-page hero title into sentence-based display lines.
- Added a dedicated hero title line class so each sentence can render on its own line.
- Increased the hero title max width so the final line does not wrap awkwardly on desktop.

## Version 1.0.23

Front-page hero identity update.

- Updated default front-page hero copy for the broader JimLunsford.com direction.
- Replaced the hardcoded hero Subscribe button with customizable hero button label and URL settings.
- Set the default hero button to Start Here and link it to the About page.
- Broadened theme description, footer note default, and author block default away from the older narrow doctrine framing.

## Version 1.0.22

SEO link-text hardening release.

- Replaced the previous `aria-label` only fix with hidden descriptive text inside each post-feed Read more link.
- Visible text still stays as "Read more," but the actual link text now includes the post title for Lighthouse, screen readers, and crawlers.

## Version 1.0.21

SEO and accessibility refinement release.

- Updated archive and post-feed "Read more" links so their accessible names include the post title while keeping the visible text unchanged.
- This addresses the PageSpeed/Lighthouse warning that generic links do not have descriptive text.

## Version 1.0.20

Cleanup and hardening release.

- Fixed duplicate search field IDs by generating a unique search input ID for every rendered search form.
- Removed the mobile metadata `aria-hidden` issue so visible mobile post metadata is available to assistive technology.
- Added a skip link after `wp_body_open()`.
- Improved category pagination markup with an accessible navigation wrapper.
- Escaped pagination output with `wp_kses_post()`.
- Switched the footer year to WordPress-native `wp_date()`.
- Escaped visible site name and description output where direct `bloginfo()` calls were used.
- Added `wp_link_pages()` support to posts and pages.
- Removed comment-related theme support and theme tags because comments are not used on jimlunsford.com.
- Added progressive enhancement for the primary navigation so it remains available if JavaScript fails.
- Cleaned the changelog into a single source of truth.

## Recent version history

- 1.0.55: Cleaned the homepage Customizer around the identity hub, removed the Start Here intro, and removed unused legacy homepage code.
- 1.0.54: Widened the identity hub, removed the selected-looking first-card state, restored compact mobile social pills, tightened mobile spacing, and softened the mobile footer.
- 1.0.53: Polished the homepage identity hub, destination cards, social links, mobile rhythm, screenshot, and shared design-system details.
- 1.0.52: Made homepage identity and social link Customizer controls generic numbered slots and hardened hidden-link filtering.
- 1.0.51: Centered the mobile identity heading under the centered profile image while keeping the bio readable.
- 1.0.50: Fixed inline critical CSS so mobile correctly stacks the centered profile image above the identity text.
- 1.0.49: Restored the mobile identity hub to a stacked hero layout.
- 1.0.48: Removed the location line and briefly tested compact mobile identity row.
- 1.0.47: Hid the normal site header on the identity hub front page.
- 1.0.46: Hardened mobile identity hub link structure and cache-busting.
- 1.0.45: Added identity hub front page with profile hero, destination links, image row, social links, and minimized front-page navigation.
- 1.0.34: Added a customizable Homepage Current Work section with four cards.
- 1.0.33: Renamed the homepage section to Explore the Site and widened the section intro text.
- 1.0.32: Added a customizable Homepage Start Here section under the hero.
- 1.0.31: Removed internal implementation language from the Projects page body copy.
- 1.0.30: Added Projects card visibility controls, defaulted cards to released projects only, and centered Projects body copy.
- 1.0.29: Fixed Projects page heading capitalization and centered reading body while keeping full-width dividers.
- 1.0.28: Added Projects page template, editable project cards, and hardcoded Projects body copy.
- 1.0.27: Cleaned post feed excerpts so signup forms and subscription CTA text do not appear in Recent Published Work.
- 1.0.26: Added archive-style header controls to The Work page template.
- 1.0.25: Former below-hero homepage sections moved to The Work page template.
- 1.0.24: Hero title sentences now render as separate lines.
- 1.0.23: Front-page hero copy defaults updated and hero button made customizable.
- 1.0.22: Hidden descriptive post-title text added inside post-feed Read more links.
- 1.0.21: Descriptive accessible labels for post-feed Read more links.
- 1.0.20: Cleanup and hardening pass.
- 1.0.19: Current production baseline before cleanup pass.
- 1.0.18: Prior design and behavior refinements.
- 1.0.17: Prior design and behavior refinements.
- 1.0.16: Prior design and behavior refinements.
- 1.0.15: Prior design and behavior refinements.
- 1.0.14: Prior design and behavior refinements.
- 1.0.13: Prior design and behavior refinements.

