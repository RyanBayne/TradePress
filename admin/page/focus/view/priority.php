<?php
/**
 * TradePress Focus - Priority Tab
 *
 * Highlights high priority matters related to portfolio stocks and open CFD positions.
 * This is where traders need to focus first and take action when required.
 *
 * @package TradePress/Admin/Focus
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tradepress-focus-priority">
    <div class="priority-sections">
        <!-- Portfolio Alerts -->
        <div class="priority-section portfolio-alerts">
            <h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e( 'Portfolio Alerts', 'tradepress' ); ?></h3>
            <div class="alert-items">
                <div class="alert-item high-priority">
                    <span class="alert-icon">🔴</span>
                    <div class="alert-content">
                        <strong>NVDA</strong> - Earnings announcement in 2 days
                        <span class="alert-action">Review position size</span>
                    </div>
                </div>
                <div class="alert-item medium-priority">
                    <span class="alert-icon">🟡</span>
                    <div class="alert-content">
                        <strong>TSLA</strong> - Approaching resistance at $250
                        <span class="alert-action">Consider profit taking</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open CFD Positions -->
        <div class="priority-section cfd-positions">
            <h3><span class="dashicons dashicons-chart-line"></span> <?php esc_html_e( 'Open CFD Positions', 'tradepress' ); ?></h3>
            <div class="cfd-items">
                <div class="cfd-item">
                    <div class="cfd-symbol">USA500</div>
                    <div class="cfd-details">
                        <span class="position-type long">Long</span>
                        <span class="entry-price">Entry: 5,420</span>
                        <span class="current-pnl positive">+$1,250</span>
                    </div>
                </div>
                <div class="cfd-item">
                    <div class="cfd-symbol">EUR/USD</div>
                    <div class="cfd-details">
                        <span class="position-type short">Short</span>
                        <span class="entry-price">Entry: 1.0850</span>
                        <span class="current-pnl negative">-$340</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Events Today -->
        <div class="priority-section market-events">
            <h3><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Market Events Today', 'tradepress' ); ?></h3>
            <div class="event-items">
                <div class="event-item">
                    <span class="event-time">09:30 EST</span>
                    <span class="event-description">Fed Chair Powell Speech</span>
                    <span class="event-impact high">High Impact</span>
                </div>
                <div class="event-item">
                    <span class="event-time">14:00 EST</span>
                    <span class="event-description">FOMC Meeting Minutes</span>
                    <span class="event-impact medium">Medium Impact</span>
                </div>
            </div>
        </div>

        <!-- Action Items -->
        <div class="priority-section action-items">
            <h3><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Action Items', 'tradepress' ); ?></h3>
            <div class="action-list">
                <div class="action-item">
                    <input type="checkbox" id="action1">
                    <label for="action1">Review NVDA position before earnings</label>
                </div>
                <div class="action-item">
                    <input type="checkbox" id="action2">
                    <label for="action2">Set stop-loss for EUR/USD position</label>
                </div>
                <div class="action-item">
                    <input type="checkbox" id="action3">
                    <label for="action3">Monitor Fed speech for market volatility</label>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tradepress-focus-priority {
    padding: 20px 0;
}

.priority-header {
    margin-bottom: 30px;
}

.priority-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.priority-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.priority-section h3 {
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #333;
}

.alert-item, .cfd-item, .event-item {
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    border-left: 4px solid #ddd;
}

.alert-item.high-priority {
    border-left-color: #dc3545;
    background: #fff5f5;
}

.alert-item.medium-priority {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.alert-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.alert-action {
    font-size: 12px;
    color: #666;
    font-style: italic;
}

.cfd-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left-color: #007cba;
    background: #f0f8ff;
}

.cfd-symbol {
    font-weight: bold;
    font-size: 16px;
}

.cfd-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    font-size: 12px;
}

.position-type.long {
    color: #28a745;
}

.position-type.short {
    color: #dc3545;
}

.current-pnl.positive {
    color: #28a745;
    font-weight: bold;
}

.current-pnl.negative {
    color: #dc3545;
    font-weight: bold;
}

.event-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left-color: #6f42c1;
    background: #f8f7ff;
}

.event-impact.high {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
}

.event-impact.medium {
    background: #ffc107;
    color: #333;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
}

.action-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 4px;
    background: #f8f9fa;
}

.action-item input[type="checkbox"] {
    margin: 0;
}
</style>