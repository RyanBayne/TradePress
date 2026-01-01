<?php
/**
* TradePress User Settings
* 
* @author Ryan Bayne
* @category Users
* @package TradePress/Settings/Users
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TradePress_Settings_Users' ) ) :

class TradePress_Settings_Users extends TradePress_Settings_Page {
    
    private $sections_array = array();
    
    /**
    * Constructor
    * 
    * @version 1.0    
    */
    public function __construct() {

        $this->id    = 'users';
        $this->label = __( 'Users', 'tradepress' );

        add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'TradePress_sections_' . $this->id, array( $this, 'output_sections' ) );
        
    }
    
    /**
    * Get sections.
    * 
    * @return array
    * 
    * @version 1.0
    */
    public function get_sections() {
        
        // Can leave this array empty and the first extensions first section...
        // will become the default view. Only use this if core plugin
        // needs settings on this tab. 
        $this->sections_array = array(
            'default'              => __( 'Service Switches', 'tradepress' ),
            'loginandregistration' => __( 'Login and Registration', 'tradepress' ),
        );
        
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
     * 
     * @version 2.0
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
        
        // Switch public services on and off easily/quickly.
        if ( 'default' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_user_publicserviceswitches_settings', array(
            
                array(
                    'title' => __( 'Service Switches', 'tradepress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Main controls for public services. Take great care if your service is live and busy as each switch can cause disruption to your subscribers. These settings do not affect administrator access or automated services setup by administrators.', 'tradepress' ),
                    'id'     => 'publicserviceswitches_settings'
                ),
               
                array(
                    'title'         => __( 'Channel Profiles', 'tradepress' ),
                    'desc'          => __( 'Take Ownership', 'tradepress' ),
                    'id'            => 'TradePress_serviceswitch_channels_takeownership',
                    'type'          => 'checkbox',
                    'default'       => 'no',
                    'checkboxgroup' => 'start',
                    'autoload'      => false,
                ),
                
                array(
                    'desc'            => __( 'Edit Channel Post Content', 'tradepress' ),
                    'id'              => 'TradePress_serviceswitch_channels_editcontent',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                
                array(
                    'desc'          => __( 'Control Chat Display', 'tradepress' ),
                    'id'            => 'TradePress_serviceswitch_channels_controlchatdisplay',
                    'type'          => 'checkbox',
                    'default'       => 'yes',
                    'checkboxgroup' => 'end',
                    'autoload'      => false,
                ),
                                                                                   
                array(
                    'type'     => 'sectionend',
                    'id'     => 'publicserviceswitches_settings'
                ),     
            
            ));
            
        // Pair public services with roles and capabilities.
        } elseif( 'publicservicepermissions' == $current_section ) {
            
            return;// REMOVE WHEN SECTION READY
                
            $settings = apply_filters( 'TradePress_user_publicservicepermissions_settings', array(
 
                array(
                    'title' => __( 'Registraton Settings', 'tradepress' ),
                    'type'     => 'title',
                    'desc'     => 'The.',
                    'id'     => 'usersregisrationsettings',
                ),
            
                array(
                    'desc'            => __( 'Checkbox Two', 'tradepress' ),
                    'id'              => 'loginsettingscheckbox2',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ), 
                
                array(
                    'type'     => 'sectionend',
                    'id'     => 'usersregisrationsettings'
                ),

            ));
        } elseif ( 'loginandregistration' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_loginextension_login_settings', array(
 
                array(
                    'title' => __( 'Login', 'TradePress-login' ),
                    'type'     => 'title',
                    'desc'     => __( 'These settings are offered by the TradePress Login Extension.', 'TradePress-login' ),
                    'id'     => 'loginsettings',
                ),

                array(
                    'title'   => __( 'Login Page Type', 'TradePress-login' ),
                    'desc'    => __( 'What type of login page have you setup?', 'TradePress-login' ),
                    'id'      => 'TradePress_login_loginpage_type',
                    'default' => 'both',
                    'type'    => 'radio',
                    'options' => array(
                        'default' => __( 'WP Login Form.', 'TradePress-login' ),
                        'page'    => __( 'Custom Login Page', 'TradePress-login' ),
                        'both'    => __( 'Mixed', 'TradePress-login' ),
                    ),
                    'autoload'        => false,
                    'show_if_checked' => 'option',
                ),

                array(
                    'title'   => __( 'Twitch Button Position', 'TradePress-login' ),
                    'desc'    => __( 'Select button position if using the WordPress login form.', 'TradePress-login' ),
                    'id'      => 'TradePress_login_loginpage_position',
                    'default' => 'above',
                    'type'    => 'radio',
                    'options' => array(
                        'above' => __( 'Above.', 'TradePress-login' ),
                        'below' => __( 'Below', 'TradePress-login' ),
                    ),
                    'autoload'        => false,
                    'show_if_checked' => 'option',
                ),
                
                array(
                    'title'           => __( 'Display "Connect Using Twitch" Button', 'TradePress-login' ),
                    'desc'            => __( 'Use Main Login Form', 'TradePress-login' ),
                    'id'              => 'TradePress_login_button',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'title'           => __( 'Require Twitch Login', 'TradePress-login' ),
                    'desc'            => __( 'Twitch only login. Hides login fields on wp-login.php only.', 'TradePress-login' ),
                    'id'              => 'TradePress_login_requiretwitch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'title'           => __( 'Custom Page Only', 'TradePress-login' ),
                    'desc'            => __( 'Redirect visitors away from wp-login.php to your custom page.', 'TradePress-login' ),
                    'id'              => 'TradePress_login_redirect_to_custom',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                
                array(
                    'title'           => __( 'No Redirects', 'TradePress-login' ),
                    'desc'            => __( 'Do not redirect on login success.', 'TradePress-login' ),
                    'id'              => 'TradePress_login_prevent_redirect',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'title'           => __( 'Redirect All Logins', 'TradePress-login' ),
                    'desc'            => __( 'Redirect none Twitch oAuth logins (admin excluded).', 'TradePress-login' ),
                    'id'              => 'TradePress_login_redirect_all',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                
                array(
                    'title'    => __( 'Custom Login Page', 'TradePress-login' ),
                    'desc'     => __( 'Enter the page ID that displays your main login form. This does not add login features to the page. Please do that using [TradePress_connect_button]', 'TradePress-login' ),
                    'id'       => 'TradePress_login_mainform_page_id',
                    'css'      => 'width:75px;',
                    'default'  => '',
                    'type'     => 'text',
                ),
                                                               
                array(
                    'title'    => __( 'Custom Logged-In Page', 'TradePress-login' ),
                    'desc'     => __( 'Enter the page ID where you visitors to be redirected to once logged in.', 'TradePress-login' ),
                    'id'       => 'TradePress_login_loggedin_page_id',
                    'css'      => 'width:75px;',
                    'default'  => '',
                    'type'     => 'text',
                ),
                
                array(
                    'title'    => __( 'Login Button Text', 'TradePress-login' ),
                    'desc'     => __( 'Enter the text you would like to display on your Twitch button.', 'TradePress-login' ),
                    'id'       => 'TradePress_login_button_text',
                    'css'      => 'width:230px;',
                    'default'  => '',
                    'type'     => 'text',
                ),
                 
                array(
                    'type'     => 'sectionend',
                    'id'     => 'loginsettings'
                ),
                
                array(
                    'title' => __( 'Registration', 'TradePress-login' ),
                    'type'     => 'title',
                    'desc'     => __( '', 'TradePress-login' ),
                    'id'     => 'registrationsettings',
                ),
                                                        
                array(
                    'desc'          => __( 'Registration Button: Display a Twitch button on the WordPress registration form.', 'TradePress-login' ),
                    'id'            => 'TradePress_registration_button',
                    'default'       => 'yes',
                    'type'          => 'checkbox',
                    'checkboxgroup' => '',
                    'autoload'      => false,
                ),

                array(
                    'desc'          => __( 'Force Registration: Force registration by Twitch only and hide WP registration form.', 'TradePress-login' ),
                    'id'            => 'TradePress_registration_twitchonly',
                    'default'       => 'no',
                    'type'          => 'checkbox',
                    'checkboxgroup' => '',
                    'autoload'      => false,
                ),
                
                array(
                    'desc'          => __( 'Email Validation: Require a validated email address (validated by user through their Twitch account).', 'TradePress-login' ),
                    'id'            => 'TradePress_registration_requirevalidemail',
                    'default'       => 'yes',
                    'type'          => 'checkbox',
                    'checkboxgroup' => '',
                    'autoload'      => false,
                ),
              
                array(
                    'type'     => 'sectionend',
                    'id'     => 'registrationsettings'
                ),
                
                array(
                    'title' => __( 'Automatic Registration', 'TradePress-login' ),
                    'type'     => 'title',
                    'desc'     => __( 'You can register a new user if the visitor attempts to login using the TradePress button provided and their Twitch details do not match an existing WordPress account. Users will be instantly logged in at the end of the procedure.', 'TradePress-login' ),
                    'id'     => 'automaticregistrationsettings',
                ), 
                
                array(
                    'desc'          => __( 'Register on Login.', 'TradePress-login' ),
                    'id'            => 'TradePress_automatic_registration',
                    'default'       => 'no',
                    'type'          => 'checkbox',
                    'checkboxgroup' => '',
                    'autoload'      => false,
                ),
              
                array(
                    'type'     => 'sectionend',
                    'id'     => 'automaticregistrationsettings'
                ),                                       

            ));   
            
        } 
    
        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TradePress_Settings_Users();
