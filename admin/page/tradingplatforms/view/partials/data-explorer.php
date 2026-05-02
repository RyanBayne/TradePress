<?php
/**
 * Partial: Data Explorer
 *
 * This partial template includes the data explorer component for API tabs.
 * Required variables: $api_id, $api_name, $explorer_data_types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verify required variables are set
if ( ! isset( $api_id ) || ! isset( $api_name ) ) {
	return;
}

// Default data types if not provided
if ( ! isset( $explorer_data_types ) || ! is_array( $explorer_data_types ) ) {
	$explorer_data_types = array(
		'quote'      => __( 'Quote Data', 'tradepress' ),
		'historical' => __( 'Historical Data', 'tradepress' ),
		'company'    => __( 'Company Data', 'tradepress' ),
	);
}
?>

<div class="data-explorer">
	<div class="tool-header">
		<h3><?php esc_html_e( 'Data Explorer', 'tradepress' ); ?></h3>
	</div>

	<div class="tool-section">
		<div class="data-explorer-form">
			<?php
			// Generate unique form ID to avoid duplicate IDs when multiple instances are loaded
			$form_id     = 'data-explorer-form-' . esc_attr( $api_id ) . '-' . uniqid();
			$symbol_id   = 'data-explorer-symbol-' . esc_attr( $api_id ) . '-' . uniqid();
			$endpoint_id = 'data-explorer-endpoint-' . esc_attr( $api_id ) . '-' . uniqid();
			?>
			<form method="post" id="<?php echo esc_attr( $form_id ); ?>">
				<div class="symbol-input">
					<label for="<?php echo esc_attr( $symbol_id ); ?>"><?php esc_html_e( 'Symbol', 'tradepress' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $symbol_id ); ?>" name="symbol" placeholder="Enter symbol (e.g. AAPL.US)" class="regular-text">
				</div>

				<div class="endpoint-select">
					<label for="<?php echo esc_attr( $endpoint_id ); ?>"><?php esc_html_e( 'Endpoint', 'tradepress' ); ?></label>
					<select id="<?php echo esc_attr( $endpoint_id ); ?>" name="endpoint" class="regular-text">
						<option value=""><?php esc_html_e( '-- Select Endpoint --', 'tradepress' ); ?></option>
						<?php if ( ! empty( $endpoints ) && is_array( $endpoints ) ) : ?>
							<?php foreach ( $endpoints as $endpoint ) : ?>
								<?php
								$endpoint_status = isset( $endpoint['status'] ) ? $endpoint['status'] : 'unknown';
								if ( in_array( $endpoint_status, array( 'inactive', 'outage' ), true ) ) {
									continue;
								}

								$endpoint_key   = isset( $endpoint['key'] ) ? $endpoint['key'] : $endpoint['name'];
								$endpoint_label = isset( $endpoint['name'] ) ? $endpoint['name'] : $endpoint_key;
								if ( ! empty( $endpoint['endpoint'] ) ) {
									$endpoint_label .= ' - ' . $endpoint['endpoint'];
								}
								?>
								<option value="<?php echo esc_attr( $endpoint_key ); ?>"><?php echo esc_html( $endpoint_label ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="form-actions">
					<button type="submit" name="explore_data" class="button button-primary">
						<?php esc_html_e( 'Explore Data', 'tradepress' ); ?>
					</button>
				</div>
				
				<?php
				// Generate a unique nonce field ID for each form instance
				$nonce_id = 'tradepress_explore_data_nonce_' . $api_id . '_' . uniqid();
				wp_nonce_field( 'tradepress_explore_data_nonce', $nonce_id );
				?>
			</form>
		</div>
	</div>

	<div class="data-results">
		<div class="results-placeholder">
			<p><?php esc_html_e( 'Enter a symbol and select an endpoint to explore data.', 'tradepress' ); ?></p>
		</div>
	</div>
</div>
