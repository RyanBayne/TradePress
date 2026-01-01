<?php
/**
 * Template for API tabs
 * 
 * This template is used as a base for all API integration tabs.
 * It provides a consistent UI for API configuration, status, and usage.
 * 
 * @version 1.0.3
 * @since   1.0.0
 * @package TradePress/admin/page/TradingPlatforms
 *
 * === IMPORTANT: UI FUNCTIONALITY DEPENDENCIES ===
 * Related JS: js/admin-api-tab.js - Controls tab switching, quick action buttons, and API testing functionality
 * Related CSS: css/admin-api-tabs.css - Contains all styling for the API tabs interface
 *
 * === DEVELOPMENT NOTES ===
 * 1. DO NOT add inline JavaScript to this file - use the admin-api-tab.js file instead
 * 2. DO NOT add inline CSS to this file - use the admin-api-tabs.css file instead
 * 3. The quick-action buttons trigger content section visibility via data-target attributes
 *    which must match the IDs of the respective content sections
 */

if (!defined('ABSPATH')) {
    exit;
}

// Set default values for variables if they are not defined by the including file
$api_id = isset($api_id) ? $api_id : '';
$api_name = isset($api_name) ? $api_name : '';
$api_description = isset($api_description) ? $api_description : '';
$api_version = isset($api_version) ? $api_version : 'v1';
$api_logo_url = isset($api_logo_url) ? $api_logo_url : '';
$endpoints = isset($endpoints) ? $endpoints : array();
$demo_mode = isset($demo_mode) ? $demo_mode : 'no';
$is_demo_mode = isset($is_demo_mode) ? $is_demo_mode : false;

// Include helper functions if they haven't been included already
if (!function_exists('get_status_color')) {
    require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';
}

// Variables that must be defined before including this template:
// $api_id - The API identifier (e.g., 'alltick', 'alpaca')
// $api_name - Display name of the API (e.g., 'AllTick')
// $api_description - Brief description of the API
// $api_version - Current API version
// $api_logo_url - URL to the API logo image
// $endpoints - Array of endpoints data
// $local_status - Local connection status array
// $service_status - Service status array
// $rate_limits - Rate limiting data array
// $documentation_links - Array of documentation links

// Check for demo mode - prioritize TRADEPRESS_DEMO_MODE constant if defined
$is_demo_mode = defined('TRADEPRESS_DEMO_MODE') ? (bool)TRADEPRESS_DEMO_MODE : ($demo_mode === 'yes');

// Process API settings form submission
if (isset($_POST['tradepress_' . $api_id . '_api_settings_nonce']) && wp_verify_nonce($_POST['tradepress_' . $api_id . '_api_settings_nonce'], 'tradepress_' . $api_id . '_api_settings')) {
    
    // Save API settings
    $api_enabled = isset($_POST['TradePress_switch_' . $api_id . '_api_services']) ? 'yes' : 'no';
    update_option('TradePress_switch_' . $api_id . '_api_services', $api_enabled);
    
    $api_logs = isset($_POST['TradePress_switch_' . $api_id . '_api_logs']) ? 'yes' : 'no';
    update_option('TradePress_switch_' . $api_id . '_api_logs', $api_logs);
    
    $api_premium = isset($_POST['TradePress_switch_' . $api_id . '_api_premium']) ? 'yes' : 'no';
    update_option('TradePress_switch_' . $api_id . '_api_premium', $api_premium);
    
    // Sanitize and save API keys
    if (isset($_POST['TradePress_api_' . $api_id . '_realmoney_apikey'])) {
        update_option('TradePress_api_' . $api_id . '_realmoney_apikey', sanitize_text_field($_POST['TradePress_api_' . $api_id . '_realmoney_apikey']));
    }
    
    if (isset($_POST['TradePress_api_' . $api_id . '_realmoney_secretkey'])) {
        update_option('TradePress_api_' . $api_id . '_realmoney_secretkey', sanitize_text_field($_POST['TradePress_api_' . $api_id . '_realmoney_secretkey']));
    }
    
    if (isset($_POST['TradePress_api_' . $api_id . '_papermoney_apikey'])) {
        update_option('TradePress_api_' . $api_id . '_papermoney_apikey', sanitize_text_field($_POST['TradePress_api_' . $api_id . '_papermoney_apikey']));
    }
    
    if (isset($_POST['TradePress_api_' . $api_id . '_papermoney_secretkey'])) {
        update_option('TradePress_api_' . $api_id . '_papermoney_secretkey', sanitize_text_field($_POST['TradePress_api_' . $api_id . '_papermoney_secretkey']));
    }
    
    // Save trading mode
    if (isset($_POST['TradePress_api_' . $api_id . '_trading_mode'])) {
        update_option('TradePress_api_' . $api_id . '_trading_mode', sanitize_text_field($_POST['TradePress_api_' . $api_id . '_trading_mode']));
    }
    
    // Additional API-specific fields can be handled in their respective view files
    
    // Add success message
    add_action('admin_notices', function() use ($api_name) {
        echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(__('%s API settings saved successfully.', 'tradepress'), esc_html($api_name)) . '</p></div>';
    });
}

