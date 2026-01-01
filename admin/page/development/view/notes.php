<?php
/**
 * TradePress Development Notes Tab
 * 
 * Displays development notes from DEVELOPMENT.md file.
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
 * Class TradePress_Admin_Development_Notes
 * 
 * Handles the rendering and functionality of the Notes tab
 * in the Development section.
 */
class TradePress_Admin_Development_Notes {
    
    /**
     * Output the Notes view
     */
    public static function output() {
        $development_notes = self::get_development_notes();
        ?>
        <div class="tab-content" id="notes">
            <div class="tradepress-notes-container">
                <div class="tradepress-notes-header">
                    <h2><?php esc_html_e('Plugin Authors Notes', 'tradepress'); ?></h2>
                    <p><?php esc_html_e('This section contains casual development notes by the plugins author: Ryan.', 'tradepress'); ?></p>
                    <div class="tradepress-notes-actions">
                        <a href="#" id="download-notes" class="button">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Download', 'tradepress'); ?>
                        </a>
                    </div>
                </div>
                
                <?php if (is_wp_error($development_notes)) : ?>
                    <div class="tradepress-notice tradepress-notice-error">
                        <p><?php esc_html_e('Error retrieving development notes:', 'tradepress'); ?> <?php echo esc_html($development_notes->get_error_message()); ?></p>
                    </div>
                <?php elseif (empty($development_notes)) : ?>
                    <div class="tradepress-notice tradepress-notice-info">
                        <p><?php esc_html_e('No development notes found.', 'tradepress'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="development-notes-content" id="development-notes-content">
                        <?php echo wp_kses_post(self::markdown_to_html($development_notes)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get development notes from DEVELOPMENT.md
     */
    private static function get_development_notes() {
        $development_path = TRADEPRESS_PLUGIN_DIR . 'docs/DEVELOPMENT-NOTES.md';
        if (!file_exists($development_path)) {
            return new WP_Error('missing_file', __('DEVELOPMENT-NOTES.md.', 'tradepress'));
        }
        return file_get_contents($development_path);
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
