# Changelog

## [1.9.4] - 2026-07-23

### Added
- Added Rail Color controls to global defaults and individual timeline settings.
- Added admin preview support for the rail color swatch and selector map.
- Added the `rail_color` shortcode override.

### Changed
- Updated frontend rail rendering to use the `--stc-rail-color` custom property instead of relying only on the theme border color.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.9.4`.

## [1.9.3] - 2026-07-23

### Changed
- Updated left and right event alignment layouts so the timeline wrapper fills the available block/container width.
- Moved the timeline rail to the outside edge for single-side layouts: right layouts use a left-side rail and left layouts use a right-side rail.
- Updated left/right timeline cards to stretch across the available content lane instead of using the alternate layout's half-width columns.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.9.3`.

## [1.9.2] - 2026-07-23

### Changed
- Prepared WordPress.org submission metadata to match the MyPotsTracker author structure used for Simple Page Head Injector.
- Added `Author URI: https://mypotstracker.com` to the plugin header.
- Updated readme tags, ownership notes, and privacy notes for directory readiness.
- Corrected the readme description to remove the removed dynamic interior marker centering behavior.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.9.2`.

### Fixed
- Tightened timeline-event POST input unslashing before sanitization.

## [1.9.1] - 2026-07-18

### Changed
- Changed the event actions trigger from a horizontal ellipsis to a vertical ellipsis icon.
- Scoped event actions menu list spacing more specifically so theme selectors such as `.single-content ul` do not add inherited left padding.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.9.1`.

## [1.9.0] - 2026-07-18

### Added
- Added Editor/Admin-only frontend event action dropdowns on timeline event cards.
- Added accessible ellipsis action buttons inline with each event date.
- Added Edit and Delete menu actions using WordPress native edit and trash links.
- Added keyboard support for opening, closing, and navigating event action menus.

### Changed
- Added a delete confirmation prompt before following the WordPress trash URL.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.9.0`.

## [1.8.0] - 2026-07-18

### Added
- Added a frontend-only editorial Add Event to This Timeline button below rendered timelines.
- The button is visible only to logged-in users with Editor/Admin-level publishing permissions.
- The button links to the existing Add Timeline Event screen with the current timeline preselected.

### Changed
- Reused the existing timeline preselection workflow used by the Timeline edit-screen Add Event button.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.8.0`.

## [1.7.4] - 2026-07-18

### Removed
- Removed the dynamic interior marker centering feature entirely.

### Changed
- Timeline markers now keep the same CSS-defined vertical offset for every event.
- Kept compact Alternate same-side spacing and measured parent rail behavior.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.7.4`.

## [1.7.3] - 2026-07-18

### Changed
- Updated the compact Alternate parent rail to use a measured horizontal marker axis instead of assuming `50%`.
- The frontend script now sets `--stc-parent-rail-x` from the rendered marker center so the parent rail aligns with event markers.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.7.3`.

## [1.7.2] - 2026-07-18

### Added
- Added a measured parent-level rail for compact Alternate timelines after the frontend layout script runs.
- Added frontend rail start/end calculations based on the first and last marker positions.

### Changed
- Compact Alternate timelines now disable per-item rail segments after enhancement to prevent overlapping dashed-line segments.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.7.2`.

## [1.7.1] - 2026-07-18

### Changed
- Refined frontend timeline marker alignment so only interior event markers are centered to the vertical middle of their associated event cards.
- Kept the first and last event markers anchored to the default rail start/end position.
- Updated the frontend layout recalculation script to preserve marker fallback behavior when JavaScript is unavailable.
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.7.1`.

## [1.7.0] - 2026-07-18

### Added
- Added a Same-side Card Gap setting for desktop Alternate timeline layouts.
- Added per-timeline, global default, and shortcode support for `alternate_card_gap`.
- Added progressive frontend JavaScript to compact alternating timelines while preserving the chronological ordered-list markup and reading order.
- Added timeline side data attributes for rendered items to support same-side spacing calculations.

