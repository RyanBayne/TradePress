<?php
/**
 * TradePress Analysis - Support & Resistance tab
 *
 * @package TradePress/Admin/Analysis
 * @version 1.0.0
 * @created 2024-04-26 20:30:00
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
$tab_id = 'support_resistance';
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
    echo '<p>' . esc_html__('This tab is running in demo mode with sample data. In live mode, this will identify actual support and resistance levels for your symbols.', 'tradepress') . '</p>';
    echo '</div>';
    echo '</div>';
}

// Include the finder classes
if (!class_exists('ResistanceZoneFinder')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/resistance-zones.php';
}

if (!class_exists('SupportZoneFinder')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/support-zones.php';
}

// Process form submission if symbol is provided
$symbol = isset($_POST['symbol']) ? sanitize_text_field($_POST['symbol']) : '';
$levels_type = isset($_POST['levels_type']) ? sanitize_text_field($_POST['levels_type']) : 'both';
$results = [];
$has_results = false;

if (!empty($symbol)) {
    $has_results = true;
    
    // In demo mode, use sample data
    if ($tab_mode['mode'] === 'demo') {
        $results = get_demo_level_results($symbol, $levels_type);
    } else {
        // In live mode, use the actual finder classes
        // Create API service instance (placeholder - would need actual implementation)
        $api_service = new TradePress_Financial_API_Service();
        
        // Process based on selected level type
        if ($levels_type === 'resistance' || $levels_type === 'both') {
            $resistance_finder = new ResistanceZoneFinder($symbol, $api_service);
            $resistance_results = $resistance_finder->find_resistance_zones();
            $results['resistance'] = $resistance_results;
        }
        
        if ($levels_type === 'support' || $levels_type === 'both') {
            $support_finder = new SupportZoneFinder($symbol, $api_service);
            $support_results = $support_finder->find_support_zones();
            $results['support'] = $support_results;
        }
    }
}

/**
 * Get demo results for support and resistance levels
 *
 * @param string $symbol The symbol to analyze
 * @param string $levels_type Type of levels to return (resistance, support, or both)
 * @return array Sample results
 */
