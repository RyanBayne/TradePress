<?php
/**
 * TradePress Pointer Registry
 * 
 * Centralized registry for all WordPress pointers in TradePress
 *
 * @package TradePress/Pointers
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TradePress_Pointer_Registry {
    
    /**
     * Get all registered pointers
     *
     * @return array Array of pointer configurations
     */
    public static function get_all_pointers() {
        return array(
            'tradepress_directive_configuration' => array(
                'id' => 'tradepress_directive_configuration',
                'title' => __('Configure Your Directive', 'tradepress'),
                'content' => __('Use these settings to customize how the directive calculates scores. Adjust the weight to control its importance in the overall scoring algorithm, and configure specific parameters for optimal performance.', 'tradepress'),
                'target' => '.directive-settings',
                'position' => 'left',
                'page' => 'tradepress_scoring_directives',
                'tab' => 'configure_directives',
                'status' => 'active',
                'priority' => 1,
                'category' => 'configuration'
            ),
            'tradepress_testing_pointer' => array(
                'id' => 'tradepress_testing_pointer',
                'title' => __('Testing Your Configuration', 'tradepress'),
                'content' => __('Use this section to test how your directive configuration performs with real market data. Select a trading mode and symbol, then click Test to see the scoring results.', 'tradepress'),
                'target' => '#tradepress-testing-pointer-target',
                'position' => 'top',
                'page' => 'tradepress_scoring_directives',
                'tab' => 'configure_directives',
                'status' => 'active',
                'priority' => 2,
                'category' => 'testing',
                'depends_on' => 'tradepress_directive_configuration'
            ),
            'tradepress_automatic_focus_test' => array(
                'id' => 'tradepress_automatic_focus_test',
                'title' => __('Automatic Focus Test', 'tradepress'),
                'content' => __('This pointer appears automatically with focus overlay when the page loads. It demonstrates how automatic pointers can work with focus effects.', 'tradepress'),
                'target' => '#target-1',
                'position' => 'left',
                'page' => 'tradepress',
                'tab' => 'pointers',
                'status' => 'active',
                'priority' => 1,
                'category' => 'testing',
                'focus' => true
            )
        );
    }
    
    /**
     * Get pointers for a specific page/tab
     *
     * @param string $page Page slug
     * @param string $tab Tab slug (optional)
     * @return array Filtered array of pointers
     */
    public static function get_pointers_for_page($page, $tab = '') {
        $all_pointers = self::get_all_pointers();
        $filtered = array();
        
        foreach ($all_pointers as $pointer) {
            if ($pointer['page'] === $page) {
                if (empty($tab) || $pointer['tab'] === $tab) {
                    $filtered[] = $pointer;
                }
            }
        }
        
        // Sort by priority
        usort($filtered, function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        return $filtered;
    }
    
    /**
     * Get pointer status for current user
     *
     * @param string $pointer_id Pointer ID
     * @return bool True if dismissed, false if not
     */
    public static function is_pointer_dismissed($pointer_id) {
        $dismissed_pointers = explode(',', (string) get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true));
        return in_array($pointer_id, $dismissed_pointers);
    }
    
    /**
     * Get all pointers with their status for current user
     *
     * @return array Array of pointers with status
     */
    public static function get_all_pointers_with_status() {
        $pointers = self::get_all_pointers();
        
        foreach ($pointers as &$pointer) {
            $pointer['dismissed'] = self::is_pointer_dismissed($pointer['id']);
        }
        
        return $pointers;
    }
}