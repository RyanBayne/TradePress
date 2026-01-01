<?php
/**
 * TradePress Volatility Calculator Class
 *
 * Provides methods for calculating and analyzing stock volatility
 *
 * @package TradePress
 * @subpackage Includes
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-05-19 14:30
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Volatility Tools Class
 */
class TradePress_Volatility_Tools {

    /**
     * Calculate historical volatility from price data
     * 
     * @param array $price_data Array of daily closing prices
     * @param int $days Number of days to calculate volatility for
     * @return float Historical volatility as a percentage
     */
    public static function calculate_historical_volatility($price_data, $days = 30) {
        // Ensure we have enough data
        if (count($price_data) < $days + 1) {
            return 0;
        }
        
        // Calculate daily returns
        $returns = array();
        for ($i = 1; $i < count($price_data); $i++) {
            $returns[] = log($price_data[$i] / $price_data[$i - 1]);
        }
        
        // Use only the specified number of days
        $returns = array_slice($returns, 0, $days);
        
        // Calculate average return
        $avg_return = array_sum($returns) / count($returns);
        
        // Calculate sum of squared deviations
        $sum_squared_deviations = 0;
        foreach ($returns as $return) {
            $sum_squared_deviations += pow($return - $avg_return, 2);
        }
        
        // Calculate variance
        $variance = $sum_squared_deviations / (count($returns) - 1);
        
        // Calculate standard deviation (daily volatility)
        $daily_volatility = sqrt($variance);
        
        // Annualize the volatility (multiply by square root of 252 trading days)
        $annualized_volatility = $daily_volatility * sqrt(252);
        
        // Convert to percentage
        return $annualized_volatility * 100;
    }
    
    /**
     * Calculate implied volatility from options data
     * 
     * @param array $options_data Array of options data
     * @return float Implied volatility as a percentage
     */
    public static function calculate_implied_volatility($options_data) {
        // This would use the Black-Scholes model to calculate implied volatility
        // For now, we'll return a placeholder value
        return 0;
    }
    
    /**
     * Calculate beta from stock and market data
     * 
     * @param array $stock_returns Array of stock returns
     * @param array $market_returns Array of market returns (S&P 500)
     * @return float Beta value
     */
    public static function calculate_beta($stock_returns, $market_returns) {
        // Ensure the arrays are the same length
        if (count($stock_returns) != count($market_returns)) {
            return 0;
        }
        
        $n = count($stock_returns);
        
        // Calculate means
        $stock_mean = array_sum($stock_returns) / $n;
        $market_mean = array_sum($market_returns) / $n;
        
        // Calculate covariance and market variance
        $covariance = 0;
        $market_variance = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $covariance += ($stock_returns[$i] - $stock_mean) * ($market_returns[$i] - $market_mean);
            $market_variance += pow($market_returns[$i] - $market_mean, 2);
        }
        
        $covariance /= ($n - 1);
        $market_variance /= ($n - 1);
        
        // Calculate beta
        if ($market_variance == 0) {
            return 0;
        }
        
