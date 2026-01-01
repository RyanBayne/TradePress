<?php
/**
 * Institutional Timing Directive
 * 
 * Analyzes end-of-period rebalancing effects and institutional timing patterns
 * Detects month-end, quarter-end, and year-end institutional activities
 * 
 * @package TradePress
 * @subpackage Scoring_Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_INSTITUTIONAL_TIMING extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'institutional_timing';
        $this->name = 'Institutional Timing';
        $this->description = 'Analyzes end-of-period rebalancing effects and institutional timing patterns';
        $this->weight = 16;
        $this->max_score = 100;
        $this->bullish_values = 'Positive institutional flows, end-of-period buying';
        $this->bearish_values = 'Institutional selling pressure, rebalancing outflows';
        $this->priority = 28;
        $this->api_requirements = array('Alpha Vantage Daily Time Series', 'Economic Calendar data');
        $this->update_frequency = 'Monthly/Quarterly analysis';
        $this->api_cost = 'LOW';
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $lookback_months = $config['lookback_months'] ?? 6;
        $period_weight = $config['period_weight'] ?? 25;
        $flow_weight = $config['flow_weight'] ?? 20;
        $timing_weight = $config['timing_weight'] ?? 15;
        
        $historical_data = $symbol_data['historical'] ?? null;
        $current_price = $symbol_data['price'] ?? 0;
        
        if (!$historical_data || empty($historical_data) || $current_price <= 0) {
            return array('score' => 50, 'signal' => 'Insufficient historical data');
        }
        
        // Analyze institutional timing patterns
        $timing_analysis = $this->analyze_institutional_timing($historical_data, $lookback_months);
        
        if (!$timing_analysis) {
            return array('score' => 50, 'signal' => 'Unable to analyze institutional patterns');
        }
        
        $base_score = 50; // Neutral starting point
        
        // Institutional Timing Analysis
        $month_end_effect = $timing_analysis['month_end_effect'];
        $quarter_end_effect = $timing_analysis['quarter_end_effect'];
        $current_period_position = $timing_analysis['current_period_position'];
        $institutional_flow_trend = $timing_analysis['flow_trend'];
        $rebalancing_pressure = $timing_analysis['rebalancing_pressure'];
        
        // Current timing context
        $current_date = new DateTime();
        $days_to_month_end = $this->days_to_month_end($current_date);
        $days_to_quarter_end = $this->days_to_quarter_end($current_date);
        
        // Score based on period effects
        if ($days_to_month_end <= 3) {
            // Near month-end
            if ($month_end_effect > 0.2) {
                $base_score += $period_weight;
                $signal = 'Positive Month-End Effect';
            } elseif ($month_end_effect < -0.2) {
                $base_score -= ($period_weight * 0.8);
                $signal = 'Negative Month-End Effect';
            } else {
                $signal = 'Near Month-End';
            }
        } elseif ($days_to_quarter_end <= 5) {
            // Near quarter-end
            if ($quarter_end_effect > 0.3) {
                $base_score += ($period_weight * 1.2);
                $signal = 'Positive Quarter-End Effect';
            } elseif ($quarter_end_effect < -0.3) {
                $base_score -= $period_weight;
                $signal = 'Negative Quarter-End Effect';
            } else {
                $signal = 'Near Quarter-End';
            }
        } else {
            $signal = 'Mid-Period';
        }
        
        // Institutional flow analysis
        if ($institutional_flow_trend > 0.4) {
            // Strong institutional buying
            $base_score += $flow_weight;
            $signal .= ' - Strong Institutional Buying';
        } elseif ($institutional_flow_trend < -0.4) {
            // Strong institutional selling
            $base_score -= ($flow_weight * 0.9);
            $signal .= ' - Institutional Selling Pressure';
        } elseif (abs($institutional_flow_trend) > 0.2) {
            $signal .= ' - Moderate Institutional Activity';
        }
        
        // Rebalancing pressure
        if ($rebalancing_pressure > 0.5) {
            // High rebalancing buying pressure
            $base_score += $timing_weight;
            $signal .= ' - Rebalancing Buying';
        } elseif ($rebalancing_pressure < -0.5) {
            // High rebalancing selling pressure
            $base_score -= ($timing_weight * 0.8);
            $signal .= ' - Rebalancing Selling';
        }
        
        // Current period position
        if ($current_period_position > 0.6) {
            // Strong performance this period
            $base_score += 10;
            $signal .= ' - Strong Period Performance';
        } elseif ($current_period_position < 0.4) {
            // Weak performance this period
            $base_score -= 8;
            $signal .= ' - Weak Period Performance';
        }
        
        // Special considerations for year-end
        if ($current_date->format('m') == '12' && $current_date->format('d') > 15) {
            // Year-end considerations
            $year_end_effect = $timing_analysis['year_end_effect'] ?? 0;
            if ($year_end_effect > 0.3) {
                $base_score += 15;
                $signal .= ' - Year-End Rally';
            } elseif ($year_end_effect < -0.2) {
                $base_score -= 10;
                $signal .= ' - Year-End Selling';
            }
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'month_end_effect' => round($month_end_effect, 3),
            'quarter_end_effect' => round($quarter_end_effect, 3),
            'flow_trend' => round($institutional_flow_trend, 3),
            'rebalancing_pressure' => round($rebalancing_pressure, 3),
            'days_to_month_end' => $days_to_month_end,
            'days_to_quarter_end' => $days_to_quarter_end,
            'current_period_position' => round($current_period_position, 3),
            'analysis_months' => $lookback_months
        );
    }
    
    private function analyze_institutional_timing($historical_data, $lookback_months) {
        $month_end_returns = array();
        $quarter_end_returns = array();
        $regular_returns = array();
        $volume_flows = array();
        $period_performances = array();
        
        $months_analyzed = 0;
        $data_count = count($historical_data);
        
        for ($i = 1; $i < $data_count && $months_analyzed < ($lookback_months * 22); $i++) {
            $current = $historical_data[$i];
            $previous = $historical_data[$i - 1];
            
            if (!isset($current['timestamp']) || !isset($previous['close']) || !isset($current['close'])) {
                continue;
            }
            
            $date = new DateTime();
            $date->setTimestamp($current['timestamp']);
            $daily_return = (($current['close'] - $previous['close']) / $previous['close']) * 100;
            
            // Check if this is near month-end or quarter-end
            $days_to_month_end = $this->days_to_month_end($date);
            $days_to_quarter_end = $this->days_to_quarter_end($date);
            
            if ($days_to_month_end <= 2) {
                $month_end_returns[] = $daily_return;
            } elseif ($days_to_quarter_end <= 3) {
                $quarter_end_returns[] = $daily_return;
            } else {
                $regular_returns[] = $daily_return;
            }
            
            // Volume flow analysis (simplified)
            if (isset($current['volume']) && isset($previous['volume']) && $previous['volume'] > 0) {
                $volume_change = (($current['volume'] - $previous['volume']) / $previous['volume']) * 100;
                $volume_flows[] = array(
                    'volume_change' => $volume_change,
                    'price_change' => $daily_return,
                    'date' => $date->format('Y-m-d')
                );
            }
        }
        
        if (empty($month_end_returns) && empty($quarter_end_returns)) {
            return false;
        }
        
        // Calculate effects
        $avg_regular = !empty($regular_returns) ? array_sum($regular_returns) / count($regular_returns) : 0;
        $avg_month_end = !empty($month_end_returns) ? array_sum($month_end_returns) / count($month_end_returns) : 0;
        $avg_quarter_end = !empty($quarter_end_returns) ? array_sum($quarter_end_returns) / count($quarter_end_returns) : 0;
        
        $month_end_effect = $avg_month_end - $avg_regular;
        $quarter_end_effect = $avg_quarter_end - $avg_regular;
        
        // Institutional flow trend (volume-weighted price changes)
        $flow_trend = $this->calculate_flow_trend($volume_flows);
        
        // Rebalancing pressure (based on recent performance vs historical)
        $rebalancing_pressure = $this->calculate_rebalancing_pressure($historical_data);
        
        // Current period position
        $current_period_position = $this->calculate_current_period_position($historical_data);
        
        return array(
            'month_end_effect' => $month_end_effect,
            'quarter_end_effect' => $quarter_end_effect,
            'flow_trend' => $flow_trend,
            'rebalancing_pressure' => $rebalancing_pressure,
            'current_period_position' => $current_period_position,
            'total_observations' => count($month_end_returns) + count($quarter_end_returns) + count($regular_returns)
        );
    }
    
    private function calculate_flow_trend($volume_flows) {
        if (empty($volume_flows) || count($volume_flows) < 10) {
            return 0;
        }
        
        $recent_flows = array_slice($volume_flows, 0, 20); // Last 20 days
        $flow_score = 0;
        
        foreach ($recent_flows as $flow) {
            // Positive volume change with positive price change = institutional buying
            if ($flow['volume_change'] > 10 && $flow['price_change'] > 0) {
                $flow_score += 1;
            } elseif ($flow['volume_change'] > 10 && $flow['price_change'] < 0) {
                $flow_score -= 1;
            }
        }
        
        return $flow_score / count($recent_flows);
    }
    
    private function calculate_rebalancing_pressure($historical_data) {
        if (count($historical_data) < 60) {
            return 0;
        }
        
        // Compare recent 30-day performance to 60-day average
        $recent_data = array_slice($historical_data, 0, 30);
        $older_data = array_slice($historical_data, 30, 30);
        
        $recent_return = $this->calculate_period_return($recent_data);
        $older_return = $this->calculate_period_return($older_data);
        
        // If recent performance is much better, expect rebalancing selling
        // If recent performance is much worse, expect rebalancing buying
        return ($older_return - $recent_return) / 10; // Normalized
    }
    
    private function calculate_current_period_position($historical_data) {
        if (count($historical_data) < 30) {
            return 0.5;
        }
        
        $month_data = array_slice($historical_data, 0, 22); // Approximate month
        $month_return = $this->calculate_period_return($month_data);
        
        // Normalize to 0-1 scale (assuming ±10% is the range)
        return max(0, min(1, ($month_return + 10) / 20));
    }
    
    private function calculate_period_return($data) {
        if (count($data) < 2) {
            return 0;
        }
        
        $start_price = end($data)['close'] ?? 0;
        $end_price = $data[0]['close'] ?? 0;
        
        if ($start_price <= 0) {
            return 0;
        }
        
        return (($end_price - $start_price) / $start_price) * 100;
    }
    
    private function days_to_month_end($date) {
        $month_end = clone $date;
        $month_end->modify('last day of this month');
        return $date->diff($month_end)->days;
    }
    
    private function days_to_quarter_end($date) {
        $quarter = ceil($date->format('n') / 3);
        $quarter_end_month = $quarter * 3;
        
        $quarter_end = clone $date;
        $quarter_end->setDate($date->format('Y'), $quarter_end_month, 1);
        $quarter_end->modify('last day of this month');
        
        return $date->diff($quarter_end)->days;
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $lookback_months = $config['lookback_months'] ?? 6;
        $period_weight = $config['period_weight'] ?? 25;
        $flow_weight = $config['flow_weight'] ?? 20;
        $timing_weight = $config['timing_weight'] ?? 15;
        
        return "Institutional Timing Directive:\n\n" .
               "Configuration:\n" .
               "- Analysis Period: {$lookback_months} months\n" .
               "- Period Weight: {$period_weight} points\n" .
               "- Flow Weight: {$flow_weight} points\n" .
               "- Timing Weight: {$timing_weight} points\n\n" .
               "Analysis:\n" .
               "- Tracks month-end and quarter-end effects\n" .
               "- Analyzes institutional flow patterns\n" .
               "- Detects rebalancing pressure\n" .
               "- Considers current period performance\n\n" .
               "Scoring:\n" .
               "- Positive month-end effect (>0.2%): +{$period_weight} points\n" .
               "- Positive quarter-end effect (>0.3%): +" . round($period_weight * 1.2) . " points\n" .
               "- Strong institutional buying: +{$flow_weight} points\n" .
               "- Rebalancing buying pressure: +{$timing_weight} points\n" .
               "- Year-end rally effect: +15 points\n\n" .
               "Institutional timing patterns reflect portfolio rebalancing, window dressing, and regulatory reporting requirements.";
    }
}