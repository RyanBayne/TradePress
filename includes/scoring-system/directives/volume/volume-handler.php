<?php
/**
 * Volume Analysis Directive Handler
 * Handles testing, validation, and admin UI for Volume directive
 */

class TradePress_Volume_Handler {
    
    public static function test_directive($symbol = 'AAPL', $trading_mode = 'long') {
        // Load API factory
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api/api-factory.php';
        }
        
        // Get quote data
        $api = TradePress_API_Factory::create_from_settings('alphavantage');
        if (is_wp_error($api)) {
            return array('success' => false, 'message' => 'Alpha Vantage API not configured');
        }
        
        $quote_data = $api->get_quote($symbol);
        if (is_wp_error($quote_data)) {
            return array('success' => false, 'message' => 'API call failed: ' . $quote_data->get_error_message());
        }
        
        // Prepare volume data
        $current_volume = $quote_data['volume'] ?? 0;
        $avg_volume = $quote_data['avg_volume'] ?? ($current_volume * 0.8);
        
        $symbol_data = array(
            'volume' => $current_volume,
            'avg_volume' => $avg_volume
        );
        
        // Load and test directive
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/volume.php';
        $directive = new TradePress_Scoring_Directive_Volume();
        
        $scores = array();
        if ($trading_mode === 'both') {
            $result = $directive->calculate_score($symbol_data, 'both');
            $scores = is_array($result) ? $result : array('long' => $result, 'short' => $result);
        } else {
            $result = $directive->calculate_score($symbol_data, $trading_mode);
            $scores[$trading_mode] = is_array($result) ? $result[$trading_mode] : $result;
        }
        
        $volume_ratio = $avg_volume > 0 ? round($current_volume / $avg_volume, 2) : 0;
        $volume_signal = $volume_ratio >= 1.5 ? 'High Volume' : ($volume_ratio <= 0.5 ? 'Low Volume' : 'Normal Volume');
        
        return array(
            'success' => true,
            'message' => 'Volume analysis test completed with LIVE Alpha Vantage data',
            'test_data' => array(
                'symbol' => $symbol,
                'current_price' => $quote_data['price'] ?? 0,
                'volume' => $current_volume,
                'avg_volume' => $avg_volume,
                'volume_ratio' => $volume_ratio,
                'volume_signal' => $volume_signal,
                'trading_mode' => $trading_mode,
                'scores' => $scores
            )
        );
    }
    
    public static function validate_config($data) {
        $validated = array();
        $validated['weight'] = max(0, min(100, intval($data['weight'] ?? 10)));
        $validated['base_multiplier'] = max(0.1, min(3.0, floatval($data['base_multiplier'] ?? 1.0)));
        $validated['high_volume_bonus'] = max(0, min(50, intval($data['high_volume_bonus'] ?? 25)));
        $validated['surge_bonus'] = max(0, min(100, intval($data['surge_bonus'] ?? 50)));
        $validated['min_score'] = max(0, min(100, floatval($data['min_score'] ?? 0)));
        
        return $validated;
    }
    
    public static function generate_warnings($data) {
        $warnings = array();
        
        if ($data['base_multiplier'] > 2.0) {
            $warnings[] = 'High base multiplier (>2.0) may cause excessive score volatility.';
        }
        
        if ($data['surge_bonus'] > 75) {
            $warnings[] = 'Very high surge bonus (>75) may cause volume spikes to dominate scoring.';
        }
        
        return $warnings;
    }
}