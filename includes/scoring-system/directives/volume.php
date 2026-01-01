<?php
/**
 * Volume Scoring Directive
 *
 * @package TradePress/ScoringDirectives
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Scoring_Directive_Volume Class
 */
class TradePress_Scoring_Directive_Volume extends TradePress_Scoring_Directive_Base {
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'volume';
        $this->name = 'Volume Analysis';
        $this->description = 'Scores symbols based on trading volume relative to average volume.';
        $this->weight = 15;
        $this->bullish_values = '> 1.5x Average';
        $this->bearish_values = '< 0.5x Average';
        $this->priority = 30;
        
        // Data freshness requirements (in seconds)
        $this->data_freshness_requirements = array(
            'volume_data' => 600,    // 10 minutes for volume data (more frequent)
            'price_data' => 900,     // 15 minutes for underlying price data
            'avg_volume' => 3600     // 1 hour for average volume calculation
        );
    }
    
    /**
     * Calculate score based on volume
     *
     * @param array $symbol_data Symbol data
     * @param string $trading_mode Trading mode ('long', 'short', 'both')
     * @return int|array Score from 0-100 (int for single mode, array for both)
     */
    public function calculate_score($symbol_data, $trading_mode = 'long') {
        // Check data freshness before processing
        $symbol = $symbol_data['symbol'] ?? 'UNKNOWN';
        $this->check_data_freshness($symbol, array('volume_data', 'price_data', 'avg_volume'));
        
        // Get configuration
        $config = get_option('tradepress_directive_volume', array());
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $high_volume_bonus = $config['high_volume_bonus'] ?? 25;
        $surge_bonus = $config['surge_bonus'] ?? 50;
        $min_score = $config['min_score'] ?? 0;
        
        // Log calculation start
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D22 Volume | {$symbol} | Starting calculation with base_multiplier={$base_multiplier}, high_bonus={$high_volume_bonus}, surge_bonus={$surge_bonus}, min_score={$min_score}");
        }
        
        // Get volume data - fetch if not provided
        if (isset($symbol_data['volume']) && isset($symbol_data['avg_volume'])) {
            $current_volume = $symbol_data['volume'];
            $avg_volume = $symbol_data['avg_volume'];
        } else {
            // Fetch fresh volume data
            $volume_data = $this->fetch_fresh_volume_data($symbol);
            if (is_wp_error($volume_data)) {
                if (get_option('bugnet_output_directives') === 'yes') {
                    TradePress_Directive_Logger::log("D22 Volume | {$symbol} | ERROR: " . $volume_data->get_error_message());
                }
                return $trading_mode === 'both' ? array('long' => 50, 'short' => 50) : 50;
            }
            $current_volume = $volume_data['volume'];
            $avg_volume = $volume_data['avg_volume'];
        }
        
        // If we don't have valid data, return neutral score
        if ($current_volume <= 0 || $avg_volume <= 0) {
            if (get_option('bugnet_output_directives') === 'yes') {
                TradePress_Directive_Logger::log("D22 Volume | {$symbol} | ERROR: Invalid volume data - current={$current_volume}, avg={$avg_volume}");
            }
            return $trading_mode === 'both' ? array('long' => 50, 'short' => 50) : 50;
        }
        
        // Calculate volume ratio
        $volume_ratio = $current_volume / $avg_volume;
        
        // Enhanced logging for developer mode
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            $volume_condition = $volume_ratio >= 3.0 ? 'SURGE' : ($volume_ratio >= 1.5 ? 'HIGH' : ($volume_ratio <= 0.5 ? 'LOW' : 'NORMAL'));
            TradePress_Directive_Logger::log("D22 Volume | {$symbol} | Current={$current_volume} Avg={$avg_volume} Ratio={$volume_ratio} Condition={$volume_condition}");
        }
        
        $volume_condition = $volume_ratio >= 3.0 ? 'SURGE' : ($volume_ratio >= 1.5 ? 'HIGH' : ($volume_ratio <= 0.5 ? 'LOW' : 'NORMAL'));
        $signal = $volume_condition === 'SURGE' ? 'Volume Surge' : ($volume_condition === 'HIGH' ? 'High Volume' : ($volume_condition === 'LOW' ? 'Low Volume' : 'Normal Volume'));
        
        if ($trading_mode === 'both') {
            $long_score = max($min_score, $this->calculate_single_score($volume_ratio, $base_multiplier, $high_volume_bonus, $surge_bonus, $symbol));
            $short_score = max($min_score, $this->calculate_single_score($volume_ratio, $base_multiplier, $high_volume_bonus, $surge_bonus, $symbol));
            return array(
                'long' => array(
                    'score' => $long_score,
                    'volume' => $current_volume,
                    'avg_volume' => $avg_volume,
                    'volume_ratio' => $volume_ratio,
                    'signal' => $signal
                ),
                'short' => array(
                    'score' => $short_score,
                    'volume' => $current_volume,
                    'avg_volume' => $avg_volume,
                    'volume_ratio' => $volume_ratio,
                    'signal' => $signal
                )
            );
        }
        
        $final_score = max($min_score, $this->calculate_single_score($volume_ratio, $base_multiplier, $high_volume_bonus, $surge_bonus, $symbol));
        
        return array(
            'score' => $final_score,
            'volume' => $current_volume,
            'avg_volume' => $avg_volume,
            'volume_ratio' => $volume_ratio,
            'signal' => $signal
        );
    }
    
    /**
     * Calculate single score for volume ratio
     */
    private function calculate_single_score($volume_ratio, $base_multiplier, $high_volume_bonus, $surge_bonus, $symbol = 'UNKNOWN') {
        // Base score from volume ratio
        $base_score = 50 + (($volume_ratio - 1) * 25 * $base_multiplier);
        
        // Apply bonuses
        $bonus = 0;
        if ($volume_ratio >= 3.0) {
            // Volume surge (3x+ average)
            $bonus = $high_volume_bonus + $surge_bonus;
        } elseif ($volume_ratio >= 1.5) {
            // High volume (1.5x+ average)
            $bonus = $high_volume_bonus;
        }
        
        $final_score = round(max(0, $base_score + $bonus));
        
        // Enhanced logging for developer mode
        if (get_option('bugnet_output_directives') === 'yes') {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            TradePress_Directive_Logger::log("D22 Volume | {$symbol} | Base_Score={$base_score} Bonus={$bonus} | Final_Score={$final_score}");
        }
        
        return $final_score;
    }
    
    /**
     * Get maximum possible score
     *
     * @param array $config Configuration array (optional)
     * @return int Maximum score
     */
    public function get_max_score($config = []) {
        if (empty($config)) {
            $config = get_option('tradepress_directive_volume', array());
        }
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $high_volume_bonus = $config['high_volume_bonus'] ?? 25;
        $surge_bonus = $config['surge_bonus'] ?? 50;
        
        // Maximum base score (assuming very high volume ratio)
        $max_base = 50 + (10 * 25 * $base_multiplier); // Assuming 10x volume ratio
        return $max_base + $high_volume_bonus + $surge_bonus;
    }
    
    /**
     * Get scoring explanation
     *
     * @param array $config Configuration array (optional)
     * @return string Explanation of how scoring works
     */
    public function get_explanation($config = []) {
        if (empty($config)) {
            $config = get_option('tradepress_directive_volume', array());
        }
        $base_multiplier = $config['base_multiplier'] ?? 1.0;
        $high_volume_bonus = $config['high_volume_bonus'] ?? 25;
        $surge_bonus = $config['surge_bonus'] ?? 50;
        
        return "Volume Analysis Scoring:\n\n" .
               "Base Formula: 50 + ((volume_ratio - 1) × 25 × {$base_multiplier})\n\n" .
               "Bonuses:\n" .
               "• High Volume (≥1.5x avg): +{$high_volume_bonus} points\n" .
               "• Volume Surge (≥3.0x avg): +{$surge_bonus} additional points\n\n" .
               "Volume Ratio = Current Volume ÷ Average Volume\n\n" .
               "Examples:\n" .
               "• 2x average volume: 50 + (1 × 25 × {$base_multiplier}) + {$high_volume_bonus} = " . (50 + (25 * $base_multiplier) + $high_volume_bonus) . " points\n" .
               "• 4x average volume: 50 + (3 × 25 × {$base_multiplier}) + {$high_volume_bonus} + {$surge_bonus} = " . (50 + (75 * $base_multiplier) + $high_volume_bonus + $surge_bonus) . " points\n\n" .
               "Data Freshness Requirements:\n" .
               "- Volume Data: 10 minutes\n" .
               "- Price Data: 15 minutes\n" .
               "- Average Volume: 1 hour";
    }
    
    /**
     * Check data freshness for this directive
     */
    private function check_data_freshness($symbol, $required_data) {
        if (!class_exists('TradePress_Data_Freshness_Manager')) {
            return;
        }
        
        $validation = TradePress_Data_Freshness_Manager::validate_data_freshness(
            $symbol,
            'scoring_algorithms',
            $required_data
        );
        
        if (class_exists('TradePress_Developer_Notices')) {
            TradePress_Developer_Notices::data_freshness_notice(
                'D22 Volume',
                $symbol,
                $validation,
                $this->data_freshness_requirements
            );
        }
    }
    
    public function get_data_freshness_requirements() {
        return $this->data_freshness_requirements;
    }
    
    /**
     * Fetch fresh volume data from API
     * 
     * @param string $symbol Symbol ticker
     * @return array Volume data or error
     */
    public function fetch_fresh_volume_data($symbol) {
        if (!class_exists('TradePress_API_Factory')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';
        }
        
        $api = TradePress_API_Factory::create_from_settings('alphavantage', 'paper', 'quote');
        
        if (is_wp_error($api)) {
            return $api;
        }
        
        // Get quote data which includes volume
        $quote_data = $api->get_quote($symbol);
        
        if (is_wp_error($quote_data)) {
            return $quote_data;
        }
        
        $current_volume = $quote_data['volume'] ?? 0;
        $avg_volume = $quote_data['avg_volume'] ?? ($current_volume * 0.8); // Fallback estimate
        
        return array(
            'volume' => $current_volume,
            'avg_volume' => $avg_volume,
            'symbol' => $symbol
        );
    }
}
