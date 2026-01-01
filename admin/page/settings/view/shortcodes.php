<?php
/**
 * TradePress Shortcodes Settings
 *
 * @package TradePress/Admin
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Settings_Shortcodes Class
 */
class TradePress_Admin_Settings_Shortcodes {
    /**
     * Setting page ID
     * 
     * @var string
     */
    protected $id;

    /**
     * Setting page label
     * 
     * @var string
     */
    protected $label;
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'shortcodes';
        $this->label = __( 'Shortcodes', 'tradepress' );

        add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        
        // Enqueue styles and scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue necessary styles and scripts
     */
    public function enqueue_assets( $hook ) {
        // Only load on settings page
        if ( strpos( $hook, 'page_tradepress-settings' ) !== false ) {
            wp_enqueue_style(
                'tradepress-shortcodes-styles',
                plugins_url('css/admin-shortcodes.css', dirname(dirname(__FILE__))),
                array(),
                defined('TRADEPRESS_VERSION') ? TRADEPRESS_VERSION : time()
            );
            
            // Add JavaScript for clipboard functionality
            wp_enqueue_script(
                'tradepress-shortcodes-script',
                plugins_url('assets/js/shortcodes.js', dirname(dirname(__FILE__))),
                array('jquery'),
                defined('TRADEPRESS_VERSION') ? TRADEPRESS_VERSION : time(),
                true
            );
            
            // Add dashicons for the copy button
            wp_enqueue_style('dashicons');
        }
    }

    /**
     * Add this page to settings
     */
    public function add_settings_page($pages) {
        $pages[$this->id] = $this->label;
        return $pages;
    }

