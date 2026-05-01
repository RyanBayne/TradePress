<?php
/**
 * API Usage Tracker and Fallback System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradePress_API_Usage_Tracker {

	/**
	 * Default daily limits used for health scoring and dashboard display.
	 *
	 * These are conservative estimates for free/basic plans and are only
	 * used when provider-specific limits are not explicitly configured.
	 *
	 * @var array
	 */
	private static $default_daily_limits = array(
		'alphavantage' => 25,
		'finnhub'      => 60,
		'alpaca'       => 200,
		'fmp'          => 250,
		'tradingview'  => 100,
	);

	/**
	 * Track API call usage
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $provider_id
	 * @param mixed $endpoint
	 * @param bool  $success
	 */
	public static function track_call( $provider_id, $endpoint, $success = true ) {
		$today     = date( 'Y-m-d' );
		$usage_key = "tradepress_api_usage_{$provider_id}_{$today}";

		$usage = get_option(
			$usage_key,
			array(
				'total_calls'      => 0,
				'successful_calls' => 0,
				'failed_calls'     => 0,
				'endpoints'        => array(),
				'last_call'        => null,
				'rate_limited'     => false,
			)
		);

		++$usage['total_calls'];
		if ( $success ) {
			++$usage['successful_calls'];
		} else {
			++$usage['failed_calls'];
		}

		if ( ! isset( $usage['endpoints'][ $endpoint ] ) ) {
			$usage['endpoints'][ $endpoint ] = 0;
		}
		++$usage['endpoints'][ $endpoint ];

		$usage['last_call'] = current_time( 'mysql' );

		update_option( $usage_key, $usage );
	}

	/**
	 * Mark API as rate limited
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $provider_id
	 * @param mixed $reset_time
	 */
	public static function mark_rate_limited( $provider_id, $reset_time = null ) {
		$today     = date( 'Y-m-d' );
		$usage_key = "tradepress_api_usage_{$provider_id}_{$today}";

		$usage                    = get_option( $usage_key, array() );
		$usage['rate_limited']    = true;
		$usage['rate_limit_time'] = current_time( 'mysql' );
		if ( $reset_time ) {
			$usage['rate_limit_reset'] = $reset_time;
		}

		update_option( $usage_key, $usage );

		// Developer notice
		if ( function_exists( 'tradepress_is_developer_mode' ) && tradepress_is_developer_mode() ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-notices.php';
			TradePress_Developer_Notices::api_call_notice(
				$provider_id,
				'rate_limit_detected',
				'N/A',
				array( 'message' => 'API rate limit detected, switching to fallback' )
			);
		}
	}

	/**
	 * Check if API is likely rate limited with cooling period
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $provider_id
	 */
	public static function is_likely_rate_limited( $provider_id ) {
		$today     = date( 'Y-m-d' );
		$usage_key = "tradepress_api_usage_{$provider_id}_{$today}";
		$usage     = get_option(
			$usage_key,
			array(
				'total_calls'     => 0,
				'rate_limited'    => false,
				'rate_limit_time' => null,
			)
		);

		// Check if explicitly marked as rate limited with cooling period
		if ( ! empty( $usage['rate_limited'] ) && ! empty( $usage['rate_limit_time'] ) ) {
			$limit_time     = strtotime( $usage['rate_limit_time'] );
			$cooling_period = self::get_cooling_period( $provider_id );

			// If still in cooling period, remain rate limited
			if ( time() - $limit_time < $cooling_period ) {
				return true;
			}

			// Cooling period expired - clear rate limit flag
			$usage['rate_limited']    = false;
			$usage['rate_limit_time'] = null;
			update_option( $usage_key, $usage );
		}

		// Check usage patterns for Alpha Vantage (25 calls/day limit)
		if ( $provider_id === 'alphavantage' && $usage['total_calls'] >= 23 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get best available API for data type with dynamic priority
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $data_type
	 */
	public static function get_best_api_for_data( $data_type ) {
		$ranked_providers = self::get_ranked_providers_for_data( $data_type );

		foreach ( $ranked_providers as $candidate ) {
			$provider_id = $candidate['provider_id'];

			if ( self::is_likely_rate_limited( $provider_id ) && count( $ranked_providers ) > 1 ) {
				if ( function_exists( 'tradepress_is_developer_mode' ) && tradepress_is_developer_mode() ) {
					require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-notices.php';
					TradePress_Developer_Notices::api_call_notice(
						$provider_id,
						'skipped_rate_limited',
						$data_type,
						array( 'message' => "Skipping {$provider_id} - in cooling period" )
					);
				}
				continue;
			}

			$api = TradePress_API_Factory::create_from_settings( $provider_id );
			if ( ! is_wp_error( $api ) ) {
				if ( function_exists( 'tradepress_is_developer_mode' ) && tradepress_is_developer_mode() ) {
					require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/developer-notices.php';
					TradePress_Developer_Notices::api_call_notice(
						$provider_id,
						'selected_for_fallback',
						$data_type,
						array(
							'message' => sprintf(
								'Selected %1$s for %2$s (health score: %3$d)',
								$provider_id,
								$data_type,
								(int) $candidate['health_score']
							),
						)
					);
				}
				return $api;
			}
		}

		return new WP_Error( 'no_available_api', "No available API for {$data_type}" );
	}

	/**
	 * Get a ranked provider list for a data type using configuration + runtime health.
	 *
	 * @param string $data_type Data type capability.
	 * @return array
	 */
	public static function get_ranked_providers_for_data( $data_type ) {
		$priority_candidates = self::get_data_type_provider_priority( $data_type );
		$ranked             = array();

		foreach ( $priority_candidates as $index => $provider_id ) {
			if ( get_option( "TradePress_switch_{$provider_id}_api_services", 'no' ) !== 'yes' ) {
				continue;
			}

			if ( ! self::is_provider_configured( $provider_id ) ) {
				continue;
			}

			$health = self::get_provider_runtime_health( $provider_id );

			// Keep a small preference boost for configured priority order.
			$weighted_score = (int) $health['health_score'] - ( $index * 3 );
			$ranked[]       = array(
				'provider_id'  => $provider_id,
				'health_score' => $weighted_score,
				'health_state' => $health['health_state'],
				'details'      => $health,
			);
		}

		usort(
			$ranked,
			function ( $a, $b ) {
				return (int) $b['health_score'] <=> (int) $a['health_score'];
			}
		);

		return $ranked;
	}

	/**
	 * Build provider priority order by combining configured primary/secondary APIs
	 * with capability-specific defaults.
	 *
	 * @param string $data_type Data type capability.
	 * @return array
	 */
	private static function get_data_type_provider_priority( $data_type ) {
		$primary_settings = get_option( 'tradepress_primary_apis', array() );
		$fallback_settings = get_option( 'tradepress_secondary_apis', array() );

		$defaults = array(
			'quote'                => array( 'alphavantage', 'finnhub', 'alpaca' ),
			'market_status'        => array( 'alphavantage', 'alpaca', 'finnhub' ),
			'technical_indicators' => array( 'alphavantage' ),
			'news'                 => array( 'alpaca', 'alphavantage', 'finnhub' ),
			'fundamentals'         => array( 'alphavantage', 'finnhub' ),
			'economic_calendar'    => array( 'fmp', 'tradingview' ),
			'earnings'             => array( 'alphavantage' ),
		);

		$configured = array();

			if ( in_array( $data_type, array( 'quote', 'market_status', 'technical_indicators', 'fundamentals', 'news', 'economic_calendar', 'earnings' ), true ) ) {
			if ( ! empty( $primary_settings['primary_data_only'] ) ) {
				$configured[] = sanitize_key( $primary_settings['primary_data_only'] );
			}

			if ( ! empty( $fallback_settings['secondary_data_only'] ) ) {
				$configured[] = sanitize_key( $fallback_settings['secondary_data_only'] );
			}
		}

		if ( $data_type === 'news' ) {
			if ( ! in_array( 'alpaca', $configured, true ) ) {
				array_unshift( $configured, 'alpaca' );
			}
		}

		$ordered = array_merge( $configured, $defaults[ $data_type ] ?? array( 'alphavantage' ) );

		return array_values( array_unique( array_filter( $ordered ) ) );
	}

	/**
	 * Compute runtime health for a provider from recent usage and rate-limit state.
	 *
	 * @param string $provider_id Provider identifier.
	 * @return array
	 */
	public static function get_provider_runtime_health( $provider_id ) {
		$enabled    = get_option( "TradePress_switch_{$provider_id}_api_services", 'no' ) === 'yes';
		$configured = self::is_provider_configured( $provider_id );

		$stats_today = self::get_usage_stats( $provider_id, 1 );
		$today_key   = date( 'Y-m-d' );
		$today       = isset( $stats_today[ $today_key ] ) ? $stats_today[ $today_key ] : array();

		$total_calls      = isset( $today['total_calls'] ) ? (int) $today['total_calls'] : 0;
		$successful_calls = isset( $today['successful_calls'] ) ? (int) $today['successful_calls'] : 0;
		$failed_calls     = isset( $today['failed_calls'] ) ? (int) $today['failed_calls'] : 0;
		$rate_limited     = ! empty( $today['rate_limited'] ) || self::is_likely_rate_limited( $provider_id );
		$last_call        = isset( $today['last_call'] ) ? (string) $today['last_call'] : '';
		$error_rate       = $total_calls > 0 ? ( $failed_calls / $total_calls ) : 0;
		$daily_limit      = self::get_daily_limit_for_provider( $provider_id );
		$usage_ratio      = $daily_limit > 0 ? min( 1, $total_calls / $daily_limit ) : 0;

		$score   = 100;
		$reasons = array();

		if ( ! $enabled ) {
			$score     = 0;
			$reasons[] = 'disabled';
		}

		if ( ! $configured ) {
			$score     = 0;
			$reasons[] = 'missing_credentials';
		}

		if ( $rate_limited ) {
			$score    -= 45;
			$reasons[] = 'rate_limited';
		}

		if ( $total_calls >= 5 ) {
			$score    -= (int) round( $error_rate * 55 );
			$reasons[] = 'error_rate:' . round( $error_rate * 100, 1 ) . '%';
		}

		if ( $usage_ratio >= 0.9 ) {
			$score    -= 20;
			$reasons[] = 'near_daily_limit';
		} elseif ( $usage_ratio >= 0.75 ) {
			$score    -= 10;
			$reasons[] = 'high_daily_usage';
		}

		$score = max( 0, min( 100, $score ) );

		$health_state = 'healthy';
		if ( $score < 40 ) {
			$health_state = 'unavailable';
		} elseif ( $score < 70 ) {
			$health_state = 'degraded';
		}

		return array(
			'provider_id'       => $provider_id,
			'enabled'           => $enabled,
			'configured'        => $configured,
			'health_state'      => $health_state,
			'health_score'      => $score,
			'total_calls'       => $total_calls,
			'successful_calls'  => $successful_calls,
			'failed_calls'      => $failed_calls,
			'error_rate'        => $error_rate,
			'daily_limit'       => $daily_limit,
			'usage_ratio'       => $usage_ratio,
			'rate_limited'      => $rate_limited,
			'last_call'         => $last_call,
			'health_reasons'    => $reasons,
		);
	}

	/**
	 * Check if a provider appears to have credentials configured.
	 *
	 * @param string $provider_id Provider identifier.
	 * @return bool
	 */
	private static function is_provider_configured( $provider_id ) {
		if ( ! class_exists( 'TradePress_API_Directory' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/api-directory.php';
		}

		$provider = TradePress_API_Directory::get_provider( $provider_id );
		if ( ! is_array( $provider ) ) {
			return false;
		}

		if ( isset( $provider['api_type'] ) && $provider['api_type'] === 'trading' ) {
			$paper_key    = (string) get_option( "TradePress_api_{$provider_id}_papermoney_apikey", '' );
			$paper_secret = (string) get_option( "TradePress_api_{$provider_id}_papermoney_secretkey", '' );
			$live_key     = (string) get_option( "TradePress_api_{$provider_id}_realmoney_apikey", '' );
			$live_secret  = (string) get_option( "TradePress_api_{$provider_id}_realmoney_secretkey", '' );

			return ( $paper_key !== '' && $paper_secret !== '' ) || ( $live_key !== '' && $live_secret !== '' );
		}

		$api_key = (string) get_option( "TradePress_api_{$provider_id}_key", '' );
		return $api_key !== '';
	}

	/**
	 * Get daily limit estimate for a provider.
	 *
	 * @param string $provider_id Provider identifier.
	 * @return int
	 */
	public static function get_daily_limit_for_provider( $provider_id ) {
		if ( isset( self::$default_daily_limits[ $provider_id ] ) ) {
			return (int) self::$default_daily_limits[ $provider_id ];
		}

		return 100;
	}

	/**
	 * Get cooling period for rate limited API
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $provider_id
	 */
	private static function get_cooling_period( $provider_id ) {
		$cooling_periods = array(
			'alphavantage' => 3600, // 1 hour
			'finnhub'      => 60,        // 1 minute
			'alpaca'       => 300,         // 5 minutes
		);

		return $cooling_periods[ $provider_id ] ?? 1800; // Default 30 minutes
	}

	/**
	 * Get usage statistics
	 *
	 * @version 1.0.0
	 *
	 * @param mixed $provider_id
	 * @param int   $days
	 */
	/**
	 * Log a provider failover event for audit trail.
	 *
	 * @param string $data_type      Data type being fetched.
	 * @param string $skipped        Provider that was skipped.
	 * @param string $reason         Why it was skipped.
	 * @param string $selected       Provider that was ultimately used (empty if all failed).
	 */
	public static function log_failover_event( $data_type, $skipped, $reason, $selected = '' ) {
		$events   = get_option( 'tradepress_api_failover_events', array() );
		$events[] = array(
			'ts'        => current_time( 'mysql' ),
			'data_type' => sanitize_key( $data_type ),
			'skipped'   => sanitize_key( $skipped ),
			'reason'    => sanitize_text_field( $reason ),
			'selected'  => sanitize_key( $selected ),
		);

		// Keep the 100 most recent events.
		if ( count( $events ) > 100 ) {
			$events = array_slice( $events, -100 );
		}

		update_option( 'tradepress_api_failover_events', $events );
	}

	/**
	 * Get the most recent failover events.
	 *
	 * @param int $limit Maximum events to return.
	 * @return array
	 */
	public static function get_recent_failover_events( $limit = 20 ) {
		$events = get_option( 'tradepress_api_failover_events', array() );
		$events = array_reverse( $events );
		return array_slice( $events, 0, (int) $limit );
	}

	public static function get_usage_stats( $provider_id, $days = 7 ) {
		$stats = array();

		for ( $i = 0; $i < $days; $i++ ) {
			$date      = date( 'Y-m-d', strtotime( "-{$i} days" ) );
			$usage_key = "tradepress_api_usage_{$provider_id}_{$date}";
			$usage     = get_option(
				$usage_key,
				array(
					'total_calls'      => 0,
					'successful_calls' => 0,
					'failed_calls'     => 0,
					'endpoints'        => array(),
					'last_call'        => null,
					'rate_limited'     => false,
				)
			);

			$stats[ $date ] = $usage;
		}

		return $stats;
	}
}
