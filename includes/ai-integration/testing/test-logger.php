<?php
/**
 * Test Results Logger for AI Analysis
 */

class TradePress_Test_Logger {
    
    /**
     * Log directive test for AI analysis
     */
    public static function log_directive_test($directive_id, $symbol, $test_result, $symbol_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_directive_tests';
        
        $log_data = array(
            'directive_id' => $directive_id,
            'symbol' => $symbol,
            'test_timestamp' => current_time('mysql'),
            'score' => $test_result['result']['score'] ?? 0,
            'indicator_value' => json_encode(self::extract_indicator_data($directive_id, $symbol_data)),
            'raw_data' => json_encode($symbol_data),
            'test_result' => json_encode($test_result),
            'validation_status' => 'pending'
        );
        
        $wpdb->insert($table_name, $log_data);
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get recent test logs for AI analysis
     */
    public static function get_recent_tests($directive_id = null, $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_directive_tests';
        $where = $directive_id ? $wpdb->prepare("WHERE directive_id = %s", $directive_id) : "";
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} {$where} ORDER BY test_timestamp DESC LIMIT {$limit}",
            ARRAY_A
        );
    }
    
    /**
     * Extract key indicator data for logging
     */
    private static function extract_indicator_data($directive_id, $symbol_data) {
        switch ($directive_id) {
            case 'adx':
                return $symbol_data['technical']['adx'] ?? null;
            case 'cci':
                return array('cci' => $symbol_data['technical']['cci'] ?? null);
            case 'rsi':
                return array('rsi' => $symbol_data['technical']['rsi'] ?? null);
            default:
                return $symbol_data['technical'] ?? null;
        }
    }
}