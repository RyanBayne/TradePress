<?php
/**
 * TradePress Enhanced Object Registry
 * 
 * Enhanced version with symbol data building and API integration
 * 
 * @package TradePress/Core
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Build or update symbol data in registry with fresh API data
 * 
 * @param string $symbol Symbol ticker
 * @param bool $force_refresh Force API refresh
 * @return object Symbol object with fresh data
 */
function tradepress_build_symbol_data( $symbol, $force_refresh = false ) {
    $registry_key = 'symbol_' . $symbol;
    
    // Get existing object or create new one
    $symbol_obj = TradePress_Object_Registry::get( $registry_key );
    
    if ( !$symbol_obj ) {
        // Create basic symbol object
        $symbol_obj = (object) array(
            'ticker' => $symbol,
            'name' => '',
            'price_data' => array(),
            'technical_data' => array(),
            'last_updated' => null,
            'data_sources' => array()
        );
    }
    
    // Check if refresh is needed
    $needs_refresh = $force_refresh || 
                    !$symbol_obj->last_updated || 
                    (time() - strtotime($symbol_obj->last_updated)) > 1800; // 30 minutes
    
    if ( $needs_refresh ) {
        // Load freshness manager
        if ( !class_exists('TradePress_Data_Freshness_Manager') ) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/data-freshness-manager.php';
        }
        
        // Update with fresh data
        $freshness_result = TradePress_Data_Freshness_Manager::ensure_data_freshness(
            $symbol,
            'scoring_algorithms',
            array('price_data', 'volume_data'),
            $force_refresh
        );
        
        // Update symbol object with fresh data
        if ( !empty($freshness_result['update_results']) ) {
            $symbol_obj->last_updated = current_time('mysql');
            $symbol_obj->data_sources = $freshness_result['update_results'];
            
            // Get fresh price data from database
            global $wpdb;
            $latest_price = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM {$wpdb->prefix}tradepress_price_history 
                WHERE symbol = %s 
                ORDER BY date DESC 
                LIMIT 1
            ", $symbol), ARRAY_A);
            
            if ( $latest_price ) {
                $symbol_obj->price_data = $latest_price;
            }
        }
    }
    
    // Store updated object in registry
    TradePress_Object_Registry::add( $registry_key, $symbol_obj );
    
    return $symbol_obj;
}

/**
 * Helper function to get symbol with fresh data
 * 
 * @param string $symbol Symbol ticker
 * @param bool $force_refresh Force API refresh
 * @return object Symbol object
 */
function tradepress_get_symbol( $symbol, $force_refresh = false ) {
    return tradepress_build_symbol_data( $symbol, $force_refresh );
}

/**
 * Helper function to get fresh symbol data
 * 
 * @param string $symbol Symbol ticker
 * @return object Symbol object with guaranteed fresh data
 */
function tradepress_get_fresh_symbol( $symbol ) {
    return tradepress_build_symbol_data( $symbol, true );
}