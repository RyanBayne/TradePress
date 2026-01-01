<?php
/**
 * TradePress Education Settings
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Settings_Education' ) ) :

/**
 * TradePress_Settings_Education.
 */
class TradePress_Settings_Education extends TradePress_Settings_Page {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'education';
        $this->label = __( 'Education', 'tradepress' );

        add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'TradePress_sections_' . $this->id, array( $this, 'output_sections' ) );
        add_action( 'admin_init', array( $this, 'handle_toolbar_actions' ) );
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections() {
        return array(
            'default' => __( 'Pointers', 'tradepress' ),
        );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings( $current_section = 'default' ) {
        $settings = array();
        
        if ( 'default' == $current_section ) {
            $settings = array(
                array(
                    'title' => __( 'Education System', 'tradepress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Manage tips, guidance, help content and pointers throughout the TradePress interface.', 'tradepress' ),
                    'id'    => 'education_options'
                ),

                array(
                    'title'    => __( 'Reset Pointers', 'tradepress' ),
                    'desc'     => __( 'Reset all WordPress pointers to show again for all users.', 'tradepress' ),
                    'id'       => 'reset_pointers_section',
                    'type'     => 'reset_pointers_button',
                    'desc_tip' => __( 'This will clear the dismissed_wp_pointers user meta for all users, causing all pointers to display again.', 'tradepress' ),
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'education_options'
                ),
            );
        }

        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
    }

    /**
     * Output the settings.
     */
    public function output() {
        global $current_section;
        
        $settings = $this->get_settings( $current_section );
        
        // Add custom action for reset button field
        add_action( 'TradePress_admin_field_reset_pointers_button', array( $this, 'output_reset_button' ) );
        
        TradePress_Admin_Settings::output_fields( $settings );
    }
    
    /**
     * Output reset pointers button.
     */
    public function output_reset_button( $value ) {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label><?php echo esc_html( $value['title'] ); ?></label>
                <?php if ( ! empty( $value['desc_tip'] ) ) : ?>
                    <span class="TradePress-help-tip" data-tip="<?php echo esc_attr( $value['desc_tip'] ); ?>"></span>
                <?php endif; ?>
            </th>
            <td class="forminp">
                <button type="submit" name="reset_pointers_button" class="button button-secondary">
                    <?php esc_html_e( 'Reset All Pointers', 'tradepress' ); ?>
                </button>
                <?php if ( ! empty( $value['desc'] ) ) : ?>
                    <p class="description"><?php echo wp_kses_post( $value['desc'] ); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Save settings.
     */
    public function save() {
        global $current_section;
        
        if ( isset( $_POST['reset_pointers_button'] ) ) {
            $this->reset_all_pointers();
            TradePress_Admin_Settings::add_message( __( 'All pointers have been reset and will show again for all users.', 'tradepress' ) );
            return;
        }

        $settings = $this->get_settings( $current_section );
        TradePress_Admin_Settings::save_fields( $settings );
    }

    /**
     * Handle toolbar actions
     */
    public function handle_toolbar_actions() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'tradepress_reset_pointers' ) {
            if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'tradepress_reset_pointers' ) || ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'Security check failed.', 'tradepress' ) );
            }
            
            $this->reset_all_pointers();
            
            // Redirect with success message
            wp_redirect( add_query_arg( array(
                'page' => 'tradepress-settings',
                'tab' => 'education',
                'pointers_reset' => '1'
            ), admin_url( 'admin.php' ) ) );
            exit;
        }
        
        // Show success message if redirected from toolbar action
        if ( isset( $_GET['pointers_reset'] ) && $_GET['pointers_reset'] === '1' ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'All pointers have been reset and will show again for all users.', 'tradepress' ) . '</p></div>';
            } );
        }
    }

    /**
     * Reset all WordPress pointers for all users.
     */
    private function reset_all_pointers() {
        global $wpdb;
        
        // Delete dismissed_wp_pointers meta for all users
        $wpdb->delete(
            $wpdb->usermeta,
            array( 'meta_key' => 'dismissed_wp_pointers' ),
            array( '%s' )
        );
    }
}

endif;

return new TradePress_Settings_Education();