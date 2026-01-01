<?php
/**
 * GitHub Next Issue Tab
 * 
 * Displays the next GitHub issue to work on based on priority.
 * 
 * @package TradePress/Admin/roadmap/GitHub
 * @version 1.0.1
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display content for the Next Issue subtab
 * 
 * @param string $repo_owner GitHub repository owner
 * @param string $repo_name GitHub repository name
 */
function TRADEPRESS_GITHUB_next_issue_content($repo_owner, $repo_name) {
    // Get GitHub API settings - follow the same pattern as all-issues.php
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', $repo_owner);
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', $repo_name);
    
    // Basic validation - IMPORTANT: Match the same validation logic as all-issues.php
    if (empty($github_token) || empty($repo_owner) || empty($repo_name)) {
        ?>
        <div class="github-next-issue-wrapper">
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('GitHub API configuration is incomplete.', 'tradepress'); ?>
                    <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'api-config'))); ?>">
                        <?php esc_html_e('Configure GitHub API', 'tradepress'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
        return;
    }
    
    // Fetch issues with priority labels
    $issues = tradepress_get_github_repository_issues($repo_owner, $repo_name, $github_token, array(
        'state' => 'open',
        'sort' => 'updated',
        'direction' => 'desc'
    ));
    
    if (is_wp_error($issues)) {
        ?>
        <div class="github-next-issue-wrapper">
            <div class="notice notice-error">
                <p><?php echo esc_html('GitHub API error: ' . $issues->get_error_message()); ?></p>
            </div>
        </div>
        <?php
        return;
    }
    
    // Find the highest priority issue
    $next_issue = null;
    $priority_labels = array('priority:critical', 'priority:high', 'priority:medium', 'priority:low');
    
    if (!empty($issues['items'])) {
        // First try to find an issue with a priority label
        foreach ($priority_labels as $priority) {
            if ($next_issue) break;
            
            foreach ($issues['items'] as $issue) {
                if (has_github_label($issue, $priority)) {
                    $next_issue = $issue;
                    break;
                }
            }
        }
        
        // If no issue with priority label found, use the most recently updated
        if (!$next_issue) {
            $next_issue = $issues['items'][0];
        }
    }
    
    ?>
    <div class="github-next-issue-wrapper">
        <?php if (!$next_issue) : ?>
            <div class="no-issues">
                <div class="notice notice-success">
                    <p><?php esc_html_e('No open issues found in the repository. Great job!', 'tradepress'); ?></p>
                </div>
            </div>
        <?php else : ?>
            <div class="next-issue-container">
                <h2><?php esc_html_e('Next Issue to Work On', 'tradepress'); ?></h2>
                
                <div class="issue-details">
                    <div class="issue-header">
                        <h3 class="issue-title">
                            <a href="<?php echo esc_url($next_issue->html_url); ?>" target="_blank">
                                <?php echo esc_html($next_issue->title); ?>
                                <span class="issue-number">#<?php echo esc_html($next_issue->number); ?></span>
                            </a>
                        </h3>
                        
                        <?php if (!empty($next_issue->labels)) : ?>
                            <div class="issue-labels">
                                <?php foreach ($next_issue->labels as $label) : ?>
                                    <?php 
                                    $text_color = TRADEPRESS_GITHUB_get_contrasting_color('#' . $label->color);
                                    ?>
                                    <span class="issue-label" style="background-color: #<?php echo esc_attr($label->color); ?>; color: <?php echo esc_attr($text_color); ?>;">
                                        <?php echo esc_html($label->name); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="issue-body">
                        <?php 
                        // Format issue body for display - truncate if too long
                        $body = !empty($next_issue->body) ? $next_issue->body : __('No description provided.', 'tradepress');
                        $max_length = 400;
                        
                        if (strlen($body) > $max_length) {
                            $body = substr($body, 0, $max_length) . '...';
                        }
                        
                        echo nl2br(esc_html($body));
                        ?>
                    </div>
                    
                    <div class="issue-meta">
                        <div class="meta-item">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php 
                            $created_date = new DateTime($next_issue->created_at);
                            echo esc_html__('Created: ', 'tradepress') . esc_html($created_date->format('M j, Y')); 
                            ?>
                        </div>
                        
                        <div class="meta-item">
                            <span class="dashicons dashicons-update"></span>
                            <?php 
                            $updated_date = new DateTime($next_issue->updated_at);
                            echo esc_html__('Updated: ', 'tradepress') . esc_html($updated_date->format('M j, Y')); 
                            ?>
                        </div>
                        
                        <?php if ($next_issue->comments > 0) : ?>
                            <div class="meta-item">
                                <span class="dashicons dashicons-admin-comments"></span>
                                <?php echo sprintf(esc_html(_n('%d comment', '%d comments', $next_issue->comments, 'tradepress')), $next_issue->comments); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="issue-actions">
                        <a href="<?php echo esc_url($next_issue->html_url); ?>" target="_blank" class="button button-primary">
                            <span class="dashicons dashicons-external"></span>
                            <?php esc_html_e('View on GitHub', 'tradepress'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url($next_issue->html_url . '#new_comment_field'); ?>" target="_blank" class="button">
                            <span class="dashicons dashicons-admin-comments"></span>
                            <?php esc_html_e('Add Comment', 'tradepress'); ?>
                        </a>
                        
                        <a href="#" class="button start-working" data-issue="<?php echo esc_attr($next_issue->number); ?>">
                            <span class="dashicons dashicons-controls-play"></span>
                            <?php esc_html_e('Start Working', 'tradepress'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="next-issues-list">
                    <h3><?php esc_html_e('Other Open Issues', 'tradepress'); ?></h3>
                    
                    <?php if (count($issues['items']) <= 1) : ?>
                        <p><?php esc_html_e('No other open issues.', 'tradepress'); ?></p>
                    <?php else : ?>
                        <table class="widefat next-issues-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('#', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Title', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Labels', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Updated', 'tradepress'); ?></th>
                                    <th><?php esc_html_e('Actions', 'tradepress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $issue_count = 0;
                                foreach ($issues['items'] as $issue) : 
                                    // Skip the current next issue
                                    if ($issue->number === $next_issue->number) continue;
                                    if ($issue_count >= 5) break; // Limit to 5 other issues
                                    $issue_count++;
                                    
                                    $updated_date = new DateTime($issue->updated_at);
                                    $now = new DateTime();
                                    $updated_time_ago = TRADEPRESS_GITHUB_time_ago($updated_date, $now);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($issue->number); ?></td>
                                        <td>
                                            <a href="<?php echo esc_url($issue->html_url); ?>" target="_blank">
                                                <?php echo esc_html($issue->title); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if (!empty($issue->labels)) : ?>
                                                <div class="issue-labels">
                                                    <?php foreach ($issue->labels as $label) : ?>
                                                        <span class="issue-label-small" style="background-color: #<?php echo esc_attr($label->color); ?>;">
                                                            <?php echo esc_html($label->name); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html($updated_time_ago); ?></td>
                                        <td>
                                            <a href="#" class="button make-next" data-issue="<?php echo esc_attr($issue->number); ?>">
                                                <?php esc_html_e('Make Next', 'tradepress'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (count($issues['items']) > 6) : // 1 main issue + 5 in table ?>
                            <div class="more-issues">
                                <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'all-issues'))); ?>" class="button">
                                    <?php echo sprintf(esc_html__('View All %d Issues', 'tradepress'), count($issues['items'])); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Helper function to check if an issue has a specific label
 * 
 * @param object $issue The issue object
 * @param string $label_name The label name to check for
 * @return bool Whether the issue has the label
 */
function has_github_label($issue, $label_name) {
    if (empty($issue->labels)) {
        return false;
    }
    
    foreach ($issue->labels as $label) {
        if (strtolower($label->name) === strtolower($label_name)) {
            return true;
        }
    }
    
    return false;
}
