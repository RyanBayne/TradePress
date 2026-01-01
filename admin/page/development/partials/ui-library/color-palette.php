<?php
/**
 * UI Library Color Palette Partial
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<div class="tradepress-ui-section">
    <h3><?php esc_html_e('Color Palette', 'tradepress'); ?></h3>
    <p><?php esc_html_e('The TradePress color system uses CSS custom properties for consistent theming.', 'tradepress'); ?></p>

    <!-- Primary Colors -->
    <div class="tradepress-color-group">
        <h4><?php esc_html_e('Primary Colors', 'tradepress'); ?></h4>
        <div class="tradepress-color-grid">
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Primary', '#2271b1', '--tradepress-color-primary')">
                <div class="tradepress-color-swatch tradepress-color-primary"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Primary</span>
                    <span class="tradepress-color-value">#2271b1</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Primary Dark', '#135e96', '--tradepress-color-primary-dark')">
                <div class="tradepress-color-swatch tradepress-color-primary-dark"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Primary Dark</span>
                    <span class="tradepress-color-value">#135e96</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Primary Light', '#72aee6', '--tradepress-color-primary-light')">
                <div class="tradepress-color-swatch tradepress-color-primary-light"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Primary Light</span>
                    <span class="tradepress-color-value">#72aee6</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Colors -->
    <div class="tradepress-color-group">
        <h4><?php esc_html_e('Status Colors', 'tradepress'); ?></h4>
        <div class="tradepress-color-grid">
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Success', '#00a32a', '--tradepress-color-success')">
                <div class="tradepress-color-swatch tradepress-color-success"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Success</span>
                    <span class="tradepress-color-value">#00a32a</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Warning', '#dba617', '--tradepress-color-warning')">
                <div class="tradepress-color-swatch tradepress-color-warning"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Warning</span>
                    <span class="tradepress-color-value">#dba617</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Error', '#d63638', '--tradepress-color-error')">
                <div class="tradepress-color-swatch tradepress-color-error"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Error</span>
                    <span class="tradepress-color-value">#d63638</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Neutral Colors -->
    <div class="tradepress-color-group">
        <h4><?php esc_html_e('Neutral Colors', 'tradepress'); ?></h4>
        <div class="tradepress-color-grid">
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'White', '#ffffff', '--tradepress-color-white')">
                <div class="tradepress-color-swatch tradepress-color-white"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">White</span>
                    <span class="tradepress-color-value">#ffffff</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Gray 100', '#f0f0f1', '--tradepress-color-gray-100')">
                <div class="tradepress-color-swatch tradepress-color-gray-100"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Gray 100</span>
                    <span class="tradepress-color-value">#f0f0f1</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Gray 300', '#dcdcde', '--tradepress-color-gray-300')">
                <div class="tradepress-color-swatch tradepress-color-gray-300"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Gray 300</span>
                    <span class="tradepress-color-value">#dcdcde</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Gray 500', '#a7aaad', '--tradepress-color-gray-500')">
                <div class="tradepress-color-swatch tradepress-color-gray-500"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Gray 500</span>
                    <span class="tradepress-color-value">#a7aaad</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Gray 700', '#646970', '--tradepress-color-gray-700')">
                <div class="tradepress-color-swatch tradepress-color-gray-700"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Gray 700</span>
                    <span class="tradepress-color-value">#646970</span>
                </div>
            </div>
            <div class="tradepress-color-item" onclick="TradePressUILibrary.showColorInfo(this, 'Gray 900', '#1d2327', '--tradepress-color-gray-900')">
                <div class="tradepress-color-swatch tradepress-color-gray-900"></div>
                <div class="tradepress-color-info">
                    <span class="tradepress-color-name">Gray 900</span>
                    <span class="tradepress-color-value">#1d2327</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Color Information Display -->
    <div id="tradepress-color-info-display" class="tradepress-color-info-panel" style="display: none;">
        <h4><?php esc_html_e('Color Information', 'tradepress'); ?></h4>
        <div id="tradepress-color-details"></div>
    </div>
</div>
