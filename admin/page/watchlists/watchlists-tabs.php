<?php
/**
 * TradePress - Admin Watchlists Area
 *
 * Handles the Watchlists tabs including Active Symbols and User Watchlists.
 *
 * @author   TradePress
 * @category Admin
 * @package  TradePress/Admin
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Watchlists_Page' ) ) :

/**
 * TradePress_Admin_Watchlists_Page Class.
 */
class TradePress_Admin_Watchlists_Page {

    /**
     * Current active tab.
     *
     * @var string
     */
    private $active_tab = 'active-symbols';

    /**
     * Constructor.
     */
    public function __construct() {
        if ( isset( $_GET['tab'] ) ) {
            $this->active_tab = sanitize_text_field( $_GET['tab'] );
        }
    }

    /**
     * Get tabs for the watchlists area.
     *
     * @return array
     */
    public function get_tabs() {
        $tabs = array(
            'active-symbols' => __( 'Active Symbols', 'tradepress' ),
            'user-watchlists' => __( 'User Watchlists', 'tradepress' ),
            'create-watchlist' => __( 'Create Watchlist', 'tradepress' ),
        );
        
        return apply_filters( 'tradepress_watchlists_tabs', $tabs );
    }

    /**
     * Output the Watchlists area.
     */
    public function output() {
        $tabs = $this->get_tabs();
        ?>
        <div class="wrap tradepress-admin">
            <h1>
                <?php 
                echo esc_html__( 'TradePress Watchlists', 'tradepress' );
                if (isset($tabs[$this->active_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($tabs[$this->active_tab]);
                }
                ?>
            </h1>
            
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_id => $tab_name ) : 
                    $active = ( $this->active_tab === $tab_id ) ? 'nav-tab-active' : '';
                    $url = admin_url( 'admin.php?page=tradepress_watchlists&tab=' . $tab_id );
                ?>
                    <a href="<?php echo esc_url( $url ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>"><?php echo esc_html( $tab_name ); ?></a>
                <?php endforeach; ?>
            </nav>
            
            <div class="tradepress-watchlists-content">
                <?php 
                    // Load the active tab content
                    $this->load_tab_content( $this->active_tab );
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Load tab content based on the active tab.
     *
     * @param string $tab Tab to load.
     */
    private function load_tab_content( $tab ) {
        switch ( $tab ) {
            case 'active-symbols':
                $this->active_symbols_tab();
                break;
            case 'user-watchlists':
                $this->user_watchlists_tab();
                break;
            case 'create-watchlist':
                $this->create_watchlist_tab();
                break;
            default:
                $this->active_symbols_tab();
                break;
        }
    }

    /**
     * Active Symbols tab content.
     */
    private function active_symbols_tab() {
        // Include the active symbols view
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/watchlists/view/active-symbols.php';
    }

    /**
     * User Watchlists tab content.
     */
    private function user_watchlists_tab() {
        // Include the user watchlists view
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/watchlists/view/user-watchlists.php';
    }

    /**
     * Create Watchlist tab content.
     */
    private function create_watchlist_tab() {
        // Include the create watchlist view
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/watchlists/view/create-watchlist.php';
    }

    /**
     * Get active symbols data.
     * In a real implementation, this would fetch from database based on user activity.
     *
     * @return array Active symbols data
     */
    public static function get_active_symbols() {
        // Sample data - in production this would come from user's activity
        $active_symbols = array(
            array(
                'symbol' => 'AAPL',
                'name' => 'Apple Inc.',
                'price' => 185.92,
                'change_pct' => 2.45,
                'score' => 87,
                'activity' => array('scored', 'traded', 'researched'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'added_date' => date('Y-m-d', strtotime('-2 months')),
            ),
            array(
                'symbol' => 'MSFT',
                'name' => 'Microsoft Corporation',
                'price' => 376.17,
                'change_pct' => 1.87,
                'score' => 92,
                'activity' => array('scored', 'researched'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'added_date' => date('Y-m-d', strtotime('-1 month')),
            ),
            array(
                'symbol' => 'NVDA',
                'name' => 'NVIDIA Corporation',
                'price' => 950.02,
                'change_pct' => 3.25,
                'score' => 95,
                'activity' => array('traded', 'researched'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'added_date' => date('Y-m-d', strtotime('-2 weeks')),
            ),
            array(
                'symbol' => 'AMZN',
                'name' => 'Amazon.com, Inc.',
                'price' => 180.35,
                'change_pct' => 1.23,
                'score' => 79,
                'activity' => array('researched'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'added_date' => date('Y-m-d', strtotime('-3 weeks')),
            ),
            array(
                'symbol' => 'TSLA',
                'name' => 'Tesla, Inc.',
                'price' => 223.31,
                'change_pct' => -3.54,
                'score' => 65,
                'activity' => array('scored', 'traded'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-1 week')),
                'added_date' => date('Y-m-d', strtotime('-1 week')),
            ),
            array(
                'symbol' => 'GOOG',
                'name' => 'Alphabet Inc.',
                'price' => 156.98,
                'change_pct' => -0.87,
                'score' => 83,
                'activity' => array('scored'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'added_date' => date('Y-m-d', strtotime('-2 months')),
            ),
            array(
                'symbol' => 'META',
                'name' => 'Meta Platforms, Inc.',
                'price' => 487.95,
                'change_pct' => -1.74,
                'score' => 72,
                'activity' => array('researched'),
                'last_activity' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'added_date' => date('Y-m-d', strtotime('-3 weeks')),
            ),
        );
        
        return apply_filters('tradepress_active_symbols', $active_symbols);
    }

    /**
     * Get user watchlists data.
     * In a real implementation, this would fetch from user metadata.
     *
     * @return array User watchlists data
     */
    public static function get_user_watchlists() {
        // Sample data - in production this would come from user metadata
        $watchlists = array(
            array(
                'id' => 1,
                'name' => 'Tech Stocks',
                'description' => 'Technology sector watchlist',
                'created' => date('Y-m-d', strtotime('-2 months')),
                'symbol_count' => 5,
                'symbols' => array(
                    'AAPL' => array('score' => 87, 'strategy' => 'Growth'),
                    'MSFT' => array('score' => 92, 'strategy' => 'Momentum'),
                    'NVDA' => array('score' => 95, 'strategy' => 'AI Growth'),
                    'GOOG' => array('score' => 83, 'strategy' => 'Value'),
                    'META' => array('score' => 78, 'strategy' => 'Momentum')
                ),
                'scoring_active' => true,
                'horizontal_score' => 87,
                'horizontal_trend' => 'up',
                'score_updated' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
            ),
            array(
                'id' => 2,
                'name' => 'EV Companies',
                'description' => 'Electric vehicle manufacturers',
                'created' => date('Y-m-d', strtotime('-1 month')),
                'symbol_count' => 3,
                'symbols' => array(
                    'TSLA' => array('score' => 65, 'strategy' => 'Growth'),
                    'NIO' => array('score' => 58, 'strategy' => 'Speculative'),
                    'RIVN' => array('score' => 61, 'strategy' => 'Emerging')
                ),
                'scoring_active' => false,
                'horizontal_score' => 61,
                'horizontal_trend' => 'down',
                'score_updated' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ),
            array(
                'id' => 3,
                'name' => 'Growth Candidates',
                'description' => 'High growth potential stocks',
                'created' => date('Y-m-d', strtotime('-2 weeks')),
                'symbol_count' => 4,
                'symbols' => array(
                    'AMZN' => array('score' => 89, 'strategy' => 'E-commerce'),
                    'NVDA' => array('score' => 95, 'strategy' => 'AI Growth'),
                    'SHOP' => array('score' => 82, 'strategy' => 'E-commerce'),
                    'AMD' => array('score' => 87, 'strategy' => 'Semiconductor')
                ),
                'scoring_active' => false,
                'horizontal_score' => 88,
                'horizontal_trend' => 'up',
                'score_updated' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
            ),
            array(
                'id' => 4,
                'name' => 'Financial Sector',
                'description' => 'Banking and financial services stocks',
                'created' => date('Y-m-d', strtotime('-3 weeks')),
                'symbol_count' => 5,
                'symbols' => array(
                    'JPM' => array('score' => 76, 'strategy' => 'Value'),
                    'BAC' => array('score' => 72, 'strategy' => 'Dividend'),
                    'GS' => array('score' => 81, 'strategy' => 'Value'),
                    'MS' => array('score' => 74, 'strategy' => 'Value'),
                    'WFC' => array('score' => 69, 'strategy' => 'Recovery')
                ),
                'scoring_active' => true,
                'horizontal_score' => 74,
                'horizontal_trend' => 'neutral',
                'score_updated' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            ),
            array(
                'id' => 5,
                'name' => 'Healthcare Leaders',
                'description' => 'Top healthcare and pharmaceutical companies',
                'created' => date('Y-m-d', strtotime('-6 weeks')),
                'symbol_count' => 4,
                'symbols' => array(
                    'JNJ' => array('score' => 79, 'strategy' => 'Stable Growth'),
                    'PFE' => array('score' => 71, 'strategy' => 'Dividend'),
                    'UNH' => array('score' => 85, 'strategy' => 'Sector Leader'),
                    'ABBV' => array('score' => 83, 'strategy' => 'Value')
                ),
                'scoring_active' => true,
                'horizontal_score' => 80,
                'horizontal_trend' => 'up',
                'score_updated' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ),
            array(
                'id' => 6,
                'name' => 'Renewable Energy',
                'description' => 'Clean energy and sustainability focused companies',
                'created' => date('Y-m-d', strtotime('-1 month')),
                'symbol_count' => 3,
                'symbols' => array(
                    'ENPH' => array('score' => 68, 'strategy' => 'Growth'),
                    'SEDG' => array('score' => 64, 'strategy' => 'Speculative'),
                    'NEE' => array('score' => 77, 'strategy' => 'Dividend Growth')
                ),
                'scoring_active' => false,
                'horizontal_score' => 70,
                'horizontal_trend' => 'up',
                'score_updated' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ),
        );
        
        return apply_filters('tradepress_user_watchlists', $watchlists);
    }

    /**
     * Toggle the scoring status for a watchlist
     * 
     * @param int $watchlist_id The ID of the watchlist to toggle
     * @return bool True if successful, false otherwise
     */
    public static function toggle_watchlist_scoring($watchlist_id) {
        // In a real implementation, this would update the watchlist metadata
        // For demo purposes, we'll just return true to simulate success
        return true;
    }
}

endif;
