<?php
/**
 * Bollinger Bands Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_BOLLINGER_BANDS extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'bollinger_bands';
        $this->name = 'Bollinger Bands';
        $this->description = 'Volatility indicator using standard deviations to create price channels';
        $this->weight = 15;
        $this->max_score = 100;
        $this->bullish_values = 'Price near lower band';
        $this->bearish_values = 'Price near upper band';
        $this->priority = 15;
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $period = $config['period'] ?? 20;
        $std_dev = $config['std_dev'] ?? 2.0;
        $squeeze_threshold = $config['squeeze_threshold'] ?? 0.1;
        
        $current_price = $symbol_data['price'] ?? 0;
        $symbol = $symbol_data['symbol'] ?? 'UNKNOWN';
        
        // Get Bollinger Bands data - fetch if not provided
        if (isset($symbol_data['technical']['bollinger_bands'])) {
            $bb_data = $symbol_data['technical']['bollinger_bands'];
        } else {
            // Fetch fresh Bollinger Bands data
            $bb_data = $this->fetch_fresh_bollinger_data($symbol, $config);
            if (is_wp_error($bb_data)) {
                return array('score' => 0, 'signal' => 'API Error: ' . $bb_data->get_error_message());
            }
        }
        
        if (!$bb_data || $current_price <= 0) {
            return array('score' => 0, 'signal' => 'No data');
        }
        
        $upper_band = $bb_data['upper_band'];
        $middle_band = $bb_data['middle_band']; // SMA
        $lower_band = $bb_data['lower_band'];
        
        // Calculate band width for squeeze detection
        $band_width = ($upper_band - $lower_band) / $middle_band;
        $is_squeeze = $band_width < $squeeze_threshold;
        
        // Calculate position within bands (0 = lower band, 1 = upper band)
        $band_position = ($current_price - $lower_band) / ($upper_band - $lower_band);
        $band_position = max(0, min(1, $band_position)); // Clamp between 0 and 1
        
        // Scoring logic
        $base_score = 50; // Neutral
        
        if ($is_squeeze) {
            // Squeeze condition - potential breakout
            $base_score += 20;
            $signal = 'Squeeze - Breakout Expected';
        } elseif ($band_position <= 0.2) {
            // Near lower band - oversold/bullish
            $base_score += 25;
            $signal = 'Near Lower Band - Bullish';
        } elseif ($band_position >= 0.8) {
            // Near upper band - overbought/bearish  
            $base_score -= 15;
            $signal = 'Near Upper Band - Bearish';
        } elseif ($band_position >= 0.4 && $band_position <= 0.6) {
            // Near middle band - neutral
            $base_score += 5;
            $signal = 'Near Middle Band - Neutral';
        } else {
            $signal = 'Between Bands';
        }
        
        return array(
            'score' => max(0, min(100, $base_score)),
            'signal' => $signal,
            'band_position' => round($band_position * 100, 1),
            'is_squeeze' => $is_squeeze,
            'band_width' => round($band_width * 100, 2),
            'upper_band' => $upper_band,
            'middle_band' => $middle_band,
            'lower_band' => $lower_band
        );
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $period = $config['period'] ?? 20;
        $std_dev = $config['std_dev'] ?? 2.0;
        $squeeze_threshold = $config['squeeze_threshold'] ?? 0.1;
        
        return "Bollinger Bands Directive:\n\n" .
               "Period: {$period} days\n" .
               "Standard Deviations: {$std_dev}\n" .
               "Squeeze Threshold: " . ($squeeze_threshold * 100) . "%\n\n" .
               "Scoring:\n" .
               "- Squeeze condition: +20 points (breakout potential)\n" .
               "- Near lower band (≤20%): +25 points (oversold/bullish)\n" .
               "- Near upper band (≥80%): -15 points (overbought/bearish)\n" .
               "- Near middle band: +5 points (neutral trend)\n\n" .
               "Bollinger Bands identify volatility and potential reversal points.";
    }
    
    /**
     * Fetch fresh Bollinger Bands data from API
     * 
     * @param string $symbol Symbol ticker
     * @param array $params Parameters
     * @return array Bollinger Bands data or error
     */
    public function fetch_fresh_bollinger_data($symbol, $params = array()) {
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        $parameters = array(
            'time_period' => $params['period'] ?? 20,
            'std_dev' => $params['std_dev'] ?? 2.0,
            'interval' => $params['interval'] ?? 'daily'
        );
        
        // Check cache first
        $cached_result = TradePress_Call_Register::get_cached_result(
            'alphavantage', 
            'bbands', 
            array_merge(array('symbol' => $symbol), $parameters), 
            30 // 30 minutes
        );
        
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        $api = TradePress_API_Factory::create_from_settings('alphavantage', 'paper', 'technical_indicators');
        
        if (is_wp_error($api)) {
            return $api;
        }
        
        // Make API call for Bollinger Bands data
        $bb_response = $api->make_request('BBANDS', array(
            'symbol' => $symbol,
            'interval' => $parameters['interval'],
            'time_period' => (string)$parameters['time_period'],
            'series_type' => 'close',
            'nbdevup' => (string)$parameters['std_dev'],
            'nbdevdn' => (string)$parameters['std_dev']
        ));
        
        if (is_wp_error($bb_response)) {
            return $bb_response;
        }
        
        // Extract the most recent Bollinger Bands values
        $technical_analysis = $bb_response['Technical Analysis: BBANDS'] ?? array();
        
        if (empty($technical_analysis)) {
            return new WP_Error('no_bb_data', 'No Bollinger Bands data in API response');
        }
        
        // Get the most recent date's data
        $latest_date = array_key_first($technical_analysis);
        $latest_data = $technical_analysis[$latest_date];
        
        $bb_data = array(
            'upper_band' => (float) ($latest_data['Real Upper Band'] ?? 0),
            'middle_band' => (float) ($latest_data['Real Middle Band'] ?? 0),
            'lower_band' => (float) ($latest_data['Real Lower Band'] ?? 0)
        );
        
        // Cache the result for 30 minutes
        TradePress_Call_Register::cache_result(
            'alphavantage',
            'bbands',
            array_merge(array('symbol' => $symbol), $parameters),
            $bb_data,
            30
        );
        
        return $bb_data;
    }
}