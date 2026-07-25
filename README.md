=== Simple Timeline Collections ===
Contributors: kylejhagel
Tags: timeline, events, shortcode, responsive, history
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.9.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create responsive, accessibility-conscious timeline collections from reusable event entries and display them with a shortcode.

== Description ==

Simple Timeline Collections lets site administrators create any number of separate Timelines. Each Timeline is a collection of Timeline Events and has its own display settings.

Developed by Kyle J. Hagel for MyPotsTracker.

Use this structure for separate departments, programs, campaigns, project histories, product roadmaps, organization milestones, or other timeline collections that should not share the same events or layout settings.

Each Timeline includes its own settings for:

* Live admin preview cards for Event Item Colors and selector mapping
* Timeline display title
* Timeline title alignment: left, center, or right
* Event alignment: alternate, left of center line, or right of center line
* Same-side Card Gap: optional desktop spacing control for the Alternate layout
* Event Item Before color: optional custom hex color for the default marker dot
* Event Item Current color: optional custom hex color for the current/last event marker
* Card Background color: optional custom hex background color for timeline event cards

Each Timeline Event includes:

* Event title
* Event date
* Event description
* Assigned Timeline
* Optional event call-to-action URL
* Optional event call-to-action text
* Optional new-tab behavior


Timeline text and heading elements are intentionally designed to inherit the active theme's global typography first. The plugin controls timeline layout, rail/marker visuals, and alignment, while the theme remains the default source for heading sizes, text color, font family, line height, and typography rhythm.

Simple Timeline Collections is designed with accessibility-conscious output, including semantic ordered-list markup, time elements with datetime values, keyboard-friendly CTA links, reduced-motion support, and high-contrast/forced-colors considerations. Compact Alternate layouts use progressive enhancement to preserve the chronological ordered list while reducing visual gaps and drawing a measured parent rail. Event markers use the same CSS-defined vertical offset for every event. It does not add public powered-by links, load third-party frontend assets, or collect visitor data.

== Installation ==

1. Upload the plugin ZIP through Plugins > Add New > Upload Plugin.
2. Activate Simple Timeline Collections.
3. Go to Simple Timeline Collections > All Timelines and create a Timeline.
4. Configure the Timeline display title, title alignment, event alignment, event item colors, and card background.
5. Go to Simple Timeline Collections > Add Event and assign the event to a Timeline.
6. Add the Timeline shortcode to a page or post.

== Shortcode ==

Display the default Timeline:

`[simple_timeline_collections]`

Display a specific Timeline by ID:

`[simple_timeline_collections id="123"]`

Display a specific Timeline by slug:

`[simple_timeline_collections timeline="program-history"]`

Optional overrides:

`[simple_timeline_collections id="123" heading="Project History" heading_align="center" layout="alternate" alternate_card_gap="100" rail_color="#b5c2ce"]`

Override event item colors and card background with custom hex values:

`[simple_timeline_collections timeline="program-history" event_item_before="#007c89" event_item_current="#1a545b" rail_color="#b5c2ce" card_background="#ffffff"]`

Supported heading_align values:

* left
* center
* right

Supported layout values:

* alternate
* left
* right

Supported alternate_card_gap values:

* Any whole number from 0 to 300, representing pixels.
* This setting applies to desktop Alternate layouts only. Small screens use the normal single-column timeline spacing.

Additional shortcode attributes:

* id: Timeline post ID.
* timeline: Timeline slug or ID.
* heading: optional display heading override.
* heading_level: number from 2 through 6. Default: 2.
* heading_align: left, center, or right.
* layout: alternate, left, or right.
* event_item_before: optional custom 3- or 6-digit hex color for the default `.stc-timeline__item::before` marker dot, including border, fill, and halo.
* event_item_current: optional custom 3- or 6-digit hex color for the `.stc-timeline__item.is-current::before` marker used for the current/last timeline item.
* card_background: optional custom 3- or 6-digit hex background color for `.stc-timeline__card`.
* limit: number of events. Default: -1.
* order: ASC or DESC. Default: ASC.
* show_heading: true or false. Default: true.
* class: optional custom wrapper class.

== Privacy ==