        return $covariance / $market_variance;
    }
    
    /**
     * Generate demo historical volatility data
     * 
     * @param string $symbol Stock symbol
     * @param int $days Number of days
     * @return float Demo historical volatility
     */
    public static function calculate_demo_historical_volatility($symbol, $days = 30) {
        // Generate consistent demo data based on symbol and days
        $base_volatility = 0;
        
        // Use symbol characters to create a seeded random volatility
        for ($i = 0; $i < strlen($symbol); $i++) {
            $base_volatility += ord($symbol[$i]);
        }
        
        $base_volatility = ($base_volatility % 15) + 10; // Base between 10-25%
        
        // Adjust based on timeframe
        $modifier = 1 + (($days - 30) / 100);
        
        return $base_volatility * $modifier;
    }
    
    /**
     * Generate demo implied volatility data
     * 
     * @param string $symbol Stock symbol
     * @return float Demo implied volatility
     */
    public static function calculate_demo_implied_volatility($symbol) {
        // Generate consistent demo data based on symbol
        // Usually implied volatility is higher than historical
        $historical = self::calculate_demo_historical_volatility($symbol);
        
        // Add a premium to historical volatility
        $random_premium = ((ord($symbol[0]) % 10) + 5) / 10; // 0.5 to 1.4
        
        return $historical * (1 + $random_premium);
    }
    
    /**
     * Generate demo beta data
     * 
     * @param string $symbol Stock symbol
     * @return float Demo beta
     */
    public static function calculate_demo_beta($symbol) {
        // Generate consistent demo beta based on symbol
        $base_beta = 0;
        
        // Use symbol characters to create a seeded random beta
        for ($i = 0; $i < strlen($symbol); $i++) {
            $base_beta += ord($symbol[$i]);
        }
        
        // Convert to a beta between 0.5 and 2.0
        $beta = ($base_beta % 150 + 50) / 100;
        
        return $beta;
    }
    
    /**
     * Determine the current volatility regime
     * 
     * @param float $historical_volatility Historical volatility
     * @param float $implied_volatility Implied volatility
     * @return array Volatility regime data
     */
    public static function determine_volatility_regime($historical_volatility, $implied_volatility) {
        $regimes = array(
            'low' => array(
                'name' => __('Low Volatility Regime', 'tradepress'),
                'description' => __('Period of relative calm with smaller and less frequent price movements.', 'tradepress'),
                'threshold' => 15
            ),
            'moderate' => array(
                'name' => __('Moderate Volatility Regime', 'tradepress'),
                'description' => __('Normal market conditions with average price fluctuations.', 'tradepress'),
                'threshold' => 25
            ),
            'high' => array(
                'name' => __('High Volatility Regime', 'tradepress'),
                'description' => __('Period of significant price swings, potentially indicating uncertainty or fear.', 'tradepress'),
                'threshold' => 35
            ),
            'extreme' => array(
                'name' => __('Extreme Volatility Regime', 'tradepress'),
                'description' => __('Market crisis conditions with very large price movements and high uncertainty.', 'tradepress'),
                'threshold' => 100
            )
        );
        
        // Use the higher of historical or implied volatility
        $volatility = max($historical_volatility, $implied_volatility);
        
        if ($volatility < $regimes['low']['threshold']) {
            return $regimes['low'];
        } elseif ($volatility < $regimes['moderate']['threshold']) {
            return $regimes['moderate'];
        } elseif ($volatility < $regimes['high']['threshold']) {
            return $regimes['high'];
        } else {
            return $regimes['extreme'];
        }
    }
    
    /**
     * Generate trading interpretation based on volatility analysis
     * 
     * @param float $historical_volatility Historical volatility
     * @param float $implied_volatility Implied volatility
     * @param float $beta Beta value
     * @param array $volatility_regime Volatility regime data
     * @return string HTML formatted interpretation
     */
    public static function generate_volatility_interpretation($historical_volatility, $implied_volatility, $beta, $volatility_regime) {
        $output = '';
        
        // Volatility comparison
        $volatility_difference = $implied_volatility - $historical_volatility;
        $volatility_ratio = $implied_volatility / max(0.1, $historical_volatility);
        
        // Market Expectation Analysis
        $output .= '<h4>' . __('Market Expectations', 'tradepress') . '</h4>';
        
        if ($volatility_difference > 5 && $volatility_ratio > 1.2) {
            $output .= '<p>' . __('The market expects <strong>increased volatility</strong> in the near future compared to recent price action. Options may be relatively expensive, and traders are preparing for larger price moves.', 'tradepress') . '</p>';
        } elseif ($volatility_difference < -5 && $volatility_ratio < 0.8) {
            $output .= '<p>' . __('The market expects <strong>decreased volatility</strong> in the near future. Recent price action has been more volatile than what options traders expect going forward. Options may be relatively cheap.', 'tradepress') . '</p>';
        } else {
            $output .= '<p>' . __('The market expects future volatility to be <strong>similar to recent history</strong>. Options pricing is generally aligned with recent price action.', 'tradepress') . '</p>';
        }
        
        // Beta interpretation
        $output .= '<h4>' . __('Market Sensitivity (Beta)', 'tradepress') . '</h4>';
        
        if ($beta > 1.5) {
            $output .= '<p>' . __('With a beta of', 'tradepress') . ' <strong>' . number_format($beta, 2) . '</strong>, ' . __('this stock is <strong>highly sensitive</strong> to overall market movements. Expect amplified price swings compared to the broader market.', 'tradepress') . '</p>';
        } elseif ($beta > 1.0) {
            $output .= '<p>' . __('With a beta of', 'tradepress') . ' <strong>' . number_format($beta, 2) . '</strong>, ' . __('this stock tends to move <strong>slightly more</strong> than the overall market.', 'tradepress') . '</p>';
        } elseif ($beta > 0.8) {
            $output .= '<p>' . __('With a beta of', 'tradepress') . ' <strong>' . number_format($beta, 2) . '</strong>, ' . __('this stock tends to move <strong>similarly</strong> to the overall market.', 'tradepress') . '</p>';
        } else {
            $output .= '<p>' . __('With a beta of', 'tradepress') . ' <strong>' . number_format($beta, 2) . '</strong>, ' . __('this stock tends to be <strong>less volatile</strong> than the overall market, potentially offering more stability.', 'tradepress') . '</p>';
        }
        
        // Volatility regime analysis
        $output .= '<h4>' . __('Trading Environment', 'tradepress') . '</h4>';
        
        $output .= '<p>' . sprintf(__('Current analysis indicates a <strong>%s</strong>. %s', 'tradepress'), 
            $volatility_regime['name'], 
            $volatility_regime['description']) . '</p>';
        
        // Trading strategy suggestions
        $output .= '<h4>' . __('Strategy Considerations', 'tradepress') . '</h4>';
        
        if ($volatility_regime['name'] == __('Low Volatility Regime', 'tradepress')) {
            $output .= '<p>' . __('Consider strategies that profit from range-bound markets:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Selling covered calls to generate income in sideways markets', 'tradepress') . '</li>';
            $output .= '<li>' . __('Credit spreads to profit from time decay in low volatility', 'tradepress') . '</li>';
            $output .= '<li>' . __('Mean reversion strategies may be effective', 'tradepress') . '</li>';
            $output .= '</ul>';
        } elseif ($volatility_regime['name'] == __('Moderate Volatility Regime', 'tradepress')) {
            $output .= '<p>' . __('Consider a balanced approach with moderate position sizing:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Trend following with appropriate stop-losses', 'tradepress') . '</li>';
            $output .= '<li>' . __('Vertical spreads to define risk', 'tradepress') . '</li>';
            $output .= '<li>' . __('Regular portfolio rebalancing', 'tradepress') . '</li>';
            $output .= '</ul>';
        } elseif ($volatility_regime['name'] == __('High Volatility Regime', 'tradepress')) {
            $output .= '<p>' . __('Consider strategies suited for higher volatility environments:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Reduced position sizes to manage increased risk', 'tradepress') . '</li>';
            $output .= '<li>' . __('Tighter stop-losses to protect capital', 'tradepress') . '</li>';
            $output .= '<li>' . __('Straddles or strangles to profit from large moves in either direction', 'tradepress') . '</li>';
            $output .= '</ul>';
        } else {
            $output .= '<p>' . __('Extreme caution is advised in current market conditions:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Consider moving to cash or defensive assets', 'tradepress') . '</li>';
            $output .= '<li>' . __('Significantly reduce position sizes', 'tradepress') . '</li>';
            $output .= '<li>' . __('Hedging strategies to protect existing positions', 'tradepress') . '</li>';
            $output .= '</ul>';
        }
        
        // Volatility outlook based on historical vs implied
        $output .= '<h4>' . __('Volatility Outlook', 'tradepress') . '</h4>';
        
        if ($implied_volatility > $historical_volatility * 1.3) {
            $output .= '<p>' . __('The market is pricing in <strong>significantly higher volatility</strong> than recent historical patterns. This could indicate:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Expectation of major upcoming events (earnings, product launches, regulatory decisions)', 'tradepress') . '</li>';
            $output .= '<li>' . __('Potential for large price movements in either direction', 'tradepress') . '</li>';
            $output .= '<li>' . __('Options are relatively expensive compared to historical norms', 'tradepress') . '</li>';
            $output .= '</ul>';
        } elseif ($historical_volatility > $implied_volatility * 1.3) {
            $output .= '<p>' . __('Recent realized volatility has been <strong>significantly higher</strong> than what the market expects going forward. This could indicate:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Market expects recent volatility to subside', 'tradepress') . '</li>';
            $output .= '<li>' . __('Options may be relatively inexpensive', 'tradepress') . '</li>';
            $output .= '<li>' . __('Potential mean reversion in price action', 'tradepress') . '</li>';
            $output .= '</ul>';
        } else {
            $output .= '<p>' . __('Historical and implied volatility are <strong>relatively aligned</strong>, suggesting:', 'tradepress') . '</p>';
            $output .= '<ul>';
            $output .= '<li>' . __('Market consensus that current volatility patterns will continue', 'tradepress') . '</li>';
            $output .= '<li>' . __('Options are fairly priced relative to recent price action', 'tradepress') . '</li>';
            $output .= '<li>' . __('No significant volatility events anticipated by options traders', 'tradepress') . '</li>';
            $output .= '</ul>';
        }
        
        return $output;
    }
    
    /**
     * Calculate Average True Range (ATR) from price data
     * 
     * @param array $price_data Array of price data (each element should contain 'high', 'low', 'close' keys)
     * @param int $period Number of periods to calculate ATR for
     * @return float ATR value
     */
    public static function calculate_atr($price_data, $period = 14) {
        // Ensure we have enough data
        if (count($price_data) < $period + 1) {
            return 0;
        }
        
        // Calculate True Range series
        $tr_values = array();
        
        for ($i = 1; $i < count($price_data); $i++) {
            $high = $price_data[$i]['high'];
            $low = $price_data[$i]['low'];
            $prev_close = $price_data[$i - 1]['close'];
            
            // True Range is the greatest of the following:
            // 1. Current High - Current Low
            // 2. Absolute value of Current High - Previous Close
            // 3. Absolute value of Current Low - Previous Close
            $tr = max(
                $high - $low,
                abs($high - $prev_close),
                abs($low - $prev_close)
            );
            
            $tr_values[] = $tr;
        }
        
        // Use only the specified number of periods
        $tr_values = array_slice($tr_values, 0, $period);
        
        // Calculate simple average of True Range values for the initial ATR
        $atr = array_sum($tr_values) / count($tr_values);
        
        return $atr;
    }
    
    /**
     * Calculate Bollinger Bands from price data
     * 
     * @param array $price_data Array of closing prices
     * @param int $period Number of periods for moving average
     * @param float $standard_deviations Number of standard deviations for bands
     * @return array Bollinger Bands data (upper, middle, lower)
     */
    public static function calculate_bollinger_bands($price_data, $period = 20, $standard_deviations = 2) {
        // Ensure we have enough data
        if (count($price_data) < $period) {
            return array(
                'upper' => 0,
                'middle' => 0,
                'lower' => 0
            );
        }
        
        // Use only the specified number of periods
        $data = array_slice($price_data, 0, $period);
        
        // Calculate simple moving average (middle band)
        $sma = array_sum($data) / count($data);
        
        // Calculate standard deviation
        $variance = 0;
        foreach ($data as $price) {
            $variance += pow($price - $sma, 2);
        }
        $variance /= count($data);
        $standard_deviation = sqrt($variance);
        
        // Calculate upper and lower bands
        $upper_band = $sma + ($standard_deviations * $standard_deviation);
        $lower_band = $sma - ($standard_deviations * $standard_deviation);
        
        return array(
            'upper' => $upper_band,
            'middle' => $sma,
            'lower' => $lower_band
        );
    }
    
    /**
     * Generate demo ATR data
     * 
     * @param string $symbol Stock symbol
     * @param int $period Number of periods
     * @return float Demo ATR value
     */
    public static function calculate_demo_atr($symbol, $period = 14) {
        // Generate consistent demo data based on symbol and period
        $base_price = 0;
        
        // Use symbol characters to create a seeded base price
        for ($i = 0; $i < strlen($symbol); $i++) {
            $base_price += ord($symbol[$i]);
        }
        
        $base_price = ($base_price % 90) + 10; // Base between 10-100
        
        // ATR typically ranges from 1-5% of price for most stocks
        $atr_percentage = (((ord($symbol[0]) % 4) + 1) / 100); // 1-5%
        
        // Adjust based on period (shorter periods may have higher ATR)
        $modifier = 1 + ((20 - min(20, $period)) / 40); // 1-1.5x modifier for shorter periods
        
        return $base_price * $atr_percentage * $modifier;
    }
    
    /**
     * Calculate volatility-based position size recommendation
     * 
     * @param float $historical_volatility Historical volatility percentage
     * @param float $account_size Total account size
     * @param float $risk_percentage Maximum percentage of account to risk per trade
     * @return array Position sizing recommendations
     */
    public static function calculate_position_size($historical_volatility, $account_size, $risk_percentage = 2) {
        // Maximum account risk in dollars
        $max_risk_amount = $account_size * ($risk_percentage / 100);
        
        // Different position sizing approaches based on volatility
        $conservative_size = 0;
        $moderate_size = 0;
        $aggressive_size = 0;
        
        // Conservative sizing (reduced for higher volatility)
        $conservative_factor = 100 / (100 + $historical_volatility);
        $conservative_size = $max_risk_amount * $conservative_factor;
        
        // Moderate sizing (standard approach)
        $moderate_size = $max_risk_amount;
        
        // Aggressive sizing (increases with lower volatility)
        $aggressive_factor = 100 / max(5, $historical_volatility);
        $aggressive_size = $max_risk_amount * $aggressive_factor;
        
        // Cap aggressive size at 5% of account
        $aggressive_size = min($aggressive_size, $account_size * 0.05);
        
        return array(
            'conservative' => $conservative_size,
            'moderate' => $moderate_size,
            'aggressive' => $aggressive_size,
            'max_risk_amount' => $max_risk_amount,
            'risk_percentage' => $risk_percentage
        );
    }
}