// Process trading mode toggle form submission
if (isset($_POST['tradepress_' . $api_id . '_trading_mode_nonce']) && wp_verify_nonce($_POST['tradepress_' . $api_id . '_trading_mode_nonce'], 'tradepress_' . $api_id . '_api_settings')) {
    
    // Save trading mode setting
    if (isset($_POST['TradePress_api_' . $api_id . '_trading_mode'])) {
        $trading_mode = sanitize_text_field($_POST['TradePress_api_' . $api_id . '_trading_mode']);
        update_option('TradePress_api_' . $api_id . '_trading_mode', $trading_mode);
        
        // Add success message
        add_action('admin_notices', function() use ($api_name) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                sprintf(__('%s trading mode updated successfully.', 'tradepress'), esc_html($api_name)) . 
                '</p></div>';
        });
    }
}

// Process operational status toggle form submission
if (isset($_POST['tradepress_' . $api_id . '_operational_nonce']) && wp_verify_nonce($_POST['tradepress_' . $api_id . '_operational_nonce'], 'tradepress_' . $api_id . '_api_settings')) {
    
    // Save operational status setting
    if (isset($_POST['TradePress_switch_' . $api_id . '_api_services'])) {
        $api_enabled = sanitize_text_field($_POST['TradePress_switch_' . $api_id . '_api_services']);
        update_option('TradePress_switch_' . $api_id . '_api_services', $api_enabled);
        
        // Add success message
        add_action('admin_notices', function() use ($api_name) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                sprintf(__('%s operational status updated successfully.', 'tradepress'), esc_html($api_name)) . 
                '</p></div>';
        });
    }
}

// Process demo mode toggle form submission
if (isset($_POST['tradepress_' . $api_id . '_demo_mode_nonce']) && wp_verify_nonce($_POST['tradepress_' . $api_id . '_demo_mode_nonce'], 'tradepress_' . $api_id . '_api_settings')) {
    
    // Save demo mode setting if TRADEPRESS_DEMO_MODE constant is not defined
    if (!defined('TRADEPRESS_DEMO_MODE')) {
        if (isset($_POST['TradePress_switch_' . $api_id . '_demo_mode'])) {
            $demo_mode = sanitize_text_field($_POST['TradePress_switch_' . $api_id . '_demo_mode']);
            update_option('TradePress_switch_' . $api_id . '_demo_mode', $demo_mode);
            
            // Add success message
            add_action('admin_notices', function() use ($api_name) {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                    sprintf(__('%s demo mode updated successfully.', 'tradepress'), esc_html($api_name)) . 
                    '</p></div>';
            });
        }
    } else {
        // Show notice that demo mode is controlled by constant
        add_action('admin_notices', function() {
            echo '<div class="notice notice-info is-dismissible"><p>' . 
                __('Demo mode is controlled by the TRADEPRESS_DEMO_MODE constant in your configuration. This setting will not take effect.', 'tradepress') . 
                '</p></div>';
        });
    }
}

// Helper function to get real local status data
if (!function_exists('tradepress_get_real_local_status')) {
    function tradepress_get_real_local_status($api_id) {
        // This function should be implemented to check the local connection status with the API
        // For now, we'll implement a basic version that checks if API keys are configured
        
        $api_key = get_option('TradePress_api_' . $api_id . '_realmoney_apikey', '');
        $api_secret = get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '');
        $api_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no');
        
        $status = array();
        
        if ($api_enabled !== 'yes') {
            $status = array(
                'status' => 'error',
                'message' => __('API is disabled', 'tradepress')
            );
        } elseif (empty($api_key)) {
            $status = array(
                'status' => 'error',
                'message' => __('API key not configured', 'tradepress')
            );
        } elseif (empty($api_secret)) {
            $status = array(
                'status' => 'warning',
                'message' => __('API secret not configured', 'tradepress')
            );
        } else {
            try {
                // Try to make a test call to the API
                // This would be implemented based on the specific API class for the API ID
                // For demonstration, we'll return a success status
                $status = array(
                    'status' => 'success',
                    'message' => __('Connected', 'tradepress')
                );
            } catch (Exception $e) {
                $status = array(
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
            }
        }
        
        return $status;
    }
}

