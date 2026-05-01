<?php
/**
 * TradePress News Feed Tab
 *
 * Displays a merged feed of multiple discussion sources: official company news,
 * market platform news, analyst updates, blog posts, emails, tweets, RSS feeds, etc.
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.0
 * @since 1.0.0
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
function tradepress_news_get_first_option_value( $keys ) {
	foreach ( $keys as $key ) {
		$value = get_option( $key, '' );
		if ( ! empty( $value ) ) {
			return (string) $value;
		}
	}

	return '';
}

/**
 * Build provider and import status information for News Feed.
 *
 * @return array
 */
function tradepress_news_get_provider_status() {
	$alpha_enabled  = ( 'yes' === get_option( 'TradePress_switch_alphavantage_api_services', 'no' ) );
	$alpaca_enabled = ( 'yes' === get_option( 'TradePress_switch_alpaca_api_services', 'no' ) );

	$alpha_key = tradepress_news_get_first_option_value(
		array(
			'tradepress_api_alphavantage_key',
			'TradePress_alphavantage_api_key',
			'tradepress_alphavantage_api_key',
			'TradePress_api_alphavantage_key',
		)
	);

	$alpaca_key = tradepress_news_get_first_option_value(
		array(
			'tradepress_alpaca_api_key',
			'TradePress_api_alpaca_key',
			'TradePress_api_alpaca_papermoney_apikey',
			'TradePress_api_alpaca_realmoney_apikey',
		)
	);

	$last_import = (int) max(
		(int) get_option( 'tradepress_news_last_imported', 0 ),
		(int) get_option( 'tradepress_news_last_updated', 0 ),
		(int) get_option( 'tradepress_news_last_update', 0 )
	);

	$queue_status = get_option( 'tradepress_data_import_status', 'stopped' );
	$has_stored_records = tradepress_news_has_stored_records();
	$age_seconds        = $last_import > 0 ? current_time( 'timestamp' ) - $last_import : null;
	$queue_pending      = tradepress_news_has_pending_import();
	$error_states       = get_option( 'tradepress_data_import_error_state', array() );
	$runtime_health     = isset( $error_states['news'] ) || isset( $error_states['news_fetch_failed'] ) || isset( $error_states['news_exception'] )
		? 'failed'
		: 'healthy';

	return array(
		'alpha_enabled'         => $alpha_enabled,
		'alpha_key_configured'  => ! empty( $alpha_key ),
		'alpaca_enabled'        => $alpaca_enabled,
		'alpaca_key_configured' => ! empty( $alpaca_key ),
		'last_import'           => $last_import,
		'queue_status'          => $queue_status,
		'age_seconds'           => $age_seconds,
		'freshness_sla'         => HOUR_IN_SECONDS,
		'queue_threshold'       => 30 * MINUTE_IN_SECONDS,
		'has_stored_records'    => $has_stored_records,
		'queue_pending'         => $queue_pending,
		'queued_this_request'   => false,
		'data_mode'             => tradepress_news_resolve_data_mode( $has_stored_records, $age_seconds, $queue_pending ),
		'runtime_health'        => $runtime_health,
	);
}

/**
 * Check whether news storage currently contains records.
 *
 * @return bool
 */
function tradepress_news_has_stored_records() {
	$raw_data = get_option( 'tradepress_news_data', array() );

	return ! empty( $raw_data ) && is_array( $raw_data );
}

/**
 * Check whether a news import is already queued.
 *
 * @return bool
 */
function tradepress_news_has_pending_import() {
	if ( ! class_exists( 'TradePress_Queue_Schema' ) && defined( 'TRADEPRESS_PLUGIN_DIR_PATH' ) ) {
		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/queue-schema.php';
	}

	if ( ! class_exists( 'TradePress_Queue_Schema' ) ) {
		return false;
	}

	return TradePress_Queue_Schema::has_active_item( 'data_import', 'fetch_news' );
}

/**
 * Resolve the standard data-mode label for stored news data.
 *
 * @param bool     $has_stored_records Whether stored records exist.
 * @param int|null $age_seconds Stored data age in seconds.
 * @param bool     $queue_pending Whether a refresh is queued or processing.
 * @return string
 */
