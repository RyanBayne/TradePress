<?php
/**
 * Standalone sanity checks for TradePress technical indicator calculations.
 *
 * Run:
 * php tests/run-indicator-sanity.php
 *
 * @package TradePress
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/scoring-system/class-tradepress-technical-indicators.php';

$started_at = microtime(true);
$indicator  = new TradePress_Technical_Indicators();
$tests      = array();
$warnings   = array();
$passed     = true;

/**
 * Record an indicator test result.
 *
 * @param array  $tests Test collection.
 * @param string $name Test name.
 * @param string $status Test status.
 * @param array  $details Result details.
 * @return void
 */
function tradepress_indicator_sanity_record(array &$tests, string $name, string $status, array $details = array()): void {
	$tests[] = array(
		'name'    => $name,
		'status'  => $status,
		'details' => $details,
	);
}

/**
 * Record a failing indicator test.
 *
 * @param array  $tests Test collection.
 * @param bool   $passed Overall pass flag.
 * @param string $name Test name.
 * @param array  $details Result details.
 * @return void
 */
function tradepress_indicator_sanity_fail(array &$tests, bool &$passed, string $name, array $details = array()): void {
	$passed = false;
	tradepress_indicator_sanity_record($tests, $name, 'failed', $details);
}

/**
 * Compare two floating-point values using tolerance.
 *
 * @param mixed $actual Actual value.
 * @param float $expected Expected value.
 * @param float $tolerance Allowed absolute difference.
 * @return bool
 */
function tradepress_indicator_sanity_nearly_equal($actual, float $expected, float $tolerance = 0.0001): bool {
	return (is_int($actual) || is_float($actual)) && abs((float) $actual - $expected) <= $tolerance;
}

/**
 * Check whether a value is a finite number.
 *
 * @param mixed $value Value to check.
 * @return bool
 */
function tradepress_indicator_sanity_is_finite_number($value): bool {
	return is_int($value) || is_float($value);
}

