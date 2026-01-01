<?php
/**
 * TradePress Endpoints Tab
 *
 * Displays all API endpoints from all providers
 *
 * @package TradePress
 * @subpackage admin/page/data
 * @version 1.0.1
 * @since 1.0.0
 * @created 2025-05-22
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue the configure-directives CSS for consistent layout
wp_enqueue_style('tradepress-configure-directives', TRADEPRESS_PLUGIN_URL . 'assets/css/pages/configure-directives.css', array(), TRADEPRESS_VERSION);

// Load API Directory
if (!class_exists('TradePress_API_Directory')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . '/api/api-directory.php';
}

/**
 * Get all endpoints from all API providers
 */
function tradepress_get_all_endpoints() {
    $all_providers = TradePress_API_Directory::get_all_providers();
    $all_endpoints = array();
    
    foreach ($all_providers as $api_id => $provider) {
        // Try to load the endpoints class for each API
        $endpoints_file = TRADEPRESS_PLUGIN_DIR_PATH . '/api/' . $api_id . '/' . $api_id . '-endpoints.php';
        
        if (file_exists($endpoints_file)) {
            require_once $endpoints_file;
            
            $class_name = 'TradePress_' . ucfirst($api_id) . '_Endpoints';
            
            if (class_exists($class_name) && method_exists($class_name, 'get_endpoints')) {
                $endpoints = $class_name::get_endpoints();
                
                foreach ($endpoints as $endpoint_key => $endpoint_data) {
                    // Handle different endpoint structures
                    $endpoint_path = '';
                    $method = 'GET';
                    $description = $endpoint_data['description'] ?? '';
                    $parameters = array();
                    
                    // Alpaca-style endpoints (REST API)
                    if (isset($endpoint_data['endpoint'])) {
                        $endpoint_path = $endpoint_data['endpoint'];
                        $method = $endpoint_data['method'] ?? 'GET';
                        $parameters = $endpoint_data['parameters'] ?? array();
                    }
                    // Alpha Vantage-style endpoints (Query parameters)
                    elseif (isset($endpoint_data['function'])) {
                        $endpoint_path = '/query?function=' . $endpoint_data['function'];
                        $method = 'GET';
                        // Combine required and optional params
                        $required_params = $endpoint_data['required_params'] ?? array();
                        $optional_params = $endpoint_data['optional_params'] ?? array();
                        $parameters = array_merge($required_params, $optional_params);
                    }
                    // Generic fallback
                    else {
                        $endpoint_path = '/' . strtolower($endpoint_key);
                    }
                    
                    $all_endpoints[] = array(
                        'api_id' => $api_id,
                        'api_name' => $provider['name'],
                        'endpoint_key' => $endpoint_key,
                        'endpoint' => $endpoint_path,
                        'method' => $method,
                        'description' => $description,
                        'parameters' => $parameters,
                        'api_enabled' => get_option('TradePress_switch_' . $api_id . '_api_services', 'no') === 'yes'
                    );
                }
            }
        }
    }
    
    return $all_endpoints;
}

// Get all endpoints
$all_endpoints = tradepress_get_all_endpoints();

// Handle search filtering
$search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$filtered_endpoints = $all_endpoints;

if (!empty($search_term)) {
    $filtered_endpoints = array_filter($all_endpoints, function($endpoint) use ($search_term) {
        return stripos($endpoint['api_name'], $search_term) !== false || 
               stripos($endpoint['endpoint_key'], $search_term) !== false ||
               stripos($endpoint['endpoint'], $search_term) !== false ||
               stripos($endpoint['description'], $search_term) !== false;
    });
}

// Sort endpoints - enabled APIs first, then by API name
usort($filtered_endpoints, function($a, $b) {
    if ($a['api_enabled'] !== $b['api_enabled']) {
        return $b['api_enabled'] - $a['api_enabled'];
    }
    return strcmp($a['api_name'], $b['api_name']);
});
?>

