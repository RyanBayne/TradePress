<?php
/**
 * TradePress Automation Controller
 *
 * Handles the automation system's backend operations
 *
 * @package TradePress/Admin
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Automation_Controller Class
 */
class TradePress_Admin_Automation_Controller {
    /**
     * Constructor
     */
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_tradepress_toggle_algorithm', array($this, 'ajax_toggle_algorithm'));
        add_action('wp_ajax_tradepress_get_algorithm_metrics', array($this, 'ajax_get_algorithm_metrics'));
        add_action('wp_ajax_tradepress_toggle_component', array($this, 'ajax_toggle_component'));
        add_action('wp_ajax_tradepress_toggle_all_automation', array($this, 'ajax_toggle_all_automation'));
        add_action('wp_ajax_tradepress_get_dashboard_metrics', array($this, 'ajax_get_dashboard_metrics'));
        
        // Register data import AJAX handlers
        add_action('wp_ajax_tradepress_start_data_import', array($this, 'ajax_start_data_import'));
        add_action('wp_ajax_tradepress_stop_data_import', array($this, 'ajax_stop_data_import'));
        add_action('wp_ajax_tradepress_get_data_import_status', array($this, 'ajax_get_data_import_status'));
        
        // Register scoring process AJAX handlers
        add_action('wp_ajax_tradepress_start_scoring_process', array($this, 'ajax_start_scoring_process'));
        add_action('wp_ajax_tradepress_stop_scoring_process', array($this, 'ajax_stop_scoring_process'));
        add_action('wp_ajax_tradepress_get_scoring_process_status', array($this, 'ajax_get_scoring_process_status'));
        
        // Register health monitoring AJAX handler
        add_action('wp_ajax_tradepress_get_system_health', array($this, 'ajax_get_system_health'));
        
        // Register algorithm processing actions
        add_action('tradepress_run_algorithm_iteration', array($this, 'process_algorithm_iteration'));
        add_action('tradepress_run_signals_iteration', array($this, 'process_signals_iteration'));
        add_action('tradepress_run_trading_iteration', array($this, 'process_trading_iteration'));
        
