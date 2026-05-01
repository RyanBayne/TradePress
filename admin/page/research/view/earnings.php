<?php
/**
 * TradePress Earnings Tab
 *
 * Displays earnings analysis tools and data for the Research page
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.4
 * @since 1.0.0
 * @created 2023-10-04 14:30
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the first non-empty option value from a list of keys.
 *
 * @param array $keys Option keys to check.
 * @return string
 */
function tradepress_earnings_get_first_option_value( $keys ) {
	foreach ( $keys as $key ) {
		$value = get_option( $key, '' );
		if ( ! empty( $value ) ) {
			return (string) $value;
		}
	}

	return '';
}

/**
 * Build provider and scheduling status information for Earnings Calendar.
 *
 * @return array
 */
function tradepress_earnings_get_provider_status() {
	$alpha_enabled = ( 'yes' === get_option( 'TradePress_switch_alphavantage_api_services', 'no' ) );
	$alpha_key     = tradepress_earnings_get_first_option_value(
		array(
			'tradepress_api_alphavantage_key',
			'TradePress_alphavantage_api_key',
			'tradepress_alphavantage_api_key',
			'TradePress_api_alphavantage_key',
		)
	);

	$cron_enabled   = (bool) get_option( 'tradepress_earnings_cron_enabled', false );
	$cron_interval  = get_option( 'tradepress_earnings_cron_interval', 'daily' );
	$next_scheduled = wp_next_scheduled( 'tradepress_fetch_earnings_calendar' );

	$last_import = (int) max(
		(int) get_option( 'tradepress_earnings_last_update', 0 ),
		(int) get_option( 'tradepress_earnings_last_updated', 0 )
	);

	$has_stored_records = tradepress_earnings_has_stored_records();
	$age_seconds        = $last_import > 0 ? current_time( 'timestamp' ) - $last_import : null;
	$queue_pending      = tradepress_earnings_has_pending_import();
	$error_states       = get_option( 'tradepress_data_import_error_state', array() );
	$runtime_health     = isset( $error_states['earnings'] ) || isset( $error_states['earnings_fetch_failed'] ) || isset( $error_states['earnings_exception'] )
		? 'failed'
		: 'healthy';

	return array(
		'alpha_enabled'        => $alpha_enabled,
		'alpha_key_configured' => ! empty( $alpha_key ),
		'cron_enabled'         => $cron_enabled,
		'cron_interval'        => $cron_interval,
		'next_scheduled'       => $next_scheduled,
		'last_import'          => $last_import,
		'age_seconds'          => $age_seconds,
		'freshness_sla'        => DAY_IN_SECONDS,
		'queue_threshold'      => 12 * HOUR_IN_SECONDS,
		'has_stored_records'   => $has_stored_records,
		'queue_pending'        => $queue_pending,
		'queued_this_request'  => false,
		'data_mode'            => tradepress_earnings_resolve_data_mode( $has_stored_records, $age_seconds, $queue_pending ),
		'runtime_health'       => $runtime_health,
	);
}

/**
 * Check whether earnings storage currently contains records.
 *
 * @return bool
 */
function tradepress_earnings_has_stored_records() {
	$raw_data = get_option( 'tradepress_earnings_data', array() );

	if ( empty( $raw_data ) ) {
		$raw_data = get_option( 'tradepress_earnings_calendar_data', array() );
	}

	return ! empty( $raw_data ) && is_array( $raw_data );
}

/**
 * Check whether an earnings import is already queued.
 *
 * @return bool
 */
function tradepress_earnings_has_pending_import() {
	if ( ! class_exists( 'TradePress_Queue_Schema' ) && defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ) ) {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';
	}

	if ( ! class_exists( 'TradePress_Queue_Schema' ) ) {
		return false;
	}

	return TradePress_Queue_Schema::has_active_item( 'data_import', 'fetch_earnings' );
}

/**
 * Resolve the standard data-mode label for stored earnings data.
 *
 * @param bool     $has_stored_records Whether stored records exist.
 * @param int|null $age_seconds Stored data age in seconds.
 * @param bool     $queue_pending Whether a refresh is queued or processing.
 * @return string
 */
function tradepress_earnings_resolve_data_mode( $has_stored_records, $age_seconds, $queue_pending ) {
	if ( $queue_pending ) {
		return 'Queued';
	}

	if ( ! $has_stored_records ) {
		return 'Empty';
	}

	if ( null === $age_seconds ) {
		return 'Cached';
	}

	return $age_seconds <= ( 12 * HOUR_IN_SECONDS ) ? 'Live' : 'Cached';
}

/**
 * Queue an earnings refresh when stored data is missing or old enough.
 *
 * @param array $provider_status Current provider status.
 * @return array Updated provider status.
 */
