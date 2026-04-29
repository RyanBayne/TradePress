<?php
/**
 * Aggregate runner for TradePress standalone sanity tests.
 *
 * Run:
 * php tests/run-sanity-suite.php
 *
 * @package TradePress
 */

declare(strict_types=1);

$started_at = microtime(true);
$php_binary = PHP_BINARY;
$tests_dir  = __DIR__;

$suites = array(
	'fixture'   => array(
		'command' => array( $php_binary, $tests_dir . '/run-fixture-sanity.php' ),
	),
	'indicator' => array(
		'command' => array( $php_binary, $tests_dir . '/run-indicator-sanity.php' ),
	),
	'directive' => array(
		'command' => array( $php_binary, $tests_dir . '/run-directive-sanity.php', '--summary' ),
	),
);

/**
 * Escape a command for proc_open.
 *
 * @param array $parts Command parts.
 * @return string
 */
function tradepress_sanity_suite_command(array $parts): string {
	return implode(
		' ',
		array_map(
			static function ($part): string {
				return escapeshellarg((string) $part);
			},
			$parts
		)
	);
}

/**
 * Run a child PHP sanity suite and decode its JSON output.
 *
 * @param array $command_parts Command parts.
 * @return array
 */
function tradepress_sanity_suite_run(array $command_parts): array {
	$descriptors = array(
		0 => array( 'pipe', 'r' ),
		1 => array( 'pipe', 'w' ),
		2 => array( 'pipe', 'w' ),
	);

	$process = proc_open(tradepress_sanity_suite_command($command_parts), $descriptors, $pipes);

	if (! is_resource($process)) {
		return array(
			'status' => 'failed',
			'error'  => 'Unable to start process.',
		);
	}

	fclose($pipes[0]);
	$stdout = stream_get_contents($pipes[1]);
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);

	$exit_code = proc_close($process);
	$decoded   = json_decode((string) $stdout, true);

	if (! is_array($decoded)) {
		return array(
			'status'    => 'failed',
			'exit_code' => $exit_code,
			'error'     => 'Suite did not return valid JSON.',
			'stdout'    => trim((string) $stdout),
			'stderr'    => trim((string) $stderr),
		);
	}

	return array(
		'status'    => isset($decoded['status']) ? (string) $decoded['status'] : 'unknown',
		'exit_code' => $exit_code,
		'summary'   => $decoded['summary'] ?? array(
			'tests_run'       => $decoded['tests_run'] ?? null,
			'warnings_count'  => $decoded['warnings_count'] ?? null,
			'symbols_checked' => $decoded['symbols_checked'] ?? null,
			'candles_checked' => $decoded['candles_checked'] ?? null,
		),
		'stderr'    => trim((string) $stderr),
	);
}

$suite_results = array();
$passed_count  = 0;
$failed_count  = 0;

foreach ($suites as $suite_name => $suite) {
	$result = tradepress_sanity_suite_run($suite['command']);

	$is_passed = 'passed' === $result['status'] && 0 === (int) $result['exit_code'];
	if ($is_passed) {
		++$passed_count;
	} else {
		++$failed_count;
	}

	$suite_results[$suite_name] = $result;
}

$overall_status = 0 === $failed_count ? 'passed' : 'failed';

$output = array(
	'status'            => $overall_status,
	'test_suite'        => 'tradepress_sanity_suite',
	'execution_time_ms' => round((microtime(true) - $started_at) * 1000, 2),
	'suites_run'        => count($suites),
	'suites_passed'     => $passed_count,
	'suites_failed'     => $failed_count,
	'suites'            => $suite_results,
);

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit('passed' === $overall_status ? 0 : 1);
