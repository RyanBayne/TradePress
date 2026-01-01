<?php
/**
 * Midweek Momentum Directive
 * 
 * Scores Tue-Thu volume/volatility strength patterns
 *
 * @package TradePress
 * @subpackage Scoring\Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Midweek_Momentum_Directive extends TradePress_Base_Directive {
    
    public function __construct() {
        parent::__construct();
        $this->directive_id = 'midweek_momentum';
        $this->name = 'Midweek Momentum';
        $this->description = 'Scores Tuesday-Thursday volume and volatility strength';
    }
    
    /**
     * Calculate midweek momentum score
     */
    public function calculate_score($symbol, $data = array()) {
        $score = 0;
        $signals = array();
        
        // Get last 4 weeks of daily data
        $daily_data = $this->get_daily_data($symbol, 28);
        
        if (!$daily_data) {
            return array(
                'score' => 0,
                'signals' => array(),
                'debug' => array('error' => 'No daily data available')
            );
        }
        
        // Analyze Tuesday-Thursday patterns
        $midweek_analysis = $this->analyze_midweek_patterns($daily_data);
        
        // Volume strength during midweek
        if ($midweek_analysis['avg_midweek_volume'] > $midweek_analysis['avg_other_volume'] * 1.2) {
            $score += 30;
            $signals[] = 'Strong midweek volume (+' . round(($midweek_analysis['avg_midweek_volume'] / $midweek_analysis['avg_other_volume'] - 1) * 100, 1) . '%)';
        }
        
        // Volatility patterns
        if ($midweek_analysis['midweek_volatility'] > $midweek_analysis['other_volatility'] * 1.1) {
            $score += 25;
            $signals[] = 'Higher midweek volatility';
        }
        
        // Price momentum during midweek
        if ($midweek_analysis['midweek_returns'] > 0.01) {
            $score += 35;
            $signals[] = 'Positive midweek momentum (+' . round($midweek_analysis['midweek_returns'] * 100, 2) . '%)';
        } elseif ($midweek_analysis['midweek_returns'] > 0) {
            $score += 15;
            $signals[] = 'Slight midweek momentum (+' . round($midweek_analysis['midweek_returns'] * 100, 2) . '%)';
        }
        
        return array(
            'score' => max(0, min(100, $score)),
            'signals' => $signals,
            'debug' => $midweek_analysis
        );
    }
    
    /**
     * Analyze midweek (Tue-Thu) patterns vs other days
     */
    private function analyze_midweek_patterns($daily_data) {
        $midweek_volume = array();
        $other_volume = array();
        $midweek_returns = array();
        $midweek_ranges = array();
        $other_ranges = array();
        
        foreach ($daily_data as $date => $data) {
            $day_of_week = date('N', strtotime($date)); // 1=Monday, 7=Sunday
            
            $daily_return = ($data['close'] - $data['open']) / $data['open'];
            $daily_range = ($data['high'] - $data['low']) / $data['low'];
            
            if ($day_of_week >= 2 && $day_of_week <= 4) { // Tue-Thu
                $midweek_volume[] = $data['volume'];
                $midweek_returns[] = $daily_return;
                $midweek_ranges[] = $daily_range;
            } else {
                $other_volume[] = $data['volume'];
                $other_ranges[] = $daily_range;
            }
        }
        
        return array(
            'avg_midweek_volume' => !empty($midweek_volume) ? array_sum($midweek_volume) / count($midweek_volume) : 0,
            'avg_other_volume' => !empty($other_volume) ? array_sum($other_volume) / count($other_volume) : 0,
            'midweek_returns' => !empty($midweek_returns) ? array_sum($midweek_returns) / count($midweek_returns) : 0,
            'midweek_volatility' => !empty($midweek_ranges) ? array_sum($midweek_ranges) / count($midweek_ranges) : 0,
            'other_volatility' => !empty($other_ranges) ? array_sum($other_ranges) / count($other_ranges) : 0
        );
    }
    
    /**
     * Get daily data for analysis
     */
    private function get_daily_data($symbol, $days = 28) {
        // Use Alpha Vantage daily time series
        $alpha_vantage = new TradePress_AlphaVantage_API();
        
        $params = array(
            'symbol' => $symbol,
            'outputsize' => 'compact'
        );
        
        return $alpha_vantage->call_endpoint('TIME_SERIES_DAILY', $params);
    }
    
    /**
     * Get required API endpoints
     */
    public function get_api_requirements() {
        return array(
            'alpha_vantage' => array(
                'TIME_SERIES_DAILY'
            )
        );
    }
}