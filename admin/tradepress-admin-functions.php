<?php
/**
 * TradePress - Admin Only Functions
 *
 * This file will only be included during an admin request. Use a file
 * like functions.TradePress-core.php if your function is meant for the frontend.   
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Admin
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate the complete nonce string, from the nonce base, the action 
 * and an item, e.g. TradePress_delete_table_3.
 *
 * @since 1.0.0
 *
 * @param string      $action Action for which the nonce is needed.
 * @param string|bool $item   Optional. Item for which the action will be performed, like "table".
 * @return string The resulting nonce string.
 */
function TradePress_nonce_prepend( $action, $item = false ) {
    $nonce = "TradePress_{$action}";
    if ( $item ) {
        $nonce .= "_{$item}";
    }
    return $nonce;
}

/**
 * Get all WordPress TradePress screen ids. These must be updated if new pages are added to the plugin.
 * Else they will not be able to use the TradePress admin functions or styles.
 *
 * @return array
 * 
 * @version 2.0
 */
function TradePress_get_screen_ids() {

    $screen_ids = array(
        // Main plugin pages
        'toplevel_page_TradePress',
        'toplevel_page_tradepress',
        
        // API related pages
        'tradepress_page_tradepress_api',
        'TradePress_page_TradePress_api',
        
        // Research pages
        'tradepress_page_tradepress_research',
        'TradePress_page_TradePress_research',
        
        // Tools pages
        'tradepress_page_tradepress_tools',
        'TradePress_page_TradePress_tools',
        
        // Automation pages
        'tradepress_page_tradepress_automation',
        'TradePress_page_TradePress_automation',
        
        // Social Platforms pages
        'tradepress_page_tradepress_social',
        'TradePress_page_TradePress_social',
        
        // Debug pages
        'tradepress_page_tradepress_debug',
        'TradePress_page_TradePress_debug',
        
        // Bot pages
        'tradepress_page_tradepress_bot',
        'TradePress_page_TradePress_bot',
        
        // Sandbox pages
        'tradepress_page_tradepress_sandbox',
        'TradePress_page_TradePress_sandbox',
        
        // Development pages
        'tradepress_page_tradepress_development',
        'TradePress_page_TradePress_development',
        
        // Settings pages
        'tradepress_page_tradepress-settings',
        'TradePress_page_TradePress-settings',
        
        // Data pages
        'tradepress_page_tradepress_data',
        'TradePress_page_TradePress_data',
        
        // Symbol CPT screens
        'symbols',
        'edit-symbols',
        
        // Legacy screens
        'channels',
        'edit-channels',
        
        // Webhook CPT screens
        'webhooks',
        'edit-webhooks',
    );

    return apply_filters( 'TradePress_screen_ids', $screen_ids );
}

/**
* Creates a new symbol post.
* 
* @version 1.1
*/
function TradePress_insert_symbol( string $company, string $stock_symbol, bool $validated = false ) {
    // Ensure $company begins with a capital letter.
    $company = ucfirst( $company );
    
    // Ensure $stock_symbol is uppercase and has no spaces.
    $stock_symbol = strtoupper( $stock_symbol );
    $stock_symbol = str_replace( ' ', '', $stock_symbol );

    // Ensure the stock symbol is not already linked to a symbol post.  
    if( TradePress_is_stocksymbol_in_postmeta( $stock_symbol ) ) {   
        return false;    
    }
   
    // Ensure post slug does not already exist.
    $post_name = sanitize_title( $stock_symbol );
    $post_name_exists = TradePress_does_post_name_exist( $post_name );                
    if( $post_name_exists ) {   
        return false;
    }
                                           
    // Create a new channel post.
    $post = array(
        'post_author' => 1,
        'post_title' => $company,
        'post_name'  => $stock_symbol,
        'post_status' => 'draft',
        'post_type' => 'symbols',
    );
    
    $post_id = wp_insert_post( $post, true );
    
    if( is_wp_error( $post_id ) ) {     
        return false;
    }
    
    // Add Twitch channel ID to the post as a permanent pairing. 
    add_post_meta( $post_id, 'tradepress_stock_symbol', $stock_symbol );
    
    return $post_id;
}

