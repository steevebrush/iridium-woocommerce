<?php
/*
Iridium for WooCommerce
https://github.com/steevebrush/iridium-woocommerce
Adapted by stevebrush from krb plugin
*/

// Include everything
include( dirname( __FILE__ ) . '/ird-include-all.php' );

//===========================================================================
// Global vars.

global $g_IRD__plugin_directory_url;
$g_IRD__plugin_directory_url = plugins_url ('', __FILE__);

global $g_IRD__cron_script_url;
$g_IRD__cron_script_url = $g_IRD__plugin_directory_url . '/ird-cron.php';

//===========================================================================

//===========================================================================
// Global default settings
global $g_IRD__config_defaults;
$g_IRD__config_defaults = array (

   // ------- Hidden constants
   'assigned_address_expires_in_mins'     =>  2*60,   // 2 hours to pay for order and receive necessary number of confirmations.
   'funds_received_value_expires_in_mins' =>  '5',		// 'received_funds_checked_at' is fresh (considered to be a valid value) if it was last checked within 'funds_received_value_expires_in_mins' minutes.
   'blockchain_api_timeout_secs'          =>  '20',   // Connection and request timeouts for curl operations dealing with blockchain requests.
   'exchange_rate_api_timeout_secs'       =>  '10',   // Connection and request timeouts for curl operations dealing with exchange rate API requests.
   'soft_cron_job_schedule_name'          =>  'minutes_1',   // WP cron job frequency
   'cache_exchange_rates_for_minutes'	  =>	5,			// Cache exchange rate for that number of minutes without re-calling exchange rate API's.

   // ------- General Settings
   'service_provider'				 	  =>  'local_wallet',		// 'blockchain_info'
   'address'                              =>  '', 
   'confs_num'                            =>  '5', // number of confirmations required before accepting payment.
   'exchange_multiplier'                  =>  '1.00',

   'delete_db_tables_on_uninstall'        =>  '0',
   'autocomplete_paid_orders'			  =>  '1',
   'enable_soft_cron_job'                 =>  '1',    // Enable "soft" Wordpress-driven cron jobs.

   // ------- Special settings
   'exchange_rates'                       =>  array('EUR' => array('method|type' => array('time-last-checked' => 0, 'exchange_rate' => 1), 'GBP' => array())),
   'walletd_api'          =>  '127.0.0.1:14007',   // walletd api:port
   );
//===========================================================================

//===========================================================================
function IRD__GetPluginNameVersionEdition($please_donate = false) // false to turn off
{
  $return_data = '<img src="'. plugins_url('/images/ird_banner.png', __FILE__).'" height="150" width="auto"><br><h2 style="border-bottom:1px solid #DDD;padding-bottom:10px;margin-bottom:20px;">'.
            IRD_PLUGIN_NAME . ' version: <span>' .
            IRD_VERSION. '</span>' .
          '</h2>';


  if ($please_donate)
  {
    $return_data .= '<p style="border:1px solid #890e4e;padding:5px 10px;color:#004400;background-color:#FFF;"><u>Please donate IRD to</u>:&nbsp;&nbsp;<span style="color:#d21577;font-size:110%;font-weight:bold;"></span></p>';
  }

  return $return_data;
}
//===========================================================================

//===========================================================================
function IRD__withdraw ()
{
	$IRD_settings = IRD__get_settings();
	$address = $IRD_settings['address'];
	$walletd_api=$IRD_settings['walletd_api'];
	$withdraw_fee = 50000;
	$send_amount = $_POST['sendAmount'] * 100000000.0;
	$send_address = $_POST["withdraw_address"];
	$max_amount = $send_amount + $withdraw_fee;

	try{
		$wallet_api = New ForkNoteWalletd($walletd_api);
		$address_balance = $wallet_api->getBalance($address);
	}

	catch(Exception $e) {
		// amount is wrong
		return $e->GetMessage();
	}

	// okay ? let's send
	try {
		$sent = $wallet_api->sendTransaction( array( $address ), array(
			array(
				"amount" => $send_amount,
				"address" => $send_address
			)
		), false, 2, $withdraw_fee, $address, 0 );


		return "Withdraw Sent in Transaction: " . $sent["transactionHash"];
		//@TODO Log
	} catch ( Exception $e ) {
		// address not valid
		if ( strpos( $e, 'Bad address' ) !== false ) {
			return "Address is not valid";
		}

		//wrong amount
		if ( strpos( $e, 'Wrong amount' ) !== false ) {
			return "Amount is too big : " . $max_amount / 100000000.0 . " (fee include). you're balance is : " . $address_balance['availableBalance'] / 100000000.0 ;
		}

		return $e->GetMessage();
	}



}
//===========================================================================

//===========================================================================
function IRD__get_settings ($key=false)
{
  global   $g_IRD__plugin_directory_url;
  global   $g_IRD__config_defaults;

  $IRD_settings = get_option (IRD_SETTINGS_NAME);
  if (!is_array($IRD_settings))
    $IRD_settings = array();

  if ($key)
    return (@$IRD_settings[$key]);
  else
    return ($IRD_settings);
}
//===========================================================================

