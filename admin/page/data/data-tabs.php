<?php
/**
 * TradePress - Admin Data Area
 *
 * Handles the Data tabs including API Keys, Data Import, etc.
 *
 * @author   TradePress
 * @category Admin
 * @package  TradePress/Admin
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Admin_Data_Tabs' ) ) :

/**
 * TradePress_Admin_Data_Tabs Class.
 */
class TradePress_Admin_Data_Tabs {

    /**
     * Current active tab.
     *
     * @var string
     */
    private $active_tab = 'sources';

    /**
     * Constructor.
     */
    public function __construct() {
        if ( isset( $_GET['tab'] ) ) {
            $tab = sanitize_text_field( $_GET['tab'] );
            $valid_tabs = array_keys( $this->get_tabs() );
            $this->active_tab = in_array( $tab, $valid_tabs, true ) ? $tab : 'sources';
        }
    }

    /**
     * Get tabs for the data area.
     *
     * @return array
     */
    public function get_tabs() {
        $tabs = array(
            'sources'     => __( 'Sources', 'tradepress' ),
            'data-elements' => __( 'Data Elements', 'tradepress' ),
            'import'      => __( 'Manual Import', 'tradepress' ),
            'decoder'     => __( 'Decoder', 'tradepress' ),
            'api-keys'    => __( 'API Keys', 'tradepress' ),
            'tables'      => __( 'Tables', 'tradepress' ),
            'api-activity' => __( 'API Activity', 'tradepress' ),
            'api-endpoints' => __( 'API Endpoints', 'tradepress' ),
            'api-errors'  => __( 'API Errors', 'tradepress' ),
            'transient-caches' => __( 'Transient Caches', 'tradepress' ),
            'sources_list' => __( 'Sources List', 'tradepress' ),
            'create_source' => __( 'Create Source', 'tradepress' )
        );
        
        return apply_filters( 'tradepress_data_tabs', $tabs );
    }

    /**
     * Output the data area.
     */
    public static function output() {
        // Create an instance of the class
        $instance = new self();
        $tabs = $instance->get_tabs();
        $active_tab = $instance->active_tab;
        
        ?>
        <div class="wrap tradepress-admin">
            <h1>
                <?php 
                echo esc_html__( 'TradePress Data', 'tradepress' );
                if (isset($tabs[$active_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($tabs[$active_tab]);
                }
                ?>
            </h1>
            
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_id => $tab_name ) : 
                    $active = ( $active_tab === $tab_id ) ? 'nav-tab-active' : '';
                    $url = admin_url( 'admin.php?page=tradepress_data&tab=' . $tab_id );
                ?>
                    <a href="<?php echo esc_url( $url ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>"><?php echo esc_html( $tab_name ); ?></a>
                <?php endforeach; ?>
            </nav>
            
            <div class="tradepress-data-content">
                <?php 
                    // Load the active tab content
                    $instance->load_tab_content( $active_tab );
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Load tab content based on the active tab.
     *
     * @param string $tab Tab to load.
     */
    private function load_tab_content( $tab ) {
        switch ( $tab ) {
            case 'sources':
                $this->sources_tab();
                break;
            case 'data-elements':
                $this->data_elements_tab();
                break;
            case 'import':
                $this->import_tab();
                break;
            case 'decoder':
                $this->decoder_tab();
                break;
            case 'api-keys':
                $this->api_keys_tab();
                break;
            case 'tables':
                tradepress_display_tables_tab_content();
                break;
            case 'api-activity':
                tradepress_display_api_activity_tab_content();
                break;
            case 'api-endpoints':
                tradepress_display_api_endpoints_tab_content();
                break;
            case 'api-errors':
                tradepress_display_api_errors_tab_content();
                break;
            case 'transient-caches':
                tradepress_display_transient_caches_tab_content();
                break;
            case 'sources_list':
                tradepress_display_sources_list_tab_content();
                break;
            case 'create_source':
                tradepress_display_create_source_tab_content();
                break;
            default:
                $this->sources_tab();
                break;
        }
    }

    /**
     * Sources tab content
     */
    public function sources_tab() {
        $sources_view = dirname(__FILE__) . '/view/manage-sources.php';
        if (file_exists($sources_view)) {
            try {
                require_once($sources_view);
                if (class_exists('TradePress_Manage_Sources')) {
                    $manage_sources = new TradePress_Manage_Sources();
                    $manage_sources->render_sources_list();
                }
            } catch ( Exception $e ) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Error loading sources view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
            }
        } else {
            echo '<p>' . esc_html__('Manage sources view not found.', 'tradepress') . '</p>';
        }
    }

    /**
     * Import tab content
     */
    public function import_tab() {
        $import_view = dirname(__FILE__) . '/view/import.php';
        if (file_exists($import_view)) {
            try {
                include($import_view);
            } catch ( Exception $e ) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Error loading import view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
            }
        } else {
            echo '<p>' . esc_html__('Import view not found.', 'tradepress') . '</p>';
        }
    }

