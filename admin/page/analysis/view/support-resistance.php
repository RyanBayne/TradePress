<?php
/**
 * TradePress Analysis - Support & Resistance tab.
 *
 * @package TradePress/Admin/Analysis
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tradepress_get_tab_mode' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/functions/function.tradepress-features-helpers.php';
}

$tab_mode = tradepress_get_tab_mode( 'analysis', 'support_resistance' );

if ( ! $tab_mode['enabled'] ) {
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'This tab is currently disabled in Features settings.', 'tradepress' ) . '</p></div>';
	return;
}

$symbol         = '';
$levels_type    = 'both';
$allowed_levels = array( 'both', 'resistance', 'support' );
$results     = array();
$notice      = '';

if ( isset( $_POST['tradepress_support_resistance_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tradepress_support_resistance_nonce'] ) ), 'tradepress_support_resistance' ) ) {
	$symbol      = isset( $_POST['symbol'] ) ? sanitize_text_field( wp_unslash( $_POST['symbol'] ) ) : '';
	$levels_type = isset( $_POST['levels_type'] ) ? sanitize_key( wp_unslash( $_POST['levels_type'] ) ) : 'both';

	if ( ! in_array( $levels_type, $allowed_levels, true ) ) {
		$levels_type = 'both';
	}
}

if ( isset( $_POST['symbol'] ) && empty( $symbol ) ) {
	$notice = __( 'Unable to process the request. Please reload the page and try again.', 'tradepress' );
} elseif ( ! empty( $symbol ) ) {
	$notice = __( 'Support and resistance analysis is not connected to a stored OHLC data source yet. Results will appear here after price history import and queued analysis are wired.', 'tradepress' );
}

if ( ! function_exists( 'tradepress_render_analysis_level_zones' ) ) {
	function tradepress_render_analysis_level_zones( $zones ) {
		if ( empty( $zones ) ) {
			echo '<p>' . esc_html__( 'No zones found.', 'tradepress' ) . '</p>';
			return;
		}

		echo '<div class="levels-grid">';
		foreach ( $zones as $zone ) {
			$methods = isset( $zone['methods'] ) && is_array( $zone['methods'] ) ? $zone['methods'] : array();
			echo '<div class="level-card">';
			echo '<div class="price-range"><span class="zone-label">' . esc_html__( 'Zone:', 'tradepress' ) . '</span> ';
			echo '<span class="price-values">$' . esc_html( number_format( (float) $zone['min_price'], 2 ) ) . ' - $' . esc_html( number_format( (float) $zone['max_price'], 2 ) ) . '</span></div>';
			echo '<div class="confirmation"><span class="confirmation-label">' . esc_html__( 'Confirmed by:', 'tradepress' ) . '</span> ';
			echo '<span class="confirmation-count">' . esc_html( count( $methods ) ) . ' ' . esc_html__( 'methods', 'tradepress' ) . '</span></div>';
			echo '</div>';
		}
		echo '</div>';
	}
}
?>

<div class="support-resistance-container">
	<div class="tradepress-data-status-panel" data-mode="dev-only-demo" data-health="not_applicable">
		<h3><?php esc_html_e( 'Support & Resistance Data Status', 'tradepress' ); ?></h3>
		<table class="widefat fixed striped">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Data mode', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Dev-only Demo', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Source of truth', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No stored OHLC analysis table connected yet', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Provider', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Not selected for render path', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Queue behavior', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No queue-backed support/resistance analysis exists from this view yet', 'tradepress' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="support-resistance-form-container">
		<form method="post" action="" class="support-resistance-form">
			<?php wp_nonce_field( 'tradepress_support_resistance', 'tradepress_support_resistance_nonce' ); ?>
			<div class="form-row">
				<label for="symbol"><?php esc_html_e( 'Symbol:', 'tradepress' ); ?></label>
				<input type="text" id="symbol" name="symbol" value="<?php echo esc_attr( $symbol ); ?>" placeholder="AAPL, MSFT, NVDA..." required>
			</div>
			<div class="form-row">
				<label for="levels_type"><?php esc_html_e( 'Levels to Find:', 'tradepress' ); ?></label>
				<select id="levels_type" name="levels_type">
					<option value="both" <?php selected( $levels_type, 'both' ); ?>><?php esc_html_e( 'Both Support & Resistance', 'tradepress' ); ?></option>
					<option value="resistance" <?php selected( $levels_type, 'resistance' ); ?>><?php esc_html_e( 'Resistance Only', 'tradepress' ); ?></option>
					<option value="support" <?php selected( $levels_type, 'support' ); ?>><?php esc_html_e( 'Support Only', 'tradepress' ); ?></option>
				</select>
			</div>
			<div class="form-row">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Find Levels', 'tradepress' ); ?></button>
			</div>
		</form>
	</div>

	<?php if ( ! empty( $notice ) ) : ?>
		<div class="notice notice-info"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $results ) ) : ?>
		<div class="support-resistance-results">
			<h3><?php echo esc_html( sprintf( __( 'Results for %s', 'tradepress' ), strtoupper( $symbol ) ) ); ?></h3>

			<?php foreach ( $results as $type => $result ) : ?>
				<div class="<?php echo esc_attr( $type ); ?>-results">
					<h4><?php echo esc_html( 'resistance' === $type ? __( 'Resistance Levels', 'tradepress' ) : __( 'Support Levels', 'tradepress' ) ); ?></h4>
					<div class="current-price">
						<strong><?php esc_html_e( 'Current Price:', 'tradepress' ); ?></strong>
						$<?php echo esc_html( number_format( (float) $result['current_price'], 2 ) ); ?>
					</div>

					<div class="levels-section">
						<h5><?php esc_html_e( 'Strong Zones', 'tradepress' ); ?></h5>
						<?php tradepress_render_analysis_level_zones( $result['highly_overlapped'] ); ?>
					</div>

					<div class="levels-section">
						<h5><?php esc_html_e( 'Moderate Zones', 'tradepress' ); ?></h5>
						<?php tradepress_render_analysis_level_zones( $result['well_overlapped'] ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