        // Register admin post handlers
        add_action('admin_post_tradepress_save_scoring_directives', array($this, 'save_scoring_directives'));
    }
    
    /**
     * AJAX handler for toggling algorithm state
     */
    public function ajax_toggle_algorithm() {
        // Verify nonce and permissions
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $action = isset($_POST['algorithm_action']) ? sanitize_text_field($_POST['algorithm_action']) : '';
        
        if ($action === 'start') {
            $result = $this->start_algorithm();
            if ($result) {
                wp_send_json_success(array(
                    'message' => __('Algorithm started successfully', 'tradepress'),
                    'is_running' => true
                ));
            } else {
                wp_send_json_error(array('message' => __('Failed to start algorithm', 'tradepress')));
            }
        } elseif ($action === 'stop') {
            $result = $this->stop_algorithm();
            if ($result) {
                wp_send_json_success(array(
                    'message' => __('Algorithm stopped successfully', 'tradepress'),
                    'is_running' => false
                ));
            } else {
                wp_send_json_error(array('message' => __('Failed to stop algorithm', 'tradepress')));
            }
        } else {
            wp_send_json_error(array('message' => __('Invalid action', 'tradepress')));
        }
    }
    
    /**
     * AJAX handler for getting algorithm metrics
     */
    public function ajax_get_algorithm_metrics() {
        // Verify nonce and permissions
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        wp_send_json_success(array(
            'symbols_processed' => get_option('tradepress_symbols_processed', 0),
            'scores_generated' => get_option('tradepress_scores_generated', 0),
            'trade_signals' => get_option('tradepress_trade_signals', 0),
            'runtime' => $this->get_algorithm_runtime()
        ));
    }
    
    /**
     * AJAX handler for toggling individual components
     */
    public function ajax_toggle_component() {
        // Verify nonce and permissions
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $component = isset($_POST['component']) ? sanitize_text_field($_POST['component']) : '';
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        if (!in_array($component, array('algorithm', 'signals', 'trading', 'data_import', 'scoring'))) {
            wp_send_json_error(array('message' => __('Invalid component', 'tradepress')));
        }
        
        if (!in_array($action, array('start', 'stop'))) {
            wp_send_json_error(array('message' => __('Invalid action', 'tradepress')));
        }
        
        $result = false;
        
        switch ($component) {
            case 'algorithm':
                $result = ($action === 'start') ? $this->start_algorithm() : $this->stop_algorithm();
                break;
            case 'signals':
                $result = ($action === 'start') ? $this->start_signals() : $this->stop_signals();
                break;
            case 'trading':
                $result = ($action === 'start') ? $this->start_trading() : $this->stop_trading();
                break;
            case 'data_import':
                if ($action === 'start') {
                    $data_import = new TradePress_Data_Import_Process();
                    $data_import->push_to_queue(array('action' => 'fetch_earnings'));
                    $data_import->push_to_queue(array('action' => 'fetch_prices'));
                    $data_import->push_to_queue(array('action' => 'fetch_market_status'));
                    $data_import->save()->dispatch();
                    update_option('tradepress_data_import_status', 'running');
                    update_option('tradepress_data_import_start_time', current_time('timestamp'));
                    $result = true;
                } else {
                    $data_import = new TradePress_Data_Import_Process();
                    $data_import->cancel_process();
                    update_option('tradepress_data_import_status', 'stopped');
                    $result = true;
                }
                break;
            case 'scoring':
                if ($action === 'start') {
                    $scoring_process = new TradePress_Scoring_Process();
                    $scoring_process->push_to_queue(array('action' => 'calculate_scores'));
                    $scoring_process->push_to_queue(array('action' => 'generate_signals'));
                    $scoring_process->push_to_queue(array('action' => 'update_rankings'));
                    $scoring_process->save()->dispatch();
                    update_option('tradepress_scoring_process_status', 'running');
                    update_option('tradepress_scoring_process_start_time', current_time('timestamp'));
                    $result = true;
                } else {
                    $scoring_process = new TradePress_Scoring_Process();
                    $scoring_process->cancel_process();
                    update_option('tradepress_scoring_process_status', 'stopped');
                    $result = true;
                }
                break;
        }
        
        if ($result) {
            wp_send_json_success(array(
                'message' => sprintf(__('%s %s successfully', 'tradepress'), 
                    ucfirst($component), 
                    ($action === 'start' ? 'started' : 'stopped')
                ),
                'is_running' => ($action === 'start')
            ));
        } else {
            wp_send_json_error(array(
                'message' => sprintf(__('Failed to %s %s', 'tradepress'), 
                    $action, 
                    $component
                )
            ));
        }
    }
    
    /**
     * AJAX handler for toggling all automation components
     */
    public function ajax_toggle_all_automation() {
        // Verify nonce and permissions
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        
        if (!in_array($action, array('start', 'stop'))) {
            wp_send_json_error(array('message' => __('Invalid action', 'tradepress')));
        }
        
        $results = array(
            'data_import' => false,
            'scoring' => false
        );
        
        if ($action === 'start') {
            // Start data import process
            $data_import = new TradePress_Data_Import_Process();
            $data_import->push_to_queue(array('action' => 'fetch_earnings'));
            $data_import->push_to_queue(array('action' => 'fetch_prices'));
            $data_import->push_to_queue(array('action' => 'fetch_market_status'));
            $data_import->save()->dispatch();
            update_option('tradepress_data_import_status', 'running');
            update_option('tradepress_data_import_start_time', current_time('timestamp'));
            $results['data_import'] = true;
            
            // Start scoring process
            $scoring_process = new TradePress_Scoring_Process();
            $scoring_process->push_to_queue(array('action' => 'calculate_scores'));
            $scoring_process->push_to_queue(array('action' => 'generate_signals'));
            $scoring_process->push_to_queue(array('action' => 'update_rankings'));
            $scoring_process->save()->dispatch();
            update_option('tradepress_scoring_process_status', 'running');
            update_option('tradepress_scoring_process_start_time', current_time('timestamp'));
            $results['scoring'] = true;
        } else {
            // Stop data import process
            $data_import = new TradePress_Data_Import_Process();
            $data_import->cancel_process();
            update_option('tradepress_data_import_status', 'stopped');
            $results['data_import'] = true;
            
            // Stop scoring process
            $scoring_process = new TradePress_Scoring_Process();
            $scoring_process->cancel_process();
            update_option('tradepress_scoring_process_status', 'stopped');
            $results['scoring'] = true;
        }
        
        $success = $results['data_import'] && $results['scoring'];
        
        if ($success) {
            wp_send_json_success(array(
                'message' => sprintf(__('All background processes %s successfully', 'tradepress'), 
                    ($action === 'start' ? 'started' : 'stopped')
                ),
                'is_running' => ($action === 'start'),
                'components' => $results
            ));
        } else {
            wp_send_json_error(array(
                'message' => sprintf(__('Failed to %s all background processes', 'tradepress'), $action),
                'components' => $results
            ));
        }
    }
    
    /**
     * AJAX handler for getting dashboard metrics
     */
    public function ajax_get_dashboard_metrics() {
        // Verify nonce and permissions
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        // Get data import runtime
        $data_import_status = get_option('tradepress_data_import_status', 'stopped');
        $data_import_start_time = get_option('tradepress_data_import_start_time', 0);
        $data_import_runtime = '00:00:00';
        if ($data_import_status === 'running' && $data_import_start_time) {
            $elapsed = current_time('timestamp') - $data_import_start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $data_import_runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        // Get scoring runtime
        $scoring_status = get_option('tradepress_scoring_process_status', 'stopped');
        $scoring_start_time = get_option('tradepress_scoring_process_start_time', 0);
        $scoring_runtime = '00:00:00';
        if ($scoring_status === 'running' && $scoring_start_time) {
            $elapsed = current_time('timestamp') - $scoring_start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $scoring_runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        // Get health data
        $data_import_health = $this->get_data_import_health();
        $scoring_health = $this->get_scoring_health();
        $overall_health = $this->calculate_overall_health($data_import_health, $scoring_health);
        
        wp_send_json_success(array(
            'data_import_runtime' => $data_import_runtime,
            'scoring_runtime' => $scoring_runtime,
            'symbols_processed' => get_option('tradepress_scoring_symbols_processed', 0),
            'scores_generated' => get_option('tradepress_scoring_scores_generated', 0),
            'data_import_health' => $data_import_health,
            'scoring_health' => $scoring_health,
            'overall_health' => $overall_health
        ));
    }
    
    /**
     * Save scoring directives
     */
    public function save_scoring_directives() {
        // Verify nonce and permissions
        check_admin_referer('save_scoring_directives', 'scoring_directives_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'tradepress'));
        }
        
        // Don't allow changes while algorithm is running
        if (self::is_algorithm_running()) {
            add_settings_error(
                'tradepress_scoring_directives',
                'algorithm_running',
                __('Cannot modify directives while the algorithm is running. Stop it first.', 'tradepress'),
                'error'
            );
            
            // Redirect back to the directives tab
            wp_redirect(add_query_arg(
                array(
                    'page' => 'tradepress_automation',
                    'tab' => 'directives',
                    'settings-updated' => 'false'
                ),
                admin_url('admin.php')
            ));
            exit;
        }
        
        // Get existing directives from the registry
        $registry = TradePress_Scoring_Directives_Registry::instance();
        $existing_directives = $registry->get_directives();
        
        // Process and save directives
        $directives = isset($_POST['directives']) ? $_POST['directives'] : array();
        $sanitized_directives = array();
        
        foreach ($directives as $key => $directive) {
            // Skip if the directive doesn't exist in our registry
            // REMOVE this check as it can fail if registry isn't loaded
            // if (!isset($existing_directives[$key]) && strpos($key, 'new_') !== 0) {
            //     continue;
            // }
            
            // Handle new directives (those with 'name' field)
            if (isset($directive['name']) && !empty($directive['name']) && strpos($key, 'new_') === 0) {
                $indicator_key = sanitize_title($directive['name']);
                $sanitized_directives[$indicator_key] = array(
                    'weight' => isset($directive['weight']) ? intval($directive['weight']) : 10,
                    'bullish' => isset($directive['bullish']) ? sanitize_text_field($directive['bullish']) : '',
                    'bearish' => isset($directive['bearish']) ? sanitize_text_field($directive['bearish']) : '',
                    'active' => isset($directive['active']) ? true : false
                );
            } 
            // Handle existing directives
            else {
                $sanitized_directives[$key] = array(
                    'weight' => isset($directive['weight']) ? intval($directive['weight']) : 10,
                    'bullish' => isset($directive['bullish']) ? sanitize_text_field($directive['bullish']) : '',
                    'bearish' => isset($directive['bearish']) ? sanitize_text_field($directive['bearish']) : '',
                    'active' => isset($directive['active']) ? true : false
                );
            }
        }
        
        // Ensure weights of active directives add up to 100%
        $total_weight = 0;
        foreach ($sanitized_directives as $key => $directive) {
            if ($directive['active']) {
                $total_weight += $directive['weight'];
            }
        }
        
        if ($total_weight != 100 && $total_weight > 0) {
            // Normalize weights to total 100%
            foreach ($sanitized_directives as $key => $directive) {
                if ($directive['active']) {
                    $sanitized_directives[$key]['weight'] = round(($directive['weight'] / $total_weight) * 100);
                }
            }
            
            add_settings_error(
                'tradepress_scoring_directives',
                'weights_normalized',
                __('Directive weights have been normalized to total 100%.', 'tradepress'),
                'info'
            );
        }
        
        // Save the directives
        update_option('tradepress_scoring_directives', $sanitized_directives);
        
        add_settings_error(
            'tradepress_scoring_directives',
            'directives_saved',
            __('Scoring directives saved successfully.', 'tradepress'),
            'success'
        );
        
        // Redirect back to the directives tab
        wp_redirect(add_query_arg(
            array(
                'page' => 'tradepress_automation',
                'tab' => 'directives',
                'settings-updated' => 'true'
            ),
            admin_url('admin.php')
        ));
        exit;
    }
    
    /**
     * Start the algorithm
     *
     * @return bool Success status
     */
    private function start_algorithm() {
        // Don't start if already running
        if ($this->is_algorithm_running()) {
            return false;
        }
        
        // Set algorithm state
        update_option('tradepress_algorithm_running', true);
        update_option('tradepress_algorithm_start_time', time());
        
        // Initialize counters if they don't exist
        if (!get_option('tradepress_symbols_processed', false)) {
            update_option('tradepress_symbols_processed', 0);
        }
        if (!get_option('tradepress_scores_generated', false)) {
            update_option('tradepress_scores_generated', 0);
        }
        if (!get_option('tradepress_trade_signals', false)) {
            update_option('tradepress_trade_signals', 0);
        }
        
        // Schedule the first iteration
        if (!wp_next_scheduled('tradepress_run_algorithm_iteration')) {
            wp_schedule_single_event(time(), 'tradepress_run_algorithm_iteration');
        }
        
        return true;
    }
    
    /**
     * Stop the algorithm
     *
     * @return bool Success status
     */
    private function stop_algorithm() {
        // Don't stop if not running
        if (!$this->is_algorithm_running()) {
            return false;
        }
        
        // Set algorithm state
        update_option('tradepress_algorithm_running', false);
        
        // Clear any scheduled iterations
        wp_clear_scheduled_hook('tradepress_run_algorithm_iteration');
        
        return true;
    }
    
    /**
     * Start the signals processor
     *
     * @return bool Success status
     */
    private function start_signals() {
        // Don't start if already running
        if ($this->is_signals_running()) {
            return false;
        }
        
        // Set signals state
        update_option('tradepress_signals_running', true);
        update_option('tradepress_signals_start_time', time());
        
        // Initialize counters if they don't exist
        if (!get_option('tradepress_signals_generated', false)) {
            update_option('tradepress_signals_generated', 0);
        }
        
        // Schedule the first iteration
        if (!wp_next_scheduled('tradepress_run_signals_iteration')) {
            wp_schedule_single_event(time(), 'tradepress_run_signals_iteration');
        }
        
        return true;
    }
    
    /**
     * Stop the signals processor
     *
     * @return bool Success status
     */
    private function stop_signals() {
        // Don't stop if not running
        if (!$this->is_signals_running()) {
            return false;
        }
        
        // Set signals state
        update_option('tradepress_signals_running', false);
        
        // Clear any scheduled iterations
        wp_clear_scheduled_hook('tradepress_run_signals_iteration');
        
        return true;
    }
    
    /**
     * Start the trading bot
     *
     * @return bool Success status
     */
    private function start_trading() {
        // Don't start if already running
        if ($this->is_trading_running()) {
            return false;
        }
        
        // Set trading state
        update_option('tradepress_trading_running', true);
        update_option('tradepress_trading_start_time', time());
        
        // Initialize counters if they don't exist
        if (!get_option('tradepress_trades_executed', false)) {
            update_option('tradepress_trades_executed', 0);
        }
        
        // Schedule the first iteration
        if (!wp_next_scheduled('tradepress_run_trading_iteration')) {
            wp_schedule_single_event(time(), 'tradepress_run_trading_iteration');
        }
        
        return true;
    }
    
    /**
     * Stop the trading bot
     *
     * @return bool Success status
     */
    private function stop_trading() {
        // Don't stop if not running
        if (!$this->is_trading_running()) {
            return false;
        }
        
        // Set trading state
        update_option('tradepress_trading_running', false);
        
        // Clear any scheduled iterations
        wp_clear_scheduled_hook('tradepress_run_trading_iteration');
        
        return true;
    }
    
    /**
     * Process one iteration of the algorithm
     */
    public function process_algorithm_iteration() {
        // Check if algorithm should still be running
        if (!$this->is_algorithm_running()) {
            return;
        }
        
        // Get current counters
        $symbols_processed = get_option('tradepress_symbols_processed', 0);
        $scores_generated = get_option('tradepress_scores_generated', 0);
        $trade_signals = get_option('tradepress_trade_signals', 0);
        
        // Process a batch of symbols
        $batch_size = get_option('tradepress_algorithm_batch_size', 10);
        
        // For now, just increment counters to simulate work
        $new_symbols = min(10, $batch_size);
        $symbols_processed += $new_symbols;
        $scores_generated += $new_symbols;
        $trade_signals += rand(0, 2); // Random number of trade signals
        
        // Update counters (use scoring-specific option names for dashboard)
        update_option('tradepress_symbols_processed', $symbols_processed);
        update_option('tradepress_scores_generated', $scores_generated);
        update_option('tradepress_trade_signals', $trade_signals);
        
        // Also update scoring-specific counters for dashboard display
        update_option('tradepress_scoring_symbols_processed', $symbols_processed);
        update_option('tradepress_scoring_scores_generated', $scores_generated);
        
        // Schedule next iteration if still running
        if ($this->is_algorithm_running()) {
            if (!wp_next_scheduled('tradepress_run_algorithm_iteration')) {
                wp_schedule_single_event(time() + 5, 'tradepress_run_algorithm_iteration');
            }
        }
    }
    
    /**
     * Process one iteration of the signals processor
     */
    public function process_signals_iteration() {
        // Check if signals should still be running
        if (!$this->is_signals_running()) {
            return;
        }
        
        // Get current counters
        $signals_generated = get_option('tradepress_signals_generated', 0);
        
        // For now, just increment counter to simulate work
        $signals_generated += rand(0, 2); // Random number of signals
        
        // Update counter
        update_option('tradepress_signals_generated', $signals_generated);
        
        // Schedule next iteration if still running
        if ($this->is_signals_running()) {
            if (!wp_next_scheduled('tradepress_run_signals_iteration')) {
                wp_schedule_single_event(time() + 5, 'tradepress_run_signals_iteration');
            }
        }
    }
    
    /**
     * Process one iteration of the trading bot
     */
    public function process_trading_iteration() {
        // Check if trading should still be running
        if (!$this->is_trading_running()) {
            return;
        }
        
        // Get current counters
        $trades_executed = get_option('tradepress_trades_executed', 0);
        
        // For now, just increment counter to simulate work
        if (rand(0, 10) > 8) { // 20% chance of executing a trade
            $trades_executed++;
        }
        
        // Update counter
        update_option('tradepress_trades_executed', $trades_executed);
        
        // Schedule next iteration if still running
        if ($this->is_trading_running()) {
            if (!wp_next_scheduled('tradepress_run_trading_iteration')) {
                wp_schedule_single_event(time() + 5, 'tradepress_run_trading_iteration');
            }
        }
    }
    
    /**
     * Check if the algorithm is running
     *
     * @return bool
     */
    public static function is_algorithm_running() {
        return (bool) get_option('tradepress_algorithm_running', false);
    }
    
    /**
     * Check if the signals processor is running
     *
     * @return bool
     */
    public static function is_signals_running() {
        return (bool) get_option('tradepress_signals_running', false);
    }
    
    /**
     * Check if the trading bot is running
     *
     * @return bool
     */
    public static function is_trading_running() {
        return (bool) get_option('tradepress_trading_running', false);
    }
    
    /**
     * Get the algorithm runtime
     *
     * @return string Runtime in HH:MM:SS format
     */
    public static function get_algorithm_runtime() {
        if (!self::is_algorithm_running()) {
            return '00:00:00';
        }
        
        $start_time = get_option('tradepress_algorithm_start_time', 0);
        if (!$start_time) {
            return '00:00:00';
        }
        
        $runtime = time() - $start_time;
        $hours = floor($runtime / 3600);
        $minutes = floor(($runtime % 3600) / 60);
        $seconds = $runtime % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    /**
     * Get the signals runtime
     *
     * @return string Runtime in HH:MM:SS format
     */
    public static function get_signals_runtime() {
        if (!self::is_signals_running()) {
            return '00:00:00';
        }
        
        $start_time = get_option('tradepress_signals_start_time', 0);
        if (!$start_time) {
            return '00:00:00';
        }
        
        $runtime = time() - $start_time;
        $hours = floor($runtime / 3600);
        $minutes = floor(($runtime % 3600) / 60);
        $seconds = $runtime % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    /**
     * Get the trading bot runtime
     *
     * @return string Runtime in HH:MM:SS format
     */
    public static function get_trading_runtime() {
        if (!self::is_trading_running()) {
            return '00:00:00';
        }
        
        $start_time = get_option('tradepress_trading_start_time', 0);
        if (!$start_time) {
            return '00:00:00';
        }
        
        $runtime = time() - $start_time;
        $hours = floor($runtime / 3600);
        $minutes = floor(($runtime % 3600) / 60);
        $seconds = $runtime % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    /**
     * Enhanced error handling and health monitoring methods
     */
    
    /**
     * Get data import process health status
     */
    private function get_data_import_health() {
        if (class_exists('TradePress_Data_Import_Process')) {
            $process = new TradePress_Data_Import_Process();
            if (method_exists($process, 'get_health_status')) {
                return $process->get_health_status();
            }
        }
        
        // Fallback health check
        $error_states = get_option('tradepress_data_import_error_state', array());
        $last_run = get_option('tradepress_data_import_last_run', 0);
        
        return array(
            'status' => get_option('tradepress_data_import_status', 'stopped'),
            'last_run' => $last_run,
            'errors' => $error_states,
            'health_score' => $this->calculate_basic_health_score($error_states, $last_run)
        );
    }
    
    /**
     * Get scoring process health status
     */
    private function get_scoring_health() {
        if (class_exists('TradePress_Scoring_Process')) {
            $process = new TradePress_Scoring_Process();
            if (method_exists($process, 'get_health_status')) {
                return $process->get_health_status();
            }
        }
        
        // Fallback health check
        $error_states = get_option('tradepress_scoring_process_error_state', array());
        $last_run = get_option('tradepress_scoring_process_last_run', 0);
        
        return array(
            'status' => get_option('tradepress_scoring_process_status', 'stopped'),
            'last_run' => $last_run,
            'errors' => $error_states,
            'health_score' => $this->calculate_basic_health_score($error_states, $last_run)
        );
    }
    
    /**
     * Calculate basic health score for fallback
     */
    private function calculate_basic_health_score($error_states, $last_run) {
        $score = 100;
        
        // Deduct for errors
        foreach ($error_states as $error) {
            $score -= 15;
        }
        
        // Deduct for stale runs
        if ($last_run) {
            $hours_since_run = (current_time('timestamp') - $last_run) / 3600;
            if ($hours_since_run > 24) {
                $score -= 30;
            } elseif ($hours_since_run > 6) {
                $score -= 15;
            }
        } else {
            $score -= 40;
        }
        
        return max(0, $score);
    }
    
    /**
     * Calculate overall system health
     */
    private function calculate_overall_health($data_import_health, $scoring_health) {
        $data_score = isset($data_import_health['health_score']) ? $data_import_health['health_score'] : 0;
        $scoring_score = isset($scoring_health['health_score']) ? $scoring_health['health_score'] : 0;
        
        // Weighted average (data import is more critical)
        $overall_score = ($data_score * 0.6) + ($scoring_score * 0.4);
        
        $status = 'healthy';
        if ($overall_score < 30) {
            $status = 'critical';
        } elseif ($overall_score < 60) {
            $status = 'degraded';
        } elseif ($overall_score < 80) {
            $status = 'warning';
        }
        
        return array(
            'score' => round($overall_score),
            'status' => $status,
            'data_import_score' => $data_score,
            'scoring_score' => $scoring_score
        );
    }
    
    /**
     * AJAX handler for getting system health status
     */
    public function ajax_get_system_health() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $data_import_health = $this->get_data_import_health();
        $scoring_health = $this->get_scoring_health();
        $overall_health = $this->calculate_overall_health($data_import_health, $scoring_health);
        
        wp_send_json_success(array(
            'data_import' => $data_import_health,
            'scoring' => $scoring_health,
            'overall' => $overall_health,
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * AJAX handler for starting data import
     */
    public function ajax_start_data_import() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $import_type = isset($_POST['import_type']) ? sanitize_text_field($_POST['import_type']) : 'all';
        
        // Initialize data import process
        $data_import = new TradePress_Data_Import_Process();
        
        // Add items to queue based on import type
        switch ($import_type) {
            case 'earnings':
                $data_import->push_to_queue(array('action' => 'fetch_earnings'));
                break;
            case 'prices':
                $data_import->push_to_queue(array('action' => 'fetch_prices'));
                break;
            case 'market_status':
                $data_import->push_to_queue(array('action' => 'fetch_market_status'));
                break;
            default:
                $data_import->push_to_queue(array('action' => 'fetch_earnings'));
                $data_import->push_to_queue(array('action' => 'fetch_prices'));
                $data_import->push_to_queue(array('action' => 'fetch_market_status'));
                break;
        }
        
        // Start the background process
        $data_import->save()->dispatch();
        
        // Update status
        update_option('tradepress_data_import_status', 'running');
        update_option('tradepress_data_import_start_time', current_time('timestamp'));
        
        wp_send_json_success(array(
            'message' => __('Data import started successfully', 'tradepress'),
            'status' => 'running'
        ));
    }
    
    /**
     * AJAX handler for stopping data import
     */
    public function ajax_stop_data_import() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        // Cancel the background process
        $data_import = new TradePress_Data_Import_Process();
        $data_import->cancel_process();
        
        // Update status
        update_option('tradepress_data_import_status', 'stopped');
        
        wp_send_json_success(array(
            'message' => __('Data import stopped successfully', 'tradepress'),
            'status' => 'stopped'
        ));
    }
    
    /**
     * AJAX handler for getting data import status
     */
    public function ajax_get_data_import_status() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $status = get_option('tradepress_data_import_status', 'stopped');
        $start_time = get_option('tradepress_data_import_start_time', 0);
        $last_run = get_option('tradepress_data_import_last_run', 0);
        
        // Calculate runtime
        $runtime = '00:00:00';
        if ($status === 'running' && $start_time) {
            $elapsed = current_time('timestamp') - $start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        // Get data freshness info
        $earnings_last_update = get_option('tradepress_earnings_last_update', 0);
        $market_status_last_update = get_option('tradepress_market_status_last_update', 0);
        
        wp_send_json_success(array(
            'status' => $status,
            'runtime' => $runtime,
            'last_run' => $last_run ? date('Y-m-d H:i:s', $last_run) : 'Never',
            'data_freshness' => array(
                'earnings' => $earnings_last_update ? date('Y-m-d H:i:s', $earnings_last_update) : 'Never',
                'market_status' => $market_status_last_update ? date('Y-m-d H:i:s', $market_status_last_update) : 'Never'
            )
        ));
    }
    
    /**
     * AJAX handler for starting scoring process
     */
    public function ajax_start_scoring_process() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $process_type = isset($_POST['process_type']) ? sanitize_text_field($_POST['process_type']) : 'all';
        
        // Initialize scoring process
        $scoring_process = new TradePress_Scoring_Process();
        
        // Add items to queue based on process type
        switch ($process_type) {
            case 'scores':
                $scoring_process->push_to_queue(array('action' => 'calculate_scores'));
                break;
            case 'signals':
                $scoring_process->push_to_queue(array('action' => 'generate_signals'));
                break;
            case 'rankings':
                $scoring_process->push_to_queue(array('action' => 'update_rankings'));
                break;
            default:
                $scoring_process->push_to_queue(array('action' => 'calculate_scores'));
                $scoring_process->push_to_queue(array('action' => 'generate_signals'));
                $scoring_process->push_to_queue(array('action' => 'update_rankings'));
                break;
        }
        
        // Start the background process
        $scoring_process->save()->dispatch();
        
        // Update status
        update_option('tradepress_scoring_process_status', 'running');
        update_option('tradepress_scoring_process_start_time', current_time('timestamp'));
        
        wp_send_json_success(array(
            'message' => __('Scoring process started successfully', 'tradepress'),
            'status' => 'running'
        ));
    }
    
    /**
     * AJAX handler for stopping scoring process
     */
    public function ajax_stop_scoring_process() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        // Cancel the background process
        $scoring_process = new TradePress_Scoring_Process();
        $scoring_process->cancel_process();
        
        // Update status
        update_option('tradepress_scoring_process_status', 'stopped');
        
        wp_send_json_success(array(
            'message' => __('Scoring process stopped successfully', 'tradepress'),
            'status' => 'stopped'
        ));
    }
    
    /**
     * AJAX handler for getting scoring process status
     */
    public function ajax_get_scoring_process_status() {
        check_ajax_referer('tradepress_automation_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
        }
        
        $status = get_option('tradepress_scoring_process_status', 'stopped');
        $start_time = get_option('tradepress_scoring_process_start_time', 0);
        $last_run = get_option('tradepress_scoring_process_last_run', 0);
        
        // Calculate runtime
        $runtime = '00:00:00';
        if ($status === 'running' && $start_time) {
            $elapsed = current_time('timestamp') - $start_time;
            $hours = floor($elapsed / 3600);
            $minutes = floor(($elapsed % 3600) / 60);
            $seconds = $elapsed % 60;
            $runtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        // Get processing metrics
        $symbols_processed = get_option('tradepress_scoring_symbols_processed', 0);
        $scores_generated = get_option('tradepress_scoring_scores_generated', 0);
        $signals_generated = get_option('tradepress_scoring_signals_generated', 0);
        $rankings_last_update = get_option('tradepress_rankings_last_update', 0);
        
        wp_send_json_success(array(
            'status' => $status,
            'runtime' => $runtime,
            'last_run' => $last_run ? date('Y-m-d H:i:s', $last_run) : 'Never',
            'metrics' => array(
                'symbols_processed' => $symbols_processed,
                'scores_generated' => $scores_generated,
                'signals_generated' => $signals_generated,
                'rankings_last_update' => $rankings_last_update ? date('Y-m-d H:i:s', $rankings_last_update) : 'Never'
            )
        ));
    }
}

// Initialize the controller
new TradePress_Admin_Automation_Controller();
