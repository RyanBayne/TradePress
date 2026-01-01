<?php
/**
 * TradePress Current Task Handler
 * 
 * Processes all POST submissions related to the Current Task feature.
 * Responsible for updating task status, notes, and subtasks.
 * 
 * @package TradePress\Admin\Handlers
 * @version 1.0.0
 * @see     TradePress_Admin_Development_Current_Task For the UI implementation
 * @see     TradePress_Listener For the main request handling
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_Current_Task_Handler
 */
class TradePress_Current_Task_Handler {
    
    /**
     * Initialize the handler
     * 
     * @return void
     */
    public static function init() {
        // Register POST handlers
        add_action('tradepress_process_form_select_current_task', array(__CLASS__, 'handle_select_current_task'));
        add_action('tradepress_process_form_update_task_working_notes', array(__CLASS__, 'handle_update_working_notes'));
        add_action('tradepress_process_form_update_subtasks', array(__CLASS__, 'handle_update_subtasks'));
        add_action('tradepress_process_form_update_task_status', array(__CLASS__, 'handle_update_task_status'));
    }
    
    /**
     * Handle selecting a current task
     * 
     * @return void
     */
    public static function handle_select_current_task() {
        if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
            self::add_admin_notice('error', __('No task selected.', 'tradepress'));
            return;
        }
        
        $task_id = sanitize_text_field($_POST['task_id']);
        
        // Get all tasks
        $all_tasks = TradePress_Admin_Development_Current_Task::get_all_tasks();
        $selected_task = null;
        
        // Find the task with the matching ID
        foreach ($all_tasks as $task) {
            if ($task['id'] === $task_id) {
                $selected_task = $task;
                break;
            }
        }
        
        if (!$selected_task) {
            self::add_admin_notice('error', __('Selected task not found.', 'tradepress'));
            return;
        }
        
        // Save as current task
        update_option('tradepress_current_task', $selected_task);
        
        // Update task history
        self::update_task_history($selected_task);
        
        self::add_admin_notice('success', __('Current task updated successfully.', 'tradepress'));
        
        // Redirect to prevent form resubmission
        wp_safe_redirect(remove_query_arg(array('updated', 'error')));
        exit;
    }
    
    /**
     * Handle updating task working notes
     * 
     * @return void
     */
    public static function handle_update_working_notes() {
        if (!isset($_POST['task_id']) || empty($_POST['task_id']) || !isset($_POST['working_notes'])) {
            return;
        }
        
        $task_id = sanitize_text_field($_POST['task_id']);
        $notes = wp_kses_post($_POST['working_notes']);
        
        // Get current task
        $current_task = get_option('tradepress_current_task', null);
        
        // Check if task exists and matches the requested task ID
        if (empty($current_task) || $current_task['id'] !== $task_id) {
            self::add_admin_notice('error', __('Task not found.', 'tradepress'));
            return;
        }
        
        // Update working notes
        $current_task['working_notes'] = $notes;
        
        // Save updated task
        update_option('tradepress_current_task', $current_task);
        
        self::add_admin_notice('success', __('Working notes updated successfully.', 'tradepress'));
        
        // Redirect to prevent form resubmission
        wp_safe_redirect(remove_query_arg(array('updated', 'error')));
        exit;
    }
    
    /**
     * Handle updating subtasks
     * 
     * @return void
     */
    public static function handle_update_subtasks() {
        if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
            return;
        }
        
        $task_id = sanitize_text_field($_POST['task_id']);
        $subtask_complete = isset($_POST['subtask_complete']) ? $_POST['subtask_complete'] : array();
        
        // Get current task
        $current_task = get_option('tradepress_current_task', null);
        
        // Check if task exists and matches the requested task ID
        if (empty($current_task) || $current_task['id'] !== $task_id) {
            self::add_admin_notice('error', __('Task not found.', 'tradepress'));
            return;
        }
        
        // Update subtask status
        if (!empty($current_task['subtasks'])) {
            foreach ($current_task['subtasks'] as $index => $subtask) {
                $current_task['subtasks'][$index]['status'] = 
                    isset($subtask_complete[$index]) ? 'completed' : 'pending';
            }
        }
        
        // Save updated task
        update_option('tradepress_current_task', $current_task);
        
        self::add_admin_notice('success', __('Subtasks updated successfully.', 'tradepress'));
        
        // Redirect to prevent form resubmission
        wp_safe_redirect(remove_query_arg(array('updated', 'error')));
        exit;
    }
    
    /**
     * Handle updating task status
     * 
     * @return void
     */
    public static function handle_update_task_status() {
        if (!isset($_POST['task_id']) || empty($_POST['task_id'])) {
            return;
        }
        
        $task_id = sanitize_text_field($_POST['task_id']);
        
        // Determine the new status
        $new_status = isset($_POST['mark_completed']) ? 'completed' : 
                     (isset($_POST['mark_in_progress']) ? 'in-progress' : '');
        
        if (empty($new_status)) {
            return;
        }
        
        // Get current task
        $current_task = get_option('tradepress_current_task', null);
        
        // Check if task exists and matches the requested task ID
        if (empty($current_task) || $current_task['id'] !== $task_id) {
            self::add_admin_notice('error', __('Task not found.', 'tradepress'));
            return;
        }
        
        // Check if we're in demo mode
        $demo_mode = defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE;
        
        if ($demo_mode) {
            // For demo mode, we just update the status without any actual processing
            $current_task['status'] = $new_status;
            
            // If starting a task, add a demo timestamp
            if ($new_status === 'in-progress') {
                $current_task['started_at'] = current_time('mysql');
                // Add a demo message when starting a task in demo mode
                self::add_admin_notice('info', 
                    __('DEMO MODE: Task status changed to In Progress. In normal mode, this would trigger workflow events.', 'tradepress')
                );
            } elseif ($new_status === 'completed') {
                $current_task['completed_at'] = current_time('mysql');
                self::add_admin_notice('success', 
                    __('DEMO MODE: Task marked as completed. Great job!', 'tradepress')
                );
            }
        } else {
            // Normal mode processing
            $current_task['status'] = $new_status;
            
            if ($new_status === 'in-progress') {
                $current_task['started_at'] = current_time('mysql');
                self::add_admin_notice('success', __('Task status updated to In Progress.', 'tradepress'));
            } elseif ($new_status === 'completed') {
                $current_task['completed_at'] = current_time('mysql');
                self::add_admin_notice('success', __('Task marked as completed.', 'tradepress'));
            }
        }
        
        // Save updated task
        update_option('tradepress_current_task', $current_task);
        
        // Redirect to prevent form resubmission
        wp_safe_redirect(remove_query_arg(array('updated', 'error')));
        exit;
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
     * Add admin notice
     *
     * @param string $type Notice type (success, error, warning, info)
     * @param string $message Notice message
     * @return void
     */
    private static function add_admin_notice($type, $message) {
        // Store the notice in a transient
        $notices = get_transient('tradepress_admin_notices') ?: array();
        $notices[] = array(
            'type' => $type,
            'message' => $message
        );
        set_transient('tradepress_admin_notices', $notices, 60);
    }
}

// Initialize the handler
TradePress_Current_Task_Handler::init();
