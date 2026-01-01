<?php
/**
* This file contains the arrays of all known options for the TradePress plugin.
* Use for uninstallation and troubleshooting.
*
* @author Ryan R. Bayne
* @package TradePress
* @version 1.0
*/

function TradePress_options_array() {
    return array(
        'misc'     => TradePress_options_misc(),
        'api'      => TradePress_options_twitch_api(),
        'switch'   => TradePress_options_switch(),
        'otherapi' => TradePress_options_otherapi(),
        'scope'    => TradePress_options_scope(),
        'bugnet'   => TradePress_options_bugnet(),
    );   
}
                                                  
function TradePress_options_misc() {
    $arr = array();
                        
    $arr[ 'TradePress_admin_notices' ] = array();
    $arr[ 'TradePress_admin_notice_missingvaluesofferwizard' ] = array();
    $arr[ 'TradePress_displayerrors' ] = array();
    $arr[ 'TradePress_db_version' ] = array();
    $arr[ 'TradePress_feedback_data' ] = array();
    $arr[ 'TradePress_feedback_prompt' ] = array();
    $arr[ 'TradePress_login_messages' ] = array();
    $arr[ 'TradePress_removeall' ] = array();
    $arr[ 'TradePress_remove_options' ] = array();
    $arr[ 'TradePress_remove_database_tables' ] = array();
    $arr[ 'TradePress_remove_extensions' ] = array();
    $arr[ 'TradePress_remove_user_data' ] = array();
    $arr[ 'TradePress_remove_media' ] = array();
    $arr[ 'TradePress_sync_job_channel_subscribers' ] = array();
    $arr[ 'TradePress_sync_timing' ] = array();
    $arr[ 'TRADEPRESS_VERSION' ] = array();
    $arr[ 'TradePress_feedback_data' ] = array();
    $arr[ 'TradePress_feedback_prompt' ] = array();
    $arr[ 'TradePress_displayerrors' ] = array();
    $arr[ 'TradePress_redirect_tracking_switch' ] = array();
    $arr[ 'TradePress_new_channeltowp' ] = array();
    $arr[ 'TradePress_new_wptochannel' ] = array();
    $arr[ 'TradePress_apply_prepend_value_all_posts' ] = array();
    $arr[ 'TradePress_prepend_value_all_posts' ] = array();
    $arr[ 'TradePress_apply_appending_value_all_posts' ] = array();
    $arr[ 'TradePress_appending_value_all_posts' ] = array();
    $arr[ 'TradePress_shareable_posttype_post' ] = array();
    $arr[ 'TradePress_shareable_posttype_page' ] = array();

    return $arr;        
}

function TradePress_options_twitch_api() {
    
    $arr = array();

    $arr[ 'TradePress_apiversion' ] = array();
    
    // Twitch Application Credentials Group
    $arr[ 'TradePress_app_id' ] = array();// Client ID
    $arr[ 'TradePress_app_secret' ] = array();// Client Secret
    $arr[ 'TradePress_app_redirect' ] = array();// Redirect URL
    $arr[ 'TradePress_app_token' ] = array();// Generated Token
    $arr[ 'TradePress_app_scopes' ] = array();// Tokens Scopes
    $arr[ 'TradePress_app_expiry' ] = array();// Tokens Scopes
    
    // API calls made on behalf 
    $arr[ 'TradePress_main_channels_code' ] = array();// Main users own channel oauth code. 
    $arr[ 'TradePress_main_channels_wpowner_id' ] = array();// WordPress ID of the main channel owner. 
    $arr[ 'TradePress_main_channels_token' ] = array();// Actually a user token but this makes it easier to obtain in many cases. 
    $arr[ 'TradePress_main_channels_refresh' ] = array();// Main channels oauth refresh token. 
    $arr[ 'TradePress_main_channels_scopes' ] = array();// Main users accepted API scope. 
    $arr[ 'TradePress_main_channels_postid' ] = array();// Generated on behalf of the main user. 
    $arr[ 'TradePress_main_channels_name' ] = array();// Main channel name (this might be the title of channel and not lowercase, please confirm)
    $arr[ 'TradePress_main_channels_id' ] = array();// Main channels Twitch ID (same as user ID)

    return $arr;
}

function TradePress_options_extension_integration() {
    $arr = array();
    $arr[ 'TradePress_TradePress-embed-everything_settings' ] = array();
    $arr[ 'TradePress_TradePress-login-extension_settings' ] = array();
    $arr[ 'TradePress_TradePress-sync-extension_settings' ] = array();
    $arr[ 'TradePress_TradePress-um-extension_settings' ] = array();
    return $arr;
}

function TradePress_options_switch() {
    $arr = array();
    $arr[ 'TradePress_admin_notices' ] = array();
    $arr[ 'TradePress_switch_discord_api_services' ] = array();
    $arr[ 'TradePress_switch_discord_api_logs' ] = array();
    return $arr;
}

function TradePress_options_otherapi() {
    $arr = array();
    $arr[ 'TradePress_otherapi_application_saving' ] = array();
    return $arr;  
}

function TradePress_options_bugnet() {
    $arr = array();
    $arr[ 'bugnet_activate_events' ] = array();
    $arr[ 'bugnet_activate_log' ] = array();
    $arr[ 'bugnet_activate_tracing' ] = array();
    $arr[ 'bugnet_levelswitch_emergency' ] = array();
    $arr[ 'bugnet_levelswitch_alert' ] = array();
    $arr[ 'bugnet_levelswitch_critical' ] = array();
    $arr[ 'bugnet_levelswitch_error' ] = array();
    $arr[ 'bugnet_levelswitch_warning' ] = array();
    $arr[ 'bugnet_levelswitch_notice' ] = array();
    $arr[ 'bugnet_handlerswitch_email' ] = array();
    $arr[ 'bugnet_handlerswitch_logfiles' ] = array();
    $arr[ 'bugnet_handlerswitch_restapi' ] = array();
    $arr[ 'bugnet_handlerswitch_tracing' ] = array();
    $arr[ 'bugnet_handlerswitch_wpdb' ] = array();
    $arr[ 'bugnet_reportsswitch_dailysummary' ] = array();
    $arr[ 'bugnet_reportsswitch_eventsnapshot' ] = array();
    $arr[ 'bugnet_reportsswitch_tracecomplete' ] = array();
    $arr[ 'bugnet_systemlogging_switch' ] = array();
    $arr[ 'bugnet_error_dump_user_id' ] = array();
    return $arr;
}