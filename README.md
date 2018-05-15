# Iridium for WooCommerce

beta release.

Iridium for WooCommerce is a Wordpress plugin that allows merchants to accept IRD at WooCommerce-powered online stores.

Contributors: Stevebrush, KittyCatTech, gesman

Tags: Iridium, Iridium wordpress plugin, Iridium plugin, Iridium payments, accept Iridium, Iridiums

Requires at least: 3.0.1

Tested up to: 4.4.1

Stable tag: trunk

License: BipCot NoGov Software License bipcot.org

License URI: https://github.com/steevebrush/iridium-woocommerceblob/master/LICENSE

## Description

Your online store must use WooCommerce platform (free wordpress plugin).
Once you have installed and activated WooCommerce, you may install and activate Iridium for WooCommerce.

### Benefits 

* Fully automatic operation.
* Accept payments with Iridium directly into your Iridium wallet.
* Iridium wallet payment option completely removes dependency on any third party service and middlemen.
* withdraw the wallet to another address
* Accept payment with Iridium for physical and digital downloadable products.
* Add Iridium option to your existing online store with alternative main currency.
* Flexible exchange rate calculations fully managed via administrative settings.
* Zero fees and no commissions for Iridium processing from any third party.
* Set main currency of your store in USD, IRD or BTC.
* Automatic conversion to Iridium via realtime exchange rate feed and calculations.
* Ability to set exchange rate calculation multiplier to compensate for any possible losses due to bank conversions and funds transfer fees or to simply promote the use of Iridium :-).
* iridium_walletd can be run locally or with a remote node, in that case, the blockchain is not stored locally.


## Installation 

1. Install WooCommerce plugin and configure your store (if you haven't done so already - http://wordpress.org/plugins/woocommerce/).
2. Install "Iridium for WooCommerce" wordpress plugin just like any other Wordpress plugin.
3. Activate.
4. Download and install on your computer the Iridium RPC walletd program. This file is part of the Iridium core : https://github.com/iridiumdev/iridium/releases/latest
5.  Generate a container wallet (https://wiki.ird.cash/iridium_walletd) (optionally reset the containter to a view only container and add the view only address - but you will lost the withdraw possibility). Run the iridium_walletd as a service.
6. Get your wallet address from walletd.
8. Within your site's Wordpress admin, navigate to: Iridium and paste your wallet address into "Wallet Address" field.
9. Fill in your iridium_walletd rpc api address and port
9. Fill-in other settings at Iridium management panel.
10. Press [Save changes]
11. If you do not see any errors - your store is ready for operation and to access payments with Iridium!


## Remove plugin

1. Deactivate plugin through the 'Plugins' menu in WordPress
2. Delete plugin through the 'Plugins' menu in WordPress

## Thanks
Thanks to Aiwe(KRB) 