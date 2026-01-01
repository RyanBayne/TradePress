<?php
/**
 * Admin View: API - Alpha Vantage Tab
 * 
 * @version 1.0.1
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Require the Alpha Vantage endpoints class
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alphavantage/alphavantage-endpoints.php';
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alphavantage/alphavantage-api.php';

// Include helper functions if not already included
require_once dirname(__FILE__) . '/helpers/api-tab-helpers.php';

// Get API provider details from the API directory
require_once TRADEPRESS_PLUGIN_DIR . 'api/api-directory.php';
$provider = TradePress_API_Directory::get_provider('alphavantage');

// Get real endpoints from the class
$real_endpoints = TradePress_AlphaVantage_Endpoints::get_endpoints();

// Initialize the API
$api = new TradePress_AlphaVantage_API();
$api_key = get_option('TradePress_api_alphavantage_key', '');
$api_stats = $api->get_api_call_stats();

// Get a random symbol for testing
$random_symbol = $api->get_random_symbol();

// Set up the API ID first
$api_id = 'alphavantage';

// Status data - Check actual connection status
if (empty($api_key)) {
    $local_status = get_real_api_local_status($api_id);
} else {
    $connection_test = $api->test_connection();
    $local_status = array(
        'status' => is_wp_error($connection_test) ? 'error' : 'active',
        'message' => is_wp_error($connection_test) ? $connection_test->get_error_message() : 'Connected and authenticated'
    );
}

$service_status = array(
    'status' => 'operational', // Alpha Vantage doesn't have a public status API, so we assume operational
    'message' => 'All systems operational',
    'last_updated' => date('Y-m-d H:i:s')
);

// Rate limits - Use real data from the API stats
$rate_limits = array(
    'minute_quota' => $api_stats['minute_limit_free'] ?? 0,
    'minute_used' => $api_stats['minute_count'] ?? 0,
    'daily_quota' => $api_stats['daily_limit_free'] ?? 0,
    'daily_used' => $api_stats['daily_count'] ?? 0,
    'reset_time' => $api_stats['reset_time'] ?? null
);

// Generate endpoint data based on actual API information
$endpoints = array();
foreach ($real_endpoints as $key => $endpoint) {
    if (isset($endpoint['function'])) {
        $endpoints[] = array(
            'name' => ucfirst(str_replace('_', ' ', $key)),
            'endpoint' => isset($endpoint['function']) ? $endpoint['function'] : '',
            'description' => isset($endpoint['description']) ? $endpoint['description'] : '',
            'usage_count' => get_transient('alphavantage_endpoint_' . $key . '_count') ?: 0,
            'status' => 'active', // We assume all endpoints are active
            'method' => 'GET',
            'key' => $key
        );
    }
}

// Set up the variables needed for the template
$api_name = 'Alpha Vantage';
$api_description = __('Financial data API providing realtime and historical stock data, forex, cryptocurrency, and technical indicators.', 'tradepress');
$api_version = 'v1';
$api_logo_url = !empty($provider['logo_url']) ? $provider['logo_url'] : TRADEPRESS_PLUGIN_URL . 'assets/images/alphavantage-logo.png';

// Documentation links
$documentation_links = array(
    array(
        'text' => __('API Documentation', 'tradepress'),
        'url' => 'https://www.alphavantage.co/documentation/',
        'icon' => 'external'
    ),
    array(
        'text' => __('Support', 'tradepress'),
        'icon' => 'book',
        'url' => 'https://www.alphavantage.co/support/'
    )
);

// Define data types for explorer
$explorer_data_types = array(
    'time_series' => __('Time Series', 'tradepress'),
    'indicators' => __('Technical Indicators', 'tradepress'),
    'fx' => __('Forex', 'tradepress'),
    'crypto' => __('Cryptocurrency', 'tradepress'),
    'fundamentals' => __('Fundamentals', 'tradepress')
);

// Include the API tab template
include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/template.api-tab.php');
?>

<!-- Documentation Links -->
<div id="documentation-links" class="content-section status-box" style="display: none;">
    <div class="status-header">
        <h3><?php esc_html_e('Documentation & Resources', 'tradepress'); ?></h3>
    </div>
    
    <div class="documentation-links">
        <?php foreach ($documentation_links as $link): ?>
            <a href="<?php echo esc_url($link['url']); ?>" class="documentation-link" target="_blank">
                <?php if (isset($link['icon']) && $link['icon'] === 'external'): ?>
                    <span class="dashicons dashicons-external"></span>
                <?php elseif (isset($link['icon'])): ?>
                    <span class="dashicons dashicons-<?php echo esc_attr($link['icon']); ?>"></span>
                <?php else: ?>
                    <span class="dashicons dashicons-admin-links"></span>
                <?php endif; ?>
                <?php echo esc_html($link['text']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Include the data explorer partial -->
<div id="data-explorer-section" class="content-section" style="display: none;">    
    <?php include(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/view/partials/data-explorer.php'); ?>
</div>

<?php
// Set random symbol as default for the data explorer
wp_add_inline_script('tradepress-tradingplatforms-alphavantage', 'jQuery(document).ready(function($) { $("#data-explorer-symbol").val("' . esc_js($random_symbol) . '"); });');
?>