//===========================================================================
function IRD__update_settings ($IRD_use_these_settings=false, $also_update_persistent_settings=false)
{
   if ($IRD_use_these_settings)
      {
      // if ($also_update_persistent_settings)
      //   IRD__update_persistent_settings ($IRD_use_these_settings);

      update_option (IRD_SETTINGS_NAME, $IRD_use_these_settings);
      return;
      }

   global   $g_IRD__config_defaults;

   // Load current settings and overwrite them with whatever values are present on submitted form
   $IRD_settings = IRD__get_settings();

   foreach ($g_IRD__config_defaults as $k=>$v)
      {
      if (isset($_POST[$k]))
         {
         if (!isset($IRD_settings[$k]))
            $IRD_settings[$k] = ""; // Force set to something.
         IRD__update_individual_IRD_setting ($IRD_settings[$k], $_POST[$k]);
         }
      // If not in POST - existing will be used.
      }

  update_option (IRD_SETTINGS_NAME, $IRD_settings);
}
//===========================================================================

//===========================================================================
// Takes care of recursive updating
function IRD__update_individual_IRD_setting (&$IRD_current_setting, $IRD_new_setting)
{
   if (is_string($IRD_new_setting))
      $IRD_current_setting = IRD__stripslashes ($IRD_new_setting);
   else if (is_array($IRD_new_setting))  // Note: new setting may not exist yet in current setting: curr[t5] - not set yet, while new[t5] set.
      {
      // Need to do recursive
      foreach ($IRD_new_setting as $k=>$v)
         {
         if (!isset($IRD_current_setting[$k]))
            $IRD_current_setting[$k] = "";   // If not set yet - force set it to something.
         IRD__update_individual_IRD_setting ($IRD_current_setting[$k], $v);
         }
      }
   else
      $IRD_current_setting = $IRD_new_setting;
}
//===========================================================================

//===========================================================================
//
// Reset settings only for one screen
function IRD__reset_partial_settings ($also_reset_persistent_settings=false)
{
   global   $g_IRD__config_defaults;

   // Load current settings and overwrite ones that are present on submitted form with defaults
   $IRD_settings = IRD__get_settings();

   foreach ($_POST as $k=>$v)
      {
      if (isset($g_IRD__config_defaults[$k]))
         {
         if (!isset($IRD_settings[$k]))
            $IRD_settings[$k] = ""; // Force set to something.
         IRD__update_individual_IRD_setting ($IRD_settings[$k], $g_IRD__config_defaults[$k]);
         }
      }

  update_option (IRD_SETTINGS_NAME, $IRD_settings);

  // if ($also_reset_persistent_settings)
  //   IRD__update_persistent_settings ($IRD_settings);
}
//===========================================================================

//===========================================================================
function IRD__reset_all_settings ($also_reset_persistent_settings=false)
{
  global   $g_IRD__config_defaults;

  update_option (IRD_SETTINGS_NAME, $g_IRD__config_defaults);

  // if ($also_reset_persistent_settings)
  //   IRD__reset_all_persistent_settings ();
}
//===========================================================================

//===========================================================================
// Recursively strip slashes from all elements of multi-nested array
function IRD__stripslashes (&$val)
{
   if (is_string($val))
      return (stripslashes($val));
   if (!is_array($val))
      return $val;

   foreach ($val as $k=>$v)
      {
      $val[$k] = IRD__stripslashes ($v);
      }

   return $val;
}
//===========================================================================

//===========================================================================
/*
    ----------------------------------
    : Table 'IRD_payments' :
    ----------------------------------
      status                "unused"      - never been used address with last known zero balance
                            "assigned"    - order was placed and this address was assigned for payment
                            "revalidate"  - assigned/expired, unused or unknown address suddenly got non-zero balance in it. Revalidate it for possible late order payment against meta_data.
                            "used"        - order was placed and this address and payment in full was received. Address will not be used again.
                            "xused"       - address was used (touched with funds) by unknown entity outside of this application. No metadata is present for this address, will not be able to correlated it with any order.
                            "unknown"     - new address was generated but cannot retrieve balance due to blockchain API failure.
*/
function IRD__create_database_tables ($IRD_settings)
{
  global $wpdb;

  $IRD_settings = IRD__get_settings();
  $must_update_settings = false;

  $IRD_payments_table_name             = $wpdb->prefix . 'IRD_IRD_payments';

  if($wpdb->get_var("SHOW TABLES LIKE '$IRD_payments_table_name'") != $IRD_payments_table_name)
      $b_first_time = true;
  else
      $b_first_time = false;

 //----------------------------------------------------------
 // Create tables
  $query = "CREATE TABLE IF NOT EXISTS `$IRD_payments_table_name` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `IRD_address` char(98) NOT NULL,
    `IRD_payment_id` char(64) NOT NULL,
    `origin_id` char(128) NOT NULL DEFAULT '',
    `index_in_wallet` bigint(20) NOT NULL DEFAULT '0',
    `status` char(16)  NOT NULL DEFAULT 'unknown',
    `last_assigned_to_ip` char(16) NOT NULL DEFAULT '0.0.0.0',
    `assigned_at` bigint(20) NOT NULL DEFAULT '0',
    `total_received_funds` DECIMAL( 16, 8 ) NOT NULL DEFAULT '0.00000000',
    `received_funds_checked_at` bigint(20) NOT NULL DEFAULT '0',
    `address_meta` MEDIUMBLOB NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `IRD_payment_id` (`IRD_payment_id`),
    KEY `index_in_wallet` (`index_in_wallet`),
    KEY `origin_id` (`origin_id`),
    KEY `status` (`status`)
    );";
  $wpdb->query ($query);
 //----------------------------------------------------------
}
//===========================================================================

//===========================================================================
// NOTE: Irreversibly deletes all plugin tables and data
function IRD__delete_database_tables ()
{
  global $wpdb;

  $IRD_payments_table_name    = $wpdb->prefix . 'IRD_IRD_payments';

  $wpdb->query("DROP TABLE IF EXISTS `$IRD_payments_table_name`");
}
//===========================================================================

