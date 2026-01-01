<?php
/**
 * TradePress Symbols Class
 *
 * Manages collections of symbols and symbol-related operations.
 *
 * @package TradePress/Classes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TradePress_Symbols {
    /**
     * Get a single symbol by ID or ticker
     * 
     * @param string|int $symbol_or_id Symbol ticker or database ID
     * @param string $by_field Field to query by ('symbol', 'id', or 'post_id')
     * @return TradePress_Symbol|false Symbol object or false on failure
     */
    public static function get_symbol($symbol_or_id, $by_field = 'symbol') {
        // Check if symbol already exists in the registry
        $registry_key = 'symbol_' . $by_field . '_' . $symbol_or_id;
        $symbol = TradePress_Object_Registry::get($registry_key);
        
        if ($symbol !== null) {
            return $symbol;
        }
        
        // Create a new symbol object
        require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
        $symbol = new TradePress_Symbol($symbol_or_id, $by_field);
        
        // If loaded successfully, add to registry
        if ($symbol->get_id() > 0) {
            TradePress_Object_Registry::add($registry_key, $symbol);
            return $symbol;
        }
        
        return false;
    }
    
    /**
     * Get multiple symbols based on criteria
     * 
     * @param array $args Query arguments
     * @return array Array of TradePress_Symbol objects
     */
    public static function get_symbols($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'symbol',
            'order' => 'ASC',
            'active' => true,
            'sector' => '',
            'exchange' => '',
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build the query
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $query = "SELECT * FROM $table_name WHERE 1=1";
        
        // Add filters
        if ($args['active']) {
            $query .= " AND active = 1";
        }
        
        if (!empty($args['sector'])) {
            $query .= $wpdb->prepare(" AND sector = %s", $args['sector']);
        }
        
        if (!empty($args['exchange'])) {
            $query .= $wpdb->prepare(" AND exchange = %s", $args['exchange']);
        }
        
        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $query .= $wpdb->prepare(" AND (symbol LIKE %s OR name LIKE %s)", $search, $search);
        }
        
        // Add ordering
        $query .= " ORDER BY {$args['orderby']} {$args['order']}";
        
        // Add limit
        $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        
        // Get results
        $results = $wpdb->get_results($query);
        
        if (empty($results)) {
            return array();
        }
        
        // Convert to symbol objects
        $symbols = array();
        require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
        
        foreach ($results as $result) {
            $symbol = new TradePress_Symbol($result->id, 'id');
            $symbols[] = $symbol;
            
            // Add to registry
            $registry_key = 'symbol_id_' . $result->id;
            TradePress_Object_Registry::add($registry_key, $symbol);
        }
        
        return $symbols;
    }
    
    /**
     * Count symbols based on criteria
     * 
     * @param array $args Query arguments
     * @return int Number of symbols
     */
    public static function count_symbols($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'active' => true,
            'sector' => '',
            'exchange' => '',
            'search' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Build the query
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        
        // Add filters
        if ($args['active']) {
            $query .= " AND active = 1";
        }
        
        if (!empty($args['sector'])) {
            $query .= $wpdb->prepare(" AND sector = %s", $args['sector']);
        }
        
        if (!empty($args['exchange'])) {
            $query .= $wpdb->prepare(" AND exchange = %s", $args['exchange']);
        }
        
        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $query .= $wpdb->prepare(" AND (symbol LIKE %s OR name LIKE %s)", $search, $search);
        }
        
        // Get count
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Initialize symbols from posts if they don't exist in the database
     */
    public static function initialize_from_posts() {
        $args = array(
            'post_type' => 'symbols',
            'posts_per_page' => -1
        );
        
        $posts = get_posts($args);
        
        if (empty($posts)) {
            return;
        }
        
        require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
        
        foreach ($posts as $post) {
            $symbol_obj = new TradePress_Symbol();
            $symbol_obj->create_from_post($post->ID);
        }
    }
    
    /**
     * Get unique sectors from the symbols table
     * 
     * @return array List of sectors
     */
    public static function get_sectors() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $query = "SELECT DISTINCT sector FROM $table_name WHERE sector IS NOT NULL AND sector != '' ORDER BY sector";
        
        $results = $wpdb->get_col($query);
        
        return $results;
    }
    
    /**
     * Get unique exchanges from the symbols table
     * 
     * @return array List of exchanges
     */
    public static function get_exchanges() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_symbols';
        $query = "SELECT DISTINCT exchange FROM $table_name WHERE exchange IS NOT NULL AND exchange != '' ORDER BY exchange";
        
        $results = $wpdb->get_col($query);
        
        return $results;
    }
}
