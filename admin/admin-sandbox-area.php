<?php
/**
 * TradePress Admin Sandbox Area
 * 
 * Handles the sandbox area for testing and diagnostics.
 * 
 * @package TradePress
 * @subpackage Admin
 * @version 1.0.1
 */

class TradePress_Admin_Sandbox_Page {

    /**
     * Generate API test error report with enhanced platform information
     * 
     * @param string $endpoint The API endpoint being tested
     * @param mixed $response The API response or error message
     * @param string $platform The API platform (e.g., 'Alpha Vantage', 'Alpaca', etc.)
     * @return string Formatted error report
     */
    private function generate_api_error_report($endpoint, $response, $platform = 'Alpha Vantage') {
        $report = "### API Test Error Report ###\n";
        $report .= "Platform: " . esc_html($platform) . "\n"; // Add platform information
        $report .= "Endpoint: " . esc_html($endpoint) . "\n";
        
        // Status determination
        $status = "Connection Error";
        if (is_wp_error($response)) {
            $status = "WP Error: " . $response->get_error_message();
        } elseif (is_object($response) && property_exists($response, 'status')) {
            $status = $response->status;
        } elseif (is_array($response) && isset($response['status'])) {
            $status = $response['status'];
        }
        
        $report .= "Status: " . esc_html($status) . "\n";
        
        // Additional environment information
        $report .= "Environment: " . (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE ? 'Demo' : 'Production') . "\n";
        $report .= "Trading Mode: " . get_option('tradepress_trading_mode', 'paper') . "\n";
        $report .= "API Version: " . get_option('tradepress_api_version', 'v2') . "\n";
        $report .= "Time: " . current_time('d/m/Y, H:i:s') . "\n";
        
        // Error details
        $report .= "Error: endpoint not found [DEBUG: " . date('Y-m-d\TH:i:s\Z', time()) . "]\n\n";
        
        // Add raw response details
        $report .= "Error Details: \n";
        $raw_response = '';
        
        if (is_wp_error($response)) {
            $raw_response = $response->get_error_message();
        } elseif (is_object($response) || is_array($response)) {
            $raw_response = json_encode($response, JSON_PRETTY_PRINT);
        } elseif (is_string($response)) {
            $raw_response = $response;
        }
        
        $report .= "Raw Response Size: " . strlen($raw_response) . " characters\n";
        $report .= "            " . wp_html_excerpt($raw_response, 1000, '...') . "\n";
        
        return $report;
    }

    /**
     * Process API test request
     */
    public function test_api_endpoint() {
        // Security check
        check_ajax_referer('tradepress_sandbox_nonce', 'nonce');
        
        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $platform = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : 'Alpha Vantage';
        
        // Here we would normally make the actual API call
        // For demo, let's simulate a response based on endpoint
        $response = $this->make_test_api_call($endpoint, $platform);
        
        if (is_wp_error($response) || 
            (is_object($response) && property_exists($response, 'error')) || 
            (is_array($response) && isset($response['error'])) ||
            empty($response)) {
            
            // Generate and return error report with platform info
            $error_report = $this->generate_api_error_report($endpoint, $response, $platform);
            wp_send_json_error(array('message' => $error_report));
        } else {
            // Return successful response with platform info
            wp_send_json_success(array(
                'message' => 'API test completed successfully!',
                'data' => $response,
                'platform' => $platform
            ));
        }
        
        wp_die();
    }

    /**
     * Make test API call
     * 
     * @param string $endpoint The API endpoint to test
     * @param string $platform The API platform to use
     * @return mixed API response or WP_Error
     */
    private function make_test_api_call($endpoint, $platform) {
        // Here we would implement the actual API call based on platform
        // For now, we'll just return a simulated response
        
        switch ($platform) {
            case 'Alpha Vantage':
                return $this->make_alpha_vantage_call($endpoint);
                
            case 'Alpaca':
                return $this->make_alpaca_call($endpoint);
                
            default:
                return new WP_Error('invalid_platform', 'Unsupported API platform');
        }
    }

    /**
     * Simulate Alpha Vantage API call
     * 
     * @param string $endpoint The API endpoint to test
     * @return mixed Simulated response or WP_Error
     */
    private function make_alpha_vantage_call($endpoint) {
        // Simulate a successful response
        return array('status' => 'success', 'data' => 'Alpha Vantage response for ' . $endpoint);
    }

    /**
     * Simulate Alpaca API call
     * 
     * @param string $endpoint The API endpoint to test
     * @return mixed Simulated response or WP_Error
     */
    private function make_alpaca_call($endpoint) {
        // Simulate an error response
        return new WP_Error('api_error', 'Alpaca API error for ' . $endpoint);
    }
}
?>