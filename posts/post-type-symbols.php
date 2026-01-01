<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Methods for handling all post types including "post", "page" and 
 * registrating custom post types.
 *
 * @class     TradePress_Post_types
 * @version   1.0.0
 * @package   TradePress/Includes/Post_Types
 * @category  Class
 * @author    Ryan Bayne
 */
class TradePress_Post_Type_Symbols {

    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
        add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_custom_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_TradePress_post_sharing_options' ) );  
        add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'post_submitbox' ) );
        add_action('init', array(__CLASS__, 'register_post_meta'), 10);
        add_filter('post_updated_messages', array(__CLASS__, 'updated_messages'), 10, 1);
        add_filter('bulk_post_updated_messages', array(__CLASS__, 'bulk_updated_messages'), 10, 2);
        
        // Add template support for the symbols post type
        add_post_type_support('symbols', 'page-attributes');
        
        // Add this hook to the init function
        add_action('save_post', array(__CLASS__, 'update_data_timestamp'), 10, 3);
        
        // Add this new line to include symbols in Recent Posts widget
        add_filter('widget_posts_args', array(__CLASS__, 'include_symbols_in_recent_posts'), 10, 1);
        
        // Add filters for managing admin columns
        add_filter('manage_symbols_posts_columns', array(__CLASS__, 'set_custom_edit_symbols_columns'));
        add_action('manage_symbols_posts_custom_column', array(__CLASS__, 'custom_symbols_column'), 10, 2);
        
        // Add filter to make the Score column sortable
        add_filter('manage_edit-symbols_sortable_columns', array(__CLASS__, 'set_custom_symbols_sortable_columns'));
    }
    
    public static function register_taxonomies() {
        
        if ( ! is_blog_installed() ) {
            return;
        }

        $permalinks = TradePress_get_permalink_structure();
        
        if ( !taxonomy_exists( 'symbols_type' ) ) {
            // Add taxonomy registration here
            register_taxonomy(
                'symbols_type',
                'symbols',
                array(
                    'labels' => array(
                        'name'              => __( 'Symbol Types', 'tradepress' ),
                        'singular_name'     => __( 'Symbol Type', 'tradepress' ),
                        'search_items'      => __( 'Search Symbol Types', 'tradepress' ),
                        'all_items'         => __( 'All Symbol Types', 'tradepress' ),
                        'parent_item'       => __( 'Parent Symbol Type', 'tradepress' ),
                        'parent_item_colon' => __( 'Parent Symbol Type:', 'tradepress' ),
                        'edit_item'         => __( 'Edit Symbol Type', 'tradepress' ),
                        'update_item'       => __( 'Update Symbol Type', 'tradepress' ),
                        'add_new_item'      => __( 'Add New Symbol Type', 'tradepress' ),
                        'new_item_name'     => __( 'New Symbol Type Name', 'tradepress' ),
                        'menu_name'         => __( 'Symbol Types', 'tradepress' ),
                    ),
                    'hierarchical'      => true,
                    'show_ui'           => true,
                    'show_in_menu'      => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
                    'rewrite'           => array( 'slug' => 'symbol-type' ),
                )
            );
        }
        
        do_action( 'TradePress_after_register_taxonomy' );        
    }

    /**
     * Register core post types.
     * 
     * @link https://developer.wordpress.org/reference/functions/register_post_type/   
     */
    public static function register_post_type() {
        if ( ! is_blog_installed() || post_type_exists( 'symbols' ) ) {
            return;
        }

        $permalinks = TradePress_get_permalink_structure();  
        
        register_post_type( 'symbols',
            apply_filters( 'TradePress_register_post_type_symbols',
                array(
                    'labels' => array(
                            'name'                  => __( 'TradePress Stock Symbols', 'tradepress' ),
                            'singular_name'         => __( 'Stock Symbol', 'tradepress' ),
                            'menu_name'             => _x( 'Symbols', 'Admin menu name', 'tradepress' ),
                            'add_new'               => __( 'Add a symbol', 'tradepress' ),
                            'add_new_item'          => __( 'Add New Symbol', 'tradepress' ),
                            'edit'                  => __( 'Edit', 'tradepress' ),
                            'edit_item'             => __( 'Edit TradePress symbol', 'tradepress' ),
                            'new_item'              => __( 'New symbol', 'tradepress' ),
                            'view'                  => __( 'View symbol', 'tradepress' ),
                            'view_item'             => __( 'View symbol', 'tradepress' ),
                            'search_items'          => __( 'Search symbols', 'tradepress' ),
                            'not_found'             => __( 'No symbols found', 'tradepress' ),
                            'not_found_in_trash'    => __( 'No symbols found in trash', 'tradepress' ),
                            'parent'                => __( 'Parent symbol', 'tradepress' ),
                            'featured_image'        => __( 'Symbol image', 'tradepress' ),
                            'set_featured_image'    => __( 'Set symbol image', 'tradepress' ),
                            'remove_featured_image' => __( 'Remove symbol image', 'tradepress' ),
                            'use_featured_image'    => __( 'Use as symbol image', 'tradepress' ),
                            'insert_into_item'      => __( 'Insert into content', 'tradepress' ),
                            'uploaded_to_this_item' => __( 'Uploaded to this symbol post', 'tradepress' ),
                            'filter_items_list'     => __( 'Filter symbol', 'tradepress' ),
                            'items_list_navigation' => __( 'Twitch symbol navigation', 'tradepress' ),
                            'items_list'            => __( 'Symbol list', 'tradepress' ),
                        ),
                    'description'         => __( 'This is where you can add stock market symbols.', 'tradepress' ),
                    'public'              => true,
                    'show_ui'             => true,
                    'publicly_queryable'  => true,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, 
                    'rewrite'             => $permalinks['symbols_rewrite_slug'] ? array( 'slug' => $permalinks['symbols_rewrite_slug'], 'with_front' => false, 'symbols' => true ) : false,
                    'query_var'           => true, // Change to true to allow in nav menus
                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'wpcom-markdown' ),
                    'has_archive'         => true, // Change to true to show in admin menu
                    'show_in_nav_menus'   => true,
                    'show_in_rest'        => true,
                    'show_in_menu'        => false,
                    'map_meta_cap'        => true,
                    'capability_type'     => 'post',
                )
            )
        );
    }

    /**
     * Register our custom post statuses, used for order status.
     * 
     * @version 1.0
     */
    public static function register_post_status() {

        $order_statuses = apply_filters( 'TradePress_register_symbols_post_statuses',
            array(
                'TradePress-awaitingtrigger'    => array(
                    'label'                     => _x( 'Awaiting Trigger', 'Order status', 'tradepress' ),
                    'public'                    => false,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'Awaiting Trigger <span class="count">(%s)</span>', 'Awaiting Trigger <span class="count">(%s)</span>', 'tradepress' ),
                )
            )
        );

        foreach ( $order_statuses as $order_status => $values ) {
            register_post_status( $order_status, $values );
        }
    }

    /**
     * Allow twitchfeed posts in API controlled by JetPack.
     *
     * @param  array $post_types
     * @return array
     */
    public static function rest_api_allowed_post_types( $post_types ) {

        return $post_types;
    }
    
    /**
    * Add all custom meta boxes. 
    * 
    * @version 1.0
    */
    public static function add_custom_boxes() {
        global $post;
        
        // Display checkbox option to share post content to Twitch.
        $post_type = get_post_type( $post );
        
        // Should this post-type get Twitch sharing controls? 
        if( 'yes' == get_option( 'tradepress_shareable_posttype_' . $post_type ) ) {
            add_meta_box(
                'tradepress_post_sharing_options', // Unique ID
                __( 'Symbols', 'tradepress' ),  
                array( __CLASS__, 'html_tradepress_post_sharing_options' ),        
                $post_type // Post type
            );
        }
    } 
    
    /**
    * Options for sharing post content to Twitch feed.
    * 
    * @param mixed $post
    * 
    * @version 1.0
    */
    public static function html_TradePress_post_sharing_options($post) {
        
        /*
        ?>
        <label for="TradePress_whentoshare"><?php _e( 'When should the content be shared?', 'tradepress' ); ?></label>
        <select name="TradePress_whentoshare" id="TradePress_whentoshare" class="postbox">
            <option value="">Select something...</option>
            <option value="publishing">ASAP</option>
        </select>
        <?php
        */
        
    }
 
    /**
    * Saves and processes share to feed options.
    * 
    * @param mixed $post_id
    * 
    * @version 1.0
    */
    public static function save_TradePress_post_sharing_options($post_id){
        
        /*
        if ( array_key_exists( 'TradePress_whentoshare', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_TradePress_whentoshare',
                $_POST['TradePress_whentoshare']
            );
        }
        */
        
        return $post_id;
    }  
    
    /**
    * Display custom fields in the publish box. 
    * 
    * @version 1.0
    */
    public static function post_submitbox() {
        global $post;
        
        // Display checkbox option to share post content to Twitch.
        $post_type = get_post_type($post);
    }   

    public static function register_post_meta() {
        // Register core symbol meta fields
        register_post_meta('symbols', '_tradepress_ticker', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => '__return_true'
        ));
        
        register_post_meta('symbols', '_tradepress_exchange', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => '__return_true'
        ));
        
        register_post_meta('symbols', '_tradepress_last_price', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'number',
            'auth_callback' => '__return_true'
         ));
        
        // Add data last updated timestamp
        register_post_meta('symbols', '_tradepress_data_last_updated', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'number',
            'auth_callback' => '__return_true',
            'description' => __('Timestamp when symbol data was last updated', 'tradepress')
         ));
        
        // Add score meta field
        register_post_meta('symbols', '_tradepress_score', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'number',
            'auth_callback' => '__return_true',
            'description' => __('Trading score for the symbol', 'tradepress')
        ));
    }

    /**
     * Update the last modified timestamp when symbol data is updated
     *
     * @param int $post_id The post ID
     * @param WP_Post $post The post object
     * @param bool $update Whether this is an update
     */
    public static function update_data_timestamp($post_id, $post, $update) {
        // Only run for symbols post type
        if ($post->post_type !== 'symbols') {
            return;
        }
        
        // Avoid infinite loop
        remove_action('save_post', array(__CLASS__, 'update_data_timestamp'), 10, 3);
        
        // Update the timestamp when any price-related data is changed
        $price_related_fields = array(
            '_tradepress_last_price', 
            '_tradepress_price_change',
            '_tradepress_price_change_pct'
        );
        
        $should_update = false;
        foreach ($price_related_fields as $field) {
            if (isset($_POST[$field])) {
                $should_update = true;
                break;
            }
        }
        
        if ($should_update) {
            update_post_meta($post_id, '_tradepress_data_last_updated', current_time('timestamp'));
        }
        
        // Re-add the action
        add_action('save_post', array(__CLASS__, 'update_data_timestamp'), 10, 3);
    }

    /**
     * Include symbols in Recent Posts widget
     *
     * @param array $args Widget arguments
     * @return array Modified arguments
     */
    public static function include_symbols_in_recent_posts($args) {
        if (!isset($args['post_type'])) {
            $args['post_type'] = array('post', 'symbols');
        }
        return $args;
    }

    /**
     * Customize post type messages.
     * 
     * @param array $messages Post updated messages.
     * @return array
     */
    public static function updated_messages($messages) {
        global $post;

        $messages['symbols'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => __('Symbol updated.', 'tradepress'),
            2  => __('Custom field updated.', 'tradepress'),
            3  => __('Custom field deleted.', 'tradepress'),
            4  => __('Symbol updated.', 'tradepress'),
            /* translators: %s: date and time of the revision */
            5  => isset($_GET['revision']) ? sprintf(__('Symbol restored to revision from %s', 'tradepress'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => __('Symbol published.', 'tradepress'),
            7  => __('Symbol saved.', 'tradepress'),
            8  => __('Symbol submitted.', 'tradepress'),
            9  => sprintf(
                __('Symbol scheduled for: <strong>%1$s</strong>.', 'tradepress'),
                /* translators: Publish box date string format */
                date_i18n(__('M j, Y @ G:i', 'tradepress'), strtotime($post->post_date))
            ),
            10 => __('Symbol draft updated.', 'tradepress')
        );

        return $messages;
    }

    /**
     * Customize bulk post type messages.
     *
     * @param array $bulk_messages Arrays of messages, each keyed by the corresponding post type.
     * @param array $bulk_counts Array of item counts for each message.
     * @return array
     */
    public static function bulk_updated_messages($bulk_messages, $bulk_counts) {
        $bulk_messages['symbols'] = array(
            /* translators: %s: product count */
            'updated'   => _n('%s symbol updated.', '%s symbols updated.', $bulk_counts['updated'], 'tradepress'),
            /* translators: %s: product count */
            'locked'    => _n('%s symbol not updated, somebody is editing it.', '%s symbols not updated, somebody is editing them.', $bulk_counts['locked'], 'tradepress'),
            /* translators: %s: product count */
            'deleted'   => _n('%s symbol permanently deleted.', '%s symbols permanently deleted.', $bulk_counts['deleted'], 'tradepress'),
            /* translators: %s: product count */
            'trashed'   => _n('%s symbol moved to the Trash.', '%s symbols moved to the Trash.', $bulk_counts['trashed'], 'tradepress'),
            /* translators: %s: product count */
            'untrashed' => _n('%s symbol restored from the Trash.', '%s symbols restored from the Trash.', $bulk_counts['untrashed'], 'tradepress'),
        );

        return $bulk_messages;
    }

    /**
     * Set custom columns for Symbols post type admin table.
     *
     * @param array $columns Default columns.
     * @return array Modified columns.
     */
    public static function set_custom_edit_symbols_columns($columns) {
        // Add the demo notice at the top of the page
        add_action('admin_notices', array(__CLASS__, 'display_demo_notice'));
        
        $columns['ticker'] = __('Ticker', 'tradepress');
        $columns['exchange'] = __('Exchange', 'tradepress');
        $columns['last_price'] = __('Last Price', 'tradepress');
        $columns['score'] = __('Score', 'tradepress');
        return $columns;
    }

    /**
     * Display a demo indicator notice at the top of the Symbols listing page
     */
    public static function display_demo_notice() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'edit-symbols') {
            echo '<div class="demo-indicator">
                <div class="demo-icon dashicons dashicons-admin-tools"></div>
                <div class="demo-text">
                    <h4>' . esc_html__('Demo Data', 'tradepress') . '</h4>
                    <p>' . esc_html__('The Score column contains demo values for testing and development purposes.', 'tradepress') . '</p>
                </div>
                <span class="demo-badge">' . esc_html__('DEMO', 'tradepress') . '</span>
            </div>';
        }
    }

    /**
     * Populate custom columns for Symbols post type admin table.
     *
     * @param string $column Column name.
     * @param int $post_id Post ID.
     */
    public static function custom_symbols_column($column, $post_id) {
        switch ($column) {
            case 'ticker':
                echo esc_html(get_post_meta($post_id, '_tradepress_ticker', true));
                break;
            case 'exchange':
                echo esc_html(get_post_meta($post_id, '_tradepress_exchange', true));
                break;
            case 'last_price':
                $price = get_post_meta($post_id, '_tradepress_last_price', true);
                echo !empty($price) ? '$' . number_format($price, 2) : '';
                break;
            case 'score':
                // Get existing score or generate a demo score if it doesn't exist
                $score = get_post_meta($post_id, '_tradepress_score', true);
                
                if (empty($score)) {
                    // Generate a demo score between 0 and 100
                    $score = mt_rand(10, 95);
                    
                    // Store the demo score
                    update_post_meta($post_id, '_tradepress_score', $score);
                }
                
                // Output the score with color coding based on value
                self::render_score_indicator($score);
                break;
        }
    }

    /**
     * Render a visual score indicator
     *
     * @param int $score The score value (0-100)
     */
    public static function render_score_indicator($score) {
        // Ensure the score is within valid range
        $score = max(0, min(100, intval($score)));
        
        // Determine color based on score value
        if ($score < 30) {
            $color = '#e53935'; // Red
            $label = __('Low', 'tradepress');
        } elseif ($score < 50) {
            $color = '#fb8c00'; // Orange
            $label = __('Moderate', 'tradepress');
        } elseif ($score < 70) {
            $color = '#fdd835'; // Yellow
            $label = __('Average', 'tradepress');
        } elseif ($score < 90) {
            $color = '#43a047'; // Green
            $label = __('Good', 'tradepress');
        } else {
            $color = '#2e7d32'; // Dark green
            $label = __('Excellent', 'tradepress');
        }
        
        // For demo purposes, use a random value between 70-100 for the max possible score
        // In a real implementation, this would come from the strategy that scored the symbol
        $max_possible_score = mt_rand(max($score, 70), 100);
        
        // Calculate the percentage of the maximum possible score
        $percentage = round(($score / $max_possible_score) * 100);
        
        // Create a visual score indicator
        echo '<div class="score-indicator" style="display: inline-block; min-width: 40px; text-align: center; padding: 3px 8px; border-radius: 3px; background-color: ' . esc_attr($color) . '; color: white;">';
        echo esc_html($score);
        echo '</div> ';
        
        // Display percentage alongside the score
        echo '<span class="score-percentage" style="margin-left: 5px; font-weight: bold;">' . esc_html($percentage) . '%</span> ';
        
        // Display the score label
        echo '<span class="score-label" style="margin-left: 5px; color: ' . esc_attr($color) . ';">' . esc_html($label) . '</span>';
    }

    /**
     * Make custom columns sortable in Symbols post type admin table.
     *
     * @param array $columns Default sortable columns.
     * @return array Modified sortable columns.
     */
    public static function set_custom_symbols_sortable_columns($columns) {
        $columns['ticker'] = 'ticker';
        $columns['exchange'] = 'exchange';
        $columns['last_price'] = 'last_price';
        $columns['score'] = 'score';
        return $columns;
    }
}