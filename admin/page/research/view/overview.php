<?php
/**
 * TradePress Research Overview Tab
 *
 * @package TradePress/Admin/Research
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get recent symbols data
$recent_symbols_data = array();
if (class_exists('TradePress_Recent_Symbols')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/utils/recent-symbols.php';
    $recent_symbols_data = TradePress_Recent_Symbols::get_recent_symbols_data(10); // Get 10 recent symbols
}

// Get top technical indicators
$technical_symbols = array();
if (function_exists('tradepress_generate_test_technical_indicators')) {
    $symbols = array('AAPL', 'MSFT', 'NVDA', 'TSLA', 'GOOG');
    foreach ($symbols as $symbol) {
        $technical_symbols[$symbol] = tradepress_generate_test_technical_indicators($symbol);
    }
}

// Get market movers data without directly instantiating the class
$market_movers = array();

// Create sample market movers data since we can't call the dashboard widget method
$market_movers = array(
    'gainers' => array(
        array(
            'symbol' => 'AAPL',
            'price' => 185.92,
            'change_pct' => 2.45,
        ),
        array(
            'symbol' => 'MSFT',
            'price' => 376.17,
            'change_pct' => 1.87,
        ),
        array(
            'symbol' => 'NVDA',
            'price' => 950.02,
            'change_pct' => 3.25,
        ),
        array(
            'symbol' => 'AMZN',
            'price' => 180.35,
            'change_pct' => 1.23,
        ),
    ),
    'losers' => array(
        array(
            'symbol' => 'META',
            'price' => 487.95,
            'change_pct' => -1.74,
        ),
        array(
            'symbol' => 'NFLX',
            'price' => 657.31,
            'change_pct' => -2.12,
        ),
        array(
            'symbol' => 'GOOG',
            'price' => 156.98,
            'change_pct' => -0.87,
        ),
        array(
            'symbol' => 'TSLA',
            'price' => 223.31,
            'change_pct' => -3.54,
        ),
    ),
    'volume' => array(
        array(
            'symbol' => 'AAPL',
            'price' => 185.92,
            'volume' => 75489632,
        ),
        array(
            'symbol' => 'SPY',
            'price' => 523.89,
            'volume' => 68741523,
        ),
        array(
            'symbol' => 'NVDA',
            'price' => 950.02,
            'volume' => 35874123,
        ),
        array(
            'symbol' => 'TSLA',
            'price' => 223.31,
            'volume' => 28561974,
        ),
    ),
);

// Get symbols with strong scoring directives
$directives_symbols = array();
if (class_exists('TradePress_Directive_Registry')) {
    $symbols = array('AAPL', 'NVDA', 'MSFT', 'META', 'AMZN');
    $directives = TradePress_Directive_Registry::get_directives();
    
    foreach ($symbols as $symbol) {
        $score = mt_rand(60, 95);
        $directives_count = mt_rand(2, 6);
        $applicable_directives = array_rand($directives, min($directives_count, count($directives)));
        if (!is_array($applicable_directives)) {
            $applicable_directives = array($applicable_directives);
        }
        
        $directive_names = array();
        foreach ($applicable_directives as $directive_key) {
            if (isset($directives[$directive_key])) {
                $directive_names[] = $directives[$directive_key]['name'];
            }
        }
        
        $directives_symbols[$symbol] = array(
            'score' => $score,
            'directives' => $directive_names,
            'strength' => ($score >= 80) ? 'strong' : (($score >= 65) ? 'moderate' : 'weak')
        );
    }
}
?>

<div class="research-overview-wrapper">
    <div class="overview-header">
        <h2><?php esc_html_e('Research Overview', 'tradepress'); ?></h2>
        <p class="description"><?php esc_html_e('A summary of your recent research activity and market insights', 'tradepress'); ?></p>
    </div>
    
    <div class="overview-grid">
        <!-- Recent Research Activity -->
        <div class="overview-card recent-activity">
            <div class="card-header">
                <h3><?php esc_html_e('Recent Research Activity', 'tradepress'); ?></h3>
                <span class="refresh-data dashicons dashicons-update" title="<?php esc_attr_e('Refresh Data', 'tradepress'); ?>"></span>
            </div>
            <div class="card-content">
                <?php if (empty($recent_symbols_data)): ?>
                    <div class="no-data-message">
                        <p><?php esc_html_e('No recent research activity found. Start analyzing stocks to see them appear here.', 'tradepress'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=symbol-lookup')); ?>" class="button button-secondary"><?php esc_html_e('Look Up Symbols', 'tradepress'); ?></a>
                    </div>
                <?php else: ?>
                    <ul class="recent-symbols-list">
                        <?php foreach ($recent_symbols_data as $symbol => $data): ?>
                            <li class="symbol-item">
                                <div class="symbol-icon">
                                    <?php if (isset($data['thumbnail']) && !empty($data['thumbnail'])): ?>
                                        <img src="<?php echo esc_url($data['thumbnail']); ?>" alt="<?php echo esc_attr($symbol); ?>">
                                    <?php else: ?>
                                        <span class="default-icon"><?php echo esc_html(substr($symbol, 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="symbol-details">
                                    <h4>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=technical-indicators&symbol=' . $symbol)); ?>">
                                            <?php echo esc_html($symbol); ?>
                                        </a>
                                    </h4>
                                    <p class="company-name"><?php echo isset($data['company_name']) ? esc_html($data['company_name']) : ''; ?></p>
                                </div>
                                <div class="symbol-metrics">
                                    <span class="price"><?php echo isset($data['price']) ? '$' . number_format($data['price'], 2) : ''; ?></span>
                                    <?php if (isset($data['change_percent'])): ?>
                                        <span class="change <?php echo ($data['change_percent'] >= 0) ? 'positive' : 'negative'; ?>">
                                            <?php echo ($data['change_percent'] >= 0) ? '+' : ''; ?><?php echo number_format($data['change_percent'], 2); ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="card-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=symbol-lookup')); ?>" class="button button-secondary"><?php esc_html_e('Look Up More Symbols', 'tradepress'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Technical Indicators Summary -->
        <div class="overview-card technical-summary">
            <div class="card-header">
                <h3><?php esc_html_e('Technical Indicators Summary', 'tradepress'); ?></h3>
                <span class="refresh-data dashicons dashicons-update" title="<?php esc_attr_e('Refresh Data', 'tradepress'); ?>"></span>
            </div>
            <div class="card-content">
                <?php if (empty($technical_symbols)): ?>
                    <div class="no-data-message">
                        <p><?php esc_html_e('No technical analysis data available. Analyze symbols to see technical indicators summary.', 'tradepress'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=technical-indicators')); ?>" class="button button-secondary"><?php esc_html_e('Technical Analysis', 'tradepress'); ?></a>
                    </div>
                <?php else: ?>
                    <table class="indicators-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                                <th><?php esc_html_e('RSI', 'tradepress'); ?></th>
                                <th><?php esc_html_e('MACD', 'tradepress'); ?></th>
                                <th><?php esc_html_e('MAs', 'tradepress'); ?></th>
                                <th><?php esc_html_e('BB', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($technical_symbols as $symbol => $data): ?>
                                <tr>
                                    <td class="symbol-column">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=technical-indicators&symbol=' . $symbol)); ?>">
                                            <?php echo esc_html($symbol); ?>
                                        </a>
                                    </td>
                                    <td class="indicator-column">
                                        <span class="indicator-badge <?php echo esc_attr($data['rsi']['signal']); ?>">
                                            <?php echo esc_html($data['rsi']['value']); ?>
                                        </span>
                                    </td>
                                    <td class="indicator-column">
                                        <span class="indicator-badge <?php echo esc_attr($data['macd']['indicator']); ?>">
                                            <?php echo esc_html(ucfirst($data['macd']['indicator'])); ?>
                                        </span>
                                    </td>
                                    <td class="indicator-column">
                                        <span class="indicator-badge <?php echo esc_attr(str_replace(' ', '-', strtolower($data['moving_averages']['signal']))); ?>">
                                            <?php echo esc_html(ucfirst($data['moving_averages']['signal'])); ?>
                                        </span>
                                    </td>
                                    <td class="indicator-column">
                                        <span class="indicator-badge <?php echo esc_attr($data['bollinger']['signal']); ?>">
                                            <?php echo esc_html(ucfirst($data['bollinger']['signal'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="card-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=technical-indicators')); ?>" class="button button-secondary"><?php esc_html_e('Full Technical Analysis', 'tradepress'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Market Movers -->
        <div class="overview-card market-movers">
            <div class="card-header">
                <h3><?php esc_html_e('Market Movers', 'tradepress'); ?></h3>
                <span class="refresh-data dashicons dashicons-update" title="<?php esc_attr_e('Refresh Data', 'tradepress'); ?>"></span>
            </div>
            <div class="card-content">
                <?php if (empty($market_movers) || !isset($market_movers['gainers']) || !isset($market_movers['losers'])): ?>
                    <div class="no-data-message">
                        <p><?php esc_html_e('Market movers data is not available at the moment.', 'tradepress'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="movers-tabs">
                        <div class="tabs-nav">
                            <button class="tab-button active" data-tab="gainers"><?php esc_html_e('Top Gainers', 'tradepress'); ?></button>
                            <button class="tab-button" data-tab="losers"><?php esc_html_e('Top Losers', 'tradepress'); ?></button>
                        </div>
                        
                        <div class="tabs-content">
                            <div id="gainers-tab" class="tab-pane active">
                                <table class="movers-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Price', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Change', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($market_movers['gainers'], 0, 5) as $gainer): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=symbol-lookup&symbol=' . $gainer['symbol'])); ?>">
                                                        <?php echo esc_html($gainer['symbol']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo esc_html('$' . number_format($gainer['price'], 2)); ?></td>
                                                <td class="positive">+<?php echo esc_html(number_format($gainer['change_pct'], 2)); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="losers-tab" class="tab-pane">
                                <table class="movers-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Price', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Change', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($market_movers['losers'], 0, 5) as $loser): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=symbol-lookup&symbol=' . $loser['symbol'])); ?>">
                                                        <?php echo esc_html($loser['symbol']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo esc_html('$' . number_format($loser['price'], 2)); ?></td>
                                                <td class="negative"><?php echo esc_html(number_format($loser['change_pct'], 2)); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Scoring Directives Results -->
        <div class="overview-card directives-results">
            <div class="card-header">
                <h3><?php esc_html_e('Scoring Directives Results', 'tradepress'); ?></h3>
                <span class="refresh-data dashicons dashicons-update" title="<?php esc_attr_e('Refresh Data', 'tradepress'); ?>"></span>
            </div>
            <div class="card-content">
                <?php if (empty($directives_symbols)): ?>
                    <div class="no-data-message">
                        <p><?php esc_html_e('No scoring directives data available. Configure and run the scoring algorithm to see results here.', 'tradepress'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_automation&tab=scoring-directives')); ?>" class="button button-secondary"><?php esc_html_e('Scoring Directives', 'tradepress'); ?></a>
                    </div>
                <?php else: ?>
                    <table class="directives-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Score', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Signal', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Directives', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($directives_symbols as $symbol => $data): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=symbol-lookup&symbol=' . $symbol)); ?>">
                                            <?php echo esc_html($symbol); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="score-circle <?php echo esc_attr($data['strength']); ?>">
                                            <?php echo esc_html($data['score']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="signal-badge <?php echo esc_attr($data['strength']); ?>">
                                            <?php echo esc_html(ucfirst($data['strength'])); ?>
                                        </span>
                                    </td>
                                    <td class="directives-list">
                                        <?php if (!empty($data['directives'])): ?>
                                            <div class="directives-tooltip">
                                                <?php echo count($data['directives']) . ' ' . esc_html__('Matched', 'tradepress'); ?>
                                                <span class="directives-tooltip-text">
                                                    <?php echo esc_html(implode(', ', $data['directives'])); ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <?php esc_html_e('None', 'tradepress'); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="card-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_automation&tab=scoring-directives')); ?>" class="button button-secondary"><?php esc_html_e('Manage Scoring Directives', 'tradepress'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
