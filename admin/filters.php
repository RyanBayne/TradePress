<?php
/**
 * TradePress Admin Filters
 *
 * This file contains filters for the TradePress admin pages.
 *
 * @package TradePress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filters for the TradePress admin pages.
 *
 * @package TradePress
 */
function tradepress_filter_admin_title( $admin_title, $title ) {
    if (isset($_GET['page']) && $_GET['page'] == 'tradepress_development') {
        $tabs = TradePress_Admin_Development_Page::get_tabs();
        $current_tab = isset($_GET['tab']) ? sanitize_title($_GET['tab']) : 'current_task';
        $tab_title = isset($tabs[$current_tab]) ? $tabs[$current_tab] : '';
        $main_title = __('TradePress Development', 'tradepress');
        if ($tab_title) {
            $admin_title = $tab_title . ': ' . $main_title . ' ‹ ' . get_bloginfo('name');
        } else {
            $admin_title = $main_title . ' ‹ ' . get_bloginfo('name');
        }
    }
    return $admin_title;
}
add_filter('admin_title', 'tradepress_filter_admin_title', 10, 2);

