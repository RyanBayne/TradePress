<?php
/**
 * TradePress Admin Sandbox Listener Tab for Testing the Listener Class
 *
 * Provides a testing interface for the TradePress_Listener class with sample GET and POST forms.
 *
 * @package TradePress\Admin\sandbox
 * @version 1.0.0
 * @date    2023-10-25
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_Admin_Page_Listener_Testing
 * 
 * @todo This class contains too much HTML output, most without PHP variables, it should be moved to a partials folder
 */
class TradePress_Admin_Page_Listener_Testing {
    
    /**
     * Output the listener testing tab
     */
    public static function output() {
        // Ensure styles are loaded
        wp_enqueue_style('dashicons');
        
        // Check for form submission results
        $get_processed = isset($_GET['listener_get_processed']) ? sanitize_text_field($_GET['listener_get_processed']) : '';
        $post_processed = isset($_GET['listener_post_processed']) ? sanitize_text_field($_GET['listener_post_processed']) : '';
        
        // Display results if available
        if (!empty($get_processed)) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 sprintf(__('GET form processed successfully! Value: %s', 'tradepress'), esc_html($get_processed)) .
                 '</p></div>';
        }
        
        if (!empty($post_processed)) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 sprintf(__('POST form processed successfully! Value: %s', 'tradepress'), esc_html($post_processed)) .
                 '</p></div>';
        }
        
        // Check for security test results
        $security_error = isset($_GET['security_error']) ? sanitize_text_field($_GET['security_error']) : '';
        
        if (!empty($security_error)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 sprintf(__('Security test failed as expected: %s', 'tradepress'), esc_html($security_error)) .
                 '</p></div>';
        }
        
        ?>
        <div class="listener-sandbox-container">
            <div class="listener-sandbox-forms">
                <!-- GET Form Section -->
                <div class="listener-form-section">
                    <h3><?php esc_html_e('GET Form Test', 'tradepress'); ?></h3>
                    <p class="description"><?php esc_html_e('Tests handling GET requests through the TradePress_Listener class.', 'tradepress'); ?></p>
                    
                    <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="listener-test-form">
                        <input type="hidden" name="page" value="tradepress_sandbox">
                        <input type="hidden" name="tab" value="listener">
                        <input type="hidden" name="TradePressaction" value="test_get_listener">
                        <?php wp_nonce_field('test_get_listener'); ?>
                        
                        <div class="form-field">
                            <label for="get-test-value"><?php esc_html_e('Test Value:', 'tradepress'); ?></label>
                            <input type="text" id="get-test-value" name="test_value" value="Sample GET Value" required>
                        </div>
                        
                        <div class="form-field">
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Submit GET Form', 'tradepress'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div class="listener-code-example">
                        <h4><?php esc_html_e('How it works:', 'tradepress'); ?></h4>
                        <pre>
                            // In listener.php
                            private function process_get_requests() {
                                // Check for TradePressaction parameter
                                if( !isset( $_GET['TradePressaction'] ) ) {
                                    return;    
                                }
                                
                                // Verify nonce
                                if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $_GET['TradePressaction'] ) ) {
                                    wp_die( 'Security check failed' );
                                }
                                
                                // Process the action
                                if( $_GET['TradePressaction'] === 'test_get_listener' ) {
                                    $test_value = sanitize_text_field( $_GET['test_value'] );
                                    wp_redirect( add_query_arg( 'listener_get_processed', $test_value, wp_get_referer() ) );
                                    exit;
                                }
                            }
                        </pre>
                    </div>
                </div>
                
                <!-- POST Form Section -->
                <div class="listener-form-section">
                    <h3><?php esc_html_e('POST Form Test', 'tradepress'); ?></h3>
                    <p class="description"><?php esc_html_e('Tests handling POST requests through the TradePress_Listener class.', 'tradepress'); ?></p>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="listener-test-form">
                        <input type="hidden" name="action" value="tradepress_test_post_listener">
                        <input type="hidden" name="TradePress_form_action" value="test_post_listener">
                        <?php wp_nonce_field('test_post_listener'); ?>
                        
                        <div class="form-field">
                            <label for="post-test-value"><?php esc_html_e('Test Value:', 'tradepress'); ?></label>
                            <input type="text" id="post-test-value" name="test_value" value="Sample POST Value" required>
                        </div>
                        
                        <div class="form-field">
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Submit POST Form', 'tradepress'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div class="listener-code-example">
                        <h4><?php esc_html_e('How it works:', 'tradepress'); ?></h4>
                        <pre>
                            // In listener.php
                            private function process_post_requests() {
                                // Check for TradePress_form_action
                                if( !isset( $_POST['TradePress_form_action'] ) ) {
                                    return;    
                                }
                                
                                $action = sanitize_key( $_POST['TradePress_form_action'] );
                                
                                // Verify nonce
                                if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], $action ) ) {
                                    wp_die( 'Security check failed' );
                                }
                                
                                // Process the action
                                if( $action === 'test_post_listener' ) {
                                    $test_value = sanitize_text_field( $_POST['test_value'] );
                                    wp_redirect( add_query_arg( 'listener_post_processed', $test_value, wp_get_referer() ) );
                                    exit;
                                }
                            }
                        </pre>
                    </div>
                </div>
                
                <!-- NEW: Security Test Forms Section -->
                <div class="listener-form-section security-test-section">
                    <h3><?php esc_html_e('Security Test Forms', 'tradepress'); ?></h3>
                    <p class="description"><?php esc_html_e('These forms are designed to test security measures by intentionally failing them.', 'tradepress'); ?></p>
                    <div class="security-warning">
                        <span class="dashicons dashicons-shield-alt"></span>
                        <?php esc_html_e('These forms are expected to fail with security errors. They demonstrate how WordPress protects against unauthorized or malformed requests.', 'tradepress'); ?>
                    </div>
                    
                    <!-- Test 1: Missing Nonce Field -->
                    <div class="security-test-form">
                        <h4><?php esc_html_e('Test 1: Missing Nonce Field', 'tradepress'); ?></h4>
                        <p class="description"><?php esc_html_e('This form attempts to submit without a nonce field, which should trigger a security error.', 'tradepress'); ?></p>
                        
                        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="listener-test-form">
                            <input type="hidden" name="page" value="tradepress_sandbox">
                            <input type="hidden" name="tab" value="listener">
                            <input type="hidden" name="TradePressaction" value="test_get_listener">
                            <!-- No nonce field is included here -->
                            
                            <div class="form-field">
                                <label for="missing-nonce-value"><?php esc_html_e('Test Value:', 'tradepress'); ?></label>
                                <input type="text" id="missing-nonce-value" name="test_value" value="Missing Nonce Test" required>
                            </div>
                            
                            <div class="form-field">
                                <button type="submit" class="button button-warning">
                                    <?php esc_html_e('Submit (Will Fail)', 'tradepress'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Test 2: Invalid Nonce Value -->
                    <div class="security-test-form">
                        <h4><?php esc_html_e('Test 2: Invalid Nonce Value', 'tradepress'); ?></h4>
                        <p class="description"><?php esc_html_e('This form includes an invalid nonce value, which should trigger a security error.', 'tradepress'); ?></p>
                        
                        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="listener-test-form">
                            <input type="hidden" name="page" value="tradepress_sandbox">
                            <input type="hidden" name="tab" value="listener">
                            <input type="hidden" name="TradePressaction" value="test_get_listener">
                            <input type="hidden" name="_wpnonce" value="invalid_nonce_value_12345">
                            
                            <div class="form-field">
                                <label for="invalid-nonce-value"><?php esc_html_e('Test Value:', 'tradepress'); ?></label>
                                <input type="text" id="invalid-nonce-value" name="test_value" value="Invalid Nonce Test" required>
                            </div>
                            
                            <div class="form-field">
                                <button type="submit" class="button button-warning">
                                    <?php esc_html_e('Submit (Will Fail)', 'tradepress'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Test 3: Missing Action Parameter -->
                    <div class="security-test-form">
                        <h4><?php esc_html_e('Test 3: Missing Action Parameter', 'tradepress'); ?></h4>
                        <p class="description"><?php esc_html_e('This form is missing the required TradePressaction parameter.', 'tradepress'); ?></p>
                        
                        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="listener-test-form">
                            <input type="hidden" name="page" value="tradepress_sandbox">
                            <input type="hidden" name="tab" value="listener">
                            <!-- No TradePressaction parameter -->
                            <?php wp_nonce_field('test_get_listener'); ?>
                            
                            <div class="form-field">
                                <label for="missing-action-value"><?php esc_html_e('Test Value:', 'tradepress'); ?></label>
                                <input type="text" id="missing-action-value" name="test_value" value="Missing Action Test" required>
                            </div>
                            
                            <div class="form-field">
                                <button type="submit" class="button button-warning">
                                    <?php esc_html_e('Submit (Will Silently Fail)', 'tradepress'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Test 4: Mismatched Nonce Action -->
                    <div class="security-test-form">
                        <h4><?php esc_html_e('Test 4: Mismatched Nonce Action', 'tradepress'); ?></h4>
                        <p class="description"><?php esc_html_e('This form uses a valid nonce but for a different action than requested.', 'tradepress'); ?></p>
                        
                        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="listener-test-form">
                            <input type="hidden" name="page" value="tradepress_sandbox">
                            <input type="hidden" name="tab" value="listener">
                            <input type="hidden" name="TradePressaction" value="test_get_listener">
                            <?php 
                            // Generate a nonce for a different action
                            wp_nonce_field('different_action_nonce'); 
                            ?>
                            
                            <div class="form-field">
                                <label for="mismatched-nonce-value"><?php esc_html_e('Test Value:', 'tradepress'); ?></label>
                                <input type="text" id="mismatched-nonce-value" name="test_value" value="Mismatched Nonce Test" required>
                            </div>
                            
                            <div class="form-field">
                                <button type="submit" class="button button-warning">
                                    <?php esc_html_e('Submit (Will Fail)', 'tradepress'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Code for handling security test responses -->
                <div class="listener-form-section">
                    <h3><?php esc_html_e('Handling Security Test Failures', 'tradepress'); ?></h3>
                    <p class="description"><?php esc_html_e('Security failures will be handled by WordPress as shown below:', 'tradepress'); ?></p>
                    
                    <div class="listener-code-example">
                        <pre>
                            // Standard WordPress security response to failed nonce checks
                            if (!wp_verify_nonce($_REQUEST['_wpnonce'], $action)) {
                                // Usually results in a wp_die() call with a message like:
                                wp_die('The link you followed has expired.', 'tradepress');
                                
                                // Or it might redirect to a specific page with an error parameter
                                wp_redirect(add_query_arg('security_error', 'invalid_nonce', $redirect_url));
                                exit;
                            }
                        </pre>
                    </div>
                    
                    <p><?php esc_html_e('When you click the buttons in the Security Test Forms section, you should expect one of these outcomes:', 'tradepress'); ?></p>
                    <ul class="security-outcomes">
                        <li><span class="dashicons dashicons-warning"></span> <?php esc_html_e('WordPress will display a "The link you followed has expired" message', 'tradepress'); ?></li>
                        <li><span class="dashicons dashicons-warning"></span> <?php esc_html_e('You will be redirected back to this page with an error message', 'tradepress'); ?></li>
                        <li><span class="dashicons dashicons-warning"></span> <?php esc_html_e('The request will silently fail (no action will be taken)', 'tradepress'); ?></li>
                    </ul>
                </div>
                
                <!-- Additional Information -->
                <div class="listener-docs-section">
                    <h3><?php esc_html_e('Listener Class Overview', 'tradepress'); ?></h3>
                    <p><?php esc_html_e('The TradePress_Listener class is responsible for processing all TradePress-related GET and POST requests. Here\'s how it works:', 'tradepress'); ?></p>
                    
                    <ul class="listener-info-list">
                        <li><strong><?php esc_html_e('GET Requests:', 'tradepress'); ?></strong> <?php esc_html_e('Identified by the TradePressaction parameter', 'tradepress'); ?></li>
                        <li><strong><?php esc_html_e('POST Requests:', 'tradepress'); ?></strong> <?php esc_html_e('Identified by the TradePress_form_action parameter', 'tradepress'); ?></li>
                        <li><strong><?php esc_html_e('Security:', 'tradepress'); ?></strong> <?php esc_html_e('All requests must include a valid WordPress nonce', 'tradepress'); ?></li>
                        <li><strong><?php esc_html_e('Processing:', 'tradepress'); ?></strong> <?php esc_html_e('Occurs during the wp_loaded action hook', 'tradepress'); ?></li>
                    </ul>
                    
                    <p><?php esc_html_e('Use this sandbox to test your custom form handlers with the TradePress_Listener class.', 'tradepress'); ?></p>
                </div>
            </div>
        </div>
        
        <?php
    }
}
