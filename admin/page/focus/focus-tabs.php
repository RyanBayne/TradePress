<?php
/**
 * TradePress - Admin Focus Page
 *
 * Daily trading routine and priority management system.
 * Helps traders focus on high-priority matters and stay informed about market changes.
 *
 * @package TradePress/Admin
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



if ( ! class_exists( 'TradePress_Admin_Focus_Page' ) ) :

/**
 * TradePress_Admin_Focus_Page Class.
 */
class TradePress_Admin_Focus_Page {

    /**
     * Current active tab.
     *
     * @var string
     */
    private $active_tab = 'priority';

    /**
     * Constructor.
     */
    public function __construct() {
        if ( isset( $_GET['tab'] ) && array_key_exists( sanitize_text_field( $_GET['tab'] ), $this->get_tabs() ) ) {
            $this->active_tab = sanitize_text_field( $_GET['tab'] );
        }
    }

    /**
     * Get tabs for the focus area.
     *
     * @return array
     */
    public function get_tabs() {
        return array(
            'priority' => __( 'Priority', 'tradepress' ),
            'daily'    => __( 'Daily', 'tradepress' ),
            'weekly'   => __( 'Weekly', 'tradepress' ),
            'advisor'  => __( 'Advisor', 'tradepress' ),
        );
    }

    /**
     * Output the focus area interface.
     */
    public function output() {
        $tabs = $this->get_tabs();
        
        echo '<div class="wrap tradepress-admin">';
        echo '<h1>' . esc_html__( 'TradePress Focus', 'tradepress' );
        if (isset($tabs[$this->active_tab])) {
            echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
            echo esc_html($tabs[$this->active_tab]);
        }
        echo '</h1>';
        
        echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
        foreach ( $tabs as $tab_id => $tab_name ) {
            $active_class = ( $this->active_tab === $tab_id ) ? ' nav-tab-active' : '';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=tradepress_focus&tab=' . $tab_id ) ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . esc_html( $tab_name ) . '</a>';
        }
        echo '</nav>';
        
        echo '<div class="tradepress-tab-content">';
        do_action( 'tradepress_focus_area_' . $this->active_tab . '_tab_content' );
        echo '</div>';
        
        echo '</div>';
    }
}

endif;

// Action hooks for tab content
add_action( 'tradepress_focus_area_priority_tab_content', 'tradepress_display_priority_tab_content' );
add_action( 'tradepress_focus_area_daily_tab_content', 'tradepress_display_daily_tab_content' );
add_action( 'tradepress_focus_area_weekly_tab_content', 'tradepress_display_weekly_tab_content' );
add_action( 'tradepress_focus_area_advisor_tab_content', 'tradepress_display_advisor_tab_content' );

/**
 * Display Priority tab content
 */
function tradepress_display_priority_tab_content() {
    $view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/focus/view/priority.php';
    if ( file_exists( $view_file ) ) {
        include_once $view_file;
    } else {
        echo '<p>' . esc_html__( 'Priority tab content view file not found.', 'tradepress' ) . '</p>';
    }
}

/**
 * Display Daily tab content
 */
function tradepress_display_daily_tab_content() {
    $view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/focus/view/daily.php';
    if ( file_exists( $view_file ) ) {
        include_once $view_file;
    } else {
        echo '<p>' . esc_html__( 'Daily tab content view file not found.', 'tradepress' ) . '</p>';
    }
}

/**
 * Display Weekly tab content
 */
function tradepress_display_weekly_tab_content() {
    $view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/focus/view/weekly.php';
    if ( file_exists( $view_file ) ) {
        include_once $view_file;
    } else {
        echo '<p>' . esc_html__( 'Weekly tab content view file not found.', 'tradepress' ) . '</p>';
    }
}

/**
 * Display Advisor tab content
 */
function tradepress_display_advisor_tab_content() {
    $view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/focus/view/advisor.php';
    if ( file_exists( $view_file ) ) {
        include_once $view_file;
    } else {
        echo '<p>' . esc_html__( 'Advisor tab content view file not found.', 'tradepress' ) . '</p>';
    }
}

// AJAX handlers for advisor functionality
add_action( 'wp_ajax_tradepress_clear_advisor_session', 'tradepress_ajax_clear_advisor_session' );

/**
 * AJAX handler to clear advisor session
 */
function tradepress_ajax_clear_advisor_session() {
    // Load BugNet functions if not already loaded
    if ( ! function_exists( 'tradepress_trace_log' ) ) {
        require_once TRADEPRESS_PLUGIN_DIR . 'includes/bugnet-system/functions.tradepress-bugnet.php';
    }
    
    tradepress_trace_log('AJAX clear advisor session called');
    
    check_ajax_referer( 'tradepress_clear_advisor_session', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log('Permission denied for clear session');
        }
        wp_send_json_error( __( 'Permission denied', 'tradepress' ) );
    }
    
    require_once TRADEPRESS_PLUGIN_DIR . 'includes/advisor/advisor-session.php';
    $session = new TradePress_Advisor_Session();
    
    if ( $session->clear_session() ) {
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log('Advisor session cleared successfully');
        }
        wp_send_json_success( 'Session cleared' );
    } else {
        if ( function_exists( 'tradepress_trace_log' ) ) {
            tradepress_trace_log('Failed to clear advisor session');
        }
        wp_send_json_error( 'Failed to clear session' );
    }
}