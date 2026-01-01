<?php
/**
 * The admin-specific Price Forecast tab template.
 *
 * @link       https://example.com
 * @since      1.0.0
 * @package    TradePress
 * @subpackage TradePress/admin/partials
 * @created    2025-04-14 22:07
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Check if demo mode is active
$is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;

// Display demo indicator warning only if in demo mode
if ($is_demo): ?>
<div class="demo-indicator">
    <div class="demo-icon dashicons dashicons-info-outline"></div>
    <div class="demo-text">
        <h4><?php esc_html_e('Demo Mode Active', 'tradepress'); ?></h4>
        <p><?php esc_html_e('The price forecast data displayed on this page is for demonstration purposes only and does not represent live market data.', 'tradepress'); ?></p>
    </div>
    <span class="demo-badge"><?php esc_html_e('DEMO', 'tradepress'); ?></span>
</div>
<?php endif;

// Check if a specific symbol was requested for detailed view
$symbol = isset($_GET['symbol']) ? sanitize_text_field($_GET['symbol']) : '';

if (!empty($symbol)) { 
    // Display detailed view for the selected symbol
    $this->display_symbol_details($symbol);
} else { 
    // Display the real table
    ?>
    <div class="price-forecast-container">
        <?php
        // Check if the class exists - with more detailed error message
        if (!class_exists('TradePress_Price_Forecast_Table')) {
            echo '<div class="notice notice-error"><p>' . 
                 __('Error: The TradePress_Price_Forecast_Table class is not available.', 'tradepress') . 
                 ' Class file should be located at: ' . TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/price-forecast-table.php' .
                 '</p></div>';
        } else {
            // Create form for table
            ?>
            <form id="price-forecasts-filter" method="get">
                <input type="hidden" name="page" value="tradepress_research" />
                <input type="hidden" name="tab" value="price_forecast" />
                
                <?php
                // Create an instance of the table
                $forecasts_table = new TradePress_Price_Forecast_Table();
                
                // Prepare the table items
                $forecasts_table->prepare_items();
                
                // Display search box first, before the table
                $forecasts_table->search_box(__('Search Symbols', 'tradepress'), 'price-forecast-search');
                
                // Add a clear div to ensure proper spacing and layout
                echo '<div style="clear: both; margin-bottom: 10px;"></div>';
                
                // Display top navigation first
                echo '<div class="tablenav top">';
                $forecasts_table->display_tablenav('top');
                echo '</div>';
                
                // Define column headers (matching the get_columns method in the table class)
                $columns = array(
                    'symbol'      => __('Symbol', 'tradepress'),
                    'name'        => __('Company Name', 'tradepress'),
                    'current'     => __('Current Price', 'tradepress'),
                    'forecast_1m' => __('1 Month', 'tradepress'),
                    'forecast_3m' => __('3 Month', 'tradepress'),
                    'forecast_6m' => __('6 Month', 'tradepress'),
                    'forecast_1y' => __('1 Year', 'tradepress'),
                    'confidence'  => __('Confidence', 'tradepress'),
                    'updated'     => __('Last Updated', 'tradepress'),
                );
                
                // Start the table with proper class
                echo '<table class="wp-list-table widefat fixed striped forecasts">';
                
                // Table header
                echo '<thead>';
                echo '<tr>';
                foreach ($columns as $column_key => $column_display_name) {
                    echo '<th scope="col" class="manage-column column-' . esc_attr($column_key) . '">' . esc_html($column_display_name) . '</th>';
                }
                echo '</tr>';
                echo '</thead>';
                
                // Table body
                echo '<tbody id="the-list">';
                if (count($forecasts_table->items) > 0) {
                    // If the built-in display_rows() method isn't working properly, 
                    // let's manually render the rows
                    
                    foreach ($forecasts_table->items as $item) {
                        echo '<tr>';
                        foreach ($columns as $column_name => $column_display_name) {
                            echo '<td class="column-' . esc_attr($column_name) . '">';
                            
                            if ($column_name === 'symbol') {
                                // Special rendering for symbol column with actions
                                $symbol = $item['symbol'];
                                $actions = array(
                                    'view' => sprintf(
                                        '<a href="%s">%s</a>',
                                        esc_url(add_query_arg(array('symbol' => $symbol), admin_url('admin.php?page=tradepress_research&tab=price_forecast'))),
                                        __('View Details', 'tradepress')
                                    ),
                                    'refresh' => sprintf(
                                        '<a href="%s">%s</a>',
                                        esc_url(add_query_arg(array('action' => 'refresh_forecast', 'symbol' => $symbol), admin_url('admin.php?page=tradepress_research&tab=price_forecast'))),
                                        __('Refresh', 'tradepress')
                                    ),
                                );
                                
                                $action_links = '';
                                foreach ($actions as $action => $link) {
                                    $action_links .= '<span class="' . esc_attr($action) . '">' . $link . '</span> | ';
                                }
                                $action_links = rtrim($action_links, ' | ');
                                
                                echo '<strong><a href="' . esc_url(add_query_arg(array('symbol' => $symbol), admin_url('admin.php?page=tradepress_research&tab=price_forecast'))) . '">' . esc_html($symbol) . '</a></strong>';
                                echo '<div class="row-actions">' . $action_links . '</div>';
                            } 
                            elseif ($column_name === 'current') {
                                echo '$' . number_format($item[$column_name], 2);
                            }
                            elseif (in_array($column_name, array('forecast_1m', 'forecast_3m', 'forecast_6m', 'forecast_1y'))) {
                                $forecast = $item[$column_name];
                                $confidence = $forecast['confidence'];
                                $normalized = max(0, min(100, $confidence)) / 100;
                                
                                // Calculate color for confidence
                                if ($normalized < 0.5) {
                                    // Red to yellow
                                    $r = 255;
                                    $g = round(255 * ($normalized * 2));
                                    $b = 0;
                                } else {
                                    // Yellow to green
                                    $r = round(255 * (1 - ($normalized - 0.5) * 2));
                                    $g = 255;
                                    $b = 0;
                                }
                                
                                $style = sprintf(
                                    'background-color: rgba(%d, %d, %d, 0.3); padding: 3px 8px; border-radius: 4px; display: inline-block;',
                                    $r, $g, $b
                                );
                                
                                echo '<span style="' . $style . '">$' . number_format($forecast['price'], 2) . '</span>';
                            }
                            elseif ($column_name === 'confidence') {
                                $confidence = $item[$column_name];
                                $normalized = max(0, min(100, $confidence)) / 100;
                                
                                // Calculate color for confidence
                                if ($normalized < 0.5) {
                                    // Red to yellow
                                    $r = 255;
                                    $g = round(255 * ($normalized * 2));
                                    $b = 0;
                                } else {
                                    // Yellow to green
                                    $r = round(255 * (1 - ($normalized - 0.5) * 2));
                                    $g = 255;
                                    $b = 0;
                                }
                                
                                $style = sprintf(
                                    'background-color: rgba(%d, %d, %d, 0.3); padding: 3px 8px; border-radius: 4px; display: inline-block;',
                                    $r, $g, $b
                                );
                                
                                echo '<span style="' . $style . '">' . number_format($confidence, 1) . '%</span>';
                            }
                            elseif ($column_name === 'updated') {
                                echo human_time_diff(strtotime($item[$column_name]), current_time('timestamp')) . ' ago';
                            }
                            else {
                                echo esc_html($item[$column_name]);
                            }
                            
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                } else {
                    echo '<tr class="no-items"><td class="colspanchange" colspan="' . count($columns) . '">';
                    $forecasts_table->no_items();
                    echo '</td></tr>';
                }
                echo '</tbody>';
                
                // Table footer
                echo '<tfoot>';
                echo '<tr>';
                foreach ($columns as $column_key => $column_display_name) {
                    echo '<th scope="col" class="manage-column column-' . esc_attr($column_key) . '">' . esc_html($column_display_name) . '</th>';
                }
                echo '</tr>';
                echo '</tfoot>';
                
                echo '</table>';
                
                // Display bottom navigation last
                echo '<div class="tablenav bottom">';
                $forecasts_table->display_tablenav('bottom');
                echo '</div>';
                ?>
            </form>
            <?php
        }
        ?>
    </div>
    <?php
}
?>