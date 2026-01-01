<?php
/**
 * TradePress API Factory
 * 
 * Factory class for creating API instances with proper validation and configuration.
 * Ensures all APIs are created consistently and with proper error handling.
 * 
 * @package TradePress/API
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_API_Factory {
    
    /**
     * Create an API instance
     * 
     * @param string $provider_id API provider ID
     * @param array $args Configuration arguments
     * @return TradePress_Base_API|WP_Error API instance or error
     */
    public static function create($provider_id, $args = array()) {
        // Get provider configuration
        $provider = TradePress_API_Directory::get_provider($provider_id);
        
        if (!$provider) {
            return new WP_Error(
                'unknown_provider',
                sprintf('Unknown API provider: %s', $provider_id)
            );
        }
        
        // Build class name
        $class_name = $provider['class_name'];
        
        // Check if class file exists
        $class_file = TRADEPRESS_PLUGIN_DIR_PATH . 'api/' . $provider['class_path'];
        
        if (!file_exists($class_file)) {
            return new WP_Error(
                'missing_class_file',
                sprintf('API class file not found: %s', $class_file)
            );
        }
        
        // Include the class file
        require_once $class_file;
        
        // Check if class exists
        if (!class_exists($class_name)) {
            return new WP_Error(
                'missing_class',
                sprintf('API class not found: %s', $class_name)
            );
        }
        
        // Create instance
        try {
            $instance = new $class_name($provider_id, $args);
            
            // Validate that it extends the base class
            if (!($instance instanceof TradePress_Base_API)) {
                return new WP_Error(
                    'invalid_base_class',
                    sprintf('API class %s must extend TradePress_Base_API', $class_name)
                );
            }
            
            return $instance;
            
        } catch (Exception $e) {
            return new WP_Error(
                'instantiation_error',
                sprintf('Failed to create API instance: %s', $e->getMessage())
            );
        }
    }
    
    /**
     * Create API instance from saved settings with fallback
     * 
     * @param string|null $provider_id API provider ID (null for automatic selection)
     * @param string $mode Trading mode (paper/live) for trading APIs
     * @param string $data_type Optional data type for intelligent fallback
     * @return TradePress_Base_API|WP_Error API instance or error
     */
    public static function create_from_settings($provider_id = null, $mode = 'paper', $data_type = null) {
        // Load usage tracker
        if (!class_exists('TradePress_API_Usage_Tracker')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
        }
        
        // If no provider specified, use automatic selection
        if ($provider_id === null) {
            $fallback_api = TradePress_API_Usage_Tracker::get_best_api_for_data($data_type ?: 'technical_indicators');
            if (!is_wp_error($fallback_api)) {
                return $fallback_api;
            }
            // Fallback to alphavantage if automatic selection fails
            $provider_id = 'alphavantage';
        }
        
        // Check if primary API is rate limited and use fallback if needed
        if ($data_type && TradePress_API_Usage_Tracker::is_likely_rate_limited($provider_id)) {
            $fallback_api = TradePress_API_Usage_Tracker::get_best_api_for_data($data_type);
            if (!is_wp_error($fallback_api)) {
                return $fallback_api;
            }
        }
        
        $provider = TradePress_API_Directory::get_provider($provider_id);
        
        if (!$provider) {
            return new WP_Error(
                'unknown_provider',
                sprintf('Unknown API provider: %s', $provider_id)
            );
        }
        
        // Build configuration from saved settings
        $args = array();
        
        // Get API keys based on provider type and mode
        if ($provider['api_type'] === 'trading') {
            // Trading APIs have paper/live modes
            if ($mode === 'live') {
                $args['api_key'] = get_option("TradePress_api_{$provider_id}_realmoney_apikey", '');
                $args['api_secret'] = get_option("TradePress_api_{$provider_id}_realmoney_secretkey", '');
            } else {
                $args['api_key'] = get_option("TradePress_api_{$provider_id}_papermoney_apikey", '');
                $args['api_secret'] = get_option("TradePress_api_{$provider_id}_papermoney_secretkey", '');
            }
            $args['mode'] = $mode;
        } else {
            // Data-only APIs just have API key
            $args['api_key'] = get_option("TradePress_api_{$provider_id}_key", '');
        }
        
        $api = self::create($provider_id, $args);
        
        // Track successful creation
        if (!is_wp_error($api)) {
            TradePress_API_Usage_Tracker::track_call($provider_id, 'connection_test', true);
        }
        
        return $api;
    }
    
    /**
     * Get all available API providers
     * 
     * @param string $type Optional. Filter by API type (trading, data_only, messaging)
     * @return array Array of provider configurations
     */
    public static function get_available_providers($type = '') {
        $providers = TradePress_API_Directory::get_all_providers();
        
        if (empty($type)) {
            return $providers;
        }
        
        return array_filter($providers, function($provider) use ($type) {
            return isset($provider['api_type']) && $provider['api_type'] === $type;
        });
    }
    
    /**
     * Test all configured APIs
     * 
     * @return array Test results for each API
     */
    public static function test_all_apis() {
        $results = array();
        $providers = self::get_available_providers();
        
        foreach ($providers as $provider_id => $provider) {
            // Check if API is enabled
            $enabled = get_option("TradePress_switch_{$provider_id}_api_services", 'no');
            
            if ($enabled !== 'yes') {
                $results[$provider_id] = array(
                    'status' => 'disabled',
                    'message' => 'API is disabled in settings'
                );
                continue;
            }
            
            // Create API instance
            $api = self::create_from_settings($provider_id);
            
            if (is_wp_error($api)) {
                $results[$provider_id] = array(
                    'status' => 'error',
                    'message' => $api->get_error_message()
                );
                continue;
            }
            
            // Test connection
            $test_result = $api->test_connection();
            
            if (is_wp_error($test_result)) {
                $results[$provider_id] = array(
                    'status' => 'error',
                    'message' => $test_result->get_error_message()
                );
            } else {
                $results[$provider_id] = array(
                    'status' => 'success',
                    'message' => 'Connection successful'
                );
            }
        }
        
        return $results;
    }
}