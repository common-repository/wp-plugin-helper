=== WP Plugin Helper ===
Contributors: grrega
Donate link: https://grrega.com/plugins/free/wp-plugin-helper
Tags: plugins, plugin notes, memo, meta, notes, note, admin note, compatibility, wordpress, woocommerce, php
Requires at least: 3.3.0
Tested up to: 4.9.8
Requires PHP: 5.4
Stable tag: 1.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Add notes to plugins on the plugin page and display WordPress, WooCommerce and PHP compatibility.

== Description ==

<h4>WP Plugin Helper</h4>
<p><em>Stay in control of your plugins.</em></p>
<br/>
<strong>Notes</strong>
<p>The Plugins page in WordPress dashboard can get crowded over time.</p>
<p>This plugin enables you to add notes to any plugin (active or disabled) to help you keep track of all the different plugins that you gather over the years.</p>
<p>Note editor uses the default WordPress editor so you are probably familiar with the interface and you can change the background color to indicate how important the note is.</p>
<br/>
<strong>Compatibility</strong>
<p>This plugin also adds a new column to the plugins table, showing compatibility for each plugin.</p>
<p>It checks for WordPress, WooCommerce and PHP requirements.</p>
<p>Compatibility table is color coded so you instantly know the status of your plugins.</p>
<p>You can get more information about the compatibility status by hovering over a colored table column with your mouse.</p>

<h4>Features</h4>
<ul>
	<li>Compatibility table showing WordPress, WooCommerce and PHP requirements</li>
	<li>Add notes to plugins to keep track</li>
	<li>Change notes background color</li>
	<li>Responsive</li>
	<li>Lighweight</li>
</ul>

<h4>Support</h4>
<p>You can contact me by <a href="https://grrega.com/contact">Grrega.com contact form</a> if you have any questions or need support.</p>
<p>You can also use the support forum for this plugin at wordpress.org</p>

== Installation ==

SERVER REQUIREMENTS

1. PHP version 5.4 or greater (PHP 7.2 or greater is recommended)
2. MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)


AUTOMATIC INSTALLATION

1. Log in to your WordPress dashboard, navigate to the Plugins menu and click Add New
2. In the search field type "WP Plugin Helper" and click Search Plugins
3. Install the plugin by simply clicking "Install Now"

MANUAL INSTALLATION

The manual installation method involves downloading the WP Plugin Helper plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains instructions on how to do this <a href="https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation">here</a>.

== FAQ ==

= "Tested up to" version is different than the one on wordpress.org =

Some plugin developers do not include a full version number in the readme.txt file of their plugin. WP Plugin Helper gets that information straight from the readme.txt file and not from wordpress.org website. If the difference is only in the last number of the version (for example 4.9 on local site and 4.9.6 on wordpress.org) there should be no reason to worry as the change should be backwards compatible. For more information about semantic versioning check this link.

== Screenshots ==

1. Plugins page
2. Plugin notes form
3. Compatibility table


== Changelog ==

= 1.1.1 =
* FIX: Fixed count() warning when getting empty notes instead of an array
* FIX: Break loop when description starts when getting plugin data from readme.txt as we only need the header
* FIX: Translations update

= 1.1.0 =
* ADD: A custom version_compare function to check if only the last part of the version (PATCH) is different, but MAJOR and MINOR versions are compatible (eg: 4.9 vs 4.9.6)
* ADD: A new compatibility status color and title (for different PATCH versions)
* ADD: Security - _wpnonce check
* FIX: PHP warning if plugin readme file is not found
* FIX: WooCommerce "Tested up to" compatibility wrong status if lower version 
* FIX: Compatibility wrong status if first version has PATCH version and it is "0", but the second one doesn't (eg: 3.4.0 vs 3.4)
* TWEAK: Minor reponsive tweaks
