<?php
/**
 * TradePress UI Library - Notice Components
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.1.0
 */

defined('ABSPATH') || exit;
?>

<div class="tradepress-ui-section" data-section="notice-components">
    <div class="tradepress-card">
        <div class="tradepress-card-header">
            <h3><?php esc_html_e('Notice Components', 'tradepress'); ?></h3>
        </div>
        
        <div class="tradepress-card-body">
            
            <!-- WordPress Standard Notices -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('WordPress Standard Notices', 'tradepress'); ?></h5>
            
                <?php TradePress_Admin_Notices::warning('API Limit', 'You have used 80% of your monthly API quota.', true); ?>
                <?php TradePress_Admin_Notices::error('Connection Error', 'Unable to connect to trading platform API.', true); ?>
                
                <div class="notice notice-success">
                    <p><strong>Portfolio Updated:</strong> Added 50 shares of AAPL to your portfolio.</p>
                </div>
            </section>

            <!-- Demo Mode Indicators -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('Demo Mode Indicators', 'tradepress'); ?></h5>
                
                <?php TradePress_Admin_Notices::simple_demo_indicator(); ?>
                
                <?php TradePress_Admin_Notices::simple_demo_indicator(
                    'Feature Testing Mode', 
                    'This feature is in beta testing with limited functionality.',
                    array('Real-time data may be delayed', 'Some features are disabled')
                ); ?>
                
                <?php TradePress_Admin_Notices::development_progress_notice(
                    'Advanced Charting',
                    array('Basic charts implemented', 'Technical indicators in progress', 'Custom timeframes coming soon')
                ); ?>
                
                <?php TradePress_Admin_Notices::demo_mode_indicator(); ?>
            </section>

            <!-- Custom Notice Boxes -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('Custom Notice Boxes', 'tradepress'); ?></h5>
                
                <div class="tradepress-notice-wrapper">
                    <?php 
                    $notices = new TradePress_Admin_Notices();
                    echo $notices->info_area('Trading Tip', 'Diversification reduces risk. Consider spreading investments across different sectors.');
                    ?>
                </div>
                
                <div class="tradepress-notice-wrapper">
                    <?php 
                    $notices->progress_box(
                        __( 'Sync Progress', 'tradepress' ),
                        __( 'Synchronizing portfolio data', 'tradepress' ),
                        array('Positions' => 85, 'Orders' => 60, 'History' => 30)
                    );
                    ?>
                </div>
            </section>

            <!-- Notice Files from /notices folder -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('System Notices', 'tradepress'); ?></h5>
                <p><?php __('You will find this notice at the top of the page.', 'tradepress'); ?></p>
                
                <!-- Custom Notice -->
                <div id="message" class="updated TradePress-message">
                    <p><strong>API Connected:</strong> Successfully connected to Alpha Vantage API. Real-time data is now available.</p>
                </div>
                
            </section>

            <!-- Inline Alert Patterns -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('Inline Alert Patterns', 'tradepress'); ?></h5>
                <p><?php __('You will find this notice at the top of the page.', 'tradepress'); ?></p>
                
                <div class="notice notice-info">
                    <p><strong>Market Alert:</strong> High volatility detected in <code>TSLA</code>, <code>NVDA</code>. 
                    <a href="#" class="button button-small">View Details</a></p>
                </div>
                
                <div class="notice notice-warning">
                    <p><strong>Stop Loss Triggered:</strong> Position in MSFT closed at $330.25. 
                    <a href="#">View Transaction</a></p>
                </div>
                
            </section>

            <!-- Status Messages -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('Status Messages', 'tradepress'); ?></h5>
                
                <div class="status-message connecting">
                    <span class="dashicons dashicons-update spin"></span>
                    Connecting to trading platform...
                </div>
                
                <div class="status-message success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    Connected to Alpaca Trading
                </div>
                
                <div class="status-message info">
                    <span class="dashicons dashicons-info"></span>
                    Market data delayed by 15 minutes
                </div>
            </section>

            <!-- Compact Guidelines -->
            <section class="notice-demo-group">
                <h5><?php esc_html_e('Usage Guidelines', 'tradepress'); ?></h5>
                <div class="tradepress-notice-wrapper">
                    <?php echo $notices->info_area('', 
                        '<strong>Best Practices:</strong> Use specific titles • Provide clear actions • Make dismissible when appropriate • Test accessibility'
                    ); ?>
                </div>
            </section>
        </div>
    </div>
</div>