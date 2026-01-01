<?php
/**
 * TradePress Automation: Core
 *
 * Defines the base automation class, responsible for registering AJAX handlers
 * and providing core functionality for the TradePress automation system.
 *
 * @package TradePress/Automation
 * @category Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Automation Class
 */
class TradePress_Automation {

    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers
        $this->register_ajax_handlers();
    }

    /**
     * Register AJAX handlers
     */
    private function register_ajax_handlers() {
        // Register the AJAX handlers for automation management
        add_action('wp_ajax_tradepress_toggle_algorithm', array($this, 'ajax_toggle_algorithm'));
        add_action('wp_ajax_tradepress_toggle_component', array($this, 'ajax_toggle_component'));
        add_action('wp_ajax_tradepress_toggle_all_automation', array($this, 'ajax_toggle_all_automation'));
        add_action('wp_ajax_tradepress_get_algorithm_metrics', array($this, 'ajax_get_algorithm_metrics'));
        add_action('wp_ajax_tradepress_get_dashboard_metrics', array($this, 'ajax_get_dashboard_metrics'));
        add_action('wp_ajax_tradepress_start_all_automation', array($this, 'ajax_start_all_automation'));
        
        // Add any other automation AJAX handlers here
    }

    /**
     * AJAX handler for toggling an algorithm
     */
    public function ajax_toggle_algorithm() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_automation_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed. Please refresh the page and try again.', 'tradepress')
            ));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to manage automation.', 'tradepress')
            ));
        }
        
        // Get action
        $action = isset($_POST['algorithm_action']) ? sanitize_text_field($_POST['algorithm_action']) : '';
        
        // Handle the action
        if ($action === 'start') {
            // Logic to start the algorithm
            wp_send_json_success(array(
                'message' => __('Algorithm started successfully.', 'tradepress')
            ));
        } elseif ($action === 'stop') {
            // Logic to stop the algorithm
            wp_send_json_success(array(
                'message' => __('Algorithm stopped successfully.', 'tradepress')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid action.', 'tradepress')
            ));
        }
    }

    /**
     * AJAX handler for toggling a component
     */
    public function ajax_toggle_component() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_automation_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed. Please refresh the page and try again.', 'tradepress')
            ));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to manage automation.', 'tradepress')
            ));
        }
        
        // Get parameters
        $component = isset($_POST['component']) ? sanitize_text_field($_POST['component']) : '';
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        // Handle the action
        if ($action_type === 'start') {
            // Logic to start the component
            wp_send_json_success(array(
                'message' => sprintf(__('Component "%s" started successfully.', 'tradepress'), $component)
            ));
        } elseif ($action_type === 'stop') {
            // Logic to stop the component
            wp_send_json_success(array(
                'message' => sprintf(__('Component "%s" stopped successfully.', 'tradepress'), $component)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid action type.', 'tradepress')
            ));
        }
    }

    /**
     * AJAX handler for toggling all automation
     */
    public function ajax_toggle_all_automation() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_automation_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed. Please refresh the page and try again.', 'tradepress')
            ));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to manage automation.', 'tradepress')
            ));
        }
        
        // Get action type
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        // Handle the action
        if ($action_type === 'stop') {
            // Logic to stop all automation
            wp_send_json_success(array(
                'message' => __('All automation components stopped successfully.', 'tradepress')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Invalid action type.', 'tradepress')
            ));
        }
    }

    /**
     * AJAX handler for getting algorithm metrics
     */
    public function ajax_get_algorithm_metrics() {
        // Check nonce for security
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'tradepress_automation_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed. Please refresh the page and try again.', 'tradepress')
            ));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to access metrics.', 'tradepress')
            ));
        }
        
        // Get metrics data
        $metrics = array(
            'symbols_processed' => rand(100, 500),
            'scores_generated' => rand(50, 200),
            'trade_signals' => rand(5, 20),
            'runtime' => sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59))
        );
        
        wp_send_json_success($metrics);
    }

    /**
     * AJAX handler for getting dashboard metrics
     */
    public function ajax_get_dashboard_metrics() {
        // Check nonce for security
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'tradepress_automation_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed. Please refresh the page and try again.', 'tradepress')
            ));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to access metrics.', 'tradepress')
            ));
        }
        
        // Get metrics data
        $metrics = array(
            'algorithm' => array(
                'runtime' => sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59)),
                'symbols_processed' => rand(100, 500),
                'scores_generated' => rand(50, 200)
            ),
            'signals' => array(
                'runtime' => sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59)),
                'signals_generated' => rand(5, 20)
            ),
            'trading' => array(
                'runtime' => sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59)),
                'trades_executed' => rand(1, 10)
            )
        );
        
        wp_send_json_success($metrics);
    }

    /**
     * AJAX handler for starting all automations
     */
    public function ajax_start_all_automation() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_automation_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security verification failed. Please refresh the page and try again.', 'tradepress')
            ));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to start automations.', 'tradepress')
            ));
        }
        
        // Get and validate automation mode
        $automation_mode = isset($_POST['automation_mode']) ? sanitize_text_field($_POST['automation_mode']) : 'all';
        if ($automation_mode !== 'all') {
            wp_send_json_error(array(
                'message' => __('Invalid automation mode.', 'tradepress')
            ));
        }
        
        try {
            // Start automation logic here...
            wp_send_json_success(array(
                'message' => __('All automations started successfully.', 'tradepress'),
                'started_count' => 3,
                'total_count' => 3
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('An error occurred while starting automations: ', 'tradepress') . $e->getMessage()
            ));
        }
    }
}

// Initialize the automation class
new TradePress_Automation();