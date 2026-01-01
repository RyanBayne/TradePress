<?php
/**
 * TradePress Symbol Class
 *
 * Handles operations related to symbols including data retrieval across multiple tables
 * and data updates from various API sources.
 *
 * @package TradePress/Classes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TradePress_Symbol {
    /**
     * Symbol data from main symbols table
     * @var array
     */
    private $data = array();
    
    /**
     * Symbol ID from database
     * @var int
     */
    private $id = 0;
    
    /**
     * Symbol ticker (e.g., AAPL)
     * @var string
     */
    private $symbol = '';
    
    /**
     * WordPress post ID if associated
     * @var int
     */
    private $post_id = 0;
    
    /**
     * Price history data
     * @var array
     */
    private $price_history = array();
    
    /**
     * Price levels (support/resistance)
     * @var array
     */
    private $price_levels = array();
    
    /**
     * Symbol scores history
     * @var array
     */
    private $scores = array();
    
    /**
     * Prediction data
     * @var array
     */
    private $predictions = array();
    
    /**
     * Social alerts for this symbol
     * @var array
     */
    private $social_alerts = array();
    
    /**
     * Constructor
     * 
     * @param string|int $symbol_or_id Symbol ticker or database ID
     * @param string $by_field Field to query by ('symbol', 'id', or 'post_id')
     */
    public function __construct($symbol_or_id = null, $by_field = 'symbol') {
        if (!is_null($symbol_or_id)) {
            $this->load($symbol_or_id, $by_field);
        }
    }
    
    /**
     * Load symbol data from database
     * 
     * @param string|int $symbol_or_id Symbol ticker or database ID
     * @param string $by_field Field to query by ('symbol', 'id', or 'post_id')
     * @return bool Success or failure
     */
    public function load($symbol_or_id, $by_field = 'symbol') {
        global $wpdb;
        
        // Validate parameter type based on by_field
        if ($by_field == 'id' || $by_field == 'post_id') {
            if (!is_numeric($symbol_or_id)) {
                return false;
            }
        } elseif ($by_field == 'symbol') {
            $symbol_or_id = strtoupper(sanitize_text_field($symbol_or_id));
        }
        
        // Determine table and where clause
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        
        // Load basic symbol data
        if ($by_field == 'symbol') {
            $where = "symbol = '$symbol_or_id'";
        } elseif ($by_field == 'post_id') {
            $where = "post_id = $symbol_or_id";
        } else {
            $where = "id = $symbol_or_id";
        }
        
        // Get symbol data from main table
        $symbol_data = $wpdb->get_row("SELECT * FROM $table_name WHERE $where");
        
        if (!$symbol_data) {
            // Check if this symbol exists as a post but not in the custom table
            if ($by_field == 'symbol') {
                // Try to find a matching post
                $args = array(
                    'post_type' => 'symbols',
                    'name' => $symbol_or_id,
                    'posts_per_page' => 1
                );
                
                $posts = get_posts($args);
                
                if (!empty($posts)) {
                    $post = $posts[0];
                    
                    // Create a new entry in the symbols table
                    $this->create_from_post($post->ID);
                    
                    // Reload the data
                    return $this->load($symbol_or_id, $by_field);
                }
            }
            
            return false;
        }
        
        // Store the data
        $this->data = (array)$symbol_data;
        $this->id = $symbol_data->id;
        $this->symbol = $symbol_data->symbol;
        $this->post_id = $symbol_data->post_id;
        
        return true;
    }
    
    /**
     * Create a symbol record from a WordPress post
     * 
     * @param int $post_id Post ID
     * @return bool Success or failure
     */
    public function create_from_post($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type != 'symbols') {
            return false;
        }
        
        // Get symbol from post_name or meta field
        $symbol = strtoupper($post->post_name);
        $symbol_meta = get_post_meta($post_id, 'tradepress_stock_symbol', true);
        
        if ($symbol_meta) {
            $symbol = strtoupper($symbol_meta);
        }
        
        if (empty($symbol)) {
            return false;
        }
        
        // Check if we already have this symbol in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $existing = $wpdb->get_row("SELECT * FROM $table_name WHERE symbol = '$symbol'");
        
        if ($existing) {
            // Update existing record
            $fields = array(
                'post_id' => $post_id,
                'name' => $post->post_title,
                'updated_at' => current_time('mysql')
            );
            
            $wpdb->update($table_name, $fields, array('id' => $existing->id));
            return true;
        }
        
        // Create a new record
        $fields = array(
            'symbol' => $symbol,
            'name' => $post->post_title,
            'description' => $post->post_content,
            'post_id' => $post_id,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'active' => 1
        );
        
        $result = $wpdb->insert($table_name, $fields);
        
        return ($result > 0);
    }
    
    /**
     * Get complete symbol data across all tables
     * 
     * @param bool $with_history Whether to include price history
     * @param string $timeframe For price history, which timeframe to include
     * @param int $limit Maximum number of price history records
     * @return array Complete symbol data
     */
    public function get_complete_data($with_history = false, $timeframe = 'daily', $limit = 30) {
        if (empty($this->data)) {
            return array();
        }
        
        // Start with the main data
        $complete_data = $this->data;
        
        // Add price levels
        $complete_data['price_levels'] = $this->get_price_levels();
        
        // Add latest score
        $complete_data['latest_score'] = $this->get_latest_score();
        
        // Add latest predictions
        $complete_data['predictions'] = $this->get_predictions(5);
        
        // Add recent social alerts
        $complete_data['social_alerts'] = $this->get_social_alerts(5);
        
        // Add price history if requested
        if ($with_history) {
            $complete_data['price_history'] = $this->get_price_history($timeframe, $limit);
        }
        
        // Add post metadata if available
        if ($this->post_id > 0) {
            $complete_data['post_meta'] = $this->get_post_metadata();
        }
        
        return $complete_data;
    }
    
    /**
     * Get price levels (support/resistance)
     * 
     * @return array Price levels
     */
    public function get_price_levels() {
        global $wpdb;
        
        if (empty($this->id)) {
            return array();
        }
        
        $table_name = $wpdb->prefix . 'tradepress_price_levels';
        $query = "SELECT * FROM $table_name WHERE symbol_id = {$this->id} ORDER BY price_level DESC";
        
        $this->price_levels = $wpdb->get_results($query, ARRAY_A);
        
        return $this->price_levels;
    }
    
    /**
     * Get price history
     * 
     * @param string $timeframe Timeframe (daily, weekly, etc)
     * @param int $limit Maximum number of records
     * @return array Price history data
     */
    public function get_price_history($timeframe = 'daily', $limit = 30) {
        global $wpdb;
        
        if (empty($this->id)) {
            return array();
        }
        
        $table_name = $wpdb->prefix . 'tradepress_price_history';
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE symbol_id = %d AND timeframe = %s 
            ORDER BY date_time DESC LIMIT %d",
            $this->id,
            $timeframe,
            $limit
        );
        
        $this->price_history = $wpdb->get_results($query, ARRAY_A);
        
        return $this->price_history;
    }
    
    /**
     * Get the latest score for this symbol
     * 
     * @return array|null Score data or null if not found
     */
    public function get_latest_score() {
        global $wpdb;
        
        if (empty($this->id)) {
            return null;
        }
        
        $table_name = $wpdb->prefix . 'tradepress_symbol_scores';
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE symbol_id = %d 
            ORDER BY created_at DESC LIMIT 1",
            $this->id
        );
        
        $score = $wpdb->get_row($query, ARRAY_A);
        
        if (!$score) {
            return null;
        }
        
        // Get score components if available
        if (!empty($score['id'])) {
            $components_table = $wpdb->prefix . 'tradepress_directive_scores';
            $components_query = $wpdb->prepare(
                "SELECT * FROM $components_table 
                WHERE score_id = %d",
                $score['id']
            );
            
            $components = $wpdb->get_results($components_query, ARRAY_A);
            $score['components'] = $components;
        }
        
        return $score;
    }
    
    /**
     * Get predictions for this symbol
     * 
     * @param int $limit Maximum number of predictions
     * @return array Predictions
     */
    public function get_predictions($limit = 5) {
        global $wpdb;
        
        if (empty($this->symbol)) {
            return array();
        }
        
        $table_name = $wpdb->prefix . 'tradepress_price_predictions';
        $query = $wpdb->prepare(
            "SELECT p.*, s.source_name 
            FROM $table_name p
            LEFT JOIN {$wpdb->prefix}tradepress_prediction_sources s 
            ON p.source_id = s.source_id
            WHERE p.symbol = %s 
            ORDER BY p.prediction_date DESC LIMIT %d",
            $this->symbol,
            $limit
        );
        
        $this->predictions = $wpdb->get_results($query, ARRAY_A);
        
        return $this->predictions;
    }
    
    /**
     * Get social alerts for this symbol
     * 
     * @param int $limit Maximum number of alerts
     * @return array Social alerts
     */
    public function get_social_alerts($limit = 5) {
        global $wpdb;
        
        if (empty($this->symbol)) {
            return array();
        }
        
        $table_name = $wpdb->prefix . 'tradepress_social_alerts';
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE symbols LIKE %s 
            ORDER BY message_date DESC LIMIT %d",
            '%' . $wpdb->esc_like($this->symbol) . '%',
            $limit
        );
        
        $this->social_alerts = $wpdb->get_results($query, ARRAY_A);
        
        return $this->social_alerts;
    }
    
    /**
     * Get post metadata for this symbol
     * 
     * @return array Post metadata
     */
    public function get_post_metadata() {
        if (empty($this->post_id)) {
            return array();
        }
        
        $meta_keys = array(
            '_tradepress_exchange',
            '_tradepress_last_price',
            '_tradepress_price_change',
            '_tradepress_price_change_pct',
            '_tradepress_market_cap',
            '_tradepress_pe_ratio',
            '_tradepress_eps',
            '_tradepress_dividend',
            '_tradepress_dividend_yield',
            '_tradepress_52w_high',
            '_tradepress_52w_low',
            '_tradepress_volume',
            '_tradepress_avg_volume'
        );
        
        $post_meta = array();
        
        foreach ($meta_keys as $key) {
            $value = get_post_meta($this->post_id, $key, true);
            if (!empty($value)) {
                $post_meta[str_replace('_tradepress_', '', $key)] = $value;
            }
        }
        
        return $post_meta;
    }
    
    /**
     * Update symbol data from API source
     * 
     * @param string $source API source name
     * @return bool Success or failure
     */
    public function update_from_api($source = 'alphavantage') {
        if (empty($this->symbol)) {
            return false;
        }
        
        // Initialize the result
        $result = false;
        
        // Load the appropriate API service
        switch ($source) {
            case 'alphavantage':
                $result = $this->update_from_alphavantage();
                break;
            case 'finnhub':
                $result = $this->update_from_finnhub();
                break;
            case 'iex':
                $result = $this->update_from_iex();
                break;
            default:
                return false;
        }
        
        // If update was successful, update the timestamp
        if ($result) {
            $this->update_last_updated();
        }
        
        return $result;
    }
    
    /**
     * Update symbol data from Alpha Vantage API
     * 
     * @return bool Success or failure
     */
    private function update_from_alphavantage() {
        // Check if AlphaVantage API class exists
        if (!class_exists('TradePress_AlphaVantage_API')) {
            if (file_exists(TRADEPRESS_PLUGIN_DIR . 'api/alphavantage/alphavantage-api.php')) {
                require_once TRADEPRESS_PLUGIN_DIR . 'api/alphavantage/alphavantage-api.php';
            } else {
                return false;
            }
        }
        
        // Initialize API (let the constructor handle API key retrieval)
        $api = new TradePress_AlphaVantage_API();
        
        // Get global quote data (using the correct method from the API class)
        $quote = $api->get_global_quote($this->symbol);
        
        if (is_wp_error($quote) || empty($quote)) {
            return false;
        }
        
        // Update symbol data in database
        global $wpdb;
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        
        $fields = array(
            'current_price' => isset($quote['price']) ? $quote['price'] : null,
            'volume' => isset($quote['volume']) ? $quote['volume'] : null,
            'price_updated' => current_time('mysql')
        );
        
        // Update the database
        $result = $wpdb->update($table_name, $fields, array('id' => $this->id));
        
        // Update post meta if we have a post
        if ($this->post_id > 0) {
            if (isset($quote['price'])) {
                update_post_meta($this->post_id, '_tradepress_last_price', $quote['price']);
            }
            
            if (isset($quote['change'])) {
                update_post_meta($this->post_id, '_tradepress_price_change', $quote['change']);
            }
            
            if (isset($quote['change_percent'])) {
                update_post_meta($this->post_id, '_tradepress_price_change_pct', $quote['change_percent']);
            }
            
            if (isset($quote['volume'])) {
                update_post_meta($this->post_id, '_tradepress_volume', $quote['volume']);
            }
        }
        
        return ($result !== false);
    }
    
    /**
     * Update the last updated timestamp
     */
    private function update_last_updated() {
        if ($this->post_id > 0) {
            update_post_meta($this->post_id, '_tradepress_data_last_updated', time());
        }
    }
    
    /**
     * Get basic data getter
     * 
     * @param string $key Data key to get
     * @return mixed Data value or null if not found
     */
    public function get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        return null;
    }
    
    /**
     * Get ID
     * 
     * @return int Symbol ID
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Get symbol ticker
     * 
     * @return string Symbol ticker
     */
    public function get_symbol() {
        return $this->symbol;
    }
    
    /**
     * Get post ID
     * 
     * @return int Post ID
     */
    public function get_post_id() {
        return $this->post_id;
    }
    
    /**
     * Get symbol name
     * 
     * @return string Symbol name
     */
    public function get_name() {
        return isset($this->data['name']) ? $this->data['name'] : '';
    }
}
