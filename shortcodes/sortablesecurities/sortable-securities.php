<?php
/**
 * TradePress Sortable Securities V1 Shortcode Class
 *
 * Built from scratch using the Charlie approach with minimal WordPress dependencies.
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TradePress
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('TradePress_Sortable_Securities')) :

    /**
     * TradePress_Sortable_Securities Class.
     */
    class TradePress_Sortable_Securities
    {
        /**
         * Constructor.
         */
        public function __construct()
        {   
            // MAJOR CHANGE: We're removing AJAX completely for the demo version
            // This matches Charlie's prototype approach where demo data is generated client-side
        }

        /**
         * Main output method for the shortcode.
         *
         * @return string The HTML output.
         */
        public function output()
        {
            // Enqueue required scripts and styles
            wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
            wp_enqueue_style('tradepress-sortable-style', plugins_url('sortables-style.css', __FILE__));
            wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_script('tradepress-sortable-script', plugins_url('ajaxloop.js', __FILE__), array('jquery', 'jquery-ui-js'), null, true);

            // MAJOR CHANGE: No need for wp_localize_script anymore since we're generating data client-side

            // Get initial securities data
            $initial_items = $this->get_initial_securities_data();

            // Build the HTML output (based on Charlie's approach)
            $output = '';
            
            $output .= '<div class="container">';
            $output .= '<h2>Auto-Reordering Securities List V1</h2>';
            $output .= '<p>This list will automatically reorder based on simulated scores fetched via AJAX.</p>';

            $output .= '<div class="controls">';
            $output .= '<button id="start-polling">Start Auto-Reorder</button>';
            $output .= '<button id="stop-polling">Stop Auto-Reorder</button>';
            $output .= '</div>';

            $output .= '<div id="status">Service stopped.</div>';

            $output .= '<div id="sortable-list">';
            
            // Output initial items
            foreach ($initial_items as $item) {
                $output .= '<div class="sortable-item" data-id="' . esc_attr($item['id']) . '" data-score="' . esc_attr($item['current_score']) . '">';
                $output .= '<span class="item-name">' . esc_html($item['name']) . '</span>';
                $output .= '<span class="item-score">Score: ' . esc_html($item['current_score']) . '</span>';
                $output .= '</div>';
            }
            
            $output .= '</div>'; // Close sortable-list
            $output .= '</div>'; // Close container

            return $output;
        }

        /**
         * Get initial securities data.
         * 
         * @return array Array of securities with basic data
         */
        private function get_initial_securities_data()
        {
            return array(
                array('id' => 'item-1', 'name' => 'Alpha Stock', 'current_score' => rand(70, 100)),
                array('id' => 'item-2', 'name' => 'Beta Security', 'current_score' => rand(60, 95)),
                array('id' => 'item-3', 'name' => 'Gamma Asset', 'current_score' => rand(50, 90)),
                array('id' => 'item-4', 'name' => 'Delta Holding', 'current_score' => rand(40, 85)),
                array('id' => 'item-5', 'name' => 'Epsilon Fund', 'current_score' => rand(30, 80)),
            );
        }

        // MAJOR CHANGE: Removed all AJAX handlers - they're not needed for this demo approach
    }

endif;
