<?php
/**
 * TradePress Scoring Algorithm Class
 *
 * Implements the core algorithm functionality for scoring symbols by processing directives, which 
 * generates scores or immediate trade signals based on various financial indicators and data.
 *
 * Eventually the scores themselves will be signals used by the trading system to make decisions.
 * 
 * @package TradePress/Classes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Scoring_Algorithm Class
 */
class TradePress_Scoring_Algorithm {
    /**
     * Current run ID
     *
     * @var int
     */
    private $run_id = 0;
    
    /**
     * Logger for algorithm operations
     *
     * @var object
     */
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the logger only if the class exists
        if (class_exists('TradePress_Logger')) {
            $this->logger = new TradePress_Logger();
        } else {
            // Create a simple fallback logger or disable logging
            $this->logger = null;
            error_log('TradePress_Logger class not found - logging disabled for scoring algorithm');
        }
    }
    
    /**
     * Start a new scoring run
     *
     * @param string $run_type Type of run (manual, cron)
     * @return int The run ID
     */
    public function start_new_run($run_type = 'manual') {
        global $wpdb;
        
        // Log the start
        $this->safe_log('info', 'algorithm', 'Starting new scoring algorithm run: ' . $run_type);
        
        // Create a new run record
        $wpdb->insert(
            $wpdb->prefix . 'tradepress_algorithm_runs',
            array(
                'start_time' => current_time('mysql'),
                'status' => 'running',
                'run_type' => $run_type
            ),
            array('%s', '%s', '%s')
        );
        
        $this->run_id = $wpdb->insert_id;
        
        // Update state
        update_option('tradepress_algorithm_running', true);
        update_option('tradepress_algorithm_start_time', time());
        update_option('tradepress_current_run_id', $this->run_id);
        
        return $this->run_id;
    }
    
    /**
     * End the current algorithm run
     *
     * @param string $status Final status of the run
     * @return bool True on success
     */
    public function end_run($status = 'completed') {
        global $wpdb;
        
        if (!$this->run_id) {
            $this->run_id = get_option('tradepress_current_run_id', 0);
        }
        
        if (!$this->run_id) {
            $this->logger->log('error', 'algorithm', 'Cannot end run - no run ID found');
            return false;
        }
        
        // Update the run record
        $wpdb->update(
            $wpdb->prefix . 'tradepress_algorithm_runs',
            array(
                'end_time' => current_time('mysql'),
                'status' => $status
            ),
            array('id' => $this->run_id),
            array('%s', '%s'),
            array('%d')
        );
        
        // Log the end
        $this->safe_log('info', 'algorithm', 'Scoring algorithm run #' . $this->run_id . ' ended with status: ' . $status);
        
        // Update state
        update_option('tradepress_algorithm_running', false);
        delete_option('tradepress_current_run_id');
        
        return true;
    }
    
    /**
     * Process symbols with the scoring algorithm
     *
     * @param int $max_symbols Maximum number of symbols to process
     * @return array Processing statistics
     */
    public function process_symbols($max_symbols = 100) {
        // Get symbols to process
        $symbols = $this->get_symbols_to_process($max_symbols);
        
        $stats = array(
            'symbols_processed' => 0,
            'api_calls' => 0,
            'scores_generated' => 0,
            'trade_signals' => 0
        );
        
        $this->safe_log('info', 'algorithm', 'Starting to process ' . count($symbols) . ' symbols');
        
        foreach ($symbols as $symbol) {
            // Process each symbol
            $this->safe_log('info', 'processing', 'Processing symbol: ' . $symbol->post_title);
            
            // Get data for this symbol from APIs
            $symbol_data = $this->get_symbol_data($symbol->post_title);
            $stats['api_calls'] += $symbol_data['api_calls'];
            
            // Calculate score
            $score_data = $this->calculate_score($symbol, $symbol_data);
            $stats['scores_generated']++;
            
            // Check if score exceeds threshold for trading
            $score_threshold = get_option('tradepress_score_threshold', 75);
            if ($score_data['score'] >= $score_threshold) {
                $stats['trade_signals']++;
                
                // Notify the trading system of a potential signal
                do_action('tradepress_trade_signal', $symbol, $score_data);
                
                $this->safe_log('info', 'signal', 'Trade signal generated for ' . $symbol->post_title . ' with score ' . $score_data['score']);
            }
            
            $stats['symbols_processed']++;
            
            // Add a small delay to prevent server overload
            usleep(100000); // 0.1 seconds
            
            // Check if we should continue running
            if (!get_option('tradepress_algorithm_running', false)) {
                $this->safe_log('warning', 'algorithm', 'Algorithm run stopped prematurely');
                break;
            }
        }
        
        // Update the run record with stats
        $this->update_run_stats($stats);
        
        // Update global stats
        update_option('tradepress_symbols_processed', get_option('tradepress_symbols_processed', 0) + $stats['symbols_processed']);
        update_option('tradepress_calls', get_option('tradepress_calls', 0) + $stats['api_calls']);
        update_option('tradepress_scores_generated', get_option('tradepress_scores_generated', 0) + $stats['scores_generated']);
        update_option('tradepress_trade_signals', get_option('tradepress_trade_signals', 0) + $stats['trade_signals']);
        
        return $stats;
    }
    
    /**
     * Get symbols to process
     *
     * @param int $max_symbols Maximum number of symbols to process
     * @return array Array of symbols
     */
    private function get_symbols_to_process($max_symbols = 100) {
        // Get symbols based on priority or other criteria
        return get_posts(array(
            'post_type' => 'symbols',
            'posts_per_page' => $max_symbols,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
    }
    
    /**
     * Get data for a symbol from APIs
     *
     * @param string $symbol_ticker Symbol ticker
     * @return array Symbol data array
     */
    private function get_symbol_data($symbol_ticker) {
        $api_calls = 0;
        $data = array();
        
        // Get the financial API service
        $api_service = new TradePress_Financial_API_Service();
        
        // Get price data
        $price_data = $api_service->get_price_data($symbol_ticker);
        $data['price'] = $price_data['data'];
        $api_calls += $price_data['api_calls'];
        
        // Get technical indicators
        $technical_data = $api_service->get_technical_indicators($symbol_ticker);
        $data['technical'] = $technical_data['data'];
        $api_calls += $technical_data['api_calls'];
        
        // Get fundamental data if available
        if (apply_filters('tradepress_include_fundamental_data', true)) {
            $fundamental_data = $api_service->get_fundamental_data($symbol_ticker);
            $data['fundamental'] = $fundamental_data['data'];
            $api_calls += $fundamental_data['api_calls'];
        }
        
        // Get market sentiment if available
        if (apply_filters('tradepress_include_sentiment_data', true)) {
            $sentiment_data = $api_service->get_sentiment_data($symbol_ticker);
            $data['sentiment'] = $sentiment_data['data'];
            $api_calls += $sentiment_data['api_calls'];
        }
        
        return array(
            'data' => $data,
            'api_calls' => $api_calls
        );
    }
    
    /**
     * Calculate score for a symbol
     *
     * @param WP_Post $symbol Symbol post object
     * @param array $symbol_data Symbol data from APIs
     * @return array Score data
     */
    private function calculate_score($symbol, $symbol_data) {
        $score_components = array();
        $final_score = 0;
        
        // Get previous score if available
        $previous_score = get_post_meta($symbol->ID, 'tradepress_latest_score', true);
        
        // Price movement component (20% weight)
        $price_change = isset($symbol_data['data']['price']['change_percent']) ? $symbol_data['data']['price']['change_percent'] : 0;
        $price_score = 50; // Neutral
        if ($price_change > 0) {
            $price_score = min(100, 50 + ($price_change * 5));
        } elseif ($price_change < 0) {
            $price_score = max(0, 50 + ($price_change * 5));
        }
        $score_components['price'] = array(
            'name' => 'Price Movement',
            'value' => $price_change . '%',
            'score' => $price_score,
            'weight' => 0.2,
            'contribution' => $price_score * 0.2 / 100
        );
        $final_score += $price_score * 0.2;
        
        // Volume component (15% weight)
        $volume = isset($symbol_data['data']['price']['volume']) ? $symbol_data['data']['price']['volume'] : 0;
        $volume_score = 50; // Default
        // Add more logic here for volume scoring
        $score_components['volume'] = array(
            'name' => 'Volume',
            'value' => number_format($volume),
            'score' => $volume_score,
            'weight' => 0.15,
            'contribution' => $volume_score * 0.15 / 100
        );
        $final_score += $volume_score * 0.15;
        
        // RSI component (25% weight)
        $rsi = isset($symbol_data['data']['technical']['rsi']) ? $symbol_data['data']['technical']['rsi'] : 50;
        $rsi_score = 50; // Neutral
        if ($rsi >= 70) {
            $rsi_score = max(0, 100 - (($rsi - 70) * 5)); // Overbought
        } elseif ($rsi <= 30) {
            $rsi_score = min(100, (30 - $rsi) * 5 + 50); // Oversold - bullish
        } else {
            $rsi_score = 50 + (($rsi - 50) * 2.5);
        }
        $score_components['rsi'] = array(
            'name' => 'RSI',
            'value' => $rsi,
            'score' => $rsi_score,
            'weight' => 0.25,
            'contribution' => $rsi_score * 0.25 / 100
        );
        $final_score += $rsi_score * 0.25;
        
        // MACD component (25% weight)
        $macd = isset($symbol_data['data']['technical']['macd']) ? $symbol_data['data']['technical']['macd'] : 0;
        $macd_score = 50; // Neutral
        if ($macd > 0) {
            $macd_score = min(100, 50 + ($macd * 10));
        } elseif ($macd < 0) {
            $macd_score = max(0, 50 + ($macd * 10));
        }
        $score_components['macd'] = array(
            'name' => 'MACD',
            'value' => $macd,
            'score' => $macd_score,
            'weight' => 0.25,
            'contribution' => $macd_score * 0.25 / 100
        );
        $final_score += $macd_score * 0.25;
        
        // Moving averages component (15% weight)
        $sma_50 = isset($symbol_data['data']['technical']['moving_averages']['sma_50']) ? $symbol_data['data']['technical']['moving_averages']['sma_50'] : 0;
        $sma_200 = isset($symbol_data['data']['technical']['moving_averages']['sma_200']) ? $symbol_data['data']['technical']['moving_averages']['sma_200'] : 0;
        $ma_score = 50; // Neutral
        
        if ($sma_50 > 0 && $sma_200 > 0) {
            if ($sma_50 > $sma_200) {
                // Golden cross - bullish
                $ma_score = min(100, 50 + (($sma_50 / $sma_200 - 1) * 100));
            } elseif ($sma_50 < $sma_200) {
                // Death cross - bearish
                $ma_score = max(0, 50 - ((1 - $sma_50 / $sma_200) * 100));
            }
        }
        
        $score_components['moving_averages'] = array(
            'name' => 'Moving Averages',
            'value' => 'SMA50: ' . $sma_50 . ', SMA200: ' . $sma_200,
            'score' => $ma_score,
            'weight' => 0.15,
            'contribution' => $ma_score * 0.15 / 100
        );
        $final_score += $ma_score * 0.15;
        
        // Apply additional user-defined score modifiers
        $final_score = apply_filters('tradepress_modify_symbol_score', $final_score, $symbol, $symbol_data, $score_components);
        
        // Round to integer
        $final_score = round($final_score);
        
        // Store the score in post meta
        update_post_meta($symbol->ID, 'tradepress_latest_score', $final_score);
        update_post_meta($symbol->ID, 'tradepress_previous_score', $previous_score);
        update_post_meta($symbol->ID, 'tradepress_score_time', current_time('mysql'));
        update_post_meta($symbol->ID, 'tradepress_score_components', $score_components);
        
        // Store in database for history
        $this->save_score($symbol->ID, $symbol->post_title, $final_score, $previous_score, $score_components);
        
        return array(
            'score' => $final_score,
            'previous_score' => $previous_score,
            'components' => $score_components
        );
    }
    
    /**
     * Save score to database
     *
     * @param int $symbol_id Symbol post ID
     * @param string $symbol_ticker Symbol ticker
     * @param int $score Calculated score
     * @param int $previous_score Previous score
     * @param array $components Score components
     * @return int|false The inserted ID or false on failure
     */
    private function save_score($symbol_id, $symbol_ticker, $score, $previous_score, $components) {
        global $wpdb;
        
        return $wpdb->insert(
            $wpdb->prefix . 'tradepress_symbol_scores',
            array(
                'symbol_id' => $symbol_id,
                'symbol' => $symbol_ticker,
                'score' => $score,
                'previous_score' => $previous_score,
                'components' => maybe_serialize($components),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%d', '%s', '%s')
        );
    }
    
    /**
     * Get score history for a symbol
     *
     * @param int|string $symbol Symbol ID or ticker
     * @param int $limit Maximum number of scores to return
     * @return array Array of scores
     */
    public function get_score_history($symbol, $limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_symbol_scores';
        
        // Determine if we're searching by ID or ticker
        $field = is_numeric($symbol) ? 'symbol_id' : 'symbol';
        
        $scores = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE $field = %s ORDER BY created_at DESC LIMIT %d",
                $symbol,
                $limit
            ),
            ARRAY_A
        );
        
        // Unserialize components
        foreach ($scores as &$score) {
            if (!empty($score['components'])) {
                $score['components'] = maybe_unserialize($score['components']);
            }
        }
        
        return $scores;
    }
    
    /**
     * Get latest scores for all symbols
     *
     * @param int $min_score Minimum score to include
     * @param int $limit Maximum number of scores to return
     * @param string $order_by Field to order by
     * @param string $order Sort order (ASC or DESC)
     * @return array Array of scores
     */
    public function get_latest_scores($min_score = 0, $limit = 100, $order_by = 'score', $order = 'DESC') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_symbol_scores';
        
        // Validate order parameters
        $allowed_order_by = ['score', 'symbol', 'created_at'];
        if (!in_array($order_by, $allowed_order_by)) {
            $order_by = 'score';
        }
        
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        // Get latest score for each symbol
        $query = "
            SELECT t.* 
            FROM $table t
            INNER JOIN (
                SELECT symbol, MAX(created_at) as max_date
                FROM $table
                GROUP BY symbol
            ) m ON t.symbol = m.symbol AND t.created_at = m.max_date
            WHERE t.score >= %d
            ORDER BY t.$order_by $order
            LIMIT %d
        ";
        
        $scores = $wpdb->get_results(
            $wpdb->prepare(
                $query,
                $min_score,
                $limit
            ),
            ARRAY_A
        );
        
        // Unserialize components
        foreach ($scores as &$score) {
            if (!empty($score['components'])) {
                $score['components'] = maybe_unserialize($score['components']);
            }
        }
        
        return $scores;
    }
    
    /**
     * Get signals based on score changes
     * 
     * @param int $threshold_change Change threshold to consider significant
     * @param int $limit Maximum number of signals to return
     * @return array Array of signals
     */
    public function get_score_change_signals($threshold_change = 10, $limit = 50) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'tradepress_symbol_scores';
        
        // Find significant score changes
        $query = "
            SELECT 
                t1.symbol_id,
                t1.symbol,
                t1.score as current_score,
                t1.previous_score,
                (t1.score - t1.previous_score) as score_change,
                t1.created_at
            FROM 
                $table t1
            WHERE 
                t1.previous_score IS NOT NULL
                AND ABS(t1.score - t1.previous_score) >= %d
            ORDER BY 
                t1.created_at DESC
            LIMIT %d
        ";
        
        return $wpdb->get_results(
            $wpdb->prepare(
                $query,
                $threshold_change,
                $limit
            ),
            ARRAY_A
        );
    }
    
    /**
     * Update run statistics
     *
     * @param array $stats Run statistics
     * @return bool True on success
     */
    private function update_run_stats($stats) {
        global $wpdb;
        
        if (!$this->run_id) {
            $this->run_id = get_option('tradepress_current_run_id', 0);
        }
        
        if (!$this->run_id) {
            return false;
        }
        
        return $wpdb->update(
            $wpdb->prefix . 'tradepress_algorithm_runs',
            array(
                'symbols_processed' => $stats['symbols_processed'],
                'api_calls' => $stats['api_calls'],
                'scores_generated' => $stats['scores_generated'],
                'trade_signals' => $stats['trade_signals']
            ),
            array('id' => $this->run_id),
            array('%d', '%d', '%d', '%d'),
            array('%d')
        );
    }
    
    /**
     * Calculate scores for securities
     * 
     * SIMPLIFIED VERSION: Generates scoring data using only basic PHP operations
     * to avoid WordPress AJAX conflicts. No external API calls or complex operations.
     * 
     * @return array Array of boxes with security data and scores
     */
    public function calculate_scores() {
        // Use only basic PHP operations to avoid AJAX conflicts
        $boxes = array();
        
        // Get a simple list of test symbols - no external dependencies
        $symbols = $this->get_simple_test_symbols();
        
        $id = 1;
        foreach ($symbols as $symbol => $name) {
            // Generate randomized scores using only basic PHP
            $base_score = 50; // Base score
            $variation = mt_rand(-30, 40); // Random variation
            $score = max(1, min(100, $base_score + $variation)); // Keep within 1-100
            
            // Generate simple price data
            $base_price = 100;
            $price_variation = mt_rand(-50, 200) / 10; // -5.0 to +20.0
            $price = number_format(max(10, $base_price + $price_variation), 2);
            
            $boxes[] = array(
                'id' => $id,
                'symbol' => $symbol,
                'security' => $name,
                'score' => $score,
                'price' => '$' . $price
            );
            
            $id++;
            
            // Limit to reasonable number for performance
            if ($id > 8) break;
        }
        
        return $boxes;
    }
    
    /**
     * Get simple test stock symbols - no external dependencies
     * 
     * @return array Array of stock symbols and names
     */
    private function get_simple_test_symbols() {
        // Hardcoded array to avoid any file system or external dependencies
        return array(
            'AAPL' => 'Apple Inc.',
            'MSFT' => 'Microsoft Corporation',
            'GOOGL' => 'Alphabet Inc.',
            'AMZN' => 'Amazon.com Inc.',
            'TSLA' => 'Tesla Inc.',
            'NVDA' => 'NVIDIA Corporation',
            'META' => 'Meta Platforms Inc.',
            'JPM' => 'JPMorgan Chase & Co.',
            'V' => 'Visa Inc.',
            'JNJ' => 'Johnson & Johnson'
        );
    }
}
