<?php
/**
 * TradePress Admin Class
 *
 * Manages the admin area functionality for TradePress, including menu
 * registration, settings, and page handling.
 *
 * @package TradePress\Admin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin class
 *
 * Core class for managing the admin area of TradePress.
 */
class TradePress_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        // Add admin menu and settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Register settings tabs
        $this->register_settings_tabs();
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Add top-level menu
        add_menu_page(
            'TradePress',
            'TradePress',
            'manage_options',
            'tradepress',
            array($this, 'admin_page'),
            'dashicons-chart-line',
            100
        );
        
        // Add submenus
        add_submenu_page('tradepress', 'Settings', 'Settings', 'manage_options', 'tradepress_settings', array($this, 'settings_page'));
        add_submenu_page('tradepress', 'SEES', 'SEES', 'manage_options', 'tradepress_sees', array($this, 'sees_page'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings for each tab
        register_setting('tradepress_settings_group', 'tradepress_settings');
        register_setting('tradepress_sees_group', 'tradepress_sees_settings');
    }

    /**
     * Register settings tabs
     */
    public function register_settings_tabs() {
        $this->register_tab('settings', new TradePress_Admin_Settings_SEES());
    }

    /**
     * Admin page callback
     */
    public function admin_page() {
        // Render the admin page
        include_once TRADEPRESS_PLUGIN_DIR . 'admin/page/admin-overview.php';
        $overview_page = new TradePress_Admin_Overview();
        $overview_page->render();
    }

    /**
     * Settings page callback
     */
    public function settings_page() {
        // Render the settings page
        include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/admin-settings.php';
        $settings_page = new TradePress_Admin_Settings();
        $settings_page->render();
    }

    /**
     * SEES page callback
     */
    public function sees_page() {
        // Render the SEES settings page
        include_once TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/settings/view/sees.php';
        $sees_page = new TradePress_Admin_SEES();
        $sees_page->render();
    }
}