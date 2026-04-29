<?php
/**
 * TradePress UI Library - Notice Components
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="tradepress-ui-section tradepress-ui-section--notices" data-section="notice-components">
	<div class="tradepress-card">
		<div class="tradepress-card-header">
			<h3><?php esc_html_e( 'Notice Components', 'tradepress' ); ?></h3>
		</div>
		
		<div class="tradepress-card-body tradepress-notice-lab">
			<p class="tradepress-notice-lab-intro"><?php esc_html_e( 'A practical notice system for product updates, trading signals, and API operations.', 'tradepress' ); ?></p>

			<div class="tradepress-notice-grid">
				<!-- WordPress Standard Notices -->
				<section class="notice-demo-group tradepress-notice-panel">
					<header class="tradepress-notice-panel-header">
						<h4><?php esc_html_e( 'WordPress Standard Notices', 'tradepress' ); ?></h4>
						<p><?php esc_html_e( 'System-level patterns for errors, warnings, and successful updates.', 'tradepress' ); ?></p>
					</header>

					<p class="tradepress-top-notice-note">
						<?php esc_html_e( 'These notices render at the top of the admin page when triggered. They are intentionally not displayed inside this panel.', 'tradepress' ); ?>
					</p>

					<div class="tradepress-top-notice-types" role="list" aria-label="<?php esc_attr_e( 'Top notice types', 'tradepress' ); ?>">
						<span class="tradepress-top-notice-type tradepress-top-notice-type--success" role="listitem"><?php esc_html_e( 'Success', 'tradepress' ); ?></span>
						<span class="tradepress-top-notice-type tradepress-top-notice-type--info" role="listitem"><?php esc_html_e( 'Info', 'tradepress' ); ?></span>
						<span class="tradepress-top-notice-type tradepress-top-notice-type--warning" role="listitem"><?php esc_html_e( 'Warning', 'tradepress' ); ?></span>
						<span class="tradepress-top-notice-type tradepress-top-notice-type--error" role="listitem"><?php esc_html_e( 'Error', 'tradepress' ); ?></span>
					</div>
				</section>

				<!-- Demo Mode Indicators -->
				<section class="notice-demo-group tradepress-notice-panel tradepress-notice-panel--modes">
					<header class="tradepress-notice-panel-header">
						<h4><?php esc_html_e( 'Demo Mode Indicators', 'tradepress' ); ?></h4>
						<p><?php esc_html_e( 'Reusable indicators for feature readiness and mock-data states.', 'tradepress' ); ?></p>
					</header>

					<?php $is_demo_mode_active = function_exists( 'is_demo_mode' ) ? is_demo_mode() : true; ?>
					<div class="tradepress-mode-stack" role="list">
						<?php if ( $is_demo_mode_active ) : ?>
						<article class="tradepress-mode-signal is-demo is-active" role="listitem">
						<?php else : ?>
						<article class="tradepress-mode-signal is-demo" role="listitem">
						<?php endif; ?>
							<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span>
							<div class="tradepress-mode-signal-copy">
								<h5><?php esc_html_e( 'Development in Progress', 'tradepress' ); ?></h5>
								<p><?php esc_html_e( 'Placeholder data is currently active while integrations are finalized.', 'tradepress' ); ?></p>
							</div>
							<span class="tradepress-mode-signal-pill"><?php esc_html_e( 'DEMO', 'tradepress' ); ?></span>
						</article>

						<?php if ( ! $is_demo_mode_active ) : ?>
						<article class="tradepress-mode-signal is-live is-active" role="listitem">
						<?php else : ?>
						<article class="tradepress-mode-signal is-live" role="listitem">
						<?php endif; ?>
							<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
							<div class="tradepress-mode-signal-copy">
								<h5><?php esc_html_e( 'Live Mode Active', 'tradepress' ); ?></h5>
								<p><?php esc_html_e( 'Real credentials and production API calls are enabled for this environment.', 'tradepress' ); ?></p>
							</div>
							<span class="tradepress-mode-signal-pill"><?php esc_html_e( 'LIVE', 'tradepress' ); ?></span>
						</article>
					</div>
				</section>

				<!-- System Notices -->
				<section class="notice-demo-group tradepress-notice-panel">
					<header class="tradepress-notice-panel-header">
						<h4><?php esc_html_e( 'System Notices', 'tradepress' ); ?></h4>
						<p><?php esc_html_e( 'Persistent system confirmations that users should not miss.', 'tradepress' ); ?></p>
					</header>

					<div class="tradepress-system-notice tradepress-system-notice--success" role="status" aria-live="polite">
						<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
						<div class="tradepress-system-notice-copy">
							<h5><?php esc_html_e( 'API Connected', 'tradepress' ); ?></h5>
							<p><?php esc_html_e( 'Successfully connected to Alpha Vantage API. Real-time data is now available.', 'tradepress' ); ?></p>
						</div>
					</div>
				</section>

				<!-- Inline Alert Patterns -->
				<section class="notice-demo-group tradepress-notice-panel">
					<header class="tradepress-notice-panel-header">
						<h4><?php esc_html_e( 'Inline Alert Patterns', 'tradepress' ); ?></h4>
						<p><?php esc_html_e( 'Contextual alerts embedded inside workflow screens.', 'tradepress' ); ?></p>
					</header>

					<div class="tradepress-inline-alert tradepress-inline-alert--info" role="status">
						<span class="dashicons dashicons-chart-line" aria-hidden="true"></span>
						<p><strong><?php esc_html_e( 'Market Alert:', 'tradepress' ); ?></strong> <?php esc_html_e( 'High volatility detected in', 'tradepress' ); ?> <code>TSLA</code>, <code>NVDA</code>. <a href="#" class="button button-small"><?php esc_html_e( 'View Details', 'tradepress' ); ?></a></p>
					</div>

					<div class="tradepress-inline-alert tradepress-inline-alert--warning" role="alert">
						<span class="dashicons dashicons-warning" aria-hidden="true"></span>
						<p><strong><?php esc_html_e( 'Stop Loss Triggered:', 'tradepress' ); ?></strong> <?php esc_html_e( 'Position in MSFT closed at $330.25.', 'tradepress' ); ?> <a href="#"><?php esc_html_e( 'View Transaction', 'tradepress' ); ?></a></p>
					</div>
				</section>

				<!-- Status Messages -->
				<section class="notice-demo-group tradepress-notice-panel">
					<header class="tradepress-notice-panel-header">
						<h4><?php esc_html_e( 'Status Messages', 'tradepress' ); ?></h4>
						<p><?php esc_html_e( 'Lightweight status strips for connection and feed health.', 'tradepress' ); ?></p>
					</header>

					<div class="status-message connecting">
						<span class="dashicons dashicons-update spin"></span>
						Connecting to trading platform...
					</div>

					<div class="status-message success">
						<span class="dashicons dashicons-yes-alt"></span>
						Connected to Alpaca Trading
					</div>

					<div class="status-message info">
						<span class="dashicons dashicons-info"></span>
						Market data delayed by 15 minutes
					</div>

					<div class="status-message warning">
						<span class="dashicons dashicons-warning"></span>
						Latency spike detected on order routing
					</div>
				</section>

				<!-- Usage Guidelines -->
				<section class="notice-demo-group tradepress-notice-panel tradepress-notice-panel--guidelines">
					<header class="tradepress-notice-panel-header">
						<h4><?php esc_html_e( 'Usage Guidelines', 'tradepress' ); ?></h4>
						<p><?php esc_html_e( 'Design rules for clearer, calmer messaging during busy trading sessions.', 'tradepress' ); ?></p>
					</header>

					<ul class="tradepress-notice-guidelines-list">
						<li><?php esc_html_e( 'Use direct titles that state impact first.', 'tradepress' ); ?></li>
						<li><?php esc_html_e( 'Pair each warning with a clear action or next step.', 'tradepress' ); ?></li>
						<li><?php esc_html_e( 'Reserve high-contrast error styles for urgent failures only.', 'tradepress' ); ?></li>
						<li><?php esc_html_e( 'Prefer compact progress and status views over large callouts.', 'tradepress' ); ?></li>
					</ul>
				</section>
			</div>
		</div>
	</div>
</div>
