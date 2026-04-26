<?php
/**
 * TradePress Analysis - Recent Symbols tab view
 *
 * @package TradePress/Admin/Analysis
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Make sure the helper functions are loaded
if (!function_exists('tradepress_get_tab_mode')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/functions/function.tradepress-features-helpers.php';
}

// Get the tab mode information
$page_id = 'analysis';
$tab_id = 'recent_symbols';
$tab_mode = tradepress_get_tab_mode($page_id, $tab_id);

// Check if tab is enabled
if (!$tab_mode['enabled']) {
    echo '<div class="notice notice-warning"><p>' . 
         esc_html__('This tab is currently disabled in Features settings.', 'tradepress') . 
         '</p></div>';
    return;
}

// Show demo mode notice if applicable
if ($tab_mode['mode'] === 'demo') {
    echo '<div class="demo-mode-notice">';
    echo '<div class="demo-mode-icon dashicons dashicons-warning"></div>';
    echo '<div class="demo-mode-content">';
    echo '<h4>' . esc_html__('Demo Mode', 'tradepress') . '</h4>';
    echo '<p>' . esc_html__('This tab is running in demo mode with sample data. In live mode, this will display actual analysis data from your tracked symbols.', 'tradepress') . '</p>';
    echo '</div>';
    echo '</div>';
}

// Demo symbols data
$symbols = array(
    array(
        'symbol' => 'AAPL',
        'name' => 'Apple Inc.',
        'price' => 186.34,
        'change' => 1.45,
        'change_percent' => 0.78,
        'score' => 78,
        'max_score' => 100,
        'weight' => 25,
        'last_updated' => '2023-06-25 14:35:22',
        'indicators' => array(
            'rsi' => array('value' => 63.4, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 65, 'weight' => 25),
            'macd' => array('value' => 1.25, 'signal' => 'bullish', 'description' => 'MACD above signal line', 'score' => 82, 'weight' => 20),
            'moving_averages' => array('value' => '200 SMA support', 'signal' => 'bullish', 'description' => 'Price above major moving averages', 'score' => 90, 'weight' => 30),
            'volume' => array('value' => '+ 15% avg', 'signal' => 'bullish', 'description' => 'Volume above average', 'score' => 75, 'weight' => 25)
        ),
        'analysis' => 'Strong technical position with steady uptrend. Multiple technical indicators showing bullish signals, particularly the MACD crossover and above-average volume. Price action has formed a solid base above the 50-day moving average.',
        'recommendation' => 'Buy'
    ),
    array(
        'symbol' => 'MSFT',
        'name' => 'Microsoft Corporation',
        'price' => 334.57,
        'change' => -2.13,
        'change_percent' => -0.63,
        'score' => 65,
        'max_score' => 100,
        'weight' => 20,
        'last_updated' => '2023-06-25 14:32:10',
        'indicators' => array(
            'rsi' => array('value' => 58.2, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 55, 'weight' => 25),
            'macd' => array('value' => 0.87, 'signal' => 'neutral', 'description' => 'MACD flat relative to signal line', 'score' => 50, 'weight' => 20),
            'moving_averages' => array('value' => '50 SMA test', 'signal' => 'neutral', 'description' => 'Price testing 50-day moving average', 'score' => 60, 'weight' => 30),
            'volume' => array('value' => '- 5% avg', 'signal' => 'neutral', 'description' => 'Volume slightly below average', 'score' => 45, 'weight' => 25)
        ),
        'analysis' => 'Currently consolidating after a strong run-up. The recent pullback appears to be a normal retracement within an overall uptrend. Price is testing the 50-day moving average which may provide support.',
        'recommendation' => 'Hold'
    ),
    array(
        'symbol' => 'NVDA',
        'name' => 'NVIDIA Corporation',
        'price' => 925.73,
        'change' => 32.45,
        'change_percent' => 3.63,
        'score' => 92,
        'max_score' => 100,
        'weight' => 35,
        'last_updated' => '2023-06-25 14:30:45',
        'indicators' => array(
            'rsi' => array('value' => 78.6, 'signal' => 'overbought', 'description' => 'Potentially overbought', 'score' => 75, 'weight' => 25),
            'macd' => array('value' => 12.45, 'signal' => 'strongly bullish', 'description' => 'MACD well above signal line', 'score' => 95, 'weight' => 20),
            'moving_averages' => array('value' => '50 & 200 SMA support', 'signal' => 'strongly bullish', 'description' => 'Price well above all major moving averages', 'score' => 98, 'weight' => 30),
            'volume' => array('value' => '+ 85% avg', 'signal' => 'strongly bullish', 'description' => 'Volume significantly above average', 'score' => 95, 'weight' => 25)
        ),
        'analysis' => 'Extremely strong momentum with significant institutional buying. While RSI indicates potential overbought conditions, the strength of the trend and volume profile suggest continued upside potential. Key driver is AI-related revenue growth.',
        'recommendation' => 'Strong Buy'
    ),
    array(
        'symbol' => 'TSLA',
        'name' => 'Tesla, Inc.',
        'price' => 184.47,
        'change' => -5.89,
        'change_percent' => -3.09,
        'score' => 42,
        'max_score' => 100,
        'weight' => 20,
        'last_updated' => '2023-06-25 14:28:33',
        'indicators' => array(
            'rsi' => array('value' => 42.3, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 45, 'weight' => 25),
            'macd' => array('value' => -1.56, 'signal' => 'bearish', 'description' => 'MACD below signal line', 'score' => 35, 'weight' => 20),
            'moving_averages' => array('value' => 'Below 50 SMA', 'signal' => 'bearish', 'description' => 'Price below 50-day moving average', 'score' => 30, 'weight' => 30),
            'volume' => array('value' => '+ 32% avg', 'signal' => 'neutral', 'description' => 'Above average volume but selling pressure', 'score' => 60, 'weight' => 25)
        ),
        'analysis' => 'Currently showing weakness with a break below the 50-day moving average. The increased volume on down days suggests distribution. MACD showing bearish divergence. May need time to build a new base before resuming uptrend.',
        'recommendation' => 'Neutral'
    ),
    array(
        'symbol' => 'AMZN',
        'name' => 'Amazon.com, Inc.',
        'price' => 178.12,
        'change' => 3.24,
        'change_percent' => 1.85,
        'score' => 81,
        'max_score' => 100,
        'weight' => 30,
        'last_updated' => '2023-06-25 14:27:15',
        'indicators' => array(
            'rsi' => array('value' => 67.2, 'signal' => 'neutral', 'description' => 'Approaching overbought territory', 'score' => 70, 'weight' => 25),
            'macd' => array('value' => 2.88, 'signal' => 'bullish', 'description' => 'MACD crossed above signal line', 'score' => 85, 'weight' => 20),
            'moving_averages' => array('value' => 'Above all key MAs', 'signal' => 'bullish', 'description' => 'Price above all key moving averages', 'score' => 90, 'weight' => 30),
            'volume' => array('value' => '+ 25% avg', 'signal' => 'bullish', 'description' => 'Volume above average on up days', 'score' => 80, 'weight' => 25)
        ),
        'analysis' => 'Amazon showing strong momentum following positive e-commerce growth data. Price action has broken out of a multi-month consolidation pattern with increasing volume. The stock is showing leadership within the tech sector with potential for further upside.',
        'recommendation' => 'Buy'
    ),
    array(
        'symbol' => 'META',
        'name' => 'Meta Platforms, Inc.',
        'price' => 472.86,
        'change' => 6.31,
        'change_percent' => 1.35,
        'score' => 84,
        'max_score' => 100,
        'weight' => 25,
        'last_updated' => '2023-06-25 14:26:42',
        'indicators' => array(
            'rsi' => array('value' => 69.8, 'signal' => 'neutral', 'description' => 'Approaching overbought territory', 'score' => 68, 'weight' => 25),
            'macd' => array('value' => 3.12, 'signal' => 'bullish', 'description' => 'MACD above signal line', 'score' => 88, 'weight' => 20),
            'moving_averages' => array('value' => 'Above 20, 50, 200 MAs', 'signal' => 'bullish', 'description' => 'Price above all major moving averages', 'score' => 92, 'weight' => 30),
            'volume' => array('value' => '+ 40% avg', 'signal' => 'bullish', 'description' => 'Strong volume on breakout', 'score' => 85, 'weight' => 25)
        ),
        'analysis' => 'Meta continues to demonstrate strong execution with its AI initiatives and ad platform improvements. Recent earnings beat expectations with particular strength in engagement metrics. Technical pattern shows a cup-and-handle formation that has broken to the upside.',
        'recommendation' => 'Strong Buy'
    ),
    array(
        'symbol' => 'GOOGL',
        'name' => 'Alphabet Inc.',
        'price' => 167.83,
        'change' => 0.58,
        'change_percent' => 0.35,
        'score' => 76,
        'max_score' => 100,
        'weight' => 28,
        'last_updated' => '2023-06-25 14:25:18',
        'indicators' => array(
            'rsi' => array('value' => 61.4, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 65, 'weight' => 25),
            'macd' => array('value' => 1.05, 'signal' => 'neutral', 'description' => 'MACD slightly above signal line', 'score' => 70, 'weight' => 20),
            'moving_averages' => array('value' => 'Above 50 & 200 SMAs', 'signal' => 'bullish', 'description' => 'Price above major moving averages', 'score' => 85, 'weight' => 30),
            'volume' => array('value' => '+ 5% avg', 'signal' => 'neutral', 'description' => 'Volume slightly above average', 'score' => 60, 'weight' => 25)
        ),
        'analysis' => 'Alphabet showing steady progress in its core search business with increasing AI integration. The stock is in a solid uptrend but currently consolidating recent gains. Advertising revenue outlook remains positive with potential catalyst from upcoming product announcements.',
        'recommendation' => 'Buy'
    ),
    array(
        'symbol' => 'AMD',
        'name' => 'Advanced Micro Devices, Inc.',
        'price' => 157.25,
        'change' => 4.36,
        'change_percent' => 2.85,
        'score' => 79,
        'max_score' => 100,
        'weight' => 15,
        'last_updated' => '2023-06-25 14:24:05',
        'indicators' => array(
            'rsi' => array('value' => 64.3, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 68, 'weight' => 25),
            'macd' => array('value' => 2.34, 'signal' => 'bullish', 'description' => 'MACD above signal line', 'score' => 80, 'weight' => 20),
            'moving_averages' => array('value' => 'Above key MAs', 'signal' => 'bullish', 'description' => 'Price above all key moving averages', 'score' => 88, 'weight' => 30),
            'volume' => array('value' => '+ 20% avg', 'signal' => 'bullish', 'description' => 'Above average volume on up days', 'score' => 75, 'weight' => 25)
        ),
        'analysis' => 'AMD continues to gain market share in the datacenter segment with its MI300 AI accelerators. Technical pattern shows a strong breakout from a base with increasing volume. Near-term resistance at $165 with support at the 20-day moving average around $149.',
        'recommendation' => 'Buy'
    ),
    array(
        'symbol' => 'INTC',
        'name' => 'Intel Corporation',
        'price' => 31.44,
        'change' => -0.87,
        'change_percent' => -2.69,
        'score' => 38,
        'max_score' => 100,
        'weight' => 10,
        'last_updated' => '2023-06-25 14:22:53',
        'indicators' => array(
            'rsi' => array('value' => 42.6, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 45, 'weight' => 25),
            'macd' => array('value' => -0.76, 'signal' => 'bearish', 'description' => 'MACD below signal line', 'score' => 35, 'weight' => 20),
            'moving_averages' => array('value' => 'Below 50 SMA', 'signal' => 'bearish', 'description' => 'Price below 50-day moving average', 'score' => 30, 'weight' => 30),
            'volume' => array('value' => '+ 10% avg', 'signal' => 'neutral', 'description' => 'Average volume characteristics', 'score' => 50, 'weight' => 25)
        ),
        'analysis' => 'Intel faces continued challenges in regaining technological leadership and market share. Recent earnings disappointment has led to a break of support levels. The stock is trading below most major moving averages with increased selling pressure. Recovery dependent on successful execution of new product roadmap.',
        'recommendation' => 'Sell'
    ),
    array(
        'symbol' => 'QCOM',
        'name' => 'Qualcomm Incorporated',
        'price' => 170.92,
        'change' => 2.34,
        'change_percent' => 1.39,
        'score' => 73,
        'max_score' => 100,
        'weight' => 15,
        'last_updated' => '2023-06-25 14:21:37',
        'indicators' => array(
            'rsi' => array('value' => 58.7, 'signal' => 'neutral', 'description' => 'Neither overbought nor oversold', 'score' => 60, 'weight' => 25),
            'macd' => array('value' => 1.65, 'signal' => 'bullish', 'description' => 'MACD above signal line', 'score' => 75, 'weight' => 20),
            'moving_averages' => array('value' => 'Above 50 & 200 SMAs', 'signal' => 'bullish', 'description' => 'Price above major moving averages', 'score' => 80, 'weight' => 30),
            'volume' => array('value' => '+ 15% avg', 'signal' => 'neutral', 'description' => 'Slightly above average volume', 'score' => 65, 'weight' => 25)
        ),
        'analysis' => 'Qualcomm showing improving momentum with expansion beyond mobile into automotive and IoT markets. Recent product announcements in AI processors have been well-received by the market. The stock is in an uptrend channel with support at the 50-day moving average of $165.',
        'recommendation' => 'Buy'
    )
);

// Group symbols by trading strategy categories
$portfolioSymbols = array();
$dayTradingSymbols = array();
$intradaySymbols = array();

// Assign symbols to appropriate categories based on characteristics
foreach ($symbols as $symbol) {
    if (in_array($symbol['symbol'], ['AAPL', 'MSFT', 'GOOGL', 'AMZN'])) {
        $symbol['portfolio_advice'] = getPortfolioAdvice($symbol);
        $portfolioSymbols[] = $symbol;
    }
    if (in_array($symbol['symbol'], ['NVDA', 'TSLA', 'AMD', 'META'])) {
        $symbol['day_trading_advice'] = getDayTradingAdvice($symbol);
        $dayTradingSymbols[] = $symbol;
    }
    if (in_array($symbol['symbol'], ['META', 'NVDA', 'TSLA', 'QCOM', 'INTC'])) {
        $symbol['intraday_advice'] = getIntradayAdvice($symbol);
        $intradaySymbols[] = $symbol;
    }
}

/**
 * Generate portfolio-focused advice for a symbol
 */
