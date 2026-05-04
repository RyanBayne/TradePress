<?php
/**
 * TradePress Trading Area - SEES Diagnostics Tab View
 *
 * A 3-column diagnostics workspace:
 * 1) controls, 2) symbol cards, 3) algorithm trace visualization.
 *
 * @package TradePress\Admin\trading\Views
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap tradepress-sees-diagnostics-wrap">

	<div class="tradepress-data-status-panel" data-mode="dev-only-demo" data-health="not_applicable">
		<table class="widefat fixed striped">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Data mode', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Dev-only Demo', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Strategy source', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Stored scoring strategy records; trading-mode traces use this transitional storage until dedicated trading strategy tables are active', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Symbol source', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Bundled test symbol metadata for Developer Mode diagnostics only', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Provider', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No external market-data provider is called by this render path or AJAX trace path', 'tradepress' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="tradepress-sees-diagnostics-grid">
		<section class="tp-sees-col tp-sees-controls" aria-label="SEES Controls">
			<h3><?php esc_html_e( 'Controls', 'tradepress' ); ?></h3>

			<label for="tp-sees-trace-mode">
				<?php esc_html_e( 'Trace mode', 'tradepress' ); ?>
				<span class="tp-dev-mode-input-marker dashicons dashicons-admin-tools" title="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>"></span>
			</label>
			<select id="tp-sees-trace-mode">
				<option value="scoring" selected><?php esc_html_e( 'Scoring Strategy (scoring directives)', 'tradepress' ); ?></option>
				<option value="trading"><?php esc_html_e( 'Trading Strategy (standard indicators)', 'tradepress' ); ?></option>
			</select>

			<label for="tp-sees-strategy-select">
				<?php esc_html_e( 'Strategy', 'tradepress' ); ?>
				<span class="tp-dev-mode-input-marker dashicons dashicons-admin-tools" title="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>"></span>
			</label>
			<select id="tp-sees-strategy-select"></select>

			<label for="tp-sees-selected-symbol">
				<?php esc_html_e( 'Diagnostic test symbol', 'tradepress' ); ?>
				<span class="tp-dev-mode-input-marker dashicons dashicons-admin-tools" title="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>"></span>
			</label>
			<select id="tp-sees-selected-symbol"></select>

			<label for="tp-sees-max-symbols">
				<?php esc_html_e( 'Card list size', 'tradepress' ); ?>
				<span class="tp-dev-mode-input-marker dashicons dashicons-admin-tools" title="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>"></span>
			</label>
			<select id="tp-sees-max-symbols">
				<option value="8">8</option>
				<option value="12" selected>12</option>
				<option value="20">20</option>
				<option value="30">30</option>
			</select>

			<label for="tp-sees-refresh-interval">
				<?php esc_html_e( 'Auto-refresh interval', 'tradepress' ); ?>
				<span class="tp-dev-mode-input-marker dashicons dashicons-admin-tools" title="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Developer Mode diagnostics input', 'tradepress' ); ?>"></span>
			</label>
			<select id="tp-sees-refresh-interval">
				<option value="5000">5s</option>
				<option value="10000" selected>10s</option>
				<option value="20000">20s</option>
				<option value="30000">30s</option>
			</select>

			<div class="tp-sees-control-buttons">
				<button id="tp-sees-refresh-now" class="button button-primary"><?php esc_html_e( 'Refresh Now', 'tradepress' ); ?></button>
				<button id="tp-sees-start-auto" class="button button-secondary"><?php esc_html_e( 'Start Auto', 'tradepress' ); ?></button>
				<button id="tp-sees-stop-auto" class="button button-secondary" disabled><?php esc_html_e( 'Stop Auto', 'tradepress' ); ?></button>
			</div>
			<p id="tp-sees-auto-status" class="description"></p>

			<div class="tp-sees-notes">
				<strong><?php esc_html_e( 'Usage notes', 'tradepress' ); ?></strong>
				<ul>
					<li><?php esc_html_e( 'Red tools icons mark controls that are available only for Developer Mode diagnostics.', 'tradepress' ); ?></li>
					<li><?php esc_html_e( 'This selector is only a Developer Mode diagnostics input for tracing one bundled test symbol.', 'tradepress' ); ?></li>
					<li><?php esc_html_e( 'Production SEES symbol inputs should come from watchlists, strategy scope, scans, or stored scoring runs.', 'tradepress' ); ?></li>
					<li><?php esc_html_e( 'Trace steps are loaded from the selected stored strategy components; a strategy with no active components stops before evaluation.', 'tradepress' ); ?></li>
				</ul>
			</div>
		</section>

		<section class="tp-sees-col tp-sees-cards" aria-label="Symbol Cards">
			<h3>
				<?php esc_html_e( 'Symbol Cards', 'tradepress' ); ?>
				<span class="tp-demo-feature-marker dashicons dashicons-warning" title="<?php esc_attr_e( 'Demo data feature', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Demo data feature', 'tradepress' ); ?>"></span>
			</h3>
			<div class="tp-phase-panel tp-phase-panel-demo" role="note">
				<div class="tp-phase-panel-header">
					<span class="tp-phase-panel-icon dashicons dashicons-warning" aria-hidden="true"></span>
					<strong><?php esc_html_e( 'Demo data: bundled symbols and simulated market movement', 'tradepress' ); ?></strong>
				</div>
				<p><?php esc_html_e( 'These cards refresh from bundled test symbol metadata. Price and change values are intentionally regenerated so the sorting and card UI can be tested; this is not live market data and it is not reading stored SEES scoring runs yet.', 'tradepress' ); ?></p>
				<p class="tp-phase-next-step"><?php esc_html_e( 'Next live-data step: replace this card source with stored scoring results produced from imported provider data, then keep the demo generator available only as a diagnostic fixture.', 'tradepress' ); ?></p>
			</div>
			<div id="tp-sees-symbol-cards" class="tp-sees-symbol-cards"></div>
		</section>

		<section class="tp-sees-col tp-sees-trace" aria-label="Algorithm Visual Trace">
			<h3>
				<?php esc_html_e( 'Algorithm Visual Trace', 'tradepress' ); ?>
				<span class="tp-demo-feature-marker dashicons dashicons-warning" title="<?php esc_attr_e( 'Demo simulation feature', 'tradepress' ); ?>" aria-label="<?php esc_attr_e( 'Demo simulation feature', 'tradepress' ); ?>"></span>
			</h3>
			<div class="tp-phase-panel tp-phase-panel-demo" role="note">
				<div class="tp-phase-panel-header">
					<span class="tp-phase-panel-icon dashicons dashicons-warning" aria-hidden="true"></span>
					<strong><?php esc_html_e( 'Mixed state: real strategy configuration, simulated evaluation inputs', 'tradepress' ); ?></strong>
				</div>
				<p><?php esc_html_e( 'The selected strategy, components, threshold suggestion, and symbol-scope metadata are loaded from local strategy storage. The per-step input values and scores are deterministic diagnostic simulations, so the same symbol and strategy normally produce the same trace.', 'tradepress' ); ?></p>
				<p class="tp-phase-next-step"><?php esc_html_e( 'Next live-data step: feed the trace from stored scoring/trading execution records after provider imports run, then use this panel to explain real SEES decisions instead of simulated diagnostics.', 'tradepress' ); ?></p>
			</div>
			<div class="tp-sees-trace-actions">
				<button type="button" id="tp-sees-copy-json" class="button button-secondary"><?php esc_html_e( 'Copy Trace JSON', 'tradepress' ); ?></button>
				<span id="tp-sees-copy-status" class="tp-sees-copy-status" aria-live="polite"></span>
			</div>
			<div class="tp-sees-trace-header" id="tp-sees-trace-header"></div>
			<div id="tp-sees-trace-process" class="tp-sees-trace-process"></div>
			<div id="tp-sees-branch-details" class="tp-sees-branch-details"></div>
			<div id="tp-sees-strategy-stack" class="tp-sees-strategy-stack"></div>

			<div class="tp-sees-trace-experiments">
				<button class="button button-primary" disabled><?php esc_html_e( 'Pipeline View', 'tradepress' ); ?></button>
				<button class="button" disabled><?php esc_html_e( 'Decision Tree (next)', 'tradepress' ); ?></button>
				<button class="button" disabled><?php esc_html_e( 'What-If Mode (next)', 'tradepress' ); ?></button>
			</div>

			<div id="tp-sees-trace-steps" class="tp-sees-trace-steps"></div>
		</section>
	</div>
</div>
