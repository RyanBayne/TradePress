<?php
/**
 * API Capability Matrix Cache System
 * Scans API platform classes to build capability matrix
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_API_Capability_Matrix {
    
    private static $cache_key = 'tradepress_api_capability_matrix';
    private static $cache_expiry = 86400; // 24 hours
    
    /**
     * Get capability matrix (from cache or build fresh)
     */
    public static function get_matrix() {
        $cached = get_transient(self::$cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        return self::build_matrix();
    }
    
    /**
     * Build capability matrix by scanning API classes
     */
    public static function build_matrix() {
        $matrix = array(
            'data_types' => array(),
            'platforms' => array(),
            'freshness_requirements' => array(),
            'last_updated' => time(),
            'expires' => time() + self::$cache_expiry
        );
        
        // Scan API directory for platform classes
        $api_dir = TRADEPRESS_PLUGIN_DIR_PATH . 'api/';
        $platforms = self::scan_api_platforms($api_dir);
        
        foreach ($platforms as $platform_id => $platform_data) {
            $matrix['platforms'][$platform_id] = $platform_data['capabilities'];
            
            // Build reverse mapping (data_type -> platforms)
            foreach ($platform_data['capabilities'] as $capability) {
                if (!isset($matrix['data_types'][$capability])) {
                    $matrix['data_types'][$capability] = array();
                }
                $matrix['data_types'][$capability][] = $platform_id;
            }
        }
        
        // Add directive freshness requirements
        $matrix['freshness_requirements'] = self::get_directive_freshness_requirements();
        
        // Cache for 24 hours
        set_transient(self::$cache_key, $matrix, self::$cache_expiry);
        
        return $matrix;
    }
    
    /**
     * Scan API platforms directory
     */
    private static function scan_api_platforms($api_dir) {
        $platforms = array();
        
        // Complete platform mappings with all data types
        $platform_capabilities = array(
            'alphavantage' => array('rsi', 'cci', 'macd', 'adx', 'sma', 'ema', 'quote', 'volume', 'bollinger_bands', 'stochastic', 'mfi', 'obv', 'vwap', 'candles', 'fundamentals', 'earnings', 'news'),
            'finnhub' => array('quote', 'candles', 'volume', 'news', 'earnings', 'fundamentals', 'rsi', 'macd', 'sma', 'ema'),
            'alpaca' => array('quote', 'volume', 'portfolio', 'candles', 'intraday', 'rsi', 'macd', 'sma', 'ema'),
            'iexcloud' => array('quote', 'volume', 'fundamentals', 'news', 'earnings', 'rsi', 'sma', 'ema'),
            'polygon' => array('quote', 'volume', 'candles', 'rsi', 'macd', 'sma', 'ema', 'cci', 'adx', 'vwap'),
            'twelvedata' => array('rsi', 'macd', 'sma', 'ema', 'bollinger_bands', 'stochastic', 'cci', 'adx', 'mfi', 'obv', 'quote', 'volume', 'vwap', 'candles'),
            'eodhd' => array('rsi', 'macd', 'sma', 'ema', 'cci', 'adx', 'bollinger_bands', 'stochastic', 'mfi', 'obv', 'quote', 'volume', 'fundamentals', 'candles'),
            'marketstack' => array('quote', 'volume', 'intraday', 'candles'),
            'intrinio' => array('quote', 'volume', 'fundamentals', 'rsi', 'sma', 'bollinger_bands', 'macd', 'ema', 'earnings'),
            'fmp' => array('quote', 'volume', 'fundamentals', 'rsi', 'macd', 'sma', 'ema', 'earnings', 'news'),
            'tradingview' => array('quote', 'candles', 'rsi', 'macd', 'sma', 'ema', 'bollinger_bands', 'volume'),
            'alltick' => array('quote', 'volume', 'candles', 'intraday', 'vwap'),
            'yahoo' => array('quote', 'volume', 'fundamentals', 'news', 'rsi', 'sma', 'ema', 'candles'),
            'ibkr' => array('quote', 'volume', 'portfolio', 'candles', 'rsi', 'macd', 'fundamentals'),
            'tradier' => array('quote', 'volume', 'portfolio', 'candles'),
            'trading212' => array('quote', 'volume', 'portfolio', 'rsi', 'sma', 'candles'),
            'fidelity' => array('quote', 'volume', 'portfolio', 'fundamentals', 'rsi', 'sma'),
            'etoro' => array('quote', 'volume', 'portfolio', 'candles'),
            'webull' => array('quote', 'volume', 'portfolio', 'rsi', 'macd', 'candles'),
            'geminiapi' => array('quote', 'volume', 'candles'),
            'github' => array('calendar'),
            'tradingapi' => array('quote', 'volume', 'portfolio')
        );
        
        foreach ($platform_capabilities as $platform_id => $capabilities) {
            $platforms[$platform_id] = array(
                'id' => $platform_id,
                'capabilities' => $capabilities,
                'name' => ucfirst($platform_id)
            );
        }
        
        return $platforms;
    }
    
    /**
     * Get freshness requirements from directive classes
     */
    private static function get_directive_freshness_requirements() {
        // Load directives register if not loaded
        if (!function_exists('tradepress_get_all_system_directives')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-register.php';
        }
        
        // Get all directives from register
        $all_directives = tradepress_get_all_system_directives();
        $requirements = array();
        
        // Map directive codes to data requirements
        foreach ($all_directives as $directive_id => $directive_data) {
            $data_type = self::map_directive_to_data_type($directive_id);
            if ($data_type) {
                $requirements[$data_type] = 1800; // Default 30 minutes
            }
        }
        
        // Override with specific requirements
        $specific_requirements = array(
            'volume' => 900,    // 15 minutes
            'quote' => 300,     // 5 minutes
        );
        
        return array_merge($requirements, $specific_requirements);
    }
    
    /**
     * Map directive ID to data type requirement
     */
    private static function map_directive_to_data_type($directive_id) {
        $mapping = array(
            'adx' => 'adx',
            'bollinger_band_squeeze' => 'bollinger_bands',
            'bollinger_bands' => 'bollinger_bands',
            'cci' => 'cci',
            'dividend_yield_attractive' => 'fundamentals',
            'earnings_proximity' => 'earnings',
            'ema' => 'ema',
            'isa_reset' => 'calendar',
            'ma_crossover' => 'sma',
            'macd' => 'macd',
            'macd_bullish_crossover' => 'macd',
            'mfi' => 'mfi',
            'moving_averages' => 'sma',
            'news_sentiment_positive' => 'news',
            'obv' => 'obv',
            'price_above_sma_50' => 'sma',
            'rsi' => 'rsi',
            'rsi_overbought' => 'rsi',
            'rsi_oversold' => 'rsi',
            'stochastic' => 'stochastic',
            'support_resistance_levels' => 'quote',
            'volume' => 'volume',
            'volume_surge' => 'volume',
            'vwap' => 'vwap',
            'doji_pattern' => 'candles',
            'engulfing_pattern' => 'candles',
            'hammer_pattern' => 'candles',
            'pin_bar_pattern' => 'candles',
            'three_soldiers_pattern' => 'candles',
            'confluence_reversal_combo' => 'quote',
            'momentum_continuation_combo' => 'quote',
            'price_action' => 'quote',
            'monday_effect' => 'quote',
            'friday_positioning' => 'quote',
            'volume_rhythm' => 'volume',
            'institutional_timing' => 'quote',
            'portfolio_performance' => 'portfolio',
            'midweek_momentum' => 'quote',
            'intraday_u_pattern' => 'intraday',
            'time_based_support' => 'vwap',
            'basic_weekly_rhythm' => 'quote',
            'advanced_weekly_rhythm' => 'quote'
        );
        
        return $mapping[$directive_id] ?? 'quote';
    }
    
    /**
     * Get platforms that support specific data type
     */
    public static function get_platforms_for_data_type($data_type) {
        $matrix = self::get_matrix();
        return $matrix['data_types'][$data_type] ?? array();
    }
    
    /**
     * Get capabilities for specific platform
     */
    public static function get_platform_capabilities($platform_id) {
        $matrix = self::get_matrix();
        return $matrix['platforms'][$platform_id] ?? array();
    }
    
    /**
     * Check if platform supports data type
     */
    public static function platform_supports($platform_id, $data_type) {
        $capabilities = self::get_platform_capabilities($platform_id);
        return in_array($data_type, $capabilities);
    }
    
    /**
     * Get freshness requirement for data type
     */
    public static function get_freshness_requirement($data_type) {
        $matrix = self::get_matrix();
        return $matrix['freshness_requirements'][$data_type] ?? 1800;
    }
    
    /**
     * Manually refresh cache
     */
    public static function refresh_cache() {
        delete_transient(self::$cache_key);
        return self::build_matrix();
    }
    
    /**
     * Get cache status
     */
    public static function get_cache_status() {
        $matrix = self::get_matrix();
        return array(
            'cached' => get_transient(self::$cache_key) !== false,
            'last_updated' => $matrix['last_updated'],
            'expires' => $matrix['expires'],
            'platforms_count' => count($matrix['platforms']),
            'data_types_count' => count($matrix['data_types'])
        );
    }
}