<?php
/**
 * TradePress Trading Area - SEES Demo Tab View
 *
 * Scoring Engine Execution System (SEES) - Demo Mode.
 * Shows a score-ranked securities list with optional auto re-sorting.
 *
 * @package TradePress\Admin\trading\Views
 * @version 1.1.0
 * @since   NEXT_VERSION_NUMBER
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style(
	'tradepress-sees-demo',
	TRADEPRESS_PLUGIN_URL . 'assets/css/pages/sees-demo.css',
	array(),
	TRADEPRESS_VERSION
);

wp_enqueue_script(
	'tradepress-sees-demo',
	TRADEPRESS_PLUGIN_URL . 'assets/js/sees-demo.js',
	array( 'jquery' ),
	TRADEPRESS_VERSION,
	true
);

wp_localize_script(
	'tradepress-sees-demo',
	'tradepress_sees_demo_nonce',
	array(
		'nonce'           => wp_create_nonce( 'tradepress_fetch_sees_demo_data_nonce' ),
		'loading_text'    => __( 'Loading SEES data...', 'tradepress' ),
		'no_data_message' => __( 'No securities available.', 'tradepress' ),
		'error_message'   => __( 'Unable to load SEES data.', 'tradepress' ),
	)
);

$current_mode = 'Live';
if ( function_exists( 'is_demo_mode' ) && is_demo_mode() ) {
	$current_mode = 'Demo';
} elseif ( defined( 'TRADEPRESS_DEMO_MODE' ) && TRADEPRESS_DEMO_MODE ) {
	$current_mode = 'Demo';
}
?>

<div class="wrap tradepress-sees-demo">
	<div class="tradepress-sees-header-wrapper">
		<h2>
			<?php esc_html_e( 'SEES - Demo Mode', 'tradepress' ); ?>
			<span class="tp-demo-feature-marker dashicons dashicons-warning" title="<?php esc_attr_e( 'Demo data feature', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Demo data feature', 'tradepress' ); ?>"></span>
		</h2>
		<div class="tradepress-sees-info-box">
			<h4><?php esc_html_e( 'Configuration Overview', 'tradepress' ); ?></h4>
			<ul>
				<li><strong><?php esc_html_e( 'Mode:', 'tradepress' ); ?></strong> <?php echo esc_html( $current_mode ); ?></li>
				<li><strong><?php esc_html_e( 'Strategy:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Default', 'tradepress' ); ?></li>
				<li><strong><?php esc_html_e( 'Asset Class:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Stocks', 'tradepress' ); ?></li>
				<li><strong><?php esc_html_e( 'Trade Horizon:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Day Trading', 'tradepress' ); ?></li>
			</ul>
		</div>
	</div>

	<div class="tradepress-data-status-panel" data-mode="dev-only-demo" data-health="not_applicable">
		<h3><?php esc_html_e( 'SEES Demo Data Status', 'tradepress' ); ?></h3>
		<table class="widefat fixed striped">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Data mode', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Dev-only Demo', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Source of truth', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Generated from bundled test symbol metadata for Developer Mode review only', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Provider', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No market-data provider is selected in this demo path', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Queue behavior', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No queue trigger; future release-facing SEES output must read stored scoring results', 'tradepress' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="tp-phase-panel tp-phase-panel-demo" role="note">
		<div class="tp-phase-panel-header">
			<span class="tp-phase-panel-icon dashicons dashicons-warning" aria-hidden="true"></span>
			<strong><?php esc_html_e( 'Demo data: generated SEES ranking preview', 'tradepress' ); ?></strong>
		</div>
		<p><?php esc_html_e( 'This tab uses bundled test symbol metadata and generated scores, prices, and percentage changes so the ranking and auto re-sorting interface can be reviewed in Developer Mode.', 'tradepress' ); ?></p>
		<p class="tp-phase-next-step"><?php esc_html_e( 'Next live-data step: replace the demo AJAX source with stored scoring results from imported provider data, then keep this generator only as a diagnostics fixture.', 'tradepress' ); ?></p>
	</div>

	<div class="tradepress-sees-controls">
		<button id="refresh-sees-data" class="button button-primary"><?php esc_html_e( 'Refresh Data', 'tradepress' ); ?></button>
		<button id="tradepress-start-auto-refresh-sees-data" class="button button-secondary"><?php esc_html_e( 'Start Auto-Re-Sorting', 'tradepress' ); ?></button>
		<button id="tradepress-stop-auto-refresh-sees-data" class="button button-secondary tradepress-hidden"><?php esc_html_e( 'Stop Auto-Re-Sorting', 'tradepress' ); ?></button>
		<span id="tradepress-sees-last-updated"></span>
	</div>

	<div id="sees-demo-container" class="sees-demo-container">
		<p class="loading-message"><?php esc_html_e( 'Loading SEES data...', 'tradepress' ); ?></p>
	</div>
</div>
