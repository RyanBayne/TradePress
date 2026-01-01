<?php
/**
 * Technical indicators calculation class
 *
 * This class handles calculations for technical indicators used in the scoring system
 *
 * @link       https://example.com
 * @since      1.0.0
 * @package    TradePress
 * @subpackage TradePress/includes
 * @created    <?php echo date('Y-m-d H:i:s'); ?>
 */

class TradePress_Technical_Indicators {

    /**
     * Calculate Relative Strength Index (RSI)
     *
     * @param array $prices Array of closing prices
     * @param int $period The period to calculate RSI (default: 14)
     * @return float|null The RSI value or null if not enough data
     */
    public function calculate_rsi($prices, $period = 14) {
        if (count($prices) < $period + 1) {
            return null;
        }

        // Get price differences
        $differences = array();
        for ($i = 1; $i < count($prices); $i++) {
            $differences[] = $prices[$i] - $prices[$i - 1];
        }

        // Get gains and losses
        $gains = array();
        $losses = array();
        foreach ($differences as $diff) {
            $gains[] = max(0, $diff);
            $losses[] = max(0, -$diff);
        }

        // Calculate average gain and loss
        $avg_gain = array_sum(array_slice($gains, 0, $period)) / $period;
        $avg_loss = array_sum(array_slice($losses, 0, $period)) / $period;

        // Calculate smoothed RS
        $rs = ($avg_loss == 0) ? 100 : $avg_gain / $avg_loss;

        // Calculate RSI
        $rsi = 100 - (100 / (1 + $rs));

        return $rsi;
    }

    /**
     * Calculate Moving Average Convergence Divergence (MACD)
     *
     * @param array $prices Array of closing prices
     * @param int $fast_period Fast EMA period (default: 12)
     * @param int $slow_period Slow EMA period (default: 26)
     * @param int $signal_period Signal period (default: 9)
     * @return array|null The MACD values or null if not enough data
     */
    public function calculate_macd($prices, $fast_period = 12, $slow_period = 26, $signal_period = 9) {
        if (count($prices) < $slow_period + $signal_period) {
            return null;
        }

        $fast_ema = $this->calculate_ema($prices, $fast_period);
        $slow_ema = $this->calculate_ema($prices, $slow_period);

        if (!$fast_ema || !$slow_ema) {
            return null;
        }

        // Calculate MACD line
        $macd_line = $fast_ema - $slow_ema;

        // Calculate signal line (EMA of MACD line)
        $macd_values = array();
        for ($i = 0; $i < count($prices) - $slow_period + 1; $i++) {
            $macd_values[] = $this->calculate_ema($prices, $fast_period, $i) - $this->calculate_ema($prices, $slow_period, $i);
        }

        $signal_line = $this->calculate_ema($macd_values, $signal_period);
        $histogram = $macd_line - $signal_line;

        return array(
            'macd' => $macd_line,
            'signal' => $signal_line,
            'histogram' => $histogram
        );
    }

    /**
     * Calculate Exponential Moving Average (EMA)
     *
     * @param array $prices Array of closing prices
     * @param int $period The period to calculate EMA
     * @param int $offset Optional offset from the end of the array
     * @return float|null The EMA value or null if not enough data
     */
    public function calculate_ema($prices, $period, $offset = 0) {
        $prices = array_slice($prices, 0, count($prices) - $offset);
        
        if (count($prices) < $period) {
            return null;
        }

        $multiplier = 2 / ($period + 1);
        
        // Start with SMA for the first EMA value
        $sma = array_sum(array_slice($prices, 0, $period)) / $period;
        $ema = $sma;
        
        // Calculate EMA for the remaining prices
        for ($i = $period; $i < count($prices); $i++) {
            $ema = (($prices[$i] - $ema) * $multiplier) + $ema;
        }
        
        return $ema;
    }

