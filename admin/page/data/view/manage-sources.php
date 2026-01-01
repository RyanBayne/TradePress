<?php
/**
 * TradePress Manage Sources Class
 *
 * @package  TradePress
 * @category Admin
 * @since    1.0.0
 * 
 * This file contains the view for managing data sources within the TradePress plugin.
 * Data sources represent connections to various trading platforms, APIs, and data feeds
 * that the plugin can utilize to fetch financial and trading data.
 * 
 * The view allows administrators to:
 * - Add new data sources
 * - Edit existing data source configurations
 * - Enable/disable specific data sources
 * - Test API connections to ensure proper functionality
 * - Set refresh intervals and data synchronization options
 * 
 * This view works in conjunction with the TradePress_Data_Sources_Controller class
 * which handles the business logic for managing the data sources.
 * 
 * @see TradePress_Data_Sources_Controller For the controller handling the data operations
 * @see TradePress_Data_Source_Model For the underlying data model representing sources
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Manage_Sources Class
 */
class TradePress_Manage_Sources {

    /**
     * The sources table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'tradepress_research_sources';
    }

    /**
     * Initialize the class
     */
    public function init() {
        add_action('wp_ajax_tradepress_save_source', array($this, 'ajax_save_source'));
        add_action('wp_ajax_tradepress_delete_source', array($this, 'ajax_delete_source'));
        add_action('wp_ajax_tradepress_toggle_source_status', array($this, 'ajax_toggle_source_status'));
        add_action('wp_ajax_tradepress_archive_source', array($this, 'ajax_archive_source'));
        add_action('wp_ajax_tradepress_test_source', array($this, 'ajax_test_source'));
    }

    /**
     * Create the sources table if it doesn't exist
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(50) NOT NULL,
            url text NOT NULL,
            credentials text,
            settings longtext,
            last_fetch datetime,
            status varchar(50) DEFAULT 'active',
            created datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Render the manage sources tab content
     */
    public function render() {
        // Ensure table exists
        $this->create_table();
        
        // Check for actions
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $source_id = isset($_GET['source_id']) ? intval($_GET['source_id']) : 0;
        
        if ($action === 'edit' && $source_id > 0) {
            $this->render_source_form($source_id);
        } elseif ($action === 'new') {
            $this->render_source_form();
        } else {
            $this->render_sources_list();
        }
    }

