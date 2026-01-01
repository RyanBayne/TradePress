<?php
/**
 * AI-Assisted Directive Validation System
 */

class TradePress_AI_Directive_Validator {
    
    /**
     * Validate directive test results with AI analysis
     */
    public static function validate_test_result($directive_id, $test_result, $symbol_data) {
        $validation = array(
            'directive_id' => $directive_id,
            'is_valid' => true,
            'confidence' => 'high',
            'issues' => array(),
            'recommendations' => array(),
            'expected_ranges' => self::get_expected_ranges($directive_id),
            'actual_values' => self::extract_actual_values($directive_id, $test_result, $symbol_data)
        );
        
        // Validate based on directive type
        switch ($directive_id) {
            case 'adx':
                return self::validate_adx($validation, $test_result, $symbol_data);
            case 'cci':
                return self::validate_cci($validation, $test_result, $symbol_data);
            case 'rsi':
                return self::validate_rsi($validation, $test_result, $symbol_data);
            default:
                return self::validate_generic($validation, $test_result, $symbol_data);
        }
    }
    
    /**
     * Validate ADX directive results
     */
    private static function validate_adx($validation, $test_result, $symbol_data) {
        $adx_value = $symbol_data['technical']['adx']['adx'] ?? null;
        $score = $test_result['result']['score'] ?? 0;
        
        // ADX should be 0-100
        if ($adx_value < 0 || $adx_value > 100) {
            $validation['issues'][] = "ADX value {$adx_value} outside normal range (0-100)";
            $validation['is_valid'] = false;
        }
        
        // Score validation for ADX=39.34, Score=80
        if ($adx_value > 25 && $score < 50) {
            $validation['issues'][] = "Strong ADX ({$adx_value}) should produce score > 50, got {$score}";
            $validation['confidence'] = 'low';
        }
        
        if ($adx_value > 40 && $score < 70) {
            $validation['recommendations'][] = "Very strong ADX ({$adx_value}) typically scores 70+";
        }
        
        return $validation;
    }
    
    /**
     * Get expected ranges for each directive
     */
    private static function get_expected_ranges($directive_id) {
        $ranges = array(
            'adx' => array('value' => '0-100', 'score' => '20-100'),
            'cci' => array('value' => '-300 to +300', 'score' => '0-100'),
            'rsi' => array('value' => '0-100', 'score' => '0-150'),
            'macd' => array('value' => 'varies', 'score' => '0-100')
        );
        
        return $ranges[$directive_id] ?? array('value' => 'unknown', 'score' => '0-100');
    }
    
    /**
     * Extract actual values from test results
     */
    private static function extract_actual_values($directive_id, $test_result, $symbol_data) {
        return array(
            'indicator_value' => $symbol_data['technical'][$directive_id] ?? 'N/A',
            'score' => $test_result['result']['score'] ?? 0,
            'signal' => $test_result['result']['signal'] ?? 'No Signal'
        );
    }
}