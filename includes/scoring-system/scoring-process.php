<?php
/**
 * TradePress Scoring Algorithm Background Process
 * 
 * Handles scoring algorithm processing in background using database data
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Process extends TradePress_Background_Processing {
    
    protected $action = 'scoring_algorithm';
    
    /**
     * Process queue item - handles all task types including WordPress posts
     */
    protected function task($item) {
        if (!isset($item['action'])) {
            return false;
        }
        
        switch ($item['action']) {
            case 'calculate_scores':
                return $this->calculate_symbol_scores($item);
            case 'generate_signals':
                return $this->generate_trading_signals($item);
            case 'update_rankings':
                return $this->update_symbol_rankings($item);
            case 'process_symbol_post':
                return $this->process_symbol_post($item);
            default:
                return false;
        }
    }
    
    /**
     * Calculate scores for symbols using database data
     */
    private function calculate_symbol_scores($item) {
        $retry_count = isset($item['retry_count']) ? $item['retry_count'] : 0;
        $max_retries = 3;
        
        try {
            $symbols = isset($item['symbols']) ? $item['symbols'] : $this->get_active_symbols();
            
            if (empty($symbols)) {
                $this->log_process_activity('warning', 'No symbols found for scoring');
                return false;
            }
            
            $this->log_process_activity('info', 'Starting symbol score calculation', array(
                'symbol_count' => count($symbols),
                'retry' => $retry_count
            ));
            
            // Check if scoring algorithm class exists
            if (!class_exists('TradePress_Scoring_Algorithm')) {
                $this->log_process_activity('error', 'TradePress_Scoring_Algorithm class not found');
                $this->set_error_state('scoring_algorithm_missing', array(
                    'error' => 'Scoring algorithm class not available',
                    'status' => 'failed'
                ));
                return false;
            }
            
            $scoring_algorithm = new TradePress_Scoring_Algorithm();
            $processed_count = 0;
            $failed_count = 0;
            $stale_data_count = 0;
            
            foreach ($symbols as $symbol_code) {
                try {
                    // Get symbol data from database (not API)
                    $symbol_data = $this->get_symbol_data_from_db($symbol_code);
                    
                    if (!$symbol_data) {
                        $stale_data_count++;
                        $this->log_process_activity('warning', "No data available for symbol: {$symbol_code}");
                        continue;
                    }
                    
                    // Check data freshness
                    if ($this->is_data_stale($symbol_data)) {
                        $stale_data_count++;
                        $this->log_process_activity('warning', "Stale data for symbol: {$symbol_code}");
                    }
                    
                    // Calculate score using existing algorithm
                    $score = $scoring_algorithm->calculate_symbol_score($symbol_code, $symbol_data);
                    
                    if ($score !== false) {
                        // Store score in database
                        $this->store_symbol_score($symbol_code, $score);
                        $processed_count++;
                    } else {
                        $failed_count++;
                        $this->log_process_activity('warning', "Failed to calculate score for: {$symbol_code}");
                    }
                    
                } catch (Exception $symbol_error) {
                    $failed_count++;
                    $this->log_process_activity('error', "Exception processing symbol {$symbol_code}", array(
                        'error' => $symbol_error->getMessage()
                    ));
                }
                
                // Prevent timeout
                if ($processed_count >= 20) {
                    break;
                }
            }
            
            // Update metrics
            $total_processed = get_option('tradepress_scoring_symbols_processed', 0) + $processed_count;
            $total_scores = get_option('tradepress_scoring_scores_generated', 0) + $processed_count;
            
            update_option('tradepress_scoring_symbols_processed', $total_processed);
            update_option('tradepress_scoring_scores_generated', $total_scores);
            
            // Log results
            $this->log_process_activity('info', 'Score calculation completed', array(
                'processed' => $processed_count,
                'failed' => $failed_count,
                'stale_data' => $stale_data_count
            ));
            
            // Clear error state if mostly successful
            if ($processed_count > $failed_count) {
                delete_option('tradepress_scoring_process_error_state');
            } else if ($failed_count > 0) {
                $this->set_error_state('scoring_failures', array(
                    'processed' => $processed_count,
                    'failed' => $failed_count,
                    'status' => 'degraded'
                ));
            }
            
            return false; // Task completed
            
        } catch (Exception $e) {
            $this->log_process_activity('error', 'Exception in score calculation', array(
                'error' => $e->getMessage(),
                'retry' => $retry_count
            ));
            
            return $this->handle_retry($item, $retry_count, $max_retries, 'scoring_exception');
        }
    }
    
    /**
     * Generate trading signals based on scores
     */
    private function generate_trading_signals($item) {
        try {
            // Get top scored symbols
            $top_symbols = $this->get_top_scored_symbols(10);
            
            if (empty($top_symbols)) {
                return false;
            }
            
            $signals_generated = 0;
            
            foreach ($top_symbols as $symbol_data) {
                $signal = $this->evaluate_trading_signal($symbol_data);
                
                if ($signal) {
                    $this->store_trading_signal($signal);
                    $signals_generated++;
                }
            }
            
            // Update metrics
            $total_signals = get_option('tradepress_trade_signals', 0) + $signals_generated;
            update_option('tradepress_trade_signals', $total_signals);
            
            return false; // Task completed
            
        } catch (Exception $e) {
            error_log('TradePress Signal Generation Error: ' . $e->getMessage());
            return $item;
        }
    }
    
    /**
     * Update symbol rankings based on scores
     */
    private function update_symbol_rankings($item) {
        try {
            // Get all symbols with scores
            $symbols_with_scores = $this->get_symbols_with_scores();
            
            if (empty($symbols_with_scores)) {
                return false;
            }
            
            // Sort by score descending
            usort($symbols_with_scores, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            // Update rankings
            foreach ($symbols_with_scores as $index => $symbol_data) {
                $this->update_symbol_ranking($symbol_data['symbol'], $index + 1);
            }
            
            // Store ranking update timestamp
            update_option('tradepress_rankings_last_update', current_time('timestamp'));
            
            return false; // Task completed
            
        } catch (Exception $e) {
            error_log('TradePress Ranking Update Error: ' . $e->getMessage());
            return $item;
        }
    }
    
    /**
     * Get symbol data from database (not API)
     */
    private function get_symbol_data_from_db($symbol_code) {
        // Get price data from options (populated by data import process)
        $price_data = get_option("tradepress_price_data_{$symbol_code}");
        
        // Get earnings data
        $earnings_data = get_option('tradepress_earnings_data', array());
        
        // Get market status
        $market_status = get_option('tradepress_market_status');
        
        if (!$price_data) {
            return false;
        }
        
        return array(
            'symbol' => $symbol_code,
            'price_data' => $price_data,
            'earnings_data' => $earnings_data,
            'market_status' => $market_status,
            'timestamp' => current_time('timestamp')
        );
    }
    
    /**
     * Store calculated score in database
     */
    private function store_symbol_score($symbol_code, $score) {
        update_option("tradepress_symbol_score_{$symbol_code}", array(
            'score' => $score,
            'timestamp' => current_time('timestamp'),
            'calculated_by' => 'background_process'
        ));
    }
    
    /**
     * Get active symbols for processing
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
     * Get top scored symbols
     */
    private function get_top_scored_symbols($limit = 10) {
        // Implementation would query stored scores
        return array(); // Placeholder
    }
    
    /**
     * Evaluate trading signal for symbol
     */
    private function evaluate_trading_signal($symbol_data) {
        // Implementation would evaluate signal based on score and other factors
        return false; // Placeholder
    }
    
    /**
     * Store trading signal
     */
    private function store_trading_signal($signal) {
        // Implementation would store signal in database
    }
    
    /**
     * Get symbols with scores
     */
    private function get_symbols_with_scores() {
        // Implementation would query all symbols with scores
        return array(); // Placeholder
    }
    
    /**
     * Update symbol ranking
     */
    private function update_symbol_ranking($symbol_code, $ranking) {
        update_option("tradepress_symbol_ranking_{$symbol_code}", $ranking);
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
            $logger->log($level, $message, TradePress_Logger::CAT_ALGORITHM, $context);
        } else {
            error_log("TradePress Scoring [{$level}]: {$message}");
        }
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
        $error_states = get_option('tradepress_scoring_process_error_state', array());
        $error_states[$error_type] = array_merge($error_data, array(
            'timestamp' => current_time('mysql'),
            'process' => 'scoring_algorithm'
        ));
        update_option('tradepress_scoring_process_error_state', $error_states);
    }
    
    /**
     * Check if symbol data is stale
     */
    private function is_data_stale($symbol_data) {
        if (!isset($symbol_data['timestamp'])) {
            return true;
        }
        
        $data_age = current_time('timestamp') - $symbol_data['timestamp'];
        return $data_age > (6 * HOUR_IN_SECONDS); // Consider stale after 6 hours
    }
    
    /**
     * Get process health status
     */
    public function get_health_status() {
        $error_states = get_option('tradepress_scoring_process_error_state', array());
        $last_run = get_option('tradepress_scoring_process_last_run', 0);
        $status = get_option('tradepress_scoring_process_status', 'stopped');
        
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
            if (isset($error['status'])) {
                switch ($error['status']) {
                    case 'failed':
                        $score -= 25;
                        break;
                    case 'degraded':
                        $score -= 15;
                        break;
                    default:
                        $score -= 10;
                }
            }
        }
        
        // Deduct points for stale processing
        if ($last_run) {
            $hours_since_run = (current_time('timestamp') - $last_run) / 3600;
            if ($hours_since_run > 12) {
                $score -= 20;
            } elseif ($hours_since_run > 4) {
                $score -= 10;
            }
        } else {
            $score -= 40; // Never run
        }
        
        return max(0, $score);
    }
    
    // =============================================================================
    // WORDPRESS POST INTEGRATION (from scoring-system version)
    // =============================================================================
    
    /**
     * Process WordPress symbol posts with scoring
     */
    public function process_symbol_posts($symbols = array()) {
        // If no symbols provided, get all published symbols
        if (empty($symbols)) {
            $args = array(
                'post_type' => 'symbol',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
            );
            
            $args = apply_filters('tradepress_scoring_symbols_query', $args);
            $symbols = get_posts($args);
        }
        
        // Shuffle to avoid processing same ones first each time
        shuffle($symbols);
        
        // Add each symbol to the queue
        foreach ($symbols as $symbol_id) {
            $this->push_to_queue(array(
                'action' => 'process_symbol_post',
                'symbol_id' => $symbol_id
            ));
        }
        
        $this->save()->dispatch();
    }
    
    /**
     * Process individual symbol post
     */
    private function process_symbol_post($item) {
        $symbol_id = $item['symbol_id'];
        
        try {
            // Get the symbol post
            $symbol = get_post($symbol_id);
            
            if (!$symbol || $symbol->post_type !== 'symbol') {
                $this->log_process_activity('warning', "Invalid symbol ID: {$symbol_id} - skipping");
                return false;
            }
            
            // Get symbol code from post meta
            $symbol_code = get_post_meta($symbol_id, 'symbol_code', true);
            if (!$symbol_code) {
                $symbol_code = $symbol->post_title; // Fallback to title
            }
            
            // Calculate score using algorithm
            if (class_exists('TradePress_Scoring_Algorithm')) {
                $algorithm = new TradePress_Scoring_Algorithm();
                $score = $algorithm->calculate_score($symbol_id);
                
                // Store score in post meta
                update_post_meta($symbol_id, '_tradepress_score', $score['total_score']);
                update_post_meta($symbol_id, '_tradepress_score_data', $score);
                update_post_meta($symbol_id, '_tradepress_score_updated', current_time('timestamp'));
                
                $this->log_process_activity('info', "Calculated score for {$symbol->post_title}: {$score['total_score']}", array(
                    'symbol_id' => $symbol_id,
                    'score_details' => $score
                ));
            }
            
            // Allow time between processing to avoid overload
            sleep(1);
            
            return false; // Remove from queue
            
        } catch (Exception $e) {
            $this->log_process_activity('error', "Error processing symbol post {$symbol_id}: {$e->getMessage()}");
            return $item; // Retry
        }
    }
    
    /**
     * Start process with WordPress posts
     */
    public function start_wordpress_process($symbols = array()) {
        $this->log_process_activity('info', 'Starting WordPress symbol post processing', array(
            'symbol_count' => count($symbols)
        ));
        
        update_option('tradepress_algorithm_status', 'running');
        update_option('tradepress_algorithm_start_time', current_time('timestamp'));
        
        $this->process_symbol_posts($symbols);
    }
    
    /**
     * Handle completion with WordPress integration
     */
    public function handle_wordpress_completion() {
        // Notify admin if enabled
        if (get_option('tradepress_notify_on_completion', false)) {
            $admin_email = get_option('admin_email');
            $subject = __('TradePress: Scoring Process Completed', 'tradepress');
            $message = __('The TradePress scoring algorithm has finished processing all symbols.', 'tradepress');
            
            wp_mail($admin_email, $subject, $message);
        }
        
        // Fire completion action
        do_action('tradepress_scoring_process_complete');
    }
    
    /**
     * Complete processing
     */
    protected function complete() {
        parent::complete();
        
        // Log completion
        $this->log_process_activity('info', 'Scoring algorithm process completed successfully');
        
        // Update status
        update_option('tradepress_scoring_process_status', 'completed');
        update_option('tradepress_scoring_process_last_run', current_time('timestamp'));
        
        // Clear any temporary error states on successful completion
        $error_states = get_option('tradepress_scoring_process_error_state', array());
        foreach ($error_states as $key => $error) {
            if (!isset($error['status']) || $error['status'] !== 'failed') {
                unset($error_states[$key]);
            }
        }
        update_option('tradepress_scoring_process_error_state', $error_states);
        
        // Handle WordPress completion actions
        $this->handle_wordpress_completion();
    }
}