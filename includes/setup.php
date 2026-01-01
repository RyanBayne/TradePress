<?php
// ... existing code ...

/**
 * Setup Wizard Class
 */
class TradePress_Setup_Wizard {
    // ... existing code ...
    
    /**
     * Enqueue scripts and styles for the setup wizard
     */
    public function enqueue_scripts() {
        // Updated path to the admin setup CSS
        wp_enqueue_style( 'tradepress-setup', TRADEPRESS_PLUGIN_URL . '/css/admin-setup.css', array( 'dashicons' ), TRADEPRESS_VERSION );
        
        // ... existing code for scripts ...
    }
    
    // ... existing code ...
}

// ... existing code ...
