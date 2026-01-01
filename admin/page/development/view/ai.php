<?php
/**
 * TradePress Development AI Tab
 * 
 * Displays AI assistant guidelines from AI.md and GEMINI.md files.
 * 
 * @package TradePress\Admin\development
 * @version 1.0.0
 * @since 1.0.0
 * @created 2024-01-15
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TradePress_Admin_Development_AI
 * 
 * Handles the rendering and functionality of the AI tab
 * in the Development section.
 */
class TradePress_Admin_Development_AI {
    
    /**
     * Output the AI view
     */
    public static function output() {
        $ai_notes = self::get_ai_notes();
        ?>
        <div class="tab-content" id="ai">
            <div class="tradepress-ai-container">
                <div class="ai-tabs">
                    <a href="#" class="ai-tab active" data-tab="ai-md"><?php esc_html_e('AI.md', 'tradepress'); ?></a>
                </div>
                
                <div class="ai-content-wrapper">
                    <div id="ai-md-content" class="ai-content active">
                        <div class="tradepress-ai-header">
                            <h2><?php esc_html_e('AI Assistant Guidelines', 'tradepress'); ?></h2>
                            
                            <div class="tradepress-ai-actions">
                                <a href="#" id="download-ai-md" class="button">
                                    <span class="dashicons dashicons-download"></span>
                                    <?php esc_html_e('Download', 'tradepress'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (is_wp_error($ai_notes)) : ?>
                            <div class="tradepress-notice tradepress-notice-error">
                                <p><?php esc_html_e('Error retrieving AI notes:', 'tradepress'); ?> <?php echo esc_html($ai_notes->get_error_message()); ?></p>
                            </div>
                        <?php elseif (empty($ai_notes)) : ?>
                            <div class="tradepress-notice tradepress-notice-info">
                                <p><?php esc_html_e('No AI notes found.', 'tradepress'); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="ai-notes-content">
                                <?php echo wp_kses_post(self::markdown_to_html($ai_notes)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get AI notes from AI.md
     */
    private static function get_ai_notes() {
        $ai_path = TRADEPRESS_PLUGIN_DIR . 'docs/AI.md';
        if (!file_exists($ai_path)) {
            return new WP_Error('missing_file', __('AI.md file not found.', 'tradepress'));
        }
        return file_get_contents($ai_path);
    }
    
    /**
     * Convert markdown to HTML
     */
    private static function markdown_to_html($markdown) {
        $html = $markdown;
        $html = preg_replace('/^# (.+?)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^## (.+?)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^### (.+?)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html);
        $html = preg_replace('/^- (.+?)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.+?<\/li>)+/', '<ul>$0</ul>', $html);
        $html = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $html);
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);
        $html = preg_replace('/^(?!<[a-z]).+?$/m', '<p>$0</p>', $html);
        return $html;
    }
}