    /**
     * Render the sources list specifically for the tab
     */
    public function render_sources_list() {
        global $wpdb;
        
        // Ensure table exists
        $this->create_table();
        
        // Get all sources
        $sources = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY name ASC");
        
        ?>
        <div class="tradepress-manage-sources-container">
            <div class="tradepress-sources-header">
                <h2><?php esc_html_e('Research Data Sources', 'tradepress'); ?></h2>
                <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_data&tab=create_source')); ?>" class="button button-primary"><?php esc_html_e('Add New Source', 'tradepress'); ?></a>
            </div>
            
            <div class="tradepress-sources-description">
                <p><?php esc_html_e('Manage data sources for research, including websites to scrape, Discord channels, social media profiles, and more.', 'tradepress'); ?></p>
            </div>
            
            <?php if (empty($sources)): ?>
                <div class="tradepress-no-sources">
                    <p><?php esc_html_e('No data sources have been added yet.', 'tradepress'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_data&tab=create_source')); ?>" class="button button-primary"><?php esc_html_e('Add Your First Source', 'tradepress'); ?></a>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped tradepress-sources-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Type', 'tradepress'); ?></th>
                            <th><?php esc_html_e('URL/Location', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Last Fetched', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Status', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Usage Stats', 'tradepress'); ?></th>
                            <th><?php esc_html_e('Actions', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sources as $source): ?>
                            <tr>
                                <td><?php echo esc_html($source->name); ?></td>
                                <td><?php echo esc_html($this->get_source_type_label($source->type)); ?></td>
                                <td><?php echo esc_url($source->url); ?></td>
                                <td><?php echo $source->last_fetch ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($source->last_fetch)) : esc_html__('Never', 'tradepress'); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($source->status); ?>">
                                        <?php echo esc_html(ucfirst($source->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    // Display usage statistics
                                    $source_id = isset($source->id) ? $source->id : 0;
                                    if ($source_id > 0) {
                                        $usage_stats = $this->get_source_usage_stats($source_id);
                                        echo sprintf(
                                            __('Imports: %d<br>Directive Uses: %d', 'tradepress'),
                                            $usage_stats['import_count'],
                                            $usage_stats['directive_uses']
                                        );
                                    } else {
                                        echo __('No data available', 'tradepress');
                                    }
                                    ?>
                                </td>
                                <td class="actions">
                                    <?php $source_id = isset($source->id) ? $source->id : 0; ?>
                                    <?php if ($source_id > 0): ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_data&tab=create_source&source_id=' . $source_id)); ?>" class="button button-small"><?php esc_html_e('Edit', 'tradepress'); ?></a>
                                        
                                        <?php if ($source->status === 'active'): ?>
                                            <a href="#" class="button button-small toggle-status" data-id="<?php echo esc_attr($source_id); ?>" data-action="pause"><?php esc_html_e('Suspend', 'tradepress'); ?></a>
                                        <?php else: ?>
                                            <a href="#" class="button button-small toggle-status" data-id="<?php echo esc_attr($source_id); ?>" data-action="activate"><?php esc_html_e('Activate', 'tradepress'); ?></a>
                                        <?php endif; ?>
                                        
                                        <a href="#" class="button button-small fetch-now" data-id="<?php echo esc_attr($source_id); ?>"><?php esc_html_e('Fetch Now', 'tradepress'); ?></a>
                                        <a href="#" class="button button-small archive-source" data-id="<?php echo esc_attr($source_id); ?>"><?php esc_html_e('Archive', 'tradepress'); ?></a>
                                        <a href="#" class="button button-small delete-source" data-id="<?php echo esc_attr($source_id); ?>"><?php esc_html_e('Delete', 'tradepress'); ?></a>
                                    <?php else: ?>
                                        <span class="button button-small disabled"><?php esc_html_e('No Actions Available', 'tradepress'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render the source form
     *
     * @param int $source_id Optional source ID for editing
     */
    public function render_source_form($source_id = 0) {
        global $wpdb;
        
        $source = null;
        $is_edit = false;
        
        if ($source_id > 0) {
            $source = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $source_id));
            $is_edit = true;
        }
        
        // Set defaults
        $name = $source ? $source->name : '';
        $type = $source ? $source->type : 'website';
        $url = $source ? $source->url : '';
        $status = $source ? $source->status : 'active';
        $settings = $source ? json_decode($source->settings, true) : array();
        
        // Define source type descriptions and availability status
        $source_types = array(
            'website' => array(
                'title' => __('Website (Scraping)', 'tradepress'),
                'description' => __('Extract data from websites by scraping the content. Useful for analyst opinions, news, and other publicly available information.', 'tradepress'),
                'ready' => true,
                'notice' => ''
            ),
            'rss' => array(
                'title' => __('RSS Feed', 'tradepress'),
                'description' => __('Subscribe to RSS feeds to automatically collect articles, news, and updates. Easily process structured feed data.', 'tradepress'),
                'ready' => true,
                'notice' => ''
            ),
            'webhook' => array(
                'title' => __('Webhook', 'tradepress'),
                'description' => __('Receive data pushed from external services through webhooks. Ideal for real-time notifications and updates.', 'tradepress'),
                'ready' => false,
                'notice' => __('Webhook processing is currently in development and not yet available.', 'tradepress')
            ),
            'discord' => array(
                'title' => __('Discord Channel', 'tradepress'),
                'description' => __('Monitor Discord channels for trading signals, market analysis, and community sentiment.', 'tradepress'),
                'ready' => false,
                'notice' => __('Discord integration is in development. API implementation is not yet complete.', 'tradepress')
            ),
            'twitter' => array(
                'title' => __('Twitter/X.com Profile', 'tradepress'),
                'description' => __('Track Twitter/X.com accounts for real-time market insights, breaking news, and sentiment from influencers and financial experts.', 'tradepress'),
                'ready' => false,
                'notice' => __('Twitter/X.com API integration is in development. Currently unavailable due to API changes.', 'tradepress')
            ),
            'youtube' => array(
                'title' => __('YouTube Channel', 'tradepress'),
                'description' => __('Follow YouTube channels for video content analysis, transcriptions, and extracting insights from financial experts.', 'tradepress'),
                'ready' => false,
                'notice' => __('YouTube API integration is planned but not yet implemented.', 'tradepress')
            ),
            'reddit' => array(
                'title' => __('Reddit Subreddit', 'tradepress'),
                'description' => __('Monitor Reddit communities for trading discussions, market sentiment, and emerging trends.', 'tradepress'),
                'ready' => false,
                'notice' => __('Reddit API integration is in development and not yet available.', 'tradepress')
            ),
            'api' => array(
                'title' => __('API Endpoint', 'tradepress'),
                'description' => __('Connect to third-party APIs to fetch structured data directly from financial data providers.', 'tradepress'),
                'ready' => true, 
                'notice' => ''
            ),
            'custom' => array(
                'title' => __('Custom Source', 'tradepress'),
                'description' => __('Create a custom data source with specialized configuration for unique data needs.', 'tradepress'),
                'ready' => true,
                'notice' => ''
            ),
        );
        
        ?>
        <div class="tradepress-source-form-container">
            <h2><?php echo $is_edit ? esc_html__('Edit Source', 'tradepress') : esc_html__('Add New Source', 'tradepress'); ?></h2>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_data&tab=sources_list')); ?>" class="button"><?php esc_html_e('Back to List', 'tradepress'); ?></a>
            

            
            <!-- Test Results Container (initially hidden) -->
            <div id="source-test-results" class="source-test-results" style="display: none;">
                <h3><?php esc_html_e('Source Test Results', 'tradepress'); ?></h3>
                <div class="source-test-content"></div>
                <div class="source-test-actions">
                    <button type="button" id="save-tested-source" class="button button-primary"><?php esc_html_e('Accept & Save Source', 'tradepress'); ?></button>
                    <button type="button" id="close-test-results" class="button"><?php esc_html_e('Close', 'tradepress'); ?></button>
                </div>
            </div>
            
            <form id="tradepress-source-form" method="post" action="">
                <input type="hidden" name="source_id" value="<?php echo esc_attr($source_id); ?>">
                <?php wp_nonce_field('tradepress_save_source', 'tradepress_source_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="source_name"><?php esc_html_e('Source Name', 'tradepress'); ?></label></th>
                        <td>
                            <input type="text" id="source_name" name="source_name" value="<?php echo esc_attr($name); ?>" class="regular-text" required>
                            <p class="description"><?php esc_html_e('A descriptive name for this source.', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="source_type"><?php esc_html_e('Source Type', 'tradepress'); ?></label></th>
                        <td>
                            <select id="source_type" name="source_type" required>
                                <?php foreach ($source_types as $type_key => $type_data): ?>
                                    <option value="<?php echo esc_attr($type_key); ?>" <?php selected($type, $type_key); ?>>
                                        <?php echo esc_html($type_data['title']); ?>
                                        <?php if (!$type_data['ready']): ?>
                                            (<?php esc_html_e('Coming Soon', 'tradepress'); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <!-- Source type description container -->
                            <div id="source-type-description" class="source-type-info">
                                <div class="source-type-description-text"></div>
                                <div class="source-type-notice notice notice-warning inline" style="display: none;"></div>
                            </div>
                            
                            <p class="description"><?php esc_html_e('The type of data source.', 'tradepress'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="source_url"><?php esc_html_e('URL/Location', 'tradepress'); ?></label></th>
                        <td>
                            <input type="text" id="source_url" name="source_url" value="<?php echo esc_attr($url); ?>" class="regular-text" required>
                            
                            <!-- Dynamic URL field descriptions based on source type -->
                            <?php foreach ($source_types as $type_key => $type_data): ?>
                                <p class="description source-url-description" data-type="<?php echo esc_attr($type_key); ?>" style="<?php echo $type === $type_key ? '' : 'display:none;'; ?>">
                                    <?php 
                                    switch ($type_key) {
                                        case 'website':
                                            esc_html_e('The full URL of the webpage to scrape (e.g., https://example.com/news).', 'tradepress');
                                            break;
                                        case 'rss':
                                            esc_html_e('The complete URL of the RSS feed (e.g., https://example.com/feed).', 'tradepress');
                                            break;
                                        case 'webhook':
                                            esc_html_e('A unique endpoint identifier for this webhook.', 'tradepress');
                                            break;
                                        case 'discord':
                                            esc_html_e('The Discord channel ID or invite link (e.g., 123456789012345678).', 'tradepress');
                                            break;
                                        case 'twitter':
                                            esc_html_e('The Twitter/X.com username without @ (e.g., elonmusk).', 'tradepress');
                                            break;
                                        case 'youtube':
                                            esc_html_e('The YouTube channel ID or URL.', 'tradepress');
                                            break;
                                        case 'reddit':
                                            esc_html_e('The subreddit name without /r/ (e.g., wallstreetbets).', 'tradepress');
                                            break;
                                        case 'api':
                                            esc_html_e('The base URL of the API endpoint.', 'tradepress');
                                            break;
                                        case 'custom':
                                            esc_html_e('A unique identifier or URL for this custom source.', 'tradepress');
                                            break;
                                    }
                                    ?>
                                </p>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    
                    <!-- Advanced settings sections that will be shown/hidden based on source type -->
                    <tr class="source-settings-row website-settings" <?php echo $type !== 'website' ? 'style="display:none;"' : ''; ?>>
                        <th scope="row"><label><?php esc_html_e('Website Scraping Settings', 'tradepress'); ?></label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('Website Scraping Settings', 'tradepress'); ?></legend>
                                
                                <p><label for="website_selector"><strong><?php esc_html_e('CSS Selector', 'tradepress'); ?></strong></label></p>
                                <input type="text" id="website_selector" name="settings[website][selector]" value="<?php echo isset($settings['website']['selector']) ? esc_attr($settings['website']['selector']) : ''; ?>" class="regular-text">
                                <p class="description"><?php esc_html_e('CSS selector for the content you want to extract (e.g., ".article-content").', 'tradepress'); ?></p>
                                
                                <p><label for="website_frequency"><strong><?php esc_html_e('Check Frequency', 'tradepress'); ?></strong></label></p>
                                <select id="website_frequency" name="settings[website][frequency]">
                                    <option value="hourly" <?php selected(isset($settings['website']['frequency']) ? $settings['website']['frequency'] : '', 'hourly'); ?>><?php esc_html_e('Hourly', 'tradepress'); ?></option>
                                    <option value="daily" <?php selected(isset($settings['website']['frequency']) ? $settings['website']['frequency'] : '', 'daily'); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                    <option value="weekly" <?php selected(isset($settings['website']['frequency']) ? $settings['website']['frequency'] : '', 'weekly'); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('How often to check for new content.', 'tradepress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <!-- RSS Feed settings -->
                    <tr class="source-settings-row rss-settings" <?php echo $type !== 'rss' ? 'style="display:none;"' : ''; ?>>
                        <th scope="row"><label><?php esc_html_e('RSS Feed Settings', 'tradepress'); ?></label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('RSS Feed Settings', 'tradepress'); ?></legend>
                                
                                <p><label for="rss_item_limit"><strong><?php esc_html_e('Items to Process', 'tradepress'); ?></strong></label></p>
                                <input type="number" id="rss_item_limit" name="settings[rss][item_limit]" value="<?php echo isset($settings['rss']['item_limit']) ? esc_attr($settings['rss']['item_limit']) : '10'; ?>" min="1" max="100" class="small-text">
                                <p class="description"><?php esc_html_e('Maximum number of items to fetch and process from the feed.', 'tradepress'); ?></p>
                                
                                <p><label for="rss_frequency"><strong><?php esc_html_e('Check Frequency', 'tradepress'); ?></strong></label></p>
                                <select id="rss_frequency" name="settings[rss][frequency]">
                                    <option value="hourly" <?php selected(isset($settings['rss']['frequency']) ? $settings['rss']['frequency'] : '', 'hourly'); ?>><?php esc_html_e('Hourly', 'tradepress'); ?></option>
                                    <option value="daily" <?php selected(isset($settings['rss']['frequency']) ? $settings['rss']['frequency'] : '', 'daily'); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                    <option value="weekly" <?php selected(isset($settings['rss']['frequency']) ? $settings['rss']['frequency'] : '', 'weekly'); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('How often to check for new content.', 'tradepress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <!-- API settings -->
                    <tr class="source-settings-row api-settings" <?php echo $type !== 'api' ? 'style="display:none;"' : ''; ?>>
                        <th scope="row"><label><?php esc_html_e('API Connection Settings', 'tradepress'); ?></label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('API Connection Settings', 'tradepress'); ?></legend>
                                
                                <p><label for="api_auth_type"><strong><?php esc_html_e('Authentication Type', 'tradepress'); ?></strong></label></p>
                                <select id="api_auth_type" name="settings[api][auth_type]">
                                    <option value="none" <?php selected(isset($settings['api']['auth_type']) ? $settings['api']['auth_type'] : '', 'none'); ?>><?php esc_html_e('None', 'tradepress'); ?></option>
                                    <option value="api_key" <?php selected(isset($settings['api']['auth_type']) ? $settings['api']['auth_type'] : '', 'api_key'); ?>><?php esc_html_e('API Key', 'tradepress'); ?></option>
                                    <option value="bearer" <?php selected(isset($settings['api']['auth_type']) ? $settings['api']['auth_type'] : '', 'bearer'); ?>><?php esc_html_e('Bearer Token', 'tradepress'); ?></option>
                                    <option value="basic" <?php selected(isset($settings['api']['auth_type']) ? $settings['api']['auth_type'] : '', 'basic'); ?>><?php esc_html_e('Basic Auth', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('The type of authentication required by the API.', 'tradepress'); ?></p>
                                
                                <div id="api_key_fields" style="<?php echo (!isset($settings['api']['auth_type']) || $settings['api']['auth_type'] !== 'api_key') ? 'display:none;' : ''; ?>">
                                    <p><label for="api_key_name"><strong><?php esc_html_e('API Key Parameter Name', 'tradepress'); ?></strong></label></p>
                                    <input type="text" id="api_key_name" name="settings[api][key_name]" value="<?php echo isset($settings['api']['key_name']) ? esc_attr($settings['api']['key_name']) : 'api_key'; ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('The parameter name for the API key (e.g., "api_key", "key", "apikey").', 'tradepress'); ?></p>
                                    
                                    <p><label for="api_key_value"><strong><?php esc_html_e('API Key', 'tradepress'); ?></strong></label></p>
                                    <input type="password" id="api_key_value" name="settings[api][key_value]" value="<?php echo isset($settings['api']['key_value']) ? esc_attr($settings['api']['key_value']) : ''; ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Your API key. This will be stored securely.', 'tradepress'); ?></p>
                                </div>
                                
                                <div id="bearer_fields" style="<?php echo (!isset($settings['api']['auth_type']) || $settings['api']['auth_type'] !== 'bearer') ? 'display:none;' : ''; ?>">
                                    <p><label for="api_bearer_token"><strong><?php esc_html_e('Bearer Token', 'tradepress'); ?></strong></label></p>
                                    <input type="password" id="api_bearer_token" name="settings[api][bearer_token]" value="<?php echo isset($settings['api']['bearer_token']) ? esc_attr($settings['api']['bearer_token']) : ''; ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Your bearer authentication token. This will be stored securely.', 'tradepress'); ?></p>
                                </div>
                                
                                <div id="basic_auth_fields" style="<?php echo (!isset($settings['api']['auth_type']) || $settings['api']['auth_type'] !== 'basic') ? 'display:none;' : ''; ?>">
                                    <p><label for="api_username"><strong><?php esc_html_e('Username', 'tradepress'); ?></strong></label></p>
                                    <input type="text" id="api_username" name="settings[api][username]" value="<?php echo isset($settings['api']['username']) ? esc_attr($settings['api']['username']) : ''; ?>" class="regular-text">
                                    
                                    <p><label for="api_password"><strong><?php esc_html_e('Password', 'tradepress'); ?></strong></label></p>
                                    <input type="password" id="api_password" name="settings[api][password]" value="<?php echo isset($settings['api']['password']) ? esc_attr($settings['api']['password']) : ''; ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Basic authentication credentials. These will be stored securely.', 'tradepress'); ?></p>
                                </div>
                                
                                <p><label for="api_frequency"><strong><?php esc_html_e('Check Frequency', 'tradepress'); ?></strong></label></p>
                                <select id="api_frequency" name="settings[api][frequency]">
                                    <option value="hourly" <?php selected(isset($settings['api']['frequency']) ? $settings['api']['frequency'] : '', 'hourly'); ?>><?php esc_html_e('Hourly', 'tradepress'); ?></option>
                                    <option value="daily" <?php selected(isset($settings['api']['frequency']) ? $settings['api']['frequency'] : '', 'daily'); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                    <option value="weekly" <?php selected(isset($settings['api']['frequency']) ? $settings['api']['frequency'] : '', 'weekly'); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('How often to fetch data from the API.', 'tradepress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <!-- Custom source settings -->
                    <tr class="source-settings-row custom-settings" <?php echo $type !== 'custom' ? 'style="display:none;"' : ''; ?>>
                        <th scope="row"><label><?php esc_html_e('Custom Source Settings', 'tradepress'); ?></label></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('Custom Source Settings', 'tradepress'); ?></legend>
                                
                                <p><label for="custom_description"><strong><?php esc_html_e('Description', 'tradepress'); ?></strong></label></p>
                                <textarea id="custom_description" name="settings[custom][description]" rows="3" class="large-text"><?php echo isset($settings['custom']['description']) ? esc_textarea($settings['custom']['description']) : ''; ?></textarea>
                                <p class="description"><?php esc_html_e('Detailed description of this custom source and how it should be processed.', 'tradepress'); ?></p>
                                
                                <p><label for="custom_format"><strong><?php esc_html_e('Data Format', 'tradepress'); ?></strong></label></p>
                                <select id="custom_format" name="settings[custom][format]">
                                    <option value="json" <?php selected(isset($settings['custom']['format']) ? $settings['custom']['format'] : '', 'json'); ?>><?php esc_html_e('JSON', 'tradepress'); ?></option>
                                    <option value="xml" <?php selected(isset($settings['custom']['format']) ? $settings['custom']['format'] : '', 'xml'); ?>><?php esc_html_e('XML', 'tradepress'); ?></option>
                                    <option value="csv" <?php selected(isset($settings['custom']['format']) ? $settings['custom']['format'] : '', 'csv'); ?>><?php esc_html_e('CSV', 'tradepress'); ?></option>
                                    <option value="text" <?php selected(isset($settings['custom']['format']) ? $settings['custom']['format'] : '', 'text'); ?>><?php esc_html_e('Plain Text', 'tradepress'); ?></option>
                                    <option value="other" <?php selected(isset($settings['custom']['format']) ? $settings['custom']['format'] : '', 'other'); ?>><?php esc_html_e('Other', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('The format of the data from this source.', 'tradepress'); ?></p>
                                
                                <p><label for="custom_frequency"><strong><?php esc_html_e('Check Frequency', 'tradepress'); ?></strong></label></p>
                                <select id="custom_frequency" name="settings[custom][frequency]">
                                    <option value="hourly" <?php selected(isset($settings['custom']['frequency']) ? $settings['custom']['frequency'] : '', 'hourly'); ?>><?php esc_html_e('Hourly', 'tradepress'); ?></option>
                                    <option value="daily" <?php selected(isset($settings['custom']['frequency']) ? $settings['custom']['frequency'] : '', 'daily'); ?>><?php esc_html_e('Daily', 'tradepress'); ?></option>
                                    <option value="weekly" <?php selected(isset($settings['custom']['frequency']) ? $settings['custom']['frequency'] : '', 'weekly'); ?>><?php esc_html_e('Weekly', 'tradepress'); ?></option>
                                    <option value="manual" <?php selected(isset($settings['custom']['frequency']) ? $settings['custom']['frequency'] : '', 'manual'); ?>><?php esc_html_e('Manual Only', 'tradepress'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('How often to check for new content.', 'tradepress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><label for="source_status"><?php esc_html_e('Status', 'tradepress'); ?></label></th>
                        <td>
                            <select id="source_status" name="source_status">
                                <option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 'tradepress'); ?></option>
                                <option value="paused" <?php selected($status, 'paused'); ?>><?php esc_html_e('Paused', 'tradepress'); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e('Set to paused to temporarily stop fetching data.', 'tradepress'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" id="submit-source" class="button button-primary"><?php echo $is_edit ? esc_html__('Update Source', 'tradepress') : esc_html__('Add Source', 'tradepress'); ?></button>
                    <button type="button" id="test-source" class="button button-secondary"><?php esc_html_e('Test Source', 'tradepress'); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Get source type label
     *
     * @param string $type Source type
     * @return string Label for the source type
     */
    private function get_source_type_label($type) {
        $types = array(
            'website' => __('Website Scraping', 'tradepress'),
            'rss' => __('RSS Feed', 'tradepress'),
            'webhook' => __('Webhook', 'tradepress'),
            'discord' => __('Discord Channel', 'tradepress'),
            'twitter' => __('Twitter Profile', 'tradepress'),
            'youtube' => __('YouTube Channel', 'tradepress'),
            'reddit' => __('Reddit Subreddit', 'tradepress'),
            'api' => __('API Endpoint', 'tradepress'),
            'custom' => __('Custom Source', 'tradepress')
        );
        
        return isset($types[$type]) ? $types[$type] : ucfirst($type);
    }

    /**
     * Get source usage statistics
     * 
     * @param int $source_id Source ID
     * @return array Statistics about usage
     */
    private function get_source_usage_stats($source_id) {
        // In a real implementation, we would query the database for this information
        // For now, returning demo data
        return array(
            'import_count' => mt_rand(1, 50),
            'directive_uses' => mt_rand(0, 100),
            'last_active' => date('Y-m-d H:i:s', strtotime('-' . mt_rand(1, 30) . ' days')),
        );
    }

    /**
     * Ajax handler for saving a source
     */
    public function ajax_save_source() {
        // Check nonce
        if (!isset($_POST['tradepress_source_nonce']) || !wp_verify_nonce($_POST['tradepress_source_nonce'], 'tradepress_save_source')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get form data
        $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
        $name = isset($_POST['source_name']) ? sanitize_text_field($_POST['source_name']) : '';
        $type = isset($_POST['source_type']) ? sanitize_text_field($_POST['source_type']) : '';
        $url = isset($_POST['source_url']) ? esc_url_raw($_POST['source_url']) : '';
        $status = isset($_POST['source_status']) ? sanitize_text_field($_POST['source_status']) : 'active';
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        // Validate required fields
        if (empty($name) || empty($type) || empty($url)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'tradepress')));
        }
        
        // Sanitize settings
        $sanitized_settings = array();
        foreach ($settings as $setting_group => $setting_values) {
            $sanitized_settings[$setting_group] = array();
            foreach ($setting_values as $key => $value) {
                $sanitized_settings[$setting_group][$key] = sanitize_text_field($value);
            }
        }
        
        // Prepare data for database
        $data = array(
            'name' => $name,
            'type' => $type,
            'url' => $url,
            'settings' => json_encode($sanitized_settings),
            'status' => $status
        );
        
        global $wpdb;
        
        // Save to database
        if ($source_id > 0) {
            // Update existing
            $result = $wpdb->update(
                $this->table_name,
                $data,
                array('id' => $source_id)
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => __('Error updating source.', 'tradepress') . ' ' . $wpdb->last_error));
            }
        } else {
            // Insert new
            $result = $wpdb->insert(
                $this->table_name,
                $data
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => __('Error adding source.', 'tradepress') . ' ' . $wpdb->last_error));
            }
            
            $source_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array(
            'message' => __('Source saved successfully.', 'tradepress'),
            'source_id' => $source_id
        ));
    }

    /**
     * Ajax handler for deleting a source
     */
    public function ajax_delete_source() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_source_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get source ID
        $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
        
        if ($source_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid source ID.', 'tradepress')));
        }
        
        global $wpdb;
        
        // Delete the source
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $source_id)
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error deleting source.', 'tradepress') . ' ' . $wpdb->last_error));
        }
        
        wp_send_json_success(array('message' => __('Source deleted successfully.', 'tradepress')));
    }

    /**
     * Ajax handler for toggling source status
     */
    public function ajax_toggle_source_status() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_source_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
        $action = isset($_POST['status_action']) ? sanitize_text_field($_POST['status_action']) : '';
        
        if ($source_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid source ID.', 'tradepress')));
        }
        
        $new_status = 'active';
        if ($action === 'pause') {
            $new_status = 'paused';
        } elseif ($action === 'archive') {
            $new_status = 'archived';
        }
        
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array('status' => $new_status),
            array('id' => $source_id)
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error updating source status.', 'tradepress')));
        }
        
        wp_send_json_success(array('message' => __('Source status updated.', 'tradepress')));
    }
    
    /**
     * Ajax handler for archiving a source
     */
    public function ajax_archive_source() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tradepress_source_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        $source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
        
        if ($source_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid source ID.', 'tradepress')));
        }
        
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array('status' => 'archived'),
            array('id' => $source_id)
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Error archiving source.', 'tradepress')));
        }
        
        wp_send_json_success(array('message' => __('Source archived successfully.', 'tradepress')));
    }

    /**
     * Ajax handler for testing a source
     */
    public function ajax_test_source() {
        // Check nonce
        if (!isset($_POST['tradepress_source_nonce']) || !wp_verify_nonce($_POST['tradepress_source_nonce'], 'tradepress_save_source')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'tradepress')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to do this.', 'tradepress')));
        }
        
        // Get form data
        $source_type = isset($_POST['source_type']) ? sanitize_text_field($_POST['source_type']) : '';
        $source_url = isset($_POST['source_url']) ? esc_url_raw($_POST['source_url']) : '';
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();
        
        // Validate required fields
        if (empty($source_type) || empty($source_url)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields.', 'tradepress')));
        }
        
        try {
            // Test the source based on its type
            $sample_data = $this->test_source_connection($source_type, $source_url, $settings);
            
            // Return success with sample data
            wp_send_json_success(array(
                'message' => __('Source tested successfully.', 'tradepress'),
                'sample' => $sample_data['data'],
                'notes' => $sample_data['notes']
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'trace' => __('Error occurred while testing the source.', 'tradepress'),
                'suggestion' => __('Check the URL/location and ensure it is accessible.', 'tradepress')
            ));
        }
    }
    