function tradepress_earnings_maybe_queue_refresh( $provider_status ) {
	$provider_ready = $provider_status['alpha_enabled'] && $provider_status['alpha_key_configured'];
	$refresh_due    = ! $provider_status['has_stored_records']
		|| null === $provider_status['age_seconds']
		|| $provider_status['age_seconds'] >= $provider_status['queue_threshold'];

	if ( function_exists( 'tradepress_debug' ) ) {
		tradepress_debug(
			array(
				'provider_ready' => $provider_ready,
				'refresh_due'    => $refresh_due,
				'queue_pending'  => $provider_status['queue_pending'],
				'data_mode'      => $provider_status['data_mode'],
			),
			'Earnings refresh queue evaluation'
		);
	}

	if ( ! $provider_ready || ! $refresh_due || $provider_status['queue_pending'] ) {
		return $provider_status;
	}

	if ( ! class_exists( 'TradePress_Data_Import_Process' ) && defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ) ) {
		if ( ! class_exists( 'TradePress_Background_Processing' ) ) {
			require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/class.background-process.php';
		}

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/data-import-process.php';
	}

	if ( ! class_exists( 'TradePress_Data_Import_Process' ) ) {
		if ( function_exists( 'tradepress_log_error' ) ) {
			tradepress_log_error(
				'Earnings refresh could not be queued because TradePress_Data_Import_Process is unavailable.',
				array( 'source' => 'research_earnings_calendar' )
			);
		}

		return $provider_status;
	}

	$queued = TradePress_Data_Import_Process::queue_data_fetch(
		'fetch_earnings',
		array(
			'source' => 'research_earnings_calendar',
			'reason' => $provider_status['has_stored_records'] ? 'stale' : 'missing',
		),
		20
	);

	if ( $queued ) {
		$provider_status['queue_pending']       = true;
		$provider_status['queued_this_request'] = true;
		$provider_status['data_mode']           = 'Queued';

		if ( function_exists( 'tradepress_log_user_action' ) ) {
			tradepress_log_user_action(
				'earnings_refresh_queued',
				array(
					'source' => 'research_earnings_calendar',
					'reason' => $provider_status['has_stored_records'] ? 'stale' : 'missing',
				)
			);
		}
	}

	return $provider_status;
}

/**
 * Convert a potentially formatted value to float when possible.
 *
 * @param mixed $value Value to normalize.
 * @return float|null
 */
function tradepress_earnings_to_float( $value ) {
	if ( is_numeric( $value ) ) {
		return (float) $value;
	}

	if ( is_string( $value ) ) {
		$normalized = preg_replace( '/[^0-9.\-]/', '', $value );
		if ( $normalized !== '' && is_numeric( $normalized ) ) {
			return (float) $normalized;
		}
	}

	return null;
}

/**
 * Build deterministic display metrics from stored earnings fields.
 *
 * @param array $earning Earnings item.
 * @return array
 */
function tradepress_earnings_build_display_metrics( $earning ) {
	$eps_change_percent = isset( $earning['eps_change_percent'] ) ? (float) $earning['eps_change_percent'] : 0.0;

	$score_source = null;
	if ( isset( $earning['opportunity_score'] ) && is_numeric( $earning['opportunity_score'] ) ) {
		$score_source = (float) $earning['opportunity_score'];
	} elseif ( isset( $earning['algorithm_score'] ) && is_numeric( $earning['algorithm_score'] ) ) {
		$score_source = (float) $earning['algorithm_score'];
	}

	$opportunity_score = null !== $score_source
		? (int) round( $score_source )
		: (int) round( max( 0, min( 100, 50 + ( $eps_change_percent * 2 ) ) ) );

	if ( $opportunity_score >= 75 ) {
		$opportunity_class = 'high-opportunity';
	} elseif ( $opportunity_score >= 50 ) {
		$opportunity_class = 'medium-opportunity';
	} else {
		$opportunity_class = 'low-opportunity';
	}

	$sentiment = isset( $earning['sentiment'] ) ? sanitize_text_field( (string) $earning['sentiment'] ) : '';
	if ( '' === $sentiment ) {
		if ( $eps_change_percent > 0 ) {
			$sentiment = 'Bullish';
		} elseif ( $eps_change_percent < 0 ) {
			$sentiment = 'Bearish';
		} else {
			$sentiment = 'Neutral';
		}
	}

	$sentiment_percent = isset( $earning['sentiment_percent'] ) ? sanitize_text_field( (string) $earning['sentiment_percent'] ) : '';
	if ( '' === $sentiment_percent ) {
		$sentiment_percent = number_format( min( 100, max( 0, abs( $eps_change_percent ) ) ), 1 ) . '%';
	}

	$sentiment_class = strtolower( $sentiment );

	$whisper = isset( $earning['whisper'] ) && '' !== trim( (string) $earning['whisper'] )
		? (string) $earning['whisper']
		: 'N/A';

	$current_price = null;
	if ( isset( $earning['current_price'] ) ) {
		$current_price = tradepress_earnings_to_float( $earning['current_price'] );
	}

	$estimated_price = null;
	if ( isset( $earning['estimated_price'] ) ) {
		$estimated_price = tradepress_earnings_to_float( $earning['estimated_price'] );
	} elseif ( isset( $earning['price_estimate'] ) ) {
		$estimated_price = tradepress_earnings_to_float( $earning['price_estimate'] );
	}

	$price_change_pct = isset( $earning['price_change_percent'] )
		? tradepress_earnings_to_float( $earning['price_change_percent'] )
		: null;

	if ( null === $price_change_pct && null !== $current_price && null !== $estimated_price && 0.0 !== $current_price ) {
		$price_change_pct = ( ( $estimated_price - $current_price ) / $current_price ) * 100;
	}

	return array(
		'opportunity_score'  => $opportunity_score,
		'opportunity_class'  => $opportunity_class,
		'sentiment'          => $sentiment,
		'sentiment_class'    => sanitize_html_class( $sentiment_class ),
		'sentiment_percent'  => $sentiment_percent,
		'whisper'            => $whisper,
		'current_price'      => $current_price,
		'estimated_price'    => $estimated_price,
		'price_change_pct'   => $price_change_pct,
		'eps_change_percent' => $eps_change_percent,
	);
}

/**
 * Display the Earnings tab content
 *
 * @version 1.0.0
 */
