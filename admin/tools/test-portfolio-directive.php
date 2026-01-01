<?php
/**
 * Portfolio Performance Directive Test
 * 
 * Tests the Portfolio Performance directive with Alpaca API integration
 * 
 * @package TradePress
 * @subpackage Tools
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test Portfolio Performance Directive
 */
function tradepress_test_portfolio_directive() {
    echo '<h2>Portfolio Performance Directive Test</h2>';
    
    // Load the directive
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/portfolio-performance.php';
    
    // Initialize the directive
    $directive = new TradePress_Portfolio_Performance_Directive();
    
    echo '<h3>Test 1: Directive Initialization</h3>';
    if ($directive) {
        echo '<div class="notice notice-success"><p>Success: Portfolio Performance directive loaded</p></div>';
        echo '<p><strong>Directive ID:</strong> ' . $directive->directive_id . '</p>';
        echo '<p><strong>Name:</strong> ' . $directive->name . '</p>';
        echo '<p><strong>Description:</strong> ' . $directive->description . '</p>';
    } else {
        echo '<div class="notice notice-error"><p>Failed: Could not load directive</p></div>';
        return;
    }
    
    echo '<h3>Test 2: API Requirements</h3>';
    $requirements = $directive->get_api_requirements();
    echo '<p><strong>Required APIs:</strong></p>';
    echo '<pre>' . print_r($requirements, true) . '</pre>';
    
    echo '<h3>Test 3: Calculate Score (Test Symbol: AAPL)</h3>';
    $result = $directive->calculate_score('AAPL');
    
    if (is_array($result)) {
        echo '<div class="notice notice-success"><p>Success: Score calculation completed</p></div>';
        echo '<p><strong>Score:</strong> ' . $result['score'] . '</p>';
        echo '<p><strong>Signals:</strong></p>';
        if (!empty($result['signals'])) {
            echo '<ul>';
            foreach ($result['signals'] as $signal) {
                echo '<li>' . esc_html($signal) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No signals generated</p>';
        }
        
        if (!empty($result['debug'])) {
            echo '<p><strong>Debug Information:</strong></p>';
            echo '<pre>' . print_r($result['debug'], true) . '</pre>';
        }
    } else {
        echo '<div class="notice notice-error"><p>Failed: Score calculation failed</p></div>';
    }
    
    echo '<h3>Test 4: Alpaca API Integration Status</h3>';
    
    // Check if Alpaca API credentials are configured
    $api_key = get_option('TradePress_api_alpaca_key', '');
    $api_secret = get_option('TradePress_api_alpaca_secret', '');
    
    if (empty($api_key) || empty($api_secret)) {
        echo '<div class="notice notice-warning"><p>Warning: Alpaca API credentials not configured. This directive requires Alpaca API access.</p></div>';
        echo '<p>To configure Alpaca API:</p>';
        echo '<ol>';
        echo '<li>Go to Trading Platforms → Alpaca → Settings</li>';
        echo '<li>Enter your API Key and Secret Key</li>';
        echo '<li>Save settings</li>';
        echo '</ol>';
    } else {
        echo '<div class="notice notice-success"><p>Success: Alpaca API credentials are configured</p></div>';
        
        // Test API connection
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/alpaca/alpaca-api.php';
        $api = new TradePress_Alpaca_API();
        $test_result = $api->test_connection();
        
        if (is_wp_error($test_result)) {
            echo '<div class="notice notice-error"><p>API Connection Failed: ' . $test_result->get_error_message() . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>API Connection: Working</p></div>';
        }
    }
}

// Run the test
tradepress_test_portfolio_directive();