    /**
     * Calculate Bollinger Bands
     *
     * @param array $prices Array of closing prices
     * @param int $period Period for SMA calculation (default: 20)
     * @param float $standard_deviations Number of standard deviations (default: 2)
     * @return array|null The Bollinger Bands values or null if not enough data
     */
    public function calculate_bollinger_bands($prices, $period = 20, $standard_deviations = 2) {
        if (count($prices) < $period) {
            return null;
        }

        // Calculate SMA
        $sma = array_sum(array_slice($prices, -$period)) / $period;
        
        // Calculate standard deviation
        $variance = 0;
        $recent_prices = array_slice($prices, -$period);
        foreach ($recent_prices as $price) {
            $variance += pow($price - $sma, 2);
        }
        $std_dev = sqrt($variance / $period);
        
        // Calculate upper and lower bands
        $upper_band = $sma + ($standard_deviations * $std_dev);
        $lower_band = $sma - ($standard_deviations * $std_dev);
        
        return array(
            'middle' => $sma,
            'upper' => $upper_band,
            'lower' => $lower_band
        );
    }

    /**
     * Calculate Stochastic Oscillator
     *
     * @param array $high Array of high prices
     * @param array $low Array of low prices
     * @param array $close Array of closing prices
     * @param int $k_period %K period (default: 14)
     * @param int $d_period %D period (default: 3)
     * @param int $slowing Slowing period (default: 3)
     * @return array|null The Stochastic Oscillator values or null if not enough data
     */
    public function calculate_stochastic($high, $low, $close, $k_period = 14, $d_period = 3, $slowing = 3) {
        if (count($high) < $k_period || count($low) < $k_period || count($close) < $k_period) {
            return null;
        }

        // Calculate %K
        $k_values = array();
        for ($i = $k_period - 1; $i < count($close); $i++) {
            $lowest_low = min(array_slice($low, $i - $k_period + 1, $k_period));
            $highest_high = max(array_slice($high, $i - $k_period + 1, $k_period));
            
            if ($highest_high - $lowest_low === 0) {
                $k_values[] = 50; // Default to 50 if there's no range
            } else {
                $k_values[] = 100 * (($close[$i] - $lowest_low) / ($highest_high - $lowest_low));
            }
        }

        // Calculate slow %K if slowing > 1
        if ($slowing > 1 && count($k_values) >= $slowing) {
            $slow_k = array();
            for ($i = $slowing - 1; $i < count($k_values); $i++) {
                $sum = 0;
                for ($j = 0; $j < $slowing; $j++) {
                    $sum += $k_values[$i - $j];
                }
                $slow_k[] = $sum / $slowing;
            }
            $k_values = $slow_k;
        }

        // Calculate %D (SMA of %K)
        $d_values = array();
        if (count($k_values) >= $d_period) {
            for ($i = $d_period - 1; $i < count($k_values); $i++) {
                $sum = 0;
                for ($j = 0; $j < $d_period; $j++) {
                    $sum += $k_values[$i - $j];
                }
                $d_values[] = $sum / $d_period;
            }
        }

        return array(
            'k' => end($k_values),
            'd' => end($d_values)
        );
    }

