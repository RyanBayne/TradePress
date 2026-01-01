<?php
/**
 * Base test case class for TradePress testing framework
 *
 * @package TradePress/Testing
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('TradePress_Test_Case')):

class TradePress_Test_Case {
    /**
     * Test metadata
     */
    protected $test_id;
    protected $title;
    protected $description;
    protected $category = 'standard';
    protected $priority_level = 3;
    protected $status = 'active';
    protected $current_test;
    
    /**
     * Current test run data
     */
    protected $run_id;
    protected $start_time;
    protected $end_time;
    protected $memory_start;
    protected $memory_peak;
    
    /**
     * Test results
     */
    protected $assertions = [];
    protected $failures = [];
    protected $errors = [];
    protected $output = [];
    
    /**
     * Initialize a new test case
     */
    public function __construct($test_id = null) {
        $this->test_id = $test_id;
        if ($test_id) {
            $this->load_test_metadata();
        }
    }
    
    /**
     * Load test metadata from database
     */
    protected function load_test_metadata() {
        global $wpdb;
        
        $test = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tradepress_tests WHERE test_id = %d",
            $this->test_id
        ));
        
        if ($test) {
            $this->title = $test->title;
            $this->description = $test->description;
            $this->category = $test->category;
            $this->priority_level = $test->priority_level;
            $this->status = $test->status;
        }
    }
    
    /**
     * Set up test environment before running a test
     */
    protected function setUp() {
        // Initialize timing and memory tracking
        $this->start_time = microtime(true);
        $this->memory_start = memory_get_usage();
        
        // Create new test run record
        $this->create_test_run();
    }
    
    /**
     * Clean up after test execution
     */
    protected function tearDown() {
        $this->end_time = microtime(true);
        $this->memory_peak = memory_get_peak_usage();
        
        // Update test run record
        $this->update_test_run();
    }
    
    /**
     * Create a new test run record
     */
    protected function create_test_run() {
        global $wpdb;
        
        $data = [
            'test_id' => $this->test_id,
            'run_date' => current_time('mysql'),
            'status' => 'running',
            'run_by' => get_current_user_id(),
            'environment' => 'development',
            'version' => TRADEPRESS_VERSION
        ];
        
        $wpdb->insert($wpdb->prefix . 'tradepress_test_runs', $data);
        $this->run_id = $wpdb->insert_id;
    }
    
    /**
     * Update test run record with results
     */
    protected function update_test_run() {
        global $wpdb;
        
        $data = [
            'status' => empty($this->failures) ? 'passed' : 'failed',
            'execution_time' => $this->end_time - $this->start_time,
            'memory_usage' => $this->memory_peak - $this->memory_start,
            'output_data' => wp_json_encode([
                'assertions' => $this->assertions,
                'failures' => $this->failures,
                'errors' => $this->errors,
                'output' => $this->output
            ])
        ];
        
        if (!empty($this->errors)) {
            $data['error_message'] = reset($this->errors);
            $data['stack_trace'] = wp_debug_backtrace_summary();
        }
        
        $wpdb->update(
            $wpdb->prefix . 'tradepress_test_runs',
            $data,
            ['run_id' => $this->run_id]
        );
        
        // Update test statistics
        $this->update_test_statistics();
    }
    
    /**
     * Update overall test statistics
     */
    protected function update_test_statistics() {
        global $wpdb;
        
        // Get all runs for this test
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_runs,
                SUM(CASE WHEN status = 'passed' THEN 1 ELSE 0 END) as passed_runs,
                AVG(execution_time) as avg_time
            FROM {$wpdb->prefix}tradepress_test_runs 
            WHERE test_id = %d
        ", $this->test_id));
        
        if ($stats) {
            $success_rate = ($stats->passed_runs / $stats->total_runs) * 100;
            
            $wpdb->update(
                $wpdb->prefix . 'tradepress_tests',
                [
                    'run_count' => $stats->total_runs,
                    'success_rate' => $success_rate,
                    'avg_execution_time' => $stats->avg_time,
                    'last_run' => current_time('mysql')
                ],
                ['test_id' => $this->test_id]
            );
        }
    }
    
    /**
     * Assertion methods
     */
    protected function assertEquals($expected, $actual, $message = '') {
        $result = $expected === $actual;
        $this->record_assertion($result, $message, [
            'type' => 'equals',
            'expected' => $expected,
            'actual' => $actual
        ]);
    }
    
    protected function assertTrue($condition, $message = '') {
        $result = $condition === true;
        $this->record_assertion($result, $message, [
            'type' => 'true',
            'condition' => $condition
        ]);
    }
    
    protected function assertFalse($condition, $message = '') {
        $result = $condition === false;
        $this->record_assertion($result, $message, [
            'type' => 'false',
            'condition' => $condition
        ]);
    }
    
    protected function assertNotNull($value, $message = '') {
        $result = $value !== null;
        $this->record_assertion($result, $message, [
            'type' => 'not_null',
            'value' => $value
        ]);
    }
    
    /**
     * Record an assertion result
     */
    protected function record_assertion($passed, $message, $data = []) {
        $assertion = array_merge($data, [
            'passed' => $passed,
            'message' => $message,
            'line' => debug_backtrace()[1]['line'],
            'file' => debug_backtrace()[1]['file']
        ]);
        
        $this->assertions[] = $assertion;
        
        if (!$passed) {
            $this->failures[] = $assertion;
        }
    }
    
    /**
     * Record test output
     */
    protected function log($message) {
        $this->output[] = [
            'time' => microtime(true),
            'message' => $message
        ];
    }
    
    /**
     * Record an error
     */
    protected function recordError($message, $exception = null) {
        $error = [
            'message' => $message,
            'time' => microtime(true)
        ];
        
        if ($exception) {
            $error['exception'] = get_class($exception);
            $error['trace'] = $exception->getTraceAsString();
        }
        
        $this->errors[] = $error;
    }

    /**
     * Set run context for test execution
     */
    public function set_run_context($run_id, $test) {
        $this->run_id = $run_id;
        $this->current_test = $test;
        $this->test_id = $test->test_id;
        $this->title = $test->title;
        $this->description = $test->description;
        $this->category = $test->category;
        $this->priority_level = $test->priority_level;
        $this->status = $test->status;
    }

    /**
     * Run all test methods in the class
     */
    public function run_all_tests() {
        $methods = get_class_methods($this);
        $results = [];
        
        foreach ($methods as $method) {
            // Run methods that start with 'test'
            if (strpos($method, 'test') === 0) {
                try {
                    $this->setUp();
                    $result = $this->$method();
                    $this->tearDown();
                    $results[$method] = $result;
                } catch (Exception $e) {
                    $this->recordError("Error in {$method}: " . $e->getMessage(), $e);
                    $results[$method] = false;
                }
            }
        }
        
        return [
            'passed' => empty($this->failures) && empty($this->errors),
            'results' => $results,
            'assertions' => $this->assertions,
            'failures' => $this->failures,
            'errors' => $this->errors,
            'output' => $this->output
        ];
    }
}

endif;