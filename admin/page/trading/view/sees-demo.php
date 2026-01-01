<?php
/**
 * TradePress Trading Area - SEES Demo Tab View
 *
 * Scoring Engine Execution System (SEES) - Demo Mode.
 * This tab will demonstrate the SEES functionality using hard-coded data
 * with randomization to simulate a live market effect. It's intended for
 * showcasing the system's capabilities without live API connections.
 *
 * @package TradePress\Admin\trading\Views
 * @version 1.0.0
 * @since   NEXT_VERSION_NUMBER
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Ensure data functions are available
if ( ! function_exists( 'tradepress_get_test_stock_symbols' ) || ! function_exists( 'tradepress_get_test_company_details' ) ) {
    $data_functions_file = TRADEPRESS_PLUGIN_DIR . 'includes/data/symbols-data.php';
    if ( file_exists( $data_functions_file ) ) {
        require_once $data_functions_file;
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'Error: Test data functions file is missing.', 'tradepress' ) . '</p></div>';
        return;
    }
}

$test_symbols = function_exists('tradepress_get_test_stock_symbols') ? tradepress_get_test_stock_symbols() : array();
$company_details_all = function_exists('tradepress_get_test_company_details') ? tradepress_get_test_company_details() : array();

// For the demo, let's pick a subset of symbols to make it manageable, e.g., first 20
$demo_symbols = array_slice($test_symbols, 0, 20);

// Determine current mode
$current_mode = 'Live'; // Default to Live
if (function_exists('is_demo_mode') && is_demo_mode()) {
    $current_mode = 'Demo';
} elseif (defined('TRADEPRESS_DEMO_MODE') && TRADEPRESS_DEMO_MODE) {
    $current_mode = 'Demo';
}
?>

<div class="wrap tradepress-sees-demo">
    <div class="tradepress-sees-layout">
        <!-- Left Column: Controls and Configuration -->
        <div class="tradepress-sees-left-column">
            <!-- Configuration Overview Box -->
            <div class="tradepress-sees-info-box">
                <h4><?php esc_html_e('Configuration Overview', 'tradepress'); ?></h4>
                <ul>
                    <li><strong><?php esc_html_e('Mode:', 'tradepress'); ?></strong> <?php echo esc_html($current_mode); ?></li>
                    <li><strong><?php esc_html_e('Strategy:', 'tradepress'); ?></strong> <?php esc_html_e('Default', 'tradepress'); ?></li>
                    <li><strong><?php esc_html_e('Asset Class:', 'tradepress'); ?></strong> <?php esc_html_e('Stocks', 'tradepress'); ?></li>
                    <li><strong><?php esc_html_e('Trade Horizon:', 'tradepress'); ?></strong> <?php esc_html_e('Day Trading', 'tradepress'); ?></li>
                </ul>
            </div>

            <!-- Demo Controls -->
            <div class="sees-demo-controls">
                <h4><?php esc_html_e('Controls', 'tradepress'); ?></h4>
                <button id="refresh-sees-data" class="button-primary"><?php esc_html_e( 'Refresh Data', 'tradepress' ); ?></button>
                <button id="tradepress-start-auto-refresh-sees-data" class="button button-secondary"><?php esc_html_e( 'Start Auto-Refresh', 'tradepress' ); ?></button>
                <button id="tradepress-stop-auto-refresh-sees-data" class="button button-secondary tradepress-hidden"><?php esc_html_e( 'Stop Auto-Refresh', 'tradepress' ); ?></button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=TradePress&tab=sees' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Settings', 'tradepress' ); ?></a>
            </div>
        </div>

        <!-- Right Column: Security Boxes -->
        <div class="tradepress-sees-right-column">
            <div id="sees-demo-container" class="sees-demo-container">
                <p class="loading-message"><?php esc_html_e( 'Loading SEES data...', 'tradepress' ); ?></p>
                <!-- Security boxes will be loaded here by JavaScript -->
            </div>
        </div>
    </div>
</div>
