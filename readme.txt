=== Simple Notification Bar ===
Contributors:      yourname
Tags:              notification bar, announcement bar, top bar, alert bar
Requires at least: 6.0
Tested up to:      6.7
Requires PHP:      8.0
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Displays a configurable announcement bar at the top of every frontend page.

== Description ==

Simple Notification Bar lets you show a full-width announcement bar fixed at
the top of every page on your WordPress site. Visitors can dismiss the bar with
a single click.

**Features:**

* Fixed-position bar displayed above all page content.
* Announcement text set from the WordPress admin.
* Background color chosen via a native color picker.
* Visibility toggle — enable or disable the bar instantly.
* Bar and all assets load only when visibility is enabled.
* Fully accessible: ARIA roles, live regions, and keyboard-friendly dismiss button.
* Compatible with the WordPress Admin Bar (logged-in users).
* Translation-ready (i18n).
* No jQuery dependency — vanilla JavaScript only.
* No external libraries or CDN calls — 100% self-hosted.

== Installation ==

= Automatic (recommended) =
1. In your WordPress admin, go to Plugins → Add New.
2. Search for "Simple Notification Bar".
3. Click Install Now, then Activate.

= Manual =
1. Download the plugin zip file.
2. In your WordPress admin, go to Plugins → Add New → Upload Plugin.
3. Choose the zip file and click Install Now.
4. Click Activate Plugin.

= Via FTP =
1. Unzip the plugin archive.
2. Upload the `wp-simple-notification-bar` folder to `/wp-content/plugins/`.
3. In your WordPress admin, go to Plugins and activate "Simple Notification Bar".

== Configuration ==

1. After activation, go to **Settings → Notification Bar** in your WordPress admin.
2. Enter your **Announcement Text** — leave this blank to hide the bar regardless
   of other settings.
3. Choose a **Background Color** using the color picker.
4. Tick **Show Notification Bar** to make it visible on the frontend.
5. Click **Save Settings**.

== Frequently Asked Questions ==

= The bar is not showing on the frontend. =

Check both of these:
1. The **Show Notification Bar** checkbox is ticked and saved.
2. The **Announcement Text** field is not empty.

Both conditions must be true for the bar to render.

= The bar overlaps my theme header. =

Your theme's header is likely also using `position: fixed` or `position: sticky`.
Add the following to your theme's Additional CSS (Appearance → Customize → Additional CSS),
adjusting the pixel value to match your header's height:

  .site-header { margin-top: 46px; }

= The bar does not appear on my custom theme. =

The bar is injected via the `wp_body_open` WordPress hook. Your theme's header.php
must call `wp_body_open()` immediately after the opening `<body>` tag:

  <body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

All themes following WordPress standards since version 5.2 already do this.

= Can visitors permanently dismiss the bar? =

The current version hides the bar for the duration of the browser session only.
The bar reappears on the next visit. Session/cookie persistence may be added in
a future version.

= Is the plugin translation-ready? =

Yes. The text domain is `simple-notification-bar`. Place your .po/.mo translation
files in the plugin's `/languages/` directory.

== Screenshots ==

1. Frontend — notification bar displayed at the top of the page.
2. Admin — Settings → Notification Bar settings page.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release — no upgrade steps required.
