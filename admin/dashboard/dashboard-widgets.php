<?php
/**
 * TradePress Dashboard Widgets
 *
 * @package  TradePress
 * @category Dashboard
 * @author   TradePress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Dashboard_Widgets Class
 */
class TradePress_Dashboard_Widgets {

    /**
     * Initialize dashboard widgets
     */
    public static function init() {
        // Add dashboard widgets
        add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widgets' ) );
        
        // Add admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        
        // AJAX handlers for widget refresh
        add_action( 'wp_ajax_tradepress_refresh_market_widget', array( __CLASS__, 'ajax_refresh_market_widget' ) );
        add_action( 'wp_ajax_tradepress_refresh_trades_widget', array( __CLASS__, 'ajax_refresh_trades_widget' ) );
        add_action( 'wp_ajax_tradepress_refresh_watchlist_widget', array( __CLASS__, 'ajax_refresh_watchlist_widget' ) );
        add_action( 'wp_ajax_tradepress_refresh_recent_symbols_widget', array( __CLASS__, 'ajax_refresh_recent_symbols_widget' ) );
    }

    /**
     * Register dashboard widgets
     */
    public static function register_dashboard_widgets() {
        // Check user capabilities
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }
        
        // Recent symbols widget
        wp_add_dashboard_widget(
            'tradepress_recent_symbols_widget',
            __( 'TradePress Recent Symbols', 'tradepress' ),
            array( __CLASS__, 'recent_symbols_widget_callback' )
        );
        
        // Market overview widget
        wp_add_dashboard_widget(
            'tradepress_market_overview',
            __( 'TradePress Market Overview', 'tradepress' ),
            array( __CLASS__, 'market_overview_widget' )
        );
        
        // Recent trades widget
        wp_add_dashboard_widget(
            'tradepress_recent_trades',
            __( 'TradePress Recent Trades', 'tradepress' ),
            array( __CLASS__, 'recent_trades_widget' )
        );
        
