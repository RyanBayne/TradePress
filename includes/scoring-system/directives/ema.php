<?php
/**
 * EMA (Exponential Moving Average) Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_EMA extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'ema';
        $this->name = 'Exponential Moving Average';
        $this->description = 'Weighted moving average giving more importance to recent prices';
        $this->weight = 10;
        $this->max_score = 100;
        $this->bullish_values = 'Price above EMA';
        $this->bearish_values = 'Price below EMA';
        $this->priority = 10;
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $trend_bonus = $config['trend_bonus'] ?? 20;
        $distance_multiplier = $config['distance_multiplier'] ?? 1.0;
        
        $current_price = $symbol_data['price'] ?? 0;
        $symbol = $symbol_data['symbol'] ?? 'UNKNOWN';
        
        // Get EMA data - fetch if not provided
        if (isset($symbol_data['technical']['ema'])) {
            $ema_value = $symbol_data['technical']['ema'];
        } else {
            // Fetch fresh EMA data
            $ema_value = $this->fetch_fresh_ema_data($symbol, $config);
            if (is_wp_error($ema_value)) {
                return array('score' => 0, 'signal' => 'API Error: ' . $ema_value->get_error_message());
            }
        }
        
        if (!$ema_value || $current_price <= 0) {
            return array('score' => 0, 'signal' => 'No EMA data');
        }
        
        // Calculate distance from EMA as percentage
        $distance_percent = (($current_price - $ema_value) / $ema_value) * 100;
        $abs_distance = abs($distance_percent);
        
        // Base score starts at 50 (neutral)
        $base_score = 50;
        
        // Determine trend and signal
        if ($current_price > $ema_value) {
            // Price above EMA - bullish
            $signal = 'Above EMA - Bullish';
            $base_score += $trend_bonus;
            
            // Additional bonus based on distance (closer = better for entry)
            if ($abs_distance <= 1) {
                $base_score += 15; // Very close to EMA
                $signal = 'Near EMA - Strong Support';
            } elseif ($abs_distance <= 3) {
                $base_score += 10; // Close to EMA
            } elseif ($abs_distance > 10) {
                $base_score -= 5; // Too far from EMA - potential pullback
                $signal = 'Far Above EMA - Pullback Risk';
            }
        } else {
            // Price below EMA - bearish
            $signal = 'Below EMA - Bearish';
            $base_score -= ($trend_bonus * 0.7);
            
            // Distance considerations for bearish trend
            if ($abs_distance <= 1) {
                $base_score += 10; // Close to EMA - potential bounce
                $signal = 'Near EMA - Potential Bounce';
            } elseif ($abs_distance > 10) {
                $base_score -= 10; // Very bearish
                $signal = 'Far Below EMA - Strong Bearish';
            }
        }
        
        // Apply distance multiplier
        $distance_adjustment = ($abs_distance * $distance_multiplier * 0.5);
        if ($current_price > $ema_value) {
            $base_score += min(15, $distance_adjustment);
        } else {
            $base_score -= min(10, $distance_adjustment);
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'ema_value' => round($ema_value, 2),
            'distance_percent' => round($distance_percent, 2),
            'trend' => $current_price > $ema_value ? 'Bullish' : 'Bearish'
        );
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $period = $config['period'] ?? 21;
        $trend_bonus = $config['trend_bonus'] ?? 20;
        $distance_multiplier = $config['distance_multiplier'] ?? 1.0;
        
        return "EMA (Exponential Moving Average) Directive:\n\n" .
               "Period: {$period} days\n" .
               "Trend Bonus: {$trend_bonus} points\n" .
               "Distance Multiplier: {$distance_multiplier}x\n\n" .
               "Scoring:\n" .
               "- Above EMA: +{$trend_bonus} points (bullish trend)\n" .
               "- Below EMA: -" . round($trend_bonus * 0.7) . " points (bearish trend)\n" .
               "- Near EMA (≤1%): +15 points (support/resistance)\n" .
               "- Close to EMA (≤3%): +10 points\n" .
               "- Far from EMA (>10%): -5 to -10 points\n\n" .
               "EMA responds faster to price changes than SMA, making it ideal for trend following.";
    }
    
    /**
     * Fetch fresh EMA data from API
     * 
     * @param string $symbol Symbol ticker
     * @param array $params Parameters
     * @return float EMA value or error
     */
    public function fetch_fresh_ema_data($symbol, $params = array()) {
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        $parameters = array(
            'time_period' => $params['period'] ?? 21,
            'interval' => $params['interval'] ?? 'daily'
        );
        
        // Check cache first
        $cached_result = TradePress_Call_Register::get_cached_result(
            'alphavantage', 
            'ema', 
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
        
        // Make API call for EMA data
        $ema_response = $api->make_request('EMA', array(
            'symbol' => $symbol,
            'interval' => $parameters['interval'],
            'time_period' => (string)$parameters['time_period'],
            'series_type' => 'close'
        ));
        
        if (is_wp_error($ema_response)) {
            return $ema_response;
        }
        
        // Extract the most recent EMA value
        $technical_analysis = $ema_response['Technical Analysis: EMA'] ?? array();
        
        if (empty($technical_analysis)) {
            return new WP_Error('no_ema_data', 'No EMA data in API response');
        }
        
        // Get the most recent date's data
        $latest_date = array_key_first($technical_analysis);
        $ema_value = (float) ($technical_analysis[$latest_date]['EMA'] ?? 0);
        
        // Cache the result for 30 minutes
        if ($ema_value > 0) {
            TradePress_Call_Register::cache_result(
                'alphavantage',
                'ema',
                array_merge(array('symbol' => $symbol), $parameters),
                $ema_value,
                30
            );
        }
        
        return $ema_value;
    }
}