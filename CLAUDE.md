# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repository is

The WordPress theme **« La Brévinoise »** for the association La Brévinoise Jeux, a board-game publisher in Saint-Brevin-les-Pins. Everything is in French: UI strings, code comments, PHPDoc, and the readme (`LISEZMOI.txt`). Keep new code and text in French to match.

This is **not a git repo and has no unpacked source tree**. The working directory only holds zip snapshots, one per iteration. Each one is named after the change it introduced:

| Zip | Theme `Version:` |
|---|---|
| `brevinoise.zip`, `brevinoise encart escape.zip`, `brevinoise bouton vidéo.zip` | 1.0.0 |
| `brevinoise pages jeux.zip` | 1.1.0 |
| `brevinoise carrousel a droite.zip` | 1.1.1 |
| `brevinoise redimension images carrousel.zip` | 1.1.2 |
| `brevinoise etiquettes gauche.zip` | 1.1.3 (latest) |
| `Site La Brevinoise visualisateur.zip` | Static HTML/JS mockup (v1.2.0 of the mockup), predates the theme |

The latest snapshot is the one with the highest `Version:` in `brevinoise/style.css`. File mtimes are not reliable for this. To work on the theme, unzip the latest snapshot into a scratch directory, edit it there, and re-zip it as a **new** snapshot. Do not overwrite existing zips.

- Each theme zip must contain a single top-level folder `brevinoise/`, because WordPress installs it as the theme directory.
- For the build that will be uploaded to WordPress, the file must be named `brevinoise.zip` (Apparence › Thèmes › Téléverser › « Remplacer l'actuel »).
- Find the version with: `unzip -p "<zip>" brevinoise/style.css | grep -m1 '^Version'`

There is no build step, linter, test suite, or local PHP/WP-CLI. You can only verify changes in a real WordPress install (WP ≥ 6.0, PHP ≥ 7.4).

## Bump the version on every release

`BREV_VERSION` is read from the `Version:` header in `style.css`. Three things depend on it:
- It is the cache-busting `ver` for every enqueued CSS and JS file.
- `brev_maybe_flush()` (`inc/setup.php`) flushes rewrite rules when the version changes. New CPT slugs or `/jouer/` routes only take effect after a bump.
- `brev_check_update()` (`inc/tools.php`) offers an auto-update when a remote JSON at `brev_update_url` has a higher `version` and a `package` URL.

## Theme architecture (`brevinoise/`)

`functions.php` only defines `BREV_VERSION`, `BREV_DIR` and `BREV_URI`, then `require`s the `inc/` files in order. All functions use the `brev_` prefix.

- **`inc/defaults.php` is the central registry.**
  - `brev_defaults()`: every Customizer setting and its default value. Read settings with `brev_mod( 'key' )`, which wraps `get_theme_mod( 'brev_' . $key )`. Never call `get_theme_mod` directly.
  - `brev_types()`: the five content types (`festival`, `jeu`, `escape`, `bd`, `adherent`). Each entry sets its post type `brev_*`, archive slug, admin labels, and a `fields` map `name => [label, fieldType, help]`. Post types, post meta (`_brev_<field>`), the « Informations » meta box, its saving, and the demo import are all generated from this array. To add a field, edit `brev_types()` and, if the field type is new, the render/save branches in `inc/post-types.php`. Field types: `text`, `url`, `games`, `gallery`, `play`, `kind`.
  - `brev_nav_games()`: the registry of browser-playable games bundled in `jeux/`.
- **`inc/post-types.php`**: registers the CPTs (classic editor forced, `show_in_rest` false), the meta boxes, and saving. `brev_key_of( $post_type )` maps a post type back to its short key.
- **`inc/customizer.php`**: the « La Brévinoise » Customizer panel. Its keys must exist in `brev_defaults()`.
- **`inc/setup.php`**:
  - Asset enqueue, plus inline CSS that injects the colour settings as `--primary`, `--dark`, `--accent` and `--soft`.
  - `pre_get_posts` ordering: `menu_order` first, then newest first.
  - Menu helpers and a fallback menu.
- **`inc/template-tags.php`**: rendering helpers used by the templates (`brev_card`, `brev_fest_card`, `brev_member_card`, `brev_pop_template`, `brev_gallery_ids`, `brev_video_html`, …). It also has a `single_template` filter that routes `jeu`, `escape` and `bd` posts to `single-fiche.php`, the presentation page with tags, shop button, carousel and video tab.
- **`inc/contact.php`**: handles the contact form through `admin-post.php` (`brev_contact`), with a nonce and a honeypot field `site_web`. It sends `wp_mail` to `brev_mod('contact_email')`, or to the admin e-mail if that is empty.
- **`inc/game.php`**: the online game « Gare au Chacal ».
  - Settings live in the `brev_gac` option and are edited on the admin page `brev-gac`.
  - The rewrite rule `^jouer/<slug>/` sets the query var `brev_game`. On `template_redirect`, the theme reads `jeux/gare-au-chacal/game.html` and replaces `<script src="../../assets/data.js"></script>` with `window.BREV_GAME_CFG = {...}`. It also replaces the back link `../../index.html#jeux`, then echoes the HTML and exits.
  - **`game.html` is a standalone file shared with the static mockup.** Keep both placeholder strings exactly as they are, or WordPress injection breaks. Outside WordPress, the game falls back to `window.BREV_DEFAULTS` and localStorage.
- **`inc/tools.php`**: the admin page « La Brévinoise : outils » (`brev-tools`). It provides:
  - Demo-content import from `demo/img/`, tracked by the option `brev_demo_done`.
  - JSON export and import of theme mods and `brev_gac`.
  - The self-hosted update-check URL.
- **Templates:**
  - `front-page.php`: the home page.
  - `archive.php`: all CPT archives. The template branches on the post type key.
  - `single-fiche.php`, `template-contact.php`, `header.php`, `footer.php`, `index.php`.
- **Front-end JS (`assets/theme.js`)**: vanilla JS with no dependencies. It handles the burger menu, the search and category filter on archives, and the hover/tap popovers. Popover content comes from `<template id="pop-…">` elements emitted by `brev_pop_template`. It also runs the `[data-carousel]` scroll-snap carousels and the tabs on the fiche page.
- **Admin JS**: `assets/admin-media.js` is the media picker on the game settings page. `assets/admin-gallery.js` is the gallery field on fiches.

Content and settings are stored in WordPress (CPT posts, theme mods, options), not in the theme. A theme update must never require migrating them, so keep existing option and meta keys stable.

## Static mockup (`Site La Brevinoise visualisateur.zip`)

This is an earlier client-side prototype of the same site: open `index.html` directly, no server needed. Its pieces line up with the theme as follows:
- `assets/data.js` (`window.BREV_DEFAULTS`) corresponds to `brev_defaults()` plus the demo content.
- `assets/admin.js` is a fake admin that stores data in localStorage (`brevinoise-site`).
- `assets/site.js` renders the pages.

Use it as a reference for intended behaviour and design. The real deliverable is the WordPress theme.

## Design notes (from the `style.css` header)

- The colour palette is configurable in the Customizer. It is navy blue from the logo, white, and the meeple yellow.
- Fonts are Fredoka and Nunito.
- Layout: logo on a white background, a menu bar, full-width bands, and a dark footer.
- Tone: community-run, family-friendly, cheerful.
