<?php
/**
 * TradePress Development Snippets Tab
 *
 * @package TradePress\Admin\Development
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TradePress_Admin_Development_Snippets
 */
class TradePress_Admin_Development_Snippets {

    /**
     * Output the snippets view
     */
    public static function output() {
        ?>
        <div class="tab-content" id="snippets">
            <h3><?php esc_html_e('Developer Snippets', 'tradepress'); ?></h3>
            <p class="description"><?php esc_html_e('Code examples for using TradePress systems and classes.', 'tradepress'); ?></p>
            
            <div class="snippets-container">
                
                <div class="snippet-section">
                    <h4><?php esc_html_e('Background Processing', 'tradepress'); ?></h4>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Starting a Continuous Data Import Process', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// User clicks "Start Data Import"
$data_import = new TradePress_Data_Import_Process();
$data_import->push_to_queue(['action' => 'fetch_earnings']);
$data_import->push_to_queue(['action' => 'fetch_prices']);
$data_import->save()->dispatch(); // Starts continuous processing</code></pre>
                    </div>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Stopping the Process', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// User clicks "Stop Data Import"
$data_import->cancel_process(); // Stops gracefully</code></pre>
                    </div>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Starting Scoring Process', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// Start scoring algorithm
$scoring = new TradePress_Scoring_Process();
$scoring->push_to_queue(['action' => 'calculate_scores']);
$scoring->push_to_queue(['action' => 'generate_signals']);
$scoring->save()->dispatch();</code></pre>
                    </div>
                </div>
                
                <div class="snippet-section">
                    <h4><?php esc_html_e('API Integration', 'tradepress'); ?></h4>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Making API Calls', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// Get API instance
$api = TradePress_API_Manager::get_instance();

// Make earnings calendar request
$earnings_data = $api->get_earnings_calendar([
    'horizon' => '3month',
    'symbol' => 'AAPL'
]);

// Store in database
if (!is_wp_error($earnings_data)) {
    update_option('tradepress_earnings_data', $earnings_data);
}</code></pre>
                    </div>
                </div>
                
                <div class="snippet-section">
                    <h4><?php esc_html_e('Database Operations', 'tradepress'); ?></h4>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Storing Symbol Data', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// Store symbol metadata
$symbol_id = wp_insert_post([
    'post_type' => 'symbols',
    'post_title' => 'AAPL',
    'post_status' => 'publish'
]);

// Add meta data
update_post_meta($symbol_id, 'current_price', 150.25);
update_post_meta($symbol_id, 'last_updated', current_time('mysql'));</code></pre>
                    </div>
                </div>
                
                <div class="snippet-section">
                    <h4><?php esc_html_e('Scoring System', 'tradepress'); ?></h4>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Calculate Symbol Score', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// Get scoring engine
$scoring = TradePress_Scoring_Engine::get_instance();

// Calculate score for symbol
$score = $scoring->calculate_symbol_score('AAPL', [
    'strategy_id' => 1,
    'include_technical' => true,
    'include_fundamental' => true
]);

// Store score
update_post_meta($symbol_id, 'tradepress_score', $score);</code></pre>
                    </div>
                </div>
                
                <div class="snippet-section">
                    <h4><?php esc_html_e('Asset Management', 'tradepress'); ?></h4>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Enqueue Assets', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// Get asset manager
global $tradepress_assets;

// Enqueue specific assets for page
$tradepress_assets->enqueue_for_page('automation', [
    'admin-automation',
    'process-monitoring'
]);</code></pre>
                    </div>
                </div>
                
                <div class="snippet-section">
                    <h4><?php esc_html_e('AJAX Handlers', 'tradepress'); ?></h4>
                    
                    <div class="snippet-item">
                        <h5><?php esc_html_e('Creating AJAX Handler', 'tradepress'); ?></h5>
                        <pre><code class="language-php">// In controller class
public function ajax_custom_action() {
    check_ajax_referer('tradepress_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'tradepress'));
    }
    
    $result = $this->perform_action();
    
    wp_send_json_success($result);
}</code></pre>
                    </div>
                </div>
                
            </div>
        </div>
        
        <style>
        .snippets-container {
            max-width: 1200px;
        }
        .snippet-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
        }
        .snippet-section h4 {
            margin-top: 0;
            color: #23282d;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .snippet-item {
            margin-bottom: 20px;
        }
        .snippet-item h5 {
            margin-bottom: 10px;
            color: #555;
        }
        .snippet-item pre {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.4;
        }
        .snippet-item code {
            color: #d63384;
            font-family: 'Courier New', monospace;
        }
        </style>
        <?php
    }
}