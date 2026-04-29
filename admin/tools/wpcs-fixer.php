<?php
/**
 * TradePress WPCS Auto-Fixer
 *
 * Provides automated fixes for specific WordPress Coding Standards issues.
 * Registers an AJAX handler so the WP Verifier issue panel can offer a "Fix" button.
 *
 * Supported fix codes:
 * - WordPress.Security.EscapeOutput.UnsafePrintingFunction (_e → esc_html_e)
 *
 * @package TradePress/Admin/Tools
 * @version 1.0.95
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TradePress_WPCS_Fixer Class
 *
 * @version 1.0.95
 */
class TradePress_WPCS_Fixer {

	/**
	 * Issue codes this fixer can handle.
	 *
	 * @var array
	 */
	private static $supported_codes = array(
		'WordPress.Security.EscapeOutput.UnsafePrintingFunction',
	);

	/**
	 * Initialize hooks.
	 *
	 * @version 1.0.95
	 */
	public static function init() {
		add_action( 'wp_ajax_tradepress_wpcs_fix', array( __CLASS__, 'handle_ajax_fix' ) );
		add_filter( 'tradepress_wpcs_fixable_codes', array( __CLASS__, 'get_supported_codes' ) );
	}

	/**
	 * Return the list of codes this fixer supports.
	 *
	 * @param array $codes Existing fixable codes.
	 * @return array
	 * @version 1.0.95
	 */
	public static function get_supported_codes( $codes = array() ) {
		return array_merge( $codes, self::$supported_codes );
	}

	/**
	 * Check if a given WPCS code can be auto-fixed.
	 *
	 * @param string $code The WPCS sniff code.
	 * @return bool
	 * @version 1.0.95
	 */
	public static function is_fixable( $code ) {
		return in_array( $code, self::$supported_codes, true );
	}

	/**
	 * Handle the AJAX fix request.
	 *
	 * Expects POST parameters:
	 * - nonce: security nonce
	 * - file: relative file path within the plugin directory
	 * - code: the WPCS sniff code to fix
	 * - issue_id: (optional) specific issue ID for JSON cleanup
	 *
	 * @version 1.0.95
	 */
	public static function handle_ajax_fix() {
		check_ajax_referer( 'tradepress_wpcs_fix', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions.' );
		}

		$file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
		$code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

		if ( empty( $file ) || empty( $code ) ) {
			wp_send_json_error( 'Missing file or code parameter.' );
		}

		if ( ! self::is_fixable( $code ) ) {
			wp_send_json_error( 'This issue code is not auto-fixable: ' . $code );
		}

		$plugin_dir = TRADEPRESS_PLUGIN_DIR_PATH;
		$abs_path   = $plugin_dir . $file;

		// Security: ensure the file is within the plugin directory
		$real_plugin = realpath( $plugin_dir );
		$real_file   = realpath( $abs_path );
		if ( ! $real_file || strpos( $real_file, $real_plugin ) !== 0 ) {
			wp_send_json_error( 'File path is outside the plugin directory.' );
		}

		$result = self::apply_fix( $abs_path, $code );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Remove fixed issues from the results JSON
		$removed = self::remove_issues_from_json( $file, $code );

		wp_send_json_success(
			array(
				'message'        => $result['message'],
				'fixes_applied'  => $result['count'],
				'issues_removed' => $removed,
			)
		);
	}

	/**
	 * Apply the fix for a given code to a file.
	 *
	 * @param string $abs_path Absolute path to the file.
	 * @param string $code     The WPCS sniff code.
	 * @return array|WP_Error  Array with 'message' and 'count', or WP_Error on failure.
	 * @version 1.0.95
	 */
	public static function apply_fix( $abs_path, $code ) {
		if ( ! file_exists( $abs_path ) ) {
			return new WP_Error( 'file_not_found', 'File not found: ' . basename( $abs_path ) );
		}

		$content = file_get_contents( $abs_path );
		if ( false === $content ) {
			return new WP_Error( 'read_error', 'Could not read file.' );
		}

		switch ( $code ) {
			case 'WordPress.Security.EscapeOutput.UnsafePrintingFunction':
				return self::fix_unsafe_printing_function( $abs_path, $content );
			default:
				return new WP_Error( 'unsupported', 'No fixer for code: ' . $code );
		}
	}

	/**
	 * Fix UnsafePrintingFunction: replace _e() with esc_html_e().
	 *
	 * Uses negative lookbehind to avoid double-replacing already-escaped calls.
	 *
	 * @param string $abs_path Absolute file path.
	 * @param string $content  File content.
	 * @return array Array with 'message' and 'count'.
	 * @version 1.0.95
	 */
	private static function fix_unsafe_printing_function( $abs_path, $content ) {
		// Match _e( that is NOT preceded by esc_html or esc_attr
		$pattern = '/(?<!esc_html)(?<!esc_attr)_e\s*\(/';
		$count   = 0;
		$fixed   = preg_replace_callback(
			$pattern,
			function ( $matches ) use ( &$count ) {
				$count++;
				// Preserve original spacing: _e( or _e ( etc.
				return str_replace( '_e', 'esc_html_e', $matches[0] );
			},
			$content
		);

		if ( $count === 0 ) {
			return array(
				'message' => 'No unescaped _e() calls found.',
				'count'   => 0,
			);
		}

		// Update @version tag in file header if present
		$fixed = preg_replace(
			'/(@version\s+)\d+\.\d+(\.\d+)?/',
			'${1}' . TRADEPRESS_VERSION,
			$fixed,
			1 // Only update the first occurrence (file header)
		);

		$written = file_put_contents( $abs_path, $fixed );
		if ( false === $written ) {
			return new WP_Error( 'write_error', 'Could not write to file.' );
		}

		return array(
			/* translators: %d: number of replacements made */
			'message' => sprintf( __( 'Replaced %d _e() calls with esc_html_e().', 'tradepress' ), $count ),
			'count'   => $count,
		);
	}

	/**
	 * Remove issues matching a specific code for a file from the results JSON.
	 *
	 * @param string $rel_path Relative file path.
	 * @param string $code     The WPCS sniff code.
	 * @return int Number of issues removed.
	 * @version 1.0.95
	 */
	private static function remove_issues_from_json( $rel_path, $code ) {
		$json_path = TRADEPRESS_PLUGIN_DIR_PATH . '.wpv-results.json';
		if ( ! file_exists( $json_path ) ) {
			return 0;
		}

		$json = json_decode( file_get_contents( $json_path ), true );
		if ( ! $json || ! isset( $json['results'][ $rel_path ] ) ) {
			return 0;
		}

		$before                       = count( $json['results'][ $rel_path ] );
		$json['results'][ $rel_path ] = array_values(
			array_filter(
				$json['results'][ $rel_path ],
				function ( $issue ) use ( $code ) {
					return $issue['code'] !== $code;
				}
			)
		);
		$after                        = count( $json['results'][ $rel_path ] );

		if ( empty( $json['results'][ $rel_path ] ) ) {
			unset( $json['results'][ $rel_path ] );
		}

		// Recalculate error count
		$error_count = 0;
		foreach ( $json['results'] as $issues ) {
			foreach ( $issues as $issue ) {
				if ( 'ERROR' === $issue['type'] ) {
					++$error_count;
				}
			}
		}
		$json['readiness']['errors'] = $error_count;

		file_put_contents( $json_path, wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );

		return $before - $after;
	}
}

TradePress_WPCS_Fixer::init();