Simple Timeline Collections does not collect, store, or transmit visitor analytics, telemetry, or personal data. The plugin does not load third-party frontend assets or external services. Timeline content, settings, and event metadata are stored in the local WordPress database. Frontend editorial links are shown only to users with appropriate WordPress permissions.

== Frequently Asked Questions ==

= Can I create more than one timeline? =

Yes. Go to Simple Timeline Collections > All Timelines and create as many Timelines as needed. Each Timeline has its own settings and collection of events.

= Can each department or program have its own timeline? =

Yes. Create one Timeline per department, program, campaign, or organizational unit, then assign Timeline Events to the correct Timeline.

= How do I add an event to a specific timeline? =

When editing a Timeline Event, choose the Timeline from the Timeline dropdown in the Timeline Event Details panel. You can also open a Timeline and use the Add Event to This Timeline button.

= Does Simple Timeline Collections load external CSS or JavaScript? =

No. Frontend CSS and JavaScript are included locally in the plugin package.

= Does Simple Timeline Collections add a public credit or powered-by link? =

No. Simple Timeline Collections does not add a public powered-by link.

= Does Simple Timeline Collections track visitors? =

No. Simple Timeline Collections does not include analytics, telemetry, or visitor tracking.

= Can I change the timeline title? =

Yes. Each Timeline has its own Timeline Display Title setting. You can also override the heading per shortcode with the heading attribute.

= Can I alternate events left and right of the center line? =

Yes. Edit the Timeline and choose the alternate event alignment option, or use layout="alternate" in the shortcode.

== Developer Notes ==

SCSS source files are included in assets/scss. WordPress loads the compiled CSS from assets/css.

Ownership: developed by Kyle J. Hagel for MyPotsTracker.

To rebuild CSS during development:

`npm install`

`npm run build:css`

== Changelog ==

Full release notes are maintained in CHANGELOG.md. Each minor and major release must include a dated changelog entry before packaging.

= 1.9.4 =
* Added Rail Color controls to global defaults and individual timeline settings.
* Added the `rail_color` shortcode override.
* Updated frontend rail rendering to use the `--stc-rail-color` custom property.

= 1.9.3 =
* Updated left and right layouts to fill the available container width.
* Moved the rail to the outside edge for single-side layouts.
* Updated single-side cards to stretch across the available content lane.

= 1.9.2 =
* Prepared WordPress.org submission metadata to match the MyPotsTracker author structure.
* Added Author URI metadata and ownership notes.
* Added a dedicated Privacy section.
* Corrected the readme description to remove the removed dynamic interior marker centering behavior.
* Tightened timeline-event POST input unslashing before sanitization.

= 1.9.1 =
* Changed the event actions trigger to a vertical ellipsis icon.
* Scoped event actions menu padding so theme list styles do not add extra left padding.

= 1.9.0 =
* Added Editor/Admin-only frontend event action dropdowns on each timeline event card.
* Added Edit and Delete actions using WordPress native edit and trash links.
* Added keyboard support and accessible menu attributes for the event actions dropdown.

= 1.8.0 =
* Added a frontend Add Event to This Timeline button below rendered timelines for logged-in Editors and Administrators.
* The button opens the existing Add Timeline Event screen with the current timeline preselected.

= 1.7.4 =
* Removed dynamic interior marker centering.
* Timeline markers now keep the same CSS-defined vertical offset for every event.
* Kept compact Alternate same-side spacing and measured parent rail behavior.

= 1.7.3 =
* Updated the compact Alternate parent rail to use the measured marker center axis.
* Added `--stc-parent-rail-x` so the parent rail aligns horizontally with event markers.

= 1.7.2 =
* Added a measured parent-level rail for compact Alternate timelines after frontend enhancement runs.
* Disabled per-item rail segments in enhanced compact Alternate layouts to prevent overlapping dashed lines.
* Added rail start/end calculations based on first and last marker positions.

= 1.7.1 =
* Centered only interior event markers to the vertical middle of their associated cards.
* Kept first and last markers anchored to the default rail start/end position.
* Updated frontend layout recalculation while preserving the CSS fallback when JavaScript is unavailable.

= 1.7.0 =
* Added Same-side Card Gap settings for desktop Alternate timeline layouts.
* Added `alternate_card_gap` shortcode support.
* Added progressive frontend JavaScript to compact alternating timelines while preserving chronological ordered-list markup and reading order.