function getPortfolioAdvice($symbol) {
    $advice = array(
        'summary' => '',
        'allocation' => 0,
        'horizon' => '',
        'risk' => '',
        'catalyst' => ''
    );
    $score = $symbol['score'];
    if ($score > 80) {
        $advice['summary'] = 'Strong candidate for core portfolio position';
        $advice['allocation'] = 'Consider 3-5% allocation';
        $advice['horizon'] = '1-3 years minimum hold';
        $advice['risk'] = 'Low to medium';
        $advice['catalyst'] = 'Consistent growth, strong market position';
    } elseif ($score > 65) {
        $advice['summary'] = 'Solid long-term position with growth potential';
        $advice['allocation'] = 'Consider 2-4% allocation';
        $advice['horizon'] = '1-2 years recommended hold';
        $advice['risk'] = 'Medium';
        $advice['catalyst'] = 'Sector leadership, stable fundamentals';
    } else {
        $advice['summary'] = 'Speculative long-term hold';
        $advice['allocation'] = 'Consider small position (1-2%)';
        $advice['horizon'] = 'Monitor quarterly for reassessment';
        $advice['risk'] = 'Medium to high';
        $advice['catalyst'] = 'Potential turnaround or undervaluation';
    }
    return $advice;
}

/**
 * Generate day trading advice for a symbol
 */
