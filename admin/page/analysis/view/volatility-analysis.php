<?php
/**
 * TradePress Volatility Analysis Tab.
 *
 * @package TradePress/Admin/Analysis
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display the Volatility Analysis tab content.
 *
 * @return void
 * @version 1.1.0
 */
if ( ! function_exists( 'tradepress_volatility_analysis_tab_content' ) ) {
	function tradepress_volatility_analysis_tab_content() {
		$tab_mode = function_exists( 'tradepress_get_tab_mode' ) ? tradepress_get_tab_mode( 'analysis', 'volatility_analysis' ) : array(
			'mode'    => 'pending',
			'enabled' => true,
		);

		if ( empty( $tab_mode['enabled'] ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'This tab is currently disabled in Features settings.', 'tradepress' ) . '</p></div>';
			return;
		}

		echo '<div class="tradepress-volatility-analysis-container">';
		echo '<div class="tradepress-data-status-panel" data-mode="dev-only-demo" data-health="not_applicable">';
		echo '<h3>' . esc_html__( 'Volatility Analysis Data Status', 'tradepress' ) . '</h3>';
		echo '<table class="widefat fixed striped"><tbody>';
		echo '<tr><th scope="row">' . esc_html__( 'Data mode', 'tradepress' ) . '</th><td>' . esc_html__( 'Dev-only Demo', 'tradepress' ) . '</td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Source of truth', 'tradepress' ) . '</th><td>' . esc_html__( 'No stored volatility analysis table connected yet', 'tradepress' ) . '</td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Provider', 'tradepress' ) . '</th><td>' . esc_html__( 'Not selected for render path', 'tradepress' ) . '</td></tr>';
		echo '<tr><th scope="row">' . esc_html__( 'Queue behavior', 'tradepress' ) . '</th><td>' . esc_html__( 'No queue-backed volatility analysis exists from this view yet', 'tradepress' ) . '</td></tr>';
		echo '</tbody></table>';
		echo '</div>';
		echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'In Development', 'tradepress' ) . '</strong> - ' . esc_html__( 'Volatility analysis will display here once live OHLC data has been imported for the requested symbol.', 'tradepress' ) . '</p></div>';
		echo '</div>';
	}
}

tradepress_volatility_analysis_tab_content();
