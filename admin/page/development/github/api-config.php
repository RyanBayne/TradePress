<?php
/**
 * GitHub API Configuration Tab
 * 
 * Settings to configure GitHub API integration.
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

/**
 * Display content for the API Configuration subtab
 * 
 * @param string $repo_owner GitHub repository owner
 * @param string $repo_name GitHub repository name
 */
function TRADEPRESS_GITHUB_api_config_content($repo_owner, $repo_name) {
    // Process form submission
    if (isset($_POST['submit_github_config'])) {
        // Verify nonce
        if (check_admin_referer('TRADEPRESS_GITHUB_config_nonce', 'TRADEPRESS_GITHUB_nonce')) {
            // Sanitize and save settings
            $github_token = isset($_POST['github_token']) ? sanitize_text_field($_POST['github_token']) : '';
            $repo_owner = isset($_POST['repo_owner']) ? sanitize_text_field($_POST['repo_owner']) : '';
            $repo_name = isset($_POST['repo_name']) ? sanitize_text_field($_POST['repo_name']) : '';
            $sync_frequency = isset($_POST['sync_frequency']) ? absint($_POST['sync_frequency']) : 24;
            
            // Feature toggles
            $enable_issues = isset($_POST['enable_issues']) ? 'yes' : 'no';
            $enable_prs = isset($_POST['enable_prs']) ? 'yes' : 'no';
            $enable_releases = isset($_POST['enable_releases']) ? 'yes' : 'no';
            
            // Save to WordPress options
            update_option('TRADEPRESS_GITHUB_token', $github_token);
            update_option('TRADEPRESS_GITHUB_repo_owner', $repo_owner);
            update_option('TRADEPRESS_GITHUB_repo_name', $repo_name);
            update_option('TRADEPRESS_GITHUB_sync_frequency', $sync_frequency);
            update_option('TRADEPRESS_GITHUB_enable_issues', $enable_issues);
            update_option('TRADEPRESS_GITHUB_enable_prs', $enable_prs);
            update_option('TRADEPRESS_GITHUB_enable_releases', $enable_releases);
            
            // Show success message
            add_settings_error(
                'TRADEPRESS_GITHUB_settings',
                'settings_updated',
                __('GitHub API settings saved successfully.', 'tradepress'),
                'updated'
            );
        }
    }
    
    // Process test connection
    if (isset($_POST['test_github_connection'])) {
        if (check_admin_referer('TRADEPRESS_GITHUB_config_nonce', 'TRADEPRESS_GITHUB_nonce')) {
            $test_result = tradepress_test_github_connection();
            
            if (is_wp_error($test_result)) {
                add_settings_error(
                    'TRADEPRESS_GITHUB_settings',
                    'connection_failed',
                    sprintf(__('Connection failed: %s', 'tradepress'), $test_result->get_error_message()),
                    'error'
                );
            } else {
                add_settings_error(
                    'TRADEPRESS_GITHUB_settings',
                    'connection_success',
                    __('GitHub API connection successful!', 'tradepress'),
                    'updated'
                );
            }
        }
    }
    
    // Get current settings
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', $repo_owner);
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', $repo_name);
    $sync_frequency = get_option('TRADEPRESS_GITHUB_sync_frequency', 24);
    $enable_issues = get_option('TRADEPRESS_GITHUB_enable_issues', 'yes');
    $enable_prs = get_option('TRADEPRESS_GITHUB_enable_prs', 'yes');
    $enable_releases = get_option('TRADEPRESS_GITHUB_enable_releases', 'yes');
    
    // Display settings errors
    settings_errors('TRADEPRESS_GITHUB_settings');
    ?>
    <div class="github-api-config-wrapper">
        <h3><?php esc_html_e('GitHub API Configuration', 'tradepress'); ?></h3>
        <p class="description">
            <?php esc_html_e('Configure the GitHub API integration for the TradePress plugin. A personal access token with appropriate permissions is required.', 'tradepress'); ?>
            <a href="https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/creating-a-personal-access-token" target="_blank">
                <?php esc_html_e('Learn how to create a token', 'tradepress'); ?> <span class="dashicons dashicons-external"></span>
            </a>
        </p>
        
        <form method="post" action="" class="github-config-form">
            <?php wp_nonce_field('TRADEPRESS_GITHUB_config_nonce', 'TRADEPRESS_GITHUB_nonce'); ?>
            
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="github_token"><?php esc_html_e('GitHub API Token', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input name="github_token" type="password" id="github_token" 
                                value="<?php echo esc_attr($github_token); ?>" class="regular-text" 
                                autocomplete="off" placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxx">
                            <p class="description">
                                <?php esc_html_e('Personal access token with repo scope permissions.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="repo_owner"><?php esc_html_e('Repository Owner', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input name="repo_owner" type="text" id="repo_owner" 
                                value="<?php echo esc_attr($repo_owner); ?>" class="regular-text"
                                placeholder="username or organization">
                            <p class="description">
                                <?php esc_html_e('GitHub username or organization that owns the repository.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="repo_name"><?php esc_html_e('Repository Name', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <input name="repo_name" type="text" id="repo_name" 
                                value="<?php echo esc_attr($repo_name); ?>" class="regular-text"
                                placeholder="repository-name">
                            <p class="description">
                                <?php esc_html_e('Name of the GitHub repository (not the full URL).', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="sync_frequency"><?php esc_html_e('Sync Frequency', 'tradepress'); ?></label>
                        </th>
                        <td>
                            <select name="sync_frequency" id="sync_frequency">
                                <option value="1" <?php selected($sync_frequency, 1); ?>><?php esc_html_e('Every hour', 'tradepress'); ?></option>
                                <option value="3" <?php selected($sync_frequency, 3); ?>><?php esc_html_e('Every 3 hours', 'tradepress'); ?></option>
                                <option value="6" <?php selected($sync_frequency, 6); ?>><?php esc_html_e('Every 6 hours', 'tradepress'); ?></option>
                                <option value="12" <?php selected($sync_frequency, 12); ?>><?php esc_html_e('Every 12 hours', 'tradepress'); ?></option>
                                <option value="24" <?php selected($sync_frequency, 24); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                <option value="168" <?php selected($sync_frequency, 168); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('How often to synchronize GitHub data with TradePress.', 'tradepress'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Features', 'tradepress'); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('GitHub Features to Enable', 'tradepress'); ?></legend>
                                
                                <label for="enable_issues">
                                    <input name="enable_issues" type="checkbox" id="enable_issues" value="yes" 
                                        <?php checked($enable_issues, 'yes'); ?>>
                                    <?php esc_html_e('Issues', 'tradepress'); ?>
                                </label><br>
                                
                                <label for="enable_prs">
                                    <input name="enable_prs" type="checkbox" id="enable_prs" value="yes" 
                                        <?php checked($enable_prs, 'yes'); ?>>
                                    <?php esc_html_e('Pull Requests', 'tradepress'); ?>
                                </label><br>
                                
                                <label for="enable_releases">
                                    <input name="enable_releases" type="checkbox" id="enable_releases" value="yes" 
                                        <?php checked($enable_releases, 'yes'); ?>>
                                    <?php esc_html_e('Releases', 'tradepress'); ?>
                                </label>
                                
                                <p class="description">
                                    <?php esc_html_e('Select which GitHub features to integrate with TradePress.', 'tradepress'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="github-api-actions">
                <input type="submit" name="submit_github_config" id="submit_github_config" 
                    class="button button-primary" value="<?php esc_attr_e('Save Settings', 'tradepress'); ?>">
                
                <input type="submit" name="test_github_connection" id="test_github_connection" 
                    class="button" value="<?php esc_attr_e('Test Connection', 'tradepress'); ?>">
            </div>
        </form>
        
        <?php
        // Show rate limit info if token is configured
        if (!empty($github_token)) {
            $rate_limits = tradepress_get_github_rate_limits();
            if (!is_wp_error($rate_limits)) {
                ?>
                <div class="github-rate-limits">
                    <h4><?php esc_html_e('GitHub API Rate Limits', 'tradepress'); ?></h4>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Remaining', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Limit', 'tradepress'); ?></th>
                                <th><?php esc_html_e('Reset Time', 'tradepress'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rate_limits as $type => $data): ?>
                                <tr>
                                    <td><?php echo esc_html($type); ?></td>
                                    <td><?php echo esc_html($data['remaining']); ?></td>
                                    <td><?php echo esc_html($data['limit']); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $data['reset'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            }
        }
        ?>
        
        <div class="github-api-docs">
            <h4><?php esc_html_e('Required API Scopes', 'tradepress'); ?></h4>
            <p><?php esc_html_e('Your GitHub Personal Access Token requires the following scopes:', 'tradepress'); ?></p>
            <ul class="github-scopes-list">
                <li><code>repo</code> - <?php esc_html_e('Full control of private repositories', 'tradepress'); ?></li>
                <li><code>read:org</code> - <?php esc_html_e('Read organization membership (for org repositories)', 'tradepress'); ?></li>
                <li><code>user:email</code> - <?php esc_html_e('Access user email addresses (read-only)', 'tradepress'); ?></li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Test GitHub API connection
 * 
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function tradepress_test_github_connection() {
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
    
    if (empty($github_token)) {
        return new WP_Error('missing_token', __('GitHub API token not configured.', 'tradepress'));
    }
    
    if (empty($repo_owner) || empty($repo_name)) {
        return new WP_Error('missing_repo', __('Repository owner or name not configured.', 'tradepress'));
    }
    
    // Test API connection by getting repository info
    $api_url = "https://api.github.com/repos/{$repo_owner}/{$repo_name}";
    
    $response = wp_remote_get(
        $api_url,
        array(
            'headers' => array(
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $github_token,
                'User-Agent' => 'TradePress',
                'X-GitHub-Api-Version' => '2022-11-28'
            ),
            'timeout' => 15,
        )
    );
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $message = isset($body['message']) ? $body['message'] : __('Unknown error', 'tradepress');
        return new WP_Error(
            'request_failed',
            sprintf(__('Request failed with status %d: %s', 'tradepress'), $response_code, $message)
        );
    }
    
    return true;
}

/**
 * Get GitHub API rate limits
 * 
 * @return array|WP_Error Rate limit data or WP_Error on failure
 */
function tradepress_get_github_rate_limits() {
    $github_token = get_option('TRADEPRESS_GITHUB_token', '');
    
    if (empty($github_token)) {
        return new WP_Error('missing_token', __('GitHub API token not configured.', 'tradepress'));
    }
    
    $api_url = "https://api.github.com/rate_limit";
    
    $response = wp_remote_get(
        $api_url,
        array(
            'headers' => array(
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $github_token,
                'User-Agent' => 'TradePress',
                'X-GitHub-Api-Version' => '2022-11-28'
            ),
            'timeout' => 15,
        )
    );
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error(
            'request_failed',
            sprintf(__('Request failed with status %d', 'tradepress'), $response_code)
        );
    }
    
    $data = json_decode(wp_remote_retrieve_body($response), true);
    
    $rate_limits = array();
    
    // Core rate limits
    $rate_limits['Core API'] = array(
        'limit' => $data['resources']['core']['limit'],
        'remaining' => $data['resources']['core']['remaining'],
        'reset' => $data['resources']['core']['reset'],
    );
    
    // Search rate limits
    $rate_limits['Search API'] = array(
        'limit' => $data['resources']['search']['limit'],
        'remaining' => $data['resources']['search']['remaining'],
        'reset' => $data['resources']['search']['reset'],
    );
    
    // GraphQL rate limits
    $rate_limits['GraphQL API'] = array(
        'limit' => $data['resources']['graphql']['limit'],
        'remaining' => $data['resources']['graphql']['remaining'],
        'reset' => $data['resources']['graphql']['reset'],
    );
    
    return $rate_limits;
}
