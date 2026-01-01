<?php
/**
 * TradePress GitHub API Class
 * 
 * Handles interactions with the GitHub API for retrieving issues, updating issues, and other GitHub operations.
 * 
 * @package TradePress\API\GitHub
 * @version 1.0.0
 * @date    2023-09-15
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TRADEPRESS_GITHUB_API Class
 */
class TRADEPRESS_GITHUB_API {
    
    /**
     * API Token
     *
     * @var string
     */
    private $api_token = '';
    
    /**
     * Base API URL
     *
     * @var string
     */
    private $api_base_url = 'https://api.github.com';
    
    /**
     * Default request timeout in seconds
     *
     * @var int
     */
    private $timeout = 15;

    /**
     * Cache key prefix for GitHub data
     */
    private $cache_prefix = 'TRADEPRESS_GITHUB_cache_';
    
    /**
     * Cache expiration in seconds (default: 10 minutes)
     */
    private $cache_expiration = 600;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_token = get_option('TRADEPRESS_GITHUB_token', '');
    }
    
    /**
     * Get API token
     *
     * @return string API token
     */
    public function get_token() {
        return $this->api_token;
    }
    
    /**
     * Set API token
     *
     * @param string $token API token
     * @return void
     */
    public function set_token($token) {
        $this->api_token = sanitize_text_field($token);
        update_option('TRADEPRESS_GITHUB_token', $this->api_token);
    }
    
    /**
     * Check if API token is set
     *
     * @return bool True if token is set, false otherwise
     */
    public function has_token() {
        return !empty($this->api_token);
    }
    
    /**
     * Get request headers
     *
     * @return array Request headers
     */
    private function get_headers() {
        return array(
            'Authorization' => 'token ' . $this->api_token,
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'TradePress-Plugin'
        );
    }
    
    /**
     * Make API request
     *
     * @param string $endpoint API endpoint
     * @param string $method Request method (GET, POST, PATCH, etc.)
     * @param array $body Request body
     * @return object|WP_Error Response object or WP_Error on failure
     */
    private function request($endpoint, $method = 'GET', $body = null) {
        if (!$this->has_token()) {
            return new WP_Error('missing_token', __('GitHub API token not configured.', 'tradepress'));
        }
        
        $url = $this->api_base_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'headers' => $this->get_headers(),
            'timeout' => $this->timeout
        );
        
        if ($body !== null) {
            $args['body'] = json_encode($body);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            return new WP_Error(
                'github_api_error',
                sprintf(__('GitHub API error: %s', 'tradepress'), wp_remote_retrieve_response_message($response)),
                array('status' => $response_code)
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (empty($data) && $method !== 'DELETE') {
            return new WP_Error('invalid_response', __('Invalid or empty response from GitHub API.', 'tradepress'));
        }
        
        return $data;
    }
    
    /**
     * Get all issues for a repository with caching
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param array $params Additional query parameters
     * @param bool $force_refresh Force cache refresh
     * @return array|WP_Error Array of issues or WP_Error on failure
     */
    public function get_issues($owner, $repo, $params = array(), $force_refresh = false) {
        // Check if we're in demo mode
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return $this->get_demo_issues($owner, $repo);
        }
        
        // Generate cache key
        $cache_key = $this->get_cache_key('issues', $owner, $repo, $params);
        
        // Get cached data if not forcing refresh
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Query string for additional parameters
        $query = '';
        if (!empty($params)) {
            $query = '?' . http_build_query($params);
        }
        
        // Make API request
        $result = $this->request("/repos/{$owner}/{$repo}/issues{$query}");
        
        // Cache result if not an error
        if (!is_wp_error($result)) {
            set_transient($cache_key, $result, $this->cache_expiration);
        }
        
        return $result;
    }
    
    /**
     * Get a specific issue with caching
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param int $issue_number Issue number
     * @param bool $force_refresh Force cache refresh
     * @return object|WP_Error Issue object or WP_Error on failure
     */
    public function get_issue($owner, $repo, $issue_number, $force_refresh = false) {
        // Check if we're in demo mode
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return $this->get_demo_issue($owner, $repo, $issue_number);
        }
        
        // Generate cache key
        $cache_key = $this->get_cache_key('issue', $owner, $repo, array('issue_number' => $issue_number));
        
        // Get cached data if not forcing refresh
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Make API request
        $result = $this->request("/repos/{$owner}/{$repo}/issues/{$issue_number}");
        
        // Cache result if not an error
        if (!is_wp_error($result)) {
            set_transient($cache_key, $result, $this->cache_expiration);
        }
        
        return $result;
    }
    
    /**
     * Get repository labels with caching
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param bool $force_refresh Force cache refresh
     * @return array|WP_Error Array of labels or WP_Error on failure
     */
    public function get_labels($owner, $repo, $force_refresh = false) {
        // Check if we're in demo mode
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return $this->get_demo_labels($owner, $repo);
        }
        
        // Generate cache key
        $cache_key = $this->get_cache_key('labels', $owner, $repo);
        
        // Get cached data if not forcing refresh
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Make API request
        $result = $this->request("/repos/{$owner}/{$repo}/labels?per_page=100");
        
        // Cache result if not an error
        if (!is_wp_error($result)) {
            set_transient($cache_key, $result, $this->cache_expiration);
        }
        
        return $result;
    }
    
    /**
     * Get repository collaborators with caching
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param bool $force_refresh Force cache refresh
     * @return array|WP_Error Array of collaborators or WP_Error on failure
     */
    public function get_collaborators($owner, $repo, $force_refresh = false) {
        // Check if we're in demo mode
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return $this->get_demo_collaborators($owner, $repo);
        }
        
        // Generate cache key
        $cache_key = $this->get_cache_key('collaborators', $owner, $repo);
        
        // Get cached data if not forcing refresh
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Make API request
        $result = $this->request("/repos/{$owner}/{$repo}/collaborators?per_page=100");
        
        // Cache result if not an error
        if (!is_wp_error($result)) {
            set_transient($cache_key, $result, $this->cache_expiration);
        }
        
        return $result;
    }
    
    /**
     * Get repository milestones with caching
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param string $state Milestone state (open, closed, all)
     * @param bool $force_refresh Force cache refresh
     * @return array|WP_Error Array of milestones or WP_Error on failure
     */
    public function get_milestones($owner, $repo, $state = 'open', $force_refresh = false) {
        // Check if we're in demo mode
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return $this->get_demo_milestones($owner, $repo, $state);
        }
        
        // Generate cache key
        $cache_key = $this->get_cache_key('milestones', $owner, $repo, array('state' => $state));
        
        // Get cached data if not forcing refresh
        if (!$force_refresh) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Make API request
        $result = $this->request("/repos/{$owner}/{$repo}/milestones?state={$state}&per_page=100");
        
        // Cache result if not an error
        if (!is_wp_error($result)) {
            set_transient($cache_key, $result, $this->cache_expiration);
        }
        
        return $result;
    }
    
    /**
     * Create a new issue in a repository
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param array $data Issue data (title, body, labels, etc.)
     * @return object|WP_Error Created issue object or WP_Error on failure
     */
    public function create_issue($owner, $repo, $data) {
        // Check if we're in demo mode
        if (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
            return $this->get_demo_created_issue($owner, $repo, $data);
        }
        
        // Make API request to create issue
        return $this->request("/repos/{$owner}/{$repo}/issues", 'POST', $data);
    }

    /**
     * Get a demo created issue for testing
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param array $data Issue data
     * @return object Demo issue object
     */
    private function get_demo_created_issue($owner, $repo, $data) {
        // Create a fake response with the provided data
        $issue = (object) array(
            'number' => rand(100, 999),
            'title' => $data['title'],
            'body' => isset($data['body']) ? $data['body'] : '',
            'html_url' => "https://github.com/{$owner}/{$repo}/issues/" . rand(100, 999),
            'state' => 'open',
            'labels' => array(),
            'created_at' => date('Y-m-d\TH:i:s\Z'),
            'updated_at' => date('Y-m-d\TH:i:s\Z')
        );
        
        // Add labels if provided
        if (!empty($data['labels'])) {
            foreach ($data['labels'] as $label_name) {
                $issue->labels[] = (object) array(
                    'name' => $label_name,
                    'color' => substr(md5($label_name), 0, 6)
                );
            }
        }
        
        return $issue;
    }
    
    /**
     * Schedule a background refresh of GitHub data
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return void
     */
    public function schedule_data_refresh($owner, $repo) {
        if (!wp_next_scheduled('TRADEPRESS_GITHUB_data_refresh', array($owner, $repo))) {
            wp_schedule_event(time(), 'hourly', 'TRADEPRESS_GITHUB_data_refresh', array($owner, $repo));
        }
    }
    
    /**
     * Refresh all GitHub data
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return array Refresh status
     */
    public function refresh_all_data($owner, $repo) {
        $status = array(
            'issues' => false,
            'labels' => false,
            'collaborators' => false,
            'milestones' => false
        );
        
        // Refresh issues
        $issues = $this->get_issues($owner, $repo, array(), true);
        $status['issues'] = !is_wp_error($issues);
        
        // Refresh labels
        $labels = $this->get_labels($owner, $repo, true);
        $status['labels'] = !is_wp_error($labels);
        
        // Refresh collaborators
        $collaborators = $this->get_collaborators($owner, $repo, true);
        $status['collaborators'] = !is_wp_error($collaborators);
        
        // Refresh milestones
        $milestones = $this->get_milestones($owner, $repo, 'open', true);
        $status['milestones'] = !is_wp_error($milestones);
        
        // Record last refresh time
        update_option('TRADEPRESS_GITHUB_last_refresh', time());
        
        return $status;
    }
    
    /**
     * Clear all GitHub cache
     *
     * @return int Number of cache items cleared
     */
    public function clear_all_cache() {
        global $wpdb;
        
        // Get all transients matching our prefix
        $sql = $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $this->cache_prefix . '%'
        );
        
        $transients = $wpdb->get_col($sql);
        $count = 0;
        
        // Delete each transient
        foreach ($transients as $transient) {
            $transient_name = str_replace('_transient_', '', $transient);
            if (delete_transient($transient_name)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get cache expiration time in seconds
     *
     * @return int Cache expiration time
     */
    public function get_cache_expiration() {
        return $this->cache_expiration;
    }
    
    /**
     * Set cache expiration time
     *
     * @param int $seconds Expiration time in seconds
     * @return void
     */
    public function set_cache_expiration($seconds) {
        $this->cache_expiration = max(60, absint($seconds));
    }
    
    /**
     * Generate a standardized cache key
     *
     * @param string $type Data type (issues, issue, labels, etc.)
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @param array $params Additional parameters for the key
     * @return string Cache key
     */
    private function get_cache_key($type, $owner, $repo, $params = array()) {
        $key = $this->cache_prefix . $type . '_' . $owner . '_' . $repo;
        
        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }
        
        return $key;
    }
    
    /**
     * Get last refresh time
     *
     * @return int|false Timestamp of last refresh or false if never refreshed
     */
    public function get_last_refresh_time() {
        return get_option('TRADEPRESS_GITHUB_last_refresh', false);
    }
    
    /**
     * Check if cache is stale
     *
     * @param int $threshold Threshold in seconds (default: 1 hour)
     * @return bool True if cache is stale, false otherwise
     */
    public function is_cache_stale($threshold = 3600) {
        $last_refresh = $this->get_last_refresh_time();
        if (!$last_refresh) {
            return true;
        }
        
        return (time() - $last_refresh) > $threshold;
    }
    
    /**
     * Get demo labels for testing
     *
     * @param string $owner Repository owner
     * @param string $repo Repository name
     * @return array Array of demo labels
     */
    private function get_demo_labels($owner, $repo) {
        return array(
            (object) array(
                'id' => 1,
                'name' => 'bug',
                'color' => 'f29513',
                'description' => 'Something isn\'t working'
            ),
            (object) array(
                'id' => 2,
                'name' => 'enhancement',
                'color' => '84b6eb',
                'description' => 'New feature or request'
            ),
            (object) array(
                'id' => 3,
                'name' => 'documentation',
                'color' => '0075ca',
                'description' => 'Improvements or additions to documentation'
            )
        );
    }
}

