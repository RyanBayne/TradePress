<?php
/**
 * TradePress Automation: Admin Interface
 *
 * Handles the admin interface elements and AJAX endpoints for controlling
 * the TradePress automation system.  This includes managing the dashboard,
 * settings, and schedule.
 *
 * @package TradePress/Admin/Automation
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Admin_Automation {
    /**
     * Logger instance
     *
     * @var TradePress_Logger
     */
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize logger
        $this->logger = new TradePress_Logger();
        
        // Add admin menu item
        add_action('admin_menu', array($this, 'add_menu_item'));
        
        // Register scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_tradepress_refresh_diagnostics', array($this, 'ajax_refresh_diagnostics'));
        add_action('wp_ajax_tradepress_start_algorithm', array($this, 'ajax_start_algorithm'));
        add_action('wp_ajax_tradepress_stop_algorithm', array($this, 'ajax_stop_algorithm'));
        add_action('wp_ajax_tradepress_save_schedule', array($this, 'ajax_save_schedule'));
        
        // Add custom cron schedules
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));
        
        // Register cron hooks
        add_action('tradepress_run_algorithm', array($this, 'cron_run_algorithm'));
    }
    
    /**
     * Add admin menu item
     */
    public function add_menu_item() {
        $automation_page = add_submenu_page(
            'edit.php?post_type=symbol',
            __('Automation', 'tradepress'),
            __('Automation', 'tradepress'),
            'manage_options',
            'tradepress_automation', // Changed from tradepress-automation to match your URL
            array($this, 'render_automation_page')
        );
        
        // Load scripts only on our page
        add_action('load-' . $automation_page, array($this, 'load_scripts'));
    }
    
    /**
     * Register scripts and styles
     */
    public function register_scripts() {
        wp_register_style(
            'tradepress-admin-automation',
            TRADEPRESS_PLUGIN_URL . '/assets/css/admin-automation.css',
            array('wp-components'),
            TRADEPRESS_VERSION
        );
        
        wp_register_script(
            'tradepress-admin-automation',
            TRADEPRESS_PLUGIN_URL . '/assets/js/admin-automation.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
    }
    
    /**
     * Load scripts for the automation page
     */
    public function load_scripts() {
        wp_enqueue_style('tradepress-admin-automation');
        wp_enqueue_script('tradepress-admin-automation');
        
        // Add data for JavaScript
        wp_localize_script('tradepress-admin-automation', 'tradepressAutomation', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonces' => array(
                'diagnostics' => wp_create_nonce('tradepress_diagnostics'),
                'algorithm_control' => wp_create_nonce('tradepress_algorithm_control'),
                'save_schedule' => wp_create_nonce('tradepress_save_schedule')
            ),
            'strings' => array(
                'running' => __('Running', 'tradepress'),
                'stopping' => __('Stopping...', 'tradepress'),
                'idle' => __('Idle', 'tradepress'),
                'error_refresh' => __('Error refreshing diagnostic data', 'tradepress'),
                'no_logs' => __('No log entries found', 'tradepress'),
                'no_api_calls' => __('No API calls recorded', 'tradepress')
            )
        ));
    }
    
    /**
     * Render the automation admin page
     */
    public function render_automation_page() {
        include_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/views/automation-dashboard.php');
    }
    
    /**
     * Add custom cron schedules
     *
     * @param array $schedules Existing cron schedules
     * @return array Modified cron schedules
     */
    public function add_cron_schedules($schedules) {
        // Add a 15-minute schedule
        $schedules['tradepress_15min'] = array(
            'interval' => 15 * 60,
            'display' => __('Every 15 minutes', 'tradepress')
        );
        
        // Add a market-hours schedule (US market)
        $schedules['tradepress_market_hours'] = array(
            'interval' => 60 * 60, // 1 hour, but we'll check market hours before running
            'display' => __('During Market Hours', 'tradepress')
        );
        
        return $schedules;
    }
    
    /**
     * Cron callback to run the algorithm
     */
    public function cron_run_algorithm() {
        $this->logger->info('Cron job triggered algorithm run', TradePress_Logger::CAT_ALGORITHM);
        
        // Check if we should run (market hours, etc.)
        if (!$this->should_run_algorithm()) {
            $this->logger->info('Algorithm run skipped (outside configured hours)', TradePress_Logger::CAT_ALGORITHM);
            return;
        }
        
        // Set status to running
        update_option('tradepress_algorithm_status', 'running');
        update_option('tradepress_algorithm_start_time', time());
        
        // Run the algorithm
        $this->run_algorithm_process();
        
        // Update last run time
        update_option('tradepress_algorithm_last_run', time());
        
        // Set status back to idle
        update_option('tradepress_algorithm_status', 'idle');
    }
    
    /**
     * Check if the algorithm should run based on schedule settings
     *
     * @return bool Whether the algorithm should run
     */
    private function should_run_algorithm() {
        $schedule_type = get_option('tradepress_schedule_type', 'manual');
        
        // Always run for hourly, daily (because we've scheduled it appropriately)
        if (in_array($schedule_type, array('hourly', 'daily', 'custom'))) {
            return true;
        }
        
        // For market hours, check if we're in market hours
        if ($schedule_type === 'market_hours') {
            $nyse_enabled = get_option('tradepress_market_hours_nyse', true);
            $lse_enabled = get_option('tradepress_market_hours_lse', false);
            
            // Get current time in New York (EST/EDT)
            $ny_time = new DateTime('now', new DateTimeZone('America/New_York'));
            $ny_hours = (int)$ny_time->format('G');
            $ny_minutes = (int)$ny_time->format('i');
            $ny_day = (int)$ny_time->format('N'); // 1 (Monday) to 7 (Sunday)
            
            // Get current time in London (GMT/BST)
            $london_time = new DateTime('now', new DateTimeZone('Europe/London'));
            $london_hours = (int)$london_time->format('G');
            $london_minutes = (int)$london_time->format('i');
            $london_day = (int)$london_time->format('N'); // 1 (Monday) to 7 (Sunday)
            
            // NYSE hours: 9:30 AM - 4:00 PM ET, Monday-Friday
            $is_nyse_hours = $nyse_enabled && 
                            $ny_day >= 1 && $ny_day <= 5 && // Monday-Friday
                            (($ny_hours > 9 || ($ny_hours == 9 && $ny_minutes >= 30)) && $ny_hours < 16);
            
            // LSE hours: 8:00 AM - 4:30 PM GMT, Monday-Friday
            $is_lse_hours = $lse_enabled && 
                           $london_day >= 1 && $london_day <= 5 && // Monday-Friday
                           (($london_hours >= 8) && ($london_hours < 16 || ($london_hours == 16 && $london_minutes <= 30)));
            
            return $is_nyse_hours || $is_lse_hours;
        }
        
        // Default to not running
        return false;
    }
    
    /**
     * AJAX handler for refreshing diagnostic information
     */
    public function ajax_refresh_diagnostics() {
        check_ajax_referer('tradepress_diagnostics', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
            return;
        }
        
        // Get fresh diagnostic data
        $diagnostic_data = $this->get_diagnostic_data();
        
        wp_send_json_success($diagnostic_data);
    }
    
    /**
     * AJAX handler for starting the algorithm
     */
    public function ajax_start_algorithm() {
        check_ajax_referer('tradepress_algorithm_control', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
            return;
        }
        
        // Update algorithm status
        update_option('tradepress_algorithm_status', 'running');
        update_option('tradepress_algorithm_start_time', time());
        
        // Log the event
        $this->logger->info('Algorithm started manually', TradePress_Logger::CAT_ALGORITHM);
        
        // Start the algorithm process
        $this->run_algorithm_process();
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler for stopping the algorithm
     */
    public function ajax_stop_algorithm() {
        check_ajax_referer('tradepress_algorithm_control', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
            return;
        }
        
        // Update algorithm status
        update_option('tradepress_algorithm_status', 'idle');
        
        // Calculate runtime
        $start_time = get_option('tradepress_algorithm_start_time', 0);
        $runtime = time() - $start_time;
        
        // Update last run time
        update_option('tradepress_algorithm_last_run', time());
        
        // Log the event
        $this->logger->info('Algorithm stopped manually', TradePress_Logger::CAT_ALGORITHM, array(
            'runtime' => $runtime
        ));
        
        // Stop the algorithm process
        $this->stop_algorithm_process();
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler for saving the schedule
     */
    public function ajax_save_schedule() {
        check_ajax_referer('tradepress_save_schedule', 'security');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'tradepress')));
            return;
        }
        
        // Get and sanitize schedule data
        $schedule_type = isset($_POST['schedule_type']) ? sanitize_text_field($_POST['schedule_type']) : 'manual';
        
        // Save basic schedule settings
        update_option('tradepress_schedule_type', $schedule_type);
        
        // Handle specific schedule types
        switch ($schedule_type) {
            case 'market_hours':
                $nyse_enabled = isset($_POST['market_hours_nyse']) && $_POST['market_hours_nyse'] == '1';
                $lse_enabled = isset($_POST['market_hours_lse']) && $_POST['market_hours_lse'] == '1';
                
                update_option('tradepress_market_hours_nyse', $nyse_enabled);
                update_option('tradepress_market_hours_lse', $lse_enabled);
                
                // Schedule or clear the market hours cron
                $this->update_cron_schedule('tradepress_market_hours');
                break;
                
            case 'hourly':
                // Schedule or clear the hourly cron
                $this->update_cron_schedule('hourly');
                break;
                
            case 'daily':
                $daily_time = isset($_POST['daily_time']) ? sanitize_text_field($_POST['daily_time']) : '18:00';
                update_option('tradepress_daily_time', $daily_time);
                
                // Schedule the daily cron at the specified time
                $this->update_cron_schedule('daily', $daily_time);
                break;
                
            case 'custom':
                // Custom schedule handling would go here
                break;
                
            case 'manual':
            default:
                // Clear all cron jobs for manual mode
                $this->clear_cron_schedules();
                break;
        }
        
        // Log the schedule change
        $this->logger->info('Algorithm schedule updated', TradePress_Logger::CAT_ALGORITHM, array(
            'schedule_type' => $schedule_type
        ));
        
        wp_send_json_success(array(
            'message' => __('Schedule saved successfully', 'tradepress')
        ));
    }
    
    /**
     * Update the cron schedule for the algorithm
     *
     * @param string $schedule Schedule type ('hourly', 'daily', 'tradepress_market_hours', etc.)
     * @param string $time Optional time for daily schedule (format: 'HH:MM')
     */
    private function update_cron_schedule($schedule, $time = null) {
        // Clear existing schedules
        $this->clear_cron_schedules();
        
        // If time is provided for daily schedule, calculate the timestamp
        if ($schedule === 'daily' && $time !== null) {
            list($hours, $minutes) = explode(':', $time);
            
            // Create a timestamp for today at the specified time
            $timestamp = strtotime(date('Y-m-d') . ' ' . $hours . ':' . $minutes . ':00');
            
            // If the time has already passed today, schedule for tomorrow
            if ($timestamp < time()) {
                $timestamp = strtotime('+1 day', $timestamp);
            }
        } else {
            // For other schedules, start as soon as possible
            $timestamp = time();
        }
        
        // Schedule the event
        wp_schedule_event($timestamp, $schedule, 'tradepress_run_algorithm');
    }
    
    /**
     * Clear all algorithm cron schedules
     */
    private function clear_cron_schedules() {
        $timestamp = wp_next_scheduled('tradepress_run_algorithm');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'tradepress_run_algorithm');
        }
    }
    
    /**
     * Run the algorithm process
     * 
     * This is a placeholder method that would actually run your algorithm
     * in a real implementation
     */
    private function run_algorithm_process() {
        // Log the start of the algorithm
        $this->logger->info('Algorithm process started', TradePress_Logger::CAT_ALGORITHM);
        
        // This would be where you actually run your algorithm
        // For example:
        // 1. Get symbols to analyze
        // 2. Fetch data for each symbol
        // 3. Calculate scores
        // 4. Save results
        
        // In a real implementation, you might use an asynchronous process
        // or run the algorithm in small batches to avoid timeouts
        
        // For demonstration purposes, let's simulate some activity
        $this->simulate_algorithm_activity();
        
        // Update last run time
        update_option('tradepress_algorithm_last_run', time());
        
        // Log completion
        $this->logger->info('Algorithm process completed', TradePress_Logger::CAT_ALGORITHM);
        
        // Set status to idle
        update_option('tradepress_algorithm_status', 'idle');
    }
    
    /**
     * Stop the algorithm process
     * 
     * This is a placeholder method that would actually stop your algorithm
     * in a real implementation
     */
    private function stop_algorithm_process() {
        // Log the stopping of the algorithm
        $this->logger->info('Algorithm process stopping', TradePress_Logger::CAT_ALGORITHM);
        
        // This would be where you actually stop your algorithm
        // For example:
        // 1. Set a flag in the database
        // 2. Kill any running background processes
        // 3. Clean up any temporary data
    }
    
    /**
     * Simulate algorithm activity for demonstration purposes
     * 
     * This is only used for testing and would not be part of a real implementation
     */
    private function simulate_algorithm_activity() {
        // Log some sample messages
        $this->logger->info('Initializing algorithm run', TradePress_Logger::CAT_ALGORITHM);
        $this->logger->debug('Loading symbol list', TradePress_Logger::CAT_ALGORITHM);
        
        // Simulate API calls
        $this->logger->info('Fetching data for AAPL', TradePress_Logger::CAT_API);
        $this->logger->debug('API response received for AAPL', TradePress_Logger::CAT_API);
        
        // Simulate some warnings
        $this->logger->warning('Rate limit approaching for AlphaVantage', TradePress_Logger::CAT_API);
        
        // Simulate calculation
        $this->logger->debug('Calculating RSI for MSFT', TradePress_Logger::CAT_ALGORITHM);
        $this->logger->debug('Calculating MACD for MSFT', TradePress_Logger::CAT_ALGORITHM);
        
        // Simulate an error
        $this->logger->error('Failed to retrieve data for GOOG', TradePress_Logger::CAT_API, array(
            'error_code' => 429,
            'error_message' => 'Too Many Requests'
        ));
        
        // Simulate results
        $this->logger->info('Scored 156 symbols successfully', TradePress_Logger::CAT_ALGORITHM);
        $this->logger->info('Top scoring symbol: AAPL (score: 87.5)', TradePress_Logger::CAT_ALGORITHM);
    }
    
    /**
     * Get diagnostic data for the dashboard
     *
     * @return array Diagnostic data
     */
    private function get_diagnostic_data() {
        // Get log entries
        $logs = $this->logger->get_log_entries(array(
            'number' => 20,
            'orderby' => 'timestamp',
            'order' => 'DESC'
        ));
        
        // Format logs for JSON response
        $formatted_logs = array();
        foreach ($logs as $log) {
            $formatted_logs[] = array(
                'timestamp' => date_i18n('Y-m-d H:i:s', strtotime($log['timestamp'])),
                'level' => $log['level'],
                'category' => $log['category'],
                'message' => $log['message']
            );
        }
        
        // In a real implementation, these would be actual metrics from your algorithm runs
        $metrics = array(
            'execution_time' => array(
                'current' => '1.2s',
                'average' => '1.5s',
                'peak' => '3.2s'
            ),
            'memory_usage' => array(
                'current' => '32MB',
                'average' => '28MB',
                'peak' => '45MB'
            ),
            'database_queries' => array(
                'current' => '24',
                'average' => '20',
                'peak' => '35'
            ),
            'api_calls' => array(
                'current' => '12',
                'average' => '10',
                'peak' => '18'
            ),
            'algorithm' => array(
                'symbols_processed' => '156',
                'directives_active' => '8',
                'highest_score' => '87.5',
                'average_score' => '42.3'
            )
        );
        
        // In a real implementation, this would track actual API calls
        $api_calls = array(
            array(
                'timestamp' => date_i18n('Y-m-d H:i:s', time() - 60),
                'api' => 'AlphaVantage',
                'endpoint' => 'TIME_SERIES_DAILY',
                'status' => 'Success',
                'duration' => '345'
            ),
            array(
                'timestamp' => date_i18n('Y-m-d H:i:s', time() - 120),
                'api' => 'AlphaVantage',
                'endpoint' => 'GLOBAL_QUOTE',
                'status' => 'Success',
                'duration' => '212'
            ),
            array(
                'timestamp' => date_i18n('Y-m-d H:i:s', time() - 180),
                'api' => 'Trading212',
                'endpoint' => 'instruments',
                'status' => 'Success',
                'duration' => '156'
            ),
            array(
                'timestamp' => date_i18n('Y-m-d H:i:s', time() - 240),
                'api' => 'AlphaVantage',
                'endpoint' => 'TIME_SERIES_INTRADAY',
                'status' => 'Error (429)',
                'duration' => '543'
            )
        );
        
        return array(
            'logs' => $formatted_logs,
            'metrics' => $metrics,
            'api_calls' => $api_calls
        );
    }
}

// Initialize the admin automation class
$tradepress_admin_automation = new TradePress_Admin_Automation();
