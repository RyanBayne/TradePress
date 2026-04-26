<?php
/**
 * TradePress Developer Mode
 *
 * Manages the toggling and display of developer-specific information
 * on TradePress admin pages.
 *
 * @package TradePress\Developer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class TradePress_Developer_Mode {

	const OPTION_NAME = 'tradepress_developer_mode';

	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_toggle' ), 999 );
		add_action( 'wp_ajax_tradepress_toggle_developer_mode', array( $this, 'handle_toggle_ajax' ) );

		if ( $this->is_enabled() ) {
			add_action( 'admin_footer', array( $this, 'display_developer_info_footer' ) );
		}
	}

	/**
	 * Checks if developer mode is currently enabled for the current user.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public function is_enabled() {
		return (bool) get_option( self::OPTION_NAME, false );
	}

	/**
	 * Adds a toggle button to the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 */
	public function add_admin_bar_toggle( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$enabled = $this->is_enabled();
		$text    = $enabled ? __( 'Developer Mode: On', 'tradepress' ) : __( 'Developer Mode: Off', 'tradepress' );
		$icon    = $enabled ? 'dashicons-admin-tools' : 'dashicons-admin-generic';

		$wp_admin_bar->add_node(
			array(
				'id'    => 'tradepress-developer-mode',
				'title' => '<span class="ab-icon ' . esc_attr( $icon ) . '"></span><span class="ab-label">' . esc_html( $text ) . '</span>',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'TradePressDeveloperMode.toggle();',
					'class'   => 'tradepress-developer-mode-toggle',
				),
			)
		);

		// Add BugNet submenu
		$wp_admin_bar->add_node(
			array(
				'parent' => 'tradepress-developer-mode',
				'id'     => 'tradepress-bugnet',
				'title'  => __( 'BugNet', 'tradepress' ),
				'href'   => admin_url( 'admin.php?page=TradePress&tab=bugnet' ),
			)
		);

		// Add test item
		$wp_admin_bar->add_node(
			array(
				'parent' => 'tradepress-developer-mode',
				'id'     => 'tradepress-test-item',
				'title'  => 'Test Item',
				'href'   => '#',
			)
		);

		wp_add_inline_script(
			'admin-bar',
			"
			var TradePressDeveloperMode = {
				toggle: function() {
					var data = {
						'action': 'tradepress_toggle_developer_mode',
						'nonce': '" . wp_create_nonce( 'tradepress_developer_mode_nonce' ) . "'
					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response.success) {
							location.reload();
						} else {
							alert('" . esc_js( __( 'Failed to toggle developer mode.', 'tradepress' ) ) . "');
						}
					});
				}
			};
			"
		);
	}

	/**
	 * Handles AJAX request to toggle developer mode.
	 */
	public function handle_toggle_ajax() {
		check_ajax_referer( 'tradepress_developer_mode_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'tradepress' ) );
		}

		$current_status = $this->is_enabled();
		update_option( self::OPTION_NAME, ! $current_status );

		wp_send_json_success( array( 'new_status' => ! $current_status ) );
	}

	/**
	 * Displays developer information in the admin footer.
	 */
	public function display_developer_info_footer() {
		$screen = get_current_screen();
		$plugin_base_dir = untrailingslashit( TRADEPRESS_PLUGIN_DIR );
		$file_path = '';

		// Attempt to guess the current view file based on common plugin admin page structures.
		if ( isset( $_GET['page'] ) ) {
			$page_slug = sanitize_key( $_GET['page'] );
			$tab_slug  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

			if ( ! empty( $tab_slug ) ) {
				// Common pattern for tabbed pages.
				$possible_path = "admin/page/{$page_slug}/view/{$tab_slug}.php";
				if ( file_exists( $plugin_base_dir . '/' . $possible_path ) ) {
					$file_path = $possible_path;
				}
			}

			if ( empty( $file_path ) ) {
				// Fallback for non-tabbed pages or if 'view' subdirectory is not used.
				$possible_path = "admin/page/{$page_slug}/{$page_slug}.php";
				if ( file_exists( $plugin_base_dir . '/' . $possible_path ) ) {
					$file_path = $possible_path;
				}
			}

			if ( empty( $file_path ) ) {
				// Another common fallback for main page files.
				$possible_path = "admin/page/{$page_slug}/index.php";
				if ( file_exists( $plugin_base_dir . '/' . $possible_path ) ) {
					$file_path = $possible_path;
				}
			}
		}

		if ( ! empty( $file_path ) ) {
			$full_file_path = $plugin_base_dir . '/' . $file_path;
			$vscode_link = 'vscode://file/' . str_replace( '\\', '/', $full_file_path );
			?>
			<div class="tradepress-developer-info-footer" style="
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				background: #23282d;
				color: #fff;
				padding: 10px 20px;
				box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
				z-index: 99999;
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 13px;
			">
				<span><?php _e( 'Developer Mode Active', 'tradepress' ); ?></span>
				<?php if ( ! empty( $file_path ) ) : ?>
					<span>
						<?php _e( 'Current View File:', 'tradepress' ); ?>
						<a href="<?php echo esc_url( $vscode_link ); ?>" style="color: #00b9eb; text-decoration: none; margin-left: 5px;">
							<?php echo esc_html( $file_path ); ?>
						</a>
					</span>
				<?php endif; ?>
			</div>
			<?php
		} else {
			// Fallback if no specific view file is guessed.
			?>
			<div class="tradepress-developer-info-footer" style="
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				background: #23282d;
				color: #fff;
				padding: 10px 20px;
				box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
				z-index: 99999;
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 13px;
			">
				<span><?php _e( 'Developer Mode Active', 'tradepress' ); ?></span>
				<span><?php _e( 'Could not determine specific view file.', 'tradepress' ); ?></span>
			</div>
			<?php
		}
	}
}

// Initialize the developer mode.
new TradePress_Developer_Mode();