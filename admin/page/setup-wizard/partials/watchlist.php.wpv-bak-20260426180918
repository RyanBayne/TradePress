<?php
/**
 * Setup Wizard - Watchlist Step
 */

if (!defined('ABSPATH')) {
    exit;
}

// Display API test results if available
$test_results = get_transient('tradepress_api_test_results');
if ($test_results) {
    echo $test_results;
    delete_transient('tradepress_api_test_results');
}

// Load test symbols
require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'includes/data/symbols-data.php');
$test_symbols = function_exists('tradepress_get_all_test_symbols') ? tradepress_get_all_test_symbols() : array();

// Clean and sort symbols
if (is_array($test_symbols) && !empty($test_symbols)) {
    $test_symbols = array_unique($test_symbols);
    sort($test_symbols);
} else {
    $test_symbols = array();
}

// Get company details if available
$company_details = function_exists('tradepress_get_test_company_details') ? tradepress_get_test_company_details() : array();

// Filter symbols to only show those with company details
if (!empty($company_details)) {
    $test_symbols = array_intersect($test_symbols, array_keys($company_details));
}

// Get any previously entered symbols
$saved_symbols = get_option('TradePress_watchlist_symbols', '');
$manual_symbols = $saved_symbols ? $saved_symbols : '';
?>

<h1><?php _e('Start Your Watchlist', 'tradepress'); ?></h1>

<p><?php _e('The plugin needs to be initalised with some symbols to work. No less than three is recommended.', 'tradepress'); ?></p>

<form method="post">
    <div class="tradepress-setup-content-inner">
        <div class="tradepress-symbols-grid">
            <?php foreach ($test_symbols as $symbol) : 
                // Get company details if available
                $company_name = $symbol;
                $sector = 'Unknown';
                $industry = 'Unknown';
                $exchange = 'Unknown';
                $description = '';
                $market_cap = '';
                $beta = '';
                
                if (is_array($company_details) && isset($company_details[$symbol]) && is_array($company_details[$symbol])) {
                    $details = $company_details[$symbol];
                    $company_name = !empty($details['name']) ? $details['name'] : $company_name;
                    $sector = !empty($details['sector']) ? $details['sector'] : 'Unknown';
                    $industry = !empty($details['industry']) ? $details['industry'] : 'Unknown';
                    $exchange = !empty($details['exchange']) ? $details['exchange'] : 'Unknown';
                    $description = !empty($details['description']) ? wp_trim_words($details['description'], 15) : '';
                    $market_cap = !empty($details['market_cap_category']) ? $details['market_cap_category'] : '';
                    $beta = !empty($details['beta']) ? number_format($details['beta'], 2) : '';
                }
            ?>
                <div class="tradepress-symbol-card">
                    <div class="symbol-header">
                        <div class="symbol-checkbox">
                            <input type="checkbox" id="symbol_<?php echo esc_attr($symbol); ?>" name="TradePress_test_symbols[]" value="<?php echo esc_attr($symbol); ?>" checked>
                        </div>
                        <div class="symbol-info">
                            <label for="symbol_<?php echo esc_attr($symbol); ?>" class="symbol-ticker"><?php echo esc_html($symbol); ?></label>
                            <div class="symbol-name"><?php echo esc_html($company_name); ?></div>
                        </div>
                        <div class="symbol-exchange"><?php echo esc_html($exchange); ?></div>
                    </div>
                    <div class="symbol-details">
                        <div class="symbol-sector"><?php echo esc_html($sector); ?> • <?php echo esc_html($industry); ?></div>
                        <?php if ($market_cap || $beta) : ?>
                            <div class="symbol-metrics">
                                <?php if ($market_cap) : ?><span class="metric"><?php echo esc_html($market_cap); ?></span><?php endif; ?>
                                <?php if ($beta) : ?><span class="metric">β <?php echo esc_html($beta); ?></span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($description) : ?>
                            <div class="symbol-description"><?php echo esc_html($description); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        
        <div class="tradepress-symbol-controls">
            <button type="button" class="button" id="select-all-symbols"><?php _e('Select All', 'tradepress'); ?></button>
            <button type="button" class="button" id="deselect-all-symbols"><?php _e('Deselect All', 'tradepress'); ?></button>
        </div>
        
        <h3><?php _e('Add Custom Symbols', 'tradepress'); ?></h3>
        <p><?php _e('You can also manually add symbols that are not in the list above.', 'tradepress'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="TradePress_watchlist_symbols"><?php _e('Custom Symbols', 'tradepress'); ?></label></th>
                <td>
                    <textarea id="TradePress_watchlist_symbols" name="TradePress_watchlist_symbols" class="large-text" rows="3" placeholder="AAPL, MSFT, GOOGL, AMZN, TSLA"><?php echo esc_textarea($manual_symbols); ?></textarea>
                    <p class="description"><?php _e('Enter additional stock symbols separated by commas. Example: AAPL, MSFT, GOOGL', 'tradepress'); ?></p>
                </td>
            </tr>
        </table>
    </div>
    
    <p class="tradepress-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'tradepress'); ?>" name="save_step" />
        <?php wp_nonce_field('tradepress-setup'); ?>
    </p>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllBtn = document.getElementById('select-all-symbols');
    const deselectAllBtn = document.getElementById('deselect-all-symbols');
    const checkboxes = document.querySelectorAll('input[name="TradePress_test_symbols[]"]');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(checkbox => checkbox.checked = true);
        });
    }
    
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(checkbox => checkbox.checked = false);
        });
    }
});
</script>