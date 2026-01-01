<?php
/**
 * TradePress Development Current Task
 * 
 * Displays the currently selected development task with detailed information
 * and progress tracking.
 * 
 * @package TradePress\Admin\development
 * @version 1.0.1
 * @date    2023-10-15
 * @see     TradePress_Current_Task_Handler For form processing logic
 * @see     TradePress_Listener For handling form submissions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_Admin_Development_Current_Task
 * 
 * Handles the rendering and functionality of the Current Task tab
 * in the Development section.
 */
class TradePress_Admin_Development_Current_Task {
    
    /**
     * Output the current task view
     */
    public static function output() {
        // Ensure dashicons are available
        wp_enqueue_style('dashicons');
        
        // Display notifications for GitHub issue creation
        $github_issue_created = get_transient('TRADEPRESS_GITHUB_issue_created');
        $github_issue_error = get_transient('TRADEPRESS_GITHUB_issue_error');
        
        if ($github_issue_created) {
            delete_transient('TRADEPRESS_GITHUB_issue_created');
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 sprintf(__('GitHub issue #%s created successfully!', 'tradepress'), esc_html($github_issue_created)) .
                 '</p></div>';
        }
        
        if ($github_issue_error) {
            delete_transient('TRADEPRESS_GITHUB_issue_error');
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 sprintf(__('Error creating GitHub issue: %s', 'tradepress'), esc_html($github_issue_error)) .
                 '</p></div>';
        }
        
        // Get current task data
        $current_task = self::get_current_task();
        $task_history = self::get_task_history();
        
        // Setup demo task if none is selected
        if (empty($current_task)) {
            $current_task = self::get_demo_task();
        }
        
        // Get all tasks for the dropdown
        $all_tasks = self::get_all_tasks();
        ?>
        <div class="current-task-container">
            <div class="current-task-header">
                <h2><?php esc_html_e('Current Development Task', 'tradepress'); ?></h2>
                <p class="description"><?php esc_html_e('Focus on one development task at a time with detailed tracking.', 'tradepress'); ?></p>
                
                <div class="task-selection">
                    <form method="post" id="select-current-task-form">
                        <?php wp_nonce_field('tradepress_select_current_task', 'current_task_nonce'); ?>
                        <select name="task_id" id="current-task-selector">
                            <option value=""><?php esc_html_e('Select a task...', 'tradepress'); ?></option>
                            <?php foreach ($all_tasks as $task): ?>
                                <option value="<?php echo esc_attr($task['id']); ?>" <?php selected($current_task['id'], $task['id']); ?>>
                                    <?php echo esc_html($task['title'] . ' (' . $task['source_label'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="set_current_task" class="button button-primary">
                            <?php esc_html_e('Set as Current Task', 'tradepress'); ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="current-task-content">
                <?php if (!empty($current_task)): ?>
                    <div class="task-details-card">
                        <div class="task-details-header">
                            <h3 class="task-title"><?php echo esc_html($current_task['title']); ?></h3>
                            <div class="task-meta">
                                <span class="task-source">
                                    <span class="dashicons dashicons-tag"></span>
                                    <?php echo esc_html($current_task['source_label']); ?>
                                </span>
                                <span class="task-phase">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    <?php echo esc_html(sprintf(__('Phase %d', 'tradepress'), $current_task['phase'])); ?>
                                </span>
                                <span class="task-priority">
                                    <span class="dashicons dashicons-flag"></span>
                                    <?php 
                                    switch (intval($current_task['priority'])) {
                                        case 1: echo esc_html(__('High Priority', 'tradepress')); break;
                                        case 2: echo esc_html(__('Medium Priority', 'tradepress')); break;
                                        case 3: echo esc_html(__('Low Priority', 'tradepress')); break;
                                        default: echo esc_html(__('Medium Priority', 'tradepress'));
                                    }
                                    ?>
                                </span>
                                <span class="task-status">
                                    <span class="dashicons dashicons-marker"></span>
                                    <?php 
                                    switch ($current_task['status']) {
                                        case 'pending': echo esc_html(__('Pending', 'tradepress')); break;
                                        case 'in-progress': echo esc_html(__('In Progress', 'tradepress')); break;
                                        case 'completed': echo esc_html(__('Completed', 'tradepress')); break;
                                        default: echo esc_html(__('Pending', 'tradepress'));
                                    }
                                    ?>
                                </span>
                                <span class="task-github-status">
                                    <span class="dashicons <?php echo $current_task['is_github_issue'] ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                                    <?php echo $current_task['is_github_issue'] 
                                        ? esc_html(__('GitHub Issue', 'tradepress')) 
                                        : esc_html(__('No GitHub Issue', 'tradepress')); ?>
                                    <?php if (!empty($current_task['github_url'])): ?>
                                        <a href="<?php echo esc_url($current_task['github_url']); ?>" target="_blank" class="github-link">
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="task-details-body">
                            <?php if (!empty($current_task['description'])): ?>
                                <div class="task-description">
                                    <h4><?php esc_html_e('Description', 'tradepress'); ?></h4>
                                    <div class="description-content">
                                        <?php echo self::format_markdown($current_task['description']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($current_task['notes'])): ?>
                                <div class="task-notes">
                                    <h4><?php esc_html_e('Implementation Notes', 'tradepress'); ?></h4>
                                    <div class="notes-content">
                                        <?php echo self::format_markdown($current_task['notes']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($current_task['ai_guidance'])): ?>
                                <div class="task-ai-guidance">
                                    <h4><?php esc_html_e('AI Guidance', 'tradepress'); ?></h4>
                                    <div class="ai-guidance-content">
                                        <?php echo self::format_markdown($current_task['ai_guidance']); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($current_task['subtasks'])): ?>
                                <div class="task-subtasks">
                                    <h4><?php esc_html_e('Subtasks', 'tradepress'); ?></h4>
                                    <form method="post" id="update-subtasks-form">
                                        <?php wp_nonce_field('tradepress_update_subtasks', 'subtasks_nonce'); ?>
                                        <input type="hidden" name="task_id" value="<?php echo esc_attr($current_task['id']); ?>">
                                        
                                        <ul class="subtasks-list">
                                            <?php foreach ($current_task['subtasks'] as $index => $subtask): ?>
                                                <li class="subtask-item <?php echo $subtask['status']; ?>">
                                                    <label>
                                                        <input type="checkbox" 
                                                            name="subtask_complete[<?php echo $index; ?>]" 
                                                            value="1" 
                                                            <?php checked($subtask['status'], 'completed'); ?>>
                                                        <span class="subtask-title"><?php echo esc_html($subtask['title']); ?></span>
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        
                                        <div class="subtasks-actions">
                                            <button type="submit" name="update_subtasks" class="button">
                                                <?php esc_html_e('Update Subtasks', 'tradepress'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="task-details-footer">
                            <div class="task-actions">
                                <?php if ($current_task['status'] !== 'completed'): ?>
                                    <form method="post" class="task-status-form">
                                        <?php wp_nonce_field('tradepress_update_task_status', 'task_status_nonce'); ?>
                                        <input type="hidden" name="task_id" value="<?php echo esc_attr($current_task['id']); ?>">
                                        
                                        <?php if ($current_task['status'] == 'pending'): ?>
                                            <button type="submit" name="mark_in_progress" class="button">
                                                <span class="dashicons dashicons-controls-play"></span>
                                                <?php esc_html_e('Start Working on Task', 'tradepress'); ?>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="submit" name="mark_completed" class="button button-primary">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php esc_html_e('Mark Task Completed', 'tradepress'); ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="task-completed-message">
                                        <span class="dashicons dashicons-saved"></span>
                                        <?php esc_html_e('This task has been completed!', 'tradepress'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                // Display test message if set
                                if (isset($_GET['test_message']) && !empty($_GET['test_message'])) {
                                    echo '<div class="task-test-message"><p>' . esc_html(urldecode($_GET['test_message'])) . '</p></div>';
                                }
                                ?>
                                
                                <?php if (!empty($current_task['link'])): ?>
                                    <a href="<?php echo esc_url($current_task['link']); ?>" target="_blank" class="button">
                                        <span class="dashicons dashicons-external"></span>
                                        <?php esc_html_e('View Related Link', 'tradepress'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="task-notes-section">
                        <h3><?php esc_html_e('Working Notes', 'tradepress'); ?></h3>
                        <form method="post" id="task-notes-form">
                            <?php wp_nonce_field('tradepress_update_task_working_notes', 'task_notes_nonce'); ?>
                            <input type="hidden" name="task_id" value="<?php echo esc_attr($current_task['id']); ?>">
                            
                            <textarea name="working_notes" id="task-working-notes" rows="8" placeholder="<?php esc_attr_e('Add your working notes for this task here...', 'tradepress'); ?>"><?php echo esc_textarea(isset($current_task['working_notes']) ? $current_task['working_notes'] : ''); ?></textarea>
                            
                            <button type="submit" name="update_working_notes" class="button button-primary">
                                <?php esc_html_e('Save Notes', 'tradepress'); ?>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="no-current-task">
                        <p><?php esc_html_e('No task is currently selected. Please select a task to work on from the dropdown above.', 'tradepress'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($task_history)): ?>
                <div class="recent-tasks-section">
                    <h3><?php esc_html_e('Recently Viewed Tasks', 'tradepress'); ?></h3>
                    <ul class="recent-tasks-list">
                        <?php foreach ($task_history as $task): ?>
                            <li class="recent-task-item">
                                <form method="post" class="select-task-form">
                                    <?php wp_nonce_field('tradepress_select_current_task', 'current_task_nonce'); ?>
                                    <input type="hidden" name="task_id" value="<?php echo esc_attr($task['id']); ?>">
                                    
                                    <button type="submit" name="set_current_task" class="text-button">
                                        <?php echo esc_html($task['title']); ?>
                                    </button>
                                    <span class="task-source"><?php echo esc_html($task['source_label']); ?></span>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div><!-- .current-task-container -->
        <?php
        
        // Process form submissions
        self::process_forms();
    }
    
    /**
     * Format markdown text into HTML
     *
     * @param string $text Text to format
     * @return string Formatted HTML
     */
    private static function format_markdown($text) {
        if (empty($text)) {
            return '';
        }
        
        // Very basic markdown conversion
        $html = nl2br(esc_html($text));
        
        // Convert backticks to code tags
        $html = preg_replace('/`(.*?)`/', '<code>$1</code>', $html);
        
        return $html;
    }
    
    /**
     * Get the currently selected task
     *
     * @return array|null Current task data or null if none selected
     */
    private static function get_current_task() {
        $current_task = get_option('tradepress_current_task', null);
        
        if ($current_task) {
            // Set default GitHub status
            $current_task['is_github_issue'] = false;
            $current_task['github_url'] = '';
            
            // Check if this is already a GitHub issue
            if ($current_task['source'] === 'github') {
                $current_task['is_github_issue'] = true;
                $current_task['github_url'] = $current_task['link'];
            } else {
                // Check if a GitHub issue was created for this task
                $github_issues = self::get_github_issues_by_task_id($current_task['id']);
                if (!empty($github_issues)) {
                    $current_task['is_github_issue'] = true;
                    $current_task['github_url'] = $github_issues[0]['html_url'];
                    $current_task['github_issue_number'] = $github_issues[0]['number'];
                }
            }
        } else {
            // If no current task, use a null value that won't cause errors when accessing keys
            $current_task = null;
        }
        
        return $current_task;
    }
    
    /**
     * Get GitHub issues that were created for a specific task ID
     *
     * @param string $task_id Task ID to search for
     * @return array GitHub issues related to this task
     */
    private static function get_github_issues_by_task_id($task_id) {
        $issues = array();
        
        // Get repository settings
        $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
        $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
        
        if (empty($repo_owner) || empty($repo_name)) {
            return $issues;
        }
        
        // Try to get issues from the GitHub API
        if (function_exists('tradepress_get_github_issues')) {
            $all_issues = tradepress_get_github_issues($repo_owner, $repo_name);
            
            if (!is_wp_error($all_issues) && !empty($all_issues)) {
                foreach ($all_issues as $issue) {
                    // Check if the issue body contains a reference to this task ID
                    if (isset($issue->body) && strpos($issue->body, "Original task ID: `{$task_id}`") !== false) {
                        $issues[] = array(
                            'number' => $issue->number,
                            'html_url' => $issue->html_url,
                            'title' => $issue->title,
                            'state' => $issue->state
                        );
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Get recently viewed tasks
     *
     * @param int $limit Number of tasks to retrieve
     * @return array Recent tasks
     */
    private static function get_task_history($limit = 5) {
        $history = get_option('tradepress_task_history', array());
        
        // Return only the specified number of recent tasks
        return array_slice($history, 0, $limit);
    }
    
    /**
     * Get all available tasks
     * 
     * @return array All tasks
     */
    public static function get_all_tasks() {
        // Use the same function as the Tasks tab
        if (!class_exists('TradePress_Admin_Development_Tasks')) {
            require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/view/tasks.php';
        }
        
        // Get technical tasks, GitHub tasks, and feature tasks
        $technical_tasks = self::get_technical_doc_tasks();
        $github_tasks = self::get_github_tasks();
        $feature_tasks = self::get_feature_tasks();
        
        // Merge all tasks
        return array_merge($technical_tasks, $github_tasks, $feature_tasks);
    }
    
    /**
     * Get a demo task to display when no task is selected
     * 
     * @return array Demo task data
     */
    private static function get_demo_task() {
        return array(
            'id' => 'demo_task',
            'title' => 'Get Started with Current Task Tab',
            'source_label' => 'Onboarding',
            'source' => 'onboarding',
            'status' => 'pending',
            'phase' => 1,
            'priority' => 2,
            'is_github_issue' => false, // Add this to prevent undefined key error
            'github_url' => '', // Add default empty URL
            'link' => '',
            'description' => "This tab helps you focus on one development task at a time. Select a real task from the dropdown above to get started.",
            'notes' => "The Current Task tab allows you to:\n\n1. Focus on one task at a time\n2. Track subtask completion\n3. Add working notes\n4. View task details in one place",
            'ai_guidance' => "Use this tab to maintain focus on the most important development task. By working on one task at a time, you'll make more consistent progress.",
            'subtasks' => array(
                array(
                    'title' => 'Select a development task from the dropdown',
                    'status' => 'pending'
                ),
                array(
                    'title' => 'Mark tasks as in-progress or completed',
                    'status' => 'pending'
                )
            )
        );
    }
    
    /**
     * Process form submissions
     * 
     * @see TradePress_Current_Task_Handler For the actual form processing
     */
    private static function process_forms() {
        // In the updated architecture, form processing is handled by TradePress_Current_Task_Handler
        // This method remains as a lightweight router to maintain backward compatibility
        
        // The action hooks for processing forms are registered in TradePress_Current_Task_Handler::init()
    }
    
    /**
     * Update task history with recently viewed task
     *
     * @param array $task Task data
     * @return bool Success status
     */
    private static function update_task_history($task) {
        // Simplify the task data for history
        $history_task = array(
            'id' => $task['id'],
            'title' => $task['title'],
            'source_label' => $task['source_label']
        );
        
        // Get existing history
        $history = get_option('tradepress_task_history', array());
        
        // Remove this task if it already exists in history 
        foreach ($history as $key => $item) {
            if ($item['id'] === $task['id']) {
                unset($history[$key]);
                break;
            }
        }
        
        // Add task to the beginning of the array
        array_unshift($history, $history_task);
        
        // Limit history to 10 items
        $history = array_slice($history, 0, 10);
        
        // Save updated history
        return update_option('tradepress_task_history', $history);
    }
    
    /**
     * Update subtasks completion status
     *
     * @param string $task_id Task ID
     * @param array $subtask_complete Array of completed subtask indexes
     * @return bool Success status
     */
    private static function update_subtasks($task_id, $subtask_complete) {
        // Get current task
        $current_task = get_option('tradepress_current_task', null);
        
        // Check if task exists and matches the requested task ID
        if (empty($current_task) || $current_task['id'] !== $task_id) {
            return false;
        }
        
        // Update subtask status
        if (!empty($current_task['subtasks'])) {
            foreach ($current_task['subtasks'] as $index => $subtask) {
                $current_task['subtasks'][$index]['status'] = 
                    isset($subtask_complete[$index]) ? 'completed' : 'pending';
            }
        }
        
        // Save updated task
        return update_option('tradepress_current_task', $current_task);
    }
    
    /**
     * Update task status
     *
     * @param string $task_id Task ID
     * @param string $status New status (pending, in-progress, completed)
     * @return bool Success status 
     */
    private static function update_task_status($task_id, $status) {
        // Get current task
        $current_task = get_option('tradepress_current_task', null);
        
        // Check if task exists and matches the requested task ID
        if (empty($current_task) || $current_task['id'] !== $task_id) {
            return false;
        }
        
        // Update task status
        $current_task['status'] = $status;
        
        // Save updated task
        return update_option('tradepress_current_task', $current_task);
    }
    
    /**
     * Update working notes for a task
     *
     * @param string $task_id Task ID
     * @param string $notes Working notes
     * @return bool Success status 
     */
    private static function update_working_notes($task_id, $notes) {
        // Get current task
        $current_task = get_option('tradepress_current_task', null);
        
        // Check if task exists and matches the requested task ID
        if (empty($current_task) || $current_task['id'] !== $task_id) {
            return false;
        }
        
        // Update working notes
        $current_task['working_notes'] = $notes;
        
        // Save updated task
        return update_option('tradepress_current_task', $current_task);
    }
    
    /**
     * Get technical doc tasks (using same method from Tasks tab)
     * 
     * @return array Array of tasks from technical documentation
     */
    private static function get_technical_doc_tasks() {
        // Reuse the method from the Tasks tab
        if (method_exists('TradePress_Admin_Development_Tasks', 'get_technical_doc_tasks')) {
            return TradePress_Admin_Development_Tasks::get_technical_doc_tasks();
        }
        return array();
    }
    
    /**
     * Get GitHub issues formatted as tasks (using same method from Tasks tab)
     * 
     * @return array Array of tasks from GitHub issues
     */
    private static function get_github_tasks() {
        // Reuse the method from the Tasks tab
        if (method_exists('TradePress_Admin_Development_Tasks', 'get_github_tasks')) {
            return TradePress_Admin_Development_Tasks::get_github_tasks();
        }
        return array();
    }
    
    /**
     * Get feature definitions formatted as tasks (using same method from Tasks tab)
     * 
     * @return array Array of tasks from feature definitions
     */
    private static function get_feature_tasks() {
        // Use the function from feature-loader.php
        if (function_exists('tradepress_get_features_as_tasks')) {
            return tradepress_get_features_as_tasks();
        }
        return array();
    }
}
