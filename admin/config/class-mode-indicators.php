<?php
/**
 * TradePress Mode Indicators
 *
 * Displays developer mode indicators in admin
 *
 * @package TradePress/Admin/Config
 * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TradePress_Mode_Indicators Class
 */
class TradePress_Mode_Indicators {

	/**
	 * Constructor
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_head', array( $this, 'add_mode_indicators' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_mode_indicator_styles' ) );
	}

	/**
	 * Add mode indicators to admin pages
	 *
	 * @version 1.0.0
	 */
	public function add_mode_indicators() {
		if ( ! $this->is_developer_mode() ) {
			return;
		}

		// Only show on TradePress plugin pages
		if ( ! $this->is_tradepress_page() ) {
			return;
		}

		?>
		<div id="tradepress-mode-indicators" class="screen-meta-links">
			<div class="tradepress-mode-indicator-wrap">
				<button type="button" id="tradepress-developer-indicator" class="button show-settings" aria-expanded="false">
					<span class="dashicons dashicons-admin-tools"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Developer Mode Active', 'tradepress' ); ?></span>
					Developer Mode
				</button>
			</div>
		</div>
		<?php
	}



	/**
	 * Check if developer mode is active
	 *
	 * @version 1.0.0
	 */
	private function is_developer_mode() {
		return function_exists( 'tradepress_is_developer_mode' ) && tradepress_is_developer_mode();
	}

	/**
	 * Check if current page is a TradePress plugin page
	 *
	 * @version 1.0.0
	 */
	private function is_tradepress_page() {
		// Use TradePress screen IDs function if available
		if ( function_exists( 'TradePress_get_screen_ids' ) ) {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->id, TradePress_get_screen_ids() ) ) {
				return true;
			}
		}

		// Simplified check: any page parameter containing 'tradepress'
		if ( isset( $_GET['page'] ) ) {
			return strpos( strtolower( wp_unslash( $_GET['page'] ) ), 'tradepress' ) !== false;
		}

		return false;
	}

	/**
	 * Enqueue mode indicator styles
	 *
	 * @version 1.0.0
	 */
	public function enqueue_mode_indicator_styles() {
		if ( ! $this->is_tradepress_page() ) {
			return;
		}

		wp_enqueue_style(
			'tradepress-mode-indicators',
			TRADEPRESS_PLUGIN_URL . 'assets/css/components/mode-indicators.css',
			array(),
			TRADEPRESS_VERSION
		);
	}
}

// Initialize the mode indicators
new TradePress_Mode_Indicators();
