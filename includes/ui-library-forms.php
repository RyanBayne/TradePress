<?php
/**
 * UI Library Form Handlers
 * 
 * @package TradePress
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit;

class TradePress_UI_Library_Forms {
    
    /**
     *   C On St Ru Ct.
     *
     * @version 1.0.0
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );
        add_action( 'wp_ajax_tradepress_validate_username', array( $this, 'validate_username' ) );
        add_action( 'wp_ajax_tradepress_validate_symbol', array( $this, 'validate_symbol' ) );
        add_action( 'wp_ajax_tradepress_submit_ajax_form', array( $this, 'submit_ajax_form' ) );
    }
    
    /**
     * Handle form submissions
      *
      * @version 1.0.0
     */
    public function handle_form_submissions() {
        if ( empty( $_POST['tradepress_form_action'] ) ) {
            return;
        }
        
        $action = sanitize_text_field( wp_unslash( $_POST['tradepress_form_action'] ) );
        
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
      *
      * @version 1.0.0
     */
    private function handle_contact_form() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'tradepress_ui_contact_form' ) ) {
            wp_die( 'Security check failed' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }
        
        $name = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
        $email = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
        $message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';
        
        // Log the submission
        tradepress_ai_log( 'Contact form submitted', array(
            'name' => $name,
            'email' => $email,
            'message_length' => strlen( $message )
        ));
        
        // Redirect with success message
        wp_safe_redirect( add_query_arg( array(
            'page' => 'tradepress_development',
            'tab' => 'ui-library',
            'message' => 'contact_sent'
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * Handle trading settings form
      *
      * @version 1.0.0
     */
    private function handle_trading_settings() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'tradepress_ui_trading_settings' ) ) {
            wp_die( 'Security check failed' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }
        
        $risk_level = isset( $_POST['risk_level'] ) ? sanitize_text_field( wp_unslash( $_POST['risk_level'] ) ) : '';
        $max_investment = isset( $_POST['max_investment'] ) ? absint( wp_unslash( $_POST['max_investment'] ) ) : 0;
        $preferences = array();
        if ( isset( $_POST['preferences'] ) && is_array( $_POST['preferences'] ) ) {
            $preferences = array_map(
                'sanitize_text_field',
                array_map( 'wp_unslash', $_POST['preferences'] )
            );
        }
        
        // Log the submission
        tradepress_ai_log( 'Trading settings updated', array(
            'risk_level' => $risk_level,
            'max_investment' => $max_investment,
            'preferences' => $preferences
        ));
        
        // Redirect with success message
        wp_safe_redirect( add_query_arg( array(
            'page' => 'tradepress_development',
            'tab' => 'ui-library',
            'message' => 'settings_saved'
        ), admin_url( 'admin.php' ) ) );
        exit;
    }
    
    /**
     * Ajax username validation
      *
      * @version 1.0.0
     */
    public function validate_username() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_ui_ajax_validation' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }
        
        $username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
        
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
      *
      * @version 1.0.0
     */
    public function validate_symbol() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_ui_ajax_validation' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }
        
        $symbol = isset( $_POST['symbol'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['symbol'] ) ) ) : '';
        
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
      *
      * @version 1.0.0
     */
    public function submit_ajax_form() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_ui_ajax_validation' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }
        
        $username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
        $symbol = isset( $_POST['symbol'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['symbol'] ) ) ) : '';
        
        // Log the submission
        tradepress_ai_log( 'Ajax form submitted', array(
            'username' => $username,
            'symbol' => $symbol
        ));
        
        wp_send_json_success( array( 'message' => 'Form data validated and logged successfully' ) );
    }
    
    /**
     * Display success messages
      *
      * @version 1.0.0
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
