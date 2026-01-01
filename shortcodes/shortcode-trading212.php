<?php
/**
 * Trading212 Shortcodes
 *
 * Shortcodes for displaying Trading212 data
 *
 * @package TradePress/Shortcodes
 * @version 1.0.0
 * @since 2023-07-11 12:15
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress_Shortcode_Trading212 Class
 */
class TradePress_Shortcode_Trading212 {
    
    /**
     * Trading212 Data Controller instance
     * @var TradePress_Trading212_Data_Controller
     */
    private $data_controller;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register shortcodes
        add_shortcode('trading212_api_status', array($this, 'shortcode_api_status'));
        
        // Load data controller when needed
        require_once dirname(dirname(__FILE__)) . '/api/trading212/trading212-data-controller.php';
        $this->data_controller = new TradePress_Trading212_Data_Controller();
        
        // Add shortcode assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue shortcode scripts and styles
     */
    public function enqueue_scripts() {
        global $post;
        
        // Only load if the shortcode is used
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'trading212_api_status')) {
            wp_enqueue_style('tradepress-trading212-shortcodes', TRADEPRESS_PLUGIN_URL . '/assets/css/trading212-shortcodes.css', array(), TRADEPRESS_VERSION);
        }
    }
    
    /**
     * Get data for a specific endpoint
     * 
     * @param string $endpoint Endpoint identifier
     * @return array|WP_Error Response data
     */
    private function get_endpoint_data($endpoint) {
        switch ($endpoint) {
            case 'account_info':
                return $this->data_controller->get_account_info();
            case 'account_cash':
                return $this->data_controller->get_account_cash();
            case 'instruments':
                // Limit to 5 instruments for display
                $instruments = $this->data_controller->get_instruments();
                if (!is_wp_error($instruments)) {
                    return array_slice($instruments, 0, 5);
                }
                return $instruments;
            case 'positions':
                return $this->data_controller->get_positions();
            case 'orders':
                return $this->data_controller->get_orders();
            case 'transactions':
                return $this->data_controller->get_transaction_history();
            case 'quotes':
                // Get default symbols or top positions
                $positions = $this->data_controller->get_positions();
                $symbols = array();
                if (!is_wp_error($positions) && !empty($positions)) {
                    foreach ($positions as $position) {
                        $symbols[] = $position['instrumentCode'];
                    }
                    // Limit to 5 symbols
                    $symbols = array_slice($symbols, 0, 5);
                } else {
                    // Default symbols if no positions
                    $symbols = array('AAPL', 'MSFT', 'GOOGL', 'AMZN', 'TSLA');
                }
                return $this->data_controller->get_market_quotes($symbols);
            case 'watchlists':
                return $this->data_controller->get_watchlists();
            default:
                return new WP_Error('invalid_endpoint', __('Invalid endpoint specified', 'tradepress'));
        }
    }
    
    /**
     * Trading212 API Status shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function shortcode_api_status($atts) {
        $atts = shortcode_atts(array(
            'demo' => 'yes', // Use demo data if API key is not available
        ), $atts, 'trading212_api_status');
        
        // Define all endpoints to display
        $endpoints = array(
            'account_info' => 'Account Information',
            'account_cash' => 'Account Cash',
            'instruments' => 'Instruments',
            'positions' => 'Positions',
            'orders' => 'Orders',
            'transactions' => 'Transactions',
            'quotes' => 'Market Quotes',
            'watchlists' => 'Watchlists'
        );
        
        ob_start();
        
        echo '<div class="tradepress-trading212-status">';
        echo '<h2>' . esc_html__('Trading212 API Status & Sample Data', 'tradepress') . '</h2>';
        
        $api_settings = get_option('tradepress_api_settings', array());
        $api_key = isset($api_settings['trading212_api_key']) ? $api_settings['trading212_api_key'] : '';
        
        if (empty($api_key) && $atts['demo'] !== 'yes') {
            echo '<div class="tradepress-trading212-notice tradepress-notice-error">';
            echo esc_html__('Trading212 API key is not configured. Please add your API key in the plugin settings or enable demo mode.', 'tradepress');
            echo '</div>';
            echo '</div>'; // Close main div
            return ob_get_clean();
        }
        
        echo '<div class="tradepress-trading212-notice tradepress-notice-info">';
        if (empty($api_key)) {
            echo esc_html__('Using demo data mode. The data shown below is sample data, not real Trading212 data.', 'tradepress');
        } else {
            echo esc_html__('Using live Trading212 API data.', 'tradepress');
        }
        echo '</div>';
        
        // Display available endpoints with sample data
        echo '<div class="tradepress-trading212-endpoints">';
        
        foreach ($endpoints as $endpoint => $label) {
            echo '<div class="tradepress-trading212-endpoint">';
            echo '<h3>' . esc_html($label) . '</h3>';
            
            // Get data for the endpoint
            $data = $this->get_endpoint_data($endpoint);
            
            if (is_wp_error($data)) {
                echo '<div class="tradepress-trading212-notice tradepress-notice-error">';
                echo esc_html($data->get_error_message());
                echo '</div>';
            } else {
                echo '<div class="tradepress-trading212-data">';
                echo '<pre class="tradepress-trading212-json">' . esc_html(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';
                echo '</div>';
            }
            
            echo '</div>'; // Close endpoint div
        }
        
        echo '</div>'; // Close endpoints div
        echo '</div>'; // Close main div
        
        return ob_get_clean();
    }
}

// Initialize shortcodes
new TradePress_Shortcode_Trading212();