/**
 * Get a GitHub API instance
 *
 * @return TRADEPRESS_GITHUB_API GitHub API instance
 */
function TRADEPRESS_GITHUB_api() {
    static $api = null;
    
    if ($api === null) {
        $api = new TRADEPRESS_GITHUB_API();
    }
    
    return $api;
}

/**
 * Initialize GitHub caching system
 * 
 * @return void
 */
function tradepress_init_github_cache() {
    // Register the background refresh event
    add_action('TRADEPRESS_GITHUB_data_refresh', 'tradepress_refresh_github_data', 10, 2);
    
    // Schedule initial data refresh if needed
    $api = TRADEPRESS_GITHUB_api();
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
    
    if ($api->has_token() && !empty($repo_owner) && !empty($repo_name)) {
        $api->schedule_data_refresh($repo_owner, $repo_name);
    }
}
add_action('init', 'tradepress_init_github_cache');

/**
 * Refresh GitHub data (callback for scheduled event)
 * 
 * @param string $owner Repository owner
 * @param string $repo Repository name
 * @return void
 */
function tradepress_refresh_github_data($owner, $repo) {
    $api = TRADEPRESS_GITHUB_api();
    $api->refresh_all_data($owner, $repo);
}

/**
 * Get GitHub issues for a repository
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @return array|WP_Error Array of issue objects or WP_Error on failure
 */
