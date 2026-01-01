<?php
/**
 * Feature Feedback System Foundation Class
 * 
 * @package TradePress/Feedback
 * @version 1.0.0
 * 
 * @todo Expand to support multiple UI contexts (directives, strategies, general)
 * @todo Integrate with GitHub API for issue creation
 * @todo Add email notifications for critical issues
 * @todo Implement feedback analytics and reporting
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Feature_Feedback_System {
    
    /**
     * Feature status constants
     * 
     * @todo Implement full status checking across all features
     * Available = Feature exists and is functional
     * Used = Feature is configured in active strategies  
     * Unused = Feature exists but not in any strategies
     * Active = Feature has been executed recently
     * Disabled = User has disabled due to issues
     * Caution = User wants warnings before use
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_USED = 'used';
    const STATUS_UNUSED = 'unused'; 
    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';
    const STATUS_CAUTION = 'caution';
    
    /**
     * Priority levels matching GitHub issue priorities
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';
    
    /**
     * Generate feature issue form
     * 
     * @param string $feature_type Type of feature (directive, strategy, etc)
     * @param string $feature_id Unique identifier for the feature
     * @param array $options Form configuration options
     * @return string HTML form
     */
    public static function render_issue_form($feature_type, $feature_id, $options = array()) {
        $defaults = array(
            'show_priority' => true,
            'show_disable_option' => true,
            'context_label' => ucfirst($feature_type),
            'submit_text' => 'Report Issue'
        );
        
        $options = array_merge($defaults, $options);
        
        ob_start();
        ?>
        <div class="feature-issue-form">
            <form method="post" class="issue-report-form">
                <?php wp_nonce_field('tradepress_feature_issue', 'issue_nonce'); ?>
                <input type="hidden" name="action" value="report_feature_issue">
                <input type="hidden" name="feature_type" value="<?php echo esc_attr($feature_type); ?>">
                <input type="hidden" name="feature_id" value="<?php echo esc_attr($feature_id); ?>">
                
                <div class="form-group">
                    <label for="issue_description"><?php esc_html_e('Issue Description:', 'tradepress'); ?></label>
                    <textarea name="issue_description" id="issue_description" rows="4" required 
                              placeholder="Describe the issue you're experiencing with this <?php echo esc_attr(strtolower($options['context_label'])); ?>..."></textarea>
                </div>
                
                <?php if ($options['show_priority']) : ?>
                <div class="form-group">
                    <label for="issue_priority"><?php esc_html_e('Priority:', 'tradepress'); ?></label>
                    <select name="issue_priority" id="issue_priority">
                        <option value="<?php echo self::PRIORITY_LOW; ?>"><?php esc_html_e('Low - Minor issue', 'tradepress'); ?></option>
                        <option value="<?php echo self::PRIORITY_MEDIUM; ?>" selected><?php esc_html_e('Medium - Affects functionality', 'tradepress'); ?></option>
                        <option value="<?php echo self::PRIORITY_HIGH; ?>"><?php esc_html_e('High - Significant problem', 'tradepress'); ?></option>
                        <option value="<?php echo self::PRIORITY_CRITICAL; ?>"><?php esc_html_e('Critical - System breaking', 'tradepress'); ?></option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="submit" name="report_only" class="button button-secondary">
                        <?php esc_html_e('Report Issue Only', 'tradepress'); ?>
                    </button>
                    <button type="submit" name="report_and_disable" class="button button-primary" style="margin-left: 10px;">
                        <?php esc_html_e('Report Issue & Disable', 'tradepress'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle feature issue submission
     * 
     * @param array $data Form submission data
     * @return array Result with success/error message
     * 
     * @todo Add GitHub issue creation
     * @todo Add email notifications for critical issues
     * @todo Implement strategy impact checking
     */
    public static function handle_issue_submission($data) {
        if (!wp_verify_nonce($data['issue_nonce'], 'tradepress_feature_issue') || !current_user_can('manage_options')) {
            return array('success' => false, 'message' => 'Security check failed.');
        }
        
        $feature_type = sanitize_text_field($data['feature_type']);
        $feature_id = sanitize_text_field($data['feature_id']);
        $description = sanitize_textarea_field($data['issue_description']);
        $priority = sanitize_text_field($data['issue_priority'] ?? self::PRIORITY_MEDIUM);
        $disable_feature = !empty($data['report_and_disable']);
        
        // Store issue report
        $issue_data = array(
            'feature_type' => $feature_type,
            'feature_id' => $feature_id,
            'description' => $description,
            'priority' => $priority,
            'reported_by' => get_current_user_id(),
            'reported_at' => current_time('mysql'),
            'status' => 'open'
        );
        
        $option_key = 'tradepress_feature_issues';
        $existing_issues = get_option($option_key, array());
        $existing_issues[] = $issue_data;
        update_option($option_key, $existing_issues);
        
        // Handle feature disabling for directives
        if ($disable_feature && $feature_type === 'directive') {
            $result = self::disable_directive($feature_id, $description);
            if (!$result['success']) {
                return $result;
            }
        }
        
        // Store return URL for redirect
        $return_url = wp_get_referer() ?: admin_url();
        set_transient('tradepress_feedback_return_' . get_current_user_id(), $return_url, 300);
        
        // TODO: Create GitHub issue if configured
        // TODO: Send email notification for critical issues
        // TODO: Check strategy impact and notify user
        
        $message = sprintf(
            __('Issue reported for %s "%s". Priority: %s.', 'tradepress'),
            $feature_type,
            $feature_id,
            ucfirst($priority)
        );
        
        if ($disable_feature) {
            $message .= ' ' . __('Feature has been disabled.', 'tradepress');
        }
        
        return array('success' => true, 'message' => $message);
    }
    
    /**
     * Disable a directive due to reported issue
     * 
     * @param string $directive_id Directive identifier
     * @param string $reason Reason for disabling
     * @return array Result
     * 
     * @todo Check if directive is used in active strategies
     * @todo Provide strategy impact warnings
     * @todo Implement strategy auto-pause functionality
     */
    private static function disable_directive($directive_id, $reason) {
        // Get current directives configuration
        $configured_directives = get_option('tradepress_scoring_directives', array());
        
        // Update directive status
        if (!isset($configured_directives[$directive_id])) {
            $configured_directives[$directive_id] = array();
        }
        
        $configured_directives[$directive_id]['active'] = false;
        $configured_directives[$directive_id]['status'] = self::STATUS_DISABLED;
        $configured_directives[$directive_id]['disabled_reason'] = $reason;
        $configured_directives[$directive_id]['disabled_at'] = current_time('mysql');
        $configured_directives[$directive_id]['disabled_by'] = get_current_user_id();
        
        update_option('tradepress_scoring_directives', $configured_directives);
        
        // TODO: Check strategy usage and provide warnings
        // TODO: Implement strategy impact checking
        
        return array('success' => true, 'message' => 'Directive disabled successfully.');
    }
}