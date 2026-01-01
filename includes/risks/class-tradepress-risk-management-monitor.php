<?php
/**
 * TradePress Risk Management Monitor
 *
 * Provides an asynchronous CRON-based process that monitors positions
 * and adjusts risk parameters based on changing market conditions.
 *
 * @package TradePress
 * @subpackage Risks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Risk_Management_Monitor' ) ) :

/**
 * Risk Management Monitor Class
 */
class TradePress_Risk_Management_Monitor extends TradePress_Background_Process {
    
    /**
     * Action identifier for this background process
     *
     * @var string
     */
    protected $action = 'tradepress_risk_management';
    
    /**
     * Logger instance
     *
     * @var TradePress_Logger
     */
    protected $logger;
    
    /**
     * Volatility monitor instance
     *
     * @var TradePress_Volatility_Monitor
     */
    protected $volatility_monitor;
    
    /**
     * Position manager instance
     *
     * @var TradePress_Position_Manager
     */
    protected $position_manager;
    
    /**
     * Initialize the risk monitor with configurable settings
     */
    public function __construct() {
        parent::__construct();
        
        // Initialize logger
        $this->logger = TradePress_Logger::get_instance();
        
        // Initialize volatility monitor
        $this->volatility_monitor = new TradePress_Volatility_Monitor();
        
        // Initialize position manager
        $this->position_manager = TradePress_Position_Manager::get_instance();
        
        // Register the CRON schedule
        add_action('init', array($this, 'register_cron_schedule'));
        
        // Hook the CRON event to our processor
        add_action('tradepress_risk_management_check', array($this, 'trigger_risk_check'));
    }
    
