<?php
/**
 * TradePress Sector Rotation Tab
 *
 * Displays sector rotation analysis and market sector performance information
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-06-16 14:30
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display the Sector Rotation tab content
 */
function tradepress_sector_rotation_tab_content() {
    // Get timeframe from URL parameter, default to 1 month
    $timeframe = isset($_GET['timeframe']) ? sanitize_text_field($_GET['timeframe']) : '1m';
    
    // Generate sector rotation data (would connect to real API in production)
    $sector_data = tradepress_get_demo_sector_data($timeframe);
    $rotation_phases = tradepress_get_rotation_phases();
    $current_phase = tradepress_get_current_rotation_phase();
    
    // Display the sector rotation interface
    ?>
    <div class="tradepress-sector-rotation-container">

        
        <div class="tradepress-research-section">
            <h2><?php esc_html_e('Sector Rotation Analysis', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Track sector performance and identify rotation patterns to inform investment decisions.', 'tradepress'); ?></p>
            
            <div class="tradepress-research-inputs">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="sector-rotation">
                    
                    <div class="tradepress-input-group">
                        <label for="timeframe"><?php esc_html_e('Timeframe:', 'tradepress'); ?></label>
                        <select id="timeframe" name="timeframe" onchange="this.form.submit()">
                            <option value="1w" <?php selected($timeframe, '1w'); ?>><?php esc_html_e('1 Week', 'tradepress'); ?></option>
                            <option value="1m" <?php selected($timeframe, '1m'); ?>><?php esc_html_e('1 Month', 'tradepress'); ?></option>
                            <option value="3m" <?php selected($timeframe, '3m'); ?>><?php esc_html_e('3 Months', 'tradepress'); ?></option>
                            <option value="6m" <?php selected($timeframe, '6m'); ?>><?php esc_html_e('6 Months', 'tradepress'); ?></option>
                            <option value="1y" <?php selected($timeframe, '1y'); ?>><?php esc_html_e('1 Year', 'tradepress'); ?></option>
                            <option value="ytd" <?php selected($timeframe, 'ytd'); ?>><?php esc_html_e('Year to Date', 'tradepress'); ?></option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="tradepress-research-results">
            <!-- Current Market Phase -->
            <div class="tradepress-market-phase-panel">
                <h3><?php esc_html_e('Current Market Cycle Phase', 'tradepress'); ?></h3>
                <div class="tradepress-cycle-indicator">
                    <div class="tradepress-cycle-diagram">
                        <?php 
                        // Render the market cycle diagram with current phase highlighted
                        foreach ($rotation_phases as $phase_id => $phase) {
                            $active_class = ($current_phase['id'] === $phase_id) ? ' active' : '';
                            echo '<div class="phase-segment phase-' . esc_attr($phase_id) . $active_class . '" title="' . esc_attr($phase['name']) . '"></div>';
                        }
                        ?>
                        <div class="cycle-center">
                            <span class="cycle-label"><?php esc_html_e('Market Cycle', 'tradepress'); ?></span>
                        </div>
                    </div>
                    <div class="tradepress-current-phase">
                        <h4><?php esc_html_e('Current Phase:', 'tradepress'); ?> <span class="phase-name"><?php echo esc_html($current_phase['name']); ?></span></h4>
                        <p class="phase-description"><?php echo esc_html($current_phase['description']); ?></p>
                        <div class="phase-indicators">
                            <div class="indicator">
                                <span class="indicator-label"><?php esc_html_e('Confidence:', 'tradepress'); ?></span>
                                <div class="indicator-bar">
                                    <div class="indicator-fill" style="width: <?php echo esc_attr($current_phase['confidence']); ?>%;"></div>
                                </div>
                                <span class="indicator-value"><?php echo esc_html($current_phase['confidence']); ?>%</span>
                            </div>
                            <div class="indicator">
                                <span class="indicator-label"><?php esc_html_e('Duration:', 'tradepress'); ?></span>
                                <span class="indicator-text"><?php echo esc_html($current_phase['duration']); ?> <?php esc_html_e('days', 'tradepress'); ?></span>
                            </div>
                            <div class="indicator">
                                <span class="indicator-label"><?php esc_html_e('Phase Start:', 'tradepress'); ?></span>
                                <span class="indicator-text"><?php echo esc_html($current_phase['start_date']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sector Performance Table -->
            <div class="tradepress-sector-performance">
                <h3><?php esc_html_e('Sector Performance', 'tradepress'); ?> (<?php echo esc_html(tradepress_get_timeframe_label($timeframe)); ?>)</h3>
                
                <div class="tradepress-sectors-grid">
                    <table class="tradepress-sectors-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Sector', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Performance', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Momentum', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Relative Strength', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Volume Trend', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Outlook', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sector_data as $sector) : ?>
                                <tr>
                                    <td class="sector-name">
                                        <strong><?php echo esc_html($sector['name']); ?></strong>
                                        <div class="sector-etf"><?php echo esc_html($sector['etf']); ?></div>
                                    </td>
                                    <td class="sector-performance">
                                        <div class="performance-value <?php echo ($sector['performance'] >= 0) ? 'positive' : 'negative'; ?>">
                                            <?php echo ($sector['performance'] >= 0) ? '+' : ''; ?><?php echo esc_html(number_format($sector['performance'], 2)); ?>%
                                        </div>
                                        <div class="performance-bar">
                                            <?php if ($sector['performance'] >= 0) : ?>
                                                <div class="performance-fill positive" style="width: <?php echo min(abs($sector['performance']) * 5, 100); ?>%;"></div>
                                            <?php else : ?>
                                                <div class="performance-fill negative" style="width: <?php echo min(abs($sector['performance']) * 5, 100); ?>%;"></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="sector-momentum">
                                        <div class="momentum-indicator <?php echo esc_attr($sector['momentum_class']); ?>">
                                            <span class="dashicons <?php echo esc_attr($sector['momentum_icon']); ?>"></span>
                                            <?php echo esc_html($sector['momentum']); ?>
                                        </div>
                                    </td>
                                    <td class="sector-relative-strength">
                                        <div class="strength-bar">
                                            <div class="strength-fill" style="width: <?php echo esc_attr($sector['relative_strength']); ?>%;"></div>
                                        </div>
                                        <div class="strength-value"><?php echo esc_html($sector['relative_strength']); ?></div>
                                    </td>
                                    <td class="sector-volume">
                                        <div class="volume-trend <?php echo esc_attr($sector['volume_class']); ?>">
                                            <span class="dashicons <?php echo esc_attr($sector['volume_icon']); ?>"></span>
                                            <?php echo esc_html($sector['volume']); ?>
                                        </div>
                                    </td>
                                    <td class="sector-outlook">
                                        <div class="outlook-indicator <?php echo esc_attr($sector['outlook_class']); ?>">
                                            <?php echo esc_html($sector['outlook']); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Industry Groups -->
            <div class="tradepress-industry-groups">
                <h3><?php esc_html_e('Leading Industry Groups', 'tradepress'); ?></h3>
                <div class="tradepress-industries-container">
                    <div class="tradepress-industry-column">
                        <h4><?php esc_html_e('Top Performing Industries', 'tradepress'); ?></h4>
                        <ul class="tradepress-industry-list">
                            <?php 
                            $top_industries = tradepress_get_demo_top_industries();
                            foreach ($top_industries as $industry) :
                            ?>
                                <li class="industry-item">
                                    <div class="industry-name"><?php echo esc_html($industry['name']); ?></div>
                                    <div class="industry-performance positive">+<?php echo esc_html(number_format($industry['performance'], 2)); ?>%</div>
                                    <div class="industry-sector"><?php echo esc_html($industry['sector']); ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tradepress-industry-column">
                        <h4><?php esc_html_e('Weakest Performing Industries', 'tradepress'); ?></h4>
                        <ul class="tradepress-industry-list">
                            <?php 
                            $bottom_industries = tradepress_get_demo_bottom_industries();
                            foreach ($bottom_industries as $industry) :
                            ?>
                                <li class="industry-item">
                                    <div class="industry-name"><?php echo esc_html($industry['name']); ?></div>
                                    <div class="industry-performance negative"><?php echo esc_html(number_format($industry['performance'], 2)); ?>%</div>
                                    <div class="industry-sector"><?php echo esc_html($industry['sector']); ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Rotation Phases Educational Section -->
            <div class="tradepress-educational-panel">
                <h3><?php esc_html_e('Understanding Sector Rotation', 'tradepress'); ?></h3>
                <div class="tradepress-rotation-cycle">
                    <div class="cycle-diagram">
                        <img src="<?php echo esc_url(TRADEPRESS_PLUGIN_URL . 'admin/images/sector-rotation-cycle.png'); ?>" alt="<?php esc_attr_e('Sector Rotation Cycle', 'tradepress'); ?>" />
                    </div>
                    <div class="cycle-description">
                        <p><?php esc_html_e('Sector rotation is the phenomenon where money flows from one industry sector to another as the economic cycle evolves. This pattern is based on the observation that different sectors tend to perform better during different phases of the economic cycle.', 'tradepress'); ?></p>
                        
                        <div class="rotation-phases">
                            <?php foreach ($rotation_phases as $phase_id => $phase) : ?>
                                <div class="rotation-phase">
                                    <h4><?php echo esc_html($phase['name']); ?></h4>
                                    <p><?php echo esc_html($phase['description']); ?></p>
                                    <div class="phase-sectors">
                                        <strong><?php esc_html_e('Sectors that typically outperform:', 'tradepress'); ?></strong>
                                        <span><?php echo esc_html(implode(', ', $phase['sectors'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get demo sector data for the specified timeframe
 *
 * @param string $timeframe The timeframe to get data for (1w, 1m, 3m, 6m, 1y, ytd)
 * @return array An array of sector data
 */
function tradepress_get_demo_sector_data($timeframe) {
    // In a real implementation, this would fetch data from an API or database
    // For demo purposes, we'll generate random data
    
    $sectors = array(
        array(
            'name' => 'Technology',
            'etf' => 'XLK',
            'performance' => ($timeframe === '1w') ? 2.3 : (($timeframe === '1m') ? 5.7 : (($timeframe === '3m') ? 12.4 : (($timeframe === '6m') ? 18.6 : (($timeframe === 'ytd') ? 15.2 : 24.8)))),
            'momentum' => 'Strong',
            'momentum_class' => 'strong',
            'momentum_icon' => 'dashicons-arrow-up-alt',
            'relative_strength' => 85,
            'volume' => 'Increasing',
            'volume_class' => 'increasing',
            'volume_icon' => 'dashicons-chart-line',
            'outlook' => 'Bullish',
            'outlook_class' => 'bullish'
        ),
        array(
            'name' => 'Healthcare',
            'etf' => 'XLV',
            'performance' => ($timeframe === '1w') ? 1.5 : (($timeframe === '1m') ? 3.2 : (($timeframe === '3m') ? 7.8 : (($timeframe === '6m') ? 10.5 : (($timeframe === 'ytd') ? 8.7 : 14.3)))),
            'momentum' => 'Neutral',
            'momentum_class' => 'neutral',
            'momentum_icon' => 'dashicons-minus',
            'relative_strength' => 62,
            'volume' => 'Stable',
            'volume_class' => 'stable',
            'volume_icon' => 'dashicons-controls-play',
            'outlook' => 'Neutral',
            'outlook_class' => 'neutral'
        ),
        array(
            'name' => 'Financials',
            'etf' => 'XLF',
            'performance' => ($timeframe === '1w') ? -0.8 : (($timeframe === '1m') ? -2.1 : (($timeframe === '3m') ? 4.5 : (($timeframe === '6m') ? 7.2 : (($timeframe === 'ytd') ? 5.3 : 12.7)))),
            'momentum' => 'Weak',
            'momentum_class' => 'weak',
            'momentum_icon' => 'dashicons-arrow-down-alt',
            'relative_strength' => 48,
            'volume' => 'Increasing',
            'volume_class' => 'increasing',
            'volume_icon' => 'dashicons-chart-line',
            'outlook' => 'Neutral',
            'outlook_class' => 'neutral'
        ),
        array(
            'name' => 'Consumer Discretionary',
            'etf' => 'XLY',
            'performance' => ($timeframe === '1w') ? 1.9 : (($timeframe === '1m') ? 4.3 : (($timeframe === '3m') ? 9.6 : (($timeframe === '6m') ? 14.8 : (($timeframe === 'ytd') ? 12.5 : 21.2)))),
            'momentum' => 'Strong',
            'momentum_class' => 'strong',
            'momentum_icon' => 'dashicons-arrow-up-alt',
            'relative_strength' => 78,
            'volume' => 'Stable',
            'volume_class' => 'stable',
            'volume_icon' => 'dashicons-controls-play',
            'outlook' => 'Bullish',
            'outlook_class' => 'bullish'
        ),
        array(
            'name' => 'Industrials',
            'etf' => 'XLI',
            'performance' => ($timeframe === '1w') ? 0.6 : (($timeframe === '1m') ? 1.8 : (($timeframe === '3m') ? 5.7 : (($timeframe === '6m') ? 9.2 : (($timeframe === 'ytd') ? 7.4 : 15.8)))),
            'momentum' => 'Neutral',
            'momentum_class' => 'neutral',
            'momentum_icon' => 'dashicons-minus',
            'relative_strength' => 56,
            'volume' => 'Stable',
            'volume_class' => 'stable',
            'volume_icon' => 'dashicons-controls-play',
            'outlook' => 'Neutral',
            'outlook_class' => 'neutral'
        ),
        array(
            'name' => 'Energy',
            'etf' => 'XLE',
            'performance' => ($timeframe === '1w') ? -1.4 : (($timeframe === '1m') ? -3.6 : (($timeframe === '3m') ? -7.2 : (($timeframe === '6m') ? -5.8 : (($timeframe === 'ytd') ? -4.3 : -2.9)))),
            'momentum' => 'Weak',
            'momentum_class' => 'weak',
            'momentum_icon' => 'dashicons-arrow-down-alt',
            'relative_strength' => 32,
            'volume' => 'Decreasing',
            'volume_class' => 'decreasing',
            'volume_icon' => 'dashicons-arrow-down',
            'outlook' => 'Bearish',
            'outlook_class' => 'bearish'
        ),
        array(
            'name' => 'Consumer Staples',
            'etf' => 'XLP',
            'performance' => ($timeframe === '1w') ? 0.3 : (($timeframe === '1m') ? 0.9 : (($timeframe === '3m') ? 2.4 : (($timeframe === '6m') ? 4.1 : (($timeframe === 'ytd') ? 3.2 : 7.5)))),
            'momentum' => 'Neutral',
            'momentum_class' => 'neutral',
            'momentum_icon' => 'dashicons-minus',
            'relative_strength' => 45,
            'volume' => 'Stable',
            'volume_class' => 'stable',
            'volume_icon' => 'dashicons-controls-play',
            'outlook' => 'Neutral',
            'outlook_class' => 'neutral'
        ),
        array(
            'name' => 'Utilities',
            'etf' => 'XLU',
            'performance' => ($timeframe === '1w') ? -0.5 : (($timeframe === '1m') ? -1.2 : (($timeframe === '3m') ? 1.8 : (($timeframe === '6m') ? 3.5 : (($timeframe === 'ytd') ? 2.7 : 6.4)))),
            'momentum' => 'Weak',
            'momentum_class' => 'weak',
            'momentum_icon' => 'dashicons-arrow-down-alt',
            'relative_strength' => 38,
            'volume' => 'Decreasing',
            'volume_class' => 'decreasing',
            'volume_icon' => 'dashicons-arrow-down',
            'outlook' => 'Bearish',
            'outlook_class' => 'bearish'
        ),
        array(
            'name' => 'Materials',
            'etf' => 'XLB',
            'performance' => ($timeframe === '1w') ? 0.8 : (($timeframe === '1m') ? 2.5 : (($timeframe === '3m') ? 6.3 : (($timeframe === '6m') ? 8.7 : (($timeframe === 'ytd') ? 6.8 : 11.2)))),
            'momentum' => 'Neutral',
            'momentum_class' => 'neutral',
            'momentum_icon' => 'dashicons-minus',
            'relative_strength' => 52,
            'volume' => 'Increasing',
            'volume_class' => 'increasing',
            'volume_icon' => 'dashicons-chart-line',
            'outlook' => 'Neutral',
            'outlook_class' => 'neutral'
        ),
        array(
            'name' => 'Real Estate',
            'etf' => 'XLRE',
            'performance' => ($timeframe === '1w') ? -0.2 : (($timeframe === '1m') ? 0.4 : (($timeframe === '3m') ? 3.2 : (($timeframe === '6m') ? 5.8 : (($timeframe === 'ytd') ? 4.5 : 9.3)))),
            'momentum' => 'Neutral',
            'momentum_class' => 'neutral',
            'momentum_icon' => 'dashicons-minus',
            'relative_strength' => 47,
            'volume' => 'Stable',
            'volume_class' => 'stable',
            'volume_icon' => 'dashicons-controls-play',
            'outlook' => 'Neutral',
            'outlook_class' => 'neutral'
        ),
        array(
            'name' => 'Communication Services',
            'etf' => 'XLC',
            'performance' => ($timeframe === '1w') ? 1.7 : (($timeframe === '1m') ? 3.9 : (($timeframe === '3m') ? 8.5 : (($timeframe === '6m') ? 12.3 : (($timeframe === 'ytd') ? 10.1 : 18.7)))),
            'momentum' => 'Strong',
            'momentum_class' => 'strong',
            'momentum_icon' => 'dashicons-arrow-up-alt',
            'relative_strength' => 72,
            'volume' => 'Increasing',
            'volume_class' => 'increasing',
            'volume_icon' => 'dashicons-chart-line',
            'outlook' => 'Bullish',
            'outlook_class' => 'bullish'
        )
    );
    
    // Sort sectors by performance (descending)
    usort($sectors, function($a, $b) {
        return $b['performance'] <=> $a['performance'];
    });
    
    return $sectors;
}

/**
 * Get the market cycle rotation phases
 *
 * @return array The rotation phases information
 */
function tradepress_get_rotation_phases() {
    return array(
        'early-expansion' => array(
            'name' => 'Early Expansion',
            'description' => 'The economy is recovering from recession. Interest rates are low and starting to rise.',
            'sectors' => array('Technology', 'Industrials', 'Basic Materials', 'Consumer Discretionary')
        ),
        'late-expansion' => array(
            'name' => 'Late Expansion',
            'description' => 'The economy is growing at a healthy pace. Inflation may be increasing and interest rates continue to rise.',
            'sectors' => array('Energy', 'Materials', 'Industrials', 'Financials')
        ),
        'early-recession' => array(
            'name' => 'Early Recession',
            'description' => 'Economic growth is slowing. Corporate profits may be declining and unemployment rising.',
            'sectors' => array('Healthcare', 'Consumer Staples', 'Utilities', 'Communication Services')
        ),
        'late-recession' => array(
            'name' => 'Late Recession',
            'description' => 'The economy is in full recession. Interest rates are falling and the yield curve may be inverted.',
            'sectors' => array('Consumer Staples', 'Utilities', 'Healthcare', 'Real Estate')
        )
    );
}

/**
 * Get the current rotation phase
 *
 * @return array The current market rotation phase
 */
function tradepress_get_current_rotation_phase() {
    // In a real implementation, this would analyze economic indicators and market data
    // For demo purposes, we'll return a static phase
    
    $phases = tradepress_get_rotation_phases();
    $current_phase = $phases['early-expansion'];
    
    return array(
        'id' => 'early-expansion',
        'name' => $current_phase['name'],
        'description' => $current_phase['description'],
        'confidence' => 72,
        'duration' => 47,
        'start_date' => date('M j, Y', strtotime('-47 days'))
    );
}

/**
 * Get the top performing industries
 *
 * @return array The top performing industries
 */
function tradepress_get_demo_top_industries() {
    return array(
        array(
            'name' => 'Semiconductors',
            'performance' => 18.4,
            'sector' => 'Technology'
        ),
        array(
            'name' => 'Internet Software & Services',
            'performance' => 16.7,
            'sector' => 'Technology'
        ),
        array(
            'name' => 'Interactive Media',
            'performance' => 15.2,
            'sector' => 'Communication Services'
        ),
        array(
            'name' => 'Specialty Retail',
            'performance' => 13.9,
            'sector' => 'Consumer Discretionary'
        ),
        array(
            'name' => 'IT Services',
            'performance' => 12.5,
            'sector' => 'Technology'
        ),
        array(
            'name' => 'Biotechnology',
            'performance' => 11.8,
            'sector' => 'Healthcare'
        ),
        array(
            'name' => 'Aerospace & Defense',
            'performance' => 10.3,
            'sector' => 'Industrials'
        )
    );
}

/**
 * Get the worst performing industries
 *
 * @return array The worst performing industries
 */
function tradepress_get_demo_bottom_industries() {
    return array(
        array(
            'name' => 'Oil & Gas Exploration',
            'performance' => -12.6,
            'sector' => 'Energy'
        ),
        array(
            'name' => 'Oil & Gas Equipment',
            'performance' => -10.8,
            'sector' => 'Energy'
        ),
        array(
            'name' => 'Coal & Consumable Fuels',
            'performance' => -9.2,
            'sector' => 'Energy'
        ),
        array(
            'name' => 'Multi-Utilities',
            'performance' => -7.5,
            'sector' => 'Utilities'
        ),
        array(
            'name' => 'Banks',
            'performance' => -6.3,
            'sector' => 'Financials'
        ),
        array(
            'name' => 'Gas Utilities',
            'performance' => -5.7,
            'sector' => 'Utilities'
        ),
        array(
            'name' => 'Insurance',
            'performance' => -4.9,
            'sector' => 'Financials'
        )
    );
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
        'ytd' => 'Year to Date'
    );
    
    return isset($labels[$timeframe]) ? $labels[$timeframe] : $timeframe;
}
