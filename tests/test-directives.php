<?php
/**
 * TradePress Directive Testing Script
 * 
 * Run this file to test all directives and update their status
 */

// WordPress environment
require_once '../../../wp-config.php';

// Include required files
require_once 'includes/scoring-system/directives-register.php';
require_once 'includes/scoring-system/scoring-directive-base.php';
require_once 'includes/scoring-system/directive-status-tester.php';

echo "TradePress Directive Status Testing\n";
echo "===================================\n\n";

// Test all directives
$results = TradePress_Directive_Status_Tester::test_all_directives();
$report = TradePress_Directive_Status_Tester::generate_status_report($results);

// Display summary
echo "SUMMARY:\n";
echo "--------\n";
echo "Total Directives: " . $report['summary']['total'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo "Tested: " . $report['summary']['tested'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo "Ready: " . $report['summary']['ready'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo "Development: " . $report['summary']['development'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo "With Issues: " . $report['summary']['issues'] . "\n\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// Display detailed results
echo "DETAILED RESULTS:\n";
echo "-----------------\n\n";

foreach ($results as $result) {
    echo "Directive: {$result['code']} - {$result['name']}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo "Current Status: {$result['current_status']}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo "Recommended Status: {$result['recommended_status']}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    
    echo "Tests:\n";
    foreach ($result['tests'] as $test => $passed) {
        $status = $passed ? "✓" : "✗";
        echo "  {$status} " . ucwords(str_replace('_', ' ', $test)) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    
    if (!empty($result['issues'])) {
        echo "Issues:\n";
        foreach ($result['issues'] as $issue) {
            echo "  • {$issue}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
    
    echo "\n";
}

// Show directives ready for status upgrade
echo "READY FOR STATUS UPGRADE:\n";
echo "-------------------------\n";

foreach ($results as $result) {
    if ($result['current_status'] !== $result['recommended_status']) {
        echo "{$result['code']} - {$result['name']}: {$result['current_status']} → {$result['recommended_status']}\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

echo "\nTesting complete!\n";
?>