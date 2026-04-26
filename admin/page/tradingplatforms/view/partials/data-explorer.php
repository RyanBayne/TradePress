<?php
/**
 * Partial: Data Explorer
 * 
 * This partial template includes the data explorer component for API tabs.
 * Required variables: $api_id, $api_name, $explorer_data_types
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verify required variables are set
if (!isset($api_id) || !isset($api_name)) {
    return;
}

// Default data types if not provided
if (!isset($explorer_data_types) || !is_array($explorer_data_types)) {
    $explorer_data_types = array(
        'quote' => __('Quote Data', 'tradepress'),
        'historical' => __('Historical Data', 'tradepress'),
        'company' => __('Company Data', 'tradepress')
    );
}
?>

<div class="data-explorer">
    <div class="tool-header">
        <h3><?php esc_html_e('Data Explorer', 'tradepress'); ?></h3>
    </div>

    <div class="tool-section">
        <div class="data-explorer-form">
            <?php 
            // Generate unique form ID to avoid duplicate IDs when multiple instances are loaded
            $form_id = 'data-explorer-form-' . esc_attr($api_id) . '-' . uniqid(); 
            ?>
            <form method="post" id="<?php echo esc_attr($form_id); ?>">
                <div class="symbol-input">
                    <label for="data-explorer-symbol-<?php echo esc_attr($api_id); ?>-<?php echo uniqid(); ?>"><?php esc_html_e('Symbol', 'tradepress'); ?></label>
                    <input type="text" id="data-explorer-symbol-<?php echo esc_attr($api_id); ?>-<?php echo uniqid(); ?>" name="symbol" placeholder="Enter symbol (e.g. AAPL.US)" class="regular-text">
                </div>

                <div class="endpoint-select">
                    <label for="data-explorer-endpoint-<?php echo esc_attr($api_id); ?>-<?php echo uniqid(); ?>"><?php esc_html_e('Endpoint', 'tradepress'); ?></label>
                    <select id="data-explorer-endpoint-<?php echo esc_attr($api_id); ?>-<?php echo uniqid(); ?>" name="endpoint" class="regular-text">
                        <option value=""><?php esc_html_e('-- Select Endpoint --', 'tradepress'); ?></option>
                        <?php foreach ($endpoints as $key => $endpoint): ?>
                            <?php if ($endpoint['status'] === 'active'): ?>
                            <option value="<?php echo esc_attr($endpoint['name']); ?>"><?php echo esc_html($endpoint['name']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="explore_data" class="button button-primary">
                        <?php esc_html_e('Explore Data', 'tradepress'); ?>
                    </button>
                </div>
                
                <?php 
                // Generate a unique nonce field ID for each form instance
                $nonce_id = 'tradepress_explore_data_nonce_' . $api_id . '_' . uniqid(); 
                wp_nonce_field('tradepress_explore_data_nonce', $nonce_id); 
                ?>
            </form>
        </div>
    </div>

    <div class="data-results">
        <div class="results-placeholder">
            <p><?php esc_html_e('Enter a symbol and select an endpoint to explore data.', 'tradepress'); ?></p>
        </div>
    </div>
</div>
