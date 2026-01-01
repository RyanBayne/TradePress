<?php
/**
 * Advanced Weekly Rhythm Composite Directive
 * 
 * Combines all 7 weekly rhythm directives for comprehensive temporal analysis
 *
 * @package TradePress
 * @subpackage Scoring\Directives
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Advanced_Weekly_Rhythm_Directive extends TradePress_Base_Directive {
    
    public function __construct() {
        parent::__construct();
        $this->directive_id = 'advanced_weekly_rhythm';
        $this->name = 'Advanced Weekly Rhythm';
        $this->description = 'Comprehensive composite directive using all 7 weekly rhythm components';
    }
    
    /**
     * Calculate composite score from all weekly rhythm directives
     */
    public function calculate_score($symbol, $data = array()) {
        $total_score = 0;
        $signals = array();
        $debug = array();
        
        // Define all weekly rhythm directives with weights
        $directives = array(
            'monday_effect' => array('weight' => 0.15, 'class' => 'TradePress_Monday_Effect_Directive'),
            'friday_positioning' => array('weight' => 0.15, 'class' => 'TradePress_Friday_Positioning_Directive'),
            'volume_rhythm' => array('weight' => 0.15, 'class' => 'TradePress_Volume_Rhythm_Directive'),
            'institutional_timing' => array('weight' => 0.20, 'class' => 'TradePress_Institutional_Timing_Directive'),
            'midweek_momentum' => array('weight' => 0.15, 'class' => 'TradePress_Midweek_Momentum_Directive'),
            'intraday_u_pattern' => array('weight' => 0.10, 'class' => 'TradePress_Intraday_U_Pattern_Directive'),
            'time_based_support' => array('weight' => 0.10, 'class' => 'TradePress_Time_Based_Support_Directive')
        );
        
        $successful_directives = 0;
        $total_weight = 0;
        
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
                        $total_weight += $config['weight'];
                        $successful_directives++;
                        
                        $debug[$directive_id] = array(
                            'raw_score' => $result['score'],
                            'weight' => $config['weight'],
                            'weighted_score' => $weighted_score,
                            'signals' => $result['signals'] ?? array(),
                            'status' => 'success'
                        );
                        
                        // Add significant signals (score > 60 or < 40)
                        if (!empty($result['signals']) && ($result['score'] > 60 || $result['score'] < 40)) {
                            foreach ($result['signals'] as $signal) {
                                $signals[] = ucfirst(str_replace('_', ' ', $directive_id)) . ': ' . $signal;
                            }
                        }
                    } else {
                        $debug[$directive_id] = array(
                            'status' => 'failed',
                            'error' => 'No score returned'
                        );
                    }
                } else {
                    $debug[$directive_id] = array(
                        'status' => 'failed',
                        'error' => 'Class not found: ' . $config['class']
                    );
                }
            } else {
                $debug[$directive_id] = array(
                    'status' => 'failed',
                    'error' => 'File not found: ' . $file_path
                );
            }
        }
        
        // Normalize score if not all directives were successful
        if ($total_weight > 0 && $total_weight < 1.0) {
            $total_score = $total_score / $total_weight;
        }
        
        // Add summary signal
        if ($successful_directives >= 5) {
            $signals[] = "Advanced analysis complete: {$successful_directives}/7 components active";
        } else {
            $signals[] = "Limited analysis: Only {$successful_directives}/7 components available";
        }
        
        return array(
            'score' => round($total_score, 1),
            'signals' => $signals,
            'debug' => array_merge($debug, array(
                'successful_components' => $successful_directives,
                'total_components' => count($directives),
                'total_weight' => $total_weight
            ))
        );
    }
    
    /**
     * Get required API endpoints from all component directives
     */
    public function get_api_requirements() {
        return array(
            'alpha_vantage' => array(
                'TIME_SERIES_DAILY'
            ),
            'finnhub' => array(
                'stock/candle',
                'stock/metric'
            ),
            'alpaca' => array(
                'bars',
                'quotes'
            )
        );
    }
}