function get_demo_level_results($symbol, $levels_type) {
    $current_price = 0;
    
    // Set different price points based on symbol for demo variety
    switch (strtoupper($symbol)) {
        case 'AAPL':
            $current_price = 186.34;
            break;
        case 'MSFT':
            $current_price = 334.57;
            break;
        case 'NVDA':
            $current_price = 925.73;
            break;
        case 'TSLA':
            $current_price = 184.47;
            break;
        case 'AMZN':
            $current_price = 178.12;
            break;
        case 'GOOGL':
            $current_price = 167.83;
            break;
        default:
            $current_price = 100.00 + (rand(0, 50000) / 100);
    }
    
    $results = [];
    
    // Add resistance levels if requested
    if ($levels_type === 'resistance' || $levels_type === 'both') {
        $resistance_base = $current_price * 1.05; // Base resistance 5% above current price
        
        $results['resistance'] = [
            'symbol' => strtoupper($symbol),
            'current_price' => $current_price,
            'highly_overlapped' => [
                [
                    'min_price' => round($resistance_base * 1.02, 2),
                    'max_price' => round($resistance_base * 1.03, 2),
                    'method_count' => 4,
                    'methods' => ['swing_highs', 'fibonacci', 'pivot_points', 'psychological']
                ],
                [
                    'min_price' => round($resistance_base * 1.10, 2),
                    'max_price' => round($resistance_base * 1.12, 2),
                    'method_count' => 3,
                    'methods' => ['swing_highs', 'trendline_peaks', 'psychological']
                ]
            ],
            'well_overlapped' => [
                [
                    'min_price' => round($resistance_base * 1.06, 2),
                    'max_price' => round($resistance_base * 1.07, 2),
                    'method_count' => 2,
                    'methods' => ['fibonacci', 'moving_averages']
                ],
                [
                    'min_price' => round($resistance_base * 1.15, 2),
                    'max_price' => round($resistance_base * 1.16, 2),
                    'method_count' => 2,
                    'methods' => ['pivot_points', 'psychological']
                ]
            ],
            'all_levels' => [
                'swing_highs' => [round($resistance_base * 1.02, 2), round($resistance_base * 1.11, 2)],
                'trendline_peaks' => [round($resistance_base * 1.11, 2)],
                'fibonacci' => [round($resistance_base * 1.02, 2), round($resistance_base * 1.06, 2)],
                'pivot_points' => [round($resistance_base * 1.03, 2), round($resistance_base * 1.15, 2)],
                'psychological' => [round($resistance_base * 1.03, 2), round($resistance_base * 1.10, 2), round($resistance_base * 1.16, 2)],
                'moving_averages' => [round($resistance_base * 1.07, 2)]
            ]
        ];
    }
    
    // Add support levels if requested
    if ($levels_type === 'support' || $levels_type === 'both') {
        $support_base = $current_price * 0.95; // Base support 5% below current price
        
        $results['support'] = [
            'symbol' => strtoupper($symbol),
            'current_price' => $current_price,
            'highly_overlapped' => [
                [
                    'min_price' => round($support_base * 0.97, 2),
                    'max_price' => round($support_base * 0.98, 2),
                    'method_count' => 4,
                    'methods' => ['swing_lows', 'fibonacci', 'pivot_points', 'psychological']
                ],
                [
                    'min_price' => round($support_base * 0.90, 2),
                    'max_price' => round($support_base * 0.91, 2),
                    'method_count' => 3,
                    'methods' => ['swing_lows', 'trendline_bottoms', 'psychological']
                ]
            ],
            'well_overlapped' => [
                [
                    'min_price' => round($support_base * 0.94, 2),
                    'max_price' => round($support_base * 0.95, 2),
                    'method_count' => 2,
                    'methods' => ['fibonacci', 'moving_averages']
                ],
                [
                    'min_price' => round($support_base * 0.85, 2),
                    'max_price' => round($support_base * 0.86, 2),
                    'method_count' => 2,
                    'methods' => ['pivot_points', 'psychological']
                ]
            ],
            'all_levels' => [
                'swing_lows' => [round($support_base * 0.98, 2), round($support_base * 0.90, 2)],
                'trendline_bottoms' => [round($support_base * 0.91, 2)],
                'fibonacci' => [round($support_base * 0.97, 2), round($support_base * 0.94, 2)],
                'pivot_points' => [round($support_base * 0.98, 2), round($support_base * 0.85, 2)],
                'psychological' => [round($support_base * 0.97, 2), round($support_base * 0.90, 2), round($support_base * 0.86, 2)],
                'moving_averages' => [round($support_base * 0.95, 2)]
            ]
        ];
    }
    
    return $results;
}

?>