function tradepress_news_resolve_data_mode( $has_stored_records, $age_seconds, $queue_pending ) {
	if ( $queue_pending ) {
		return 'Queued';
	}

	if ( ! $has_stored_records ) {
		return 'Empty';
	}

	if ( null === $age_seconds ) {
		return 'Cached';
	}

	return $age_seconds <= ( 30 * MINUTE_IN_SECONDS ) ? 'Live' : 'Cached';
}

/**
 * Queue a news refresh when stored data is missing or old enough.
 *
 * @param array $provider_status Current provider status.
 * @return array Updated provider status.
 */
function tradepress_news_maybe_queue_refresh( $provider_status ) {
	$provider_ready = ( $provider_status['alpaca_enabled'] && $provider_status['alpaca_key_configured'] )
		|| ( $provider_status['alpha_enabled'] && $provider_status['alpha_key_configured'] );
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
			'News refresh queue evaluation'
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
				'News refresh could not be queued because TradePress_Data_Import_Process is unavailable.',
				array( 'source' => 'research_news_feed' )
			);
		}

		return $provider_status;
	}

	$queued = TradePress_Data_Import_Process::queue_data_fetch(
		'fetch_news',
		array(
			'source' => 'research_news_feed',
			'reason' => $provider_status['has_stored_records'] ? 'stale' : 'missing',
			'limit'  => 50,
		),
		20
	);

	if ( $queued ) {
		$provider_status['queue_pending']       = true;
		$provider_status['queued_this_request'] = true;
		$provider_status['data_mode']           = 'Queued';

		if ( function_exists( 'tradepress_log_user_action' ) ) {
			tradepress_log_user_action(
				'news_refresh_queued',
				array(
					'source' => 'research_news_feed',
					'reason' => $provider_status['has_stored_records'] ? 'stale' : 'missing',
				)
			);
		}
	}

	return $provider_status;
}

/**
 * Display the News Feed tab content
 *
 * @version 1.0.0
 */
