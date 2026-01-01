<?php
/**
 * Test runner class for TradePress testing framework
 *
 * @package TradePress/Testing
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TradePress_Test_Runner')):

class TradePress_Test_Runner {
    /**
     * Current test run ID
     */
    private $run_id = null;
    
    /**
     * Test results for current run
     */
    private $results = [];
    
    /**
     * Run a specific test
     */
    public function run_test($test_id) {
        global $wpdb;
        
        // Get test details
        $test = TradePress_Test_Registry::get_test($test_id);
        if (!$test) {
            return new WP_Error('invalid_test', 'Test not found');
        }
        
        // Create test run record
        $run_id = $this->start_test_run($test_id);
        if (is_wp_error($run_id)) {
            return $run_id;
        }
        
        $this->run_id = $run_id;
        
        try {
            if ($test->test_type === 'file') {
                $result = $this->run_file_based_test($test);
            } else {
                $result = $this->run_ui_based_test($test);
            }
            
            // Record results
            $this->record_test_results($result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->record_test_error($e->getMessage());
            return new WP_Error('test_error', $e->getMessage());
        }
    }
    
    /**
     * Run multiple tests
     */
    public function run_tests($args = []) {
        $tests = TradePress_Test_Registry::get_tests($args);
        $results = [];
        
        foreach ($tests as $test) {
            $results[$test->test_id] = $this->run_test($test->test_id);
        }
        
        return $results;
    }
    
    /**
     * Start a new test run
     */
    private function start_test_run($test_id) {
        global $wpdb;
        
        $data = [
            'test_id' => $test_id,
            'start_time' => current_time('mysql'),
            'status' => 'running',
            'run_by' => get_current_user_id()
        ];
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'tradepress_test_runs',
            $data,
            ['%d', '%s', '%s', '%d']
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create test run');
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Run a file-based test
     */
    private function run_file_based_test($test) {
        // Load test file if not already loaded
        $file_path = TRADEPRESS_PLUGIN_DIR_PATH . $test->file_path;
        if (!class_exists($test->class_name)) {
            if (!file_exists($file_path)) {
                throw new Exception('Test file not found: ' . $test->file_path);
            }
            require_once $file_path;
        }
        
        // Create test instance
        if (!class_exists($test->class_name)) {
            throw new Exception('Test class not found: ' . $test->class_name);
        }
        
        $test_instance = new $test->class_name();
        
        if (!$test_instance instanceof TradePress_Test_Case) {
            throw new Exception('Test class must extend TradePress_Test_Case');
        }
        
        // Set current run context
        $test_instance->set_run_context($this->run_id, $test);
        
        // Run test method or all test methods
        if ($test->method_name) {
            if (!method_exists($test_instance, $test->method_name)) {
                throw new Exception('Test method not found: ' . $test->method_name);
            }
            $result = $test_instance->{$test->method_name}();
        } else {
            $result = $test_instance->run_all_tests();
        }
        
        return $result;
    }
    
    /**
     * Run a UI-based test
     */
    private function run_ui_based_test($test) {
        // Decode test data and expected result
        $test_data = json_decode($test->test_data, true);
        $expected = json_decode($test->expected_result, true);
        
        if (!$test_data || !$expected) {
            throw new Exception('Invalid test data or expected result');
        }
        
        // Execute test steps
        $result = $this->execute_ui_steps($test_data);
        
        // Compare with expected result
        $passed = $this->compare_results($result, $expected);
        
        return [
            'passed' => $passed,
            'actual' => $result,
            'expected' => $expected
        ];
    }
    
    /**
     * Execute UI test steps
     */
    private function execute_ui_steps($steps) {
        // This will be expanded with Selenium/headless browser integration
        return new WP_Error('not_implemented', 'UI testing not yet implemented');
    }
    
    /**
     * Compare test results
     */
    private function compare_results($actual, $expected) {
        if (is_array($actual) && is_array($expected)) {
            return $this->compare_arrays($actual, $expected);
        }
        return $actual === $expected;
    }
    
    /**
     * Deep compare arrays
     */
    private function compare_arrays($actual, $expected) {
        if (count($actual) !== count($expected)) {
            return false;
        }
        
        foreach ($expected as $key => $value) {
            if (!array_key_exists($key, $actual)) {
                return false;
            }
            
            if (is_array($value)) {
                if (!$this->compare_arrays($actual[$key], $value)) {
                    return false;
                }
            } elseif ($actual[$key] !== $value) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Record test results
     */
    private function record_test_results($result) {
        global $wpdb;
        
        $data = [
            'end_time' => current_time('mysql'),
            'status' => is_wp_error($result) ? 'error' : (
                (is_array($result) && isset($result['passed']) ? 
                    ($result['passed'] ? 'passed' : 'failed') : 
                    ($result ? 'passed' : 'failed')
            )),
            'result_data' => is_array($result) ? wp_json_encode($result) : $result
        ];
        
        return $wpdb->update(
            $wpdb->prefix . 'tradepress_test_runs',
            $data,
            ['run_id' => $this->run_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }
    
    /**
     * Record a test error
     */
    private function record_test_error($error_message) {
        global $wpdb;
        
        $data = [
            'end_time' => current_time('mysql'),
            'status' => 'error',
            'result_data' => wp_json_encode(['error' => $error_message])
        ];
        
        return $wpdb->update(
            $wpdb->prefix . 'tradepress_test_runs',
            $data,
            ['run_id' => $this->run_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }
}

endif;