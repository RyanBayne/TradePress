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
		echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'In Development', 'tradepress' ) . '</strong> - ' . esc_html__( 'Volatility analysis will display here once live OHLC data has been imported for the requested symbol.', 'tradepress' ) . '</p></div>';
		echo '</div>';
	}
}

tradepress_volatility_analysis_tab_content();
