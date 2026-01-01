<?php
/**
 * TradePress - Admin Automation AJAX Handlers
 *
 * Handles AJAX requests for automation settings.
 *
 * @author   TradePress
 * @category Admin
 * @package  TradePress/Admin/Automation
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Automation_AJAX' ) ) :

/**
 * TradePress_Admin_Automation_AJAX Class.
 */
class TradePress_Admin_Automation_AJAX {

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Add AJAX actions - only for authenticated users with proper capabilities
        add_action('wp_ajax_tradepress_update_isa_reset_directive', array(__CLASS__, 'update_isa_reset_directive'));
        add_action('wp_ajax_tradepress_toggle_directive_status', array(__CLASS__, 'toggle_directive_status'));
        // Note: No wp_ajax_nopriv_ hooks for security - these actions require authentication
    }

    /**
     * Update ISA Reset scoring directive settings
     */
    public static function update_isa_reset_directive() {
        // Check nonce
        check_ajax_referer('tradepress-scoring-directives', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        // Get parameters
        $days_before = isset($_POST['days_before']) ? intval($_POST['days_before']) : 3;
        $days_after = isset($_POST['days_after']) ? intval($_POST['days_after']) : 3;
        $score_impact = isset($_POST['score_impact']) ? intval($_POST['score_impact']) : 10;
        $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false;
        
        // Validate inputs
        if ($days_before < 0 || $days_before > 30) {
            wp_send_json_error(array('message' => __('Days before must be between 0 and 30', 'tradepress')));
        }
        
        if ($days_after < 0 || $days_after > 30) {
            wp_send_json_error(array('message' => __('Days after must be between 0 and 30', 'tradepress')));
        }
        
        if ($score_impact < 0 || $score_impact > 50) {
            wp_send_json_error(array('message' => __('Score impact must be between 0 and 50', 'tradepress')));
        }
        
        // Load the centralized directives loader instead of the missing data class
        if (!function_exists('tradepress_get_directive_by_id')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
        }
        
        // In a real implementation, update the directive settings using the loader functions
        // For now, this is a placeholder that would integrate with the actual storage system
        $directive_data = array(
            'days_before' => $days_before,
            'days_after' => $days_after,
            'score_impact' => $score_impact,
            'is_active' => $is_active,
        );
        
        // Placeholder for actual save functionality
        $success = true; // Would be replaced with actual save logic
        
        if ($success) {
            wp_send_json_success(array(
                'message' => __('ISA Reset directive updated successfully', 'tradepress'),
                'data' => $directive_data
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to update directive settings', 'tradepress')));
        }
    }

    /**
     * Toggle directive active status
     */
    public static function toggle_directive_status() {
        // Check nonce
        check_ajax_referer('tradepress-scoring-directives', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        // Get parameters
        $directive_id = isset($_POST['directive_id']) ? sanitize_key($_POST['directive_id']) : '';
        $active = isset($_POST['active']) ? (bool)$_POST['active'] : false;
        
        if (empty($directive_id)) {
            wp_send_json_error(array('message' => __('Missing directive ID', 'tradepress')));
        }
        
        // Load the centralized directives loader instead of the missing data class
        if (!function_exists('tradepress_get_directive_by_id')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
        }
        
        // Placeholder for actual toggle functionality using the loader functions
        $success = true; // Would be replaced with actual toggle logic
        
        if ($success) {
            wp_send_json_success(array(
                'message' => $active 
                    ? __('Directive activated successfully', 'tradepress') 
                    : __('Directive deactivated successfully', 'tradepress'),
                'directive_id' => $directive_id,
                'active' => $active
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to update directive status', 'tradepress')));
        }
    }
}

// Initialize AJAX handlers
TradePress_Admin_Automation_AJAX::init();

endif;
