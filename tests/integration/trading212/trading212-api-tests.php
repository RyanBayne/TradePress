<?php
/**
 * Trading212 API Integration Tests
 *
 * Exercises all read-only Trading212 API endpoints using the configured API key.
 * No orders, pies, or watchlists are created or modified — all tests are read-only.
 *
 * @package TradePress/Tests/Integration/Trading212
 * @version 1.0.0
 * @since   2025-05-01
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TradePress_Trading212_API_Tests
 *
 * Each test method returns a result array with keys:
 *   - name     (string)  Human-readable test name
 *   - endpoint (string)  API path tested
 *   - group    (string)  Category (Account, Portfolio, Orders, …)
 *   - status   (string)  'pass' | 'fail' | 'skip'
 *   - message  (string)  Detail or error message
 */
class TradePress_Trading212_API_Tests {

	/**
	 * API instance.
	 *
	 * @var TradePress_Trading212_API
	 */
	private $api;

	/**
	 * API key presence flag.
	 *
	 * @var bool
	 */
	private $has_key;

	/**
	 * Constructor
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		if ( ! class_exists( 'TradePress_Trading212_API' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/trading212/trading212-api.php';
		}

		$this->api     = new TradePress_Trading212_API();
		$api_settings = get_option( 'tradepress_api_settings', array() );
		$api_key      = isset( $api_settings['trading212_api_key'] ) ? trim( (string) $api_settings['trading212_api_key'] ) : '';
		if ( '' === $api_key ) {
			$api_key = trim( (string) get_option( 'tradepress_trading212_api_key', '' ) );
		}
		if ( '' === $api_key ) {
			$api_key = trim( (string) get_option( 'tradepress_trading212_paper_api_key', '' ) );
		}
		if ( '' === $api_key ) {
			$api_key = trim( (string) get_option( 'TradePress_api_trading212_realmoney_apikey', '' ) );
		}
		if ( '' === $api_key ) {
			$api_key = trim( (string) get_option( 'TradePress_api_trading212_papermoney_apikey', '' ) );
		}
		$this->has_key = '' !== $api_key;
	}

	/**
	 * Run all tests and return a structured result set.
	 *
	 * @return array { tests: array, environment: string, total: int, passed: int, failed: int, skipped: int }
	 * @version 1.0.0
	 */
	public function run_all_tests() {
		$tests = array_merge(
			$this->run_account_tests(),
			$this->run_portfolio_tests(),
			$this->run_metadata_tests(),
			$this->run_order_tests(),
			$this->run_history_tests(),
			$this->run_watchlist_tests(),
			$this->run_pie_tests(),
			$this->run_market_data_tests()
		);

		$passed  = count( array_filter( $tests, fn( $t ) => 'pass' === $t['status'] ) );
		$failed  = count( array_filter( $tests, fn( $t ) => 'fail' === $t['status'] ) );
		$skipped = count( array_filter( $tests, fn( $t ) => 'skip' === $t['status'] ) );

		return array(
			'tests'       => $tests,
			'environment' => $this->api->get_environment(),
			'total'       => count( $tests ),
			'passed'      => $passed,
			'failed'      => $failed,
			'skipped'     => $skipped,
		);
	}

	// -------------------------------------------------------------------------
	// Account
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_account_tests() {
		return array(
			$this->test_account_info(),
			$this->test_account_cash(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_account_info() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Account Info', 'equity/account/info', 'Account', 'No API key configured' );
		}

		$result = $this->api->get_account_info();

		if ( is_wp_error( $result ) ) {
			return $this->handle_api_error( $result, 'Account Info', 'equity/account/info', 'Account' );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Account Info', 'equity/account/info', 'Account', 'Unexpected response type: ' . gettype( $result ) );
		}

		return $this->pass( 'Account Info', 'equity/account/info', 'Account', 'Received account info — keys: ' . implode( ', ', array_keys( $result ) ) );
	}

	/**
	 * @version 1.0.0
	 */
	private function test_account_cash() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Account Cash', 'equity/account/cash', 'Account', 'No API key configured' );
		}

