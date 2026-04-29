<?php
/**
 * Standalone sanity checks for TradePress scoring directive contracts.
 *
 * This runner validates directive shape and score contracts. It does not assume
 * that every strategy total is a percentage or capped at 100.
 *
 * Run:
 * php tests/run-directive-sanity.php
 *
 * @package TradePress
 */

declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(
	static function ($severity, $message, $file, $line) {
		if (! (error_reporting() & $severity)) {
			return false;
		}

		if (E_DEPRECATED === $severity || E_USER_DEPRECATED === $severity) {
			return true;
		}

		throw new ErrorException($message, 0, $severity, $file, $line);
	}
);

if (! defined('ABSPATH')) {
	define('ABSPATH', dirname(__DIR__, 4) . '/');
}

if (! defined('TRADEPRESS_PLUGIN_DIR_PATH')) {
	define('TRADEPRESS_PLUGIN_DIR_PATH', dirname(__DIR__) . '/');
}

if (! function_exists('get_option')) {
	/**
	 * Minimal get_option mock for standalone runner.
	 *
	 * @param string $option Option name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	function get_option($option, $default = false) {
		return $default;
	}
}

if (! function_exists('is_wp_error')) {
	/**
	 * Minimal is_wp_error mock for standalone runner.
	 *
	 * @param mixed $thing Value to check.
	 * @return bool
	 */
	function is_wp_error($thing): bool {
		return $thing instanceof WP_Error;
	}
}

if (! function_exists('get_transient')) {
	/**
	 * Minimal get_transient mock for standalone runner.
	 *
	 * @param string $transient Transient name.
	 * @return false
	 */
	function get_transient($transient) {
		return false;
	}
}

if (! function_exists('set_transient')) {
	/**
	 * Minimal set_transient mock for standalone runner.
	 *
	 * @param string $transient Transient name.
	 * @param mixed  $value Transient value.
	 * @param int    $expiration Expiration.
	 * @return bool
	 */
	function set_transient($transient, $value, $expiration = 0): bool {
		return true;
	}
}

if (! function_exists('current_time')) {
	/**
	 * Minimal current_time mock for standalone runner.
	 *
	 * @param string $type Type.
	 * @param bool   $gmt GMT flag.
	 * @return int|string
	 */
	function current_time($type, $gmt = false) {
		if ('timestamp' === $type || 'U' === $type) {
			return time();
		}

		return gmdate('Y-m-d H:i:s');
	}
}

if (! function_exists('__')) {
	/**
	 * Minimal translation mock.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function __($text) {
		return $text;
	}
}

if (! function_exists('esc_html__')) {
	/**
	 * Minimal escaped translation mock.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function esc_html__($text) {
		return $text;
	}
}

if (! class_exists('WP_Error')) {
	/**
	 * Minimal WP_Error mock.
	 */
	class WP_Error {
		/**
		 * Error message.
		 *
		 * @var string
		 */
		private $message;

		/**
		 * Constructor.
		 *
		 * @param string $code Error code.
		 * @param string $message Error message.
		 */
		public function __construct($code = '', $message = '') {
			$this->message = $message;
		}

		/**
		 * Get message.
		 *
		 * @return string
		 */
		public function get_error_message(): string {
			return $this->message;
		}
	}
}

if (! class_exists('TradePress_Developer_Flow_Logger')) {
	/**
	 * Minimal developer flow logger mock.
	 */
	class TradePress_Developer_Flow_Logger {
		/**
		 * Start flow.
		 *
		 * @return void
		 */
		public static function start_flow(): void {}

		/**
		 * Log action.
		 *
		 * @return void
		 */
		public static function log_action(): void {}

		/**
		 * Log cache action.
		 *
		 * @return void
		 */
		public static function log_cache(): void {}

		/**
		 * Log API action.
		 *
		 * @return void
		 */
		public static function log_api(): void {}

		/**
		 * End flow.
		 *
		 * @return void
		 */
		public static function end_flow(): void {}
	}
}

if (! class_exists('TradePress_Directive_Logger')) {
	/**
	 * Minimal directive logger mock.
	 */
	class TradePress_Directive_Logger {
		/**
		 * Log message.
		 *
		 * @return void
		 */
		public static function log(): void {}
	}
}

