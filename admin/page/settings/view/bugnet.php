<?php
/**
 * TradePress BugNet Settings View
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TradePress
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TradePress_Settings_BugNet' ) ) :

/**
 * TradePress_Settings_BugNet.
 */
class TradePress_Settings_BugNet extends TradePress_Settings_Page {

    /**
     * Output sections for this tab.
     */
    public function output_sections() {
        global $current_section;
        
        $sections = $this->get_sections();
        
        if ( empty( $sections ) || 1 === count( $sections ) ) {
            return;
        }

        echo '<ul class="subsubsub">';
        
        $section_keys = array_keys( $sections );
        
        foreach ( $sections as $id => $label ) {
            echo '<li><a href="' . admin_url( 'admin.php?page=TradePress&tab=bugnet&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $section_keys ) === $id ? '' : '|' ) . ' </li>';
        }
        
        echo '</ul><br class="clear" />';
    }

    /**
     * Constructor.
     */
    public function __construct() {
        global $current_section;
        
        $this->id    = 'bugnet';
        $this->label = __( 'BugNet', 'tradepress' );
        
        add_filter( 'TradePress_settings_save_button_text', array( $this, 'custom_save_button' ), 1 );
        add_filter( 'TradePress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'TradePress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'TradePress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'TradePress_sections_' . $this->id, array( $this, 'output_sections' ) );
    }
    
    /**
    * Filter the save button text when on the BugNet tab
    * 
    * @param mixed $text original button text i.e. "Save changes"
    * @version 1.0
    */
    public function custom_save_button( $text ) {
        return $text;
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'settings'  => __( 'Settings', 'tradepress' ),
            'debug'     => __( 'Debug Log', 'tradepress' ),
            'trace'     => __( 'Trace Log', 'tradepress' ),
            'users'     => __( 'Users Log', 'tradepress' ),
            'scoring'   => __( 'Scoring Log', 'tradepress' ),
            'trading'   => __( 'Trading Log', 'tradepress' ),
            'paper'     => __( 'Paper Log', 'tradepress' ),
            'calls'     => __( 'Calls Log', 'tradepress' ),
            'ai'        => __( 'AI Log', 'tradepress' ),
            'automation' => __( 'Automation Log', 'tradepress' ),
            'directives' => __( 'Directives Log', 'tradepress' ),
            'alpaca' => __( 'Alpaca API Log', 'tradepress' ),
            'alphavantage' => __( 'Alpha Vantage API Log', 'tradepress' ),
        );

        // Allow other components to add their own log sections
        $sections = apply_filters( 'tradepress_bugnet_log_sections', $sections );

        return apply_filters( 'TradePress_get_sections_' . $this->id, $sections );
    }

    /**
     * Output the settings.
     */
    public function output() {
        global $current_section;
        
        // Show success message if logs were cleared
        if ( isset( $_GET['cleared'] ) && $_GET['cleared'] === '1' ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Selected log files have been cleared.', 'tradepress' ) . '</p></div>';
        }
        
        // Default to settings if no section specified or if section is 'default'
        if ( empty( $current_section ) || $current_section === 'default' ) {
            $current_section = 'settings';
        }
        
        if ( in_array( $current_section, array( 'debug', 'trace', 'users', 'scoring', 'trading', 'paper', 'calls', 'ai', 'automation', 'directives', 'alpaca', 'alphavantage' ) ) ) {
            $this->output_log_viewer( $current_section );
        } else {
            $settings = $this->get_settings( $current_section );
            
            // Custom handling for bugnet output selection
            foreach ( $settings as $setting ) {
                if ( isset( $setting['type'] ) && $setting['type'] === 'bugnet_output_selection' ) {
                    $this->output_bugnet_selection();
                } else {
                    TradePress_Admin_Settings::output_fields( array( $setting ) );
                }
            }
        }
    }

    /**
     * Save settings...
     */
    public function save() {
        global $current_section;
        
        // Log clear logs request
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'clear_logs' ) {
            error_log( '[' . current_time( 'Y-m-d H:i:s' ) . '] TradePress BugNet: Clear logs request received' . "\n", 3, WP_CONTENT_DIR . '/users.log' );
        }
        
        // Handle clear logs action
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'clear_logs' && wp_verify_nonce( $_GET['nonce'], 'tradepress_clear_logs' ) ) {
            error_log( '[' . current_time( 'Y-m-d H:i:s' ) . '] TradePress BugNet: Nonce verified, clearing selected logs' . "\n", 3, WP_CONTENT_DIR . '/users.log' );
            $this->clear_selected_logs();
            wp_redirect( admin_url( 'admin.php?page=TradePress&tab=bugnet&cleared=1' ) );
            exit;
        }
        