<div class="configure-directives-container">
    <div class="directives-layout">
        <!-- Single Column: Endpoints Table (No Right Sidebar) -->
        <div class="directives-table-container" style="width: 100%;">
            <div class="tablenav top">
                <div class="alignleft actions">
                    <input type="search" id="endpoint-search-input" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="<?php esc_attr_e('Search endpoints...', 'tradepress'); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Endpoints', 'tradepress'); ?>">
                    <button type="button" class="button action-refresh"><span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh', 'tradepress'); ?></button>
                </div>
            </div>
            
            <?php if (empty($filtered_endpoints)): ?>
                <div class="tradepress-notice notice-info">
                    <p><?php esc_html_e('No API endpoints found. Make sure API endpoint files exist in the /api/ directory.', 'tradepress'); ?></p>
                </div>
            <?php else: ?>
                <div class="wp-list-table widefat fixed striped">
                    <div class="table-header" style="display: flex; background: #f1f1f1; padding: 12px 15px; font-weight: 600; border-bottom: 1px solid #c3c4c7;">
                        <div style="flex: 2;"><?php _e('API Provider', 'tradepress'); ?></div>
                        <div style="flex: 2;"><?php _e('Endpoint', 'tradepress'); ?></div>
                        <div style="flex: 1;"><?php _e('Method', 'tradepress'); ?></div>
                        <div style="flex: 1;"><?php _e('Status', 'tradepress'); ?></div>
                        <div style="flex: 3;"><?php _e('Description', 'tradepress'); ?></div>
                    </div>
                </div>

                <div class="tradepress-compact-table">
                    <?php foreach ($filtered_endpoints as $endpoint): ?>
                        <div class="accordion-row">
                            <div class="accordion-header">
                                <div style="flex: 2;">
                                    <strong><?php echo esc_html($endpoint['api_name']); ?></strong>
                                </div>
                                <div style="flex: 2;">
                                    <code><?php echo esc_html($endpoint['endpoint']); ?></code>
                                </div>
                                <div style="flex: 1;">
                                    <span class="method-badge method-<?php echo strtolower($endpoint['method']); ?>">
                                        <?php echo esc_html($endpoint['method']); ?>
                                    </span>
                                </div>
                                <div style="flex: 1;">
                                    <span class="status-badge <?php echo $endpoint['api_enabled'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $endpoint['api_enabled'] ? 'Available' : 'Disabled'; ?>
                                    </span>
                                </div>
                                <div style="flex: 3;">
                                    <?php echo esc_html($endpoint['description']); ?>
                                </div>
                            </div>
                            <div class="accordion-content">
                                <div class="endpoint-meta">
                                    <div>
                                        <strong>Endpoint Key:</strong><br>
                                        <?php echo esc_html($endpoint['endpoint_key']); ?>
                                    </div>
                                    <div>
                                        <strong>Full Path:</strong><br>
                                        <code><?php echo esc_html($endpoint['endpoint']); ?></code>
                                    </div>
                                    <div>
                                        <strong>Parameters:</strong><br>
                                        <?php if (!empty($endpoint['parameters'])): ?>
                                            <?php echo count($endpoint['parameters']); ?> parameters defined
                                        <?php else: ?>
                                            No parameters required
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="endpoint-actions">
                                    <button type="button" class="button button-primary test-endpoint" 
                                            data-api="<?php echo esc_attr($endpoint['api_id']); ?>" 
                                            data-endpoint="<?php echo esc_attr($endpoint['endpoint_key']); ?>">
                                        <?php esc_html_e('Test Endpoint', 'tradepress'); ?>
                                    </button>
                                    <button type="button" class="button view-parameters" 
                                            data-endpoint="<?php echo esc_attr($endpoint['endpoint_key']); ?>">
                                        <?php esc_html_e('View Parameters', 'tradepress'); ?>
                                    </button>
                                    <button type="button" class="button view-documentation" 
                                            data-api="<?php echo esc_attr($endpoint['api_id']); ?>">
                                        <?php esc_html_e('API Docs', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.method-badge {
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.method-get { background: #28a745; color: white; }
.method-post { background: #007bff; color: white; }
.method-put { background: #ffc107; color: black; }
.method-delete { background: #dc3545; color: white; }
.method-patch { background: #6f42c1; color: white; }

.endpoint-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.endpoint-meta > div {
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.endpoint-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Search functionality
    $('#endpoint-search-input').on('keyup', function(e) {
        if (e.keyCode === 13) { // Enter key
            performSearch();
        }
    });
    
    $('#search-submit').on('click', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    function performSearch() {
        var searchTerm = $('#endpoint-search-input').val();
        var url = new URL(window.location);
        if (searchTerm) {
            url.searchParams.set('s', searchTerm);
        } else {
            url.searchParams.delete('s');
        }
        window.location.href = url.toString();
    }
    
    // Refresh button
    $('.action-refresh').on('click', function() {
        window.location.reload();
    });
    
    // Accordion functionality
    $('.accordion-header').on('click', function() {
        var $content = $(this).next('.accordion-content');
        var isActive = $content.hasClass('active');
        
        // Close all accordions
        $('.accordion-content').removeClass('active').slideUp();
        $('.accordion-header').removeClass('active');
        
        // Open clicked accordion if it wasn't active
        if (!isActive) {
            $content.addClass('active').slideDown();
            $(this).addClass('active');
        }
    });
    
    // Test endpoint button
    $('.test-endpoint').on('click', function() {
        var apiId = $(this).data('api');
        var endpoint = $(this).data('endpoint');
        alert('Test endpoint functionality will be implemented: ' + apiId + ' - ' + endpoint);
    });
    
    // View parameters button
    $('.view-parameters').on('click', function() {
        var endpoint = $(this).data('endpoint');
        alert('Parameters view will be implemented for: ' + endpoint);
    });
    
    // View documentation button
    $('.view-documentation').on('click', function() {
        var apiId = $(this).data('api');
        alert('Documentation link will be implemented for: ' + apiId);
    });
});
</script>