// EMA: prices 1..10, period 3. First SMA is 2, multiplier is 0.5, final EMA is 9.
$ema_prices = range(1, 10);
$ema        = $indicator->calculate_ema($ema_prices, 3);
if (tradepress_indicator_sanity_nearly_equal($ema, 9.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'ema_known_sequence',
		'passed',
		array(
			'expected' => 9.0,
			'actual'   => $ema,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'ema_known_sequence',
		array(
			'expected' => 9.0,
			'actual'   => $ema,
		)
	);
}

// RSI: classic 15-point sample where the first 14-period RSI is approximately 70.4641.
$rsi_sample = array(
	44.34,
	44.09,
	44.15,
	43.61,
	44.33,
	44.83,
	45.10,
	45.42,
	45.84,
	46.08,
	45.89,
	46.03,
	45.61,
	46.28,
	46.28,
);
$rsi = $indicator->calculate_rsi($rsi_sample, 14);
if (tradepress_indicator_sanity_nearly_equal($rsi, 70.4641, 0.001)) {
	tradepress_indicator_sanity_record(
		$tests,
		'rsi_known_wilder_sample',
		'passed',
		array(
			'expected'  => 70.4641,
			'actual'    => $rsi,
			'tolerance' => 0.001,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'rsi_known_wilder_sample',
		array(
			'expected'  => 70.4641,
			'actual'    => $rsi,
			'tolerance' => 0.001,
		)
	);
}

// RSI edge case: all gains conventionally resolves to 100.
$rsi_all_gains = $indicator->calculate_rsi(range(1, 15), 14);
if (tradepress_indicator_sanity_nearly_equal($rsi_all_gains, 100.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'rsi_all_gains_edge_case',
		'passed',
		array(
			'expected' => 100.0,
			'actual'   => $rsi_all_gains,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'rsi_all_gains_edge_case',
		array(
			'expected' => 100.0,
			'actual'   => $rsi_all_gains,
		)
	);
}

// RSI edge case: flat prices have neither gains nor losses, so neutral 50 is expected.
$rsi_flat = $indicator->calculate_rsi(array_fill(0, 15, 10), 14);
if (tradepress_indicator_sanity_nearly_equal($rsi_flat, 50.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'rsi_flat_prices_edge_case',
		'passed',
		array(
			'expected' => 50.0,
			'actual'   => $rsi_flat,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'rsi_flat_prices_edge_case',
		array(
			'expected' => 50.0,
			'actual'   => $rsi_flat,
		)
	);
}

// Bollinger Bands: prices 1..20, period 20, population standard deviation.
$bollinger = $indicator->calculate_bollinger_bands(range(1, 20), 20, 2);
if (
	is_array($bollinger)
	&& tradepress_indicator_sanity_nearly_equal($bollinger['middle'], 10.5)
	&& tradepress_indicator_sanity_nearly_equal($bollinger['upper'], 22.032562594671, 0.0001)
	&& tradepress_indicator_sanity_nearly_equal($bollinger['lower'], -1.0325625946708, 0.0001)
) {
	tradepress_indicator_sanity_record(
		$tests,
		'bollinger_known_sequence',
		'passed',
		array(
			'expected' => array(
				'middle' => 10.5,
				'upper'  => 22.032562594671,
				'lower'  => -1.0325625946708,
			),
			'actual'   => $bollinger,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'bollinger_known_sequence',
		array(
			'actual' => $bollinger,
		)
	);
}

// OBV: unchanged close should leave OBV unchanged.
$obv = $indicator->calculate_obv(array(10, 11, 10, 10, 12), array(100, 200, 150, 50, 300));
if (tradepress_indicator_sanity_nearly_equal($obv, 450.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'obv_known_sequence',
		'passed',
		array(
			'expected' => 450.0,
			'actual'   => $obv,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'obv_known_sequence',
		array(
			'expected' => 450.0,
			'actual'   => $obv,
		)
	);
}

// VWAP: weighted typical price across three candles.
$vwap = $indicator->calculate_vwap(
	array(11, 12, 13),
	array(9, 10, 11),
	array(10, 11, 12),
	array(100, 200, 300)
);
if (tradepress_indicator_sanity_nearly_equal($vwap, 11.333333333333, 0.0001)) {
	tradepress_indicator_sanity_record(
		$tests,
		'vwap_known_sequence',
		'passed',
		array(
			'expected' => 11.333333333333,
			'actual'   => $vwap,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'vwap_known_sequence',
		array(
			'expected' => 11.333333333333,
			'actual'   => $vwap,
		)
	);
}

// MFI all-positive money flow should resolve to 100.
$mfi = $indicator->calculate_mfi(
	array(11, 12, 13, 14, 15),
	array(9, 10, 11, 12, 13),
	array(10, 11, 12, 13, 14),
	array(100, 100, 100, 100, 100),
	4
);
if (tradepress_indicator_sanity_nearly_equal($mfi, 100.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'mfi_all_positive_flow',
		'passed',
		array(
			'expected' => 100.0,
			'actual'   => $mfi,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'mfi_all_positive_flow',
		array(
			'expected' => 100.0,
			'actual'   => $mfi,
		)
	);
}

// MFI flat typical prices have neither positive nor negative flow, so neutral 50 is expected.
$mfi_flat = $indicator->calculate_mfi(
	array_fill(0, 5, 10),
	array_fill(0, 5, 10),
	array_fill(0, 5, 10),
	array_fill(0, 5, 100),
	4
);
if (tradepress_indicator_sanity_nearly_equal($mfi_flat, 50.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'mfi_flat_prices_edge_case',
		'passed',
		array(
			'expected' => 50.0,
			'actual'   => $mfi_flat,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'mfi_flat_prices_edge_case',
		array(
			'expected' => 50.0,
			'actual'   => $mfi_flat,
		)
	);
}

// CCI flat prices have zero mean deviation, so neutral 0 is expected.
$cci_flat = $indicator->calculate_cci(
	array_fill(0, 20, 10),
	array_fill(0, 20, 10),
	array_fill(0, 20, 10)
);
if (tradepress_indicator_sanity_nearly_equal($cci_flat, 0.0)) {
	tradepress_indicator_sanity_record(
		$tests,
		'cci_flat_prices_edge_case',
		'passed',
		array(
			'expected' => 0.0,
			'actual'   => $cci_flat,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'cci_flat_prices_edge_case',
		array(
			'expected' => 0.0,
			'actual'   => $cci_flat,
		)
	);
}

// Stochastic flat prices have no high/low range, so neutral K and D values are expected.
$stochastic_flat = $indicator->calculate_stochastic(
	array_fill(0, 14, 10),
	array_fill(0, 14, 10),
	array_fill(0, 14, 10)
);
if (
	is_array($stochastic_flat)
	&& tradepress_indicator_sanity_nearly_equal($stochastic_flat['k'], 50.0)
	&& tradepress_indicator_sanity_nearly_equal($stochastic_flat['d'], 50.0)
) {
	tradepress_indicator_sanity_record(
		$tests,
		'stochastic_flat_prices_edge_case',
		'passed',
		array(
			'expected' => array(
				'k' => 50.0,
				'd' => 50.0,
			),
			'actual'   => $stochastic_flat,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'stochastic_flat_prices_edge_case',
		array(
			'expected' => array(
				'k' => 50.0,
				'd' => 50.0,
			),
			'actual'   => $stochastic_flat,
		)
	);
}

// ADX flat prices have no directional movement, so zero trend strength is expected.
$adx_flat = $indicator->calculate_adx(
	array_fill(0, 28, 10),
	array_fill(0, 28, 10),
	array_fill(0, 28, 10)
);
if (
	is_array($adx_flat)
	&& tradepress_indicator_sanity_nearly_equal($adx_flat['adx'], 0.0)
	&& tradepress_indicator_sanity_nearly_equal($adx_flat['plus_di'], 0.0)
	&& tradepress_indicator_sanity_nearly_equal($adx_flat['minus_di'], 0.0)
) {
	tradepress_indicator_sanity_record(
		$tests,
		'adx_flat_prices_edge_case',
		'passed',
		array(
			'expected' => array(
				'adx'      => 0.0,
				'plus_di'  => 0.0,
				'minus_di' => 0.0,
			),
			'actual'   => $adx_flat,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'adx_flat_prices_edge_case',
		array(
			'expected' => array(
				'adx'      => 0.0,
				'plus_di'  => 0.0,
				'minus_di' => 0.0,
			),
			'actual'   => $adx_flat,
		)
	);
}

// MACD with zero prices should return zero components, not null.
$macd_zero = $indicator->calculate_macd(array_fill(0, 35, 0));
if (
	is_array($macd_zero)
	&& tradepress_indicator_sanity_nearly_equal($macd_zero['macd'], 0.0)
	&& tradepress_indicator_sanity_nearly_equal($macd_zero['signal'], 0.0)
	&& tradepress_indicator_sanity_nearly_equal($macd_zero['histogram'], 0.0)
) {
	tradepress_indicator_sanity_record(
		$tests,
		'macd_zero_prices_edge_case',
		'passed',
		array(
			'expected' => array(
				'macd'      => 0.0,
				'signal'    => 0.0,
				'histogram' => 0.0,
			),
			'actual'   => $macd_zero,
		)
	);
} else {
	tradepress_indicator_sanity_fail(
		$tests,
		$passed,
		'macd_zero_prices_edge_case',
		array(
			'expected' => array(
				'macd'      => 0.0,
				'signal'    => 0.0,
				'histogram' => 0.0,
			),
			'actual'   => $macd_zero,
		)
	);
}

$status = $passed ? 'passed' : 'failed';
if ($passed && ! empty($warnings)) {
	$status = 'passed_with_warnings';
}

$output = array(
	'status'            => $status,
	'test_suite'        => 'technical_indicator_sanity',
	'execution_time_ms' => round((microtime(true) - $started_at) * 1000, 2),
	'tests_run'         => count($tests),
	'warnings_count'    => count($warnings),
	'tests'             => $tests,
	'warnings'          => $warnings,
);

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($passed ? 0 : 1);
