<?php
/**
 * TradePress Focus - Advisor Tab
 *
 * Step-by-step investment decision wizard with progressive access and session management.
 *
 * @package TradePress/Admin/Focus
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load required functions and classes
require_once TRADEPRESS_PLUGIN_DIR . 'includes/bugnet-system/functions.tradepress-bugnet.php';
require_once TRADEPRESS_PLUGIN_DIR . 'includes/components/symbols-container.php';
require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-mode-handler.php';
require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-controller.php';

// Styles are loaded via assets-loader-original.php

// Initialize advisor controller
$advisor_controller = new TradePress_Advisor_Controller();
$advisor_session = new TradePress_Advisor_Session();

// Handle reset parameter
if ( isset( $_GET['reset'] ) && $_GET['reset'] === '1' ) {
    tradepress_trace_log('URL reset parameter detected, clearing advisor session');
    $advisor_session->clear_session();
    // Redirect to clean URL
    wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1' ) );
    exit;
}

// Handle earnings refresh parameter
if ( isset( $_GET['refresh_earnings'] ) && $_GET['refresh_earnings'] === '1' ) {
    tradepress_trace_log('Earnings refresh requested');
    require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/earnings-data-bridge.php';
    TradePress_Advisor_Earnings_Bridge::force_refresh();
    // Redirect to clean URL
    wp_redirect( admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=2' ) );
    exit;
}

// Get current step from URL or default to 1
$current_step = isset( $_GET['advisor_step'] ) ? intval( $_GET['advisor_step'] ) : 1;

// Get progress status
$progress_status = $advisor_controller->get_progress_status();
$session_data = $advisor_session->get_session_data();
?>

<!-- Floating Symbols Container -->
<?php 
if ( $session_data && ! empty( $session_data['selected_symbols'] ) ) {
    echo TradePress_Symbols_Container::render( $session_data['selected_symbols'], array(
        'title' => ucfirst( $session_data['mode'] ) . ' ' . __( 'Mode', 'tradepress' ),
        'show_count' => true,
        'show_reset' => true,
        'reset_url' => admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1&reset=1' ),
        'position' => 'floating'
    ));
}
?>



<div class="tradepress-focus-advisor">
    <div class="advisor-header">


    </div>

    <!-- Step Navigation -->
    <div class="advisor-navigation">
        <div class="step-tabs">
            <?php foreach ( $progress_status as $step_num => $step_info ) : ?>
                <?php 
                $tab_class = 'step-tab';
                $tab_class .= ' step-' . $step_info['status'];
                if ( $current_step === $step_num ) {
                    $tab_class .= ' active';
                }
                
                $is_accessible = in_array( $step_info['status'], array( 'accessible', 'completed' ) );
                $step_url = $is_accessible ? 
                    admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=' . $step_num ) : 
                    '#';
                ?>
                <a href="<?php echo esc_url( $step_url ); ?>" 
                   class="<?php echo esc_attr( $tab_class ); ?>"
                   <?php echo ! $is_accessible ? 'onclick="return false;"' : ''; ?>>
                    <span class="step-number"><?php echo $step_num; ?></span>
                    <span class="step-title"><?php echo esc_html( $step_info['title'] ); ?></span>
                    <?php if ( $step_info['status'] === 'completed' ) : ?>
                        <span class="dashicons dashicons-yes-alt"></span>
                    <?php elseif ( $step_info['status'] === 'locked' ) : ?>
                        <span class="dashicons dashicons-lock"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step Content -->
    <div class="advisor-content">
        <?php echo $advisor_controller->render_step( $current_step ); ?>
    </div>
</div>



<script>
function clearAdvisorSession() {
    if (confirm('<?php esc_html_e( 'Are you sure you want to start over? This will clear all your progress.', 'tradepress' ); ?>')) {
        // AJAX call to clear session
        jQuery.post(ajaxurl, {
            action: 'tradepress_clear_advisor_session',
            nonce: '<?php echo wp_create_nonce( 'tradepress_clear_advisor_session' ); ?>'
        }, function(response) {
            // Always redirect regardless of response to ensure fresh start
            window.location.href = '<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1' ); ?>';
        }).fail(function() {
            // Even if AJAX fails, redirect to start fresh
            window.location.href = '<?php echo admin_url( 'admin.php?page=tradepress_focus&tab=advisor&advisor_step=1' ); ?>';
        });
    }
}
</script>