if (! class_exists('TradePress_Call_Register')) {
	/**
	 * Minimal cached indicator mock for standalone runner.
	 */
	class TradePress_Call_Register {
		/**
		 * Return deterministic cached values for API-backed directives.
		 *
		 * @param string $call_id Call ID.
		 * @return mixed
		 */
		public static function get_cached_result($call_id) {
			$args = func_get_args();
			foreach ($args as $arg) {
				if (is_string($arg) && in_array($arg, array( 'rsi', 'adx', 'cci' ), true)) {
					$call_id = $arg;
					break;
				}
			}

			$values = array(
				'rsi' => 28.0,
				'adx' => array(
					'adx'      => 24.0,
					'plus_di'  => 30.0,
					'minus_di' => 18.0,
				),
				'cci' => -85.0,
			);

			return array_key_exists($call_id, $values) ? $values[$call_id] : false;
		}

		/**
		 * Cache result mock.
		 *
		 * @return bool
		 */
		public static function cache_result(): bool {
			return true;
		}
	}
}

if (! class_exists('TradePress_Technical_Indicator_Cache')) {
	/**
	 * Minimal technical indicator cache mock for standalone runner.
	 */
	class TradePress_Technical_Indicator_Cache {
		/**
		 * Return deterministic indicator values.
		 *
		 * @param string   $symbol Symbol.
		 * @param string   $indicator Indicator.
		 * @param array    $parameters Parameters.
		 * @param callable $fetcher Fetcher.
		 * @param int      $ttl TTL.
		 * @return mixed
		 */
		public static function get_or_fetch_indicator($symbol, $indicator, $parameters = array(), $fetcher = null, $ttl = 0) {
			$values = array(
				'macd' => array(
					'macd'      => 1.5,
					'signal'    => 1.1,
					'histogram' => 0.4,
				),
				'cci'  => -85.0,
			);

			return array_key_exists($indicator, $values) ? $values[$indicator] : null;
		}
	}
}

require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/scoring-directive-base.php';

$started_at     = microtime(true);
$directives_dir = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives';
$results        = array();
$summary        = array(
	'files_discovered'       => 0,
	'directives_loaded'      => 0,
	'directives_skipped'     => 0,
	'directives_passed'      => 0,
	'directives_failed'      => 0,
	'directives_warning'     => 0,
	'contract_gaps'          => 0,
	'score_values_checked'   => 0,
);

/**
 * Create rich symbol data for directive sanity checks.
 *
 * @return array
 */
function tradepress_directive_sanity_sample_symbol_data(): array {
	$historical = array();
	$base_time  = strtotime('2026-03-02 16:00:00 UTC');
	$price      = 100.0;

	for ($i = 0; $i < 90; $i++) {
		$open   = $price;
		$close  = $price + (($i % 5) - 2) * 0.35 + 0.08;
		$high   = max($open, $close) + 0.75;
		$low    = min($open, $close) - 0.65;
		$volume = 1000000 + (($i % 7) * 45000);

		$historical[] = array(
			'date'      => gmdate('Y-m-d', $base_time + ($i * DAY_IN_SECONDS)),
			'timestamp' => $base_time + ($i * DAY_IN_SECONDS),
			'open'      => round($open, 4),
			'high'      => round($high, 4),
			'low'       => round($low, 4),
			'close'     => round($close, 4),
			'price'     => round($close, 4),
			'volume'    => $volume,
		);

		$price = $close;
	}

	return array(
		'symbol'       => 'TPTEST',
		'price'        => 112.5,
		'volume'       => 1800000,
		'avg_volume'   => 1200000,
		'volume_ratio' => 1.5,
		'historical'   => $historical,
		'price_data'   => $historical,
		'technical'    => array(
			'rsi'             => 28.0,
			'macd'            => array(
				'macd'      => 1.5,
				'signal'    => 1.1,
				'histogram' => 0.4,
			),
			'bollinger_bands' => array(
				'upper'  => 120.0,
				'middle' => 110.0,
				'lower'  => 100.0,
				'upper_band'  => 120.0,
				'middle_band' => 110.0,
				'lower_band'  => 100.0,
			),
			'ema'             => 110.0,
			'mfi'             => 72.0,
			'obv'             => 3500000,
			'sma'             => 108.0,
			'vwap'            => 111.0,
			'stoch_k'         => 35.0,
			'stoch_d'         => 40.0,
			'moving_averages' => array(
				'short_ma' => 111.0,
				'long_ma'  => 106.0,
				'sma_20'  => 109.0,
				'sma_50'  => 106.0,
				'sma_200' => 98.0,
				'ema_12'  => 111.0,
				'ema_26'  => 107.0,
			),
			'ma_20'           => 109.0,
			'ma_50'           => 106.0,
			'ma_200'          => 98.0,
		),
		'analyst'      => array(
			'last_update_date' => '2026-04-20',
		),
	);
}

/**
 * Extract class declarations from a PHP file.
 *
 * @param string $contents File contents.
 * @return array
 */
