<?php
/**
 * Volume Rhythm Directive
 * 
 * Analyzes day-of-week volume patterns and institutional trading rhythms
 * Identifies volume anomalies and seasonal trading patterns
 * 
 * @package TradePress
 * @subpackage Scoring_Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_VOLUME_RHYTHM extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'volume_rhythm';
        $this->name = 'Volume Rhythm';
        $this->description = 'Analyzes day-of-week volume patterns and trading rhythms';
        $this->weight = 14;
        $this->max_score = 100;
        $this->bullish_values = 'High volume on up days, institutional accumulation patterns';
        $this->bearish_values = 'High volume on down days, distribution patterns';
        $this->priority = 27;
        $this->api_requirements = array('Alpha Vantage Daily Time Series', 'Finnhub Historical Volume');
        $this->update_frequency = 'Weekly pattern analysis';
        $this->api_cost = 'LOW';
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $lookback_weeks = $config['lookback_weeks'] ?? 8;
        $volume_weight = $config['volume_weight'] ?? 30;
        $rhythm_weight = $config['rhythm_weight'] ?? 20;
        $anomaly_weight = $config['anomaly_weight'] ?? 15;
        
        $historical_data = $symbol_data['historical'] ?? null;
        $current_volume = $symbol_data['volume'] ?? 0;
        
        if (!$historical_data || empty($historical_data)) {
            return array('score' => 50, 'signal' => 'Insufficient volume data');
        }
        
        // Analyze volume rhythm patterns
        $volume_analysis = $this->analyze_volume_rhythm($historical_data, $lookback_weeks);
        
        if (!$volume_analysis) {
            return array('score' => 50, 'signal' => 'Unable to analyze volume patterns');
        }
        
        $base_score = 50; // Neutral starting point
        
        // Volume Rhythm Analysis
        $day_patterns = $volume_analysis['day_patterns'];
        $volume_trend = $volume_analysis['volume_trend'];
        $current_vs_pattern = $volume_analysis['current_vs_pattern'];
        $volume_price_correlation = $volume_analysis['volume_price_correlation'];
        $anomaly_score = $volume_analysis['anomaly_score'];
        
        // Score based on volume-price relationship
        if ($volume_price_correlation > 0.6) {
            // Strong positive correlation (volume confirms price moves)
            $base_score += $volume_weight;
            $signal = 'Volume Confirms Price Action';
        } elseif ($volume_price_correlation < -0.4) {
            // Negative correlation (divergence warning)
            $base_score -= ($volume_weight * 0.7);
            $signal = 'Volume-Price Divergence';
        } else {
            $signal = 'Mixed Volume Signals';
        }
        
        // Day-of-week rhythm analysis
        $current_day = date('N');
        $expected_volume_ratio = $day_patterns[$current_day] ?? 1.0;
        
        if ($current_vs_pattern > 1.5) {
            // Much higher volume than typical for this day
            $base_score += $rhythm_weight;
            $signal .= ' - High Volume Day';
        } elseif ($current_vs_pattern < 0.6) {
            // Much lower volume than typical
            $base_score -= ($rhythm_weight * 0.6);
            $signal .= ' - Low Volume Day';
        }
        
        // Volume trend consideration
        if ($volume_trend > 0.2) {
            // Increasing volume trend
            $base_score += 15;
            $signal .= ' - Rising Volume Trend';
        } elseif ($volume_trend < -0.2) {
            // Decreasing volume trend
            $base_score -= 12;
            $signal .= ' - Declining Volume Trend';
        }
        
        // Anomaly detection
        if ($anomaly_score > 2.0) {
            // Significant volume anomaly
            $base_score += $anomaly_weight;
            $signal .= ' - Volume Anomaly Detected';
        } elseif ($anomaly_score < -1.5) {
            // Unusually low volume
            $base_score -= ($anomaly_weight * 0.8);
            $signal .= ' - Unusually Low Volume';
        }
        
        // Institutional pattern recognition
        $institutional_pattern = $this->detect_institutional_patterns($day_patterns);
        if ($institutional_pattern['score'] != 0) {
            $base_score += $institutional_pattern['score'];
            $signal .= ' - ' . $institutional_pattern['pattern'];
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'volume_price_correlation' => round($volume_price_correlation, 3),
            'current_vs_pattern' => round($current_vs_pattern, 2),
            'volume_trend' => round($volume_trend, 3),
            'anomaly_score' => round($anomaly_score, 2),
            'day_patterns' => array_map(function($v) { return round($v, 2); }, $day_patterns),
            'analysis_weeks' => $lookback_weeks,
            'current_day' => $this->get_day_name($current_day)
        );
    }
    
    private function analyze_volume_rhythm($historical_data, $lookback_weeks) {
        $day_volumes = array(1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array());
        $volume_returns = array();
        $price_returns = array();
        $recent_volumes = array();
        
        $weeks_analyzed = 0;
        $data_count = count($historical_data);
        $total_volume = 0;
        $volume_count = 0;
        
        for ($i = 1; $i < $data_count && $weeks_analyzed < ($lookback_weeks * 5); $i++) {
            $current = $historical_data[$i];
            $previous = $historical_data[$i - 1];
            
            if (!isset($current['timestamp']) || !isset($current['volume']) || $current['volume'] <= 0) {
                continue;
            }
            
            $day_of_week = date('N', $current['timestamp']);
            
            // Skip weekends
            if ($day_of_week > 5) continue;
            
            $volume = $current['volume'];
            $day_volumes[$day_of_week][] = $volume;
            
            $total_volume += $volume;
            $volume_count++;
            
            // Track recent volumes for trend analysis
            if ($i < 20) {
                $recent_volumes[] = $volume;
            }
            
            // Volume and price returns for correlation
            if (isset($previous['volume']) && $previous['volume'] > 0 && isset($previous['close']) && isset($current['close'])) {
                $volume_change = (($volume - $previous['volume']) / $previous['volume']) * 100;
                $price_change = (($current['close'] - $previous['close']) / $previous['close']) * 100;
                
                $volume_returns[] = $volume_change;
                $price_returns[] = $price_change;
            }
        }
        
        if ($volume_count == 0) {
            return false;
        }
        
        $avg_volume = $total_volume / $volume_count;
        
        // Calculate day-of-week patterns
        $day_patterns = array();
        foreach ($day_volumes as $day => $volumes) {
            if (!empty($volumes)) {
                $day_avg = array_sum($volumes) / count($volumes);
                $day_patterns[$day] = $day_avg / $avg_volume; // Ratio to average
            } else {
                $day_patterns[$day] = 1.0;
            }
        }
        
        // Volume-price correlation
        $correlation = 0;
        if (count($volume_returns) > 5 && count($price_returns) > 5) {
            $correlation = $this->calculate_correlation($volume_returns, $price_returns);
        }
        
        // Volume trend (recent vs older)
        $volume_trend = 0;
        if (count($recent_volumes) > 5) {
            $recent_avg = array_sum($recent_volumes) / count($recent_volumes);
            $volume_trend = ($recent_avg - $avg_volume) / $avg_volume;
        }
        
        // Current volume vs pattern
        $current_day = date('N');
        $current_volume = end($historical_data)['volume'] ?? 0;
        $expected_volume = $avg_volume * ($day_patterns[$current_day] ?? 1.0);
        $current_vs_pattern = $expected_volume > 0 ? $current_volume / $expected_volume : 1.0;
        
        // Anomaly detection
        $anomaly_score = 0;
        if ($current_volume > 0 && $avg_volume > 0) {
            $z_score = ($current_volume - $avg_volume) / ($avg_volume * 0.5); // Simplified z-score
            $anomaly_score = $z_score;
        }
        
        return array(
            'day_patterns' => $day_patterns,
            'volume_price_correlation' => $correlation,
            'volume_trend' => $volume_trend,
            'current_vs_pattern' => $current_vs_pattern,
            'anomaly_score' => $anomaly_score,
            'avg_volume' => $avg_volume
        );
    }
    
    private function detect_institutional_patterns($day_patterns) {
        // Typical institutional patterns:
        // - Higher volume Monday/Friday (rebalancing)
        // - Lower volume Wednesday (mid-week lull)
        // - High Tuesday/Thursday (active trading)
        
        $monday_ratio = $day_patterns[1] ?? 1.0;
        $tuesday_ratio = $day_patterns[2] ?? 1.0;
        $wednesday_ratio = $day_patterns[3] ?? 1.0;
        $thursday_ratio = $day_patterns[4] ?? 1.0;
        $friday_ratio = $day_patterns[5] ?? 1.0;
        
        // Institutional accumulation pattern
        if ($monday_ratio > 1.2 && $friday_ratio > 1.1 && $wednesday_ratio < 0.9) {
            return array('score' => 10, 'pattern' => 'Institutional Rebalancing Pattern');
        }
        
        // Active trading pattern
        if ($tuesday_ratio > 1.15 && $thursday_ratio > 1.15) {
            return array('score' => 8, 'pattern' => 'Active Institutional Trading');
        }
        
        // Distribution pattern
        if ($monday_ratio > 1.3 && $tuesday_ratio > 1.2 && $friday_ratio < 0.8) {
            return array('score' => -8, 'pattern' => 'Potential Distribution Pattern');
        }
        
        return array('score' => 0, 'pattern' => 'No Clear Institutional Pattern');
    }
    
    private function calculate_correlation($x, $y) {
        $n = min(count($x), count($y));
        if ($n < 2) return 0;
        
        $sum_x = array_sum(array_slice($x, 0, $n));
        $sum_y = array_sum(array_slice($y, 0, $n));
        $sum_xy = 0;
        $sum_x2 = 0;
        $sum_y2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sum_xy += $x[$i] * $y[$i];
            $sum_x2 += $x[$i] * $x[$i];
            $sum_y2 += $y[$i] * $y[$i];
        }
        
        $denominator = sqrt(($n * $sum_x2 - $sum_x * $sum_x) * ($n * $sum_y2 - $sum_y * $sum_y));
        
        if ($denominator == 0) return 0;
        
        return ($n * $sum_xy - $sum_x * $sum_y) / $denominator;
    }
    
    private function get_day_name($day_number) {
        $days = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday');
        return $days[$day_number] ?? 'Unknown';
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $lookback_weeks = $config['lookback_weeks'] ?? 8;
        $volume_weight = $config['volume_weight'] ?? 30;
        $rhythm_weight = $config['rhythm_weight'] ?? 20;
        $anomaly_weight = $config['anomaly_weight'] ?? 15;
        
        return "Volume Rhythm Directive:\n\n" .
               "Configuration:\n" .
               "- Analysis Period: {$lookback_weeks} weeks\n" .
               "- Volume Weight: {$volume_weight} points\n" .
               "- Rhythm Weight: {$rhythm_weight} points\n" .
               "- Anomaly Weight: {$anomaly_weight} points\n\n" .
               "Analysis:\n" .
               "- Tracks day-of-week volume patterns\n" .
               "- Analyzes volume-price correlation\n" .
               "- Detects volume anomalies and trends\n" .
               "- Identifies institutional trading patterns\n\n" .
               "Scoring:\n" .
               "- Strong volume-price correlation (>0.6): +{$volume_weight} points\n" .
               "- Volume-price divergence (<-0.4): -" . round($volume_weight * 0.7) . " points\n" .
               "- High volume vs pattern (>150%): +{$rhythm_weight} points\n" .
               "- Volume anomaly detected (>2.0σ): +{$anomaly_weight} points\n" .
               "- Institutional patterns: ±8 to ±10 points\n\n" .
               "Volume rhythm analysis reveals institutional trading patterns and helps identify accumulation/distribution phases.";
    }
}