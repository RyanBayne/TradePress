<?php
/**
 * The research functionality of the plugin.
 * 
 * @todo The Research page title does not dislay with the same format as the other pages 
 *
 * @link       https://example.com
 * @since      1.0.0
 * @package    TradePress
 * @subpackage TradePress/admin
 * @created    <?php echo date('Y-m-d H:i:s'); ?>
 */

class TradePress_Research {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The current active tab.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $active_tab    The current active tab.
     */
    private $active_tab;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'price_forecast';
    }

    /**
     * Static method to output the research area - moved from TradePress_Admin_Research_Area
     * 
     * @since    1.1.0
     */
    public static function output() {
        // Instantiate the Research class
        global $tradepress_research;
        if (!isset($tradepress_research)) {
            $tradepress_research = new TradePress_Research('tradepress', TRADEPRESS_VERSION);
            $tradepress_research->init(); // Initialize hooks for screen options
        }
        
        // Get current active tab
        $current_tab = empty($_GET['tab']) ? 'price_forecast' : sanitize_title(wp_unslash($_GET['tab']));
        
        // Get tabs to display tab name in title
        $tabs = $tradepress_research->get_tabs();
        
        // Output the UI
        ?>
        <div class="wrap tradepress-admin">
            <h1>
                <?php 
                echo esc_html__('TradePress Research', 'tradepress');
                if ( ! empty( $tabs[ $current_tab ]['title'] ) ) {
                    echo ' &gt; ';
                    echo esc_html($tabs[$current_tab]['title']);
                }
                ?>
            </h1>
            
            <nav class="nav-tab-wrapper">
                <?php
                foreach ($tabs as $tab_id => $tab_data) {
                    $active = $current_tab === $tab_id ? ' nav-tab-active' : '';
                    echo '<a href="' . esc_url(admin_url('admin.php?page=tradepress_research&tab=' . $tab_id)) . '" class="nav-tab' . esc_attr($active) . '">' . esc_html($tab_data['title']) . '</a>';
                }
                ?>
            </nav>
            
            <div class="tradepress-research-content">
                <?php $tradepress_research->load_research_tab_content($current_tab); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Set up screen options - implemented from reference in the original TradePress_Admin_Research_Area class
     * 
     * @since    1.1.0
     */
    public static function setup_screen() {
        $screen = get_current_screen();
        
        if (!is_object($screen)) {
            return;
        }
        
        // Add filter to modify the admin page title.
        add_filter( 'admin_title', array( __CLASS__, 'filter_admin_title' ), 10, 2 );
        // Add screen options for the research page
        if (strpos($screen->id, 'tradepress_research') !== false) {
            add_screen_option('layout_columns', array(
                'max' => 2,
                'default' => 1
            ));
            
            // Add help tabs if needed
            $screen->add_help_tab(array(
                'id'      => 'tradepress_research_overview',
                'title'   => __('Overview', 'tradepress'),
                'content' => '<p>' . __('This page provides research tools for market analysis and stock evaluation.', 'tradepress') . '</p>',
            ));
        }
    }

    /**
     * Filters the admin page title to make it dynamic based on the current tab.
     *
     * @since 1.1.0
     * @param string $admin_title The current admin title.
     * @param string $title       The original page title.
     * @return string The modified admin title.
     */
    public static function filter_admin_title( $admin_title, $title ) {
        // This is a static method, so we need to create a new instance to access get_tabs().
        // In a future refactor, making get_tabs() static would be more efficient.
        $instance = new self( 'tradepress', defined( 'TRADEPRESS_VERSION' ) ? TRADEPRESS_VERSION : '1.1.0' );
        $tabs = $instance->get_tabs();
        
        $current_tab = empty( $_GET['tab'] ) ? 'price_forecast' : sanitize_key( $_GET['tab'] );

        if ( isset( $tabs[ $current_tab ]['title'] ) ) {
            $tab_title = $tabs[ $current_tab ]['title'];
            $base_title = __( 'TradePress Research', 'tradepress' );

            /* translators: 1: Tab title, 2: Page title, 3: Site title. */
            return sprintf( '%1$s &lsaquo; %2$s &mdash; %3$s', $tab_title, $base_title, get_bloginfo( 'name' ) );
        }
        return $admin_title;
    }

    /**
     * Initialize hooks
     *
     * @since    1.0.0
     */
    public function init() {
        // Add screen options - try different hook names that might be used in your setup
        add_action('load-tradepress_page_tradepress-research', array($this, 'setup_price_forecast_screen_options'));
        add_action('load-tradepress_page_tradepress_research', array($this, 'setup_price_forecast_screen_options'));
        add_action('current_screen', array($this, 'check_current_screen'));
        
        // Set screen options
        add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
    }
    
    /**
     * Check the current screen and set up screen options if we're on our page
     * This is a fallback in case the direct hook methods don't work
     */
    public function check_current_screen() {
        $screen = get_current_screen();
        if (is_object($screen) && strpos($screen->id, 'tradepress') !== false && strpos($screen->id, 'research') !== false) {
            $this->setup_price_forecast_screen_options();
        }
    }

    /**
     * Display the research page.
     *
     * @since    1.0.0
     */
    public function display_page() {
        // Enqueue styles and scripts if needed
        wp_enqueue_style($this->plugin_name . '-research', plugin_dir_url(__FILE__) . 'css/tradepress-research.css', array(), $this->version, 'all');
        wp_enqueue_script($this->plugin_name . '-research', plugin_dir_url(__FILE__) . 'js/tradepress-research.js', array('jquery'), $this->version, false);

        // Display the page
        include_once('partials/research-display.php');
    }

    /**
     * Get the tab list for the research page.
     *
     * @since    1.0.0
     * @return   array    The array of tabs.
     */
    public function get_tabs() {
        $tabs = array(
            'overview' => array(
                'title' => __('Overview', 'tradepress'),
                'callback' => array($this, 'load_overview_tab')
            ),
            'sector-rotation' => array(
                'title' => __('Sector Rotation', 'tradepress'),
                'callback' => array($this, 'load_sector_rotation_tab')
            ),
            'market-correlations' => array(
                'title' => __('Market Correlations', 'tradepress'),
                'callback' => array($this, 'load_market_correlations_tab')
            ),
            'economic-calendar' => array(
                'title' => __('Economic Calendar', 'tradepress'),
                'callback' => array($this, 'load_economic_calendar_tab')
            ),
            'technical-indicators' => array(
                'title' => __('Technical Indicators', 'tradepress'),
                'callback' => array($this, 'load_technical_indicators_tab')
            ),
            'earnings' => array(
                'title' => __('Earnings Calendar', 'tradepress'),
                'callback' => array($this, 'load_earnings_tab')
            ),
            'price_forecast' => array(
                'title' => __('Price Forecast', 'tradepress'),
                'callback' => array($this, 'render_price_forecast_tab')
            ),
            'news_feed' => array(
                'title' => __('News Feed', 'tradepress'),
                'callback' => array($this, 'load_news_feed_tab')
            ),
            'social_networks' => array(
                'title' => __('Social Networks', 'tradepress'),
                'callback' => array($this, 'load_social_networks_tab')
            ),
        );

        return apply_filters('tradepress_research_tabs', $tabs);
    }

    /**
     * Load research tab content
     * 
     * @since 1.0.0
     * @param string $tab The current tab
     * @return void
     */
    public function load_research_tab_content($tab) {
        $tabs = $this->get_tabs();
        
        if (isset($tabs[$tab]) && isset($tabs[$tab]['callback']) && is_callable($tabs[$tab]['callback'])) {
            call_user_func($tabs[$tab]['callback']);
        } else {
            // Default to overview tab if the requested tab doesn't exist
            $this->load_overview_tab();
        }
    }
    
    /**
     * Load the Overview tab content
     */
    public function load_overview_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/overview.php';
        tradepress_research_overview_tab_content();
    }
    
    /**
     * Load the Sector Rotation tab content
     */
    public function load_sector_rotation_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/sector-rotation.php';
        tradepress_sector_rotation_tab_content();
    }
    
    /**
     * Load the Economic Calendar tab content
     */
    public function load_economic_calendar_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/economic-calendar.php';
        tradepress_economic_calendar_tab_content();
    }
    
    /**
     * Load the Technical Indicators tab content
     */
    public function load_technical_indicators_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/technical-indicators.php';
        tradepress_technical_indicators_tab_content();
    }
    
    /**
     * Load the Market Correlations tab content
     */
    public function load_market_correlations_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/market-correlations.php';
        tradepress_market_correlations_tab_content();
    }

    /**
     * Load the Earnings tab content
     */
    public function load_earnings_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/earnings.php';
        tradepress_earnings_tab_content();
    }

    /**
     * Load the News Feed tab content
     */
    public function load_news_feed_tab() {
        include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/news-feed.php';
        tradepress_news_feed_tab_content();
    }

    /**
     * Load the Social Networks tab content
     */
    public function load_social_networks_tab() {
        // Enqueue Social Networks CSS
        wp_enqueue_style(
            'tradepress-social-networks',
            TRADEPRESS_PLUGIN_URL . 'assets/css/pages/research-social-networks.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        require_once(TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/research/view/social-networks/social-networks-tabs.php');
        
        // Create sub-navigation for Social Networks
        $current_subtab = isset($_GET['subtab']) ? sanitize_title($_GET['subtab']) : 'settings';
        
        ?>
        <div class="social-networks-container">
            <h3><?php _e('Social Networks Management', 'tradepress'); ?></h3>
            <p><?php _e('Configure social media platform integrations for research and signal monitoring.', 'tradepress'); ?></p>
            
            <div class="subsubsub">
                <ul>
                    <li><a href="<?php echo esc_url(add_query_arg(array('subtab' => 'settings'), remove_query_arg('subtab'))); ?>" class="<?php echo $current_subtab === 'settings' ? 'current' : ''; ?>"><?php _e('Settings', 'tradepress'); ?></a> |</li>
                    <li><a href="<?php echo esc_url(add_query_arg(array('subtab' => 'switches'), remove_query_arg('subtab'))); ?>" class="<?php echo $current_subtab === 'switches' ? 'current' : ''; ?>"><?php _e('Platform Switches', 'tradepress'); ?></a></li>
                </ul>
            </div>
            
            <div class="social-networks-content">
                <?php
                switch ($current_subtab) {
                    case 'switches':
                        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/research/view/social-networks/platform_switches.php';
                        break;
                    case 'settings':
                    default:
                        include TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/research/view/social-networks/sp-settings.php';
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the Price Forecast tab.
     *
     * @since    1.0.0
     */
    public function render_price_forecast_tab() {
        // First, ensure the class file is loaded
        $forecast_table_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/price-forecast-table.php';
        
        if (file_exists($forecast_table_file)) {
            require_once($forecast_table_file);
        } else {
            echo '<div class="notice notice-error"><p>Price forecast table class file not found at: ' . esc_html($forecast_table_file) . '</p></div>';
        }
        
        // Now include the view file
        require_once(TRADEPRESS_PLUGIN_DIR . 'admin/page/research/partials/research-price-forecast.php');
    }

    /**
     * Get sample price forecast data.
     * In a real scenario, this would fetch data from various sources.
     *
     * @since    1.0.0
     * @return   array    Sample forecast data.
     */
    public function get_price_forecast_data() {
        // Sample data - in production this would come from actual APIs/sources
        $forecast_data = array(
            'AAPL' => array(
                'sources' => array(
                    'Analyst Consensus' => array(
                        'low' => 170.25,
                        'average' => 198.50,
                        'high' => 225.75,
                    ),
                    'Algorithm Prediction' => array(
                        'low' => 165.80,
                        'average' => 195.20,
                        'high' => 220.40,
                    ),
                    'Technical Analysis' => array(
                        'low' => 172.60,
                        'average' => 200.10,
                        'high' => 230.30,
                    ),
                ),
            ),
            'MSFT' => array(
                'sources' => array(
                    'Analyst Consensus' => array(
                        'low' => 340.20,
                        'average' => 375.50,
                        'high' => 410.75,
                    ),
                    'Algorithm Prediction' => array(
                        'low' => 335.90,
                        'average' => 372.30,
                        'high' => 405.60,
                    ),
                    'Technical Analysis' => array(
                        'low' => 345.75,
                        'average' => 380.25,
                        'high' => 415.30,
                    ),
                ),
            ),
            'GOOGL' => array(
                'sources' => array(
                    'Analyst Consensus' => array(
                        'low' => 125.40,
                        'average' => 145.20,
                        'high' => 165.75,
                    ),
                    'Algorithm Prediction' => array(
                        'low' => 120.80,
                        'average' => 142.50,
                        'high' => 162.30,
                    ),
                    'Technical Analysis' => array(
                        'low' => 128.60,
                        'average' => 148.90,
                        'high' => 168.25,
                    ),
                ),
            ),
        );

        return apply_filters('tradepress_price_forecast_data', $forecast_data);
    }

    /**
     * Display detailed forecast information for a specific symbol
     *
     * @param string $symbol The symbol to display details for
     * @return void
     */
    public function display_symbol_details($symbol) {
        // Generate some demo data for the selected symbol
        $demo_data = $this->generate_symbol_forecast_demo_data($symbol);
        
        ?>
        <div class="symbol-forecast-details">
            <div class="symbol-header">
                <h3><?php echo esc_html(sprintf(__('Price Forecast for %s', 'tradepress'), $symbol)); ?></h3>
                <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress-research&tab=price_forecast')); ?>" class="button"><?php _e('Back to All Forecasts', 'tradepress'); ?></a>
            </div>
            
            <div class="forecast-summary">
                <div class="forecast-card current-price">
                    <span class="label"><?php _e('Current Price', 'tradepress'); ?></span>
                    <span class="value">$<?php echo number_format($demo_data['current_price'], 2); ?></span>
                </div>
                
                <div class="forecast-range">
                    <div class="forecast-card low">
                        <span class="label"><?php _e('Low Forecast', 'tradepress'); ?></span>
                        <span class="value" style="<?php echo $this->get_confidence_color_style($demo_data['confidence_low']); ?>">
                            $<?php echo number_format($demo_data['forecast_low'], 2); ?>
                        </span>
                        <span class="confidence"><?php echo number_format($demo_data['confidence_low'], 1); ?>% <?php _e('confidence', 'tradepress'); ?></span>
                    </div>
                    
                    <div class="forecast-card medium">
                        <span class="label"><?php _e('Medium Forecast', 'tradepress'); ?></span>
                        <span class="value" style="<?php echo $this->get_confidence_color_style($demo_data['confidence_medium']); ?>">
                            $<?php echo number_format($demo_data['forecast_medium'], 2); ?>
                        </span>
                        <span class="confidence"><?php echo number_format($demo_data['confidence_medium'], 1); ?>% <?php _e('confidence', 'tradepress'); ?></span>
                    </div>
                    
                    <div class="forecast-card high">
                        <span class="label"><?php _e('High Forecast', 'tradepress'); ?></span>
                        <span class="value" style="<?php echo $this->get_confidence_color_style($demo_data['confidence_high']); ?>">
                            $<?php echo number_format($demo_data['forecast_high'], 2); ?>
                        </span>
                        <span class="confidence"><?php echo number_format($demo_data['confidence_high'], 1); ?>% <?php _e('confidence', 'tradepress'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="forecast-sources">
                <h4><?php _e('Source Data', 'tradepress'); ?></h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Source', 'tradepress'); ?></th>
                            <th><?php _e('Low', 'tradepress'); ?></th>
                            <th><?php _e('Medium', 'tradepress'); ?></th>
                            <th><?php _e('High', 'tradepress'); ?></th>
                            <th><?php _e('Date', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demo_data['sources'] as $source): ?>
                        <tr>
                            <td><?php echo esc_html($source['name']); ?></td>
                            <td>$<?php echo number_format($source['low'], 2); ?></td>
                            <td>$<?php echo number_format($source['medium'], 2); ?></td>
                            <td>$<?php echo number_format($source['high'], 2); ?></td>
                            <td><?php echo esc_html(human_time_diff(strtotime($source['date']), current_time('timestamp'))); ?> <?php _e('ago', 'tradepress'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="forecast-time-horizons">
                <h4><?php _e('Forecasts by Time Horizon', 'tradepress'); ?></h4>
                <div class="time-horizon-tabs">
                    <button class="time-horizon-tab active" data-horizon="1m"><?php _e('1 Month', 'tradepress'); ?></button>
                    <button class="time-horizon-tab" data-horizon="3m"><?php _e('3 Month', 'tradepress'); ?></button>
                    <button class="time-horizon-tab" data-horizon="6m"><?php _e('6 Month', 'tradepress'); ?></button>
                    <button class="time-horizon-tab" data-horizon="1y"><?php _e('1 Year', 'tradepress'); ?></button>
                </div>
                
                <!-- Time horizon content panels will be populated with JavaScript -->
                <div id="horizon-1m" class="time-horizon-content active">
                    <!-- 1 Month forecast data -->
                    <p><?php _e('The 1-month forecast shows a potential', 'tradepress'); ?> <?php echo ($demo_data['forecast_medium'] > $demo_data['current_price']) ? 'increase' : 'decrease'; ?> 
                    <?php _e('of approximately', 'tradepress'); ?> <?php echo abs(number_format(($demo_data['forecast_medium'] - $demo_data['current_price']) / $demo_data['current_price'] * 100, 1)); ?>%.</p>
                </div>
                
                <!-- Additional horizon panels would be populated in a real implementation -->
            </div>
            

        </div>
        <?php
    }

    /**
     * Generate demo data for a specific symbol
     *
     * @param string $symbol The symbol to generate data for
     * @return array Demo data
     */
    private function generate_symbol_forecast_demo_data($symbol) {
        // Generate random current price between $10 and $1000
        $current_price = mt_rand(100, 10000) / 10;
        
        // Generate random confidence levels
        $confidence_low = mt_rand(30, 95);
        $confidence_medium = mt_rand(40, 98);
        $confidence_high = mt_rand(30, 90);
        $avg_confidence = round(($confidence_low + $confidence_medium + $confidence_high) / 3, 1);
        
        // Generate forecasts based on current price
        $forecast_change_factor = 1 + (mt_rand(-15, 40) / 100); // -15% to +40% change
        $forecast_medium = round($current_price * $forecast_change_factor, 2);
        $forecast_low = round($forecast_medium * (1 - (mt_rand(5, 20) / 100)), 2); // 5-20% below medium
        $forecast_high = round($forecast_medium * (1 + (mt_rand(5, 25) / 100)), 2); // 5-25% above medium
        
        // Generate source data
        $sources = array();
        $source_names = array(
            'Financial Analysts Consensus',
            'Technical Analysis',
            'Algorithm Prediction',
            'Market Sentiment Index',
            'Historical Pattern Analysis'
        );
        
        foreach ($source_names as $name) {
            // Random variation from the forecast values
            $variation = mt_rand(90, 110) / 100;
            $days_ago = mt_rand(0, 7);
            
            $sources[] = array(
                'name' => $name,
                'low' => round($forecast_low * $variation, 2),
                'medium' => round($forecast_medium * $variation, 2),
                'high' => round($forecast_high * $variation, 2),
                'date' => date('Y-m-d H:i:s', strtotime("-$days_ago days"))
            );
        }
        
        return array(
            'symbol' => $symbol,
            'current_price' => $current_price,
            'forecast_low' => $forecast_low,
            'forecast_medium' => $forecast_medium,
            'forecast_high' => $forecast_high,
            'confidence_low' => $confidence_low,
            'confidence_medium' => $confidence_medium,
            'confidence_high' => $confidence_high,
            'confidence' => $avg_confidence,
            'sources' => $sources,
            'last_updated' => date('Y-m-d H:i:s', strtotime('-' . mt_rand(0, 14) . ' days'))
        );
    }

    /**
     * Generate CSS background color style based on confidence level
     *
     * @param float $confidence 0-100 confidence score
     * @return string
     */
    private function get_confidence_color_style($confidence) {
        // Normalize confidence to 0-1 range
        $normalized = max(0, min(100, $confidence)) / 100;
        
        // Calculate color components (red to yellow to green)
        if ($normalized < 0.5) {
            // Red to yellow
            $r = 255;
            $g = round(255 * ($normalized * 2));
            $b = 0;
        } else {
            // Yellow to green
            $r = round(255 * (1 - ($normalized - 0.5) * 2));
            $g = 255;
            $b = 0;
        }
        
        // Return the background-color style with semi-transparent background
        return sprintf(
            'background-color: rgba(%d, %d, %d, 0.3); padding: 3px 8px; border-radius: 4px; display: inline-block;',
            $r, $g, $b
        );
    }

    /**
     * Register screen options for the Price Forecast tab
     *
     * @since    1.0.0
     */
    public function setup_price_forecast_screen_options() {
        $screen = get_current_screen();
        
        // Only add screen options on our page - check for various possible screen IDs
        if (!is_object($screen) || 
            !($screen->id === 'tradepress_page_tradepress-research' || 
              $screen->id === 'tradepress_page_tradepress_research' ||
              strpos($screen->id, 'tradepress') !== false && strpos($screen->id, 'research') !== false)) {
            return;
        }
        
        // Add per page screen option
        add_screen_option('per_page', array(
            'label' => __('Forecasts per page', 'tradepress'),
            'default' => 10,
            'option' => 'tradepress_price_forecasts_per_page'
        ));
    }
    
    /**
     * Save screen option value
     *
     * @since    1.0.0
     * @param    mixed     $status  Status of the option
     * @param    string    $option  Option name
     * @param    mixed     $value   Option value
     * @return   mixed              Filtered value
     */
    public function set_screen_option($status, $option, $value) {
        if ('tradepress_price_forecasts_per_page' === $option) {
            return $value;
        }
        return $status;
    }
}
