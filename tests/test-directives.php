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
printf( "Total Directives: %d\n", $report['summary']['total'] );
printf( "Tested: %d\n", $report['summary']['tested'] );
printf( "Ready: %d\n", $report['summary']['ready'] );
printf( "Development: %d\n", $report['summary']['development'] );
printf( "With Issues: %d\n\n", $report['summary']['issues'] );

// Display detailed results
echo "DETAILED RESULTS:\n";
echo "-----------------\n\n";

foreach ( $results as $result ) {
	printf( "Directive: %s - %s\n", $result['code'], $result['name'] );
	printf( "Current Status: %s\n", $result['current_status'] );
	printf( "Recommended Status: %s\n", $result['recommended_status'] );

	echo "Tests:\n";
	foreach ( $result['tests'] as $test => $passed ) {
		$status = $passed ? '✓' : '✗';
		printf( "  %s %s\n", $status, ucwords( str_replace( '_', ' ', $test ) ) );
	}

	if ( ! empty( $result['issues'] ) ) {
		echo "Issues:\n";
		foreach ( $result['issues'] as $issue ) {
			printf( "  • %s\n", $issue );
		}
	}

	echo "\n";
}

// Show directives ready for status upgrade
echo "READY FOR STATUS UPGRADE:\n";
echo "-------------------------\n";

foreach ( $results as $result ) {
	if ( $result['current_status'] !== $result['recommended_status'] ) {
		printf( "%s - %s: %s → %s\n", $result['code'], $result['name'], $result['current_status'], $result['recommended_status'] );
	}
}

echo "\nTesting complete!\n";
?>
