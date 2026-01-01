<?php
/**
 * Feature Help Loader
 * 
 * Handles loading and displaying feature help content from markdown files
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Feature_Help_Loader {
    
    /**
     * Load feature help content
     */
    public static function get_feature_help($feature_id) {
        $help_file = TRADEPRESS_PLUGIN_DIR . 'docs/features/' . $feature_id . '.md';
        
        if (!file_exists($help_file)) {
            return false;
        }
        
        $content = file_get_contents($help_file);
        
        // Basic markdown to HTML conversion
        $content = self::convert_markdown_to_html($content);
        
        return $content;
    }
    
    /**
     * Basic markdown to HTML conversion
     */
    private static function convert_markdown_to_html($markdown) {
        // Headers
        $markdown = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $markdown);
        $markdown = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $markdown);
        
        // Code blocks
        $markdown = preg_replace('/```([^`]+)```/s', '<pre><code>$1</code></pre>', $markdown);
        
        // Bold text
        $markdown = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $markdown);
        
        // Line breaks
        $markdown = nl2br($markdown);
        
        return $markdown;
    }
    
    /**
     * AJAX handler for feature help
     */
    public static function ajax_get_feature_help() {
        if (!wp_verify_nonce($_POST['nonce'], 'tradepress_feature_help')) {
            wp_die('Security check failed');
        }
        
        $feature_id = sanitize_text_field($_POST['feature_id']);
        $help_content = self::get_feature_help($feature_id);
        
        if ($help_content) {
            wp_send_json_success($help_content);
        } else {
            wp_send_json_error('Help content not found');
        }
    }
}

// Register AJAX handler
add_action('wp_ajax_tradepress_get_feature_help', array('TradePress_Feature_Help_Loader', 'ajax_get_feature_help'));