function getDayTradingAdvice($symbol) {
    $advice = array(
        'summary' => '',
        'setup' => '',
        'entry' => '',
        'exit' => '',
        'risk_reward' => '',
        'stop_loss' => 0
    );
    $macd_bullish = strpos(strtolower($symbol['indicators']['macd']['signal']), 'bullish') !== false;
    $rsi_value = $symbol['indicators']['rsi']['value'];
    $rsi_oversold = $rsi_value < 30;
    $rsi_overbought = $rsi_value > 70;
    $price_momentum = $symbol['change_percent'];
    if ($macd_bullish && $price_momentum > 0 && !$rsi_overbought) {
        $advice['summary'] = 'Bullish day trade setup with momentum';
        $advice['setup'] = 'Bullish trend continuation pattern';
        $advice['entry'] = 'Buy on pullback to 20-minute EMA';
        $advice['exit'] = 'Take profit at prior resistance or 2:1 reward-risk ratio';
        $advice['risk_reward'] = '2:1 minimum';
        $advice['stop_loss'] = round($symbol['price'] * 0.985, 2);
    } elseif (!$macd_bullish && $price_momentum < 0 && !$rsi_oversold) {
        $advice['summary'] = 'Bearish day trade setup';
        $advice['setup'] = 'Bearish trend continuation';
        $advice['entry'] = 'Short on bounce to 20-minute EMA';
        $advice['exit'] = 'Cover at prior support or 2:1 reward-risk ratio';
        $advice['risk_reward'] = '2:1 minimum';
        $advice['stop_loss'] = round($symbol['price'] * 1.015, 2);
    } else {
        $advice['summary'] = 'Neutral day trading setup';
        $advice['setup'] = 'Wait for clearer directional bias';
        $advice['entry'] = 'No clear entry at current price';
        $advice['exit'] = 'N/A';
        $advice['risk_reward'] = 'Undefined';
        $advice['stop_loss'] = 0;
    }
    return $advice;
}

