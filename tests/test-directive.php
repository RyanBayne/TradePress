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

printf( "Testing Directive: %s\n", $directive );
printf( "%s\n", str_repeat( '-', 40 ) );

// Run analysis first
echo "1. ANALYZING IMPLEMENTATION...\n";
$analysis = TradePress_AI_Directive_Analyzer::analyze_directive( $directive );

printf(
	"   Files: Class=%s Config=%s\n",
	$analysis['files']['class'] ? '✓' : '✗',
	$analysis['files']['config'] ? '✓' : '✗'
);
printf( "   Status: %s\n", strtoupper( $analysis['status'] ) );

if ( ! empty( $analysis['recommendations'] ) ) {
	echo "   Issues:\n";
	foreach ( $analysis['recommendations'] as $rec ) {
		printf( "   • %s\n", $rec );
	}
}

echo "\n2. RUNNING FUNCTIONAL TEST...\n";

// Run functional test
$result = TradePress_AI_Test_Runner::run_single_test( $directive );

echo "\n3. SUMMARY\n";
printf( "%s\n", str_repeat( '-', 40 ) );

if ( $result['success'] ) {
	echo "✅ DIRECTIVE PASSED ALL TESTS\n";
	echo "Ready to mark as TESTED in directive registry\n";
} else {
	echo "❌ DIRECTIVE FAILED TESTING\n";
	echo "Needs fixes before marking as TESTED\n";
}

$next_directives = array( 'ema' => 'cci', 'cci' => 'mfi', 'mfi' => 'moving-averages', 'moving-averages' => 'obv' );
printf( "\nNext directive to test: %s\n", isset( $next_directives[ $directive ] ) ? $next_directives[ $directive ] : 'Check DIRECTIVE-ANALYSIS.md' );
?>
