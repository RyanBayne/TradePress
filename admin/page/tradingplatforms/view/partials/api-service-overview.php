<?php
/**
 * Partial: API Service Overview
 *
 * Header and status summary for individual trading platform API tabs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$provider         = isset( $provider ) && is_array( $provider ) ? $provider : array();
$is_data_only_api = isset( $provider['api_type'] ) && $provider['api_type'] === 'data_only';
$endpoint_count   = is_array( $endpoints ) ? count( $endpoints ) : 0;

try {
	$real_local_status = tradepress_get_real_local_status( $api_id );
} catch ( Exception $e ) {
	$real_local_status = array(
		'status'  => 'error',
		'message' => $e->getMessage(),
	);
}

try {
	$real_service_status = tradepress_get_real_service_status( $api_id );
} catch ( Exception $e ) {
	$real_service_status = array(
		'status'       => 'error',
		'message'      => $e->getMessage(),
		'last_updated' => current_time( 'mysql' ),
	);
}

$rate_limit_data = tradepress_get_real_rate_limits( $api_id );
$latest_api_call = class_exists( 'TradePress_AJAX' ) ? TradePress_AJAX::get_latest_api_call( $api_id ) : null;

$realmoney_apikey     = get_option( 'TradePress_api_' . $api_id . '_realmoney_apikey', '' );
$realmoney_secretkey  = get_option( 'TradePress_api_' . $api_id . '_realmoney_secretkey', '' );
$papermoney_apikey    = get_option( 'TradePress_api_' . $api_id . '_papermoney_apikey', '' );
$papermoney_secretkey = get_option( 'TradePress_api_' . $api_id . '_papermoney_secretkey', '' );
$data_api_key         = get_option( 'tradepress_' . $api_id . '_api_key', '' );
if ( empty( $data_api_key ) ) {
	$data_api_key = get_option( 'TradePress_' . $api_id . '_api_key', '' );
}
?>

<div id="api-status-view" class="service-overview api-service-overview">
	<div class="api-service-main">
		<div class="service-logo">
			<?php if ( ! empty( $api_logo_url ) ) : ?>
				<img src="<?php echo esc_url( $api_logo_url ); ?>" alt="<?php echo esc_attr( $api_name ); ?>">
			<?php endif; ?>
			<span class="service-logo-fallback <?php echo ! empty( $api_logo_url ) ? 'is-hidden' : ''; ?>"><?php echo esc_html( strtoupper( substr( $api_name, 0, 2 ) ) ); ?></span>
		</div>

		<div class="service-details">
			<h3 class="service-name"><?php echo esc_html( $api_name ); ?></h3>
			<?php if ( ! empty( $api_description ) ) : ?>
				<p class="service-description"><?php echo esc_html( $api_description ); ?></p>
			<?php endif; ?>

			<div class="service-meta">
				<form method="post" id="tradepress-<?php echo esc_attr( $api_id ); ?>-operational-toggle" class="service-status-form">
					<?php wp_nonce_field( 'tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_operational_nonce' ); ?>
					<input type="hidden" name="api_id" value="<?php echo esc_attr( $api_id ); ?>">
					<input type="hidden" name="TradePress_switch_<?php echo esc_attr( $api_id ); ?>_api_services"
							value="<?php echo ( $api_enabled === 'yes' ) ? 'no' : 'yes'; ?>" id="operational-value-<?php echo esc_attr( $api_id ); ?>">
					<button type="submit" name="toggle_operational_status" class="service-status-toggle-button <?php echo $api_enabled === 'yes' ? 'operational' : 'non-operational'; ?>">
						<?php echo $api_enabled === 'yes' ? esc_html__( 'Operational', 'tradepress' ) : esc_html__( 'Disabled', 'tradepress' ); ?>
					</button>
				</form>

				<?php if ( ! $is_data_only_api ) : ?>
				<form method="post" id="tradepress-<?php echo esc_attr( $api_id ); ?>-trading-mode-toggle" class="trading-mode-button-form">
					<?php wp_nonce_field( 'tradepress_' . $api_id . '_api_settings', 'tradepress_' . $api_id . '_trading_mode_nonce' ); ?>
					<input type="hidden" name="api_id" value="<?php echo esc_attr( $api_id ); ?>">
					<input type="hidden" name="TradePress_api_<?php echo esc_attr( $api_id ); ?>_trading_mode"
							value="<?php echo $trading_mode === 'live' ? 'paper' : 'live'; ?>" id="trading-mode-value-<?php echo esc_attr( $api_id ); ?>">
					<button type="submit" name="toggle_trading_mode" class="trading-mode-toggle-button <?php echo esc_attr( $trading_mode ); ?>">
						<?php echo $trading_mode === 'live' ? esc_html__( 'Live Trading', 'tradepress' ) : esc_html__( 'Paper Trading', 'tradepress' ); ?>
					</button>
				</form>
				<?php endif; ?>
			</div>

			<div class="api-service-info">
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'Version:', 'tradepress' ); ?></span>
					<span class="info-value"><?php echo esc_html( $api_version ); ?></span>
				</div>
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'Endpoints:', 'tradepress' ); ?></span>
					<span class="info-value"><?php echo esc_html( number_format_i18n( $endpoint_count ) ); ?></span>
				</div>
				<div class="api-info-item">
					<span class="info-label"><?php esc_html_e( 'API Type:', 'tradepress' ); ?></span>
					<span class="info-value <?php echo $is_data_only_api ? 'data-only-api' : 'live-trading'; ?>">
						<?php echo $is_data_only_api ? esc_html__( 'Data Only', 'tradepress' ) : esc_html__( 'Trading', 'tradepress' ); ?>
					</span>
				</div>
				<?php if ( ! $is_data_only_api ) : ?>
					<div class="api-info-item">
						<span class="info-label"><?php esc_html_e( 'Mode:', 'tradepress' ); ?></span>
						<span class="info-value <?php echo $trading_mode === 'live' ? 'live-trading' : 'paper-trading'; ?>">
							<?php echo $trading_mode === 'live' ? esc_html__( 'Live', 'tradepress' ) : esc_html__( 'Paper', 'tradepress' ); ?>
						</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<aside class="api-service-status-panel" aria-label="<?php esc_attr_e( 'API status summary', 'tradepress' ); ?>">
		<div class="status-summary-row">
			<div class="status-indicator">
				<div class="status-dot <?php echo esc_attr( get_status_color( $real_local_status['status'] ) ); ?>"></div>
				<div>
					<strong><?php esc_html_e( 'Local', 'tradepress' ); ?></strong>
					<span><?php echo esc_html( $real_local_status['message'] ); ?></span>
				</div>
			</div>
			<div class="status-indicator">
				<div class="status-dot <?php echo esc_attr( get_status_color( $real_service_status['status'] ) ); ?>"></div>
				<div>
					<strong><?php esc_html_e( 'Service', 'tradepress' ); ?></strong>
					<span><?php echo esc_html( $real_service_status['message'] ); ?></span>
				</div>
			</div>
		</div>

		<div class="status-summary-grid">
			<div>
				<span class="summary-label"><?php esc_html_e( 'Credentials', 'tradepress' ); ?></span>
				<strong>
					<?php
					if ( isset( $real_local_status['status'] ) && 'requires_api_key' === $real_local_status['status'] ) {
						esc_html_e( 'Requires API Key', 'tradepress' );
					} elseif ( $is_data_only_api ) {
						echo empty( $data_api_key ) ? esc_html__( 'Missing', 'tradepress' ) : esc_html__( 'Configured', 'tradepress' );
					} else {
						$configured_count = 0;
						$configured_count += empty( $realmoney_apikey ) ? 0 : 1;
						$configured_count += empty( $realmoney_secretkey ) ? 0 : 1;
						$configured_count += empty( $papermoney_apikey ) ? 0 : 1;
						$configured_count += empty( $papermoney_secretkey ) ? 0 : 1;
						printf(
							/* translators: %1$d: configured credentials count, %2$d: total credentials count */
							esc_html__( '%1$d of %2$d', 'tradepress' ),
							(int) $configured_count,
							4
						);
					}
					?>
				</strong>
			</div>
			<div>
				<span class="summary-label"><?php esc_html_e( 'Latest Call', 'tradepress' ); ?></span>
				<strong><?php echo ! empty( $latest_api_call['endpoint'] ) ? esc_html( $latest_api_call['endpoint'] ) : esc_html__( 'None cached', 'tradepress' ); ?></strong>
			</div>
			<div>
				<span class="summary-label"><?php esc_html_e( 'Daily Limit', 'tradepress' ); ?></span>
				<strong><?php echo isset( $rate_limit_data['daily_quota'] ) && $rate_limit_data['daily_quota'] ? esc_html( number_format_i18n( $rate_limit_data['daily_quota'] ) ) : esc_html__( 'Unknown', 'tradepress' ); ?></strong>
			</div>
			<div>
				<span class="summary-label"><?php esc_html_e( 'Updated', 'tradepress' ); ?></span>
				<strong><?php echo ! empty( $latest_api_call['timestamp'] ) ? esc_html( $latest_api_call['timestamp'] ) : esc_html( $real_service_status['last_updated'] ); ?></strong>
			</div>
		</div>
	</aside>
</div>
