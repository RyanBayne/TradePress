<?php
/**
 * TradePress Earnings Tab
 *
 * Displays earnings analysis tools and data for the Research page
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.4
 * @since 1.0.0
 * @created 2023-10-04 14:30
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display the Earnings tab content
 */
function tradepress_earnings_tab_content() {
    // Get current date for filtering
    $current_date = current_time('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+7 days', strtotime($current_date)));
    
    // Get filter parameters with defaults
    $view_mode = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'week';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : $current_date;
    if ($view_mode === 'week') {
        $end_date = date('Y-m-d', strtotime('+7 days', strtotime($start_date)));
    } elseif ($view_mode === 'month') {
        $end_date = date('Y-m-d', strtotime('+30 days', strtotime($start_date)));
    } else {
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : $end_date;
    }
    
    $sector_filter = isset($_GET['sector']) ? sanitize_text_field($_GET['sector']) : 'all';
    $importance_filter = isset($_GET['importance']) ? sanitize_text_field($_GET['importance']) : 'all';
    $display_mode = isset($_GET['display']) ? sanitize_text_field($_GET['display']) : 'table';
    
    // Get user's timezone setting
    $user_timezone = get_option('timezone_string');
    if (empty($user_timezone)) {
        // Default to WordPress timezone if user hasn't set one
        $user_timezone = 'UTC';
    }
    
    // Check if we're in demo mode - if so, use mock data, otherwise use real data
    if (!function_exists('is_demo_mode')) {
        require_once TRADEPRESS_PLUGIN_DIR . 'functions/functions.tradepress-test-data.php';
    }
    
    $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;
    $earnings_data = array();
    
    if ($is_demo) {
        // Generate mock earnings data in demo mode only
        $earnings_data = tradepress_get_mock_earnings_data($start_date, $end_date, $sector_filter, $importance_filter);
        $data_source = 'Demo Mode';
        $last_updated_text = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), current_time('timestamp'));
    } else {
        // In real mode, fetch data from Alpha Vantage API
        $earnings_data = tradepress_fetch_earnings_calendar_data($start_date, $end_date, $sector_filter);
        
        // Get data source and last updated information for display
        $data_source = get_option('tradepress_earnings_data_source', 'Alpha Vantage');
        $last_updated = get_option('tradepress_earnings_last_updated', 0);
        $last_updated_text = $last_updated ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_updated) : 'Never';
    }
    
    // Group earnings by date
    $earnings_by_date = array();
    foreach ($earnings_data as $earning) {
        $date = $earning['date'];
        if (!isset($earnings_by_date[$date])) {
            $earnings_by_date[$date] = array();
        }
        $earnings_by_date[$date][] = $earning;
    }
    
    // Sort dates chronologically
    ksort($earnings_by_date);
    ?>
    
    <div class="tradepress-earnings-container">

        
        <div class="tradepress-research-section">
            <!-- Earnings Calendar Controls -->
            <div class="earnings-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="earnings">
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="view"><?php esc_html_e('View:', 'tradepress'); ?></label>
                            <select id="view" name="view" onchange="this.form.submit()">
                                <option value="week" <?php selected($view_mode, 'week'); ?>><?php esc_html_e('Week Ahead', 'tradepress'); ?></option>
                                <option value="month" <?php selected($view_mode, 'month'); ?>><?php esc_html_e('Month Ahead', 'tradepress'); ?></option>
                                <option value="custom" <?php selected($view_mode, 'custom'); ?>><?php esc_html_e('Custom Range', 'tradepress'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group date-filter <?php echo ($view_mode === 'custom') ? 'visible' : ''; ?>">
                            <label for="start_date"><?php esc_html_e('From:', 'tradepress'); ?></label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
                            
                            <label for="end_date"><?php esc_html_e('To:', 'tradepress'); ?></label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="sector"><?php esc_html_e('Sector:', 'tradepress'); ?></label>
                            <select id="sector" name="sector">
                                <option value="all" <?php selected($sector_filter, 'all'); ?>><?php esc_html_e('All Sectors', 'tradepress'); ?></option>
                                <option value="technology" <?php selected($sector_filter, 'technology'); ?>><?php esc_html_e('Technology', 'tradepress'); ?></option>
                                <option value="healthcare" <?php selected($sector_filter, 'healthcare'); ?>><?php esc_html_e('Healthcare', 'tradepress'); ?></option>
                                <option value="financial" <?php selected($sector_filter, 'financial'); ?>><?php esc_html_e('Financial', 'tradepress'); ?></option>
                                <option value="consumer" <?php selected($sector_filter, 'consumer'); ?>><?php esc_html_e('Consumer', 'tradepress'); ?></option>
                                <option value="industrial" <?php selected($sector_filter, 'industrial'); ?>><?php esc_html_e('Industrial', 'tradepress'); ?></option>
                                <option value="energy" <?php selected($sector_filter, 'energy'); ?>><?php esc_html_e('Energy', 'tradepress'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="importance"><?php esc_html_e('Importance:', 'tradepress'); ?></label>
                            <select id="importance" name="importance">
                                <option value="all" <?php selected($importance_filter, 'all'); ?>><?php esc_html_e('All', 'tradepress'); ?></option>
                                <option value="high" <?php selected($importance_filter, 'high'); ?>><?php esc_html_e('High', 'tradepress'); ?></option>
                                <option value="medium" <?php selected($importance_filter, 'medium'); ?>><?php esc_html_e('Medium', 'tradepress'); ?></option>
                                <option value="low" <?php selected($importance_filter, 'low'); ?>><?php esc_html_e('Low', 'tradepress'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group display-toggle">
                            <label><?php esc_html_e('Display Mode:', 'tradepress'); ?></label>
                            <div class="toggle-buttons">
                                <a href="<?php echo esc_url(add_query_arg(array('display' => 'table'))); ?>" class="toggle-button <?php echo $display_mode === 'table' ? 'active' : ''; ?>">
                                    <span class="dashicons dashicons-list-view"></span>
                                    <?php esc_html_e('Table', 'tradepress'); ?>
                                </a>
                                <a href="<?php echo esc_url(add_query_arg(array('display' => 'cards'))); ?>" class="toggle-button <?php echo $display_mode === 'cards' ? 'active' : ''; ?>">
                                    <span class="dashicons dashicons-grid-view"></span>
                                    <?php esc_html_e('Cards', 'tradepress'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="button button-primary"><?php esc_html_e('Apply Filters', 'tradepress'); ?></button>
                            <a href="<?php echo esc_url(add_query_arg(array('page' => 'tradepress_research', 'tab' => 'earnings'))); ?>" class="button"><?php esc_html_e('Reset', 'tradepress'); ?></a>
                        </div>
                    </div>
                </form>
                
                <div class="timezone-info">
                    <span class="dashicons dashicons-clock"></span>
                    <?php echo sprintf(esc_html__('All times shown in your local timezone: %s', 'tradepress'), '<strong>' . esc_html($user_timezone) . '</strong>'); ?>
                </div>
            </div>
            
            <!-- Results Header -->
            <div class="earnings-results-header">
                <h3>
                    <?php 
                    if ($view_mode === 'week') {
                        echo esc_html__('Week Ahead Earnings', 'tradepress');
                    } elseif ($view_mode === 'month') {
                        echo esc_html__('Month Ahead Earnings', 'tradepress');
                    } else {
                        echo sprintf(esc_html__('Earnings from %s to %s', 'tradepress'), 
                            date_i18n(get_option('date_format'), strtotime($start_date)),
                            date_i18n(get_option('date_format'), strtotime($end_date))
                        );
                    }
                    ?>
                </h3>
                
                <?php if (empty($earnings_data)): ?>
                    <div class="no-earnings-message">
                        <p><?php esc_html_e('No earnings reports found for the selected criteria.', 'tradepress'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="earnings-summary">
                        <span class="total-count"><?php echo sprintf(esc_html(_n('%d company reporting', '%d companies reporting', count($earnings_data), 'tradepress')), count($earnings_data)); ?></span>
                        <a href="#" class="export-earnings button"><?php esc_html_e('Export to CSV', 'tradepress'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Earnings Calendar -->
            <?php if (!empty($earnings_data)): ?>
                <?php if ($display_mode === 'table'): ?>
                    <!-- Table View -->
                    <div class="earnings-calendar">
                        <?php foreach ($earnings_by_date as $date => $day_earnings): ?>
                            <div class="earnings-date-group">
                                <h4 class="earnings-date-header">
                                    <?php echo date_i18n(get_option('date_format'), strtotime($date)); ?>
                                    <span class="day-name"><?php echo date_i18n('l', strtotime($date)); ?></span>
                                    <span class="report-count"><?php echo count($day_earnings); ?> <?php echo _n('report', 'reports', count($day_earnings), 'tradepress'); ?></span>
                                </h4>
                                
                                <table class="earnings-table widefat striped">
                                    <thead>
                                        <tr>
                                            <th class="column-time"><?php esc_html_e('Time (Local)', 'tradepress'); ?></th>
                                            <th class="column-symbol"><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                                            <th class="column-company"><?php esc_html_e('Company', 'tradepress'); ?></th>
                                            <th class="column-market-cap"><?php esc_html_e('Market Cap', 'tradepress'); ?></th>
                                            <th class="column-sector"><?php esc_html_e('Sector', 'tradepress'); ?></th>
                                            <th class="column-eps-estimate"><?php esc_html_e('EPS Est.', 'tradepress'); ?></th>
                                            <th class="column-whisper"><?php esc_html_e('Whisper', 'tradepress'); ?></th>
                                            <th class="column-prev-eps"><?php esc_html_e('Previous EPS', 'tradepress'); ?></th>
                                            <th class="column-eps-change"><?php esc_html_e('EPS %', 'tradepress'); ?></th>
                                            <th class="column-sentiment"><?php esc_html_e('Sentiment', 'tradepress'); ?></th>
                                            <th class="column-opp-score"><?php esc_html_e('Opp. Score', 'tradepress'); ?></th>
                                            <th class="column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($day_earnings as $index => $earning): 
                                            // Generate sentiment data 
                                            $sentiment_options = array('Bullish', 'Neutral', 'Bearish');
                                            $sentiment = $sentiment_options[array_rand($sentiment_options)];
                                            $sentiment_percent = mt_rand(20, 80) . '%';
                                            $sentiment_class = strtolower($sentiment);
                                            
                                            // Generate opportunity score
                                            $opportunity_score = mt_rand(10, 95);
                                            if ($opportunity_score >= 75) {
                                                $opportunity_class = 'high-opportunity';
                                            } elseif ($opportunity_score >= 50) {
                                                $opportunity_class = 'medium-opportunity';
                                            } else {
                                                $opportunity_class = 'low-opportunity';
                                            }
                                            
                                            // Generate whisper value if not present
                                            if (!isset($earning['whisper']) || empty($earning['whisper'])) {
                                                $eps_value = (float)substr($earning['eps_estimate'], 1);
                                                $whisper_diff = (mt_rand(-10, 15) / 100) * $eps_value;
                                                $earning['whisper'] = '$' . number_format($eps_value + $whisper_diff, 2);
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo esc_html($earning['time']); ?></td>
                                                <td><?php echo esc_html($earning['symbol']); ?></td>
                                                <td><?php echo esc_html($earning['company']); ?></td>
                                                <td><?php echo esc_html($earning['market_cap']); ?></td>
                                                <td><?php echo esc_html($earning['sector']); ?></td>
                                                <td><?php echo esc_html($earning['eps_estimate']); ?></td>
                                                <td><?php echo esc_html($earning['whisper']); ?></td>
                                                <td><?php echo esc_html($earning['previous_eps']); ?></td>
                                                <td>
                                                    <?php
                                                    $eps_percent = $earning['eps_change_percent'];
                                                    $eps_class = '';
                                                    
                                                    if ($eps_percent > 0) {
                                                        $eps_class = 'earnings-positive';
                                                        echo '<span class="' . esc_attr($eps_class) . '">+' . number_format($eps_percent, 2) . '%</span>';
                                                    } elseif ($eps_percent < 0) {
                                                        $eps_class = 'earnings-negative';
                                                        echo '<span class="' . esc_attr($eps_class) . '">' . number_format($eps_percent, 2) . '%</span>';
                                                    } else {
                                                        $eps_class = 'earnings-neutral';
                                                        echo '<span class="' . esc_attr($eps_class) . '">0.00%</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="sentiment-badge sentiment-<?php echo $sentiment_class; ?>">
                                                        <?php echo esc_html($sentiment); ?>
                                                    </span>
                                                    <span class="sentiment-pct"><?php echo esc_html($sentiment_percent); ?></span>
                                                </td>
                                                <td>
                                                    <div class="opp-score-container <?php echo esc_attr($opportunity_class); ?>">
                                                        <div class="opp-score-value"><?php echo esc_html($opportunity_score); ?></div>
                                                        <div class="opp-score-bar" style="width: <?php echo min(100, $opportunity_score); ?>%;"></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="button button-small view-details" data-symbol="<?php echo esc_attr($earning['symbol']); ?>">
                                                        <?php esc_html_e('Details', 'tradepress'); ?>
                                                    </button>
                                                    <button type="button" class="button button-small add-to-watchlist" data-symbol="<?php echo esc_attr($earning['symbol']); ?>">
                                                        <?php esc_html_e('Watch', 'tradepress'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Card View -->
                    <div class="earnings-cards-view">
                        <?php foreach ($earnings_data as $earning): 
                            // Generate random current price and estimated price for card view
                            $current_price = mt_rand(1000, 50000) / 100;
                            $price_change_pct = mt_rand(-1000, 2000) / 100;
                            $estimated_price = $current_price * (1 + $price_change_pct / 100);
                            $algorithm_score = mt_rand(10, 100);
                            
                            // Determine if this is a potential opportunity based on score
                            $opportunity_class = '';
                            if ($algorithm_score >= 75) {
                                $opportunity_class = 'high-opportunity';
                            } elseif ($algorithm_score >= 50) {
                                $opportunity_class = 'medium-opportunity';
                            } else {
                                $opportunity_class = 'low-opportunity';
                            }
                        ?>
                            <div class="earnings-card <?php echo esc_attr($opportunity_class); ?>">
                                <div class="earnings-card-header">
                                    <div class="company-logo">
                                        <!-- Placeholder for company logo -->
                                        <div class="logo-placeholder"><?php echo substr($earning['symbol'], 0, 1); ?></div>
                                    </div>
                                    <div class="company-info">
                                        <h4 class="symbol"><?php echo esc_html($earning['symbol']); ?></h4>
                                        <p class="company-name"><?php echo esc_html($earning['company']); ?></p>
                                    </div>
                                    <div class="earnings-date-badge">
                                        <span class="date"><?php echo date_i18n('M d', strtotime($earning['date'])); ?></span>
                                        <span class="time"><?php echo esc_html($earning['time']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="earnings-card-body">
                                    <div class="earnings-data-row">
                                        <div class="data-item">
                                            <span class="label"><?php esc_html_e('Sector', 'tradepress'); ?></span>
                                            <span class="value sector-value"><?php echo esc_html($earning['sector']); ?></span>
                                        </div>
                                        <div class="data-item">
                                            <span class="label"><?php esc_html_e('Market Cap', 'tradepress'); ?></span>
                                            <span class="value"><?php echo esc_html($earning['market_cap']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="earnings-data-row">
                                        <div class="data-item">
                                            <span class="label"><?php esc_html_e('Current Price', 'tradepress'); ?></span>
                                            <span class="value price-value">$<?php echo number_format($current_price, 2); ?></span>
                                        </div>
                                        <div class="data-item">
                                            <span class="label"><?php esc_html_e('Est. Price', 'tradepress'); ?></span>
                                            <span class="value estimated-price <?php echo $price_change_pct >= 0 ? 'positive' : 'negative'; ?>">
                                                $<?php echo number_format($estimated_price, 2); ?>
                                                <span class="price-change">(<?php echo $price_change_pct >= 0 ? '+' : ''; ?><?php echo number_format($price_change_pct, 2); ?>%)</span>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="earnings-data-row">
                                        <div class="data-item">
                                            <span class="label"><?php esc_html_e('EPS Est.', 'tradepress'); ?></span>
                                            <span class="value"><?php echo esc_html($earning['eps_estimate']); ?></span>
                                        </div>
                                        <div class="data-item">
                                            <span class="label"><?php esc_html_e('Previous EPS', 'tradepress'); ?></span>
                                            <span class="value"><?php echo esc_html($earning['previous_eps']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="algorithm-score">
                                        <div class="score-label"><?php esc_html_e('Algorithm Score', 'tradepress'); ?></div>
                                        <div class="score-container <?php echo esc_attr($opportunity_class); ?>">
                                            <div class="score-bar" style="width: <?php echo esc_attr($algorithm_score); ?>%;"></div>
                                            <div class="score-value"><?php echo esc_html($algorithm_score); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="earnings-card-footer">
                                    <button type="button" class="button button-primary view-details" data-symbol="<?php echo esc_attr($earning['symbol']); ?>">
                                        <?php esc_html_e('Details', 'tradepress'); ?>
                                    </button>
                                    <button type="button" class="button add-to-watchlist" data-symbol="<?php echo esc_attr($earning['symbol']); ?>">
                                        <?php esc_html_e('Add to Watchlist', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Empty state - Display loading state when refresh is clicked -->
                <div class="earnings-loading-state" style="display: none;">
                    <p>
                        <span class="spinner is-active"></span>
                        <?php esc_html_e('Loading earnings data...', 'tradepress'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Generate mock earnings data for testing
 * 
 * @param string $start_date Start date in Y-m-d format
 * @param string $end_date End date in Y-m-d format
 * @param string $sector_filter Sector to filter by, or 'all'
 * @param string $importance_filter Importance to filter by, or 'all'
 * @return array Array of earnings data
 */
function tradepress_get_mock_earnings_data($start_date, $end_date, $sector_filter = 'all', $importance_filter = 'all') {
    // Sample company data
    $companies = array(
        array('symbol' => 'MSFT', 'company' => 'Microsoft Corporation', 'sector' => 'Technology', 'market_cap' => '$2.5T', 'importance' => 'high'),
        array('symbol' => 'AAPL', 'company' => 'Apple Inc.', 'sector' => 'Technology', 'market_cap' => '$2.8T', 'importance' => 'high'),
        array('symbol' => 'GOOGL', 'company' => 'Alphabet Inc.', 'sector' => 'Technology', 'market_cap' => '$1.7T', 'importance' => 'high'),
        array('symbol' => 'AMZN', 'company' => 'Amazon.com Inc.', 'sector' => 'Consumer', 'market_cap' => '$1.4T', 'importance' => 'high'),
        array('symbol' => 'META', 'company' => 'Meta Platforms Inc.', 'sector' => 'Technology', 'market_cap' => '$1.0T', 'importance' => 'high'),
        array('symbol' => 'TSLA', 'company' => 'Tesla Inc.', 'sector' => 'Consumer', 'market_cap' => '$800B', 'importance' => 'high'),
        array('symbol' => 'NVDA', 'company' => 'NVIDIA Corporation', 'sector' => 'Technology', 'market_cap' => '$1.2T', 'importance' => 'high'),
        array('symbol' => 'JPM', 'company' => 'JPMorgan Chase & Co.', 'sector' => 'Financial', 'market_cap' => '$550B', 'importance' => 'high'),
        array('symbol' => 'JNJ', 'company' => 'Johnson & Johnson', 'sector' => 'Healthcare', 'market_cap' => '$400B', 'importance' => 'high'),
        array('symbol' => 'V', 'company' => 'Visa Inc.', 'sector' => 'Financial', 'market_cap' => '$490B', 'importance' => 'high'),
        array('symbol' => 'PG', 'company' => 'Procter & Gamble Co.', 'sector' => 'Consumer', 'market_cap' => '$350B', 'importance' => 'medium'),
        array('symbol' => 'UNH', 'company' => 'UnitedHealth Group Inc.', 'sector' => 'Healthcare', 'market_cap' => '$450B', 'importance' => 'medium'),
        array('symbol' => 'HD', 'company' => 'Home Depot Inc.', 'sector' => 'Consumer', 'market_cap' => '$330B', 'importance' => 'medium'),
        array('symbol' => 'MA', 'company' => 'Mastercard Inc.', 'sector' => 'Financial', 'market_cap' => '$380B', 'importance' => 'medium'),
        array('symbol' => 'PFE', 'company' => 'Pfizer Inc.', 'sector' => 'Healthcare', 'market_cap' => '$240B', 'importance' => 'medium'),
        array('symbol' => 'CSCO', 'company' => 'Cisco Systems Inc.', 'sector' => 'Technology', 'market_cap' => '$200B', 'importance' => 'medium'),
        array('symbol' => 'CVX', 'company' => 'Chevron Corporation', 'sector' => 'Energy', 'market_cap' => '$320B', 'importance' => 'medium'),
        array('symbol' => 'KO', 'company' => 'The Coca-Cola Company', 'sector' => 'Consumer', 'market_cap' => '$260B', 'importance' => 'medium'),
        array('symbol' => 'DIS', 'company' => 'The Walt Disney Company', 'sector' => 'Consumer', 'market_cap' => '$170B', 'importance' => 'medium'),
        array('symbol' => 'MRK', 'company' => 'Merck & Co., Inc.', 'sector' => 'Healthcare', 'market_cap' => '$230B', 'importance' => 'medium'),
        array('symbol' => 'BA', 'company' => 'The Boeing Company', 'sector' => 'Industrial', 'market_cap' => '$120B', 'importance' => 'medium'),
        array('symbol' => 'WMT', 'company' => 'Walmart Inc.', 'sector' => 'Consumer', 'market_cap' => '$410B', 'importance' => 'medium'),
        array('symbol' => 'CRM', 'company' => 'Salesforce, Inc.', 'sector' => 'Technology', 'market_cap' => '$220B', 'importance' => 'medium'),
        array('symbol' => 'XOM', 'company' => 'Exxon Mobil Corporation', 'sector' => 'Energy', 'market_cap' => '$410B', 'importance' => 'medium'),
        array('symbol' => 'INTC', 'company' => 'Intel Corporation', 'sector' => 'Technology', 'market_cap' => '$150B', 'importance' => 'medium'),
        array('symbol' => 'VZ', 'company' => 'Verizon Communications Inc.', 'sector' => 'Technology', 'market_cap' => '$160B', 'importance' => 'low'),
        array('symbol' => 'NFLX', 'company' => 'Netflix, Inc.', 'sector' => 'Technology', 'market_cap' => '$240B', 'importance' => 'medium'),
        array('symbol' => 'ADBE', 'company' => 'Adobe Inc.', 'sector' => 'Technology', 'market_cap' => '$230B', 'importance' => 'medium'),
        array('symbol' => 'PYPL', 'company' => 'PayPal Holdings, Inc.', 'sector' => 'Financial', 'market_cap' => '$90B', 'importance' => 'medium'),
        array('symbol' => 'SBUX', 'company' => 'Starbucks Corporation', 'sector' => 'Consumer', 'market_cap' => '$100B', 'importance' => 'low'),
    );
    
    // Reporting times
    $times = array(
        'Before Market Open',
        'After Market Close',
        '8:00 AM ET',
        '4:30 PM ET',
    );
    
    // Start with an empty array
    $earnings_data = array();
    
    // Convert start and end dates to timestamps
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    // Shuffle companies to randomize
    shuffle($companies);
    
    // Generate random earnings reports for each day in the date range
    $current_timestamp = $start_timestamp;
    while ($current_timestamp <= $end_timestamp) {
        // Skip weekends
        $day_of_week = date('N', $current_timestamp);
        if ($day_of_week > 5) {
            $current_timestamp = strtotime('+1 day', $current_timestamp);
            continue;
        }
        
        // Generate 2-7 earnings reports for this day
        $report_count = mt_rand(2, 7);
        $day_companies = array_slice($companies, 0, $report_count);
        shuffle($day_companies);
        
        foreach ($day_companies as $company) {
            // Skip if sector doesn't match filter
            if ($sector_filter !== 'all' && strtolower($company['sector']) !== $sector_filter) {
                continue;
            }
            
            // Skip if importance doesn't match filter
            if ($importance_filter !== 'all' && $company['importance'] !== $importance_filter) {
                continue;
            }
            
            // Generate random EPS estimate
            $eps_estimate = sprintf('$%.2f', mt_rand(10, 500) / 100);
            
            // Generate previous EPS and calculate percentage change
            $previous_eps = sprintf('$%.2f', mt_rand(8, 500) / 100);
            $eps_value = (float)substr($eps_estimate, 1);
            $prev_eps_value = (float)substr($previous_eps, 1);
            
            // Calculate percentage change (avoid division by zero)
            if ($prev_eps_value > 0) {
                $eps_change_percent = (($eps_value - $prev_eps_value) / $prev_eps_value) * 100;
            } else {
                $eps_change_percent = 0;
            }
            
            // Add to earnings data
            $earnings_data[] = array(
                'date' => date('Y-m-d', $current_timestamp),
                'time' => $times[array_rand($times)],
                'symbol' => $company['symbol'],
                'company' => $company['company'],
                'market_cap' => $company['market_cap'],
                'sector' => $company['sector'],
                'eps_estimate' => $eps_estimate,
                'previous_eps' => $previous_eps,
                'eps_change_percent' => $eps_change_percent,
                'importance' => $company['importance'],
            );
        }
        
        // Move to next day
        $current_timestamp = strtotime('+1 day', $current_timestamp);
        
        // Shift companies array for next day
        $companies[] = array_shift($companies);
    }
    
    return $earnings_data;
}

/**
 * Fetch earnings calendar data from Alpha Vantage API
 * 
 * @param string $start_date Start date in Y-m-d format
 * @param string $end_date End date in Y-m-d format
 * @param string $sector_filter Sector to filter by, or 'all'
 * @return array Array of earnings data
 */
function tradepress_fetch_earnings_calendar_data($start_date, $end_date, $sector_filter = 'all') {
    // Check if we have cached data
    $transient_key = 'tradepress_earnings_data_' . md5($start_date . $end_date . $sector_filter);
    $cached_data = get_transient($transient_key);
    
    if (false !== $cached_data) {
        return $cached_data;
    }
    
    // Load the Alpha Vantage API class
    if (!class_exists('TradePress_AlphaVantage_API')) {
        require_once TRADEPRESS_PLUGIN_DIR . 'api/alphavantage/alphavantage-api.php';
    }
    
    // Get Alpha Vantage API key
    $api_key = get_option('tradepress_alphavantage_api_key', '');
    
    // Create API instance
    $api = new TradePress_AlphaVantage_API(array('api_key' => $api_key));
    
    // Get earnings calendar data
    $response = $api->get_earnings_calendar('3month'); // Using 3month horizon as default
    
    // Return empty array if there's an error
    if (is_wp_error($response)) {
        // Log the error for admin reference
        error_log('TradePress Earnings Calendar Error: ' . $response->get_error_message());
        return array();
    }
    
    // Process the raw earnings data into our standardized format
    $formatted_data = array();
    
    foreach ($response as $earning) {
        // Skip if the report date is outside our date range
        $report_date = isset($earning['reportDate']) ? $earning['reportDate'] : '';
        if (empty($report_date) || $report_date < $start_date || $report_date > $end_date) {
            continue;
        }
        
        // Extract values and set defaults for missing data
        $symbol = isset($earning['symbol']) ? $earning['symbol'] : '';
        $company = isset($earning['name']) ? $earning['name'] : '';
        $fiscal_date_ending = isset($earning['fiscalDateEnding']) ? $earning['fiscalDateEnding'] : '';
        $estimate = isset($earning['estimate']) ? $earning['estimate'] : '';
        $currency = isset($earning['currency']) ? $earning['currency'] : 'USD';
        
        // Get additional company data if possible
        $market_cap = '';
        $sector = '';
        
        // Apply sector filtering
        if ($sector_filter !== 'all' && !empty($sector) && strtolower($sector) !== strtolower($sector_filter)) {
            continue;
        }
        
        // Determine reporting time based on convention
        // The Alpha Vantage API doesn't provide exact time, so we set a default
        $time = 'TBA'; // To Be Announced
        
        // Format the estimate value
        $eps_estimate = !empty($estimate) ? '$' . number_format((float)$estimate, 2) : 'N/A';
        
        // Previous EPS data isn't provided in the API call, so we set placeholders
        $previous_eps = 'N/A';
        $eps_change_percent = 0;
        
        // Add to formatted data
        $formatted_data[] = array(
            'date' => $report_date,
            'time' => $time,
            'symbol' => $symbol,
            'company' => $company,
            'market_cap' => $market_cap,
            'sector' => $sector,
            'eps_estimate' => $eps_estimate,
            'previous_eps' => $previous_eps,
            'eps_change_percent' => $eps_change_percent,
            'importance' => 'medium', // Default importance
            'fiscal_date_ending' => $fiscal_date_ending,
            'currency' => $currency
        );
    }
    
    // Store the last updated time
    update_option('tradepress_earnings_last_updated', time());
    update_option('tradepress_earnings_data_source', 'Alpha Vantage');
    
    // Cache the data for 6 hours (earning data doesn't change frequently)
    set_transient($transient_key, $formatted_data, 6 * HOUR_IN_SECONDS);
    
    return $formatted_data;
}
?>