// Helper function to get real service status data
if (!function_exists('tradepress_get_real_service_status')) {
    function tradepress_get_real_service_status($api_id) {
        // This function should check the API service status
        // For a real implementation, you would make a call to the API's status endpoint
        
        // For now, we'll return a basic status based on whether we can ping the service
        $status = array();
        
        try {
            // Attempt to ping the API service (implementation depends on the specific API)
            // For demonstration purposes, we'll randomly determine if the service is available
            $random = mt_rand(0, 10);
            
            if ($random >= 8) {
                // Simulate an intermittent issue for testing
                throw new Exception(__('Service temporarily unavailable', 'tradepress'));
            }
            
            $status = array(
                'status' => 'success',
                'message' => __('Service operational', 'tradepress'),
                'last_updated' => current_time('mysql')
            );
        } catch (Exception $e) {
            $status = array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'last_updated' => current_time('mysql')
            );
        }
        
        return $status;
    }
}

// Helper function to get real rate limit data
if (!function_exists('tradepress_get_real_rate_limits')) {
    function tradepress_get_real_rate_limits($api_id) {
        // This function should retrieve real rate limit data from the API
        // For now, we'll return data based on the specific API ID or random values for testing
        
        $rate_limits = array();
        
        // Example implementation - in a real scenario, this would come from the API response headers
        // or a dedicated rate limiting endpoint
        switch ($api_id) {
            case 'alpaca':
                $rate_limits = array(
                    'daily_quota' => 200,
                    'daily_used' => mt_rand(10, 50),
                    'minute_quota' => 60,
                    'minute_used' => mt_rand(0, 10),
                    'reset_time' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime('00:00:00')))
                );
                break;
                
            case 'alphavantage':
                $rate_limits = array(
                    'daily_quota' => 500,
                    'daily_used' => mt_rand(50, 200),
                    'reset_time' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime('00:00:00')))
                );
                break;
                
            default:
                // Generic rate limit data for testing
                $rate_limits = array(
                    'daily_quota' => 1000,
                    'daily_used' => mt_rand(100, 400),
                    'hourly_quota' => 100,
                    'hourly_used' => mt_rand(10, 40),
                    'reset_time' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime('00:00:00')))
                );
                break;
        }
        
        return $rate_limits;
    }
}

// Get saved settings
$api_enabled = get_option('TradePress_switch_' . $api_id . '_api_services', 'no');
$api_logs = get_option('TradePress_switch_' . $api_id . '_api_logs', 'no');
$api_premium = get_option('TradePress_switch_' . $api_id . '_api_premium', 'no');
$realmoney_apikey = get_option('TradePress_api_' . $api_id . '_realmoney_apikey', '');
$realmoney_secretkey = get_option('TradePress_api_' . $api_id . '_realmoney_secretkey', '');
$papermoney_apikey = get_option('TradePress_api_' . $api_id . '_papermoney_apikey', '');
$papermoney_secretkey = get_option('TradePress_api_' . $api_id . '_papermoney_secretkey', '');
$trading_mode = get_option('TradePress_api_' . $api_id . '_trading_mode', 'paper');
?>

