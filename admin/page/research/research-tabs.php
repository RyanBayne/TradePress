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
	 * @param    string $plugin_name    The name of this plugin.
	 * @param    string $version        The version of this plugin.
	 * @version 1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->active_tab  = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'economic-calendar';
	}

	/**
	 * Static method to output the research area - moved from TradePress_Admin_Research_Area
	 *
	 * @since    1.1.0
	 * @version 1.0.0
	 */
	public static function output() {
		// Instantiate the Research class
		global $tradepress_research;
		if ( ! isset( $tradepress_research ) ) {
			$tradepress_research = new TradePress_Research( 'tradepress', TRADEPRESS_VERSION );
			$tradepress_research->init(); // Initialize hooks for screen options
		}

		// Get tabs to display tab name in title
		$tabs = $tradepress_research->get_tabs();

		// Get current active tab
		$default_tab = array_key_first( $tabs );
		$current_tab = empty( $_GET['tab'] ) ? $default_tab : sanitize_title( wp_unslash( $_GET['tab'] ) );
		if ( ! isset( $tabs[ $current_tab ] ) ) {
			$current_tab = $default_tab;
		}

		// Output the UI
		?>
		<div class="wrap tradepress-admin">
			<h1>
				<?php
				echo esc_html__( 'TradePress Research', 'tradepress' );
				if ( ! empty( $tabs[ $current_tab ]['title'] ) ) {
					echo ' &gt; ';
					echo esc_html( $tabs[ $current_tab ]['title'] );
				}
				?>
			</h1>
			
			<nav class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab_id => $tab_data ) {
					$active = $current_tab === $tab_id ? ' nav-tab-active' : '';
					echo '<a href="' . esc_url( admin_url( 'admin.php?page=tradepress_research&tab=' . $tab_id ) ) . '" class="nav-tab' . esc_attr( $active ) . '">' . esc_html( $tab_data['title'] ) . '</a>';
				}
				?>
			</nav>
			
			<div class="tradepress-research-content">
				<?php $tradepress_research->load_research_tab_content( $current_tab ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Set up screen options - implemented from reference in the original TradePress_Admin_Research_Area class
	 *
	 * @since    1.1.0
	 * @version 1.0.0
	 */
	public static function setup_screen() {
		$screen = get_current_screen();

		if ( ! is_object( $screen ) ) {
			return;
		}

		// Add filter to modify the admin page title.
		add_filter( 'admin_title', array( __CLASS__, 'filter_admin_title' ), 10, 2 );
		// Add screen options for the research page
		if ( strpos( $screen->id, 'tradepress_research' ) !== false ) {
			add_screen_option(
				'layout_columns',
				array(
					'max'     => 2,
					'default' => 1,
				)
			);

			// Add help tabs if needed
			$screen->add_help_tab(
				array(
					'id'      => 'tradepress_research_overview',
					'title'   => __( 'Overview', 'tradepress' ),
					'content' => '<p>' . __( 'This page provides research tools for market analysis and stock evaluation.', 'tradepress' ) . '</p>',
				)
			);
		}
	}

	/**
	 * Filters the admin page title to make it dynamic based on the current tab.
	 *
	 * @since 1.1.0
	 * @param string $admin_title The current admin title.
	 * @param string $title       The original page title.
	 * @return string The modified admin title.
	 * @version 1.0.0
	 */
	public static function filter_admin_title( $admin_title, $title ) {
		// This is a static method, so we need to create a new instance to access get_tabs().
		// In a future refactor, making get_tabs() static would be more efficient.
		$instance = new self( 'tradepress', defined( 'TRADEPRESS_VERSION' ) ? TRADEPRESS_VERSION : '1.1.0' );
		$tabs     = $instance->get_tabs();

		$default_tab = array_key_first( $tabs );
		$current_tab = empty( $_GET['tab'] ) ? $default_tab : sanitize_key( $_GET['tab'] );

		if ( isset( $tabs[ $current_tab ]['title'] ) ) {
			$tab_title  = $tabs[ $current_tab ]['title'];
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
	 * @version 1.0.0
	 */
	public function init() {
		// Add screen options - try different hook names that might be used in your setup
		add_action( 'load-tradepress_page_tradepress-research', array( $this, 'setup_price_forecast_screen_options' ) );
		add_action( 'load-tradepress_page_tradepress_research', array( $this, 'setup_price_forecast_screen_options' ) );
		add_action( 'current_screen', array( $this, 'check_current_screen' ) );

		// Set screen options
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );
	}

	/**
	 * Check the current screen and set up screen options if we're on our page
	 * This is a fallback in case the direct hook methods don't work
	 *
	 * @version 1.0.0
	 */
	public function check_current_screen() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && strpos( $screen->id, 'tradepress' ) !== false && strpos( $screen->id, 'research' ) !== false ) {
			$this->setup_price_forecast_screen_options();
		}
	}

	/**
	 * Display the research page.
	 *
	 * @since    1.0.0
	 * @version 1.0.0
	 */
	public function display_page() {
		// Enqueue styles and scripts if needed
		wp_enqueue_style( $this->plugin_name . '-research', plugin_dir_url( __FILE__ ) . 'css/tradepress-research.css', array(), $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name . '-research', plugin_dir_url( __FILE__ ) . 'js/tradepress-research.js', array( 'jquery' ), $this->version, false );

		// Display the page
		include_once 'partials/research-display.php';
	}

	/**
	 * Get the tab list for the research page.
	 *
	 * @since    1.0.0
	 * @return   array    The array of tabs.
	 * @version 1.0.0
	 */
	public function get_tabs() {
		$tabs = array(
			'economic-calendar'    => array(
				'title'    => __( 'Economic Calendar', 'tradepress' ),
				'callback' => array( $this, 'load_economic_calendar_tab' ),
			),
		);

		if ( function_exists( 'tradepress_can_access_development_views' ) && tradepress_can_access_development_views() ) {
			$dev_tabs = array(
				'overview'            => array(
					'title'    => __( 'Overview', 'tradepress' ),
					'callback' => array( $this, 'load_overview_tab' ),
				),
				'sector-rotation'     => array(
					'title'    => __( 'Sector Rotation', 'tradepress' ),
					'callback' => array( $this, 'load_sector_rotation_tab' ),
				),
				'market-correlations' => array(
					'title'    => __( 'Market Correlations', 'tradepress' ),
					'callback' => array( $this, 'load_market_correlations_tab' ),
				),
				'technical-indicators' => array(
					'title'    => __( 'Technical Indicators', 'tradepress' ),
					'callback' => array( $this, 'load_technical_indicators_tab' ),
				),
				'news_feed'           => array(
					'title'    => __( 'News Feed', 'tradepress' ),
					'callback' => array( $this, 'load_news_feed_tab' ),
				),
				'earnings'            => array(
					'title'    => __( 'Earnings Calendar', 'tradepress' ),
					'callback' => array( $this, 'load_earnings_tab' ),
				),
				'social_networks'     => array(
					'title'    => __( 'Social Networks', 'tradepress' ),
					'callback' => array( $this, 'load_social_networks_tab' ),
				),
				'price_forecast'      => array(
					'title'    => __( 'Price Forecast', 'tradepress' ),
					'callback' => array( $this, 'render_price_forecast_tab' ),
				),
			);

			$tabs = $dev_tabs + $tabs;
		}

		return apply_filters( 'tradepress_research_tabs', $tabs );
	}

	/**
	 * Load research tab content
	 *
	 * @since 1.0.0
	 * @param string $tab The current tab
	 * @return void
	 * @version 1.0.0
	 */
	public function load_research_tab_content( $tab ) {
		$tabs = $this->get_tabs();

		if ( isset( $tabs[ $tab ] ) && isset( $tabs[ $tab ]['callback'] ) && is_callable( $tabs[ $tab ]['callback'] ) ) {
			call_user_func( $tabs[ $tab ]['callback'] );
		} else {
			// Default to the first available tab if the requested tab is hidden or invalid.
			$default_tab = array_key_first( $tabs );
			if ( $default_tab && isset( $tabs[ $default_tab ]['callback'] ) && is_callable( $tabs[ $default_tab ]['callback'] ) ) {
				call_user_func( $tabs[ $default_tab ]['callback'] );
			}
		}
	}

	/**
	 * Load the Overview tab content
	 *
	 * @version 1.0.0
	 */
	public function load_overview_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/overview.php';
	}

	/**
	 * Load the Sector Rotation tab content
	 *
	 * @version 1.0.0
	 */
	public function load_sector_rotation_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/sector-rotation.php';
		tradepress_sector_rotation_tab_content();
	}

	/**
	 * Load the Economic Calendar tab content
	 *
	 * @version 1.0.0
	 */
	public function load_economic_calendar_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/economic-calendar.php';
		tradepress_economic_calendar_tab_content();
	}

	/**
	 * Load the Technical Indicators tab content
	 *
	 * @version 1.0.0
	 */
	public function load_technical_indicators_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/technical-indicators.php';
		tradepress_technical_indicators_tab_content();
	}

	/**
	 * Load the Market Correlations tab content
	 *
	 * @version 1.0.0
	 */
	public function load_market_correlations_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/market-correlations.php';
		tradepress_market_correlations_tab_content();
	}

	/**
	 * Load the Earnings tab content
	 *
	 * @version 1.0.0
	 */
	public function load_earnings_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/earnings.php';
		tradepress_earnings_tab_content();
	}

	/**
	 * Load the News Feed tab content
	 *
	 * @version 1.0.0
	 */
	public function load_news_feed_tab() {
		include TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/news-feed.php';
		tradepress_news_feed_tab_content();
	}

	/**
	 * Load the Social Networks tab content
	 *
	 * @since  1.0.0
	 * @version 1.0.95
	 */
	public function load_social_networks_tab() {
		// Enqueue Social Networks CSS
		wp_enqueue_style(
			'tradepress-social-networks',
			TRADEPRESS_PLUGIN_URL . 'assets/css/pages/research-social-networks.css',
			array(),
			TRADEPRESS_VERSION
		);

		require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/research/view/social-networks/social-networks-tabs.php';

		// Create sub-navigation for Social Networks
		$current_subtab = isset( $_GET['subtab'] ) ? sanitize_title( $_GET['subtab'] ) : 'settings';

		?>
		<div class="social-networks-container">
			<h3><?php esc_html_e( 'Social Networks Management', 'tradepress' ); // Escaped output per WordPress coding standards ?></h3>
			<p><?php esc_html_e( 'Configure social media platform integrations for research and signal monitoring.', 'tradepress' ); // Escaped output per WordPress coding standards ?></p>
			
			<div class="subsubsub">
				<ul>
					<li><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'settings' ), remove_query_arg( 'subtab' ) ) ); ?>" class="<?php echo $current_subtab === 'settings' ? 'current' : ''; ?>"><?php esc_html_e( 'Settings', 'tradepress' ); // Escaped output per WordPress coding standards ?></a> |</li>
					<li><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'switches' ), remove_query_arg( 'subtab' ) ) ); ?>" class="<?php echo $current_subtab === 'switches' ? 'current' : ''; ?>"><?php esc_html_e( 'Platform Switches', 'tradepress' ); // Escaped output per WordPress coding standards ?></a></li>
				</ul>
			</div>
			
			<div class="social-networks-content">
				<?php
				switch ( $current_subtab ) {
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
	 * @version 1.0.0
	 */
	public function render_price_forecast_tab() {
		// First, ensure the class file is loaded
		$forecast_table_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/research/view/price-forecast-table.php';

		if ( file_exists( $forecast_table_file ) ) {
			require_once $forecast_table_file;
		} else {
			echo '<div class="notice notice-error"><p>Price forecast table class file not found at: ' . esc_html( $forecast_table_file ) . '</p></div>';
		}

		// Now include the view file
		require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/research/partials/research-price-forecast.php';
	}

	public function get_price_forecast_data() {
		return apply_filters( 'tradepress_price_forecast_data', array() );
	}

	/**
	 * Display detailed forecast information for a specific symbol
	 *
	 * @since  1.0.0
	 * @version 1.0.95
	 * @param string $symbol The symbol to display details for
	 * @return void
	 */
	public function display_symbol_details( $symbol ) {
		?>
		<div class="symbol-forecast-details">
			<div class="symbol-header">
				<?php /* translators: %s: string value */ ?>
				<h3><?php echo esc_html( sprintf( __( 'Price Forecast for %s', 'tradepress' ), $symbol ) ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress-research&tab=price_forecast' ) ); ?>" class="button"><?php esc_html_e( 'Back to All Forecasts', 'tradepress' ); // Escaped output per WordPress coding standards ?></a>
			</div>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'No imported forecast details are available for this symbol.', 'tradepress' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate CSS background color style based on confidence level
	 *
	 * @param float $confidence 0-100 confidence score
	 * @return string
	 * @version 1.0.0
	 */
	private function get_confidence_color_style( $confidence ) {
		// Normalize confidence to 0-1 range
		$normalized = max( 0, min( 100, $confidence ) ) / 100;

		// Calculate color components (red to yellow to green)
		if ( $normalized < 0.5 ) {
			// Red to yellow
			$r = 255;
			$g = round( 255 * ( $normalized * 2 ) );
			$b = 0;
		} else {
			// Yellow to green
			$r = round( 255 * ( 1 - ( $normalized - 0.5 ) * 2 ) );
			$g = 255;
			$b = 0;
		}

		// Return the background-color style with semi-transparent background
		return sprintf(
			'background-color: rgba(%d, %d, %d, 0.3); padding: 3px 8px; border-radius: 4px; display: inline-block;',
			$r,
			$g,
			$b
		);
	}

	/**
	 * Register screen options for the Price Forecast tab
	 *
	 * @since    1.0.0
	 * @version 1.0.0
	 */
	public function setup_price_forecast_screen_options() {
		$screen = get_current_screen();

		// Only add screen options on our page - check for various possible screen IDs
		if ( ! is_object( $screen ) ||
			! ( $screen->id === 'tradepress_page_tradepress-research' ||
				$screen->id === 'tradepress_page_tradepress_research' ||
				strpos( $screen->id, 'tradepress' ) !== false && strpos( $screen->id, 'research' ) !== false ) ) {
			return;
		}

		// Add per page screen option
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Forecasts per page', 'tradepress' ),
				'default' => 10,
				'option'  => 'tradepress_price_forecasts_per_page',
			)
		);
	}

	/**
	 * Save screen option value
	 *
	 * @since    1.0.0
	 * @param    mixed  $status  Status of the option
	 * @param    string $option  Option name
	 * @param    mixed  $value   Option value
	 * @return   mixed              Filtered value
	 * @version 1.0.0
	 */
	public function set_screen_option( $status, $option, $value ) {
		if ( 'tradepress_price_forecasts_per_page' === $option ) {
			return $value;
		}
		return $status;
	}
}
