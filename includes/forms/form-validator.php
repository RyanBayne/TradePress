<?php
/**
 * TradePress Form Validator
 * 
 * Utility class for form validation and error handling
 * 
 * @package TradePress
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Form_Validator {
    
    /**
     * Validate form submission basics
     */
    public static function validate_form_basics($required_fields = array()) {
        $errors = array();
        
        // Check if POST data exists
        if (empty($_POST)) {
            $errors[] = 'No form data received';
            return $errors;
        }
        
        // Check required fields
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Required field missing: {$field}";
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate nonce
     */
    public static function validate_nonce($nonce_field, $nonce_action) {
        if (!isset($_POST[$nonce_field])) {
            tradepress_trace_log('Nonce field missing: ' . $nonce_field);
            return false;
        }
        
        if (!wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
            tradepress_trace_log('Nonce verification failed', array(
                'field' => $nonce_field,
                'action' => $nonce_action
            ));
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate user permissions
     */
    public static function validate_permissions($capability = 'manage_options') {
        if (!current_user_can($capability)) {
            tradepress_trace_log('User lacks required capability: ' . $capability);
            return false;
        }
        
        return true;
    }
    
    /**
     * Log form submission for debugging
     */
    public static function log_form_submission($form_name, $additional_data = array()) {
        $log_data = array(
            'form' => $form_name,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'post_keys' => array_keys($_POST),
            'additional' => $additional_data
        );
        
        tradepress_trace_log('Form submission logged', $log_data);
    }
    
    /**
     * Sanitize API settings
     */
    public static function sanitize_api_settings($api_id, $post_data) {
        $sanitized = array();
        
        // Boolean fields
        $boolean_fields = array(
            $api_id . '_enabled',
            $api_id . '_trading_enabled'
        );
        
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($post_data[$field]) ? 'yes' : 'no';
        }
        
        // Text fields
        $text_fields = array(
            $api_id . '_api_key',
            $api_id . '_api_secret',
            $api_id . '_paper_api_key',
            $api_id . '_paper_api_secret',
            $api_id . '_trading_mode',
            $api_id . '_update_frequency',
            $api_id . '_data_priority',
            'TradePress_api_' . $api_id . '_key'
        );
        
        foreach ($text_fields as $field) {
            if (isset($post_data[$field])) {
                $sanitized[$field] = sanitize_text_field($post_data[$field]);
            }
        }
        
        // Numeric fields
        $numeric_fields = array(
            $api_id . '_data_retention' => 'int',
            $api_id . '_max_position_size' => 'float',
            $api_id . '_stop_loss_percent' => 'float',
            $api_id . '_take_profit_percent' => 'float'
        );
        
        foreach ($numeric_fields as $field => $type) {
            if (isset($post_data[$field])) {
                $sanitized[$field] = ($type === 'int') ? intval($post_data[$field]) : floatval($post_data[$field]);
            }
        }
        
        return $sanitized;
    }
}