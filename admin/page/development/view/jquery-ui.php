<?php
/**
 * TradePress Development jQuery UI Demo View
 *
 * @package TradePress/Admin/Views/Development
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Development_jQuery_UI Class
 */
class TradePress_Admin_Development_jQuery_UI {
    
    /**
     * Output the jQuery UI demos
     */
    public static function output() {
        self::enqueue_assets();
        ?>
        <div class="tradepress-jquery-ui-demos">
            <h2><?php esc_html_e('jQuery UI Demos', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Working examples of jQuery UI components using WordPress bundled libraries.', 'tradepress'); ?></p>
            
            <!-- Demo Navigation -->
            <div id="demo-tabs">
                <ul>
                    <li><a href="#accordion-demo"><?php esc_html_e('Accordion', 'tradepress'); ?></a></li>
                    <li><a href="#tabs-demo"><?php esc_html_e('Tabs', 'tradepress'); ?></a></li>
                    <li><a href="#dialog-demo"><?php esc_html_e('Dialog', 'tradepress'); ?></a></li>
                    <li><a href="#datepicker-demo"><?php esc_html_e('Datepicker', 'tradepress'); ?></a></li>
                    <li><a href="#sortable-demo"><?php esc_html_e('Sortable', 'tradepress'); ?></a></li>
                    <li><a href="#draggable-demo"><?php esc_html_e('Draggable', 'tradepress'); ?></a></li>
                    <li><a href="#droppable-demo"><?php esc_html_e('Droppable', 'tradepress'); ?></a></li>
                    <li><a href="#resizable-demo"><?php esc_html_e('Resizable', 'tradepress'); ?></a></li>
                    <li><a href="#progressbar-demo"><?php esc_html_e('Progressbar', 'tradepress'); ?></a></li>
                    <li><a href="#slider-demo"><?php esc_html_e('Slider', 'tradepress'); ?></a></li>
                    <li><a href="#spinner-demo"><?php esc_html_e('Spinner', 'tradepress'); ?></a></li>
                    <li><a href="#autocomplete-demo"><?php esc_html_e('Autocomplete', 'tradepress'); ?></a></li>
                </ul>
                
                <!-- Accordion Demo -->
                <div id="accordion-demo">
                    <h3><?php esc_html_e('Accordion Widget Demo', 'tradepress'); ?></h3>
                    <div id="demo-accordion">
                        <h3><?php esc_html_e('Section 1', 'tradepress'); ?></h3>
                        <div>
                            <p><?php esc_html_e('Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.', 'tradepress'); ?></p>
                        </div>
                        <h3><?php esc_html_e('Section 2', 'tradepress'); ?></h3>
                        <div>
                            <p><?php esc_html_e('Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor velit, faucibus interdum tellus libero ac justo.', 'tradepress'); ?></p>
                        </div>
                        <h3><?php esc_html_e('Section 3', 'tradepress'); ?></h3>
                        <div>
                            <p><?php esc_html_e('Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis. Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero ac tellus pellentesque semper.', 'tradepress'); ?></p>
                            <ul>
                                <li><?php esc_html_e('List item one', 'tradepress'); ?></li>
                                <li><?php esc_html_e('List item two', 'tradepress'); ?></li>
                                <li><?php esc_html_e('List item three', 'tradepress'); ?></li>
                            </ul>
                        </div>
                        <h3><?php esc_html_e('Section 4', 'tradepress'); ?></h3>
                        <div>
                            <p><?php esc_html_e('Cras dictum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia mauris vel est.', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs Demo -->
                <div id="tabs-demo">
                    <h3><?php esc_html_e('Tabs Widget Demo', 'tradepress'); ?></h3>
                    <div id="demo-tabs-widget">
                        <ul>
                            <li><a href="#tabs-1"><?php esc_html_e('Tab 1', 'tradepress'); ?></a></li>
                            <li><a href="#tabs-2"><?php esc_html_e('Tab 2', 'tradepress'); ?></a></li>
                            <li><a href="#tabs-3"><?php esc_html_e('Tab 3', 'tradepress'); ?></a></li>
                        </ul>
                        <div id="tabs-1">
                            <p><?php esc_html_e('Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante.', 'tradepress'); ?></p>
                        </div>
                        <div id="tabs-2">
                            <p><?php esc_html_e('Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis.', 'tradepress'); ?></p>
                        </div>
                        <div id="tabs-3">
                            <p><?php esc_html_e('Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem.', 'tradepress'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Dialog Demo -->
                <div id="dialog-demo">
                    <h3><?php esc_html_e('Dialog Widget Demo', 'tradepress'); ?></h3>
                    <button id="dialog-opener"><?php esc_html_e('Open Dialog', 'tradepress'); ?></button>
                    <div id="demo-dialog" title="Basic dialog">
                        <p><?php esc_html_e('This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the \'x\' icon.', 'tradepress'); ?></p>
                    </div>
                </div>
                
                <!-- Datepicker Demo -->
                <div id="datepicker-demo">
                    <h3><?php esc_html_e('Datepicker Widget Demo', 'tradepress'); ?></h3>
                    <p><?php esc_html_e('Date:', 'tradepress'); ?> <input type="text" id="demo-datepicker"></p>
                </div>
                
                <!-- Sortable Demo -->
                <div id="sortable-demo">
                    <h3><?php esc_html_e('Sortable Interaction Demo', 'tradepress'); ?></h3>
                    <ul id="demo-sortable">
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 1', 'tradepress'); ?></li>
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 2', 'tradepress'); ?></li>
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 3', 'tradepress'); ?></li>
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 4', 'tradepress'); ?></li>
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 5', 'tradepress'); ?></li>
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 6', 'tradepress'); ?></li>
                        <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php esc_html_e('Item 7', 'tradepress'); ?></li>
                    </ul>
                </div>
                
                <!-- Draggable Demo -->
                <div id="draggable-demo">
                    <h3><?php esc_html_e('Draggable Interaction Demo', 'tradepress'); ?></h3>
                    <div id="demo-draggable" class="ui-widget-content">
                        <p><?php esc_html_e('Drag me around', 'tradepress'); ?></p>
                    </div>
                </div>
                
                <!-- Droppable Demo -->
                <div id="droppable-demo">
                    <h3><?php esc_html_e('Droppable Interaction Demo', 'tradepress'); ?></h3>
                    <div id="demo-draggable2" class="ui-widget-content">
                        <p><?php esc_html_e('Drag me to my target', 'tradepress'); ?></p>
                    </div>
                    <div id="demo-droppable" class="ui-widget-header">
                        <p><?php esc_html_e('Drop here', 'tradepress'); ?></p>
                    </div>
                </div>
                
                <!-- Resizable Demo -->
                <div id="resizable-demo">
                    <h3><?php esc_html_e('Resizable Interaction Demo', 'tradepress'); ?></h3>
                    <div id="demo-resizable" class="ui-widget-content">
                        <h3 class="ui-widget-header"><?php esc_html_e('Resizable', 'tradepress'); ?></h3>
                        <p><?php esc_html_e('Resize me by dragging my edges or corners.', 'tradepress'); ?></p>
                    </div>
                </div>
                
                <!-- Progressbar Demo -->
                <div id="progressbar-demo">
                    <h3><?php esc_html_e('Progressbar Widget Demo', 'tradepress'); ?></h3>
                    <div id="demo-progressbar"></div>
                    <button id="progressbar-start"><?php esc_html_e('Start Progress', 'tradepress'); ?></button>
                </div>
                
                <!-- Slider Demo -->
                <div id="slider-demo">
                    <h3><?php esc_html_e('Slider Widget Demo', 'tradepress'); ?></h3>
                    <div id="demo-slider"></div>
                    <p><?php esc_html_e('Value:', 'tradepress'); ?> <span id="slider-value">50</span></p>
                </div>
                
                <!-- Spinner Demo -->
                <div id="spinner-demo">
                    <h3><?php esc_html_e('Spinner Widget Demo', 'tradepress'); ?></h3>
                    <label for="demo-spinner"><?php esc_html_e('Select a value:', 'tradepress'); ?></label>
                    <input id="demo-spinner" name="value">
                </div>
                
                <!-- Autocomplete Demo -->
                <div id="autocomplete-demo">
                    <h3><?php esc_html_e('Autocomplete Widget Demo', 'tradepress'); ?></h3>
                    <div class="ui-widget">
                        <label for="demo-autocomplete"><?php esc_html_e('Tags:', 'tradepress'); ?> </label>
                        <input id="demo-autocomplete">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Styles moved to assets/css/pages/jquery-ui.css -->
        
        <?php
        self::enqueue_assets();
    }
    
    /**
     * Enqueue required assets
     */
    private static function enqueue_assets() {
        // Enqueue WordPress bundled jQuery UI components
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-resizable');
        wp_enqueue_script('jquery-ui-progressbar');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-spinner');
        wp_enqueue_script('jquery-ui-autocomplete');
        
        // Enqueue jQuery UI CSS
        wp_enqueue_style('wp-jquery-ui-dialog');
    }
}
