<?php
/**
 * TradePress Position Risk Calculator
 *
 * Calculates risk metrics for individual trading positions.
 *
 * @package TradePress
 * @subpackage Risks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Position_Risk_Calculator' ) ) :

/**
 * Position Risk Calculator Class
 */
class TradePress_Position_Risk_Calculator {
    
    /**
     * Position data
     *
     * @var array
     */
    private $position;
    
    /**
     * Market data provider
     *
     * @var TradePress_Market_Data
     */
    private $market_data;
    
    /**
     * Volatility monitor
     *
     * @var TradePress_Volatility_Monitor
     */
    private $volatility_monitor;
    
    /**
     * Constructor
     *
     * @param array $position Position data
     */
    public function __construct($position) {
        $this->position = $position;
        $this->market_data = new TradePress_Market_Data();
        $this->volatility_monitor = new TradePress_Volatility_Monitor();
    }
    
    /**
     * Calculate comprehensive risk metrics for a position
     *
     * @return array Risk metrics
     */
    public function calculate_position_risk() {
        // Base metrics
        $portfolio_percentage = $this->calculate_portfolio_percentage();
        $unrealized_loss_percentage = $this->calculate_unrealized_loss_percentage();
        $time_held_factor = $this->calculate_time_factor();
        
        // Market factors
        $market_volatility = $this->volatility_monitor->get_market_volatility_value();
        $correlation_to_market = $this->get_correlation_to_market();
        $symbol_volatility = $this->get_symbol_volatility();
        
        // Symbol-specific factors
        $sector_risk = $this->get_sector_risk();
        $earnings_proximity = $this->get_earnings_proximity_factor();
        $technical_signals = $this->get_technical_warning_count();
        
        // Calculate the composite risk score (0-100)
        $risk_score = (
            ($portfolio_percentage * 100) * 0.20 +
            (abs($unrealized_loss_percentage) * 100) * 0.15 +
            ($time_held_factor * 100) * 0.05 +
            ($market_volatility * 100) * 0.15 +
            ($correlation_to_market * 100) * 0.10 +
            ($symbol_volatility * 100) * 0.10 +
            ($sector_risk * 100) * 0.10 +
            ($earnings_proximity * 100) * 0.10 +
            ($technical_signals * 10) * 0.05
        );
        
        // Ensure risk score is within 0-100 range
        $risk_score = min(100, max(0, $risk_score));
        
        // Build detailed risk metrics array
        return [
            'risk_score' => $risk_score,
            'details' => [
                'portfolio_percentage' => $portfolio_percentage,
                'unrealized_loss_percentage' => $unrealized_loss_percentage,
                'time_held_factor' => $time_held_factor,
                'market_volatility' => $market_volatility,
                'correlation_to_market' => $correlation_to_market,
                'symbol_volatility' => $symbol_volatility,
                'sector_risk' => $sector_risk,
                'earnings_proximity' => $earnings_proximity,
                'technical_signals' => $technical_signals,
            ],
            'risk_level' => $this->determine_risk_level($risk_score),
        ];
    }
    
    /**
     * Calculate position size as percentage of portfolio
     *
     * @return float Position percentage (0-1)
     */
    private function calculate_portfolio_percentage() {
        $portfolio_value = $this->get_portfolio_value();
        
        if ($portfolio_value <= 0) {
            return 0;
        }
        
        $position_value = isset($this->position['current_value']) ? 
            $this->position['current_value'] : 
            ($this->position['position_size'] * $this->position['current_price']);
        
        return $position_value / $portfolio_value;
    }
    
    /**
     * Get total portfolio value
     *
     * @return float Portfolio value
     */
    private function get_portfolio_value() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_positions';
        
        $value = $wpdb->get_var(
            "SELECT SUM(position_size * current_price) FROM {$table_name} WHERE status = 'open'"
        );
        
