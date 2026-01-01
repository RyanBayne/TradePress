<?php
/**
 * TradePress main class - includes, debugging, error output, object registry, constants.
 * 
 * @author   Ryan Bayne
 * @package  TradePress
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main TradePress Class.
 *
 * @class TradePress
 */
final class WordPressTradePress {
    
    /**
     * Minimum WP version.
     *
     * @var string
     */
    public $min_wp_version = '5.4';
    
    /**
     * The single instance of the class.
     *
     * @var TradePress
     * @since 2.1
     */
    protected static $_instance = null;

    /**
     * Session instance.
     *
     * @var TradePress_Session
     */
    public $session = null; 

    /**
    * Quick and dirty way to debug by adding values that are dumped in footer.
    * 
    * @var mixed
    */
    public $dump = array();
    
    public $subsman        = null;
    public $public_notices = null;
    public $blocks_core    = null;
    public $login          = null; 
    public $login_sc       = null;  
    public $gate           = null;
    public $admin_notices  = null;
    public $available_languages = null;
    public $bugnet = null;
    
    /**
     * Main TradePress Instance.
     *
     * Ensures only one instance of TradePress is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @see WordPressSeed()
     * @return TradePress - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }                    
        return self::$_instance;
    }

    /**
     * Cloning TradePress is forbidden.
     * @since 1.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'You\'re not allowed to do that!', 'tradepress' ), '1.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     * @since 1.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'You\'re not allowed to do that!', 'tradepress' ), '1.0' );
    }
    
    /**
     * TradePress Constructor.
     */
    public function __construct() {      
        $this->define_hard_constants();        
        $this->includes();                     
        $this->init_hooks();                      
        
        $this->available_languages = array(
            //'en_US' => 'English (US)',
            //'fr_FR' => 'Français',
            //'de_DE' => 'Deutsch',
        );
                    
        do_action( 'TradePress_loaded' );
    }

