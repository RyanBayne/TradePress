<?php
/**
 * Standalone sanity checks for the canonical synthetic fixture.
 *
 * Run:
 * php tests/run-fixture-sanity.php
 *
 * @package TradePress
 */

declare(strict_types=1);

$fixture_file = __DIR__ . '/fixtures/canonical-24h-v1.json';
$started_at   = microtime(true);

/**
 * Create a test result entry.
 *
 * @param string $name Test name.
 * @param string $status Test status.
 * @param array  $details Diagnostic details.
 * @return array
 */
function tradepress_fixture_sanity_result(string $name, string $status, array $details = array()): array {
	return array(
		'name'    => $name,
		'status'  => $status,
		'details' => $details,
	);
}

/**
 * Add a failure result and mark overall run as failed.
 *
 * @param array  $tests Test result collection.
 * @param bool   $passed Overall pass flag.
 * @param string $name Test name.
 * @param array  $details Diagnostic details.
 * @return void
 */
function tradepress_fixture_sanity_fail(array &$tests, bool &$passed, string $name, array $details = array()): void {
	$passed  = false;
	$tests[] = tradepress_fixture_sanity_result($name, 'failed', $details);
}

/**
 * Check whether a value is a finite number.
 *
 * @param mixed $value Value to check.
 * @return bool
 */
function tradepress_fixture_sanity_is_finite_number($value): bool {
	return is_int($value) || is_float($value);
}

$tests           = array();
$passed          = true;
$symbols_checked = 0;
$candles_checked = 0;
$rsi_results     = array();

if (! file_exists($fixture_file)) {
	tradepress_fixture_sanity_fail(
		$tests,
		$passed,
		'fixture_file_exists',
		array( 'fixture_file' => $fixture_file )
	);
} else {
	$tests[] = tradepress_fixture_sanity_result('fixture_file_exists', 'passed');
}

$fixture = null;
if ($passed) {
	$raw_fixture = file_get_contents($fixture_file);
	$fixture     = json_decode((string) $raw_fixture, true);

	if (! is_array($fixture) || JSON_ERROR_NONE !== json_last_error()) {
		tradepress_fixture_sanity_fail(
			$tests,
			$passed,
			'fixture_json_decodes',
			array( 'json_error' => json_last_error_msg() )
		);
	} else {
		$tests[] = tradepress_fixture_sanity_result('fixture_json_decodes', 'passed');
	}
}

if ($passed && is_array($fixture)) {
	$required_top_level = array(
		'dataset_id',
		'dataset_version',
		'synthetic',
		'contains_live_or_downloaded_market_data',
		'timezone',
		'granularity',
		'symbols',
		'candles',
	);
	$missing_required  = array();

	foreach ($required_top_level as $field) {
		if (! array_key_exists($field, $fixture)) {
			$missing_required[] = $field;
		}
	}

	if (! empty($missing_required)) {
		tradepress_fixture_sanity_fail(
			$tests,
			$passed,
			'fixture_top_level_schema',
			array( 'missing_fields' => $missing_required )
		);
	} elseif (true !== $fixture['synthetic'] || false !== $fixture['contains_live_or_downloaded_market_data']) {
		tradepress_fixture_sanity_fail(
			$tests,
			$passed,
			'fixture_packaging_contract',
			array(
				'synthetic'                                => $fixture['synthetic'],
				'contains_live_or_downloaded_market_data' => $fixture['contains_live_or_downloaded_market_data'],
			)
		);
	} else {
		$tests[] = tradepress_fixture_sanity_result('fixture_top_level_schema', 'passed');
		$tests[] = tradepress_fixture_sanity_result('fixture_packaging_contract', 'passed');
	}
}

if ($passed && is_array($fixture)) {
	$symbols = $fixture['symbols'];
	$candles = $fixture['candles'];

	if (! is_array($symbols) || ! is_array($candles)) {
		tradepress_fixture_sanity_fail($tests, $passed, 'fixture_symbol_collections', array());
	} else {
		$symbol_keys = array_keys($candles);
		sort($symbols);
		sort($symbol_keys);

		if ($symbols !== $symbol_keys) {
			tradepress_fixture_sanity_fail(
				$tests,
				$passed,
				'fixture_symbols_match_candle_keys',
				array(
					'symbols'     => $symbols,
					'candle_keys' => $symbol_keys,
				)
			);
		} else {
			$tests[] = tradepress_fixture_sanity_result('fixture_symbols_match_candle_keys', 'passed');
		}
	}
}