function tradepress_get_github_issues($owner, $repo) {
    return TRADEPRESS_GITHUB_api()->get_issues($owner, $repo);
}

/**
 * Get a specific GitHub issue by number
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param int $issue_number Issue number
 * @return object|WP_Error Issue object or WP_Error on failure
 */
function tradepress_get_github_issue($owner, $repo, $issue_number) {
    return TRADEPRESS_GITHUB_api()->get_issue($owner, $repo, $issue_number);
}

/**
 * Update the state of a GitHub issue (open/closed)
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param int $issue_number Issue number
 * @param string $state New state ('open' or 'closed')
 * @return object|WP_Error Updated issue object or WP_Error on failure
 */
function tradepress_update_github_issue_state($owner, $repo, $issue_number, $state) {
    return TRADEPRESS_GITHUB_api()->update_issue_state($owner, $repo, $issue_number, $state);
}

/**
 * Create a new GitHub issue
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param array $data Issue data (title, body, etc.)
 * @return object|WP_Error Created issue object or WP_Error on failure
 */
function tradepress_create_github_issue($owner, $repo, $data) {
    return TRADEPRESS_GITHUB_api()->create_issue($owner, $repo, $data);
}

/**
 * Create a GitHub issue from a task data structure
 *
 * @since 1.0.0
 * @param array $task Task data with title, description, subtasks, etc.
 * @return array|WP_Error GitHub issue data or WP_Error on failure
 */