    /**
     * Calculate Average Directional Index (ADX)
     *
     * @param array $high Array of high prices
     * @param array $low Array of low prices
     * @param array $close Array of closing prices
     * @param int $period Period for ADX calculation (default: 14)
     * @return array|null The ADX values or null if not enough data
     */
    public function calculate_adx($high, $low, $close, $period = 14) {
        if (count($high) < $period * 2 || count($low) < $period * 2 || count($close) < $period * 2) {
            return null;
        }

        // Calculate +DI and -DI
        $plus_dm = array();
        $minus_dm = array();
        $tr = array(); // True Range

        for ($i = 1; $i < count($high); $i++) {
            // Directional Movement
            $up_move = $high[$i] - $high[$i - 1];
            $down_move = $low[$i - 1] - $low[$i];
            
            $plus_dm[] = ($up_move > $down_move && $up_move > 0) ? $up_move : 0;
            $minus_dm[] = ($down_move > $up_move && $down_move > 0) ? $down_move : 0;
            
            // True Range
            $tr[] = max(
                abs($high[$i] - $low[$i]),
                abs($high[$i] - $close[$i - 1]),
                abs($low[$i] - $close[$i - 1])
            );
        }

        // Calculate smoothed values
        $smoothed_plus_dm = $this->calculate_smoothed_average($plus_dm, $period);
        $smoothed_minus_dm = $this->calculate_smoothed_average($minus_dm, $period);
        $smoothed_tr = $this->calculate_smoothed_average($tr, $period);

        // Calculate +DI and -DI
        $plus_di = 100 * ($smoothed_plus_dm / $smoothed_tr);
        $minus_di = 100 * ($smoothed_minus_dm / $smoothed_tr);

        // Calculate DX
        $dx = 100 * (abs($plus_di - $minus_di) / ($plus_di + $minus_di));

        // Calculate ADX (smoothed DX)
        $dx_values = array();
        for ($i = 0; $i < $period; $i++) {
            $dx_values[] = $dx; // Placeholder, in real calculation we'd have individual DX values
        }
        
        $adx = $this->calculate_smoothed_average($dx_values, $period);

        return array(
            'adx' => $adx,
            'plus_di' => $plus_di,
            'minus_di' => $minus_di
        );
    }

    /**
     * Helper function to calculate smoothed average (Wilder's smoothing method)
     *
     * @param array $values Array of values
     * @param int $period Period for smoothing
     * @return float The smoothed average
     */
    private function calculate_smoothed_average($values, $period) {
        if (count($values) < $period) {
            return null;
        }
        
        $initial_sum = array_sum(array_slice($values, 0, $period));
        $smoothed = $initial_sum;
        
        for ($i = $period; $i < count($values); $i++) {
            $smoothed = $smoothed - ($smoothed / $period) + $values[$i];
        }
        
        return $smoothed / $period;
    }

    /**
     * Calculate On-Balance Volume (OBV)
     *
     * @param array $close Array of closing prices
     * @param array $volume Array of volume data
     * @return float|null The OBV value or null if not enough data
     */
    public function calculate_obv($close, $volume) {
        if (count($close) < 2 || count($volume) < 2) {
            return null;
        }

        $obv = $volume[0]; // Start with first day's volume
        
        for ($i = 1; $i < count($close); $i++) {
            if ($close[$i] > $close[$i - 1]) {
                $obv += $volume[$i];
            } elseif ($close[$i] < $close[$i - 1]) {
                $obv -= $volume[$i];
            }
            // If prices are the same, OBV doesn't change
        }
        
        return $obv;
    }

    /**
     * Calculate Money Flow Index (MFI)
     *
     * @param array $high Array of high prices
     * @param array $low Array of low prices
     * @param array $close Array of closing prices
     * @param array $volume Array of volume data
     * @param int $period Period for MFI calculation (default: 14)
     * @return float|null The MFI value or null if not enough data
     */
    public function calculate_mfi($high, $low, $close, $volume, $period = 14) {
        if (count($high) < $period + 1 || count($low) < $period + 1 || 
            count($close) < $period + 1 || count($volume) < $period + 1) {
            return null;
        }

        // Calculate typical price
        $typical_prices = array();
        for ($i = 0; $i < count($close); $i++) {
            $typical_prices[] = ($high[$i] + $low[$i] + $close[$i]) / 3;
        }

        // Calculate raw money flow
        $money_flow = array();
        for ($i = 0; $i < count($typical_prices); $i++) {
            $money_flow[] = $typical_prices[$i] * $volume[$i];
        }

        // Separate positive and negative money flows
        $positive_flows = array();
        $negative_flows = array();
        
        for ($i = 1; $i < count($typical_prices); $i++) {
            if ($typical_prices[$i] > $typical_prices[$i - 1]) {
                $positive_flows[] = $money_flow[$i];
                $negative_flows[] = 0;
            } else {
                $positive_flows[] = 0;
                $negative_flows[] = $money_flow[$i];
            }
        }

        // Calculate money flow ratio and MFI
        $positive_flow_sum = array_sum(array_slice($positive_flows, -$period));
        $negative_flow_sum = array_sum(array_slice($negative_flows, -$period));
        
        if ($negative_flow_sum == 0) {
            return 100; // All positive flows
        }
        
        $money_flow_ratio = $positive_flow_sum / $negative_flow_sum;
        $mfi = 100 - (100 / (1 + $money_flow_ratio));
        
        return $mfi;
    }