function tradepress_directive_sanity_parse_classes(string $contents): array {
	preg_match_all('/class\s+([A-Za-z0-9_]+)\s+extends\s+([A-Za-z0-9_]+)/i', $contents, $matches, PREG_SET_ORDER);

	$classes = array();
	foreach ($matches as $match) {
		$classes[] = array(
			'class'  => $match[1],
			'parent' => $match[2],
		);
	}

	return $classes;
}

/**
 * Extract numeric score values from directive output.
 *
 * @param mixed $value Directive output.
 * @return array
 */
function tradepress_directive_sanity_extract_scores($value): array {
	if (is_int($value) || is_float($value)) {
		return array( (float) $value );
	}

	if (! is_array($value)) {
		return array();
	}

	$scores = array();
	foreach ($value as $key => $child) {
		if ('score' === $key && (is_int($child) || is_float($child))) {
			$scores[] = (float) $child;
			continue;
		}

		if (is_array($child)) {
			$scores = array_merge($scores, tradepress_directive_sanity_extract_scores($child));
		}
	}

	return $scores;
}

/**
 * Check whether a value is a finite number.
 *
 * @param mixed $value Value.
 * @return bool
 */
function tradepress_directive_sanity_is_number($value): bool {
	return is_int($value) || is_float($value);
}

/**
 * Run one directive result contract check.
 *
 * @param object $directive Directive instance.
 * @param string $mode Test mode.
 * @param mixed  $input Input data.
 * @param mixed  $max_score Directive max score.
 * @return array
 */
function tradepress_directive_sanity_run_calculation($directive, string $mode, $input, $max_score): array {
	$check = array(
		'mode'    => $mode,
		'status'  => 'passed',
		'details' => array(),
	);

	try {
		$output = $directive->calculate_score($input);
	} catch (Throwable $throwable) {
		$check['status']             = 'failed';
		$check['details']['error']   = get_class($throwable) . ': ' . $throwable->getMessage();
		$check['details']['output']  = null;
		return $check;
	}

	$scores = tradepress_directive_sanity_extract_scores($output);

	if (null === $output) {
		$check['status']              = 'warning';
		$check['details']['message']  = 'Directive returned null. This may be acceptable for unavailable provider data, but should be explicit in the contract.';
		$check['details']['output']   = null;
		$check['details']['scores']   = array();
		return $check;
	}

	if (empty($scores)) {
		$check['status']              = 'failed';
		$check['details']['message']  = 'Directive output did not contain a numeric score value.';
		$check['details']['output']   = $output;
		$check['details']['scores']   = array();
		return $check;
	}

	$score_issues = array();
	foreach ($scores as $score) {
		if (! tradepress_directive_sanity_is_number($score)) {
			$score_issues[] = array(
				'score' => $score,
				'issue' => 'not_numeric',
			);
			continue;
		}

		if ($score < 0) {
			$score_issues[] = array(
				'score' => $score,
				'issue' => 'below_zero',
			);
		}

		if (tradepress_directive_sanity_is_number($max_score) && $score > (float) $max_score) {
			$score_issues[] = array(
				'score'     => $score,
				'max_score' => (float) $max_score,
				'issue'     => 'above_declared_max_score',
			);
		}
	}

	$check['details']['scores'] = $scores;

	if (! empty($score_issues)) {
		$check['status']                   = 'failed';
		$check['details']['score_issues']  = $score_issues;
		$check['details']['output_sample'] = $output;
	}

	return $check;
}

if (! defined('DAY_IN_SECONDS')) {
	define('DAY_IN_SECONDS', 86400);
}

$files       = glob($directives_dir . '/*.php');
$sample_data = tradepress_directive_sanity_sample_symbol_data();