    /**
     * Define TradePress in-line constants...
     * 
     * @version 2.0      
     */
    private function define_hard_constants() {
        
        $upload_dir = wp_upload_dir();
    
        // Main (package) constants.
        if ( ! defined( 'TRADEPRESS_MIN_WP_VERSION' ) ) {    define( 'TRADEPRESS_MIN_WP_VERSION', $this->min_wp_version ); }
        if ( ! defined( 'tradepress_uploads_DIR' ) ) {       define( 'tradepress_uploads_DIR', $upload_dir['basedir'] . 'tradepress-uploads/' ); }
        if ( ! defined( 'TRADEPRESS_WORDPRESSORG_SLUG' ) ) { define( 'TRADEPRESS_WORDPRESSORG_SLUG', false ); }
                    
        // Support (project) constants.
        if ( ! defined( 'TRADEPRESS_HOME' ) ) {              define( 'TRADEPRESS_HOME', 'https://TradePress.wordpress.com' ); }
        if ( ! defined( 'TRADEPRESS_FORUM' ) ) {             define( 'TRADEPRESS_FORUM', 'https://wordpress.org/support/plugin/TradePress' ); }
        if ( ! defined( 'TRADEPRESS_TWITTER' ) ) {           define( 'TRADEPRESS_TWITTER', 'https://twitter.com/TradePress' ); }
        if ( ! defined( 'TRADEPRESS_DONATE' ) ) {            define( 'TRADEPRESS_DONATE', 'https://www.patreon.com/TradePress' ); }
        if ( ! defined( 'TRADEPRESS_GITHUB' ) ) {            define( 'TRADEPRESS_GITHUB', 'https://github.com/ryanbayne/TradePress' ); }
        if ( ! defined( 'TRADEPRESS_DISCORD' ) ) {           define( 'TRADEPRESS_DISCORD', 'https://discord.gg/ScrhXPE' ); }
       
        // Author (social) constants - can act as default when support constants are false.                                                                                                              
        if ( ! defined( 'TRADEPRESS_AUTHOR_HOME' ) ) {       define( 'TRADEPRESS_AUTHOR_HOME', 'https://ryanbayne.wordpress.com' ); }
        if ( ! defined( 'TRADEPRESS_AUTHOR_TWITTER' ) ) {    define( 'TRADEPRESS_AUTHOR_TWITTER', 'http://www.twitter.com/ryan_r_bayne' ); }
        if ( ! defined( 'TRADEPRESS_AUTHOR_FACEBOOK' ) ) {   define( 'TRADEPRESS_AUTHOR_FACEBOOK', 'https://www.facebook.com/ryanrbayne' ); }
        if ( ! defined( 'TRADEPRESS_AUTHOR_DONATE' ) ) {     define( 'TRADEPRESS_AUTHOR_DONATE', 'https://www.patreon.com/TradePress' ); }
        if ( ! defined( 'TRADEPRESS_AUTHOR_GITHUB' ) ) {     define( 'TRADEPRESS_AUTHOR_GITHUB', 'https://github.com/RyanBayne' ); }
        if ( ! defined( 'TRADEPRESS_AUTHOR_LINKEDIN' ) ) {   define( 'TRADEPRESS_AUTHOR_LINKEDIN', 'https://www.linkedin.com/in/ryanrbayne/' ); }
        if ( ! defined( 'TRADEPRESS_AUTHOR_DISCORD' ) ) {    define( 'TRADEPRESS_AUTHOR_DISCORD', 'https://discord.gg/ScrhXPE' ); }

        // Constants for the TradePress Community Network API 
        if( ! defined( "TRADEPRESS_KEY_NAME" ) ){               define( "TRADEPRESS_KEY_NAME", 'name' );}
        if( ! defined( "TRADEPRESS_DEFAULT_TIMEOUT" ) ){        define( "TRADEPRESS_DEFAULT_TIMEOUT", 5 );}
        if( ! defined( "TRADEPRESS_DEFAULT_RETURN_TIMEOUT" ) ){ define( "TRADEPRESS_DEFAULT_RETURN_TIMEOUT", 20 );}
        if( ! defined( "TRADEPRESS_TOKEN_SEND_METHOD" ) ){      define( "TRADEPRESS_TOKEN_SEND_METHOD", 'HEADER' );}
        if( ! defined( "TRADEPRESS_RETRY_CALL_LIMIT" ) ){       define( "TRADEPRESS_RETRY_CALL_LIMIT", 5 );}
        if( ! defined( "TRADEPRESS_CERT_PATH" ) ){              define( "TRADEPRESS_CERT_PATH", '' );}
        if( ! defined( "TRADEPRESS_CALL_LIMIT_DEFAULT" ) ){     define( "TRADEPRESS_CALL_LIMIT_DEFAULT", '15' );}
        if( ! defined( "TRADEPRESS_CALL_LIMIT_DOUBLE" ) ){      define( "TRADEPRESS_CALL_LIMIT_DOUBLE", '30' );}
        if( ! defined( "TRADEPRESS_CALL_LIMIT_MAX" ) ){         define( "TRADEPRESS_CALL_LIMIT_MAX", '60' );}
        if( ! defined( "TRADEPRESS_CALL_LIMIT_SETTING" ) ){     define( "TRADEPRESS_CALL_LIMIT_SETTING", TRADEPRESS_CALL_LIMIT_MAX );}     
    }
    
    /**
    * Define constants that require the WP init for accessing core functions
    * and WP globals.
    * 
    * @version 1.0
    */
    private function define_wp_reliant_constants() {    
        if( !defined( "TRADEPRESS_CURRENTUSERID" ) ){ define( "TRADEPRESS_CURRENTUSERID", get_current_user_id() );}
        if( !defined( 'TRADEPRESS_BETA' ) ) { define( 'TRADEPRESS_BETA', get_option( 'TradePress_beta_testing', 0 ) ); }    
    }
    
