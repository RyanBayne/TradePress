<?php
/**
 * TradePress Development and Feature Tracking
 *
 * Previously contained the PHP-based roadmap system. All feature tasks have
 * been migrated to GitHub Issues and ROADMAP.md.
 *
 * @see https://github.com/RyanBayne/TradePress/issues
 * @see G:/My Drive/Project Management/Live/TradePress-Documentation/ROADMAP.md
 *
 * @package TradePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TradePress_Development
 *
 * Stub class retained for backwards compatibility with any code that calls
 * TradePress_Development::get_*() methods. All methods return empty arrays
 * now that the PHP roadmap system has been replaced by GitHub Issues.
 */
class TradePress_Development {

	/**
	 * Stub getter — returns empty array.
	 *
	 * @return array
	 * @version 1.0.0
	 */
	public static function __callStatic( $name, $args ) {
		return array();
	}

	/**
	 * Get all development data.
	 *
	 * @return array Empty array — feature data now lives in GitHub Issues.
	 * @version 1.0.0
	 */
	public static function get_all_data() {
		return array();
	}

	/**
	 * Get feature data in a format compatible with the admin UI.
	 *
	 * @return array Empty pages/systems structure.
	 * @version 1.0.0
	 */
	public static function get_ui_compatible_feature_data() {
		return array(
			'pages'   => array(),
			'systems' => array(),
		);
	}
}
