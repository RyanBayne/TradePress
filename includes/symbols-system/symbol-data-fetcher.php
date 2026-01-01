<?php
/**
 * TradePress Symbol Data Fetcher
 *
 * Handles API data fetching for symbols
 *
 * @package TradePress
 * @subpackage Includes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Symbol_Data_Fetcher {
    
    private $symbol_manager;
    
    public function __construct() {
        $this->symbol_manager = new TradePress_Symbol_Manager();
    }
    
    /**
     * Fetch basic symbol information
     *
     * @param string $symbol
     * @return array|false
     */
    public function fetch_symbol_info($symbol) {
        // Use existing API classes
        if (class_exists('TradePress_Alpaca_API')) {
            $alpaca = new TradePress_Alpaca_API();
            $info = $alpaca->get_asset($symbol);
            
            if ($info && !is_wp_error($info)) {
                $this->track_api_call('alpaca', 'get_asset', 200);
                return $info;
            }
        }
        
        return false;
    }
    
    /**
     * Get current price data
     *
     * @param string $symbol
     * @return array|false
     */
    public function fetch_price_data($symbol) {
        if (class_exists('TradePress_Alpha_Vantage_API')) {
            $av = new TradePress_Alpha_Vantage_API();
            $price_data = $av->get_quote($symbol);
            
            if ($price_data && !is_wp_error($price_data)) {
                $this->track_api_call('alpha_vantage', 'get_quote', 200);
                return $price_data;
            }
        }
        
        return false;
    }
    
    /**
     * Get earnings calendar
     *
     * @param string $symbol
     * @return array|false
     */
    public function fetch_earnings_calendar($symbol) {
        if (class_exists('TradePress_Alpha_Vantage_API')) {
            $av = new TradePress_Alpha_Vantage_API();
            $earnings = $av->get_earnings_calendar($symbol);
            
            if ($earnings && !is_wp_error($earnings)) {
                $this->track_api_call('alpha_vantage', 'get_earnings_calendar', 200);
                return $earnings;
            }
        }
        
        return false;
    }
    
    /**
     * Complete symbol data update
     *
     * @param string $symbol
     * @return bool
     */
    public function update_symbol_from_api($symbol) {
        $symbol_id = $this->symbol_manager->ensure_symbol_exists($symbol);
        $symbol_obj = $this->symbol_manager->get_symbol($symbol);
        
        if (!$symbol_obj) {
            return false;
        }
        
        $updated = false;
        
        // Fetch basic info
        $info = $this->fetch_symbol_info($symbol);
        if ($info) {
            $symbol_obj->update_meta('basic_info', $info, 'alpaca');
            $updated = true;
        }
        
        // Fetch price data
        $price_data = $this->fetch_price_data($symbol);
        if ($price_data) {
            $symbol_obj->update_meta('current_price', $price_data, 'alpha_vantage');
            $updated = true;
        }
        
        // Fetch earnings calendar
        $earnings = $this->fetch_earnings_calendar($symbol);
        if ($earnings) {
            $symbol_obj->update_meta('earnings_calendar', $earnings, 'alpha_vantage');
            $updated = true;
        }
        
        return $updated;
    }
    
    /**
     * Track API call for rate limiting
     *
     * @param string $provider
     * @param string $endpoint
     * @param int $response_code
     */
    private function track_api_call($provider, $endpoint, $response_code) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_api_calls';
        
        $wpdb->insert($table_name, array(
            'provider' => $provider,
            'endpoint' => $endpoint,
            'response_code' => $response_code,
            'cache_hit' => 0
        ));
    }
}