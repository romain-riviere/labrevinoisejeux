# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repository is

The WordPress theme **« La Brévinoise »** for the association La Brévinoise Jeux, a board-game publisher in Saint-Brevin-les-Pins. Everything is in French: UI strings, code comments, PHPDoc, and the READMEs (`README.md` at the root for developers, `brevinoise/README.md` for site admins, shipped inside the theme zip). Keep new code and text in French to match.

`README.md` (in French) is the full developer guide: workflow, compatibility rules, testing, publishing, architecture. Read it before changing scripts or the release process.

Layout:
- `brevinoise/` is the theme itself; this folder is what gets zipped and installed.
- `scripts/` holds the tooling.
- `dist/` and `.wp-test/` are generated and git-ignored.

The early history was rebuilt from zip deliveries, one commit per zip, dated from the zip. Released versions are tagged `vX.Y.Z`. **The latest tag is the version running in production**, and it is the baseline for every compatibility check. The original `*.zip` files are git-ignored and are not a source of truth any more.

## Commands

Everything runs in Docker, so Docker Desktop must be running. There is no local PHP.

The scripts must run both on macOS (bash 3.2 and BSD tools) and on Windows through WSL 2 Ubuntu (GNU tools):
- Use `sed -i.bak`, not `sed -i ''`.
- Do not use `head` in pipes, because `pipefail` turns SIGPIPE into a failure.
- Do not use GNU-only or BSD-only flags.

`.gitattributes` forces LF line endings.

| Command | What it does |
|---|---|
| `make lint` | PHP syntax on 7.4 and 8.3, JS syntax (including the inline scripts of `game.html`), `style.css` header, version in the `brevinoise/README.md` title, `game.html` placeholders, junk files |
| `make compat` | `scripts/compat.sh [ref]`: diffs the theme "contract" against the latest tag, fails on any removal |
| `make test` | `scripts/test-wp.sh --php 7.4` (WordPress 6.1) and `--php 8.3` (recent WordPress): full upgrade rehearsal from the latest tag |
| `make site` | Same as the test on PHP 8.3, but leaves WordPress running at http://localhost:8083 (admin / admin) |
| `make patch`, `make minor`, `make major` | `scripts/version.sh`: bumps `style.css` and the `brevinoise/README.md` title |
| `make build` | `dist/brevinoise.zip`, `dist/brevinoise-X.Y.Z.zip` and `dist/brevinoise-update.json`, built with `git archive` from **committed** files (`--local` builds the working tree instead) |
| `make release` | Runs all of the above, asks for confirmation, then tags, pushes and runs `gh release create`. It refuses to run if the GitHub repo is not public |

Useful flags:
- `scripts/test-wp.sh --depuis v1.1.0` starts the rehearsal from another version.
- `--garder` keeps the site running after the test.
- On failure, the snapshot diff is in `.wp-test/differences.txt`.

### How the safety net works
- **`scripts/lib/contrat.php`** loads `inc/*.php` with stub WordPress functions. It does not need a database. It prints every name that links the theme to stored data:
  - post types and their slugs;
  - `_brev_*` meta keys;
  - `brev_*` theme mods;
  - options;
  - nav menu location;
  - rewrite rules and query vars;
  - Gare au Chacal setting, bot and card keys;
  - page templates.

  It also fails if a Customizer setting has no entry in `brev_defaults()`. If you add a new kind of persisted name, extend this script.
- **`scripts/test-wp.sh`** runs these steps in order:
  1. installs the tagged version;
  2. fills the site with `scripts/lib/wp-donnees.php`, which seeds "TEST" marker values;
  3. snapshots it with `scripts/lib/wp-instantane.php`, including field values as the meta-box form would read them;
  4. upgrades through the theme's own `brev_check_update()`, using a fake release server (`maj` service in `scripts/docker/compose.yml`);
  5. diffs the snapshots and crawls every public URL;
  6. greps `debug.log` for errors coming from the theme.

  If the version wasn't bumped, it force-installs instead and warns.

## Never break production data

A theme update replaces only the `brevinoise/` folder. Posts, meta, theme mods, options and menus stay in the database, and they are linked to the theme **by name**. If you rename or remove a post type, slug, `_brev_*` field, `brev_*` setting, option, bot or card key, menu location, page template or `/jouer/` route, that content is orphaned silently. It also gets wiped the next time the post is saved.

- Adding is always fine.
- Changing labels is fine, because only the array keys matter.
- To retire a field, keep it declared and stop displaying it.

`make compat` and `make test` enforce these rules. Never bypass them by moving tags.

The theme folder and slug must stay `brevinoise`, because theme mods are stored under `theme_mods_brevinoise`.

## Bump the version on every release

Use `make patch`, `make minor` or `make major`. Historically the version was sometimes not bumped, and `make release` now refuses that. `BREV_VERSION` is read from the `Version:` header in `style.css`. Three things depend on it:
- It is the cache-busting `ver` for every enqueued CSS and JS file.
- `brev_maybe_flush()` (`inc/setup.php`) flushes rewrite rules when the version changes. New CPT slugs or `/jouer/` routes only take effect after a bump.
- `brev_check_update()` (`inc/tools.php`) offers an auto-update when a remote JSON at `brev_update_url` has a higher `version` and a `package` URL.

## Publishing

Releases go to GitHub Releases on `romain-riviere/labrevinoisejeux`; override with the `DEPOT_GITHUB` environment variable.

- The production site's `brev_update_url` points to `…/releases/latest/download/brevinoise-update.json`. Every release must therefore attach `brevinoise-update.json` alongside `brevinoise-X.Y.Z.zip`.
- WordPress downloads those files anonymously, so the repo must be public.
- The manual fallback is to upload `dist/brevinoise.zip` and choose « Remplacer l'actuel ».

Pushing, tagging and publishing are outward-facing actions, so confirm with the user before running `make release`.

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
  - **`game.html` is a standalone HTML file.** Keep both placeholder strings exactly as they are, or WordPress injection breaks (`make lint` checks this). Outside WordPress it falls back to built-in defaults and localStorage.
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

## Design notes (from the `style.css` header)

- The colour palette is configurable in the Customizer. It is navy blue from the logo, white, and the meeple yellow.
- Fonts are Fredoka and Nunito.
- Layout: logo on a white background, a menu bar, full-width bands, and a dark footer.
- Tone: community-run, family-friendly, cheerful.
