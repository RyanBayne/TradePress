<?php
/**
 * TradePress Directive Testing Interface
 * 
 * Usage: php test-directive.php ema
 */

// WordPress environment
require_once '../../../wp-config.php';

// Include AI testing system
require_once 'includes/ai-integration/testing/test-runner.php';
require_once 'includes/ai-integration/analysis/directive-analyzer.php';
require_once 'includes/scoring-system/scoring-directive-base.php';

// Get directive from command line or default to 'ema'
$directive = isset($argv[1]) ? $argv[1] : 'ema';

echo "TradePress AI-Guided Directive Testing\n";
echo "=====================================\n\n";

echo "Testing Directive: {$directive}\n";
echo str_repeat("-", 40) . "\n";

// Run analysis first
echo "1. ANALYZING IMPLEMENTATION...\n";
$analysis = TradePress_AI_Directive_Analyzer::analyze_directive($directive);

echo "   Files: Class=" . ($analysis['files']['class'] ? '✓' : '✗') . 
     " Config=" . ($analysis['files']['config'] ? '✓' : '✗') . "\n";
echo "   Status: " . strtoupper($analysis['status']) . "\n";

if (!empty($analysis['recommendations'])) {
    echo "   Issues:\n";
    foreach ($analysis['recommendations'] as $rec) {
        echo "   • {$rec}\n";
    }
}

echo "\n2. RUNNING FUNCTIONAL TEST...\n";

// Run functional test
$result = TradePress_AI_Test_Runner::run_single_test($directive);

echo "\n3. SUMMARY\n";
echo str_repeat("-", 40) . "\n";

if ($result['success']) {
    echo "✅ DIRECTIVE PASSED ALL TESTS\n";
    echo "Ready to mark as TESTED in directive registry\n";
} else {
    echo "❌ DIRECTIVE FAILED TESTING\n";
    echo "Needs fixes before marking as TESTED\n";
}

echo "\nNext directive to test: ";
$next_directives = array('ema' => 'cci', 'cci' => 'mfi', 'mfi' => 'moving-averages', 'moving-averages' => 'obv');
echo isset($next_directives[$directive]) ? $next_directives[$directive] : 'Check DIRECTIVE-ANALYSIS.md';
echo "\n";
?>