= 1.6.2 =
* Added admin preview cards for Event Item Colors with live color swatches for Event Item Before, Event Item Current, and Card Background.
* Added a rendered selector map preview card to visualize the default marker, event card, and current marker selectors.
* Added a lightweight admin script so preview swatches and selector-map visuals update while editing timeline settings.

= 1.6.1 =
* Updated the plugin author display name to MyPotsTracker for public WordPress plugin listings.

= 1.6.0 =
* Renamed the plugin to Simple Timeline Collections for WordPress.org submission readiness.
* Updated the package slug, text domain, main plugin file, class prefix, function prefix, post types, meta keys, options, shortcode, asset handles, file names, CSS classes, and CSS custom properties to use the STC naming system.
* Added the `[simple_timeline_collections]` shortcode as the clean public shortcode.

= 1.5.2 =
* Fixed Plugin Check sanitization warnings for timeline color fields.
* Added translator context for CTA aria-label placeholder text.
* Shortened the WordPress.org readme short description.

= 1.5.1 =
* Refined frontend marker styling so default event markers use the selected color for border, fill, and halo.
* Factored timeline wrapper CSS custom properties into the SCSS token layer through a reusable mixin.
* Cleaned up CTA spacing and high-contrast marker handling.
* Updated internal asset versioning and admin helper text for marker color behavior.

= 1.5.0 =

* Added Card Background custom hex color setting below Event Item Current in the Event Item Colors section.
* Added the `--stc-card-background-color` CSS custom property for `<article class="stc-timeline__card">`.
* Added optional `card_background` shortcode override.

= 1.4.2 =
* Removed deprecated marker color shortcode compatibility and legacy fallback code.
* Kept event item color controls focused on Event Item Before and Event Item Current custom hex values.

= 1.4.1 =
* Replaced global palette marker color selection with simple custom hex fields.
* Added Event Item Before and Event Item Current controls below Event Alignment.
* Updated marker styles to use separate CSS custom properties for default and current/last markers.

= 1.4.0 =
* Added per-timeline marker color controls below Event Alignment.
* Added experimental WordPress global palette color selection and custom hex marker colors.
* Updated the timeline marker pseudo-element to use a timeline-specific CSS custom property.

= 1.3.1 =
* Refined frontend styles so timeline headings, event titles, event dates, and event descriptions inherit the active theme typography by default.
* Removed plugin-level font size, font weight, letter spacing, line height, and text color overrides from timeline text elements.
* Preserved layout-only styling for timeline alignment, rail/marker visuals, responsive behavior, focus states, reduced-motion support, and forced-colors support.

= 1.2.3 =
* Standardized the plugin package directory to simple-timeline-collections for long-term update stability and WordPress.org readiness.
* Added a one-time legacy deactivation helper for early simple-timeline-collections-v1 ZIP installs.

= 1.2.2 =
* Added update-safe packaging for existing pre-directory installs that use the simple-timeline-collections-v1 plugin folder.
* Preserved GPLv2-or-later metadata and WordPress.org submission preparation updates.

= 1.2.1 =
* Prepared WordPress.org submission release.
* Changed license metadata to GPLv2 or later.
* Removed placeholder Plugin URI from the plugin header.
* Updated readme metadata, privacy notes, source/build notes, and stable tag.
* Renamed package root folder to simple-timeline-collections for WordPress.org slug readiness.

= 1.2.0 =
* Added Timeline Title Alignment setting: left, center, right.
* Added heading_align shortcode override.
* Updated title styles to use flex-based alignment.

= 1.1.1 =
* Added standalone CHANGELOG.md.
* Added changelog maintenance policy for future minor and major updates.
* Updated plugin metadata and package version to 1.1.1.

= 1.1.0 =
* Added Simple Timeline Collections Settings screen.
* Added default timeline title option.
* Added event alignment option: alternate, left, right.
* Updated frontend layout CSS to support centered rail and side-based cards.
* Preserved exact rail start/end behavior at first and last marker dots.

= 1.0.1 =
* Added SCSS source workflow.

= 1.0.0 =
* Initial plugin version.