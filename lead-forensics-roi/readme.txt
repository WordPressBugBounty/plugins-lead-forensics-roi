=== Lead Forensics ===
Contributors: leadforensics
Tags: lead forensics, tracking, b2b, analytics, visitor tracking
Requires at least: 5.2
Tested up to: 6.5
Stable tag: 3.4.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds the Lead Forensics tracking script to your WordPress site — correctly, without async or defer.

== Description ==

The official Lead Forensics plugin for WordPress. It places your tracking code exactly where it needs to be:

* **Script tag** injected into `<head>` — synchronously, without `async` or `defer`, ensuring Lead Forensics and dependent tools like LF Chat load correctly.
* **Noscript tag** injected immediately after the opening `<body>` tag — the correct position per the HTML specification.

**Why does placement matter?**

Many WordPress performance plugins add `async` or `defer` to any script they find. This breaks Lead Forensics tracking and any tools that depend on the visitor data it provides. This plugin bypasses the WordPress enqueue system entirely, giving you reliable, consistent injection every time.

**Features**

* Separate fields for Script Tag and Noscript Tag
* Automatic stripping of `async` and `defer` attributes on save
* Live preview of exactly what will be injected
* Tracking status indicator — see at a glance whether your code is configured
* Direct link to your tracking code in the Lead Forensics portal

== Installation ==

1. Upload the `lead-forensics-roi` folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins** in WordPress admin
3. Go to **Settings → Lead Forensics**
4. Paste your Script Tag and Noscript Tag from the [Lead Forensics portal](https://portal.leadforensics.com/TrackingCode)
5. Click **Save Changes**

== Frequently Asked Questions ==

= Where do I find my tracking code? =

Log in to your [Lead Forensics portal](https://portal.leadforensics.com/TrackingCode) and navigate to Settings → Tracking Code.

= Why are there two separate fields? =

The script tag must go in `<head>` and the noscript tag must go immediately after `<body>`. Putting both in the head (as some plugins do) is invalid HTML and causes inconsistent behaviour across browsers.

= Will this work with caching or performance plugins? =

Yes. Because we bypass `wp_enqueue_script()` and echo directly into the relevant hooks, caching and optimisation plugins cannot modify or defer our output.

= My theme doesn't support wp_body_open — will the noscript tag still work? =

The noscript tag requires your theme to call `wp_body_open()`. All themes from WordPress 5.2 onwards support this. If your theme does not call it, the noscript tag will not render — however this will not break your tracking, as the noscript tag is only a fallback for visitors without JavaScript.

= Does the plugin collect any data? =

No. The plugin stores only the two tracking code snippets you paste in (in your own WordPress database). It does not collect, transmit, or store any user data itself.

== Screenshots ==

1. The settings page — paste your tracking codes and save.
2. Active status shown once tracking codes are configured.

== Changelog ==


= 3.4.0 =
* Separate script/noscript fields, correct head/body injection, async/defer stripping, redesigned settings page

= 2.0.3 =
* Added wp_kses sanitisation on frontend output
* Moved plugin_basename filter inside plugins_loaded hook
* Added rel="noopener noreferrer" to all external links
* Added direct link to Lead Forensics portal tracking code page
* Full i18n support

= 2.0.0 =
* Complete rewrite with separate Script Tag and Noscript Tag fields
* Correct injection positions (head / body open)
* Automatic async/defer stripping
* Redesigned settings page

== Upgrade Notice ==

= 2.0.3 =
Security and review improvements. Recommended update for all users.