        // Watchlist widget
        wp_add_dashboard_widget(
            'tradepress_watchlist',
            __( 'TradePress Watchlist', 'tradepress' ),
            array( __CLASS__, 'watchlist_widget' )
        );
    }

    /**
     * Enqueue dashboard widget scripts and styles
     *
     * @param string $hook Current admin page
     */
    public static function enqueue_scripts( $hook ) {
        if ( 'index.php' !== $hook ) {
            return;
        }
        
        wp_enqueue_style(
            'tradepress-dashboard-widgets',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/dashboard.css',
            array(),
            defined('TRADEPRESS_VERSION') ? TRADEPRESS_VERSION : '1.0.0'
        );
        
        wp_enqueue_script(
            'tradepress-dashboard-widgets',
            TRADEPRESS_PLUGIN_URL . 'assets/js/dashboard-widgets.js',
            array( 'jquery' ),
            defined('TRADEPRESS_VERSION') ? TRADEPRESS_VERSION : '1.0.0',
            true
        );
        
        wp_localize_script(
            'tradepress-dashboard-widgets',
            'tradepressWidgets',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'tradepress-dashboard-widgets' ),
                'refreshInterval' => apply_filters( 'tradepress_dashboard_refresh_interval', 60000 ),
                'i18n' => array(
                    'refreshing' => __( 'Refreshing...', 'tradepress' ),
                    'updated' => __( 'Updated', 'tradepress' ),
                    'errorRefreshing' => __( 'Error refreshing data', 'tradepress' ),
                ),
            )
        );
    }

    /**
     * Display callback for the recent symbols widget
     */
    public static function recent_symbols_widget_callback() {
        // Ensure the recent symbols class is loaded
        if (!class_exists('TradePress_Recent_Symbols')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/utils/recent-symbols-tracker.php';
        }
        
        // Get recent symbols data
        $recent_symbols_data = TradePress_Recent_Symbols::get_recent_symbols_data();
        
        // If no recent symbols, show a message
        if (empty($recent_symbols_data)) {
            echo '<p class="tradepress-no-recent">' . esc_html__('No recent symbols found. Start browsing stock data to track your recent activity.', 'tradepress') . '</p>';
            echo '<p><a href="' . esc_url(admin_url('admin.php?page=tradepress_research')) . '" class="button button-secondary">' . esc_html__('Browse Stocks', 'tradepress') . '</a></p>';
            return;
        }
        
        // Output the widget content
        ?>
        <div class="tradepress-recent-symbols-widget">
            <div class="tradepress-recent-symbols-list">
                <?php foreach ($recent_symbols_data as $symbol => $data): ?>
                    <div class="tradepress-recent-symbol-item">
                        <div class="tradepress-symbol-icon">
                            <img src="<?php echo esc_url($data['thumbnail']); ?>" alt="<?php echo esc_attr($symbol); ?>" width="40" height="40">
                        </div>
                        <div class="tradepress-symbol-info">
                            <h4 class="tradepress-symbol-name">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=technical-indicators&symbol=' . $symbol)); ?>">
                                    <?php echo esc_html($symbol); ?>
                                </a>
                            </h4>
                            <p class="tradepress-company-name"><?php echo esc_html($data['company_name']); ?></p>
                        </div>
                        <div class="tradepress-symbol-price">
                            <div class="tradepress-price-value">$<?php echo number_format($data['price'], 2); ?></div>
                            <div class="tradepress-price-change <?php echo $data['is_positive'] ? 'positive' : 'negative'; ?>">
                                <?php echo $data['is_positive'] ? '+' : ''; ?><?php echo number_format($data['change'], 2); ?> (<?php echo $data['is_positive'] ? '+' : ''; ?><?php echo number_format($data['change_percent'], 2); ?>%)
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="tradepress-widget-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research')); ?>" class="tradepress-view-all-link">
                    <?php esc_html_e('View All Research', 'tradepress'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for refreshing recent symbols widget
     */
    public static function ajax_refresh_recent_symbols_widget() {
        check_ajax_referer( 'tradepress-dashboard-widgets', 'nonce' );
        
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Permission denied' );
        }
        
        ob_start();
        self::recent_symbols_widget_callback();
        $html = ob_get_clean();
        
        wp_send_json_success( array(
            'html' => $html,
            'timestamp' => current_time( 'mysql' )
        ) );
    }

    /**
     * Market overview widget content
     */
    public static function market_overview_widget() {
        // Get top market movers
        $market_movers = self::get_market_movers();
        
        ?>
        <div class="tradepress-widget market-overview">
            <div class="widget-header">
                <h3><?php esc_html_e( 'Market Movers', 'tradepress' ); ?></h3>
                <div class="widget-actions">
                    <a href="#" class="refresh-widget" data-widget="market-overview">
                        <span class="dashicons dashicons-update"></span>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <?php if ( empty( $market_movers ) ) : ?>
                    <p class="no-data"><?php esc_html_e( 'No market data available.', 'tradepress' ); ?></p>
                <?php else : ?>
                    <div class="market-movers-tabs">
                        <ul class="tabs-nav">
                            <li class="active"><a href="#gainers"><?php esc_html_e( 'Gainers', 'tradepress' ); ?></a></li>
                            <li><a href="#losers"><?php esc_html_e( 'Losers', 'tradepress' ); ?></a></li>
                            <li><a href="#volume"><?php esc_html_e( 'Volume', 'tradepress' ); ?></a></li>
                        </ul>
                        
                        <div class="tabs-content">
                            <div id="gainers" class="tab-content active">
                                <table class="movers-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
                                            <th><?php esc_html_e( 'Price', 'tradepress' ); ?></th>
                                            <th><?php esc_html_e( 'Change', 'tradepress' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $market_movers['gainers'] as $symbol ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $symbol['symbol'] ); ?></td>
                                                <td><?php echo esc_html( number_format( $symbol['price'], 2 ) ); ?></td>
                                                <td class="positive">+<?php echo esc_html( number_format( $symbol['change_pct'], 2 ) ); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div id="losers" class="tab-content">
                                <table class="movers-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
                                            <th><?php esc_html_e( 'Price', 'tradepress' ); ?></th>
                                            <th><?php esc_html_e( 'Change', 'tradepress' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $market_movers['losers'] as $symbol ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $symbol['symbol'] ); ?></td>
                                                <td><?php echo esc_html( number_format( $symbol['price'], 2 ) ); ?></td>
                                                <td class="negative"><?php echo esc_html( number_format( $symbol['change_pct'], 2 ) ); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div id="volume" class="tab-content">
                                <table class="movers-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
                                            <th><?php esc_html_e( 'Price', 'tradepress' ); ?></th>
                                            <th><?php esc_html_e( 'Volume', 'tradepress' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $market_movers['volume'] as $symbol ) : ?>
                                            <tr>
                                                <td><?php echo esc_html( $symbol['symbol'] ); ?></td>
                                                <td><?php echo esc_html( number_format( $symbol['price'], 2 ) ); ?></td>
                                                <td><?php echo esc_html( number_format( $symbol['volume'] ) ); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Recent trades widget content
     */
    public static function recent_trades_widget() {
        // Get recent trades
        $recent_trades = self::get_recent_trades();
        
        ?>
        <div class="tradepress-widget recent-trades">
            <div class="widget-header">
                <h3><?php esc_html_e( 'Recent Trades', 'tradepress' ); ?></h3>
                <div class="widget-actions">
                    <a href="#" class="refresh-widget" data-widget="recent-trades">
                        <span class="dashicons dashicons-update"></span>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <?php if ( empty( $recent_trades ) ) : ?>
                    <p class="no-data"><?php esc_html_e( 'No recent trades found.', 'tradepress' ); ?></p>
                <?php else : ?>
                    <table class="trades-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Date', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Type', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Price', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Quantity', 'tradepress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent_trades as $trade ) : 
                                $class = $trade['type'] === 'buy' ? 'buy-trade' : 'sell-trade';
                            ?>
                                <tr class="<?php echo esc_attr( $class ); ?>">
                                    <td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $trade['date'] ) ) ); ?></td>
                                    <td><?php echo esc_html( $trade['symbol'] ); ?></td>
                                    <td><?php echo esc_html( ucfirst( $trade['type'] ) ); ?></td>
                                    <td><?php echo esc_html( number_format( $trade['price'], 2 ) ); ?></td>
                                    <td><?php echo esc_html( $trade['quantity'] ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="widget-footer">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress-trades' ) ); ?>" class="view-all">
                            <?php esc_html_e( 'View All Trades', 'tradepress' ); ?> →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Watchlist widget content
     */
    public static function watchlist_widget() {
        // Get watchlist items
        $watchlist = self::get_watchlist();
        
        ?>
        <div class="tradepress-widget watchlist">
            <div class="widget-header">
                <h3><?php esc_html_e( 'Your Watchlist', 'tradepress' ); ?></h3>
                <div class="widget-actions">
                    <a href="#" class="refresh-widget" data-widget="watchlist">
                        <span class="dashicons dashicons-update"></span>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <?php if ( empty( $watchlist ) ) : ?>
                    <p class="no-data"><?php esc_html_e( 'Your watchlist is empty.', 'tradepress' ); ?></p>
                <?php else : ?>
                    <table class="watchlist-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Symbol', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Price', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Change', 'tradepress' ); ?></th>
                                <th><?php esc_html_e( 'Notes', 'tradepress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $watchlist as $item ) : 
                                $class = $item['change_pct'] >= 0 ? 'positive' : 'negative';
                                $change_prefix = $item['change_pct'] >= 0 ? '+' : '';
                            ?>
                                <tr>
                                    <td><?php echo esc_html( $item['symbol'] ); ?></td>
                                    <td><?php echo esc_html( number_format( $item['price'], 2 ) ); ?></td>
                                    <td class="<?php echo esc_attr( $class ); ?>">
                                        <?php echo esc_html( $change_prefix . number_format( $item['change_pct'], 2 ) ); ?>%
                                    </td>
                                    <td class="notes" title="<?php echo esc_attr( $item['notes'] ); ?>">
                                        <?php echo esc_html( wp_trim_words( $item['notes'], 5, '...' ) ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="widget-footer">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress-watchlist' ) ); ?>" class="view-all">
                            <?php esc_html_e( 'Manage Watchlist', 'tradepress' ); ?> →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX handler for refreshing market widget
     */
    public static function ajax_refresh_market_widget() {
        check_ajax_referer( 'tradepress-dashboard-widgets', 'nonce' );
        
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Permission denied' );
        }
        
        ob_start();
        self::market_overview_widget();
        $html = ob_get_clean();
        
        wp_send_json_success( array(
            'html' => $html,
            'timestamp' => current_time( 'mysql' )
        ) );
    }

    /**
     * AJAX handler for refreshing trades widget
     */
    public static function ajax_refresh_trades_widget() {
        check_ajax_referer( 'tradepress-dashboard-widgets', 'nonce' );
        
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Permission denied' );
        }
        
        ob_start();
        self::recent_trades_widget();
        $html = ob_get_clean();
        
        wp_send_json_success( array(
            'html' => $html,
            'timestamp' => current_time( 'mysql' )
        ) );
    }

    /**
     * AJAX handler for refreshing watchlist widget
     */
    public static function ajax_refresh_watchlist_widget() {
        check_ajax_referer( 'tradepress-dashboard-widgets', 'nonce' );
        
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Permission denied' );
        }
        
        ob_start();
        self::watchlist_widget();
        $html = ob_get_clean();
        
        wp_send_json_success( array(
            'html' => $html,
            'timestamp' => current_time( 'mysql' )
        ) );
    }

    /**
     * Get market movers data
     * In a real implementation, this would fetch from actual API
     *
     * @return array Market movers data
     */
    private static function get_market_movers() {
        // Sample data - in production this would come from actual API
        $market_movers = array(
            'gainers' => array(
                array(
                    'symbol' => 'AAPL',
                    'price' => 185.92,
                    'change_pct' => 2.45,
                ),
                array(
                    'symbol' => 'MSFT',
                    'price' => 376.17,
                    'change_pct' => 1.87,
                ),
                array(
                    'symbol' => 'NVDA',
                    'price' => 950.02,
                    'change_pct' => 3.25,
                ),
                array(
                    'symbol' => 'AMZN',
                    'price' => 180.35,
                    'change_pct' => 1.23,
                ),
            ),
            'losers' => array(
                array(
                    'symbol' => 'META',
                    'price' => 487.95,
                    'change_pct' => -1.74,
                ),
                array(
                    'symbol' => 'NFLX',
                    'price' => 657.31,
                    'change_pct' => -2.12,
                ),
                array(
                    'symbol' => 'GOOG',
                    'price' => 156.98,
                    'change_pct' => -0.87,
                ),
                array(
                    'symbol' => 'TSLA',
                    'price' => 223.31,
                    'change_pct' => -3.54,
                ),
            ),
            'volume' => array(
                array(
                    'symbol' => 'AAPL',
                    'price' => 185.92,
                    'volume' => 75489632,
                ),
                array(
                    'symbol' => 'SPY',
                    'price' => 523.89,
                    'volume' => 68741523,
                ),
                array(
                    'symbol' => 'NVDA',
                    'price' => 950.02,
                    'volume' => 35874123,
                ),
                array(
                    'symbol' => 'TSLA',
                    'price' => 223.31,
                    'volume' => 28561974,
                ),
            ),
        );
        
        return apply_filters( 'tradepress_market_movers_data', $market_movers );
    }

    /**
     * Get recent trades data
     * In a real implementation, this would fetch from actual database
     *
     * @return array Recent trades data
     */
    private static function get_recent_trades() {
        // Sample data - in production this would come from database
        $trades = array(
            array(
                'date' => date( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
                'symbol' => 'AAPL',
                'type' => 'buy',
                'price' => 185.92,
                'quantity' => 10,
            ),
            array(
                'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
                'symbol' => 'MSFT',
                'type' => 'sell',
                'price' => 376.17,
                'quantity' => 5,
            ),
            array(
                'date' => date( 'Y-m-d H:i:s', strtotime( '-3 days' ) ),
                'symbol' => 'NVDA',
                'type' => 'buy',
                'price' => 920.45,
                'quantity' => 2,
            ),
            array(
                'date' => date( 'Y-m-d H:i:s', strtotime( '-1 week' ) ),
                'symbol' => 'TSLA',
                'type' => 'sell',
                'price' => 228.82,
                'quantity' => 8,
            ),
        );
        
        return apply_filters( 'tradepress_recent_trades_data', $trades );
    }

    /**
     * Get watchlist data
     * In a real implementation, this would fetch from user metadata
     *
     * @return array Watchlist data
     */
    private static function get_watchlist() {
        // Sample data - in production this would come from user metadata
        $watchlist = array(
            array(
                'symbol' => 'AAPL',
                'price' => 185.92,
                'change_pct' => 2.45,
                'notes' => 'Waiting for Q3 earnings report before buying more.',
            ),
            array(
                'symbol' => 'MSFT',
                'price' => 376.17,
                'change_pct' => 1.87,
                'notes' => 'Strong cloud growth, considering increasing position.',
            ),
            array(
                'symbol' => 'TSLA',
                'price' => 223.31,
                'change_pct' => -3.54,
                'notes' => 'Looking for entry below $200.',
            ),
            array(
                'symbol' => 'NVDA',
                'price' => 950.02,
                'change_pct' => 3.25,
                'notes' => 'Watching AI developments closely.',
            ),
        );
        
        return apply_filters( 'tradepress_watchlist_data', $watchlist );
    }
}

// Initialize dashboard widgets
add_action( 'init', array( 'TradePress_Dashboard_Widgets', 'init' ) );