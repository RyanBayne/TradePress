<?php
/**
 * TradePress Data Freshness Manager
 * 
 * Gatekeeper layer ensuring data quality and freshness before algorithm execution.
 * Integrates with object registry to build symbol data on-demand with API fallbacks.
 * 
 * @package TradePress/Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Data_Freshness_Manager {
    
    /**
     * Freshness requirements by use case
     */
    private static $freshness_requirements = array(
        'cfd_trading' => array(
            'price_data' => 60,      // 1 minute
            'volume_data' => 300,    // 5 minutes
            'technical_indicators' => 300
        ),
        'swing_trading' => array(
            'price_data' => 3600,    // 1 hour
            'volume_data' => 3600,
            'technical_indicators' => 7200  // 2 hours
        ),
        'scoring_algorithms' => array(
            'price_data' => 1800,    // 30 minutes
            'fundamental_data' => 86400,  // 24 hours
            'technical_indicators' => 3600
        ),
        'test_button' => array(
            'price_data' => 0,       // Always fresh for testing
            'volume_data' => 0,
            'technical_indicators' => 0
        )
    );
    
    /**
     * Validate data freshness for a symbol and use case
     * 
     * @param string $symbol Symbol ticker
     * @param string $use_case Use case (cfd_trading, swing_trading, etc.)
     * @param array $required_data Types of data needed
     * @return array Validation result with fresh/stale data breakdown
     */
    public static function validate_data_freshness($symbol, $use_case, $required_data = array()) {
        $requirements = self::get_freshness_requirements($use_case);
        $validation_result = array(
            'symbol' => $symbol,
            'use_case' => $use_case,
            'overall_fresh' => true,
            'data_status' => array(),
            'stale_data' => array(),
            'missing_data' => array(),
            'update_needed' => false
        );
        
        foreach ($required_data as $data_type) {
            $status = self::check_data_type_freshness($symbol, $data_type, $requirements);
            $validation_result['data_status'][$data_type] = $status;
            
            if ($status['status'] === 'stale') {
                $validation_result['stale_data'][] = $data_type;
                $validation_result['overall_fresh'] = false;
                $validation_result['update_needed'] = true;
            } elseif ($status['status'] === 'missing') {
                $validation_result['missing_data'][] = $data_type;
                $validation_result['overall_fresh'] = false;
                $validation_result['update_needed'] = true;
            }
        }
        
        return $validation_result;
    }
    
    /**
     * Check freshness of specific data type for symbol
     * 
     * @param string $symbol Symbol ticker
     * @param string $data_type Type of data to check
     * @param array $requirements Freshness requirements
     * @return array Status information
     */
    private static function check_data_type_freshness($symbol, $data_type, $requirements) {
        global $wpdb;
        
        $max_age = isset($requirements[$data_type]) ? $requirements[$data_type] : 3600;
        $cutoff_time = current_time('mysql', true);
        
        // Load Call Register
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        // Check Call Register for cached data first
        $method = ($data_type === 'price_data') ? 'get_quote' : $data_type;
        $parameters = array('symbol' => $symbol);
        $serial = TradePress_Call_Register::generate_serial('alphavantage', $method, $parameters);
        $cached_call = TradePress_Call_Register::check_recent_call($serial, $max_age / 60);
        
        // Add developer notice for cache check
        if (class_exists('TradePress_Developer_Notices')) {
            TradePress_Developer_Notices::cache_check_notice('alphavantage', $method, $symbol, $cached_call, $parameters);
        }
        
        if ($cached_call['found']) {
            $age_seconds = $cached_call['age_minutes'] * 60;
            $is_fresh = ($max_age === 0) ? false : ($age_seconds <= $max_age);
            
            return array(
                'status' => $is_fresh ? 'fresh' : 'stale',
                'last_update' => date('Y-m-d H:i:s', $cached_call['timestamp']),
                'age_seconds' => $age_seconds,
                'max_age_seconds' => $max_age,
                'source' => 'call_register'
            );
        }
        
        // No cached data found
        return array(
            'status' => 'missing',
            'last_update' => null,
            'age_seconds' => null,
            'max_age_seconds' => $max_age,
            'source' => 'none'
        );
    }
    
    /**
     * Get freshness requirements for use case
     * 
     * @param string $use_case Use case identifier
     * @return array Requirements array
     */
    public static function get_freshness_requirements($use_case) {
        return isset(self::$freshness_requirements[$use_case]) 
            ? self::$freshness_requirements[$use_case] 
            : self::$freshness_requirements['scoring_algorithms'];
    }
    
    /**
     * Trigger data update for symbol with API fallback
     * 
     * @param string $symbol Symbol ticker
     * @param array $data_types Types of data to update
     * @param bool $force_api Force API call even if data exists
     * @return array Update results
     */
    public static function trigger_data_update($symbol, $data_types, $force_api = false) {
        // Load required classes
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $results = array(
            'symbol' => $symbol,
            'updates' => array(),
            'errors' => array(),
            'api_calls_made' => 0
        );
        
        // Get or create symbol in object registry
        $symbol_obj = self::get_or_create_symbol_object($symbol);
        
        foreach ($data_types as $data_type) {
            try {
                $update_result = self::update_data_type($symbol_obj, $data_type, $force_api);
                $results['updates'][$data_type] = $update_result;
                
                if ($update_result['api_called']) {
                    $results['api_calls_made']++;
                }
                
            } catch (Exception $e) {
                $results['errors'][$data_type] = $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Get or create symbol object in registry
     * 
     * @param string $symbol Symbol ticker
     * @return object Symbol object
     */
    private static function get_or_create_symbol_object($symbol) {
        $registry_key = 'symbol_' . $symbol;
        
        // Try to get from registry first
        $symbol_obj = TradePress_Object_Registry::get($registry_key);
        
        if (!$symbol_obj) {
            // Create new symbol object
            if (class_exists('TradePress_Symbol')) {
                $symbol_obj = new TradePress_Symbol($symbol);
            } else {
                // Fallback to basic object
                $symbol_obj = (object) array('ticker' => $symbol);
            }
            
            // Store in registry
            TradePress_Object_Registry::add($registry_key, $symbol_obj);
        }
        
        return $symbol_obj;
    }
    
    /**
     * Update specific data type for symbol
     * 
     * @param object $symbol_obj Symbol object
     * @param string $data_type Data type to update
     * @param bool $force_api Force API call
     * @return array Update result
     */
    private static function update_data_type($symbol_obj, $data_type, $force_api = false) {
        // Load required classes
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        if (!class_exists('TradePress_API_Directory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-directory.php';
        }
        
        $result = array(
            'data_type' => $data_type,
            'success' => false,
            'api_called' => false,
            'source' => 'none',
            'message' => ''
        );
        
        switch ($data_type) {
            case 'price_data':
                $result = self::update_price_data($symbol_obj, $force_api);
                break;
                
            case 'volume_data':
                $result = self::update_volume_data($symbol_obj, $force_api);
                break;
                
            case 'technical_indicators':
                $result = self::update_technical_indicators($symbol_obj, $force_api);
                break;
                
            default:
                $result['message'] = 'Unknown data type: ' . $data_type;
        }
        
        return $result;
    }
    
    /**
     * Update price data for symbol
     * 
     * @param object $symbol_obj Symbol object
     * @param bool $force_api Force API call
     * @return array Update result
     */
    private static function update_price_data($symbol_obj, $force_api = false) {
        // Load API factory if not available
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        // Try to get API instance
        $api = TradePress_API_Factory::create_from_settings('alphavantage');
        
        if (is_wp_error($api)) {
            return array(
                'data_type' => 'price_data',
                'success' => false,
                'api_called' => false,
                'source' => 'none',
                'message' => 'API not available: ' . $api->get_error_message()
            );
        }
        
        try {
            // Check cache first
            $cached_result = TradePress_Call_Register::get_cached_result('alphavantage', 'get_quote', array('symbol' => $symbol_obj->ticker), 15);
            
            if ($cached_result && !$force_api) {
                return array(
                    'data_type' => 'price_data',
                    'success' => true,
                    'api_called' => false,
                    'source' => 'cache',
                    'message' => 'Used cached price data'
                );
            }
            
            // Make API call
            $quote_data = $api->get_quote($symbol_obj->ticker);
            
            // Developer mode API call notice
            TradePress_Developer_Notices::api_call_notice('alphavantage', 'get_quote', $symbol_obj->ticker, $quote_data);
            
            // Log API call
            TradePress_Logging_Helper::log_calls('Alpha Vantage', 'Price data request', array($symbol_obj->ticker), is_wp_error($quote_data) ? 'error' : 'success');
            
            if (!is_wp_error($quote_data) && !empty($quote_data)) {
                // Store in database
                $store_result = self::store_price_data($symbol_obj->ticker, $quote_data);
                
                // Developer mode database notice
                TradePress_Developer_Notices::database_notice('INSERT', 'price_data', $quote_data, $store_result);
                
                return array(
                    'data_type' => 'price_data',
                    'success' => true,
                    'api_called' => true,
                    'source' => 'alphavantage',
                    'message' => 'Price data updated successfully'
                );
            } else {
                // Developer mode API error notice already handled above
                
                return array(
                    'data_type' => 'price_data',
                    'success' => false,
                    'api_called' => true,
                    'source' => 'alphavantage',
                    'message' => 'API call failed: ' . (is_wp_error($quote_data) ? $quote_data->get_error_message() : 'No data returned')
                );
            }
            
        } catch (Exception $e) {
            // Developer mode exception notice
            $error = new WP_Error('api_exception', $e->getMessage());
            TradePress_Developer_Notices::api_call_notice('alphavantage', 'get_quote', $symbol_obj->ticker, $error);
            
            return array(
                'data_type' => 'price_data',
                'success' => false,
                'api_called' => true,
                'source' => 'alphavantage',
                'message' => 'Exception: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Store price data in database and cache
     * 
     * @param string $symbol Symbol ticker
     * @param array $quote_data Quote data from API
     * @return bool Success status
     */
    private static function store_price_data($symbol, $quote_data) {
        global $wpdb;
        
        // Cache the data using Call Register (15 minutes default)
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        TradePress_Call_Register::cache_result('alphavantage', 'get_quote', array('symbol' => $symbol), $quote_data, 15);
        
        // Store in symbol meta table
        if (function_exists('tradepress_store_symbol_meta')) {
            tradepress_store_symbol_meta($symbol, 'last_price', $quote_data['price'] ?? 0);
            tradepress_store_symbol_meta($symbol, 'last_update', current_time('mysql'));
        }
        
        return true;
    }
    
    /**
     * Update volume data (usually comes with price data)
     * 
     * @param object $symbol_obj Symbol object
     * @param bool $force_api Force API call
     * @return array Update result
     */
    private static function update_volume_data($symbol_obj, $force_api = false) {
        // Volume data typically comes with price data
        return self::update_price_data($symbol_obj, $force_api);
    }
    
    /**
     * Update technical indicators
     * 
     * @param object $symbol_obj Symbol object
     * @param bool $force_api Force API call
     * @return array Update result
     */
    private static function update_technical_indicators($symbol_obj, $force_api = false) {
        // For now, return development notice
        return array(
            'data_type' => 'technical_indicators',
            'success' => false,
            'api_called' => false,
            'source' => 'none',
            'message' => 'Technical indicators calculation not yet implemented'
        );
    }
    
    /**
     * Check if data update is needed before algorithm execution
     * 
     * @param string $symbol Symbol ticker
     * @param string $use_case Use case for freshness requirements
     * @param array $required_data Required data types
     * @return bool True if update needed
     */
    public static function needs_update($symbol, $use_case, $required_data = array()) {
        $validation = self::validate_data_freshness($symbol, $use_case, $required_data);
        return $validation['update_needed'];
    }
    
    /**
     * Ensure data freshness before algorithm execution
     * 
     * @param string $symbol Symbol ticker
     * @param string $use_case Use case for freshness requirements
     * @param array $required_data Required data types
     * @param bool $force_update Force update regardless of freshness
     * @return array Validation and update results
     */
    public static function ensure_data_freshness($symbol, $use_case, $required_data = array(), $force_update = false) {
        // Load required classes
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $validation = self::validate_data_freshness($symbol, $use_case, $required_data);
        
        if ($validation['update_needed'] || $force_update) {
            $data_to_update = array_merge($validation['stale_data'], $validation['missing_data']);
            if ($force_update) {
                $data_to_update = $required_data;
            }
            
            $update_results = self::trigger_data_update($symbol, $data_to_update, $force_update);
            $validation['update_results'] = $update_results;
        }
        
        return $validation;
    }
}