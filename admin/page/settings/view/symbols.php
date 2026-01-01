<?php
/**
 * TradePress Symbols Settings View
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TradePress_Settings_Symbols' ) ) :

/**
 * TradePress_Settings_Symbols.
 */
class TradePress_Settings_Symbols extends TradePress_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'symbols';
        $this->label = __( 'Symbols', 'tradepress' );
        
        add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'default'  => __( 'Default Symbol', 'tradepress' ),
        );

        return apply_filters( 'TradePress_get_sections_' . $this->id, $sections );
    }

    /**
     * Output the settings.
     */
    public function output() {
        global $current_section;
        
        $settings = $this->get_settings( $current_section );
        TradePress_Admin_Settings::output_fields( $settings );
    }

    /**
     * Save settings.
     */
    public function save() {
        global $current_section;
        
        $settings = $this->get_settings( $current_section );
        TradePress_Admin_Settings::save_fields( $settings );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
                            
        if ( 'default' == $current_section || '' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_symbols_settings', array(

                array(
                    'title' => __( 'Default Symbol Settings', 'tradepress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Configure the default symbol used for testing and demo content.', 'tradepress' ),
                    'id'    => 'symbols_default_options'
                ),

                array(
                    'title'    => __( 'Default Test Symbol', 'tradepress' ),
                    'desc'     => __( 'Symbol used when testing directives or creating demo content', 'tradepress' ),
                    'id'       => 'tradepress_default_test_symbol',
                    'css'      => 'width:100px;',
                    'default'  => 'NVDA',
                    'type'     => 'text',
                    'desc_tip' => __( 'This symbol will be used as the default when testing directives or generating demo content. Use a liquid, well-known stock symbol.', 'tradepress' ),
                ),

                array(
                    'title'    => __( 'Fallback Symbols', 'tradepress' ),
                    'desc'     => __( 'Comma-separated list of backup symbols to use if the default fails', 'tradepress' ),
                    'id'       => 'tradepress_fallback_symbols',
                    'css'      => 'width:300px;',
                    'default'  => 'AAPL,MSFT,GOOGL,AMZN,TSLA',
                    'type'     => 'text',
                    'desc_tip' => __( 'These symbols will be tried in order if the default symbol fails. Separate with commas.', 'tradepress' ),
                ),

                array(
                    'title'    => __( 'Symbol Validation', 'tradepress' ),
                    'desc'     => __( 'Validate symbols before using them in tests', 'tradepress' ),
                    'id'       => 'tradepress_validate_symbols',
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'When enabled, symbols will be validated against API availability before use.', 'tradepress' ),
                ),

                array(
                    'title'    => __( 'Testing Symbol Mode', 'tradepress' ),
                    'desc'     => __( 'Use default symbol for testing to reduce API calls', 'tradepress' ),
                    'id'       => 'tradepress_use_default_test_symbol',
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                    'desc_tip' => __( 'When enabled, directive testing will always use the default test symbol instead of randomizing. This reduces API calls during development.', 'tradepress' ),
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'symbols_default_options'
                ),

            ));
        } 
                                   
        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TradePress_Settings_Symbols();