=== Laybuy Payment Extension for WooCommerce  ===
Contributors: laybuy, 16hands
Tags: woocommerce, payment-gateway
Requires at least: 4.6
Tested up to: 5.1.1
Stable tag: 4.6
Requires PHP: 5.6.32
License: Apache License
License URI: https://www.apache.org/licenses/LICENSE-2.0.html

Laybuy WooCommerce Gateway Plugin

== Description ==

This extension allows you to integrate your WooCommerce store platform with the https://laybuy.com payment system

= REQUIREMENTS =

* PHP version 5.6 or greater (PHP 7.1+ is recommended)
* MySQL version 5.5 or greater (MySQL 5.6+ is recommended)
* WooCommerce 3.3+ / Requires WordPress 4.5+


== Installation ==

1. Use the github's download feature to download a zip of the plugin (Clone or Download -> Download ZIP) to the `/wp-content/plugins/laybuy-woocommerce` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Browse to Admin -> Wocommerce -> Settings -> Checkout -> Laybuy, here you can set your Laybuy Merchant details and choose to display the product price breakdown. The breakdown is displayed with Woocommerce's Product actions, there is a link in the Description to show you where these will display.

== Changelog ==
= 3.4.0 =
* extend woocommerce-multicurrency support to allow multiple currencies available at the same time
= 3.3.11 =
* woocommerce-multicurrency support fixes
= 3.3.9 =
* added support font sizes in product page breakdowns
= 3.3.8 =
* added support for woocommerce-multicurrency
= 3.3.7 =
* version number issue
= 3.3.6 =
* Submit to Wordpress.org repo
= 3.3.5 =
* Updates for Wordpress.org repo
= 3.3.2 =
* Add setting to show or hide the Currencey code in product and checkout breakdowns
= 3.3.1 =
* Handle when woocommerce is set to optional phone number
= 3.3.0 =
* updated tax handling for UK to match API
= 3.2.8 =
* stopped SVG logo scaling on some site
= 3.2.7 =
* version bump
= 3.2.6 =
* Added new Laybuy Branding and reviewed all currency output
= 3.2.5 =
* Breakdowns now reflect the WooCommerce Currency
= 3.2.4 =
* Fixed a small php.5.x formating issue & removed the ssl message
= 3.2.2 =
* Fixed a small issue with some sites where the laybuy pay token was being reused, added a simple currency selector to the Laybuy Gateway settings
= 3.2.1 =
* Fixed a Confirm Order issue with some sites
= 3.2.0 =
* Refactored for Laybuy API changes
= 3.1.8 =
* use get_woocommerce_currency() instead of $order->get_currency()
= 3.1.7 =
* resolved a multisite issue
= 3.1.6 =
* Fixed an issue with order stock handling and correct the order complete handling
= 3.0 =
* Pulled in Lots of updates by Larry from private update service. Notably this includes the price breakdowns.
= 2.0 =
* Reworked name of plugin to be inline with other woocommerce gateways, added logging via the WC logging class, updated the way items are sent to laybuy so there is less chance of a calculation error, updated logo and description of the payment page.




