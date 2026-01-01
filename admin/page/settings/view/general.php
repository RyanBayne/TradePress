<?php
/**
* TradePress General Settings
*
* @author Ryan Bayne
* @category settings
* @package TradePress/Settings/General
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TradePress_Settings_General' ) ) :

class TradePress_Settings_General extends TradePress_Settings_Page {

    private $sections_array = array ();
 
    /**
    * Constructor
    * 
    * @version 1.0  
    */
    public function __construct()  {

        $this->id  = 'general'; 
        $this->label = __( 'General', 'tradepress' );

        add_filter( 'TradePress_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id,      array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'TradePress_sections_' . $this->id,      array( $this, 'output_sections' ) );
        add_action('admin_init', array($this, 'register_favorite_tabs_setting'));
        add_action('current_screen', array($this, 'process_favorite_tabs_form'));
    }

    /**
    * Register favorite tabs settings
    */
    public function register_favorite_tabs_setting() {
        register_setting(
            'tradepress_favorite_tabs', // Option group
            'tradepress_favorite_tabs', // Option name
            array(
                'sanitize_callback' => array($this, 'sanitize_favorite_tabs'),
                'default' => array(),
            )
        );
    }
 
    /**
    * Process favorite tabs form submission
    */
    public function process_favorite_tabs_form() {
        // Only process on the settings page with fave tabs section
        if (!isset($_POST['save_favorite_tabs']) || 
            !isset($_GET['page']) || $_GET['page'] !== 'tradepress-settings' ||
            !isset($_GET['tab']) || $_GET['tab'] !== 'general' ||
            !isset($_GET['section']) || $_GET['section'] !== 'favetabs') {
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['favorite_tabs_nonce']) || !wp_verify_nonce($_POST['favorite_tabs_nonce'], 'save_favorite_tabs')) {
            add_settings_error('favorite_tabs', 'nonce_failed', __('Security check failed. Please try again.', 'tradepress'), 'error');
            return;
        }
        
        // Process favorite tabs data
        $favorite_tabs = isset($_POST['tradepress_favorite_tabs']) ? $_POST['tradepress_favorite_tabs'] : array();
        
        // Sanitize each tab ID
        $favorite_tabs = array_map('sanitize_text_field', $favorite_tabs);
        
        // Save to database directly to avoid potential issues
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "INSERT INTO $wpdb->options (option_name, option_value, autoload) 
             VALUES (%s, %s, %s)
             ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)",
            'tradepress_favorite_tabs',
            maybe_serialize($favorite_tabs),
            'yes'
        ));
        
        // Clear caches
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
        wp_cache_delete('tradepress_favorite_tabs', 'options');
        
        // Add success message
        add_settings_error('favorite_tabs', 'settings_updated', __('Favorite tabs saved successfully.', 'tradepress'), 'success');
    }
 
    /**
    * Get sections.
    * 
    * @return array
    * 
    * @version 1.1
    */
    public function get_sections() {
        
        // Add more sections to the settings tab.
        $this->sections_array = array(
            'default'   => __( 'General', 'tradepress' ), 
            'removal'   => __( 'Plugin Removal', 'tradepress' ),
            'advanced'  => __( 'Advanced', 'tradepress' ),
            'systems'   => __( 'System Switches', 'tradepress' ),
            'features'  => __( 'Feature Switches', 'tradepress' ),
            'favetabs'  => __( 'Favorite Tabs', 'tradepress' ),
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
     * Save settings method runs along with save() method in admin-settings.php
     * 
     * @version 3.1
     */
    public function save() {      
        
        // Handle all sections (tabs) first...
        global $current_section;
        
        $settings = $this->get_settings( $current_section );
        TradePress_Admin_Settings::save_fields( $settings ); // Use the saved values where possible...
    
        $notices = new TradePress_Admin_Notices();
        
        // Handle the $current_section only...
        switch ( $current_section ) {
            case 'default':

            break;
            case 'apicall':
                // Add save handling for API Call Configuration if needed
            break;
            case 'removal':

            break;
            case 'advanced':

            break;
            case 'systems':            
                
                if( isset( $_POST['TradePress_webhooks_switch'] ) ) {
                    TradePress_Admin_Settings::add_message( __( 'Webhooks System Activated', 'tradepress' ) );
                } else {
                    TradePress_Admin_Settings::add_message( __( 'Webhooks System Disabled', 'tradepress' ) );    
                }                
                
                if( isset( $_POST['TradePress_gate_switch'] ) ) {
                    TradePress_Admin_Settings::add_message( __( 'Content Gate System Activated', 'tradepress' ) );
                } else {
                    TradePress_Admin_Settings::add_message( __( 'Content Gate System Disabled', 'tradepress' ) );    
                }                

            break;
        }
  
        
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

            $settings = apply_filters( 'TradePress_general_settings', array(

                array(
                    'title' => __( 'General Settings', 'tradepress' ),
                    'type'     => 'title',
                    'desc'     => __( 'You can support development by opting into the improvement program. It does not send sensitive data.', 'tradepress' ),
                    'id'     => 'generalsettings'
                ),
                
                array(
                    'title'           => __( 'Demo Mode', 'tradepress' ),
                    'desc'            => __( 'When enabled, TradePress will use test data instead of real API calls. This is a global setting affecting all plugin features.', 'tradepress' ),
                    'id'              => 'tradepress_demo_mode',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                
                array(
                    'title'           => __( 'Beta Testing', 'tradepress' ),
                    'desc'            => __( 'Enable access to beta features and new functionality that may use demo data temporarily.', 'tradepress' ),
                    'id'              => 'tradepress_beta_testing',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
            
                array(
                    'title'           => __( 'Force Paper Trading', 'tradepress' ),
                    'desc'            => __( 'Force all accounts to use paper trading only.', 'tradepress' ),
                    'id'              => 'tradepress_paper_trading_only',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'Send Usage Data', 'tradepress' ),
                    'id'              => 'TradePress_feedback_data',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'Allow Feedback Prompts', 'tradepress' ),
                    'id'              => 'TradePress_feedback_prompt',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                    
                array(
                    'type'     => 'sectionend',
                    'id'     => 'generalsettings'
                )

            ));
            
        } elseif( 'removal' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_general_removal_settings', array(
 
                array(
                    'title' => __( 'Plugin Removal Settings', 'tradepress' ),
                    'type'     => 'title',
                    'desc'     => __( 'What should the TradePress core plugin remove when being deleted?', 'tradepress' ),
                    'id'     => 'pluginremovalsettings',
                ),
            
                array(
                    'desc'            => __( 'Delete Options', 'tradepress' ),
                    'id'              => 'TradePress_remove_options',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    

                array(
                    'desc'            => __( 'Delete Database Tables', 'tradepress' ),
                    'id'              => 'TradePress_remove_database_tables',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),        
            
                array(
                    'desc'            => __( 'Delete User Data', 'tradepress' ),
                    'id'              => 'TradePress_remove_user_data',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    
            
                array(
                    'desc'            => __( 'Delete Media', 'tradepress' ),
                    'id'              => 'TradePress_remove_media',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    
                
                array(
                    'type'     => 'sectionend',
                    'id'     => 'pluginremovalsettings'
                ),

            ));
        
         // Advanced settings for developers only...
        } elseif( 'advanced' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_general_advanced_settings', array(
 
                array(
                    'title' => __( 'Advanced Settings', 'tradepress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Use with care. Some settings are meant for development environments (not live sites).', 'tradepress' ),
                    'id'     => 'advancedsettings',
                ),
            
                array(
                    'desc'            => __( 'Display Errors', 'tradepress' ),
                    'id'              => 'TradePress_displayerrors',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                          
                array(
                    'desc'            => __( 'Activate Redirect Tracking', 'tradepress' ),
                    'id'              => 'TradePress_redirect_tracking_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                          
                array(
                    'desc'            => __( 'Log API Activity', 'tradepress' ),
                    'id'              => 'TradePress_api_logging_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                          
                array(
                    'desc'            => __( 'Log API Raw Response/Body', 'tradepress' ),
                    'id'              => 'TradePress_api_logging_body_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                          
                array(
                    'desc'            => __( 'Demo Mode', 'tradepress' ),
                    'id'              => 'tradepress_demo_mode',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                          
                array(
                    'desc'            => __( 'Developer Mode', 'tradepress' ),
                    'id'              => 'tradepress_developer_mode',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                        
                array(
                    'type'     => 'sectionend',
                    'id'     => 'advancedsettings'
                ),

            ));

        } elseif( 'systems' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_general_systems_settings', array(
 
                array(
                    'title' => __( 'System Switches', 'tradepress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Use these settings to quickly enable/disable systems.', 'tradepress' ),
                    'id'     => 'systemsettings',
                ),
                                
                array(
                    'desc'            => __( 'Content Gate System', 'tradepress' ),
                    'id'              => 'TradePress_gate_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                                            
                array(
                    'type'     => 'sectionend',
                    'id'     => 'systemsettings'
                ),

            ));

        } elseif ( 'features' == $current_section ) {
            // Feature Switches section
            $settings = apply_filters( 'TradePress_features_settings', array(
                array(
                    'title'     => __( 'Feature Switches', 'tradepress' ),
                    'type'      => 'title',
                    'desc'      => __( 'Enable or disable specific TradePress features. These settings control which functionality is available to users.', 'tradepress' ),
                    'id'        => 'feature_switches_options'
                ),
                
                // Add page and tab visibility controls
                array(
                    'title'     => __( 'Page & Tab Visibility', 'tradepress' ),
                    'desc'      => __( 'Control which pages and tabs are displayed in the TradePress admin interface.', 'tradepress' ),
                    'type'      => 'tradepress_page_tab_controls',
                    'id'        => 'tradepress_page_tab_visibility',
                ),
                
                array(
                    'type'      => 'sectionend',
                    'id'        => 'feature_switches_options'
                ),
            ) );
            
            // Add custom field type for page & tab visibility controls
            add_action('TradePress_admin_field_tradepress_page_tab_controls', array($this, 'output_page_tab_controls'), 10, 1);
        } elseif ( 'favetabs' == $current_section ) {
            // Get current favorite tabs
            $favorite_tabs = get_option('tradepress_favorite_tabs', array());
            
            // Get all available tabs
            $all_tabs = $this->get_all_tabs();
            
            // Create checkbox fields for each tab
            $tab_checkboxes = array();
            
            // Start with title array element
            $tab_checkboxes[] = array(
                'title' => __( 'Favorite Tabs', 'tradepress' ),
                'type'  => 'title',
                'desc'  => __( 'Select tabs you want to quickly access. These will appear in the TradePress Tabs toolbar menu.', 'tradepress' ),
                'id'    => 'favetabs_settings'
            );
            
            // Add dynamic checkboxes for each tab
            foreach ($all_tabs as $tab_id => $tab) {
                $tab_checkboxes[] = array(
                    'desc'            => $tab['title'],
                    'id'              => 'tradepress_favetab_' . $tab_id,
                    'default'         => in_array($tab['id'], $favorite_tabs) ? 'yes' : 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                );
            }
            
            // Add section end
            $tab_checkboxes[] = array(
                'type' => 'sectionend',
                'id'   => 'favetabs_settings'
            );
            
            $settings = apply_filters( 'TradePress_general_favetabs_settings', $tab_checkboxes );
        }
        
        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
    }

    /**
     * Get all available tabs in the system
     * 
     * @return array All available tabs with their information
     */
    private function get_all_tabs() {
        // This is a comprehensive mapping of tab IDs to their information
        return array(
            // Research page tabs
            'research_overview' => array(
                'id' => 'research_overview',
                'title' => __('Research Overview', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research')
            ),
            'research_earnings' => array(
                'id' => 'research_earnings',
                'title' => __('Earnings Calendar', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=earnings')
            ),
            'research_technical' => array(
                'id' => 'research_technical',
                'title' => __('Technical Analysis', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=technical')
            ),
            'research_fundamental' => array(
                'id' => 'research_fundamental',
                'title' => __('Fundamental Analysis', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=fundamental')
            ),
            'research_news' => array(
                'id' => 'research_news',
                'title' => __('News', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=news')
            ),
            'research_chatter' => array(
                'id' => 'research_chatter',
                'title' => __('Chatter', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_research&tab=chatter')
            ),
            
            // Automation tabs
            'automation_dashboard' => array(
                'id' => 'automation_dashboard',
                'title' => __('Automation Dashboard', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation')
            ),
            'automation_algorithm' => array(
                'id' => 'automation_algorithm',
                'title' => __('Scoring Algorithm', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=algorithm')
            ),
            'automation_signals' => array(
                'id' => 'automation_signals',
                'title' => __('Trading Signals', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=signals')
            ),
            'automation_trading' => array(
                'id' => 'automation_trading',
                'title' => __('Trading', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=trading')
            ),
            'automation_cron' => array(
                'id' => 'automation_cron',
                'title' => __('CRON Jobs', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_automation&tab=cron')
            ),
            
            // Data tabs
            'data_tables' => array(
                'id' => 'data_tables',
                'title' => __('Data Tables', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_data&tab=tables')
            ),
            'data_symbols' => array(
                'id' => 'data_symbols',
                'title' => __('Symbols', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_data&tab=symbols')
            ),
            'data_sources' => array(
                'id' => 'data_sources',
                'title' => __('Data Sources', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_data&tab=sources')
            ),
            
            // Trading tabs
            'trading_platforms' => array(
                'id' => 'trading_platforms',
                'title' => __('Trading Platforms', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_platforms')
            ),
            
            // Settings tabs
            'settings_general' => array(
                'id' => 'settings_general',
                'title' => __('General Settings', 'tradepress'),
                'url' => admin_url('admin.php?page=TradePress&tab=general') // Update to use correct slug
            ),
            'settings_api' => array(
                'id' => 'settings_api',
                'title' => __('API Settings', 'tradepress'),
                'url' => admin_url('admin.php?page=TradePress&tab=api') // Update to use correct slug
            ),
            'settings_favetabs' => array(
                'id' => 'settings_favetabs',
                'title' => __('Favorite Tabs', 'tradepress'),
                'url' => admin_url('admin.php?page=TradePress&tab=general&section=favetabs') // Update to use correct slug
            ),
            
            // Bot tabs
            'bot_dashboard' => array(
                'id' => 'bot_dashboard',
                'title' => __('Bot Dashboard', 'tradepress'),
                'url' => admin_url('admin.php?page=tradepress_bot')
            ),
        );
    }

    /**
     * Custom renderer for section_start field type
     */
    public function output_section_start($value) {
        ?>
        <tr valign="top" class="favetabs-section-header">
            <th scope="row" class="titledesc">
                <h3><?php echo esc_html($value['title']); ?></h3>
            </th>
            <td class="forminp forminp-section-start">
                <?php if (!empty($value['desc'])): ?>
                    <p class="description"><?php echo wp_kses_post($value['desc']); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Custom renderer for section_end field type
     */
    public function output_section_end($value) {
        // No visible output needed
        echo '<!-- End of section: ' . esc_attr($value['id']) . ' -->';
    }

    /**
     * Output the page and tab visibility controls
     *
     * @param array $field Field data
     */
    public function output_page_tab_controls($field) {
        // Include the features data class if not already included
        if (!class_exists('TradePress_Features_Data')) {
            require_once TRADEPRESS_PLUGIN_DIR . 'includes/data/features-data.php';
        }
        
        // Get the feature data
        $features = TradePress_Features_Data::get_features_data();
        
        // Get saved settings
        $visibility_settings = get_option($field['id'], array());
        
        // Output field
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['title']); ?></label>
                <?php if (isset($field['desc_tip']) && $field['desc_tip']): ?>
                    <span class="dashicons dashicons-info tooltip" title="<?php echo esc_attr($field['desc_tip']); ?>"></span>
                <?php endif; ?>
            </th>
            <td class="forminp">
                <div class="description"><?php echo wp_kses_post($field['desc']); ?></div>
                
                <div class="tradepress-feature-controls">
                    <!-- Styles moved to assets/css/pages/settings-general.css -->
                    
                    <?php foreach ($features as $page_id => $page): ?>
                    <div class="page-visibility-section">
                        <div class="page-visibility-header">
                            <div class="visibility-toggle">
                                <input type="checkbox" 
                                      id="page_visibility_<?php echo esc_attr($page_id); ?>" 
                                      name="<?php echo esc_attr($field['id']); ?>[pages][<?php echo esc_attr($page_id); ?>]" 
                                      value="1"
                                      <?php checked(isset($visibility_settings['pages'][$page_id]) ? $visibility_settings['pages'][$page_id] : 1); ?>>
                                <label for="page_visibility_<?php echo esc_attr($page_id); ?>">
                                    <strong><?php echo esc_html($page['label']); ?></strong>
                                </label>
                            </div>
                        </div>
                        
                        <?php if (!empty($page['tabs'])): ?>
                            <div class="tab-visibility-list">
                                <?php foreach ($page['tabs'] as $tab_id => $tab): ?>
                                    <div class="tab-visibility-item">
                                        <div class="visibility-toggle">
                                            <input type="checkbox" 
                                                  id="tab_visibility_<?php echo esc_attr($page_id); ?>_<?php echo esc_attr($tab_id); ?>" 
                                                  name="<?php echo esc_attr($field['id']); ?>[tabs][<?php echo esc_attr($page_id); ?>][<?php echo esc_attr($tab_id); ?>]" 
                                                  value="1"
                                                  <?php checked(isset($visibility_settings['tabs'][$page_id][$tab_id]) ? $visibility_settings['tabs'][$page_id][$tab_id] : 1); ?>>
                                            <label for="tab_visibility_<?php echo esc_attr($page_id); ?>_<?php echo esc_attr($tab_id); ?>">
                                                <?php echo esc_html($tab['label']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <p class="description"><?php _e('Note: This feature is currently in development. Changing these settings will not affect visibility yet.', 'tradepress'); ?></p>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Output the Favorite Tabs section
     */
    private function output_favorite_tabs_section() {
        // Get current favorite tabs
        $favorite_tabs = get_option('tradepress_favorite_tabs', array());
        
        // Get all available tabs
        $all_tabs = $this->get_all_tabs();
        
        // Check if we have a settings update message
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === '1') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Favorite tabs saved successfully.', 'tradepress') . '</p></div>';
        }
        
        ?>
        <div class="tradepress-favorite-tabs-settings">
            <h2><?php esc_html_e('Configure Favorite Tabs', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Select tabs you frequently use to add them to the TradePress Favorites toolbar for quick access.', 'tradepress'); ?></p>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="save_tradepress_favorite_tabs">
                <?php wp_nonce_field('save_tradepress_favorite_tabs', 'favorite_tabs_nonce'); ?>
                
                <?php /* ...existing code... */ ?>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'tradepress'); ?>">
                </p>
            </form>
        </div>
        <?php
    }
}
    
endif;

return new TradePress_Settings_General();




