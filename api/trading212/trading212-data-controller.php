<?php
/**
 * Trading212 Data Controller
 *
 * Handles accessing and managing data from Trading212 API
 *
 * @package TradePress/API
 * @version 1.0.0
 * @since 2023-07-11 11:30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TradePress_Trading212_Data_Controller Class
 */
class TradePress_Trading212_Data_Controller {

	/**
	 * Trading212 API instance
	 *
	 * @var TradePress_Trading212_API
	 */
	private $api;

	/**
	 * Data cache time in seconds
	 *
	 * @var int
	 */
	private $cache_time = 300; // 5 minutes

	/**
	 * Constructor
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		require_once __DIR__ . '/trading212-api.php';
		$this->api = new TradePress_Trading212_API();
	}

	/**
	 * Get account information
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error Account information
	 * @version 1.0.0
	 */
	public function get_account_info( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_account_info';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$account_info = $this->api->get_account_info();

		if ( ! is_wp_error( $account_info ) ) {
			set_transient( $cache_key, $account_info, $this->cache_time );
		}

		return $account_info;
	}

	/**
	 * Get account cash/balance information
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error Account cash information
	 * @version 1.0.0
	 */
	public function get_account_cash( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_account_cash';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$account_cash = $this->api->get_account_cash();

		if ( ! is_wp_error( $account_cash ) ) {
			set_transient( $cache_key, $account_cash, $this->cache_time );
		}

		return $account_cash;
	}

	/**
	 * Get all available instruments
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error List of instruments
	 * @version 1.0.0
	 */
	public function get_instruments( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_instruments';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$instruments = $this->api->get_instruments();

		if ( ! is_wp_error( $instruments ) ) {
			set_transient( $cache_key, $instruments, $this->cache_time * 12 ); // Cache for longer (1 hour)
		}

		return $instruments;
	}

	/**
	 * Get all open positions
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error List of positions
	 * @version 1.0.0
	 */
	public function get_positions( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_positions';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$positions = $this->api->get_positions();

		if ( ! is_wp_error( $positions ) ) {
			set_transient( $cache_key, $positions, $this->cache_time );
		}

		return $positions;
	}

	/**
	 * Get all orders
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error List of orders
	 * @version 1.0.0
	 */
	public function get_orders( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_orders';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$orders = $this->api->get_orders();

		if ( ! is_wp_error( $orders ) ) {
			set_transient( $cache_key, $orders, $this->cache_time );
		}

		return $orders;
	}

	/**
	 * Get transaction history
	 *
	 * @param array $params Optional parameters (startDate, endDate, limit)
	 * @param bool  $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error Transaction history
	 * @version 1.0.0
	 */
	public function get_transaction_history( $params = array(), $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_transactions_' . md5( serialize( $params ) );
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$transactions = $this->api->get_transaction_history( $params );

		if ( ! is_wp_error( $transactions ) ) {
			set_transient( $cache_key, $transactions, $this->cache_time * 6 ); // Cache for 30 minutes
		}

		return $transactions;
	}

	/**
	 * Get market quotes for instruments
	 *
	 * @param array $instrument_codes Array of instrument codes
	 * @param bool  $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error Market quotes
	 * @version 1.0.0
	 */
	public function get_market_quotes( $instrument_codes = array(), $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_quotes_' . md5( serialize( $instrument_codes ) );
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$quotes = $this->api->get_market_quotes( $instrument_codes );

		if ( ! is_wp_error( $quotes ) ) {
			// Cache for a shorter time since market data changes frequently
			set_transient( $cache_key, $quotes, 60 ); // Cache for 1 minute
		}

		return $quotes;
	}

	/**
	 * Get watchlists
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache
	 * @return array|WP_Error List of watchlists
	 * @version 1.0.0
	 */
	public function get_watchlists( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_watchlists';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$watchlists = $this->api->get_watchlists();

		if ( ! is_wp_error( $watchlists ) ) {
			set_transient( $cache_key, $watchlists, $this->cache_time * 6 ); // Cache for 30 minutes
		}

		return $watchlists;
	}

	/**
	 * Get exchanges and their working schedules
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache.
	 * @return array|WP_Error List of exchanges
	 * @version 1.0.0
	 */
	public function get_exchanges( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_exchanges';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$exchanges = $this->api->get_exchanges();

		if ( ! is_wp_error( $exchanges ) ) {
			set_transient( $cache_key, $exchanges, $this->cache_time * 12 ); // Cache 1 hour — rarely changes.
		}

		return $exchanges;
	}

	/**
	 * Get equity portfolio positions
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache.
	 * @return array|WP_Error Portfolio positions
	 * @version 1.0.0
	 */
	public function get_equity_portfolio( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_equity_portfolio';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$portfolio = $this->api->get_equity_portfolio();

		if ( ! is_wp_error( $portfolio ) ) {
			set_transient( $cache_key, $portfolio, $this->cache_time );
		}

		return $portfolio;
	}

	/**
	 * Get all pies for the account
	 *
	 * @param bool $force_refresh Force refresh from API instead of cache.
	 * @return array|WP_Error List of pies
	 * @version 1.0.0
	 */
	public function get_pies( $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_pies';
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$pies = $this->api->get_pies();

		if ( ! is_wp_error( $pies ) ) {
			set_transient( $cache_key, $pies, $this->cache_time );
		}

		return $pies;
	}

