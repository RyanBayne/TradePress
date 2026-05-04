<?php
/**
 * TradePress - Manage Scoring Strategies Tab
 *
 * Edit and configure existing scoring strategies
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load strategies from database.
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/strategy-scope-service.php';

$strategies = TradePress_Scoring_Strategies_DB::get_strategies(
	array(
		'limit'   => 20,
		'orderby' => 'updated_at',
		'order'   => 'DESC',
	)
);

// Convert to array format and add directive data.
$sample_strategies = array();
foreach ( $strategies as $strategy ) {
	$directives = TradePress_Scoring_Strategies_DB::get_strategy_directives( $strategy->id );
	$scope      = TradePress_Strategy_Scope_Service::get_scope( $strategy->id );

	$directive_array = array();
	foreach ( $directives as $directive ) {
		$directive_array[] = array(
			'id'     => $directive->directive_id,
			'name'   => $directive->directive_name,
			'weight' => $directive->weight,
		);
	}

	$sample_strategies[] = array(
		'id'                  => $strategy->id,
		'name'                => $strategy->name,
		'description'         => $strategy->description,
		'directives'          => $directive_array,
		'created'             => gmdate( 'Y-m-d', strtotime( $strategy->created_at ) ),
		'last_used'           => $strategy->last_test_date ? human_time_diff( strtotime( $strategy->last_test_date ) ) . ' ago' : 'Never',
		'status'              => $strategy->status,
		'total_tests'         => $strategy->total_tests,
		'success_rate'        => $strategy->success_rate,
		'min_score_threshold' => isset( $strategy->min_score_threshold ) ? $strategy->min_score_threshold : 50,
		'scope'               => $scope,
	);
}

// Built-in strategy templates (display-only, not editable in this tab).
$built_in_strategies = array(
	array(
		'name'          => 'Momentum Confluence',
		'group'         => 'ready',
		'description'   => 'Identifies oversold momentum entries confirmed by MACD crossover, volume surge, and price above EMA. Best for swing trading growth stocks.',
		'recommended'   => array( 'RSI', 'MACD', 'Volume', 'EMA' ),
		'unavailable'   => array(),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'Mean Reversion',
		'group'         => 'ready',
		'description'   => 'Identifies oversold conditions from multiple angles using RSI, Bollinger Bands, CCI, and MFI volume-weighted confirmation. Best for ranging markets.',
		'recommended'   => array( 'RSI', 'Bollinger Bands', 'CCI', 'MFI' ),
		'unavailable'   => array(),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'Trend Strength',
		'group'         => 'ready',
		'description'   => 'Confirms a strong trend exists before entering. ADX measures trend strength, moving averages confirm direction, MACD confirms momentum, volume confirms participation.',
		'recommended'   => array( 'ADX', 'Moving Averages', 'MACD', 'Volume' ),
		'unavailable'   => array(),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'Volume Breakout',
		'group'         => 'ready',
		'description'   => 'Targets stocks breaking out of consolidation on above-average volume. OBV confirms accumulation, Bollinger Bands detect the squeeze, ADX confirms the new trend.',
		'recommended'   => array( 'Volume', 'OBV', 'Bollinger Bands', 'ADX', 'EMA' ),
		'unavailable'   => array(),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'Oscillator Confluence',
		'group'         => 'ready',
		'description'   => 'Requires agreement across four independent oscillators before scoring high. RSI, Stochastic, CCI, and MFI must all signal oversold simultaneously.',
		'recommended'   => array( 'RSI', 'Stochastic', 'CCI', 'MFI' ),
		'unavailable'   => array(),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'UK ISA Seasonal',
		'group'         => 'ready',
		'description'   => 'Combines technical momentum with UK ISA seasonal buying pressure. Scores highest during January-April when UK investors deploy fresh ISA allowances into oversold quality stocks.',
		'recommended'   => array( 'RSI', 'Moving Averages', 'Volume', 'ISA', 'ISA Reset' ),
		'unavailable'   => array(),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'Earnings Catalyst',
		'group'         => 'requires_more',
		'description'   => 'Targets stocks approaching earnings with strong technical setup. Earnings Proximity and News Sentiment become available as those directives are implemented.',
		'recommended'   => array( 'Earnings Proximity', 'RSI', 'Volume', 'MACD', 'News Sentiment Positive' ),
		'unavailable'   => array( 'Earnings Proximity', 'News Sentiment Positive' ),
		'requirements'  => array( 'Alpha Vantage', 'News API' ),
	),
	array(
		'name'          => 'Candle Reversal',
		'group'         => 'requires_more',
		'description'   => 'High-probability reversal entries using candle pattern confluence at key levels. Pattern directives are enabled as those directives are added.',
		'recommended'   => array( 'Support/Resistance Levels', 'Engulfing Pattern', 'Hammer Pattern', 'Volume', 'RSI' ),
		'unavailable'   => array( 'Support/Resistance Levels', 'Engulfing Pattern', 'Hammer Pattern' ),
		'requirements'  => array( 'Alpha Vantage' ),
	),
	array(
		'name'          => 'News Momentum',
		'group'         => 'requires_more',
		'description'   => 'Combines positive news sentiment with technical momentum confirmation. News-related directives become available as they are implemented.',
		'recommended'   => array( 'News Sentiment Positive', 'RSI', 'MACD', 'Volume Surge', 'Moving Averages' ),
		'unavailable'   => array( 'News Sentiment Positive', 'Volume Surge' ),
		'requirements'  => array( 'Alpha Vantage', 'News API' ),
	),
	array(
		'name'          => 'Weekly Rhythm',
		'group'         => 'requires_more',
		'description'   => 'Exploits predictable day-of-week institutional patterns. Monday Effect and Friday Positioning are available, while additional temporal directives are enabled as implemented.',
		'recommended'   => array( 'Monday Effect', 'Friday Positioning', 'Volume Rhythm', 'Midweek Momentum', 'Volume' ),
		'unavailable'   => array( 'Volume Rhythm', 'Midweek Momentum' ),
		'requirements'  => array( 'Alpha Vantage' ),
	),
);
?>

<div class="manage-strategies-interface">
	<div class="strategies-header">
		<h3><?php esc_html_e( 'Manage Scoring Strategies', 'tradepress' ); ?></h3>
		<p><?php esc_html_e( 'Edit existing strategies, modify directive weights, and configure per-strategy settings.', 'tradepress' ); ?></p>
		
		<div class="header-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress_scoring_directives&tab=create_strategies' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Create New Strategy', 'tradepress' ); ?>
			</a>
		</div>
	</div>
	
	<div class="strategies-list">
		<?php foreach ( $sample_strategies as $strategy ) : ?>
			<div class="strategy-card" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>" data-strategy="<?php echo esc_attr( wp_json_encode( $strategy ) ); ?>">
				<div class="strategy-header">
					<div class="strategy-info">
						<h4 class="strategy-name"><?php echo esc_html( $strategy['name'] ); ?></h4>
						<p class="strategy-description"><?php echo esc_html( $strategy['description'] ); ?></p>
					</div>
					<div class="strategy-status">
						<span class="status-badge status-<?php echo esc_attr( $strategy['status'] ); ?>">
							<?php echo esc_html( ucfirst( $strategy['status'] ) ); ?>
						</span>
					</div>
				</div>
				
				<div class="strategy-meta">
					<div class="meta-item">
						<span class="meta-label"><?php esc_html_e( 'Created:', 'tradepress' ); ?></span>
						<span class="meta-value"><?php echo esc_html( $strategy['created'] ); ?></span>
					</div>
					<div class="meta-item">
						<span class="meta-label"><?php esc_html_e( 'Last Used:', 'tradepress' ); ?></span>
						<span class="meta-value"><?php echo esc_html( $strategy['last_used'] ); ?></span>
					</div>
					<div class="meta-item">
						<span class="meta-label"><?php esc_html_e( 'Directives:', 'tradepress' ); ?></span>
						<span class="meta-value"><?php echo count( $strategy['directives'] ); ?></span>
					</div>
					<div class="meta-item">
						<span class="meta-label"><?php esc_html_e( 'Suggested Threshold:', 'tradepress' ); ?></span>
						<span class="meta-value"><?php echo esc_html( number_format_i18n( (float) $strategy['min_score_threshold'], 2 ) ); ?></span>
					</div>
					<div class="meta-item">
						<span class="meta-label"><?php esc_html_e( 'Intended Scope:', 'tradepress' ); ?></span>
						<span class="meta-value strategy-scope-summary"><?php echo esc_html( $strategy['scope']['summary'] ); ?></span>
					</div>
				</div>
				
				<div class="strategy-directives">
					<h5><?php esc_html_e( 'Directive Weights:', 'tradepress' ); ?></h5>
					<div class="directives-grid">
						<?php foreach ( $strategy['directives'] as $directive ) : ?>
							<div class="directive-weight-item">
								<span class="directive-name"><?php echo esc_html( $directive['name'] ); ?></span>
								<span class="directive-weight"><?php echo esc_html( $directive['weight'] ); ?>%</span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				
				<div class="strategy-actions">
					<button type="button" class="button button-primary edit-strategy" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>">
						<?php esc_html_e( 'Edit Strategy', 'tradepress' ); ?>
					</button>
					<button type="button" class="button test-strategy" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>">
						<?php esc_html_e( 'Test Strategy', 'tradepress' ); ?>
					</button>
					<button type="button" class="button duplicate-strategy" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>">
						<?php esc_html_e( 'Duplicate', 'tradepress' ); ?>
					</button>
					<?php if ( 'active' === $strategy['status'] ) : ?>
						<button type="button" class="button deactivate-strategy" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>">
							<?php esc_html_e( 'Deactivate', 'tradepress' ); ?>
						</button>
					<?php else : ?>
						<button type="button" class="button button-secondary activate-strategy" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>">
							<?php esc_html_e( 'Activate', 'tradepress' ); ?>
						</button>
					<?php endif; ?>
					<button type="button" class="button button-link-delete delete-strategy" data-strategy-id="<?php echo esc_attr( $strategy['id'] ); ?>">
						<?php esc_html_e( 'Delete', 'tradepress' ); ?>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="built-in-strategies-section">
		<h4><?php esc_html_e( 'Built-in Strategy Templates (Read Only)', 'tradepress' ); ?></h4>
		<p class="description">
			<?php esc_html_e( 'These are built-in templates for quick setup. They are displayed here for reference and are not editable in this tab. Create a custom strategy from a template to make changes.', 'tradepress' ); ?>
		</p>

		<div class="built-in-strategies-list">
			<?php foreach ( $built_in_strategies as $built_in_strategy ) : ?>
				<div class="strategy-card built-in-strategy-card">
					<div class="strategy-header">
						<div class="strategy-info">
							<h4 class="strategy-name"><?php echo esc_html( $built_in_strategy['name'] ); ?></h4>
							<p class="strategy-description"><?php echo esc_html( $built_in_strategy['description'] ); ?></p>
						</div>
						<div class="strategy-status">
							<span class="status-badge built-in-badge"><?php esc_html_e( 'Built-in', 'tradepress' ); ?></span>
							<span class="status-badge <?php echo esc_attr( 'ready' === $built_in_strategy['group'] ? 'status-active' : 'status-draft' ); ?>">
								<?php echo esc_html( 'ready' === $built_in_strategy['group'] ? __( 'Ready to Use', 'tradepress' ) : __( 'Requires Additional Directives', 'tradepress' ) ); ?>
							</span>
						</div>
					</div>

					<div class="strategy-meta">
						<div class="meta-item">
							<span class="meta-label"><?php esc_html_e( 'Recommended Directives:', 'tradepress' ); ?></span>
							<span class="meta-value"><?php echo esc_html( implode( ', ', $built_in_strategy['recommended'] ) ); ?></span>
						</div>
						<div class="meta-item">
							<span class="meta-label"><?php esc_html_e( 'API Requirements:', 'tradepress' ); ?></span>
							<span class="meta-value"><?php echo esc_html( implode( ', ', $built_in_strategy['requirements'] ) ); ?></span>
						</div>
						<?php if ( ! empty( $built_in_strategy['unavailable'] ) ) : ?>
							<div class="meta-item">
								<span class="meta-label"><?php esc_html_e( 'Currently Unavailable Directives:', 'tradepress' ); ?></span>
								<span class="meta-value"><?php echo esc_html( implode( ', ', $built_in_strategy['unavailable'] ) ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="strategy-actions">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress_scoring_directives&tab=create_strategies' ) ); ?>" class="button button-secondary">
							<?php esc_html_e( 'Create from Template', 'tradepress' ); ?>
						</a>
						<span class="description"><?php esc_html_e( 'Read-only template information.', 'tradepress' ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	
	<?php if ( empty( $sample_strategies ) ) : ?>
		<div class="no-strategies">
			<div class="no-strategies-content">
				<span class="dashicons dashicons-chart-line"></span>
				<h3><?php esc_html_e( 'No Custom Strategies Found', 'tradepress' ); ?></h3>
				<p><?php esc_html_e( 'You haven\'t created any manual scoring strategies yet. Built-in templates are listed below for reference.', 'tradepress' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress_scoring_directives&tab=create_strategies' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Create Your First Strategy', 'tradepress' ); ?>
				</a>
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
.manage-strategies-interface {
	margin: 20px 0;
}

.strategies-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 30px;
	padding-bottom: 20px;
	border-bottom: 1px solid #ddd;
}

.strategies-header h3 {
	margin: 0 0 10px 0;
}

.strategies-list {
	display: grid;
	gap: 20px;
}

.built-in-strategies-section {
	margin-top: 30px;
	padding-top: 20px;
	border-top: 1px solid #ddd;
}

.built-in-strategies-list {
	display: grid;
	gap: 20px;
	margin-top: 12px;
}

.strategy-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	transition: all 0.2s ease;
}

.strategy-card:hover {
	border-color: #0073aa;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.built-in-strategy-card {
	border-color: #d9e2ec;
	background: #fcfdff;
}

.built-in-strategy-card:hover {
	border-color: #d9e2ec;
	box-shadow: none;
}

.strategy-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 15px;
}

.strategy-name {
	margin: 0 0 8px 0;
	color: #0073aa;
}

.strategy-description {
	margin: 0;
	color: #666;
	font-size: 14px;
}

.status-badge {
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}

.status-active {
	background: #e8f5e8;
	color: #2e7d32;
}

.status-draft {
	background: #fff3e0;
	color: #f57c00;
}

.status-inactive {
	background: #f5f5f5;
	color: #666;
}

.built-in-badge {
	background: #edf2f7;
	color: #334155;
	margin-right: 6px;
}

.strategy-meta {
	display: flex;
	gap: 20px;
	margin-bottom: 15px;
	padding: 10px 0;
	border-top: 1px solid #f0f0f0;
	border-bottom: 1px solid #f0f0f0;
}

.meta-item {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.meta-label {
	font-size: 12px;
	color: #666;
	text-transform: uppercase;
	font-weight: 600;
}

.meta-value {
	font-size: 14px;
	color: #333;
}

.strategy-scope-summary {
	font-weight: 600;
}

.strategy-directives h5 {
	margin: 0 0 10px 0;
	font-size: 14px;
}

.directives-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 8px;
}

.directive-weight-item {
	display: flex;
	justify-content: space-between;
	padding: 6px 10px;
	background: #f9f9f9;
	border-radius: 4px;
	font-size: 13px;
}

.directive-weight {
	font-weight: 600;
	color: #0073aa;
}

.strategy-actions {
	display: flex;
	gap: 8px;
	margin-top: 15px;
	padding-top: 15px;
	border-top: 1px solid #f0f0f0;
	flex-wrap: wrap;
}

.no-strategies {
	text-align: center;
	padding: 60px 20px;
}

.no-strategies-content .dashicons {
	font-size: 64px;
	color: #ddd;
	margin-bottom: 20px;
}

.no-strategies-content h3 {
	color: #666;
	margin-bottom: 10px;
}

.no-strategies-content p {
	color: #999;
	margin-bottom: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
	const strategyNonce = '<?php echo esc_attr( wp_create_nonce( 'tradepress_strategy_nonce' ) ); ?>';

	// Edit strategy — inline edit form.
	$('.edit-strategy').on('click', function() {
		const $card = $(this).closest('.strategy-card');

		// Toggle: if already open, close it.
		if ( $card.find('.inline-edit-form').length ) {
			$card.find('.inline-edit-form').remove();
			$(this).text('<?php echo esc_js( __( 'Edit Strategy', 'tradepress' ) ); ?>');
			return;
		}

		const strategy = $card.data('strategy');
		let directivesHtml = '';
		let totalWeight = 0;
		const scope = strategy.scope || { scope_mode: 'advisory', manual_symbols: [], watchlist_ids: [] };
		const manualSymbols = Array.isArray(scope.manual_symbols) ? scope.manual_symbols.join("\n") : '';

		if ( strategy.directives && strategy.directives.length ) {
			strategy.directives.forEach(function(d) {
				totalWeight += parseFloat(d.weight) || 0;
				directivesHtml += '<div class="edit-directive-row" style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">' +
					'<span style="flex:2;font-size:13px;">' + $('<span>').text(d.name).html() + '</span>' +
					'<input type="number" class="directive-weight-input small-text" data-directive-id="' + $('<span>').text(d.id).html() + '" value="' + parseFloat(d.weight) + '" min="0" max="100" step="0.01" style="width:75px;"> %' +
				'</div>';
			});
		}

		const editForm = $('<div class="inline-edit-form" style="margin-top:16px;padding:16px;background:#f9f9f9;border:1px solid #ddd;border-radius:6px;">' +
			'<h4 style="margin:0 0 12px 0;"><?php echo esc_js( __( 'Edit Strategy', 'tradepress' ) ); ?></h4>' +
			'<table class="form-table" style="margin:0;"><tbody>' +
			'<tr><th style="width:160px;padding:6px 0;"><label><?php echo esc_js( __( 'Name', 'tradepress' ) ); ?></label></th>' +
			'<td style="padding:4px 0;"><input type="text" class="regular-text edit-name" value="' + $('<span>').text(strategy.name).html() + '"></td></tr>' +
			'<tr><th style="padding:6px 0;"><label><?php echo esc_js( __( 'Description', 'tradepress' ) ); ?></label></th>' +
			'<td style="padding:4px 0;"><textarea class="large-text edit-description" rows="2">' + $('<span>').text(strategy.description).html() + '</textarea></td></tr>' +
			'<tr><th style="padding:6px 0;"><label><?php echo esc_js( __( 'Status', 'tradepress' ) ); ?></label></th>' +
			'<td style="padding:4px 0;"><select class="edit-status">' +
				'<option value="draft"' + (strategy.status === 'draft' ? ' selected' : '') + '><?php echo esc_js( __( 'Draft', 'tradepress' ) ); ?></option>' +
				'<option value="active"' + (strategy.status === 'active' ? ' selected' : '') + '><?php echo esc_js( __( 'Active', 'tradepress' ) ); ?></option>' +
				'<option value="inactive"' + (strategy.status === 'inactive' ? ' selected' : '') + '><?php echo esc_js( __( 'Inactive', 'tradepress' ) ); ?></option>' +
			'</select></td></tr>' +
			'<tr><th style="padding:6px 0;"><label><?php echo esc_js( __( 'Suggested Trading Threshold', 'tradepress' ) ); ?></label></th>' +
			'<td style="padding:4px 0;"><input type="number" class="small-text edit-threshold" value="' + parseFloat(strategy.min_score_threshold) + '" min="0" max="500" step="0.01"></td></tr>' +
			'<tr><th style="padding:6px 0;"><label><?php echo esc_js( __( 'Symbol Scope Handling', 'tradepress' ) ); ?></label></th>' +
			'<td style="padding:4px 0;"><select class="edit-scope-mode">' +
				'<option value="advisory"' + (scope.scope_mode === 'advisory' ? ' selected' : '') + '><?php echo esc_js( __( 'Advisory note only', 'tradepress' ) ); ?></option>' +
				'<option value="enforced"' + (scope.scope_mode === 'enforced' ? ' selected' : '') + '><?php echo esc_js( __( 'Recommend enforcement when used by trading', 'tradepress' ) ); ?></option>' +
			'</select></td></tr>' +
			'<tr><th style="padding:6px 0;"><label><?php echo esc_js( __( 'Intended Symbols', 'tradepress' ) ); ?></label></th>' +
			'<td style="padding:4px 0;"><textarea class="large-text edit-manual-symbols" rows="3" placeholder="<?php echo esc_js( __( 'AAPL, MSFT, USD/JPY', 'tradepress' ) ); ?>">' + $('<span>').text(manualSymbols).html() + '</textarea>' +
			'<p class="description" style="margin:4px 0 0;"><?php echo esc_js( __( 'This is scoring-strategy applicability context. Trading strategies decide whether to enforce it.', 'tradepress' ) ); ?></p></td></tr>' +
			( directivesHtml ? '<tr><th style="padding:6px 0;vertical-align:top;"><label><?php echo esc_js( __( 'Directive Weights', 'tradepress' ) ); ?></label></th>' +
				'<td style="padding:4px 0;">' + directivesHtml +
				'<p class="weight-total-notice" style="margin:6px 0 0;font-size:12px;color:#666;"><?php echo esc_js( __( 'Total:', 'tradepress' ) ); ?> <strong class="weight-total">' + totalWeight.toFixed(2) + '%</strong></p>' +
				'</td></tr>' : '' ) +
			'</tbody></table>' +
			'<div style="margin-top:12px;display:flex;gap:8px;">' +
			'<button type="button" class="button button-primary save-edit-strategy"><?php echo esc_js( __( 'Save Changes', 'tradepress' ) ); ?></button>' +
			'<button type="button" class="button cancel-edit-strategy"><?php echo esc_js( __( 'Cancel', 'tradepress' ) ); ?></button>' +
			'<span class="edit-strategy-feedback" style="margin-left:8px;line-height:28px;font-size:13px;"></span>' +
			'</div>' +
		'</div>');

		$card.find('.strategy-actions').after(editForm);
		$(this).text('<?php echo esc_js( __( 'Cancel Edit', 'tradepress' ) ); ?>');

		// Live weight total.
		$card.on('input.weightwatch', '.directive-weight-input', function() {
			let sum = 0;
			$card.find('.directive-weight-input').each(function() { sum += parseFloat($(this).val()) || 0; });
			$card.find('.weight-total').text(sum.toFixed(2) + '%');
			$card.find('.weight-total').css('color', Math.abs(sum - 100) < 0.01 ? '#2e7d32' : '#dc3232');
		});

		// Save.
		$card.find('.save-edit-strategy').on('click', function() {
			const $btn     = $(this);
			const $feedback = $card.find('.edit-strategy-feedback');
			const strategy  = $card.data('strategy');

			const updatedDirectives = [];
			$card.find('.directive-weight-input').each(function() {
				updatedDirectives.push({ id: $(this).data('directive-id'), weight: parseFloat($(this).val()) || 0 });
			});

			if ( updatedDirectives.length ) {
				const total = updatedDirectives.reduce(function(s, d) { return s + d.weight; }, 0);
				if ( Math.abs(total - 100) > 0.01 ) {
					$feedback.css('color','#dc3232').text('<?php echo esc_js( __( 'Directive weights must total exactly 100%.', 'tradepress' ) ); ?>');
					return;
				}
			}

			$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving…', 'tradepress' ) ); ?>');
			$feedback.css('color','#666').text('');

			$.post(ajaxurl, {
				action:               'tradepress_update_strategy',
				nonce:                strategyNonce,
				strategy_id:          strategy.id,
				name:                 $card.find('.edit-name').val(),
				description:          $card.find('.edit-description').val(),
				status:               $card.find('.edit-status').val(),
				min_score_threshold:  $card.find('.edit-threshold').val(),
				scope_mode:           $card.find('.edit-scope-mode').val(),
				manual_symbols:       $card.find('.edit-manual-symbols').val(),
				directives:           JSON.stringify(updatedDirectives),
			})
			.done(function(response) {
				if ( response.success ) {
					$feedback.css('color','#2e7d32').text('<?php echo esc_js( __( 'Saved.', 'tradepress' ) ); ?>');
					// Update displayed values on the card.
					$card.find('.strategy-name').text($card.find('.edit-name').val());
					$card.find('.strategy-description').text($card.find('.edit-description').val());
					const newStatus = $card.find('.edit-status').val();
					$card.find('.status-badge').text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1))
						.attr('class', 'status-badge status-' + newStatus);
					// Update cached data attribute.
					const cached = $card.data('strategy');
					cached.name                = $card.find('.edit-name').val();
					cached.description         = $card.find('.edit-description').val();
					cached.status              = newStatus;
					cached.min_score_threshold = parseFloat($card.find('.edit-threshold').val());
					if ( response.data && response.data.scope ) {
						cached.scope = response.data.scope;
						$card.find('.strategy-scope-summary').text(response.data.scope.summary);
					}
					if ( updatedDirectives.length ) {
						updatedDirectives.forEach(function(ud) {
							cached.directives.forEach(function(d) { if (d.id === ud.id) d.weight = ud.weight; });
						});
					}
					$card.data('strategy', cached);
				} else {
					$feedback.css('color','#dc3232').text(response.data || '<?php echo esc_js( __( 'Update failed.', 'tradepress' ) ); ?>');
				}
			})
			.fail(function() {
				$feedback.css('color','#dc3232').text('<?php echo esc_js( __( 'Network error.', 'tradepress' ) ); ?>');
			})
			.always(function() {
				$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Changes', 'tradepress' ) ); ?>');
			});
		});

		// Cancel.
		$card.find('.cancel-edit-strategy').on('click', function() {
			$card.find('.inline-edit-form').remove();
			$card.off('input.weightwatch');
			$card.find('.edit-strategy').text('<?php echo esc_js( __( 'Edit Strategy', 'tradepress' ) ); ?>');
		});
	});

	// Test strategy.
	$('.test-strategy').on('click', function() {
		const strategyId = $(this).data('strategy-id');
		// Test is handled via SEES Diagnostics. Redirect there with the strategy pre-selected.
		window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=tradepress_trading&tab=sees_diagnostics' ) ); ?>&strategy_id=' + strategyId;
	});
	
	// Duplicate strategy
	$('.duplicate-strategy').on('click', function() {
		const strategyId = $(this).data('strategy-id');
		const $button = $(this);
		
		$button.prop('disabled', true).text('Duplicating...');
		
		$.post(ajaxurl, {
			action: 'tradepress_duplicate_strategy',
			nonce: '<?php echo esc_attr( wp_create_nonce( 'tradepress_strategy_nonce' ) ); ?>',
			strategy_id: strategyId
		})
		.done(function(response) {
			if (response.success) {
				alert('Strategy duplicated successfully!');
				location.reload(); // Refresh to show new strategy
			} else {
				alert('Error: ' + response.data);
			}
		})
		.fail(function() {
			alert('Network error occurred');
		})
		.always(function() {
			$button.prop('disabled', false).text('Duplicate');
		});
	});
	
	// Activate/Deactivate strategy.
	$('.activate-strategy, .deactivate-strategy').on('click', function() {
		const $btn       = $(this);
		const strategyId = $btn.data('strategy-id');
		const isActivate = $btn.hasClass('activate-strategy');
		const newStatus  = isActivate ? 'active' : 'inactive';
		const label      = isActivate ? '<?php echo esc_js( __( 'activate', 'tradepress' ) ); ?>' : '<?php echo esc_js( __( 'deactivate', 'tradepress' ) ); ?>';

		if ( ! confirm('<?php echo esc_js( __( 'Are you sure you want to', 'tradepress' ) ); ?> ' + label + ' <?php echo esc_js( __( 'this strategy?', 'tradepress' ) ); ?>') ) {
			return;
		}

		$btn.prop('disabled', true);

		const $card    = $btn.closest('.strategy-card');
		const strategy = $card.data('strategy');

		$.post(ajaxurl, {
			action:      'tradepress_update_strategy',
			nonce:       strategyNonce,
			strategy_id: strategyId,
			name:        strategy.name,
			status:      newStatus,
			min_score_threshold: strategy.min_score_threshold,
			scope_mode:   strategy.scope ? strategy.scope.scope_mode : 'advisory',
			manual_symbols: strategy.scope && strategy.scope.manual_symbols ? strategy.scope.manual_symbols.join("\n") : '',
		})
		.done(function(response) {
			if ( response.success ) {
				$card.find('.status-badge').text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1))
					.attr('class', 'status-badge status-' + newStatus);
				// Swap button.
				if ( isActivate ) {
					$btn.removeClass('activate-strategy button-secondary').addClass('deactivate-strategy')
						.text('<?php echo esc_js( __( 'Deactivate', 'tradepress' ) ); ?>');
				} else {
					$btn.removeClass('deactivate-strategy').addClass('activate-strategy button-secondary')
						.text('<?php echo esc_js( __( 'Activate', 'tradepress' ) ); ?>');
				}
				// Update cached data.
				strategy.status = newStatus;
				$card.data('strategy', strategy);
			} else {
				alert(response.data || '<?php echo esc_js( __( 'Update failed.', 'tradepress' ) ); ?>');
			}
		})
		.fail(function() {
			alert('<?php echo esc_js( __( 'Network error occurred.', 'tradepress' ) ); ?>');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});
	
	// Delete strategy
	$('.delete-strategy').on('click', function() {
		const strategyId = $(this).data('strategy-id');
		const $button = $(this);
		
		if (confirm('Are you sure you want to delete this strategy? This action cannot be undone.')) {
			$button.prop('disabled', true).text('Deleting...');
			
			$.post(ajaxurl, {
				action: 'tradepress_delete_strategy',
				nonce: '<?php echo esc_attr( wp_create_nonce( 'tradepress_strategy_nonce' ) ); ?>',
				strategy_id: strategyId
			})
			.done(function(response) {
				if (response.success) {
					$button.closest('.strategy-card').fadeOut(function() {
						$(this).remove();
					});
				} else {
					alert('Error: ' + response.data);
					$button.prop('disabled', false).text('Delete');
				}
			})
			.fail(function() {
				alert('Network error occurred');
				$button.prop('disabled', false).text('Delete');
			});
		}
	});
});
</script>