/**
 * Generate intraday trading advice for a symbol
 */
function getIntradayAdvice($symbol) {
    $advice = array(
        'summary' => '',
        'timeframes' => '',
        'key_levels' => '',
        'volume_zones' => '',
        'patterns' => '',
        'best_hours' => ''
    );
    $volatility = abs($symbol['change_percent']);
    $high_volume = strpos($symbol['indicators']['volume']['value'], '+') !== false;
    if ($volatility > 2.5 && $high_volume) {
        $advice['summary'] = 'High-opportunity intraday setup';
        $advice['timeframes'] = '5-min and 15-min charts recommended';
        $advice['key_levels'] = 'Watch pre-market high/low and previous day close';
        $advice['volume_zones'] = 'Highest in first hour and last hour of trading';
        $advice['patterns'] = 'Watch for breakouts of first hour range';
        $advice['best_hours'] = '9:30-10:30 AM and 3:00-4:00 PM EST';
    } elseif ($volatility > 1.0) {
        $advice['summary'] = 'Moderate intraday opportunity';
        $advice['timeframes'] = '15-min chart for entries, 5-min for exits';
        $advice['key_levels'] = 'VWAP and 9-EMA are key intraday references';
        $advice['volume_zones'] = 'Watch for unusual volume spikes';
        $advice['patterns'] = 'Flag patterns and consolidation breaks';
        $advice['best_hours'] = '9:30-11:00 AM EST';
    } else {
        $advice['summary'] = 'Limited intraday movement expected';
        $advice['timeframes'] = 'Consider longer timeframes or other symbols';
        $advice['key_levels'] = 'Major support/resistance levels only';
        $advice['volume_zones'] = 'Insufficient volume for clean intraday moves';
        $advice['patterns'] = 'Range-bound behavior likely';
        $advice['best_hours'] = 'Avoid except on news catalysts';
    }
    return $advice;
}
?>

