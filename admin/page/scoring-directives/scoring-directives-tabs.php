<?php
/**
 * TradePress - Scoring Directives Tabs Controller
 * 
 * Configure and manage the scoring directives that power TradePress automated analysis and ranking system
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress_Admin_Scoring_Directives_Page Class
 */
class TradePress_Admin_Scoring_Directives_Page {
    
    /**
     * Current tab
     * @var string
     */
    private $current_tab;
    
    /**
     * Available tabs
     * @var array
     */
    private $tabs;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
        $this->setup_tabs();
    }
    
    /**
     * Setup available tabs
     */
    private function setup_tabs() {
        $this->tabs = array(
            'overview' => array(
                'title' => __('Overview', 'tradepress'),
                'description' => __('View and manage all scoring directives', 'tradepress'),
                'file' => 'view/sd-overview.php'
            ),
            'configure_directives' => array(
                'title' => __('Configure Directives', 'tradepress'),
                'description' => __('Clean directive configuration interface', 'tradepress'),
                'file' => 'view/configure-directives.php'
            ),
            'directives_status' => array(
                'title' => __('Directive Development Status', 'tradepress'),
                'description' => __('View the development status of scoring directives.', 'tradepress'),
                'file' => 'view/directives-status.php'
            ),
            'algorithm' => array(
                'title' => __('Algorithm', 'tradepress'),
                'description' => __('Configure the scoring algorithm and weights', 'tradepress'),
                'file' => 'view/algorithm.php'
            ),

            'create_strategies' => array(
                'title' => __('Create Scoring Strategies', 'tradepress'),
                'description' => __('Build new scoring strategies with drag & drop interface', 'tradepress'),
                'file' => 'view/create-strategies.php'
            ),
            'manage_strategies' => array(
                'title' => __('Manage Scoring Strategies', 'tradepress'),
                'description' => __('Edit and configure existing scoring strategies', 'tradepress'),
                'file' => 'view/manage-strategies.php'
            ),
            'view_strategies' => array(
                'title' => __('View Scoring Strategies', 'tradepress'),
                'description' => __('View strategy performance and test results', 'tradepress'),
                'file' => 'view/view-strategies.php'
            ),
            'logs' => array(
                'title' => __('Scoring Activity Logs', 'tradepress'),
                'description' => __('View scoring activity and debugging information', 'tradepress'),
                'file' => 'view/logs.php'
            )
        );
        
        // Add conditional tabs
        if (get_option('tradepress_developer_mode', false)) {
            $this->tabs['developer'] = array(
                'title' => __('Developer Mode', 'tradepress'),
                'description' => __('Developer tools and debugging features', 'tradepress'),
                'file' => 'view/developer.php'
            );
        }
        
        if (function_exists('is_demo_mode') && is_demo_mode()) {
            $this->tabs['demo'] = array(
                'title' => __('Demo Mode', 'tradepress'),
                'description' => __('Demo mode controls and settings', 'tradepress'),
                'file' => 'view/demo.php'
            );
        }
    }
    
    /**
     * Output the page
     */
    public function output() {
        ?>
        <div class="wrap tradepress-scoring-directives-wrap">
            <h1>
                <?php 
                echo esc_html__('TradePress Scoring Directives', 'tradepress');
                if (isset($this->tabs[$this->current_tab])) {
                    echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                    echo esc_html($this->tabs[$this->current_tab]['title']);
                }
                ?>
            </h1>
            
            <?php $this->render_demo_notice(); ?>
            <?php $this->render_tab_navigation(); ?>
            <?php $this->render_current_tab(); ?>
        </div>
        <?php
    }
    
    /**
     * Render demo notice
     */
    private function render_demo_notice() {
        if (function_exists('is_demo_mode') && is_demo_mode()) {
            ?>
            <div class="notice notice-info">
                <p>
                    <span class="dashicons dashicons-admin-tools"></span>
                    <strong><?php esc_html_e('Demo Mode Active:', 'tradepress'); ?></strong>
                    <?php esc_html_e('All scoring directives are in demonstration mode. Changes will not affect live trading data.', 'tradepress'); ?>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Render tab navigation
     */
    private function render_tab_navigation() {
        ?>
        <nav class="nav-tab-wrapper">
            <?php foreach ($this->tabs as $tab_key => $tab_data): ?>
                <a href="<?php echo esc_url($this->get_tab_url($tab_key)); ?>" 
                   class="nav-tab <?php echo $this->current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab_data['title']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php
    }
    
    /**
     * Render current tab content
     */
    private function render_current_tab() {
        if (!isset($this->tabs[$this->current_tab])) {
            $this->current_tab = 'overview';
        }
        
        $tab_file = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/scoring-directives/' . $this->tabs[$this->current_tab]['file'];
        
        if (file_exists($tab_file)) {
            include $tab_file;
        } else {
            $this->render_tab_not_found();
        }
    }
    
    /**
     * Render tab not found message
     */
    private function render_tab_not_found() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php esc_html_e('Tab content file not found:', 'tradepress'); ?>
                <code><?php echo esc_html($this->tabs[$this->current_tab]['file']); ?></code>
            </p>
        </div>
        <?php
    }
    
    /**
     * Get tab URL
     */
    private function get_tab_url($tab_key) {
        return admin_url('admin.php?page=tradepress_scoring_directives&tab=' . $tab_key);
    }
}