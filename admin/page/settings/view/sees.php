<?php
/**
* TradePress SEES Settings
*
* @author Your Name
* @category settings
* @package TradePress/Settings/SEES
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TradePress_Settings_SEES' ) ) :

class TradePress_Settings_SEES extends TradePress_Settings_Page {
 
    /**
    * Constructor
    * 
    * @version 1.0  
    */
    public function __construct()  {

        $this->id    = 'sees'; 
        $this->label = __( 'SEES', 'tradepress' );

        add_filter( 'TradePress_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id,      array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
    }
    
    /**
    * Get settings array.
    *
    * @return array
    * 
    * @version 1.0
    */
    public function get_settings( $current_section = 'default' ) {
        $settings = array(); 
        
        if ( 'default' == $current_section ) {

            $settings = apply_filters( 'TradePress_sees_settings', array(

                array(
                    'title' => __( 'Scoring Engine Execution System Configuration', 'tradepress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Configure the SEES module for automated trade execution based on scoring directives.', 'tradepress' ),
                    'id'    => 'sees_settings_title'
                ),
                
                array(
                    'title'    => __( 'Enable SEES Module', 'tradepress' ),
                    'desc'     => __( 'Enable the Scoring Engine Execution System for automated trading.', 'tradepress' ),
                    'id'       => 'tradepress_sees_enabled',
                    'default'  => 'no',
                    'type'     => 'checkbox'
                ),
                
                array(
                    'title'    => __( 'Minimum Score Threshold', 'tradepress' ),
                    'desc'     => __( 'Minimum score required to trigger trade execution (0-100)', 'tradepress' ),
                    'id'       => 'tradepress_sees_min_score',
                    'default'  => '75',
                    'type'     => 'number',
                    'custom_attributes' => array(
                        'min' => '0',
                        'max' => '100'
                    )
                ),
                
                array(
                    'title'    => __( 'Execution Mode', 'tradepress' ),
                    'desc'     => __( 'Select how SEES should execute trades', 'tradepress' ),
                    'id'       => 'tradepress_sees_execution_mode',
                    'default'  => 'demo',
                    'type'     => 'select',
                    'options'  => array(
                        'demo'     => __( 'Demo Mode Only', 'tradepress' ),
                        'paper'    => __( 'Paper Trading', 'tradepress' ),
                        'live'     => __( 'Live Trading (Use with extreme caution)', 'tradepress' )
                    )
                ),
                                    
                array(
                    'type'  => 'sectionend',
                    'id'    => 'sees_settings_title'
                )

            ));
            
        }
        
        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
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
     * Save settings method
     */
    public function save() {      
        global $current_section;
        $settings = $this->get_settings( $current_section );
        TradePress_Admin_Settings::save_fields( $settings );
    }  
}

endif;

return new TradePress_Settings_SEES();
