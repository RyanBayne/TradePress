<?php
/**
 * Direct API Test Page
 * 
 * A standalone API test page that bypasses WordPress AJAX
 * for debugging Alpaca API connection issues.
 * 
 * @package TradePress
 * @version 1.0.0
 */

// Initialize WordPress
require_once('../../../../wp-load.php');

// Check admin privileges
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

// Load the direct API handler
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alpaca/alpaca-direct.php';

// Enqueue the CSS and JS files
wp_enqueue_style(
    'tradepress-direct-api-test',
    TRADEPRESS_PLUGIN_URL . 'assets/css/pages/direct-api-test.css',
    array(),
    TRADEPRESS_VERSION
);

wp_enqueue_script(
    'tradepress-direct-api-test',
    TRADEPRESS_PLUGIN_URL . 'assets/js/direct-api-test.js',
    array(),
    TRADEPRESS_VERSION,
    true
);

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$endpoint = isset($_GET['endpoint']) ? sanitize_text_field($_GET['endpoint']) : 'account';
$mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'paper';
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'test';

// Get API credentials
if ($mode === 'paper') {
    $api_key = get_option('TradePress_api_alpaca_papermoney_apikey', '');
    $api_secret = get_option('TradePress_api_alpaca_papermoney_secretkey', '');
} else {
    $api_key = get_option('TradePress_api_alpaca_realmoney_apikey', '');
    $api_secret = get_option('TradePress_api_alpaca_realmoney_secretkey', '');
}

// Results array
$result = [
    'success' => false,
    'timestamp' => date('Y-m-d H:i:s'),
    'endpoint' => $endpoint,
    'mode' => $mode,
    'message' => '',
    'data' => null,
    'debug' => []
];

// Check if credentials are configured
if (empty($api_key) || empty($api_secret)) {
    $result['message'] = 'API credentials are not configured';
    $result['debug'][] = 'Missing API key and/or secret';
} else {
    // Initialize the API client
    $api = new TradePress_Alpaca_Direct($api_key, $api_secret, $mode);
    
    // Make request based on endpoint
    if ($action === 'test') {
        try {
            if ($endpoint === 'account') {
                $api_result = $api->get_account();
            } else if ($endpoint === 'watchlists') {
                $api_result = $api->get_watchlists();
            } else {
                // Default to generic request
                $api_result = $api->request($endpoint);
            }
            
            $result['success'] = $api_result['success'];
            $result['message'] = $api_result['success'] ? 'API request successful' : $api_result['message'];
            $result['data'] = $api_result['data'] ?? null;
            
            // Add debug information
            $result['debug'][] = 'API endpoint: ' . $endpoint;
            $result['debug'][] = 'Response code: ' . ($api_result['code'] ?? 'unknown');
        } catch (Exception $e) {
            $result['message'] = 'Exception: ' . $e->getMessage();
            $result['debug'][] = 'Exception caught during API request';
            $result['debug'][] = $e->getTraceAsString();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TradePress Direct API Test</title>
    <?php wp_print_styles(); ?>
    <?php wp_print_scripts(); ?>
</head>
<body>
    <div class="container">
        <a href="<?php echo esc_url( admin_url('admin.php?page=tradepress-apis') ); ?>" class="back-link">&larr; Back to TradePress API Dashboard</a>
        
        <h1>TradePress Direct API Test <span class="debug-badge">Direct Test Mode</span></h1>
        
        <div class="panel">
            <p>This page bypasses WordPress AJAX to test the Alpaca API directly. Use this for diagnosing API connection issues.</p>
            
            <div class="mode-toggle">
                <strong>Trading Mode:</strong>
                <button class="mode-button <?php echo $mode === 'paper' ? 'active' : ''; ?>" data-mode="paper">Paper Trading</button>
                <button class="mode-button <?php echo $mode === 'live' ? 'active' : ''; ?>" data-mode="live">Live Trading</button>
            </div>
            
            <div class="endpoints">
                <strong>Select Endpoint:</strong>
                <button class="endpoint-button <?php echo $endpoint === 'account' ? 'active' : ''; ?>" data-endpoint="account">Account</button>
                <button class="endpoint-button <?php echo $endpoint === 'account/configurations' ? 'active' : ''; ?>" data-endpoint="account/configurations">Account Config</button>
                <button class="endpoint-button <?php echo $endpoint === 'watchlists' ? 'active' : ''; ?>" data-endpoint="watchlists">Watchlists</button>
                <button class="endpoint-button <?php echo $endpoint === 'positions' ? 'active' : ''; ?>" data-endpoint="positions">Positions</button>
                <button class="endpoint-button <?php echo $endpoint === 'orders' ? 'active' : ''; ?>" data-endpoint="orders">Orders</button>
                <button class="endpoint-button <?php echo $endpoint === 'assets' ? 'active' : ''; ?>" data-endpoint="assets">Assets</button>
            </div>
            
            <?php if (empty($api_key) || empty($api_secret)): ?>
                <div class="error">
                    <p><strong>Error:</strong> API credentials are not configured. Please configure your credentials in the TradePress API Settings.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="panel result-container">
            <h2>API Test Results <span class="debug-badge"><?php echo htmlspecialchars($endpoint); ?></span></h2>
            
            <?php if ($result['success']): ?>
                <h3 class="success">Success</h3>
            <?php else: ?>
                <h3 class="error">Error: <?php echo htmlspecialchars($result['message']); ?></h3>
            <?php endif; ?>
            
            <div class="api-response">
                <h3>API Response:</h3>
                <pre><?php echo $result['data'] ? json_encode($result['data'], JSON_PRETTY_PRINT) : 'No data returned'; ?></pre>
            </div>
            
            <div class="debug-info">
                <h3>Debug Information</h3>
                <p><strong>Timestamp:</strong> <?php echo htmlspecialchars($result['timestamp']); ?></p>
                <p><strong>Endpoint:</strong> <?php echo htmlspecialchars($result['endpoint']); ?></p>
                <p><strong>Trading Mode:</strong> <?php echo htmlspecialchars($result['mode']); ?></p>
                <p><strong>API Key Present:</strong> <?php echo !empty($api_key) ? 'Yes' : 'No'; ?></p>
                <p><strong>API Secret Present:</strong> <?php echo !empty($api_secret) ? 'Yes' : 'No'; ?></p>
                
                <?php if (!empty($result['debug'])): ?>
                    <h4>Debug Log:</h4>
                    <pre><?php echo htmlspecialchars(implode("\n", $result['debug'])); ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>