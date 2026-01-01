<?php
/**
 * Basic Weekly Rhythm Composite Directive
 * 
 * Combines monday-effect + midweek-momentum + volume-rhythm for simplified user experience
 *
 * @package TradePress
 * @subpackage Scoring\Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Basic_Weekly_Rhythm_Directive extends TradePress_Base_Directive {
    
    public function __construct() {
        parent::__construct();
        $this->directive_id = 'basic_weekly_rhythm';
        $this->name = 'Basic Weekly Rhythm';
        $this->description = 'Composite directive combining Monday Effect, Midweek Momentum, and Volume Rhythm patterns';
    }
    
    /**
     * Calculate composite score from individual directives
     */
    public function calculate_score($symbol, $data = array()) {
        $total_score = 0;
        $signals = array();
        $debug = array();
        
        // Load individual directives
        $directives = array(
            'monday_effect' => array('weight' => 0.3, 'class' => 'TradePress_Monday_Effect_Directive'),
            'midweek_momentum' => array('weight' => 0.4, 'class' => 'TradePress_Midweek_Momentum_Directive'),
            'volume_rhythm' => array('weight' => 0.3, 'class' => 'TradePress_Volume_Rhythm_Directive')
        );
        
        foreach ($directives as $directive_id => $config) {
            // Load directive file
            $file_path = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/' . str_replace('_', '-', $directive_id) . '.php';
            
            if (file_exists($file_path)) {
                require_once $file_path;
                
                if (class_exists($config['class'])) {
                    $directive = new $config['class']();
                    $result = $directive->calculate_score($symbol, $data);
                    
                    if (is_array($result) && isset($result['score'])) {
                        $weighted_score = $result['score'] * $config['weight'];
                        $total_score += $weighted_score;
                        
                        $debug[$directive_id] = array(
                            'raw_score' => $result['score'],
                            'weight' => $config['weight'],
                            'weighted_score' => $weighted_score,
                            'signals' => $result['signals'] ?? array()
                        );
                        
                        // Add significant signals to main signals array
                        if (!empty($result['signals'])) {
                            foreach ($result['signals'] as $signal) {
                                $signals[] = ucfirst(str_replace('_', ' ', $directive_id)) . ': ' . $signal;
                            }
                        }
                    }
                }
            }
        }
        
        return array(
            'score' => round($total_score, 1),
            'signals' => $signals,
            'debug' => $debug
        );
    }
    
    /**
     * Get required API endpoints from component directives
     */
    public function get_api_requirements() {
        return array(
            'alpha_vantage' => array(
                'TIME_SERIES_DAILY'
            ),
            'finnhub' => array(
                'stock/candle'
            )
        );
    }
}