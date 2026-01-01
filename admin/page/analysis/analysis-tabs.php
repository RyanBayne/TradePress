<?php
/**
 * TradePress - Analysis Tabs 
 *
 * @package TradePress
 * @subpackage admin/page
 * @version 1.0.0
 * @created 2023-06-25 14:30:00
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Analysis_Tabs' ) ) :

/**
 * TradePress_Admin_Analysis_Tabs Class
 */
class TradePress_Admin_Analysis_Tabs {

    /**
     * Output the Analysis tabs interface
     */
    public static function output() {
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/functions/function.tradepress-features-helpers.php';
        
        // Get the current tab
        $current_tab = isset( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : 'recent_symbols';
        
        // Define available tabs
        $tabs = self::get_tabs();
        
        // Ensure current tab exists, otherwise set to default
        if ( ! isset( $tabs[ $current_tab ] ) ) {
            $current_tab = key( $tabs );
        }
        
        // Output the tabs interface
        ?>
        <div class="wrap tradepress-admin">
            <h1>
                <?php 
                echo esc_html__( 'TradePress Analysis', 'tradepress' );
                if (isset($tabs[$current_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($tabs[$current_tab]);
                }
                ?>
            </h1>
            
            <nav class="nav-tab-wrapper tradepress-nav-tab-wrapper">
                <?php
                foreach ( $tabs as $tab_id => $tab_name ) {
                    $active = ( $tab_id === $current_tab ) ? 'nav-tab-active' : '';
                    $tab_url = add_query_arg( array( 'tab' => $tab_id ), admin_url( 'admin.php?page=tradepress_analysis' ) );
                    echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab ' . esc_attr( $active ) . '">' . esc_html( $tab_name ) . '</a>';
                }
                ?>
            </nav>
            
            <div class="tradepress-tab-content">
                <?php
                // Load the appropriate tab content
                switch ( $current_tab ) {
                    case 'recent_symbols':
                        self::recent_symbols_tab();
                        break;
                    case 'support_resistance':
                        self::support_resistance_tab();
                        break;
                    case 'volatility_analysis':
                        self::volatility_analysis_tab();
                        break;
                    default:
                        do_action( 'tradepress_analysis_tab_' . $current_tab );
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get tabs for the Analysis page
     *
     * @return array
     */
    public static function get_tabs() {
        $tabs = array(
            'recent_symbols' => __( 'Recently Analysed Symbols', 'tradepress' ),
            'support_resistance' => __( 'Support & Resistance', 'tradepress' ),
            'volatility_analysis' => __( 'Volatility Analysis', 'tradepress' )
        );
        
        // Allow extensions to add their own tabs
        return apply_filters( 'tradepress_analysis_tabs', $tabs );
    }
    
    /**
     * Recent Symbols tab content
     */
    public static function recent_symbols_tab() {
        include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/analysis/view/recent-symbols.php';
    }
    
    /**
     * Support & Resistance tab content
     */
    public static function support_resistance_tab() {
        include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/analysis/view/support-resistance.php';
    }

    /**
     * Volatility Analysis tab content
     */
    public static function volatility_analysis_tab() {
        include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/analysis/view/volatility-analysis.php';
    }
}

endif;