function tradepress_create_github_issue_from_task($task) {
    // Get repository settings
    $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
    $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
    
    if (empty($repo_owner) || empty($repo_name)) {
        return new WP_Error('missing_repo', __('GitHub repository information is not configured.', 'tradepress'));
    }
    
    // Prepare issue description
    $description = '';
    
    if (!empty($task['description'])) {
        $description .= $task['description'] . "\n\n";
    }
    
    if (!empty($task['section'])) {
        $description .= "**Section:** " . $task['section'] . "\n";
    }
    
    if (!empty($task['subtasks']) && is_array($task['subtasks'])) {
        $description .= "\n**Subtasks:**\n";
        foreach ($task['subtasks'] as $subtask) {
            if (!isset($subtask['title'])) {
                continue; // Skip subtasks without titles
            }
            $status = isset($subtask['status']) && $subtask['status'] === 'completed' ? 'x' : ' ';
            $description .= "- [{$status}] " . $subtask['title'] . "\n";
        }
    }
    
    // Add reference to the original task
    $description .= "\n\n---\n";
    $description .= "Original task ID: `{$task['id']}`\n";
    $description .= "Original task source: `{$task['source']}`";
    
    // Prepare labels
    $labels = array("phase:{$task['phase']}");
    
    switch(intval($task['priority'])) {
        case 1:
            $labels[] = 'priority:high';
            break;
        case 2:
            $labels[] = 'priority:medium';
            break;
        case 3:
            $labels[] = 'priority:low';
            break;
    }
    
    // Add source as a label
    $labels[] = "source:{$task['source']}";
    
    // Prepare issue data
    $issue_data = array(
        'title' => $task['title'],
        'body' => $description,
        'labels' => $labels
    );
    
    // Try to create the issue using the GitHub API
    $github_api = TRADEPRESS_GITHUB_api();
    $result = $github_api->create_issue($repo_owner, $repo_name, $issue_data);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    return array(
        'number' => $result->number,
        'html_url' => $result->html_url
    );
}

