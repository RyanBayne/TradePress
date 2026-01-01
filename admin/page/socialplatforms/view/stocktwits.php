<?php
/**
 * TradePress - StockTwits Social Platform Tab
 *
 * Displays StockTwits integration settings and functionality
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/admin/page
 * @since    1.0.0
 * @created  April 22, 2025
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap tradepress-stocktwits-integration">

    
    <h2><?php esc_html_e('StockTwits Integration', 'tradepress'); ?></h2>
    
    <div class="stocktwits-connection-settings">
        <h3><?php esc_html_e('StockTwits API Connection', 'tradepress'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('tradepress-stocktwits-settings'); ?>
            <input type="hidden" name="social_provider" value="stocktwits">
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="api_stocktwits_apikey"><?php esc_html_e('API Key', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="api_stocktwits_apikey" id="api_stocktwits_apikey" 
                                   value="<?php echo esc_attr(get_option('TradePress_social_stocktwits_apikey', '')); ?>" 
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Enter your StockTwits API key obtained from the StockTwits developer portal.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="switch_stocktwits_services"><?php esc_html_e('StockTwits Integration', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input name="switch_stocktwits_services" type="checkbox" id="switch_stocktwits_services" 
                                       value="yes" <?php checked(get_option('TradePress_switch_stocktwits_social_services', 'no'), 'yes'); ?>>
                                <?php esc_html_e('Enable StockTwits Integration', 'tradepress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Enable StockTwits integration for sentiment analysis and market discussions.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="switch_stocktwits_logs"><?php esc_html_e('API Logging', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input name="switch_stocktwits_logs" type="checkbox" id="switch_stocktwits_logs" 
                                       value="yes" <?php checked(get_option('TradePress_switch_stocktwits_social_logs', 'no'), 'yes'); ?>>
                                <?php esc_html_e('Enable StockTwits API Logs', 'tradepress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Enable logging for StockTwits API calls for debugging purposes.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_social_provider" class="button button-primary" value="<?php esc_attr_e('Save StockTwits Settings', 'tradepress'); ?>">
            </p>
        </form>
    </div>
    
    <div class="stocktwits-sentiment-settings">
        <h3><?php esc_html_e('Sentiment Analysis Settings', 'tradepress'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('tradepress-stocktwits-sentiment-settings'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="stocktwits_sentiment_threshold"><?php esc_html_e('Sentiment Threshold', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="stocktwits_sentiment_threshold" id="stocktwits_sentiment_threshold" 
                                   value="<?php echo esc_attr(get_option('tradepress_stocktwits_sentiment_threshold', '70')); ?>" 
                                   min="50" max="100" step="1" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Threshold percentage for considering sentiment as significant (50-100%).', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="stocktwits_sentiment_period"><?php esc_html_e('Analysis Period', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <select name="stocktwits_sentiment_period" id="stocktwits_sentiment_period">
                                <option value="day" <?php selected(get_option('tradepress_stocktwits_sentiment_period', 'day'), 'day'); ?>>
                                    <?php esc_html_e('Last 24 Hours', 'tradepress'); ?>
                                </option>
                                <option value="week" <?php selected(get_option('tradepress_stocktwits_sentiment_period', 'day'), 'week'); ?>>
                                    <?php esc_html_e('Last 7 Days', 'tradepress'); ?>
                                </option>
                                <option value="month" <?php selected(get_option('tradepress_stocktwits_sentiment_period', 'day'), 'month'); ?>>
                                    <?php esc_html_e('Last 30 Days', 'tradepress'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Time period for analyzing sentiment trends.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="stocktwits_sentiment_importance"><?php esc_html_e('Scoring Weight', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input type="range" name="stocktwits_sentiment_importance" id="stocktwits_sentiment_importance" 
                                   value="<?php echo esc_attr(get_option('tradepress_stocktwits_sentiment_importance', '50')); ?>" 
                                   min="0" max="100" step="10" class="widefat">
                            <span class="slider-value"><?php echo esc_html(get_option('tradepress_stocktwits_sentiment_importance', '50')); ?>%</span>
                            <p class="description">
                                <?php esc_html_e('Weight given to sentiment analysis in scoring algorithm (0-100%).', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_stocktwits_sentiment_settings" class="button button-primary" value="<?php esc_attr_e('Save Sentiment Settings', 'tradepress'); ?>">
            </p>
        </form>
    </div>
    
    <div class="stocktwits-display-settings">
        <h3><?php esc_html_e('Display Settings', 'tradepress'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('tradepress-stocktwits-display-settings'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="stocktwits_display_widget"><?php esc_html_e('Display Sentiment Widget', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input name="stocktwits_display_widget" type="checkbox" id="stocktwits_display_widget" 
                                       value="yes" <?php checked(get_option('tradepress_stocktwits_display_widget', 'no'), 'yes'); ?>>
                                <?php esc_html_e('Show sentiment widget on symbol pages', 'tradepress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Display StockTwits sentiment data on individual symbol pages.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="stocktwits_display_trending"><?php esc_html_e('Display Trending Symbols', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input name="stocktwits_display_trending" type="checkbox" id="stocktwits_display_trending" 
                                       value="yes" <?php checked(get_option('tradepress_stocktwits_display_trending', 'no'), 'yes'); ?>>
                                <?php esc_html_e('Show trending symbols widget', 'tradepress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Display trending symbols from StockTwits on the dashboard.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_stocktwits_display_settings" class="button button-primary" value="<?php esc_attr_e('Save Display Settings', 'tradepress'); ?>">
            </p>
        </form>
    </div>
    
    <div class="stocktwits-testing">
        <h3><?php esc_html_e('StockTwits API Testing', 'tradepress'); ?></h3>
        <p><?php esc_html_e('Test your StockTwits API connection and sentiment analysis functionality.', 'tradepress'); ?></p>
        
        <div class="test-buttons">
            <button type="button" class="button" disabled><?php esc_html_e('Test Connection', 'tradepress'); ?></button>
            <button type="button" class="button" disabled><?php esc_html_e('Test Sentiment Analysis', 'tradepress'); ?></button>
        </div>
        
        <div class="test-results" style="display: none;">
            <h4><?php esc_html_e('Test Results', 'tradepress'); ?></h4>
            <pre class="test-output"></pre>
        </div>
    </div>
</div>