<div class="wrap tradepress-admin">
    <?php
    // Check if demo mode is active
    $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;

    // Display demo/live mode indicator using centralized function
    TradePress_Admin_Notices::demo_mode_indicator();
    ?>

    <?php include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/api-service-overview.php'); ?>

    <div class="api-dashboard">

        <!-- Quick Actions Bar -->
        <?php include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/quick-actions.php'); ?>

        <!-- API Status View (3 column layout) -->
        <div id="api-status-view" class="content-section">

            <div class="api-status-layout">
                <!-- Column 1: API Status Overview -->
                <?php include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/api-status-overview.php'); ?>
                
                <!-- Column 2: Configuration -->
                <?php include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/api-status-configuration.php'); ?>
                
                <!-- Column 3: Latest API Call Information -->
                <?php include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/api-status-call.php'); ?>
            </div>
        </div>

        <!-- Layout with API Configuration section -->
        <div class="api-layout-container">
            <div class="api-main-section">
                <div class="api-main-content">
                    <!-- Configuration Container -->
                    <div id="api-settings-section" class="content-section" style="display: none;">
                        <?php
                        // Get provider details from the API directory
                        $provider = TradePress_API_Directory::get_provider($api_id);
                        $api_type = isset($provider['api_type']) ? $provider['api_type'] : 'trading';
                        
                        // Use the appropriate configuration partial based on API type
                        if ($api_type === 'data_only') {
                            // For data-only APIs, use the config-data-only.php partial
                            include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/config-data-only.php');
                        } else {
                            // For trading APIs, use the default configuration form
                        ?>
                        <div class="status-box">
                            <div class="status-header">
                                <h3><?php esc_html_e('API Configuration', 'tradepress'); ?></h3>
                            </div>
                            
                            <!-- API settings form here -->
                            <div class="api-settings-form">
                                <form method="post" id="tradepress-<?php echo esc_attr($api_id); ?>-api-settings-inline">
                                    <?php wp_nonce_field('tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_api_settings_nonce'); ?>
                                    
                                    <table class="form-table">
                                        <tbody>
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_services"><?php _e('Enable API', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-checkbox">
                                                    <fieldset>
                                                        <legend class="screen-reader-text"><span><?php _e('Enable API', 'tradepress'); ?></span></legend>
                                                        <label for="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_services">
                                                            <input name="TradePress_switch_<?php echo esc_attr($api_id); ?>_api_services" id="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_services" type="checkbox" value="1" <?php checked($api_enabled, 'yes'); ?>> 
                                                            <?php _e('Enable this API for use.', 'tradepress'); ?>
                                                        </label>
                                                    </fieldset>
                                                </td>
                                            </tr>
                                            
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_logs"><?php _e('API Logging', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-checkbox">
                                                    <fieldset>
                                                        <legend class="screen-reader-text"><span><?php _e('API Logging', 'tradepress'); ?></span></legend>
                                                        <label for="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_logs">
                                                            <input name="TradePress_switch_<?php echo esc_attr($api_id); ?>_api_logs" id="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_logs" type="checkbox" value="1" <?php checked($api_logs, 'yes'); ?>> 
                                                            <?php _e('Log API Activity', 'tradepress'); ?>
                                                        </label>
                                                    </fieldset>
                                                </td>
                                            </tr>
                                            
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_premium"><?php _e('Premium Endpoints', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-checkbox">
                                                    <fieldset>
                                                        <legend class="screen-reader-text"><span><?php _e('Premium Endpoints', 'tradepress'); ?></span></legend>
                                                        <label for="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_premium">
                                                            <input name="TradePress_switch_<?php echo esc_attr($api_id); ?>_api_premium" id="inline_TradePress_switch_<?php echo esc_attr($api_id); ?>_api_premium" type="checkbox" value="1" <?php checked($api_premium, 'yes'); ?>> 
                                                            <?php _e('Allow Premium Endpoints', 'tradepress'); ?>
                                                        </label>
                                                    </fieldset>
                                                </td>
                                            </tr>
                                            
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_realmoney_apikey"><?php _e('Real-Money API Key ID', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-text">
                                                    <input name="TradePress_api_<?php echo esc_attr($api_id); ?>_realmoney_apikey" id="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_realmoney_apikey" type="text" 
                                                           value="<?php echo esc_attr($realmoney_apikey); ?>" class="regular-text">
                                                    <p class="description"><?php printf(__('Your API key ID for real money trading on %s.', 'tradepress'), esc_html($api_name)); ?></p>
                                                </td>
                                            </tr>
                                            
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_realmoney_secretkey"><?php _e('Real-Money API Secret Key', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-password">
                                                    <input name="TradePress_api_<?php echo esc_attr($api_id); ?>_realmoney_secretkey" id="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_realmoney_secretkey" type="password" 
                                                           value="<?php echo esc_attr($realmoney_secretkey); ?>" class="regular-text">
                                                    <p class="description"><?php printf(__('Your API secret key for real money trading on %s.', 'tradepress'), esc_html($api_name)); ?></p>
                                                </td>
                                            </tr>
                                            
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_papermoney_apikey"><?php _e('Paper-Money API Key ID', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-text">
                                                    <input name="TradePress_api_<?php echo esc_attr($api_id); ?>_papermoney_apikey" id="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_papermoney_apikey" type="text" 
                                                           value="<?php echo esc_attr($papermoney_apikey); ?>" class="regular-text">
                                                    <p class="description"><?php printf(__('Your API key ID for paper trading on %s.', 'tradepress'), esc_html($api_name)); ?></p>
                                                </td>
                                            </tr>
                                            
                                            <tr valign="top">
                                                <th scope="row" class="titledesc">
                                                    <label for="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_papermoney_secretkey"><?php _e('Paper-Money API Secret Key', 'tradepress'); ?></label>
                                                </th>
                                                <td class="forminp forminp-password">
                                                    <input name="TradePress_api_<?php echo esc_attr($api_id); ?>_papermoney_secretkey" id="inline_TradePress_api_<?php echo esc_attr($api_id); ?>_papermoney_secretkey" type="password" 
                                                           value="<?php echo esc_attr($papermoney_secretkey); ?>" class="regular-text">
                                                    <p class="description"><?php printf(__('Your API secret key for paper trading on %s.', 'tradepress'), esc_html($api_name)); ?></p>
                                                </td>
                                            </tr>

                                            <?php do_action('tradepress_api_settings_' . $api_id . '_fields'); ?>
                                        </tbody>
                                    </table>
                                    
                                    <p class="submit">
                                        <button type="submit" class="button-primary" name="save_<?php echo esc_attr($api_id); ?>_api_settings" value="Save"><?php _e('Save API Settings', 'tradepress'); ?></button>
                                    </p>
                                </form>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    
                    <!-- Data Explorer Section -->
                    <div id="data-explorer-section" class="content-section" style="display: none;">
                        <?php require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/data-explorer.php'; ?>
                    </div>
                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API Endpoints Table -->
    <div class="endpoints-table-wrapper content-section" id="available-endpoints" style="display: none;">
        <?php require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/endpoints-table.php'; ?>
    </div>

    <!-- API Debug Panel - Only shown in testing mode -->
    <div class="api-debug-panel" style="display: none;">
        <?php if (defined('TRADEPRESS_TESTING') && TRADEPRESS_TESTING): ?>
            <div class="debug-panel-header">
                <h3>Latest API Call Information</h3>
                <div class="debug-panel-actions">
                    <button type="button" class="button refresh-debug-info" data-api="<?php echo esc_attr($api_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('refresh-debug-info')); ?>">
                        <span class="dashicons dashicons-update"></span> Refresh
                    </button>
                </div>
            </div>
            <div id="api-call-info-container-debug">
                <?php 
                // Get latest API call details
                $api_call = TradePress_AJAX::get_latest_api_call($api_id);
                
                if ($api_call): ?>
                    <div class="api-call-details">
                        <table class="widefat">
                            <tr>
                                <th>API Identifier:</th>
                                <td><?php echo esc_html($api_call['api_id']); ?></td>
                            </tr>
                            <tr>
                                <th>Endpoint:</th>
                                <td><?php echo esc_html($api_call['endpoint']); ?></td>
                            </tr>
                            <tr>
                                <th>Method:</th>
                                <td><?php echo esc_html($api_call['method']); ?></td>
                            </tr>
                            <tr>
                                <th>Timestamp:</th>
                                <td><?php echo esc_html($api_call['timestamp']); ?></td>
                            </tr>
                            <tr>
                                <th>Request Data:</th>
                                <td><pre><?php echo esc_html(print_r($api_call['request_data'], true)); ?></pre></td>
                            </tr>
                            <tr>
                                <th>Response:</th>
                                <td>
                                    <div class="api-response">
                                        <pre><?php echo esc_html(is_string($api_call['response']) ? $api_call['response'] : print_r($api_call['response'], true)); ?></pre>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="api-info-message">
                        <p>No recent API calls for <?php echo esc_html($api_id); ?> have been cached. Make an API call with testing mode enabled to see results here.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
                <div class="api-warning-message">
                    <p><strong>Testing Mode Disabled:</strong> Enable TRADEPRESS_TESTING in tradepress.php to view API call details here.</p>
                </div>
        <?php endif; ?>
    </div>
        
    <!-- Include the diagnostic components -->
    <?php 
    // Only include if we're in debug mode or if a specific query parameter is present
    if (defined('WP_DEBUG') && WP_DEBUG || isset($_GET['debug']) || isset($_GET['diagnostics'])) {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/event-debugger.php';
    }
    ?>
</div>

