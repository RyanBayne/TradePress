<?php
/**
 * Uninstall plugin.
 * 
 * The uninstall.php file is a standard approach to running an uninstall
 * procedure for a plugin. It should be as simple as possible.
 *
 * @author      Ryan Bayne
 * @category    Core
 * @package     TradePress/Uninstaller
 * @version     2.0
 */

// Ensure plugin uninstall is being run by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    wp_die( __( 'Uninstallation file incorrectly requested for the TradePress plugin.', 'tradepress' ) );
}
                                                             
if( 'yes' == get_option( 'TradePress_remove_database_tables' ) ) { TradePress_remove_database_tables(); }
if( 'yes' == get_option( 'TradePress_remove_extensions' ) ) { TradePress_remove_extensions(); }
if( 'yes' == get_option( 'TradePress_remove_user_data' ) ) { TradePress_remove_user_data(); }
if( 'yes' == get_option( 'TradePress_remove_media' ) ) { TradePress_remove_media(); }
if( 'yes' == get_option( 'TradePress_remove_roles' ) ) { TradePress_remove_roles(); }

// The plan is to offer different levels of uninstallation to make testing and re-configuration easier...
//if( 'yes' == get_option( 'TradePress_remove_options' ) ) { TradePress_remove_options_surgically(); }
if( 'yes' == get_option( 'TradePress_remove_options' ) ) { TradePress_remove_options(); }

/**
* Uninstall all of the plugins options with care! 
* 
* @version 2.0
*/
function TradePress_remove_options() {          
    delete_option( 'TradePress_admin_notices' );
    delete_option( 'TradePress_admin_notice_missingvaluesofferwizard' );
    delete_option( 'TradePress_automatic_registration' );
    delete_option( 'TradePress_bugnet_cache_action_hooks' );
    delete_option( 'TradePress_display_actions' );
    delete_option( 'TradePress_display_filters' );
    delete_option( 'TradePress_login_button' );
    delete_option( 'TradePress_login_button_text' );
    delete_option( 'TradePress_login_loggedin_page_id' );
    delete_option( 'TradePress_login_loginpage_position' );
    delete_option( 'TradePress_login_loginpage_type' );
    delete_option( 'TradePress_login_mainform_page_id' );
    delete_option( 'TradePress_login_redirect_to_custom' );
    delete_option( 'TradePress_login_requiretwitch' );
    delete_option( 'TradePress_main_channels_refresh_token' );
    delete_option( 'TradePress_registration_button' );
    delete_option( 'TradePress_registration_requirevalidemail' );
    delete_option( 'TradePress_registration_twitchonly' );
    delete_option( 'TradePress_remove_database_tables' );
    delete_option( 'TradePress_remove_extensions' );
    delete_option( 'TradePress_remove_media' );
    delete_option( 'TradePress_remove_options' );
    delete_option( 'TradePress_remove_roles' );
    delete_option( 'TradePress_remove_user_data' );

    // BugNet   
    delete_option( 'bugnet_activate_events' );        
    delete_option( 'bugnet_activate_log' );        
    delete_option( 'bugnet_activate_tracing' );        
    delete_option( 'bugnet_levelswitch_emergency' );        
    delete_option( 'bugnet_levelswitch_alert' );        
    delete_option( 'bugnet_levelswitch_critical' );        
    delete_option( 'bugnet_levelswitch_error' );        
    delete_option( 'bugnet_levelswitch_warning' );        
    delete_option( 'bugnet_levelswitch_notice' );        
    delete_option( 'bugnet_handlerswitch_email' );        
    delete_option( 'bugnet_handlerswitch_logfiles' );        
    delete_option( 'bugnet_handlerswitch_restapi' );        
    delete_option( 'bugnet_handlerswitch_tracing' );        
    delete_option( 'bugnet_handlerswitch_wpdb' );        
    delete_option( 'bugnet_reportsswitch_dailysummary' );        
    delete_option( 'bugnet_reportsswitch_eventsnapshot' );        
    delete_option( 'bugnet_reportsswitch_tracecomplete' );        
    delete_option( 'bugnet_systemlogging_switch' );        
    delete_option( 'bugnet_error_dump_user_id' );      
}    

/**
* Remove database tables created by the TradePress core.
* 
* @version 1.0 
*/
function TradePress_remove_database_tables() {
    global $wpdb;
    
    $activity  = "{$wpdb->prefix}tradepress_calls";
    $errors    = "{$wpdb->prefix}tradepress_errors";
    $endpoints = "{$wpdb->prefix}tradepress_endpoints";
    $meta      = "{$wpdb->prefix}tradepress_meta";    
    
    $wpdb->query( "DROP TABLE IF EXISTS $activity" );
    $wpdb->query( "DROP TABLE IF EXISTS $errors" );
    $wpdb->query( "DROP TABLE IF EXISTS $endpoints" );
    $wpdb->query( "DROP TABLE IF EXISTS $meta" );
}

/**
* Remove all TradePress extensions. 
* 
* @version 1.0
*/
function TradePress_remove_extensions() {      
    foreach( TradePress_extensions_array() as $extensions_group_key => $extensions_group_array ) {
        foreach( $extensions_group_array as $extension_name => $extension_array ) {
            deactivate_plugins( $extension_name, true );
            uninstall_plugin( $extension_name );                                 
        }
    }     
}

/**
* Remove all user data created by the core plugin.
* 
* @version 1.0
*/
function TradePress_remove_user_data() {
    //delete_user_meta( 1, 'TradePress_twitch_sub' );
}

/**
* Remove media created by TradePress. 
* 
* @version 1.0
*/
function TradePress_remove_media() {
    
}

/**
 * Remove all roles and all custom capabilities added to 
 * both custom roles and core roles.
 * 
 * @version 1.0
 */
function TradePress_remove_roles() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    $capabilities = TradePress_get_core_capabilities();
    $capabilities = array_merge( $capabilities, TradePress_get_developer_capabilities() );
    
    foreach ( $capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->remove_cap( 'TradePressdeveloper', $cap );
        }
    }

    remove_role( 'TradePressdeveloper' );
}
