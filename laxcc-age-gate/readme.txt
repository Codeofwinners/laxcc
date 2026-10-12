=== LAXCC Age Gate ===
Contributors: laxcc
Tags: age gate, age verification, cannabis, dispensary, 21+
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern, fully customizable age verification overlay. Elementor friendly. Image or video backgrounds with separate desktop + mobile sources. Optional 4/20 celebration screen.

== Description ==

LAXCC Age Gate is a lightweight, no-bloat age verification gate built for cannabis and other 21+ sites. Designed to be Elementor-friendly and to never trigger Cloudflare/WAF rules during admin saves (zero inline HTML strings inside scripts).

= Features =

* **Image or video backgrounds** — separate uploads for desktop and mobile so dimensions stay sharp.
* **Modern admin UI** — clean tabbed control panel with toggle switches, color picker, drag-and-drop media uploaders, and live overlay preview.
* **Brand color picker** — set the primary CTA color from a real color picker.
* **Customizable copy** — headline, subtext, button labels, redirect URL.
* **Persistent verification** — stored in localStorage, sessionStorage, and cookies (configurable duration).
* **4/20 Celebration mode** — optional UFO + confetti + graffiti "Happy 4/20!!!" screen after verification, with date-range scheduling.
* **Custom CSS** — inject your own styles scoped to the gate.
* **Preview link** — open the gate on any front-end page with `?laxcc_ag_preview=1`.

== Installation ==

1. Upload the `laxcc-age-gate` folder to `/wp-content/plugins/`.
2. Activate the plugin through the WordPress Plugins screen.
3. Go to **Age Gate** in the admin sidebar to configure.

== Frequently Asked Questions ==

= Does the celebration always show? =

Only if you enable it. You can also restrict it to a date range (e.g. April 19–21).

= Can I use a video on mobile and an image on desktop? =

Background type is currently a single choice for both, but mobile/desktop sources upload separately within each type.

= Will this break Elementor? =

No. Assets load via standard `wp_enqueue_*` hooks and the gate is rendered into `body` via JS, outside of any Elementor container.

= How do I temporarily preview the gate after I've verified? =

Append `?laxcc_ag_preview=1` to any URL.

== Changelog ==

= 1.0.0 =
* Initial release.
