<?php
/**
 * TradePress Risk Management Admin Class
 *
 * Handles AJAX requests and admin functionality for risk management.
 */

if (!class_exists('TradePress_Risk_Management_Admin')) :

class TradePress_Risk_Management_Admin {

    private $risk_registry;
    private $risk_monitor;

    /**
     * Constructor
     */
    public function __construct($risk_registry, $risk_monitor) {
        $this->risk_registry = $risk_registry;
        $this->risk_monitor = $risk_monitor;

        // Register AJAX handlers
        add_action('wp_ajax_tradepress_save_risk_factors', array($this, 'ajax_save_risk_factors'));
        add_action('wp_ajax_tradepress_get_risk_report', array($this, 'ajax_get_risk_report'));
        add_action('wp_ajax_tradepress_get_position_risk', array($this, 'ajax_get_position_risk'));
    }

    /**
     * AJAX handler for saving risk factors
     */
    public function ajax_save_risk_factors() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_risk_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get form data
        $form_data = $_POST['form_data'] ?? '';
        parse_str($form_data, $form_values);
        
        // Determine which form was submitted
        $form_type = $_POST['form_type'] ?? '';
        
        // Process different form types
        switch ($form_type) {
            case 'general':
                // Save general settings
                update_option('tradepress_risk_management_enabled', isset($form_values['risk_management_enabled']) ? '1' : '0');
                
                // Update risk registry
                $this->risk_registry->set_risk_factor('portfolio_risk', 'max_position_size_percent', floatval($form_values['max_position_size_percent']));
                $this->risk_registry->set_risk_factor('portfolio_risk', 'max_sector_exposure_percent', floatval($form_values['max_sector_exposure_percent']));
                $this->risk_registry->set_risk_factor('portfolio_risk', 'max_correlation_threshold', floatval($form_values['max_correlation_threshold']));
                
                wp_send_json_success(array('message' => __('General settings saved successfully.', 'tradepress')));
                break;
                
            case 'thresholds':
                // Save risk score thresholds
                $this->risk_registry->set_risk_factor('portfolio_risk', 'default_risk_score_threshold', intval($form_values['default_risk_score_threshold']));
                $this->risk_registry->set_risk_factor('portfolio_risk', 'high_risk_score_threshold', intval($form_values['high_risk_score_threshold']));
                $this->risk_registry->set_risk_factor('portfolio_risk', 'severe_risk_score_threshold', intval($form_values['severe_risk_score_threshold']));
                
                // Save volatility thresholds
                $this->risk_registry->set_risk_factor('volatility_thresholds', 'low_vix_threshold', floatval($form_values['low_vix_threshold']));
                $this->risk_registry->set_risk_factor('volatility_thresholds', 'high_vix_threshold', floatval($form_values['high_vix_threshold']));
                $this->risk_registry->set_risk_factor('volatility_thresholds', 'vix_spike_percent', floatval($form_values['vix_spike_percent']));
                $this->risk_registry->set_risk_factor('volatility_thresholds', 'unusual_price_movement_percent', floatval($form_values['unusual_price_movement_percent']));
                
                wp_send_json_success(array('message' => __('Threshold settings saved successfully.', 'tradepress')));
                break;
                
            case 'responses':
                // Save risk response settings
                $this->risk_registry->set_risk_factor('risk_response', 'moderate_stop_adjustment_percent', intval($form_values['moderate_stop_adjustment_percent']));
                $this->risk_registry->set_risk_factor('risk_response', 'high_stop_adjustment_percent', intval($form_values['high_stop_adjustment_percent']));
                $this->risk_registry->set_risk_factor('risk_response', 'high_position_reduction_percent', intval($form_values['high_position_reduction_percent']));
                
                wp_send_json_success(array('message' => __('Response settings saved successfully.', 'tradepress')));
                break;
                
            case 'weights':
                // Validate weights sum to 1.0 (or close to it, accounting for floating point)
                $weights = array(
                    'portfolio_percentage_weight' => floatval($form_values['portfolio_percentage_weight']),
                    'unrealized_loss_weight' => floatval($form_values['unrealized_loss_weight']),
                    'time_held_weight' => floatval($form_values['time_held_weight']),
                    'market_volatility_weight' => floatval($form_values['market_volatility_weight']),
                    'correlation_weight' => floatval($form_values['correlation_weight']),
                    'symbol_volatility_weight' => floatval($form_values['symbol_volatility_weight']),
                    'sector_risk_weight' => floatval($form_values['sector_risk_weight']),
                    'earnings_proximity_weight' => floatval($form_values['earnings_proximity_weight']),
                    'technical_signals_weight' => floatval($form_values['technical_signals_weight']),
                );
                
                $total_weight = array_sum($weights);
                
                if ($total_weight < 0.99 || $total_weight > 1.01) {
                    wp_send_json_error(array('message' => sprintf(__('Weight factors must sum to 1.0. Current sum: %s', 'tradepress'), number_format($total_weight, 2))));
                }
                
                // Save all weights
                foreach ($weights as $key => $value) {
                    $this->risk_registry->set_risk_factor('risk_score_weights', $key, $value);
                }
                
                wp_send_json_success(array('message' => __('Weight settings saved successfully.', 'tradepress')));
                break;
                
            case 'sectors':
                // Process sector ratings
                $sector_ratings = array();
                
                // Extract sector ratings from form values
                foreach ($form_values as $key => $value) {
                    if (strpos($key, 'sector_') === 0) {
                        $sector_name = str_replace('_', ' ', substr($key, 7)); // Remove 'sector_' prefix
                        $sector_name = ucwords($sector_name); // Capitalize words
                        $sector_ratings[$sector_name] = floatval($value);
                    }
                }
                
                // Update each sector rating
                foreach ($sector_ratings as $sector => $rating) {
                    $this->risk_registry->set_sector_risk_rating($sector, $rating);
                }
                
                wp_send_json_success(array('message' => __('Sector ratings saved successfully.', 'tradepress')));
                break;
                
            default:
                wp_send_json_error(array('message' => __('Unknown form type.', 'tradepress')));
                break;
        }
    }
    
    /**
     * AJAX handler for getting risk report data
     */
    public function ajax_get_risk_report() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_risk_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        $time_period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '30days';
        
        // Generate risk performance report
        $report = $this->risk_monitor->generate_performance_report($time_period);
        
        // Get volatility history for chart
        $volatility_history = $this->get_volatility_history($time_period);
        
        // Get risk action summary
        $action_summary = $this->get_risk_action_summary($time_period);
        
        // Combine all data
        $response = array(
            'performance' => $report,
            'volatility_history' => $volatility_history,
            'action_summary' => $action_summary
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * Get volatility history for charting
     *
     * @param string $time_period Time period for report
     * @return array Volatility history data
     */
    private function get_volatility_history($time_period) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_risk_monitor_actions';
        
        // Define the date range
        $end_date = current_time('mysql');
        $start_date = '';
        
        switch ($time_period) {
            case '7days':
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            case 'all':
            default:
                $start_date = '1970-01-01 00:00:00';
                break;
        }
        
        // Get volatility data grouped by day
        $volatility_data = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(action_time) as date, AVG(market_volatility) as volatility
             FROM {$table_name}
             WHERE action_time BETWEEN %s AND %s
             GROUP BY DATE(action_time)
             ORDER BY date ASC",
            $start_date,
            $end_date
        ), ARRAY_A);
        
        // Format for chart.js
        $dates = array();
        $values = array();
        
        foreach ($volatility_data as $data) {
            $dates[] = $data['date'];
            $values[] = floatval($data['volatility']);
        }
        
        return array(
            'labels' => $dates,
            'data' => $values
        );
    }
    
    /**
     * Get risk action summary
     *
     * @param string $time_period Time period for report
     * @return array Action summary data
     */
    private function get_risk_action_summary($time_period) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_risk_monitor_actions';
        
        // Define the date range
        $end_date = current_time('mysql');
        $start_date = '';
        
        switch ($time_period) {
            case '7days':
                $start_date = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $start_date = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $start_date = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            case 'all':
            default:
                $start_date = '1970-01-01 00:00:00';
                break;
        }
        
        // Get action counts by type
        $action_counts = $wpdb->get_results($wpdb->prepare(
            "SELECT action_type, COUNT(*) as count
             FROM {$table_name}
             WHERE action_time BETWEEN %s AND %s
             GROUP BY action_type",
            $start_date,
            $end_date
        ), ARRAY_A);
        
        // Format into associative array
        $summary = array(
            'adjust_stop_loss' => 0,
            'reduce_position' => 0,
            'close_position' => 0,
            'total' => 0
        );
        
        foreach ($action_counts as $action) {
            $summary[$action['action_type']] = intval($action['count']);
            $summary['total'] += intval($action['count']);
        }
        
        return $summary;
    }
    
    /**
     * AJAX handler for getting position risk details
     */
    public function ajax_get_position_risk() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_risk_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get position ID
        $position_id = isset($_POST['position_id']) ? intval($_POST['position_id']) : 0;
        
        if ($position_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid position ID.', 'tradepress')));
        }
        
        // Get position data
        global $wpdb;
        $positions_table = $wpdb->prefix . 'tradepress_positions';
        
        $position = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$positions_table} WHERE id = %d", $position_id),
            ARRAY_A
        );
        
        if (!$position) {
            wp_send_json_error(array('message' => __('Position not found.', 'tradepress')));
        }
        
        // Calculate risk metrics
        $risk_calculator = new TradePress_Position_Risk_Calculator($position);
        $risk_metrics = $risk_calculator->calculate_position_risk();
        
        // Get recent risk actions for this position
        $actions_table = $wpdb->prefix . 'tradepress_risk_monitor_actions';
        
        $recent_actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$actions_table} WHERE position_id = %d ORDER BY action_time DESC LIMIT 5",
                $position_id
            ),
            ARRAY_A
        );
        
        // Prepare response data
        $response = array(
            'position' => array(
                'id' => $position['id'],
                'symbol' => $position['symbol'],
                'direction' => $position['direction'],
                'entry_price' => $position['entry_price'],
                'current_price' => $position['current_price'],
                'stop_loss' => $position['stop_loss'],
                'position_size' => $position['position_size'],
                'open_time' => $position['open_time'],
            ),
            'risk_metrics' => $risk_metrics,
            'recent_actions' => $recent_actions ?: array()
        );
        
        wp_send_json_success($response);
    }
}

endif; // End if class_exists check