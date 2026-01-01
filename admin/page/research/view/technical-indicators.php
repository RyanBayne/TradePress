<?php
/**
 * TradePress Technical Indicators Tab
 *
 * Displays technical analysis indicators and educational content for the Research page
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
 * Display the Technical Indicators tab content
 */
function tradepress_technical_indicators_tab_content() {
    // Check if demo mode is active
    $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : false;

    // Get any saved settings or default values
    $demo_stock = isset($_GET['symbol']) ? sanitize_text_field($_GET['symbol']) : 'AAPL';
    $timeframe = isset($_GET['timeframe']) ? sanitize_text_field($_GET['timeframe']) : 'daily';
    
    // Display the technical indicators interface
    ?>
    <div class="tradepress-technical-indicators-container">

        
        <div class="tradepress-research-section">
            <h2><?php esc_html_e('Technical Indicators', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Analyze securities using popular technical indicators and find potential trading opportunities.', 'tradepress'); ?></p>
            
            <div class="tradepress-research-inputs">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="technical-indicators">
                    
                    <div class="tradepress-input-group">
                        <label for="symbol"><?php esc_html_e('Symbol:', 'tradepress'); ?></label>
                        <input type="text" id="symbol" name="symbol" value="<?php echo esc_attr($demo_stock); ?>" placeholder="AAPL">
                    </div>
                    
                    <div class="tradepress-input-group">
                        <label for="timeframe"><?php esc_html_e('Timeframe:', 'tradepress'); ?></label>
                        <select id="timeframe" name="timeframe">
                            <option value="daily" <?php selected($timeframe, 'daily'); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                            <option value="weekly" <?php selected($timeframe, 'weekly'); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                            <option value="monthly" <?php selected($timeframe, 'monthly'); ?>><?php esc_html_e('Monthly', 'tradepress'); ?></option>
                        </select>
                    </div>
                    
                    <button type="submit" class="button button-primary"><?php esc_html_e('Analyze', 'tradepress'); ?></button>
                </form>
            </div>
        </div>
        
        <div class="tradepress-research-results">
            <!-- This section will contain technical indicator results -->
            <div class="tradepress-indicators-summary">
                <h3><?php echo sprintf(esc_html__('Technical Analysis for %s', 'tradepress'), $demo_stock); ?></h3>
                <!-- Indicator summary will go here -->
            </div>
            
            <div class="tradepress-indicators-panels">
                <!-- Individual indicator panels will be added here -->
                <!-- Planned indicators:
                    - Moving Averages (SMA, EMA)
                    - Relative Strength Index (RSI)
                    - MACD
                    - Bollinger Bands
                    - Stochastic Oscillator
                    - On-Balance Volume (OBV)
                    - Average Directional Index (ADX)
                -->
            </div>
            
            <div class="tradepress-indicator-interpretation">
                <!-- Interpretation and educational content will go here -->
            </div>
        </div>
    </div>
    <?php
}
?>
