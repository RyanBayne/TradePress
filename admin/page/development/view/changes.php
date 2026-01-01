<?php
/**
 * TradePress Development Changes Tab
 * 
 * Displays changelog from readme.txt file.
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
 * Class TradePress_Admin_Development_Changes
 * 
 * Handles the rendering and functionality of the Changes tab
 * in the Development section.
 */
class TradePress_Admin_Development_Changes {
    
    /**
     * Output the Changes view
     */
    public static function output() {
        $changelog = self::get_changelog();
        ?>
        <div class="tab-content" id="changes">
            <div class="tradepress-changes-container">
                <h2><?php esc_html_e('WordPress Plugin Change Log Entries', 'tradepress'); ?></h2>
                
                <?php if (is_wp_error($changelog)) : ?>
                    <div class="tradepress-notice tradepress-notice-error">
                        <p><?php esc_html_e('Error retrieving changelog:', 'tradepress'); ?> <?php echo esc_html($changelog->get_error_message()); ?></p>
                    </div>
                <?php elseif (empty($changelog)) : ?>
                    <div class="tradepress-notice tradepress-notice-info">
                        <p><?php esc_html_e('No changelog information found.', 'tradepress'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="tradepress-changelog">
                        <?php echo wp_kses_post($changelog); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get changelog from readme.txt
     */
    private static function get_changelog() {
        $readme_path = TRADEPRESS_PLUGIN_DIR . 'readme.txt';
        if (!file_exists($readme_path)) {
            return new WP_Error('missing_file', __('readme.txt file not found.', 'tradepress'));
        }
        $readme_content = file_get_contents($readme_path);
        if (preg_match('/== Changelog ==(.+?)(?:==|$)/s', $readme_content, $matches)) {
            $changelog = trim($matches[1]);
            $changelog = preg_replace('/= (\d+\.\d+\.\d+) =/', '<h4>Version $1</h4>', $changelog);
            $changelog = preg_replace('/\* (.+?)(\n|$)/', '<li>$1</li>', $changelog);
            $changelog = preg_replace('/(<li>.+?<\/li>)+/', '<ul>$0</ul>', $changelog);
            return $changelog;
        }
        return __('No changelog section found in readme.txt', 'tradepress');
    }
}
