<?php
/**
 * TradePress Focus - Daily Tab
 *
 * Daily news and updates for the last 24 hours applicable to portfolio and watchlist stocks.
 * Includes opportunity scanner and AI integration for advice and suggestions.
 *
 * @package TradePress/Admin/Focus
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tradepress-focus-daily">
    <div class="daily-header">
        <div class="daily-controls">
            <button class="button button-primary refresh-daily"><?php esc_html_e( 'Refresh Data', 'tradepress' ); ?></button>
            <span class="last-updated">Last updated: <?php echo current_time( 'M j, Y g:i A' ); ?></span>
        </div>
    </div>

    <div class="daily-sections">
        <!-- Portfolio News -->
        <div class="daily-section portfolio-news">
            <h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e( 'Portfolio Stock News', 'tradepress' ); ?></h3>
            <div class="news-items">
                <div class="news-item">
                    <div class="news-symbol">NVDA</div>
                    <div class="news-content">
                        <h4>NVIDIA Reports Strong Q3 Earnings Beat</h4>
                        <p>Revenue up 206% year-over-year driven by data center demand...</p>
                        <span class="news-time">2 hours ago</span>
                        <span class="news-sentiment positive">Bullish</span>
                    </div>
                </div>
                <div class="news-item">
                    <div class="news-symbol">TSLA</div>
                    <div class="news-content">
                        <h4>Tesla Cybertruck Production Ramp Update</h4>
                        <p>Company announces accelerated production timeline for 2024...</p>
                        <span class="news-time">4 hours ago</span>
                        <span class="news-sentiment neutral">Neutral</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opportunity Scanner -->
        <div class="daily-section opportunity-scanner">
            <h3><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Opportunity Scanner', 'tradepress' ); ?></h3>
            <div class="scanner-controls">
                <label for="opportunity-count"><?php esc_html_e( 'Show top', 'tradepress' ); ?></label>
                <select id="opportunity-count">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
                <span><?php esc_html_e( 'opportunities', 'tradepress' ); ?></span>
            </div>
            <div class="opportunity-items">
                <div class="opportunity-item">
                    <div class="opp-symbol">RBLX</div>
                    <div class="opp-details">
                        <span class="opp-reason">Oversold bounce potential</span>
                        <span class="opp-score">Score: 78</span>
                        <span class="opp-change">+5.2%</span>
                    </div>
                </div>
                <div class="opportunity-item">
                    <div class="opp-symbol">PLTR</div>
                    <div class="opp-details">
                        <span class="opp-reason">Breakout above resistance</span>
                        <span class="opp-score">Score: 82</span>
                        <span class="opp-change">+3.8%</span>
                    </div>
                </div>
                <div class="opportunity-item">
                    <div class="opp-symbol">IONQ</div>
                    <div class="opp-details">
                        <span class="opp-reason">Quantum computing momentum</span>
                        <span class="opp-score">Score: 75</span>
                        <span class="opp-change">+7.1%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Movers -->
        <div class="daily-section market-movers">
            <h3><span class="dashicons dashicons-chart-line"></span> <?php esc_html_e( 'Your Stocks - Market Movers', 'tradepress' ); ?></h3>
            <div class="movers-tabs">
                <button class="mover-tab active" data-tab="gainers"><?php esc_html_e( 'Top Gainers', 'tradepress' ); ?></button>
                <button class="mover-tab" data-tab="losers"><?php esc_html_e( 'Top Losers', 'tradepress' ); ?></button>
            </div>
            <div class="movers-content">
                <div class="mover-list" id="gainers">
                    <div class="mover-item">
                        <span class="mover-symbol">NVDA</span>
                        <span class="mover-price">$875.20</span>
                        <span class="mover-change positive">+8.5%</span>
                    </div>
                    <div class="mover-item">
                        <span class="mover-symbol">IONQ</span>
                        <span class="mover-price">$24.80</span>
                        <span class="mover-change positive">+7.1%</span>
                    </div>
                </div>
                <div class="mover-list" id="losers" style="display: none;">
                    <div class="mover-item">
                        <span class="mover-symbol">LCID</span>
                        <span class="mover-price">$3.45</span>
                        <span class="mover-change negative">-4.2%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Insights (Placeholder) -->
        <div class="daily-section ai-insights">
            <h3><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'AI Market Insights', 'tradepress' ); ?></h3>
            <div class="ai-content">
                <div class="ai-message">
                    <p><strong><?php esc_html_e( 'Market Summary:', 'tradepress' ); ?></strong> Tech stocks showing strength with NVIDIA leading gains on earnings beat. Consider taking profits on semiconductor positions near resistance levels.</p>
                </div>
                <div class="ai-suggestions">
                    <h4><?php esc_html_e( 'Suggested Actions:', 'tradepress' ); ?></h4>
                    <ul>
                        <li>Monitor NVDA for profit-taking opportunities above $880</li>
                        <li>RBLX showing oversold bounce potential - consider entry</li>
                        <li>Fed speech today may impact tech sector volatility</li>
                    </ul>
                </div>
                <div class="ai-status">
                    <em><?php esc_html_e( 'AI integration coming soon - this is placeholder content', 'tradepress' ); ?></em>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tradepress-focus-daily {
    padding: 20px 0;
}

.daily-header {
    margin-bottom: 30px;
}

.daily-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}

.last-updated {
    color: #666;
    font-size: 12px;
}

.daily-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.daily-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.daily-section h3 {
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #333;
}

.news-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 6px;
    background: #f8f9fa;
    border-left: 4px solid #007cba;
}

.news-symbol {
    font-weight: bold;
    font-size: 14px;
    min-width: 50px;
}

.news-content h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
}

.news-content p {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: #666;
}

.news-time {
    font-size: 11px;
    color: #999;
    margin-right: 10px;
}

.news-sentiment.positive {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
}

.news-sentiment.neutral {
    background: #6c757d;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
}

.scanner-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}

.opportunity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    background: #f0f8ff;
    border-left: 4px solid #28a745;
}

.opp-symbol {
    font-weight: bold;
    font-size: 16px;
}

.opp-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    font-size: 12px;
}

.opp-change {
    color: #28a745;
    font-weight: bold;
}

.movers-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.mover-tab {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 4px;
    cursor: pointer;
}

.mover-tab.active {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.mover-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 4px;
    background: #f8f9fa;
}

.mover-change.positive {
    color: #28a745;
    font-weight: bold;
}

.mover-change.negative {
    color: #dc3545;
    font-weight: bold;
}

.ai-content {
    background: #f0f8ff;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #6f42c1;
}

.ai-message {
    margin-bottom: 15px;
}

.ai-suggestions ul {
    margin: 10px 0;
    padding-left: 20px;
}

.ai-suggestions li {
    margin-bottom: 5px;
    font-size: 13px;
}

.ai-status {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
    color: #666;
    font-size: 12px;
}
</style>