    /**
     * Include required core files.
     * 
     * @version 3.1
     */
    public function includes() {
                                         
        do_action( 'before_TradePress_includes' );
               
        require_once( plugin_basename( 'functions.php' ) ); 

        // Debugging and logging... 
        require_once( plugin_basename( 'includes/api-logging.php' ) );
        require_once( plugin_basename( 'includes/developer-notices.php' ) );
        require_once( plugin_basename( 'includes/logging-helper.php' ) );
        // Logging system removed - using external logs

        // Classes using TradePress_Object_Registry() to init for global access...
        require_once( plugin_basename( 'api/set-app.php' ) );

        // Symbol classes for data management
        require_once( plugin_basename( 'classes/symbol.php' ) );
        require_once( plugin_basename( 'classes/symbols.php' ) );

        // API Base Classes
        require_once( plugin_basename( 'api/curl.php' ) );
        require_once( plugin_basename( 'api/financial-api-service.php' ) );
        require_once( plugin_basename( 'api/base-api.php' ) );
        require_once( plugin_basename( 'api/api-directory.php' ) );
        require_once( plugin_basename( 'api/api-factory.php' ) );
        require_once( plugin_basename( 'api/functions.tradepress-api-statuses.php' ) );
        
        // IBKR API
        require_once( plugin_basename( 'api/ibkr/ibkr-endpoints.php' ) );
        require_once( plugin_basename( 'api/ibkr/ibkr-api.php' ) );
        
        // IEX Cloud API
        require_once( plugin_basename( 'api/iexcloud/iexcloud-endpoints.php' ) );
        require_once( plugin_basename( 'api/iexcloud/iexcloud-api.php' ) );
        
        // Intrinio API
        require_once( plugin_basename( 'api/intrinio/intrinio-endpoints.php' ) );
        require_once( plugin_basename( 'api/intrinio/intrinio-api.php' ) );
        
        // MarketStack API
        require_once( plugin_basename( 'api/marketstack/marketstack-endpoints.php' ) );
        require_once( plugin_basename( 'api/marketstack/marketstack-api.php' ) );
        
        // Discord API
        require_once( plugin_basename( 'api/discord/discord-endpoints.php' ) );
        require_once( plugin_basename( 'api/discord/discord-api.php' ) );
        
        // Alpha Vantage API
        require_once( plugin_basename( 'api/alphavantage/alphavantage-endpoints.php' ) );
        require_once( plugin_basename( 'api/alphavantage/alphavantage-api.php' ) );
        
        // Alpaca API
        require_once( plugin_basename( 'api/alpaca/alpaca-endpoints.php' ) );
        require_once( plugin_basename( 'api/alpaca/alpaca-api.php' ) );
        
        // AllTick API
        require_once( plugin_basename( 'api/alltick/alltick-endpoints.php' ) );
        require_once( plugin_basename( 'api/alltick/alltick-api.php' ) );
        
        // EODHD API
        require_once( plugin_basename( 'api/eodhd/eodhd-endpoints.php' ) );
        require_once( plugin_basename( 'api/eodhd/eodhd-api.php' ) );
        
        // eToro API
        require_once( plugin_basename( 'api/etoro/etoro-endpoints.php' ) );
        require_once( plugin_basename( 'api/etoro/etoro-api.php' ) );
        
        // Fidelity API
        require_once( plugin_basename( 'api/fidelity/fidelity-endpoints.php' ) );
        require_once( plugin_basename( 'api/fidelity/fidelity-api.php' ) );
        
        // FinnHub API
        require_once( plugin_basename( 'api/finnhub/finnhub-endpoints.php' ) );
        require_once( plugin_basename( 'api/finnhub/finnhub-api.php' ) );
        
        // FMP (Financial Modeling Prep) API
        require_once( plugin_basename( 'api/fmp/fmp-endpoints.php' ) );
        require_once( plugin_basename( 'api/fmp/fmp-api.php' ) );
        
        // Gemini API
        require_once( plugin_basename( 'api/geminiapi/geminiapi-endpoints.php' ) );
        require_once( plugin_basename( 'api/geminiapi/geminiapi-api.php' ) );
        
        // Polygon API
        require_once( plugin_basename( 'api/polygon/polygon-endpoints.php' ) );
        require_once( plugin_basename( 'api/polygon/polygon-api.php' ) );
        
        // Tradier API
        require_once( plugin_basename( 'api/tradier/tradier-endpoints.php' ) );
        require_once( plugin_basename( 'api/tradier/tradier-api.php' ) );
        
        // Trading212 API
        require_once( plugin_basename( 'api/trading212/trading212-endpoints.php' ) );
        require_once( plugin_basename( 'api/trading212/trading212-api.php' ) );
        
        // TradingAPI
        require_once( plugin_basename( 'api/tradingapi/tradingapi-endpoints.php' ) );
        require_once( plugin_basename( 'api/tradingapi/tradingapi-api.php' ) );
        
        // TradingView API
        require_once( plugin_basename( 'api/tradingview/tradingview-endpoints.php' ) );
        require_once( plugin_basename( 'api/tradingview/tradingview-api.php' ) );
        
        // TwelveData API
        require_once( plugin_basename( 'api/twelvedata/twelvedata-endpoints.php' ) );
        require_once( plugin_basename( 'api/twelvedata/twelvedata-api.php' ) );
        
        // WeBull API
        require_once( plugin_basename( 'api/webull/webull-endpoints.php' ) );
        require_once( plugin_basename( 'api/webull/webull-api.php' ) );
        require_once( plugin_basename( 'api/webull/webull-admin.php' ) );
        require_once( plugin_basename( 'api/webull/webull-ajax.php' ) );

        // More classes...       
        require_once( 'admin/notices/admin-notices.php' );        
        require_once( 'includes/class.async-request.php' );
        require_once( 'includes/class.background-process.php' );
        require_once( 'includes/data-import-process.php' );
        require_once( 'includes/scoring-system/scoring-process.php' );
        require_once( 'includes/class-scanner.php' );                           
        require_once( 'includes/ajax.php' );
        require_once( 'includes/extend-wp-http-curl.php' );                                                                           
        require_once( 'toolbars/toolbars.php' );        
        require_once( 'includes/listener.php' ); 
        require_once( 'shortcodes/shortcodes-main.php' );
        require_once( plugin_basename( 'requests.php' ) );
        require_once( 'theme/public-preset-notices.php' ); 
        require_once( 'admin/notices/custom-login-notices.php' );
        require_once( 'includes/scoring-system/scoring-algorithm.php' );
        require_once( 'includes/trading-system/trading-algo.php' );
        
        // Include template handlers
        require_once( 'posts/templates/page-template-splitscreen.php' );
        
        // Use our simplified approach for symbol templates
        require_once( 'posts/templates/simple-symbol-template-register.php' );
        
        // Shortcodes
        require_once( 'shortcodes/shortcode-trading212.php' );
        # TODO require_once( 'includes/shortcodes/shortcode-trading212-portfolio.php' );
        # TODO require_once( 'includes/shortcodes/shortcode-trading212-watchlist.php' );

        // Include test data functions
        require_once plugin_dir_path( __FILE__ ) . 'functions/functions.tradepress-test-data.php';
        
        // Include scoring directive classes
        require_once( plugin_basename( 'includes/scoring-system/scoring-directive-base.php' ) );

        // Include AJAX handlers
        require_once( plugin_basename( 'includes/ajax-handlers.php' ) );
        
        // Administration-only files...     
        require_once( 'admin/admin-initialisation.php' );
        
        // Admin notices
        require_once( plugin_basename( 'admin/notices/api-notices.php' ) );

        // Frontend only files...
        require_once( plugin_basename( 'includes/frontend-scripts.php' ) );  

        // Load Core Objects
        $this->load_core_objects();
        
        // Load Public Objects
        $this->load_public_objects();   

        do_action( 'after_TradePress_includes' );              
    }
    