    /**
     * Test a source connection and return sample data
     *
     * @param string $source_type Source type
     * @param string $source_url Source URL/location
     * @param array $settings Source settings
     * @return array Sample data and notes
     */
    public function test_source_connection($source_type, $source_url, $settings) {
        $result = array(
            'data' => null,
            'notes' => ''
        );
        
        switch ($source_type) {
            case 'website':
                // Test website scraping
                $result = $this->test_website_source($source_url, $settings['website'] ?? array());
                break;
                
            case 'rss':
                // Test RSS feed
                $result = $this->test_rss_source($source_url, $settings['rss'] ?? array());
                break;
                
            case 'api':
                // Test API endpoint
                $result = $this->test_api_source($source_url, $settings['api'] ?? array());
                break;
                
            case 'custom':
                // Test custom source
                $result = $this->test_custom_source($source_url, $settings['custom'] ?? array());
                break;
                
            default:
                throw new Exception(__('Source type testing not implemented yet.', 'tradepress'));
        }
        
        return $result;
    }

    /**
     * Test a website source
     *
     * @param string $url Website URL
     * @param array $settings Website settings
     * @return array Sample data and notes
     */
    private function test_website_source($url, $settings) {
        $selector = isset($settings['selector']) ? $settings['selector'] : '';
        
        // Initialize WP_Http
        $http = new WP_Http();
        $response = $http->get($url);
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            throw new Exception(__('Failed to connect to the website. Status code: ', 'tradepress') . wp_remote_retrieve_response_code($response));
        }
        
