<?php
/**
 * TradePress Development Tasks
 * 
 * Manages development tasks and todos for the TradePress plugin.
 * Parses tasks from DEVELOPMENT-TECHNICAL.md and integrates with GitHub issues.
 * 
 * @package TradePress\Admin\development
 * @version 1.0.0
 * @date    2023-08-30
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_Admin_Development_Tasks
 * 
 * Handles the rendering and functionality of the Tasks tab
 * in the Development section.
 */
class TradePress_Admin_Development_Tasks {
    
    /**
     * Output the tasks view
     */
    public static function output() {
        // Ensure dashicons are available
        wp_enqueue_style('dashicons');
        
        self::enqueue_assets();
        
        // Fetch tasks data upfront
        $technical_tasks = self::get_technical_doc_tasks();
        $github_tasks = self::get_github_tasks();
        $feature_tasks = self::get_feature_tasks();
        
        // If all sources return no tasks, add sample tasks for testing
        if (empty($technical_tasks) && empty($github_tasks) && empty($feature_tasks)) {
            $technical_tasks[] = array(
                'id' => 'sample_task_1',
                'title' => 'Sample Task (Technical Doc)',
                'source' => 'technical',
                'source_label' => 'Technical Doc',
                'phase' => 1,
                'phase_title' => 'Core Setup',
                'section' => 'Sample Section',
                'section_id' => '1.1',
                'priority' => 1,
                'status' => 'pending',
                'subtasks' => [
                    ['title' => 'Sample Subtask 1', 'status' => 'pending'],
                    ['title' => 'Sample Subtask 2', 'status' => 'completed'],
                ],
                'link' => ''
            );
            
            $github_tasks[] = array(
                'id' => 'github_123',
                'title' => 'Sample Task (GitHub Issue)',
                'source' => 'github',
                'source_label' => 'GitHub #123',
                'phase' => 2,
                'phase_title' => 'Phase 2',
                'section' => 'GitHub Issue',
                'section_id' => 'github',
                'priority' => 2,
                'status' => 'pending',
                'subtasks' => [],
                'link' => 'https://github.com/your-username/TradePress/issues/123'
            );
        }
        
        // Merge all tasks
        $all_tasks = array_merge($technical_tasks, $github_tasks, $feature_tasks);
        
        // Get GitHub cache status for debug info only (not display)
        $github_cache_status = self::get_github_cache_status();
        
        // Debug data
        $debug_data = array(
            'file_exists' => file_exists(TRADEPRESS_PLUGIN_DIR . 'documentation/DEVELOPMENT-TECHNICAL.md'),
            'doc_path' => TRADEPRESS_PLUGIN_DIR . 'documentation/DEVELOPMENT-TECHNICAL.md',
            'github_cache_status' => $github_cache_status,
            'feature_count' => count($feature_tasks),
        );
        ?>
        
        <div class="tab-content" id="tasks">
            <div class="tradepress-tasks-container">
                <div class="tradepress-tasks-header">
                    <!-- Filter Controls -->
                    <div class="tradepress-tasks-filters">
                        <div class="filter-group">
                            <label for="task-source"><?php esc_html_e('Source:', 'tradepress'); ?></label>
                            <select id="task-source" class="task-filter">
                                <option value="all"><?php esc_html_e('All Sources', 'tradepress'); ?></option>
                                <option value="technical"><?php esc_html_e('Technical Doc', 'tradepress'); ?></option>
                                <option value="github"><?php esc_html_e('GitHub Issues', 'tradepress'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="task-status"><?php esc_html_e('Status:', 'tradepress'); ?></label>
                            <select id="task-status" class="task-filter">
                                <option value="all"><?php esc_html_e('All Statuses', 'tradepress'); ?></option>
                                <option value="pending"><?php esc_html_e('Pending', 'tradepress'); ?></option>
                                <option value="completed"><?php esc_html_e('Completed', 'tradepress'); ?></option>
                                <option value="in-progress"><?php esc_html_e('In Progress', 'tradepress'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="task-phase"><?php esc_html_e('Phase:', 'tradepress'); ?></label>
                            <select id="task-phase" class="task-filter">
                                <option value="all"><?php esc_html_e('All Phases', 'tradepress'); ?></option>
                                <?php 
                                // Fix syntax: using curly braces instead of alternative syntax
                                for ($i = 1; $i <= 6; $i++) { 
                                ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html(sprintf(__('Phase %d', 'tradepress'), $i)); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="task-search"><?php esc_html_e('Search:', 'tradepress'); ?></label>
                            <input type="text" id="task-search" class="task-search" placeholder="<?php esc_attr_e('Search tasks...', 'tradepress'); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Task Table -->
                <div class="tradepress-tasks-table-container">
                    <table class="tradepress-tasks-table widefat" id="tradepress-tasks-table">
                        <thead>
                            <tr>
                                <th class="column-status"><?php esc_html_e('Status', 'tradepress'); ?></th>
                                <th class="column-title sortable" data-sort="title"><?php esc_html_e('Task', 'tradepress'); ?></th>
                                <th class="column-source"><?php esc_html_e('Source', 'tradepress'); ?></th>
                                <th class="column-phase sortable" data-sort="phase"><?php esc_html_e('Phase', 'tradepress'); ?></th>
                                <th class="column-priority sortable" data-sort="priority"><?php esc_html_e('Priority', 'tradepress'); ?></th>
                                <th class="column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_tasks)) : ?>
                                <tr>
                                    <td colspan="6"><?php esc_html_e('No tasks found.', 'tradepress'); ?></td>
                                </tr>
                            <?php else : 
                                foreach ($all_tasks as $task) : 
                            ?>
                                <tr id="task-<?php echo esc_attr($task['id']); ?>" 
                                    class="task-row" 
                                    data-task-id="<?php echo esc_attr($task['id']); ?>"
                                    data-task-source="<?php echo esc_attr($task['source']); ?>"
                                    data-phase="<?php echo esc_attr($task['phase']); ?>"
                                    data-priority="<?php echo esc_attr($task['priority']); ?>"
                                    data-status="<?php echo esc_attr($task['status']); ?>">
                                    <td class="column-status">
                                        <div class="task-status-indicator <?php echo esc_attr($task['status']); ?>"></div>
                                    </td>
                                    <td class="column-title">
                                        <a href="#" class="task-title view-task-details" 
                                           data-task-id="<?php echo esc_attr($task['id']); ?>"
                                           data-task-source="<?php echo esc_attr($task['source']); ?>">
                                            <?php echo esc_html($task['title']); ?>
                                        </a>
                                    </td>
                                    <td class="column-source"><?php echo esc_html($task['source_label']); ?></td>
                                    <td class="column-phase">Phase <?php echo esc_html($task['phase']); ?></td>
                                    <td class="column-priority">
                                        <span class="priority-indicator priority-<?php echo esc_attr($task['priority']); ?>">
                                            <?php 
                                            switch (intval($task['priority'])) {
                                                case 1: echo esc_html('High'); break;
                                                case 2: echo esc_html('Medium'); break;
                                                case 3: echo esc_html('Low'); break;
                                                default: echo esc_html('Medium');
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="column-actions">
                                        <div class="task-actions">
                                            <a href="#" class="button button-small view-task-details" 
                                               data-task-id="<?php echo esc_attr($task['id']); ?>"
                                               data-task-source="<?php echo esc_attr($task['source']); ?>">
                                                <span class="dashicons dashicons-visibility"></span>
                                                <?php esc_html_e('View Details', 'tradepress'); ?>
                                            </a>
                                            
                                            <?php if ($task['status'] !== 'completed') : ?>
                                                <a href="#" class="button button-small update-task-status" 
                                                   data-task-id="<?php echo esc_attr($task['id']); ?>"
                                                   data-task-source="<?php echo esc_attr($task['source']); ?>"
                                                   data-status="completed">
                                                    <span class="dashicons dashicons-yes-alt"></span>
                                                    <?php esc_html_e('Mark Completed', 'tradepress'); ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($task['status'] === 'pending') : ?>
                                                <a href="#" class="button button-small start-task" 
                                                   data-task-id="<?php echo esc_attr($task['id']); ?>"
                                                   data-task-source="<?php echo esc_attr($task['source']); ?>">
                                                    <span class="dashicons dashicons-controls-play"></span>
                                                    <?php esc_html_e('Start', 'tradepress'); ?>
                                                </a>
                                                
                                                <a href="#" class="button button-small update-task-status" 
                                                   data-task-id="<?php echo esc_attr($task['id']); ?>"
                                                   data-task-source="<?php echo esc_attr($task['source']); ?>"
                                                   data-status="in-progress">
                                                    <span class="dashicons dashicons-chart-bar"></span>
                                                    <?php esc_html_e('Mark In Progress', 'tradepress'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- No Tasks Found Message (initially hidden, used by JS filtering) -->
                <div class="tradepress-no-tasks hidden">
                    <p><?php esc_html_e('No tasks found matching your criteria.', 'tradepress'); ?></p>
                </div>
            </div>
            
        </div>
        
        <!-- Task Detail Modal -->
        <div id="task-detail-modal" class="tradepress-modal">
            <div class="tradepress-modal-content">
                <span class="tradepress-modal-close">&times;</span>
                <div id="task-detail-content">
                    <!-- Task details will be populated with JS -->
                </div>
            </div>
        </div>
        
        <!-- Pre-render task details for JS to use -->
        <script type="text/javascript">
            var tradepressTasksData = <?php echo json_encode(array(
                'tasks' => $all_tasks,
                'debug' => $debug_data
            )); ?>;
        </script>
        <?php
    }
    
    /**
     * Enqueue required assets for the tasks tab
     */
    private static function enqueue_assets() {
        wp_enqueue_style(
            'tradepress-tasks-styles',
            TRADEPRESS_PLUGIN_URL . 'css/tradepress-tasks.css',
            array(),
            TRADEPRESS_VERSION
        );
        
        wp_enqueue_script(
            'tradepress-tasks',
            TRADEPRESS_PLUGIN_URL . 'js/tradepress-tasks.js',
            array('jquery'),
            TRADEPRESS_VERSION,
            true
        );
        
        // Add localized variables for the JavaScript
        wp_localize_script(
            'tradepress-tasks',
            'tradepressTasksStrings',
            array(
                'strings' => array(
                    'no_tasks_found' => __('No tasks found matching your criteria.', 'tradepress'),
                    'task_detail_title' => __('Task Details', 'tradepress'),
                    'loading' => __('Loading...', 'tradepress'),
                    'completed' => __('Completed', 'tradepress'),
                    'pending' => __('Pending', 'tradepress'),
                    'in_progress' => __('In Progress', 'tradepress'),
                    'view_details' => __('View Details', 'tradepress'),
                    'mark_completed' => __('Mark Completed', 'tradepress'),
                    'mark_in_progress' => __('Mark In Progress', 'tradepress'),
                    'start' => __('Start', 'tradepress'),
                    'view_link' => __('View Link', 'tradepress'),
                    'description' => __('Description', 'tradepress'),
                    'subtasks' => __('Subtasks', 'tradepress'),
                    'cache_status' => __('GitHub Data:', 'tradepress'),
                    'cache_stale' => __('Stale', 'tradepress'),
                    'cache_fresh' => __('Fresh', 'tradepress'),
                    'last_updated' => __('last updated', 'tradepress'),
                    'showing' => __('Showing', 'tradepress'),
                    'of' => __('of', 'tradepress'),
                    'per_page' => __('Per page', 'tradepress'),
                    'no_items' => __('No items', 'tradepress')
                ),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tradepress_tasks_nonce'),
                'current_task_url' => admin_url('admin.php?page=tradepress_development&tab=current_task'),
                'repo_owner' => get_option('TRADEPRESS_GITHUB_repo_owner', 'your-github-username'),
                'repo_name' => get_option('TRADEPRESS_GITHUB_repo_name', 'tradepress')
            )
        );
        
        // Tasks functionality is now handled by tradepress-development-tabs.js
    }
    
    /**
     * Get GitHub cache status
     * @return array Cache status information
     */
    private static function get_github_cache_status() {
        $api = TRADEPRESS_GITHUB_api();
        return array(
            'last_refresh' => $api->get_last_refresh_time() ? human_time_diff($api->get_last_refresh_time()) . ' ago' : 'Never',
            'is_stale' => $api->is_cache_stale(),
            'expiration' => human_time_diff(time(), time() + $api->get_cache_expiration()),
        );
    }
    
    /**
     * Parse and retrieve tasks from the technical documentation
     * @return array Array of tasks from technical documentation
     */
    public static function get_technical_doc_tasks() {
        $doc_path = TRADEPRESS_PLUGIN_DIR . 'documentation/DEVELOPMENT-TECHNICAL.md';
        if (!file_exists($doc_path)) {
            return array();
        }
        
        $content = file_get_contents($doc_path);
        $tasks = array();
        
        // Regular expression to find phase headers
        preg_match_all('/## Phase (\d+):(.*)/i', $content, $phase_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        
        foreach ($phase_matches as $index => $phase_match) {
            $phase_num = intval($phase_match[1][0]);
            $phase_title = trim($phase_match[2][0]);
            $phase_start_pos = $phase_match[0][1];
            
            // Find the end of this phase (next phase or end of file)
            $phase_end_pos = strlen($content);
            if (isset($phase_matches[$index + 1])) {
                $phase_end_pos = $phase_matches[$index + 1][0][1];
            }
            
            // Get the content for just this phase
            $phase_content = substr($content, $phase_start_pos, $phase_end_pos - $phase_start_pos);
            
            // Regular expression to find section headers within this phase
            preg_match_all('/### Section (\d+\.\d+):(.*)/i', $phase_content, $section_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            
            foreach ($section_matches as $section_index => $section_match) {
                $section_id = $section_match[1][0];
                $section_title = trim($section_match[2][0]);
                $section_start_pos = $section_match[0][1];
                
                // Find the end of this section (next section or end of phase)
                $section_end_pos = strlen($phase_content);
                if (isset($section_matches[$section_index + 1])) {
                    $section_end_pos = $section_matches[$section_index + 1][0][1];
                }
                
                // Get the content for just this section
                $section_content = substr($phase_content, $section_start_pos, $section_end_pos - $section_start_pos);
                
                // Regular expression to find tasks within this section
                preg_match_all('/- \[ \] (.*)/', $section_content, $task_matches, PREG_SET_ORDER);
                
                foreach ($task_matches as $task_match) {
                    $task_title = trim($task_match[1]);
                    
                    // Create unique ID for this task
                    $task_id = 'tech_' . $phase_num . '_' . str_replace('.', '_', $section_id) . '_' . sanitize_title($task_title);
                    
                    // Find subtasks
                    $subtasks = array();
                    if (preg_match_all('/- \[ \] (.*?)$/m', $section_content, $subtask_matches)) {
                        foreach ($subtask_matches[1] as $subtask_text) {
                            $subtasks[] = array(
                                'title' => trim($subtask_text),
                                'status' => 'pending'
                            );
                        }
                    }
                    
                    // Store the raw markdown content to help with subtask extraction in JS
                    $task_markdown = "- [ ] **" . $task_title . "**\n" . $section_content;
                    
                    $tasks[] = array(
                        'id' => $task_id,
                        'title' => $task_title,
                        'source' => 'technical',
                        'source_label' => 'Technical Doc',
                        'phase' => $phase_num,
                        'phase_title' => $phase_title,
                        'section' => $section_title,
                        'section_id' => $section_id,
                        'priority' => self::determine_priority($task_title, $phase_num),
                        'status' => 'pending',
                        'subtasks' => $subtasks,
                        'link' => '',
                        'markdown_content' => $task_markdown,
                    );
                }
            }
        }
        
        return $tasks;
    }
    
    /**
     * Get GitHub issues formatted as tasks
     * @return array Array of tasks from GitHub issues
     */
    public static function get_github_tasks() {
        // Retrieve GitHub issues from the helper function used in GitHub tab
        require_once TRADEPRESS_PLUGIN_DIR . 'admin/page/development/github/github-helpers.php';
        
        // Get configured repository settings from options
        $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
        $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
        
        // If no repository is configured, use default values for testing
        if (empty($repo_owner) || empty($repo_name)) {
            $repo_owner = 'your-github-username';
            $repo_name = 'TradePress';
        }
        
        // Log the repository details we're using
        error_log("Fetching GitHub issues for {$repo_owner}/{$repo_name}");
        
        $issues = array();
        
        // Try to get issues from the GitHub API
        if (function_exists('tradepress_get_github_issues')) {
            $issues = tradepress_get_github_issues($repo_owner, $repo_name);
        }
        
        // Check if we got a valid response or WP_Error
        if (is_wp_error($issues)) {
            error_log('GitHub API Error: ' . $issues->get_error_message());
            // For testing - provide sample issues if API call fails
            $issues = self::get_sample_github_issues();
        } elseif (empty($issues)) {
            // Also use sample data if no issues were returned
            $issues = self::get_sample_github_issues();
        }
        
        $tasks = array();
        
        // Process each issue into a task format
        foreach ($issues as $issue) {
            // Determine phase from labels
            $phase = 1; // Default to phase 1
            $priority = 3; // Default priority
            
            // Check if labels property exists and is an array/object
            if (isset($issue->labels) && !empty($issue->labels)) {
                foreach ($issue->labels as $label) {
                    // Look for phase labels
                    if (preg_match('/phase:(\d+)/i', $label->name, $phase_matches)) {
                        $phase = intval($phase_matches[1]);
                    }
                    
                    // Look for priority labels
                    if (preg_match('/priority:(high|medium|low)/i', $label->name, $priority_matches)) {
                        switch (strtolower($priority_matches[1])) {
                            case 'high':
                                $priority = 1;
                                break;
                            case 'medium':
                                $priority = 2;
                                break;
                            case 'low':
                                $priority = 3;
                                break;
                        }
                    }
                }
            }
            
            // Format the issue as a task
            $tasks[] = array(
                'id' => 'github_' . $issue->number,
                'title' => $issue->title,
                'source' => 'github',
                'source_label' => 'GitHub #' . $issue->number,
                'phase' => $phase,
                'phase_title' => sprintf('Phase %d', $phase),
                'section' => 'GitHub Issue',
                'section_id' => 'github',
                'priority' => $priority,
                'status' => (isset($issue->state) && $issue->state === 'closed') ? 'completed' : 'pending', 
                'subtasks' => array(),
                'link' => isset($issue->html_url) ? $issue->html_url : '',
                'description' => isset($issue->body) ? $issue->body : '',
                'created_at' => isset($issue->created_at) ? $issue->created_at : '',
                'updated_at' => isset($issue->updated_at) ? $issue->updated_at : '',
            );
        }
        
        return $tasks;
    }
    
    /**
     * Get feature definitions formatted as tasks
     * @return array Array of tasks from feature definitions
     */
    public static function get_feature_tasks() {
        // Check if the function exists
        if (!function_exists('tradepress_get_features_as_tasks')) {
            // Include the feature loader if it's not already included
            if (defined('TRADEPRESS_ROADMAP_DIR') && file_exists(TRADEPRESS_ROADMAP_DIR . 'feature-loader.php')) {
                require_once TRADEPRESS_ROADMAP_DIR . 'feature-loader.php';
            }
        }
        
        // Return empty array if function still doesn't exist
        if (!function_exists('tradepress_get_features_as_tasks')) {
            return array();
        }
        
        // Get tasks from feature definitions
        return tradepress_get_features_as_tasks();
    }
    
    /**
     * Get sample GitHub issues for testing
     * 
     * @return array Array of sample issues
     */
    private static function get_sample_github_issues() {
        // Create some sample issues for testing purposes
        return array(
            (object) array(
                'number' => 101,
                'title' => 'Implement chart visualization for scoring trends',
                'state' => 'open',
                'html_url' => 'https://github.com/your-username/TradePress/issues/101',
                'body' => 'We need to add chart visualization to display scoring trends over time. This will help users track performance.',
                'created_at' => date('Y-m-d\TH:i:s\Z', strtotime('-3 days')),
                'updated_at' => date('Y-m-d\TH:i:s\Z', strtotime('-1 day')),
                'labels' => array(
                    (object) array('name' => 'enhancement', 'color' => '0366d6'),
                    (object) array('name' => 'phase:2', 'color' => 'fbca04'),
                    (object) array('name' => 'priority:high', 'color' => 'b60205')
                ),
            ),
            (object) array(
                'number' => 102,
                'title' => 'Fix API connection timeout on large data sets',
                'state' => 'open',
                'html_url' => 'https://github.com/your-username/TradePress/issues/102',
                'body' => 'API connections are timing out when fetching large data sets. Need to implement pagination and optimize the requests.',
                'created_at' => date('Y-m-d\TH:i:s\Z', strtotime('-5 days')),
                'updated_at' => date('Y-m-d\TH:i:s\Z', strtotime('-2 days')),
                'labels' => array(
                    (object) array('name' => 'bug', 'color' => 'd73a4a'),
                    (object) array('name' => 'phase:1', 'color' => 'fbca04'),
                    (object) array('name' => 'priority:medium', 'color' => 'fbca04')
                ),
            ),
            (object) array(
                'number' => 103,
                'title' => 'Add portfolio diversification analysis',
                'state' => 'open',
                'html_url' => 'https://github.com/your-username/TradePress/issues/103',
                'body' => 'Create a new feature to analyze portfolio diversification across different sectors and asset classes.',
                'created_at' => date('Y-m-d\TH:i:s\Z', strtotime('-7 days')),
                'updated_at' => date('Y-m-d\TH:i:s\Z', strtotime('-7 days')),
                'labels' => array(
                    (object) array('name' => 'feature', 'color' => '0e8a16'),
                    (object) array('name' => 'phase:3', 'color' => 'fbca04'),
                    (object) array('name' => 'priority:low', 'color' => '2cbe4e')
                ),
            ),
            (object) array(
                'number' => 104,
                'title' => 'Create user documentation for indicator settings',
                'state' => 'closed',
                'html_url' => 'https://github.com/your-username/TradePress/issues/104',
                'body' => 'Comprehensive documentation needed for all technical indicators and their configuration options.',
                'created_at' => date('Y-m-d\TH:i:s\Z', strtotime('-10 days')),
                'updated_at' => date('Y-m-d\TH:i:s\Z', strtotime('-1 day')),
                'labels' => array(
                    (object) array('name' => 'documentation', 'color' => '0075ca'),
                    (object) array('name' => 'phase:2', 'color' => 'fbca04'),
                    (object) array('name' => 'priority:medium', 'color' => 'fbca04')
                ),
            )
        );
    }
    
    /**
     * Determine task priority based on title and phase
     * 
     * @param string $title Task title
     * @param int $phase Phase number
     * @return int Priority (1=highest, 3=lowest)
     */
    public static function determine_priority($title, $phase) {
        // This is a simple heuristic and could be improved
        if (stripos($title, 'core') !== false || 
            stripos($title, 'foundation') !== false || 
            stripos($title, 'base') !== false) {
            return 1;
        } elseif ($phase <= 2) {
            return 2;
        } else {
            return 3;
        }
    }
}

/**
 * Ajax handler for creating GitHub issues
 */
function tradepress_ajax_create_github_issue() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_tasks_nonce')) {
        wp_send_json_error(array('message' => 'Invalid security token.'));
        return;
    }
    
    // Check required fields
    if (empty($_POST['title'])) {
        wp_send_json_error(array('message' => 'Title is required.'));
        return;
    }
    
    // Get issue data
    $title = sanitize_text_field($_POST['title']);
    $body = isset($_POST['body']) ? sanitize_textarea_field($_POST['body']) : '';
    $labels = isset($_POST['labels']) ? (array) $_POST['labels'] : array();
    
    // Format original task reference
    if (isset($_POST['original_task_id']) && isset($_POST['original_task_source'])) {
        $original_task_id = sanitize_text_field($_POST['original_task_id']);
        $original_task_source = sanitize_text_field($_POST['original_task_source']);
        
        // Add reference to the original task
        $body .= "\n\n---\n";
        $body .= "Original task ID: `{$original_task_id}`\n";
        $body .= "Original task source: `{$original_task_source}`";
    }
    
    // Get repository settings
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
    
    // Create issue data
    $issue_data = array(
        'title' => $title,
        'body' => $body,
        'labels' => $labels
    );
    
    // Try to create the issue using the GitHub API
    require_once TRADEPRESS_PLUGIN_DIR . 'api/github/github-api.php';
    
    try {
        $github_api = new TRADEPRESS_GITHUB_API();
        
        // Debug logging
        error_log('Creating GitHub issue for task: ' . $title);
        
        $result = $github_api->create_issue($repo_owner, $repo_name, $issue_data);
        
        if (is_wp_error($result)) {
            error_log('Error creating GitHub issue: ' . $result->get_error_message());
            wp_send_json_error(array('message' => $result->get_error_message()));
            return;
        }
        
        wp_send_json_success($result);
    } catch (Exception $e) {
        error_log('Exception creating GitHub issue: ' . $e->getMessage());
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
