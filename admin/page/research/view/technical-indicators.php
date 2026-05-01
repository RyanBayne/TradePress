<?php
/**
 * TradePress Technical Indicators Tab
 *
 * Displays technical analysis indicators and educational content for the Research page
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-06-19 15:30
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display the Technical Indicators tab content
 *
 * @version 1.0.0
 */
function tradepress_technical_indicators_tab_content() {
	?>
	<div class="tradepress-technical-indicators-container">
		<div class="tradepress-research-section">
			<h2><?php esc_html_e( 'Technical Indicators', 'tradepress' ); ?></h2>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Technical indicator research will display here once imported candle data is connected to this view.', 'tradepress' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}
?>
