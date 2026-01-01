<?php
/**
 * TradePress Directive Status Testing System
 *
 * Tests all directives and updates their development status
 *
 * @package TradePress/Scoring
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Directive_Status_Tester {
    
    private static $test_results = array();
    
    /**
     * Test all directives and return status summary
     */
    public static function test_all_directives() {
        $directives = tradepress_get_all_system_directives();
        $results = array();
        
        foreach ($directives as $id => $directive) {
            $results[$id] = self::test_directive_status($id, $directive);
        }
        
        return $results;
    }
    
    /**
     * Test individual directive status
     */
    private static function test_directive_status($id, $directive) {
        $result = array(
            'id' => $id,
            'code' => $directive['code'],
            'name' => $directive['name'],
            'current_status' => $directive['development_status'],
            'tests' => array(),
            'recommended_status' => 'development',
            'issues' => array()
        );
        
        // Test 1: Class file exists
        $class_file = TRADEPRESS_PLUGIN_DIR_PATH . "includes/scoring-system/directives/{$id}.php";
        $result['tests']['class_file'] = file_exists($class_file);
        if (!$result['tests']['class_file']) {
            $result['issues'][] = "Missing class file: {$id}.php";
        }
        
        // Test 2: Config form exists (for technical indicators)
        if (isset($directive['technical_indicator'])) {
            $config_file = TRADEPRESS_PLUGIN_DIR_PATH . "admin/page/scoring-directives/directives-partials/{$id}.php";
            $result['tests']['config_form'] = file_exists($config_file);
            if (!$result['tests']['config_form']) {
                $result['issues'][] = "Missing config form: {$id}.php";
            }
        } else {
            $result['tests']['config_form'] = true; // Not required
        }
        
        // Test 3: Class can be instantiated
        if ($result['tests']['class_file']) {
            try {
                require_once $class_file;
                $class_name = 'TradePress_Scoring_Directive_' . str_replace('-', '_', ucwords($id, '-'));
                $result['tests']['class_instantiation'] = class_exists($class_name);
                
                if ($result['tests']['class_instantiation']) {
                    $instance = new $class_name();
                    $result['tests']['has_calculate_method'] = method_exists($instance, 'calculate_score');
                    $result['tests']['has_explanation_method'] = method_exists($instance, 'get_explanation');
                } else {
                    $result['issues'][] = "Class {$class_name} not found";
                }
            } catch (Exception $e) {
                $result['tests']['class_instantiation'] = false;
                $result['issues'][] = "Class instantiation error: " . $e->getMessage();
            }
        }
        
        // Test 4: Basic scoring test with dummy data
        if ($result['tests']['class_file'] && $result['tests']['class_instantiation']) {
            try {
                $dummy_data = self::get_dummy_symbol_data();
                $score = $instance->calculate_score($dummy_data);
                $result['tests']['scoring_works'] = is_numeric($score) || is_array($score);
                
                if (!$result['tests']['scoring_works']) {
                    $result['issues'][] = "Scoring method returns invalid data type";
                }
            } catch (Exception $e) {
                $result['tests']['scoring_works'] = false;
                $result['issues'][] = "Scoring error: " . $e->getMessage();
            }
        }
        
        // Determine recommended status
        $result['recommended_status'] = self::determine_status($result);
        
        return $result;
    }
    
    /**
     * Get dummy symbol data for testing
     */
    private static function get_dummy_symbol_data() {
        return array(
            'symbol' => 'NVDA',
            'price' => 450.00,
            'volume' => 50000000,
            'avg_volume' => 35000000,
            'technical' => array(
                'rsi' => 35.5,
                'adx' => array('adx' => 28.5, 'plus_di' => 25.2, 'minus_di' => 18.7),
                'macd' => array('macd' => 2.5, 'signal' => 1.8, 'histogram' => 0.7),
                'bollinger' => array('upper' => 460, 'middle' => 450, 'lower' => 440),
                'ema_20' => 445.0,
                'sma_50' => 440.0,
                'obv' => 1500000000
            ),
            'fundamentals' => array(
                'market_cap' => 1100000000000,
                'pe_ratio' => 65.5
            ),
            'earnings' => array(
                array('date' => date('Y-m-d', strtotime('+5 days')), 'estimate' => 5.25)
            )
        );
    }
    
    /**
     * Determine recommended status based on test results
     */
    private static function determine_status($result) {
        $tests = $result['tests'];
        
        // Count passed tests
        $passed = array_sum($tests);
        $total = count($tests);
        
        if ($passed === $total && empty($result['issues'])) {
            return 'tested';
        } elseif ($passed >= ($total * 0.75)) {
            return 'ready';
        } else {
            return 'development';
        }
    }
    
    /**
     * Update directive status in the registration file
     */
    public static function update_directive_status($directive_id, $new_status) {
        // This would update the directives-register.php file
        // For now, just return success
        return true;
    }
    
    /**
     * Generate status report
     */
    public static function generate_status_report($results) {
        $report = array(
            'summary' => array(
                'total' => count($results),
                'tested' => 0,
                'ready' => 0,
                'development' => 0,
                'issues' => 0
            ),
            'by_status' => array(
                'tested' => array(),
                'ready' => array(),
                'development' => array()
            ),
            'issues' => array()
        );
        
        foreach ($results as $result) {
            $status = $result['recommended_status'];
            $report['summary'][$status]++;
            $report['by_status'][$status][] = $result;
            
            if (!empty($result['issues'])) {
                $report['summary']['issues']++;
                $report['issues'][$result['id']] = $result['issues'];
            }
        }
        
        return $report;
    }
}