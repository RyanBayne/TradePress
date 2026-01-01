<?php
/**
 * TradePress - View Scoring Strategies Tab
 * 
 * View strategy performance and test results
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load strategies with performance data from database
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

$strategies = TradePress_Scoring_Strategies_DB::get_strategies(array(
    'status' => 'active',
    'limit' => 10,
    'orderby' => 'success_rate',
    'order' => 'DESC'
));

$sample_strategies = array();
foreach ($strategies as $strategy) {
    // Get recent test results
    $recent_tests = TradePress_Scoring_Strategies_DB::get_test_results($strategy->id, 5);
    $test_array = array();
    
    foreach ($recent_tests as $test) {
        $test_array[] = array(
            'symbol' => $test->symbol,
            'score' => round($test->total_score, 0),
            'date' => date('Y-m-d', strtotime($test->test_date))
        );
    }
    
    $sample_strategies[] = array(
        'id' => $strategy->id,
        'name' => $strategy->name,
        'status' => $strategy->status,
        'performance' => array(
            'total_signals' => $strategy->total_tests ?? 0,
            'positive_signals' => $strategy->successful_tests ?? 0,
            'success_rate' => $strategy->success_rate ?? 0,
            'avg_score' => $strategy->avg_score ?? 0,
            'last_signal' => $strategy->last_test_date ? date('Y-m-d H:i:s', strtotime($strategy->last_test_date)) : 'Never'
        ),
        'recent_tests' => $test_array
    );
}
?>

<div class="view-strategies-interface">
    <div class="strategies-overview">
        <h3><?php esc_html_e('Strategy Performance Overview', 'tradepress'); ?></h3>
        <p><?php esc_html_e('Monitor strategy performance, view test results, and analyze scoring patterns.', 'tradepress'); ?></p>
        
        <div class="performance-summary">
            <div class="summary-card">
                <div class="summary-number"><?php echo count(array_filter($sample_strategies, function($s) { return $s['status'] === 'active'; })); ?></div>
                <div class="summary-label"><?php esc_html_e('Active Strategies', 'tradepress'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php echo array_sum(array_column(array_column($sample_strategies, 'performance'), 'total_signals')); ?></div>
                <div class="summary-label"><?php esc_html_e('Total Signals', 'tradepress'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php 
                    $total_signals = array_sum(array_column(array_column($sample_strategies, 'performance'), 'total_signals'));
                    $positive_signals = array_sum(array_column(array_column($sample_strategies, 'performance'), 'positive_signals'));
                    echo $total_signals > 0 ? round(($positive_signals / $total_signals) * 100, 1) : 0;
                ?>%</div>
                <div class="summary-label"><?php esc_html_e('Success Rate', 'tradepress'); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php 
                    $scores = array_filter(array_column(array_column($sample_strategies, 'performance'), 'avg_score'));
                    echo count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
                ?></div>
                <div class="summary-label"><?php esc_html_e('Avg Score', 'tradepress'); ?></div>
            </div>
        </div>
    </div>
    
    <div class="strategies-performance">
        <?php foreach ($sample_strategies as $strategy): ?>
            <div class="strategy-performance-card">
                <div class="strategy-header">
                    <h4><?php echo esc_html($strategy['name']); ?></h4>
                    <span class="status-badge status-<?php echo esc_attr($strategy['status']); ?>">
                        <?php echo esc_html(ucfirst($strategy['status'])); ?>
                    </span>
                </div>
                
                <div class="performance-metrics">
                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="metric-value"><?php echo esc_html($strategy['performance']['total_signals']); ?></div>
                            <div class="metric-label"><?php esc_html_e('Total Signals', 'tradepress'); ?></div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value"><?php echo esc_html($strategy['performance']['positive_signals']); ?></div>
                            <div class="metric-label"><?php esc_html_e('Positive Signals', 'tradepress'); ?></div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value success-rate"><?php echo esc_html($strategy['performance']['success_rate']); ?>%</div>
                            <div class="metric-label"><?php esc_html_e('Success Rate', 'tradepress'); ?></div>
                        </div>
                        <div class="metric-item">
                            <div class="metric-value avg-score"><?php echo esc_html($strategy['performance']['avg_score']); ?></div>
                            <div class="metric-label"><?php esc_html_e('Avg Score', 'tradepress'); ?></div>
                        </div>
                    </div>
                    
                    <div class="last-signal">
                        <span class="label"><?php esc_html_e('Last Signal:', 'tradepress'); ?></span>
                        <span class="value"><?php echo esc_html($strategy['performance']['last_signal']); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($strategy['recent_tests'])): ?>
                    <div class="recent-tests">
                        <h5><?php esc_html_e('Recent Test Results', 'tradepress'); ?></h5>
                        <div class="tests-table">
                            <div class="table-header">
                                <span><?php esc_html_e('Symbol', 'tradepress'); ?></span>
                                <span><?php esc_html_e('Score', 'tradepress'); ?></span>
                                <span><?php esc_html_e('Date', 'tradepress'); ?></span>
                            </div>
                            <?php foreach ($strategy['recent_tests'] as $test): ?>
                                <div class="table-row">
                                    <span class="symbol"><?php echo esc_html($test['symbol']); ?></span>
                                    <span class="score score-<?php echo $test['score'] >= 70 ? 'high' : ($test['score'] >= 50 ? 'medium' : 'low'); ?>">
                                        <?php echo esc_html($test['score']); ?>
                                    </span>
                                    <span class="date"><?php echo esc_html($test['date']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-tests">
                        <p><?php esc_html_e('No test results available for this strategy.', 'tradepress'); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="strategy-actions">
                    <button type="button" class="button test-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                        <?php esc_html_e('Run Test', 'tradepress'); ?>
                    </button>
                    <button type="button" class="button view-details" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                        <?php esc_html_e('View Details', 'tradepress'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=tradepress_scoring_directives&tab=manage_strategies'); ?>" class="button button-secondary">
                        <?php esc_html_e('Edit Strategy', 'tradepress'); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="testing-interface">
        <h3><?php esc_html_e('Strategy Testing', 'tradepress'); ?></h3>
        <div class="test-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="test-strategy-select"><?php esc_html_e('Select Strategy:', 'tradepress'); ?></label>
                    <select id="test-strategy-select" class="regular-text">
                        <option value=""><?php esc_html_e('Choose a strategy...', 'tradepress'); ?></option>
                        <?php foreach ($sample_strategies as $strategy): ?>
                            <option value="<?php echo esc_attr($strategy['id']); ?>">
                                <?php echo esc_html($strategy['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="test-symbol"><?php esc_html_e('Test Symbol:', 'tradepress'); ?></label>
                    <input type="text" id="test-symbol" class="regular-text" placeholder="AAPL" value="AAPL">
                </div>
                
                <div class="form-group">
                    <label for="test-mode"><?php esc_html_e('Trading Mode:', 'tradepress'); ?></label>
                    <select id="test-mode" class="regular-text">
                        <option value="long"><?php esc_html_e('Long', 'tradepress'); ?></option>
                        <option value="short"><?php esc_html_e('Short', 'tradepress'); ?></option>
                        <option value="both"><?php esc_html_e('Both', 'tradepress'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="button" class="button button-primary" id="run-strategy-test">
                        <?php esc_html_e('Run Test', 'tradepress'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="test-results" id="test-results" style="display: none;">
            <h4><?php esc_html_e('Test Results', 'tradepress'); ?></h4>
            <div class="results-content"></div>
        </div>
    </div>
</div>

<style>
.view-strategies-interface {
    margin: 20px 0;
}

.performance-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0 40px 0;
}

.summary-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.summary-number {
    font-size: 32px;
    font-weight: bold;
    color: #0073aa;
    margin-bottom: 8px;
}

.summary-label {
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    font-weight: 600;
}

.strategies-performance {
    display: grid;
    gap: 20px;
    margin-bottom: 40px;
}

.strategy-performance-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.strategy-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.strategy-header h4 {
    margin: 0;
    color: #0073aa;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active { background: #e8f5e8; color: #2e7d32; }
.status-draft { background: #fff3e0; color: #f57c00; }

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.metric-item {
    text-align: center;
}

.metric-value {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 4px;
}

.metric-value.success-rate {
    color: #2e7d32;
}

.metric-value.avg-score {
    color: #0073aa;
}

.metric-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.last-signal {
    padding: 10px 0;
    border-top: 1px solid #f0f0f0;
    font-size: 14px;
}

.last-signal .label {
    color: #666;
    font-weight: 600;
}

.recent-tests h5 {
    margin: 20px 0 10px 0;
    font-size: 14px;
}

.tests-table {
    background: #f9f9f9;
    border-radius: 6px;
    overflow: hidden;
}

.table-header, .table-row {
    display: grid;
    grid-template-columns: 1fr 80px 100px;
    padding: 10px 15px;
    gap: 15px;
}

.table-header {
    background: #f0f0f0;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    color: #666;
}

.table-row {
    border-bottom: 1px solid #f0f0f0;
}

.table-row:last-child {
    border-bottom: none;
}

.symbol {
    font-weight: 600;
    color: #0073aa;
}

.score {
    font-weight: bold;
    text-align: center;
}

.score-high { color: #2e7d32; }
.score-medium { color: #f57c00; }
.score-low { color: #d32f2f; }

.date {
    font-size: 13px;
    color: #666;
}

.no-tests {
    padding: 20px;
    text-align: center;
    color: #999;
    font-style: italic;
}

.strategy-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.testing-interface {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.testing-interface h3 {
    margin-top: 0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 15px;
    align-items: end;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    font-size: 14px;
}

.test-results {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.results-content {
    font-family: monospace;
    background: #f5f5f5;
    padding: 15px;
    border-radius: 4px;
    white-space: pre-wrap;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Test strategy from performance cards
    $('.test-strategy').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        // TODO: Implement test functionality
        alert('Strategy testing will be implemented in next phase. Strategy ID: ' + strategyId);
    });
    
    // View strategy details
    $('.view-details').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        // TODO: Implement view details functionality
        alert('View details will be implemented in next phase. Strategy ID: ' + strategyId);
    });
    
    // Run strategy test from testing interface
    $('#run-strategy-test').on('click', function() {
        const strategyId = $('#test-strategy-select').val();
        const symbol = $('#test-symbol').val().trim();
        const mode = $('#test-mode').val();
        const $button = $(this);
        
        if (!strategyId) {
            alert('Please select a strategy to test.');
            return;
        }
        
        if (!symbol) {
            alert('Please enter a symbol to test.');
            return;
        }
        
        $button.prop('disabled', true).text('Testing...');
        
        $.post(ajaxurl, {
            action: 'tradepress_test_strategy',
            nonce: '<?php echo wp_create_nonce('tradepress_strategy_nonce'); ?>',
            strategy_id: strategyId,
            symbol: symbol,
            trading_mode: mode
        })
        .done(function(response) {
            if (response.success) {
                const result = response.data;
                let resultsText = `Strategy: ${result.strategy_name}\n`;
                resultsText += `Symbol: ${result.symbol}\n`;
                resultsText += `Mode: ${result.trading_mode}\n`;
                resultsText += `Total Score: ${result.total_score}\n`;
                resultsText += `Execution Time: ${result.execution_time}ms\n\n`;
                resultsText += 'Individual Directive Scores:\n';
                
                result.individual_scores.forEach(score => {
                    resultsText += `- ${score.directive_name}: ${score.raw_score} (weight: ${score.weight}%, weighted: ${score.weighted_score.toFixed(2)})\n`;
                    if (score.error) {
                        resultsText += `  Error: ${score.error}\n`;
                    }
                });
                
                $('#test-results .results-content').text(resultsText);
                $('#test-results').show();
                
                // Refresh page to show updated performance metrics
                setTimeout(() => location.reload(), 3000);
            } else {
                alert('Test failed: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            $button.prop('disabled', false).text('Run Test');
        });
    });
});
</script>