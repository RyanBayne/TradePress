<?php
/**
 * Shortcodes for TradePress Posts
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the shortcode for the symbol view.
 */
function tradepress_register_symbol_view_shortcode() {
    add_shortcode('tradepress_symbol_view', 'tradepress_symbol_view_shortcode_handler');
}
add_action('init', 'tradepress_register_symbol_view_shortcode');

/**
 * The handler function for the [tradepress_symbol_view] shortcode.
 *
 * @return string The HTML content for the symbol view.
 */
function tradepress_symbol_view_shortcode_handler() {
    ob_start();

    // Original logic from symbol-template.php
    
    // Load required classes
    require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbols.php';

    // Get basic post data
    $symbol_id = get_the_ID();
    $ticker = get_post_field('post_name', $symbol_id);
    $symbol_title = get_the_title();
    $symbol_content = apply_filters('the_content', get_the_content());

    // Load the symbol data from our custom tables
    $symbol_object = TradePress_Symbols::get_symbol($ticker);

    // If symbol doesn't exist in the database, create it
    if (!$symbol_object) {
        // This will create from post if possible
        $symbol_object = TradePress_Symbols::get_symbol($symbol_id, 'post_id');
    }

    // Check if we need to update data from API
    $last_updated = get_post_meta($symbol_id, '_tradepress_data_last_updated', true);
    $update_threshold = 3600; // 1 hour in seconds

    if (!$last_updated || (time() - $last_updated > $update_threshold)) {
        // Update from API
        if ($symbol_object) {
            $symbol_object->update_from_api();
        }
    }

    // Get last update time
    $last_updated = get_post_meta($symbol_id, '_tradepress_data_last_updated', true);
    $last_updated_display = !empty($last_updated) ? date('F j, Y, g:i a', $last_updated) : date('F j, Y, g:i a', get_post_modified_time('U', true));
    $time_since_update = human_time_diff(strtotime($last_updated_display), current_time('timestamp'));

    // Get complete symbol data
    $complete_data = $symbol_object ? $symbol_object->get_complete_data(true) : array();

    ?>
    <div class="tradepress-symbol-container">
        <div class="tradepress-local-data-section">
            <h2 class="section-title"><?php esc_html_e('Symbol Data', 'tradepress'); ?></h2>
            <div class="data-last-updated">
                <span class="update-indicator <?php echo ($time_since_update > '24 hours') ? 'stale' : 'fresh'; ?>">
                    <?php printf(esc_html__('Last updated: %s ago', 'tradepress'), esc_html($time_since_update)); ?>
                </span>
                <?php if ($symbol_object): ?>
                    <a href="?update_symbol=1" class="button button-small update-symbol">
                        <?php esc_html_e('Update Now', 'tradepress'); ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="symbol-meta">
                <?php if ($symbol_object && !empty($complete_data)): ?>
                    <div class="symbol-ticker">
                        <span class="ticker-label"><?php esc_html_e('Ticker:', 'tradepress'); ?></span>
                        <span class="ticker-value"><?php echo esc_html($symbol_object->get_symbol()); ?></span>
                    </div>
                    
                    <?php if (!empty($complete_data['exchange'])): ?>
                        <div class="symbol-exchange">
                            <span class="exchange-label"><?php esc_html_e('Exchange:', 'tradepress'); ?></span>
                            <span class="exchange-value"><?php echo esc_html($complete_data['exchange']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($complete_data['current_price'])): ?>
                        <div class="symbol-price">
                            <span class="price-label"><?php esc_html_e('Current Price:', 'tradepress'); ?></span>
                            <span class="price-value"><?php echo esc_html(number_format($complete_data['current_price'], 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($complete_data['post_meta']['price_change']) && !empty($complete_data['post_meta']['price_change_pct'])): ?>
                        <?php 
                            $change = $complete_data['post_meta']['price_change'];
                            $change_pct = $complete_data['post_meta']['price_change_pct'];
                            $is_positive = (float)$change >= 0;
                        ?>
                        <div class="symbol-change <?php echo $is_positive ? 'positive' : 'negative'; ?>">
                            <span class="change-label"><?php esc_html_e('Change:', 'tradepress'); ?></span>
                            <span class="change-value">
                                <?php echo esc_html($change); ?> (<?php echo esc_html($change_pct); ?>%)
                            </span>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="symbol-ticker">
                        <span class="ticker-label"><?php esc_html_e('Ticker:', 'tradepress'); ?></span>
                        <span class="ticker-value"><?php echo esc_html($ticker); ?></span>
                    </div>
                    
                    <div class="symbol-data-missing">
                        <p><?php esc_html_e('Complete symbol data not available. Please update the symbol.', 'tradepress'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($symbol_object && !empty($complete_data)): ?>
                <div class="symbol-fundamentals local-fundamentals">
                    <h3><?php esc_html_e('Fundamental Data', 'tradepress'); ?></h3>
                    <table class="fundamentals-table">
                        <tbody>
                            <?php
                            // Display from post meta or database table
                            $fundamentals_data = !empty($complete_data['post_meta']) ? $complete_data['post_meta'] : $complete_data;
                            
                            // Mapping of field keys to labels
                            $fundamental_fields = array(
                                'market_cap' => __('Market Cap', 'tradepress'),
                                'pe_ratio' => __('P/E Ratio', 'tradepress'),
                                'eps' => __('EPS', 'tradepress'),
                                'dividend' => __('Dividend', 'tradepress'),
                                'dividend_yield' => __('Dividend Yield', 'tradepress'),
                                '52w_high' => __('52-Week High', 'tradepress'),
                                '52w_low' => __('52-Week Low', 'tradepress'),
                                'volume' => __('Volume', 'tradepress'),
                                'avg_volume' => __('Avg. Volume', 'tradepress'),
                                'sector' => __('Sector', 'tradepress'),
                                'industry' => __('Industry', 'tradepress')
                            );
                            
                            foreach ($fundamental_fields as $key => $label):
                                $value = isset($fundamentals_data[$key]) ? $fundamentals_data[$key] : '';
                                if (!empty($value)):
                            ?>
                                <tr>
                                    <th><?php echo esc_html($label); ?></th>
                                    <td><?php echo esc_html($value); ?></td>
                                </tr>
                            <?php 
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($complete_data['latest_score'])): ?>
                    <div class="symbol-score">
                        <h3><?php esc_html_e('Algorithm Score', 'tradepress'); ?></h3>
                        <div class="score-display">
                            <div class="score-value <?php echo (int)$complete_data['latest_score']['score'] > 0 ? 'positive' : 'negative'; ?>">
                                <?php echo esc_html($complete_data['latest_score']['score']); ?>
                            </div>
                            <div class="score-date">
                                <?php 
                                $score_date = new DateTime($complete_data['latest_score']['created_at']);
                                echo esc_html($score_date->format('M j, Y g:i a')); 
                                ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($complete_data['latest_score']['components'])): ?>
                            <div class="score-components">
                                <h4><?php esc_html_e('Score Components', 'tradepress'); ?></h4>
                                <table class="components-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Directive', 'tradepress'); ?></th>
                                            <th><?php esc_html_e('Score', 'tradepress'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($complete_data['latest_score']['components'] as $component): ?>
                                            <tr>
                                                <td><?php echo esc_html($component['directive_name']); ?></td>
                                                <td class="<?php echo (float)$component['weighted_score'] > 0 ? 'positive' : 'negative'; ?>">
                                                    <?php echo esc_html($component['weighted_score']); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($complete_data['price_levels'])): ?>
                    <div class="symbol-price-levels">
                        <h3><?php esc_html_e('Support & Resistance Levels', 'tradepress'); ?></h3>
                        <table class="price-levels-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Timeframe', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Price Level', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Strength', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($complete_data['price_levels'] as $level): ?>
                                    <tr>
                                        <td><?php echo esc_html(ucfirst($level['level_type'])); ?></td>
                                        <td><?php echo esc_html(ucfirst($level['timeframe'])); ?></td>
                                        <td><?php echo esc_html($level['price_level']); ?></td>
                                        <td>
                                            <div class="strength-indicator strength-<?php echo esc_attr($level['strength']); ?>">
                                                <?php 
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $active = $i <= $level['strength'] ? 'active' : 'inactive';
                                                    echo '<span class="strength-dot ' . $active . '"></span>';
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div> <!-- End Local Data Section -->

        <div class="entry-content">
            <!-- External Data Section -->
            <div class="tradepress-external-data-section">
                <h2 class="section-title"><?php esc_html_e('TradingView Live Data', 'tradepress'); ?></h2>
                <div class="external-data-disclaimer">
                    <?php esc_html_e('This data is provided by TradingView and updates in real-time.', 'tradepress'); ?>
                </div>
                
                <!-- TradingView Widget Section -->
                <div class="tradingview-widget-container" id="tradingview-chart-container" data-symbol="<?php echo esc_attr($ticker); ?>">
                    <div class="tradingview-widget-container__widget"></div>
                    <div class="tradingview-widget-copyright">
                        <a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank">
                            <span class="blue-text">Track all markets on TradingView</span>
                        </a>
                    </div>
                </div>
            </div> <!-- End External Data Section -->
            
            <?php if ($symbol_object && !empty($complete_data['price_history'])): ?>
            <div class="tradepress-price-history-section">
                <h2 class="section-title"><?php esc_html_e('Price History', 'tradepress'); ?></h2>
                <table class="price-history-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Date', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Open', 'tradepress'); ?></th>
                            <th><?php esc_html_e('High', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Low', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Close', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Volume', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complete_data['price_history'] as $price): ?>
                            <tr>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($price['date_time']))); ?></td>
                                <td><?php echo !empty($price['open_price']) ? esc_html($price['open_price']) : 'N/A'; ?></td>
                                <td><?php echo !empty($price['high_price']) ? esc_html($price['high_price']) : 'N/A'; ?></td>
                                <td><?php echo !empty($price['low_price']) ? esc_html($price['low_price']) : 'N/A'; ?></td>
                                <td><?php echo !empty($price['close_price']) ? esc_html($price['close_price']) : 'N/A'; ?></td>
                                <td><?php echo !empty($price['volume']) ? esc_html(number_format($price['volume'])) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if ($symbol_object && !empty($complete_data['predictions'])): ?>
            <div class="tradepress-predictions-section">
                <h2 class="section-title"><?php esc_html_e('Price Predictions', 'tradepress'); ?></h2>
                <table class="predictions-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Source', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Date', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Target Date', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Prediction', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Confidence', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complete_data['predictions'] as $prediction): ?>
                            <tr>
                                <td><?php echo !empty($prediction['source_name']) ? esc_html($prediction['source_name']) : 'Unknown'; ?></td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($prediction['prediction_date']))); ?></td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($prediction['target_date']))); ?></td>
                                <td><?php echo esc_html('$' . number_format($prediction['price_prediction'], 2)); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($prediction['confidence_percentage'])) {
                                        echo esc_html($prediction['confidence_percentage'] . '%');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if ($symbol_object && !empty($complete_data['social_alerts'])): ?>
            <div class="tradepress-social-alerts-section">
                <h2 class="section-title"><?php esc_html_e('Social Media Alerts', 'tradepress'); ?></h2>
                <div class="social-alerts-list">
                    <?php foreach ($complete_data['social_alerts'] as $alert): ?>
                        <div class="social-alert">
                            <div class="alert-header">
                                <span class="alert-source">
                                    <?php echo esc_html($alert['platform'] . ' / ' . $alert['source']); ?>
                                </span>
                                <span class="alert-date">
                                    <?php echo esc_html(date('Y-m-d H:i', strtotime($alert['message_date']))); ?>
                                </span>
                            </div>
                            <div class="alert-message">
                                <?php echo esc_html($alert['raw_message']); ?>
                            </div>
                            <?php if (!empty($alert['detected_action']) || !empty($alert['price_target'])): ?>
                                <div class="alert-details">
                                    <?php if (!empty($alert['detected_action'])): ?>
                                        <span class="alert-action">
                                            <?php echo esc_html(ucfirst($alert['detected_action'])); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($alert['price_target'])): ?>
                                        <span class="alert-target">
                                            <?php echo esc_html('Target: $' . $alert['price_target']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="symbol-description">
                <h2><?php esc_html_e('About', 'tradepress'); ?></h2>
                <?php echo $symbol_content; ?>
            </div>
            
            <div class="tradepress-symbol-related">
                <!-- Related content, like news articles, similar securities, etc. -->
                <?php
                // Get related posts (news articles) about this security
                $related_args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 5,
                    'meta_query' => array(
                        array(
                            'key' => '_tradepress_related_symbol',
                            'value' => $ticker,
                            'compare' => '='
                        )
                    )
                );
                
                $related_query = new WP_Query($related_args);
                
                if ($related_query->have_posts()) :
                ?>
                <div class="symbol-related-news">
                    <h3><?php esc_html_e('Related News', 'tradepress'); ?></h3>
                    <ul class="news-list">
                        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                        <li>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <span class="news-date"><?php echo get_the_date(); ?></span>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <?php 
                wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>

    </div>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize TradingView widget
            var container = document.getElementById('tradingview-chart-container');
            if (container) {
                var symbol = container.getAttribute('data-symbol');
                
                new TradingView.widget({
                    "width": "100%",
                    "height": 400,
                    "symbol": symbol,
                    "interval": "D",
                    "timezone": "Etc/UTC",
                    "theme": "light",
                    "style": "1",
                    "locale": "en",
                    "toolbar_bg": "#f1f3f6",
                    "enable_publishing": false,
                    "allow_symbol_change": true,
                    "container_id": "tradingview-chart-container"
                });
            }
        });
    </script>
    <?php

    // Handle manual update requests
    if (isset($_GET['update_symbol']) && $_GET['update_symbol'] == 1 && $symbol_object) {
        $symbol_object->update_from_api();
        
        // Redirect to remove the query param
        echo '<script>window.location.href = "' . esc_url(get_permalink()) . '";</script>';
    }

    return ob_get_clean();
}