/**
 * Initialize dashboard widgets
 */
function tradepress_init_dashboard_widgets() {
    require_once plugin_dir_path(__FILE__) . 'dashboard/dashboard-widgets.php';
    $dashboard_widgets = new TradePress_Dashboard_Widgets();
    $dashboard_widgets->init();
}
add_action('init', 'tradepress_init_dashboard_widgets');

/**
 * Track symbol views across different parts of the plugin
 *
 * @param string $symbol The stock symbol being viewed
 */
function tradepress_track_symbol_view($symbol) {
    if (empty($symbol)) {
        return;
    }
    
    require_once plugin_dir_path(__FILE__) . 'includes/utils/recent-symbols.php';
    TradePress_Recent_Symbols::add_recent_symbol($symbol);
}

/**
 * Hook into various plugin activities to track symbol views
 */
function tradepress_register_symbol_tracking() {
    // Track symbols from URL parameters across the plugin
    if (isset($_GET['symbol']) && !empty($_GET['symbol'])) {
        tradepress_track_symbol_view(sanitize_text_field($_GET['symbol']));
    }
    
    // Hook into other plugin actions in the future as needed
    // Examples: add_action('tradepress_view_stock_details', 'tradepress_track_symbol_view');
}
add_action('admin_init', 'tradepress_register_symbol_tracking');

/**
 * Add a direct API test link to the TradePress menu
 * This is for debugging the Alpaca API connection issues
 */
function tradepress_add_direct_api_test_link() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $submenu;
    if (isset($submenu['tradepress'])) {
        $direct_test_url = admin_url('wp-content/plugins/TradePress/admin/page/direct-api-test.php');
        $submenu['tradepress'][] = array('Direct API Test', 'manage_options', $direct_test_url);
    }
}
add_action('admin_menu', 'tradepress_add_direct_api_test_link', 999);

/**
 * Process demo mode toggle from developer toolbar
 */
function tradepress_toggle_demo_mode() {
    // Security: Verify the nonce to prevent CSRF attacks.
    check_admin_referer('tradepress-toggle-demo-mode');

    if (!current_user_can('TradePressdevelopertoolbar')) {
        wp_die(__('You do not have permission to perform this action.', 'tradepress'));
    }
    
    // Ensure we have access to the is_demo_mode function
    if (!function_exists('is_demo_mode')) {
        require_once TRADEPRESS_PLUGIN_DIR . 'functions/functions.tradepress-test-data.php';
    }
    
    // Get current demo mode status
    $is_demo_active = is_demo_mode();
    
    // Toggle - using the same option key that the original is_demo_mode() function uses
    update_option('TradePress_demo_mode', !$is_demo_active ? 'yes' : 'no');
    
    // Clear any cached data that might depend on demo mode
    delete_transient('tradepress_earnings_data');
    
    // Redirect back to the previous page
    wp_safe_redirect(wp_get_referer());
    exit;
}
add_action('admin_post_TradePress_demo_mode_switch', 'tradepress_toggle_demo_mode');

/**
 * Fetch earnings calendar data from Alpha Vantage
 */
