<?php
/**
 * TradePress - Trading Calculators
 *
 * This file contains various financial calculators for traders
 *
 * @package TradePress/Admin/trading/tabs
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-05-01
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Enqueue required styles and scripts.
wp_enqueue_script( 'jquery-ui-tabs' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );
wp_enqueue_style( 'tradepress-calculators' );
wp_enqueue_script( 'tradepress-calculators' );
?>

<div class="tradepress-calculator-container">
	<div class="tradepress-data-status-panel" data-mode="live" data-health="not_applicable">
		<h3><?php esc_html_e( 'Calculator Data Status', 'tradepress' ); ?></h3>
		<table class="widefat fixed striped">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Data mode', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Live', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Source of truth', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'User-entered calculator inputs only', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Provider', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'Not applicable', 'tradepress' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Queue behavior', 'tradepress' ); ?></th>
					<td><?php esc_html_e( 'No queued refresh required', 'tradepress' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="calculator-tabs">
		<ul>
			<li><a href="#averaging-down-calculator">Averaging Down</a></li>
			<li><a href="#position-size-calculator">Position Size</a></li>
			<li><a href="#risk-reward-calculator">Risk/Reward</a></li>
			<li><a href="#profit-loss-calculator">Profit/Loss</a></li>
			<li><a href="#fibonacci-calculator">Fibonacci</a></li>
		</ul>

		<!-- Averaging Down Calculator -->
		<div id="averaging-down-calculator" class="calculator-section">
			<h3><?php esc_html_e( 'Averaging Down Calculator', 'tradepress' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Calculate how many additional shares to buy to reach a target average price.', 'tradepress' ); ?>
			</p>
			
			<div class="calculator-form">
				<form id="averaging-down-form">
					<div class="form-row">
						<div class="form-group">
							<label for="current_shares"><?php esc_html_e( 'Current Shares', 'tradepress' ); ?></label>
							<input type="number" id="current_shares" name="current_shares" min="1" step="1" required>
						</div>
						<div class="form-group">
							<label for="current_price"><?php esc_html_e( 'Current Price ($)', 'tradepress' ); ?></label>
							<input type="number" id="current_price" name="current_price" min="0.01" step="0.01" required>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-group">
							<label for="target_price"><?php esc_html_e( 'Target Average Price ($)', 'tradepress' ); ?></label>
							<input type="number" id="target_price" name="target_price" min="0.01" step="0.01" required>
						</div>
						<div class="form-group">
							<label for="new_price"><?php esc_html_e( 'New Purchase Price ($)', 'tradepress' ); ?></label>
							<input type="number" id="new_price" name="new_price" min="0.01" step="0.01" required>
						</div>
					</div>
					
					<div class="form-row">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Calculate', 'tradepress' ); ?></button>
						<button type="reset" class="button"><?php esc_html_e( 'Reset', 'tradepress' ); ?></button>
					</div>
				</form>
			</div>
			
			<div id="averaging-down-results" class="calculator-results" style="display: none;">
				<h4><?php esc_html_e( 'Results', 'tradepress' ); ?></h4>
				<div class="results-grid">
					<div class="result-item">
						<span class="result-label"><?php esc_html_e( 'Additional Shares Needed:', 'tradepress' ); ?></span>
						<span class="result-value" id="additional_shares_result">0</span>
					</div>
					<div class="result-item">
						<span class="result-label"><?php esc_html_e( 'Additional Investment:', 'tradepress' ); ?></span>
						<span class="result-value" id="additional_investment_result">$0.00</span>
					</div>
					<div class="result-item">
						<span class="result-label"><?php esc_html_e( 'Total Shares:', 'tradepress' ); ?></span>
						<span class="result-value" id="total_shares_result">0</span>
					</div>
					<div class="result-item">
						<span class="result-label"><?php esc_html_e( 'Total Investment:', 'tradepress' ); ?></span>
						<span class="result-value" id="total_investment_result">$0.00</span>
					</div>
					<div class="result-item">
						<span class="result-label"><?php esc_html_e( 'New Average Price:', 'tradepress' ); ?></span>
						<span class="result-value" id="new_avg_price_result">$0.00</span>
					</div>
				</div>
				<div class="result-note">
					<p id="result_note"></p>
				</div>
			</div>
		</div>
		
		<!-- Position Size Calculator -->
		<div id="position-size-calculator" class="calculator-section">
			<h3><?php esc_html_e( 'Position Size Calculator', 'tradepress' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Calculate optimal position size based on your risk tolerance.', 'tradepress' ); ?>
			</p>
			
			<!-- Position Size Calculator form will go here -->
			<div class="coming-soon-notice" data-state="coming-soon">
				<p><strong><?php esc_html_e( 'Coming Soon:', 'tradepress' ); ?></strong> <?php esc_html_e( 'This calculator is not yet available.', 'tradepress' ); ?></p>
			</div>
		</div>
		
		<!-- Risk/Reward Calculator -->
		<div id="risk-reward-calculator" class="calculator-section">
			<h3><?php esc_html_e( 'Risk/Reward Calculator', 'tradepress' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Calculate risk-to-reward ratio for potential trades.', 'tradepress' ); ?>
			</p>
			
			<!-- Risk/Reward Calculator form will go here -->
			<div class="coming-soon-notice" data-state="coming-soon">
				<p><strong><?php esc_html_e( 'Coming Soon:', 'tradepress' ); ?></strong> <?php esc_html_e( 'This calculator is not yet available.', 'tradepress' ); ?></p>
			</div>
		</div>
		
		<!-- Profit/Loss Calculator -->
		<div id="profit-loss-calculator" class="calculator-section">
			<h3><?php esc_html_e( 'Profit/Loss Calculator', 'tradepress' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Calculate potential profit or loss from a trade, including fees.', 'tradepress' ); ?>
			</p>
			
			<!-- Profit/Loss Calculator form will go here -->
			<div class="coming-soon-notice" data-state="coming-soon">
				<p><strong><?php esc_html_e( 'Coming Soon:', 'tradepress' ); ?></strong> <?php esc_html_e( 'This calculator is not yet available.', 'tradepress' ); ?></p>
			</div>
		</div>
		
		<!-- Fibonacci Calculator -->
		<div id="fibonacci-calculator" class="calculator-section">
			<h3><?php esc_html_e( 'Fibonacci Retracement Calculator', 'tradepress' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Calculate Fibonacci retracement levels for price movement.', 'tradepress' ); ?>
			</p>
			
			<!-- Fibonacci Calculator form will go here -->
			<div class="coming-soon-notice" data-state="coming-soon">
				<p><strong><?php esc_html_e( 'Coming Soon:', 'tradepress' ); ?></strong> <?php esc_html_e( 'This calculator is not yet available.', 'tradepress' ); ?></p>
			</div>
		</div>
	</div>
</div>
