<?php
/*
* Plugin Name: Iridium for WooCommerce
* Plugin URI: https://github.com/steevebrush/iridium-woocommerce
* Description: Iridium for WooCommerce plugin allows you to accept payments with Iridium for physical and digi$
* Version: 0.02
* Author: Stevebrush, partially based on KittyCatTech work for Karbo
* Author URI: https://github.com/steevebrush/iridium-woocommerce
* License: BipCot NoGov Software License bipcot.org
* WC requires at least: 3.0.0
* WC tested up to: 3.4.0
*/



// Include everything
include( dirname( __FILE__ ) . '/ird-include-all.php' );

//---------------------------------------------------------------------------
// Add hooks and filters

// create custom plugin settings menu
add_action( 'admin_menu',                   'IRD_create_menu' );

register_activation_hook(__FILE__,          'IRD_activate');
register_deactivation_hook(__FILE__,        'IRD_deactivate');
register_uninstall_hook(__FILE__,           'IRD_uninstall');

add_filter ('cron_schedules',               'IRD__add_custom_scheduled_intervals');
add_action ('IRD_cron_action',             'IRD_cron_job_worker');     // Multiple functions can be attached to 'IRD_cron_action' action

IRD_set_lang_file();
//---------------------------------------------------------------------------

//===========================================================================
// activating the default values
function IRD_activate()
{
    global  $g_IRD__config_defaults;

    $IRD_default_options = $g_IRD__config_defaults;

    // This will overwrite default options with already existing options but leave new options (in case of upgrading to new version) untouched.
    $IRD_settings = IRD__get_settings ();

    foreach ($IRD_settings as $key=>$value)
    	$IRD_default_options[$key] = $value;

    update_option (IRD_SETTINGS_NAME, $IRD_default_options);

    // Re-get new settings.
    $IRD_settings = IRD__get_settings ();

    // Create necessary database tables if not already exists...
    IRD__create_database_tables ($IRD_settings);
    IRD__SubIns ();

    //----------------------------------
    // Setup cron jobs

    if ($IRD_settings['enable_soft_cron_job'] && !wp_next_scheduled('IRD_cron_action'))
    {
    	$cron_job_schedule_name = $IRD_settings['soft_cron_job_schedule_name'];
    	wp_schedule_event(time(), $cron_job_schedule_name, 'IRD_cron_action');
    }
    //----------------------------------

}
//---------------------------------------------------------------------------
// Cron Subfunctions
function IRD__add_custom_scheduled_intervals ($schedules)
{
	$schedules['seconds_30']     = array('interval'=>30,     'display'=>__('Once every 30 seconds'));
	$schedules['minutes_1']      = array('interval'=>1*60,   'display'=>__('Once every 1 minute'));
	$schedules['minutes_2.5']    = array('interval'=>2.5*60, 'display'=>__('Once every 2.5 minutes'));
	$schedules['minutes_5']      = array('interval'=>5*60,   'display'=>__('Once every 5 minutes'));

	return $schedules;
}
//---------------------------------------------------------------------------
//===========================================================================

//===========================================================================
// deactivating
function IRD_deactivate ()
{
    // Do deactivation cleanup. Do not delete previous settings in case user will reactivate plugin again...

    //----------------------------------
    // Clear cron jobs
    wp_clear_scheduled_hook ('IRD_cron_action');
    //----------------------------------
}
//===========================================================================

//===========================================================================
// uninstalling
function IRD_uninstall ()
{
    $IRD_settings = IRD__get_settings();

    if ($IRD_settings['delete_db_tables_on_uninstall'])
    {
        // delete all settings.
        delete_option(IRD_SETTINGS_NAME);

        // delete all DB tables and data.
        IRD__delete_database_tables ();
    }
}
//===========================================================================

//===========================================================================
function IRD_create_menu()
{

    // create new top-level menu
    // http://www.fileformat.info/info/unicode/char/e3f/index.htm
    add_menu_page (
        __('Woo Iridium', IRD_I18N_DOMAIN),                    // Page title
        __('Iridium', IRD_I18N_DOMAIN),                        // Menu Title - lower corner of admin menu
        'administrator',                                        // Capability
        'IRD-settings',                                        // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
        'IRD__render_general_settings_page',                   // Function
        plugins_url('/images/iridium_16x.png', __FILE__)      // Icon URL
        );

    add_submenu_page (
        'IRD-settings',                                        // Parent
        __("WooCommerce Iridium Gateway", IRD_I18N_DOMAIN),                   // Page title
        __("General Settings", IRD_I18N_DOMAIN),               // Menu Title
        'administrator',                                        // Capability
        'IRD-settings',                                        // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
        'IRD__render_general_settings_page'                    // Function
        );

}
//===========================================================================

//===========================================================================
// load language files
function IRD_set_lang_file()
{
    # set the language file
    $currentLocale = get_locale();
    if(!empty($currentLocale))
    {
        $moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
        if (@file_exists($moFile) && is_readable($moFile))
        {
            load_textdomain(IRD_I18N_DOMAIN, $moFile);
        }

    }
}
//===========================================================================

