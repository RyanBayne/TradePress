<?php
/**
 * TradePress Data Elements Tab
 *
 * Helps the developer to establish if the necessary data elements are configured correctly.
 *
 * Displays all data groups required by the plugin, their uses, database tables,
 * data sources, and import status. Provides manual import functionality.
 *
 * IMPORT SYSTEM ARCHITECTURE:
 * - Import handlers are located in /import/ directory
 * - Each data element can have import classes in /import/sources/
 * - Main import classes: prediction-manager.php, prediction-scraper.php
 * - Readiness is determined by: API configuration, database tables, and import handlers
 * 
 * READINESS LOGIC:
 * - Green (Ready): All required components configured and functional
 * - Red (Not Ready): Missing API keys, database tables, or import handlers
 *
 * @package TradePress
 * @subpackage admin/page/Data
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the Data Elements tab content
 */
function tradepress_data_elements_tab() {
    // Get data elements configuration
    $data_elements = tradepress_get_data_elements_config();
    
    ?>
    <div class="tradepress-data-elements-container">

        <div class="data-elements-controls">
            <button id="refresh-status" class="button"><?php esc_html_e('Refresh Status', 'tradepress'); ?></button>
            <button id="run-all-imports" class="button button-primary"><?php esc_html_e('Run All Imports', 'tradepress'); ?></button>
        </div>

        <div class="data-elements-table-wrapper">
            <table class="widefat data-elements-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Data Element', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Ready', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Validation', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Uses', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Quality', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Database Table', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Primary Source', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Frequency', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Last Import', 'tradepress'); ?></th>
                        <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_elements as $element_id => $element): ?>
                        <tr data-element="<?php echo esc_attr($element_id); ?>">
                            <td class="element-name">
                                <strong><?php echo esc_html($element['name']); ?></strong>
                                <div class="element-description"><?php echo esc_html($element['description']); ?></div>
                            </td>
                            <td class="element-readiness">
                                <span class="readiness-indicator" data-element="<?php echo esc_attr($element_id); ?>">
                                    <?php echo tradepress_get_data_element_readiness($element_id); ?>
                                </span>
                            </td>
                            <td class="element-validation">
                                <button class="button button-small validate-element" data-element="<?php echo esc_attr($element_id); ?>">
                                    <?php esc_html_e('Validate', 'tradepress'); ?>
                                </button>
                            </td>
                            <td class="element-uses">
                                <?php foreach ($element['uses'] as $use): ?>
                                    <span class="use-tag"><?php echo esc_html($use); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td class="element-quality">
                                <span class="quality-badge quality-<?php echo esc_attr(strtolower($element['data_quality'] ?? 'medium')); ?>">
                                    <?php echo esc_html($element['data_quality'] ?? 'Medium'); ?>
                                </span>
                            </td>
                            <td class="element-table"><?php echo esc_html($element['database_table']); ?></td>
                            <td class="element-primary-source"><?php echo esc_html($element['primary_source']); ?></td>
                            <td class="element-frequency"><?php echo esc_html($element['frequency']); ?></td>
                            <td class="element-last-import">
                                <span class="import-time" data-element="<?php echo esc_attr($element_id); ?>">
                                    <?php echo esc_html(tradepress_get_last_import_time($element_id)); ?>
                                </span>
                            </td>
                            <td class="element-status">
                                <span class="status-indicator" data-element="<?php echo esc_attr($element_id); ?>">
                                    <?php echo tradepress_get_data_element_status($element_id); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Get data elements configuration
 * This will be expanded as we identify all required data elements
 */
