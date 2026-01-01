<?php
/**
 * GitHub All Issues Tab
 * 
 * Displays all GitHub issues for the TradePress repository.
 * 
 * @package TradePress/Admin/roadmap/GitHub
 * @version 1.0.1
 * @since 1.0.0
 * @created 2023-10-26
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the GitHub API file - already included in github-helpers.php
// require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/github/github-api.php';

/**
 * Display content for the All Issues subtab
 * 
 * @param string $repo_owner GitHub repository owner
 * @param string $repo_name GitHub repository name
 */
function TRADEPRESS_GITHUB_all_issues_content($repo_owner, $repo_name) {
    // Get GitHub API settings
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', $repo_owner);
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', $repo_name);
    
    // Basic validation
    if (empty($github_token) || empty($repo_owner) || empty($repo_name)) {
        ?>
        <div class="github-all-issues-wrapper">
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
    
    // Get filter parameters
    $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : 'open';
    $label = isset($_GET['label']) ? sanitize_text_field($_GET['label']) : '';
    $sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'updated';
    $sort_direction = isset($_GET['direction']) ? sanitize_text_field($_GET['direction']) : 'desc';
    $page = isset($_GET['gh_page']) ? absint($_GET['gh_page']) : 1;
    
    // Fetch issues
    $issues = tradepress_get_github_repository_issues($repo_owner, $repo_name, $github_token, array(
        'state' => $state,
        'labels' => $label,
        'sort' => $sort_by,
        'direction' => $sort_direction,
        'page' => $page
    ));
    
    // Fetch labels for filtering
    $labels = tradepress_get_github_labels($repo_owner, $repo_name, $github_token);
    
    // Create base URL for pagination
    $pagination_base_url = add_query_arg(array(
        'tab' => 'github',
        'subtab' => 'all-issues',
        'state' => $state,
        'sort' => $sort_by,
        'direction' => $sort_direction,
        'label' => $label,
    ));
    ?>
    <div class="github-all-issues-wrapper">
        <?php 
        if (is_wp_error($issues)) :
            ?>
            <div class="notice notice-error">
                <p><?php echo esc_html($issues->get_error_message()); ?></p>
            </div>
            <?php
        else :
            // Display filter controls
            ?>
            <div class="issues-filter-bar">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress">
                    <input type="hidden" name="view" value="development">
                    <input type="hidden" name="tab" value="github">
                    <input type="hidden" name="subtab" value="all-issues">
                    
                    <div class="filter-group">
                        <label for="issue-state"><?php esc_html_e('State:', 'tradepress'); ?></label>
                        <select name="state" id="issue-state">
                            <option value="open" <?php selected($state, 'open'); ?>><?php esc_html_e('Open', 'tradepress'); ?></option>
                            <option value="closed" <?php selected($state, 'closed'); ?>><?php esc_html_e('Closed', 'tradepress'); ?></option>
                            <option value="all" <?php selected($state, 'all'); ?>><?php esc_html_e('All', 'tradepress'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="issue-sort"><?php esc_html_e('Sort by:', 'tradepress'); ?></label>
                        <select name="sort" id="issue-sort">
                            <option value="created" <?php selected($sort_by, 'created'); ?>><?php esc_html_e('Created', 'tradepress'); ?></option>
                            <option value="updated" <?php selected($sort_by, 'updated'); ?>><?php esc_html_e('Updated', 'tradepress'); ?></option>
                            <option value="comments" <?php selected($sort_by, 'comments'); ?>><?php esc_html_e('Comments', 'tradepress'); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort-direction"><?php esc_html_e('Direction:', 'tradepress'); ?></label>
                        <select name="direction" id="sort-direction">
                            <option value="desc" <?php selected($sort_direction, 'desc'); ?>><?php esc_html_e('Descending', 'tradepress'); ?></option>
                            <option value="asc" <?php selected($sort_direction, 'asc'); ?>><?php esc_html_e('Ascending', 'tradepress'); ?></option>
                        </select>
                    </div>
                    
                    <?php if (!is_wp_error($labels) && !empty($labels)) : ?>
                        <div class="filter-group">
                            <label for="issue-label"><?php esc_html_e('Label:', 'tradepress'); ?></label>
                            <select name="label" id="issue-label">
                                <option value=""><?php esc_html_e('All labels', 'tradepress'); ?></option>
                                <?php foreach ($labels as $label_item) : ?>
                                    <option value="<?php echo esc_attr($label_item->name); ?>" <?php selected($label, $label_item->name); ?>>
                                        <?php echo esc_html($label_item->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="filter-group">
                        <button type="submit" class="button"><?php esc_html_e('Filter', 'tradepress'); ?></button>
                        <a href="<?php echo esc_url(add_query_arg(array('tab' => 'github', 'subtab' => 'all-issues'))); ?>" class="button">
                            <?php esc_html_e('Reset', 'tradepress'); ?>
                        </a>
                    </div>
                </form>
            </div>
            
            <?php if (empty($issues['items'])) : ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('No issues found matching your criteria.', 'tradepress'); ?></p>
                </div>
            <?php else : ?>
                <div class="issues-count">
                    <?php 
                    echo sprintf(
                        esc_html(_n('%d issue found', '%d issues found', $issues['total_count'], 'tradepress')), 
                        $issues['total_count']
                    ); 
                    ?>
                </div>
                
                <div class="issues-table-container">
                    <table class="widefat github-issues-table">
                        <thead>
                            <tr>
                                <th class="column-status"><?php esc_html_e('Status', 'tradepress'); ?></th>
                                <th class="column-title"><?php esc_html_e('Title', 'tradepress'); ?></th>
                                <th class="column-labels"><?php esc_html_e('Labels', 'tradepress'); ?></th>
                                <th class="column-created"><?php esc_html_e('Created', 'tradepress'); ?></th>
                                <th class="column-updated"><?php esc_html_e('Updated', 'tradepress'); ?></th>
                                <th class="column-comments"><?php esc_html_e('Comments', 'tradepress'); ?></th>
                                <th class="column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="the-issue-list">
                            <?php foreach ($issues['items'] as $issue) : 
                                // Format dates
                                $created_date = new DateTime($issue->created_at);
                                $updated_date = new DateTime($issue->updated_at);
                                $now = new DateTime();
                                $created_time_ago = TRADEPRESS_GITHUB_time_ago($created_date, $now);
                                $updated_time_ago = TRADEPRESS_GITHUB_time_ago($updated_date, $now);
                            ?>
                                <tr id="issue-<?php echo esc_attr($issue->number); ?>" class="issue-row <?php echo $issue->state; ?>">
                                    <td class="column-status">
                                        <div class="issue-status <?php echo esc_attr($issue->state); ?>">
                                            <span class="dashicons dashicons-<?php echo $issue->state === 'open' ? 'warning' : 'yes-alt'; ?>"></span>
                                            <span class="screen-reader-text"><?php echo esc_html(ucfirst($issue->state)); ?></span>
                                        </div>
                                    </td>
                                    <td class="column-title">
                                        <a href="<?php echo esc_url($issue->html_url); ?>" target="_blank" class="issue-link">
                                            <?php echo esc_html($issue->title); ?>
                                            <span class="issue-number">#<?php echo esc_html($issue->number); ?></span>
                                        </a>
                                    </td>
                                    <td class="column-labels">
                                        <?php if (!empty($issue->labels)) : ?>
                                            <div class="issue-labels">
                                                <?php foreach ($issue->labels as $issue_label) : ?>
                                                    <?php 
                                                    $text_color = TRADEPRESS_GITHUB_get_contrasting_color('#' . $issue_label->color);
                                                    ?>
                                                    <span class="issue-label" style="background-color: #<?php echo esc_attr($issue_label->color); ?>; color: <?php echo esc_attr($text_color); ?>;">
                                                        <?php echo esc_html($issue_label->name); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="column-created">
                                        <div class="time-ago" title="<?php echo esc_attr($created_date->format('Y-m-d H:i:s')); ?>">
                                            <?php echo esc_html($created_time_ago); ?>
                                        </div>
                                    </td>
                                    <td class="column-updated">
                                        <div class="time-ago" title="<?php echo esc_attr($updated_date->format('Y-m-d H:i:s')); ?>">
                                            <?php echo esc_html($updated_time_ago); ?>
                                        </div>
                                    </td>
                                    <td class="column-comments">
                                        <?php if ($issue->comments > 0) : ?>
                                            <a href="<?php echo esc_url($issue->html_url); ?>" target="_blank" class="comments-link">
                                                <span class="dashicons dashicons-admin-comments"></span>
                                                <span class="comment-count"><?php echo esc_html($issue->comments); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="column-actions">
                                        <div class="row-actions">
                                            <span class="view">
                                                <a href="<?php echo esc_url($issue->html_url); ?>" target="_blank">
                                                    <?php esc_html_e('View', 'tradepress'); ?>
                                                </a> |
                                            </span>
                                            <span class="comment">
                                                <a href="<?php echo esc_url($issue->html_url . '#new_comment_field'); ?>" target="_blank">
                                                    <?php esc_html_e('Comment', 'tradepress'); ?>
                                                </a>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($issues['pagination'])) : ?>
                    <div class="github-issues-pagination">
                        <?php if ($issues['pagination']['pages'] > 1) : ?>
                            <div class="tablenav-pages">
                                <span class="displaying-num">
                                    <?php 
                                    echo sprintf(
                                        esc_html(_n('%d issue', '%d issues', $issues['total_count'], 'tradepress')),
                                        $issues['total_count']
                                    ); 
                                    ?>
                                </span>
                                
                                <span class="pagination-links">
                                    <?php if ($page > 1) : ?>
                                        <a href="<?php echo esc_url(add_query_arg('gh_page', 1, $pagination_base_url)); ?>" class="first-page button">
                                            <span class="screen-reader-text"><?php esc_html_e('First page', 'tradepress'); ?></span>
                                            <span aria-hidden="true">«</span>
                                        </a>
                                        <a href="<?php echo esc_url(add_query_arg('gh_page', $page - 1, $pagination_base_url)); ?>" class="prev-page button">
                                            <span class="screen-reader-text"><?php esc_html_e('Previous page', 'tradepress'); ?></span>
                                            <span aria-hidden="true">‹</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <span class="paging-input">
                                        <span class="current-page"><?php echo esc_html($page); ?></span>
                                        <span class="total-pages"><?php echo esc_html($issues['pagination']['pages']); ?></span>
                                    </span>
                                    
                                    <?php if ($page < $issues['pagination']['pages']) : ?>
                                        <a href="<?php echo esc_url(add_query_arg('gh_page', $page + 1, $pagination_base_url)); ?>" class="next-page button">
                                            <span class="screen-reader-text"><?php esc_html_e('Next page', 'tradepress'); ?></span>
                                            <span aria-hidden="true">›</span>
                                        </a>
                                        <a href="<?php echo esc_url(add_query_arg('gh_page', $issues['pagination']['pages'], $pagination_base_url)); ?>" class="last-page button">
                                            <span class="screen-reader-text"><?php esc_html_e('Last page', 'tradepress'); ?></span>
                                            <span aria-hidden="true">»</span>
                                        </a>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Get contrasting text color for background color
 * 
 * @param string $hex_color Hex color code
 * @return string Black or white hex color
 */
function TRADEPRESS_GITHUB_get_contrasting_color($hex_color) {
    // Convert hex to RGB
    $hex = str_replace('#', '', $hex_color);
    
    if (strlen($hex) === 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    // Calculate luminance
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    
    // Return white or black based on luminance
    return $luminance > 0.5 ? '#000000' : '#ffffff';
}
