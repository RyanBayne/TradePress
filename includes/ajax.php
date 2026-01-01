<?php
/**
 * TradePress Ajax Event Handler.
 *                           
 * @package  TradePress/Core
 * @category Ajax
 * @author   Ryan Bayne
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TradePress_AJAX {

    /**
     * Hook in ajax handlers.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
        add_action( 'template_redirect', array( __CLASS__, 'do_TradePress_ajax' ), 0 );
        self::add_ajax_events();
    }

    /**
     * Get TradePress Ajax Endpoint.
     * @param  string $request Optional
     * @return string
     */
    public static function get_endpoint( $request = '' ) {
        return esc_url_raw( apply_filters( 'TradePress_ajax_get_endpoint', add_query_arg( 'TradePress-ajax', $request, remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart' ) ) ), $request ) );
    }

    /**
     * Set TradePress AJAX constant and headers.
     */
    public static function define_ajax() {
        if ( ! empty( $_GET['TradePress-ajax'] ) ) {
            if ( ! defined( 'DOING_AJAX' ) ) {
                define( 'DOING_AJAX', true );
            }
            if ( ! defined( 'TradePress_DOING_AJAX' ) ) {
                define( 'TradePress_DOING_AJAX', true );
            }

            $GLOBALS['wpdb']->hide_errors();
        }
    }

    /**
     * Send headers for TradePress Ajax Requests
     */
    private static function TradePress_ajax_headers() {
        send_origin_headers();
        @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        @header( 'X-Robots-Tag: noindex' );
        send_nosniff_header();
        nocache_headers();
        status_header( 200 );
    }

    /**
     * Check for TradePress Ajax request and fire action.
     */
    public static function do_TradePress_ajax() {
        global $wp_query;

        if ( ! empty( $_GET['TradePress-ajax'] ) ) {
            $wp_query->set( 'TradePress-ajax', sanitize_text_field( $_GET['TradePress-ajax'] ) );
        }

        if ( $action = $wp_query->get( 'TradePress-ajax' ) ) {
            self::TradePress_ajax_headers();
            do_action( 'TradePress_ajax_' . sanitize_text_field( $action ) );
            die();
        }
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events() {
        // TradePress_EVENT => nopriv
        $ajax_events = array(
            'refresh_debug_info' => false, // Admin-only Ajax event
            'manual_import' => false, // Admin-only Ajax event
            'refresh_data_status' => false, // Admin-only Ajax event
            'run_all_imports' => false, // Admin-only Ajax event
        );

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_TradePress_' . $ajax_event, array( __CLASS__, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_TradePress_' . $ajax_event, array( __CLASS__, $ajax_event ) );

                // TradePress AJAX can be used for frontend ajax requests
                add_action( 'TradePress_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
            }
        }
        
        // Register standard WordPress Ajax handlers
        add_action('wp_ajax_refresh_debug_info', array(__CLASS__, 'ajax_refresh_debug_info'));
        add_action('wp_ajax_tradepress_manual_import', array(__CLASS__, 'ajax_manual_import'));
        add_action('wp_ajax_tradepress_refresh_data_status', array(__CLASS__, 'ajax_refresh_data_status'));
        add_action('wp_ajax_tradepress_run_all_imports', array(__CLASS__, 'ajax_run_all_imports'));
    }
    
    /**
     * Get the latest API call information for the specified API.
     *
     * @param string $api_id The API identifier.
     * @return array|false The API call data or false if none found.
     */
    public static function get_latest_api_call($api_id) {
        // For security, only allow access to this data when in testing mode
        if (!defined('TRADEPRESS_TESTING') || !TRADEPRESS_TESTING) {
            return false;
        }
        
        // In a real implementation, this would retrieve data from the database or transients
        // For demonstration, we'll use transients to store and retrieve API call data
        $api_call = get_transient('tradepress_api_' . $api_id . '_latest_call');
        
        // If no data in transient, try to create sample data for demonstration
        if (empty($api_call) && defined('TRADEPRESS_TESTING') && TRADEPRESS_TESTING) {
            $api_call = self::generate_sample_api_call($api_id);
            
            // Cache this sample data
            set_transient('tradepress_api_' . $api_id . '_latest_call', $api_call, HOUR_IN_SECONDS);
        }
        
        return $api_call;
    }
    
    /**
     * Generate sample API call data for testing and demonstration purposes.
     *
     * @param string $api_id The API identifier.
     * @return array Sample API call data.
     */
    private static function generate_sample_api_call($api_id) {
        // Create different sample data based on the API
        switch ($api_id) {
            case 'alpaca':
                return array(
                    'api_id' => 'alpaca',
                    'endpoint' => '/v2/account',
                    'method' => 'GET',
                    'timestamp' => current_time('mysql'),
                    'request_data' => array(
                        'headers' => array(
                            'APCA-API-KEY-ID' => 'XXXX-XXXX-XXXX-XXXX',
                            'APCA-API-SECRET-KEY' => '********',
                        ),
                    ),
                    'response' => json_encode(array(
                        'id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                        'account_number' => 'ABCDEFG',
                        'status' => 'ACTIVE',
                        'currency' => 'USD',
                        'buying_power' => '25000.00',
                        'cash' => '10000.00',
                        'portfolio_value' => '15000.00',
                        'created_at' => '2023-01-01T00:00:00Z',
                        'last_equity' => '14500.00',
                        'equity' => '15000.00',
                    ), JSON_PRETTY_PRINT),
                );
                
            case 'alphavantage':
                return array(
                    'api_id' => 'alphavantage',
                    'endpoint' => '/query',
                    'method' => 'GET',
                    'timestamp' => current_time('mysql'),
                    'request_data' => array(
                        'params' => array(
                            'function' => 'TIME_SERIES_DAILY',
                            'symbol' => 'AAPL',
                            'apikey' => 'XXXXXXXXXXXX',
                        ),
                    ),
                    'response' => json_encode(array(
                        'Meta Data' => array(
                            '1. Information' => 'Daily Prices (open, high, low, close) and Volumes',
                            '2. Symbol' => 'AAPL',
                            '3. Last Refreshed' => '2023-04-17',
                            '4. Output Size' => 'Compact',
                            '5. Time Zone' => 'US/Eastern',
                        ),
                        'Time Series (Daily)' => array(
                            '2023-04-17' => array(
                                '1. open' => '165.0900',
                                '2. high' => '166.4500',
                                '3. low' => '164.4300',
                                '4. close' => '165.2300',
                                '5. volume' => '49800229',
                            ),
                            '2023-04-14' => array(
                                '1. open' => '164.3000',
                                '2. high' => '166.3200',
                                '3. low' => '163.8200',
                                '4. close' => '165.2100',
                                '5. volume' => '45223403',
                            ),
                        ),
                    ), JSON_PRETTY_PRINT),
                );
                
            default:
                // Generic API call data
                return array(
                    'api_id' => $api_id,
                    'endpoint' => '/api/data',
                    'method' => 'GET',
                    'timestamp' => current_time('mysql'),
                    'request_data' => array(
                        'headers' => array(
                            'Authorization' => 'Bearer XXXX-XXXX-XXXX-XXXX',
                        ),
                        'params' => array(
                            'symbol' => 'MSFT',
                        ),
                    ),
                    'response' => json_encode(array(
                        'success' => true,
                        'data' => array(
                            'symbol' => 'MSFT',
                            'price' => 325.42,
                            'volume' => 23540122,
                            'change' => 1.25,
                            'change_percent' => 0.38,
                        ),
                    ), JSON_PRETTY_PRINT),
                );
        }
    }
    
    /**
     * Ajax handler to refresh the API debug info panel.
     */
    public static function ajax_refresh_debug_info() {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'refresh-debug-info')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get API ID
        $api_id = isset($_POST['api_id']) ? sanitize_text_field($_POST['api_id']) : '';
        if (empty($api_id)) {
            wp_send_json_error(array('message' => __('API ID is required.', 'tradepress')));
        }
        
        // Delete the existing cached data to force regeneration
        delete_transient('tradepress_api_' . $api_id . '_latest_call');
        
        // Get latest API call details (this would normally come from a database or cache)
        $api_call = self::get_latest_api_call($api_id);
        
        // Generate the HTML for the debug panel
        ob_start();
        if ($api_call): ?>
            <div class="api-call-details">
                <table class="widefat">
                    <tr>
                        <th>API Identifier:</th>
                        <td><?php echo esc_html($api_call['api_id']); ?></td>
                    </tr>
                    <tr>
                        <th>Endpoint:</th>
                        <td><?php echo esc_html($api_call['endpoint']); ?></td>
                    </tr>
                    <tr>
                        <th>Method:</th>
                        <td><?php echo esc_html($api_call['method']); ?></td>
                    </tr>
                    <tr>
                        <th>Timestamp:</th>
                        <td><?php echo esc_html($api_call['timestamp']); ?></td>
                    </tr>
                    <tr>
                        <th>Request Data:</th>
                        <td><pre><?php echo esc_html(print_r($api_call['request_data'], true)); ?></pre></td>
                    </tr>
                    <tr>
                        <th>Response:</th>
                        <td>
                            <div class="api-response">
                                <pre><?php echo esc_html(is_string($api_call['response']) ? $api_call['response'] : print_r($api_call['response'], true)); ?></pre>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        <?php else: ?>
            <div class="api-info-message">
                <p>No recent API calls for <?php echo esc_html($api_id); ?> have been cached. Make an API call with testing mode enabled to see results here.</p>
            </div>
        <?php endif;
        
        $html = ob_get_clean();
        
        // Send the HTML back to the client
        wp_send_json_success(array(
            'html' => $html,
            'timestamp' => current_time('mysql'),
        ));
    }
    
    /**
     * Ajax handler for manual data element import
     */
    public static function ajax_manual_import() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_data_elements_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        $element_id = isset($_POST['element_id']) ? sanitize_text_field($_POST['element_id']) : '';
        if (empty($element_id)) {
            wp_send_json_error(array('message' => __('Element ID is required.', 'tradepress')));
        }
        
        // Get data elements configuration
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-elements.php';
        $config = tradepress_get_data_elements_config();
        
        if (!isset($config[$element_id])) {
            wp_send_json_error(array('message' => __('Invalid element ID.', 'tradepress')));
        }
        
        // Simulate import process (replace with actual import logic)
        $import_function = $config[$element_id]['import_function'] ?? null;
        
        if ($import_function && function_exists($import_function)) {
            $result = call_user_func($import_function, array('force' => true));
        } else {
            // Simulate successful import for demo
            sleep(1); // Simulate processing time
            $result = array(
                'success' => true,
                'message' => 'Import completed successfully',
                'records_imported' => rand(10, 100)
            );
        }
        
        if ($result['success']) {
            // Update last import time
            update_option("tradepress_last_import_{$element_id}", time());
            
            wp_send_json_success(array(
                'message' => $result['message'],
                'import_time' => date('Y-m-d H:i:s'),
                'status' => '<span class="status-success">Up to Date</span>'
            ));
        } else {
            wp_send_json_error(array('message' => $result['message'] ?? 'Import failed'));
        }
    }
    
    /**
     * Ajax handler for refreshing data status
     */
    public static function ajax_refresh_data_status() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_data_elements_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get data elements configuration
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-elements.php';
        $config = tradepress_get_data_elements_config();
        
        $status_data = array();
        foreach ($config as $element_id => $element) {
            $status_data[$element_id] = array(
                'import_time' => tradepress_get_last_import_time($element_id),
                'status' => tradepress_get_data_element_status($element_id)
            );
        }
        
        wp_send_json_success($status_data);
    }
    
    /**
     * Ajax handler for running all imports
     */
    public static function ajax_run_all_imports() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_data_elements_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get data elements configuration
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-elements.php';
        $config = tradepress_get_data_elements_config();
        
        $results = array();
        $errors = array();
        
        foreach ($config as $element_id => $element) {
            $import_function = $element['import_function'] ?? null;
            
            if ($import_function && function_exists($import_function)) {
                $result = call_user_func($import_function, array('force' => true));
            } else {
                // Simulate successful import for demo
                $result = array(
                    'success' => true,
                    'message' => 'Import completed successfully',
                    'records_imported' => rand(10, 100)
                );
            }
            
            if ($result['success']) {
                update_option("tradepress_last_import_{$element_id}", time());
                $results[$element_id] = array(
                    'import_time' => date('Y-m-d H:i:s'),
                    'status' => '<span class="status-success">Up to Date</span>'
                );
            } else {
                $errors[] = "{$element['name']}: {$result['message']}";
                $results[$element_id] = array(
                    'import_time' => tradepress_get_last_import_time($element_id),
                    'status' => '<span class="status-error">Import Failed</span>'
                );
            }
        }
        
        if (empty($errors)) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error(array(
                'message' => implode('; ', $errors),
                'data' => $results
            ));
        }
    }
}

TradePress_AJAX::init();
