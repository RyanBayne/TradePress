<?php
/**
 * TradePress - API Efficiency Management Tab
 *
 * @package TradePress/Admin/TradingPlatforms
 * @version 1.0.0
 * @created 2024-12-16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load API Directory
if ( ! class_exists( 'TradePress_API_Directory' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . '/api/api-directory.php';
}

if ( ! class_exists( 'TradePress_API_Usage_Tracker' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/api-usage-tracker.php';
}

// Get all API providers
$all_providers = TradePress_API_Directory::get_all_providers();

$enabled_provider_health = array();
foreach ( $all_providers as $api_id => $provider ) {
	$is_enabled = get_option( 'TradePress_switch_' . $api_id . '_api_services', 'no' ) === 'yes';
	if ( ! $is_enabled ) {
		continue;
	}

	$enabled_provider_health[ $api_id ] = TradePress_API_Usage_Tracker::get_provider_runtime_health( $api_id );
}

$recent_provider_activity = $enabled_provider_health;
uasort(
	$recent_provider_activity,
	function ( $a, $b ) {
		$a_last = ! empty( $a['last_call'] ) ? strtotime( (string) $a['last_call'] ) : 0;
		$b_last = ! empty( $b['last_call'] ) ? strtotime( (string) $b['last_call'] ) : 0;

		return $b_last <=> $a_last;
	}
);

// Handle form submissions for priority settings
if ( isset( $_POST['action'] ) && $_POST['action'] === 'save_api_priorities' ) {
	$priority_nonce = isset( $_POST['priority_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['priority_nonce'] ) ) : '';
	if ( ! empty( $priority_nonce ) && wp_verify_nonce( $priority_nonce, 'tradepress_api_priorities' ) && current_user_can( 'manage_options' ) ) {

		// Save primary API selections
		$primary_apis = array(
			'primary_live_trading'  => sanitize_text_field( $_POST['primary_live_trading'] ?? '' ),
			'primary_paper_trading' => sanitize_text_field( $_POST['primary_paper_trading'] ?? '' ),
			'primary_data_only'     => sanitize_text_field( $_POST['primary_data_only'] ?? '' ),
		);

		// Save secondary API selections
		$secondary_apis = array(
			'secondary_live_trading'  => sanitize_text_field( $_POST['secondary_live_trading'] ?? '' ),
			'secondary_paper_trading' => sanitize_text_field( $_POST['secondary_paper_trading'] ?? '' ),
			'secondary_data_only'     => sanitize_text_field( $_POST['secondary_data_only'] ?? '' ),
		);

		// Update options
		update_option( 'tradepress_primary_apis', $primary_apis );
		update_option( 'tradepress_secondary_apis', $secondary_apis );

		add_settings_error(
			'tradepress_api_priority',
			'priorities_saved',
			__( 'API priorities saved successfully.', 'tradepress' ),
			'updated'
		);
	}
}

// Get current priority settings
$primary_apis = get_option(
	'tradepress_primary_apis',
	array(
		'primary_live_trading'  => '',
		'primary_paper_trading' => '',
		'primary_data_only'     => '',
	)
);

$secondary_apis = get_option(
	'tradepress_secondary_apis',
	array(
		'secondary_live_trading'  => '',
		'secondary_paper_trading' => '',
		'secondary_data_only'     => '',
	)
);

// Filter APIs by type
$trading_apis   = array();
$data_only_apis = array();

foreach ( $all_providers as $api_id => $provider ) {
	$is_enabled = get_option( 'TradePress_switch_' . $api_id . '_api_services', 'no' ) === 'yes';
	if ( ! $is_enabled ) {
		continue;
	}

	if ( isset( $provider['api_type'] ) && $provider['api_type'] === 'data_only' ) {
		$data_only_apis[ $api_id ] = $provider;
	} else {
		$trading_apis[ $api_id ] = $provider;
	}
}

?>

<div class="configure-directives-container">
	<?php settings_errors( 'tradepress_api_priority' ); ?>
	
	<div class="directives-layout">
		<!-- Left Column: Rate Limiting Visualization -->
		<div class="directives-table-container">
			<h3><?php esc_html_e( 'Rate Limiting Status', 'tradepress' ); ?></h3>
			
			<div class="rate-limit-dashboard">
				<?php
				foreach ( $all_providers as $api_id => $provider ) :
					if ( ! isset( $enabled_provider_health[ $api_id ] ) ) {
						continue;
					}

					$health        = $enabled_provider_health[ $api_id ];
					$calls_today   = (int) $health['total_calls'];
					$daily_limit   = max( 1, (int) $health['daily_limit'] );
					$usage_percent = (float) $health['usage_ratio'] * 100;

					if ( $health['health_state'] === 'unavailable' ) {
						$status_class = 'critical';
					} elseif ( $health['health_state'] === 'degraded' ) {
						$status_class = 'warning';
					} else {
						$status_class = 'normal';
					}

					$last_call_label = ! empty( $health['last_call'] )
						? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $health['last_call'] ) )
						: __( 'No calls yet', 'tradepress' );
					?>
					<div class="rate-limit-card">
						<div class="card-header">
							<h4><?php echo esc_html( $provider['name'] ); ?></h4>
							<span class="status-indicator status-<?php echo esc_attr( $status_class ); ?>"></span>
						</div>
						<div class="card-content">
							<div class="usage-bar">
								<div class="usage-fill" style="width: <?php echo (int) min( 100, $usage_percent ); ?>%"></div>
							</div>
							<div class="usage-stats">
								<span><?php echo esc_html( $calls_today ); ?> / <?php echo esc_html( $daily_limit ); ?> calls</span>
								<span class="percentage"><?php echo (int) round( $usage_percent, 1 ); ?>%</span>
							</div>
							<div class="last-call">
								<?php
								printf(
									/* translators: 1: last call datetime, 2: health score */
									esc_html__( 'Last call: %1$s | Health: %2$d', 'tradepress' ),
									esc_html( $last_call_label ),
									(int) $health['health_score']
								);
								?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			
			<h3><?php esc_html_e( 'Cache Efficiency Status', 'tradepress' ); ?></h3>
			<div class="cache-efficiency-dashboard">
				<?php
				// Check cache status for common symbols
				$common_symbols = array( 'NVDA', 'AAPL', 'TSLA', 'MSFT' );
				foreach ( $all_providers as $api_id => $provider ) :
					$is_enabled = get_option( 'TradePress_switch_' . $api_id . '_api_services', 'no' ) === 'yes';
					if ( ! $is_enabled ) {
						continue;
					}

					$cache_hits   = 0;
					$total_checks = count( $common_symbols );

					foreach ( $common_symbols as $symbol ) {
						$cache_key = 'tradepress_' . $api_id . '_' . $symbol . '_bars';
						if ( get_transient( $cache_key ) !== false ) {
							++$cache_hits;
						}
					}

					$cache_efficiency = $total_checks > 0 ? ( $cache_hits / $total_checks ) * 100 : 0;
					?>
					<div class="cache-efficiency-item">
						<div class="cache-header">
							<strong><?php echo esc_html( $provider['name'] ); ?></strong>
							<span class="cache-percentage"><?php echo (int) round( $cache_efficiency ); ?>% cached</span>
						</div>
						<div class="cache-details">
							<small><?php echo (int) $cache_hits; ?>/<?php echo (int) $total_checks; ?> symbols cached</small>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			
			<h3><?php esc_html_e( 'Recent API Activity', 'tradepress' ); ?></h3>
			<div class="api-activity-log">
				<?php
				$rows_printed = 0;
				foreach ( $recent_provider_activity as $provider_id => $health ) :
					$provider_name = isset( $all_providers[ $provider_id ]['name'] ) ? $all_providers[ $provider_id ]['name'] : $provider_id;
					$timestamp     = ! empty( $health['last_call'] )
						? date_i18n( get_option( 'time_format' ), strtotime( $health['last_call'] ) )
						: __( 'N/A', 'tradepress' );

					$status_class = 'success';
					$status_text  = __( 'Healthy', 'tradepress' );

					if ( $health['health_state'] === 'unavailable' ) {
						$status_class = 'error';
						$status_text  = __( 'Unavailable', 'tradepress' );
					} elseif ( $health['health_state'] === 'degraded' ) {
						$status_class = 'warning';
						$status_text  = __( 'Degraded', 'tradepress' );
					}
					?>
					<div class="activity-item">
						<span class="timestamp"><?php echo esc_html( $timestamp ); ?></span>
						<span class="api-name"><?php echo esc_html( $provider_name ); ?></span>
						<span class="endpoint"><?php echo esc_html( sprintf( 'Calls: %1$d (Errors: %2$d)', (int) $health['total_calls'], (int) $health['failed_calls'] ) ); ?></span>
						<span class="status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_text ); ?></span>
					</div>
					<?php
					++$rows_printed;
					if ( $rows_printed >= 8 ) {
						break;
					}
				endforeach;

				if ( 0 === $rows_printed ) :
					?>
					<div class="activity-item">
						<span class="timestamp">--</span>
						<span class="api-name"><?php esc_html_e( 'No enabled providers', 'tradepress' ); ?></span>
						<span class="endpoint"><?php esc_html_e( 'Enable and configure at least one provider', 'tradepress' ); ?></span>
						<span class="status warning"><?php esc_html_e( 'Pending', 'tradepress' ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<h3><?php esc_html_e( 'Provider Failover Events', 'tradepress' ); ?></h3>
			<div class="api-activity-log">
				<?php
				$failover_events = class_exists( 'TradePress_API_Usage_Tracker' )
					? TradePress_API_Usage_Tracker::get_recent_failover_events( 15 )
					: array();

				if ( ! empty( $failover_events ) ) :
					foreach ( $failover_events as $event ) :
						$reason_map = array(
							'factory_error'    => __( 'Config error', 'tradepress' ),
							'method_missing'   => __( 'Method missing', 'tradepress' ),
							'api_error'        => __( 'API error', 'tradepress' ),
							'failed_over'      => __( 'Failed over', 'tradepress' ),
							'rate_limited'     => __( 'Rate limited', 'tradepress' ),
						);
						$reason_label = isset( $reason_map[ $event['reason'] ] ) ? $reason_map[ $event['reason'] ] : esc_html( $event['reason'] );
						$status_class = ( $event['reason'] === 'failed_over' ) ? 'warning' : 'error';
						$selected_txt = ! empty( $event['selected'] ) ? '→ ' . esc_html( $event['selected'] ) : '—';
						?>
						<div class="activity-item">
							<span class="timestamp"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $event['ts'] ) ) ); ?></span>
							<span class="api-name"><?php echo esc_html( $event['skipped'] ); ?></span>
							<span class="endpoint"><?php echo esc_html( $event['data_type'] ); ?> <span class="status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $reason_label ); ?></span></span>
							<span class="status success"><?php echo esc_html( $selected_txt ); ?></span>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="activity-item">
						<span class="timestamp">--</span>
						<span class="api-name"><?php esc_html_e( 'No failover events recorded', 'tradepress' ); ?></span>
						<span class="endpoint"><?php esc_html_e( 'All providers responding on first attempt', 'tradepress' ); ?></span>
						<span class="status success"><?php esc_html_e( 'Clean', 'tradepress' ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Right Column: Priority Management -->
		<div class="directive-right-column">
			<div class="directive-details-container">
				<div class="directive-section">
					<div class="section-header">
						<h3><?php esc_html_e( 'API Priority Configuration', 'tradepress' ); ?></h3>
					</div>
					<div class="section-content">
						<form method="post">
							<input type="hidden" name="priority_nonce" value="<?php echo esc_attr( wp_create_nonce( 'tradepress_api_priorities' ) ); ?>">
							<input type="hidden" name="action" value="save_api_priorities">
							
							<h4><?php esc_html_e( 'Primary APIs', 'tradepress' ); ?></h4>
							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="primary_live_trading">
											<?php esc_html_e( 'Live Trading API', 'tradepress' ); ?>
										</label>
									</th>
									<td>
										<select id="primary_live_trading" name="primary_live_trading">
											<option value=""><?php esc_html_e( 'Select API...', 'tradepress' ); ?></option>
											<?php foreach ( $trading_apis as $api_id => $provider ) : ?>
												<option value="<?php echo esc_attr( $api_id ); ?>" <?php selected( $primary_apis['primary_live_trading'], $api_id ); ?>>
													<?php echo esc_html( $provider['name'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="primary_paper_trading">
											<?php esc_html_e( 'Paper Trading API', 'tradepress' ); ?>
										</label>
									</th>
									<td>
										<select id="primary_paper_trading" name="primary_paper_trading">
											<option value=""><?php esc_html_e( 'Select API...', 'tradepress' ); ?></option>
											<?php foreach ( $trading_apis as $api_id => $provider ) : ?>
												<option value="<?php echo esc_attr( $api_id ); ?>" <?php selected( $primary_apis['primary_paper_trading'], $api_id ); ?>>
													<?php echo esc_html( $provider['name'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="primary_data_only">
											<?php esc_html_e( 'Data Only API', 'tradepress' ); ?>
										</label>
									</th>
									<td>
										<select id="primary_data_only" name="primary_data_only">
											<option value=""><?php esc_html_e( 'Select API...', 'tradepress' ); ?></option>
											<?php foreach ( $data_only_apis as $api_id => $provider ) : ?>
												<option value="<?php echo esc_attr( $api_id ); ?>" <?php selected( $primary_apis['primary_data_only'], $api_id ); ?>>
													<?php echo esc_html( $provider['name'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
							</table>
							
							<h4><?php esc_html_e( 'Secondary APIs (Fallback)', 'tradepress' ); ?></h4>
							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="secondary_live_trading">
											<?php esc_html_e( 'Live Trading API', 'tradepress' ); ?>
										</label>
									</th>
									<td>
										<select id="secondary_live_trading" name="secondary_live_trading">
											<option value=""><?php esc_html_e( 'Select API...', 'tradepress' ); ?></option>
											<?php foreach ( $trading_apis as $api_id => $provider ) : ?>
												<option value="<?php echo esc_attr( $api_id ); ?>" <?php selected( $secondary_apis['secondary_live_trading'], $api_id ); ?>>
													<?php echo esc_html( $provider['name'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="secondary_paper_trading">
											<?php esc_html_e( 'Paper Trading API', 'tradepress' ); ?>
										</label>
									</th>
									<td>
										<select id="secondary_paper_trading" name="secondary_paper_trading">
											<option value=""><?php esc_html_e( 'Select API...', 'tradepress' ); ?></option>
											<?php foreach ( $trading_apis as $api_id => $provider ) : ?>
												<option value="<?php echo esc_attr( $api_id ); ?>" <?php selected( $secondary_apis['secondary_paper_trading'], $api_id ); ?>>
													<?php echo esc_html( $provider['name'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="secondary_data_only">
											<?php esc_html_e( 'Data Only API', 'tradepress' ); ?>
										</label>
									</th>
									<td>
										<select id="secondary_data_only" name="secondary_data_only">
											<option value=""><?php esc_html_e( 'Select API...', 'tradepress' ); ?></option>
											<?php foreach ( $data_only_apis as $api_id => $provider ) : ?>
												<option value="<?php echo esc_attr( $api_id ); ?>" <?php selected( $secondary_apis['secondary_data_only'], $api_id ); ?>>
													<?php echo esc_html( $provider['name'] ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
							</table>
							
							<div class="api-settings-actions">
								<button type="submit" class="button button-primary">
									<?php esc_html_e( 'Save Priority Settings', 'tradepress' ); ?>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="directive-details-container">
				<div class="directive-section">
					<div class="section-header">
						<h3><?php esc_html_e( 'Fallback Strategy', 'tradepress' ); ?></h3>
					</div>
					<div class="section-content">
						<p><?php esc_html_e( 'When an API call fails or rate limits are exceeded:', 'tradepress' ); ?></p>
						<ol>
							<li><?php esc_html_e( 'Try Primary API first', 'tradepress' ); ?></li>
							<li><?php esc_html_e( 'If Primary fails, try Secondary API', 'tradepress' ); ?></li>
							<li><?php esc_html_e( 'If Secondary fails, select randomly from remaining enabled APIs', 'tradepress' ); ?></li>
							<li><?php esc_html_e( 'Log all fallback attempts for monitoring', 'tradepress' ); ?></li>
						</ol>
						
						<h4><?php esc_html_e( 'Current Priority Order', 'tradepress' ); ?></h4>
						<div class="priority-display">
							<div class="priority-group">
								<strong><?php esc_html_e( 'Live Trading:', 'tradepress' ); ?></strong>
								<span><?php echo esc_html( $primary_apis['primary_live_trading'] ? $all_providers[ $primary_apis['primary_live_trading'] ]['name'] ?? 'Unknown' : 'Not set' ); ?></span>
								→
								<span><?php echo esc_html( $secondary_apis['secondary_live_trading'] ? $all_providers[ $secondary_apis['secondary_live_trading'] ]['name'] ?? 'Unknown' : 'Not set' ); ?></span>
							</div>
							<div class="priority-group">
								<strong><?php esc_html_e( 'Paper Trading:', 'tradepress' ); ?></strong>
								<span><?php echo esc_html( $primary_apis['primary_paper_trading'] ? $all_providers[ $primary_apis['primary_paper_trading'] ]['name'] ?? 'Unknown' : 'Not set' ); ?></span>
								→
								<span><?php echo esc_html( $secondary_apis['secondary_paper_trading'] ? $all_providers[ $secondary_apis['secondary_paper_trading'] ]['name'] ?? 'Unknown' : 'Not set' ); ?></span>
							</div>
							<div class="priority-group">
								<strong><?php esc_html_e( 'Data Only:', 'tradepress' ); ?></strong>
								<span><?php echo esc_html( $primary_apis['primary_data_only'] ? $all_providers[ $primary_apis['primary_data_only'] ]['name'] ?? 'Unknown' : 'Not set' ); ?></span>
								→
								<span><?php echo esc_html( $secondary_apis['secondary_data_only'] ? $all_providers[ $secondary_apis['secondary_data_only'] ]['name'] ?? 'Unknown' : 'Not set' ); ?></span>
							</div>
						</div>
						
						<h4><?php esc_html_e( 'Next API Call Prediction', 'tradepress' ); ?></h4>
						<div class="next-call-prediction">
							<?php
							$ranked_data_candidates = TradePress_API_Usage_Tracker::get_ranked_providers_for_data( 'quote' );
							$next_data_api          = __( 'None available', 'tradepress' );
							$prediction_reason      = __( 'No configured providers available for quote data.', 'tradepress' );

							if ( ! empty( $ranked_data_candidates ) ) {
								$best         = $ranked_data_candidates[0];
								$provider_id  = $best['provider_id'];
								$provider     = isset( $all_providers[ $provider_id ] ) ? $all_providers[ $provider_id ] : array();
								$provider_name = isset( $provider['name'] ) ? $provider['name'] : $provider_id;

								$next_data_api = $provider_name;
								$prediction_reason = sprintf(
									/* translators: 1: health score, 2: health state */
									__( 'Selected by runtime health ranking (score %1$d, state: %2$s).', 'tradepress' ),
									(int) $best['health_score'],
									esc_html( $best['health_state'] )
								);
							}
							?>
							<div class="prediction-item">
								<strong><?php esc_html_e( 'Data Only Call:', 'tradepress' ); ?></strong>
								<span class="next-api"><?php echo esc_html( $next_data_api ); ?></span>
								<small class="prediction-reason"><?php echo esc_html( $prediction_reason ); ?></small>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.rate-limit-dashboard {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 15px;
	margin-bottom: 30px;
}

.rate-limit-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 6px;
	padding: 15px;
}

.card-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 10px;
}

.card-header h4 {
	margin: 0;
	font-size: 14px;
}

.status-indicator {
	width: 12px;
	height: 12px;
	border-radius: 50%;
}

.status-normal { background: #28a745; }
.status-warning { background: #ffc107; }
.status-critical { background: #dc3545; }

.usage-bar {
	background: #f1f1f1;
	height: 8px;
	border-radius: 4px;
	overflow: hidden;
	margin-bottom: 8px;
}

.usage-fill {
	height: 100%;
	background: linear-gradient(90deg, #28a745 0%, #ffc107 60%, #dc3545 80%);
	transition: width 0.3s ease;
}

.usage-stats {
	display: flex;
	justify-content: space-between;
	font-size: 12px;
	margin-bottom: 5px;
}

.percentage {
	font-weight: bold;
}

.last-call {
	font-size: 11px;
	color: #666;
}

.api-activity-log {
	background: #f8f9fa;
	border: 1px solid #dee2e6;
	border-radius: 4px;
	max-height: 200px;
	overflow-y: auto;
}

.activity-item {
	display: grid;
	grid-template-columns: 80px 100px 1fr 80px;
	gap: 10px;
	padding: 8px 12px;
	border-bottom: 1px solid #e9ecef;
	font-size: 12px;
	align-items: center;
}

.activity-item:last-child {
	border-bottom: none;
}

.timestamp {
	color: #666;
}

.api-name {
	font-weight: bold;
}

.status.success {
	color: #28a745;
}

.status.warning {
	color: #ffc107;
}

.status.error {
	color: #dc3545;
}

.priority-display {
	background: #f8f9fa;
	padding: 15px;
	border-radius: 4px;
	margin-top: 10px;
}

.priority-group {
	margin-bottom: 10px;
	display: flex;
	align-items: center;
	gap: 10px;
}

.priority-group:last-child {
	margin-bottom: 0;
}

.priority-group strong {
	min-width: 120px;
}

.priority-group span {
	background: #e9ecef;
	padding: 4px 8px;
	border-radius: 3px;
	font-size: 12px;
}

.next-call-prediction {
	background: #e8f4fd;
	border: 1px solid #bee5eb;
	border-radius: 4px;
	padding: 15px;
	margin-top: 15px;
}

.prediction-item {
	display: flex;
	flex-direction: column;
	gap: 5px;
}

.next-api {
	background: #007cba;
	color: white;
	padding: 6px 12px;
	border-radius: 4px;
	font-weight: bold;
	display: inline-block;
	width: fit-content;
}

.prediction-reason {
	color: #666;
	font-style: italic;
}

.cache-efficiency-dashboard {
	background: #f8f9fa;
	border: 1px solid #dee2e6;
	border-radius: 4px;
	padding: 15px;
	margin-bottom: 20px;
}

.cache-efficiency-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 0;
	border-bottom: 1px solid #e9ecef;
}

.cache-efficiency-item:last-child {
	border-bottom: none;
}

.cache-header {
	display: flex;
	align-items: center;
	gap: 10px;
}

.cache-percentage {
	background: #28a745;
	color: white;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: bold;
}

.cache-details {
	color: #666;
	font-size: 12px;
}
</style>
