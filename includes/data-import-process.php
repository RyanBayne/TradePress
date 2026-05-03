<?php
/**
 * TradePress Data Import Background Process
 *
 * Handles API data import in background to avoid AJAX timeout issues
 *
 * @package TradePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DateTime.CurrentTimeTimestamp.Requested -- Existing queue/status options store WordPress-local timestamps.
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment -- Legacy queue payload methods accept mixed item arrays.
// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar -- Existing release-hardening pass is not changing legacy comment text.

/**
 * Background process for queued TradePress data imports.
 */
class TradePress_Data_Import_Process extends TradePress_Background_Processing {

	/**
	 * Background process action slug.
	 *
	 * @var string
	 */
	protected $action = 'data_import';

	/**
	 * Process queue item
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item
	 */
	protected function task( $item ) {
		// Load queue schema
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';

		if ( ! isset( $item['action'] ) ) {
			return false;
		}

		switch ( $item['action'] ) {
			case 'fetch_earnings':
				return $this->fetch_earnings_data( $item );
			case 'fetch_news':
				return $this->fetch_news_data( $item );
			case 'fetch_economic_calendar':
				return $this->fetch_economic_calendar_data( $item );
			case 'fetch_prices':
				return $this->fetch_price_data( $item );
			case 'fetch_market_status':
				return $this->fetch_market_status( $item );
			case 'fetch_technical_data':
				return $this->fetch_technical_data( $item );
			default:
				return false;
		}
	}

	/**
	 * Fetch market news in the background queue.
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item Queue item.
	 * @return bool|array
	 */
	private function fetch_news_data( $item ) {
		$retry_count = isset( $item['retry_count'] ) ? $item['retry_count'] : 0;
		$max_retries = 3;
		$symbol      = isset( $item['symbol'] ) ? sanitize_text_field( (string) $item['symbol'] ) : '';
		$limit       = isset( $item['limit'] ) ? absint( $item['limit'] ) : 50;
		$limit       = max( 1, min( 50, $limit ) );

		try {
			$this->log_process_activity(
				'info',
				'Attempting to fetch news data',
				array(
					'retry'  => $retry_count,
					'symbol' => $symbol,
					'limit'  => $limit,
				)
			);

			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';

			$provider_order = $this->get_news_provider_order();
			$last_error     = null;

			foreach ( $provider_order as $provider_id ) {
				$api = TradePress_API_Factory::create_from_settings( $provider_id, 'paper', 'news' );

				if ( is_wp_error( $api ) ) {
					$last_error = $api;
					continue;
				}

				if ( ! method_exists( $api, 'get_news' ) ) {
					$last_error = new WP_Error( 'unsupported_news_provider', $provider_id . ' does not expose get_news().' );
					continue;
				}

				$news_data = $api->get_news( $symbol, $limit );

				if ( is_wp_error( $news_data ) ) {
					$last_error = $news_data;
					continue;
				}

				$normalized_news = $this->normalize_news_records( $news_data, $provider_id );

				update_option( 'tradepress_news_data', $normalized_news );
				update_option( 'tradepress_news_last_imported', current_time( 'timestamp' ) );
				update_option( 'tradepress_news_data_source', $provider_id );

				delete_option( 'tradepress_data_import_error_state' );

				$this->log_process_activity(
					'info',
					'News data imported successfully',
					array(
						'provider'   => $provider_id,
						'data_count' => count( $normalized_news ),
					)
				);

				return false;
			}

			if ( $last_error instanceof WP_Error ) {
				$this->handle_api_error( 'news', $last_error, $retry_count, $max_retries );
			}

			return $this->handle_retry( $item, $retry_count, $max_retries, 'news_fetch_failed' );
		} catch ( Exception $e ) {
			$this->log_process_activity(
				'error',
				'Exception in news data fetch',
				array(
					'error' => $e->getMessage(),
					'retry' => $retry_count,
				)
			);

			return $this->handle_retry( $item, $retry_count, $max_retries, 'news_exception' );
		}
	}

	/**
	 * Get enabled news providers in priority order.
	 *
	 * @return array
	 */
	private function get_news_provider_order() {
		if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
		}

		$ranked    = TradePress_API_Usage_Tracker::get_ranked_providers_for_data( 'news' );
		$providers = array();

		foreach ( $ranked as $candidate ) {
			if ( ! empty( $candidate['provider_id'] ) ) {
				$providers[] = sanitize_key( (string) $candidate['provider_id'] );
			}
		}

		// Keep a deterministic fallback order if ranking yields no provider.
		if ( empty( $providers ) ) {
			if ( 'yes' === get_option( 'TradePress_switch_alpaca_api_services', 'no' ) ) {
				$providers[] = 'alpaca';
			}

			if ( 'yes' === get_option( 'TradePress_switch_alphavantage_api_services', 'no' ) ) {
				$providers[] = 'alphavantage';
			}
		}

