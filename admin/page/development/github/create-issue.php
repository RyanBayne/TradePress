<?php
/**
 * GitHub Create Issue Tab
 * 
 * Form to create a new GitHub issue using the GitHub API.
 * 
 * @package TradePress/Admin/roadmap/GitHub
 * @version 1.0.2
 * @since 1.0.0
 * @created 2023-10-26
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include GitHub helper functions - this now includes the centralized API functions
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/github/github-helpers.php';

/**
 * Display content for the Create Issue subtab
 * 
 * @param string $repo_owner GitHub repository owner
 * @param string $repo_name GitHub repository name
 */
function TRADEPRESS_GITHUB_create_issue_content($repo_owner, $repo_name) {
    // Get GitHub API settings
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', $repo_owner);
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', $repo_name);
    
    // Basic validation
    if (empty($github_token) || empty($repo_owner) || empty($repo_name)) {
        ?>
        <div class="github-create-issue-wrapper">
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
    
    // Process form submission
    $form_message = '';
    $form_status = '';
    
    if (isset($_POST['github_create_issue']) && check_admin_referer('tradepress_create_github_issue', 'github_issue_nonce')) {
        // Sanitize form data
        $issue_title = isset($_POST['issue_title']) ? sanitize_text_field($_POST['issue_title']) : '';
        $issue_body = isset($_POST['issue_body']) ? sanitize_textarea_field($_POST['issue_body']) : '';
        $issue_labels = isset($_POST['issue_labels']) ? array_map('sanitize_text_field', $_POST['issue_labels']) : array();
        $issue_milestone = isset($_POST['issue_milestone']) ? intval($_POST['issue_milestone']) : null;
        $issue_assignees = isset($_POST['issue_assignees']) ? array_map('sanitize_text_field', $_POST['issue_assignees']) : array();
        
        // Validate required fields
        if (empty($issue_title)) {
            $form_message = __('Issue title is required.', 'tradepress');
            $form_status = 'error';
        } else {
            // Prepare issue data
            $issue_data = array(
                'title' => $issue_title,
                'body' => $issue_body,
            );
            
            if (!empty($issue_labels)) {
                $issue_data['labels'] = $issue_labels;
            }
            
            if (!empty($issue_milestone)) {
                $issue_data['milestone'] = $issue_milestone;
            }
            
            if (!empty($issue_assignees)) {
                $issue_data['assignees'] = $issue_assignees;
            }
            
            // Create the issue - using the centralized function
            $result = tradepress_create_github_issue($repo_owner, $repo_name, $issue_data);
            
            if (is_wp_error($result)) {
                $form_message = $result->get_error_message();
                $form_status = 'error';
            } else {
                $form_message = sprintf(
                    __('Issue created successfully! <a href="%s" target="_blank">View issue</a>', 'tradepress'),
                    esc_url($result->html_url)
                );
                $form_status = 'success';
                
                // Reset form fields
                $issue_title = '';
                $issue_body = '';
                $issue_labels = array();
                $issue_milestone = null;
                $issue_assignees = array();
            }
        }
    }
    
    // Get available labels and milestones for the form
    $labels = tradepress_get_github_labels($repo_owner, $repo_name, $github_token);
    $milestones = tradepress_get_github_milestones($repo_owner, $repo_name, $github_token);
    $collaborators = tradepress_get_github_collaborators($repo_owner, $repo_name, $github_token);
    ?>
    <div class="github-create-issue-wrapper">
        <h3><?php esc_html_e('Create New GitHub Issue', 'tradepress'); ?></h3>
        
        <?php if (!empty($form_message)) : ?>
            <div class="notice notice-<?php echo esc_attr($form_status); ?>">
                <p><?php echo wp_kses_post($form_message); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" class="github-issue-form">
            <?php wp_nonce_field('tradepress_create_github_issue', 'github_issue_nonce'); ?>
            
            <div class="github-form-row">
                <label for="issue_title"><?php esc_html_e('Issue Title', 'tradepress'); ?> <span class="required">*</span></label>
                <input type="text" id="issue_title" name="issue_title" value="<?php echo isset($issue_title) ? esc_attr($issue_title) : ''; ?>" required>
                <p class="description"><?php esc_html_e('A concise summary of the issue or feature request.', 'tradepress'); ?></p>
            </div>
            
            <div class="github-form-row">
                <label for="issue_body"><?php esc_html_e('Description', 'tradepress'); ?></label>
                <textarea id="issue_body" name="issue_body" rows="10"><?php echo isset($issue_body) ? esc_textarea($issue_body) : ''; ?></textarea>
                <p class="description">
                    <?php esc_html_e('Describe the issue in detail. GitHub markdown is supported.', 'tradepress'); ?>
                    <a href="https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax" target="_blank">
                        <?php esc_html_e('Markdown help', 'tradepress'); ?> <span class="dashicons dashicons-external"></span>
                    </a>
                </p>
            </div>
            
            <div class="github-form-layout">
                <div class="github-form-main">
                    <div class="github-form-preview">
                        <h4><?php esc_html_e('Preview', 'tradepress'); ?></h4>
                        <div class="preview-area" id="markdown-preview">
                            <p class="description"><?php esc_html_e('Enter content in the description field to see a preview.', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="github-form-sidebar">
                    <?php if (!is_wp_error($labels) && !empty($labels)) : ?>
                        <div class="github-form-field">
                            <label><?php esc_html_e('Labels', 'tradepress'); ?></label>
                            <div class="github-labels-selector">
                                <?php foreach ($labels as $label) : ?>
                                    <div class="github-label-option">
                                        <input type="checkbox" name="issue_labels[]" id="label_<?php echo esc_attr($label->name); ?>" value="<?php echo esc_attr($label->name); ?>" <?php checked(isset($issue_labels) && in_array($label->name, $issue_labels)); ?>>
                                        <label for="label_<?php echo esc_attr($label->name); ?>">
                                            <span class="label-color" style="background-color: #<?php echo esc_attr($label->color); ?>"></span>
                                            <span class="label-name"><?php echo esc_html($label->name); ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!is_wp_error($milestones) && !empty($milestones)) : ?>
                        <div class="github-form-field">
                            <label for="issue_milestone"><?php esc_html_e('Milestone', 'tradepress'); ?></label>
                            <select name="issue_milestone" id="issue_milestone">
                                <option value=""><?php esc_html_e('No milestone', 'tradepress'); ?></option>
                                <?php foreach ($milestones as $milestone) : ?>
                                    <option value="<?php echo esc_attr($milestone->number); ?>" <?php selected(isset($issue_milestone) && $issue_milestone == $milestone->number); ?>>
                                        <?php echo esc_html($milestone->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!is_wp_error($collaborators) && !empty($collaborators)) : ?>
                        <div class="github-form-field">
                            <label><?php esc_html_e('Assignees', 'tradepress'); ?></label>
                            <div class="github-assignees-selector">
                                <?php foreach ($collaborators as $collaborator) : ?>
                                    <div class="github-assignee-option">
                                        <input type="checkbox" name="issue_assignees[]" id="assignee_<?php echo esc_attr($collaborator->login); ?>" value="<?php echo esc_attr($collaborator->login); ?>" <?php checked(isset($issue_assignees) && in_array($collaborator->login, $issue_assignees)); ?>>
                                        <label for="assignee_<?php echo esc_attr($collaborator->login); ?>">
                                            <img src="<?php echo esc_url($collaborator->avatar_url); ?>" alt="" class="avatar" width="24" height="24">
                                            <span class="assignee-name"><?php echo esc_html($collaborator->login); ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="github-form-actions">
                <button type="submit" name="github_create_issue" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Create Issue', 'tradepress'); ?>
                </button>
            </div>
        </form>
    </div>
    <?php
}
