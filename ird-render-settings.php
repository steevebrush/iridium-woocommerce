<?php
/*
Iridium for WooCommerce
https://github.com/steevebrush/iridium-woocommerce
Adapted by stevebrush from krb plugin
*/

// Include everything
include( dirname( __FILE__ ) . '/ird-include-all.php' );

//===========================================================================
function IRD__render_general_settings_page ()   { IRD__render_settings_page   ('general'); }
//function IRD__render_advanced_settings_page ()  { IRD__render_settings_page   ('advanced'); }
//===========================================================================

//===========================================================================
function IRD__render_settings_page ($menu_page_name)
{
   $IRD_settings = IRD__get_settings ();
   if (isset ($_POST['button_withdraw']))
      {
	      if (isset ($_POST['sendAmount']) && $_POST['sendAmount'] <> "" && $_POST['sendAmount'] <= 500 ){
		      $result = IRD__withdraw();
          } else {
		      $result = "Wrong Amount";
          }
echo '
<div align="center" style="background-color:#FFFFE0;padding:5px;font-size:120%;border: 1px solid #E6DB55;margin:5px;border-radius:3px;">
' . $result . '
</div>
';
      }
   else if (isset ($_POST['button_update_IRD_settings']))
      {
      IRD__update_settings ("", false);
echo <<<HHHH
<div align="center" style="background-color:#FFFFE0;padding:5px;font-size:120%;border: 1px solid #E6DB55;margin:5px;border-radius:3px;">
Settings updated!
</div>
HHHH;
      }
   else if (isset($_POST['button_reset_IRD_settings']))
      {
      IRD__reset_all_settings (false);
echo <<<HHHH
<div align="center" style="background-color:#FFFFE0;padding:5px;font-size:120%;border: 1px solid #E6DB55;margin:5px;border-radius:3px;">
All settings reverted to all defaults
</div>
HHHH;
      }
   else if (isset($_POST['button_reset_partial_IRD_settings']))
      {
      IRD__reset_partial_settings (false);
echo <<<HHHH
<div align="center" style="background-color:#FFFFE0;padding:5px;font-size:120%;border: 1px solid #E6DB55;margin:5px;border-radius:3px;">
Settings on this page reverted to defaults
</div>
HHHH;
      }

   // Output full admin settings HTML
    
  $gateway_status_message = "";
  $gateway_valid_for_use = IRD__is_gateway_valid_for_use($gateway_status_message);
  if (!$gateway_valid_for_use)
  {
    $gateway_status_message =
    '<p style="border:1px solid #DDD;padding:5px 10px;font-weight:bold;color:#EE0000;background-color:#fff6e9;">' .
    "Iridium Payment Gateway is NOT operational (try to re-enter and save settings): " . $gateway_status_message .
    '</p>';
  }
  else
  {
    $IRD_settings = IRD__get_settings();
    $address = $IRD_settings['address'];

    try{
      $walletd_api=$IRD_settings['walletd_api'];
      $wallet_api = New ForkNoteWalletd($walletd_api);
      $address_balance = $wallet_api->getBalance($address);
    }
    catch(Exception $e) {
    }          

    if ($address_balance === false)
    {
      $address_balance = __("Iridium address is not found in wallet.", 'woocommerce');
    } else {
      $address_pending_balance = $address_balance['lockedAmount'];
      $address_pending_balance = sprintf("%.4f IRD", $address_pending_balance  / 100000000.0);
      $address_balance = $address_balance['availableBalance'];
      $display_address_balance  = sprintf("%.4f IRD", $address_balance  / 100000000.0);
      $withdraw_fee = 50000;
      $display_fee  = sprintf("%.4f", $withdraw_fee  / 100000000.0);
    }


    $gateway_status_message =
    '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">' .
    '<div style="border:1px solid #EC6D00;padding:5px 10px;font-weight:bold;color:#000000;background-color:#fdfff7;">' .
    "Iridium Payment Gateway is operational at " . $IRD_settings['walletd_api'] .
    "<br>Pending Amount: " . $address_pending_balance . 
    "<br>Available Balance: " . $display_address_balance .
    "</div><br/><div style=\"border:1px solid #EC6D00;padding:5px 10px;color:#000000;background-color:#fff6e9;\">".
    "<h2>Withdraw</h2>Send <input type='text' placeholder='0.000 IRD' name='sendAmount'> (Minus Fee:" . $display_fee . ") To the address below : (max amount is 500 IRD at a time) " .
    '<textarea rows="1" style="width:100%;" placeholder="IRD Address" name="withdraw_address"></textarea>' .
    '<br/><input type="submit" name="button_withdraw" value="Withdraw" /></div>
    </form>';
  }

  $currency_code = false;
  if (function_exists('get_woocommerce_currency'))
    $currency_code = @get_woocommerce_currency();
  if (!$currency_code || $currency_code=='IRD')
    $currency_code = 'USD';

  $exchange_rate_message =
    '<p style="border:1px solid #DDD;padding:5px 10px;background-color:#FFFFFF;">' .
    IRD__get_exchange_rate_per_Iridium ($currency_code, 'getfirst', true) .
    '</p>';

   echo '<div class="wrap">';

   switch ($menu_page_name)
      {
      case 'general'     :
        echo  IRD__GetPluginNameVersionEdition();
        echo  $gateway_status_message . $exchange_rate_message;
        IRD__render_general_settings_page_html();
        break;

      default            :
        break;
      }

   echo '</div>'; // wrap
}
//===========================================================================

