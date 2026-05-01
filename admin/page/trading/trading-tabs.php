<?php
/**
 * TradePress - Admin Trading Area
 *
 * Handles the Trading tabs including Portfolio, Trade History, Manual Trading, SEES, and Calculators.
 * The Trading Strategy Creator tool allows users to define conditions for automated trading actions.
 *
 * Trading Strategy System:
 * - Uses directives to set specific trading requirements and conditions
 * - Implements a rule-based approach with strict conditions (not primarily score-based)
 * - Can utilize scoring data from the Scoring Directives system as one optional factor
 * - Supports conditional pairs (OR logic) where either condition can trigger an action
 * - May result in no trade action if strict conditions are not met (unlike scoring which always produces results)
 *
 * Database Dependencies:
 * - tradepress_trading_strategies: Stores strategy configurations and metadata
 * - tradepress_trading_rules: Stores individual rule definitions for strategies
 * - tradepress_trading_actions: Records actions taken by strategies
 * - tradepress_trading_executions: Logs execution history of strategies
 * - tradepress_directives: May reference directives when used as conditions
 *
 * Related Development Tasks:
 * - Implement conditional pairs functionality (see DEVELOPMENT-NOTES.md "Trading Strategy Pairs")
 * - Add strategy execution engine (see DEVELOPMENT-ROADMAP.md "Trading Automation")
 * - Create strategy performance tracking (see DEVELOPMENT-ROADMAP.md "Strategy Analytics")
 * - Build paper trading simulation for strategy testing
 *
 * @author   TradePress
 * @category Admin
 * @package  TradePress/Admin
 * @since    1.0.0
 * @version  1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TradePress_Admin_Trading_Page' ) ) :

	/**
	 * TradePress_Admin_Trading_Page Class.
	 */
	class TradePress_Admin_Trading_Page {

		/**
		 * Current active tab.
		 *
		 * @var string
		 */
		private $active_tab = 'portfolio'; // Default to portfolio

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			if ( isset( $_GET['tab'] ) && array_key_exists( sanitize_key( wp_unslash( $_GET['tab'] ) ), $this->get_tabs() ) ) {
				$this->active_tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
			} else {
				// If the requested tab doesn't exist, default to the first available tab's key.
				$tabs             = $this->get_tabs();
				$this->active_tab = ! empty( $tabs ) ? key( $tabs ) : 'portfolio';
			}
		}

		/**
		 * Get tabs for the trading area.
		 *
		 * @return array
		 * @version 1.0.0
		 */
		public function get_tabs() {

			$tabs = array(
				'trading-strategies' => __( 'Trading Strategies', 'tradepress' ),
				'calculators'        => __( 'Calculators', 'tradepress' ),
				'portfolio'          => __( 'Portfolio', 'tradepress' ),
				'trade-history'      => __( 'Trade History', 'tradepress' ),
				'manual-trade'       => __( 'Manual Trading', 'tradepress' ),
				'sees-ready'         => __( 'SEES Ready', 'tradepress' ),
				'sees-pro'           => __( 'SEES Pro', 'tradepress' ),
			);
			$tabs = apply_filters( 'tradepress_trading_area_tabs', $tabs );
			return tradepress_filter_development_tabs( $tabs, $this->get_development_tab_ids() );
		}

		/**
		 * Get tabs that are visible only in Developer Mode.
		 *
		 * @return array
		 */
		private function get_development_tab_ids() {
			return array( 'trading-strategies', 'portfolio', 'trade-history', 'manual-trade', 'sees-ready', 'sees-pro' );
		}
		/**
		 * Output the trading area interface.
		 *
		 * @version 1.0.0
		 */
		public function output() {
			$tabs = $this->get_tabs();

			echo '<div class="wrap tradepress-admin">';
			echo '<h1>';
			echo esc_html__( 'TradePress Trading', 'tradepress' );
			if ( isset( $tabs[ $this->active_tab ] ) ) {
				echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
				echo tradepress_get_development_tab_label( $this->active_tab, $tabs[ $this->active_tab ], $this->get_development_tab_ids() );
			}
			echo '</h1>';

			echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
			foreach ( $tabs as $tab_id => $tab_name ) {
				$active_class = ( $this->active_tab === $tab_id ) ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=tradepress_trading&tab=' . $tab_id ) ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . tradepress_get_development_tab_label( $tab_id, $tab_name, $this->get_development_tab_ids() ) . '</a>';
			}
			echo '</nav>';

			echo '<div class="tradepress-tab-content">';
			// Action hook to display content for the active tab
			do_action( 'tradepress_trading_area_' . $this->active_tab . '_tab_content' );
			echo '</div>'; // .tradepress-tab-content

			echo '</div>'; // .wrap
		}
	}

