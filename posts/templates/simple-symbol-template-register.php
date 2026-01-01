<?php
/**
 * Simple Symbol Template Registration
 * 
 * A simplified approach to register templates for the symbols post type
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers the symbol template in WordPress
 */
function tradepress_register_symbol_template() {
    // Define the template path
    $template_path = plugin_dir_path(__FILE__) . 'symbol-template.php';
    
    // Only register if the template file exists
    if (!file_exists($template_path)) {
        // Use WordPress admin notice system instead of error_log
        add_action('admin_notices', 'tradepress_template_missing_notice');
        return;
    }
    
    // Add filters to register the template for different post types
    add_filter('theme_symbols_templates', 'tradepress_add_symbol_template');
    add_filter('theme_page_templates', 'tradepress_add_symbol_template');
    
    // For WordPress 4.7+, this ensures the template shows in the dropdown
    add_filter('theme_templates', 'tradepress_add_template_to_dropdown', 10, 4);
    
    // Set the template when a symbol post is viewed
    add_filter('single_template', 'tradepress_use_symbol_template');
    
    // Enqueue the CSS file for the symbol template
    add_action('wp_enqueue_scripts', 'tradepress_enqueue_symbol_styles');
    
    // Register sidebar for the symbol template
    add_action('widgets_init', 'tradepress_register_symbol_sidebar');
}
add_action('init', 'tradepress_register_symbol_template', 20);

/**
 * Display admin notice if template file is missing
 */
function tradepress_template_missing_notice() {
    echo '<div class="error notice"><p>';
    echo sprintf(
        __('TradePress Symbol Template: Template file not found at %s', 'tradepress'), 
        plugin_dir_path(__FILE__) . 'symbol-template.php'
    );
    echo '</p></div>';
}

/**
 * Adds the template to the templates list
 */
function tradepress_add_symbol_template($templates) {
    $templates['symbol-template.php'] = 'TradePress Symbol Template';
    return $templates;
}

/**
 * This is the critical filter for WordPress 4.7+ to show templates in dropdown
 */
function tradepress_add_template_to_dropdown($post_templates, $wp_theme, $post, $post_type) {
    // Add template for symbols and pages
    if ($post_type === 'symbols' || $post_type === 'page') {
        $post_templates['symbol-template.php'] = 'TradePress Symbol Template';
    }
    return $post_templates;
}

/**
 * Use our template when a symbol post is viewed
 */
function tradepress_use_symbol_template($template) {
    global $post;
    
    // Exit early if not dealing with a post
    if (!$post) {
        return $template;
    }
    
    // Check if viewing a symbol post type or using our template
    if ($post->post_type === 'symbols' || get_post_meta($post->ID, '_wp_page_template', true) === 'symbol-template.php') {
        $new_template = plugin_dir_path(__FILE__) . 'symbol-template.php';
        
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    
    return $template;
}

/**
 * Enqueue CSS styles for the symbol template
 */
function tradepress_enqueue_symbol_styles() {
    // Only load the CSS when viewing a symbol post or page with the symbol template
    if (is_singular('symbols') || 
        (is_singular() && get_post_meta(get_the_ID(), '_wp_page_template', true) === 'symbol-template.php')) {
        
        // Register the style
        wp_register_style(
            'tradepress-symbol-styles',
            TRADEPRESS_PLUGIN_URL . '/assets/css/tradepress-symbol.css',
            array(),
            filemtime(TRADEPRESS_PLUGIN_DIR_PATH . 'assets/css/tradepress-symbol.css')
        );
        
        // Enqueue the style
        wp_enqueue_style('tradepress-symbol-styles');
        
        // Enqueue TradingView script
        wp_register_script(
            'tradingview-external-api',
            'https://s3.tradingview.com/tv.js',
            array(),
            null,
            true
        );
        
        wp_register_script(
            'tradepress-tradingview-widget',
            TRADEPRESS_PLUGIN_URL . '/assets/js/tradingview-widget.js',
            array('jquery', 'tradingview-external-api'),
            filemtime(TRADEPRESS_PLUGIN_DIR_PATH . 'assets/js/tradingview-widget.js'),
            true
        );
        
        wp_enqueue_script('tradingview-external-api');
        wp_enqueue_script('tradepress-tradingview-widget');
    }
}

/**
 * Make sure the symbols post type supports templates
 */
function tradepress_add_template_support_to_symbols() {
    add_post_type_support('symbols', 'page-attributes');
    add_post_type_support('symbols', 'custom-fields');
}
add_action('init', 'tradepress_add_template_support_to_symbols', 11);

/**
 * Register a sidebar for the symbol template
 */
function tradepress_register_symbol_sidebar() {
    register_sidebar(array(
        'name'          => __('Symbol Template Sidebar', 'tradepress'),
        'id'            => 'tradepress-symbol-sidebar',
        'description'   => __('Widgets in this area will be shown on symbol template pages.', 'tradepress'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
      ));
}
