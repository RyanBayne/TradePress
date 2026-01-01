<?php
/**
 * Friday Positioning Directive
 * 
 * Analyzes end-of-week adjustment patterns and Friday positioning behavior
 * Detects institutional rebalancing and weekend risk management patterns
 * 
 * @package TradePress
 * @subpackage Scoring_Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_FRIDAY_POSITIONING extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'friday_positioning';
        $this->name = 'Friday Positioning';
        $this->description = 'Analyzes end-of-week adjustment patterns and institutional positioning';
        $this->weight = 12;
        $this->max_score = 100;
        $this->bullish_values = 'Strong Friday closes, institutional accumulation';
        $this->bearish_values = 'Friday weakness, weekend risk reduction';
        $this->priority = 26;
        $this->api_requirements = array('Alpha Vantage Daily Time Series', 'Finnhub Historical Data');
        $this->update_frequency = 'Weekly analysis (Friday close)';
        $this->api_cost = 'LOW';
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $lookback_weeks = $config['lookback_weeks'] ?? 10;
        $positioning_weight = $config['positioning_weight'] ?? 25;
        $volume_weight = $config['volume_weight'] ?? 15;
        $close_strength_weight = $config['close_strength_weight'] ?? 20;
        
        $historical_data = $symbol_data['historical'] ?? null;
        $current_price = $symbol_data['price'] ?? 0;
        
        if (!$historical_data || empty($historical_data) || $current_price <= 0) {
            return array('score' => 50, 'signal' => 'Insufficient historical data');
        }
        
        // Analyze Friday positioning patterns
        $friday_analysis = $this->analyze_friday_patterns($historical_data, $lookback_weeks);
        
        if (!$friday_analysis) {
            return array('score' => 50, 'signal' => 'Unable to analyze Friday patterns');
        }
        
        $base_score = 50; // Neutral starting point
        
        // Friday Positioning Analysis
        $friday_avg_return = $friday_analysis['avg_friday_return'];
        $friday_close_strength = $friday_analysis['avg_close_strength'];
        $friday_volume_ratio = $friday_analysis['avg_volume_ratio'];
        $friday_vs_thursday = $friday_analysis['friday_vs_thursday'];
        $recent_friday_trend = $friday_analysis['recent_trend'];
        
        // Score based on Friday performance
        if ($friday_avg_return > 0.3) {
            // Strong Friday performance
            $base_score += $positioning_weight;
            $signal = 'Strong Friday Positioning';
        } elseif ($friday_avg_return > 0.1) {
            // Moderate Friday strength
            $base_score += ($positioning_weight * 0.6);
            $signal = 'Positive Friday Positioning';
        } elseif ($friday_avg_return < -0.3) {
            // Friday weakness
            $base_score -= ($positioning_weight * 0.8);
            $signal = 'Friday Weakness Pattern';
        } else {
            $signal = 'Neutral Friday Performance';
        }
        
        // Close strength analysis (intraday performance)
        if ($friday_close_strength > 0.6) {
            // Strong closes (price near high)
            $base_score += $close_strength_weight;
            $signal .= ' - Strong Closes';
        } elseif ($friday_close_strength < 0.4) {
            // Weak closes (price near low)
            $base_score -= ($close_strength_weight * 0.8);
            $signal .= ' - Weak Closes';
        }
        
        // Volume analysis
        if ($friday_volume_ratio > 1.2) {
            // High Friday volume
            $base_score += $volume_weight;
            $signal .= ' - High Volume';
        } elseif ($friday_volume_ratio < 0.8) {
            // Low Friday volume
            $base_score -= ($volume_weight * 0.6);
            $signal .= ' - Low Volume';
        }
        
        // Friday vs Thursday comparison
        if ($friday_vs_thursday > 0.2) {
            $base_score += 10; // Fridays consistently better
        } elseif ($friday_vs_thursday < -0.2) {
            $base_score -= 8; // Fridays consistently worse
        }
        
        // Recent trend consideration
        if ($recent_friday_trend > 0.4) {
            $base_score += 12; // Recent Friday strength
        } elseif ($recent_friday_trend < -0.3) {
            $base_score -= 10; // Recent Friday weakness
        }
        
        // Current day consideration
        $current_day = date('N'); // 5 = Friday
        if ($current_day == 5) {
            // It's Friday - adjust based on pattern
            if ($friday_avg_return > 0.2 && $friday_close_strength > 0.6) {
                $base_score += 8; // Expect strong close
                $signal .= ' - Strong Close Expected';
            } elseif ($friday_avg_return < -0.2) {
                $base_score -= 5; // Expect weakness
                $signal .= ' - Weakness Expected';
            }
        } elseif ($current_day == 4) {
            // It's Thursday - consider Friday setup
            if ($friday_avg_return > 0.2) {
                $base_score += 3; // Pre-Friday positioning
                $signal .= ' - Pre-Friday Setup';
            }
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'friday_avg_return' => round($friday_avg_return, 3),
            'close_strength' => round($friday_close_strength, 3),
            'volume_ratio' => round($friday_volume_ratio, 3),
            'friday_vs_thursday' => round($friday_vs_thursday, 3),
            'recent_trend' => round($recent_friday_trend, 3),
            'analysis_weeks' => $lookback_weeks,
            'current_day' => $this->get_day_name($current_day)
        );
    }
    
    private function analyze_friday_patterns($historical_data, $lookback_weeks) {
        $friday_returns = array();
        $friday_close_strengths = array();
        $friday_volumes = array();
        $thursday_returns = array();
        $recent_fridays = array();
        $avg_volume = 0;
        $volume_count = 0;
        
        $weeks_analyzed = 0;
        $data_count = count($historical_data);
        
        // Calculate average volume first
        foreach ($historical_data as $day_data) {
            if (isset($day_data['volume']) && $day_data['volume'] > 0) {
                $avg_volume += $day_data['volume'];
                $volume_count++;
            }
        }
        $avg_volume = $volume_count > 0 ? $avg_volume / $volume_count : 1;
        
        for ($i = 1; $i < $data_count && $weeks_analyzed < $lookback_weeks; $i++) {
            $current = $historical_data[$i];
            $previous = $historical_data[$i - 1];
            
            if (!isset($current['timestamp']) || !isset($previous['close']) || !isset($current['close'])) {
                continue;
            }
            
            $day_of_week = date('N', $current['timestamp']);
            $daily_return = (($current['close'] - $previous['close']) / $previous['close']) * 100;
            
            if ($day_of_week == 5) { // Friday
                $friday_returns[] = $daily_return;
                
                // Calculate close strength (where close is relative to high-low range)
                if (isset($current['high']) && isset($current['low']) && $current['high'] > $current['low']) {
                    $close_strength = ($current['close'] - $current['low']) / ($current['high'] - $current['low']);
                    $friday_close_strengths[] = $close_strength;
                }
                
                // Volume ratio
                if (isset($current['volume']) && $current['volume'] > 0 && $avg_volume > 0) {
                    $friday_volumes[] = $current['volume'] / $avg_volume;
                }
                
                // Track recent Fridays (last 4 weeks)
                if ($weeks_analyzed < 4) {
                    $recent_fridays[] = $daily_return;
                }
                
                $weeks_analyzed++;
            } elseif ($day_of_week == 4) { // Thursday
                $thursday_returns[] = $daily_return;
            }
        }
        
        if (empty($friday_returns)) {
            return false;
        }
        
        $avg_friday_return = array_sum($friday_returns) / count($friday_returns);
        $avg_thursday_return = !empty($thursday_returns) ? array_sum($thursday_returns) / count($thursday_returns) : 0;
        $avg_close_strength = !empty($friday_close_strengths) ? array_sum($friday_close_strengths) / count($friday_close_strengths) : 0.5;
        $avg_volume_ratio = !empty($friday_volumes) ? array_sum($friday_volumes) / count($friday_volumes) : 1.0;
        $friday_vs_thursday = $avg_friday_return - $avg_thursday_return;
        $recent_trend = !empty($recent_fridays) ? array_sum($recent_fridays) / count($recent_fridays) : 0;
        
        return array(
            'avg_friday_return' => $avg_friday_return,
            'avg_close_strength' => $avg_close_strength,
            'avg_volume_ratio' => $avg_volume_ratio,
            'friday_vs_thursday' => $friday_vs_thursday,
            'recent_trend' => $recent_trend,
            'total_fridays' => count($friday_returns)
        );
    }
    
    private function get_day_name($day_number) {
        $days = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday');
        return $days[$day_number] ?? 'Unknown';
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $lookback_weeks = $config['lookback_weeks'] ?? 10;
        $positioning_weight = $config['positioning_weight'] ?? 25;
        $volume_weight = $config['volume_weight'] ?? 15;
        $close_strength_weight = $config['close_strength_weight'] ?? 20;
        
        return "Friday Positioning Directive:\n\n" .
               "Configuration:\n" .
               "- Analysis Period: {$lookback_weeks} weeks\n" .
               "- Positioning Weight: {$positioning_weight} points\n" .
               "- Volume Weight: {$volume_weight} points\n" .
               "- Close Strength Weight: {$close_strength_weight} points\n\n" .
               "Analysis:\n" .
               "- Tracks Friday performance patterns\n" .
               "- Analyzes intraday close strength\n" .
               "- Monitors volume patterns on Fridays\n" .
               "- Compares Friday vs Thursday performance\n\n" .
               "Scoring:\n" .
               "- Strong Friday performance (>0.3%): +{$positioning_weight} points\n" .
               "- Positive Friday performance (>0.1%): +" . round($positioning_weight * 0.6) . " points\n" .
               "- Friday weakness (<-0.3%): -" . round($positioning_weight * 0.8) . " points\n" .
               "- Strong closes (>60% of range): +{$close_strength_weight} points\n" .
               "- High Friday volume (>120% avg): +{$volume_weight} points\n\n" .
               "Friday positioning reflects institutional end-of-week adjustments and weekend risk management decisions.";
    }
}