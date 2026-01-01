<?php
/**
 * TradePress Volatility Monitor
 *
 * Monitors market volatility levels and provides metrics for 
 * risk assessment and scheduling decisions.
 *
 * @package TradePress
 * @subpackage Risks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Volatility_Monitor' ) ) :

/**
 * Volatility Monitor Class
 */
class TradePress_Volatility_Monitor {
    
    /**
     * Logger instance
     *
     * @var TradePress_Logger
     */
    protected $logger;
    
    /**
     * Market data provider
     *
     * @var TradePress_Market_Data
     */
    protected $market_data;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->logger = TradePress_Logger::get_instance();
        $this->market_data = new TradePress_Market_Data();
    }
    
    /**
     * Get current market volatility level
     *
     * @return string 'low', 'medium', or 'high'
     */
    public function get_market_volatility_level() {
        // Cache result to avoid repeated API calls
        $cache_key = 'tradepress_volatility_level';
        $cached_level = get_transient($cache_key);
        
        if (false !== $cached_level) {
            return $cached_level;
        }
        
        $volatility_value = $this->get_market_volatility_value();
        
        // Determine volatility level based on numerical value
        $level = 'low'; // Default
        
        if ($volatility_value >= 0.75) {
            $level = 'high';
        } elseif ($volatility_value >= 0.35) {
            $level = 'medium';
        }
        
        // Cache for 30 minutes
        set_transient($cache_key, $level, 30 * MINUTE_IN_SECONDS);
        
        return $level;
    }
    
    /**
     * Get numerical volatility value (0-1 scale)
     *
     * @return float Volatility value from 0 (low) to 1 (high)
     */
    public function get_market_volatility_value() {
        // Combine multiple volatility indicators
        $vix_factor = $this->get_vix_factor();
        $index_movement_factor = $this->get_index_movement_factor();
        $atr_factor = $this->get_average_atr_factor();
        $unusual_conditions = $this->detect_unusual_market_conditions();
        
        // Weight the factors
        $volatility_value = 
            ($vix_factor * 0.4) + 
            ($index_movement_factor * 0.3) + 
            ($atr_factor * 0.2) + 
            ($unusual_conditions * 0.1);
        
        return min(1, max(0, $volatility_value));
    }
    
    /**
     * Get VIX-based volatility factor
     *
     * @return float Normalized VIX factor (0-1)
     */
    private function get_vix_factor() {
        // Get VIX data from market data provider
        $vix_data = $this->market_data->get_market_data('VIX', 1);
        
        if (empty($vix_data)) {
            $this->logger->log('Warning: Unable to retrieve VIX data for volatility calculation');
            return 0.5; // Default to medium if data unavailable
        }
        
        // Extract latest VIX value
        $vix_value = $vix_data[0]['close'];
        
        // Normalize VIX value to 0-1 scale
        // VIX < 15 is considered low volatility
        // VIX > 30 is considered high volatility
        if ($vix_value <= 15) {
            return 0;
        } elseif ($vix_value >= 30) {
            return 1;
        } else {
            return ($vix_value - 15) / 15; // Linear scaling between 15-30
        }
    }
    
    /**
     * Get index movement factor based on major indices
     *
     * @return float Normalized index movement factor (0-1)
     */
    private function get_index_movement_factor() {
        // Use major indices (S&P 500, NASDAQ, Russell 2000)
        $indices = ['SPY', 'QQQ', 'IWM'];
        $total_movement = 0;
        $count = 0;
        
        foreach ($indices as $index) {
            $data = $this->market_data->get_market_data($index, 2); // Today and yesterday
            
            if (!empty($data) && count($data) >= 2) {
                $current = $data[0]['close'];
                $previous = $data[1]['close'];
                $percent_change = abs(($current - $previous) / $previous * 100);
                $total_movement += $percent_change;
                $count++;
            }
        }
        
        if ($count === 0) {
            return 0.5; // Default to medium if no data
        }
        
        $avg_movement = $total_movement / $count;
        
        // Normalize: <0.5% is low, >2% is high
        if ($avg_movement <= 0.5) {
            return 0;
        } elseif ($avg_movement >= 2) {
            return 1;
        } else {
            return ($avg_movement - 0.5) / 1.5; // Linear scaling
        }
    }
    
    /**
     * Get average ATR factor for major stocks/ETFs
     *
     * @return float Normalized ATR factor (0-1)
     */
    private function get_average_atr_factor() {
        // Use representative ETFs
        $symbols = ['SPY', 'QQQ', 'IWM', 'DIA'];
        $total_atr_percent = 0;
        $count = 0;
        
        foreach ($symbols as $symbol) {
            $atr = $this->calculate_atr($symbol, 14); // 14-day ATR
            
            if ($atr > 0) {
                $price = $this->market_data->get_latest_price($symbol);
                if ($price > 0) {
                    $atr_percent = ($atr / $price) * 100;
                    $total_atr_percent += $atr_percent;
                    $count++;
                }
            }
        }
        
        if ($count === 0) {
            return 0.5; // Default to medium if no data
        }
        
        $avg_atr_percent = $total_atr_percent / $count;
        
        // Normalize: <1% is low, >3% is high
        if ($avg_atr_percent <= 1) {
            return 0;
        } elseif ($avg_atr_percent >= 3) {
            return 1;
        } else {
            return ($avg_atr_percent - 1) / 2; // Linear scaling
        }
    }
    
    /**
     * Calculate ATR (Average True Range) for a symbol
     *
     * @param string $symbol Stock/ETF symbol
     * @param int $period Period for ATR calculation
     * @return float ATR value or 0 if insufficient data
     */
    private function calculate_atr($symbol, $period) {
        $data = $this->market_data->get_market_data($symbol, $period + 1);
        
        if (count($data) < $period + 1) {
            return 0;
        }
        
        $tr_sum = 0;
        
        for ($i = 0; $i < $period; $i++) {
            $high = $data[$i]['high'];
            $low = $data[$i]['low'];
            $prev_close = $data[$i + 1]['close'];
            
            // True Range = max(high - low, abs(high - prev_close), abs(low - prev_close))
            $tr = max(
                $high - $low,
                abs($high - $prev_close),
                abs($low - $prev_close)
            );
            
            $tr_sum += $tr;
        }
        
        return $tr_sum / $period;
    }
    
    /**
     * Detect unusual market conditions
     *
     * @return float Factor indicating unusual conditions (0-1)
     */
    private function detect_unusual_market_conditions() {
        $unusual_factor = 0;
        
        // Check for unusual volatility
        if ($this->detect_unusual_volatility()) {
            $unusual_factor += 0.25;
        }
        
        // Check for correlation breakdown
        if ($this->detect_correlation_breakdown()) {
            $unusual_factor += 0.25;
        }
        
        // Check for liquidity crisis
        if ($this->detect_liquidity_crisis()) {
            $unusual_factor += 0.25;
        }
        
        // Check for black swan event
        if ($this->detect_black_swan_event()) {
            $unusual_factor += 0.25;
        }
        
        return $unusual_factor;
    }
    
    /**
     * Detect unusual volatility
     *
     * @return bool True if unusual volatility detected
     */
    private function detect_unusual_volatility() {
        // Get VIX data
        $vix_data = $this->market_data->get_market_data('VIX', 10); // Last 10 days
        
        if (empty($vix_data) || count($vix_data) < 5) {
            return false;
        }
        
        // Calculate average VIX and today's value
        $avg_vix = 0;
        for ($i = 1; $i < count($vix_data); $i++) {
            $avg_vix += $vix_data[$i]['close'];
        }
        $avg_vix /= (count($vix_data) - 1);
        $today_vix = $vix_data[0]['close'];
        
        // Calculate percentage change
        $vix_change = (($today_vix - $avg_vix) / $avg_vix) * 100;
        
        // Significant increase in VIX indicates unusual volatility
        return $vix_change > 20; // 20% increase from average
    }
    
    /**
     * Detect correlation breakdown between major indices
     *
     * @return bool True if correlation breakdown detected
     */
    private function detect_correlation_breakdown() {
        // Get major indices data
        $indices = ['SPY', 'QQQ', 'IWM', 'DIA'];
        $correlation_breakdown_count = 0;
        
        // Simplified implementation - check if indices moving in opposite directions
        $direction = [];
        
        foreach ($indices as $index) {
            $data = $this->market_data->get_market_data($index, 2); // Today and yesterday
            
            if (!empty($data) && count($data) >= 2) {
                $current = $data[0]['close'];
                $previous = $data[1]['close'];
                $direction[$index] = ($current > $previous) ? 'up' : 'down';
            }
        }
        
        // Count occurrences of each direction
        $up_count = 0;
        $down_count = 0;
        
        foreach ($direction as $dir) {
            if ($dir === 'up') {
                $up_count++;
            } else {
                $down_count++;
            }
        }
        
        // If some indices are up and others are down significantly, it may indicate correlation breakdown
        return ($up_count > 0 && $down_count > 0 && min($up_count, $down_count) >= 2);
    }
    
    /**
     * Detect liquidity crisis
     *
     * @return bool True if liquidity crisis detected
     */
    private function detect_liquidity_crisis() {
        // Check bid-ask spreads on major ETFs
        $etfs = ['SPY', 'QQQ', 'IWM'];
        $spread_increases = 0;
        
        foreach ($etfs as $etf) {
            $current_spread = $this->get_current_spread($etf);
            $average_spread = $this->get_average_spread($etf, 30); // 30-day average
            
            if ($current_spread > 0 && $average_spread > 0) {
                // Calculate increase
                $spread_increase = (($current_spread - $average_spread) / $average_spread) * 100;
                
                if ($spread_increase > 200) { // 200% increase in spread
                    $spread_increases++;
                }
            }
        }
        
        // Also check daily volume
        $volume_decreases = 0;
        foreach ($etfs as $etf) {
            $current_volume = $this->get_current_volume($etf);
            $average_volume = $this->get_average_volume($etf, 30); // 30-day average
            
            if ($current_volume > 0 && $average_volume > 0) {
                // Calculate decrease
                $volume_decrease = (($average_volume - $current_volume) / $average_volume) * 100;
                
                if ($volume_decrease > 40) { // 40% decrease in volume
                    $volume_decreases++;
                }
            }
        }
        
        // If both spreads are widening and volume is decreasing across multiple ETFs
        return ($spread_increases >= 2 && $volume_decreases >= 2);
    }
    
    /**
     * Get current bid-ask spread for a symbol
     *
     * @param string $symbol Stock/ETF symbol
     * @return float Current spread value
     */
    private function get_current_spread($symbol) {
        // Implementation depends on your market data provider
        // This is a placeholder
        return 0.01; // Default value
    }
    
    /**
     * Get average spread for a symbol over time period
     *
     * @param string $symbol Stock/ETF symbol
     * @param int $days Number of days for average
     * @return float Average spread value
     */
    private function get_average_spread($symbol, $days) {
        // Implementation depends on your market data provider
        // This is a placeholder
        return 0.01; // Default value
    }
    
    /**
     * Get current trading volume for a symbol
     *
     * @param string $symbol Stock/ETF symbol
     * @return int Current volume
     */
    private function get_current_volume($symbol) {
        $data = $this->market_data->get_market_data($symbol, 1);
        
        if (!empty($data)) {
            return isset($data[0]['volume']) ? $data[0]['volume'] : 0;
        }
        
        return 0;
    }
    
    /**
     * Get average volume for a symbol over time period
     *
     * @param string $symbol Stock/ETF symbol
     * @param int $days Number of days for average
     * @return int Average volume
     */
    private function get_average_volume($symbol, $days) {
        $data = $this->market_data->get_market_data($symbol, $days);
        
        if (empty($data)) {
            return 0;
        }
        
        $total_volume = 0;
        foreach ($data as $day) {
            $total_volume += isset($day['volume']) ? $day['volume'] : 0;
        }
        
        return $total_volume / count($data);
    }
    
    /**
     * Detect black swan event
     *
     * @return bool True if black swan event detected
     */
    private function detect_black_swan_event() {
        // Check for extreme market moves
        $spy_data = $this->market_data->get_market_data('SPY', 2); // Today and yesterday
        
        if (count($spy_data) < 2) {
            return false;
        }
        
        $daily_change = (($spy_data[0]['close'] - $spy_data[1]['close']) / $spy_data[1]['close']) * 100;
        
        // Check VIX spike
        $vix_data = $this->market_data->get_market_data('VIX', 2);
        $vix_change = 0;
        
        if (count($vix_data) >= 2) {
            $vix_change = (($vix_data[0]['close'] - $vix_data[1]['close']) / $vix_data[1]['close']) * 100;
        }
        
        // Black swan event criteria:
        // - SPY moves more than 4% in a single day OR
        // - VIX spikes more than 40% in a single day
        return (abs($daily_change) > 4 || $vix_change > 40);
    }
}

endif; // End if class_exists check
