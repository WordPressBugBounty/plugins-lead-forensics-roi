=== Lead Forensics ===
Contributors: leadforensics
Tags: lead forensics, tracking, b2b, analytics, visitor tracking
Requires at least: 5.2
Tested up to: 7.0
Stable tag: 3.6.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily add your Lead Forensics tracking code to your WordPress site.

== Description ==

The official Lead Forensics plugin for WordPress. Paste your tracking code into the settings page and the plugin handles placement automatically.

* Tracking code injected into the head of every page
* No async or defer added to the script
* Simple single field entry matching the tracking code format from the Lead Forensics portal

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/
2. Activate the plugin via Plugins in WordPress admin
3. Go to Settings > Lead Forensics
4. Paste your tracking code from the Lead Forensics portal
5. Click Save Changes

== Frequently Asked Questions ==

= Where do I find my tracking code? =

Log in to your Lead Forensics portal and navigate to Settings > Tracking Code.

= Does the plugin collect any data? =

No. The plugin stores only the tracking code snippet you paste in, in your own WordPress database. It does not collect, transmit, or store any user data itself.

== Changelog ==

= 3.6.0 =
* Restored single field tracking code entry for improved compatibility
* Added automatic migration for users upgrading from all previous versions

= 3.5.4 =
* Fixed script tag output to prevent URL rendering as plain text on some themes
* Normalised whitespace in script tags at save and migration time
* Changed wp_head priority to fire after page title and SEO meta tags

= 3.5.3 =
* Fixed migration split to correctly separate script and noscript tags
* Added validation to prevent noscript tag being saved in script field and vice versa
* Improved hint text and placeholders to make field requirements clearer

= 3.5.2 =
* Fixed migration to correctly read tracking code from previous plugin version

= 3.5.1 =
* Migration now runs on any version below 3.5.1 to catch users who updated to 3.4.0 early

= 3.5.0 =
* Automatic migration of tracking code from previous plugin version on activation

= 3.4.0 =
* Separate script and noscript fields, correct head and body injection, async and defer stripping, redesigned settings page

= 2.0.3 =
* Security and review improvements

= 2.0.0 =
* Complete rewrite with separate Script Tag and Noscript Tag fields

== Upgrade Notice ==

= 3.6.0 =
* Recommended update for all users. Includes automatic migration for all previous versions.

= 3.6.1 =
* Minor version update
