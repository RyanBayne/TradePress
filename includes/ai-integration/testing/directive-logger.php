<?php
/**
 * Simple Directive Testing Logger
 */

class TradePress_Directive_Logger {
    
    /**
     * Simple log method for directive calculations
     */
    public static function log($message) {
        if (get_option('bugnet_output_directives') !== 'yes') {
            return;
        }
        
        $log_file = WP_CONTENT_DIR . '/directives.log';
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        
        error_log($log_entry, 3, $log_file);
    }
    
    /**
     * Log directive test result to directives.log
     */
    public static function log_test($directive_id, $symbol, $test_result, $symbol_data = null) {
        if (get_option('bugnet_output_directives') !== 'yes') {
            return;
        }
        
        $log_file = WP_CONTENT_DIR . '/directives.log';
        $timestamp = current_time('Y-m-d H:i:s');
        
        // Extract key data
        $score = $test_result['result']['score'] ?? 0;
        $signal = $test_result['result']['signal'] ?? 'No Signal';
        
        // Get indicator-specific data
        $indicator_data = '';
        if ($symbol_data && isset($symbol_data['technical'])) {
            switch ($directive_id) {
                case 'adx':
                    $adx = $symbol_data['technical']['adx']['adx'] ?? 'N/A';
                    $indicator_data = "ADX={$adx}";
                    break;
                case 'cci':
                    $cci = $symbol_data['technical']['cci'] ?? 'N/A';
                    $indicator_data = "CCI={$cci}";
                    break;
                case 'rsi':
                    $rsi = $symbol_data['technical']['rsi'] ?? 'N/A';
                    $indicator_data = "RSI={$rsi}";
                    break;
                default:
                    $indicator_data = "Data=Available";
            }
        }
        
        $log_entry = "[{$timestamp}] {$directive_id} | {$symbol} | {$indicator_data} | Score={$score} | Signal={$signal}\n";
        
        error_log($log_entry, 3, $log_file);
    }
    
    /**
     * Log validation result
     */
    public static function log_validation($directive_id, $symbol, $validation_result) {
        if (get_option('bugnet_output_directives') !== 'yes') {
            return;
        }
        
        $log_file = WP_CONTENT_DIR . '/directives.log';
        $timestamp = current_time('Y-m-d H:i:s');
        
        $status = $validation_result['is_valid'] ? 'VALID' : 'INVALID';
        $confidence = $validation_result['confidence'] ?? 'unknown';
        $issues = !empty($validation_result['issues']) ? implode(', ', $validation_result['issues']) : 'None';
        
        $log_entry = "[{$timestamp}] VALIDATION | {$directive_id} | {$symbol} | Status={$status} | Confidence={$confidence} | Issues={$issues}\n";
        
        error_log($log_entry, 3, $log_file);
    }
}