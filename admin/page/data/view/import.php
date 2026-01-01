<?php
/**
 * TradePress Data Import Tab
 *
 * One-time data import testing and manual operations.
 * This is separate from automation - focuses on testing individual import operations.
 *
 * AI GUIDANCE:
 * - This tab is for ONE-TIME imports and testing
 * - Automation tab handles REPEATED/SCHEDULED operations
 * - Use existing symbol classes: TradePress_Symbol, TradePress_Symbols
 * - Test individual API calls and data fetching
 * - Show detailed results for debugging
 *
 * @package TradePress
 * @subpackage Admin/Data
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tradepress-data-import-tab">
    <div class="import-section">
        <h3><?php esc_html_e('One-Time Data Import Testing', 'tradepress'); ?></h3>
        <p><?php esc_html_e('Test individual data import operations. Use this to verify API connections and data fetching before setting up automation.', 'tradepress'); ?></p>
        
        <!-- Symbol Data Testing -->
        <div class="import-test-group">
            <h4><?php esc_html_e('Symbol Data Import', 'tradepress'); ?></h4>
            <p><?php esc_html_e('Test fetching symbol information from various APIs.', 'tradepress'); ?></p>
            
            <div class="test-controls">
                <input type="text" id="test-symbol-input" placeholder="Enter symbol (e.g., AAPL)" value="AAPL" class="regular-text">
                <button class="button button-primary test-symbol-fetch" data-symbol-input="test-symbol-input">
                    <?php esc_html_e('Test Symbol Fetch', 'tradepress'); ?>
                </button>
                <button class="button button-secondary manual-symbol-update" data-symbol-input="test-symbol-input">
                    <?php esc_html_e('Update Symbol from API', 'tradepress'); ?>
                </button>
            </div>
            
            <div class="test-results" id="symbol-test-results" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; display: none;">
                <h5><?php esc_html_e('Import Results:', 'tradepress'); ?></h5>
                <pre id="symbol-test-output" style="background: white; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
        
        <!-- Batch Import Testing -->
        <div class="import-test-group">
            <h4><?php esc_html_e('Batch Symbol Import', 'tradepress'); ?></h4>
            <p><?php esc_html_e('Test importing multiple symbols at once.', 'tradepress'); ?></p>
            
            <div class="test-controls">
                <textarea id="batch-symbols-input" placeholder="Enter symbols separated by commas (e.g., AAPL,MSFT,GOOGL)" class="large-text" rows="3">AAPL,MSFT,GOOGL</textarea>
                <br><br>
                <button class="button button-primary test-batch-import">
                    <?php esc_html_e('Test Batch Import', 'tradepress'); ?>
                </button>
            </div>
            
            <div class="test-results" id="batch-test-results" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; display: none;">
                <h5><?php esc_html_e('Batch Import Results:', 'tradepress'); ?></h5>
                <pre id="batch-test-output" style="background: white; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
        
        <!-- API Connection Testing -->
        <div class="import-test-group">
            <h4><?php esc_html_e('API Connection Testing', 'tradepress'); ?></h4>
            <p><?php esc_html_e('Test connections to different data providers.', 'tradepress'); ?></p>
            
            <div class="test-controls">
                <select id="api-provider-select" class="regular-text">
                    <option value="alphavantage"><?php esc_html_e('Alpha Vantage', 'tradepress'); ?></option>
                    <option value="alpaca"><?php esc_html_e('Alpaca', 'tradepress'); ?></option>
                    <option value="finnhub"><?php esc_html_e('Finnhub', 'tradepress'); ?></option>
                </select>
                <button class="button button-primary test-api-connection">
                    <?php esc_html_e('Test API Connection', 'tradepress'); ?>
                </button>
            </div>
            
            <div class="test-results" id="api-test-results" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; display: none;">
                <h5><?php esc_html_e('API Connection Results:', 'tradepress'); ?></h5>
                <pre id="api-test-output" style="background: white; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
        
        <!-- Database Import Status -->
        <div class="import-test-group">
            <h4><?php esc_html_e('Database Import Status', 'tradepress'); ?></h4>
            <p><?php esc_html_e('Check what data has been imported and stored in the database.', 'tradepress'); ?></p>
            
            <div class="test-controls">
                <button class="button button-secondary check-database-status">
                    <?php esc_html_e('Check Database Status', 'tradepress'); ?>
                </button>
                <button class="button button-secondary view-recent-imports">
                    <?php esc_html_e('View Recent Imports', 'tradepress'); ?>
                </button>
            </div>
            
            <div class="test-results" id="database-status-results" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; display: none;">
                <h5><?php esc_html_e('Database Status:', 'tradepress'); ?></h5>
                <pre id="database-status-output" style="background: white; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Test symbol fetch
    $('.test-symbol-fetch').on('click', function() {
        var inputId = $(this).data('symbol-input');
        var symbol = $('#' + inputId).val() || 'AAPL';
        var $results = $('#symbol-test-results');
        var $output = $('#symbol-test-output');
        
        $results.show();
        $output.text('Testing symbol fetch for ' + symbol + '...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_symbol_fetch',
                symbol: symbol,
                nonce: '<?php echo wp_create_nonce('tradepress_admin'); ?>'
            },
            success: function(response) {
                $output.text(JSON.stringify(response, null, 2));
            },
            error: function() {
                $output.text('Error: Could not fetch symbol data');
            }
        });
    });
    
    // Manual symbol update
    $('.manual-symbol-update').on('click', function() {
        var inputId = $(this).data('symbol-input');
        var symbol = $('#' + inputId).val() || 'AAPL';
        var $results = $('#symbol-test-results');
        var $output = $('#symbol-test-output');
        
        $results.show();
        $output.text('Updating symbol ' + symbol + ' from API...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_update_symbols_manual',
                symbol: symbol,
                nonce: '<?php echo wp_create_nonce('tradepress_admin'); ?>'
            },
            success: function(response) {
                $output.text(JSON.stringify(response, null, 2));
            },
            error: function() {
                $output.text('Error: Could not update symbol');
            }
        });
    });
    
    // Batch import test
    $('.test-batch-import').on('click', function() {
        var symbols = $('#batch-symbols-input').val();
        var $results = $('#batch-test-results');
        var $output = $('#batch-test-output');
        
        $results.show();
        $output.text('Testing batch import for: ' + symbols + '...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_batch_import',
                symbols: symbols,
                nonce: '<?php echo wp_create_nonce('tradepress_admin'); ?>'
            },
            success: function(response) {
                $output.text(JSON.stringify(response, null, 2));
            },
            error: function() {
                $output.text('Error: Could not perform batch import');
            }
        });
    });
    
    // API connection test
    $('.test-api-connection').on('click', function() {
        var provider = $('#api-provider-select').val();
        var $results = $('#api-test-results');
        var $output = $('#api-test-output');
        
        $results.show();
        $output.text('Testing ' + provider + ' API connection...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_api_connection',
                provider: provider,
                nonce: '<?php echo wp_create_nonce('tradepress_admin'); ?>'
            },
            success: function(response) {
                $output.text(JSON.stringify(response, null, 2));
            },
            error: function() {
                $output.text('Error: Could not test API connection');
            }
        });
    });
    
    // Database status check
    $('.check-database-status').on('click', function() {
        var $results = $('#database-status-results');
        var $output = $('#database-status-output');
        
        $results.show();
        $output.text('Checking database status...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_check_database_status',
                nonce: '<?php echo wp_create_nonce('tradepress_admin'); ?>'
            },
            success: function(response) {
                $output.text(JSON.stringify(response, null, 2));
            },
            error: function() {
                $output.text('Error: Could not check database status');
            }
        });
    });
});
</script>

<style>
.import-test-group {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: white;
}

.import-test-group h4 {
    margin-top: 0;
    color: #333;
}

.test-controls {
    margin: 15px 0;
}

.test-controls input,
.test-controls select,
.test-controls textarea {
    margin-right: 10px;
    margin-bottom: 10px;
}

.test-results {
    border-left: 4px solid #0073aa;
}

.test-results h5 {
    margin-top: 0;
    color: #0073aa;
}
</style>