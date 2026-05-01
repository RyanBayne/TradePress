<?php
/**
 * TradePress Ajax Event Handler.
 *
 * @package  TradePress/Core
 * @category Ajax
 * @author   Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TradePress_AJAX {

	/**
	 * Hook in ajax handlers.
	 *
	 * @version 1.0.0
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_TradePress_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get TradePress Ajax Endpoint.
	 *
	 * @param  string $request Optional
	 * @return string
	 * @version 1.0.0
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( apply_filters( 'TradePress_ajax_get_endpoint', add_query_arg( 'TradePress-ajax', $request, remove_query_arg( array( 'remove_item', 'add-to-cart', 'added-to-cart' ) ) ), $request ) );
	}

	/**
	 * Set TradePress AJAX constant and headers.
	 *
	 * @version 1.0.0
	 */
	public static function define_ajax() {
		$tradepress_ajax = filter_input( INPUT_GET, 'TradePress-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! empty( $tradepress_ajax ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'TradePress_DOING_AJAX' ) ) {
				define( 'TradePress_DOING_AJAX', true );
			}

			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for TradePress Ajax Requests
	 *
	 * @version 1.0.0
	 */
	private static function TradePress_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for TradePress Ajax request and fire action.
	 *
	 * @version 1.0.0
	 */
	public static function do_TradePress_ajax() {
		global $wp_query;
		$tradepress_ajax = filter_input( INPUT_GET, 'TradePress-ajax', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! empty( $tradepress_ajax ) ) {
			$wp_query->set( 'TradePress-ajax', sanitize_text_field( wp_unslash( $tradepress_ajax ) ) );
		}

		if ( $action = $wp_query->get( 'TradePress-ajax' ) ) {
			self::TradePress_ajax_headers();
			do_action( 'TradePress_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 *
	 * @version 1.0.0
	 */
	public static function add_ajax_events() {
		// TradePress_EVENT => nopriv
		$ajax_events = array(
			'refresh_debug_info'  => false, // Admin-only Ajax event
			'manual_import'       => false, // Admin-only Ajax event
			'refresh_data_status' => false, // Admin-only Ajax event
			'run_all_imports'     => false, // Admin-only Ajax event
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_TradePress_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_TradePress_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// TradePress AJAX can be used for frontend ajax requests
				add_action( 'TradePress_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}

		// Register standard WordPress Ajax handlers
		add_action( 'wp_ajax_refresh_debug_info', array( __CLASS__, 'ajax_refresh_debug_info' ) );
		add_action( 'wp_ajax_tradepress_manual_import', array( __CLASS__, 'ajax_manual_import' ) );
		add_action( 'wp_ajax_tradepress_refresh_data_status', array( __CLASS__, 'ajax_refresh_data_status' ) );
		add_action( 'wp_ajax_tradepress_run_all_imports', array( __CLASS__, 'ajax_run_all_imports' ) );
	}

	/**
	 * Get the latest API call information for the specified API.
	 *
	 * @param string $api_id The API identifier.
	 * @return array|false The API call data or false if none found.
	 * @version 1.0.0
	 */
	public static function get_latest_api_call( $api_id ) {
		// For security, only allow access to this data when in testing mode
		if ( ! defined( 'TRADEPRESS_TESTING' ) || ! TRADEPRESS_TESTING ) {
			return false;
		}

		$api_call = get_transient( 'tradepress_api_' . $api_id . '_latest_call' );

		return $api_call;
	}

	/**
	 * Ajax handler to refresh the API debug info panel.
	 *
	 * @version 1.0.0
	 */
	public static function ajax_refresh_debug_info() {
		// Verify nonce
		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'refresh-debug-info' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'tradepress' ) ) );
		}

		// Check if user has permission
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'tradepress' ) ) );
		}

		// Get API ID
		$api_id = isset( $_POST['api_id'] ) ? sanitize_key( wp_unslash( $_POST['api_id'] ) ) : '';
		if ( empty( $api_id ) ) {
			wp_send_json_error( array( 'message' => __( 'API ID is required.', 'tradepress' ) ) );
		}

		// Delete the existing cached data to force regeneration
		delete_transient( 'tradepress_api_' . $api_id . '_latest_call' );

		// Get latest API call details (this would normally come from a database or cache)
		$api_call = self::get_latest_api_call( $api_id );

		// Generate the HTML for the debug panel
		ob_start();
		if ( $api_call ) : ?>
			<div class="api-call-details">
				<table class="widefat">
					<tr>
						<th>API Identifier:</th>
						<td><?php echo esc_html( $api_call['api_id'] ); ?></td>
					</tr>
					<tr>
						<th>Endpoint:</th>
						<td><?php echo esc_html( $api_call['endpoint'] ); ?></td>
					</tr>
					<tr>
						<th>Method:</th>
						<td><?php echo esc_html( $api_call['method'] ); ?></td>
					</tr>
					<tr>
						<th>Timestamp:</th>
						<td><?php echo esc_html( $api_call['timestamp'] ); ?></td>
					</tr>
					<tr>
						<th>Request Data:</th>
						<td><pre><?php echo esc_html( print_r( $api_call['request_data'], true ) ); ?></pre></td>
					</tr>
					<tr>
						<th>Response:</th>
						<td>
							<div class="api-response">
								<pre><?php echo esc_html( is_string( $api_call['response'] ) ? $api_call['response'] : print_r( $api_call['response'], true ) ); ?></pre>
							</div>
						</td>
					</tr>
				</table>
			</div>
		<?php else : ?>
			<div class="api-info-message">
				<p>No recent API calls for <?php echo esc_html( $api_id ); ?> have been cached. Make an API call with testing mode enabled to see results here.</p>
			</div>
			<?php
		endif;

		$html = ob_get_clean();

		// Send the HTML back to the client
		wp_send_json_success(
			array(
				'html'      => $html,
				'timestamp' => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Ajax handler for manual data element import
	 *
	 * @version 1.0.0
	 */
	public static function ajax_manual_import() {
		// Verify nonce
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_data_elements_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'tradepress' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'tradepress' ) ) );
		}

		$element_id = isset( $_POST['element_id'] ) ? sanitize_key( wp_unslash( $_POST['element_id'] ) ) : '';
		if ( empty( $element_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Element ID is required.', 'tradepress' ) ) );
		}

		// Get data elements configuration
		require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-elements.php';
		$config = tradepress_get_data_elements_config();

		if ( ! isset( $config[ $element_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid element ID.', 'tradepress' ) ) );
		}

		// Simulate import process (replace with actual import logic)
		$import_function = $config[ $element_id ]['import_function'] ?? null;

		if ( $import_function && function_exists( $import_function ) ) {
			$result = call_user_func( $import_function, array( 'force' => true ) );
		} else {
			$result = array(
				'success' => false,
				'message' => __( 'No real import function is configured for this data element.', 'tradepress' ),
			);
		}

		if ( $result['success'] ) {
			// Update last import time
			update_option( "tradepress_last_import_{$element_id}", time() );

			wp_send_json_success(
				array(
					'message'     => $result['message'],
					'import_time' => date( 'Y-m-d H:i:s' ),
					'status'      => '<span class="status-success">Up to Date</span>',
				)
			);
		} else {
			wp_send_json_error( array( 'message' => $result['message'] ?? 'Import failed' ) );
		}
	}

	/**
	 * Ajax handler for refreshing data status
	 *
	 * @version 1.0.0
	 */
	public static function ajax_refresh_data_status() {
		// Verify nonce
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_data_elements_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'tradepress' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'tradepress' ) ) );
		}

		// Get data elements configuration
		require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-elements.php';
		$config = tradepress_get_data_elements_config();

		$status_data = array();
		foreach ( $config as $element_id => $element ) {
			$status_data[ $element_id ] = array(
				'import_time' => tradepress_get_last_import_time( $element_id ),
				'status'      => tradepress_get_data_element_status( $element_id ),
			);
		}

		wp_send_json_success( $status_data );
	}

	/**
	 * Ajax handler for running all imports
	 *
	 * @version 1.0.0
	 */
	public static function ajax_run_all_imports() {
		// Verify nonce
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'tradepress_data_elements_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'tradepress' ) ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'tradepress' ) ) );
		}

		// Get data elements configuration
		require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-elements.php';
		$config = tradepress_get_data_elements_config();

		$results = array();
		$errors  = array();

		foreach ( $config as $element_id => $element ) {
			$import_function = $element['import_function'] ?? null;

			if ( $import_function && function_exists( $import_function ) ) {
				$result = call_user_func( $import_function, array( 'force' => true ) );
			} else {
				$result = array(
					'success' => false,
					'message' => __( 'No real import function is configured for this data element.', 'tradepress' ),
				);
			}

			if ( $result['success'] ) {
				update_option( "tradepress_last_import_{$element_id}", time() );
				$results[ $element_id ] = array(
					'import_time' => date( 'Y-m-d H:i:s' ),
					'status'      => '<span class="status-success">Up to Date</span>',
				);
			} else {
				$errors[]               = "{$element['name']}: {$result['message']}";
				$results[ $element_id ] = array(
					'import_time' => tradepress_get_last_import_time( $element_id ),
					'status'      => '<span class="status-error">Import Failed</span>',
				);
			}
		}

		if ( empty( $errors ) ) {
			wp_send_json_success( $results );
		} else {
			wp_send_json_error(
				array(
					'message' => implode( '; ', $errors ),
					'data'    => $results,
				)
			);
		}
	}
}

TradePress_AJAX::init();