if ($passed && is_array($fixture)) {
	foreach ($fixture['candles'] as $symbol => $symbol_candles) {
		++$symbols_checked;

		if (! is_array($symbol_candles) || 24 !== count($symbol_candles)) {
			tradepress_fixture_sanity_fail(
				$tests,
				$passed,
				'symbol_has_24_candles',
				array(
					'symbol' => $symbol,
					'count'  => is_array($symbol_candles) ? count($symbol_candles) : null,
				)
			);
			continue;
		}

		$previous_timestamp = null;
		$closes             = array();

		foreach ($symbol_candles as $index => $candle) {
			++$candles_checked;

			$required_candle_fields = array( 'timestamp', 'open', 'high', 'low', 'close', 'volume' );
			foreach ($required_candle_fields as $field) {
				if (! is_array($candle) || ! array_key_exists($field, $candle)) {
					tradepress_fixture_sanity_fail(
						$tests,
						$passed,
						'candle_schema',
						array(
							'symbol' => $symbol,
							'index'  => $index,
							'field'  => $field,
						)
					);
					continue 2;
				}
			}

			$timestamp = strtotime((string) $candle['timestamp']);
			if (false === $timestamp) {
				tradepress_fixture_sanity_fail(
					$tests,
					$passed,
					'candle_timestamp_parse',
					array(
						'symbol'    => $symbol,
						'index'     => $index,
						'timestamp' => $candle['timestamp'],
					)
				);
				continue;
			}

			if (null !== $previous_timestamp && $timestamp <= $previous_timestamp) {
				tradepress_fixture_sanity_fail(
					$tests,
					$passed,
					'candle_timestamps_ordered',
					array(
						'symbol'             => $symbol,
						'index'              => $index,
						'previous_timestamp' => gmdate('c', $previous_timestamp),
						'timestamp'          => $candle['timestamp'],
					)
				);
			}
			$previous_timestamp = $timestamp;

			foreach (array( 'open', 'high', 'low', 'close', 'volume' ) as $field) {
				if (! tradepress_fixture_sanity_is_finite_number($candle[$field])) {
					tradepress_fixture_sanity_fail(
						$tests,
						$passed,
						'candle_numeric_fields',
						array(
							'symbol' => $symbol,
							'index'  => $index,
							'field'  => $field,
							'value'  => $candle[$field],
						)
					);
				}
			}

			if ($candle['high'] < max($candle['open'], $candle['close']) || $candle['low'] > min($candle['open'], $candle['close'])) {
				tradepress_fixture_sanity_fail(
					$tests,
					$passed,
					'candle_ohlc_invariants',
					array(
						'symbol' => $symbol,
						'index'  => $index,
						'candle' => $candle,
					)
				);
			}

			if ($candle['volume'] < 0) {
				tradepress_fixture_sanity_fail(
					$tests,
					$passed,
					'candle_volume_non_negative',
					array(
						'symbol' => $symbol,
						'index'  => $index,
						'volume' => $candle['volume'],
					)
				);
			}

			$closes[] = (float) $candle['close'];
		}

		if (count($closes) >= 15) {
			require_once __DIR__ . '/../includes/scoring-system/class-tradepress-technical-indicators.php';

			$indicators = new TradePress_Technical_Indicators();
			$rsi        = $indicators->calculate_rsi($closes, 14);

			if (! tradepress_fixture_sanity_is_finite_number($rsi) || $rsi < 0 || $rsi > 100) {
				tradepress_fixture_sanity_fail(
					$tests,
					$passed,
					'indicator_rsi_within_range',
					array(
						'symbol' => $symbol,
						'rsi'    => $rsi,
					)
				);
			} else {
				$rsi_results[$symbol] = round((float) $rsi, 4);
			}
		}
	}

	if ($passed) {
		$tests[] = tradepress_fixture_sanity_result(
			'symbol_has_24_candles',
			'passed',
			array( 'symbols_checked' => $symbols_checked )
		);
		$tests[] = tradepress_fixture_sanity_result(
			'candle_schema_and_invariants',
			'passed',
			array( 'candles_checked' => $candles_checked )
		);
		$tests[] = tradepress_fixture_sanity_result(
			'indicator_rsi_within_range',
			'passed',
			array( 'rsi_by_symbol' => $rsi_results )
		);
	}
}

$output = array(
	'status'            => $passed ? 'passed' : 'failed',
	'dataset_id'        => is_array($fixture) && isset($fixture['dataset_id']) ? $fixture['dataset_id'] : null,
	'dataset_version'   => is_array($fixture) && isset($fixture['dataset_version']) ? $fixture['dataset_version'] : null,
	'synthetic'         => is_array($fixture) && isset($fixture['synthetic']) ? $fixture['synthetic'] : null,
	'granularity'       => is_array($fixture) && isset($fixture['granularity']) ? $fixture['granularity'] : null,
	'symbols_checked'   => $symbols_checked,
	'candles_checked'   => $candles_checked,
	'execution_time_ms' => round((microtime(true) - $started_at) * 1000, 2),
	'tests'             => $tests,
);

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($passed ? 0 : 1);
