<?php
/**
 * Partial: Endpoints Table
 * 
 * This partial template includes the endpoints table component for API tabs.
 * Required variables: $api_id, $endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verify required variables are set
if (!isset($api_id) || !isset($endpoints) || !is_array($endpoints)) {
    return;
}

// Get random symbol for testing if this is the Alpha Vantage API
$random_symbol = '';
if ($api_id === 'alphavantage') {
    if (!class_exists('TradePress_AlphaVantage_API')) {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alphavantage/alphavantage-api.php';
    }
    $av_api = new TradePress_AlphaVantage_API();
    $random_symbol = $av_api->get_random_symbol();
}

// Get endpoints from database if available, otherwise use the provided $endpoints array
$db_endpoints = TradePress_db_get_all_endpoints('endpoint_name', 'ASC');

// Log for developers (only visible in HTML source to admins)
if (current_user_can('manage_options')) {
    echo '<!-- Note: The tradepress_endpoints table should include a platform_id/api_id column to filter endpoints by platform -->';
}

// Function to determine status color class if not already defined
if (!function_exists('get_status_color')) {
    function get_status_color($status) {
        switch ($status) {
            case 'active':
            case 'operational':
                return 'status-green';
            case 'disruption':
            case 'maintenance':
                return 'status-orange';
            case 'inactive':
            case 'outage':
                return 'status-red';
            default:
                return 'status-grey';
        }
    }
}

// Get trading mode and API version settings for the current platform
$trading_mode = get_option('TradePress_api_' . $api_id . '_trading_mode', 'paper');
$api_version = get_option('TradePress_api_' . $api_id . '_version', 'v2');

// Get API name for display
$api_name = isset($api_name) ? $api_name : ucfirst($api_id);

// Check if the TradePress_Endpoint_Tester class exists and load it if needed
if (!class_exists('TradePress_Endpoint_Tester') && file_exists(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/endpoint-tester.php')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/endpoint-tester.php';
}

// Process API test if submitted by retrieving stored results from transient
$api_test_results = null;
$api_test_endpoint = '';
$api_test_endpoint_key = '';
$api_test_performed = false;

// Check for stored test results in transient
$last_test = get_transient('tradepress_last_endpoint_test');
if ($last_test && isset($last_test['platform']) && $last_test['platform'] === $api_id) {
    $api_test_endpoint = $last_test['endpoint'];
    $api_test_endpoint_key = $last_test['endpoint_key'];
    $transient_key = 'tradepress_endpoint_test_' . md5($last_test['platform'] . '_' . $last_test['endpoint']);
    $api_test_results = get_transient($transient_key);
    
    if ($api_test_results) {
        $api_test_performed = true;
        // Clear the transient to prevent showing the same results on page refresh
        delete_transient('tradepress_last_endpoint_test');
        delete_transient($transient_key);
    }
}

// Move the test results outside the content-section so they remain visible regardless of section changes
if ($api_test_performed && $api_test_results): 
    $is_successful = isset($api_test_results['success']) && $api_test_results['success'];
    $api_response = $is_successful && isset($api_test_results['data']) ? $api_test_results['data'] : '';
    $raw_response = isset($api_test_results['raw_response']) ? $api_test_results['raw_response'] : '';
    $status_code = isset($api_test_results['status_code']) ? $api_test_results['status_code'] : 0;
    $debug_timestamp = isset($api_test_results['debug_timestamp']) ? $api_test_results['debug_timestamp'] : '';
    $error_report = isset($api_test_results['error_report']) ? $api_test_results['error_report'] : '';
    
    // Determine if this is a data-only API (e.g., Alpha Vantage)
    $provider = TradePress_API_Directory::get_provider($api_id);
    $is_data_only_api = isset($provider['api_type']) && $provider['api_type'] === 'data_only';
?>
<div id="tradepress-fixed-test-results" class="notice api-test-results-notice is-dismissible">
    <h3><?php echo $is_successful ? esc_html__('API Test Results - Success', 'tradepress') : esc_html__('API Test Results - Error', 'tradepress'); ?></h3>
    
    <div class="test-result-header">
        <strong>Platform:</strong> <?php echo esc_html($api_name); ?><br>
        <strong>Endpoint:</strong> <?php echo esc_html($api_test_endpoint); ?><br>
        <strong>Status:</strong> <span class="<?php echo $is_successful ? 'success-text' : 'error-text'; ?>">
            <?php echo $is_successful ? esc_html__('Success', 'tradepress') : esc_html__('Connection Error', 'tradepress'); ?>
        </span><br>
        <strong>Environment:</strong> <?php 
            $is_demo = get_option('tradepress_demo_mode', false);
            echo esc_html($is_demo ? 'Demo' : 'Live'); 
        ?><br>
        <?php if ($is_data_only_api): ?>
        <strong>Trading Mode:</strong> <?php echo esc_html__('Not Applicable', 'tradepress'); ?><br>
        <?php else: ?>
        <strong>Trading Mode:</strong> <?php echo esc_html($trading_mode); ?><br>
        <?php endif; ?>
        <strong>API Version:</strong> <?php echo esc_html($api_version); ?><br>
        <strong>Date/Time:</strong> <?php echo esc_html(date('d/m/Y, H:i:s')); ?><br>
    </div>
    
    <?php if (!$is_successful): ?>
        <h4><?php esc_html_e('Error Message:', 'tradepress'); ?></h4>
        <div class="error-message">
            <?php echo esc_html($api_test_results['message']); ?> 
            [DEBUG: <?php echo esc_html($debug_timestamp); ?>]
        </div>
    <?php endif; ?>
    
    <div class="api-response-section">
        <h4><?php esc_html_e('API Response:', 'tradepress'); ?></h4>
        <textarea class="api-response-text" readonly onclick="this.select()">
        <?php 
            if ($is_successful && is_array($api_response)) {
                echo esc_textarea(json_encode($api_response, JSON_PRETTY_PRINT));
            } else {
                echo esc_textarea(is_array($raw_response) ? json_encode($raw_response, JSON_PRETTY_PRINT) : ($raw_response ?: ($api_response ? (is_array($api_response) ? json_encode($api_response, JSON_PRETTY_PRINT) : $api_response) : '0')));
            }
        ?>
        </textarea>
        <span class="copy-hint"><?php esc_html_e('Click to select all. Ctrl+C to copy.', 'tradepress'); ?></span>
    </div>
    
    <?php if (!$is_successful && !empty($error_report)): ?>
    <div class="ai-report-section">
        <h4><?php esc_html_e('AI Troubleshooting Report:', 'tradepress'); ?></h4>
        <textarea class="ai-report-text" readonly onclick="this.select()"><?php echo esc_textarea($error_report); ?></textarea>
        <span class="copy-hint"><?php esc_html_e('Click to select all. Ctrl+C to copy.', 'tradepress'); ?></span>
    </div>
    <?php elseif (!$is_successful): ?>
    <div class="ai-report-section">
        <h4><?php esc_html_e('AI Troubleshooting Report:', 'tradepress'); ?></h4>
        <textarea class="ai-report-text" readonly onclick="this.select()">### API Test Error Report ###
Platform: <?php echo esc_html($api_name); ?>

Endpoint: <?php echo esc_html($api_test_endpoint); ?>')); ?>

Status: <?php echo $is_successful ? esc_html__('Success', 'tradepress') : esc_html__('Connection Error', 'tradepress'); ?>

Environment: <?php echo esc_html($is_demo ? 'Demo' : 'Live'); ?>

<?php if ($is_data_only_api): ?>
Trading Mode: Not Applicable
<?php else: ?>
Trading Mode: <?php echo esc_html($trading_mode); ?>
<?php endif; ?>

API Version: <?php echo esc_html($api_version); ?>

Time: <?php echo esc_html(date('d/m/Y, H:i:s')); ?>

<?php if (!$is_successful): ?>
Error: <?php echo esc_html($api_test_results['message']); ?> [DEBUG: <?php echo esc_html($debug_timestamp); ?>]
<?php endif; ?>

Error Details: <?php echo esc_html(isset($api_test_results['details']) ? (is_array($api_test_results['details']) ? json_encode($api_test_results['details'], JSON_PRETTY_PRINT) : $api_test_results['details']) : ''); ?>

Raw Response Size: <?php echo esc_html(strlen(is_array($raw_response) ? json_encode($raw_response, JSON_PRETTY_PRINT) : ($raw_response ?: ($api_response ? (is_array($api_response) ? json_encode($api_response, JSON_PRETTY_PRINT) : $api_response) : '0')))); ?> characters
        </textarea>
        <span class="copy-hint"><?php esc_html_e('Click to select all. Ctrl+C to copy.', 'tradepress'); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if (!$is_successful): ?>
        <div class="error-guidance">
            <h4><?php esc_html_e('Troubleshooting steps:', 'tradepress'); ?></h4>
            <ol>
                <li><?php esc_html_e('Check your internet connection', 'tradepress'); ?></li>
                <li><?php esc_html_e('Verify your API credentials in the Settings tab', 'tradepress'); ?></li>
                <li><?php esc_html_e('Make sure you\'re using the correct trading mode (paper/live)', 'tradepress'); ?></li>
                <li><?php printf(esc_html__('Check if %s API services are operational', 'tradepress'), esc_html($api_name)); ?></li>
            </ol>
        </div>
    <?php endif; ?>
    
    <button type="button" class="notice-dismiss api-test-dismiss">
        <span class="screen-reader-text"><?php esc_html_e('Dismiss', 'tradepress'); ?></span>
    </button>
</div>
<?php endif; ?>

<table class="endpoints-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Endpoint', 'tradepress'); ?></th>
            <th><?php esc_html_e('Description', 'tradepress'); ?></th>
            <th><?php esc_html_e('Method', 'tradepress'); ?></th>
            <th><?php esc_html_e('Usage Count', 'tradepress'); ?></th>
            <th><?php esc_html_e('Status', 'tradepress'); ?></th>
            <th><?php esc_html_e('Test', 'tradepress'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // Use the passed $endpoints variable as the primary source of data
        foreach ($endpoints as $endpoint) : 
            // Get database counter if available
            $endpoint_counter = 0;
            $endpoint_key = isset($endpoint['key']) ? $endpoint['key'] : '';
            $endpoint_name = isset($endpoint['name']) ? $endpoint['name'] : '';
            
            // Try to match the endpoint with a database record to get real usage count
            if (!empty($db_endpoints)) {
                foreach ($db_endpoints as $db_endpoint) {
                    // Match by name or key
                    $db_endpoint_name = isset($db_endpoint->endpoint_name) ? $db_endpoint->endpoint_name : 
                        (isset($db_endpoint->name) ? $db_endpoint->name : '');
                    $db_endpoint_key = isset($db_endpoint->endpoint_key) ? $db_endpoint->endpoint_key : 
                        (isset($db_endpoint->key) ? $db_endpoint->key : '');
                        
                    if (($db_endpoint_name && $db_endpoint_name === $endpoint_name) || 
                        ($db_endpoint_key && $db_endpoint_key === $endpoint_key)) {
                        // Found a match, use the database counter
                        $endpoint_counter = isset($db_endpoint->counter) ? (int)$db_endpoint->counter : 0;
                        break;
                    }
                }
            }
        ?>
        <tr>
            <td>
                <div class="endpoint-name"><?php echo esc_html($endpoint['name']); ?></div>
                <div class="endpoint-path"><?php echo esc_html($endpoint['endpoint']); ?></div>
            </td>
            <td><?php echo esc_html($endpoint['description']); ?></td>
            <td><span class="method-badge method-<?php echo strtolower(esc_attr($endpoint['method'])); ?>"><?php echo esc_html($endpoint['method']); ?></span></td>
            <td class="usage-count"><?php echo esc_html(number_format($endpoint_counter)); ?></td>
            <td>
                <div class="status-indicator">
                    <div class="status-dot <?php echo esc_attr(get_status_color($endpoint['status'])); ?>"></div>
                    <div><?php echo esc_html(ucfirst($endpoint['status'])); ?></div>
                </div>
            </td>
            <td>
                <?php if ($endpoint['status'] === 'active') : ?>
                    <form class="test-endpoint-form" action="" method="post">
                        <input type="hidden" name="tradepress_test_endpoint" value="1">
                        <input type="hidden" name="endpoint" value="<?php echo esc_attr($endpoint['key']); ?>">
                        <input type="hidden" name="platform" value="<?php echo esc_attr($api_id); ?>">
                        <?php wp_nonce_field('tradepress_test_endpoint_nonce', 'tradepress_test_endpoint_nonce_' . $endpoint['key']); ?>
                        <button type="submit" class="button button-secondary test-endpoint" 
                                data-endpoint="<?php echo esc_attr($endpoint['key']); ?>"
                                data-api="<?php echo esc_attr($api_id); ?>">
                            <?php esc_html_e('Test', 'tradepress'); ?>
                        </button>
                    </form>
                <?php else : ?>
                    <button type="button" class="test-button maintenance" disabled>
                        <?php esc_html_e('Unavailable', 'tradepress'); ?>
                    </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>