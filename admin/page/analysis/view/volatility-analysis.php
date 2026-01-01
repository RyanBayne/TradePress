<?php
/**
 * TradePress Volatility Calculator Tab
 *
 * Displays volatility analysis tools and educational content for the Research page
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.2
 * @since 1.0.0
 * @created 2023-05-20 11:45
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Make sure we have the calculator class loaded
if (!class_exists('TradePress_Volatility_Tools')) {
    require_once TRADEPRESS_PLUGIN_DIR . 'includes/volatility-tools.php';
}

/**
 * Display the Volatility Calculator tab content
 */
function tradepress_volatility_analysis_tab_content() {
    // Check if demo mode is active
    $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;

    // Get any saved settings or demo data
    $demo_stock = isset($_GET['symbol']) ? sanitize_text_field($_GET['symbol']) : 'AAPL';
    $demo_timeframe = isset($_GET['timeframe']) ? sanitize_text_field($_GET['timeframe']) : '30';
    $account_size = isset($_GET['account_size']) ? floatval($_GET['account_size']) : 10000;
    $risk_percentage = isset($_GET['risk_percentage']) ? floatval($_GET['risk_percentage']) : 2;
    
    // Demo volatility data
    $historical_volatility = TradePress_Volatility_Tools::calculate_demo_historical_volatility($demo_stock, $demo_timeframe);
    $implied_volatility = TradePress_Volatility_Tools::calculate_demo_implied_volatility($demo_stock);
    $beta = TradePress_Volatility_Tools::calculate_demo_beta($demo_stock);
    $volatility_regime = TradePress_Volatility_Tools::determine_volatility_regime($historical_volatility, $implied_volatility);
    $position_sizing = TradePress_Volatility_Tools::calculate_position_size($historical_volatility, $account_size, $risk_percentage);
    $demo_atr = TradePress_Volatility_Tools::calculate_demo_atr($demo_stock);
    
    // Display the volatility calculator interface
    ?>
    <div class="tradepress-volatility-analysis-container">

        
        <div class="tradepress-research-section">
            <h2><?php esc_html_e('Volatility Analysis', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Analyze different types of volatility to inform trading decisions and manage risk.', 'tradepress'); ?></p>
            
            <div class="tradepress-research-inputs">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="volatility-analysis">
                    
                    <div class="tradepress-input-group">
                        <label for="symbol"><?php esc_html_e('Symbol:', 'tradepress'); ?></label>
                        <input type="text" id="symbol" name="symbol" value="<?php echo esc_attr($demo_stock); ?>" placeholder="AAPL">
                    </div>
                    
                    <div class="tradepress-input-group">
                        <label for="timeframe"><?php esc_html_e('Historical Timeframe (Days):', 'tradepress'); ?></label>
                        <select id="timeframe" name="timeframe">
                            <option value="14" <?php selected($demo_timeframe, '14'); ?>>14 Days</option>
                            <option value="30" <?php selected($demo_timeframe, '30'); ?>>30 Days</option>
                            <option value="60" <?php selected($demo_timeframe, '60'); ?>>60 Days</option>
                            <option value="90" <?php selected($demo_timeframe, '90'); ?>>90 Days</option>
                        </select>
                    </div>
                    
                    <div class="tradepress-input-group">
                        <label for="account_size"><?php esc_html_e('Account Size ($):', 'tradepress'); ?></label>
                        <input type="number" id="account_size" name="account_size" value="<?php echo esc_attr($account_size); ?>" min="1000" step="1000">
                    </div>
                    
                    <div class="tradepress-input-group">
                        <label for="risk_percentage"><?php esc_html_e('Risk per Trade (%):', 'tradepress'); ?></label>
                        <input type="number" id="risk_percentage" name="risk_percentage" value="<?php echo esc_attr($risk_percentage); ?>" min="0.5" max="5" step="0.5">
                    </div>
                    
                    <button type="submit" class="button button-primary"><?php esc_html_e('Calculate', 'tradepress'); ?></button>
                </form>
            </div>
        </div>
        
        <div class="tradepress-research-results">
            <div class="tradepress-research-panels">
                <div class="tradepress-panel">
                    <h3><?php esc_html_e('Volatility Analysis', 'tradepress'); ?> - <?php echo esc_html($demo_stock); ?></h3>
                    
                    <div class="tradepress-metrics-grid">
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Historical Volatility', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value <?php echo tradepress_volatility_class($historical_volatility); ?>">
                                <?php echo esc_html(number_format($historical_volatility, 2)); ?>%
                            </div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Based on actual price movements over the past', 'tradepress'); ?> <?php echo esc_html($demo_timeframe); ?> <?php esc_html_e('days', 'tradepress'); ?>
                            </div>
                        </div>
                        
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Implied Volatility', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value <?php echo tradepress_volatility_class($implied_volatility); ?>">
                                <?php echo esc_html(number_format($implied_volatility, 2)); ?>%
                            </div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Derived from options prices, indicating expected future volatility', 'tradepress'); ?>
                            </div>
                        </div>
                        
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Beta (β)', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value <?php echo tradepress_beta_class($beta); ?>">
                                <?php echo esc_html(number_format($beta, 2)); ?>
                            </div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Measures volatility relative to S&P 500 index', 'tradepress'); ?>
                            </div>
                        </div>
                        
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Average True Range', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value">$<?php echo esc_html(number_format($demo_atr, 2)); ?></div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Average daily price range including gaps', 'tradepress'); ?>
                            </div>
                        </div>
                        
                        <div class="tradepress-metric-card volatility-regime-card <?php echo sanitize_html_class(strtolower($volatility_regime['name'])); ?>">
                            <h4><?php esc_html_e('Volatility Regime', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value"><?php echo esc_html($volatility_regime['name']); ?></div>
                            <div class="tradepress-metric-description">
                                <?php echo esc_html($volatility_regime['description']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-panel opportunity-panel">
                    <h3><?php esc_html_e('Trading Opportunity Assessment', 'tradepress'); ?></h3>
                    <?php 
                    // Determine trading opportunity based on volatility metrics
                    $opportunity_type = tradepress_determine_trading_opportunity($historical_volatility, $implied_volatility, $beta);
                    $opportunity_class = sanitize_html_class(strtolower(str_replace(' ', '-', $opportunity_type['type'])));
                    ?>
                    <div class="trading-opportunity <?php echo $opportunity_class; ?>">
                        <div class="opportunity-heading">
                            <span class="opportunity-icon"></span>
                            <h4><?php echo esc_html($opportunity_type['title']); ?></h4>
                        </div>
                        <p><?php echo esc_html($opportunity_type['description']); ?></p>
                    </div>
                    
                    <div class="opportunity-metrics">
                        <div class="opportunity-metric long-opportunity <?php echo $opportunity_type['long_rating'] >= 3 ? 'highlighted' : ''; ?>">
                            <h4><?php esc_html_e('Long Opportunity', 'tradepress'); ?></h4>
                            <div class="rating-value">
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $opportunity_type['long_rating'] ? 'filled' : ''; ?>"></span>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-label"><?php echo esc_html($opportunity_type['long_label']); ?></div>
                            </div>
                        </div>
                        
                        <div class="opportunity-metric short-opportunity <?php echo $opportunity_type['short_rating'] >= 3 ? 'highlighted' : ''; ?>">
                            <h4><?php esc_html_e('Short Opportunity', 'tradepress'); ?></h4>
                            <div class="rating-value">
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $opportunity_type['short_rating'] ? 'filled' : ''; ?>"></span>
                                    <?php endfor; ?>
                                </div>
                                <div class="rating-label"><?php echo esc_html($opportunity_type['short_label']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-panel">
                    <h3><?php esc_html_e('Position Sizing Recommendations', 'tradepress'); ?></h3>
                    <p><?php echo sprintf(esc_html__('Based on your %s account with %s%% risk per trade:', 'tradepress'), '$' . number_format($account_size), $risk_percentage); ?></p>
                    
                    <div class="tradepress-metrics-grid columns-3">
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Conservative', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value">$<?php echo esc_html(number_format($position_sizing['conservative'], 2)); ?></div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Reduced exposure accounting for higher volatility', 'tradepress'); ?>
                            </div>
                        </div>
                        
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Moderate', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value">$<?php echo esc_html(number_format($position_sizing['moderate'], 2)); ?></div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Standard position size based on account risk percentage', 'tradepress'); ?>
                            </div>
                        </div>
                        
                        <div class="tradepress-metric-card">
                            <h4><?php esc_html_e('Aggressive', 'tradepress'); ?></h4>
                            <div class="tradepress-metric-value">$<?php echo esc_html(number_format($position_sizing['aggressive'], 2)); ?></div>
                            <div class="tradepress-metric-description">
                                <?php esc_html_e('Increased exposure for lower volatility environments', 'tradepress'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tradepress-stop-loss-calc">
                        <h4><?php esc_html_e('Stop Loss Calculation', 'tradepress'); ?></h4>
                        <p><?php esc_html_e('For a moderate position size, consider these stop loss distances:', 'tradepress'); ?></p>
                        
                        <div class="tradepress-metrics-grid columns-3">
                            <div class="tradepress-metric-card">
                                <h4><?php esc_html_e('Tight (1 ATR)', 'tradepress'); ?></h4>
                                <div class="tradepress-metric-value">$<?php echo esc_html(number_format($demo_atr, 2)); ?></div>
                                <div class="tradepress-metric-description">
                                    <?php 
                                    $shares = $position_sizing['moderate'] / $demo_atr;
                                    echo sprintf(esc_html__('Approx. %s shares', 'tradepress'), round($shares)); 
                                    ?>
                                </div>
                            </div>
                            
                            <div class="tradepress-metric-card">
                                <h4><?php esc_html_e('Standard (2 ATR)', 'tradepress'); ?></h4>
                                <div class="tradepress-metric-value">$<?php echo esc_html(number_format($demo_atr * 2, 2)); ?></div>
                                <div class="tradepress-metric-description">
                                    <?php 
                                    $shares = $position_sizing['moderate'] / ($demo_atr * 2);
                                    echo sprintf(esc_html__('Approx. %s shares', 'tradepress'), round($shares)); 
                                    ?>
                                </div>
                            </div>
                            
                            <div class="tradepress-metric-card">
                                <h4><?php esc_html_e('Wide (3 ATR)', 'tradepress'); ?></h4>
                                <div class="tradepress-metric-value">$<?php echo esc_html(number_format($demo_atr * 3, 2)); ?></div>
                                <div class="tradepress-metric-description">
                                    <?php 
                                    $shares = $position_sizing['moderate'] / ($demo_atr * 3);
                                    echo sprintf(esc_html__('Approx. %s shares', 'tradepress'), round($shares)); 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tradepress-panel">
                    <h3><?php esc_html_e('Volatility Interpretation', 'tradepress'); ?></h3>
                    <div class="tradepress-interpretation">
                        <?php echo TradePress_Volatility_Tools::generate_volatility_interpretation($historical_volatility, $implied_volatility, $beta, $volatility_regime); ?>
                    </div>
                </div>
            </div>
            
            <div class="tradepress-research-educational">
                <h3><?php esc_html_e('Understanding Volatility Types', 'tradepress'); ?></h3>
                <div class="tradepress-accordion">
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Historical Volatility', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Historical Volatility (also called Realized Volatility) measures the actual price swings of a security over a specific past period. It is typically calculated using statistical measures like standard deviation, which quantifies how dispersed the returns are around the average return. High historical volatility indicates that the price has experienced large fluctuations in the past.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Implied Volatility', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Implied Volatility is a forward-looking measure that represents the market\'s expectation of future volatility for a specific security or index. It is derived from the prices of options contracts. Higher implied volatility suggests that options traders expect larger price swings in the future. The Cboe Volatility Index (VIX) is a widely watched index that reflects the implied volatility of S&P 500 index options and is often referred to as the "fear gauge" because it tends to rise during periods of market stress and uncertainty.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Beta (β)', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Beta (β) is a measure of a security\'s systematic risk and its volatility relative to a benchmark market index (commonly the S&P 500). A beta of 1 indicates that the stock\'s price tends to move in line with the market. A beta greater than 1 suggests the stock is more volatile than the market, while a beta less than 1 indicates lower relative volatility.', 'tradepress'); ?></p>
                            <ul>
                                <li><strong><?php esc_html_e('Beta > 1.5:', 'tradepress'); ?></strong> <?php esc_html_e('Highly volatile compared to the market', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Beta 1.0-1.5:', 'tradepress'); ?></strong> <?php esc_html_e('More volatile than the market', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Beta 0.8-1.0:', 'tradepress'); ?></strong> <?php esc_html_e('Similar volatility to the market', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Beta < 0.8:', 'tradepress'); ?></strong> <?php esc_html_e('Less volatile than the market', 'tradepress'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Average True Range (ATR)', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Average True Range (ATR) is a technical indicator that measures market volatility by decomposing the entire range of an asset price for a specific period. Unlike standard deviation which measures price dispersion around a mean, ATR measures the average range between high and low prices, including any gaps between trading sessions. ATR is commonly used to determine position size and set stop-loss levels.', 'tradepress'); ?></p>
                            <p><?php esc_html_e('A higher ATR indicates higher volatility, suggesting wider stop-losses may be appropriate. A lower ATR indicates lower volatility, allowing for tighter stop-losses.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Volatility Regimes', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Market participants often refer to different volatility regimes, which are periods characterized by distinct levels and patterns of volatility driven by various economic, political, and market-specific factors. These include:', 'tradepress'); ?></p>
                            <ul>
                                <li><strong><?php esc_html_e('Low Volatility Regimes:', 'tradepress'); ?></strong> <?php esc_html_e('Periods of relative calm with smaller and less frequent price movements.', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Moderate Volatility Regimes:', 'tradepress'); ?></strong> <?php esc_html_e('Normal market conditions with average price fluctuations.', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('High Volatility Regimes:', 'tradepress'); ?></strong> <?php esc_html_e('Periods of significant price swings, often associated with uncertainty, fear, or crises.', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Extreme Volatility Regimes:', 'tradepress'); ?></strong> <?php esc_html_e('Market crisis conditions with very large price movements and high uncertainty.', 'tradepress'); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="tradepress-accordion-item">
                        <div class="tradepress-accordion-header">
                            <h4><?php esc_html_e('Position Sizing and Volatility', 'tradepress'); ?></h4>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                        <div class="tradepress-accordion-content">
                            <p><?php esc_html_e('Position sizing is a critical risk management technique that involves adjusting the amount of capital allocated to a trade based on various factors, including volatility. When volatility is high, position sizes should typically be reduced to account for larger potential price swings.', 'tradepress'); ?></p>
                            <p><?php esc_html_e('Common position sizing approaches include:', 'tradepress'); ?></p>
                            <ul>
                                <li><strong><?php esc_html_e('Fixed Dollar Amount:', 'tradepress'); ?></strong> <?php esc_html_e('Allocating the same dollar amount to each trade regardless of volatility', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Fixed Percentage Risk:', 'tradepress'); ?></strong> <?php esc_html_e('Risking a fixed percentage of your account on each trade, adjusting position size based on stop distance', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('Volatility-Adjusted Risk:', 'tradepress'); ?></strong> <?php esc_html_e('Adjusting your risk percentage based on the current volatility regime', 'tradepress'); ?></li>
                                <li><strong><?php esc_html_e('ATR-Based Position Sizing:', 'tradepress'); ?></strong> <?php esc_html_e('Using ATR to determine appropriate stop-loss distances and therefore position sizes', 'tradepress'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get the appropriate CSS class for a volatility value
 * 
 * @param float $volatility Volatility percentage
 * @return string CSS class name
 */
function tradepress_volatility_class($volatility) {
    if ($volatility < 10) {
        return 'volatility-very-low';
    } elseif ($volatility < 20) {
        return 'volatility-low';
    } elseif ($volatility < 35) {
        return 'volatility-moderate';
    } elseif ($volatility < 50) {
        return 'volatility-high';
    } else {
        return 'volatility-very-high';
    }
}

/**
 * Get the appropriate CSS class for a beta value
 * 
 * @param float $beta Beta value
 * @return string CSS class name
 */
function tradepress_beta_class($beta) {
    if ($beta < 0.5) {
        return 'beta-very-low';
    } elseif ($beta < 0.8) {
        return 'beta-low';
    } elseif ($beta < 1.2) {
        return 'beta-moderate';
    } elseif ($beta < 1.5) {
        return 'beta-high';
    } else {
        return 'beta-very-high';
    }
}

/**
 * Determine trading opportunity based on volatility metrics
 * 
 * @param float $historical_volatility Historical volatility percentage
 * @param float $implied_volatility Implied volatility percentage
 * @param float $beta Beta value
 * @return array Trading opportunity information
 */
function tradepress_determine_trading_opportunity($historical_volatility, $implied_volatility, $beta) {
    // Default values
    $opportunity = array(
        'type' => 'neutral',
        'title' => __('Neutral Conditions', 'tradepress'),
        'description' => __('Current volatility levels suggest neutral trading conditions. Consider carefully analyzing additional factors.', 'tradepress'),
        'long_rating' => 3,
        'short_rating' => 3,
        'long_label' => __('Moderate', 'tradepress'),
        'short_label' => __('Moderate', 'tradepress')
    );
    
    // Check for long-favorable conditions
    if ($historical_volatility < 20 && $implied_volatility < $historical_volatility && $beta < 1.2) {
        $opportunity = array(
            'type' => 'long favorable',
            'title' => __('Favorable for Long Positions', 'tradepress'),
            'description' => __('Low volatility and decreasing implied volatility suggest favorable conditions for long positions.', 'tradepress'),
            'long_rating' => 4,
            'short_rating' => 2,
            'long_label' => __('Good', 'tradepress'),
            'short_label' => __('Poor', 'tradepress')
        );
    }
    // Check for very favorable long conditions
    elseif ($historical_volatility < 15 && $implied_volatility < $historical_volatility * 0.8 && $beta < 1) {
        $opportunity = array(
            'type' => 'long favorable',
            'title' => __('Very Favorable for Long Positions', 'tradepress'),
            'description' => __('Very low volatility with significantly decreasing implied volatility suggests excellent conditions for long positions.', 'tradepress'),
            'long_rating' => 5,
            'short_rating' => 1,
            'long_label' => __('Excellent', 'tradepress'),
            'short_label' => __('Very Poor', 'tradepress')
        );
    }
    // Check for short-favorable conditions
    elseif ($historical_volatility > 35 && $implied_volatility > $historical_volatility && $beta > 1.2) {
        $opportunity = array(
            'type' => 'short favorable',
            'title' => __('Favorable for Short Positions', 'tradepress'),
            'description' => __('High volatility and increasing implied volatility suggest favorable conditions for short positions.', 'tradepress'),
            'long_rating' => 2,
            'short_rating' => 4,
            'long_label' => __('Poor', 'tradepress'),
            'short_label' => __('Good', 'tradepress')
        );
    }
    // Check for very favorable short conditions
    elseif ($historical_volatility > 50 && $implied_volatility > $historical_volatility * 1.2 && $beta > 1.5) {
        $opportunity = array(
            'type' => 'short favorable',
            'title' => __('Very Favorable for Short Positions', 'tradepress'),
            'description' => __('Very high volatility with significantly increasing implied volatility suggests excellent conditions for short positions.', 'tradepress'),
            'long_rating' => 1,
            'short_rating' => 5,
            'long_label' => __('Very Poor', 'tradepress'),
            'short_label' => __('Excellent', 'tradepress')
        );
    }
    // Check for high risk conditions
    elseif ($historical_volatility > 40 && abs($implied_volatility - $historical_volatility) > 10) {
        $opportunity = array(
            'type' => 'high risk',
            'title' => __('High Risk Environment', 'tradepress'),
            'description' => __('High volatility with significant discrepancy between historical and implied volatility suggests a high-risk trading environment.', 'tradepress'),
            'long_rating' => 2,
            'short_rating' => 2,
            'long_label' => __('High Risk', 'tradepress'),
            'short_label' => __('High Risk', 'tradepress')
        );
    }
    
    return $opportunity;
}

tradepress_volatility_analysis_tab_content();