<?php
/**
 * TradePress Data Import Background Process
 * 
 * Handles API data import in background to avoid AJAX timeout issues
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Data_Import_Process extends TradePress_Background_Processing {
    
    protected $action = 'data_import';
    
    /**
     * Process queue item
     */
    protected function task($item) {
        // Load queue schema
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';
        
        if (!isset($item['action'])) {
            return false;
        }
        
        switch ($item['action']) {
            case 'fetch_earnings':
                return $this->fetch_earnings_data($item);
            case 'fetch_prices':
                return $this->fetch_price_data($item);
            case 'fetch_market_status':
                return $this->fetch_market_status($item);
            case 'fetch_technical_data':
                return $this->fetch_technical_data($item);
            default:
                return false;
        }
    }
    
    /**
     * Add item to queue instead of processing immediately
     */
    public static function queue_data_fetch($action, $data = array(), $priority = 10) {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';
        
        $item_data = array_merge($data, array('action' => $action));
        
        return TradePress_Queue_Schema::add_item(
            'data_import',
            $action,
            $item_data,
            $priority
        );
    }
    
    /**
     * Fetch earnings calendar data
     */
    private function fetch_earnings_data($item) {
        $retry_count = isset($item['retry_count']) ? $item['retry_count'] : 0;
        $max_retries = 3;
        
        try {
            // Log attempt
            $this->log_process_activity('info', 'Attempting to fetch earnings data', array('retry' => $retry_count));
            
            // Get Alpha Vantage API instance
            $alpha_vantage = new TradePress_Alpha_Vantage_API();
            
            // Fetch earnings calendar
            $earnings_data = $alpha_vantage->get_earnings_calendar();
            
            if ($earnings_data && !is_wp_error($earnings_data)) {
                // Store in database
                update_option('tradepress_earnings_data', $earnings_data);
                update_option('tradepress_earnings_last_update', current_time('timestamp'));
                
                // Clear any previous error state
                delete_option('tradepress_data_import_error_state');
                
                // Log success
                $this->log_process_activity('info', 'Earnings data imported successfully', array('data_count' => count($earnings_data)));
                return false; // Task completed
            }
            
            // Handle API errors
            if (is_wp_error($earnings_data)) {
                $this->handle_api_error('earnings', $earnings_data, $retry_count, $max_retries);
            }
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'earnings_fetch_failed');
            
        } catch (Exception $e) {
            $this->log_process_activity('error', 'Exception in earnings data fetch', array(
                'error' => $e->getMessage(),
                'retry' => $retry_count
            ));
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'earnings_exception');
        }
    }
    
    /**
     * Fetch price data for symbols
     */
    private function fetch_price_data($item) {
        $retry_count = isset($item['retry_count']) ? $item['retry_count'] : 0;
        $max_retries = 3;
        
        try {
            $symbols = isset($item['symbols']) ? $item['symbols'] : $this->get_active_symbols();
            
            if (empty($symbols)) {
                $this->log_process_activity('warning', 'No symbols found for price data fetch');
                return false;
            }
            
            $this->log_process_activity('info', 'Fetching price data', array('symbol_count' => count($symbols), 'retry' => $retry_count));
            
            // Use existing API Factory for multi-provider support
            require_once TRADEPRESS_PLUGIN_DIR . 'api/api-factory.php';
            require_once TRADEPRESS_PLUGIN_DIR . 'includes/data-freshness-manager.php';
            
            $successful_updates = 0;
            $failed_updates = 0;
            
            foreach ($symbols as $symbol) {
                try {
                    // Use Data Freshness Manager for coordinated data updates
                    $freshness_result = TradePress_Data_Freshness_Manager::ensure_data_freshness(
                        $symbol, 
                        'scoring_algorithms', 
                        array('price_data', 'technical_indicators'),
                        true // Force update for background process
                    );
                    
                    if (!empty($freshness_result['update_results']['updates'])) {
                        $successful_updates++;
                        
                        // Also fetch historical data if Finnhub is available
                        $this->fetch_historical_data($symbol);
                        
                    } else {
                        $failed_updates++;
                        $this->log_process_activity('warning', "Failed to fetch price for symbol: {$symbol}");
                    }
                    
                    // Respect API rate limits
                    sleep(1);
                    
                } catch (Exception $symbol_error) {
                    $failed_updates++;
                    $this->log_process_activity('error', "Exception fetching price for {$symbol}", array('error' => $symbol_error->getMessage()));
                }
            }
            
            // Log results
            $this->log_process_activity('info', 'Price data fetch completed', array(
                'successful' => $successful_updates,
                'failed' => $failed_updates
            ));
            
            // Clear error state if mostly successful
            if ($successful_updates > $failed_updates) {
                delete_option('tradepress_data_import_error_state');
            }
            
            return false; // Task completed
            
        } catch (Exception $e) {
            $this->log_process_activity('error', 'Exception in price data fetch', array(
                'error' => $e->getMessage(),
                'retry' => $retry_count
            ));
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'price_fetch_exception');
        }
    }
    
    /**
     * Fetch historical data for advanced directive support
     */
    private function fetch_historical_data($symbol) {
        try {
            // Check if historical data is stale (older than 1 day)
            $last_update = get_option("tradepress_historical_last_update_{$symbol}", 0);
            if ((current_time('timestamp') - $last_update) < DAY_IN_SECONDS) {
                return; // Data is fresh
            }
            
            // Try Finnhub for historical data
            $finnhub_api = TradePress_API_Factory::create_from_settings('finnhub');
            
            if (!is_wp_error($finnhub_api)) {
                // Fetch historical candles
                $historical_data = $finnhub_api->get_candles($symbol, 'D', null, null, 200);
                
                if (!is_wp_error($historical_data) && !empty($historical_data)) {
                    update_option("tradepress_historical_data_{$symbol}", $historical_data);
                    update_option("tradepress_historical_last_update_{$symbol}", current_time('timestamp'));
                    
                    $this->log_process_activity('info', "Historical data updated for {$symbol}", array(
                        'data_points' => count($historical_data),
                        'provider' => 'finnhub'
                    ));
                }
                
                // Fetch technical indicators
                $rsi_data = $finnhub_api->get_rsi($symbol);
                $macd_data = $finnhub_api->get_macd($symbol);
                $ma_20 = $finnhub_api->get_moving_average($symbol, 20);
                $ma_50 = $finnhub_api->get_moving_average($symbol, 50);
                $ma_200 = $finnhub_api->get_moving_average($symbol, 200);
                
                $technical_indicators = array();
                if (!is_wp_error($rsi_data)) $technical_indicators['rsi'] = $rsi_data;
                if (!is_wp_error($macd_data)) $technical_indicators['macd'] = $macd_data;
                if (!is_wp_error($ma_20)) $technical_indicators['sma_20'] = $ma_20;
                if (!is_wp_error($ma_50)) $technical_indicators['sma_50'] = $ma_50;
                if (!is_wp_error($ma_200)) $technical_indicators['sma_200'] = $ma_200;
                
                if (!empty($technical_indicators)) {
                    update_option("tradepress_technical_indicators_{$symbol}", $technical_indicators);
                    update_option("tradepress_technical_last_update_{$symbol}", current_time('timestamp'));
                    
                    $this->log_process_activity('info', "Technical indicators updated for {$symbol}", array(
                        'indicators' => array_keys($technical_indicators)
                    ));
                }
            }
            
        } catch (Exception $e) {
            $this->log_process_activity('warning', "Failed to fetch historical data for {$symbol}", array(
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Fetch market status
     */
    private function fetch_market_status($item) {
        $retry_count = isset($item['retry_count']) ? $item['retry_count'] : 0;
        $max_retries = 3;
        
        try {
            $this->log_process_activity('info', 'Fetching market status', array('retry' => $retry_count));
            
            $alpha_vantage = new TradePress_Alpha_Vantage_API();
            $market_status = $alpha_vantage->get_market_status();
            
            if ($market_status && !is_wp_error($market_status)) {
                update_option('tradepress_market_status', $market_status);
                update_option('tradepress_market_status_last_update', current_time('timestamp'));
                
                // Clear error state
                delete_option('tradepress_data_import_error_state');
                
                $this->log_process_activity('info', 'Market status updated successfully');
                return false;
            }
            
            // Handle API errors
            if (is_wp_error($market_status)) {
                $this->handle_api_error('market_status', $market_status, $retry_count, $max_retries);
            }
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'market_status_failed');
            
        } catch (Exception $e) {
            $this->log_process_activity('error', 'Exception in market status fetch', array(
                'error' => $e->getMessage(),
                'retry' => $retry_count
            ));
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'market_status_exception');
        }
    }
    
    /**
     * Get active symbols for price updates
     */
    private function get_active_symbols() {
        $symbols = get_posts(array(
            'post_type' => 'symbols',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ));
        
        $symbol_codes = array();
        foreach ($symbols as $symbol_id) {
            $symbol_code = get_post_meta($symbol_id, 'symbol_code', true);
            if ($symbol_code) {
                $symbol_codes[] = $symbol_code;
            }
        }
        
        return $symbol_codes;
    }
    
    /**
     * Enhanced error handling methods
     */
    
    /**
     * Log process activity using TradePress Logger
     */
    private function log_process_activity($level, $message, $context = array()) {
        if (class_exists('TradePress_Logger')) {
            $logger = new TradePress_Logger();
            $logger->log($level, $message, TradePress_Logger::CAT_API, $context);
        } else {
            error_log("TradePress Data Import [{$level}]: {$message}");
        }
    }
    
    /**
     * Handle API errors with detailed logging
     */
    private function handle_api_error($endpoint, $wp_error, $retry_count, $max_retries) {
        $error_data = array(
            'endpoint' => $endpoint,
            'error_code' => $wp_error->get_error_code(),
            'error_message' => $wp_error->get_error_message(),
            'retry_count' => $retry_count,
            'max_retries' => $max_retries
        );
        
        // Log to API logging system
        if (class_exists('TradePress_API_Logging')) {
            $entry_id = TradePress_API_Logging::log_call('alphavantage', $endpoint, 'GET', 'error');
            TradePress_API_Logging::log_error($entry_id, $wp_error->get_error_code(), $wp_error->get_error_message());
        }
        
        // Set error state for UI display
        $this->set_error_state($endpoint, $error_data);
        
        $this->log_process_activity('error', "API error for {$endpoint}", $error_data);
    }
    
    /**
     * Handle retry logic with exponential backoff
     */
    private function handle_retry($item, $retry_count, $max_retries, $error_type) {
        if ($retry_count >= $max_retries) {
            $this->log_process_activity('critical', "Max retries exceeded for {$error_type}", array(
                'retry_count' => $retry_count,
                'max_retries' => $max_retries
            ));
            
            // Set permanent error state
            $this->set_error_state($error_type, array(
                'status' => 'failed',
                'retry_count' => $retry_count,
                'last_attempt' => current_time('mysql')
            ));
            
            return false; // Stop retrying
        }
        
        // Increment retry count and add exponential backoff delay
        $item['retry_count'] = $retry_count + 1;
        $item['retry_delay'] = min(300, pow(2, $retry_count) * 10); // Max 5 minutes
        
        $this->log_process_activity('warning', "Scheduling retry for {$error_type}", array(
            'retry_count' => $item['retry_count'],
            'delay' => $item['retry_delay']
        ));
        
        return $item; // Retry the task
    }
    
    /**
     * Set error state for UI display
     */
    private function set_error_state($error_type, $error_data) {
        $error_states = get_option('tradepress_data_import_error_state', array());
        $error_states[$error_type] = array_merge($error_data, array(
            'timestamp' => current_time('mysql'),
            'process' => 'data_import'
        ));
        update_option('tradepress_data_import_error_state', $error_states);
    }
    
    /**
     * Get process health status
     */
    public function get_health_status() {
        $error_states = get_option('tradepress_data_import_error_state', array());
        $last_run = get_option('tradepress_data_import_last_run', 0);
        $status = get_option('tradepress_data_import_status', 'stopped');
        
        $health = array(
            'status' => $status,
            'last_run' => $last_run,
            'errors' => $error_states,
            'health_score' => $this->calculate_health_score($error_states, $last_run)
        );
        
        return $health;
    }
    
    /**
     * Calculate health score (0-100)
     */
    private function calculate_health_score($error_states, $last_run) {
        $score = 100;
        
        // Deduct points for errors
        foreach ($error_states as $error) {
            if (isset($error['status']) && $error['status'] === 'failed') {
                $score -= 20;
            } else {
                $score -= 10;
            }
        }
        
        // Deduct points for stale data
        if ($last_run) {
            $hours_since_run = (current_time('timestamp') - $last_run) / 3600;
            if ($hours_since_run > 24) {
                $score -= 30;
            } elseif ($hours_since_run > 6) {
                $score -= 15;
            }
        } else {
            $score -= 50; // Never run
        }
        
        return max(0, $score);
    }
    
    /**
     * Fetch technical data for symbols
     */
    private function fetch_technical_data($item) {
        $retry_count = isset($item['retry_count']) ? $item['retry_count'] : 0;
        $max_retries = 3;
        
        try {
            $symbols = isset($item['symbols']) ? $item['symbols'] : $this->get_active_symbols();
            
            if (empty($symbols)) {
                $this->log_process_activity('warning', 'No symbols found for technical data fetch');
                return false;
            }
            
            $successful_updates = 0;
            $failed_updates = 0;
            
            foreach ($symbols as $symbol) {
                try {
                    $this->fetch_historical_data($symbol);
                    $successful_updates++;
                    
                    // Respect API rate limits
                    sleep(2);
                    
                } catch (Exception $symbol_error) {
                    $failed_updates++;
                    $this->log_process_activity('error', "Exception fetching technical data for {$symbol}", array('error' => $symbol_error->getMessage()));
                }
            }
            
            $this->log_process_activity('info', 'Technical data fetch completed', array(
                'successful' => $successful_updates,
                'failed' => $failed_updates
            ));
            
            return false;
            
        } catch (Exception $e) {
            $this->log_process_activity('error', 'Exception in technical data fetch', array(
                'error' => $e->getMessage(),
                'retry' => $retry_count
            ));
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'technical_fetch_exception');
        }
    }
    
    /**
     * Process queue items from database
     */
    public function process_queue_items() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';
        
        $max_items = 5; // Process up to 5 items per run
        $processed = 0;
        
        while ($processed < $max_items) {
            $item = TradePress_Queue_Schema::get_next_item('data_import');
            
            if (!$item) {
                break; // No more items
            }
            
            $item_data = json_decode($item->item_data, true);
            $result = $this->task($item_data);
            
            // Update queue item status
            global $wpdb;
            $table = $wpdb->prefix . 'tradepress_queue';
            
            if ($result === false) {
                // Task completed successfully
                $wpdb->update(
                    $table,
                    array(
                        'status' => 'completed',
                        'completed_at' => current_time('mysql')
                    ),
                    array('id' => $item->id)
                );
            } else {
                // Task needs retry
                $wpdb->update(
                    $table,
                    array(
                        'attempts' => $item->attempts + 1,
                        'scheduled_at' => date('Y-m-d H:i:s', strtotime('+' . ($item_data['retry_delay'] ?? 60) . ' seconds'))
                    ),
                    array('id' => $item->id)
                );
            }
            
            $processed++;
        }
        
        return $processed;
    }
    
    /**
     * Complete processing
     */
    protected function complete() {
        parent::complete();
        
        // Log completion
        $this->log_process_activity('info', 'Data import process completed successfully');
        
        // Update status
        update_option('tradepress_data_import_status', 'completed');
        update_option('tradepress_data_import_last_run', current_time('timestamp'));
        
        // Clear any temporary error states on successful completion
        $error_states = get_option('tradepress_data_import_error_state', array());
        foreach ($error_states as $key => $error) {
            if (!isset($error['status']) || $error['status'] !== 'failed') {
                unset($error_states[$key]);
            }
        }
        update_option('tradepress_data_import_error_state', $error_states);
    }
}