		return array_values( array_unique( $providers ) );
	}

	/**
	 * Get enabled earnings providers in runtime-health order.
	 *
	 * @return array
	 */
	private function get_earnings_provider_order() {
		if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
		}

		$ranked    = TradePress_API_Usage_Tracker::get_ranked_providers_for_data( 'earnings' );
		$providers = array();

		foreach ( $ranked as $candidate ) {
			if ( ! empty( $candidate['provider_id'] ) ) {
				$providers[] = sanitize_key( (string) $candidate['provider_id'] );
			}
		}

		if ( empty( $providers ) && 'yes' === get_option( 'TradePress_switch_alphavantage_api_services', 'no' ) ) {
			$providers[] = 'alphavantage';
		}

		return array_values( array_unique( $providers ) );
	}

	/**
	 * Get enabled economic-calendar providers in runtime-health order.
	 *
	 * @return array
	 */
	private function get_economic_calendar_provider_order() {
		if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
		}

		$ranked    = TradePress_API_Usage_Tracker::get_ranked_providers_for_data( 'economic_calendar' );
		$providers = array();

		foreach ( $ranked as $candidate ) {
			if ( ! empty( $candidate['provider_id'] ) ) {
				$providers[] = sanitize_key( (string) $candidate['provider_id'] );
			}
		}

		if ( empty( $providers ) && 'yes' === get_option( 'TradePress_switch_fmp_api_services', 'no' ) ) {
			$providers[] = 'fmp';
		}

		return array_values( array_unique( $providers ) );
	}

	/**
	 * Get enabled market-status providers in runtime-health order.
	 *
	 * @return array
	 */
	private function get_market_status_provider_order() {
		if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
		}

		$ranked    = TradePress_API_Usage_Tracker::get_ranked_providers_for_data( 'market_status' );
		$providers = array();

		foreach ( $ranked as $candidate ) {
			if ( ! empty( $candidate['provider_id'] ) ) {
				$providers[] = sanitize_key( (string) $candidate['provider_id'] );
			}
		}

		// Deterministic fallback.
		if ( empty( $providers ) && 'yes' === get_option( 'TradePress_switch_alphavantage_api_services', 'no' ) ) {
			$providers[] = 'alphavantage';
		}

		return array_values( array_unique( $providers ) );
	}

	/**
	 * Request earnings data using provider-compatible argument shape.
	 *
	 * @param mixed  $api API instance.
	 * @param string $provider_id Provider ID.
	 * @return array|WP_Error
	 */
	private function request_earnings_calendar_for_provider( $api, $provider_id ) {
		$from = gmdate( 'Y-m-d', strtotime( '-3 months' ) );
		$to   = gmdate( 'Y-m-d', strtotime( '+3 months' ) );

		if ( 'alphavantage' === $provider_id ) {
			return $api->get_earnings_calendar();
		}

		return $api->get_earnings_calendar( $from, $to );
	}

	/**
	 * Add item to queue instead of processing immediately
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $action
	 * @param array $data
	 * @param int   $priority
	 */
	public static function queue_data_fetch( $action, $data = array(), $priority = 10 ) {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';

		$item_data = array_merge( $data, array( 'action' => $action ) );

		$queued = TradePress_Queue_Schema::add_item(
			'data_import',
			$action,
			$item_data,
			$priority
		);

		if ( $queued ) {
			update_option( 'tradepress_data_import_status', 'running' );
			update_option( 'tradepress_data_import_start_time', current_time( 'timestamp' ) );

			self::schedule_db_queue_processing();
		}

		return $queued;
	}

	/**
	 * Schedule processing for the database-backed import queue.
	 *
	 * @return void
	 */
	private static function schedule_db_queue_processing() {
		if ( ! wp_next_scheduled( 'tradepress_process_data_import_queue' ) ) {
			wp_schedule_single_event( time() + 1, 'tradepress_process_data_import_queue' );
		}
	}

	/**
	 * Process database-backed import queue items from WP-Cron.
	 *
	 * @return void
	 */
	public static function process_scheduled_db_queue() {
		$process   = new self();
		$processed = $process->process_queue_items();

		if ( $processed > 0 && TradePress_Queue_Schema::has_active_queue( 'data_import' ) ) {
			self::schedule_db_queue_processing();
			return;
		}

		if ( ! TradePress_Queue_Schema::has_active_queue( 'data_import' ) ) {
			$process->complete();
		}
	}

	/**
	 * Fetch earnings calendar data
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item
	 */
	private function fetch_earnings_data( $item ) {
		$retry_count = isset( $item['retry_count'] ) ? $item['retry_count'] : 0;
		$max_retries = 3;

		try {
			$this->log_process_activity( 'info', 'Attempting to fetch earnings data', array( 'retry' => $retry_count ) );

			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';

			$provider_order = $this->get_earnings_provider_order();
			$last_error     = null;

			foreach ( $provider_order as $provider_id ) {
				$api = TradePress_API_Factory::create_from_settings( $provider_id, 'paper', 'earnings' );

				if ( is_wp_error( $api ) ) {
					$last_error = $api;
					continue;
				}

				if ( ! method_exists( $api, 'get_earnings_calendar' ) ) {
					$last_error = new WP_Error( 'unsupported_earnings_provider', $provider_id . ' does not expose get_earnings_calendar().' );
					continue;
				}

				$earnings_data = $this->request_earnings_calendar_for_provider( $api, $provider_id );

				if ( is_wp_error( $earnings_data ) ) {
					$last_error = $earnings_data;
					continue;
				}

				if ( ! $earnings_data ) {
					$last_error = new WP_Error( 'empty_earnings_response', $provider_id . ' returned an empty earnings response.' );
					continue;
				}

				$normalized_earnings_data = $this->normalize_earnings_records( $earnings_data );

				update_option( 'tradepress_earnings_data', $normalized_earnings_data );
				update_option( 'tradepress_earnings_last_update', current_time( 'timestamp' ) );
				update_option( 'tradepress_earnings_data_source', $provider_id );

				delete_option( 'tradepress_data_import_error_state' );

				$this->log_process_activity(
					'info',
					'Earnings data imported successfully',
					array(
						'provider'   => $provider_id,
						'data_count' => count( $normalized_earnings_data ),
					)
				);

				return false;
			}

			if ( ! ( $last_error instanceof WP_Error ) ) {
				$last_error = new WP_Error( 'earnings_provider_unavailable', 'No eligible earnings provider is currently available.' );
			}

			$this->handle_api_error( 'earnings', $last_error, $retry_count, $max_retries );
			return $this->handle_retry( $item, $retry_count, $max_retries, 'earnings_fetch_failed' );

		} catch ( Exception $e ) {
			$this->log_process_activity(
				'error',
				'Exception in earnings data fetch',
				array(
					'error' => $e->getMessage(),
					'retry' => $retry_count,
				)
			);

			return $this->handle_retry( $item, $retry_count, $max_retries, 'earnings_exception' );
		}
	}

	/**
	 * Fetch economic calendar data in the background queue.
	 *
	 * Primary provider: FMP economic_calendar.
	 * Fallback: none approved for free core.
	 *
	 * Stores normalised events in:
	 *   tradepress_economic_calendar_data        (array)
	 *   tradepress_economic_calendar_last_imported (timestamp)
	 *   tradepress_economic_calendar_data_source  (string)
	 *
	 * @param array $item Queue item.
	 * @return bool|array False on success; item array to retry on failure.
	 */
	private function fetch_economic_calendar_data( $item ) {
		$retry_count = isset( $item['retry_count'] ) ? (int) $item['retry_count'] : 0;
		$max_retries = 3;

		try {
			$this->log_process_activity(
				'info',
				'Attempting to fetch economic calendar data',
				array( 'retry' => $retry_count )
			);

			require_once TRADEPRESS_PLUGIN_DIR . 'api/api-factory.php';

			$provider_order = $this->get_economic_calendar_provider_order();
			$last_error     = null;

			// Fetch 3 months back and 3 months forward for a usable window.
			$from = gmdate( 'Y-m-d', strtotime( '-3 months' ) );
			$to   = gmdate( 'Y-m-d', strtotime( '+3 months' ) );

			foreach ( $provider_order as $provider_id ) {
				$api = TradePress_API_Factory::create_from_settings( $provider_id, 'paper', 'economic_calendar' );

				if ( is_wp_error( $api ) ) {
					$last_error = $api;
					continue;
				}

				if ( ! method_exists( $api, 'get_economic_calendar' ) ) {
					$last_error = new WP_Error( 'unsupported_economic_calendar_provider', $provider_id . ' does not expose get_economic_calendar().' );
					continue;
				}

				$raw_events = $api->get_economic_calendar( $from, $to );

				if ( is_wp_error( $raw_events ) ) {
					$last_error = $raw_events;
					continue;
				}

				$normalized = $this->normalize_economic_calendar_records( $raw_events );

				update_option( 'tradepress_economic_calendar_data', $normalized );
				update_option( 'tradepress_economic_calendar_last_imported', current_time( 'timestamp' ) );
				update_option( 'tradepress_economic_calendar_data_source', $provider_id );

				delete_option( 'tradepress_data_import_error_state' );

				$this->log_process_activity(
					'info',
					'Economic calendar data imported successfully',
					array(
						'provider'   => $provider_id,
						'data_count' => count( $normalized ),
					)
				);

				return false;
			}

			if ( ! ( $last_error instanceof WP_Error ) ) {
				$last_error = new WP_Error( 'economic_calendar_provider_unavailable', 'No eligible economic calendar provider is currently available.' );
			}

			$this->handle_api_error( 'economic_calendar', $last_error, $retry_count, $max_retries );
			return $this->handle_retry( $item, $retry_count, $max_retries, 'economic_calendar_fetch_failed' );

		} catch ( Exception $e ) {
			$this->log_process_activity(
				'error',
				'Exception in economic calendar data fetch',
				array(
					'error' => $e->getMessage(),
					'retry' => $retry_count,
				)
			);

			return $this->handle_retry( $item, $retry_count, $max_retries, 'economic_calendar_exception' );
		}
	}

	/**
	 * Normalise raw FMP economic calendar records to the Research Economic Calendar shape.
	 *
	 * FMP record keys: date, country, event, currency, previous, estimate, actual, change, changePercentage, impact
	 *
	 * @param mixed $records Raw provider response.
	 * @return array
	 */
	private function normalize_economic_calendar_records( $records ) {
		if ( ! is_array( $records ) ) {
			return array();
		}

		// FMP wraps results in an outer array sometimes.
		if ( isset( $records['economicCalendar'] ) && is_array( $records['economicCalendar'] ) ) {
			$records = $records['economicCalendar'];
		}

		$importance_map = array(
			'High'   => 'high',
			'Medium' => 'medium',
			'Low'    => 'low',
			'high'   => 'high',
			'medium' => 'medium',
			'low'    => 'low',
			'3'      => 'high',
			'2'      => 'medium',
			'1'      => 'low',
		);

		$normalized = array();

		foreach ( $records as $record ) {
			if ( ! is_array( $record ) ) {
				continue;
			}

			$raw_date = isset( $record['date'] ) ? (string) $record['date'] : '';
			if ( '' === $raw_date ) {
				continue;
			}

			// date may be "YYYY-MM-DD HH:MM:SS" — split to date and time.
			$date_parts = explode( ' ', $raw_date );
			$date       = $date_parts[0];
			$time       = isset( $date_parts[1] ) ? substr( $date_parts[1], 0, 5 ) : 'TBA';

			$raw_impact = (string) ( $record['impact'] ?? ( $record['changePercentage'] ?? 'low' ) );
			$importance = $importance_map[ $raw_impact ] ?? 'medium';

			$raw_country = strtolower( (string) ( $record['country'] ?? '' ) );
			// Map country codes to the region codes the view expects.
			$region_map = array(
				'us'             => 'us',
				'united states'  => 'us',
				'eu'             => 'eu',
				'euro area'      => 'eu',
				'eurozone'       => 'eu',
				'gb'             => 'uk',
				'uk'             => 'uk',
				'united kingdom' => 'uk',
				'jp'             => 'jp',
				'japan'          => 'jp',
				'ca'             => 'ca',
				'canada'         => 'ca',
				'au'             => 'au',
				'australia'      => 'au',
			);
			$region     = $region_map[ $raw_country ] ?? $raw_country;

			$normalized[] = array(
				'date'        => sanitize_text_field( $date ),
				'time'        => sanitize_text_field( $time ),
				'title'       => sanitize_text_field( (string) ( $record['event'] ?? '' ) ),
				'description' => '',
				'region'      => sanitize_text_field( $region ),
				'importance'  => $importance,
				'forecast'    => isset( $record['estimate'] ) ? sanitize_text_field( (string) $record['estimate'] ) : '',
				'previous'    => isset( $record['previous'] ) ? sanitize_text_field( (string) $record['previous'] ) : '',
				'actual'      => isset( $record['actual'] ) ? sanitize_text_field( (string) $record['actual'] ) : '',
				'currency'    => sanitize_text_field( (string) ( $record['currency'] ?? '' ) ),
			);
		}

		// Sort ascending by date + time.
		usort(
			$normalized,
			function ( $a, $b ) {
				return strcmp( $a['date'] . ' ' . $a['time'], $b['date'] . ' ' . $b['time'] );
			}
		);

		return $normalized;
	}

	/**
	 * Fetch price data for symbols
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item
	 */
	private function fetch_price_data( $item ) {
		$retry_count = isset( $item['retry_count'] ) ? $item['retry_count'] : 0;
		$max_retries = 3;

		try {
			$symbols = isset( $item['symbols'] ) ? $item['symbols'] : $this->get_active_symbols();

			if ( empty( $symbols ) ) {
				$this->log_process_activity( 'warning', 'No symbols found for price data fetch' );
				return false;
			}

			$this->log_process_activity(
				'info',
				'Fetching price data',
				array(
					'symbol_count' => count( $symbols ),
					'retry'        => $retry_count,
				)
			);

			// Use existing API Factory for multi-provider support
			require_once TRADEPRESS_PLUGIN_DIR . 'api/api-factory.php';
			require_once TRADEPRESS_PLUGIN_DIR . 'includes/data-freshness-manager.php';

			$successful_updates = 0;
			$failed_updates     = 0;

			foreach ( $symbols as $symbol ) {
				try {
					// Use Data Freshness Manager for coordinated data updates
					$freshness_result = TradePress_Data_Freshness_Manager::ensure_data_freshness(
						$symbol,
						'scoring_algorithms',
						array( 'price_data', 'technical_indicators' ),
						true // Force update for background process
					);

					if ( ! empty( $freshness_result['update_results']['updates'] ) ) {
						++$successful_updates;

						// Also fetch historical data if Finnhub is available
						$this->fetch_historical_data( $symbol );

					} else {
						++$failed_updates;
						$this->log_process_activity( 'warning', "Failed to fetch price for symbol: {$symbol}" );
					}

					// Respect API rate limits
					sleep( 1 );

				} catch ( Exception $symbol_error ) {
					++$failed_updates;
					$this->log_process_activity( 'error', "Exception fetching price for {$symbol}", array( 'error' => $symbol_error->getMessage() ) );
				}
			}

			// Log results
			$this->log_process_activity(
				'info',
				'Price data fetch completed',
				array(
					'successful' => $successful_updates,
					'failed'     => $failed_updates,
				)
			);

			// Clear error state if mostly successful
			if ( $successful_updates > $failed_updates ) {
				delete_option( 'tradepress_data_import_error_state' );
			}

			return false; // Task completed

		} catch ( Exception $e ) {
			$this->log_process_activity(
				'error',
				'Exception in price data fetch',
				array(
					'error' => $e->getMessage(),
					'retry' => $retry_count,
				)
			);

			return $this->handle_retry( $item, $retry_count, $max_retries, 'price_fetch_exception' );
		}
	}

	/**
	 * Fetch historical data for advanced directive support
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $symbol Symbol ticker.
	 */
	private function fetch_historical_data( $symbol ) {
		try {
			// Check if historical data is stale (older than 1 day).
			$last_update = get_option( "tradepress_historical_last_update_{$symbol}", 0 );
			if ( ( current_time( 'timestamp' ) - $last_update ) < DAY_IN_SECONDS ) {
				return; // Data is fresh.
			}

			$historical_provider = $this->create_historical_analysis_api();

			if ( ! is_wp_error( $historical_provider ) ) {
				$historical_api = $historical_provider['api'];
				$provider_id    = $historical_provider['provider_id'];

				// Fetch historical candles.
				$historical_data = $historical_api->get_candles( $symbol, 'D', null, null, 200 );

				if ( ! is_wp_error( $historical_data ) && ! empty( $historical_data ) ) {
					update_option( "tradepress_historical_data_{$symbol}", $historical_data );
					update_option( "tradepress_historical_last_update_{$symbol}", current_time( 'timestamp' ) );
					update_option( "tradepress_historical_data_source_{$symbol}", $provider_id );

					$this->log_process_activity(
						'info',
						"Historical data updated for {$symbol}",
						array(
							'data_points' => count( $historical_data ),
							'provider'    => $provider_id,
						)
					);
				}

				// Fetch technical indicators.
				$rsi_data  = $historical_api->get_rsi( $symbol );
				$macd_data = $historical_api->get_macd( $symbol );
				$ma_20     = $historical_api->get_moving_average( $symbol, 20 );
				$ma_50     = $historical_api->get_moving_average( $symbol, 50 );
				$ma_200    = $historical_api->get_moving_average( $symbol, 200 );

				$technical_indicators = array();
				if ( ! is_wp_error( $rsi_data ) ) {
					$technical_indicators['rsi'] = $rsi_data;
				}
				if ( ! is_wp_error( $macd_data ) ) {
					$technical_indicators['macd'] = $macd_data;
				}
				if ( ! is_wp_error( $ma_20 ) ) {
					$technical_indicators['sma_20'] = $ma_20;
				}
				if ( ! is_wp_error( $ma_50 ) ) {
					$technical_indicators['sma_50'] = $ma_50;
				}
				if ( ! is_wp_error( $ma_200 ) ) {
					$technical_indicators['sma_200'] = $ma_200;
				}

				if ( ! empty( $technical_indicators ) ) {
					update_option( "tradepress_technical_indicators_{$symbol}", $technical_indicators );
					update_option( "tradepress_technical_last_update_{$symbol}", current_time( 'timestamp' ) );
					update_option( "tradepress_technical_indicators_source_{$symbol}", $provider_id );

					$this->log_process_activity(
						'info',
						"Technical indicators updated for {$symbol}",
						array(
							'indicators' => array_keys( $technical_indicators ),
							'provider'   => $provider_id,
						)
					);
				}
			} else {
				$this->log_process_activity(
					'warning',
					"No historical analysis provider available for {$symbol}",
					array(
						'error' => $historical_provider->get_error_message(),
					)
				);
			}
		} catch ( Exception $e ) {
			$this->log_process_activity(
				'warning',
				"Failed to fetch historical data for {$symbol}",
				array(
					'error' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Create a provider that satisfies the historical analysis import contract.
	 *
	 * The current queue path requires provider methods that return normalised daily
	 * candles plus RSI, MACD, and SMA values with Finnhub-compatible method names.
	 *
	 * @return array|WP_Error
	 */
	private function create_historical_analysis_api() {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';

		if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
		}

		$provider_order = $this->get_historical_analysis_provider_order();
		$last_error     = null;

		foreach ( $provider_order as $provider_id ) {
			if ( 'yes' !== get_option( "TradePress_switch_{$provider_id}_api_services", 'no' ) ) {
				TradePress_API_Usage_Tracker::log_failover_event( 'historical_analysis', $provider_id, 'disabled' );
				continue;
			}

			if ( ! $this->provider_supports_historical_analysis_capabilities( $provider_id ) ) {
				TradePress_API_Usage_Tracker::log_failover_event( 'historical_analysis', $provider_id, 'unsupported_historical_analysis_capabilities' );
				continue;
			}

			if ( TradePress_API_Usage_Tracker::is_likely_rate_limited( $provider_id ) ) {
				TradePress_API_Usage_Tracker::log_failover_event( 'historical_analysis', $provider_id, 'rate_limited' );
				continue;
			}

			$api = TradePress_API_Factory::create_from_settings( $provider_id, 'paper' );

			if ( is_wp_error( $api ) ) {
				$last_error = $api;
				TradePress_API_Usage_Tracker::log_failover_event( 'historical_analysis', $provider_id, $api->get_error_code() );
				continue;
			}

			if ( ! $this->api_supports_historical_analysis_methods( $api ) ) {
				$last_error = new WP_Error( 'unsupported_historical_analysis_contract', $provider_id . ' does not expose the historical analysis import methods.' );
				TradePress_API_Usage_Tracker::log_failover_event( 'historical_analysis', $provider_id, 'unsupported_historical_analysis_methods' );
				continue;
			}

			return array(
				'provider_id' => $provider_id,
				'api'         => $api,
			);
		}

		if ( $last_error instanceof WP_Error ) {
			return $last_error;
		}

		return new WP_Error( 'no_historical_analysis_provider', 'No configured provider satisfies the historical analysis import contract.' );
	}

	/**
	 * Get candidate providers for historical candles and technical indicators.
	 *
	 * @return array
	 */
	private function get_historical_analysis_provider_order() {
		if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
		}

		$providers = array();

		foreach ( array( 'candles', 'rsi', 'macd', 'sma' ) as $data_type ) {
			foreach ( TradePress_API_Usage_Tracker::get_ranked_providers_for_data( $data_type ) as $candidate ) {
				if ( ! empty( $candidate['provider_id'] ) ) {
					$providers[] = sanitize_key( (string) $candidate['provider_id'] );
				}
			}
		}

		$providers = array_merge(
			$providers,
			array( 'finnhub', 'alphavantage', 'alpaca', 'fmp', 'eodhd', 'tradingview', 'polygon', 'twelvedata' )
		);

		return array_values( array_unique( array_filter( $providers ) ) );
	}

	/**
	 * Check the capability matrix portion of the historical analysis contract.
	 *
	 * @param string $provider_id Provider identifier.
	 * @return bool
	 */
	private function provider_supports_historical_analysis_capabilities( $provider_id ) {
		foreach ( array( 'candles', 'rsi', 'macd', 'sma' ) as $data_type ) {
			if ( ! TradePress_API_Usage_Tracker::provider_supports_data_type( $provider_id, $data_type ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check the method-shape portion of the historical analysis contract.
	 *
	 * @param mixed $api API instance.
	 * @return bool
	 */
	private function api_supports_historical_analysis_methods( $api ) {
		foreach ( array( 'get_candles', 'get_rsi', 'get_macd', 'get_moving_average' ) as $method_name ) {
			if ( ! method_exists( $api, $method_name ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Fetch market status
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item
	 */
	private function fetch_market_status( $item ) {
		$retry_count = isset( $item['retry_count'] ) ? $item['retry_count'] : 0;
		$max_retries = 3;

		try {
			$this->log_process_activity( 'info', 'Fetching market status', array( 'retry' => $retry_count ) );

			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-factory.php';

			$provider_order = $this->get_market_status_provider_order();
			$attempted      = array();
			$last_error     = null;

			foreach ( $provider_order as $provider_id ) {
				$api = TradePress_API_Factory::create_from_settings( $provider_id, 'live', 'market_status' );

				if ( is_wp_error( $api ) ) {
					if ( ! empty( $attempted ) ) {
						TradePress_API_Usage_Tracker::log_failover_event( 'market_status', $provider_id, 'factory_error', '' );
					}
					$last_error  = $api;
					$attempted[] = $provider_id;
					continue;
				}

				if ( ! method_exists( $api, 'get_market_status' ) ) {
					if ( ! empty( $attempted ) ) {
						TradePress_API_Usage_Tracker::log_failover_event( 'market_status', $provider_id, 'method_missing', '' );
					}
					$last_error  = new WP_Error( 'unsupported_market_status_provider', $provider_id . ' does not expose get_market_status().' );
					$attempted[] = $provider_id;
					continue;
				}

				$market_status = $api->get_market_status();

				if ( is_wp_error( $market_status ) || ! $market_status ) {
					if ( ! empty( $attempted ) ) {
						TradePress_API_Usage_Tracker::log_failover_event( 'market_status', $provider_id, 'api_error', '' );
					}
					$last_error  = is_wp_error( $market_status ) ? $market_status : new WP_Error( 'empty_market_status', 'Empty market status from ' . $provider_id );
					$attempted[] = $provider_id;
					continue;
				}

				// Log failover if this was not the first provider.
				if ( ! empty( $attempted ) ) {
					foreach ( $attempted as $skipped_id ) {
						TradePress_API_Usage_Tracker::log_failover_event( 'market_status', $skipped_id, 'failed_over', $provider_id );
					}
				}

				update_option( 'tradepress_market_status', $market_status );
				update_option( 'tradepress_market_status_last_update', current_time( 'timestamp' ) );
				update_option( 'tradepress_market_status_data_source', $provider_id );

				delete_option( 'tradepress_data_import_error_state' );

				$this->log_process_activity(
					'info',
					'Market status updated successfully',
					array( 'provider' => $provider_id )
				);
				return false;
			}

			if ( $last_error instanceof WP_Error ) {
				$this->handle_api_error( 'market_status', $last_error, $retry_count, $max_retries );
			}

			return $this->handle_retry( $item, $retry_count, $max_retries, 'market_status_failed' );

		} catch ( Exception $e ) {
			$this->log_process_activity(
				'error',
				'Exception in market status fetch',
				array(
					'error' => $e->getMessage(),
					'retry' => $retry_count,
				)
			);

			return $this->handle_retry( $item, $retry_count, $max_retries, 'market_status_exception' );
		}
	}

	/**
	 * Get active symbols for price updates
	 *
	 * @version 1.0.0
	 */
	private function get_active_symbols() {
		$symbols = get_posts(
			array(
				'post_type'      => 'symbols',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);

		$symbol_codes = array();
		foreach ( $symbols as $symbol_id ) {
			$symbol_code = get_post_meta( $symbol_id, 'symbol_code', true );
			if ( $symbol_code ) {
				$symbol_codes[] = $symbol_code;
			}
		}

		return $symbol_codes;
	}

	/**
	 * Normalize earnings records to include stable display fields.
	 *
	 * @param mixed $records
	 * @return array
	 */
	private function normalize_earnings_records( $records ) {
		if ( ! is_array( $records ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $records as $record ) {
			if ( ! is_array( $record ) ) {
				continue;
			}

			$normalized[] = $this->normalize_earnings_record( $record );
		}

		return $normalized;
	}

	/**
	 * Normalize provider news records to the Research News Feed shape.
	 *
	 * @param mixed  $records Raw provider response.
	 * @param string $provider_id Provider identifier.
	 * @return array
	 */
	private function normalize_news_records( $records, $provider_id ) {
		if ( isset( $records['news'] ) && is_array( $records['news'] ) ) {
			$records = $records['news'];
		} elseif ( isset( $records['feed'] ) && is_array( $records['feed'] ) ) {
			$records = $records['feed'];
		}

		if ( ! is_array( $records ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $records as $record ) {
			if ( ! is_array( $record ) ) {
				continue;
			}

			$normalized_record = $this->normalize_news_record( $record, $provider_id );
			if ( ! empty( $normalized_record['title'] ) || ! empty( $normalized_record['message'] ) ) {
				$normalized[] = $normalized_record;
			}
		}

		return $normalized;
	}

	/**
	 * Normalize a single provider news record.
	 *
	 * @param array  $record Raw provider record.
	 * @param string $provider_id Provider identifier.
	 * @return array
	 */
	private function normalize_news_record( $record, $provider_id ) {
		$source_name = $record['source'] ?? ( $record['source_name'] ?? ( 'alpaca' === $provider_id ? 'Alpaca News' : 'Alpha Vantage News' ) );
		$title       = $record['headline'] ?? ( $record['title'] ?? '' );
		$message     = $record['summary'] ?? ( $record['message'] ?? '' );
		$link        = $record['url'] ?? ( $record['link'] ?? '' );
		$image_url   = $record['banner_image'] ?? ( $record['image_url'] ?? '' );
		if ( empty( $image_url ) && isset( $record['images'] ) && is_array( $record['images'] ) && isset( $record['images'][0] ) && is_array( $record['images'][0] ) ) {
			$image_url = $record['images'][0]['url'] ?? '';
		}
		$symbols = $record['symbols'] ?? array();

		if ( empty( $symbols ) && ! empty( $record['ticker_sentiment'] ) && is_array( $record['ticker_sentiment'] ) ) {
			foreach ( $record['ticker_sentiment'] as $ticker_sentiment ) {
				if ( ! empty( $ticker_sentiment['ticker'] ) ) {
					$symbols[] = $ticker_sentiment['ticker'];
				}
			}
		}

		if ( is_string( $symbols ) ) {
			$symbols = array_map( 'trim', explode( ',', $symbols ) );
		}

		$published_at = $record['created_at'] ?? ( $record['time_published'] ?? ( $record['published_at'] ?? '' ) );
		$timestamp    = $this->normalize_news_timestamp( $published_at );
		$sentiment    = $this->normalize_news_sentiment( $record );

		return array(
			'id'           => md5( $provider_id . '|' . $title . '|' . $link . '|' . $timestamp ),
			'source_type'  => 'news',
			'source_name'  => sanitize_text_field( (string) $source_name ),
			'source_icon'  => 'dashicons dashicons-media-document',
			'title'        => sanitize_text_field( (string) $title ),
			'message'      => wp_strip_all_tags( (string) $message ),
			'symbols'      => array_values( array_filter( array_map( 'sanitize_text_field', $symbols ) ) ),
			'image_url'    => esc_url_raw( (string) $image_url ),
			'link'         => esc_url_raw( (string) $link ),
			'sentiment'    => $sentiment,
			'published_at' => $timestamp,
			'time_ago'     => $this->format_time_ago( $timestamp ),
			'provider'     => $provider_id,
		);
	}

	/**
	 * Convert provider news timestamps to a Unix timestamp.
	 *
	 * @param mixed $published_at Provider timestamp.
	 * @return int
	 */
	private function normalize_news_timestamp( $published_at ) {
		if ( is_numeric( $published_at ) ) {
			return (int) $published_at;
		}

		if ( is_string( $published_at ) && '' !== $published_at ) {
			$alpha_vantage_time = preg_match( '/^\d{8}T\d{6}$/', $published_at )
				? DateTime::createFromFormat( 'Ymd\THis', $published_at, new DateTimeZone( 'UTC' ) )
				: false;

			if ( $alpha_vantage_time instanceof DateTime ) {
				return $alpha_vantage_time->getTimestamp();
			}

			$timestamp = strtotime( $published_at );
			if ( false !== $timestamp ) {
				return $timestamp;
			}
		}

		return current_time( 'timestamp' );
	}

	/**
	 * Normalize provider sentiment to positive, negative, or neutral.
	 *
	 * @param array $record Raw provider record.
	 * @return string
	 */
	private function normalize_news_sentiment( $record ) {
		$label = strtolower( (string) ( $record['overall_sentiment_label'] ?? ( $record['sentiment'] ?? '' ) ) );

		if ( false !== strpos( $label, 'bullish' ) || false !== strpos( $label, 'positive' ) ) {
			return 'positive';
		}

		if ( false !== strpos( $label, 'bearish' ) || false !== strpos( $label, 'negative' ) ) {
			return 'negative';
		}

		if ( isset( $record['overall_sentiment_score'] ) && is_numeric( $record['overall_sentiment_score'] ) ) {
			$score = (float) $record['overall_sentiment_score'];
			if ( $score > 0.15 ) {
				return 'positive';
			}

			if ( $score < -0.15 ) {
				return 'negative';
			}
		}

		return 'neutral';
	}

	/**
	 * Format a timestamp as a compact relative age label.
	 *
	 * @param int $timestamp Unix timestamp.
	 * @return string
	 */
	private function format_time_ago( $timestamp ) {
		$age = max( 0, current_time( 'timestamp' ) - (int) $timestamp );

		if ( $age < HOUR_IN_SECONDS ) {
			return sprintf(
				/* translators: %d: number of minutes. */
				_n( '%d minute ago', '%d minutes ago', max( 1, (int) floor( $age / MINUTE_IN_SECONDS ) ), 'tradepress' ),
				max( 1, (int) floor( $age / MINUTE_IN_SECONDS ) )
			);
		}

		if ( $age < DAY_IN_SECONDS ) {
			return sprintf(
				/* translators: %d: number of hours. */
				_n( '%d hour ago', '%d hours ago', (int) floor( $age / HOUR_IN_SECONDS ), 'tradepress' ),
				(int) floor( $age / HOUR_IN_SECONDS )
			);
		}

		return sprintf(
			/* translators: %d: number of days. */
			_n( '%d day ago', '%d days ago', (int) floor( $age / DAY_IN_SECONDS ), 'tradepress' ),
			(int) floor( $age / DAY_IN_SECONDS )
		);
	}

	/**
	 * Normalize a single earnings record.
	 *
	 * @param array $record
	 * @return array
	 */
	private function normalize_earnings_record( $record ) {
		$estimate_value = $this->to_numeric_value(
			$record['estimate'] ?? ( $record['eps_estimate'] ?? null )
		);

		$previous_eps_value = $this->to_numeric_value(
			$record['previous_eps'] ?? ( $record['reportedEPS'] ?? ( $record['reported_eps'] ?? null ) )
		);

		$eps_change_percent = $this->to_numeric_value( $record['eps_change_percent'] ?? null );
		if ( null === $eps_change_percent && null !== $estimate_value && null !== $previous_eps_value && 0.0 !== $previous_eps_value ) {
			$eps_change_percent = ( ( $estimate_value - $previous_eps_value ) / abs( $previous_eps_value ) ) * 100;
		}

		if ( null === $eps_change_percent ) {
			$eps_change_percent = 0.0;
		}

		$current_price   = $this->to_numeric_value( $record['current_price'] ?? ( $record['price'] ?? null ) );
		$estimated_price = $this->to_numeric_value(
			$record['estimated_price'] ?? ( $record['price_estimate'] ?? $estimate_value )
		);

		$price_change_percent = $this->to_numeric_value( $record['price_change_percent'] ?? null );
		if ( null === $price_change_percent && null !== $current_price && null !== $estimated_price && 0.0 !== $current_price ) {
			$price_change_percent = ( ( $estimated_price - $current_price ) / $current_price ) * 100;
		}

		$opportunity_score = $this->to_numeric_value( $record['opportunity_score'] ?? ( $record['algorithm_score'] ?? null ) );
		if ( null === $opportunity_score ) {
			$opportunity_score = max( 0, min( 100, round( 50 + ( $eps_change_percent * 2 ) ) ) );
		}

		$sentiment = isset( $record['sentiment'] ) ? (string) $record['sentiment'] : '';
		if ( '' === trim( $sentiment ) ) {
			if ( $eps_change_percent > 0 ) {
				$sentiment = 'Bullish';
			} elseif ( $eps_change_percent < 0 ) {
				$sentiment = 'Bearish';
			} else {
				$sentiment = 'Neutral';
			}
		}

		$sentiment_percent = isset( $record['sentiment_percent'] ) ? (string) $record['sentiment_percent'] : '';
		if ( '' === trim( $sentiment_percent ) ) {
			$sentiment_percent = number_format( min( 100, max( 0, abs( $eps_change_percent ) ) ), 1 ) . '%';
		}

		$whisper = isset( $record['whisper'] ) ? trim( (string) $record['whisper'] ) : '';

		$normalized_record = array_merge(
			$record,
			array(
				'reportDate'           => isset( $record['reportDate'] ) ? (string) $record['reportDate'] : (string) ( $record['report_date'] ?? '' ),
				'name'                 => isset( $record['name'] ) ? (string) $record['name'] : (string) ( $record['company_name'] ?? '' ),
				'estimate'             => null !== $estimate_value ? $estimate_value : ( $record['estimate'] ?? null ),
				'previous_eps'         => null !== $previous_eps_value ? '$' . number_format( $previous_eps_value, 2 ) : (string) ( $record['previous_eps'] ?? 'N/A' ),
				'eps_change_percent'   => (float) $eps_change_percent,
				'current_price'        => $current_price,
				'estimated_price'      => $estimated_price,
				'price_change_percent' => $price_change_percent,
				'opportunity_score'    => (int) round( $opportunity_score ),
				'algorithm_score'      => (int) round( $opportunity_score ),
				'sentiment'            => $sentiment,
				'sentiment_percent'    => $sentiment_percent,
				'whisper'              => $whisper,
			)
		);

		return $normalized_record;
	}

	/**
	 * Convert scalar values to numeric floats where possible.
	 *
	 * @param mixed $value
	 * @return float|null
	 */
	private function to_numeric_value( $value ) {
		if ( is_int( $value ) || is_float( $value ) ) {
			return (float) $value;
		}

		if ( is_string( $value ) ) {
			$normalized = preg_replace( '/[^0-9.\-]/', '', $value );
			if ( '' !== $normalized && is_numeric( $normalized ) ) {
				return (float) $normalized;
			}
		}

		return null;
	}

	/**
	 * Enhanced error handling methods
	 */

	/**
	 * Log process activity using TradePress Logger
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $level
	 * @param mixed $message
	 * @param array $context
	 */
	private function log_process_activity( $level, $message, $context = array() ) {
		if ( class_exists( 'TradePress_Logger' ) ) {
			$logger = new TradePress_Logger();
			$logger->log( $level, $message, TradePress_Logger::CAT_API, $context );
		}
	}

	/**
	 * Handle API errors with detailed logging
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $endpoint
	 * @param mixed $wp_error
	 * @param mixed $retry_count
	 * @param mixed $max_retries
	 */
	private function handle_api_error( $endpoint, $wp_error, $retry_count, $max_retries ) {
		$error_data = array(
			'endpoint'      => $endpoint,
			'error_code'    => $wp_error->get_error_code(),
			'error_message' => $wp_error->get_error_message(),
			'retry_count'   => $retry_count,
			'max_retries'   => $max_retries,
		);

		// Log to API logging system
		if ( class_exists( 'TradePress_API_Logging' ) ) {
			$entry_id = TradePress_API_Logging::log_call( 'alphavantage', $endpoint, 'GET', 'error' );
			TradePress_API_Logging::log_error( $entry_id, $wp_error->get_error_code(), $wp_error->get_error_message() );
		}

		// Set error state for UI display
		$this->set_error_state( $endpoint, $error_data );

		$this->log_process_activity( 'error', "API error for {$endpoint}", $error_data );
	}

	/**
	 * Handle retry logic with exponential backoff
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item
	 * @param mixed $retry_count
	 * @param mixed $max_retries
	 * @param mixed $error_type
	 */
	private function handle_retry( $item, $retry_count, $max_retries, $error_type ) {
		if ( $retry_count >= $max_retries ) {
			$this->log_process_activity(
				'critical',
				"Max retries exceeded for {$error_type}",
				array(
					'retry_count' => $retry_count,
					'max_retries' => $max_retries,
				)
			);

			// Set permanent error state
			$this->set_error_state(
				$error_type,
				array(
					'status'       => 'failed',
					'retry_count'  => $retry_count,
					'last_attempt' => current_time( 'mysql' ),
				)
			);

			return false; // Stop retrying
		}

		// Increment retry count and add exponential backoff delay
		$item['retry_count'] = $retry_count + 1;
		$item['retry_delay'] = min( 300, pow( 2, $retry_count ) * 10 ); // Max 5 minutes

		$this->log_process_activity(
			'warning',
			"Scheduling retry for {$error_type}",
			array(
				'retry_count' => $item['retry_count'],
				'delay'       => $item['retry_delay'],
			)
		);

		return $item; // Retry the task
	}

	/**
	 * Set error state for UI display
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $error_type
	 * @param mixed $error_data
	 */
	private function set_error_state( $error_type, $error_data ) {
		$error_states                = get_option( 'tradepress_data_import_error_state', array() );
		$error_states[ $error_type ] = array_merge(
			$error_data,
			array(
				'timestamp' => current_time( 'mysql' ),
				'process'   => 'data_import',
			)
		);
		update_option( 'tradepress_data_import_error_state', $error_states );
	}

	/**
	 * Get process health status
	 *
	 * @version 1.0.0
	 */
	public function get_health_status() {
		$error_states = get_option( 'tradepress_data_import_error_state', array() );
		$last_run     = get_option( 'tradepress_data_import_last_run', 0 );
		$status       = get_option( 'tradepress_data_import_status', 'stopped' );

		$health = array(
			'status'       => $status,
			'last_run'     => $last_run,
			'errors'       => $error_states,
			'health_score' => $this->calculate_health_score( $error_states, $last_run ),
		);

		return $health;
	}

	/**
	 * Calculate health score (0-100)
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $error_states
	 * @param mixed $last_run
	 */
	private function calculate_health_score( $error_states, $last_run ) {
		$score = 100;

		// Deduct points for errors
		foreach ( $error_states as $error ) {
			if ( isset( $error['status'] ) && 'failed' === $error['status'] ) {
				$score -= 20;
			} else {
				$score -= 10;
			}
		}

		// Deduct points for stale data
		if ( $last_run ) {
			$hours_since_run = ( current_time( 'timestamp' ) - $last_run ) / 3600;
			if ( $hours_since_run > 24 ) {
				$score -= 30;
			} elseif ( $hours_since_run > 6 ) {
				$score -= 15;
			}
		} else {
			$score -= 50; // Never run
		}

		return max( 0, $score );
	}

	/**
	 * Fetch technical data for symbols
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $item
	 */
	private function fetch_technical_data( $item ) {
		$retry_count = isset( $item['retry_count'] ) ? $item['retry_count'] : 0;
		$max_retries = 3;

		try {
			$symbols = isset( $item['symbols'] ) ? $item['symbols'] : $this->get_active_symbols();

			if ( empty( $symbols ) ) {
				$this->log_process_activity( 'warning', 'No symbols found for technical data fetch' );
				return false;
			}

			$successful_updates = 0;
			$failed_updates     = 0;

			foreach ( $symbols as $symbol ) {
				try {
					$this->fetch_historical_data( $symbol );
					++$successful_updates;

					// Respect API rate limits
					sleep( 2 );

				} catch ( Exception $symbol_error ) {
					++$failed_updates;
					$this->log_process_activity( 'error', "Exception fetching technical data for {$symbol}", array( 'error' => $symbol_error->getMessage() ) );
				}
			}

			$this->log_process_activity(
				'info',
				'Technical data fetch completed',
				array(
					'successful' => $successful_updates,
					'failed'     => $failed_updates,
				)
			);

			return false;

		} catch ( Exception $e ) {
			$this->log_process_activity(
				'error',
				'Exception in technical data fetch',
				array(
					'error' => $e->getMessage(),
					'retry' => $retry_count,
				)
			);

			return $this->handle_retry( $item, $retry_count, $max_retries, 'technical_fetch_exception' );
		}
	}

	/**
	 * Process queue items from database
	 *
	 * @version 1.0.0
	 */
	public function process_queue_items() {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';

		$max_items = 5; // Process up to 5 items per run
		$processed = 0;

		while ( $processed < $max_items ) {
			$item = TradePress_Queue_Schema::get_next_item( 'data_import' );

			if ( ! $item ) {
				break; // No more items
			}

			$item_data = json_decode( $item->item_data, true );

			global $wpdb;
			$table = $wpdb->prefix . 'tradepress_queue';

			$wpdb->update(
				$table,
				array(
					'status'     => 'processing',
					'started_at' => current_time( 'mysql' ),
				),
				array( 'id' => $item->id ),
				array( '%s', '%s' ),
				array( '%d' )
			);

			$result = $this->task( $item_data );

			// Update queue item status
			if ( false === $result ) {
				// Task completed successfully
				$wpdb->update(
					$table,
					array(
						'status'       => 'completed',
						'completed_at' => current_time( 'mysql' ),
					),
					array( 'id' => $item->id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
			} else {
				// Task needs retry
				$new_attempts = $item->attempts + 1;
				$new_status   = $new_attempts >= $item->max_attempts ? 'failed' : 'pending';
				$retry_delay  = isset( $item_data['retry_delay'] ) ? absint( $item_data['retry_delay'] ) : 60;

				$wpdb->update(
					$table,
					array(
						'status'        => $new_status,
						'attempts'      => $new_attempts,
						'scheduled_at'  => gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) + $retry_delay ),
						'completed_at'  => 'failed' === $new_status ? current_time( 'mysql' ) : null,
						'error_message' => isset( $item_data['error_code'] ) ? $item_data['error_code'] : '',
					),
					array( 'id' => $item->id ),
					array( '%s', '%d', '%s', '%s', '%s' ),
					array( '%d' )
				);
			}

			++$processed;
		}

		return $processed;
	}

	/**
	 * Complete processing
	 *
	 * @version 1.0.0
	 */
	protected function complete() {
		parent::complete();

		// Log completion
		$this->log_process_activity( 'info', 'Data import process completed successfully' );

		// Update status
		update_option( 'tradepress_data_import_status', 'completed' );
		update_option( 'tradepress_data_import_last_run', current_time( 'timestamp' ) );

		// Clear any temporary error states on successful completion
		$error_states = get_option( 'tradepress_data_import_error_state', array() );
		foreach ( $error_states as $key => $error ) {
			if ( ! isset( $error['status'] ) || 'failed' !== $error['status'] ) {
				unset( $error_states[ $key ] );
			}
		}
		update_option( 'tradepress_data_import_error_state', $error_states );
	}
}

add_action( 'tradepress_process_data_import_queue', array( 'TradePress_Data_Import_Process', 'process_scheduled_db_queue' ) );