    /**
     * Decoder tab content
     */
    public function decoder_tab() {
        $decoder_view = dirname(__FILE__) . '/view/decoder.php';
        if (file_exists($decoder_view)) {
            try {
                require_once($decoder_view);
                if (class_exists('TradePress_Data_Decoder_Tab')) {
                    TradePress_Data_Decoder_Tab::output();
                }
            } catch ( Exception $e ) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Error loading decoder view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
            }
        } else {
            echo '<p>' . esc_html__('Decoder view not found.', 'tradepress') . '</p>';
        }
    }

    /**
     * Data Elements tab content
     */
    public function data_elements_tab() {
        $data_elements_view = dirname(__FILE__) . '/data-elements.php';
        if (file_exists($data_elements_view)) {
            try {
                require_once($data_elements_view);
                tradepress_data_elements_tab();
            } catch ( Exception $e ) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Error loading data elements view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
            }
        } else {
            echo '<p>' . esc_html__('Data Elements view not found.', 'tradepress') . '</p>';
        }
    }

    /**
     * API Keys tab content
     */
    public function api_keys_tab() {
        $api_keys_view = dirname(__FILE__) . '/view/api-keys.php';
        if (file_exists($api_keys_view)) {
            try {
                include($api_keys_view);
            } catch ( Exception $e ) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Error loading API keys view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
            }
        } else {
            echo '<p>' . esc_html__('API Keys view not found.', 'tradepress') . '</p>';
        }
    }
}

endif;
add_action('tradepress_data_create_source_tab', 'tradepress_display_create_source_tab_content');

// Register the Decoder tab action
add_action('tradepress_data_decoder_tab', 'tradepress_display_decoder_tab_content');

/**
 * Wrapper function to load the tables tab content without redeclaring the main function
 */
function tradepress_display_tables_tab_content() {
    // Include the tables-tab.php file if it exists
    $tables_tab_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/tables.php';
    
    if (file_exists($tables_tab_file)) {
        try {
            include_once($tables_tab_file);
            
            // Call the function if it exists and hasn't been called yet
            if (function_exists('tradepress_data_tables_tab_content') && !function_exists('tradepress_display_tables_tab_content_called')) {
                // Define a flag function to prevent recursion
                function tradepress_display_tables_tab_content_called() {}
                
                // Call the actual function
                tradepress_data_tables_tab_content();
            }
        } catch ( Exception $e ) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Error loading tables view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Tables tab file not found. Please check the file path: ', 'tradepress') . esc_html($tables_tab_file);
        echo '</p></div>';
    }
}

/**
 * Wrapper function to load the API Activity tab content
 */
function tradepress_display_api_activity_tab_content() {
    try {
        // Include the API Activity tab file
        include_once(TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/api-activity.php');
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading API activity view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Wrapper function to load the API Endpoints tab content
 */
function tradepress_display_api_endpoints_tab_content() {
    try {
        // Include the API Endpoints tab file
        include_once(TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/api-endpoints.php');
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading API endpoints view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Wrapper function to load the API Errors tab content
 */
function tradepress_display_api_errors_tab_content() {
    try {
        // Include the API Errors tab file
        include_once(TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/api-errors.php');
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading API errors view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Wrapper function to load the Transient Caches tab content
 */
function tradepress_display_transient_caches_tab_content() {
    try {
        // Include the Transient Caches tab file
        include_once(TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/transient-caches.php');
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading transient caches view: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Wrapper function to load the sources_list tab content
 */
function tradepress_display_sources_list_tab_content() {
    try {
        // Initialize the Manage Sources class
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/manage-sources.php';
        $manage_sources = new TradePress_Manage_Sources();
        $manage_sources->init();
        
        // Display the sources list view
        $manage_sources->render_sources_list();
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading sources list: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Wrapper function to load the create_source tab content
 */
function tradepress_display_create_source_tab_content() {
    try {
        // Initialize the Manage Sources class
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/data/view/manage-sources.php';
        $manage_sources = new TradePress_Manage_Sources();
        $manage_sources->init();
        
        // Check if we're editing an existing source
        $source_id = isset($_GET['source_id']) ? intval(sanitize_text_field($_GET['source_id'])) : 0;
        
        // Display the create/edit source form
        $manage_sources->render_source_form($source_id);
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading create source form: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Output the Decoder tab content
 */
function tradepress_display_decoder_tab_content() {
    try {
        if (!class_exists('TradePress_Data_Decoder_Tab')) {
            $decoder_tab_file = TRADEPRESS_PLUGIN_DIR . 'admin/page/data/data-decoder.php';
            if (file_exists($decoder_tab_file)) {
                include_once($decoder_tab_file);
            }
        }
        if (class_exists('TradePress_Data_Decoder_Tab')) {
            TradePress_Data_Decoder_Tab::output();
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Decoder tab class not found.', 'tradepress') . '</p></div>';
        }
    } catch ( Exception $e ) {
        echo '<div class="notice notice-error"><p>' . esc_html__('Error loading decoder tab: ', 'tradepress') . esc_html($e->getMessage()) . '</p></div>';
    }
}

/**
 * Initialize the TradePress Admin Data Tabs
 */
function tradepress_init_admin_data_tabs() {
    // Add the tabs
    if ( function_exists( 'tradepress_register_admin_data_tabs' ) ) {
        add_filter('tradepress_admin_data_tabs', 'tradepress_register_admin_data_tabs');
    }
}