    /**
     * Output the settings
     */
    public function output() {
        ?>
        <div class="tradepress-shortcodes-settings">
            <div class="tradepress-shortcodes-header">
                <h2><?php esc_html_e('TradePress Shortcodes', 'tradepress'); ?></h2>
                <p class="description"><?php esc_html_e('Use these shortcodes to embed TradePress features into your posts and pages.', 'tradepress'); ?></p>
            </div>
            
            <div class="shortcodes-filter">
                <label for="shortcode-status-filter"><?php esc_html_e('Filter by Status:', 'tradepress'); ?></label>
                <select id="shortcode-status-filter">
                    <option value="all"><?php esc_html_e('All', 'tradepress'); ?></option>
                    <option value="live"><?php esc_html_e('Live', 'tradepress'); ?></option>
                    <option value="demo"><?php esc_html_e('Demo', 'tradepress'); ?></option>
                </select>
            </div>
            
            <div class="shortcode-grid">
                <?php 
                // Define available shortcodes
                $shortcodes = array(
                    'market_data' => array(
                        'name' => __('Market Data', 'tradepress'),
                        'code' => '[tradepress_market_data symbol="AAPL" type="quote"]',
                        'description' => __('Displays current market data for a specified symbol.', 'tradepress'),
                        'parameters' => array(
                            'symbol' => __('Stock symbol (required)', 'tradepress'),
                            'type' => __('Data type: quote, chart, history (default: quote)', 'tradepress'),
                            'days' => __('Number of days for historical data (default: 30)', 'tradepress')
                        ),
                        'status' => 'live'
                    ),
                    'watchlist' => array(
                        'name' => __('Watchlist', 'tradepress'),
                        'code' => '[tradepress_watchlist id="default" columns="symbol,price,change"]',
                        'description' => __('Displays a watchlist with selected symbols.', 'tradepress'),
                        'parameters' => array(
                            'id' => __('Watchlist ID (default: default)', 'tradepress'),
                            'columns' => __('Columns to display (comma-separated)', 'tradepress'),
                            'limit' => __('Maximum number of symbols to show (default: 10)', 'tradepress')
                        ),
                        'status' => 'demo'
                    ),
                    'stock_chart' => array(
                        'name' => __('Stock Chart', 'tradepress'),
                        'code' => '[tradepress_chart symbol="MSFT" type="candlestick" period="1m"]',
                        'description' => __('Displays an interactive stock chart.', 'tradepress'),
                        'parameters' => array(
                            'symbol' => __('Stock symbol (required)', 'tradepress'),
                            'type' => __('Chart type: line, candlestick, ohlc (default: line)', 'tradepress'),
                            'period' => __('Time period: 1d, 1w, 1m, 3m, 1y, 5y (default: 1m)', 'tradepress'),
                            'height' => __('Chart height in pixels (default: 400)', 'tradepress'),
                            'indicators' => __('Technical indicators to display (comma-separated)', 'tradepress')
                        ),
                        'status' => 'demo'
                    ),
                    'portfolio' => array(
                        'name' => __('Portfolio', 'tradepress'),
                        'code' => '[tradepress_portfolio view="summary" source="alpaca"]',
                        'description' => __('Displays portfolio information from connected trading accounts.', 'tradepress'),
                        'parameters' => array(
                            'view' => __('View type: summary, detailed, performance (default: summary)', 'tradepress'),
                            'source' => __('Data source: alpaca, alltick, tdameritrade (default: alpaca)', 'tradepress'),
                            'days' => __('Performance period in days (default: 30)', 'tradepress')
                        ),
                        'status' => 'demo'
                    ),
                    'signals' => array(
                        'name' => __('Trading Signals', 'tradepress'),
                        'code' => '[tradepress_signals filter="bullish" limit="5"]',
                        'description' => __('Displays recent trading signals generated by the system.', 'tradepress'),
                        'parameters' => array(
                            'filter' => __('Signal type: all, bullish, bearish (default: all)', 'tradepress'),
                            'limit' => __('Number of signals to display (default: 5)', 'tradepress'),
                            'days' => __('Time frame in days (default: 7)', 'tradepress')
                        ),
                        'status' => 'demo'
                    ),
                    'market_movers' => array(
                        'name' => __('Market Movers', 'tradepress'),
                        'code' => '[tradepress_market_movers type="gainers" market="us" limit="5"]',
                        'description' => __('Displays top market movers (gainers, losers, most active).', 'tradepress'),
                        'parameters' => array(
                            'type' => __('Type: gainers, losers, active (default: gainers)', 'tradepress'),
                            'market' => __('Market: us, nasdaq, nyse (default: us)', 'tradepress'),
                            'limit' => __('Number of stocks to display (default: 5)', 'tradepress')
                        ),
                        'status' => 'live'
                    ),
                    'ticker' => array(
                        'name' => __('Ticker', 'tradepress'),
                        'code' => '[tradepress_ticker symbols="AAPL,MSFT,GOOG,AMZN" speed="5"]',
                        'description' => __('Displays a scrolling ticker with latest prices.', 'tradepress'),
                        'parameters' => array(
                            'symbols' => __('Comma-separated list of symbols to display', 'tradepress'),
                            'speed' => __('Scrolling speed (1-10, default: 5)', 'tradepress'),
                            'colorize' => __('Show price changes in green/red (yes/no, default: yes)', 'tradepress')
                        ),
                        'status' => 'live'
                    ),
                    'economic_calendar' => array(
                        'name' => __('Economic Calendar', 'tradepress'),
                        'code' => '[tradepress_economic_calendar days="7" importance="high,medium"]',
                        'description' => __('Displays upcoming economic events and announcements.', 'tradepress'),
                        'parameters' => array(
                            'days' => __('Number of days to show (default: 7)', 'tradepress'),
                            'importance' => __('Event importance: high, medium, low (comma-separated, default: all)', 'tradepress'),
                            'countries' => __('Country codes to include (comma-separated, default: all)', 'tradepress')
                        ),
                        'status' => 'demo'
                    ),
                );
                
                foreach ($shortcodes as $id => $shortcode) :
                ?>
                    <div class="shortcode-card <?php echo esc_attr($shortcode['status']); ?>-status">
                        <div class="shortcode-header">
                            <h4><?php echo esc_html($shortcode['name']); ?></h4>
                            <span class="shortcode-status-badge <?php echo esc_attr($shortcode['status']); ?>">
                                <?php echo $shortcode['status'] === 'live' ? esc_html__('Live', 'tradepress') : esc_html__('Demo', 'tradepress'); ?>
                            </span>
                        </div>
                        <div class="shortcode-description">
                            <?php echo esc_html($shortcode['description']); ?>
                        </div>
                        <div class="shortcode-code">
                            <code><?php echo esc_html($shortcode['code']); ?></code>
                            <button type="button" class="copy-shortcode button" data-shortcode="<?php echo esc_attr($shortcode['code']); ?>">
                                <span class="dashicons dashicons-clipboard"></span>
                            </button>
                        </div>
                        <div class="shortcode-parameters">
                            <h5><?php esc_html_e('Parameters:', 'tradepress'); ?></h5>
                            <ul>
                                <?php foreach ($shortcode['parameters'] as $param => $desc) : ?>
                                    <li><strong><?php echo esc_html($param); ?></strong>: <?php echo esc_html($desc); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="shortcode-documentation">
                <h3><?php esc_html_e('Using Shortcodes', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Shortcodes can be inserted into any WordPress post, page, or custom post type that supports the WordPress editor. Simply copy the shortcode and paste it into your content.', 'tradepress'); ?></p>
                
                <div class="shortcode-examples">
                    <h4><?php esc_html_e('Common Examples', 'tradepress'); ?></h4>
                    <div class="example">
                        <h5><?php esc_html_e('Display a stock chart for Apple:', 'tradepress'); ?></h5>
                        <code>[tradepress_chart symbol="AAPL" type="candlestick" period="3m" height="500"]</code>
                    </div>
                    <div class="example">
                        <h5><?php esc_html_e('Show top 3 daily gainers:', 'tradepress'); ?></h5>
                        <code>[tradepress_market_movers type="gainers" limit="3"]</code>
                    </div>
                    <div class="example">
                        <h5><?php esc_html_e('Display a specific watchlist with custom columns:', 'tradepress'); ?></h5>
                        <code>[tradepress_watchlist id="tech_stocks" columns="symbol,name,price,change,volume,market_cap"]</code>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Styles moved to assets/css/pages/settings-shortcodes.css -->
        <!-- JavaScript moved to assets/js/tradepress-settings.js -->
        <?php
    }

    /**
     * Save settings
     */
    public function save() {
        // No settings to save for shortcodes tab
    }
}

return new TradePress_Admin_Settings_Shortcodes();