### Changed
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.7.0`.

## [1.6.2] - 2026-07-18

### Added
- Added admin preview cards for Event Item Colors with live color swatches for Event Item Before, Event Item Current, and Card Background.
- Added a rendered selector map preview card to visualize the default marker, event card, and current marker selectors.
- Added an admin-only JavaScript enhancement so preview swatches and selector-map visuals update as color values are edited.

### Changed
- Updated plugin version, readme stable tag, package metadata, and asset comments to `1.6.2`.

## [1.6.1] - 2026-07-16

### Changed
- Updated the plugin author display name to MyPotsTracker for public WordPress plugin listings.
- Updated plugin header, internal version constant, readme stable tag, package metadata, SCSS/CSS comments, and JavaScript comment to `1.6.1`.

## [1.6.0] - 2026-07-09

### Changed
- Renamed the plugin to Simple Timeline Collections for WordPress.org submission readiness.
- Renamed the package slug, text domain, main plugin file, class name, function prefix, post types, meta keys, option names, admin field prefixes, shortcode, asset handles, asset filenames, CSS classes, and CSS custom properties to use the Simple Timeline Collections / STC naming system.
- Updated public-facing admin labels, readme content, package metadata, asset comments, and documentation references.

### Developer
- Added the clean public shortcode `[simple_timeline_collections]`.
- Removed the pre-submission legacy slug deactivation helper because the plugin is not in production.
- Updated plugin version, readme stable tag, package metadata, SCSS/CSS comments, and JavaScript comment to `1.6.0`.

## [1.5.2] - 2026-07-09

### Fixed
- Explicitly sanitized timeline color POST values before normalization to satisfy WordPress Plugin Check.
- Added translator context for CTA aria-label placeholder text.
- Shortened the WordPress.org readme short description to stay within the directory limit.

### Changed
- Updated plugin metadata, package metadata, and asset comments to `1.5.2`.

## [1.5.1] - 2026-07-09

### Changed
- Refined default event marker styling so the Event Item Before color controls marker border, fill, and halo.
- Added safe fallback declarations around `color-mix()` usage for marker halos and card backgrounds.
- Factored timeline wrapper CSS custom property setup into a reusable SCSS mixin in `_tokens.scss`.
- Simplified CTA wrapper spacing and improved forced-colors handling for marker shadows.
- Updated admin helper text to better describe marker color behavior.

### Developer
- Updated plugin header, internal `STC_Simple_Timeline_Collections::VERSION`, asset comments, readme stable tag, and package metadata to `1.5.1`.

## [1.5.0] - 2026-07-09

### Added
- Added a **Card Background** custom hex color setting below **Event Item Current** in the Event Item Colors section.
- Added `--stc-card-background-color` for `<article class="stc-timeline__card">`.
- Added optional shortcode override: `card_background="#ffffff"`.

### Changed
- Event Item Colors now controls the default marker, current/last marker, and event card background using the same custom hex input pattern.
## [1.4.2] - 2026-07-09

- Removed deprecated `marker_hex` shortcode compatibility and legacy marker color fallback code.
- Removed v1.4.0 marker color metadata constants and migration fallback logic.
- Kept timeline event item color settings intentionally simple with only custom hex inputs for Event Item Before and Event Item Current.

## [1.4.1] - 2026-07-09

- Replaced WordPress global palette marker color selection with custom hex-only color controls.
- Added **Event Item Before** and **Event Item Current** color fields below Event Alignment on timeline settings.
- Updated frontend CSS to use `--stc-event-item-before-color` for `.stc-timeline__item::before` and `--stc-event-item-current-color` for `.stc-timeline__item.is-current::before`.

## [1.4.0] - 2026-07-06

### Added
- Added per-timeline marker color settings below Event Alignment.
- Added support for selecting a WordPress global palette color for the timeline marker dot.
- Added support for entering a custom 3- or 6-digit hex color for the timeline marker dot.
- Added optional shortcode overrides: `marker_color="primary"` and `marker_hex="#1a545b"`.

### Changed
- Updated `.stc-timeline__item::before` to use the `--stc-marker-color` CSS custom property while preserving theme defaults.
- Current/latest timeline markers now use the selected marker color for their filled state.

### Developer
- Updated plugin version, readme stable tag, package metadata, SCSS/CSS comments, and documentation to `1.4.0`.

## [1.3.1] - 2026-07-06

### Changed
- Refined frontend SCSS/CSS so timeline headings, event titles, event dates, and event descriptions inherit the active theme's global typography by default.
- Removed plugin-level font size, font weight, letter spacing, line height, and text color overrides from timeline text elements.
- Preserved layout-only styling for timeline title alignment, card placement, rail/marker positioning, responsive behavior, focus indicators, reduced-motion support, and forced-colors support.

### Developer
- Updated plugin version, readme stable tag, package metadata, SCSS/CSS comments, and documentation to `1.3.1`.

## [1.3.0] - 2026-07-06

### Added
- Added a new `stc_timeline` custom post type so admins can create any number of separate timeline collections.
- Added per-timeline settings for display title, title alignment, and event alignment.
- Added event-to-timeline assignment through the Timeline Event Details form.
- Added timeline-specific shortcodes by ID, for example `[simple_timeline_collections id="123"]`.
- Added timeline-specific shortcodes by slug, for example `[simple_timeline_collections timeline="program-history"]`.
- Added Timeline list-table columns for shortcode, layout, and event count.
- Added Timeline Event list-table filtering by Timeline.
- Added a Timeline edit-screen helper panel with shortcode examples, recent events, and an Add Event to This Timeline shortcut.

### Changed
- Moved the primary admin hierarchy toward Timelines as the parent collection and Timeline Events as child entries.
- Updated the global Settings screen to function as fallback/default behavior rather than the only timeline configuration source.
- Updated the default `[simple_timeline_collections]` shortcode to use the configured default Timeline when no ID or slug is provided.

### Migration
- Added an upgrade routine that creates a default `Brief History` Timeline for existing installs.
- Existing unassigned `stc_event` posts are assigned to the default Timeline so existing content is preserved.
- Existing global title/alignment settings are copied into the default Timeline settings where possible.

### Developer
- Updated plugin version, readme stable tag, package metadata, SCSS/CSS comments, and documentation to `1.3.0`.

## [1.2.3] - 2026-07-06

### Fixed
- Standardized the plugin package directory to `simple-timeline-collections` so the plugin slug no longer includes a version number.
- Added a one-time legacy deactivation helper for sites that still have the early `simple-timeline-collections-v1` ZIP build active.
- Preserves existing `stc_event` timeline posts, event metadata, and `stc_simple_timeline_collections_options` settings during the slug normalization path.

### Notes
- Future release ZIPs should keep the root plugin directory as `simple-timeline-collections`.
- The ZIP filename may include a version number, but the folder inside the ZIP should not.

## [1.2.2] - 2026-07-06

### Fixed
- Added an update-safe release package that preserves the original `simple-timeline-collections-v1` plugin directory used by pre-directory builds.
- Prevents WordPress from installing the WordPress.org slug package side-by-side as a second plugin instance during manual upload updates.

### Notes
- Use this update-safe package for existing local/staging installs created from earlier ZIPs.
- Use the `simple-timeline-collections` directory package only for the future WordPress.org SVN/directory submission path.

All notable changes to Simple Timeline Collections will be documented in this file.

This project uses a practical Semantic Versioning-style format:

- **Major updates** change the product direction, data model, shortcode contract, or introduce breaking changes.
- **Minor updates** add new user-facing features, settings, layouts, admin tools, or shortcode options without breaking existing installs.
- **Patch updates** fix bugs, improve accessibility, update documentation, refine styles, or adjust build/package details.

## Changelog Policy

Before packaging any new **minor** or **major** plugin update, add a dated entry to this file. Each entry should summarize what changed under clear headings such as **Added**, **Changed**, **Fixed**, **Accessibility**, **Developer**, or **Documentation**.

Patch updates should also be logged when they affect user-facing behavior, accessibility, packaging, documentation, or release process.

## [1.2.1] - 2026-07-06

### Changed
- Corrected WordPress.org contributor username metadata to `kylejhagel` and updated the author display metadata during pre-submission cleanup.

- Prepared the plugin package for WordPress.org submission review.
- Changed plugin and readme license metadata from GPLv3-or-later to **GPLv2 or later** to align with the recommended WordPress plugin license.
- Removed the placeholder `Plugin URI` value from the plugin header.
- Updated the WordPress.org readme metadata, stable tag, privacy notes, source/build notes, and FAQ content.
- Renamed the packaged plugin root folder from `simple-timeline-collections-v1` to `simple-timeline-collections` for WordPress.org slug readiness.

### Developer

- Updated plugin metadata, compiled asset comments, SCSS comments, and `package.json` version to `1.2.1`.
- Added `GPL-2.0-or-later` license metadata to `package.json`.

## [1.2.0] - 2026-07-06

### Added

- Added a global **Timeline Title Alignment** setting with left, center, and right options.
- Added `heading_align` shortcode override support, for example `[simple_timeline_collections heading_align="center"]`.

### Changed

- Updated frontend output classes to include the selected title alignment.
- Updated heading styles to use flex-based alignment via `justify-content` with matching text alignment fallbacks.

### Developer

- Updated plugin metadata and `package.json` version to `1.2.0`.

## [1.1.1] - 2026-07-06

### Documentation

- Added this standalone `CHANGELOG.md` file.
- Added a changelog maintenance policy for future minor and major updates.
- Updated `readme.txt` to point to the standalone changelog.

### Developer

- Updated plugin metadata and `package.json` version to `1.1.1`.

## [1.1.0] - 2026-07-06

### Added

- Added **Simple Timeline Collections > Settings** admin page.
- Added a global **Timeline Title** setting with `Brief History` as the default value.
- Added a global **Event Alignment** setting with three layout options:
  - Alternate left and right of the center line.
  - All events left of the center line.
  - All events right of the center line.
- Added shortcode layout override support, for example `[simple_timeline_collections layout="alternate"]`.

### Changed

- Updated shortcode output to use saved settings by default.
- Updated frontend classes to support global and shortcode-controlled layout modes.
- Updated frontend SCSS and compiled CSS to support centered, side-based timeline cards.

### Accessibility

- Preserved semantic ordered-list timeline markup.
- Preserved exact dashed-rail start and end behavior at the first and last marker dots.
- Preserved responsive behavior for smaller screens.

## [1.0.1] - 2026-07-06

### Added

- Added SCSS source files for maintainable frontend and admin styling.
- Added `package.json` with `npm run watch:css` and `npm run build:css` scripts.
- Added compiled CSS files for WordPress enqueueing.

### Changed

- Moved styling workflow from direct CSS editing to SCSS-first development.

## [1.0.0] - 2026-07-06

### Added

- Initial Simple Timeline Collections plugin release.
- Added `Timeline Events` custom post type.
- Added event fields for title, date, description, optional CTA URL, optional CTA text, and optional new-tab behavior.
- Added `[simple_timeline_collections]` shortcode.
- Added responsive frontend timeline layout.
- Added theme-aware color variables using WordPress preset color fallbacks.
- Added accessible semantic markup using ordered lists and `<time datetime="">` output.