		$result = $this->api->get_account_cash();

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Account Cash', 'equity/account/cash', 'Account', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Account Cash', 'equity/account/cash', 'Account', 'Unexpected response type: ' . gettype( $result ) );
		}

		$free  = isset( $result['free'] ) ? number_format( (float) $result['free'], 2 ) : 'n/a';
		$total = isset( $result['total'] ) ? number_format( (float) $result['total'], 2 ) : 'n/a';

		return $this->pass( 'Account Cash', 'equity/account/cash', 'Account', "free={$free}, total={$total}" );
	}

	// -------------------------------------------------------------------------
	// Portfolio
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_portfolio_tests() {
		return array(
			$this->test_portfolio(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_portfolio() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Portfolio Positions', 'equity/portfolio', 'Portfolio', 'No API key configured' );
		}

		$result = $this->api->get_equity_portfolio();

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Portfolio Positions', 'equity/portfolio', 'Portfolio', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Portfolio Positions', 'equity/portfolio', 'Portfolio', 'Unexpected response type: ' . gettype( $result ) );
		}

		$count = count( $result );
		return $this->pass( 'Portfolio Positions', 'equity/portfolio', 'Portfolio', "{$count} position(s) returned" );
	}

	// -------------------------------------------------------------------------
	// Metadata
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_metadata_tests() {
		return array(
			$this->test_exchanges(),
			$this->test_instruments(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_exchanges() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Exchanges', 'equity/metadata/exchanges', 'Metadata', 'No API key configured' );
		}

		$result = $this->api->get_exchanges();

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Exchanges', 'equity/metadata/exchanges', 'Metadata', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Exchanges', 'equity/metadata/exchanges', 'Metadata', 'Unexpected response type: ' . gettype( $result ) );
		}

		$count = count( $result );
		return $this->pass( 'Exchanges', 'equity/metadata/exchanges', 'Metadata', "{$count} exchange(s) returned" );
	}

	/**
	 * @version 1.0.0
	 */
	private function test_instruments() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Instruments', 'equity/metadata/instruments', 'Metadata', 'No API key configured' );
		}

		$result = $this->api->get_instruments();

		if ( is_wp_error( $result ) ) {
			return $this->handle_api_error( $result, 'Instruments', 'equity/metadata/instruments', 'Metadata' );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Instruments', 'equity/metadata/instruments', 'Metadata', 'Unexpected response type: ' . gettype( $result ) );
		}

		$count = count( $result );
		$sample = ( $count > 0 && isset( $result[0]['ticker'] ) ) ? 'first ticker: ' . $result[0]['ticker'] : '';
		return $this->pass( 'Instruments', 'equity/metadata/instruments', 'Metadata', "{$count} instrument(s) returned. {$sample}" );
	}

	// -------------------------------------------------------------------------
	// Orders (read-only)
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_order_tests() {
		return array(
			$this->test_open_orders(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_open_orders() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Open Orders', 'equity/orders', 'Orders', 'No API key configured' );
		}

		$result = $this->api->get_orders();

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Open Orders', 'equity/orders', 'Orders', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Open Orders', 'equity/orders', 'Orders', 'Unexpected response type: ' . gettype( $result ) );
		}

		$count = count( $result );
		return $this->pass( 'Open Orders', 'equity/orders', 'Orders', "{$count} open order(s) returned" );
	}

	// -------------------------------------------------------------------------
	// History
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_history_tests() {
		return array(
			$this->test_history_transactions(),
			$this->test_history_orders(),
			$this->test_history_dividends(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_history_transactions() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Transaction History', 'equity/history/transactions', 'History', 'No API key configured' );
		}

		$result = $this->api->get_transaction_history( array( 'limit' => 10 ) );

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Transaction History', 'equity/history/transactions', 'History', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Transaction History', 'equity/history/transactions', 'History', 'Unexpected response type: ' . gettype( $result ) );
		}

		$items = isset( $result['items'] ) ? count( $result['items'] ) : count( $result );
		return $this->pass( 'Transaction History', 'equity/history/transactions', 'History', "{$items} transaction(s) returned" );
	}

	/**
	 * @version 1.0.0
	 */
	private function test_history_orders() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Order History', 'equity/history/orders', 'History', 'No API key configured' );
		}

		$result = $this->api->get_history_orders( array( 'limit' => 10 ) );

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Order History', 'equity/history/orders', 'History', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Order History', 'equity/history/orders', 'History', 'Unexpected response type: ' . gettype( $result ) );
		}

		$items = isset( $result['items'] ) ? count( $result['items'] ) : count( $result );
		return $this->pass( 'Order History', 'equity/history/orders', 'History', "{$items} record(s) returned" );
	}

	/**
	 * @version 1.0.0
	 */
	private function test_history_dividends() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Dividend History', 'equity/history/dividends', 'History', 'No API key configured' );
		}

		$result = $this->api->get_history_dividends( array( 'limit' => 10 ) );

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Dividend History', 'equity/history/dividends', 'History', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Dividend History', 'equity/history/dividends', 'History', 'Unexpected response type: ' . gettype( $result ) );
		}

		$items = isset( $result['items'] ) ? count( $result['items'] ) : count( $result );
		return $this->pass( 'Dividend History', 'equity/history/dividends', 'History', "{$items} record(s) returned" );
	}

	// -------------------------------------------------------------------------
	// Watchlists
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_watchlist_tests() {
		return array(
			$this->test_watchlists(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_watchlists() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Watchlists', 'equity/watchlists', 'Watchlists', 'No API key configured' );
		}

		$result = $this->api->get_watchlists();

		if ( is_wp_error( $result ) ) {
			return $this->handle_api_error( $result, 'Watchlists', 'equity/watchlists', 'Watchlists' );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Watchlists', 'equity/watchlists', 'Watchlists', 'Unexpected response type: ' . gettype( $result ) );
		}

		$count = count( $result );
		$names = array();
		foreach ( array_slice( $result, 0, 3 ) as $wl ) {
			if ( isset( $wl['name'] ) ) {
				$names[] = $wl['name'];
			}
		}
		$detail = $names ? implode( ', ', $names ) : "{$count} watchlist(s) returned";
		return $this->pass( 'Watchlists', 'equity/watchlists', 'Watchlists', $detail );
	}

	// -------------------------------------------------------------------------
	// Pies
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_pie_tests() {
		return array(
			$this->test_pies(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_pies() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Pies', 'equity/pies', 'Pies', 'No API key configured' );
		}

		$result = $this->api->get_pies();

		if ( is_wp_error( $result ) ) {
			return $this->fail( 'Pies', 'equity/pies', 'Pies', $result->get_error_message() );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Pies', 'equity/pies', 'Pies', 'Unexpected response type: ' . gettype( $result ) );
		}

		$count = count( $result );
		return $this->pass( 'Pies', 'equity/pies', 'Pies', "{$count} pie(s) returned" );
	}

	// -------------------------------------------------------------------------
	// Market data
	// -------------------------------------------------------------------------

	/**
	 * @return array
	 * @version 1.0.0
	 */
	private function run_market_data_tests() {
		return array(
			$this->test_market_quotes(),
		);
	}

	/**
	 * @version 1.0.0
	 */
	private function test_market_quotes() {
		if ( ! $this->has_key ) {
			return $this->skip( 'Market Quotes', 'equity/quotes', 'Market Data', 'No API key configured' );
		}

		// Use a known ticker that should always exist on Trading212.
		$result = $this->api->get_market_quotes( array( 'AAPL_US_EQ' ) );

		if ( is_wp_error( $result ) ) {
			return $this->handle_api_error( $result, 'Market Quotes', 'equity/quotes', 'Market Data' );
		}

		if ( ! is_array( $result ) ) {
			return $this->fail( 'Market Quotes', 'equity/quotes', 'Market Data', 'Unexpected response type: ' . gettype( $result ) );
		}

		return $this->pass( 'Market Quotes', 'equity/quotes', 'Market Data', 'Quote data received for AAPL_US_EQ' );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Build a pass result.
	 *
	 * @version 1.0.0
	 */
	private function pass( $name, $endpoint, $group, $message ) {
		return array(
			'name'     => $name,
			'endpoint' => $endpoint,
			'group'    => $group,
			'status'   => 'pass',
			'message'  => $message,
		);
	}

	/**
	 * Build a fail result.
	 *
	 * @version 1.0.0
	 */
	private function fail( $name, $endpoint, $group, $message ) {
		return array(
			'name'     => $name,
			'endpoint' => $endpoint,
			'group'    => $group,
			'status'   => 'fail',
			'message'  => $message,
		);
	}

	/**
	 * Build a skip result.
	 *
	 * @version 1.0.0
	 */
	private function skip( $name, $endpoint, $group, $message ) {
		return array(
			'name'     => $name,
			'endpoint' => $endpoint,
			'group'    => $group,
			'status'   => 'skip',
			'message'  => $message,
		);
	}

	/**
	 * Convert known API limitations into skip results; otherwise fail.
	 *
	 * @param WP_Error $error API error object.
	 * @param string   $name Test name.
	 * @param string   $endpoint Endpoint path.
	 * @param string   $group Test group.
	 * @return array
	 * @version 1.0.0
	 */
	private function handle_api_error( $error, $name, $endpoint, $group ) {
		$status = null;
		$data   = $error->get_error_data();

		if ( is_array( $data ) && isset( $data['status'] ) ) {
			$status = (int) $data['status'];
		}

		$message = $error->get_error_message();

		if ( 429 === $status || false !== stripos( $message, 'TooManyRequests' ) || false !== stripos( $message, 'too many requests' ) ) {
			return $this->skip( $name, $endpoint, $group, 'Rate limited by Trading212 (HTTP 429). Retry after cooldown.' );
		}

		if ( 404 === $status || false !== stripos( $message, '404 page not found' ) ) {
			return $this->skip( $name, $endpoint, $group, 'Endpoint unavailable for this account/region/environment.' );
		}

		return $this->fail( $name, $endpoint, $group, $message );
	}
}