<div class="support-resistance-container">    
    <div class="support-resistance-form-container">
        <form method="post" action="" class="support-resistance-form">
            <div class="form-row">
                <label for="symbol"><?php esc_html_e('Symbol:', 'tradepress'); ?></label>
                <input type="text" id="symbol" name="symbol" value="<?php echo esc_attr($symbol); ?>" placeholder="AAPL, MSFT, NVDA..." required>
            </div>
            <div class="form-row">
                <label for="levels_type"><?php esc_html_e('Levels to Find:', 'tradepress'); ?></label>
                <select id="levels_type" name="levels_type">
                    <option value="both" <?php selected($levels_type, 'both'); ?>><?php esc_html_e('Both Support & Resistance', 'tradepress'); ?></option>
                    <option value="resistance" <?php selected($levels_type, 'resistance'); ?>><?php esc_html_e('Resistance Only', 'tradepress'); ?></option>
                    <option value="support" <?php selected($levels_type, 'support'); ?>><?php esc_html_e('Support Only', 'tradepress'); ?></option>
                </select>
            </div>
            <div class="form-row">
                <button type="submit" class="button button-primary"><?php esc_html_e('Find Levels', 'tradepress'); ?></button>
            </div>
        </form>
    </div>
    
    <?php if ($has_results): ?>
        <div class="support-resistance-results">
            <h3><?php echo esc_html(sprintf(__('Results for %s', 'tradepress'), strtoupper($symbol))); ?></h3>
            
            <?php if (isset($results['resistance'])): ?>
                <div class="resistance-results">
                    <h4><?php esc_html_e('Resistance Levels', 'tradepress'); ?></h4>
                    
                    <div class="current-price">
                        <strong><?php esc_html_e('Current Price:', 'tradepress'); ?></strong> 
                        $<?php echo number_format($results['resistance']['current_price'], 2); ?>
                    </div>
                    
                    <?php if (!empty($results['resistance']['highly_overlapped'])): ?>
                        <div class="levels-section">
                            <h5><?php esc_html_e('Strong Resistance Zones', 'tradepress'); ?></h5>
                            <p class="description"><?php esc_html_e('These zones have significant agreement across multiple technical methods.', 'tradepress'); ?></p>
                            
                            <div class="levels-grid">
                                <?php foreach ($results['resistance']['highly_overlapped'] as $zone): ?>
                                    <div class="level-card strong-zone">
                                        <div class="price-range">
                                            <span class="zone-label"><?php esc_html_e('Zone:', 'tradepress'); ?></span>
                                            <span class="price-values">$<?php echo number_format($zone['min_price'], 2); ?> - $<?php echo number_format($zone['max_price'], 2); ?></span>
                                        </div>
                                        <div class="confirmation">
                                            <span class="confirmation-label"><?php esc_html_e('Confirmed by:', 'tradepress'); ?></span>
                                            <span class="confirmation-count"><?php echo count($zone['methods']); ?> methods</span>
                                        </div>
                                        <div class="zone-methods">
                                            <?php foreach ($zone['methods'] as $method): ?>
                                                <span class="method-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $method))); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($results['resistance']['well_overlapped'])): ?>
                        <div class="levels-section">
                            <h5><?php esc_html_e('Moderate Resistance Zones', 'tradepress'); ?></h5>
                            <p class="description"><?php esc_html_e('These zones have some agreement between different technical methods.', 'tradepress'); ?></p>
                            
                            <div class="levels-grid">
                                <?php foreach ($results['resistance']['well_overlapped'] as $zone): ?>
                                    <div class="level-card moderate-zone">
                                        <div class="price-range">
                                            <span class="zone-label"><?php esc_html_e('Zone:', 'tradepress'); ?></span>
                                            <span class="price-values">$<?php echo number_format($zone['min_price'], 2); ?> - $<?php echo number_format($zone['max_price'], 2); ?></span>
                                        </div>
                                        <div class="confirmation">
                                            <span class="confirmation-label"><?php esc_html_e('Confirmed by:', 'tradepress'); ?></span>
                                            <span class="confirmation-count"><?php echo count($zone['methods']); ?> methods</span>
                                        </div>
                                        <div class="zone-methods">
                                            <?php foreach ($zone['methods'] as $method): ?>
                                                <span class="method-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $method))); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($results['resistance']['all_levels'])): ?>
                        <div class="levels-section levels-detail">
                            <button type="button" class="button toggle-details"><?php esc_html_e('Show All Individual Levels', 'tradepress'); ?></button>
                            <div class="detailed-levels" style="display: none;">
                                <h5><?php esc_html_e('All Individual Resistance Levels', 'tradepress'); ?></h5>
                                <table class="levels-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Method', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Price Levels', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['resistance']['all_levels'] as $method => $levels): ?>
                                            <tr>
                                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $method))); ?></td>
                                                <td>
                                                    <?php 
                                                    $formatted_levels = array_map(function($level) {
                                                        return '$' . number_format($level, 2);
                                                    }, $levels);
                                                    echo implode(', ', $formatted_levels);
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($results['support'])): ?>
                <div class="support-results">
                    <h4><?php esc_html_e('Support Levels', 'tradepress'); ?></h4>
                    
                    <div class="current-price">
                        <strong><?php esc_html_e('Current Price:', 'tradepress'); ?></strong> 
                        $<?php echo number_format($results['support']['current_price'], 2); ?>
                    </div>
                    
                    <?php if (!empty($results['support']['highly_overlapped'])): ?>
                        <div class="levels-section">
                            <h5><?php esc_html_e('Strong Support Zones', 'tradepress'); ?></h5>
                            <p class="description"><?php esc_html_e('These zones have significant agreement across multiple technical methods.', 'tradepress'); ?></p>
                            
                            <div class="levels-grid">
                                <?php foreach ($results['support']['highly_overlapped'] as $zone): ?>
                                    <div class="level-card strong-zone support-zone">
                                        <div class="price-range">
                                            <span class="zone-label"><?php esc_html_e('Zone:', 'tradepress'); ?></span>
                                            <span class="price-values">$<?php echo number_format($zone['min_price'], 2); ?> - $<?php echo number_format($zone['max_price'], 2); ?></span>
                                        </div>
                                        <div class="confirmation">
                                            <span class="confirmation-label"><?php esc_html_e('Confirmed by:', 'tradepress'); ?></span>
                                            <span class="confirmation-count"><?php echo count($zone['methods']); ?> methods</span>
                                        </div>
                                        <div class="zone-methods">
                                            <?php foreach ($zone['methods'] as $method): ?>
                                                <span class="method-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $method))); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($results['support']['well_overlapped'])): ?>
                        <div class="levels-section">
                            <h5><?php esc_html_e('Moderate Support Zones', 'tradepress'); ?></h5>
                            <p class="description"><?php esc_html_e('These zones have some agreement between different technical methods.', 'tradepress'); ?></p>
                            
                            <div class="levels-grid">
                                <?php foreach ($results['support']['well_overlapped'] as $zone): ?>
                                    <div class="level-card moderate-zone support-zone">
                                        <div class="price-range">
                                            <span class="zone-label"><?php esc_html_e('Zone:', 'tradepress'); ?></span>
                                            <span class="price-values">$<?php echo number_format($zone['min_price'], 2); ?> - $<?php echo number_format($zone['max_price'], 2); ?></span>
                                        </div>
                                        <div class="confirmation">
                                            <span class="confirmation-label"><?php esc_html_e('Confirmed by:', 'tradepress'); ?></span>
                                            <span class="confirmation-count"><?php echo count($zone['methods']); ?> methods</span>
                                        </div>
                                        <div class="zone-methods">
                                            <?php foreach ($zone['methods'] as $method): ?>
                                                <span class="method-badge"><?php echo esc_html(ucwords(str_replace('_', ' ', $method))); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($results['support']['all_levels'])): ?>
                        <div class="levels-section levels-detail">
                            <button type="button" class="button toggle-details"><?php esc_html_e('Show All Individual Levels', 'tradepress'); ?></button>
                            <div class="detailed-levels" style="display: none;">
                                <h5><?php esc_html_e('All Individual Support Levels', 'tradepress'); ?></h5>
                                <table class="levels-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Method', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Price Levels', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['support']['all_levels'] as $method => $levels): ?>
                                            <tr>
                                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $method))); ?></td>
                                                <td>
                                                    <?php 
                                                    $formatted_levels = array_map(function($level) {
                                                        return '$' . number_format($level, 2);
                                                    }, $levels);
                                                    echo implode(', ', $formatted_levels);
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="levels-footer">
                <div class="levels-notes">
                    <h4><?php esc_html_e('How to Use These Levels', 'tradepress'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Strong zones (confirmed by 3+ methods) are more likely to act as significant barriers.', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Use resistance levels as potential exit points for long positions or entry points for short positions.', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Use support levels as potential entry points for long positions or exit points for short positions.', 'tradepress'); ?></li>
                        <li><?php esc_html_e('When a level breaks, it often becomes the opposite type (broken resistance becomes support, broken support becomes resistance).', 'tradepress'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