function tradepress_earnings_tab_content() {
	// Get current date for filtering
	$current_date = current_time( 'Y-m-d' );
	$end_date     = date( 'Y-m-d', strtotime( '+7 days', strtotime( $current_date ) ) );

	// Get filter parameters with defaults
	$view_mode  = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'week';
	$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : $current_date;
	if ( $view_mode === 'week' ) {
		$end_date = date( 'Y-m-d', strtotime( '+7 days', strtotime( $start_date ) ) );
	} elseif ( $view_mode === 'month' ) {
		$end_date = date( 'Y-m-d', strtotime( '+30 days', strtotime( $start_date ) ) );
	} else {
		$end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : $end_date;
	}

	$sector_filter     = isset( $_GET['sector'] ) ? sanitize_text_field( wp_unslash( $_GET['sector'] ) ) : 'all';
	$importance_filter = isset( $_GET['importance'] ) ? sanitize_text_field( wp_unslash( $_GET['importance'] ) ) : 'all';
	$display_mode      = isset( $_GET['display'] ) ? sanitize_text_field( wp_unslash( $_GET['display'] ) ) : 'table';

	// Get user's timezone setting
	$user_timezone = get_option( 'timezone_string' );
	if ( empty( $user_timezone ) ) {
		// Default to WordPress timezone if user hasn't set one
		$user_timezone = 'UTC';
	}

	$provider_status = tradepress_earnings_maybe_queue_refresh( tradepress_earnings_get_provider_status() );
	$earnings_data   = tradepress_fetch_earnings_calendar_data( $start_date, $end_date, $sector_filter );

	// Get data source and last updated information for display
	$data_source       = get_option( 'tradepress_earnings_data_source', 'Alpha Vantage' );
	$last_updated      = get_option( 'tradepress_earnings_last_updated', 0 );
	$last_updated_text = $last_updated ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_updated ) : 'Never';

	if ( ! $provider_status['alpha_key_configured'] ) {
		$empty_state_message = __( 'Alpha Vantage API key is not configured. Add a key in API Management, then run or schedule earnings import.', 'tradepress' );
	} elseif ( ! $provider_status['alpha_enabled'] ) {
		$empty_state_message = __( 'Alpha Vantage is configured but not enabled. Enable the service in Settings, then run or schedule earnings import.', 'tradepress' );
	} elseif ( $provider_status['queue_pending'] ) {
		$empty_state_message = __( 'An earnings import is queued or running. This view will show stored records after the background import completes.', 'tradepress' );
	} else {
		$empty_state_message = __( 'No earnings records are stored for this filter yet. Run or schedule an earnings import, then refresh this view.', 'tradepress' );
	}

	// Group earnings by date
	$earnings_by_date = array();
	foreach ( $earnings_data as $earning ) {
		$date = $earning['date'];
		if ( ! isset( $earnings_by_date[ $date ] ) ) {
			$earnings_by_date[ $date ] = array();
		}
		$earnings_by_date[ $date ][] = $earning;
	}

	// Sort dates chronologically
	ksort( $earnings_by_date );
	?>
	
	<div class="tradepress-earnings-container">
		<div class="notice notice-info inline" style="margin: 10px 0 16px 0;">
				<p><strong><?php esc_html_e( 'Provider status', 'tradepress' ); ?></strong></p>
				<ul style="margin: 8px 0 0 20px; list-style: disc;">
					<li>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %1$s: enabled/disabled text, %2$s: configured/missing text */
								__( 'Alpha Vantage: %1$s, key %2$s', 'tradepress' ),
								$provider_status['alpha_enabled'] ? __( 'enabled', 'tradepress' ) : __( 'disabled', 'tradepress' ),
								$provider_status['alpha_key_configured'] ? __( 'configured', 'tradepress' ) : __( 'missing', 'tradepress' )
							)
						);
						?>
					</li>
					<li>
						<?php
						printf(
							/* translators: %1$s: data mode label, %2$s: runtime health label. */
							esc_html__( 'Data mode: %1$s, health: %2$s', 'tradepress' ),
							esc_html( $provider_status['data_mode'] ),
							esc_html( $provider_status['runtime_health'] )
						);
						?>
					</li>
					<li>
						<?php
						if ( $provider_status['cron_enabled'] && $provider_status['next_scheduled'] ) {
							printf(
								esc_html__( 'Scheduled import: enabled (%1$s), next run %2$s', 'tradepress' ),
								esc_html( $provider_status['cron_interval'] ),
								esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $provider_status['next_scheduled'] ) )
							);
						} elseif ( $provider_status['cron_enabled'] ) {
							printf(
								esc_html__( 'Scheduled import: enabled (%s), next run pending schedule registration', 'tradepress' ),
								esc_html( $provider_status['cron_interval'] )
							);
						} else {
							esc_html_e( 'Scheduled import: disabled', 'tradepress' );
						}
						?>
					</li>
					<li>
						<?php
						if ( $provider_status['last_import'] > 0 ) {
							printf(
								esc_html__( 'Last imported: %s', 'tradepress' ),
								esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $provider_status['last_import'] ) )
							);
						} else {
							esc_html_e( 'Last imported: not yet imported', 'tradepress' );
						}
						?>
					</li>
				</ul>
		</div>
		
		<div class="tradepress-research-section">
			<!-- Earnings Calendar Controls -->
			<div class="earnings-filters">
				<form class="earnings-filter-form" method="get" action="">
					<input type="hidden" name="page" value="tradepress_research">
					<input type="hidden" name="tab" value="earnings">
					
					<div class="filter-row">
						<div class="filter-group">
							<label for="view"><?php esc_html_e( 'View:', 'tradepress' ); ?></label>
							<select id="view" name="view" onchange="this.form.submit()">
								<option value="week" <?php selected( $view_mode, 'week' ); ?>><?php esc_html_e( 'Week Ahead', 'tradepress' ); ?></option>
								<option value="month" <?php selected( $view_mode, 'month' ); ?>><?php esc_html_e( 'Month Ahead', 'tradepress' ); ?></option>
								<option value="custom" <?php selected( $view_mode, 'custom' ); ?>><?php esc_html_e( 'Custom Range', 'tradepress' ); ?></option>
							</select>
						</div>
						
						<div class="filter-group date-filter <?php echo ( 'custom' === $view_mode ) ? 'visible' : ''; ?>">
							<div class="date-field">
								<label for="start_date"><?php esc_html_e( 'From:', 'tradepress' ); ?></label>
								<input type="date" id="start_date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">
							</div>

							<div class="date-field">
								<label for="end_date"><?php esc_html_e( 'To:', 'tradepress' ); ?></label>
								<input type="date" id="end_date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>">
							</div>
						</div>
						
						<div class="filter-group">
							<label for="sector"><?php esc_html_e( 'Sector:', 'tradepress' ); ?></label>
							<select id="sector" name="sector">
								<option value="all" <?php selected( $sector_filter, 'all' ); ?>><?php esc_html_e( 'All Sectors', 'tradepress' ); ?></option>
								<option value="technology" <?php selected( $sector_filter, 'technology' ); ?>><?php esc_html_e( 'Technology', 'tradepress' ); ?></option>
								<option value="healthcare" <?php selected( $sector_filter, 'healthcare' ); ?>><?php esc_html_e( 'Healthcare', 'tradepress' ); ?></option>
								<option value="financial" <?php selected( $sector_filter, 'financial' ); ?>><?php esc_html_e( 'Financial', 'tradepress' ); ?></option>
								<option value="consumer" <?php selected( $sector_filter, 'consumer' ); ?>><?php esc_html_e( 'Consumer', 'tradepress' ); ?></option>
								<option value="industrial" <?php selected( $sector_filter, 'industrial' ); ?>><?php esc_html_e( 'Industrial', 'tradepress' ); ?></option>
								<option value="energy" <?php selected( $sector_filter, 'energy' ); ?>><?php esc_html_e( 'Energy', 'tradepress' ); ?></option>
							</select>
						</div>
						
						<div class="filter-group">
							<label for="importance"><?php esc_html_e( 'Importance:', 'tradepress' ); ?></label>
							<select id="importance" name="importance">
								<option value="all" <?php selected( $importance_filter, 'all' ); ?>><?php esc_html_e( 'All', 'tradepress' ); ?></option>
								<option value="high" <?php selected( $importance_filter, 'high' ); ?>><?php esc_html_e( 'High', 'tradepress' ); ?></option>
								<option value="medium" <?php selected( $importance_filter, 'medium' ); ?>><?php esc_html_e( 'Medium', 'tradepress' ); ?></option>
								<option value="low" <?php selected( $importance_filter, 'low' ); ?>><?php esc_html_e( 'Low', 'tradepress' ); ?></option>
							</select>
						</div>
						
						<div class="filter-group display-toggle">
							<label><?php esc_html_e( 'Display Mode:', 'tradepress' ); ?></label>
							<div class="toggle-buttons">
								<a href="<?php echo esc_url( add_query_arg( array( 'display' => 'table' ) ) ); ?>" class="toggle-button <?php echo $display_mode === 'table' ? 'active' : ''; ?>">
									<span class="dashicons dashicons-list-view"></span>
									<?php esc_html_e( 'Table', 'tradepress' ); ?>
								</a>
								<a href="<?php echo esc_url( add_query_arg( array( 'display' => 'cards' ) ) ); ?>" class="toggle-button <?php echo $display_mode === 'cards' ? 'active' : ''; ?>">
									<span class="dashicons dashicons-grid-view"></span>
									<?php esc_html_e( 'Cards', 'tradepress' ); ?>
								</a>
							</div>
						</div>
						
						<div class="filter-actions">
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Filters', 'tradepress' ); ?></button>
							<a href="
							<?php
							echo esc_url(
								add_query_arg(
									array(
										'page' => 'tradepress_research',
										'tab'  => 'earnings',
									)
								)
							);
							?>
										" class="button"><?php esc_html_e( 'Reset', 'tradepress' ); ?></a>
						</div>
					</div>
				</form>
				
				<div class="timezone-info">
					<span class="dashicons dashicons-clock" aria-hidden="true"></span>
					<?php /* translators: %s: string value */ ?>
					<?php printf( esc_html__( 'All times shown in your local timezone: %s', 'tradepress' ), '<strong>' . esc_html( $user_timezone ) . '</strong>' ); ?>
				</div>
			</div>
			
			<!-- Results Header -->
			<div class="earnings-results-header">
				<h3>
					<?php
					if ( $view_mode === 'week' ) {
						echo esc_html__( 'Week Ahead Earnings', 'tradepress' );
					} elseif ( $view_mode === 'month' ) {
						echo esc_html__( 'Month Ahead Earnings', 'tradepress' );
					} else {
						/* translators: %s: start date, %s: end date */
						printf(
							esc_html__( 'Earnings from %1$s to %2$s', 'tradepress' ),
							date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ),
							date_i18n( get_option( 'date_format' ), strtotime( $end_date ) )
						);
					}
					?>
				</h3>
				
				<?php if ( empty( $earnings_data ) ) : ?>
					<div class="no-earnings-message" data-state="no-data">
						<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
						<h4><?php esc_html_e( 'No imported earnings reports', 'tradepress' ); ?></h4>
						<p><?php echo esc_html( $empty_state_message ); ?></p>
					</div>
				<?php else : ?>
					<div class="earnings-summary">
						<?php /* translators: %d: number */ ?>
						<span class="total-count"><?php printf( esc_html( _n( '%d company reporting', '%d companies reporting', count( $earnings_data ), 'tradepress' ) ), count( $earnings_data ) ); ?></span>
						<a href="#" class="export-earnings button"><?php esc_html_e( 'Export to CSV', 'tradepress' ); ?></a>
					</div>
				<?php endif; ?>
			</div>
			
			<!-- Earnings Calendar -->
			<?php if ( ! empty( $earnings_data ) ) : ?>
				<?php if ( $display_mode === 'table' ) : ?>
					<!-- Table View -->
					<div class="earnings-calendar">
						<?php foreach ( $earnings_by_date as $date => $day_earnings ) : ?>
							<div class="earnings-date-group">
								<h4 class="earnings-date-header">
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ); ?>
									<span class="day-name"><?php echo esc_html( date_i18n( 'l', strtotime( $date ) ) ); ?></span>
									<span class="report-count"><?php echo (int) count( $day_earnings ); ?> <?php echo _n( 'report', 'reports', count( $day_earnings ), 'tradepress' ); ?></span>
								</h4>
								
								<table class="earnings-table widefat striped">
									<thead>
										<tr>
											<th class="column-time"><?php esc_html_e( 'Time (Local)', 'tradepress' ); ?></th>
											<th class="column-symbol"><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
											<th class="column-company"><?php esc_html_e( 'Company', 'tradepress' ); ?></th>
											<th class="column-market-cap"><?php esc_html_e( 'Market Cap', 'tradepress' ); ?></th>
											<th class="column-sector"><?php esc_html_e( 'Sector', 'tradepress' ); ?></th>
											<th class="column-eps-estimate"><?php esc_html_e( 'EPS Est.', 'tradepress' ); ?></th>
											<th class="column-whisper"><?php esc_html_e( 'Whisper', 'tradepress' ); ?></th>
											<th class="column-prev-eps"><?php esc_html_e( 'Previous EPS', 'tradepress' ); ?></th>
											<th class="column-eps-change"><?php esc_html_e( 'EPS %', 'tradepress' ); ?></th>
											<th class="column-sentiment"><?php esc_html_e( 'Sentiment', 'tradepress' ); ?></th>
											<th class="column-opp-score"><?php esc_html_e( 'Opp. Score', 'tradepress' ); ?></th>
											<th class="column-actions"><?php esc_html_e( 'Actions', 'tradepress' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ( $day_earnings as $index => $earning ) :
											$display = tradepress_earnings_build_display_metrics( $earning );
											?>
											<tr>
												<td><?php echo esc_html( $earning['time'] ); ?></td>
												<td><?php echo esc_html( $earning['symbol'] ); ?></td>
												<td><?php echo esc_html( $earning['company'] ); ?></td>
												<td><?php echo esc_html( $earning['market_cap'] ); ?></td>
												<td><?php echo esc_html( $earning['sector'] ); ?></td>
												<td><?php echo esc_html( $earning['eps_estimate'] ); ?></td>
												<td><?php echo esc_html( $display['whisper'] ); ?></td>
												<td><?php echo esc_html( $earning['previous_eps'] ); ?></td>
												<td>
													<?php
													$eps_percent = $display['eps_change_percent'];
													$eps_class   = '';

													if ( $eps_percent > 0 ) {
														$eps_class = 'earnings-positive';
														echo '<span class="' . esc_attr( $eps_class ) . '">+' . number_format( $eps_percent, 2 ) . '%</span>';
													} elseif ( $eps_percent < 0 ) {
														$eps_class = 'earnings-negative';
														echo '<span class="' . esc_attr( $eps_class ) . '">' . number_format( $eps_percent, 2 ) . '%</span>';
													} else {
														$eps_class = 'earnings-neutral';
														echo '<span class="' . esc_attr( $eps_class ) . '">0.00%</span>';
													}
													?>
												</td>
												<td>
													<span class="sentiment-badge sentiment-<?php echo esc_attr( $display['sentiment_class'] ); ?>">
														<?php echo esc_html( $display['sentiment'] ); ?>
													</span>
													<span class="sentiment-pct"><?php echo esc_html( $display['sentiment_percent'] ); ?></span>
												</td>
												<td>
													<div class="opp-score-container <?php echo esc_attr( $display['opportunity_class'] ); ?>">
														<div class="opp-score-value"><?php echo esc_html( $display['opportunity_score'] ); ?></div>
														<div class="opp-score-bar" style="width: <?php echo (int) min( 100, $display['opportunity_score'] ); ?>%;"></div>
													</div>
												</td>
												<td>
													<button type="button" class="button button-small view-details" data-symbol="<?php echo esc_attr( $earning['symbol'] ); ?>">
														<?php esc_html_e( 'Details', 'tradepress' ); ?>
													</button>
													<button type="button" class="button button-small add-to-watchlist" data-symbol="<?php echo esc_attr( $earning['symbol'] ); ?>">
														<?php esc_html_e( 'Watch', 'tradepress' ); ?>
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<!-- Card View -->
					<div class="earnings-cards-view">
						<?php
						foreach ( $earnings_data as $earning ) :
							$display = tradepress_earnings_build_display_metrics( $earning );
							?>
							<div class="earnings-card <?php echo esc_attr( $display['opportunity_class'] ); ?>">
								<div class="earnings-card-header">
									<div class="company-logo">
										<!-- Placeholder for company logo -->
										<div class="logo-placeholder"><?php echo substr( $earning['symbol'], 0, 1 ); ?></div>
									</div>
									<div class="company-info">
										<h4 class="symbol"><?php echo esc_html( $earning['symbol'] ); ?></h4>
										<p class="company-name"><?php echo esc_html( $earning['company'] ); ?></p>
									</div>
									<div class="earnings-date-badge">
										<span class="date"><?php echo esc_html( date_i18n( 'M d', strtotime( $earning['date'] ) ) ); ?></span>
										<span class="time"><?php echo esc_html( $earning['time'] ); ?></span>
									</div>
								</div>
								
								<div class="earnings-card-body">
									<div class="earnings-data-row">
										<div class="data-item">
											<span class="label"><?php esc_html_e( 'Sector', 'tradepress' ); ?></span>
											<span class="value sector-value"><?php echo esc_html( $earning['sector'] ); ?></span>
										</div>
										<div class="data-item">
											<span class="label"><?php esc_html_e( 'Market Cap', 'tradepress' ); ?></span>
											<span class="value"><?php echo esc_html( $earning['market_cap'] ); ?></span>
										</div>
									</div>
									
									<div class="earnings-data-row">
										<div class="data-item">
											<span class="label"><?php esc_html_e( 'Current Price', 'tradepress' ); ?></span>
											<span class="value price-value">
												<?php
												echo null !== $display['current_price']
													? esc_html( '$' . number_format( $display['current_price'], 2 ) )
													: esc_html__( 'N/A', 'tradepress' );
												?>
											</span>
										</div>
										<div class="data-item">
											<span class="label"><?php esc_html_e( 'Est. Price', 'tradepress' ); ?></span>
											<span class="value estimated-price <?php echo ( null !== $display['price_change_pct'] && $display['price_change_pct'] >= 0 ) ? 'positive' : 'negative'; ?>">
												<?php
												echo null !== $display['estimated_price']
													? esc_html( '$' . number_format( $display['estimated_price'], 2 ) )
													: esc_html__( 'N/A', 'tradepress' );
												?>
												<?php if ( null !== $display['price_change_pct'] ) : ?>
													<span class="price-change">(<?php echo $display['price_change_pct'] >= 0 ? '+' : ''; ?><?php echo esc_html( number_format( $display['price_change_pct'], 2 ) ); ?>%)</span>
												<?php endif; ?>
											</span>
										</div>
									</div>
									
									<div class="earnings-data-row">
										<div class="data-item">
											<span class="label"><?php esc_html_e( 'EPS Est.', 'tradepress' ); ?></span>
											<span class="value"><?php echo esc_html( $earning['eps_estimate'] ); ?></span>
										</div>
										<div class="data-item">
											<span class="label"><?php esc_html_e( 'Previous EPS', 'tradepress' ); ?></span>
											<span class="value"><?php echo esc_html( $earning['previous_eps'] ); ?></span>
										</div>
									</div>
									
									<div class="algorithm-score">
										<div class="score-label"><?php esc_html_e( 'Algorithm Score', 'tradepress' ); ?></div>
										<div class="score-container <?php echo esc_attr( $display['opportunity_class'] ); ?>">
											<div class="score-bar" style="width: <?php echo esc_attr( $display['opportunity_score'] ); ?>%;"></div>
											<div class="score-value"><?php echo esc_html( $display['opportunity_score'] ); ?></div>
										</div>
									</div>
								</div>
								
								<div class="earnings-card-footer">
									<button type="button" class="button button-primary view-details" data-symbol="<?php echo esc_attr( $earning['symbol'] ); ?>">
										<?php esc_html_e( 'Details', 'tradepress' ); ?>
									</button>
									<button type="button" class="button add-to-watchlist" data-symbol="<?php echo esc_attr( $earning['symbol'] ); ?>">
										<?php esc_html_e( 'Add to Watchlist', 'tradepress' ); ?>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<!-- Empty state - Display loading state when refresh is clicked -->
				<div class="earnings-loading-state" hidden>
					<p>
						<span class="spinner is-active"></span>
						<?php esc_html_e( 'Loading earnings data...', 'tradepress' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Generate mock earnings data for testing
 *
 * @param string $start_date Start date in Y-m-d format
 * @param string $end_date End date in Y-m-d format
 * @param string $sector_filter Sector to filter by, or 'all'
 * @param string $importance_filter Importance to filter by, or 'all'
 * @return array Array of earnings data
 * @version 1.0.0
 */
function tradepress_get_mock_earnings_data( $start_date, $end_date, $sector_filter = 'all', $importance_filter = 'all' ) {
	return array();

	// Sample company data
	$companies = array(
		array(
			'symbol'     => 'MSFT',
			'company'    => 'Microsoft Corporation',
			'sector'     => 'Technology',
			'market_cap' => '$2.5T',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'AAPL',
			'company'    => 'Apple Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$2.8T',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'GOOGL',
			'company'    => 'Alphabet Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$1.7T',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'AMZN',
			'company'    => 'Amazon.com Inc.',
			'sector'     => 'Consumer',
			'market_cap' => '$1.4T',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'META',
			'company'    => 'Meta Platforms Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$1.0T',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'TSLA',
			'company'    => 'Tesla Inc.',
			'sector'     => 'Consumer',
			'market_cap' => '$800B',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'NVDA',
			'company'    => 'NVIDIA Corporation',
			'sector'     => 'Technology',
			'market_cap' => '$1.2T',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'JPM',
			'company'    => 'JPMorgan Chase & Co.',
			'sector'     => 'Financial',
			'market_cap' => '$550B',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'JNJ',
			'company'    => 'Johnson & Johnson',
			'sector'     => 'Healthcare',
			'market_cap' => '$400B',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'V',
			'company'    => 'Visa Inc.',
			'sector'     => 'Financial',
			'market_cap' => '$490B',
			'importance' => 'high',
		),
		array(
			'symbol'     => 'PG',
			'company'    => 'Procter & Gamble Co.',
			'sector'     => 'Consumer',
			'market_cap' => '$350B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'UNH',
			'company'    => 'UnitedHealth Group Inc.',
			'sector'     => 'Healthcare',
			'market_cap' => '$450B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'HD',
			'company'    => 'Home Depot Inc.',
			'sector'     => 'Consumer',
			'market_cap' => '$330B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'MA',
			'company'    => 'Mastercard Inc.',
			'sector'     => 'Financial',
			'market_cap' => '$380B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'PFE',
			'company'    => 'Pfizer Inc.',
			'sector'     => 'Healthcare',
			'market_cap' => '$240B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'CSCO',
			'company'    => 'Cisco Systems Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$200B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'CVX',
			'company'    => 'Chevron Corporation',
			'sector'     => 'Energy',
			'market_cap' => '$320B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'KO',
			'company'    => 'The Coca-Cola Company',
			'sector'     => 'Consumer',
			'market_cap' => '$260B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'DIS',
			'company'    => 'The Walt Disney Company',
			'sector'     => 'Consumer',
			'market_cap' => '$170B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'MRK',
			'company'    => 'Merck & Co., Inc.',
			'sector'     => 'Healthcare',
			'market_cap' => '$230B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'BA',
			'company'    => 'The Boeing Company',
			'sector'     => 'Industrial',
			'market_cap' => '$120B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'WMT',
			'company'    => 'Walmart Inc.',
			'sector'     => 'Consumer',
			'market_cap' => '$410B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'CRM',
			'company'    => 'Salesforce, Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$220B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'XOM',
			'company'    => 'Exxon Mobil Corporation',
			'sector'     => 'Energy',
			'market_cap' => '$410B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'INTC',
			'company'    => 'Intel Corporation',
			'sector'     => 'Technology',
			'market_cap' => '$150B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'VZ',
			'company'    => 'Verizon Communications Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$160B',
			'importance' => 'low',
		),
		array(
			'symbol'     => 'NFLX',
			'company'    => 'Netflix, Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$240B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'ADBE',
			'company'    => 'Adobe Inc.',
			'sector'     => 'Technology',
			'market_cap' => '$230B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'PYPL',
			'company'    => 'PayPal Holdings, Inc.',
			'sector'     => 'Financial',
			'market_cap' => '$90B',
			'importance' => 'medium',
		),
		array(
			'symbol'     => 'SBUX',
			'company'    => 'Starbucks Corporation',
			'sector'     => 'Consumer',
			'market_cap' => '$100B',
			'importance' => 'low',
		),
	);

	// Reporting times
	$times = array(
		'Before Market Open',
		'After Market Close',
		'8:00 AM ET',
		'4:30 PM ET',
	);

	// Start with an empty array
	$earnings_data = array();

	// Convert start and end dates to timestamps
	$start_timestamp = strtotime( $start_date );
	$end_timestamp   = strtotime( $end_date );

	// Shuffle companies to randomize
	shuffle( $companies );

	// Generate random earnings reports for each day in the date range
	$current_timestamp = $start_timestamp;
	while ( $current_timestamp <= $end_timestamp ) {
		// Skip weekends
		$day_of_week = date( 'N', $current_timestamp );
		if ( $day_of_week > 5 ) {
			$current_timestamp = strtotime( '+1 day', $current_timestamp );
			continue;
		}

		// Generate 2-7 earnings reports for this day
		$report_count  = mt_rand( 2, 7 );
		$day_companies = array_slice( $companies, 0, $report_count );
		shuffle( $day_companies );

		foreach ( $day_companies as $company ) {
			// Skip if sector doesn't match filter
			if ( $sector_filter !== 'all' && strtolower( $company['sector'] ) !== $sector_filter ) {
				continue;
			}

			// Skip if importance doesn't match filter
			if ( $importance_filter !== 'all' && $company['importance'] !== $importance_filter ) {
				continue;
			}

			// Generate random EPS estimate
			$eps_estimate = sprintf( '$%.2f', mt_rand( 10, 500 ) / 100 );

			// Generate previous EPS and calculate percentage change
			$previous_eps   = sprintf( '$%.2f', mt_rand( 8, 500 ) / 100 );
			$eps_value      = (float) substr( $eps_estimate, 1 );
			$prev_eps_value = (float) substr( $previous_eps, 1 );

			// Calculate percentage change (avoid division by zero)
			if ( $prev_eps_value > 0 ) {
				$eps_change_percent = ( ( $eps_value - $prev_eps_value ) / $prev_eps_value ) * 100;
			} else {
				$eps_change_percent = 0;
			}

			// Add to earnings data
			$earnings_data[] = array(
				'date'               => date( 'Y-m-d', $current_timestamp ),
				'time'               => $times[ array_rand( $times ) ],
				'symbol'             => $company['symbol'],
				'company'            => $company['company'],
				'market_cap'         => $company['market_cap'],
				'sector'             => $company['sector'],
				'eps_estimate'       => $eps_estimate,
				'previous_eps'       => $previous_eps,
				'eps_change_percent' => $eps_change_percent,
				'importance'         => $company['importance'],
			);
		}

		// Move to next day
		$current_timestamp = strtotime( '+1 day', $current_timestamp );

		// Shift companies array for next day
		$companies[] = array_shift( $companies );
	}

	return $earnings_data;
}

/**
 * Read stored earnings data and format for the Earnings tab.
 *
 * @param string $start_date Start date in Y-m-d format
 * @param string $end_date End date in Y-m-d format
 * @param string $sector_filter Sector to filter by, or 'all'
 * @return array Array of earnings data
 * @version 1.0.0
 */
function tradepress_fetch_earnings_calendar_data( $start_date, $end_date, $sector_filter = 'all' ) {
	$raw_data = get_option( 'tradepress_earnings_data', array() );

	if ( empty( $raw_data ) ) {
		$raw_data = get_option( 'tradepress_earnings_calendar_data', array() );
	}

	if ( empty( $raw_data ) ) {
		return array();
	}

	$formatted_data = array();

	foreach ( $raw_data as $earning ) {
		$report_date = isset( $earning['reportDate'] ) ? (string) $earning['reportDate'] : ( isset( $earning['report_date'] ) ? (string) $earning['report_date'] : '' );
		if ( empty( $report_date ) || $report_date < $start_date || $report_date > $end_date ) {
			continue;
		}

		$sector = isset( $earning['sector'] ) ? (string) $earning['sector'] : '';
		if ( $sector_filter !== 'all' && ! empty( $sector ) && strtolower( $sector ) !== strtolower( $sector_filter ) ) {
			continue;
		}

		$estimate       = isset( $earning['estimate'] ) ? $earning['estimate'] : ( isset( $earning['eps_estimate'] ) ? $earning['eps_estimate'] : '' );
		$estimate_value = tradepress_earnings_to_float( $estimate );
		$eps_estimate   = null !== $estimate_value ? '$' . number_format( $estimate_value, 2 ) : 'N/A';

		$previous_eps_raw   = isset( $earning['previous_eps'] ) ? $earning['previous_eps'] : ( isset( $earning['reportedEPS'] ) ? $earning['reportedEPS'] : ( isset( $earning['reported_eps'] ) ? $earning['reported_eps'] : 'N/A' ) );
		$previous_eps_value = tradepress_earnings_to_float( $previous_eps_raw );
		$previous_eps       = null !== $previous_eps_value ? '$' . number_format( $previous_eps_value, 2 ) : (string) $previous_eps_raw;

		$eps_change_percent = isset( $earning['eps_change_percent'] ) ? (float) $earning['eps_change_percent'] : 0.0;
		if ( 0.0 === $eps_change_percent && null !== $estimate_value && null !== $previous_eps_value && 0.0 !== $previous_eps_value ) {
			$eps_change_percent = ( ( $estimate_value - $previous_eps_value ) / abs( $previous_eps_value ) ) * 100;
		}

		$formatted_data[] = array(
			'date'                 => $report_date,
			'time'                 => isset( $earning['time'] ) ? (string) $earning['time'] : 'TBA',
			'symbol'               => isset( $earning['symbol'] ) ? (string) $earning['symbol'] : '',
			'company'              => isset( $earning['name'] ) ? (string) $earning['name'] : ( isset( $earning['company_name'] ) ? (string) $earning['company_name'] : '' ),
			'market_cap'           => isset( $earning['market_cap'] ) ? (string) $earning['market_cap'] : '',
			'sector'               => $sector,
			'eps_estimate'         => $eps_estimate,
			'previous_eps'         => $previous_eps,
			'eps_change_percent'   => $eps_change_percent,
			'importance'           => isset( $earning['importance'] ) ? (string) $earning['importance'] : 'medium',
			'fiscal_date_ending'   => isset( $earning['fiscalDateEnding'] ) ? (string) $earning['fiscalDateEnding'] : ( isset( $earning['fiscal_date_ending'] ) ? (string) $earning['fiscal_date_ending'] : '' ),
			'currency'             => isset( $earning['currency'] ) ? (string) $earning['currency'] : 'USD',
			'whisper'              => isset( $earning['whisper'] ) ? (string) $earning['whisper'] : '',
			'current_price'        => isset( $earning['current_price'] ) ? $earning['current_price'] : ( isset( $earning['price'] ) ? $earning['price'] : null ),
			'estimated_price'      => isset( $earning['estimated_price'] ) ? $earning['estimated_price'] : ( isset( $earning['price_estimate'] ) ? $earning['price_estimate'] : $estimate_value ),
			'price_change_percent' => isset( $earning['price_change_percent'] ) ? $earning['price_change_percent'] : null,
			'opportunity_score'    => isset( $earning['opportunity_score'] ) ? $earning['opportunity_score'] : ( isset( $earning['algorithm_score'] ) ? $earning['algorithm_score'] : null ),
			'algorithm_score'      => isset( $earning['algorithm_score'] ) ? $earning['algorithm_score'] : ( isset( $earning['opportunity_score'] ) ? $earning['opportunity_score'] : null ),
			'sentiment'            => isset( $earning['sentiment'] ) ? (string) $earning['sentiment'] : '',
			'sentiment_percent'    => isset( $earning['sentiment_percent'] ) ? (string) $earning['sentiment_percent'] : '',
		);
	}

	return $formatted_data;
}
?>
