<?php
/**
 * TradePress GitHub API Endpoints
 * 
 * Registers REST API endpoints for GitHub functionality
 * 
 * @package TradePress\API\GitHub
 * @version 1.0.0
 * @date    2024-08-05
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TRADEPRESS_GITHUB_Endpoints Class
 */
class TRADEPRESS_GITHUB_Endpoints {
    
    /**
     * Namespace for API endpoints
     *
     * @var string
     */
    private $namespace = 'tradepress/v1';
    
    /**
     * Register endpoints
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/github/refresh',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'refresh_github_data'),
                'permission_callback' => array($this, 'check_admin_permission')
            )
        );
        
        register_rest_route(
            $this->namespace,
            '/github/cache/clear',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'clear_github_cache'),
                'permission_callback' => array($this, 'check_admin_permission')
            )
        );
        
        register_rest_route(
            $this->namespace,
            '/github/cache/status',
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_cache_status'),
                'permission_callback' => array($this, 'check_admin_permission')
            )
        );
    }
    
    /**
     * Check if current user has admin permissions
     *
     * @return bool
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }
    
    /**
     * Refresh GitHub data
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function refresh_github_data($request) {
        // Include GitHub helper functions
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/github/github-api.php';
        
        $repo_owner = get_option('TRADEPRESS_GITHUB_repo_owner', '');
        $repo_name = get_option('TRADEPRESS_GITHUB_repo_name', '');
        
        if (empty($repo_owner) || empty($repo_name)) {
            return new WP_Error(
                'config_missing',
                __('GitHub repository owner or name not configured.', 'tradepress'),
                array('status' => 400)
            );
        }
        
        $api = TRADEPRESS_GITHUB_api();
        $result = $api->refresh_all_data($repo_owner, $repo_name);
        
        if (in_array(false, $result, true)) {
            $error_types = array();
            foreach ($result as $type => $success) {
                if (!$success) {
                    $error_types[] = $type;
                }
            }
            
            return new WP_Error(
                'refresh_failed',
                sprintf(
                    __('Failed to refresh some GitHub data: %s', 'tradepress'),
                    implode(', ', $error_types)
                ),
                array('status' => 500)
            );
        }
        
        return rest_ensure_response(array(
            'status' => 'success',
            'message' => __('GitHub data refreshed successfully.', 'tradepress'),
            'last_refresh' => human_time_diff(time(), time()),
            'details' => $result
        ));
    }
    
    /**
     * Clear GitHub cache
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function clear_github_cache($request) {
        // Include GitHub helper functions
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/github/github-api.php';
        
        $api = TRADEPRESS_GITHUB_api();
        $count = $api->clear_all_cache();
        
        return rest_ensure_response(array(
            'status' => 'success',
            'message' => sprintf(
                __('GitHub cache cleared (%d items).', 'tradepress'),
                $count
            )
        ));
    }
    
    /**
     * Get cache status
     *
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function get_cache_status($request) {
        // Include GitHub helper functions
        require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/github/github-api.php';
        
        $api = TRADEPRESS_GITHUB_api();
        $last_refresh = $api->get_last_refresh_time();
        
        return rest_ensure_response(array(
            'last_refresh' => $last_refresh ? human_time_diff($last_refresh) . ' ago' : __('Never', 'tradepress'),
            'is_stale' => $api->is_cache_stale(),
            'expiration' => human_time_diff(time(), time() + $api->get_cache_expiration()),
            'timestamp' => $last_refresh ? $last_refresh : 0
        ));
    }
}

// Initialize and register endpoints
add_action('rest_api_init', function() {
    $github_endpoints = new TRADEPRESS_GITHUB_Endpoints();
    $github_endpoints->register_routes();
});
