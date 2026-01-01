<?php
/**
 * UI Library Form Handlers
 * 
 * @package TradePress
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

class TradePress_UI_Library_Forms {
    
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );
        add_action( 'wp_ajax_tradepress_validate_username', array( $this, 'validate_username' ) );
        add_action( 'wp_ajax_tradepress_validate_symbol', array( $this, 'validate_symbol' ) );
        add_action( 'wp_ajax_tradepress_submit_ajax_form', array( $this, 'submit_ajax_form' ) );
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        if ( empty( $_POST['tradepress_form_action'] ) ) {
            return;
        }
        
        $action = sanitize_text_field( $_POST['tradepress_form_action'] );
        
        switch ( $action ) {
            case 'contact_form':
                $this->handle_contact_form();
                break;
            case 'trading_settings':
                $this->handle_trading_settings();
                break;
        }
    }
    
    /**
     * Handle contact form submission
     */
    private function handle_contact_form() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_ui_contact_form' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $name = sanitize_text_field( $_POST['contact_name'] );
        $email = sanitize_email( $_POST['contact_email'] );
        $message = sanitize_textarea_field( $_POST['contact_message'] );
        
        // Log the submission
        tradepress_ai_log( 'Contact form submitted', array(
            'name' => $name,
            'email' => $email,
            'message_length' => strlen( $message )
        ));
        
        // Redirect with success message
        wp_redirect( add_query_arg( array(
            'page' => 'tradepress_development',
            'tab' => 'ui-library',
            'message' => 'contact_sent'
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * Handle trading settings form
     */
    private function handle_trading_settings() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_ui_trading_settings' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $risk_level = sanitize_text_field( $_POST['risk_level'] );
        $max_investment = absint( $_POST['max_investment'] );
        $preferences = isset( $_POST['preferences'] ) ? array_map( 'sanitize_text_field', $_POST['preferences'] ) : array();
        
        // Log the submission
        tradepress_ai_log( 'Trading settings updated', array(
            'risk_level' => $risk_level,
            'max_investment' => $max_investment,
            'preferences' => $preferences
        ));
        
        // Redirect with success message
        wp_redirect( add_query_arg( array(
            'page' => 'tradepress_development',
            'tab' => 'ui-library',
            'message' => 'settings_saved'
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * Ajax username validation
     */
    public function validate_username() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'tradepress_ui_ajax_validation' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }
        
        $username = sanitize_text_field( $_POST['username'] );
        
        if ( strlen( $username ) < 3 ) {
            wp_send_json_error( array( 'message' => 'Username must be at least 3 characters' ) );
        }
        
        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => 'Username already exists' ) );
        }
        
        wp_send_json_success( array( 'message' => 'Username is available' ) );
    }
    
    /**
     * Ajax symbol validation
     */
    public function validate_symbol() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'tradepress_ui_ajax_validation' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }
        
        $symbol = strtoupper( sanitize_text_field( $_POST['symbol'] ) );
        
        // Simple validation - check if it's a valid format
        if ( ! preg_match( '/^[A-Z]{1,5}$/', $symbol ) ) {
            wp_send_json_error( array( 'message' => 'Invalid symbol format' ) );
        }
        
        // Check against common symbols
        $valid_symbols = array( 'AAPL', 'MSFT', 'GOOGL', 'AMZN', 'TSLA', 'META', 'NVDA' );
        
        if ( in_array( $symbol, $valid_symbols ) ) {
            wp_send_json_success( array( 'message' => 'Valid stock symbol' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Symbol not found in our database' ) );
        }
    }
    
    /**
     * Ajax form submission
     */
    public function submit_ajax_form() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'tradepress_ui_ajax_validation' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }
        
        $username = sanitize_text_field( $_POST['username'] );
        $symbol = strtoupper( sanitize_text_field( $_POST['symbol'] ) );
        
        // Log the submission
        tradepress_ai_log( 'Ajax form submitted', array(
            'username' => $username,
            'symbol' => $symbol
        ));
        
        wp_send_json_success( array( 'message' => 'Form data validated and logged successfully' ) );
    }
    
    /**
     * Display success messages
     */
    public static function display_messages() {
        if ( isset( $_GET['message'] ) ) {
            $message = sanitize_text_field( $_GET['message'] );
            
            switch ( $message ) {
                case 'contact_sent':
                    echo '<div class="notice notice-success is-dismissible"><p>Contact form submitted successfully!</p></div>';
                    break;
                case 'settings_saved':
                    echo '<div class="notice notice-success is-dismissible"><p>Trading settings saved successfully!</p></div>';
                    break;
            }
        }
    }
}

// Initialize the form handler
new TradePress_UI_Library_Forms();