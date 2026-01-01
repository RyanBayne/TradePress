<?php
/**
 * TradePress Form Handler Example
 * 
 * Based on the setup wizard pattern - shows proper WordPress form handling
 * 
 * @package TradePress
 * @version 1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

class TradePress_Form_Handler_Example {
    
    /**
     * Initialize form handling
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submission() {
        // Check if this is our form
        if ( empty( $_POST['tradepress_form_action'] ) ) {
            return;
        }
        
        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tradepress_form_nonce' ) ) {
            wp_die( __( 'Security check failed. Please refresh and try again.', 'tradepress' ) );
        }
        
        // Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to perform this action.', 'tradepress' ) );
        }
        
        $action = sanitize_text_field( $_POST['tradepress_form_action'] );
        
        switch ( $action ) {
            case 'save_earnings_settings':
                $this->save_earnings_settings();
                break;
                
            case 'update_watchlist':
                $this->update_watchlist();
                break;
                
            default:
                wp_die( __( 'Invalid form action.', 'tradepress' ) );
        }
    }
    
    /**
     * Save earnings settings
     */
    private function save_earnings_settings() {
        // Sanitize and validate inputs
        $settings = array();
        
        if ( isset( $_POST['earnings_api_key'] ) ) {
            $settings['api_key'] = sanitize_text_field( $_POST['earnings_api_key'] );
        }
        
        if ( isset( $_POST['earnings_refresh_interval'] ) ) {
            $interval = absint( $_POST['earnings_refresh_interval'] );
            // Validate refresh interval is within reasonable bounds (1-1440 minutes)
            if ( $interval >= 1 && $interval <= 1440 ) {
                $settings['refresh_interval'] = $interval;
            } else {
                wp_die( __( 'Refresh interval must be between 1 and 1440 minutes.', 'tradepress' ) );
            }
        }
        
        if ( isset( $_POST['earnings_sectors'] ) && is_array( $_POST['earnings_sectors'] ) ) {
            $settings['sectors'] = array_map( 'sanitize_text_field', $_POST['earnings_sectors'] );
        }
        
        // Save settings
        update_option( 'tradepress_earnings_settings', $settings );
        
        // Redirect with success message
        $redirect_url = add_query_arg( array(
            'page' => 'tradepress_research',
            'tab' => 'earnings',
            'message' => 'settings_saved'
        ), admin_url( 'admin.php' ) );
        
        wp_safe_redirect( $redirect_url );
        exit;
    }
    
    /**
     * Update watchlist
     */
    private function update_watchlist() {
        $symbols = array();
        
        if ( isset( $_POST['watchlist_symbols'] ) ) {
            $raw_symbols = sanitize_textarea_field( $_POST['watchlist_symbols'] );
            $symbol_array = array_map( 'trim', explode( ',', $raw_symbols ) );
            
            foreach ( $symbol_array as $symbol ) {
                $symbol = strtoupper( $symbol );
                if ( ! empty( $symbol ) && preg_match( '/^[A-Z]{1,5}$/', $symbol ) ) {
                    $symbols[] = $symbol;
                }
            }
        }
        
        // Save symbols
        update_option( 'tradepress_watchlist_symbols', $symbols );
        
        // Redirect with success message
        $redirect_url = add_query_arg( array(
            'page' => 'tradepress_research',
            'tab' => 'earnings',
            'message' => 'watchlist_updated'
        ), admin_url( 'admin.php' ) );
        
        wp_safe_redirect( $redirect_url );
        exit;
    }
    
    /**
     * Display form with proper nonce and structure
     */
    public static function render_earnings_form() {
        $settings = get_option( 'tradepress_earnings_settings', array() );
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'tradepress_form_nonce' ); ?>
            <input type="hidden" name="tradepress_form_action" value="save_earnings_settings">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="earnings_api_key"><?php _e( 'API Key', 'tradepress' ); ?></label>
                    </th>
                    <td>
                        <input type="password" 
                               id="earnings_api_key" 
                               name="earnings_api_key" 
                               value="<?php echo esc_attr( $settings['api_key'] ?? '' ); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="earnings_refresh_interval"><?php _e( 'Refresh Interval (minutes)', 'tradepress' ); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               id="earnings_refresh_interval" 
                               name="earnings_refresh_interval" 
                               value="<?php echo esc_attr( $settings['refresh_interval'] ?? 60 ); ?>" 
                               min="1" 
                               max="1440">
                    </td>
                </tr>
            </table>
            
            <?php submit_button( __( 'Save Settings', 'tradepress' ) ); ?>
        </form>
        <?php
    }
    
    /**
     * Display success/error messages
     */
    public static function display_messages() {
        if ( isset( $_GET['message'] ) ) {
            $message = sanitize_text_field( $_GET['message'] );
            
            switch ( $message ) {
                case 'settings_saved':
                    echo '<div class="notice notice-success is-dismissible"><p>' . 
                         __( 'Settings saved successfully.', 'tradepress' ) . '</p></div>';
                    break;
                    
                case 'watchlist_updated':
                    echo '<div class="notice notice-success is-dismissible"><p>' . 
                         __( 'Watchlist updated successfully.', 'tradepress' ) . '</p></div>';
                    break;
                    
                default:
                    // Handle unexpected message values
                    error_log( 'TradePress: Unknown message parameter: ' . $message );
                    break;
            }
        }
    }
}

// Initialize the form handler
new TradePress_Form_Handler_Example();