    /**
    * Load class objects required by this core plugin for any request (front or abck) 
    * or at all times by extensions. 
    * 
    * @version 2.0
    */
    private function load_core_objects() {
        // Create objects core objects...   
        if( class_exists( 'BugNet' ) ){ $this->bugnet = new BugNet(); }            
        $this->public_notices = new TradePress_Public_PreSet_Notices();
        $this->admin_notices  = new TradePress_Admin_Notices();  

        // Initialize Scoring Directives Registry
        if( class_exists( 'TradePress_Scoring_Directives_Registry' ) ){
            TradePress_Scoring_Directives_Registry::instance();
        }

        # Hint: Go to function init_system() after adding new line here...        
    }
    
    /**
    * Load objects only required for a front-end request.  
    * 
    * @version 1.0
    */
    private function load_public_objects() {
  
    }

    /**
     * Hook into actions and filters. 
     * 
     * Extensions hook into the init() before and after TradePress full init.
     * 
     * @version 1.0
     */
    private function init_hooks() {        
        add_action( 'init', 'TradePress_register_tables', 0 );       
        add_action( 'admin_init', array( $this, 'load_admin_dependencies' ) );
        add_action( 'admin_init', 'TradePress_offer_wizard' );
        add_action( 'init', array( $this, 'init_system' ), 0 );
        add_action( 'init', array( $this, 'output_errors' ), 2 );
        add_action( 'init', array( $this, 'output_actions' ), 2 );            
        add_action( 'init', array( $this, 'output_filters' ), 2 );      
        add_filter( 'views_edit-plugins', array( $this, 'views_edit_plugins' ), 1 );    
        add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_public_css' ), 10 );   
        add_action( 'plugins_loaded', array( $this, 'init_third_party_integration' ), 1 );// Load the 3rd party integration files...             
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'init', array( $this, 'register_ajax_handlers' ) );
        add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
    }
    
    /**
     * Register AJAX handlers
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_tradepress_update_symbol', array($this, 'ajax_update_symbol'));
        add_action('wp_ajax_tradepress_get_api_calls', array($this, 'ajax_get_api_calls'));
        add_action('wp_ajax_tradepress_get_directive_outcome', array($this, 'ajax_get_directive_outcome'));
    }

    /**
     * AJAX handler for updating symbols
     */
    public function ajax_update_symbol() {
        // Check nonce
        check_ajax_referer('tradepress_update_symbol', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
            return;
        }
        
        // Get symbol
        $symbol_id = isset($_POST['symbol_id']) ? intval($_POST['symbol_id']) : 0;
        $ticker = isset($_POST['ticker']) ? sanitize_text_field($_POST['ticker']) : '';
        
        if (empty($symbol_id) && empty($ticker)) {
            wp_send_json_error(array('message' => __('No symbol specified.', 'tradepress')));
            return;
        }
        
        // Load symbol object
        if (!empty($ticker)) {
            $symbol_object = TradePress_Symbols::get_symbol($ticker);
        } else {
            $symbol_object = TradePress_Symbols::get_symbol($symbol_id, 'post_id');
        }
        
        if (!$symbol_object) {
            wp_send_json_error(array('message' => __('Symbol not found.', 'tradepress')));
            return;
        }
        
        // Update from API
        $result = $symbol_object->update_from_api();
        
        if ($result) {
            wp_send_json_success(array('message' => __('Symbol updated successfully.', 'tradepress')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update symbol.', 'tradepress')));
        }
    }
    
    /**
     * AJAX handler for getting API calls
     */
    public function ajax_get_api_calls() {
        if (!wp_verify_nonce($_POST['nonce'], 'tradepress_api_calls') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        $directive = sanitize_text_field($_POST['directive']);
        
        // Load Call Register
        if (!class_exists('TradePress_Call_Register')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/query-register.php';
        }
        
        // Get recent calls from transients
        $calls = array();
        
        // Check current and previous hour transients
        $current_key = 'tradepress_call_register_' . date('YmdH');
        $previous_key = 'tradepress_call_register_' . date('YmdH', strtotime('-1 hour'));
        
        $current_register = get_transient($current_key);
        $previous_register = get_transient($previous_key);
        
        // Combine and filter calls related to this directive
        $all_calls = array();
        if ($current_register) $all_calls = array_merge($all_calls, $current_register);
        if ($previous_register) $all_calls = array_merge($all_calls, $previous_register);
        
        // Get stored serial numbers for this directive
        $directive_config = get_option('tradepress_directive_' . $directive, array());
        $stored_serials = isset($directive_config['recent_api_serials']) ? $directive_config['recent_api_serials'] : array();
        
        foreach ($stored_serials as $serial_info) {
            if (isset($all_calls[$serial_info['serial']])) {
                $call_data = $all_calls[$serial_info['serial']];
                $calls[] = array(
                    'platform' => $serial_info['platform'],
                    'method' => $serial_info['method'],
                    'parameters' => $serial_info['parameters'],
                    'timestamp' => date('Y-m-d H:i:s', $call_data['timestamp']),
                    'age_minutes' => round((time() - $call_data['timestamp']) / 60, 1),
                    'serial' => $serial_info['serial'],
                    'response' => $call_data['result']
                );
            }
        }
        
        wp_send_json_success($calls);
    }
    
    /**
     * AJAX handler for getting directive outcome
     */
    public function ajax_get_directive_outcome() {
        if (!wp_verify_nonce($_POST['nonce'], 'tradepress_directive_outcome') || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed');
        }
        
        $directive = sanitize_text_field($_POST['directive']);
        $symbol = sanitize_text_field($_POST['symbol'] ?? 'AAPL');
        $trading_mode = sanitize_text_field($_POST['trading_mode'] ?? 'long');
        
        // Load directive configuration
        $config = get_option('tradepress_directive_' . $directive, array());
        $config['trading_mode'] = $trading_mode;
        
        // Load directive file
        $directive_file = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives/' . $directive . '.php';
        if (file_exists($directive_file)) {
            require_once $directive_file;
        }
        
        // Load directive class
        $directive_class = 'TradePress_Scoring_Directive_' . strtoupper($directive);
        if (!class_exists($directive_class)) {
            wp_send_json_error('Directive class not found: ' . $directive_class);
        }
        
        $directive_instance = new $directive_class();
        
        // Get explanation
        $explanation = method_exists($directive_instance, 'get_explanation') 
            ? $directive_instance->get_explanation($config)
            : 'No explanation available for this directive.';
        
        // Calculate score with sample data (you can enhance this with real API data)
        $sample_data = array(
            'technical' => array(
                'rsi' => 25 // Sample RSI value for demonstration
            )
        );
        
        $outcome = $directive_instance->calculate_score($sample_data, $config);
        
        wp_send_json_success(array(
            'explanation' => $explanation,
            'outcome' => $outcome,
            'config' => $config,
            'symbol' => $symbol,
            'trading_mode' => $trading_mode
        ));
    }
    
    /**
     * Add custom cron schedules
     */
    public function add_cron_schedules($schedules) {
        $schedules['wp_data_import_cron_interval'] = array(
            'interval' => 300, // 5 minutes
            'display' => __('Every 5 Minutes (Data Import)', 'tradepress')
        );
        
        $schedules['wp_scoring_algorithm_cron_interval'] = array(
            'interval' => 300, // 5 minutes
            'display' => __('Every 5 Minutes (Scoring Algorithm)', 'tradepress')
        );
        
        return $schedules;
    }
    
    public function init_third_party_integration() {
        if( function_exists( 'is_plugin_active' ) && is_plugin_active( 'ultimate-member/ultimate-member.php' ) ){       
            //require_once( 'includes/integration/ultimate-member.php' ); 
            //$this->UM = new TradePress_Ultimate_Member(); 
            //$this->UM->init();
        }   
    }
    
    public function enqueue_public_css() {
        wp_register_style( 'TradePress_shortcode_styles', self::plugin_url() . '/assets/css/TradePress-shortcodes.css' );
        wp_enqueue_style( 'TradePress_shortcode_styles' );
    }
    
    /**
     * Enqueue admin scripts and styles
     * 
     * @since 1.0.0
     */
    public function enqueue_admin_scripts() {
        // Common scripts for all TradePress admin pages
        if (is_admin() && (isset($_GET['page']) && strpos($_GET['page'], 'tradepress') !== false)) {
            // Register and enqueue the common admin script
            wp_enqueue_script(
                'tradepress-admin-common',
                self::plugin_url() . '/assets/js/admin-common.js',
                array('jquery'),
                TRADEPRESS_VERSION,
                true
            );
            
            // Add debug information
            wp_localize_script('tradepress-admin-common', 'tradePressAdmin', array(
                'debug' => WP_DEBUG,
                'screen' => get_current_screen()->id,
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_admin_nonce')
            ));
        }
    }
    
    /**
    * Include required admin files, including the admin menu and settings page, and view/views
    * 
    * @version 2.0
    */
    public function load_admin_dependencies() { 
        require_once( 'admin/installation/deactivate.php' );
        
        // Load the new asset queue system
        if (is_admin()) {
            require_once( 'assets/queue-assets.php' );
        }
    }

    public function views_edit_plugins( $views ) {       
        $screen = get_current_screen();
    }       
    
    public function init_system() {
                   
        // Before init action...
        do_action( 'before_TradePress_init' );    
         
        $this->define_wp_reliant_constants();
                                                       
        // Core classes that require initializing...                                                
        $this->admin_notices->init();
        
        if( isset( $this->webhooks ) ) { 
            //$this->webhooks->init(); 
        }
        
        // Collect required scopes from extensions and establish system requirements. 
        global $system_scopes_status;
        $system_scopes_status = array();
        
        // Scopes for admin only or main account functionality that is always used. 
        $system_scopes_status['admin']['core']['required'] = array();
         
        // Scopes for admin only or main account features that may not be used.
        $system_scopes_status['admin']['core']['optional'] = array(); 
        
        // Scopes for functionality that is always used. 
        $system_scopes_status['public']['core']['required'] = array();
        
        // Scopes for features that may not be used.
        $system_scopes_status['public']['core']['optional'] = array(); 
        
        $system_scopes_status = apply_filters( 'TradePress_update_system_scopes_status', $system_scopes_status );  

        add_filter( 'plugin_action_links_' . TRADEPRESS_PLUGIN_BASENAME, 'TradePress_plugin_action_links' );
        add_filter( 'plugin_row_meta', 'TradePress_plugin_row_meta', 10, 2 );

        // Load the various systems, includes custom post types within those systems...
        $this->load_systems();
         
        // Init action.
        do_action( 'TradePress_init' );     
    }
    
    /**
    * Load systems, including registration of custom post types...
    * 
    * @version 2.2
    */
    public function load_systems() {    
        
        // Channels - Core
        require_once( 'posts/post-type-symbols.php' );
        TradePress_Post_Type_Symbols::init();
        
        // Initialize symbols from posts
        add_action('admin_init', array($this, 'initialize_symbols'), 20);
        
        // Handle template includes for symbols
        add_filter('template_include', array($this, 'template_loader'));

        // Webhooks System
        if( get_option( 'TradePress_webhooks_switch' ) == 'yes' ) {
            
            // Set the callback URL sent to Twitch.tv when subscribing to a webhook...
            $url = admin_url( 'admin-post.php?webhook=tradepress_eventsub_notification' );
            if( !defined( 'TradePress_WEBHOOK_CALLBACK' ) ) { define( 'TradePress_WEBHOOK_CALLBACK', $url ); }
            unset( $url );
            
            require_once( 'posts/post-type-webhooks.php' );             
            require_once( plugin_basename( 'includes/webhooks/webhooks-cache.php' ) );
            require_once( plugin_basename( 'includes/webhooks/webhooks-event-processing.php' ) );
            
            // Initiate the custom post-type...            
            TradePress_Post_Type_Webhooks::init();
                     
            // Hook into init for single site, priority 0 = highest priority...
            add_action( 'init', 'TradePress_integrate_wpdb_webhooksmeta', 0);

            // Hook in to switch blog to support multisite...
            add_action( 'switch_blog', 'TradePress_integrate_wpdb_webhooksmeta', 0 );

            // Listen for Twitch.tv EventSub webhook notifications and store them in cache for processing later...
            add_action( 'admin_post_nopriv', 'TradePress_webhooks_eventsub_listener', 1, 0 );
            
            // Background processing of webhook notifications that have been stored in cache...
            add_action( 'init', array( new TradePress_Webhooks_Event_Processing(), 'process_handler' ) );            
        }             
    }
    
    /**
     * Initialize symbols from posts
     */
    public function initialize_symbols() {
        // Only initialize if we're in the admin area and TradePress_Symbols class exists
        if (is_admin() && class_exists('TradePress_Symbols')) {
            TradePress_Symbols::initialize_from_posts();
        }
    }
    
    /**
     * Handle template includes
     *
     * @param string $template Template file
     * @return string Modified template file
     */
    public function template_loader($template) {
        if (is_singular('symbols')) {
            $default_file = TRADEPRESS_PLUGIN_DIR . 'posts/templates/symbol-template.php';
            
            // Check if theme has overridden the template
            $theme_file = locate_template(array('symbol-template.php'));
            
            if (!empty($theme_file)) {
                return $theme_file;
            }
            
            if (file_exists($default_file)) {
                return $default_file;
            }
        }
        
        return $template;
    }
          
    /**
    * Output errors with a plain dump.
    * 
    * Pre-BugNet measure. 
    *     
    * @version 1.0
    */
    public function output_errors() {          
        // Display Errors Tool            
        if( !TradePress_are_errors_allowed() ) { return false; }
                             
        ini_set( 'display_errors', 1 );   
        error_reporting(E_ALL);                          
        
        add_action( 'shutdown', array( $this, 'show_errors' ), 1 );
        add_action( 'shutdown', array( $this, 'print_errors' ), 1 );                    
    }
    
    public function output_actions() {
        if( 'yes' !== get_option( 'TradePress_display_actions') ) { return; }
                                                                       
        add_action( 'shutdown', array( $this, 'show_actions' ), 1 );                                                               
    }
    
    public function output_filters() {    
        if( 'yes' !== get_option( 'TradePress_display_filters') ) { return; }
                                                                       
        add_action( 'shutdown', array( $this, 'show_filters' ), 1 );                                                               
    }    
    
    public static function show_errors() {      
        global $wpdb;
        echo '<div id="bugnet-wperror-dump">';       
            _e( '<h1>BugNet: Possible Errors</h1>', 'tradepress' );
            $wpdb->show_errors( true );
        echo '</div>';   
    }   
    
    public static function print_errors() {     
        global $wpdb;       
        $wpdb->print_error();        
    }    
    
    public function show_actions() {
        global $wp_actions;
        echo '<div id="bugnet-wpactions-dump">';
        _e( '<h1>BugNet: WordPress Actions</h1>', 'tradepress' );
        echo '<pre>';       
        print_r( $wp_actions );
        echo '</pre>';
        echo '</div>';          
    }
 
    public function show_filters() {
        global $wp_filter;
        echo '<div id="bugnet-wpfilters-dump">';
        _e( '<h1>BugNet: WordPress Filters</h1>', 'tradepress' );
        echo '<pre>';
        //print_r( $wp_filter['admin_bar_menu'] );
        print_r( $wp_filter ); 
        echo '</pre>';
        echo '</div>';   
    }    
        
    /**
     * Get the plugin url.   
     * @return string    
     */
    public function plugin_url() {                
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }
    
    /**
     * Get the plugin path.
     * @return string    
     */
    public function plugin_path() {              
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
    
    /**
     * Get Ajax URL (this is the URL to WordPress core ajax file).
     * @return string   
     */     
    public function ajax_url() {                
        return admin_url( 'admin-ajax.php', 'relative' );
    }                    
}  

if( !function_exists( 'tradepress' ) ) {
    /**
     * Main instance of TradePress.
     *
     * Returns the main instance of TradePress to prevent the need to use globals.
     *
     * @since  1.0
     * @return TradePress     
     */
    function TradePress() {        
        return WordPressTradePress::instance();
    }   
}

$GLOBALS['TradePress'] = TradePress();     // Global for backwards compatibility.