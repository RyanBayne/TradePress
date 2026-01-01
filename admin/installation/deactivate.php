<?php       

if ( ! defined( 'ABSPATH' ) ) {
    throw new Exception('Direct access not allowed');
}

if( !class_exists( 'TradePress_Admin_Deactivate' ) ) : 

/**
 * TradePress_Admin_Deactivate Class
 * 
 * Handles the deactivation process for the TradePress plugin.
 * 
 * @version 1.0.1
 */
class TradePress_Admin_Deactivate {
    
    public function __construct() {
        // Include the settings class with corrected path
        $form_fields_path = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/admin/form-fields.php';
        if (file_exists($form_fields_path)) {
            include_once $form_fields_path;
        }
    }
    
    /**
    * Called when Deactive is clicked on the Plugins view. 
    * 
    * This is not the uninstallation but some level of cleanup can be run here. 
    * 
    * @version 1.0
    */
    public static function deactivate() {
        
    }
      
}

endif;