endif; // Class exists check

// Action hooks for tab content
// Existing tabs (assuming they will be created or have placeholders)
add_action( 'tradepress_trading_area_portfolio_tab_content', 'tradepress_display_portfolio_tab_content' );
add_action( 'tradepress_trading_area_trade-history_tab_content', 'tradepress_display_trade_history_tab_content' );
add_action( 'tradepress_trading_area_manual-trade_tab_content', 'tradepress_display_manual_trade_tab_content' );

// New SEES tabs
add_action( 'tradepress_trading_area_sees-ready_tab_content', 'tradepress_display_sees_ready_tab_content' );
add_action( 'tradepress_trading_area_sees-pro_tab_content', 'tradepress_display_sees_pro_tab_content' );

// Calculators tab
add_action( 'tradepress_trading_area_calculators_tab_content', 'tradepress_display_calculators_tab_content' );

// Trading Strategies tab (merged)
add_action( 'tradepress_trading_area_trading-strategies_tab_content', 'tradepress_display_trading_strategies_tab_content' );

// Placeholder functions for existing tabs (if not already defined elsewhere)
/**
 * Display portfolio tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_portfolio_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/portfolio.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'Portfolio tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Display trade history tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_trade_history_tab_content() {
	// Example: include 'views/trade-history.php';
	echo '<p>' . esc_html__( 'Trade History tab content to be added.', 'tradepress' ) . '</p>';
}

/**
 * Display manual trade tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_manual_trade_tab_content() {
	// Example: include 'views/manual-trade.php';
	echo '<p>' . esc_html__( 'Manual Trading tab content to be added.', 'tradepress' ) . '</p>';
}

// Functions to display content for the new SEES tabs
/**
 * Display sees ready tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_sees_ready_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/sees-ready.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'SEES Ready tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Display sees pro tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_sees_pro_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/sees-pro.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'SEES Pro tab content view file not found.', 'tradepress' ) . '</p>';
	}
}


// Calculators tab content
/**
 * Display calculators tab content.
 *
 * @version 1.0.0
 */
function tradepress_display_calculators_tab_content() {
	$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/calculators.php';
	if ( file_exists( $view_file ) ) {
		include_once $view_file;
	} else {
		echo '<p>' . esc_html__( 'Calculators tab content view file not found.', 'tradepress' ) . '</p>';
	}
}

/**
 * Trading Strategies tab content with sub-tabs
 *
 * Displays the interface for creating and managing trading strategies with sub-navigation.
 *
 * @version 1.0.0
 */
function tradepress_display_trading_strategies_tab_content() {
	// Get current sub-tab
	$sub_tab = isset( $_GET['sub_tab'] ) ? sanitize_key( wp_unslash( $_GET['sub_tab'] ) ) : 'create';

	// Sub-tab navigation
	$sub_tabs = array(
		'create'  => __( 'Create Trading Strategy', 'tradepress' ),
		'custom'  => __( 'My Custom Strategies', 'tradepress' ),
		'builtin' => __( 'Built-in Strategies', 'tradepress' ),
	);

	echo '<div class="tradepress-sub-tabs">';
	echo '<ul class="subsubsub">';
	$count = 0;
	foreach ( $sub_tabs as $key => $label ) {
		$active_class = ( $sub_tab === $key ) ? ' current' : '';
		$url          = admin_url( 'admin.php?page=tradepress_trading&tab=trading-strategies&sub_tab=' . $key );
		$separator    = ( $count > 0 ) ? ' | ' : '';
		echo '<li><a href="' . esc_url( $url ) . '" class="' . esc_attr( $active_class ) . '">' . esc_html( $label ) . '</a>' . $separator . '</li>';
		++$count;
	}
	echo '</ul>';

	echo '<div class="sub-tab-content">';
	switch ( $sub_tab ) {
		case 'create':
			$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/create_strategy.php';
			if ( file_exists( $view_file ) ) {
				include_once $view_file;
			} else {
				echo '<p>' . esc_html__( 'Create Strategy view file not found.', 'tradepress' ) . '</p>';
			}
			break;
		case 'custom':
			$view_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/trading/view/trading-strategies.php';
			if ( file_exists( $view_file ) ) {
				include_once $view_file;
			} else {
				echo '<p>' . esc_html__( 'Custom Strategies view file not found.', 'tradepress' ) . '</p>';
			}
			break;
		case 'builtin':
			tradepress_display_builtin_strategies();
			break;
		default:
			echo '<p>' . esc_html__( 'Invalid sub-tab.', 'tradepress' ) . '</p>';
	}
	echo '</div>';
	echo '</div>';
}

