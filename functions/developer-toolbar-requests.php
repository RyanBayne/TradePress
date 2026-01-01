<?php
/**
 * TradePress $_POST processing for developer-toolbar requests!
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TradePress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {    
    exit;
}

add_action( 'admin_post_TradePress_api_version_switch', 'TradePress_api_version_switch' );

/**
 * Controls the ability to switch between versions of the Twitch API 
 * Was introduced when preparing for Helix release
 *
 * @return void
 * @version 1.2.0
 */
function TradePress_api_version_switch() {

    // Only users with the TradePress_developer capability will be allowed to do this...
    if( !current_user_can( 'TradePressdevelopertoolbar' ) ) 
    {      
        TradePress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_twitchapiswitch_notice',
            'warning',
            false,
            __( 'No Permission', 'tradepress' ),
            __( 'You do not have the TradePress Developer capability for this action. That permission must be added to your WordPress account first.', 'tradepress' ) 
        );

        wp_redirect( wp_get_referer() );
        exit;                      
    }

    update_option( 'TradePress_apiversion', 6 );
    $version = 6;
    $name = 'Helix';        

    TradePress_Admin_Notices::add_wordpress_notice(
        'devtoolbar_twitchapiswitch_notice',
        'success',
        false,
        __( 'Twitch API Version Changed', 'tradepress' ),
        sprintf( __( 'You changed the Twitch API version to %d (%s)', 'tradepress' ), $version, $name ) 
    );
        
    wp_redirect( wp_get_referer() );
    exit;    
}

add_action( 'admin_post_TradePress_beta_testing_switch', 'TradePress_beta_testing_switch' );
  
function TradePress_beta_testing_switch() {

    // Only users with the TradePress_developer capability will be allowed to do this...
    if( !current_user_can( 'TradePressdevelopertoolbar' ) ) 
    {      
        TradePress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_beta_testing_nopermission_notice',
            'warning',
            false,
            __( 'Request Rejected', 'tradepress' ),
            __( 'You do not have the wp-capability (TradePress Developer) for this action.', 'tradepress' ) 
        );

        wp_redirect( wp_get_referer() );
        exit;                      
    }
    
    $beta_testing_switch = get_option( 'TradePress_beta_testing' );
    
    if( $beta_testing_switch )
    {
        update_option( 'TradePress_beta_testing', 0 );    
        TradePress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_beta_testing_disabled_notice',
            'success',
            false,
            __( 'TradePress Beta Testing Disabled', 'tradepress' ),
            __( 'Beta testing has been turned off. Some features might be hidden and others may operate differently.', 'tradepress' ) 
        );        
    }
    else
    {
        update_option( 'TradePress_beta_testing', 1 );
        TradePress_Admin_Notices::add_wordpress_notice(
            'devtoolbar_beta_testing_activated_notice',
            'success',
            false,
            __( 'TradePress Beta Testing Enabled', 'tradepress' ),
            __( 'You activated beta testing for TradePress. Please disable if you have not checked this versions risk-level and you are on a live site.', 'tradepress' ) 
        ); 
    }
        
    wp_redirect( wp_get_referer() );
    exit;    
}

add_action( 'admin_post_tradepress_reset_setup_wizard_toolbar', 'tradepress_reset_setup_wizard_toolbar' );

function tradepress_reset_setup_wizard_toolbar() {
    if( !current_user_can( 'TradePressdevelopertoolbar' ) ) {
        wp_die( __( 'Insufficient permissions', 'tradepress' ) );
    }
    
    if( !wp_verify_nonce( $_GET['_wpnonce'], 'tradepress_reset_setup_wizard_nonce' ) ) {
        wp_die( __( 'Security check failed', 'tradepress' ) );
    }
    
    delete_option( 'tradepress_setup_complete' );
    
    TradePress_Admin_Notices::add_wordpress_notice(
        'devtoolbar_reset_setup_wizard_notice',
        'success',
        false,
        __( 'Setup Wizard Reset', 'tradepress' ),
        __( 'Setup wizard has been reset. You can now access it from the TradePress menu.', 'tradepress' )
    );
    
    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_post_tradepress_toggle_developer_mode_toolbar', 'tradepress_toggle_developer_mode_toolbar' );

function tradepress_toggle_developer_mode_toolbar() {
    if( !current_user_can( 'TradePressdevelopertoolbar' ) ) {
        wp_die( __( 'Insufficient permissions', 'tradepress' ) );
    }
    
    if( !wp_verify_nonce( $_GET['_wpnonce'], 'tradepress_developer_mode_nonce' ) ) {
        wp_die( __( 'Security check failed', 'tradepress' ) );
    }
    
    $current_status = get_option('tradepress_developer_mode', false);
    $new_status = !$current_status;
    
    update_option('tradepress_developer_mode', $new_status);
    
    $status_text = $new_status ? __('enabled', 'tradepress') : __('disabled', 'tradepress');
    
    TradePress_Admin_Notices::add_wordpress_notice(
        'devtoolbar_developer_mode_notice',
        'success',
        false,
        __( 'Developer Mode Toggled', 'tradepress' ),
        sprintf( __( 'Developer mode has been %s.', 'tradepress' ), $status_text )
    );
    
    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_post_tradepress_refresh_api_matrix_cache', 'tradepress_refresh_api_matrix_cache' );

function tradepress_refresh_api_matrix_cache() {
    if( !current_user_can( 'TradePressdevelopertoolbar' ) ) {
        wp_die( __( 'Insufficient permissions', 'tradepress' ) );
    }
    
    if( !wp_verify_nonce( $_GET['_wpnonce'], 'tradepress_refresh_api_cache_nonce' ) ) {
        wp_die( __( 'Security check failed', 'tradepress' ) );
    }
    
    // Load the API capability matrix class
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/api-capability-matrix.php';
    
    // Refresh the cache
    $matrix = TradePress_API_Capability_Matrix::refresh_cache();
    
    TradePress_Admin_Notices::add_wordpress_notice(
        'devtoolbar_api_cache_refresh_notice',
        'success',
        false,
        __( 'API Matrix Cache Refreshed', 'tradepress' ),
        sprintf( __( 'Cache refreshed successfully. Found %d platforms and %d data types.', 'tradepress' ), 
            count($matrix['platforms']), count($matrix['data_types']) )
    );
    
    wp_redirect( wp_get_referer() );
    exit;
}