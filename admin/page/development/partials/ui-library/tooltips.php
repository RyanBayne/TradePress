<?php
/**
 * UI Library Tooltips Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="tradepress-ui-section tradepress-ui-section--tooltips">
	<h3><?php esc_html_e( 'Tooltips', 'tradepress' ); ?></h3>
	<p><?php esc_html_e( 'Hover and click-triggered contextual information. Basic tooltips use CSS only — no JavaScript required. Popovers use a click-toggle pattern scoped to their container.', 'tradepress' ); ?></p>

	<div class="tradepress-component-group">

		<!-- CSS Attribute Tooltips -->
		<div class="component-demo">
			<h4><?php esc_html_e( 'Basic Tooltips (CSS)', 'tradepress' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Hover over any item. Tooltips are rendered entirely via CSS using the data-tooltip attribute — no JavaScript.', 'tradepress' ); ?></p>
			<div class="tp-tooltip-row">
				<span class="tp-tooltip-trigger" data-tooltip="<?php esc_attr_e( 'Default informational tooltip. Use for supplementary context.', 'tradepress' ); ?>">
					<span class="dashicons dashicons-info-outline"></span> <?php esc_html_e( 'Information', 'tradepress' ); ?>
				</span>
				<span class="tp-tooltip-trigger tp-tooltip-success" data-tooltip="<?php esc_attr_e( 'Order executed successfully at $172.45.', 'tradepress' ); ?>">
					<span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Success', 'tradepress' ); ?>
				</span>
				<span class="tp-tooltip-trigger tp-tooltip-warning" data-tooltip="<?php esc_attr_e( 'High volatility detected. RSI above 70.', 'tradepress' ); ?>">
					<span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Warning', 'tradepress' ); ?>
				</span>
				<span class="tp-tooltip-trigger tp-tooltip-error" data-tooltip="<?php esc_attr_e( 'Insufficient funds to execute this order.', 'tradepress' ); ?>">
					<span class="dashicons dashicons-dismiss"></span> <?php esc_html_e( 'Error', 'tradepress' ); ?>
				</span>
			</div>
		</div>

		<!-- Tooltips on Table Data -->
		<div class="component-demo">
			<h4><?php esc_html_e( 'Indicator Table with Tooltips', 'tradepress' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Info icons on metric labels — hover to reveal definitions. The pattern used throughout TradePress admin pages.', 'tradepress' ); ?></p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Indicator', 'tradepress' ); ?></th>
						<th><?php esc_html_e( 'Value', 'tradepress' ); ?></th>
						<th><?php esc_html_e( 'Signal', 'tradepress' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php esc_html_e( 'RSI (14)', 'tradepress' ); ?>
							<span class="tp-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Relative Strength Index. Values above 70 indicate overbought conditions; below 30 indicate oversold.', 'tradepress' ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</td>
						<td><strong>65.2</strong></td>
						<td><span class="tradepress-badge tradepress-badge-warning"><?php esc_html_e( 'Neutral', 'tradepress' ); ?></span></td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'MACD', 'tradepress' ); ?>
							<span class="tp-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Moving Average Convergence Divergence. Positive values indicate bullish momentum.', 'tradepress' ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</td>
						<td><strong>+0.24</strong></td>
						<td><span class="tradepress-badge tradepress-badge-success"><?php esc_html_e( 'Bullish', 'tradepress' ); ?></span></td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Beta', 'tradepress' ); ?>
							<span class="tp-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Measures volatility relative to the market. Beta > 1 means more volatile than the benchmark.', 'tradepress' ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</td>
						<td><strong>1.15</strong></td>
						<td><span class="tradepress-badge tradepress-badge-info"><?php esc_html_e( 'Moderate', 'tradepress' ); ?></span></td>
					</tr>
					<tr>
						<td>
							<?php esc_html_e( 'Sharpe Ratio', 'tradepress' ); ?>
							<span class="tp-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Risk-adjusted return. Values above 1.0 are considered good; above 2.0 are very good.', 'tradepress' ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</td>
						<td><strong>1.42</strong></td>
						<td><span class="tradepress-badge tradepress-badge-success"><?php esc_html_e( 'Good', 'tradepress' ); ?></span></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Positional Tooltips -->
		<div class="component-demo">
			<h4><?php esc_html_e( 'Tooltip Positioning', 'tradepress' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Control tooltip direction with position modifier classes.', 'tradepress' ); ?></p>
			<div class="tp-tooltip-position-grid">
				<div class="tp-tooltip-position-cell">
					<button class="button tp-tooltip-trigger tp-tooltip-pos-top" data-tooltip="<?php esc_attr_e( 'Positioned above — default direction', 'tradepress' ); ?>">
						<span class="dashicons dashicons-arrow-up-alt2"></span> <?php esc_html_e( 'Top', 'tradepress' ); ?>
					</button>
				</div>
				<div class="tp-tooltip-position-cell">
					<button class="button tp-tooltip-trigger tp-tooltip-pos-right" data-tooltip="<?php esc_attr_e( 'Positioned to the right', 'tradepress' ); ?>">
						<?php esc_html_e( 'Right', 'tradepress' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
				<div class="tp-tooltip-position-cell">
					<button class="button tp-tooltip-trigger tp-tooltip-pos-bottom" data-tooltip="<?php esc_attr_e( 'Positioned below — use when near the top of the page', 'tradepress' ); ?>">
						<span class="dashicons dashicons-arrow-down-alt2"></span> <?php esc_html_e( 'Bottom', 'tradepress' ); ?>
					</button>
				</div>
				<div class="tp-tooltip-position-cell">
					<button class="button tp-tooltip-trigger tp-tooltip-pos-left" data-tooltip="<?php esc_attr_e( 'Positioned to the left', 'tradepress' ); ?>">
						<span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Left', 'tradepress' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Click Popovers -->
		<div class="component-demo">
			<h4><?php esc_html_e( 'Click Popovers', 'tradepress' ); ?></h4>
			<p class="description"><?php esc_html_e( 'Richer contextual cards triggered by click. Positioned relative to their row — no viewport overflow issues.', 'tradepress' ); ?></p>
			<div class="tp-popover-list">

				<div class="tp-popover-row">
					<div class="tp-popover-row-main">
						<strong>AAPL</strong>
						<span class="tp-popover-price positive">$172.45 <span class="tp-popover-change">+2.15%</span></span>
					</div>
					<button class="button button-small tp-popover-trigger" data-popover="aapl">
						<span class="dashicons dashicons-chart-line"></span> <?php esc_html_e( 'Details', 'tradepress' ); ?>
					</button>
					<div class="tp-popover-card" id="tp-popover-aapl">
						<div class="tp-popover-card-header">
							<strong><?php esc_html_e( 'Apple Inc. (AAPL)', 'tradepress' ); ?></strong>
							<button class="tp-popover-close dashicons dashicons-no-alt"></button>
						</div>
						<table class="tp-popover-table">
							<tr><th><?php esc_html_e( 'Market Cap', 'tradepress' ); ?></th><td>$2.85T</td></tr>
							<tr><th><?php esc_html_e( 'P/E Ratio', 'tradepress' ); ?></th><td>28.5</td></tr>
							<tr><th><?php esc_html_e( 'Volume', 'tradepress' ); ?></th><td>45.2M</td></tr>
							<tr><th><?php esc_html_e( '52W High', 'tradepress' ); ?></th><td>$198.23</td></tr>
						</table>
						<p class="tp-popover-footer"><?php esc_html_e( 'Last updated 2 minutes ago', 'tradepress' ); ?></p>
					</div>
				</div>

				<div class="tp-popover-row">
					<div class="tp-popover-row-main">
						<strong>TSLA</strong>
						<span class="tp-popover-price negative">$245.80 <span class="tp-popover-change">-1.85%</span></span>
					</div>
					<button class="button button-small tp-popover-trigger" data-popover="tsla">
						<span class="dashicons dashicons-chart-line"></span> <?php esc_html_e( 'Details', 'tradepress' ); ?>
					</button>
					<div class="tp-popover-card" id="tp-popover-tsla">
						<div class="tp-popover-card-header">
							<strong><?php esc_html_e( 'Tesla, Inc. (TSLA)', 'tradepress' ); ?></strong>
							<button class="tp-popover-close dashicons dashicons-no-alt"></button>
						</div>
						<table class="tp-popover-table">
							<tr><th><?php esc_html_e( 'Market Cap', 'tradepress' ); ?></th><td>$780B</td></tr>
							<tr><th><?php esc_html_e( 'P/E Ratio', 'tradepress' ); ?></th><td>58.3</td></tr>
							<tr><th><?php esc_html_e( 'Volume', 'tradepress' ); ?></th><td>125.8M</td></tr>
							<tr><th><?php esc_html_e( '52W High', 'tradepress' ); ?></th><td>$299.29</td></tr>
						</table>
						<p class="tp-popover-footer"><?php esc_html_e( 'Last updated 1 minute ago', 'tradepress' ); ?></p>
					</div>
				</div>

				<div class="tp-popover-row">
					<div class="tp-popover-row-main">
						<strong>MSFT</strong>
						<span class="tp-popover-price positive">$385.20 <span class="tp-popover-change">+0.95%</span></span>
					</div>
					<button class="button button-small tp-popover-trigger" data-popover="msft">
						<span class="dashicons dashicons-chart-line"></span> <?php esc_html_e( 'Details', 'tradepress' ); ?>
					</button>
					<div class="tp-popover-card" id="tp-popover-msft">
						<div class="tp-popover-card-header">
							<strong><?php esc_html_e( 'Microsoft Corporation (MSFT)', 'tradepress' ); ?></strong>
							<button class="tp-popover-close dashicons dashicons-no-alt"></button>
						</div>
						<table class="tp-popover-table">
							<tr><th><?php esc_html_e( 'Market Cap', 'tradepress' ); ?></th><td>$2.92T</td></tr>
							<tr><th><?php esc_html_e( 'P/E Ratio', 'tradepress' ); ?></th><td>32.1</td></tr>
							<tr><th><?php esc_html_e( 'Volume', 'tradepress' ); ?></th><td>28.9M</td></tr>
							<tr><th><?php esc_html_e( '52W High', 'tradepress' ); ?></th><td>$420.82</td></tr>
						</table>
						<p class="tp-popover-footer"><?php esc_html_e( 'Last updated 3 minutes ago', 'tradepress' ); ?></p>
					</div>
				</div>

			</div>
		</div>

	</div>

	<?php
	$tooltip_script = "
        jQuery(document).ready(function(\$) {
            // Click popovers — scoped to row, no body appending
            \$('.tp-popover-trigger').on('click', function(e) {
                e.stopPropagation();
                var key = \$(this).data('popover');
                var \$row = \$(this).closest('.tp-popover-row');
                var \$card = \$row.find('#tp-popover-' + key);

                // Close all other open popovers
                \$('.tp-popover-card.is-open').not(\$card).removeClass('is-open');

                \$card.toggleClass('is-open');
            });

            // Close button
            \$(document).on('click', '.tp-popover-close', function() {
                \$(this).closest('.tp-popover-card').removeClass('is-open');
            });

            // Click outside closes
            \$(document).on('click', function(e) {
                if (!\$(e.target).closest('.tp-popover-row').length) {
                    \$('.tp-popover-card.is-open').removeClass('is-open');
                }
            });
        });
    ";
	wp_add_inline_script( 'jquery', $tooltip_script );
	?>
</div>
