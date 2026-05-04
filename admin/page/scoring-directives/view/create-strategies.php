<?php
/**
 * TradePress - Create Scoring Strategies Tab
 *
 * Drag & drop interface for creating new scoring strategies
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load directives.
if ( ! function_exists( 'tradepress_get_all_directives' ) ) {
	require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
}

$all_directives    = tradepress_get_all_directives();
$active_directives = array_filter(
	$all_directives,
	function ( $directive ) {
		return true === $directive['active'];
	}
);
?>

<div class="create-strategies-interface">
	<div class="strategy-builder">
		<div class="builder-header">
			<h3><?php esc_html_e( 'Strategy Builder', 'tradepress' ); ?></h3>
			<p><?php esc_html_e( 'Drag active directives into your strategy and configure their weights.', 'tradepress' ); ?></p>
		</div>
		
		<!-- Strategy Template Selection -->
		<div class="strategy-template-section">
			<h4><?php esc_html_e( 'Optional Strategy Template', 'tradepress' ); ?></h4>
			<div class="template-selection">
				<select id="strategy-template" class="regular-text">
					<option value=""><?php esc_html_e( 'Custom Strategy (No Template)', 'tradepress' ); ?></option>
					<optgroup label="<?php esc_attr_e( 'Ready to Use', 'tradepress' ); ?>">
						<option value="momentum_confluence"><?php esc_html_e( 'Momentum Confluence', 'tradepress' ); ?></option>
						<option value="mean_reversion"><?php esc_html_e( 'Mean Reversion', 'tradepress' ); ?></option>
						<option value="trend_strength"><?php esc_html_e( 'Trend Strength', 'tradepress' ); ?></option>
						<option value="volume_breakout"><?php esc_html_e( 'Volume Breakout', 'tradepress' ); ?></option>
						<option value="oscillator_confluence"><?php esc_html_e( 'Oscillator Confluence', 'tradepress' ); ?></option>
						<option value="uk_isa_seasonal"><?php esc_html_e( 'UK ISA Seasonal', 'tradepress' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'Requires Additional Directives', 'tradepress' ); ?>">
						<option value="earnings_catalyst"><?php esc_html_e( 'Earnings Catalyst — needs Earnings Proximity', 'tradepress' ); ?></option>
						<option value="candle_reversal"><?php esc_html_e( 'Candle Reversal — needs pattern directives', 'tradepress' ); ?></option>
						<option value="news_momentum"><?php esc_html_e( 'News Momentum — needs News Sentiment', 'tradepress' ); ?></option>
						<option value="weekly_rhythm"><?php esc_html_e( 'Weekly Rhythm — needs temporal directives', 'tradepress' ); ?></option>
					</optgroup>
				</select>
				<p class="description" id="template-description"><?php esc_html_e( 'Select a pre-configured strategy template or build a custom strategy from scratch.', 'tradepress' ); ?></p>
			</div>
		</div>
		
		<div class="builder-layout">
			<!-- Available Directives Panel -->
			<div class="available-directives-panel">
				<h4><?php esc_html_e( 'Available Directives', 'tradepress' ); ?></h4>
				<div class="directives-list" id="available-directives">
					<?php foreach ( $active_directives as $directive_id => $directive ) : ?>
						<div class="directive-item" data-directive-id="<?php echo esc_attr( $directive_id ); ?>" draggable="true">
							<div class="directive-header">
								<span class="directive-name"><?php echo esc_html( $directive['name'] ); ?></span>
								<span class="directive-code"><?php echo esc_html( $directive['code'] ?? '' ); ?></span>
							</div>
							<div class="directive-description">
								<?php echo esc_html( $directive['description'] ?? '' ); ?>
							</div>
							<div class="directive-impact">
								<span class="impact-badge impact-<?php echo esc_attr( $directive['impact'] ?? 'low' ); ?>">
									<?php echo esc_html( ucfirst( $directive['impact'] ?? 'low' ) ); ?> Impact
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			
			<!-- Strategy Builder Panel -->
			<div class="strategy-builder-panel">
				<div class="strategy-form">
					<div class="form-group">
						<label for="strategy-name"><?php esc_html_e( 'Strategy Name:', 'tradepress' ); ?></label>
						<input type="text" id="strategy-name" class="regular-text" placeholder="<?php esc_attr_e( 'Enter strategy name', 'tradepress' ); ?>">
					</div>
					
					<div class="form-group">
						<label for="strategy-description"><?php esc_html_e( 'Description:', 'tradepress' ); ?></label>
						<textarea id="strategy-description" rows="3" class="regular-text" placeholder="<?php esc_attr_e( 'Describe your strategy', 'tradepress' ); ?>"></textarea>
					</div>
				</div>
				
				<div class="strategy-directives-area">
					<h4><?php esc_html_e( 'Strategy Directives', 'tradepress' ); ?></h4>
					<div class="drop-zone" id="strategy-drop-zone">
						<div class="drop-zone-placeholder">
							<span class="dashicons dashicons-plus-alt2"></span>
							<p><?php esc_html_e( 'Drag directives here to build your strategy', 'tradepress' ); ?></p>
						</div>
					</div>
					
					<div class="strategy-summary">
						<div class="weight-total">
							<span><?php esc_html_e( 'Total Weight:', 'tradepress' ); ?></span>
							<span id="total-weight">0%</span>
						</div>
						<div class="directive-count">
							<span><?php esc_html_e( 'Directives:', 'tradepress' ); ?></span>
							<span id="directive-count">0</span>
						</div>
					</div>
					<div class="strategy-weight-tools">
						<button type="button" class="button button-secondary" id="evenly-divide-weights" disabled>
							<?php esc_html_e( 'Evenly Divide Weights', 'tradepress' ); ?>
						</button>
						<span id="weight-helper-message" class="description"></span>
					</div>
				</div>

				<div class="strategy-advisory-settings">
					<h4><?php esc_html_e( 'Advisory Trading Context', 'tradepress' ); ?></h4>
					<p class="description"><?php esc_html_e( 'These settings document how this scoring strategy is intended to be used. They do not enforce trading decisions by themselves.', 'tradepress' ); ?></p>

					<div class="form-group">
						<label for="strategy-min-score-threshold"><?php esc_html_e( 'Suggested Trading Threshold:', 'tradepress' ); ?></label>
						<input type="number" id="strategy-min-score-threshold" class="small-text" min="0" max="500" step="0.01" value="50">
						<p class="description"><?php esc_html_e( 'Use this as a recommended score target after reviewing the directive stack and maximum possible score. Trading strategies decide whether to enforce a threshold.', 'tradepress' ); ?></p>
					</div>

					<div class="form-group strategy-scope-fields">
						<label for="strategy-scope-mode"><?php esc_html_e( 'Symbol Scope Handling:', 'tradepress' ); ?></label>
						<select id="strategy-scope-mode">
							<option value="advisory"><?php esc_html_e( 'Advisory note only', 'tradepress' ); ?></option>
							<option value="enforced"><?php esc_html_e( 'Recommend enforcement when used by trading', 'tradepress' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Scoring and SEES ranking treat scope as applicability context. Trading strategies may use this preference as an execution guard.', 'tradepress' ); ?></p>
					</div>

					<div class="form-group strategy-scope-fields">
						<label for="strategy-manual-symbols"><?php esc_html_e( 'Intended Symbols:', 'tradepress' ); ?></label>
						<textarea id="strategy-manual-symbols" rows="3" class="regular-text" placeholder="<?php esc_attr_e( 'AAPL, MSFT, USD/JPY', 'tradepress' ); ?>"></textarea>
						<p class="description"><?php esc_html_e( 'Separate symbols with commas, spaces, or new lines. Watchlist pairing will use the same scope service when durable watchlist storage is available.', 'tradepress' ); ?></p>
					</div>
				</div>
				
				<div class="strategy-actions">
					<button type="button" class="button button-primary" id="save-strategy" disabled>
						<?php esc_html_e( 'Create Strategy', 'tradepress' ); ?>
					</button>
					<button type="button" class="button button-secondary" id="test-strategy" disabled>
						<?php esc_html_e( 'Test Strategy', 'tradepress' ); ?>
					</button>
					<button type="button" class="button" id="clear-strategy">
						<?php esc_html_e( 'Clear All', 'tradepress' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.create-strategies-interface {
	margin: 20px 0;
}

.builder-layout {
	display: flex;
	gap: 20px;
	margin-top: 20px;
}

.available-directives-panel {
	flex: 1;
	background: #f9f9f9;
	padding: 20px;
	border-radius: 8px;
	border: 1px solid #ddd;
}

.strategy-builder-panel {
	flex: 1;
	background: #fff;
	padding: 20px;
	border-radius: 8px;
	border: 1px solid #ddd;
}

.directive-item {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 6px;
	padding: 15px;
	margin-bottom: 10px;
	cursor: grab;
	transition: all 0.2s ease;
}

.directive-item:hover {
	border-color: #0073aa;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.directive-item.dragging {
	opacity: 0.5;
	transform: rotate(2deg);
}

.directive-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 8px;
}

.directive-name {
	font-weight: 600;
	color: #0073aa;
}

.directive-code {
	background: #0073aa;
	color: white;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: bold;
}

.directive-description {
	font-size: 13px;
	color: #666;
	margin-bottom: 8px;
}

.impact-badge {
	padding: 2px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
}

.impact-low { background: #e8f5e8; color: #2e7d32; }
.impact-medium { background: #fff3e0; color: #f57c00; }
.impact-high { background: #ffebee; color: #c62828; }

.drop-zone {
	min-height: 200px;
	border: 2px dashed #ddd;
	border-radius: 8px;
	padding: 20px;
	margin: 15px 0;
	transition: all 0.2s ease;
}

.drop-zone.drag-over {
	border-color: #0073aa;
	background: #f0f8ff;
}

.drop-zone-placeholder {
	text-align: center;
	color: #999;
}

.drop-zone-placeholder .dashicons {
	font-size: 48px;
	margin-bottom: 10px;
}

.strategy-directive-item {
	background: #f0f8ff;
	border: 1px solid #0073aa;
	border-radius: 6px;
	padding: 15px;
	margin-bottom: 10px;
	position: relative;
}

.strategy-directive-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 10px;
}

.remove-directive {
	background: #dc3232;
	color: white;
	border: none;
	border-radius: 50%;
	width: 24px;
	height: 24px;
	cursor: pointer;
	font-size: 12px;
}

.weight-input {
	width: 80px;
	text-align: center;
}

.strategy-summary {
	display: flex;
	justify-content: space-between;
	padding: 15px;
	background: #f9f9f9;
	border-radius: 6px;
	margin: 15px 0;
}

.strategy-actions {
	display: flex;
	gap: 10px;
	margin-top: 20px;
}

.strategy-weight-tools {
	display: flex;
	align-items: center;
	gap: 10px;
	margin: 10px 0 0;
}

.strategy-weight-tools .description {
	color: #646970;
}

.strategy-advisory-settings {
	margin-top: 18px;
	padding-top: 16px;
	border-top: 1px solid #dcdcde;
}

.form-group {
	margin-bottom: 15px;
}

.form-group label {
	display: block;
	margin-bottom: 5px;
	font-weight: 600;
}

.strategy-scope-fields textarea {
	width: 100%;
	max-width: 420px;
}

.strategy-template-section {
	background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
	border: 2px solid #0073aa;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
	position: relative;
}

.strategy-template-section::before {
	content: '✨';
	position: absolute;
	top: 15px;
	right: 20px;
	font-size: 24px;
}

.template-selection {
	margin-top: 10px;
}

.template-selection select {
	width: 100%;
	max-width: 400px;
}

.template-description {
	margin-top: 10px;
	padding: 10px;
	background: #f0f8ff;
	border-left: 4px solid #0073aa;
	border-radius: 4px;
	display: none;
}

.directive-item.template-recommended {
	border-color: #0073aa;
	background: #f0f8ff;
	position: relative;
}

.directive-item.template-recommended::before {
	content: 'Recommended';
	position: absolute;
	top: -8px;
	right: 10px;
	background: #0073aa;
	color: white;
	padding: 2px 8px;
	border-radius: 3px;
	font-size: 10px;
	font-weight: bold;
}

.directive-item.template-disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.directive-item.template-disabled::after {
	content: 'API Requirements Not Met';
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	background: rgba(220, 50, 50, 0.9);
	color: white;
	padding: 5px 10px;
	border-radius: 4px;
	font-size: 12px;
	font-weight: bold;
	z-index: 10;
}
</style>

<script>
jQuery(document).ready(function($) {
	let draggedElement = null;
	let strategyDirectives = [];
	let currentTemplate = '';
	
	// Strategy templates configuration.
	// 'disabled' lists directive IDs that are not yet implemented and will be greyed out.
	const strategyTemplates = {

		// ── Ready to use ──────────────────────────────────────────────────────────

		momentum_confluence: {
			name: 'Momentum Confluence',
			description: 'Identifies oversold momentum entries confirmed by MACD crossover, volume surge, and price above EMA. Best for swing trading growth stocks.',
			recommended: ['rsi', 'macd', 'volume', 'ema'],
			weights: { rsi: 30, macd: 30, volume: 25, ema: 15 },
			disabled: [],
			apiRequirements: ['Alpha Vantage']
		},

		mean_reversion: {
			name: 'Mean Reversion',
			description: 'Identifies oversold conditions from multiple angles using RSI, Bollinger Bands, CCI, and MFI volume-weighted confirmation. Best for ranging markets.',
			recommended: ['rsi', 'bollinger_bands', 'cci', 'mfi'],
			weights: { rsi: 35, bollinger_bands: 30, cci: 20, mfi: 15 },
			disabled: [],
			apiRequirements: ['Alpha Vantage']
		},

		trend_strength: {
			name: 'Trend Strength',
			description: 'Confirms a strong trend exists before entering. ADX measures trend strength, moving averages confirm direction, MACD confirms momentum, volume confirms participation.',
			recommended: ['adx', 'moving_averages', 'macd', 'volume'],
			weights: { adx: 30, moving_averages: 30, macd: 25, volume: 15 },
			disabled: [],
			apiRequirements: ['Alpha Vantage']
		},

		volume_breakout: {
			name: 'Volume Breakout',
			description: 'Targets stocks breaking out of consolidation on above-average volume. OBV confirms accumulation, Bollinger Bands detect the squeeze, ADX confirms the new trend.',
			recommended: ['volume', 'obv', 'bollinger_bands', 'adx', 'ema'],
			weights: { volume: 30, obv: 20, bollinger_bands: 20, adx: 20, ema: 10 },
			disabled: [],
			apiRequirements: ['Alpha Vantage']
		},

		oscillator_confluence: {
			name: 'Oscillator Confluence',
			description: 'Requires agreement across four independent oscillators before scoring high. RSI, Stochastic, CCI, and MFI must all signal oversold simultaneously.',
			recommended: ['rsi', 'stochastic', 'cci', 'mfi'],
			weights: { rsi: 30, stochastic: 25, cci: 25, mfi: 20 },
			disabled: [],
			apiRequirements: ['Alpha Vantage']
		},

		uk_isa_seasonal: {
			name: 'UK ISA Seasonal',
			description: 'Combines technical momentum with UK ISA seasonal buying pressure. Scores highest during January-April when UK investors deploy fresh ISA allowances into oversold quality stocks.',
			recommended: ['rsi', 'moving_averages', 'volume', 'isa', 'isa_reset'],
			weights: { rsi: 30, moving_averages: 25, volume: 20, isa: 15, isa_reset: 10 },
			disabled: [],
			apiRequirements: ['Alpha Vantage']
		},

		// ── Requires additional directives ────────────────────────────────────────

		earnings_catalyst: {
			name: 'Earnings Catalyst',
			description: 'Targets stocks approaching earnings with strong technical setup. Earnings Proximity and News Sentiment are not yet implemented — those slots will be enabled when the directives are active.',
			recommended: ['earnings_proximity', 'rsi', 'volume', 'macd', 'news_sentiment_positive'],
			weights: { earnings_proximity: 30, rsi: 20, volume: 20, macd: 20, news_sentiment_positive: 10 },
			disabled: ['earnings_proximity', 'news_sentiment_positive'],
			apiRequirements: ['Alpha Vantage', 'News API']
		},

		candle_reversal: {
			name: 'Candle Reversal',
			description: 'High-probability reversal entries using candle pattern confluence at key levels. Pattern directives are not yet implemented — those slots will be enabled when the directives are active.',
			recommended: ['support_resistance_levels', 'engulfing_pattern', 'hammer_pattern', 'volume', 'rsi'],
			weights: { support_resistance_levels: 25, engulfing_pattern: 25, hammer_pattern: 20, volume: 15, rsi: 15 },
			disabled: ['support_resistance_levels', 'engulfing_pattern', 'hammer_pattern'],
			apiRequirements: ['Alpha Vantage']
		},

		news_momentum: {
			name: 'News Momentum',
			description: 'Combines positive news sentiment with technical momentum confirmation. News Sentiment is not yet implemented — that slot will be enabled when the directive is active.',
			recommended: ['news_sentiment_positive', 'rsi', 'macd', 'volume_surge', 'moving_averages'],
			weights: { news_sentiment_positive: 30, rsi: 20, macd: 20, volume_surge: 15, moving_averages: 15 },
			disabled: ['news_sentiment_positive', 'volume_surge'],
			apiRequirements: ['Alpha Vantage', 'News API']
		},

		weekly_rhythm: {
			name: 'Weekly Rhythm',
			description: 'Exploits predictable day-of-week institutional patterns. Monday Effect and Friday Positioning are implemented; Volume Rhythm and Midweek Momentum will be enabled when their directives are active.',
			recommended: ['monday_effect', 'friday_positioning', 'volume_rhythm', 'midweek_momentum', 'volume'],
			weights: { monday_effect: 25, friday_positioning: 25, volume_rhythm: 20, midweek_momentum: 20, volume: 10 },
			disabled: ['volume_rhythm', 'midweek_momentum'],
			apiRequirements: ['Alpha Vantage']
		}
	};
	
	// Template selection handler
	$('#strategy-template').on('change', function() {
		const templateId = $(this).val();
		currentTemplate = templateId;
		
		if (templateId && strategyTemplates[templateId]) {
			applyTemplate(strategyTemplates[templateId]);
		} else {
			clearTemplate();
		}
	});
	
	/**
	 * Apply template.
	 *
	 * @version 1.1.0
	 */
	function applyTemplate(template) {
		// Update description
		let reqText = template.apiRequirements.join(', ');
		$('#template-description').html(
			'<strong>' + template.name + ':</strong> ' + template.description +
			'<br><strong>API Requirements:</strong> ' + reqText +
			( template.disabled.length ? '<br><em>Note: ' + template.disabled.length + ' directive(s) not yet implemented — shown as disabled below.</em>' : '' )
		).addClass('template-description').show();
		
		// Clear existing strategy
		strategyDirectives = [];
		
		// Pre-populate strategy name if empty
		if (!$('#strategy-name').val().trim()) {
			$('#strategy-name').val(template.name);
		}
		
		// Reset all directive highlighting
		$('.directive-item').removeClass('template-recommended template-disabled');

		// Mark recommended and disabled directives
		template.recommended.forEach(directiveId => {
			const $item = $(`.directive-item[data-directive-id="${directiveId}"]`);
			if (template.disabled.includes(directiveId)) {
				$item.addClass('template-disabled');
			} else {
				$item.addClass('template-recommended');
			}
		});
		
		// Auto-add only the non-disabled recommended directives
		template.recommended.forEach(directiveId => {
			if (template.disabled.includes(directiveId)) {
				return; // Skip unimplemented directives
			}
			const $directive = $(`.directive-item[data-directive-id="${directiveId}"]`);
			if ($directive.length) {
				const directiveName = $directive.find('.directive-name').text();
				const weight = template.weights[directiveId] || 20;
				strategyDirectives.push({ id: directiveId, name: directiveName, weight: weight });
			}
		});
		
		renderStrategyDirectives();
		updateSummary();
		updateButtons();
	}
	
	/**
	 * Clear template.
	 *
	 * @version 1.0.0
	 */
	function clearTemplate() {
		$('#template-description').hide().removeClass('template-description');
		$('.directive-item').removeClass('template-recommended template-disabled');
		currentTemplate = '';
	}
	
	// Drag and drop functionality
	$('.directive-item').on('dragstart', function(e) {
		draggedElement = this;
		$(this).addClass('dragging');
	});
	
	$('.directive-item').on('dragend', function(e) {
		$(this).removeClass('dragging');
		draggedElement = null;
	});
	
	$('#strategy-drop-zone').on('dragover', function(e) {
		e.preventDefault();
		$(this).addClass('drag-over');
	});
	
	$('#strategy-drop-zone').on('dragleave', function(e) {
		$(this).removeClass('drag-over');
	});
	
	$('#strategy-drop-zone').on('drop', function(e) {
		e.preventDefault();
		$(this).removeClass('drag-over');
		
		if (draggedElement) {
			const directiveId = $(draggedElement).data('directive-id');
			const directiveName = $(draggedElement).find('.directive-name').text();
			
			// Check if already added
			if (strategyDirectives.find(d => d.id === directiveId)) {
				alert('Directive already added to strategy');
				return;
			}
			
			addDirectiveToStrategy(directiveId, directiveName);
			updateStrategyDisplay();
		}
	});
	
	/**
	 * Add directive to strategy.
	 *
	 * @version 1.0.0
	 */
	function addDirectiveToStrategy(id, name) {
		// Get template weight if available
		let weight = 20; // Default weight
		if (currentTemplate && strategyTemplates[currentTemplate] && strategyTemplates[currentTemplate].weights[id]) {
			weight = strategyTemplates[currentTemplate].weights[id];
		}
		
		strategyDirectives.push({
			id: id,
			name: name,
			weight: weight
		});

		if (!currentTemplate) {
			normalizeWeightsEvenly(false);
		}
		
		renderStrategyDirectives();
		updateSummary();
		updateButtons();
	}
	
	/**
	 * Render strategy directives.
	 *
	 * @version 1.0.0
	 */
	function renderStrategyDirectives() {
		const $dropZone = $('#strategy-drop-zone');
		$dropZone.empty();
		
		if (strategyDirectives.length === 0) {
			$dropZone.html(`
				<div class="drop-zone-placeholder">
					<span class="dashicons dashicons-plus-alt2"></span>
					<p><?php esc_html_e( 'Drag directives here to build your strategy', 'tradepress' ); ?></p>
				</div>
			`);
			return;
		}
		
		strategyDirectives.forEach((directive, index) => {
			$dropZone.append(`
				<div class="strategy-directive-item" data-index="${index}">
					<div class="strategy-directive-header">
						<span class="directive-name">${directive.name}</span>
						<button type="button" class="remove-directive" data-index="${index}">×</button>
					</div>
					<div class="weight-control">
						<label>Weight: </label>
						<input type="number" class="weight-input" value="${directive.weight}" min="1" max="100" data-index="${index}">
						<span>%</span>
					</div>
				</div>
			`);
		});
	}
	
	// Remove directive
	$(document).on('click', '.remove-directive', function() {
		const index = $(this).data('index');
		strategyDirectives.splice(index, 1);
		renderStrategyDirectives();
		updateSummary();
		updateButtons();
	});
	
	// Update weight
	$(document).on('input', '.weight-input', function() {
		const index = $(this).data('index');
		const weight = parseInt($(this).val()) || 0;
		strategyDirectives[index].weight = weight;
		updateSummary();
	});
	
	/**
	 * Update summary.
	 *
	 * @version 1.0.0
	 */
	function updateSummary() {
		const totalWeight = strategyDirectives.reduce((sum, d) => sum + d.weight, 0);
		$('#total-weight').text(totalWeight + '%');
		$('#directive-count').text(strategyDirectives.length);
		
		// Color code total weight
		const $totalWeight = $('#total-weight');
		$totalWeight.removeClass('weight-low weight-perfect weight-high');
		if (totalWeight < 90) $totalWeight.addClass('weight-low');
		else if (totalWeight === 100) $totalWeight.addClass('weight-perfect');
		else $totalWeight.addClass('weight-high');

		if (strategyDirectives.length > 0 && totalWeight !== 100) {
			$('#weight-helper-message').text('<?php echo esc_js( __( 'Use Evenly Divide Weights to make the total exactly 100%.', 'tradepress' ) ); ?>');
		} else {
			$('#weight-helper-message').text('');
		}
	}
	
	/**
	 * Update buttons.
	 *
	 * @version 1.0.0
	 */
	function updateButtons() {
		const hasDirectives = strategyDirectives.length > 0;
		const hasName = $('#strategy-name').val().trim().length > 0;
		
		$('#save-strategy, #test-strategy').prop('disabled', !(hasDirectives && hasName));
		$('#evenly-divide-weights').prop('disabled', !hasDirectives);
	}

	$('#evenly-divide-weights').on('click', function() {
		normalizeWeightsEvenly(true);
	});

	/**
	 * Divide weights evenly and keep the total exactly 100%.
	 *
	 * @param {boolean} showMessage Whether to show the helper message.
	 * @return {void}
	 */
	function normalizeWeightsEvenly(showMessage) {
		const count = strategyDirectives.length;

		if (count === 0) {
			return;
		}

		const baseWeight = Math.floor(100 / count);
		let remainder = 100 - (baseWeight * count);

		strategyDirectives = strategyDirectives.map((directive) => {
			const nextDirective = Object.assign({}, directive);
			nextDirective.weight = baseWeight + (remainder > 0 ? 1 : 0);
			remainder--;
			return nextDirective;
		});

		renderStrategyDirectives();
		updateSummary();
		updateButtons();

		if (showMessage) {
			$('#weight-helper-message').text('<?php echo esc_js( __( 'Weights divided evenly and total is now 100%.', 'tradepress' ) ); ?>');
		}
	}
	
	// Clear strategy
	$('#clear-strategy').on('click', function() {
		if (confirm('<?php esc_html_e( 'Clear all directives from strategy?', 'tradepress' ); ?>')) {
			strategyDirectives = [];
			$('#strategy-template').val('').trigger('change'); // Reset template
			$('#strategy-name, #strategy-description').val(''); // Clear form
			renderStrategyDirectives();
			updateSummary();
			updateButtons();
		}
	});
	
	// Update buttons on name change
	$('#strategy-name').on('input', updateButtons);
	
	// Save strategy
	$('#save-strategy').on('click', function() {
		const $button = $(this);
		const strategyData = {
			name: $('#strategy-name').val().trim(),
			description: $('#strategy-description').val().trim(),
			min_score_threshold: parseFloat($('#strategy-min-score-threshold').val()) || 50,
			scope_mode: $('#strategy-scope-mode').val(),
			manual_symbols: $('#strategy-manual-symbols').val().trim(),
			directives: strategyDirectives.map((d, index) => ({
				id: d.id,
				name: d.name,
				weight: d.weight,
				sort_order: index
			}))
		};

		const totalWeight = strategyDirectives.reduce((sum, d) => sum + d.weight, 0);
		if (totalWeight !== 100) {
			const shouldDivide = confirm('<?php echo esc_js( __( 'The selected directive weights must total 100%. Divide them evenly now?', 'tradepress' ) ); ?>');
			if (!shouldDivide) {
				return;
			}

			normalizeWeightsEvenly(true);
			strategyData.directives = strategyDirectives.map((d, index) => ({
				id: d.id,
				name: d.name,
				weight: d.weight,
				sort_order: index
			}));
		}
		
		$button.prop('disabled', true).text('Creating...');
		
		$.post(ajaxurl, {
			action: 'tradepress_create_strategy',
			nonce: '<?php echo esc_attr( wp_create_nonce( 'tradepress_strategy_nonce' ) ); ?>',
			name: strategyData.name,
			description: strategyData.description,
			template: currentTemplate,
			min_score_threshold: strategyData.min_score_threshold,
			scope_mode: strategyData.scope_mode,
			manual_symbols: strategyData.manual_symbols,
			directives: JSON.stringify(strategyData.directives)
		})
		.done(function(response) {
			if (response.success) {
				alert('Strategy created successfully!');
				// Redirect to manage strategies
				window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=tradepress_scoring_directives&tab=manage_strategies' ) ); ?>';
			} else {
				alert('Error: ' + response.data);
			}
		})
		.fail(function() {
			alert('Network error occurred');
		})
		.always(function() {
			$button.prop('disabled', false).text('Create Strategy');
		});
	});
	
	// Test strategy
	$('#test-strategy').on('click', function() {
		if (strategyDirectives.length === 0) {
			alert('Please add directives to test the strategy');
			return;
		}
		
		const $button = $(this);
		$button.prop('disabled', true).text('Testing...');
		
		// Create temporary strategy for testing
		const testData = {
			directives: strategyDirectives.map((d, index) => ({
				id: d.id,
				name: d.name,
				weight: d.weight,
				sort_order: index
			}))
		};
		
		// For now, show test preview
		let testResults = 'Strategy Test Preview:\n\n';
		testResults += 'Directives:\n';
		strategyDirectives.forEach(d => {
			testResults += `- ${d.name}: ${d.weight}%\n`;
		});
		testResults += `\nTotal Weight: ${strategyDirectives.reduce((sum, d) => sum + d.weight, 0)}%\n`;
		testResults += '\nNote: Full testing available after strategy is saved.';
		
		alert(testResults);
		$button.prop('disabled', false).text('Test Strategy');
	});
});
</script>

<style>
.weight-low { color: #d63638; }
.weight-perfect { color: #00a32a; font-weight: bold; }
.weight-high { color: #dba617; }
</style>
