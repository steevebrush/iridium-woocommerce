=== Iridium for WooCommerce ===
Contributors: KittyCatTech, gesman
Tags: Iridium, Iridium wordpress plugin, Iridium plugin, Iridium payments, accept Iridium, Iridiums
Requires at least: 3.0.1
Tested up to: 4.4.1
Stable tag: trunk
License: BipCot NoGov Software License bipcot.org
License URI: https://github.com/steevebrush/iridium-woocommerceblob/master/LICENSE


Iridium for WooCommerce is a Wordpress plugin that allows merchants to accept IRD at WooCommerce-powered online stores.

== Description ==

Your online store must use WooCommerce platform (free wordpress plugin).
Once you have installed and activated WooCommerce, you may install and activate Iridium for WooCommerce.

= Benefits =

* Fully automatic operation.
* Can be used with view only wallet so only the view private key is on the server and none of the spend private keys are required to be kept anywhere on your online store server.
* Accept payments with Iridium directly into your Iridium wallet.
* Iridium wallet payment option completely removes dependency on any third party service and middlemen.
* Accept payment with Iridium for physical and digital downloadable products.
* Add Iridium option to your existing online store with alternative main currency.
* Flexible exchange rate calculations fully managed via administrative settings.
* Zero fees and no commissions for Iridium processing from any third party.
* Set main currency of your store in USD, IRD or BTC.
* Automatic conversion to Iridium via realtime exchange rate feed and calculations.
* Ability to set exchange rate calculation multiplier to compensate for any possible losses due to bank conversions and funds transfer fees.


== Installation ==


1.  Install WooCommerce plugin and configure your store (if you haven't done so already - http://wordpress.org/plugins/woocommerce/).
2.  Install "Iridium for WooCommerce" wordpress plugin just like any other Wordpress plugin.
3.  Activate.
4.  Download and install on your computer Iridium wallet program from: https://Iridium.org/
5.  Copy and setup your wallet on the server. Change permission to executable. Run Iridiumd as a service.
6.  Generate Container (optionally reset containter to view only container and add view only address). Run walletd as a service.
7.  Get your wallet address from walletd.
8.  Within your site's Wordpress admin, navigate to:
	    WooCommerce -> Settings -> Checkout -> Iridium
	    and paste your wallet address into "Wallet Address" field.
9.  Select "Iridium service provider" = "Local Wallet" and fill-in other settings at Iridium management panel.
10. Press [Save changes]
11. If you do not see any errors - your store is ready for operation and to access payments with Iridium!


== Remove plugin ==

1. Deactivate plugin through the 'Plugins' menu in WordPress
2. Delete plugin through the 'Plugins' menu in WordPress


== Changelog ==

none