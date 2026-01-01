<?php
/**
 * TradePress Economic Calendar Tab
 *
 * Displays upcoming economic events, releases, and market-moving indicators
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.0
 * @since 1.0.0
 * @created 2023-06-18 09:45
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display the Economic Calendar tab content
 */
function tradepress_economic_calendar_tab_content() {
    // Get date range parameters with defaults
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-d');
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-d', strtotime('+7 days'));
    $importance = isset($_GET['importance']) ? sanitize_text_field($_GET['importance']) : 'all';
    $regions = isset($_GET['regions']) ? (array) $_GET['regions'] : array('us', 'eu', 'uk', 'jp', 'ca', 'au');
    
    // Get economic events data (demo data for now)
    $economic_events = tradepress_get_demo_economic_events($start_date, $end_date, $importance, $regions);
    
    // Get region names for display
    $region_names = tradepress_get_economic_regions();
    
    // Display the economic calendar interface
    ?>
    <div class="tradepress-economic-calendar-container">

        
        <div class="tradepress-research-section">
            <h2><?php esc_html_e('Economic Calendar', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Track upcoming economic events, data releases, and market-moving indicators.', 'tradepress'); ?></p>
            
            <div class="tradepress-research-inputs">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="economic-calendar">
                    
                    <div class="tradepress-input-row">
                        <div class="tradepress-input-group">
                            <label for="start_date"><?php esc_html_e('Start Date:', 'tradepress'); ?></label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
                        </div>
                        
                        <div class="tradepress-input-group">
                            <label for="end_date"><?php esc_html_e('End Date:', 'tradepress'); ?></label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>">
                        </div>
                        
                        <div class="tradepress-input-group">
                            <label for="importance"><?php esc_html_e('Importance:', 'tradepress'); ?></label>
                            <select id="importance" name="importance">
                                <option value="all" <?php selected($importance, 'all'); ?>><?php esc_html_e('All Events', 'tradepress'); ?></option>
                                <option value="high" <?php selected($importance, 'high'); ?>><?php esc_html_e('High Impact Only', 'tradepress'); ?></option>
                                <option value="medium" <?php selected($importance, 'medium'); ?>><?php esc_html_e('Medium & High', 'tradepress'); ?></option>
                                <option value="low" <?php selected($importance, 'low'); ?>><?php esc_html_e('Low Impact Only', 'tradepress'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="tradepress-input-row">
                        <div class="tradepress-input-group regions-filter">
                            <label><?php esc_html_e('Regions:', 'tradepress'); ?></label>
                            <div class="region-checkboxes">
                                <?php foreach ($region_names as $code => $name): ?>
                                    <label class="region-checkbox">
                                        <input type="checkbox" name="regions[]" value="<?php echo esc_attr($code); ?>" 
                                            <?php checked(in_array($code, $regions)); ?>>
                                        <span class="region-flag flag-<?php echo esc_attr($code); ?>"></span>
                                        <?php echo esc_html($name); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tradepress-action-buttons">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Apply Filters', 'tradepress'); ?></button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=economic-calendar')); ?>" class="button"><?php esc_html_e('Reset', 'tradepress'); ?></a>
                        
                        <!-- Quick date selectors -->
                        <div class="quick-date-selectors">
                            <a href="<?php echo esc_url(add_query_arg(array('start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')))); ?>" class="quick-date-link"><?php esc_html_e('Today', 'tradepress'); ?></a>
                            <a href="<?php echo esc_url(add_query_arg(array('start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+7 days'))))); ?>" class="quick-date-link"><?php esc_html_e('This Week', 'tradepress'); ?></a>
                            <a href="<?php echo esc_url(add_query_arg(array('start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days'))))); ?>" class="quick-date-link"><?php esc_html_e('Next 30 Days', 'tradepress'); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="tradepress-notification-center">
            <h3><?php esc_html_e('Trading Notifications', 'tradepress'); ?></h3>
            <div class="notification-options">
                <label>
                    <input type="checkbox" id="enable_notifications" name="enable_notifications" value="1" checked>
                    <?php esc_html_e('Enable browser notifications for high-impact events', 'tradepress'); ?>
                </label>
                
                <label>
                    <select id="notification_time" name="notification_time">
                        <option value="5"><?php esc_html_e('5 minutes before', 'tradepress'); ?></option>
                        <option value="15"><?php esc_html_e('15 minutes before', 'tradepress'); ?></option>
                        <option value="30"><?php esc_html_e('30 minutes before', 'tradepress'); ?></option>
                        <option value="60"><?php esc_html_e('1 hour before', 'tradepress'); ?></option>
                    </select>
                    <?php esc_html_e('Notification time', 'tradepress'); ?>
                </label>
                
                <button type="button" id="test_notification" class="button button-secondary">
                    <?php esc_html_e('Test Notification', 'tradepress'); ?>
                </button>
            </div>
            
            <?php 
            // Add the export calendar button
            echo tradepress_get_calendar_export_button($economic_events); 
            ?>
        </div>
        
        <div class="tradepress-research-results">
            <!-- Today's Highlights -->
            <?php
            $todays_events = tradepress_get_todays_economic_events($economic_events);
            if (!empty($todays_events)):
            ?>
            <div class="tradepress-highlights-panel">
                <h3><?php esc_html_e('Today\'s Economic Events', 'tradepress'); ?></h3>
                <div class="todays-events-grid">
                    <?php foreach ($todays_events as $event): ?>
                        <div class="event-card importance-<?php echo esc_attr($event['importance']); ?>">
                            <div class="event-time"><?php echo esc_html($event['time']); ?></div>
                            <div class="event-region">
                                <span class="region-flag flag-<?php echo esc_attr($event['region']); ?>"></span>
                                <?php echo esc_html($region_names[$event['region']]); ?>
                            </div>
                            <div class="event-title"><?php echo esc_html($event['title']); ?></div>
                            <?php if (!empty($event['forecast'])): ?>
                                <div class="event-forecast">
                                    <span class="label"><?php esc_html_e('Forecast:', 'tradepress'); ?></span>
                                    <span class="value"><?php echo esc_html($event['forecast']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($event['previous'])): ?>
                                <div class="event-previous">
                                    <span class="label"><?php esc_html_e('Previous:', 'tradepress'); ?></span>
                                    <span class="value"><?php echo esc_html($event['previous']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="event-importance-indicator"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Economic Calendar -->
            <div class="tradepress-calendar-panel">
                <h3><?php esc_html_e('Economic Calendar', 'tradepress'); ?> <span class="date-range">(<?php echo esc_html(date('M j, Y', strtotime($start_date))); ?> - <?php echo esc_html(date('M j, Y', strtotime($end_date))); ?>)</span></h3>
                
                <?php if (empty($economic_events)): ?>
                    <div class="tradepress-no-events">
                        <p><?php esc_html_e('No economic events found for the selected date range and filters.', 'tradepress'); ?></p>
                    </div>
                <?php else: ?>
                    <!-- Group events by date -->
                    <?php
                    $events_by_date = array();
                    foreach ($economic_events as $event) {
                        $date = $event['date'];
                        if (!isset($events_by_date[$date])) {
                            $events_by_date[$date] = array();
                        }
                        $events_by_date[$date][] = $event;
                    }
                    ?>
                    
                    <div class="tradepress-calendar-dates">
                        <?php foreach ($events_by_date as $date => $day_events): ?>
                            <div class="calendar-date">
                                <h4 class="date-heading">
                                    <?php echo esc_html(date('l, F j, Y', strtotime($date))); ?>
                                    <span class="event-count"><?php echo count($day_events); ?> <?php echo _n('event', 'events', count($day_events), 'tradepress'); ?></span>
                                </h4>
                                
                                <table class="events-table">
                                    <thead>
                                        <tr>
                                            <th class="column-time"><?php esc_html_e('Time', 'tradepress'); ?></th>
                                            <th class="column-region"><?php esc_html_e('Region', 'tradepress'); ?></th>
                                            <th class="column-event"><?php esc_html_e('Economic Event', 'tradepress'); ?></th>
                                            <th class="column-impact"><?php esc_html_e('Impact', 'tradepress'); ?></th>
                                            <th class="column-forecast"><?php esc_html_e('Forecast', 'tradepress'); ?></th>
                                            <th class="column-previous"><?php esc_html_e('Previous', 'tradepress'); ?></th>
                                            <?php if (strtotime($date) < current_time('timestamp')): ?>
                                                <th class="column-actual"><?php esc_html_e('Actual', 'tradepress'); ?></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($day_events as $event): ?>
                                            <tr class="event-row importance-<?php echo esc_attr($event['importance']); ?>">
                                                <td class="column-time"><?php echo esc_html($event['time']); ?></td>
                                                <td class="column-region">
                                                    <span class="region-flag flag-<?php echo esc_attr($event['region']); ?>"></span>
                                                </td>
                                                <td class="column-event">
                                                    <div class="event-title"><?php echo esc_html($event['title']); ?></div>
                                                    <?php if (!empty($event['description'])): ?>
                                                        <div class="event-description"><?php echo esc_html($event['description']); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="column-impact">
                                                    <div class="impact-indicator impact-<?php echo esc_attr($event['importance']); ?>">
                                                        <?php echo esc_html(ucfirst($event['importance'])); ?>
                                                    </div>
                                                </td>
                                                <td class="column-forecast">
                                                    <?php echo !empty($event['forecast']) ? esc_html($event['forecast']) : '-'; ?>
                                                </td>
                                                <td class="column-previous">
                                                    <?php echo !empty($event['previous']) ? esc_html($event['previous']) : '-'; ?>
                                                </td>
                                                <?php if (strtotime($date) < current_time('timestamp')): ?>
                                                    <td class="column-actual">
                                                        <?php 
                                                        if (!empty($event['actual'])) {
                                                            $actual_class = '';
                                                            if (!empty($event['forecast'])) {
                                                                $actual_numeric = preg_replace('/[^0-9.-]/', '', $event['actual']);
                                                                $forecast_numeric = preg_replace('/[^0-9.-]/', '', $event['forecast']);
                                                                
                                                                if (is_numeric($actual_numeric) && is_numeric($forecast_numeric)) {
                                                                    if ($actual_numeric > $forecast_numeric) {
                                                                        $actual_class = 'better-than-expected';
                                                                    } elseif ($actual_numeric < $forecast_numeric) {
                                                                        $actual_class = 'worse-than-expected';
                                                                    } else {
                                                                        $actual_class = 'as-expected';
                                                                    }
                                                                }
                                                            }
                                                            echo '<span class="actual-value ' . esc_attr($actual_class) . '">' . esc_html($event['actual']) . '</span>';
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Economic Events Impact Guide -->
            <div class="tradepress-guide-panel">
                <h3><?php esc_html_e('Economic Indicators Impact Guide', 'tradepress'); ?></h3>
                <div class="impact-guide-content">
                    <div class="impact-guide-section">
                        <h4><?php esc_html_e('Understanding Market Impact', 'tradepress'); ?></h4>
                        <p><?php esc_html_e('Economic events can affect markets in different ways. Here\'s how to interpret the potential impact:', 'tradepress'); ?></p>
                        
                        <div class="impact-levels">
                            <div class="impact-level high">
                                <span class="impact-bullet"></span>
                                <div class="impact-text">
                                    <strong><?php esc_html_e('High Impact', 'tradepress'); ?></strong>
                                    <p><?php esc_html_e('These events often cause significant market volatility and can affect multiple asset classes. Examples include interest rate decisions, NFP reports, GDP data, and inflation reports.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="impact-level medium">
                                <span class="impact-bullet"></span>
                                <div class="impact-text">
                                    <strong><?php esc_html_e('Medium Impact', 'tradepress'); ?></strong>
                                    <p><?php esc_html_e('These events can move markets, especially if results differ significantly from expectations. Examples include retail sales, manufacturing PMI, and consumer confidence.', 'tradepress'); ?></p>
                                </div>
                            </div>
                            
                            <div class="impact-level low">
                                <span class="impact-bullet"></span>
                                <div class="impact-text">
                                    <strong><?php esc_html_e('Low Impact', 'tradepress'); ?></strong>
                                    <p><?php esc_html_e('These events typically cause minimal market reaction unless they show extreme deviation from forecasts. They often provide background economic information.', 'tradepress'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="impact-guide-section">
                        <h4><?php esc_html_e('Key Economic Indicators and Their Market Impact', 'tradepress'); ?></h4>
                        
                        <div class="key-indicators">
                            <div class="indicator-section">
                                <h5><?php esc_html_e('Employment Data', 'tradepress'); ?></h5>
                                <ul>
                                    <li><strong><?php esc_html_e('Non-Farm Payrolls (US):', 'tradepress'); ?></strong> <?php esc_html_e('High impact - Strong numbers typically boost the USD and stock markets while potentially pressuring bonds.', 'tradepress'); ?></li>
                                    <li><strong><?php esc_html_e('Unemployment Rate:', 'tradepress'); ?></strong> <?php esc_html_e('High impact - Lower rates typically strengthen the local currency and boost equity markets.', 'tradepress'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="indicator-section">
                                <h5><?php esc_html_e('Growth Indicators', 'tradepress'); ?></h5>
                                <ul>
                                    <li><strong><?php esc_html_e('Gross Domestic Product (GDP):', 'tradepress'); ?></strong> <?php esc_html_e('High impact - Stronger growth typically strengthens the local currency and boosts stocks.', 'tradepress'); ?></li>
                                    <li><strong><?php esc_html_e('Purchasing Managers\' Index (PMI):', 'tradepress'); ?></strong> <?php esc_html_e('Medium impact - Readings above 50 indicate expansion, which is typically positive for local stocks and currency.', 'tradepress'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="indicator-section">
                                <h5><?php esc_html_e('Inflation Data', 'tradepress'); ?></h5>
                                <ul>
                                    <li><strong><?php esc_html_e('Consumer Price Index (CPI):', 'tradepress'); ?></strong> <?php esc_html_e('High impact - Higher than expected inflation can lead to interest rate hikes, affecting bonds, currencies, and stocks.', 'tradepress'); ?></li>
                                    <li><strong><?php esc_html_e('Producer Price Index (PPI):', 'tradepress'); ?></strong> <?php esc_html_e('Medium impact - Often a leading indicator for CPI, affecting interest rate expectations.', 'tradepress'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="indicator-section">
                                <h5><?php esc_html_e('Central Bank Decisions', 'tradepress'); ?></h5>
                                <ul>
                                    <li><strong><?php esc_html_e('Interest Rate Decisions:', 'tradepress'); ?></strong> <?php esc_html_e('High impact - One of the most important market movers, affecting all asset classes.', 'tradepress'); ?></li>
                                    <li><strong><?php esc_html_e('FOMC Statements:', 'tradepress'); ?></strong> <?php esc_html_e('High impact - Policy statements and forward guidance can cause significant market volatility.', 'tradepress'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="impact-guide-section">
                        <h4><?php esc_html_e('Trading Around Economic Events', 'tradepress'); ?></h4>
                        <div class="trading-tips">
                            <div class="trading-tip">
                                <strong><?php esc_html_e('Before the Event:', 'tradepress'); ?></strong>
                                <ul>
                                    <li><?php esc_html_e('Be aware of upcoming high-impact events that could affect your positions', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Consider reducing position sizes ahead of major announcements', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Research forecasts and previous results to understand market expectations', 'tradepress'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="trading-tip">
                                <strong><?php esc_html_e('During the Event:', 'tradepress'); ?></strong>
                                <ul>
                                    <li><?php esc_html_e('Be cautious about entering new positions immediately after high-impact data releases', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Expect wider spreads and lower liquidity in the immediate aftermath', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Watch for initial market reaction, followed by potential reversals', 'tradepress'); ?></li>
                                </ul>
                            </div>
                            
                            <div class="trading-tip">
                                <strong><?php esc_html_e('After the Event:', 'tradepress'); ?></strong>
                                <ul>
                                    <li><?php esc_html_e('Compare actual results with forecasts to identify potential trading opportunities', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Consider the broader economic context, not just the specific number', 'tradepress'); ?></li>
                                    <li><?php esc_html_e('Look for trends across multiple indicators for stronger trading signals', 'tradepress'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Market Reaction History -->
                    <div class="impact-guide-section">
                        <h4><?php esc_html_e('Market Reaction Analysis', 'tradepress'); ?></h4>
                        <p><?php esc_html_e('Understanding how markets typically react to economic data can help inform trading decisions:', 'tradepress'); ?></p>
                        
                        <div class="reaction-table-container">
                            <table class="reaction-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Economic Event', 'tradepress'); ?></th>
                                        <th><?php esc_html_e('Better Than Expected', 'tradepress'); ?></th>
                                        <th><?php esc_html_e('In-line With Forecast', 'tradepress'); ?></th>
                                        <th><?php esc_html_e('Worse Than Expected', 'tradepress'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong><?php esc_html_e('GDP Growth', 'tradepress'); ?></strong></td>
                                        <td>
                                            <span class="asset up"><?php esc_html_e('Equity ↑', 'tradepress'); ?></span>
                                            <span class="asset up"><?php esc_html_e('Currency ↑', 'tradepress'); ?></span>
                                            <span class="asset down"><?php esc_html_e('Bonds ↓', 'tradepress'); ?></span>
                                        </td>
                                        <td>
                                            <span class="asset neutral"><?php esc_html_e('Limited reaction', 'tradepress'); ?></span>
                                        </td>
                                        <td>
                                            <span class="asset down"><?php esc_html_e('Equity ↓', 'tradepress'); ?></span>
                                            <span class="asset down"><?php esc_html_e('Currency ↓', 'tradepress'); ?></span>
                                            <span class="asset up"><?php esc_html_e('Bonds ↑', 'tradepress'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Inflation (CPI)', 'tradepress'); ?></strong></td>
                                        <td>
                                            <span class="asset complex"><?php esc_html_e('Equity ↓↑*', 'tradepress'); ?></span>
                                            <span class="asset up"><?php esc_html_e('Currency ↑', 'tradepress'); ?></span>
                                            <span class="asset down"><?php esc_html_e('Bonds ↓', 'tradepress'); ?></span>
                                        </td>
                                        <td>
                                            <span class="asset neutral"><?php esc_html_e('Mild reaction', 'tradepress'); ?></span>
                                        </td>
                                        <td>
                                            <span class="asset complex"><?php esc_html_e('Equity ↑↓*', 'tradepress'); ?></span>
                                            <span class="asset down"><?php esc_html_e('Currency ↓', 'tradepress'); ?></span>
                                            <span class="asset up"><?php esc_html_e('Bonds ↑', 'tradepress'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('NFP', 'tradepress'); ?></strong></td>
                                        <td>
                                            <span class="asset up"><?php esc_html_e('Equity ↑', 'tradepress'); ?></span>
                                            <span class="asset up"><?php esc_html_e('Currency ↑', 'tradepress'); ?></span>
                                            <span class="asset down"><?php esc_html_e('Bonds ↓', 'tradepress'); ?></span>
                                        </td>
                                        <td>
                                            <span class="asset neutral"><?php esc_html_e('Moderate volatility', 'tradepress'); ?></span>
                                        </td>
                                        <td>
                                            <span class="asset down"><?php esc_html_e('Equity ↓', 'tradepress'); ?></span>
                                            <span class="asset down"><?php esc_html_e('Currency ↓', 'tradepress'); ?></span>
                                            <span class="asset up"><?php esc_html_e('Bonds ↑', 'tradepress'); ?></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <p class="reaction-note"><?php esc_html_e('* Inflation impact on equities depends on the current economic environment and interest rate expectations.', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Currency Impact Matrix -->
                    <div class="tradepress-panel impact-matrix">
                        <h4><?php esc_html_e('Currency Impact Matrix', 'tradepress'); ?></h4>
                        <p><?php esc_html_e('How various economic indicators typically affect major currencies', 'tradepress'); ?></p>
                        
                        <div class="currency-impact-table-container">
                            <table class="currency-impact-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Indicator', 'tradepress'); ?></th>
                                        <th>USD</th>
                                        <th>EUR</th>
                                        <th>GBP</th>
                                        <th>JPY</th>
                                        <th>AUD</th>
                                        <th>CAD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong><?php esc_html_e('Interest Rate Hike', 'tradepress'); ?></strong></td>
                                        <td class="impact-up"><?php esc_html_e('Strong ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Strong ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Strong ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Strong ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Strong ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Strong ↑', 'tradepress'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('GDP (Higher)', 'tradepress'); ?></strong></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Inflation (Higher)', 'tradepress'); ?></strong></td>
                                        <td class="impact-complex"><?php esc_html_e('Mixed ↕', 'tradepress'); ?></td>
                                        <td class="impact-complex"><?php esc_html_e('Mixed ↕', 'tradepress'); ?></td>
                                        <td class="impact-complex"><?php esc_html_e('Mixed ↕', 'tradepress'); ?></td>
                                        <td class="impact-complex"><?php esc_html_e('Mixed ↕', 'tradepress'); ?></td>
                                        <td class="impact-complex"><?php esc_html_e('Mixed ↕', 'tradepress'); ?></td>
                                        <td class="impact-complex"><?php esc_html_e('Mixed ↕', 'tradepress'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Unemployment (Lower)', 'tradepress'); ?></strong></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Retail Sales (Higher)', 'tradepress'); ?></strong></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Weak ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                        <td class="impact-up"><?php esc_html_e('Moderate ↑', 'tradepress'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
}

/**
 * Get a list of supported economic regions
 *
 * @return array Associative array of region codes and names
 */
function tradepress_get_economic_regions() {
    return array(
        'us' => __('United States', 'tradepress'),
        'eu' => __('Eurozone', 'tradepress'),
        'uk' => __('United Kingdom', 'tradepress'),
        'jp' => __('Japan', 'tradepress'),
        'ca' => __('Canada', 'tradepress'),
        'au' => __('Australia', 'tradepress')
    );
}

/**
 * Get demo economic events for the specified date range and filters
 *
 * @param string $start_date Start date (Y-m-d format)
 * @param string $end_date End date (Y-m-d format)
 * @param string $importance Importance filter (all, high, medium, low)
 * @param array $regions Array of region codes to include
 * @return array Array of economic events
 */
function tradepress_get_demo_economic_events($start_date, $end_date, $importance, $regions) {
    // Convert dates to timestamps for comparison
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    
    // Sample demo data - this would be replaced with API data in production
    $demo_events = array(
        // Today's events
        array(
            'date' => date('Y-m-d'),
            'time' => '08:30 AM',
            'title' => 'Non-Farm Payrolls',
            'description' => 'Monthly change in employment excluding the farming sector',
            'region' => 'us',
            'importance' => 'high',
            'forecast' => '+180K',
            'previous' => '+175K',
            'actual' => ''
        ),
        array(
            'date' => date('Y-m-d'),
            'time' => '08:30 AM',
            'title' => 'Unemployment Rate',
            'description' => 'Percentage of total workforce unemployed and actively seeking employment',
            'region' => 'us',
            'importance' => 'high',
            'forecast' => '3.8%',
            'previous' => '3.9%',
            'actual' => ''
        ),
        array(
            'date' => date('Y-m-d'),
            'time' => '10:00 AM',
            'title' => 'ISM Manufacturing PMI',
            'description' => 'Index based on surveyed purchasing managers in the manufacturing industry',
            'region' => 'us',
            'importance' => 'medium',
            'forecast' => '52.3',
            'previous' => '51.9',
            'actual' => ''
        ),
        
        // Tomorrow's events
        array(
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '10:00 AM',
            'title' => 'Factory Orders',
            'description' => 'Change in the total value of new purchase orders placed with manufacturers',
            'region' => 'us',
            'importance' => 'medium',
            'forecast' => '0.5%',
            'previous' => '0.3%',
            'actual' => ''
        ),
        array(
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time' => '04:30 AM',
            'title' => 'Construction PMI',
            'description' => 'Level of a diffusion index based on surveyed purchasing managers in the construction industry',
            'region' => 'uk',
            'importance' => 'medium',
            'forecast' => '49.5',
            'previous' => '49.2',
            'actual' => ''
        ),
        
        // Two days from now
        array(
            'date' => date('Y-m-d', strtotime('+2 days')),
            'time' => '07:45 AM',
            'title' => 'ECB Interest Rate Decision',
            'description' => 'ECB interest rate decision and policy statement',
            'region' => 'eu',
            'importance' => 'high',
            'forecast' => '3.75%',
            'previous' => '3.75%',
            'actual' => ''
        ),
        array(
            'date' => date('Y-m-d', strtotime('+2 days')),
            'time' => '08:30 AM',
            'title' => 'ECB Press Conference',
            'description' => 'ECB President discusses monetary policy decisions and economic outlook',
            'region' => 'eu',
            'importance' => 'high',
            'forecast' => '',
            'previous' => '',
            'actual' => ''
        ),
        
        // Three days from now
        array(
            'date' => date('Y-m-d', strtotime('+3 days')),
            'time' => '02:00 AM',
            'title' => 'Bank of Japan Minutes',
            'description' => 'Minutes from the last BoJ policy meeting',
            'region' => 'jp',
            'importance' => 'medium',
            'forecast' => '',
            'previous' => '',
            'actual' => ''
        ),
        
        // Four days from now
        array(
            'date' => date('Y-m-d', strtotime('+4 days')),
            'time' => '08:30 AM',
            'title' => 'CPI m/m',
            'description' => 'Change in the price of goods and services purchased by consumers',
            'region' => 'us',
            'importance' => 'high',
            'forecast' => '0.2%',
            'previous' => '0.3%',
            'actual' => ''
        ),
        array(
            'date' => date('Y-m-d', strtotime('+4 days')),
            'time' => '08:30 AM',
            'title' => 'Core CPI m/m',
            'description' => 'Change in the price of goods and services purchased by consumers, excluding food and energy',
            'region' => 'us',
            'importance' => 'high',
            'forecast' => '0.2%',
            'previous' => '0.2%',
            'actual' => ''
        ),
        
        // Five days from now
        array(
            'date' => date('Y-m-d', strtotime('+5 days')),
            'time' => '10:00 AM',
            'title' => 'Retail Sales m/m',
            'description' => 'Change in the total value of sales at the retail level',
            'region' => 'ca',
            'importance' => 'medium',
            'forecast' => '0.4%',
            'previous' => '0.2%',
            'actual' => ''
        ),
        
        // Six days from now
        array(
            'date' => date('Y-m-d', strtotime('+6 days')),
            'time' => '08:30 AM',
            'title' => 'Unemployment Rate',
            'description' => 'Percentage of total workforce unemployed and actively seeking employment',
            'region' => 'au',
            'importance' => 'high',
            'forecast' => '4.0%',
            'previous' => '4.1%',
            'actual' => ''
        ),
        
        // Past events for demo purposes (with actual values)
        array(
            'date' => date('Y-m-d', strtotime('-1 day')),
            'time' => '08:30 AM',
            'title' => 'GDP q/q',
            'description' => 'Quarter-over-quarter change in the inflation-adjusted value of all goods and services produced by the economy',
            'region' => 'us',
            'importance' => 'high',
            'forecast' => '2.6%',
            'previous' => '2.4%',
            'actual' => '2.8%',
        ),
        array(
            'date' => date('Y-m-d', strtotime('-1 day')),
            'time' => '10:00 AM',
            'title' => 'Consumer Confidence',
            'description' => 'Level of a composite index based on surveyed consumers',
            'region' => 'us',
            'importance' => 'medium',
            'forecast' => '101.0',
            'previous' => '99.8',
            'actual' => '97.5',
        ),
        array(
            'date' => date('Y-m-d', strtotime('-2 days')),
            'time' => '04:30 AM',
            'title' => 'Manufacturing PMI',
            'description' => 'Level of a diffusion index based on surveyed purchasing managers in the manufacturing industry',
            'region' => 'uk',
            'importance' => 'medium',
            'forecast' => '51.5',
            'previous' => '51.1',
            'actual' => '51.4',
        )
    );
    
    // Filter events based on date range, importance and regions
    $filtered_events = array();
    
    foreach ($demo_events as $event) {
        $event_date = strtotime($event['date']);
        
        // Check if event is within date range
        if ($event_date >= $start_timestamp && $event_date <= $end_timestamp) {
            // Check importance filter
            if ($importance === 'all' || 
                ($importance === 'high' && $event['importance'] === 'high') ||
                ($importance === 'medium' && in_array($event['importance'], array('high', 'medium'))) ||
                ($importance === 'low' && $event['importance'] === 'low')) {
                
                // Check region filter
                if (in_array($event['region'], $regions)) {
                    $filtered_events[] = $event;
                }
            }
        }
    }
    
    // Sort events by date and time
    usort($filtered_events, function($a, $b) {
        $date_a = strtotime($a['date'] . ' ' . $a['time']);
        $date_b = strtotime($b['date'] . ' ' . $b['time']);
        return $date_a - $date_b;
    });
    
    return $filtered_events;
}

/**
 * Get today's economic events from the events array
 *
 * @param array $events Array of economic events
 * @return array Array of today's events
 */
function tradepress_get_todays_economic_events($events) {
    $today = date('Y-m-d');
    $todays_events = array();
    
    foreach ($events as $event) {
        if ($event['date'] === $today) {
            $todays_events[] = $event;
        }
    }
    
    // Sort by time
    usort($todays_events, function($a, $b) {
        return strtotime($a['time']) - strtotime($b['time']);
    });
    
    return $todays_events;
}

/**
 * Helper function for current timestamp
 * 
 * @return int Current timestamp
 */
function current_timestamp() {
    return current_time('timestamp');
}

/**
 * Get a calendar export button that generates an .ics file with upcoming economic events
 * 
 * @param array $events Array of economic events to export
 * @return string HTML for the export button
 */
function tradepress_get_calendar_export_button($events) {
    // Only show export button if we have events
    if (empty($events)) {
        return '';
    }
    
    // Generate a unique ID for this export
    $export_id = 'econ_cal_' . wp_create_nonce('tradepress_calendar_export');
    
    // Start building the iCalendar data
    $ical_data = "BEGIN:VCALENDAR\r\n";
    $ical_data .= "VERSION:2.0\r\n";
    $ical_data .= "PRODID:-//TradePress//Economic Calendar//EN\r\n";
    $ical_data .= "CALSCALE:GREGORIAN\r\n";
    $ical_data .= "METHOD:PUBLISH\r\n";
    $ical_data .= "X-WR-CALNAME:TradePress Economic Calendar\r\n";
    $ical_data .= "X-WR-TIMEZONE:UTC\r\n";
    
    // Add each event to the calendar
    foreach ($events as $event) {
        $event_date = strtotime($event['date'] . ' ' . $event['time']);
        $end_date = strtotime('+1 hour', $event_date); // Default 1 hour duration
        
        $ical_data .= "BEGIN:VEVENT\r\n";
        $ical_data .= "UID:" . md5($event['date'] . $event['time'] . $event['title']) . "@tradepress.com\r\n";
        $ical_data .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical_data .= "DTSTART:" . gmdate('Ymd\THis\Z', $event_date) . "\r\n";
        $ical_data .= "DTEND:" . gmdate('Ymd\THis\Z', $end_date) . "\r\n";
        
        // Add importance level to summary
        $importance_marker = '';
        if ($event['importance'] === 'high') {
            $importance_marker = '[!!!] ';
        } elseif ($event['importance'] === 'medium') {
            $importance_marker = '[!!] ';
        } elseif ($event['importance'] === 'low') {
            $importance_marker = '[!] ';
        }
        
        $summary = $importance_marker . $event['title'] . ' (' . strtoupper($event['region']) . ')';
        $ical_data .= "SUMMARY:" . $summary . "\r\n";
        
        // Add description with forecast and previous values if available
        $description = '';
        if (!empty($event['description'])) {
            $description .= $event['description'] . '\n\n';
        }
        if (!empty($event['forecast'])) {
            $description .= 'Forecast: ' . $event['forecast'] . '\n';
        }
        if (!empty($event['previous'])) {
            $description .= 'Previous: ' . $event['previous'] . '\n';
        }
        
        $ical_data .= "DESCRIPTION:" . $description . "\r\n";
        $ical_data .= "END:VEVENT\r\n";
    }
    
    $ical_data .= "END:VCALENDAR";
    
    // Base64 encode the calendar data for the href
    $encoded_cal = base64_encode($ical_data);
    
    // Return the export button HTML
    $button = '<div class="calendar-export-button">';
    $button .= '<a href="data:text/calendar;charset=utf8;base64,' . $encoded_cal . '" download="tradepress_economic_calendar.ics" class="button">';
    $button .= '<span class="dashicons dashicons-calendar-alt"></span> ' . __('Export to Calendar (.ics)', 'tradepress');
    $button .= '</a>';
    $button .= '</div>';
    
    return $button;
}
?>
