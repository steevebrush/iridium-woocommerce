<?php
/*
Iridium for WooCommerce
https://github.com/steevebrush/iridium-woocommerce
Adapted by stevebrush from krb plugin
*/

//---------------------------------------------------------------------------
// Global definitions
if (!defined('IRD_PLUGIN_NAME'))
  {
  define('IRD_VERSION',           '0.02');

  //-----------------------------------------------
  define('IRD_EDITION',           'Standard');

  //-----------------------------------------------
  define('IRD_SETTINGS_NAME',     'IRD-Settings');
  define('IRD_PLUGIN_NAME',       'Iridium for WooCommerce');


  // i18n plugin domain for language files
  define('IRD_I18N_DOMAIN',       'IRD');

  }
//---------------------------------------------------------------------------

//------------------------------------------
// Load wordpress for POSTback, WebHook and API pages that are called by external services directly.
if (defined('IRD_MUST_LOAD_WP') && !defined('WP_USE_THEMES') && !defined('ABSPATH'))
   {
   $g_blog_dir = preg_replace ('|(/+[^/]+){4}$|', '', str_replace ('\\', '/', __FILE__)); // For love of the art of regex-ing
   define('WP_USE_THEMES', false);
   require_once ($g_blog_dir . '/wp-blog-header.php');

   // Force-elimination of header 404 for non-wordpress pages.
   header ("HTTP/1.1 200 OK");
   header ("Status: 200 OK");

   require_once ($g_blog_dir . '/wp-admin/includes/admin.php');
   }
//------------------------------------------


// This loads necessary modules
require_once (dirname(__FILE__) . '/libs/forknoteWalletdAPI.php');

require_once( dirname( __FILE__ ) . '/ird-cron.php' );
require_once( dirname( __FILE__ ) . '/ird-utils.php' );
require_once( dirname( __FILE__ ) . '/ird-admin.php' );
require_once( dirname( __FILE__ ) . '/ird-render-settings.php' );
require_once( dirname( __FILE__ ) . '/ird-iridium-gateway.php' );

?>