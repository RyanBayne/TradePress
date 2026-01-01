<?php
/**
 * TradePress Mode Indicators
 *
 * Displays developer mode and demo mode indicators in admin
 *
 * @package TradePress/Admin/Config
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress_Mode_Indicators Class
 */
class TradePress_Mode_Indicators {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_head', array($this, 'add_mode_indicators'));
        add_action('admin_footer', array($this, 'add_demo_toggle_script'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_mode_indicator_styles'));
    }

    /**
     * Add mode indicators to admin pages
     */
    public function add_mode_indicators() {
        if (!$this->is_developer_mode()) {
            return;
        }

        // Only show on TradePress plugin pages
        if (!$this->is_tradepress_page()) {
            return;
        }

        $demo_mode = $this->is_demo_mode();
        ?>
        <div id="tradepress-mode-indicators" class="screen-meta-links">
            <div class="tradepress-mode-indicator-wrap">
                <button type="button" id="tradepress-developer-indicator" class="button show-settings" aria-expanded="false">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <span class="screen-reader-text"><?php _e('Developer Mode Active', 'tradepress'); ?></span>
                    Developer Mode
                </button>
            </div>
            <div class="tradepress-demo-indicator-wrap">
                <button type="button" id="tradepress-demo-indicator" class="button show-settings <?php echo $demo_mode ? 'demo-on' : 'demo-off'; ?>" aria-expanded="false" data-demo-mode="<?php echo $demo_mode ? '1' : '0'; ?>">
                    <span class="dashicons <?php echo $demo_mode ? 'dashicons-yes-alt' : 'dashicons-dismiss'; ?>"></span>
                    <span class="screen-reader-text"><?php echo $demo_mode ? __('Demo Mode On', 'tradepress') : __('Demo Mode Off', 'tradepress'); ?></span>
                    Demo: <?php echo $demo_mode ? 'ON' : 'OFF'; ?>
                </button>
            </div>
        </div>
        <?php
    }



    /**
     * Check if developer mode is active
     */
    private function is_developer_mode() {
        $developer_mode = get_option('tradepress_developer_mode', false);
        return ($developer_mode === true || $developer_mode === 1 || $developer_mode === '1' || $developer_mode === 'yes');
    }

    /**
     * Check if demo mode is active
     */
    private function is_demo_mode() {
        $demo_mode = get_option('tradepress_demo_mode', 'yes');
        return ($demo_mode === true || $demo_mode === 1 || $demo_mode === '1' || $demo_mode === 'yes');
    }

    /**
     * Check if current page is a TradePress plugin page
     */
    private function is_tradepress_page() {
        // Use TradePress screen IDs function if available
        if (function_exists('TradePress_get_screen_ids')) {
            $screen = get_current_screen();
            if ($screen && in_array($screen->id, TradePress_get_screen_ids())) {
                return true;
            }
        }

        // Simplified check: any page parameter containing 'tradepress'
        if (isset($_GET['page'])) {
            return strpos(strtolower($_GET['page']), 'tradepress') !== false;
        }

        return false;
    }



    /**
     * Add JavaScript for demo mode toggle
     */
    public function add_demo_toggle_script() {
        if (!$this->is_developer_mode()) {
            return;
        }

        // Only show on TradePress plugin pages
        if (!$this->is_tradepress_page()) {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#tradepress-demo-indicator').on('click', function(e) {
                e.preventDefault();
                
                var button = $(this);
                var originalText = button.html();
                
                // Show loading state
                button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt" style="animation: rotation 1s infinite linear;"></span> Toggling...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tradepress_demo_mode_toggle',
                        nonce: '<?php echo wp_create_nonce('tradepress_demo_mode_toggle'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Refresh the page to update all indicators
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data || 'Unknown error'));
                            button.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        alert('Error: Failed to toggle demo mode');
                        button.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
        </script>

        <?php
    }

    /**
     * Enqueue mode indicator styles
     */
    public function enqueue_mode_indicator_styles() {
        if (!$this->is_tradepress_page()) {
            return;
        }

        wp_enqueue_style(
            'tradepress-mode-indicators',
            TRADEPRESS_PLUGIN_URL . 'assets/css/components/mode-indicators.css',
            array(),
            TRADEPRESS_VERSION
        );
    }
}

// Initialize the mode indicators
new TradePress_Mode_Indicators();