<div class="recent-symbols-container">    
    <!-- Long-term Portfolio Section -->
    <div class="trading-strategy-section portfolio-section">
        <div class="strategy-header">
            <h3><?php esc_html_e('Long-term Portfolio Candidates', 'tradepress'); ?></h3>
            <p class="strategy-description">
                <?php esc_html_e('Symbols suitable for long-term investment (1+ year horizon)', 'tradepress'); ?>
            </p>
        </div>
        
        <div class="symbols-grid">
            <?php foreach ($portfolioSymbols as $symbol) : 
                $weighted_percentage = round(($symbol['score'] / $symbol['max_score']) * 100);
            ?>
                <div class="symbol-card <?php echo esc_attr(strtolower($symbol['recommendation'])); ?>">
                    <div class="symbol-header">
                        <div class="symbol-name-section">
                            <h3 class="symbol-ticker"><?php echo esc_html($symbol['symbol']); ?></h3>
                            <span class="symbol-full-name"><?php echo esc_html($symbol['name']); ?></span>
                        </div>
                        <div class="symbol-price-section">
                            <span class="symbol-price"><?php echo '$' . number_format($symbol['price'], 2); ?></span>
                            <span class="symbol-change <?php echo $symbol['change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $symbol['change'] >= 0 ? '+' : ''; echo number_format($symbol['change'], 2); ?> 
                                (<?php echo $symbol['change'] >= 0 ? '+' : ''; echo number_format($symbol['change_percent'], 2); ?>%)
                            </span>
                        </div>
                    </div>
                    
                    <div class="symbol-score">
                        <div class="score-circle" style="background: conic-gradient(#4CAF50 0% <?php echo $weighted_percentage; ?>%, #f5f5f5 <?php echo $weighted_percentage; ?>% 100%);">
                            <span class="score-value"><?php echo esc_html($symbol['score']); ?></span>
                        </div>
                        <div class="score-details">
                            <div class="score-ratio">
                                <span class="score-ratio-value"><?php echo esc_html($symbol['score']); ?> / <?php echo esc_html($symbol['max_score']); ?></span>
                                <span class="score-percentage">(<?php echo esc_html($weighted_percentage); ?>%)</span>
                            </div>
                            <div class="recommendation">
                                <span class="recommendation-label"><?php esc_html_e('Recommendation:', 'tradepress'); ?></span>
                                <span class="recommendation-value"><?php echo esc_html($symbol['recommendation']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="strategy-specific-advice">
                        <h4><?php esc_html_e('Portfolio Strategy', 'tradepress'); ?></h4>
                        <div class="advice-summary"><?php echo esc_html($symbol['portfolio_advice']['summary']); ?></div>
                        <table class="advice-details">
                            <tr>
                                <th><?php esc_html_e('Suggested Allocation:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['portfolio_advice']['allocation']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Time Horizon:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['portfolio_advice']['horizon']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Risk Level:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['portfolio_advice']['risk']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Growth Catalyst:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['portfolio_advice']['catalyst']); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="symbol-indicators">
                        <h4><?php esc_html_e('Technical Indicators', 'tradepress'); ?></h4>
                        <table class="indicators-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Indicator', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Value', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Signal', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($symbol['indicators'] as $name => $indicator) : ?>
                                    <tr>
                                        <td class="indicator-name"><?php echo esc_html(ucfirst($name)); ?></td>
                                        <td class="indicator-value"><?php echo esc_html($indicator['value']); ?></td>
                                        <td class="indicator-signal signal-<?php echo esc_attr(str_replace(' ', '-', strtolower($indicator['signal']))); ?>">
                                            <?php echo esc_html(ucfirst($indicator['signal'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="symbol-analysis">
                        <h4><?php esc_html_e('Analysis', 'tradepress'); ?></h4>
                        <p><?php echo esc_html($symbol['analysis']); ?></p>
                    </div>
                    
                    <div class="symbol-footer">
                        <span class="last-updated">
                            <?php esc_html_e('Last updated:', 'tradepress'); ?> 
                            <?php echo esc_html($symbol['last_updated']); ?>
                        </span>
                        <a href="#" class="view-details-button"><?php esc_html_e('View Full Analysis', 'tradepress'); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Day Trading Section -->
    <div class="trading-strategy-section day-trading-section">
        <div class="strategy-header">
            <h3><?php esc_html_e('Day Trading Opportunities', 'tradepress'); ?></h3>
            <p class="strategy-description">
                <?php esc_html_e('Symbols with setups suitable for daily trades (1-day horizon)', 'tradepress'); ?>
            </p>
        </div>
        
        <div class="symbols-grid">
            <?php foreach ($dayTradingSymbols as $symbol) : 
                $weighted_percentage = round(($symbol['score'] / $symbol['max_score']) * 100);
            ?>
                <div class="symbol-card <?php echo esc_attr(strtolower($symbol['recommendation'])); ?>">
                    <div class="symbol-header">
                        <div class="symbol-name-section">
                            <h3 class="symbol-ticker"><?php echo esc_html($symbol['symbol']); ?></h3>
                            <span class="symbol-full-name"><?php echo esc_html($symbol['name']); ?></span>
                        </div>
                        <div class="symbol-price-section">
                            <span class="symbol-price"><?php echo '$' . number_format($symbol['price'], 2); ?></span>
                            <span class="symbol-change <?php echo $symbol['change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $symbol['change'] >= 0 ? '+' : ''; echo number_format($symbol['change'], 2); ?> 
                                (<?php echo $symbol['change'] >= 0 ? '+' : ''; echo number_format($symbol['change_percent'], 2); ?>%)
                            </span>
                        </div>
                    </div>
                    
                    <div class="symbol-score">
                        <div class="score-circle" style="background: conic-gradient(#4CAF50 0% <?php echo $weighted_percentage; ?>%, #f5f5f5 <?php echo $weighted_percentage; ?>% 100%);">
                            <span class="score-value"><?php echo esc_html($symbol['score']); ?></span>
                        </div>
                        <div class="score-details">
                            <div class="score-ratio">
                                <span class="score-ratio-value"><?php echo esc_html($symbol['score']); ?> / <?php echo esc_html($symbol['max_score']); ?></span>
                                <span class="score-percentage">(<?php echo esc_html($weighted_percentage); ?>%)</span>
                            </div>
                            <div class="recommendation">
                                <span class="recommendation-label"><?php esc_html_e('Recommendation:', 'tradepress'); ?></span>
                                <span class="recommendation-value"><?php echo esc_html($symbol['recommendation']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="strategy-specific-advice">
                        <h4><?php esc_html_e('Day Trading Strategy', 'tradepress'); ?></h4>
                        <div class="advice-summary"><?php echo esc_html($symbol['day_trading_advice']['summary']); ?></div>
                        <table class="advice-details">
                            <tr>
                                <th><?php esc_html_e('Setup:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['day_trading_advice']['setup']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Entry Strategy:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['day_trading_advice']['entry']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Exit Strategy:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['day_trading_advice']['exit']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Risk/Reward:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['day_trading_advice']['risk_reward']); ?></td>
                            </tr>
                            <?php if ($symbol['day_trading_advice']['stop_loss'] > 0) : ?>
                            <tr>
                                <th><?php esc_html_e('Suggested Stop Loss:', 'tradepress'); ?></th>
                                <td>$<?php echo number_format($symbol['day_trading_advice']['stop_loss'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <div class="symbol-indicators">
                        <h4><?php esc_html_e('Technical Indicators', 'tradepress'); ?></h4>
                        <table class="indicators-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Indicator', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Value', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Signal', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($symbol['indicators'] as $name => $indicator) : ?>
                                    <tr>
                                        <td class="indicator-name"><?php echo esc_html(ucfirst($name)); ?></td>
                                        <td class="indicator-value"><?php echo esc_html($indicator['value']); ?></td>
                                        <td class="indicator-signal signal-<?php echo esc_attr(str_replace(' ', '-', strtolower($indicator['signal']))); ?>">
                                            <?php echo esc_html(ucfirst($indicator['signal'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="symbol-footer">
                        <span class="last-updated">
                            <?php esc_html_e('Last updated:', 'tradepress'); ?> 
                            <?php echo esc_html($symbol['last_updated']); ?>
                        </span>
                        <a href="#" class="view-details-button"><?php esc_html_e('View Full Analysis', 'tradepress'); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Intraday Trading Section -->
    <div class="trading-strategy-section intraday-section">
        <div class="strategy-header">
            <h3><?php esc_html_e('Intraday Trading Opportunities', 'tradepress'); ?></h3>
            <p class="strategy-description">
                <?php esc_html_e('Symbols with setups suitable for intraday trading (minutes to hours)', 'tradepress'); ?>
            </p>
        </div>
        
        <div class="symbols-grid">
            <?php foreach ($intradaySymbols as $symbol) : 
                $weighted_percentage = round(($symbol['score'] / $symbol['max_score']) * 100);
            ?>
                <div class="symbol-card <?php echo esc_attr(strtolower($symbol['recommendation'])); ?>">
                    <div class="symbol-header">
                        <div class="symbol-name-section">
                            <h3 class="symbol-ticker"><?php echo esc_html($symbol['symbol']); ?></h3>
                            <span class="symbol-full-name"><?php echo esc_html($symbol['name']); ?></span>
                        </div>
                        <div class="symbol-price-section">
                            <span class="symbol-price"><?php echo '$' . number_format($symbol['price'], 2); ?></span>
                            <span class="symbol-change <?php echo $symbol['change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $symbol['change'] >= 0 ? '+' : ''; echo number_format($symbol['change'], 2); ?> 
                                (<?php echo $symbol['change'] >= 0 ? '+' : ''; echo number_format($symbol['change_percent'], 2); ?>%)
                            </span>
                        </div>
                    </div>
                    
                    <div class="symbol-score">
                        <div class="score-circle" style="background: conic-gradient(#4CAF50 0% <?php echo $weighted_percentage; ?>%, #f5f5f5 <?php echo $weighted_percentage; ?>% 100%);">
                            <span class="score-value"><?php echo esc_html($symbol['score']); ?></span>
                        </div>
                        <div class="score-details">
                            <div class="score-ratio">
                                <span class="score-ratio-value"><?php echo esc_html($symbol['score']); ?> / <?php echo esc_html($symbol['max_score']); ?></span>
                                <span class="score-percentage">(<?php echo esc_html($weighted_percentage); ?>%)</span>
                            </div>
                            <div class="recommendation">
                                <span class="recommendation-label"><?php esc_html_e('Recommendation:', 'tradepress'); ?></span>
                                <span class="recommendation-value"><?php echo esc_html($symbol['recommendation']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="strategy-specific-advice">
                        <h4><?php esc_html_e('Intraday Trading Strategy', 'tradepress'); ?></h4>
                        <div class="advice-summary"><?php echo esc_html($symbol['intraday_advice']['summary']); ?></div>
                        <table class="advice-details">
                            <tr>
                                <th><?php esc_html_e('Recommended Timeframes:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['intraday_advice']['timeframes']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Key Levels:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['intraday_advice']['key_levels']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Volume Zones:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['intraday_advice']['volume_zones']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Chart Patterns:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['intraday_advice']['patterns']); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('Best Trading Hours:', 'tradepress'); ?></th>
                                <td><?php echo esc_html($symbol['intraday_advice']['best_hours']); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="symbol-indicators">
                        <h4><?php esc_html_e('Technical Indicators', 'tradepress'); ?></h4>
                        <table class="indicators-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Indicator', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Value', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Signal', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($symbol['indicators'] as $name => $indicator) : ?>
                                    <tr>
                                        <td class="indicator-name"><?php echo esc_html(ucfirst($name)); ?></td>
                                        <td class="indicator-value"><?php echo esc_html($indicator['value']); ?></td>
                                        <td class="indicator-signal signal-<?php echo esc_attr(str_replace(' ', '-', strtolower($indicator['signal']))); ?>">
                                            <?php echo esc_html(ucfirst($indicator['signal'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="symbol-footer">
                        <span class="last-updated">
                            <?php esc_html_e('Last updated:', 'tradepress'); ?> 
                            <?php echo esc_html($symbol['last_updated']); ?>
                        </span>
                        <a href="#" class="view-details-button"><?php esc_html_e('View Full Analysis', 'tradepress'); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