    /**
     * Calculate Commodity Channel Index (CCI)
     *
     * @param array $high Array of high prices
     * @param array $low Array of low prices
     * @param array $close Array of closing prices
     * @param int $period Period for CCI calculation (default: 20)
     * @param float $constant CCI constant (default: 0.015)
     * @return float|null The CCI value or null if not enough data
     */
    public function calculate_cci($high, $low, $close, $period = 20, $constant = 0.015) {
        if (count($high) < $period || count($low) < $period || count($close) < $period) {
            return null;
        }

        // Calculate typical price
        $typical_prices = array();
        for ($i = 0; $i < count($close); $i++) {
            $typical_prices[] = ($high[$i] + $low[$i] + $close[$i]) / 3;
        }

        // Calculate SMA of typical price
        $recent_typical_prices = array_slice($typical_prices, -$period);
        $sma = array_sum($recent_typical_prices) / $period;
        
        // Calculate mean deviation
        $mean_deviation = 0;
        foreach ($recent_typical_prices as $tp) {
            $mean_deviation += abs($tp - $sma);
        }
        $mean_deviation /= $period;
        
        // Calculate CCI
        $current_typical_price = end($typical_prices);
        $cci = ($current_typical_price - $sma) / ($constant * $mean_deviation);
        
        return $cci;
    }

    /**
     * Calculate Volume Weighted Average Price (VWAP)
     *
     * @param array $high Array of high prices
     * @param array $low Array of low prices
     * @param array $close Array of closing prices
     * @param array $volume Array of volume data
     * @param string $session_start Timestamp for session start (optional)
     * @return float|null The VWAP value or null if not enough data
     */
    public function calculate_vwap($high, $low, $close, $volume, $session_start = null) {
        if (count($high) < 1 || count($low) < 1 || count($close) < 1 || count($volume) < 1) {
            return null;
        }

        // Filter data by session if session_start is provided
        $start_index = 0;
        if ($session_start !== null) {
            // Logic to find starting index based on timestamp would go here
            // For this example, we'll just use all data
        }

        $cumulative_pv = 0;
        $cumulative_volume = 0;
        
        for ($i = $start_index; $i < count($close); $i++) {
            $typical_price = ($high[$i] + $low[$i] + $close[$i]) / 3;
            $cumulative_pv += $typical_price * $volume[$i];
            $cumulative_volume += $volume[$i];
        }
        
        if ($cumulative_volume === 0) {
            return null;
        }
        
        return $cumulative_pv / $cumulative_volume;
    }

    /**
     * Calculate Moving Average Crossover signals
     * 
     * Placeholder function - will be implemented in future development
     *
     * @param array $prices Array of closing prices
     * @param int $fast_period Fast MA period
     * @param int $slow_period Slow MA period
     * @param string $ma_type Type of moving average ('sma' or 'ema')
     * @return array|null The crossover signals or null if not enough data
     */
    public function calculate_ma_crossover($prices, $fast_period = 50, $slow_period = 200, $ma_type = 'ema') {
        // Placeholder implementation - returns basic data structure
        // This will be fully implemented in a future update
        
        return array(
            'current_fast_ma' => null,
            'current_slow_ma' => null,
            'position' => 'neutral',
            'crossover' => false,
            'crossover_direction' => null,
            'days_since_crossover' => null,
            'status' => 'under_construction'
        );
    }
}
