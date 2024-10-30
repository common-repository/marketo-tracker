=== Marketo Tracker ===
Contributors: jakemgold, thinkoomph
Donate link: http://www.get10up.com/plugins/marketo-tracker-wordpress/
Tags: tracking, analytics, marketo, leads, sales, munchkin, CRM
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 2.5

Effortless integration with Marketo's Lead Management solution. Add the Munchkin tracking script to your site and associate comment fields with leads!


== Description ==

Marketo Tracker allows you to easily add the Marketo "Munchkin" tracking script to your site's footer, and can even create lead associations with name, email, and website when a visitor submits a comment. Simply enter the tracking code into the plug-in settings page, and fill out a few simple configuration options.

Comment field associations are not created if the comment is identified as spam.

Includes an easy to use configuration panel inside the WordPress settings menu:

1. Enter your unique Marketo tracking code.
1. Choose whether to track logged in users with a contributor or higher role.
1. Choose whether to associate comment fields with tracked leads.
1. Customize the "lead source" value when the lead is associated with comment fields.
1. Enter your secret API key for comment lead association. The key is hashed server side, and kept secure.


== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted
folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the "Marketo Tracker" menu item under "Settings". To find your tracking code
and secret API key, go to Munchkin Setup under Marketo Admin.


== Screenshots ==

1. Sceenshot of configuration page.


== Changelog ==

= 2.5 =
* Major clean up of code for performance, compatibility, minor bug fixes, and best WordPress practices
* Expanded plug-in help
* Cleaned up user interface for WordPress UI consistency and a better user experience

= 2.1.1 =
* Fix missing type attribute for tracker script tag, breaking XHTML validation

= 2.1 =
* Multiple options for lead source
* Fix post/page association for lead source

= 2.0.1 =
* Fix for 10 character limit on secret key

= 2.0 =
* Ability to create lead associations from comments