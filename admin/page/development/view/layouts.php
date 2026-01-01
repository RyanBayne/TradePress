<?php
/**
 * TradePress Development Layouts View
 *
 * @package TradePress/Admin/Views
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Admin_Development_Layouts Class
 */
class TradePress_Admin_Development_Layouts {
    
    /**
     * Output the layouts view
     */
    public static function output() {
        ?>
        <style>
        .layout-demo {
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .layout-demo h3 {
            margin-top: 0;
            color: #333;
        }
        .layout-container {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }
        .layout-column {
            background: white;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .layout-column h4 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #0073aa;
        }
        .two-column .layout-column {
            flex: 1;
        }
        .three-column .layout-column {
            flex: 1;
        }
        .stacked-right {
            flex-direction: column;
        }
        .stacked-right .layout-column {
            margin-bottom: 20px;
        }
        .stacked-right .layout-column:last-child {
            margin-bottom: 0;
        }
        .layout-left {
            flex: 2;
        }
        .layout-right {
            flex: 1;
        }
        </style>

        <div class="wrap">
            <h2><?php esc_html_e('Layout Examples', 'tradepress'); ?></h2>
            <p><?php esc_html_e('Reference layouts for two-column and three-column page structures with stacked containers.', 'tradepress'); ?></p>

            <!-- Two Column Layout -->
            <div class="layout-demo">
                <h3><?php esc_html_e('Two Column Layout', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Left column wider (2/3), right column narrower (1/3)', 'tradepress'); ?></p>
                
                <div class="layout-container two-column">
                    <div class="layout-column layout-left">
                        <h4><?php esc_html_e('Main Content Area', 'tradepress'); ?></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    </div>
                    <div class="layout-column layout-right">
                        <h4><?php esc_html_e('Sidebar Content', 'tradepress'); ?></h4>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                        <p>Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt.</p>
                    </div>
                </div>
            </div>

            <!-- Two Column with Stacked Right -->
            <div class="layout-demo">
                <h3><?php esc_html_e('Two Column with Stacked Right Containers', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Left column for main content, right column with multiple stacked containers', 'tradepress'); ?></p>
                
                <div class="layout-container two-column">
                    <div class="layout-column layout-left">
                        <h4><?php esc_html_e('Main Configuration Form', 'tradepress'); ?></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis.</p>
                    </div>
                    <div class="layout-right">
                        <div class="stacked-right">
                            <div class="layout-column">
                                <h4><?php esc_html_e('Testing Section', 'tradepress'); ?></h4>
                                <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti.</p>
                                <p>Quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
                            </div>
                            <div class="layout-column">
                                <h4><?php esc_html_e('Symbols Section', 'tradepress'); ?></h4>
                                <p>Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga.</p>
                                <p>Et harum quidem rerum facilis est et expedita distinctio nam libero tempore.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Three Column Layout -->
            <div class="layout-demo">
                <h3><?php esc_html_e('Three Column Layout', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Equal width columns (1/3 each)', 'tradepress'); ?></p>
                
                <div class="layout-container three-column">
                    <div class="layout-column">
                        <h4><?php esc_html_e('Left Column', 'tradepress'); ?></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    </div>
                    <div class="layout-column">
                        <h4><?php esc_html_e('Center Column', 'tradepress'); ?></h4>
                        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
                        <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                    </div>
                    <div class="layout-column">
                        <h4><?php esc_html_e('Right Column', 'tradepress'); ?></h4>
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                        <p>Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt.</p>
                    </div>
                </div>
            </div>

            <!-- Code Examples -->
            <div class="layout-demo">
                <h3><?php esc_html_e('CSS Structure', 'tradepress'); ?></h3>
                <p><?php esc_html_e('Key CSS classes for implementing these layouts:', 'tradepress'); ?></p>
                
                <pre style="background: #f1f1f1; padding: 15px; border-radius: 4px; overflow-x: auto;">
<code>.layout-container {
    display: flex;
    gap: 20px;
}

/* Two Column */
.layout-left {
    flex: 2; /* Takes 2/3 of space */
}
.layout-right {
    flex: 1; /* Takes 1/3 of space */
}

/* Stacked Right Containers */
.stacked-right {
    flex-direction: column;
}
.stacked-right .layout-column {
    margin-bottom: 20px;
}

/* Three Column Equal */
.three-column .layout-column {
    flex: 1; /* Equal width */
}</code></pre>
            </div>
        </div>
        <?php
    }
}