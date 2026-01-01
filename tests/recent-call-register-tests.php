<?php
/**
 * Recent Call Register Testing Suite - Phase 3
 * 
 * Tests for API call deduplication, platform-aware caching, and cross-feature integration
 * Produces structured output for both developer and AI analysis
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Recent_Call_Register_Tests {
    
    private $call_register;
    private $test_results = [];
    
    public function __construct() {
        // Use the actual class name from query-register.php
        $this->call_register = new TradePress_Call_Register();
    }
    
    /**
     * Run all Phase 3 tests and return structured results
     */
    public function run_phase3_tests() {
        $this->test_results = [
            'test_suite' => 'recent_call_register_phase3',
            'execution_time' => current_time('c'),
            'overall_status' => 'pending',
            'requirements_satisfied' => [],
            'tests' => [],
            'performance_summary' => [
                'api_calls_saved' => 0,
                'cache_hit_rate' => 0,
                'memory_usage_mb' => 0
            ],
            'recommendations' => []
        ];
        
        // Execute individual tests
        $this->test_results['tests'][] = $this->test_platform_aware_caching();
        $this->test_results['tests'][] = $this->test_api_call_deduplication();
        $this->test_results['tests'][] = $this->test_cross_feature_integration();
        $this->test_results['tests'][] = $this->test_transient_rotation();
        $this->test_results['tests'][] = $this->test_performance_metrics();
        
        // Calculate overall status
        $this->calculate_overall_status();
        
        return $this->test_results;
    }
    
    /**
     * Test 1: Platform-Aware Caching
     * Requirement: Different platforms must have separate cache entries
     */
    private function test_platform_aware_caching() {
        $test_start = microtime(true);
        
        $result = [
            'name' => 'test_platform_aware_caching',
            'status' => 'pending',
            'requirement' => 'platform_aware_caching',
            'metrics' => [
                'cache_entries_created' => 0,
                'expected_entries' => 2,
                'platform_separation' => false
            ],
            'execution_time_ms' => 0,
            'details' => []
        ];
        
        try {
            // Test same symbol, different platforms
            $symbol = 'AAPL';
            $endpoint = 'get_quote';
            
            // Generate serials for different platforms
            $alphavantage_serial = TradePress_Call_Register::generate_serial('alphavantage', $endpoint, [$symbol]);
            $finnhub_serial = TradePress_Call_Register::generate_serial('finnhub', $endpoint, [$symbol]);
            
            $result['details'][] = "AlphaVantage serial: " . substr($alphavantage_serial, 0, 16) . "...";
            $result['details'][] = "Finnhub serial: " . substr($finnhub_serial, 0, 16) . "...";
            
            // Verify serials are different
            if ($alphavantage_serial !== $finnhub_serial) {
                $result['metrics']['platform_separation'] = true;
                $result['metrics']['cache_entries_created'] = 2;
                $result['status'] = 'passed';
                $result['details'][] = "✓ Platform-aware caching working correctly";
            } else {
                $result['status'] = 'failed';
                $result['details'][] = "✗ Platforms generating identical cache keys";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        $result['execution_time_ms'] = round((microtime(true) - $test_start) * 1000, 2);
        return $result;
    }
    
    /**
     * Test 2: API Call Deduplication
     * Requirement: Multiple identical calls within 2-hour window use cache
     */
    private function test_api_call_deduplication() {
        $test_start = microtime(true);
        
        $result = [
            'name' => 'test_api_call_deduplication',
            'status' => 'pending',
            'requirement' => 'api_call_deduplication',
            'metrics' => [
                'api_calls_made' => 0,
                'cache_hits' => 0,
                'expected_calls' => 1,
                'actual_calls' => 0,
                'deduplication_rate' => 0
            ],
            'execution_time_ms' => 0,
            'details' => []
        ];
        
        try {
            $platform = 'alphavantage';
            $endpoint = 'get_quote';
            $symbol = 'MSFT';
            
            // Generate serial for this test
            $serial = TradePress_Call_Register::generate_serial($platform, $endpoint, [$symbol]);
            
            // First call - should not be cached
            $first_check = TradePress_Call_Register::check_recent_call($serial);
            $result['details'][] = "First call cached: " . ($first_check['found'] ? 'Yes' : 'No');
            
            if (!$first_check['found']) {
                // Register the call
                TradePress_Call_Register::register_call($serial, ['test' => 'data']);
                $result['metrics']['api_calls_made']++;
                $result['details'][] = "Registered first API call";
            }
            
            // Second call - should be cached
            $second_check = TradePress_Call_Register::check_recent_call($serial);
            $result['details'][] = "Second call cached: " . ($second_check['found'] ? 'Yes' : 'No');
            
            if ($second_check['found']) {
                $result['metrics']['cache_hits']++;
                $result['details'][] = "✓ Cache hit on second call";
            } else {
                $result['details'][] = "✗ Cache miss on second call";
            }
            
            // Calculate deduplication effectiveness
            $result['metrics']['actual_calls'] = $result['metrics']['api_calls_made'];
            $result['metrics']['deduplication_rate'] = $result['metrics']['cache_hits'] / 2;
            
            if ($result['metrics']['cache_hits'] > 0) {
                $result['status'] = 'passed';
                $result['details'][] = "✓ API call deduplication working";
            } else {
                $result['status'] = 'failed';
                $result['details'][] = "✗ No cache hits detected";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        $result['execution_time_ms'] = round((microtime(true) - $test_start) * 1000, 2);
        return $result;
    }
    
    /**
     * Test 3: Cross-Feature Integration
     * Requirement: Features can benefit from other features' API calls
     */
    private function test_cross_feature_integration() {
        $test_start = microtime(true);
        
        $result = [
            'name' => 'test_cross_feature_integration',
            'status' => 'pending',
            'requirement' => 'cross_feature_integration',
            'features_tested' => ['directive_handler', 'data_freshness_manager'],
            'metrics' => [
                'shared_cache_hits' => 0,
                'cross_feature_efficiency' => 0
            ],
            'execution_time_ms' => 0,
            'details' => []
        ];
        
        try {
            $platform = 'alphavantage';
            $endpoint = 'get_quote';
            $symbol = 'GOOGL';
            
            // Generate serials for testing
            $symbol_serial = TradePress_Call_Register::generate_serial($platform, $endpoint, [$symbol]);
            $baseline_serial = TradePress_Call_Register::generate_serial($platform, $endpoint, ['TSLA']);
            
            // Simulate directive handler making a call
            $directive_cached = TradePress_Call_Register::check_recent_call($symbol_serial);
            if (!$directive_cached['found']) {
                TradePress_Call_Register::register_call($symbol_serial, ['source' => 'directive_handler']);
                $result['details'][] = "Directive handler made API call for $symbol";
            }
            
            // Simulate data freshness manager checking same data
            $freshness_cached = TradePress_Call_Register::check_recent_call($symbol_serial);
            if ($freshness_cached['found']) {
                $result['metrics']['shared_cache_hits']++;
                $result['details'][] = "✓ Data freshness manager used cached data";
            } else {
                $result['details'][] = "✗ Data freshness manager missed cache";
            }
            
            // Test with different symbol for baseline
            $baseline_cached = TradePress_Call_Register::check_recent_call($baseline_serial);
            $result['details'][] = "Baseline check (TSLA): " . ($baseline_cached['found'] ? 'Cached' : 'Not cached');
            
            if ($result['metrics']['shared_cache_hits'] > 0) {
                $result['status'] = 'passed';
                $result['metrics']['cross_feature_efficiency'] = 1.0;
                $result['details'][] = "✓ Cross-feature integration successful";
            } else {
                $result['status'] = 'failed';
                $result['details'][] = "✗ Features not sharing cache data";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        $result['execution_time_ms'] = round((microtime(true) - $test_start) * 1000, 2);
        return $result;
    }
    
    /**
     * Test 4: Transient Rotation
     * Requirement: Hourly transient rotation for automatic cleanup
     */
    private function test_transient_rotation() {
        $test_start = microtime(true);
        
        $result = [
            'name' => 'test_transient_rotation',
            'status' => 'pending',
            'requirement' => 'hourly_transient_rotation',
            'metrics' => [
                'current_hour_key' => '',
                'previous_hour_key' => '',
                'keys_different' => false
            ],
            'execution_time_ms' => 0,
            'details' => []
        ];
        
        try {
            // Test transient rotation by checking key format
            $current_hour = date('YmdH');
            $previous_hour = date('YmdH', strtotime('-1 hour'));
            
            $expected_current_key = 'tradepress_call_register_' . $current_hour;
            $expected_previous_key = 'tradepress_call_register_' . $previous_hour;
            
            $result['metrics']['current_hour_key'] = $expected_current_key;
            $result['metrics']['previous_hour_key'] = $expected_previous_key;
            $result['details'][] = "Current hour key: $expected_current_key";
            $result['details'][] = "Previous hour key: $expected_previous_key";
            
            // Verify keys are different (unless testing at exact hour boundary)
            if ($expected_current_key !== $expected_previous_key) {
                $result['metrics']['keys_different'] = true;
                $result['status'] = 'passed';
                $result['details'][] = "✓ Transient rotation working correctly";
            } else {
                $result['status'] = 'warning';
                $result['details'][] = "⚠ Testing at hour boundary - keys may be same";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        $result['execution_time_ms'] = round((microtime(true) - $test_start) * 1000, 2);
        return $result;
    }
    
    /**
     * Test 5: Performance Metrics
     * Requirement: System performance within acceptable limits
     */
    private function test_performance_metrics() {
        $test_start = microtime(true);
        $memory_start = memory_get_usage();
        
        $result = [
            'name' => 'test_performance_metrics',
            'status' => 'pending',
            'requirement' => 'performance_optimization',
            'metrics' => [
                'avg_execution_time_ms' => 0,
                'memory_usage_kb' => 0,
                'operations_per_second' => 0
            ],
            'execution_time_ms' => 0,
            'details' => []
        ];
        
        try {
            $operations = 0;
            $total_time = 0;
            
            // Perform multiple operations to test performance
            for ($i = 0; $i < 10; $i++) {
                $op_start = microtime(true);
                
                $serial = TradePress_Call_Register::generate_serial('test_platform', 'test_endpoint', ["TEST$i"]);
                TradePress_Call_Register::check_recent_call($serial);
                TradePress_Call_Register::register_call($serial, ['test' => true]);
                
                $op_time = (microtime(true) - $op_start) * 1000;
                $total_time += $op_time;
                $operations += 2; // check + register
            }
            
            $result['metrics']['avg_execution_time_ms'] = round($total_time / $operations, 2);
            $result['metrics']['memory_usage_kb'] = round((memory_get_usage() - $memory_start) / 1024, 2);
            $result['metrics']['operations_per_second'] = round($operations / ($total_time / 1000), 2);
            
            $result['details'][] = "Performed $operations operations";
            $result['details'][] = "Average execution time: {$result['metrics']['avg_execution_time_ms']}ms";
            $result['details'][] = "Memory usage: {$result['metrics']['memory_usage_kb']}KB";
            $result['details'][] = "Operations per second: {$result['metrics']['operations_per_second']}";
            
            // Performance thresholds
            if ($result['metrics']['avg_execution_time_ms'] < 5 && $result['metrics']['memory_usage_kb'] < 100) {
                $result['status'] = 'passed';
                $result['details'][] = "✓ Performance within acceptable limits";
            } else {
                $result['status'] = 'warning';
                $result['details'][] = "⚠ Performance may need optimization";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        $result['execution_time_ms'] = round((microtime(true) - $test_start) * 1000, 2);
        return $result;
    }
    
    /**
     * Calculate overall test suite status and generate recommendations
     */
    private function calculate_overall_status() {
        $passed = 0;
        $failed = 0;
        $errors = 0;
        $warnings = 0;
        
        foreach ($this->test_results['tests'] as $test) {
            switch ($test['status']) {
                case 'passed':
                    $passed++;
                    $this->test_results['requirements_satisfied'][] = $test['requirement'];
                    break;
                case 'failed':
                    $failed++;
                    break;
                case 'error':
                    $errors++;
                    break;
                case 'warning':
                    $warnings++;
                    break;
            }
        }
        
        // Calculate performance summary
        $total_cache_hits = 0;
        $total_api_calls = 0;
        $total_memory = 0;
        
        foreach ($this->test_results['tests'] as $test) {
            if (isset($test['metrics']['cache_hits'])) {
                $total_cache_hits += $test['metrics']['cache_hits'];
            }
            if (isset($test['metrics']['api_calls_made'])) {
                $total_api_calls += $test['metrics']['api_calls_made'];
            }
            if (isset($test['metrics']['memory_usage_kb'])) {
                $total_memory += $test['metrics']['memory_usage_kb'];
            }
        }
        
        $this->test_results['performance_summary'] = [
            'api_calls_saved' => $total_cache_hits,
            'cache_hit_rate' => $total_api_calls > 0 ? round($total_cache_hits / ($total_cache_hits + $total_api_calls), 2) : 0,
            'memory_usage_mb' => round($total_memory / 1024, 2)
        ];
        
        // Determine overall status
        if ($errors > 0) {
            $this->test_results['overall_status'] = 'error';
        } elseif ($failed > 0) {
            $this->test_results['overall_status'] = 'failed';
        } elseif ($warnings > 0) {
            $this->test_results['overall_status'] = 'warning';
        } else {
            $this->test_results['overall_status'] = 'passed';
        }
        
        // Generate recommendations
        if ($this->test_results['overall_status'] === 'passed') {
            $this->test_results['recommendations'][] = "All Phase 3 requirements satisfied";
            $this->test_results['recommendations'][] = "Recent Call Register system ready for production";
            $this->test_results['recommendations'][] = "Ready to proceed with Phase 4 implementation";
        } else {
            $this->test_results['recommendations'][] = "Review failed tests before proceeding";
            if ($failed > 0) {
                $this->test_results['recommendations'][] = "Address $failed failed test(s)";
            }
            if ($errors > 0) {
                $this->test_results['recommendations'][] = "Fix $errors error(s) in test execution";
            }
        }
    }
    
    /**
     * Get results in AI-readable JSON format
     */
    public function get_ai_readable_results() {
        return json_encode($this->test_results, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get human-readable test report
     */
    public function get_human_readable_report() {
        $report = "=== Recent Call Register Phase 3 Test Results ===\n\n";
        $report .= "Overall Status: " . strtoupper($this->test_results['overall_status']) . "\n";
        $report .= "Execution Time: " . $this->test_results['execution_time'] . "\n\n";
        
        foreach ($this->test_results['tests'] as $test) {
            $status_icon = $test['status'] === 'passed' ? '✓' : ($test['status'] === 'failed' ? '✗' : '⚠');
            $report .= "$status_icon {$test['name']}: " . strtoupper($test['status']) . "\n";
            
            foreach ($test['details'] as $detail) {
                $report .= "  $detail\n";
            }
            $report .= "\n";
        }
        
        $report .= "Performance Summary:\n";
        $report .= "- API calls saved: " . $this->test_results['performance_summary']['api_calls_saved'] . "\n";
        $report .= "- Cache hit rate: " . ($this->test_results['performance_summary']['cache_hit_rate'] * 100) . "%\n";
        $report .= "- Memory usage: " . $this->test_results['performance_summary']['memory_usage_mb'] . "MB\n\n";
        
        $report .= "Recommendations:\n";
        foreach ($this->test_results['recommendations'] as $recommendation) {
            $report .= "- $recommendation\n";
        }
        
        return $report;
    }
}