    /**
     * Register a custom CRON schedule that adapts to market volatility
     */
    public function register_cron_schedule() {
        add_filter('cron_schedules', function($schedules) {
            $volatility_level = $this->volatility_monitor->get_market_volatility_level();
            
            // Adjust frequency based on volatility
            $interval = 3600; // Default hourly for low volatility
            
            if ($volatility_level === 'high') {
                $interval = 600; // Every 10 minutes during high volatility
            } else if ($volatility_level === 'medium') {
                $interval = 1800; // Every 30 minutes during medium volatility
            }
            
            $schedules['tradepress_risk_schedule'] = array(
                'interval' => $interval,
                'display' => __('TradePress Adaptive Risk Schedule', 'tradepress')
            );
            
            return $schedules;
        });
        
        // Update the schedule if already registered
        $timestamp = wp_next_scheduled('tradepress_risk_management_check');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'tradepress_risk_management_check');
        }
        
        // Schedule the event with the potentially updated interval
        wp_schedule_event(time(), 'tradepress_risk_schedule', 'tradepress_risk_management_check');
    }
    
    /**
     * Trigger a risk check for all active positions
     */
    public function trigger_risk_check() {
        $positions = $this->get_active_positions();
        
        $this->logger->log('Triggered risk check for ' . count($positions) . ' active positions');
        
        foreach ($positions as $position) {
            $this->push_to_queue($position);
        }
        
        $this->save()->dispatch();
    }
    
    /**
     * Process a single position risk assessment
     *
     * @param array $position Position data
     * @return boolean False to remove item from queue
     */
    protected function task($position) {
        // Calculate current risk metrics
        $risk_calculator = new TradePress_Position_Risk_Calculator($position);
        $risk_metrics = $risk_calculator->calculate_position_risk();
        
        // Check if risk threshold has been exceeded
        if ($this->is_risk_threshold_exceeded($risk_metrics)) {
            // Take risk mitigation action (adjust stop loss, reduce position size, etc.)
            $this->execute_risk_mitigation($position, $risk_metrics);
        }
        
        return false; // Remove from queue
    }
    
    /**
     * Get all active trading positions
     *
     * @return array Array of position objects
     */
    private function get_active_positions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_positions';
        
        $positions = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE status = 'open'",
            ARRAY_A
        );
        
        return $positions ?: array();
    }
    
    /**
     * Check if position risk exceeds acceptable thresholds
     *
     * @param array $risk_metrics Risk metrics data
     * @return boolean True if threshold exceeded
     */
    private function is_risk_threshold_exceeded($risk_metrics) {
        // Get user-defined or default thresholds
        $threshold = get_option('tradepress_risk_score_threshold', 25);
        
        return $risk_metrics['risk_score'] > $threshold;
    }
    
    /**
     * Execute risk mitigation action
     *
     * @param array $position Position data
     * @param array $risk_metrics Risk metrics data
     * @return array Action results
     */
    private function execute_risk_mitigation($position, $risk_metrics) {
        $action_taken = 'none';
        $result = 'no_action';
        $reason = '';
        
        // Determine action based on risk level
        if ($risk_metrics['risk_score'] > 75) {
            // Severe risk - close position
            $reason = 'Severe risk score: ' . $risk_metrics['risk_score'];
            $result = $this->position_manager->close_position($position['id'], 100, $reason);
            $action_taken = 'close_position';
        } 
        else if ($risk_metrics['risk_score'] > 50) {
            // High risk - reduce position and tighten stop loss
            $reason = 'High risk score: ' . $risk_metrics['risk_score'];
            
            // Reduce by 50%
            $reduction_percent = 50;
            $result = $this->position_manager->reduce_position($position['id'], $reduction_percent, $reason);
            $action_taken = 'reduce_position';
            
            // Also tighten stop loss
            $current_stop = $this->position_manager->get_stop_loss($position['id']);
            $new_stop = $this->calculate_tighter_stop_loss($position, $current_stop, 'high');
            $this->position_manager->update_stop_loss($position['id'], $new_stop);
        }
        else if ($risk_metrics['risk_score'] > 25) {
            // Moderate risk - adjust stop loss only
            $reason = 'Moderate risk score: ' . $risk_metrics['risk_score'];
            $current_stop = $this->position_manager->get_stop_loss($position['id']);
            $new_stop = $this->calculate_tighter_stop_loss($position, $current_stop, 'moderate');
            $result = $this->position_manager->update_stop_loss($position['id'], $new_stop);
            $action_taken = 'adjust_stop_loss';
        }
        
        // Log the action taken
        $this->log_risk_action($position, $risk_metrics, $action_taken, $reason, $result);
        
        return [
            'action' => $action_taken,
            'result' => $result,
            'reason' => $reason
        ];
    }
    
    /**
     * Calculate a tighter stop loss based on risk level
     *
     * @param array $position Position data
     * @param float $current_stop Current stop loss
     * @param string $risk_level Risk level (moderate/high)
     * @return float New stop loss price
     */
    private function calculate_tighter_stop_loss($position, $current_stop, $risk_level) {
        $current_price = $position['current_price'];
        $entry_price = $position['entry_price'];
        $is_long = $position['direction'] === 'long';
        
        // Calculate as percentage of current position
        if ($is_long) {
            // For long positions, move stop loss higher
            $risk_factor = ($risk_level === 'high') ? 0.6 : 0.8;
            $ideal_stop = $current_price - (($current_price - $entry_price) * $risk_factor);
            
            // Don't move stop lower than current stop
            return max($current_stop, $ideal_stop);
        } else {
            // For short positions, move stop loss lower
            $risk_factor = ($risk_level === 'high') ? 0.6 : 0.8;
            $ideal_stop = $current_price + (($entry_price - $current_price) * $risk_factor);
            
            // Don't move stop higher than current stop
            return min($current_stop, $ideal_stop);
        }
    }
    
    /**
     * Log risk management actions
     *
     * @param array $position Position data
     * @param array $risk_metrics Risk metrics data
     * @param string $action_taken Action taken
     * @param string $reason Reason for action
     * @param mixed $result Action result
     */
    private function log_risk_action($position, $risk_metrics, $action_taken, $reason, $result) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_risk_monitor_actions';
        
        $wpdb->insert(
            $table_name,
            array(
                'position_id' => $position['id'],
                'action_time' => current_time('mysql'),
                'action_type' => $action_taken,
                'previous_stop_loss' => $position['stop_loss'],
                'new_stop_loss' => $this->position_manager->get_stop_loss($position['id']),
                'previous_position_size' => $position['position_size'],
                'new_position_size' => $this->position_manager->get_position_size($position['id']),
                'market_volatility' => $this->volatility_monitor->get_market_volatility_value(),
                'risk_metric' => $risk_metrics['risk_score'],
                'reason' => $reason,
                'result' => is_string($result) ? $result : json_encode($result)
            )
        );
        
        // Also log to the main logger
        $this->logger->log('Risk Management Action: ' . $action_taken . ' for position ID ' . $position['id'] . ' - ' . $reason);
    }
    
    /**
     * Generate performance report for risk management actions
     *
     * @param string $time_period Time period for report
     * @return array Performance metrics
     */
    public function generate_performance_report($time_period = '30days') {
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
        
        // Get all actions in this period
        $actions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE action_time BETWEEN %s AND %s",
            $start_date,
            $end_date
        ), ARRAY_A);
        
        if (empty($actions)) {
            return [
                'total_actions' => 0,
                'saved_loss' => 0,
                'prevented_drawdown' => 0,
                'premature_exits' => 0,
                'premature_exit_cost' => 0,
                'roi' => 0,
            ];
        }
        
        // Analyze actions - simplified for demo
        $total_actions = count($actions);
        $saved_loss = 0;
        $prevented_drawdown = 0;
        $premature_exits = 0;
        $premature_exit_cost = 0;
        
        // Placeholder for actual analysis
        // Future implementation: compare what happened with what would have happened
        
        // Calculate ROI as (saved_loss - premature_exit_cost)
        $roi = $saved_loss - $premature_exit_cost;
        
        return [
            'total_actions' => $total_actions,
            'saved_loss' => $saved_loss,
            'prevented_drawdown' => $prevented_drawdown,
            'premature_exits' => $premature_exits,
            'premature_exit_cost' => $premature_exit_cost,
            'roi' => $roi,
        ];
    }
}

endif; // End if class_exists check
