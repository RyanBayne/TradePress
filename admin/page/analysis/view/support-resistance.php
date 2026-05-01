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

if ( ! class_exists( 'SupportResistanceLevels' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/support-resistance-levels.php';
}

$symbol      = isset( $_POST['symbol'] ) ? sanitize_text_field( wp_unslash( $_POST['symbol'] ) ) : '';
$levels_type = isset( $_POST['levels_type'] ) ? sanitize_text_field( wp_unslash( $_POST['levels_type'] ) ) : 'both';
$results     = array();
$notice      = '';

if ( ! empty( $symbol ) ) {
	if ( ! class_exists( 'TradePress_Financial_API_Service' ) || ! class_exists( 'SupportResistanceLevels' ) ) {
		$notice = __( 'The support and resistance analysis engine is not available yet. Results will appear here once live OHLC data import is stable.', 'tradepress' );
	} else {
		$api_service  = new TradePress_Financial_API_Service();
		$sr_analyzer  = new SupportResistanceLevels( $symbol, $api_service );
		$zones_result = $sr_analyzer->find_support_resistance_zones();

		if ( is_array( $zones_result ) ) {
			if ( 'resistance' === $levels_type || 'both' === $levels_type ) {
				$results['resistance'] = array(
					'current_price'     => isset( $zones_result['current_price'] ) ? (float) $zones_result['current_price'] : 0,
					'highly_overlapped' => isset( $zones_result['resistance_zones']['highly_overlapped'] ) ? $zones_result['resistance_zones']['highly_overlapped'] : array(),
					'well_overlapped'   => isset( $zones_result['resistance_zones']['well_overlapped'] ) ? $zones_result['resistance_zones']['well_overlapped'] : array(),
				);
			}

			if ( 'support' === $levels_type || 'both' === $levels_type ) {
				$results['support'] = array(
					'current_price'     => isset( $zones_result['current_price'] ) ? (float) $zones_result['current_price'] : 0,
					'highly_overlapped' => isset( $zones_result['support_zones']['highly_overlapped'] ) ? $zones_result['support_zones']['highly_overlapped'] : array(),
					'well_overlapped'   => isset( $zones_result['support_zones']['well_overlapped'] ) ? $zones_result['support_zones']['well_overlapped'] : array(),
				);
			}
		}

		if ( empty( $results ) ) {
			$notice = __( 'No support or resistance levels were found for that symbol using available live data.', 'tradepress' );
		}
	}
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
	<div class="support-resistance-form-container">
		<form method="post" action="" class="support-resistance-form">
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