/**
 * Get GitHub labels for a repository
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param string $github_token GitHub API token (for backward compatibility)
 * @param bool $force_refresh Force cache refresh
 * @return array|WP_Error Array of labels or WP_Error on failure
 */
function tradepress_get_github_labels($owner, $repo, $github_token = '', $force_refresh = false) {
    return TRADEPRESS_GITHUB_api()->get_labels($owner, $repo, $force_refresh);
}

/**
 * Get GitHub repository collaborators
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param string $github_token GitHub API token (for backward compatibility)
 * @param bool $force_refresh Force cache refresh
 * @return array|WP_Error Array of collaborators or WP_Error on failure
 */
function tradepress_get_github_collaborators($owner, $repo, $github_token = '', $force_refresh = false) {
    return TRADEPRESS_GITHUB_api()->get_collaborators($owner, $repo, $force_refresh);
}

/**
 * Get GitHub repository milestones
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param string $github_token GitHub API token (for backward compatibility)
 * @param string $state Milestone state (open, closed, all)
 * @param bool $force_refresh Force cache refresh
 * @return array|WP_Error Array of milestones or WP_Error on failure
 */
function tradepress_get_github_milestones($owner, $repo, $github_token = '', $state = 'open', $force_refresh = false) {
    return TRADEPRESS_GITHUB_api()->get_milestones($owner, $repo, $state, $force_refresh);
}

/**
 * Get GitHub repository issues with pagination information
 *
 * @since 1.0.0
 * @param string $owner GitHub repository owner
 * @param string $repo GitHub repository name
 * @param string $github_token GitHub API token (for backward compatibility)
 * @param array $params Additional query parameters
 * @param bool $force_refresh Force cache refresh
 * @return array|WP_Error Array with issues, pagination data, and total count or WP_Error on failure
 */
function tradepress_get_github_repository_issues($owner, $repo, $github_token = '', $params = array(), $force_refresh = false) {
    // Default parameters
    $default_params = array(
        'state' => 'open',
        'page' => 1,
        'per_page' => 20,
        'sort' => 'updated',
        'direction' => 'desc'
    );
    
    $params = wp_parse_args($params, $default_params);
    
    // Use the centralized GitHub API function with cache
    $issues = TRADEPRESS_GITHUB_api()->get_issues($owner, $repo, $params, $force_refresh);
    
    if (is_wp_error($issues)) {
        return $issues;
    }
    
    // Parse pagination from Link header
    $pagination = array(
        'page' => $params['page'],
        'per_page' => $params['per_page'],
        'pages' => 1,
        'total' => count($issues)
    );
    
    // Here we would normally parse the Link header from the API response,
    // but since we're using our custom function, we'll estimate pagination
    
    // Determine total count (this would normally come from API response headers)
    $total_count = count($issues) * 3; // This is just an estimate
    
    return array(
        'items' => $issues,
        'total_count' => $total_count,
        'pagination' => $pagination
    );
}