function tradepress_fetch_earnings_calendar_cron() {
    // Check if we have a valid API key
    $api_key = get_option('tradepress_alphavantage_api_key', '');
    if (empty($api_key)) {
        return;
    }
    
    // Load the AlphaVantage API class if it doesn't exist
    if (!class_exists('TradePress_AlphaVantage_API')) {
        require_once TRADEPRESS_PLUGIN_DIR . 'api/alphavantage/alphavantage-api.php';
    }
    
    // Initialize the API
    $api = new TradePress_AlphaVantage_API(array('api_key' => $api_key));
    
    // Fetch earnings calendar data with 3-month horizon
    $response = $api->get_earnings_calendar('3month');
    
    // Check for errors
    if (is_wp_error($response)) {
        // Log the error
        error_log('TradePress Earnings Calendar CRON Error: ' . $response->get_error_message());
        return;
    }
    
    // Store the data
    update_option('tradepress_earnings_calendar_data', $response);
    update_option('tradepress_earnings_last_updated', time());
    update_option('tradepress_earnings_data_source', 'Alpha Vantage');
    
    // Clear any transients related to earnings data to ensure fresh data is displayed
    delete_transient('tradepress_earnings_data');
    
    // Log a successful run
    do_action('tradepress_log', 'Earnings calendar data fetched successfully via CRON', 'info', 'cron');
}
add_action('tradepress_fetch_earnings_calendar', 'tradepress_fetch_earnings_calendar_cron');

/**
 * Handle manual CRON job execution requests
 */
function tradepress_handle_cron_actions() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'tradepress_automation' || 
        !isset($_GET['tab']) || $_GET['tab'] !== 'cron' || 
        !isset($_GET['action'])) {
        return;
    }
    
    $action = sanitize_text_field($_GET['action']);
    
    if ($action === 'run_earnings_calendar') {
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'tradepress_run_cron')) {
            wp_die(__('Security check failed. Please try again.', 'tradepress'));
        }
        
        // Run the cron job manually
        do_action('tradepress_fetch_earnings_calendar');
        
        // Redirect back with a success message
        wp_redirect(add_query_arg(array(
            'page' => 'tradepress_automation',
            'tab' => 'cron',
            'cron_run' => 'success',
            'job' => 'earnings_calendar'
        ), admin_url('admin.php')));
        exit;
    }
    
    if ($action === 'clear_all_tradepress_crons') {
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'tradepress_clear_cron')) {
            wp_die(__('Security check failed. Please try again.', 'tradepress'));
        }
        
        // Get all cron jobs
        $crons = _get_cron_array();
        
        // Loop through each timestamp
        foreach ($crons as $timestamp => $cron_jobs) {
            // Loop through each job
            foreach ($cron_jobs as $hook => $job_data) {
                // If it's a TradePress job, clear it
                if (strpos($hook, 'tradepress') !== false) {
                    wp_clear_scheduled_hook($hook);
                }
            }
        }
        
        // Redirect back with a success message
        wp_redirect(add_query_arg(array(
            'page' => 'tradepress_automation',
            'tab' => 'cron',
            'cron_clear' => 'success'
        ), admin_url('admin.php')));
        exit;
    }
}
add_action('admin_init', 'tradepress_handle_cron_actions');

/**
 * Display admin notices for CRON operations
 */
function tradepress_display_cron_notices() {
    $screen = get_current_screen();
    
    // Only show on the automation page
    if (!isset($screen->id) || $screen->id !== 'tradepress_page_tradepress_automation') {
        return;
    }
    
    // Display success message after manual cron run
    if (isset($_GET['cron_run']) && $_GET['cron_run'] === 'success' && isset($_GET['job'])) {
        $job_name = sanitize_text_field($_GET['job']);
        $job_label = $job_name === 'earnings_calendar' ? 'Earnings Calendar Import' : $job_name;
        
        echo '<div class="notice notice-success is-dismissible"><p>' . 
             sprintf(esc_html__('%s CRON job executed successfully.', 'tradepress'), esc_html($job_label)) . 
             '</p></div>';
    }
    
    // Display success message after clearing all TradePress crons
    if (isset($_GET['cron_clear']) && $_GET['cron_clear'] === 'success') {
        echo '<div class="notice notice-success is-dismissible"><p>' . 
             esc_html__('All TradePress CRON jobs have been cleared.', 'tradepress') . 
             '</p></div>';
    }
}
add_action('admin_notices', 'tradepress_display_cron_notices');

/**
 * Handle earnings calendar CRON settings update
 */