function tradepress_get_data_elements_config() {
    return array(
        // MARKET DATA ELEMENTS
        'stock_prices' => array(
            'name' => 'Stock Prices (OHLCV)',
            'description' => 'Real-time and historical OHLC price data with volume',
            'uses' => array('Price Action Scoring', 'Technical Indicators', 'Charts'),
            'database_table' => 'tradepress_stock_prices',
            'primary_source' => 'Alpha Vantage',
            'secondary_source' => 'Polygon',
            'frequency' => 'Real-time / 1 minute',
            'data_quality' => 'Critical'
        ),
        'volume_data' => array(
            'name' => 'Trading Volume',
            'description' => 'Current and average trading volume for volume analysis scoring',
            'uses' => array('Volume Scoring Directive', 'OBV Calculation'),
            'database_table' => 'tradepress_volume_data',
            'primary_source' => 'Alpha Vantage',
            'secondary_source' => 'IEX Cloud',
            'frequency' => 'Real-time / 1 minute',
            'data_quality' => 'High'
        ),
        'technical_indicators' => array(
            'name' => 'Technical Indicators',
            'description' => 'RSI, MACD, Moving Averages, Bollinger Bands',
            'uses' => array('RSI Scoring', 'Price Action Scoring', 'Technical Analysis'),
            'database_table' => 'tradepress_technical_indicators',
            'primary_source' => 'Calculated',
            'secondary_source' => 'Alpha Vantage',
            'frequency' => 'Real-time / 5 minutes',
            'data_quality' => 'High'
        ),
        'earnings_calendar' => array(
            'name' => 'Earnings Calendar',
            'description' => 'Upcoming earnings announcement dates and estimates',
            'uses' => array('Earnings Strategy', 'Earnings Whisper Scoring', 'Research Tab'),
            'database_table' => 'tradepress_earnings',
            'primary_source' => 'Alpha Vantage',
            'secondary_source' => 'Financial Modeling Prep',
            'frequency' => 'Daily',
            'data_quality' => 'Critical'
        ),
        'earnings_whispers' => array(
            'name' => 'Earnings Whisper Data',
            'description' => 'Whisper numbers, sentiment, and earnings analysis data',
            'uses' => array('Earnings Whisper Scorer', 'Trading Signals'),
            'database_table' => 'tradepress_earnings_whispers',
            'primary_source' => 'Manual Input',
            'secondary_source' => 'Scraped Data',
            'frequency' => 'As Available',
            'data_quality' => 'High'
        ),
        'company_fundamentals' => array(
            'name' => 'Company Fundamentals',
            'description' => 'Financial statements, ratios, and company metrics',
            'uses' => array('Fundamental Analysis', 'Company Scoring'),
            'database_table' => 'tradepress_fundamentals',
            'primary_source' => 'Financial Modeling Prep',
            'secondary_source' => 'Alpha Vantage',
            'frequency' => 'Weekly',
            'data_quality' => 'High'
        ),
        'portfolio_positions' => array(
            'name' => 'Portfolio Positions',
            'description' => 'Current holdings, quantities, costs, and market values',
            'uses' => array('Portfolio Management', 'Risk Management', 'Trading Decisions'),
            'database_table' => 'tradepress_portfolio',
            'primary_source' => 'Trading Platform APIs',
            'secondary_source' => 'Manual Entry',
            'frequency' => 'Real-time',
            'data_quality' => 'Critical'
        ),
        'trade_history' => array(
            'name' => 'Trade History',
            'description' => 'Historical trades, executions, and performance data',
            'uses' => array('Performance Analysis', 'Trading Algorithm', 'Portfolio Tracking'),
            'database_table' => 'tradepress_trades',
            'primary_source' => 'Trading Platform APIs',
            'secondary_source' => 'Manual Entry',
            'frequency' => 'Real-time',
            'data_quality' => 'Critical'
        ),
        'algorithm_runs' => array(
            'name' => 'Algorithm Run Data',
            'description' => 'Trading algorithm execution history and statistics',
            'uses' => array('Algorithm Performance', 'Automation Monitoring', 'System Analytics'),
            'database_table' => 'tradepress_algorithm_runs',
            'primary_source' => 'Internal System',
            'secondary_source' => null,
            'frequency' => 'Per Run',
            'data_quality' => 'High'
        ),
        'symbol_scores' => array(
            'name' => 'Symbol Scoring Data',
            'description' => 'Calculated scores and scoring component breakdowns',
            'uses' => array('Trading Algorithm', 'Symbol Ranking', 'Decision Making'),
            'database_table' => 'tradepress_symbol_scores',
            'primary_source' => 'Internal Calculation',
            'secondary_source' => null,
            'frequency' => 'Per Algorithm Run',
            'data_quality' => 'High'
        ),
        'watchlists' => array(
            'name' => 'User Watchlists',
            'description' => 'Custom symbol lists and tracking preferences',
            'uses' => array('Symbol Organization', 'Active Symbols Tracking', 'User Preferences'),
            'database_table' => 'tradepress_watchlists',
            'primary_source' => 'User Input',
            'secondary_source' => 'System Generated',
            'frequency' => 'On Demand',
            'data_quality' => 'Medium'
        ),
        'active_symbols' => array(
            'name' => 'Active Symbols Tracking',
            'description' => 'Recently researched, scored, or traded symbols',
            'uses' => array('Active Symbols Tab', 'Quick Access', 'Recent Activity'),
            'database_table' => 'tradepress_active_symbols',
            'primary_source' => 'System Tracking',
            'secondary_source' => null,
            'frequency' => 'Real-time',
            'data_quality' => 'Medium'
        ),
        'automation_metrics' => array(
            'name' => 'Automation Performance Metrics',
            'description' => 'Runtime statistics, processing counts, and system health',
            'uses' => array('Automation Dashboard', 'System Monitoring', 'Performance Analysis'),
            'database_table' => 'tradepress_automation_metrics',
            'primary_source' => 'Internal System',
            'secondary_source' => null,
            'frequency' => 'Real-time',
            'data_quality' => 'High'
        ),
        'discord_signals' => array(
            'name' => 'Discord Trading Signals',
            'description' => 'Parsed trading signals from Discord servers',
            'uses' => array('Signal Processing', 'Stock VIP Inbox'),
            'database_table' => 'TRADEPRESS_DISCORD_signals',
            'primary_source' => 'Discord API',
            'secondary_source' => 'Manual Input',
            'frequency' => 'Real-time',
            'data_quality' => 'Medium'
        )
    );
}