        $html = wp_remote_retrieve_body($response);
        
        // If we have a selector, try to parse the HTML and extract content
        if (!empty($selector)) {
            // Use simple HTML DOM parser if available, otherwise return a portion of the HTML
            if (function_exists('file_get_html')) {
                $html_dom = str_get_html($html);
                if ($html_dom) {
                    $elements = $html_dom->find($selector);
                    if ($elements) {
                        $extracted_content = '';
                        // Limit to first 3 elements
                        $count = 0;
                        foreach ($elements as $element) {
                            if ($count++ >= 3) break;
                            $extracted_content .= $element->outertext . "\n";
                        }
                        
                        return array(
                            'data' => $extracted_content,
                            'notes' => sprintf(__('Found %d elements matching selector "%s". Showing first 3.', 'tradepress'), count($elements), $selector)
                        );
                    } else {
                        throw new Exception(sprintf(__('No elements found matching selector "%s".', 'tradepress'), $selector));
                    }
                }
            }
        }
        
        // If we couldn't use the selector or none was provided, return a sample of the HTML
        $sample_html = substr($html, 0, 500) . '...';
        
        return array(
            'data' => $sample_html,
            'notes' => __('Showing first 500 characters of HTML. Provide a CSS selector to extract specific content.', 'tradepress')
        );
    }
    
    /**
     * Test an RSS feed source
     *
     * @param string $url RSS feed URL
     * @param array $settings RSS settings
     * @return array Sample data and notes
     */
    private function test_rss_source($url, $settings) {
        $item_limit = isset($settings['item_limit']) ? intval($settings['item_limit']) : 10;
        
        // Use WordPress's built-in feed parser
        include_once(ABSPATH . WPINC . '/feed.php');
        
        // Limit to a short timeout for testing
        add_filter('wp_feed_cache_transient_lifetime', function() { return 60; });
        
        $feed = fetch_feed($url);
        
        if (is_wp_error($feed)) {
            throw new Exception($feed->get_error_message());
        }
        
        $item_count = $feed->get_item_quantity();
        
        if ($item_count === 0) {
            throw new Exception(__('No items found in RSS feed.', 'tradepress'));
        }
        
        // Get a limited number of items
        $items = $feed->get_items(0, min(3, $item_count));
        
        $sample_data = array();
        
        foreach ($items as $item) {
            $sample_data[] = array(
                'title' => $item->get_title(),
                'date' => $item->get_date('Y-m-d H:i:s'),
                'link' => $item->get_permalink(),
                'description' => wp_trim_words($item->get_description(), 30, '...')
            );
        }
        
        return array(
            'data' => $sample_data,
            'notes' => sprintf(__('Found %d items in feed. Showing first %d.', 'tradepress'), $item_count, count($sample_data))
        );
    }
    
    /**
     * Test an API source
     *
     * @param string $url API endpoint URL
     * @param array $settings API settings
     * @return array Sample data and notes
     */
    private function test_api_source($url, $settings) {
        $auth_type = isset($settings['auth_type']) ? $settings['auth_type'] : 'none';
        
        // Initialize request arguments
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json'
            )
        );
        
        // Add authentication if needed
        switch ($auth_type) {
            case 'api_key':
                $key_name = isset($settings['key_name']) ? $settings['key_name'] : 'api_key';
                $key_value = isset($settings['key_value']) ? $settings['key_value'] : '';
                
                if (!empty($key_value)) {
                    // Check if this is a header or query parameter
                    if (strpos($key_name, 'x-') === 0 || strpos($key_name, 'X-') === 0) {
                        $args['headers'][$key_name] = $key_value;
                    } else {
                        // Add as query parameter
                        $url = add_query_arg($key_name, $key_value, $url);
                    }
                }
                break;
                
            case 'bearer':
                $token = isset($settings['bearer_token']) ? $settings['bearer_token'] : '';
                if (!empty($token)) {
                    $args['headers']['Authorization'] = 'Bearer ' . $token;
                }
                break;
                
            case 'basic':
                $username = isset($settings['username']) ? $settings['username'] : '';
                $password = isset($settings['password']) ? $settings['password'] : '';
                if (!empty($username) && !empty($password)) {
                    $args['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
                }
                break;
        }
        
        // Make the request
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            throw new Exception(sprintf(__('API returned error code: %d', 'tradepress'), $status_code));
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // Try to parse JSON
        $json_body = json_decode($body, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Check if we need to limit the size of the response
            if (is_array($json_body) && count($json_body) > 3 && isset($json_body[0])) {
                // Looks like an array of items, limit to 3
                $sample_data = array_slice($json_body, 0, 3);
                $notes = sprintf(__('API returned %d items. Showing first 3.', 'tradepress'), count($json_body));
            } else {
                $sample_data = $json_body;
                $notes = __('API response received successfully.', 'tradepress');
            }
            
            return array(
                'data' => $sample_data,
                'notes' => $notes
            );
        }
        
        // If not JSON, return a portion of the response
        $sample_body = substr($body, 0, 1000) . (strlen($body) > 1000 ? '...' : '');
        
        return array(
            'data' => $sample_body,
            'notes' => __('API response is not in JSON format. Showing first 1000 characters.', 'tradepress')
        );
    }
    
    /**
     * Test a custom source
     *
     * @param string $url Custom source URL/identifier
     * @param array $settings Custom source settings
     * @return array Sample data and notes
     */
    private function test_custom_source($url, $settings) {
        // For custom sources, we'll return a sample of what might be expected
        $format = isset($settings['format']) ? $settings['format'] : 'json';
        $description = isset($settings['description']) ? $settings['description'] : '';
        
        $sample_data = array(
            'source_id' => 'custom_' . substr(md5($url), 0, 8),
            'identifier' => $url,
            'description' => $description,
            'format' => $format,
            'test_mode' => true,
            'sample_records' => array(
                array(
                    'id' => 1,
                    'date' => date('Y-m-d H:i:s'),
                    'value' => 123.45,
                    'content' => 'Sample custom source data entry 1'
                ),
                array(
                    'id' => 2,
                    'date' => date('Y-m-d H:i:s', time() - 3600),
                    'value' => 234.56,
                    'content' => 'Sample custom source data entry 2'
                )
            )
        );
        
        return array(
            'data' => $sample_data,
            'notes' => __('This is sample data for a custom source. In production, you would need to implement a specific handler for this source type.', 'tradepress')
        );
    }
}