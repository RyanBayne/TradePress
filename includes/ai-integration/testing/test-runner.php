<?php
/**
 * TradePress AI Integration - Test Runner
 *
 * @package TradePress/AI
 * @version 1.0.0
 */

require_once 'directive-tester.php';
require_once 'directive-logger.php';

class TradePress_AI_Test_Runner {
    
    /**
     * Run single directive test
     */
    public static function run_single_test($directive_id) {
        echo "Testing Directive: {$directive_id}\n";
        echo str_repeat("=", 50) . "\n";
        
        $result = TradePress_AI_Directive_Tester::test_directive($directive_id);
        
        // Log to directives.log if BugNet directives output is enabled
        if (get_option('bugnet_output_directives') === 'yes') {
            if (class_exists('TradePress_Directive_Logger')) {
                if ($result['success']) {
                    $log_message = "DIRECTIVE TEST: {$directive_id} - SUCCESS - Result: " . json_encode($result['result']) . " - Time: {$result['execution_time']}";
                } else {
                    $log_message = "DIRECTIVE TEST: {$directive_id} - FAILED - Error: {$result['error']}";
                }
                TradePress_Directive_Logger::log($log_message);
            }
        }
        
        if ($result['success']) {
            echo "✅ SUCCESS\n";
            echo "Class: {$result['class_name']}\n";
            echo "Execution Time: {$result['execution_time']}\n";
            echo "Result: " . print_r($result['result'], true) . "\n";
        } else {
            echo "❌ FAILED\n";
            echo "Error: {$result['error']}\n";
        }
        
        echo "\n";
        return $result;
    }
    
    /**
     * Test all directives in priority order
     */
    public static function test_all_directives() {
        $directives = array(
            'rsi', 'volume', 'adx', 'macd', 'bollinger-bands',
            'ema', 'cci', 'mfi', 'moving-averages', 'obv'
        );
        
        $results = array();
        foreach ($directives as $directive) {
            $results[$directive] = self::run_single_test($directive);
        }
        
        return $results;
    }
}