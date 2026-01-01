<?php
/**
 * TradePress Market Correlations Tab
 *
 * Displays correlation analysis between securities and markets
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-06-19 15:30
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display the Market Correlations tab content
 */
function tradepress_market_correlations_tab_content() {
    // Get parameters from URL with defaults
    $base_symbol = isset($_GET['base_symbol']) ? sanitize_text_field($_GET['base_symbol']) : 'SPY';
    $compare_symbols = isset($_GET['compare_symbols']) ? sanitize_text_field($_GET['compare_symbols']) : 'QQQ,DIA,IWM,GLD,TLT';
    $timeframe = isset($_GET['timeframe']) ? sanitize_text_field($_GET['timeframe']) : '3m';
    
    // Convert compare symbols to array
    $compare_array = explode(',', $compare_symbols);
    
    // Demo data - in production, this would be calculated from real API data
    $correlation_data = tradepress_generate_demo_correlations($base_symbol, $compare_array, $timeframe);
    
    ?>
    <div class="tradepress-correlations-container">

        
        <div class="tradepress-research-section">
            <h2><?php esc_html_e('Market Correlations', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Analyze the relationships between different securities and markets to identify diversification opportunities and trading insights.', 'tradepress'); ?></p>
            
            <div class="tradepress-research-inputs">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="market-correlations">
                    
                    <div class="tradepress-input-row">
                        <div class="tradepress-input-group">
                            <label for="base_symbol"><?php esc_html_e('Base Symbol:', 'tradepress'); ?></label>
                            <input type="text" id="base_symbol" name="base_symbol" value="<?php echo esc_attr($base_symbol); ?>" placeholder="SPY">
                        </div>
                        
                        <div class="tradepress-input-group">
                            <label for="compare_symbols"><?php esc_html_e('Compare Symbols (comma separated):', 'tradepress'); ?></label>
                            <input type="text" id="compare_symbols" name="compare_symbols" value="<?php echo esc_attr($compare_symbols); ?>" placeholder="QQQ,DIA,IWM,GLD">
                        </div>
                        
                        <div class="tradepress-input-group">
                            <label for="timeframe"><?php esc_html_e('Timeframe:', 'tradepress'); ?></label>
                            <select id="timeframe" name="timeframe">
                                <option value="1m" <?php selected($timeframe, '1m'); ?>><?php esc_html_e('1 Month', 'tradepress'); ?></option>
                                <option value="3m" <?php selected($timeframe, '3m'); ?>><?php esc_html_e('3 Months', 'tradepress'); ?></option>
                                <option value="6m" <?php selected($timeframe, '6m'); ?>><?php esc_html_e('6 Months', 'tradepress'); ?></option>
                                <option value="1y" <?php selected($timeframe, '1y'); ?>><?php esc_html_e('1 Year', 'tradepress'); ?></option>
                                <option value="2y" <?php selected($timeframe, '2y'); ?>><?php esc_html_e('2 Years', 'tradepress'); ?></option>
                            </select>
                        </div>
                        
                        <button type="submit" class="button button-primary"><?php esc_html_e('Calculate Correlations', 'tradepress'); ?></button>
                    </div>
                    
                    <!-- Quick presets -->
                    <div class="correlation-presets">
                        <span class="preset-label"><?php esc_html_e('Presets:', 'tradepress'); ?></span>
                        <a href="<?php echo esc_url(add_query_arg(array('base_symbol' => 'SPY', 'compare_symbols' => 'QQQ,DIA,IWM,GLD,TLT'))); ?>" class="preset-link"><?php esc_html_e('Major ETFs', 'tradepress'); ?></a>
                        <a href="<?php echo esc_url(add_query_arg(array('base_symbol' => 'SPY', 'compare_symbols' => 'XLK,XLF,XLV,XLE,XLI,XLY,XLP,XLU,XLB,XLRE'))); ?>" class="preset-link"><?php esc_html_e('Sectors', 'tradepress'); ?></a>
                        <a href="<?php echo esc_url(add_query_arg(array('base_symbol' => 'SPY', 'compare_symbols' => 'EEM,EFA,FXI,EWJ,EWG,EWU'))); ?>" class="preset-link"><?php esc_html_e('International', 'tradepress'); ?></a>
                        <a href="<?php echo esc_url(add_query_arg(array('base_symbol' => 'SPY', 'compare_symbols' => 'GLD,SLV,USO,UNG,DBC'))); ?>" class="preset-link"><?php esc_html_e('Commodities', 'tradepress'); ?></a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="tradepress-research-results">
            <div class="correlation-results">
                <h3><?php echo sprintf(esc_html__('Correlation Results: %s vs. Others (%s)', 'tradepress'), $base_symbol, tradepress_get_timeframe_label($timeframe)); ?></h3>
                
                <div class="correlation-heatmap">
                    <?php foreach ($correlation_data as $symbol => $data): ?>
                        <div class="correlation-row">
                            <div class="symbol-cell"><?php echo esc_html($symbol); ?></div>
                            <div class="correlation-bar-cell">
                                <?php 
                                $corr_value = $data['correlation'];
                                $corr_width = min(abs($corr_value * 100), 100);
                                $corr_class = $corr_value >= 0 ? 'positive' : 'negative';
                                ?>
                                <div class="correlation-bar <?php echo $corr_class; ?>" style="width: <?php echo $corr_width; ?>%;">
                                    <span class="correlation-value"><?php echo number_format($corr_value, 2); ?></span>
                                </div>
                            </div>
                            <div class="performance-cell">
                                <span class="performance <?php echo $data['performance'] >= 0 ? 'up' : 'down'; ?>">
                                    <?php echo $data['performance'] >= 0 ? '+' : ''; ?><?php echo number_format($data['performance'], 2); ?>%
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="correlation-interpretation">
                    <h4><?php esc_html_e('Correlation Interpretation', 'tradepress'); ?></h4>
                    <div class="interpretation-scale">
                        <div class="scale-label negative">-1.0</div>
                        <div class="scale-bar">
                            <div class="scale-segment strong-negative"></div>
                            <div class="scale-segment moderate-negative"></div>
                            <div class="scale-segment weak-negative"></div>
                            <div class="scale-segment neutral"></div>
                            <div class="scale-segment weak-positive"></div>
                            <div class="scale-segment moderate-positive"></div>
                            <div class="scale-segment strong-positive"></div>
                        </div>
                        <div class="scale-label positive">+1.0</div>
                    </div>
                    <div class="interpretation-legend">
                        <div class="legend-item">
                            <span class="legend-color strong-negative"></span>
                            <span class="legend-text"><?php esc_html_e('Strong Negative (-1.0 to -0.7)', 'tradepress'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color moderate-negative"></span>
                            <span class="legend-text"><?php esc_html_e('Moderate Negative (-0.7 to -0.3)', 'tradepress'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color weak-negative"></span>
                            <span class="legend-text"><?php esc_html_e('Weak Negative (-0.3 to -0.1)', 'tradepress'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color neutral"></span>
                            <span class="legend-text"><?php esc_html_e('No Correlation (-0.1 to +0.1)', 'tradepress'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color weak-positive"></span>
                            <span class="legend-text"><?php esc_html_e('Weak Positive (+0.1 to +0.3)', 'tradepress'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color moderate-positive"></span>
                            <span class="legend-text"><?php esc_html_e('Moderate Positive (+0.3 to +0.7)', 'tradepress'); ?></span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color strong-positive"></span>
                            <span class="legend-text"><?php esc_html_e('Strong Positive (+0.7 to +1.0)', 'tradepress'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="correlation-insights">
                    <h4><?php esc_html_e('Trading Insights', 'tradepress'); ?></h4>
                    <div class="insights-box">
                        <?php
                        // Generate some basic insights based on the correlation data
                        $high_corr = array();
                        $low_corr = array();
                        $negative_corr = array();
                        
                        foreach ($correlation_data as $symbol => $data) {
                            $corr = $data['correlation'];
                            if ($corr >= 0.8) {
                                $high_corr[] = $symbol;
                            } elseif (abs($corr) <= 0.3) {
                                $low_corr[] = $symbol;
                            } elseif ($corr <= -0.5) {
                                $negative_corr[] = $symbol;
                            }
                        }
                        ?>
                        
                        <div class="insight-section">
                            <h5><?php esc_html_e('Diversification Opportunities', 'tradepress'); ?></h5>
                            <?php if (!empty($low_corr)): ?>
                                <p><?php esc_html_e('Consider these low correlation assets for portfolio diversification:', 'tradepress'); ?></p>
                                <ul class="insight-list">
                                    <?php foreach ($low_corr as $symbol): ?>
                                        <li><?php echo esc_html($symbol); ?> (<?php echo number_format($correlation_data[$symbol]['correlation'], 2); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php esc_html_e('No significant diversification opportunities found in the selected symbols.', 'tradepress'); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="insight-section">
                            <h5><?php esc_html_e('Hedging Opportunities', 'tradepress'); ?></h5>
                            <?php if (!empty($negative_corr)): ?>
                                <p><?php esc_html_e('These assets show negative correlation and may serve as potential hedges:', 'tradepress'); ?></p>
                                <ul class="insight-list">
                                    <?php foreach ($negative_corr as $symbol): ?>
                                        <li><?php echo esc_html($symbol); ?> (<?php echo number_format($correlation_data[$symbol]['correlation'], 2); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php esc_html_e('No significant hedging opportunities found in the selected symbols.', 'tradepress'); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="insight-section">
                            <h5><?php esc_html_e('Pair Trading Opportunities', 'tradepress'); ?></h5>
                            <?php if (!empty($high_corr)): ?>
                                <p><?php esc_html_e('These highly correlated assets may present pair trading opportunities:', 'tradepress'); ?></p>
                                <ul class="insight-list">
                                    <?php foreach ($high_corr as $symbol): ?>
                                        <li><?php echo esc_html($symbol); ?> (<?php echo number_format($correlation_data[$symbol]['correlation'], 2); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p><?php esc_html_e('No significant pair trading opportunities found in the selected symbols.', 'tradepress'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="correlation-education">
                <h3><?php esc_html_e('Using Correlations in Trading', 'tradepress'); ?></h3>
                
                <div class="tradepress-accordion">
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('What is Correlation?', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Correlation is a statistical measure that expresses the extent to which two variables move in relation to each other. Correlation values range from -1 to +1, where:', 'tradepress'); ?></p>
                            <ul>
                                <li><?php esc_html_e('+1 indicates perfect positive correlation (assets move in the same direction)', 'tradepress'); ?></li>
                                <li><?php esc_html_e('0 indicates no correlation (assets move independently)', 'tradepress'); ?></li>
                                <li><?php esc_html_e('-1 indicates perfect negative correlation (assets move in opposite directions)', 'tradepress'); ?></li>
                            </ul>
                            <p><?php esc_html_e('Correlations are calculated using historical price data over a specific period. They can change over time and during different market regimes.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Portfolio Diversification', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('One of the primary applications of correlation analysis is portfolio diversification. By including assets with low or negative correlations to each other, investors can potentially reduce portfolio volatility without necessarily sacrificing returns.', 'tradepress'); ?></p>
                            <p><?php esc_html_e('Effective diversification strategies include:', 'tradepress'); ?></p>
                            <ul>
                                <li><?php esc_html_e('Mixing asset classes (stocks, bonds, commodities)', 'tradepress'); ?></li>
                                <li><?php esc_html_e('Geographic diversification (domestic and international markets)', 'tradepress'); ?></li>
                                <li><?php esc_html_e('Sector diversification within equities', 'tradepress'); ?></li>
                                <li><?php esc_html_e('Including alternative investments with different return drivers', 'tradepress'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Hedging Strategies', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Assets with negative correlation can be used as hedges to protect against adverse price movements in the main position. For example:', 'tradepress'); ?></p>
                            <ul>
                                <li><?php esc_html_e('Using bonds (TLT) to hedge against stock market (SPY) declines', 'tradepress'); ?></li>
                                <li><?php esc_html_e('Using gold (GLD) to hedge against currency devaluation or inflation', 'tradepress'); ?></li>
                                <li><?php esc_html_e('Using defensive sectors (utilities, consumer staples) to hedge against economic downturns', 'tradepress'); ?></li>
                            </ul>
                            <p><?php esc_html_e('Effective hedging requires understanding not just the correlation, but also the volatility of both assets to properly size the hedge.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Pairs Trading', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Pairs trading is a market-neutral strategy that involves taking opposing positions in two highly correlated securities when their price relationship temporarily diverges from historical norms.', 'tradepress'); ?></p>
                            <p><?php esc_html_e('The strategy works as follows:', 'tradepress'); ?></p>
                            <ol>
                                <li><?php esc_html_e('Identify two assets with historically high correlation', 'tradepress'); ?></li>
                                <li><?php esc_html_e('Monitor the spread between these assets', 'tradepress'); ?></li>
                                <li><?php esc_html_e('When the spread widens significantly (statistically abnormal)', 'tradepress'); ?>:
                                    <ul>
                                        <li><?php esc_html_e('Short the outperforming asset', 'tradepress'); ?></li>
                                        <li><?php esc_html_e('Buy the underperforming asset', 'tradepress'); ?></li>
                                    </ul>
                                </li>
                                <li><?php esc_html_e('When the spread returns to normal, close both positions for a profit', 'tradepress'); ?></li>
                            </ol>
                            <p><?php esc_html_e('This strategy aims to profit from the relationship between assets rather than from directional market movements.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Limitations of Correlation Analysis', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('While correlation analysis is a powerful tool, it has several limitations:', 'tradepress'); ?></p>
                            <ul>
                                <li><strong><?php esc_html_e('Historical Nature:', 'tradepress'); ?></strong> <?php esc_html_e('Correlations are calculated from historical data and may not predict future relationships.', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Instability:', 'tradepress'); ?></strong> <?php esc_html_e('Correlations can change rapidly during market stress, often when diversification is most needed.', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Non-linear Relationships:', 'tradepress'); ?></strong> <?php esc_html_e('Correlation only measures linear relationships between assets.', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Timeframe Sensitivity:', 'tradepress'); ?></strong> <?php esc_html_e('Correlations can vary significantly depending on the timeframe examined.', 'tradepress'); ?></li>
                            </ul>
                            <p><?php esc_html_e('Because of these limitations, correlation analysis should be used alongside other analytical tools and risk management techniques.', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
}

/**
 * Generate demo correlation data for display
 *
 * @param string $base_symbol The base symbol for correlation calculations
 * @param array $compare_symbols Array of symbols to compare against the base
 * @param string $timeframe Timeframe for correlation calculation
 * @return array Correlation data for display
 */
function tradepress_generate_demo_correlations($base_symbol, $compare_symbols, $timeframe) {
    $correlation_data = array();
    
    // Generate correlation values that approximate real-world relationships
    $correlation_patterns = array(
        // Strong positive correlations
        'SPY' => array('QQQ' => 0.85, 'DIA' => 0.92, 'IWM' => 0.88, 'XLK' => 0.82, 'XLF' => 0.78, 'EFA' => 0.75),
        'QQQ' => array('SPY' => 0.85, 'XLK' => 0.95, 'ARKK' => 0.82, 'VGT' => 0.90),
        
        // Moderate positive correlations
        'SPY' => array('XLV' => 0.65, 'XLI' => 0.72, 'XLY' => 0.75, 'XLP' => 0.55, 'XLRE' => 0.60),
        
        // Low or negative correlations
        'SPY' => array('GLD' => -0.35, 'TLT' => -0.65, 'VXX' => -0.85),
        'QQQ' => array('GLD' => -0.30, 'TLT' => -0.60, 'VXX' => -0.80),
        
        // Some common ETF relationships
        'XLE' => array('USO' => 0.82, 'XOP' => 0.95),
        'GLD' => array('SLV' => 0.85, 'GDX' => 0.75, 'TLT' => 0.45),
        'TLT' => array('IEF' => 0.90, 'VXX' => 0.55),
    );
    
    // For each comparison symbol, generate the correlation
    foreach ($compare_symbols as $symbol) {
        if ($symbol === $base_symbol) {
            continue; // Skip the base symbol itself
        }
        
        // Try to get a realistic correlation value
        $correlation = 0;
        
        if (isset($correlation_patterns[$base_symbol][$symbol])) {
            $correlation = $correlation_patterns[$base_symbol][$symbol];
        } elseif (isset($correlation_patterns[$symbol][$base_symbol])) {
            $correlation = $correlation_patterns[$symbol][$base_symbol];
        } else {
            // Generate a random but plausible correlation if no preset exists
            $correlation = mt_rand(-70, 70) / 100;
            
            // Make positive correlations more common for equity ETFs
            if (in_array($symbol, array('SPY', 'QQQ', 'DIA', 'IWM', 'XLK', 'XLF', 'XLV', 'XLI'))) {
                $correlation = abs($correlation);
            }
            
            // Make negative correlations more common for certain pairs
            if (($base_symbol === 'SPY' && in_array($symbol, array('SH', 'VXX', 'UVXY', 'TLT'))) ||
                ($symbol === 'SPY' && in_array($base_symbol, array('SH', 'VXX', 'UVXY', 'TLT')))) {
                $correlation = -abs($correlation);
            }
        }
        
        // Add some timeframe-based randomization to make it more realistic
        $variation = mt_rand(-10, 10) / 100;
        $correlation = max(-1, min(1, $correlation + $variation));
        
        // Generate performance data
        if ($correlation > 0.7) {
            // Highly correlated, similar performance
            $performance = mt_rand(-10, 20) / 10;
        } elseif ($correlation < -0.7) {
            // Highly negatively correlated, opposite performance
            $performance = mt_rand(-20, 10) / 10;
        } else {
            // Less correlated, more random performance
            $performance = mt_rand(-15, 15) / 10;
        }
        
        $correlation_data[$symbol] = array(
            'correlation' => $correlation,
            'performance' => $performance
        );
    }
    
    // Sort by correlation value (descending)
    uasort($correlation_data, function($a, $b) {
        return $b['correlation'] <=> $a['correlation'];
    });
    
    return $correlation_data;
}

/**
 * Get a human-readable label for the timeframe
 *
 * @param string $timeframe The timeframe code
 * @return string The timeframe label
 */
function tradepress_get_timeframe_label($timeframe) {
    $labels = array(
        '1w' => 'Past Week',
        '1m' => 'Past Month',
        '3m' => 'Past 3 Months',
        '6m' => 'Past 6 Months',
        '1y' => 'Past Year',
        '2y' => 'Past 2 Years',
        'ytd' => 'Year to Date'
    );
    
    return isset($labels[$timeframe]) ? $labels[$timeframe] : $timeframe;
}
?>
