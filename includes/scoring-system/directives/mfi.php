<?php
/**
 * MFI (Money Flow Index) Directive
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Scoring_Directive_MFI extends TradePress_Scoring_Directive_Base {
    
    public function __construct() {
        $this->id = 'mfi';
        $this->name = 'Money Flow Index';
        $this->description = 'Volume-weighted RSI that measures buying/selling pressure';
        $this->weight = 10;
        $this->max_score = 100;
        $this->bullish_values = 'Below 20 (Oversold)';
        $this->bearish_values = 'Above 80 (Overbought)';
        $this->priority = 10;
    }
    
    public function calculate_score($symbol_data, $config = array()) {
        $oversold = $config['oversold'] ?? 20;
        $overbought = $config['overbought'] ?? 80;
        
        $mfi_value = $symbol_data['technical']['mfi'] ?? null;
        
        if ($mfi_value === null) {
            return array('score' => 0, 'signal' => 'No MFI data');
        }
        
        // Base score starts at 50 (neutral)
        $base_score = 50;
        
        // Determine signal and scoring
        if ($mfi_value <= $oversold) {
            // Oversold condition - bullish signal
            $signal = 'Oversold - Strong Buy';
            $intensity = ($oversold - $mfi_value) / $oversold; // 0 to 1
            $base_score += 35 + ($intensity * 15); // 35-50 points
        } elseif ($mfi_value >= $overbought) {
            // Overbought condition - bearish signal
            $signal = 'Overbought - Strong Sell';
            $intensity = ($mfi_value - $overbought) / (100 - $overbought); // 0 to 1
            $base_score -= 25 + ($intensity * 15); // -25 to -40 points
        } elseif ($mfi_value <= 35) {
            // Approaching oversold
            $signal = 'Approaching Oversold';
            $base_score += 20;
        } elseif ($mfi_value >= 65) {
            // Approaching overbought
            $signal = 'Approaching Overbought';
            $base_score -= 15;
        } elseif ($mfi_value >= 40 && $mfi_value <= 60) {
            // Neutral zone
            $signal = 'Neutral Zone';
            $base_score += 5;
        } else {
            $signal = 'Normal Range';
        }
        
        // Volume consideration bonus (MFI incorporates volume)
        if ($mfi_value <= $oversold || $mfi_value >= $overbought) {
            // Extreme readings with volume confirmation are more reliable
            $base_score += ($mfi_value <= $oversold ? 10 : -10);
        }
        
        return array(
            'score' => max(0, min(100, round($base_score))),
            'signal' => $signal,
            'mfi_value' => round($mfi_value, 2),
            'condition' => $mfi_value <= $oversold ? 'Oversold' : 
                          ($mfi_value >= $overbought ? 'Overbought' : 'Normal'),
            'volume_weighted' => true
        );
    }
    
    public function get_max_score($config = array()) {
        return 100;
    }
    
    public function get_explanation($config = array()) {
        $period = $config['period'] ?? 14;
        $oversold = $config['oversold'] ?? 20;
        $overbought = $config['overbought'] ?? 80;
        
        return "MFI (Money Flow Index) Directive:\n\n" .
               "Period: {$period} days\n" .
               "Oversold Threshold: {$oversold}\n" .
               "Overbought Threshold: {$overbought}\n\n" .
               "Scoring:\n" .
               "- Oversold (≤{$oversold}): +35 to +50 points (strong buy)\n" .
               "- Overbought (≥{$overbought}): -25 to -40 points (strong sell)\n" .
               "- Approaching oversold (≤35): +20 points\n" .
               "- Approaching overbought (≥65): -15 points\n" .
               "- Neutral zone (40-60): +5 points\n" .
               "- Volume confirmation bonus: ±10 points\n\n" .
               "MFI combines price and volume for more reliable signals than RSI alone.";
    }
}