/**
 * Get last import time for a data element
 */
function tradepress_get_last_import_time($element_id) {
    $last_import = get_option("tradepress_last_import_{$element_id}", false);
    if ($last_import) {
        return date('Y-m-d H:i:s', $last_import);
    }
    return 'Never';
}

/**
 * Get status indicator for a data element
 */
function tradepress_get_data_element_status($element_id) {
    $last_import = get_option("tradepress_last_import_{$element_id}", false);
    $config = tradepress_get_data_elements_config();
    
    if (!$last_import) {
        return '<span class="status-error">Never Imported</span>';
    }
    
    $element = $config[$element_id] ?? null;
    if (!$element) {
        return '<span class="status-error">Unknown Element</span>';
    }
    
    // Calculate if data is stale based on frequency
    $frequency_seconds = tradepress_frequency_to_seconds($element['frequency']);
    $time_since_import = time() - $last_import;
    
    if ($time_since_import > $frequency_seconds * 2) {
        return '<span class="status-warning">Stale Data</span>';
    } elseif ($time_since_import > $frequency_seconds) {
        return '<span class="status-caution">Due for Update</span>';
    } else {
        return '<span class="status-success">Up to Date</span>';
    }
}

/**
 * Get readiness indicator for a data element
 * Determines if all required components are configured and functional
 */
function tradepress_get_data_element_readiness($element_id) {
    $config = tradepress_get_data_elements_config();
    $element = $config[$element_id] ?? null;
    
    if (!$element) {
        return '<span class="readiness-not-ready">●</span>';
    }
    
    $is_ready = true;
    $reasons = array();
    
    // Check if database table exists
    global $wpdb;
    $table_name = $wpdb->prefix . str_replace('tradepress_', '', $element['database_table']);
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    
    if (!$table_exists) {
        $is_ready = false;
        $reasons[] = 'Database table missing';
    }
    
    // Check API configuration based on primary source
    $source = strtolower($element['primary_source']);
    if (strpos($source, 'alpha vantage') !== false) {
        $api_key = get_option('tradepress_alpha_vantage_api_key', '');
        if (empty($api_key)) {
            $is_ready = false;
            $reasons[] = 'Alpha Vantage API key missing';
        }
    } elseif (strpos($source, 'polygon') !== false) {
        $api_key = get_option('tradepress_polygon_api_key', '');
        if (empty($api_key)) {
            $is_ready = false;
            $reasons[] = 'Polygon API key missing';
        }
    } elseif (strpos($source, 'discord') !== false) {
        $bot_token = get_option('TRADEPRESS_DISCORD_bot_token', '');
        if (empty($bot_token)) {
            $is_ready = false;
            $reasons[] = 'Discord bot token missing';
        }
    }
    
    // Check if import handler exists for external sources
    if (!in_array($source, array('internal system', 'internal calculation', 'user input', 'system tracking', 'calculated', 'system generated'))) {
        $import_file = TRADEPRESS_PLUGIN_DIR_PATH . 'import/sources/' . str_replace('_', '-', $element_id) . '-importer.php';
        if (!file_exists($import_file)) {
            $is_ready = false;
            $reasons[] = 'Import handler missing';
        }
    }
    
    $title = $is_ready ? 'Ready for import' : 'Not ready: ' . implode(', ', $reasons);
    $class = $is_ready ? 'readiness-ready' : 'readiness-not-ready';
    
    return '<span class="' . $class . '" title="' . esc_attr($title) . '">●</span>';
}

/**
 * Convert frequency string to seconds
 */
function tradepress_frequency_to_seconds($frequency) {
    $frequency = strtolower($frequency);
    
    if (strpos($frequency, 'real-time') !== false || strpos($frequency, 'minute') !== false) {
        return 300; // 5 minutes tolerance for real-time data
    } elseif (strpos($frequency, 'hourly') !== false) {
        return 3600;
    } elseif (strpos($frequency, 'daily') !== false) {
        return 86400;
    } elseif (strpos($frequency, 'weekly') !== false) {
        return 604800;
    }
    
    return 3600; // Default to 1 hour
}
?>