	/**
	 * Get a specific pie by ID
	 *
	 * @param int  $pie_id Pie ID.
	 * @param bool $force_refresh Force refresh from API instead of cache.
	 * @return array|WP_Error Pie details
	 * @version 1.0.0
	 */
	public function get_pie( $pie_id, $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_pie_' . (int) $pie_id;
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$pie = $this->api->get_pie( $pie_id );

		if ( ! is_wp_error( $pie ) ) {
			set_transient( $cache_key, $pie, $this->cache_time );
		}

		return $pie;
	}

	/**
	 * Get historical dividend records
	 *
	 * @param array $params Optional: cursor, ticker, limit.
	 * @param bool  $force_refresh Force refresh from API instead of cache.
	 * @return array|WP_Error Dividend history
	 * @version 1.0.0
	 */
	public function get_history_dividends( $params = array(), $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_history_dividends_' . md5( serialize( $params ) );
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$dividends = $this->api->get_history_dividends( $params );

		if ( ! is_wp_error( $dividends ) ) {
			set_transient( $cache_key, $dividends, $this->cache_time * 6 ); // Cache 30 minutes.
		}

		return $dividends;
	}

	/**
	 * Get historical order records
	 *
	 * @param array $params Optional: cursor, ticker, limit.
	 * @param bool  $force_refresh Force refresh from API instead of cache.
	 * @return array|WP_Error Order history
	 * @version 1.0.0
	 */
	public function get_history_orders( $params = array(), $force_refresh = false ) {
		$cache_key   = 'tradepress_trading212_history_orders_' . md5( serialize( $params ) );
		$cached_data = get_transient( $cache_key );

		if ( ! $force_refresh && $cached_data !== false ) {
			return $cached_data;
		}

		$orders = $this->api->get_history_orders( $params );

		if ( ! is_wp_error( $orders ) ) {
			set_transient( $cache_key, $orders, $this->cache_time * 6 ); // Cache 30 minutes.
		}

		return $orders;
	}

	/**
	 * Get a list of all available endpoints in the Trading212 API
	 *
	 * @return array List of endpoints
	 * @version 1.0.0
	 */
	public function get_available_endpoints() {
		return $this->api->get_endpoints();
	}

	/**
	 * Import instruments from Trading212 into TradePress symbols
	 *
	 * @return array Results of the import operation
	 * @version 1.0.0
	 */
	public function import_instruments_to_symbols() {
		$instruments = $this->get_instruments( true ); // Force refresh

		if ( is_wp_error( $instruments ) ) {
			return array(
				'success' => false,
				'message' => $instruments->get_error_message(),
			);
		}

		$results = array(
			'success' => true,
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors'  => 0,
		);

		foreach ( $instruments as $instrument ) {
			// Skip if instrument code is empty
			if ( empty( $instrument['instrumentCode'] ) ) {
				++$results['skipped'];
				continue;
			}

			// Check if symbol already exists
			$existing_symbol = get_posts(
				array(
					'post_type'      => 'symbols',
					'meta_key'       => '_tradepress_ticker',
					'meta_value'     => $instrument['instrumentCode'],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			// Prepare symbol data
			$symbol_data = array(
				'post_title'   => $instrument['name'],
				'post_content' => sprintf(
					/* translators: %s: instrument code, %s: ISIN number, %s: currency code, %s: market name */
					__( 'Instrument Code: %1$s\nISIN: %2$s\nCurrency: %3$s\nMarket: %4$s', 'tradepress' ),
					$instrument['instrumentCode'],
					isset( $instrument['isin'] ) ? $instrument['isin'] : 'N/A',
					$instrument['currencyCode'],
					$instrument['marketId']
				),
				'post_status'  => 'publish',
				'post_type'    => 'symbols',
			);

			$meta_data = array(
				'_tradepress_ticker'            => $instrument['instrumentCode'],
				'_tradepress_exchange'          => $instrument['marketId'],
				'_tradepress_data_source'       => 'trading212',
				'_tradepress_currency'          => $instrument['currencyCode'],
				'_tradepress_data_last_updated' => current_time( 'timestamp' ),
			);

			// Add or update symbol
			if ( empty( $existing_symbol ) ) {
				// Create new symbol
				$post_id = wp_insert_post( $symbol_data, true );

				if ( is_wp_error( $post_id ) ) {
					++$results['errors'];
					continue;
				}

				// Add meta data
				foreach ( $meta_data as $key => $value ) {
					update_post_meta( $post_id, $key, $value );
				}

				++$results['created'];
			} else {
				// Update existing symbol
				$symbol_data['ID'] = $existing_symbol[0];
				$post_id           = wp_update_post( $symbol_data, true );

				if ( is_wp_error( $post_id ) ) {
					++$results['errors'];
					continue;
				}

				// Update meta data
				foreach ( $meta_data as $key => $value ) {
					update_post_meta( $post_id, $key, $value );
				}

				++$results['updated'];
			}
		}

		return $results;
	}
}