        // Default to settings if no section specified or if section is 'default'
        if ( empty( $current_section ) || $current_section === 'default' ) {
            $current_section = 'settings';
        }
        
        // Only save if on settings section
        if ( $current_section === 'settings' ) {
            $settings = $this->get_settings( $current_section );
            TradePress_Admin_Settings::save_fields( $settings );
            
            // Save bugnet output checkboxes
            $outputs = array( 'errors', 'console', 'traces', 'users', 'scoring', 'trading', 'paper', 'calls', 'ai', 'automation', 'directives', 'alpaca', 'alphavantage' );
            foreach ( $outputs as $output ) {
                $option_name = 'bugnet_output_' . $output;
                $value = isset( $_POST[ $option_name ] ) ? 'yes' : 'no';
                update_option( $option_name, $value );
            }
            
            // Create log files if outputs are enabled
            $this->create_log_files();
        }
    }
    
    /**
     * Create log files for enabled outputs
     */
    private function create_log_files() {
        $log_dir = WP_CONTENT_DIR;
        $created_files = array();
        
        // Check which outputs are enabled and create corresponding log files
        $outputs = array(
            'bugnet_output_traces' => 'trace.log',
            'bugnet_output_users' => 'users.log',
            'bugnet_output_scoring' => 'scoring.log',
            'bugnet_output_trading' => 'trading.log',
            'bugnet_output_paper' => 'paper.log',
            'bugnet_output_calls' => 'calls.log',
            'bugnet_output_ai' => 'ai.log',
            'bugnet_output_automation' => 'automation.log',
            'bugnet_output_directives' => 'directives.log',
            'bugnet_output_alpaca' => 'alpaca.log',
            'bugnet_output_alphavantage' => 'alphavantage.log'
        );
        
        foreach ( $outputs as $option => $filename ) {
            if ( get_option( $option ) === 'yes' ) {
                $file_path = $log_dir . '/' . $filename;
                
                // Create file if it doesn't exist
                if ( ! file_exists( $file_path ) ) {
                    $initial_content = "[" . current_time( 'Y-m-d H:i:s' ) . "] TradePress BugNet: Log file created\n";
                    file_put_contents( $file_path, $initial_content );
                    $created_files[] = $filename;
                }
            }
        }
        
        // Show admin notice if files were created
        if ( ! empty( $created_files ) ) {
            $message = sprintf(
                'BugNet log files created: %s in directory: %s',
                implode( ', ', $created_files ),
                $log_dir
            );
            
            add_action( 'admin_notices', function() use ( $message ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            });
        }
    }

    /**
     * Output log viewer
     */
    private function output_log_viewer( $log_type ) {
        $log_files = array(
            'debug' => 'debug.log',
            'trace' => 'trace.log',
            'users' => 'users.log',
            'scoring' => 'scoring.log',
            'trading' => 'trading.log',
            'paper' => 'paper.log',
            'calls' => 'calls.log',
            'ai' => 'ai.log',
            'automation' => 'automation.log',
            'directives' => 'directives.log',
            'alpaca' => 'alpaca.log',
            'alphavantage' => 'alphavantage.log'
        );
        
        // Allow other components to add their own log files
        $log_files = apply_filters( 'tradepress_bugnet_log_files', $log_files );
        
        $filename = $log_files[ $log_type ];
        $file_path = WP_CONTENT_DIR . '/' . $filename;
        
        echo '<div class="bugnet-log-viewer">';
        echo '<h3>' . esc_html( ucfirst( $log_type ) . ' Log' ) . '</h3>';
        
        if ( file_exists( $file_path ) ) {
            $content = file_get_contents( $file_path );
            $file_size = filesize( $file_path );
            $file_size_formatted = size_format( $file_size );
            
            if ( ! empty( $content ) ) {
                $line_count = substr_count( $content, "\n" ) + 1;
                echo '<textarea readonly style="width:100%;height:400px;font-family:monospace;font-size:12px;">' . esc_textarea( $content ) . '</textarea>';
                echo '<p><strong>File:</strong> ' . esc_html( $file_path ) . ' | <strong>Size:</strong> ' . esc_html( $file_size_formatted ) . ' | <strong>Lines:</strong> ' . esc_html( $line_count ) . '</p>';
            } else {
                echo '<p>Log file is empty (Size: ' . esc_html( $file_size_formatted ) . ').</p>';
            }
        } else {
            echo '<p>Log file does not exist. Enable the corresponding output in Settings to create it.</p>';
        }
        
        echo '</div>';
    }
    
    /**
     * Clear selected log files
     */
    private function clear_selected_logs() {
        $log_mapping = array(
            'bugnet_output_errors' => 'debug.log',
            'bugnet_output_traces' => 'trace.log',
            'bugnet_output_users' => 'users.log',
            'bugnet_output_scoring' => 'scoring.log',
            'bugnet_output_trading' => 'trading.log',
            'bugnet_output_paper' => 'paper.log',
            'bugnet_output_calls' => 'calls.log',
            'bugnet_output_ai' => 'ai.log',
            'bugnet_output_automation' => 'automation.log',
            'bugnet_output_directives' => 'directives.log',
            'bugnet_output_alpaca' => 'alpaca.log',
            'bugnet_output_alphavantage' => 'alphavantage.log'
        );
        
        $cleared_count = 0;
        
        foreach ( $log_mapping as $option => $filename ) {
            if ( get_option( $option ) === 'yes' ) {
                $file_path = WP_CONTENT_DIR . '/' . $filename;
                if ( file_exists( $file_path ) ) {
                    if ( @unlink( $file_path ) ) {
                        $cleared_count++;
                    }
                }
            }
        }
        
        return $cleared_count;
    }
    
    /**
     * Output custom bugnet selection field
     */
    public function output_bugnet_selection() {
        $outputs = array(
            'errors' => array(
                'title' => __( 'Errors (debug.log)', 'tradepress' ),
                'description' => __( 'PHP errors, warnings, and critical issues. Essential for troubleshooting.', 'tradepress' ),
                'default' => 'yes'
            ),
            'console' => array(
                'title' => __( 'Console (scripts/css)', 'tradepress' ),
                'description' => __( 'JavaScript console output and CSS debugging information.', 'tradepress' ),
                'default' => 'no'
            ),
            'traces' => array(
                'title' => __( 'Traces (trace.log)', 'tradepress' ),
                'description' => __( 'Function execution traces and stack traces for detailed debugging.', 'tradepress' ),
                'default' => 'no'
            ),
            'users' => array(
                'title' => __( 'Users (users.log)', 'tradepress' ),
                'description' => __( 'User actions, login attempts, and user-related events.', 'tradepress' ),
                'default' => 'no'
            ),
            'scoring' => array(
                'title' => __( 'Scoring (scoring.log)', 'tradepress' ),
                'description' => __( 'Scoring algorithm results, strategy names and IDs.', 'tradepress' ),
                'default' => 'no'
            ),
            'trading' => array(
                'title' => __( 'Trading (trading.log)', 'tradepress' ),
                'description' => __( 'Real trading activities and order executions.', 'tradepress' ),
                'default' => 'no'
            ),
            'paper' => array(
                'title' => __( 'Paper (paper.log)', 'tradepress' ),
                'description' => __( 'Paper trading activities and simulated orders.', 'tradepress' ),
                'default' => 'no'
            ),
            'calls' => array(
                'title' => __( 'Calls (calls.log)', 'tradepress' ),
                'description' => __( 'Trading platform API calls with symbols and results.', 'tradepress' ),
                'default' => 'no'
            ),
            'ai' => array(
                'title' => __( 'AI Debug (ai.log)', 'tradepress' ),
                'description' => __( 'AI assistant debugging and development workflow tracking.', 'tradepress' ),
                'default' => 'no'
            ),
            'automation' => array(
                'title' => __( 'Automation (automation.log)', 'tradepress' ),
                'description' => __( 'CRON jobs, async processes, JavaScript polling, and Ajax requests.', 'tradepress' ),
                'default' => 'no'
            ),
            'directives' => array(
                'title' => __( 'Directives (directives.log)', 'tradepress' ),
                'description' => __( 'Directive testing results, calculations, and validation data.', 'tradepress' ),
                'default' => 'no'
            ),
            'alpaca' => array(
                'title' => __( 'Alpaca API (alpaca.log)', 'tradepress' ),
                'description' => __( 'Alpaca API calls, responses, and trading activities.', 'tradepress' ),
                'default' => 'no'
            ),
            'alphavantage' => array(
                'title' => __( 'Alpha Vantage API (alphavantage.log)', 'tradepress' ),
                'description' => __( 'Alpha Vantage API calls, responses, and market data requests.', 'tradepress' ),
                'default' => 'no'
            )
        );
        
        echo '<div class="bugnet-output-selection">';
        foreach ( $outputs as $output_key => $output_info ) {
            $is_enabled = get_option( 'bugnet_output_' . $output_key, $output_info['default'] ) === 'yes';
            ?>
            <div class="output-option">
                <label>
                    <input type="checkbox" name="bugnet_output_<?php echo esc_attr( $output_key ); ?>" value="yes" <?php checked( $is_enabled ); ?>>
                    <strong><?php echo esc_html( $output_info['title'] ); ?></strong>
                    <p><?php echo esc_html( $output_info['description'] ); ?></p>
                </label>
            </div>
            <?php
        }
        echo '</div>';
        
        // Add CSS
        ?>
        <style>
        .bugnet-output-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .output-option {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background: #f9f9f9;
        }
        .output-option label {
            display: block;
            cursor: pointer;
        }
        .output-option input[type="checkbox"] {
            margin-right: 10px;
        }
        .output-option strong {
            display: block;
            margin-bottom: 5px;
        }
        .output-option p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }
        </style>
        <?php
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings( $current_section = 'settings' ) {
        $settings = array();
                            
        if ( 'settings' == $current_section ) {
            
            $settings = apply_filters( 'TradePress_bugnet_settings', array(
            
                array(
                    'title'    => __( 'Debug Depth Level', 'tradepress' ),
                    'desc'     => __( 'Number of stack trace levels to capture (1-10)', 'tradepress' ),
                    'id'       => 'bugnet_depth_level',
                    'css'      => 'width:75px;',
                    'default'  => '3',
                    'type'     => 'number',
                    'custom_attributes' => array(
                        'min' => 1,
                        'max' => 10
                    )
                ),

                array(
                    'type' => 'bugnet_output_selection',
                    'id'   => 'bugnet_outputs'
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'bugnet_output_types_end'
                ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'TradePress_bugnet_output_switches'
                ),

            ));
        } 
                                   
        return apply_filters( 'TradePress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TradePress_Settings_BugNet();