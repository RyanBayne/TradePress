<?php
/**
 * TradePress Trading admin page class.
 *
 * @package TradePress/Admin
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
		private $active_tab = 'portfolio'; // Default to portfolio.

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 */
		public function __construct() {
			if ( isset( $_GET['tab'] ) && array_key_exists( sanitize_key( wp_unslash( $_GET['tab'] ) ), $this->get_tabs() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab is display-only navigation, no state change.
				$this->active_tab = sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab is display-only navigation, no state change.
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
				'sees-demo'          => __( 'SEES Demo', 'tradepress' ),
				'sees-diagnostics'   => __( 'SEES Diagnostics', 'tradepress' ),
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
			return array( 'trading-strategies', 'portfolio', 'trade-history', 'manual-trade', 'sees-demo', 'sees-diagnostics', 'sees-ready', 'sees-pro' );
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
				echo tradepress_get_development_tab_label( $this->active_tab, $tabs[ $this->active_tab ], $this->get_development_tab_ids() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- function escapes all output internally.
			}
			echo '</h1>';

			echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
			foreach ( $tabs as $tab_id => $tab_name ) {
				$active_class = ( $this->active_tab === $tab_id ) ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=tradepress_trading&tab=' . $tab_id ) ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . tradepress_get_development_tab_label( $tab_id, $tab_name, $this->get_development_tab_ids() ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- function escapes all output internally.
			}
			echo '</nav>';

			echo '<div class="tradepress-tab-content">';
			$tab_hook = 'tradepress_trading_area_' . $this->active_tab . '_tab_content';

			// Keep a direct fallback for SEES Demo in case hooks are filtered/removed.
			if ( 'sees-demo' === $this->active_tab && function_exists( 'tradepress_display_sees_demo_tab_content' ) ) {
				tradepress_display_sees_demo_tab_content();
			} else {
				// Action hook to display content for the active tab.
				do_action( $tab_hook );
			}
			echo '</div>'; // .tradepress-tab-content

			echo '</div>'; // .wrap
		}
	}

endif; // Class exists check.