foreach ($files as $file) {
	++$summary['files_discovered'];

	$contents = file_get_contents($file);
	$classes  = tradepress_directive_sanity_parse_classes((string) $contents);
	$basename = basename($file);

	if (empty($classes)) {
		++$summary['directives_skipped'];
		$results[] = array(
			'file'   => $basename,
			'status' => 'skipped',
			'reason' => 'No class extending another class was found.',
		);
		continue;
	}

	foreach ($classes as $class_info) {
		$class_name  = $class_info['class'];
		$parent_name = $class_info['parent'];

		$result = array(
			'file'        => $basename,
			'class'       => $class_name,
			'parent'      => $parent_name,
			'status'      => 'passed',
			'contract'    => array(),
			'calculations'=> array(),
		);

		if ('TradePress_Scoring_Directive_Base' !== $parent_name) {
			++$summary['directives_skipped'];
			$result['status'] = 'skipped';
			$result['reason'] = 'Directive does not extend TradePress_Scoring_Directive_Base. Likely legacy or separate base-contract work.';
			$results[]        = $result;
			continue;
		}

		foreach (array( 'calculate_score', 'get_max_score', 'get_explanation' ) as $required_method) {
			if (! preg_match('/function\s+' . preg_quote($required_method, '/') . '\s*\(/i', (string) $contents)) {
				++$summary['directives_skipped'];
				$result['status'] = 'skipped';
				$result['reason'] = 'Missing required method before load: ' . $required_method;
				$results[]        = $result;
				continue 2;
			}
		}

		try {
			require_once $file;
		} catch (Throwable $throwable) {
			++$summary['directives_failed'];
			$result['status']           = 'failed';
			$result['contract']['load'] = get_class($throwable) . ': ' . $throwable->getMessage();
			$results[]                  = $result;
			continue;
		}

		if (! class_exists($class_name)) {
			++$summary['directives_failed'];
			$result['status']           = 'failed';
			$result['contract']['load'] = 'Class not found after include.';
			$results[]                  = $result;
			continue;
		}

		try {
			$directive = new $class_name();
		} catch (Throwable $throwable) {
			++$summary['directives_failed'];
			$result['status']                  = 'failed';
			$result['contract']['instantiate'] = get_class($throwable) . ': ' . $throwable->getMessage();
			$results[]                         = $result;
			continue;
		}

		++$summary['directives_loaded'];

		$result['contract']['id']   = method_exists($directive, 'get_id') ? $directive->get_id() : null;
		$result['contract']['name'] = method_exists($directive, 'get_name') ? $directive->get_name() : null;

		try {
			$max_score = $directive->get_max_score();
		} catch (Throwable $throwable) {
			$max_score = null;
			$result['contract']['max_score_error'] = get_class($throwable) . ': ' . $throwable->getMessage();
		}

		$result['contract']['max_score'] = $max_score;
		$result['contract']['score_scale_note'] = 'Scores are validated against this directive max score when available. Strategy totals may use a different maximum and may later be transformed into percentages.';

		if (! tradepress_directive_sanity_is_number($max_score) || $max_score <= 0) {
			++$summary['contract_gaps'];
			$result['status'] = 'warning';
			$result['contract']['max_score_contract'] = 'get_max_score() should return a positive numeric score ceiling for this directive/config.';
		}

		$rich_check    = tradepress_directive_sanity_run_calculation($directive, 'rich_sample_input', $sample_data, $max_score);
		$minimal_check = tradepress_directive_sanity_run_calculation($directive, 'minimal_input', array( 'symbol' => 'TPTEST' ), $max_score);

		$result['calculations'][] = $rich_check;
		$result['calculations'][] = $minimal_check;

		foreach (array( $rich_check, $minimal_check ) as $check) {
			$summary['score_values_checked'] += count($check['details']['scores'] ?? array());

			if ('failed' === $check['status']) {
				$result['status'] = 'failed';
			} elseif ('warning' === $check['status'] && 'failed' !== $result['status']) {
				$result['status'] = 'warning';
			}
		}

		if ('failed' === $result['status']) {
			++$summary['directives_failed'];
		} elseif ('warning' === $result['status']) {
			++$summary['directives_warning'];
		} else {
			++$summary['directives_passed'];
		}

		$results[] = $result;
	}
}

$overall_status = 'passed';
if ($summary['directives_failed'] > 0) {
	$overall_status = 'failed';
} elseif ($summary['directives_warning'] > 0 || $summary['contract_gaps'] > 0) {
	$overall_status = 'passed_with_warnings';
}

$output = array(
	'status'            => $overall_status,
	'test_suite'        => 'scoring_directive_sanity',
	'execution_time_ms' => round((microtime(true) - $started_at) * 1000, 2),
	'score_contract'    => array(
		'global_assumption' => 'Do not assume every scoring strategy is a 0..100 percentage scale.',
		'directive_rule'    => 'Directive outputs are checked against get_max_score() when available.',
		'strategy_rule'     => 'A strategy may define its own maximum total and optionally transform raw score/max score into a percentage for display.',
	),
	'summary'           => $summary,
	'results'           => $results,
);

if (in_array('--summary', $argv ?? array(), true)) {
	$failed = array();
	foreach ($results as $result) {
		if (isset($result['status']) && 'failed' === $result['status']) {
			$failed[] = array(
				'file'   => $result['file'] ?? null,
				'class'  => $result['class'] ?? null,
				'reason' => $result['contract']['load'] ?? $result['contract']['instantiate'] ?? ($result['calculations'][0]['details']['error'] ?? null),
			);
		}
	}

	$output = array(
		'status'            => $overall_status,
		'test_suite'        => 'scoring_directive_sanity',
		'execution_time_ms' => round((microtime(true) - $started_at) * 1000, 2),
		'score_contract'    => $output['score_contract'],
		'summary'           => $summary,
		'failed'            => $failed,
	);
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit('failed' === $overall_status ? 1 : 0);
