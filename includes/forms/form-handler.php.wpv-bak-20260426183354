<?php
/**
 * TradePress Form Handler
 * 
 * Centralized form handling system to prevent submission failures
 * 
 * @package TradePress
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Form_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'handle_form_submissions'));
        $this->register_admin_post_handlers();
    }
    
    /**
     * Handle form submissions via admin_init hook
     */
    public function handle_form_submissions() {
        if (empty($_POST['tradepress_form_action'])) {
            return;
        }
        
        tradepress_trace_log('Form submission detected', array('action' => $_POST['tradepress_form_action']));
        
        $action = sanitize_text_field($_POST['tradepress_form_action']);
        
        switch ($action) {
            case 'save_api_settings':
                $this->handle_api_settings_save();
                break;
            default:
                tradepress_trace_log('Unknown form action: ' . $action);
                break;
        }
    }
    
    /**
     * Register admin-post handlers for backward compatibility
     */
    private function register_admin_post_handlers() {
        // Alpha Vantage settings
        add_action('admin_post_tradepress_save_alphavantage_settings', array($this, 'handle_alphavantage_settings'));
        
        // Alpaca settings  
        add_action('admin_post_tradepress_save_alpaca_settings', array($this, 'handle_alpaca_settings'));
        
        // Generic API settings
        add_action('admin_post_tradepress_save_api_settings', array($this, 'handle_generic_api_settings'));
    }
    
    /**
     * Handle API settings save
     */
    public function handle_api_settings_save() {
        tradepress_trace_log('API settings save handler called');
        
        // Log form submission for debugging
        TradePress_Form_Validator::log_form_submission('api_settings');
        
        // Get API ID from form
        $api_id = sanitize_text_field($_POST['api_id'] ?? '');
        
        if (empty($api_id)) {
            tradepress_trace_log('No API ID provided in form');
            wp_die('Invalid API configuration');
        }
        
        tradepress_trace_log('Processing API settings for: ' . $api_id);
        
        // Validate form basics
        $required_fields = array('api_id');
        $validation_errors = TradePress_Form_Validator::validate_form_basics($required_fields);
        
        if (!empty($validation_errors)) {
            tradepress_trace_log('Form validation failed', $validation_errors);
            wp_die('Form validation failed: ' . implode(', ', $validation_errors));
        }
        
        // Verify nonce
        $nonce_field = 'tradepress_' . $api_id . '_nonce';
        $nonce_action = 'tradepress_save_' . $api_id . '_settings';
        
        if (!TradePress_Form_Validator::validate_nonce($nonce_field, $nonce_action)) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!TradePress_Form_Validator::validate_permissions('manage_options')) {
            wp_die('Permission denied');
        }
        
        // Process settings based on API
        $this->save_api_settings($api_id);
        
        // Set success message
        set_transient('tradepress_' . $api_id . '_settings_updated', true, 30);
        
        // Redirect back with tab parameter
        $redirect_url = admin_url('admin.php?page=tradepress_platforms&tab=' . $api_id);
        tradepress_trace_log('Redirecting to: ' . $redirect_url);
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Save API settings for specific API
     */
    private function save_api_settings($api_id) {
        tradepress_trace_log('Saving settings for API: ' . $api_id);
        
        // API enabled/disabled
        $enabled = isset($_POST[$api_id . '_enabled']) ? 'yes' : 'no';
        update_option('TradePress_switch_' . $api_id . '_api_services', $enabled);
        
        // API key (data-only APIs)
        $api_key_field = 'TradePress_api_' . $api_id . '_key';
        if (isset($_POST[$api_key_field])) {
            $api_key = sanitize_text_field($_POST[$api_key_field]);
            update_option($api_key_field, $api_key);
            tradepress_trace_log('API key updated for: ' . $api_id);
        }
        
        // Trading API credentials
        if (isset($_POST[$api_id . '_api_key'])) {
            $api_key = sanitize_text_field($_POST[$api_id . '_api_key']);
            update_option('tradepress_' . $api_id . '_api_key', $api_key);
        }
        
        if (isset($_POST[$api_id . '_api_secret'])) {
            $api_secret = sanitize_text_field($_POST[$api_id . '_api_secret']);
            update_option('tradepress_' . $api_id . '_api_secret', $api_secret);
        }
        
        if (isset($_POST[$api_id . '_paper_api_key'])) {
            $paper_key = sanitize_text_field($_POST[$api_id . '_paper_api_key']);
            update_option('tradepress_' . $api_id . '_paper_api_key', $paper_key);
        }
        
        if (isset($_POST[$api_id . '_paper_api_secret'])) {
            $paper_secret = sanitize_text_field($_POST[$api_id . '_paper_api_secret']);
            update_option('tradepress_' . $api_id . '_paper_api_secret', $paper_secret);
        }
        
        // Trading settings
        if (isset($_POST[$api_id . '_trading_mode'])) {
            $mode = sanitize_text_field($_POST[$api_id . '_trading_mode']);
            update_option('tradepress_' . $api_id . '_trading_mode', $mode);
        }
        
        $trading_enabled = isset($_POST[$api_id . '_trading_enabled']) ? 'yes' : 'no';
        update_option('tradepress_' . $api_id . '_trading_enabled', $trading_enabled);
        
        // Risk management
        if (isset($_POST[$api_id . '_max_position_size'])) {
            $max_size = floatval($_POST[$api_id . '_max_position_size']);
            update_option('tradepress_' . $api_id . '_max_position_size', $max_size);
        }
        
        if (isset($_POST[$api_id . '_stop_loss_percent'])) {
            $stop_loss = floatval($_POST[$api_id . '_stop_loss_percent']);
            update_option('tradepress_' . $api_id . '_stop_loss_percent', $stop_loss);
        }
        
        if (isset($_POST[$api_id . '_take_profit_percent'])) {
            $take_profit = floatval($_POST[$api_id . '_take_profit_percent']);
            update_option('tradepress_' . $api_id . '_take_profit_percent', $take_profit);
        }
        
        // Update frequency
        if (isset($_POST[$api_id . '_update_frequency'])) {
            $frequency = sanitize_text_field($_POST[$api_id . '_update_frequency']);
            update_option('tradepress_' . $api_id . '_update_frequency', $frequency);
        }
        
        // Data retention
        if (isset($_POST[$api_id . '_data_retention'])) {
            $retention = intval($_POST[$api_id . '_data_retention']);
            update_option('tradepress_' . $api_id . '_data_retention', $retention);
        }
        
        // Data priority
        if (isset($_POST[$api_id . '_data_priority'])) {
            $priority = sanitize_text_field($_POST[$api_id . '_data_priority']);
            update_option('tradepress_' . $api_id . '_data_priority', $priority);
        }
        
        tradepress_trace_log('Settings saved successfully for: ' . $api_id);
    }
    
    /**
     * Handle Alpha Vantage settings (admin-post compatibility)
     */
    public function handle_alphavantage_settings() {
        tradepress_trace_log('Alpha Vantage admin-post handler called');
        $_POST['api_id'] = 'alphavantage';
        $this->handle_api_settings_save();
    }
    
    /**
     * Handle Alpaca settings (admin-post compatibility)
     */
    public function handle_alpaca_settings() {
        tradepress_trace_log('Alpaca admin-post handler called');
        $_POST['api_id'] = 'alpaca';
        $this->handle_api_settings_save();
    }
    
    /**
     * Handle generic API settings (admin-post compatibility)
     */
    public function handle_generic_api_settings() {
        tradepress_trace_log('Generic API admin-post handler called');
        $this->handle_api_settings_save();
    }
}

// Initialize the form handler
new TradePress_Form_Handler();