//===========================================================================
function IRD__render_general_settings_page_html ()
{
  $IRD_settings = IRD__get_settings ();
  global $g_IRD__cron_script_url;

?>

    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
      <p class="submit">
        <input type="submit"   name="button_update_IRD_settings"        value="<?php _e('Save Changes') ?>"             />
        <input type="submit"  style="color:red;" name="button_reset_partial_IRD_settings" value="<?php _e('Reset settings') ?>" onClick="return confirm('Are you sure you want to reset settings on this page?');" />
      </p>
      <table class="form-table">


        <tr valign="top">
          <th scope="row">Delete all plugin-specific settings, database tables and data on uninstall:</th>
          <td>
            <input type="hidden" name="delete_db_tables_on_uninstall" value="0" /><input type="checkbox" name="delete_db_tables_on_uninstall" value="1" <?php if ($IRD_settings['delete_db_tables_on_uninstall']) echo 'checked="checked"'; ?> />
            <p class="description">If checked - all plugin-specific settings, database tables and data will be removed from Wordpress database upon plugin uninstall (but not upon deactivation or upgrade).</p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Iridium Service Provider:</th>
          <td>
            <select name="service_provider" class="select " style="display: none;">
              <option <?php if ($IRD_settings['service_provider'] == 'local_wallet') echo 'selected="selected"'; ?> value="local_wallet">Local wallet (walletd)</option>
            </select>
              <input type="text" name="walletd_api" value="<?php echo $IRD_settings['walletd_api']; ?>">
            <p class="description">
              Please select your Iridium walletd rpc api and press [Save changes].<br>Then fill-in necessary details and press [Save changes] again.
              <br />Recommended setting: <b>Local wallet</b>.
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Wallet Address:</th>
          <td>
            <textarea style="width:75%;" name="address"><?php echo $IRD_settings['address']; ?></textarea>
            <p class="description">
              Set up your local wallet with the instructions for <a href="http://forknote.net/documentation/rpc-wallet/">the ForkNote RPC Wallet</a> or <a href="https://wiki.bytecoin.org/wiki/Bytecoin_RPC_Wallet">Reference(Bytecoin) RPC Wallet</a>. Then copy in one of your wallet addresses.
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Number of confirmations required before accepting payment:</th>
          <td>
            <input type="text" name="confs_num" value="<?php echo $IRD_settings['confs_num']; ?>" size="4" />
            <p class="description">
              After a transaction is broadcast to the Iridium network, it may be included in a block that is published
              to the network. When that happens it is said that one <a href="https://en.Iridium.it/wiki/Confirmation"><b>confirmation</b></a> has occurred for the transaction.
              With each subsequent block that is found, the number of confirmations is increased by one. To protect against double spending, a transaction should not be considered as confirmed until a certain number of blocks confirm, or verify that transaction.
              6 is considered very safe number of confirmations, although it takes longer to confirm.
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Payment expiration time (minutes):</th>
          <td>
            <input type="text" name="assigned_address_expires_in_mins" value="<?php echo $IRD_settings['assigned_address_expires_in_mins']; ?>" size="8" />
            <p class="description">
              Payment must receive the required number of confirmations within this time. This is so that the exchange rate is current.
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Exchange rate multiplier:</th>
          <td>
            <input type="text" name="exchange_multiplier" value="<?php echo $IRD_settings['exchange_multiplier']; ?>" size="4" />
            <p class="description">
              Extra multiplier to apply to convert store default currency to Iridium price.
              <br />Example: 1.05 - will add extra 5% to the total price with Iridium.
              May be useful to compensate for market volatility or for merchant's loss to fees when converting Iridiums to local currency,
                or to <strong>encourage customer to use Iridium</strong> for purchases (by setting multiplier to < 1.00 values).
            </p>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row">Auto-complete paid orders:</th>
          <td>
            <input type="hidden" name="autocomplete_paid_orders" value="0" /><input type="checkbox" name="autocomplete_paid_orders" value="1" <?php if ($IRD_settings['autocomplete_paid_orders']) echo 'checked="checked"'; ?> />
            <p class="description">If checked - fully paid order will be marked as 'completed' and '<i>Your order is complete</i>' email will be immediately delivered to customer.
            	<br />If unchecked: store admin will need to mark order as completed manually - assuming extra time needed to ship physical product after payment is received.
<!--             	<br />Note: virtual/downloadable products will automatically complete upon receiving full payment (so this setting does not have effect in this case). -->
            </p>
          </td>
        </tr>

        <tr valign="top">
            <th scope="row">Cron job type:</th>
            <td>
              <select name="enable_soft_cron_job" class="select ">
                <option <?php if ($IRD_settings['enable_soft_cron_job'] == '1') echo 'selected="selected"'; ?> value="1">Soft Cron (Wordpress-driven)</option>
                <option <?php if ($IRD_settings['enable_soft_cron_job'] != '1') echo 'selected="selected"'; ?> value="0">Hard Cron (Cpanel-driven)</option>
              </select>
              <p class="description">
                <?php if ($IRD_settings['enable_soft_cron_job'] != '1') echo '<p style="background-color:#FFC;color:#2A2;"><b>NOTE</b>: Hard Cron job is enabled: make sure to follow instructions below to enable hard cron job at your hosting panel.</p>'; ?>
                Cron job will take care of all regular Iridium payment processing tasks, like checking if payments are made and automatically completing the orders.<br />
                <b>Soft Cron</b>: - Wordpress-driven (runs on behalf of a random site visitor).
                <br />
                <b>Hard Cron</b>: - Cron job driven by the website hosting system/server (usually via CPanel). <br />
                When enabling Hard Cron job - make this script to run every 5 minutes at your hosting panel cron job scheduler:<br />
                <?php echo '<tt style="background-color:#FFA;color:#B00;padding:0px 6px;">wget -O /dev/null ' . $g_IRD__cron_script_url . '?hardcron=1</tt>'; ?>
                <br /><b style="color:red;">NOTE:</b> Cron jobs <b>might not work</b> if your site is password protected with HTTP Basic auth or other methods. This will result in WooCommerce store not seeing received payments (even though funds will arrive correctly to your Iridium addresses).
                <br /><u>Note:</u> You will need to deactivate/reactivate plugin after changing this setting for it to have effect.<br />
                "Hard" cron jobs may not be properly supported by all hosting plans (many shared hosting plans has restrictions in place).
                <br />For secure, fast hosting service optimized for wordpress and 100% compatibility with WooCommerce and Iridium we recommend <b><a href="http://hostrum.com/" target="_blank">Hostrum Hosting</a></b>.
              </p>
            </td>
        </tr>

      </table>

      <p class="submit">
          <input type="submit"     name="button_update_IRD_settings"        value="<?php _e('Save Changes') ?>"             />
          <input type="submit"   style="color:red;" name="button_reset_partial_IRD_settings" value="<?php _e('Reset settings') ?>" onClick="return confirm('Are you sure you want to reset settings on this page?');" />
      </p>
    </form>
<?php
}
//===========================================================================
