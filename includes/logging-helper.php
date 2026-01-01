<?php
/**
 * TradePress Logging Helper
 * 
 * Simple static methods for logging to specific files
 * 
 * @package TradePress/Core
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Logging_Helper {
    
    /**
     * Log scoring algorithm results
     */
    public static function log_scoring($strategy_name, $strategy_id = 'unknown', $results = array()) {
        if (get_option('bugnet_output_scoring', 'no') !== 'yes') {
            return;
        }
        
        $message = sprintf('Strategy: %s (ID: %s)', $strategy_name, $strategy_id);
        if (!empty($results)) {
            $message .= ' | Results: ' . json_encode($results);
        }
        
        self::write_log('scoring.log', $message);
    }
    
    /**
     * Log real trading activities
     */
    public static function log_trading($action, $symbol, $details = array()) {
        if (get_option('bugnet_output_trading', 'no') !== 'yes') {
            return;
        }
        
        $message = sprintf('REAL TRADING: %s %s', strtoupper($action), $symbol);
        if (!empty($details)) {
            $message .= ' | ' . json_encode($details);
        }
        
        self::write_log('trading.log', $message);
    }
    
    /**
     * Log paper trading activities
     */
    public static function log_paper($action, $symbol, $details = array()) {
        if (get_option('bugnet_output_paper', 'no') !== 'yes') {
            return;
        }
        
        $message = sprintf('PAPER TRADING: %s %s', strtoupper($action), $symbol);
        if (!empty($details)) {
            $message .= ' | ' . json_encode($details);
        }
        
        self::write_log('paper.log', $message);
    }
    
    /**
     * Log trading platform API calls
     */
    public static function log_calls($platform, $reason, $symbols = array(), $result = 'success') {
        if (get_option('bugnet_output_calls', 'no') !== 'yes') {
            return;
        }
        
        $symbols_str = is_array($symbols) ? implode(', ', $symbols) : $symbols;
        $message = sprintf('%s: %s [%s] - %s', $platform, $reason, $symbols_str, $result);
        
        self::write_log('calls.log', $message);
    }
    
    /**
     * Write to log file
     */
    private static function write_log($filename, $message) {
        $file_path = TRADEPRESS_PLUGIN_DIR_PATH . $filename;
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = sprintf('[%s] %s%s', $timestamp, $message, PHP_EOL);
        
        error_log($log_entry, 3, $file_path);
    }
}