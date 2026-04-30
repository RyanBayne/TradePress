<?php
/**
 * TradePress Trading Area - SEES Legacy View
 *
 * Legacy generated-data preview. Demo output is no longer supported.
 *
 * @package TradePress\Admin\trading\Views
 * @version 1.0.0
 * @since   NEXT_VERSION_NUMBER
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'SEES preview removed.', 'tradepress' ) . '</strong> ' . esc_html__( 'Generated demo trading data is no longer supported. This view will return when it is backed by imported or live data.', 'tradepress' ) . '</p></div>';
return;
?>

<div class="wrap tradepress-sees-demo">
	<div class="tradepress-sees-layout">
		<!-- Left Column: Controls and Configuration -->
		<div class="tradepress-sees-left-column">
			<!-- Configuration Overview Box -->
			<div class="tradepress-sees-info-box">
				<h4><?php esc_html_e( 'Configuration Overview', 'tradepress' ); ?></h4>
				<ul>
					<li><strong><?php esc_html_e( 'Mode:', 'tradepress' ); ?></strong> <?php echo esc_html( $current_mode ); ?></li>
					<li><strong><?php esc_html_e( 'Strategy:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Default', 'tradepress' ); ?></li>
					<li><strong><?php esc_html_e( 'Asset Class:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Stocks', 'tradepress' ); ?></li>
					<li><strong><?php esc_html_e( 'Trade Horizon:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Day Trading', 'tradepress' ); ?></li>
				</ul>
			</div>

			<!-- Demo Controls -->
			<div class="sees-demo-controls">
				<h4><?php esc_html_e( 'Controls', 'tradepress' ); ?></h4>
				<button id="refresh-sees-data" class="button-primary"><?php esc_html_e( 'Refresh Data', 'tradepress' ); ?></button>
				<button id="tradepress-start-auto-refresh-sees-data" class="button button-secondary"><?php esc_html_e( 'Start Auto-Refresh', 'tradepress' ); ?></button>
				<button id="tradepress-stop-auto-refresh-sees-data" class="button button-secondary tradepress-hidden"><?php esc_html_e( 'Stop Auto-Refresh', 'tradepress' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=TradePress&tab=sees' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Settings', 'tradepress' ); ?></a>
			</div>
		</div>

		<!-- Right Column: Security Boxes -->
		<div class="tradepress-sees-right-column">
			<div id="sees-demo-container" class="sees-demo-container">
				<p class="loading-message"><?php esc_html_e( 'Loading SEES data...', 'tradepress' ); ?></p>
				<!-- Security boxes will be loaded here by JavaScript -->
			</div>
		</div>
	</div>
</div>
