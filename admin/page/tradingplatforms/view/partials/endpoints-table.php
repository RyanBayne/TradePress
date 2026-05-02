<?php
/**
 * Partial: Endpoints Table
 *
 * Endpoint catalogue and latest endpoint test summary for API tabs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $api_id ) || ! isset( $endpoints ) || ! is_array( $endpoints ) ) {
	return;
}

if ( ! function_exists( 'tradepress_api_tab_format_test_payload' ) ) {
	/**
	 * Format endpoint test payloads for display.
	 *
	 * @param mixed $payload Response or error payload.
	 * @return string
	 */
	function tradepress_api_tab_format_test_payload( $payload ) {
		if ( is_array( $payload ) || is_object( $payload ) ) {
			return wp_json_encode( $payload, JSON_PRETTY_PRINT );
		}

		if ( is_bool( $payload ) ) {
			return $payload ? 'true' : 'false';
		}

		if ( null === $payload || '' === $payload ) {
			return '';
		}

		return (string) $payload;
	}
}

if ( ! class_exists( 'TradePress_Endpoint_Tester' ) && file_exists( TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/endpoint-tester.php' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/endpoint-tester.php';
}

$api_name = isset( $api_name ) ? $api_name : ucfirst( $api_id );

$db_endpoints = function_exists( 'TradePress_db_get_all_endpoints' ) ? TradePress_db_get_all_endpoints( 'endpoint_name', 'ASC' ) : array();
$usage_counts = array();
if ( ! empty( $db_endpoints ) ) {
	foreach ( $db_endpoints as $db_endpoint ) {
		$db_endpoint_name = isset( $db_endpoint->endpoint_name ) ? $db_endpoint->endpoint_name : ( $db_endpoint->name ?? '' );
		$db_endpoint_key  = isset( $db_endpoint->endpoint_key ) ? $db_endpoint->endpoint_key : ( $db_endpoint->key ?? '' );
		$counter          = isset( $db_endpoint->counter ) ? (int) $db_endpoint->counter : 0;

		if ( $db_endpoint_name ) {
			$usage_counts[ $db_endpoint_name ] = $counter;
		}
		if ( $db_endpoint_key ) {
			$usage_counts[ $db_endpoint_key ] = $counter;
		}
	}
}

$method_options       = array();
$latest_test          = null;
$latest_test_endpoint = '';
$latest_test_key      = '';
$last_test            = get_transient( 'tradepress_last_endpoint_test' );

if ( $last_test && isset( $last_test['platform'] ) && $last_test['platform'] === $api_id ) {
	$latest_test_endpoint = isset( $last_test['endpoint'] ) ? $last_test['endpoint'] : '';
	$latest_test_key      = isset( $last_test['endpoint_key'] ) && $last_test['endpoint_key'] ? $last_test['endpoint_key'] : $latest_test_endpoint;
	$transient_key        = 'tradepress_endpoint_test_' . md5( $api_id . '_' . $latest_test_endpoint );
	$latest_test          = get_transient( $transient_key );
}

$latest_success = is_array( $latest_test ) && ! empty( $latest_test['success'] );
$latest_payload = '';
if ( is_array( $latest_test ) ) {
	if ( $latest_success && array_key_exists( 'data', $latest_test ) ) {
		$latest_payload = tradepress_api_tab_format_test_payload( $latest_test['data'] );
	} elseif ( array_key_exists( 'raw_response', $latest_test ) ) {
		$latest_payload = tradepress_api_tab_format_test_payload( $latest_test['raw_response'] );
	}
}
?>

<div class="api-endpoints-layout">
	<div class="api-endpoints-main">
		<div class="section-header endpoints-section-header">
			<h3><?php esc_html_e( 'Available Endpoints', 'tradepress' ); ?></h3>
			<div class="endpoints-filter-controls">
				<input type="search" id="endpoints-search" class="regular-text" placeholder="<?php esc_attr_e( 'Search endpoints', 'tradepress' ); ?>">
				<select id="endpoints-method-filter">
					<option value=""><?php esc_html_e( 'All methods', 'tradepress' ); ?></option>
					<?php
					foreach ( $endpoints as $endpoint ) {
						$method = isset( $endpoint['method'] ) ? strtoupper( $endpoint['method'] ) : 'GET';
						$method_options[ $method ] = $method;
					}
					foreach ( $method_options as $method ) :
						?>
						<option value="<?php echo esc_attr( strtolower( $method ) ); ?>"><?php echo esc_html( $method ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<table class="endpoints-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Endpoint', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Description', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Method', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Usage', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Status', 'tradepress' ); ?></th>
					<th><?php esc_html_e( 'Test', 'tradepress' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $endpoints as $endpoint ) : ?>
					<?php
					$endpoint_key     = isset( $endpoint['key'] ) ? $endpoint['key'] : sanitize_key( $endpoint['name'] ?? '' );
					$endpoint_name    = isset( $endpoint['name'] ) ? $endpoint['name'] : $endpoint_key;
					$endpoint_path    = isset( $endpoint['endpoint'] ) ? $endpoint['endpoint'] : '';
					$endpoint_desc    = isset( $endpoint['description'] ) ? $endpoint['description'] : '';
					$endpoint_method  = isset( $endpoint['method'] ) ? strtoupper( $endpoint['method'] ) : 'GET';
					$endpoint_status  = isset( $endpoint['status'] ) ? $endpoint['status'] : 'unknown';
					$endpoint_counter = $usage_counts[ $endpoint_key ] ?? ( $usage_counts[ $endpoint_name ] ?? 0 );
					$is_latest_test   = $latest_test_key && $latest_test_key === $endpoint_key;
					$is_testable      = ! in_array( $endpoint_status, array( 'inactive', 'outage' ), true );
					?>
					<tr class="endpoint-summary-row <?php echo $is_latest_test ? 'is-latest-endpoint-test' : ''; ?>" data-endpoint-key="<?php echo esc_attr( $endpoint_key ); ?>">
						<td>
							<div class="endpoint-name"><?php echo esc_html( $endpoint_name ); ?></div>
							<?php if ( $endpoint_path ) : ?>
								<div class="endpoint-path"><?php echo esc_html( $endpoint_path ); ?></div>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $endpoint_desc ); ?></td>
						<td><span class="method-badge method-<?php echo esc_attr( strtolower( $endpoint_method ) ); ?>"><?php echo esc_html( $endpoint_method ); ?></span></td>
						<td class="usage-count"><?php echo esc_html( number_format_i18n( $endpoint_counter ) ); ?></td>
						<td>
							<div class="status-indicator endpoint-status">
								<div class="status-dot <?php echo esc_attr( get_status_color( $endpoint_status ) ); ?>"></div>
								<div><?php echo esc_html( ucfirst( $endpoint_status ) ); ?></div>
							</div>
						</td>
						<td>
							<?php if ( $is_testable ) : ?>
								<form class="test-endpoint-form" action="" method="post">
									<input type="hidden" name="tradepress_test_endpoint" value="1">
									<input type="hidden" name="endpoint" value="<?php echo esc_attr( $endpoint_key ); ?>">
									<input type="hidden" name="endpoint_key" value="<?php echo esc_attr( $endpoint_key ); ?>">
									<input type="hidden" name="platform" value="<?php echo esc_attr( $api_id ); ?>">
									<?php wp_nonce_field( 'tradepress_test_endpoint_nonce', 'tradepress_test_endpoint_nonce_' . $endpoint_key ); ?>
									<button type="submit" class="button button-secondary test-endpoint"
											data-endpoint="<?php echo esc_attr( $endpoint_key ); ?>"
											data-api="<?php echo esc_attr( $api_id ); ?>">
										<?php esc_html_e( 'Test', 'tradepress' ); ?>
									</button>
								</form>
							<?php else : ?>
								<button type="button" class="button test-button maintenance" disabled>
									<?php esc_html_e( 'Unavailable', 'tradepress' ); ?>
								</button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<aside class="api-endpoints-sidebar">
		<div class="directive-details-container endpoint-test-summary">
			<div class="section-header">
				<h3><?php esc_html_e( 'Latest Endpoint Test', 'tradepress' ); ?></h3>
			</div>

			<?php if ( is_array( $latest_test ) ) : ?>
				<div class="endpoint-test-state <?php echo $latest_success ? 'endpoint-test-state-success' : 'endpoint-test-state-error'; ?>">
					<span class="status-dot <?php echo $latest_success ? 'status-green' : 'status-red'; ?>"></span>
					<strong><?php echo $latest_success ? esc_html__( 'Success', 'tradepress' ) : esc_html__( 'Connection Error', 'tradepress' ); ?></strong>
				</div>

				<dl class="endpoint-test-meta">
					<dt><?php esc_html_e( 'Platform', 'tradepress' ); ?></dt>
					<dd><?php echo esc_html( $api_name ); ?></dd>
					<dt><?php esc_html_e( 'Endpoint', 'tradepress' ); ?></dt>
					<dd><?php echo esc_html( $latest_test_endpoint ); ?></dd>
					<dt><?php esc_html_e( 'HTTP Status', 'tradepress' ); ?></dt>
					<dd><?php echo esc_html( $latest_test['status_code'] ?? __( 'Unknown', 'tradepress' ) ); ?></dd>
					<dt><?php esc_html_e( 'Environment', 'tradepress' ); ?></dt>
					<dd><?php echo esc_html( $latest_test['environment'] ?? __( 'Configured API', 'tradepress' ) ); ?></dd>
					<dt><?php esc_html_e( 'Time', 'tradepress' ); ?></dt>
					<dd><?php echo esc_html( $latest_test['timestamp'] ?? current_time( 'mysql' ) ); ?></dd>
				</dl>

				<?php if ( ! $latest_success && ! empty( $latest_test['message'] ) ) : ?>
					<div class="error-message"><?php echo esc_html( $latest_test['message'] ); ?></div>
				<?php endif; ?>

				<div class="api-response-section">
					<h4><?php esc_html_e( 'Cached Response', 'tradepress' ); ?></h4>
					<textarea class="api-response-text" readonly><?php echo esc_textarea( $latest_payload ); ?></textarea>
					<span class="copy-hint"><?php esc_html_e( 'Click to select all. Ctrl+C to copy.', 'tradepress' ); ?></span>
				</div>

				<?php if ( ! $latest_success && ! empty( $latest_test['error_report'] ) ) : ?>
					<div class="ai-report-section">
						<h4><?php esc_html_e( 'Troubleshooting Report', 'tradepress' ); ?></h4>
						<textarea class="ai-report-text" readonly><?php echo esc_textarea( $latest_test['error_report'] ); ?></textarea>
						<span class="copy-hint"><?php esc_html_e( 'Click to select all. Ctrl+C to copy.', 'tradepress' ); ?></span>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<div class="directive-details-placeholder">
					<p><?php esc_html_e( 'No endpoint test has been cached for this platform yet.', 'tradepress' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</aside>
</div>
