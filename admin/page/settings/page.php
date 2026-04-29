<?php
/**
 * TradePress Settings Page Base Class
 *
 * @package    TradePress
 * @subpackage Admin
 * @version    1.0.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings Page base class
 */
abstract class TradePress_Settings_Page {

	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Current tab slug.
	 *
	 * @var string
	 */
	protected $current_tab = '';

	/**
	 * Current section slug.
	 *
	 * @var string
	 */
	protected $current_section = '';

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 */
	public function __construct() {
		add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );

		// Add the action hook for sections output
		add_action( 'TradePress_sections_' . $this->id, array( $this, 'output_sections' ) );
	}

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages Settings pages.
	 * @return array
	 * @version 1.0.0
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 * @version 1.0.0
	 */
	public function get_settings() {
		return apply_filters( 'TradePress_get_settings_' . $this->id, array() );
	}

	/**
	 * Output the settings.
	 *
	 * @version 1.0.0
	 */
	public function output() {
		$settings = $this->get_settings();

		TradePress_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Output sections for this tab.
	 *
	 * @version 1.0.0
	 */
	public function output_sections() {
		global $current_section;

		// Get sections for this tab
		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$section_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=TradePress&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $section_keys ) === $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Get sections for this tab.
	 *
	 * @return array
	 * @version 1.0.0
	 */
	public function get_sections() {
		return apply_filters( 'TradePress_get_sections_' . $this->id, array() );
	}

	/**
	 * Save settings.
	 *
	 * @version 1.0.0
	 */
	public function save() {
		$settings = $this->get_settings();

		TradePress_Admin_Settings::save_fields( $settings );
	}
}