function tradepress_news_feed_tab_content() {
	// Get active symbol for filtering if available.
	$active_symbol = isset( $_GET['symbol'] ) ? sanitize_text_field( wp_unslash( $_GET['symbol'] ) ) : '';

	// Get filter parameters.
	$source_filter    = isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : 'all';
	$date_filter      = isset( $_GET['date_range'] ) ? sanitize_text_field( wp_unslash( $_GET['date_range'] ) ) : '7d';
	$sentiment_filter = isset( $_GET['sentiment'] ) ? sanitize_text_field( wp_unslash( $_GET['sentiment'] ) ) : 'all';

	$provider_status = tradepress_news_maybe_queue_refresh( tradepress_news_get_provider_status() );
	$feed_items      = get_live_feed_items( $active_symbol, $source_filter, $date_filter, $sentiment_filter );

	// Available sources for filter dropdown
	$sources = array(
		'all'        => __( 'All Sources', 'tradepress' ),
		'discord'    => __( 'Discord', 'tradepress' ),
		'twitter'    => __( 'X.com (Twitter)', 'tradepress' ),
		'news'       => __( 'News Sites', 'tradepress' ),
		'blogs'      => __( 'Financial Blogs', 'tradepress' ),
		'reddit'     => __( 'Reddit', 'tradepress' ),
		'stocktwits' => __( 'StockTwits', 'tradepress' ),
	);

	// Date range options
	$date_ranges = array(
		'1d'     => __( 'Last 24 Hours', 'tradepress' ),
		'7d'     => __( 'Last 7 Days', 'tradepress' ),
		'30d'    => __( 'Last 30 Days', 'tradepress' ),
		'custom' => __( 'Custom Range', 'tradepress' ),
	);

	// Sentiments options
	$sentiments = array(
		'all'      => __( 'All Sentiment', 'tradepress' ),
		'positive' => __( 'Positive', 'tradepress' ),
		'negative' => __( 'Negative', 'tradepress' ),
		'neutral'  => __( 'Neutral', 'tradepress' ),
	);

	// Resolve empty-state cause before entering HTML template output.
	$news_alpha_ready  = $provider_status['alpha_enabled'] && $provider_status['alpha_key_configured'];
	$news_alpaca_ready = $provider_status['alpaca_enabled'] && $provider_status['alpaca_key_configured'];
	$any_key           = $provider_status['alpha_key_configured'] || $provider_status['alpaca_key_configured'];
	$any_ready         = $news_alpha_ready || $news_alpaca_ready;

	if ( ! $any_key ) {
		$empty_state   = 'no-provider';
		$empty_heading = __( 'No news provider configured', 'tradepress' );
		$empty_message = __( 'Add an Alpha Vantage or Alpaca API key in API Management, enable the service in Settings, then run or schedule a news import.', 'tradepress' );
	} elseif ( ! $any_ready ) {
		$empty_state   = 'provider-disabled';
		$empty_heading = __( 'News provider not enabled', 'tradepress' );
		$empty_message = __( 'An API key is configured but no news-capable provider service is enabled. Enable Alpha Vantage or Alpaca in Settings, then run or schedule a news import.', 'tradepress' );
	} elseif ( $provider_status['queue_pending'] ) {
		$empty_state   = 'queued';
		$empty_heading = __( 'News import queued', 'tradepress' );
		$empty_message = __( 'A news import is queued or running. This view will show stored records after the background import completes.', 'tradepress' );
	} else {
		$empty_state   = 'no-import';
		$empty_heading = __( 'No imported news items', 'tradepress' );
		$empty_message = __( 'Provider is configured and enabled. Run or schedule a news import from the Data Import tab to populate this view.', 'tradepress' );
	}
	?>
	
	<div class="tradepress-news-feed-container">
		<div class="notice notice-info inline" style="margin: 10px 0 16px 0;">
			<p><strong><?php esc_html_e( 'Provider status', 'tradepress' ); ?></strong></p>
			<ul style="margin: 8px 0 0 20px; list-style: disc;">
				<li>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %1$s: enabled/disabled text, %2$s: configured/missing text */
							__( 'Alpha Vantage news support: %1$s, key %2$s', 'tradepress' ),
							$provider_status['alpha_enabled'] ? __( 'enabled', 'tradepress' ) : __( 'disabled', 'tradepress' ),
							$provider_status['alpha_key_configured'] ? __( 'configured', 'tradepress' ) : __( 'missing', 'tradepress' )
						)
					);
					?>
				</li>
				<li>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %1$s: enabled/disabled text, %2$s: configured/missing text */
							__( 'Alpaca news support: %1$s, key %2$s', 'tradepress' ),
							$provider_status['alpaca_enabled'] ? __( 'enabled', 'tradepress' ) : __( 'disabled', 'tradepress' ),
							$provider_status['alpaca_key_configured'] ? __( 'configured', 'tradepress' ) : __( 'missing', 'tradepress' )
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
		
		<div class="news-feed-header">
			<div class="feed-filters">
				<form method="get" action="">
					<input type="hidden" name="page" value="tradepress_research">
					<input type="hidden" name="tab" value="news_feed">
					
					<div class="filter-row">
						<div class="filter-group">
							<label for="symbol"><?php esc_html_e( 'Symbol:', 'tradepress' ); ?></label>
							<input type="text" id="symbol" name="symbol" placeholder="e.g., AAPL" value="<?php echo esc_attr( $active_symbol ); ?>">
						</div>
						
						<div class="filter-group">
							<label for="source"><?php esc_html_e( 'Source:', 'tradepress' ); ?></label>
							<select id="source" name="source">
								<?php foreach ( $sources as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source_filter, $key ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<div class="filter-group">
							<label for="date_range"><?php esc_html_e( 'Date Range:', 'tradepress' ); ?></label>
							<select id="date_range" name="date_range" onchange="toggleCustomDateRange(this.value)">
								<?php foreach ( $date_ranges as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $date_filter, $key ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<div id="custom-date-container" class="filter-group"
						<?php
						if ( 'custom' !== $date_filter ) :
							?>
							hidden<?php endif; ?>>
							<label for="start_date"><?php esc_html_e( 'From:', 'tradepress' ); ?></label>
							<input type="date" id="start_date" name="start_date" value="<?php echo esc_attr( isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : date( 'Y-m-d', strtotime( '-7 days' ) ) ); ?>">
							
							<label for="end_date"><?php esc_html_e( 'To:', 'tradepress' ); ?></label>
							<input type="date" id="end_date" name="end_date" value="<?php echo esc_attr( isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : date( 'Y-m-d' ) ); ?>">
						</div>
						
						<div class="filter-group">
							<label for="sentiment"><?php esc_html_e( 'Sentiment:', 'tradepress' ); ?></label>
							<select id="sentiment" name="sentiment">
								<?php foreach ( $sentiments as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $sentiment_filter, $key ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						
						<div class="filter-actions">
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Filters', 'tradepress' ); ?></button>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress_research&tab=news_feed' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'tradepress' ); ?></a>
						</div>
					</div>
				</form>
			</div>
		</div>
		
		<div class="news-feed-content">
			<?php if ( empty( $feed_items ) ) : ?>
				<div class="no-feed-items" data-state="<?php echo esc_attr( $empty_state ); ?>">
					<span class="dashicons dashicons-rss" aria-hidden="true"></span>
					<h3><?php echo esc_html( $empty_heading ); ?></h3>
					<p><?php echo esc_html( $empty_message ); ?></p>
				</div>
			<?php else : ?>
				<div class="feed-items-container">
					<?php foreach ( $feed_items as $item ) : ?>
						<div class="feed-item <?php echo esc_attr( $item['source_type'] ); ?> sentiment-<?php echo esc_attr( $item['sentiment'] ); ?>">
							<div class="feed-item-header">
								<div class="source-info">
									<span class="source-icon <?php echo esc_attr( $item['source_icon'] ); ?>"></span>
									<span class="source-name"><?php echo esc_html( $item['source_name'] ); ?></span>
								</div>
								<div class="feed-time">
									<span class="time-ago"><?php echo esc_html( $item['time_ago'] ); ?></span>
								</div>
							</div>
							
							<div class="feed-item-content">
								<?php if ( ! empty( $item['title'] ) ) : ?>
									<h3 class="feed-title"><?php echo esc_html( $item['title'] ); ?></h3>
								<?php endif; ?>
								
								<div class="feed-message">
									<?php echo esc_html( $item['message'] ); ?>
								</div>
								
								<?php if ( ! empty( $item['symbols'] ) ) : ?>
									<div class="feed-symbols">
										<?php foreach ( $item['symbols'] as $symbol ) : ?>
											<span class="feed-symbol"><?php echo esc_html( $symbol ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
								
								<?php if ( ! empty( $item['image_url'] ) ) : ?>
									<div class="feed-image">
										<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['source_name'] ); ?>" />
									</div>
								<?php endif; ?>
							</div>
							
							<div class="feed-item-footer">
								<div class="sentiment-indicator sentiment-<?php echo esc_attr( $item['sentiment'] ); ?>">
									<span class="sentiment-label"><?php echo esc_html( ucfirst( $item['sentiment'] ) ); ?></span>
								</div>
								
								<div class="feed-actions">
									<?php if ( ! empty( $item['link'] ) ) : ?>
										<a href="<?php echo esc_url( $item['link'] ); ?>" class="button button-small" target="_blank">
											<?php esc_html_e( 'View Original', 'tradepress' ); ?>
										</a>
									<?php endif; ?>
									
									<button type="button" class="button button-small save-feed-item" data-id="<?php echo esc_attr( $item['id'] ); ?>">
										<?php esc_html_e( 'Save', 'tradepress' ); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="feed-pagination">
					<button type="button" class="button load-more-feed" id="load-more-feed">
						<?php esc_html_e( 'Load More', 'tradepress' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Generate sample feed items for demo mode
 *
 * @param string $symbol Symbol filter
 * @param string $source Source filter
 * @param string $date_range Date range filter
 * @param string $sentiment Sentiment filter
 * @return array Array of feed items
 * @version 1.0.0
 */
function get_demo_feed_items( $symbol = '', $source = 'all', $date_range = '7d', $sentiment = 'all' ) {
	return array();

	// Sample items to demonstrate the UI
	$items = array(
		array(
			'id'          => '1',
			'source_type' => 'discord',
			'source_name' => 'Stock VIP Discord',
			'source_icon' => 'dashicons dashicons-discord',
			'title'       => '',
			'message'     => 'Buy $NVDA now at $460. This should run to $490-500 range before the conference call.',
			'symbols'     => array( 'NVDA' ),
			'image_url'   => '',
			'link'        => '',
			'sentiment'   => 'positive',
			'time_ago'    => '2 hours ago',
		),
		array(
			'id'          => '2',
			'source_type' => 'twitter',
			'source_name' => 'MarketWatch',
			'source_icon' => 'dashicons dashicons-twitter',
			'title'       => '',
			'message'     => 'Breaking: Fed signals rates will stay higher for longer as inflation battle continues $SPY $QQQ',
			'symbols'     => array( 'SPY', 'QQQ' ),
			'image_url'   => '',
			'link'        => 'https://twitter.com/MarketWatch',
			'sentiment'   => 'negative',
			'time_ago'    => '5 hours ago',
		),
		array(
			'id'          => '3',
			'source_type' => 'news',
			'source_name' => 'CNBC',
			'source_icon' => 'dashicons dashicons-admin-site',
			'title'       => 'Microsoft beats earnings expectations, stock climbs 4%',
			'message'     => 'Microsoft reported better-than-expected fiscal Q1 earnings, with cloud revenue up 23% year over year.',
			'symbols'     => array( 'MSFT' ),
			'image_url'   => '',
			'link'        => 'https://www.cnbc.com/',
			'sentiment'   => 'positive',
			'time_ago'    => '1 day ago',
		),
		array(
			'id'          => '4',
			'source_type' => 'reddit',
			'source_name' => 'r/WallStreetBets',
			'source_icon' => 'dashicons dashicons-reddit',
			'title'       => '',
			'message'     => 'TSLA earnings tomorrow, anyone playing calls or puts? The stock looks oversold to me but guidance could be weak.',
			'symbols'     => array( 'TSLA' ),
			'image_url'   => '',
			'link'        => 'https://www.reddit.com/r/wallstreetbets/',
			'sentiment'   => 'neutral',
			'time_ago'    => '12 hours ago',
		),
		array(
			'id'          => '5',
			'source_type' => 'stocktwits',
			'source_name' => 'StockTwits Trending',
			'source_icon' => 'dashicons dashicons-chart-area',
			'title'       => '',
			'message'     => '$AAPL bouncing off support level at $170. Bulls taking control after that big red candle.',
			'symbols'     => array( 'AAPL' ),
			'image_url'   => 'https://picsum.photos/300/200',
			'link'        => 'https://stocktwits.com/',
			'sentiment'   => 'positive',
			'time_ago'    => '3 hours ago',
		),
		array(
			'id'          => '6',
			'source_type' => 'blogs',
			'source_name' => 'Seeking Alpha',
			'source_icon' => 'dashicons dashicons-media-text',
			'title'       => 'Why AMD is poised to outperform NVDA in the next 6 months',
			'message'     => 'AMD\'s new MI300 chip series is gaining traction in the data center market, potentially taking market share from Nvidia.',
			'symbols'     => array( 'AMD', 'NVDA' ),
			'image_url'   => '',
			'link'        => 'https://seekingalpha.com/',
			'sentiment'   => 'positive',
			'time_ago'    => '8 hours ago',
		),
	);

	// Apply symbol filter
	if ( ! empty( $symbol ) ) {
		$filtered_items = array();
		foreach ( $items as $item ) {
			if ( in_array( strtoupper( $symbol ), $item['symbols'] ) ) {
				$filtered_items[] = $item;
			}
		}
		$items = $filtered_items;
	}

	// Apply source filter
	if ( $source !== 'all' ) {
		$filtered_items = array();
		foreach ( $items as $item ) {
			if ( $item['source_type'] === $source ) {
				$filtered_items[] = $item;
			}
		}
		$items = $filtered_items;
	}

	// Apply sentiment filter
	if ( $sentiment !== 'all' ) {
		$filtered_items = array();
		foreach ( $items as $item ) {
			if ( $item['sentiment'] === $sentiment ) {
				$filtered_items[] = $item;
			}
		}
		$items = $filtered_items;
	}

	return $items;
}

/**
 * Get live feed items from actual data sources
 *
 * @param string $symbol Symbol filter
 * @param string $source Source filter
 * @param string $date_range Date range filter
 * @param string $sentiment Sentiment filter
 * @return array Array of feed items
 * @version 1.0.0
 */
function get_live_feed_items( $symbol = '', $source = 'all', $date_range = '7d', $sentiment = 'all' ) {
	$items = get_option( 'tradepress_news_data', array() );

	if ( empty( $items ) || ! is_array( $items ) ) {
		return array();
	}

	$filtered_items = array();
	$symbol         = strtoupper( trim( (string) $symbol ) );
	$cutoff_time    = tradepress_news_get_date_cutoff( $date_range );

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$item = tradepress_news_normalize_stored_item_for_display( $item );

		if ( ! empty( $symbol ) && ! in_array( $symbol, array_map( 'strtoupper', $item['symbols'] ), true ) ) {
			continue;
		}

		if ( 'all' !== $source && $item['source_type'] !== $source ) {
			continue;
		}

		if ( 'all' !== $sentiment && $item['sentiment'] !== $sentiment ) {
			continue;
		}

		if ( null !== $cutoff_time && $item['published_at'] < $cutoff_time ) {
			continue;
		}

		$filtered_items[] = $item;
	}

	usort(
		$filtered_items,
		function ( $a, $b ) {
			return $b['published_at'] <=> $a['published_at'];
		}
	);

	return $filtered_items;
}

/**
 * Get the timestamp cutoff for a date-range filter.
 *
 * @param string $date_range Date-range filter.
 * @return int|null
 */
function tradepress_news_get_date_cutoff( $date_range ) {
	switch ( $date_range ) {
		case '1d':
			return current_time( 'timestamp' ) - DAY_IN_SECONDS;
		case '30d':
			return current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS );
		case 'custom':
			$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
			if ( '' !== $start_date ) {
				$timestamp = strtotime( $start_date . ' 00:00:00' );
				return false !== $timestamp ? $timestamp : null;
			}
			return null;
		case '7d':
		default:
			return current_time( 'timestamp' ) - ( 7 * DAY_IN_SECONDS );
	}
}

/**
 * Normalise a stored news record for display.
 *
 * @param array $item Stored news item.
 * @return array
 */
function tradepress_news_normalize_stored_item_for_display( $item ) {
	$published_at = isset( $item['published_at'] ) ? (int) $item['published_at'] : current_time( 'timestamp' );
	$symbols      = isset( $item['symbols'] ) && is_array( $item['symbols'] ) ? $item['symbols'] : array();

	return array(
		'id'           => isset( $item['id'] ) ? (string) $item['id'] : md5( wp_json_encode( $item ) ),
		'source_type'  => isset( $item['source_type'] ) ? sanitize_html_class( (string) $item['source_type'] ) : 'news',
		'source_name'  => isset( $item['source_name'] ) ? (string) $item['source_name'] : __( 'Imported News', 'tradepress' ),
		'source_icon'  => isset( $item['source_icon'] ) ? (string) $item['source_icon'] : 'dashicons dashicons-media-document',
		'title'        => isset( $item['title'] ) ? (string) $item['title'] : '',
		'message'      => isset( $item['message'] ) ? (string) $item['message'] : '',
		'symbols'      => array_values( array_filter( array_map( 'sanitize_text_field', $symbols ) ) ),
		'image_url'    => isset( $item['image_url'] ) ? (string) $item['image_url'] : '',
		'link'         => isset( $item['link'] ) ? (string) $item['link'] : '',
		'sentiment'    => isset( $item['sentiment'] ) ? sanitize_html_class( (string) $item['sentiment'] ) : 'neutral',
		'published_at' => $published_at,
		'time_ago'     => tradepress_news_format_time_ago( $published_at ),
	);
}

/**
 * Format a timestamp as a compact relative age label.
 *
 * @param int $timestamp Unix timestamp.
 * @return string
 */
function tradepress_news_format_time_ago( $timestamp ) {
	$age = max( 0, current_time( 'timestamp' ) - (int) $timestamp );

	if ( $age < HOUR_IN_SECONDS ) {
		$minutes = max( 1, (int) floor( $age / MINUTE_IN_SECONDS ) );
		return sprintf(
			/* translators: %d: number of minutes. */
			_n( '%d minute ago', '%d minutes ago', $minutes, 'tradepress' ),
			$minutes
		);
	}

	if ( $age < DAY_IN_SECONDS ) {
		$hours = (int) floor( $age / HOUR_IN_SECONDS );
		return sprintf(
			/* translators: %d: number of hours. */
			_n( '%d hour ago', '%d hours ago', $hours, 'tradepress' ),
			$hours
		);
	}

	$days = (int) floor( $age / DAY_IN_SECONDS );
	return sprintf(
		/* translators: %d: number of days. */
		_n( '%d day ago', '%d days ago', $days, 'tradepress' ),
		$days
	);
}
?>
