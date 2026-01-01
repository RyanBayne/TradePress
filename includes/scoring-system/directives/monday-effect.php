<?php
/**
 * Monday Effect Directive
 * 
 * Detects and scores Monday negative bias patterns in stock performance
 * Uses historical daily data to identify Monday underperformance trends
 * 
 * @package TradePress
 * @subpackage Scoring_Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_MONDAY_EFFECT extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'monday_effect';
        $this->name = 'Monday Effect';
        $this->description = 'Detects Monday negative bias patterns and weekend sentiment impact';
        $this->weight = 10;
        $this->max_score = 100;
        $this->bullish_values = 'Positive Monday performance, contrarian opportunity';
        $this->bearish_values = 'Consistent Monday weakness, negative weekend sentiment';
        $this->priority = 25;
        $this->api_requirements = array('Alpha Vantage Daily Time Series', 'Finnhub Historical Data');
        $this->update_frequency = 'Weekly analysis (Monday morning)';
        $this->api_cost = 'LOW';
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $lookback_weeks = $config['lookback_weeks'] ?? 12;
        $monday_weight = $config['monday_weight'] ?? 30;
        $trend_weight = $config['trend_weight'] ?? 20;
        
        $historical_data = $symbol_data['historical'] ?? null;
        $current_price = $symbol_data['price'] ?? 0;
        
        if (!$historical_data || empty($historical_data) || $current_price <= 0) {
            return array('score' => 50, 'signal' => 'Insufficient historical data');
        }
        
        // Analyze Monday performance over lookback period
        $monday_analysis = $this->analyze_monday_patterns($historical_data, $lookback_weeks);
        
        if (!$monday_analysis) {
            return array('score' => 50, 'signal' => 'Unable to analyze Monday patterns');
        }
        
        $base_score = 50; // Neutral starting point
        
        // Monday Effect Analysis
        $monday_avg_return = $monday_analysis['avg_monday_return'];
        $monday_win_rate = $monday_analysis['monday_win_rate'];
        $monday_vs_other_days = $monday_analysis['monday_vs_other_days'];
        $recent_monday_trend = $monday_analysis['recent_trend'];
        
        // Score based on Monday performance
        if ($monday_avg_return < -0.5) {
            // Strong Monday weakness
            $base_score -= $monday_weight;
            $signal = 'Strong Monday Effect Detected';
        } elseif ($monday_avg_return < -0.2) {
            // Moderate Monday weakness
            $base_score -= ($monday_weight * 0.6);
            $signal = 'Moderate Monday Effect';
        } elseif ($monday_avg_return > 0.3) {
            // Positive Monday bias (contrarian opportunity)
            $base_score += ($monday_weight * 0.8);
            $signal = 'Positive Monday Bias';
        } else {
            $signal = 'Neutral Monday Performance';
        }
        
        // Win rate consideration
        if ($monday_win_rate < 0.35) {
            $base_score -= 15; // Low Monday win rate
        } elseif ($monday_win_rate > 0.65) {
            $base_score += 12; // High Monday win rate
        }
        
        // Relative performance vs other days
        if ($monday_vs_other_days < -0.3) {
            $base_score -= $trend_weight; // Mondays significantly worse
        } elseif ($monday_vs_other_days > 0.2) {
            $base_score += ($trend_weight * 0.7); // Mondays better than average
        }
        
        // Recent trend consideration
        if ($recent_monday_trend < -0.4) {
            $base_score -= 10; // Recent Monday weakness
        } elseif ($recent_monday_trend > 0.3) {
            $base_score += 8; // Recent Monday strength
        }
        
        // Current day consideration
        $current_day = date('N'); // 1 = Monday
        if ($current_day == 1) {
            // It's Monday - adjust score based on pattern
            if ($monday_avg_return < -0.3) {
                $base_score -= 5; // Expect weakness today
                $signal .= ' - Monday Weakness Expected';
            } else {
                $base_score += 3; // No strong pattern
            }
        } elseif ($current_day == 5) {
            // It's Friday - consider weekend effect
            if ($monday_avg_return < -0.3) {
                $base_score += 5; // Friday before weak Monday
                $signal .= ' - Pre-Monday Positioning';
            }
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'monday_avg_return' => round($monday_avg_return, 3),
            'monday_win_rate' => round($monday_win_rate, 3),
            'monday_vs_others' => round($monday_vs_other_days, 3),
            'recent_trend' => round($recent_monday_trend, 3),
            'analysis_weeks' => $lookback_weeks,
            'current_day' => $this->get_day_name($current_day)
        );
    }
    
    private function analyze_monday_patterns($historical_data, $lookback_weeks) {
        $monday_returns = array();
        $other_day_returns = array();
        $recent_mondays = array();
        
        $weeks_analyzed = 0;
        $data_count = count($historical_data);
        
        for ($i = 1; $i < $data_count && $weeks_analyzed < $lookback_weeks; $i++) {
            $current = $historical_data[$i];
            $previous = $historical_data[$i - 1];
            
            if (!isset($current['timestamp']) || !isset($previous['close']) || !isset($current['close'])) {
                continue;
            }
            
            $day_of_week = date('N', $current['timestamp']);
            $daily_return = (($current['close'] - $previous['close']) / $previous['close']) * 100;
            
            if ($day_of_week == 1) { // Monday
                $monday_returns[] = $daily_return;
                
                // Track recent Mondays (last 4 weeks)
                if ($weeks_analyzed < 4) {
                    $recent_mondays[] = $daily_return;
                }
                
                $weeks_analyzed++;
            } else {
                $other_day_returns[] = $daily_return;
            }
        }
        
        if (empty($monday_returns)) {
            return false;
        }
        
        $avg_monday_return = array_sum($monday_returns) / count($monday_returns);
        $avg_other_return = !empty($other_day_returns) ? array_sum($other_day_returns) / count($other_day_returns) : 0;
        
        $monday_wins = array_filter($monday_returns, function($return) { return $return > 0; });
        $monday_win_rate = count($monday_wins) / count($monday_returns);
        
        $monday_vs_other_days = $avg_monday_return - $avg_other_return;
        
        $recent_trend = !empty($recent_mondays) ? array_sum($recent_mondays) / count($recent_mondays) : 0;
        
        return array(
            'avg_monday_return' => $avg_monday_return,
            'monday_win_rate' => $monday_win_rate,
            'monday_vs_other_days' => $monday_vs_other_days,
            'recent_trend' => $recent_trend,
            'total_mondays' => count($monday_returns)
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
        $lookback_weeks = $config['lookback_weeks'] ?? 12;
        $monday_weight = $config['monday_weight'] ?? 30;
        $trend_weight = $config['trend_weight'] ?? 20;
        
        return "Monday Effect Directive:\n\n" .
               "Configuration:\n" .
               "- Analysis Period: {$lookback_weeks} weeks\n" .
               "- Monday Weight: {$monday_weight} points\n" .
               "- Trend Weight: {$trend_weight} points\n\n" .
               "Analysis:\n" .
               "- Tracks Monday performance vs other weekdays\n" .
               "- Identifies consistent Monday weakness patterns\n" .
               "- Considers weekend sentiment impact\n" .
               "- Adjusts for current day of week\n\n" .
               "Scoring:\n" .
               "- Strong Monday weakness (<-0.5%): -{$monday_weight} points\n" .
               "- Moderate Monday weakness (<-0.2%): -" . round($monday_weight * 0.6) . " points\n" .
               "- Positive Monday bias (>0.3%): +" . round($monday_weight * 0.8) . " points\n" .
               "- Low Monday win rate (<35%): -15 points\n" .
               "- High Monday win rate (>65%): +12 points\n\n" .
               "The Monday Effect suggests that stock returns on Mondays are often lower than other days due to weekend sentiment and news accumulation.";
    }
}