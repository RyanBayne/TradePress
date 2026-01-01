<?php
/**
 * TradePress Trading (Bot) Algorithm Class
 *
 * Implements the core algorithm functionality for making trade decisions.
 * Will heavily rely on scoring algorithms to evaluate symbols and generate trade signals.
 * This class is responsible for:
 * - Starting and ending algorithm runs related to trading.
 * - Generating trade signals based on scores and market conditions.
 * - Managing algorithm state and run history.
 * - Updating plugin database tables with actions taken during the run (e.g., scores, trades, decisions and reasons for decisions).
 * 
 * Not responsible for:
 * - Processing symbols to generate scores, that is handled by the scoring-systems algorithm.
 *
 * @package TradePress/Classes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Trading_Algorithm Class
 */
class TradePress_Trading_Algorithm {
    /**
     * Current run ID
     *
     * @var int
     */
    private $run_id = 0;
    
    /**
     * Logger for algorithm operations
     *
     * @var TradePress_Bot_Logger
     */
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the logger
        $this->logger = new TradePress_Bot_Logger();
    }
    
    /**
     * Start a new algorithm run
     *
     * @param string $run_type Type of run (manual, cron)
     * @return int The run ID
     */
    public function start_new_run($run_type = 'manual') {
        global $wpdb;
        
        // Log the start
        $this->logger->log('info', 'algorithm', 'Starting new algorithm run: ' . $run_type);
        
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
        $this->logger->log('info', 'algorithm', 'Algorithm run #' . $this->run_id . ' ended with status: ' . $status);
        
        // Update state
        update_option('tradepress_algorithm_running', false);
        delete_option('tradepress_current_run_id');
        
        return true;
    }
    
    /**
     * Process symbols with the algorithm
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
        
        $this->logger->log('info', 'algorithm', 'Starting to process ' . count($symbols) . ' symbols');
        
        foreach ($symbols as $symbol) {
            // Process each symbol
            $this->logger->log('info', 'processing', 'Processing symbol: ' . $symbol->post_title);
            
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
                
                // Generate trading signal
                $this->generate_trade_signal($symbol, $score_data);
                
                $this->logger->log('info', 'signal', 'Trade signal generated for ' . $symbol->post_title . ' with score ' . $score_data['score']);
            }
            
            $stats['symbols_processed']++;
            
            // Add a small delay to prevent server overload
            usleep(100000); // 0.1 seconds
            
            // Check if we should continue running
            if (!get_option('tradepress_algorithm_running', false)) {
                $this->logger->log('warning', 'algorithm', 'Algorithm run stopped prematurely');
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
        
        // Implement API cycling here - for now just a placeholder
        $data['price'] = array(
            'current' => rand(10, 500) + (rand(0, 100) / 100),
            'previous_close' => rand(10, 500) + (rand(0, 100) / 100),
            'change_percent' => rand(-10, 10) + (rand(0, 100) / 100)
        );
        
        $data['volume'] = rand(10000, 10000000);
        $api_calls++;
        
        // Technical indicators
        $data['technical'] = array(
            'rsi' => rand(20, 80),
            'macd' => rand(-20, 20) / 10,
            'moving_averages' => array(
                'sma_50' => rand(10, 500) + (rand(0, 100) / 100),
                'sma_200' => rand(10, 500) + (rand(0, 100) / 100)
            )
        );
        $api_calls++;
        
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
        $price_change = $symbol_data['data']['price']['change_percent'];
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
        $volume = $symbol_data['data']['volume'];
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
        $rsi = $symbol_data['data']['technical']['rsi'];
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
        $macd = $symbol_data['data']['technical']['macd'];
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
        $sma_50 = $symbol_data['data']['technical']['moving_averages']['sma_50'];
        $sma_200 = $symbol_data['data']['technical']['moving_averages']['sma_200'];
        $ma_score = 50; // Neutral
        if ($sma_50 > $sma_200) {
            // Golden cross - bullish
            $ma_score = min(100, 50 + (($sma_50 / $sma_200 - 1) * 100));
        } elseif ($sma_50 < $sma_200) {
            // Death cross - bearish
            $ma_score = max(0, 50 - ((1 - $sma_50 / $sma_200) * 100));
        }
        $score_components['moving_averages'] = array(
            'name' => 'Moving Averages',
            'value' => 'SMA50: ' . $sma_50 . ', SMA200: ' . $sma_200,
            'score' => $ma_score,
            'weight' => 0.15,
            'contribution' => $ma_score * 0.15 / 100
        );
        $final_score += $ma_score * 0.15;
        
        // Round to integer
        $final_score = round($final_score);
        
        // Store the score in post meta
        update_post_meta($symbol->ID, 'tradepress_latest_score', $final_score);
        update_post_meta($symbol->ID, 'tradepress_previous_score', $previous_score);
        update_post_meta($symbol->ID, 'tradepress_score_time', current_time('mysql'));
        
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
     * Generate a trade signal
     *
     * @param WP_Post $symbol Symbol post object
     * @param array $score_data Score data
     * @return int|false The inserted ID or false on failure
     */
    private function generate_trade_signal($symbol, $score_data) {
        global $wpdb;
        
        // Determine trade action based on score
        $action = 'buy'; // Default
        
        // In a more advanced implementation, you would make this decision
        // based on more factors and possibly existing positions
        
        return $wpdb->insert(
            $wpdb->prefix . 'tradepress_trades',
            array(
                'symbol_id' => $symbol->ID,
                'symbol' => $symbol->post_title,
                'action' => $action,
                'quantity' => 1.0, // Placeholder
                'price' => 0.0, // Will be filled when executed
                'status' => 'pending',
                'score' => $score_data['score'],
                'datetime' => current_time('mysql'),
                'notes' => 'Generated by algorithm run #' . $this->run_id
            ),
            array('%d', '%s', '%s', '%f', '%f', '%s', '%d', '%s', '%s')
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
}

/**
 * TradePress Trading Bot Class
 *
 * Implements the trading functionality based on scoring algorithm signals
 * This bot decides actual trade execution, risk management, and position sizing
 * based on signals from the scoring algorithm.
 *
 * @package TradePress/Classes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Trading_Bot Class
 */
class TradePress_Trading_Bot {
    /**
     * Logger for trading operations
     *
     * @var object
     */
    private $logger;
    
    /**
     * Trading mode (paper, live)
     *
     * @var string
     */
    private $mode = 'paper';
    
    /**
     * Maximum position size as percentage of portfolio
     *
     * @var float
     */
    private $max_position_size = 0.05; // 5% of portfolio
    
    /**
     * Maximum risk per trade as percentage of portfolio
     *
     * @var float
     */
    private $max_risk_per_trade = 0.01; // 1% of portfolio
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize the logger
        $this->logger = new TradePress_Logger();
        
        // Set trading mode from options
        $this->mode = get_option('tradepress_trading_mode', 'paper');
        
        // Set risk parameters from options
        $this->max_position_size = get_option('tradepress_max_position_size', 0.05);
        $this->max_risk_per_trade = get_option('tradepress_max_risk_per_trade', 0.01);
        
        // Register hooks
        add_action('tradepress_trade_signal', array($this, 'process_trade_signal'), 10, 2);
    }
    
    /**
     * Process a trade signal from the scoring algorithm
     *
     * @param WP_Post $symbol Symbol post object
     * @param array $score_data Score data
     * @return bool Whether a trade was initiated
     */
    public function process_trade_signal($symbol, $score_data) {
        $score = $score_data['score'];
        $previous_score = $score_data['previous_score'];
        
        $this->logger->log('info', 'trading', sprintf(
            'Processing trade signal for %s with score %d (previous: %s)',
            $symbol->post_title,
            $score,
            $previous_score ?? 'none'
        ));
        
        // Check if we should generate a trade based on the score and market conditions
        if (!$this->should_generate_trade($symbol, $score_data)) {
            $this->logger->log('info', 'trading', sprintf(
                'No trade generated for %s - criteria not met',
                $symbol->post_title
            ));
            return false;
        }
        
        // Determine trade direction (buy/sell)
        $action = $this->determine_trade_action($symbol, $score_data);
        
        // Calculate position size based on risk parameters
        $quantity = $this->calculate_position_size($symbol, $action, $score);
        
        // Get current price
        $price = $this->get_symbol_price($symbol->post_title);
        
        // Create the trade record
        $trade_id = $this->create_trade_record($symbol, $action, $quantity, $price, $score);
        
        if (!$trade_id) {
            $this->logger->log('error', 'trading', sprintf(
                'Failed to create trade record for %s',
                $symbol->post_title
            ));
            return false;
        }
        
        // If in live mode, execute the trade through the broker API
        if ($this->mode === 'live') {
            $this->execute_trade($trade_id, $symbol, $action, $quantity, $price);
        } else {
            // Mark as simulated completion for paper trading
            $this->update_trade_status($trade_id, 'completed', array(
                'notes' => 'Paper trading - simulated execution',
                'execution_time' => current_time('mysql')
            ));
            
            $this->logger->log('info', 'trading', sprintf(
                'Paper trade created for %s: %s %.2f units at $%.2f',
                $symbol->post_title,
                $action,
                $quantity,
                $price
            ));
            
            // Update portfolio for paper trading
            $this->update_portfolio_after_trade($symbol->post_title, $action, $quantity, $price);
        }
        
        return true;
    }
    
    /**
     * Determine if a trade should be generated based on scoring data and market conditions
     *
     * @param WP_Post $symbol Symbol post object
     * @param array $score_data Score data
     * @return bool Whether a trade should be generated
     */
    private function should_generate_trade($symbol, $score_data) {
        $score = $score_data['score'];
        $previous_score = $score_data['previous_score'];
        
        // 1. Score threshold check
        $score_threshold = get_option('tradepress_score_threshold', 75);
        if ($score < $score_threshold) {
            return false;
        }
        
        // 2. Score change significance check
        if ($previous_score !== null) {
            $min_change = get_option('tradepress_min_score_change', 10);
            if (abs($score - $previous_score) < $min_change) {
                return false;
            }
        }
        
        // 3. Check current market conditions
        if (!$this->are_market_conditions_favorable($symbol->post_title)) {
            return false;
        }
        
        // 4. Check portfolio constraints
        if (!$this->check_portfolio_constraints($symbol->post_title)) {
            return false;
        }
        
        // Allow custom filtering of trade generation
        return apply_filters('tradepress_should_generate_trade', true, $symbol, $score_data);
    }
    
    /**
     * Check if market conditions are favorable for trading
     *
     * @param string $symbol_ticker Symbol ticker
     * @return bool Whether market conditions are favorable
     */
    private function are_market_conditions_favorable($symbol_ticker) {
        // Check market hours
        if (get_option('tradepress_market_hours_only', true)) {
            $current_day = date('w'); // 0 (Sunday) to 6 (Saturday)
            $market_days = get_option('tradepress_market_days', array('1', '2', '3', '4', '5'));
            
            if (!in_array($current_day, $market_days)) {
                // Not a market day
                return false;
            }
            
            $current_time = current_time('H:i');
            $market_open = get_option('tradepress_market_open_time', '09:30');
            $market_close = get_option('tradepress_market_close_time', '16:00');
            
            if ($current_time < $market_open || $current_time > $market_close) {
                // Outside market hours
                return false;
            }
        }
        
        // Check market volatility
        $market_volatility = $this->get_market_volatility();
        $max_volatility = get_option('tradepress_max_market_volatility', 3.0); // 3% default
        
        if ($market_volatility > $max_volatility) {
            return false;
        }
        
        return true;
    }
}
