<?php
/**
 * TradePress API Test Handler
 * 
 * Provides standardized API test reporting and handling across the plugin.
 *
 * @package TradePress
 * @subpackage API
 * @version 1.0.0
 * @since 2025-05-15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class TradePress_API_Test_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register the AJAX handler
        add_action('wp_ajax_tradepress_standardized_test_endpoint', array($this, 'handle_endpoint_test'));
        
        // Add necessary scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue assets for API testing
     */
    public function enqueue_assets() {
        // Only load assets on relevant admin pages
        $screen = get_current_screen();
        
        // Check if we're on a relevant page
        if ($screen && strpos($screen->id, 'tradepress') !== false) {
            // We can add specific scripts or styles here if needed
        }
    }
    
    /**
     * Handle API endpoint test AJAX requests
     */
    public function handle_endpoint_test() {
        // Verify nonce
        check_ajax_referer('tradepress_test_endpoint_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'tradepress'),
                'html' => $this->generate_html_report(array(
                    'success' => false,
                    'message' => __('You do not have permission to perform this action.', 'tradepress'),
                    'error_code' => 'permission_denied'
                ))
            ));
            return;
        }
        
        // Get required parameters
        $platform = isset($_POST['platform']) ? sanitize_text_field($_POST['platform']) : '';
        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $endpoint_key = isset($_POST['endpoint_key']) ? sanitize_text_field($_POST['endpoint_key']) : '';
        
        // Validate input
        if (empty($platform) || empty($endpoint)) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters.', 'tradepress'),
                'html' => $this->generate_html_report(array(
                    'success' => false,
                    'message' => __('Missing required parameters.', 'tradepress'),
                    'error_code' => 'missing_parameters'
                ))
            ));
            return;
        }
        
        // Determine endpoint handler based on platform
        $result = $this->perform_endpoint_test($platform, $endpoint, $endpoint_key);
        
        // Generate HTML report
        $html = $this->generate_html_report($result);
        
        // Send response
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => __('API test completed successfully.', 'tradepress'),
                'data' => $result['data'],
                'html' => $html,
                'report' => $result
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['message'],
                'html' => $html,
                'report' => $result
            ));
        }
    }
    
    /**
     * Perform API endpoint test
     *
     * @param string $platform The platform ID (e.g. 'alpaca', 'alphavantage')
     * @param string $endpoint The endpoint to test
     * @param string $endpoint_key The endpoint key (if different from endpoint)
     * @return array Test result
     */
    private function perform_endpoint_test($platform, $endpoint, $endpoint_key) {
        // Default result structure
        $result = array(
            'success' => false,
            'message' => '',
            'data' => null,
            'raw_response' => '',
            'timestamp' => current_time('mysql'),
            'platform' => $platform,
            'endpoint' => $endpoint,
            'endpoint_key' => $endpoint_key,
            'environment' => get_environment_mode(),
            'trading_mode' => $this->get_trading_mode($platform),
            'request' => array(),
            'debug' => array()
        );
        
        // Load the TradePress_Endpoint_Tester class if available
        if (file_exists(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/endpoint-tester.php')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/tradingplatforms/endpoint-tester.php';
            
            $tester = new TradePress_Endpoint_Tester();
            
            // Get endpoint details
            try {
                // Use reflection to access private method
                $reflection = new ReflectionObject($tester);
                $method = $reflection->getMethod('get_endpoint_details');
                $method->setAccessible(true);
                $endpoint_details = $method->invoke($tester, $endpoint, $platform);
                
                // Test the endpoint
                $test_result = $tester->test_endpoint($endpoint_details, $platform);
                
                // Process test result
                if (is_wp_error($test_result)) {
                    // Use the enhanced error report
                    $error_report = $this->generate_enhanced_error_report($tester, $endpoint_details, $test_result, $platform);
                    
                    $result['success'] = false;
                    $result['message'] = $test_result->get_error_message();
                    $result['error_code'] = $test_result->get_error_code();
                    $result['raw_response'] = $test_result->get_error_data();
                    $result['error_report'] = $error_report;
                } else {
                    $result['success'] = true;
                    $result['message'] = __('API test completed successfully.', 'tradepress');
                    $result['data'] = $test_result;
                }
                
                // Add endpoint details to the result
                $result['request']['endpoint_details'] = $endpoint_details;
            } catch (Exception $e) {
                $result['success'] = false;
                $result['message'] = __('Error initializing endpoint test: ', 'tradepress') . $e->getMessage();
                $result['debug'][] = 'Exception: ' . $e->getMessage();
                $result['debug'][] = 'Stack trace: ' . $e->getTraceAsString();
            }
        } else {
            // Fall back to a simpler test approach
            $result['success'] = false;
            $result['message'] = __('Endpoint tester class not available.', 'tradepress');
            $result['debug'][] = 'File not found: admin/page/tradingplatforms/endpoint-tester.php';
        }
        
        return $result;
    }
    
    /**
     * Get the current environment mode (Live or Demo)
     *
     * @return string Environment mode
     */
    private function get_environment_mode() {
        // Check for TRADEPRESS_DEMO_MODE constant first
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return 'Demo';
        }
        
        // Check for demo mode option next
        $demo_mode = get_option('tradepress_demo_mode', 'yes');
        if ($demo_mode === 'yes') {
            return 'Demo';
        }
        
        // If neither condition is met, return Live
        return 'Live';
    }
    
    /**
     * Generate enhanced error report using the endpoint tester
     *
     * @param TradePress_Endpoint_Tester $tester The endpoint tester instance
     * @param array $endpoint_details The endpoint details
     * @param WP_Error $error The error object
     * @param string $platform_id The platform ID
     * @return string The enhanced error report
     */
    private function generate_enhanced_error_report($tester, $endpoint_details, $error, $platform_id) {
        try {
            // Use reflection to access private method
            $reflection = new ReflectionObject($tester);
            $method = $reflection->getMethod('generate_error_report');
            $method->setAccessible(true);
            return $method->invoke($tester, $endpoint_details, $error, $platform_id);
        } catch (Exception $e) {
            // Fall back to basic error report if reflection fails
            return sprintf(
                "Error Report:\nPlatform: %s\nEndpoint: %s\nError: %s\nCode: %s",
                $platform_id,
                $endpoint_details['id'],
                $error->get_error_message(),
                $error->get_error_code()
            );
        }
    }
    
    /**
     * Get trading mode for a platform
     *
     * @param string $platform_id Platform identifier
     * @return string Trading mode (paper or live)
     */
    private function get_trading_mode($platform_id) {
        return get_option("TradePress_api_{$platform_id}_trading_mode", 'paper');
    }
    
    /**
     * Generate HTML report for an API test
     *
     * @param array $result The test result data
     * @return string HTML output
     */
    private function generate_html_report($result) {
        $is_success = isset($result['success']) ? $result['success'] : false;
        $platform = isset($result['platform']) ? $result['platform'] : '';
        $endpoint = isset($result['endpoint']) ? $result['endpoint'] : '';
        $message = isset($result['message']) ? $result['message'] : '';
        $error_code = isset($result['error_code']) ? $result['error_code'] : '';
        $data = isset($result['data']) ? $result['data'] : null;
        $raw_response = isset($result['raw_response']) ? $result['raw_response'] : '';
        $error_report = isset($result['error_report']) ? $result['error_report'] : '';
        $timestamp = isset($result['timestamp']) ? $result['timestamp'] : current_time('mysql');
        $environment = isset($result['environment']) ? $result['environment'] : get_environment_mode();
        $trading_mode = isset($result['trading_mode']) ? $result['trading_mode'] : 'paper';
        
        // Environment class for styling
        $env_class = $environment === 'Live' ? 'live-env' : 'demo-env';
        
        ob_start();
        ?>
        <div class="tradepress-api-test-report <?php echo $is_success ? 'report-success' : 'report-error'; ?>">
            <h3><?php echo $is_success ? esc_html__('API Test Results - Success', 'tradepress') : esc_html__('API Test Results - Error', 'tradepress'); ?></h3>
            
            <div class="test-result-header">
                <strong><?php esc_html_e('Platform:', 'tradepress'); ?></strong> <?php echo esc_html(ucfirst($platform)); ?><br>
                <strong><?php esc_html_e('Endpoint:', 'tradepress'); ?></strong> <?php echo esc_html($endpoint); ?><br>
                <strong><?php esc_html_e('Status:', 'tradepress'); ?></strong> 
                <span class="<?php echo $is_success ? 'success-text' : 'error-text'; ?>">
                    <?php echo $is_success ? esc_html__('Success', 'tradepress') : esc_html__('Error', 'tradepress'); ?>
                </span><br>
                <strong><?php esc_html_e('Environment3:' . $environment . '?', 'tradepress'); ?></strong> 
                <span class="<?php echo esc_attr($env_class); ?>">
                    <?php echo esc_html($environment); ?>
                </span><br>
                <strong><?php esc_html_e('Trading Mode:', 'tradepress'); ?></strong> <?php echo esc_html($trading_mode); ?><br>
                <strong><?php esc_html_e('Date/Time:', 'tradepress'); ?></strong> <?php echo esc_html($timestamp); ?><br>
                <?php if (!$is_success && !empty($error_code)): ?>
                    <strong><?php esc_html_e('Error Code:', 'tradepress'); ?></strong> <?php echo esc_html($error_code); ?><br>
                <?php endif; ?>
            </div>
            
            <?php if (!$is_success && !empty($message)): ?>
                <h4><?php esc_html_e('Error Message:', 'tradepress'); ?></h4>
                <div class="error-message">
                    <?php echo esc_html($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="api-response-section">
                <h4><?php esc_html_e('API Response:', 'tradepress'); ?></h4>
                <textarea class="api-response-text" readonly onclick="this.select()">
                <?php 
                    if ($is_success && is_array($data)) {
                        echo esc_textarea(json_encode($data, JSON_PRETTY_PRINT));
                    } else {
                        echo esc_textarea(!empty($raw_response) ? 
                            (is_string($raw_response) ? $raw_response : json_encode($raw_response, JSON_PRETTY_PRINT)) : 
                            ($data ? json_encode($data, JSON_PRETTY_PRINT) : 'No data returned'));
                    }
                ?>
                </textarea>
                <span class="copy-hint"><?php esc_html_e('Click to select all. Ctrl+C to copy.', 'tradepress'); ?></span>
            </div>
            
            <?php if (!$is_success && !empty($error_report)): ?>
                <div class="ai-report-section">
                    <h4><?php esc_html_e('AI Troubleshooting Report:', 'tradepress'); ?></h4>
                    <textarea class="ai-report-text" readonly onclick="this.select()"><?php echo esc_textarea($error_report); ?></textarea>
                    <span class="copy-hint"><?php esc_html_e('Click to select all. Ctrl+C to copy.', 'tradepress'); ?></span>
                </div>
                
                <div class="error-guidance">
                    <h4><?php esc_html_e('Troubleshooting steps:', 'tradepress'); ?></h4>
                    <ol>
                        <li><?php esc_html_e('Check your internet connection', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Verify your API credentials in the Settings tab', 'tradepress'); ?></li>
                        <li><?php esc_html_e('Make sure you\'re using the correct trading mode (paper/live)', 'tradepress'); ?></li>
                        <li><?php printf(esc_html__('Check if %s API services are operational', 'tradepress'), esc_html(ucfirst($platform))); ?></li>
                        <li><?php esc_html_e('Look for specific error details in the AI Troubleshooting Report', 'tradepress'); ?></li>
                    </ol>
                </div>
            <?php endif; ?>
            
            <?php if (current_user_can('manage_options') && WP_DEBUG): ?>
                <div class="debug-details-toggle">
                    <a href="#" class="toggle-debug-info"><?php esc_html_e('Show Debug Details', 'tradepress'); ?></a>
                    <div class="debug-details" style="display:none;">
                        <pre><?php echo esc_html(print_r($result, true)); ?></pre>
                    </div>
                </div>
            <?php endif; ?>
            
            <button type="button" class="notice-dismiss api-test-dismiss">
                <span class="screen-reader-text"><?php esc_html_e('Dismiss', 'tradepress'); ?></span>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the handler
new TradePress_API_Test_Handler();
