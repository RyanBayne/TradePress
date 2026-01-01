<?php
/**
* TradePress Trading API Settings
* 
* This is the settings view for any API other than Twitch.tv
* 
* @author Ryan Bayne
* @category Users
* @package TradePress/Settings/Trading API
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TradePress_Settings_TradingAPI' ) ) :

class TradePress_Settings_TradingAPI extends TradePress_Settings_Page {
    
    private $sections_array = array();

    public $api_array = array();    

    /**
    * Constructor
    * 
    * @version 1.0    
    */
    public function __construct() {
        $this->id    = 'otherapi';
        $this->label = __( 'Trading API', 'tradepress' );

        // Load API array from directory
        $this->load_api_data();

        add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'TradePress_sections_' . $this->id, array( $this, 'output_sections' ) );
    }
    
    /**
     * Load API data from the API Directory
     */
    private function load_api_data() {
        // Check if TradePress_API_Directory class exists and include if needed
        if (!class_exists('TradePress_API_Directory')) {
            require_once WP_PLUGIN_DIR . '/TradePress/api/api-directory.php';
        }
        
        // Get all providers from directory
        $all_providers = TradePress_API_Directory::get_all_providers();
        
        // Extract provider IDs for api_array
        $this->api_array = array_keys($all_providers);
    }
    
    /**
     * Get API display names from the directory
     * 
     * @return array API display names
     */
    public function get_api_display_names() {
        if (!class_exists('TradePress_API_Directory')) {
            require_once WP_PLUGIN_DIR . '/TradePress/api/api-directory.php';
        }
        
        $all_providers = TradePress_API_Directory::get_all_providers();
        $display_names = array();
        
        foreach ($all_providers as $id => $provider) {
            $display_names[$id] = $provider['name'];
        }
        
        return $display_names;
    }
    
    /**
    * Get sections.
    * 
    * @return array
    * 
    * @version 1.0
    */
    public function get_sections() {
        // Initialize with the default section
        $this->sections_array = array(
            'default' => __( 'API Switches', 'tradepress' ),
        );
        
        // Add sections for each API provider
        $api_display_names = $this->get_api_display_names();
        foreach ($api_display_names as $api_id => $display_name) {
            $this->sections_array[$api_id] = __( $display_name, 'tradepress' );
        }
      
        return apply_filters( 'TradePress_get_sections_' . $this->id, $this->sections_array );
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
     * 
     * @version 2.0
     */
    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        
        TradePress_Admin_Settings::save_fields( $settings );
        
        // Run procedures for reacting to new application credentials.
        if( $service = $this->is_application_being_saved() ) {
            $this->update_application( $service );         
        }        
    } 
    
    /**
    * Store application credentials.
    * 
    * @param mixed $service
    * 
    * @version 1.1
    */
    public function update_application( $service ) {

        if( !isset( $_POST[ 'TradePress_api_' . $service . '_realmoney_apikey' ] ) ) {
            TradePress_Admin_Settings::add_error( __( 'You have not entered the primary API key for this service.', 'tradepress' ) );
            return;
        }    

        $key_real = sanitize_key( $_POST[ 'TradePress_api_' . $service . '_realmoney_apikey' ] );
        $key_paper = sanitize_key( $_POST[ 'TradePress_api_' . $service . '_papermoney_apikey' ] );
                
        // The All API library will start an oAuth2 if required.  
        $this->application_being_updated( $service, $key_real, $key_paper );
    }
    
    /**
    * Carrying out post application update procedures. 
    * 
    * @version 1.0
    */
    public function application_being_updated( $service, $key_real, $key_paper ) {     
 
        switch ( $service ) {
            case 'discord':

                break;

            case 'alphavantage':

                break;
        
           default:
                // Allow third party plugins to add_action() and run custom procedures for updating app credentials...
                do_action( 'TradePress_allapi_application_update_' . $service, $service, $key_real, $key_paper );
             break;
        }              
    }
    
    /** 
    * Determines if user is saving an application on any of the 
    * application forms spread over multiple sections. 
    * 
    * @returns string API lowercase slug. 
    * @returns boolean false if
    * 
    * @version 1.0
    */
    public function is_application_being_saved() { 
        if( isset( $_POST ) && isset( $_GET['section'] ) && in_array( $_GET['section'], $this->api_array ) ) 
        {
            return $_GET['section']; 
        }
        return false;
    } 
    
    /**
     * Get settings array.
     *
     * @return array
     * 
     * @version 2.0
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
        $api_display_names = $this->get_api_display_names();

        // Switch public services on and off easily/quickly.
        if ( 'default' == $current_section ) {

            $settings = apply_filters( 'TradePress_otherapi_switches_settings', array(
            
                array(
                    'title' => __( 'Other API Switches', 'tradepress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Switches for all API services.', 'tradepress' ),
                    'id'    => 'otherapiswitches_settings'
                ),
            ));

            foreach ($this->api_array as $api) {
                $display_name = isset($api_display_names[$api]) ? $api_display_names[$api] : ucfirst($api);
                $settings[] = array(
                    'title'         => __($display_name, 'tradepress'),
                    'desc'          => sprintf(__('Activate %s API', 'tradepress'), $display_name),
                    'id'            => 'TradePress_switch_' . $api . '_api_services',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                );
                $settings[] = array(
                    'desc'            => sprintf(__('Log %s API Activity', 'tradepress'), $display_name),
                    'id'              => 'TradePress_switch_' . $api . '_api_logs',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                );
            }

            $settings[] = 
                array(
                    'type' => 'sectionend',
                    'id'   => 'otherapiswitches_settings'
                );

        } elseif ( in_array( $current_section, $this->api_array ) ) {
            $title = isset( $api_display_names[$current_section] ) ? $api_display_names[$current_section] : ucfirst($current_section);
            $settings = apply_filters( 'TradePress_' . $current_section . '_application_settings', $this->application_inputs( $title, $current_section ) );
        }

        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
    }

    public function application_inputs( $title, $service ) {
        $service = strtolower( $service );
        $fields = array(
            array(
                'title' => $title . __( ' API Settings', 'tradepress' ),
                'type'  => 'title',
                'desc'  => sprintf( __( 'Application settings for the %s API.', 'tradepress' ), $title ),
                'id'    => $service . '_api_application_settings'
            ),
            array(
                'id'              => 'TradePress_otherapi_application_saving',
                'default'         => '',
                'autoload'        => false,
                'type'            => 'hidden',
            ),
            array(
                'title'         => __( 'Enable API', 'tradepress' ),
                'desc'          => __( 'Enable this API for use.', 'tradepress' ),
                'id'            => 'TradePress_switch_' . $service . '_api_services',
                'type'          => 'checkbox',
                'default'       => 'no',
                'autoload'      => false,
            ),
            array(
                'title'         => __( 'API Logging', 'tradepress' ),
                'desc'          => __( 'Log API Activity', 'tradepress' ),
                'id'            => 'TradePress_switch_' . $service . '_api_logs',
                'type'          => 'checkbox',
                'default'       => 'no',
                'checkboxgroup' => 'start',
                'autoload'      => false,
            ),
            array(
                'title'         => __( 'Premium Endpoints', 'tradepress' ),
                'desc'          => __( 'Allow Premium Endpoints', 'tradepress' ),
                'id'            => 'TradePress_switch_' . $service . '_api_premium',
                'type'          => 'checkbox',
                'default'       => 'no',
                'autoload'      => false,
            ),
            array(
                'title'           => __( 'Data/Real-Money API Key', 'tradepress' ),
                'desc'            => __( 'Your API key for real money trading on this platform.', 'tradepress' ),
                'id'              => 'TradePress_api_' . $service . '_realmoney_apikey',
                'default'         => '',
                'type'            => 'text',
                'autoload'        => false,
            ),
            array(
                'title'           => __( 'Paper-Money API Key', 'tradepress' ),
                'desc'            => __( 'Your API key for paper trading on this platform.', 'tradepress' ),
                'id'              => 'TradePress_api_' . $service . '_papermoney_apikey',
                'default'         => '',
                'type'            => 'text',
                'autoload'        => false,
            ),           
        );
        
        // Add fields for specific services...
        if( $service == 'twitter' ) {
            
            $token_secret_field = array(
                'title'           => __( 'Access Token Secret', 'tradepress' ),
                'desc'            => __( 'Token secrets are used to regenerate the token.', 'tradepress' ),
                'id'              => 'TradePress_allapi_' . $service . '_default_access_token_secret',
                'default'         => '',
                'type'            => 'password',
                'autoload'        => false,
            );
            
            $fields = array_merge( $fields, array($token_secret_field) );     
        }   
        
        // Add final section ID value required by WP...
        $final_value = array(
                'type' => 'sectionend',
                'id'   => $service . '_api_application_settings' );
                        
        return array_merge( $fields, array($final_value) );
    }

    /**
     * Get trading API options.
     *
     * @return array
     */
    public function get_trading_api_options() {
        if (!class_exists('TradePress_API_Directory')) {
            require_once WP_PLUGIN_DIR . '/TradePress/api/api-directory.php';
        }
        
        // Get providers from the directory
        $providers = TradePress_API_Directory::get_financial_providers();
        
        // Format as needed for settings
        $options = array();
        foreach ($providers as $id => $provider) {
            $options[$id] = $provider['name'];
        }
        
        return $options;
    }
}
    
endif;

return new TradePress_Settings_TradingAPI();
