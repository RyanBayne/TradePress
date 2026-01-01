<?php
/**
 * TradePress Symbol Update CRON
 *
 * CRON job for updating symbol data
 *
 * @package TradePress
 * @subpackage Includes\Cron
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Symbol_Update_Cron {
    
    public function __construct() {
        add_action('tradepress_update_symbols', array($this, 'process_symbol_batch'));
    }
    
    /**
     * Schedule regular symbol updates
     */
    public function schedule_symbol_updates() {
        if (!wp_next_scheduled('tradepress_update_symbols')) {
            wp_schedule_event(time(), 'hourly', 'tradepress_update_symbols');
            
            $this->update_cron_meta('symbol_updates', array(
                'job_status' => 'active',
                'next_run' => date('Y-m-d H:i:s', wp_next_scheduled('tradepress_update_symbols'))
            ));
        }
    }
    
    /**
     * Process batch of symbols
     *
     * @param array $symbols
     */
    public function process_symbol_batch($symbols = null) {
        $this->update_cron_meta('symbol_updates', array(
            'job_status' => 'running',
            'last_run' => current_time('mysql')
        ));
        
        if (!$symbols) {
            $symbols = TradePress_Symbols::get_symbols(array(
                'active' => true,
                'limit' => 10
            ));
        }
        
        $processed = 0;
        foreach ($symbols as $symbol_obj) {
            if ($this->update_single_symbol($symbol_obj->get_symbol())) {
                $processed++;
            }
        }
        
        $this->update_cron_meta('symbol_updates', array(
            'job_status' => 'completed',
            'run_count' => $this->get_run_count() + 1
        ));
        
        return $processed;
    }
    
    /**
     * Update individual symbol
     *
     * @param string $symbol
     * @return bool
     */
    public function update_single_symbol($symbol) {
        try {
            $symbol_obj = TradePress_Symbols::get_symbol($symbol);
            if (!$symbol_obj) {
                $symbol_obj = new TradePress_Symbol($symbol);
            }
            return $symbol_obj->update_from_api('alphavantage');
        } catch (Exception $e) {
            $this->log_error($symbol, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cleanup stale data
     */
    public function cleanup_stale_data() {
        global $wpdb;
        
        // Remove old API call records (older than 30 days)
        $api_table = $wpdb->prefix . 'tradepress_api_calls';
        $wpdb->query("DELETE FROM $api_table WHERE call_time < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        // Remove old cache entries
        $cache_table = $wpdb->prefix . 'tradepress_cache_meta';
        $wpdb->query("DELETE FROM $cache_table WHERE expires_at < NOW()");
    }
    
    /**
     * Update CRON metadata
     *
     * @param string $job_name
     * @param array $data
     */
    private function update_cron_meta($job_name, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_cron_meta';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE job_name = %s",
            $job_name
        ));
        
        if ($existing) {
            $wpdb->update($table_name, $data, array('id' => $existing));
        } else {
            $data['job_name'] = $job_name;
            $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Get current run count
     *
     * @return int
     */
    private function get_run_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_cron_meta';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT run_count FROM $table_name WHERE job_name = %s",
            'symbol_updates'
        ));
        
        return intval($count);
    }
    
    /**
     * Log error
     *
     * @param string $symbol
     * @param string $message
     */
    private function log_error($symbol, $message) {
        global $wpdb;
        
        $cron_table = $wpdb->prefix . 'tradepress_cron_meta';
        $wpdb->update(
            $cron_table,
            array(
                'error_count' => $this->get_error_count() + 1,
                'last_error' => "Symbol: $symbol - $message"
            ),
            array('job_name' => 'symbol_updates')
        );
    }
    
    /**
     * Get current error count
     *
     * @return int
     */
    private function get_error_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tradepress_cron_meta';
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT error_count FROM $table_name WHERE job_name = %s",
            'symbol_updates'
        ));
        
        return intval($count);
    }
}