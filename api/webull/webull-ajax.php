<?php
/**
 * TradePress WeBull API AJAX Handlers
 *
 * Handles AJAX requests for WeBull API integration
 * 
 * @package TradePress
 * @subpackage API\WeBull
 * @version 1.0.0
 * @since 2025-04-13
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress WeBull API AJAX Class
 */
class TradePress_WeBull_AJAX {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX actions
        add_action('wp_ajax_tradepress_generate_webull_device_id', array($this, 'generate_device_id'));
        add_action('wp_ajax_tradepress_webull_authenticate', array($this, 'authenticate'));
    }
    
    /**
     * Generate a new WeBull device ID
     */
    public function generate_device_id() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress-webull-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tradepress')));
        }
        
        // Include the WeBull API class
        if (!class_exists('TradePress_WeBull_API')) {
            require_once(trailingslashit(TRADEPRESS_PLUGIN_PATH) . 'api/webull/webull-api.php');
        }
        
        // Initialize API
        $webull_api = new TradePress_WeBull_API();
        
        // Generate device ID
        $device_id_result = $webull_api->generate_device_id();
        
        if (is_wp_error($device_id_result)) {
            wp_send_json_error(array('message' => $device_id_result->get_error_message()));
        }
        
        // Return success
        wp_send_json_success(array('device_id' => $device_id_result));
    }
    
    /**
     * Authenticate with WeBull
     */
    public function authenticate() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress-webull-nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'tradepress')));
        }
        
        // Get credentials from POST data
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $device_id = isset($_POST['device_id']) ? sanitize_text_field($_POST['device_id']) : '';
        
        // Validate inputs
        if (empty($email) || empty($device_id)) {
            wp_send_json_error(array('message' => __('Email and device ID are required.', 'tradepress')));
        }
        
        // Get stored password if the password field was left as masked
        if (empty($password) || $password === '••••••••••••••••') {
            $password = get_option('tradepress_webull_password', '');
        } else {
            // Save the new password
            update_option('tradepress_webull_password', $password);
        }
        
        // Check if password is available
        if (empty($password)) {
            wp_send_json_error(array('message' => __('Password is required.', 'tradepress')));
        }
        
        // Include the WeBull API class
        if (!class_exists('TradePress_WeBull_API')) {
            require_once(trailingslashit(TRADEPRESS_PLUGIN_PATH) . 'api/webull/webull-api.php');
        }
        
        // Initialize API
        $webull_api = new TradePress_WeBull_API();
        
        // Attempt to log in
        $login_result = $webull_api->login($email, $password);
        
        if (is_wp_error($login_result)) {
            // Check if this is a verification code error (2FA)
            $error_data = $login_result->get_error_data();
            
            if (isset($error_data['need_code']) && $error_data['need_code'] === true) {
                // Send verification code
                $send_code_result = $webull_api->send_login_code($email);
                
                if (is_wp_error($send_code_result)) {
                    wp_send_json_error(array('message' => __('Failed to send verification code: ', 'tradepress') . $send_code_result->get_error_message()));
                }
                
                // Return the need for verification code
                wp_send_json_error(array(
                    'message' => __('Two-factor authentication required. Please check your email or phone for a verification code.', 'tradepress'),
                    'need_code' => true
                ));
            }
            
            // Regular login error
            wp_send_json_error(array('message' => $login_result->get_error_message()));
        }
        
        // Get tokens from the API instance
        $access_token = get_option('tradepress_webull_access_token', '');
        $refresh_token = get_option('tradepress_webull_refresh_token', '');
        
        // Return success with tokens
        wp_send_json_success(array(
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'message' => __('Successfully authenticated with WeBull!', 'tradepress')
        ));
    }
}

// Initialize the AJAX class
$tradepress_webull_ajax = new TradePress_WeBull_AJAX();