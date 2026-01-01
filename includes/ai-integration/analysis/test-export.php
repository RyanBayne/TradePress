<?php
/**
 * Export test data for VS Code/AI analysis
 */

class TradePress_Test_Export {
    
    /**
     * Export test results to JSON for AI analysis
     */
    public static function export_for_ai_analysis($directive_id = null) {
        $tests = TradePress_Test_Logger::get_recent_tests($directive_id, 50);
        
        $export_data = array(
            'export_timestamp' => current_time('mysql'),
            'directive_filter' => $directive_id,
            'test_count' => count($tests),
            'tests' => array()
        );
        
        foreach ($tests as $test) {
            $export_data['tests'][] = array(
                'directive_id' => $test['directive_id'],
                'symbol' => $test['symbol'],
                'timestamp' => $test['test_timestamp'],
                'score' => $test['score'],
                'indicator_data' => json_decode($test['indicator_value'], true),
                'validation_needed' => self::needs_validation($test)
            );
        }
        
        // Save to file for VS Code access
        $export_file = TRADEPRESS_PLUGIN_DIR_PATH . 'ai-analysis-export.json';
        file_put_contents($export_file, json_encode($export_data, JSON_PRETTY_PRINT));
        
        return $export_file;
    }
    
    /**
     * Check if test needs validation
     */
    private static function needs_validation($test) {
        return $test['validation_status'] === 'pending' || 
               $test['validation_status'] === 'failed';
    }
}