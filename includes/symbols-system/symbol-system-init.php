<?php
/**
 * TradePress Symbol System Initialization
 *
 * Initialize the symbol management system
 *
 * @package TradePress
 * @subpackage SymbolsSystem
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Symbol_System_Init {
    
    public function __construct() {
        add_action('init', array($this, 'init_symbol_system'));
        add_action('wp_ajax_tradepress_update_symbols_manual', array($this, 'manual_symbol_update'));
        add_action('wp_ajax_tradepress_test_symbol_fetch', array($this, 'test_symbol_fetch'));
    }
    
    /**
     * Initialize symbol system
     */
    public function init_symbol_system() {
        // Load required classes
        require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbol.php';
        require_once TRADEPRESS_PLUGIN_DIR . 'classes/symbols.php';
        require_once TRADEPRESS_PLUGIN_DIR . 'symbols-system/symbol-data-fetcher.php';
        require_once TRADEPRESS_PLUGIN_DIR . 'symbols-system/symbol-update-cron.php';
        
        // Initialize CRON job
        $cron = new TradePress_Symbol_Update_Cron();
        $cron->schedule_symbol_updates();
    }
    
    /**
     * Manual symbol update via AJAX
     */
    public function manual_symbol_update() {
        check_ajax_referer('tradepress_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $symbol = sanitize_text_field($_POST['symbol'] ?? '');
        
        if (empty($symbol)) {
            wp_send_json_error('Symbol required');
        }
        
        // Use existing symbol class
        $symbol_obj = TradePress_Symbols::get_symbol($symbol);
        
        if (!$symbol_obj) {
            // Create new symbol if it doesn't exist
            $symbol_obj = new TradePress_Symbol($symbol);
        }
        
        $result = $symbol_obj->update_from_api('alphavantage');
        
        if ($result) {
            wp_send_json_success("Symbol $symbol updated successfully");
        } else {
            wp_send_json_error("Failed to update symbol $symbol");
        }
    }
    
    /**
     * Test symbol fetch via AJAX
     */
    public function test_symbol_fetch() {
        check_ajax_referer('tradepress_admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $symbol = sanitize_text_field($_POST['symbol'] ?? 'AAPL');
        
        // Test symbol fetch using existing classes
        $symbol_obj = TradePress_Symbols::get_symbol($symbol);
        
        if (!$symbol_obj) {
            $symbol_obj = new TradePress_Symbol($symbol);
        }
        
        $complete_data = $symbol_obj->get_complete_data(false);
        
        wp_send_json_success(array(
            'symbol' => $symbol,
            'data' => $complete_data,
            'message' => 'Symbol data retrieved successfully'
        ));
    }
}

// Initialize the system
new TradePress_Symbol_System_Init();