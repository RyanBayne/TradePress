<?php
/**
 * Recent Symbols Tracker
 *
 * Tracks and manages the most recently accessed symbols by user
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    TradePress
 * @subpackage TradePress/includes/utils
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TradePress_Recent_Symbols {
    
    /**
     * The maximum number of recent symbols to track per user
     */
    const MAX_RECENT_SYMBOLS = 3;
    
    /**
     * The user meta key used to store recent symbols
     */
    const USER_META_KEY = 'tradepress_recent_symbols';
    
    /**
     * Add a symbol to the user's recent symbols list
     *
     * @param string $symbol The stock symbol to add (e.g., 'AAPL')
     * @param int $user_id Optional user ID, defaults to current user
     * @return array Updated list of recent symbols
     */
    public static function add_recent_symbol($symbol, $user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // If no user is logged in, return empty array
        if (!$user_id) {
            return array();
        }
        
        // Sanitize and standardize the symbol
        $symbol = strtoupper(sanitize_text_field($symbol));
        
        // Get current recent symbols
        $recent_symbols = self::get_recent_symbols($user_id);
        
        // If this symbol already exists in the list, remove it so we can move it to the front
        if (($key = array_search($symbol, $recent_symbols)) !== false) {
            unset($recent_symbols[$key]);
        }
        
        // Add the new symbol to the beginning of the array
        array_unshift($recent_symbols, $symbol);
        
        // Limit to MAX_RECENT_SYMBOLS
        $recent_symbols = array_slice($recent_symbols, 0, self::MAX_RECENT_SYMBOLS);
        
        // Update user meta
        update_user_meta($user_id, self::USER_META_KEY, $recent_symbols);
        
        return $recent_symbols;
    }
    
    /**
     * Get the user's recent symbols
     *
     * @param int $user_id Optional user ID, defaults to current user
     * @return array List of recent symbols
     */
    public static function get_recent_symbols($user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // If no user is logged in, return empty array
        if (!$user_id) {
            return array();
        }
        
        $recent_symbols = get_user_meta($user_id, self::USER_META_KEY, true);
        
        // If meta doesn't exist yet, initialize an empty array
        if (!is_array($recent_symbols)) {
            $recent_symbols = array();
        }
        
        return $recent_symbols;
    }
    
    /**
     * Remove a symbol from the user's recent symbols list
     *
     * @param string $symbol The stock symbol to remove
     * @param int $user_id Optional user ID, defaults to current user
     * @return array Updated list of recent symbols
     */
    public static function remove_recent_symbol($symbol, $user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // If no user is logged in, return empty array
        if (!$user_id) {
            return array();
        }
        
        // Sanitize and standardize the symbol
        $symbol = strtoupper(sanitize_text_field($symbol));
        
        // Get current recent symbols
        $recent_symbols = self::get_recent_symbols($user_id);
        
        // Remove the symbol if it exists
        if (($key = array_search($symbol, $recent_symbols)) !== false) {
            unset($recent_symbols[$key]);
            // Reindex array
            $recent_symbols = array_values($recent_symbols);
            
            // Update user meta
            update_user_meta($user_id, self::USER_META_KEY, $recent_symbols);
        }
        
        return $recent_symbols;
    }
    
    /**
     * Clear all recent symbols for a user
     *
     * @param int $user_id Optional user ID, defaults to current user
     * @return bool True if successful
     */
    public static function clear_recent_symbols($user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // If no user is logged in, return false
        if (!$user_id) {
            return false;
        }
        
        return delete_user_meta($user_id, self::USER_META_KEY);
    }
    
    /**
     * Get basic data for recent symbols
     *
     * @param int $user_id Optional user ID, defaults to current user
     * @return array Array of symbol data
     */
    public static function get_recent_symbols_data($user_id = 0) {
        $symbols = self::get_recent_symbols($user_id);
        $symbols_data = array();
        
        foreach ($symbols as $symbol) {
            // In a real implementation, this would fetch actual data from database or API
            // For now, generate demo data
            $symbols_data[$symbol] = self::get_demo_symbol_data($symbol);
        }
        
        return apply_filters('tradepress_recent_symbols_data', $symbols_data);
    }
    
    /**
     * Generate demo data for a symbol
     * 
     * @param string $symbol Stock symbol
     * @return array Symbol data
     */
    private static function get_demo_symbol_data($symbol) {
        // Set seed based on symbol name for consistent random values
        srand(crc32($symbol));
        
        $price = mt_rand(1000, 50000) / 100;
        $change_pct = (mt_rand(-500, 500) / 100);
        $is_positive = $change_pct >= 0;
        $change = $price * ($change_pct / 100);
        
        // Generate some typical price levels
        return array(
            'symbol' => $symbol,
            'company_name' => self::get_demo_company_name($symbol),
            'price' => $price,
            'change' => $change,
            'change_percent' => $change_pct,
            'is_positive' => $is_positive,
            'volume' => mt_rand(100000, 10000000),
            'last_checked' => date('Y-m-d H:i:s', time() - mt_rand(60, 3600)),
            'thumbnail' => 'https://via.placeholder.com/50x50.png?text=' . $symbol,
        );
    }
    
    /**
     * Get demo company name based on symbol
     *
     * @param string $symbol Stock symbol
     * @return string Company name
     */
    private static function get_demo_company_name($symbol) {
        $common_names = array(
            'AAPL' => 'Apple Inc.',
            'MSFT' => 'Microsoft Corporation',
            'GOOGL' => 'Alphabet Inc.',
            'GOOG' => 'Alphabet Inc.',
            'AMZN' => 'Amazon.com, Inc.',
            'TSLA' => 'Tesla, Inc.',
            'META' => 'Meta Platforms, Inc.',
            'NFLX' => 'Netflix, Inc.',
            'NVDA' => 'NVIDIA Corporation',
            'AMD' => 'Advanced Micro Devices, Inc.',
            'INTC' => 'Intel Corporation',
            'IBM' => 'International Business Machines',
            'CSCO' => 'Cisco Systems, Inc.',
            'DIS' => 'The Walt Disney Company',
            'JPM' => 'JPMorgan Chase & Co.',
            'BAC' => 'Bank of America Corporation',
            'WMT' => 'Walmart Inc.',
            'JNJ' => 'Johnson & Johnson',
            'PG' => 'Procter & Gamble Co',
            'KO' => 'The Coca-Cola Company',
            'PEP' => 'PepsiCo, Inc.',
            'T' => 'AT&T Inc.',
            'VZ' => 'Verizon Communications Inc.',
        );
        
        if (isset($common_names[$symbol])) {
            return $common_names[$symbol];
        }
        
        // Generate a placeholder name for unknown symbols
        return $symbol . ' Corporation';
    }
}
