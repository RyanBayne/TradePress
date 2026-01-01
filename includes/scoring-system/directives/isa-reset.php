<?php
/**
 * ISA Reset Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_ISA_RESET extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'isa_reset';
        $this->name = 'ISA Reset Period';
        $this->description = 'Increases score during ISA reset period (March-April)';
        $this->weight = 10;
        $this->max_score = 50;
        $this->bullish_values = 'Active during ISA reset period';
        $this->bearish_values = 'Not applicable';
        $this->priority = 20;
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $days_before = $config['days_before'] ?? 3;
        $days_after = $config['days_after'] ?? 3;
        $score_impact = $config['score_impact'] ?? 10;
        
        $isa_reset_date = date('Y') . '-04-06';
        $current_date = current_time('Y-m-d');
        
        $reset_timestamp = strtotime($isa_reset_date);
        $current_timestamp = strtotime($current_date);
        $days_diff = ($reset_timestamp - $current_timestamp) / (24 * 60 * 60);
        
        if ($days_diff >= -$days_after && $days_diff <= $days_before) {
            return array('score' => $score_impact, 'in_period' => true, 'days_to_reset' => $days_diff);
        }
        
        return array('score' => 0, 'in_period' => false, 'days_to_reset' => $days_diff);
    }
    
    public function get_max_score($config = array()) {
        return $config['score_impact'] ?? 10;
    }
    
    public function get_explanation($config = array()) {
        $days_before = $config['days_before'] ?? 3;
        $days_after = $config['days_after'] ?? 3;
        $score_impact = $config['score_impact'] ?? 10;
        
        return "ISA Reset Directive:\n\n" .
               "Adds {$score_impact} points during ISA reset period\n" .
               "Active Period: {$days_before} days before to {$days_after} days after April 6th\n\n" .
               "This directive captures seasonal buying pressure when UK investors receive fresh ISA allowances.";
    }
}