/**
 * Display built-in trading strategies
 *
 * @version 1.0.0
 */
function tradepress_display_builtin_strategies() {
	$strategies = array(
		array(
			'name'            => 'Conviction Growth Strategy',
			'type'            => 'Custom (TradePress)',
			'trading_style'   => 'Growth/Position Trading',
			'risk_level'      => 'Medium-High',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'SEES Scoring (75+), Earnings Calendar, Resistance Levels, Volume Analysis',
			'principles'      => 'High-conviction entries with aggressive profit-taking near resistance. Strategic earnings timing with confidence to hold through volatility. Target: 60% annual returns.',
			'risk_management' => 'Mental stops, position sizing by conviction level, max 5-8 positions, hold through setbacks if fundamentals intact',
		),
		array(
			'name'            => 'SEES Score-Based Strategy',
			'type'            => 'Custom (TradePress)',
			'trading_style'   => 'Swing/Position Trading',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'SEES Scoring System, Technical Analysis, Volume',
			'principles'      => 'Uses TradePress SEES scoring to identify high-probability trades. Combines fundamental analysis with technical indicators.',
			'risk_management' => 'Position sizing based on score confidence, stop-loss at 8-12%, take profit at 15-25%',
		),
		array(
			'name'            => 'Mean Reversion',
			'type'            => 'Classic',
			'trading_style'   => 'Swing Trading',
			'risk_level'      => 'Medium-High',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'Bollinger Bands, RSI, Moving Averages, Standard Deviation',
			'principles'      => 'Buy when price is below statistical average, sell when above. Works best in ranging markets.',
			'risk_management' => 'Tight stops for outlier events, position sizing based on volatility',
		),
		array(
			'name'            => 'Earnings Whispers',
			'type'            => 'Event-Based',
			'trading_style'   => 'Event Trading',
			'risk_level'      => 'High',
			'monitoring'      => 'Pre-earnings',
			'status'          => 'Planning',
			'indicators'      => 'Whisper vs Consensus, Revenue Growth, Sentiment Analysis',
			'principles'      => 'Trade based on earnings expectations vs whisper numbers. Focus on high-conviction beats.',
			'risk_management' => 'Exit before earnings if momentum shifts, limit position size due to volatility',
		),
		array(
			'name'            => 'Analyst Adjustments (Zacks-like)',
			'type'            => 'Fundamental',
			'trading_style'   => 'Position Trading',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Weekly',
			'status'          => 'Planning',
			'indicators'      => 'Estimate Revisions, Analyst Ratings, Earnings Momentum',
			'principles'      => 'Follow analyst estimate momentum and rating changes. Buy on positive revisions.',
			'risk_management' => 'Diversified positions, stop-loss on negative revision trends',
		),
		array(
			'name'            => 'Resistance Level Breakouts',
			'type'            => 'Technical',
			'trading_style'   => 'Momentum Trading',
			'risk_level'      => 'Medium-High',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'Support/Resistance Levels, Volume, Price Action',
			'principles'      => 'Buy on confirmed breakouts above resistance with volume confirmation.',
			'risk_management' => 'Stop below breakout level, trail stops on momentum moves',
		),
		array(
			'name'            => 'VIX-Based Market Timing',
			'type'            => 'Market Timing',
			'trading_style'   => 'Market Timing',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Daily',
			'status'          => 'Planning',
			'indicators'      => 'VIX Levels, VIX/VXV Ratio, Market Sentiment',
			'principles'      => 'Use VIX extremes to time market entries/exits. High VIX = opportunity, Low VIX = caution.',
			'risk_management' => 'Adjust position sizes based on volatility regime',
		),
		array(
			'name'            => 'S&P 500 Sector Rotation',
			'type'            => 'Sector Strategy',
			'trading_style'   => 'Position Trading',
			'risk_level'      => 'Medium',
			'monitoring'      => 'Weekly',
			'status'          => 'Planning',
			'indicators'      => 'Sector Performance, Economic Indicators, Yield Curve',
			'principles'      => 'Rotate between sectors based on economic cycle and relative performance.',
			'risk_management' => 'Diversification across sectors, rebalancing based on momentum',
		),
		array(
			'name'            => 'Adaptive Risk Monitor',
			'type'            => 'Risk Management',
			'trading_style'   => 'All Styles',
			'risk_level'      => 'Low',
			'monitoring'      => 'Real-time',
			'status'          => 'Planning',
			'indicators'      => 'Portfolio Beta, Correlation, Drawdown Metrics',
			'principles'      => 'Dynamically adjust position sizes and risk based on market conditions and portfolio metrics.',
			'risk_management' => 'Core risk management system - adjusts all other strategies',
		),
	);

	echo '<div class="tradepress-builtin-strategies">';
	echo '<div class="strategies-header">';
	echo '<h3>' . esc_html__( 'Built-in Trading Strategies', 'tradepress' ) . '</h3>';
	echo '<p>' . esc_html__( 'These strategies are planning references only. They are not active trading automation and do not read live provider data from this view.', 'tradepress' ) . '</p>';
	echo '</div>';

	echo '<div class="tradepress-data-status-panel" data-mode="dev-only-demo" data-health="not_applicable">';
	echo '<h3>' . esc_html__( 'Built-in Strategy Status', 'tradepress' ) . '</h3>';
	echo '<table class="widefat fixed striped"><tbody>';
	echo '<tr><th scope="row">' . esc_html__( 'Data mode', 'tradepress' ) . '</th><td>' . esc_html__( 'Dev-only Demo', 'tradepress' ) . '</td></tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Source of truth', 'tradepress' ) . '</th><td>' . esc_html__( 'Static planning definitions in the Trading tab controller', 'tradepress' ) . '</td></tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Provider', 'tradepress' ) . '</th><td>' . esc_html__( 'Not applicable', 'tradepress' ) . '</td></tr>';
	echo '<tr><th scope="row">' . esc_html__( 'Execution state', 'tradepress' ) . '</th><td>' . esc_html__( 'Planning reference only; not executable trading automation', 'tradepress' ) . '</td></tr>';
	echo '</tbody></table>';
	echo '</div>';

	echo '<div class="strategies-table-container">';
	echo '<table class="wp-list-table widefat fixed striped strategies-table">';
	echo '<thead>';
	echo '<tr>';
	echo '<th class="strategy-name">' . esc_html__( 'Strategy Name', 'tradepress' ) . '</th>';
	echo '<th class="strategy-type">' . esc_html__( 'Type', 'tradepress' ) . '</th>';
	echo '<th class="strategy-style">' . esc_html__( 'Trading Style', 'tradepress' ) . '</th>';
	echo '<th class="strategy-risk">' . esc_html__( 'Risk Level', 'tradepress' ) . '</th>';
	echo '<th class="strategy-status">' . esc_html__( 'Status', 'tradepress' ) . '</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	foreach ( $strategies as $strategy ) {
		$status_class = strtolower( str_replace( ' ', '-', $strategy['status'] ) );
		$risk_class   = strtolower( str_replace( '-', '', $strategy['risk_level'] ) );

		echo '<tr class="strategy-row" data-strategy="' . esc_attr( sanitize_title( $strategy['name'] ) ) . '">';
		echo '<td class="strategy-name"><strong>' . esc_html( $strategy['name'] ) . '</strong></td>';
		echo '<td class="strategy-type">' . esc_html( $strategy['type'] ) . '</td>';
		echo '<td class="strategy-style">' . esc_html( $strategy['trading_style'] ) . '</td>';
		echo '<td class="strategy-risk risk-' . esc_attr( $risk_class ) . '">' . esc_html( $strategy['risk_level'] ) . '</td>';
		echo '<td class="strategy-status status-' . esc_attr( $status_class ) . '">' . esc_html( $strategy['status'] ) . '</td>';
		echo '</tr>';

		echo '<tr class="strategy-details" id="details-' . esc_attr( sanitize_title( $strategy['name'] ) ) . '" style="display: none;">';
		echo '<td colspan="5">';
		echo '<div class="strategy-detail-content">';

		echo '<div class="detail-section">';
		echo '<h4>' . esc_html__( 'Indicators/Components', 'tradepress' ) . '</h4>';
		echo '<p>' . esc_html( $strategy['indicators'] ) . '</p>';
		echo '</div>';

		echo '<div class="detail-section">';
		echo '<h4>' . esc_html__( 'Strategy Principles', 'tradepress' ) . '</h4>';
		echo '<p>' . esc_html( $strategy['principles'] ) . '</p>';
		echo '</div>';

		echo '<div class="detail-section">';
		echo '<h4>' . esc_html__( 'Risk Management', 'tradepress' ) . '</h4>';
		echo '<p>' . esc_html( $strategy['risk_management'] ) . '</p>';
		echo '</div>';

		echo '<div class="detail-section monitoring-info">';
		echo '<strong>' . esc_html__( 'Monitoring Frequency:', 'tradepress' ) . '</strong> ' . esc_html( $strategy['monitoring'] );
		echo '</div>';

		echo '</div>';
		echo '</td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
}