        return floatval($value) ?: 1; // Default to 1 to avoid division by zero
    }
    
    /**
     * Calculate unrealized P&L percentage
     *
     * @return float Unrealized P&L percentage (-1 to 1)
     */
    private function calculate_unrealized_loss_percentage() {
        $entry_price = floatval($this->position['entry_price']);
        $current_price = floatval($this->position['current_price']);
        
        if ($entry_price <= 0) {
            return 0;
        }
        
        $is_long = $this->position['direction'] === 'long';
        
        if ($is_long) {
            return ($current_price - $entry_price) / $entry_price;
        } else {
            return ($entry_price - $current_price) / $entry_price;
        }
    }
    
    /**
     * Calculate time factor based on how long position has been held
     *
     * @return float Time factor (0-1)
     */
    private function calculate_time_factor() {
        $open_time = strtotime($this->position['open_time']);
        $current_time = time();
        $days_held = ($current_time - $open_time) / (60 * 60 * 24);
        
        // Normalize time factor (0-1)
        // Positions held longer than 60 days get maximum time factor
        return min(1, $days_held / 60);
    }
    
    /**
     * Get correlation between position and market (SPY)
     *
     * @return float Correlation factor (0-1)
     */
    private function get_correlation_to_market() {
        $symbol = $this->position['symbol'];
        $market_symbol = 'SPY'; // Using S&P 500 as market proxy
        
        // Get historical data
        $symbol_data = $this->market_data->get_market_data($symbol, 30);
        $market_data = $this->market_data->get_market_data($market_symbol, 30);
        
        if (empty($symbol_data) || empty($market_data) || count($symbol_data) < 10 || count($market_data) < 10) {
            return 0.5; // Default to medium correlation if not enough data
        }
        
        // Calculate correlation
        $correlation = $this->calculate_correlation($symbol_data, $market_data);
        
        // We want absolute correlation for risk assessment
        // High correlation (positive or negative) = higher risk
        return abs($correlation);
    }
    
    /**
     * Calculate correlation between two data series
     *
     * @param array $data1 First data series
     * @param array $data2 Second data series
     * @return float Correlation coefficient (-1 to 1)
     */
    private function calculate_correlation($data1, $data2) {
        // Extract price changes
        $changes1 = [];
        $changes2 = [];
        
        $min_length = min(count($data1), count($data2));
        
        for ($i = 0; $i < $min_length - 1; $i++) {
            $changes1[] = ($data1[$i]['close'] - $data1[$i + 1]['close']) / $data1[$i + 1]['close'];
            $changes2[] = ($data2[$i]['close'] - $data2[$i + 1]['close']) / $data2[$i + 1]['close'];
        }
        
        // Calculate correlation
        $n = count($changes1);
        
        // Calculate means
        $mean1 = array_sum($changes1) / $n;
        $mean2 = array_sum($changes2) / $n;
        
        // Calculate covariance and variances
        $covariance = 0;
        $variance1 = 0;
        $variance2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $covariance += ($changes1[$i] - $mean1) * ($changes2[$i] - $mean2);
            $variance1 += pow($changes1[$i] - $mean1, 2);
            $variance2 += pow($changes2[$i] - $mean2, 2);
        }
        
        $covariance /= $n;
        $variance1 /= $n;
        $variance2 /= $n;
        
        // Calculate correlation
        if ($variance1 <= 0 || $variance2 <= 0) {
            return 0;
        }
        
        $correlation = $covariance / (sqrt($variance1) * sqrt($variance2));
        
        return $correlation;
    }
    
    /**
     * Get volatility of the position's symbol
     *
     * @return float Volatility factor (0-1)
     */
    private function get_symbol_volatility() {
        $symbol = $this->position['symbol'];
        
        // Calculate ATR as percentage of price
        $atr = $this->calculate_atr($symbol, 14);
        $price = $this->position['current_price'];
        
        if ($price <= 0) {
            return 0.5; // Default to medium volatility
        }
        
        $atr_percent = ($atr / $price) * 100;
        
        // Normalize volatility (0-1)
        // <1% is low, >5% is high
        if ($atr_percent <= 1) {
            return 0;
        } elseif ($atr_percent >= 5) {
            return 1;
        } else {
            return ($atr_percent - 1) / 4; // Linear scaling
        }
    }
    
    /**
     * Calculate ATR (Average True Range) for a symbol
     *
     * @param string $symbol Stock symbol
     * @param int $period Period for ATR calculation
     * @return float ATR value
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
     * Get risk factor for stock's sector
     *
     * @return float Sector risk factor (0-1)
     */
    private function get_sector_risk() {
        $sector = isset($this->position['sector']) ? $this->position['sector'] : '';
        
        if (empty($sector)) {
            // Try to get sector from an API or database
            $symbol = $this->position['symbol'];
            $sector = $this->get_symbol_sector($symbol);
        }
        
        // Get sector risk ratings
        $sector_risks = $this->get_sector_risk_ratings();
        
        if (isset($sector_risks[$sector])) {
            return $sector_risks[$sector];
        }
        
        return 0.5; // Default to medium risk
    }
    
    /**
     * Get sector for a symbol
     *
     * @param string $symbol Stock symbol
     * @return string Sector name
     */
    private function get_symbol_sector($symbol) {
        // Implementation depends on your data source
        // This is a placeholder
        return 'Technology';
    }
    
    /**
     * Get risk ratings for different sectors
     *
     * @return array Sector risk ratings
     */
    private function get_sector_risk_ratings() {
        // These ratings should be updated regularly based on market conditions
        return [
            'Technology' => 0.7,
            'Healthcare' => 0.5,
            'Financials' => 0.6,
            'Consumer Discretionary' => 0.6,
            'Consumer Staples' => 0.3,
            'Energy' => 0.8,
            'Materials' => 0.6,
            'Industrials' => 0.5,
            'Utilities' => 0.3,
            'Real Estate' => 0.5,
            'Communication Services' => 0.6,
        ];
    }
    
    /**
     * Get risk factor based on proximity to earnings announcement
     *
     * @return float Earnings proximity factor (0-1)
     */
    private function get_earnings_proximity_factor() {
        $symbol = $this->position['symbol'];
        
        // Get next earnings date
        $next_earnings_date = $this->get_next_earnings_date($symbol);
        
        if (empty($next_earnings_date)) {
            return 0.5; // Default to medium risk if no earnings data
        }
        
        // Calculate days until earnings
        $current_time = time();
        $earnings_time = strtotime($next_earnings_date);
        $days_until_earnings = ($earnings_time - $current_time) / (60 * 60 * 24);
        
        // Very high risk if earnings is within next 5 days
        if ($days_until_earnings <= 5) {
            return 1.0;
        }
        // High risk if earnings is within next 10 days
        else if ($days_until_earnings <= 10) {
            return 0.8;
        }
        // Medium risk if earnings is within next 20 days
        else if ($days_until_earnings <= 20) {
            return 0.5;
        }
        // Low risk if earnings is more than 20 days away
        else {
            return 0.2;
        }
    }
    
    /**
     * Get next earnings announcement date for a symbol
     *
     * @param string $symbol Stock symbol
     * @return string|null Earnings date or null if not found
     */
    private function get_next_earnings_date($symbol) {
        // Implementation depends on your data source
        // This is a placeholder
        return null;
    }
    
    /**
     * Get count of technical warning signals
     *
     * @return float Technical warning count (0-1)
     */
    private function get_technical_warning_count() {
        $symbol = $this->position['symbol'];
        
        // Technical signals to check
        $signals = [
            $this->check_moving_average_crossover($symbol),
            $this->check_rsi_extreme($symbol),
            $this->check_volume_spike($symbol),
            $this->check_macd_signal($symbol),
            $this->check_price_channel_breach($symbol),
        ];
        
        // Count the number of active warning signals
        $warning_count = 0;
        foreach ($signals as $signal) {
            if ($signal) {
                $warning_count++;
            }
        }
        
        // Normalize to 0-1 range
        return $warning_count / count($signals);
    }
    
    /**
     * Check for moving average crossover
     *
     * @param string $symbol Stock symbol
     * @return bool True if warning signal active
     */
    private function check_moving_average_crossover($symbol) {
        // Implementation depends on your technical analysis library
        // This is a placeholder
        return false;
    }
    
    /**
     * Check for extreme RSI values
     *
     * @param string $symbol Stock symbol
     * @return bool True if warning signal active
     */
    private function check_rsi_extreme($symbol) {
        // Implementation depends on your technical analysis library
        // This is a placeholder
        return false;
    }
    
    /**
     * Check for unusual volume spike
     *
     * @param string $symbol Stock symbol
     * @return bool True if warning signal active
     */
    private function check_volume_spike($symbol) {
        // Implementation depends on your technical analysis library
        // This is a placeholder
        return false;
    }
    
    /**
     * Check for MACD bearish signal
     *
     * @param string $symbol Stock symbol
     * @return bool True if warning signal active
     */
    private function check_macd_signal($symbol) {
        // Implementation depends on your technical analysis library
        // This is a placeholder
        return false;
    }
    
    /**
     * Check for price channel breach
     *
     * @param string $symbol Stock symbol
     * @return bool True if warning signal active
     */
    private function check_price_channel_breach($symbol) {
        // Implementation depends on your technical analysis library
        // This is a placeholder
        return false;
    }
    
    /**
     * Determine risk level based on risk score
     *
     * @param float $risk_score Risk score (0-100)
     * @return string Risk level description
     */
    private function determine_risk_level($risk_score) {
        if ($risk_score >= 75) {
            return 'severe';
        } elseif ($risk_score >= 50) {
            return 'high';
        } elseif ($risk_score >= 25) {
            return 'moderate';
        } else {
            return 'low';
        }
    }
}

endif; // End if class_exists check