function tradepress_update_earnings_cron() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'tradepress'));
    }
    
    // Verify nonce
    if (!isset($_POST['tradepress_earnings_cron_nonce']) || !wp_verify_nonce($_POST['tradepress_earnings_cron_nonce'], 'tradepress_earnings_cron_settings')) {
        wp_die(__('Security check failed. Please try again.', 'tradepress'));
    }
    
    // Handle enable request
    if (isset($_POST['tradepress_enable_earnings_cron'])) {
        // Get the selected interval
        $interval = isset($_POST['tradepress_earnings_cron_interval']) ? sanitize_text_field($_POST['tradepress_earnings_cron_interval']) : 'daily';
        
        // Valid WordPress schedules
        $valid_schedules = array('hourly', 'twicedaily', 'daily', 'weekly');
        
        if (!in_array($interval, $valid_schedules)) {
            $interval = 'daily'; // Default to daily if invalid
        }
        
        // Clear any existing schedule
        wp_clear_scheduled_hook('tradepress_fetch_earnings_calendar');
        
        // Schedule new event
        wp_schedule_event(time(), $interval, 'tradepress_fetch_earnings_calendar');
        
        // Save settings
        update_option('tradepress_earnings_cron_enabled', true);
        update_option('tradepress_earnings_cron_interval', $interval);
        
        // Set success message
        add_settings_error('tradepress_cron', 'tradepress_cron_enabled', __('Earnings calendar updates have been scheduled.', 'tradepress'), 'success');
    }
    
    // Handle disable request
    if (isset($_POST['tradepress_disable_earnings_cron'])) {
        // Clear the scheduled hook
        wp_clear_scheduled_hook('tradepress_fetch_earnings_calendar');
        
        // Update settings
        update_option('tradepress_earnings_cron_enabled', false);
        
        // Set success message
        add_settings_error('tradepress_cron', 'tradepress_cron_disabled', __('Earnings calendar scheduled updates have been disabled.', 'tradepress'), 'success');
    }
    
    // Redirect back to the CRON tab with status message
    set_transient('settings_errors', get_settings_errors(), 30);
    wp_redirect(add_query_arg(array('page' => 'tradepress_automation', 'tab' => 'cron', 'settings-updated' => 1), admin_url('admin.php')));
    exit;
}
add_action('admin_post_tradepress_update_earnings_cron', 'tradepress_update_earnings_cron');

// Make sure WordPress registers a weekly schedule
function tradepress_add_weekly_cron_schedule($schedules) {
    if (!isset($schedules['weekly'])) {
        $schedules['weekly'] = array(
            'interval' => 604800, // 7 days in seconds
            'display' => __('Once Weekly', 'tradepress')
        );
    }
    return $schedules;
}
add_filter('cron_schedules', 'tradepress_add_weekly_cron_schedule');

/**
 * Process saving favorite tabs from settings
 */
function tradepress_save_favorite_tabs() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to perform this action.', 'tradepress'));
    }
    
    // Verify nonce
    if (!isset($_POST['favorite_tabs_nonce']) || !wp_verify_nonce($_POST['favorite_tabs_nonce'], 'save_tradepress_favorite_tabs')) {
        wp_die(__('Security check failed. Please try again.', 'tradepress'));
    }
    
    // Process favorite tabs data
    $favorite_tabs = isset($_POST['tradepress_favorite_tabs']) ? $_POST['tradepress_favorite_tabs'] : array();
    
    // Sanitize each tab ID
    $favorite_tabs = array_map('sanitize_text_field', $favorite_tabs);
    
    // Save to WordPress options
    update_option('tradepress_favorite_tabs', $favorite_tabs);
    
    // Redirect back to settings page with success message - FIX: use TradePress (uppercase) instead of tradepress-settings
    wp_redirect(add_query_arg(array(
        'page' => 'tradepress', // Changed from tradepress-settings to match actual menu slug
        'tab' => 'general',
        'section' => 'favetabs',
        'settings-updated' => '1'
    ), admin_url('admin.php')));
    exit;
}
add_action('admin_post_save_tradepress_favorite_tabs', 'tradepress_save_favorite_tabs');