<?php
/**
 * TradePress Focus - Weekly Tab
 *
 * Weekly outlook with earnings calendar, forecasts, and price targets.
 * Prioritizes portfolio and watchlist stocks with quick watchlist addition.
 *
 * @package TradePress/Admin/Focus
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tradepress-focus-weekly">
    <div class="weekly-header">
        <div class="weekly-controls">
            <select id="week-range">
                <option value="current"><?php esc_html_e( 'This Week', 'tradepress' ); ?></option>
                <option value="next"><?php esc_html_e( 'Next Week', 'tradepress' ); ?></option>
                <option value="both" selected><?php esc_html_e( 'Next 2 Weeks', 'tradepress' ); ?></option>
            </select>
        </div>
    </div>

    <div class="weekly-sections">
        <!-- Earnings Calendar -->
        <div class="weekly-section earnings-calendar">
            <h3><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Earnings Calendar', 'tradepress' ); ?></h3>
            <div class="earnings-priority">
                <h4><?php esc_html_e( 'Portfolio & Watchlist Stocks', 'tradepress' ); ?></h4>
                <div class="earnings-items priority">
                    <div class="earnings-item high-priority">
                        <div class="earnings-date">
                            <span class="day">Wed</span>
                            <span class="date">Nov 22</span>
                        </div>
                        <div class="earnings-details">
                            <span class="symbol">NVDA</span>
                            <span class="company">NVIDIA Corporation</span>
                            <span class="time">After Market Close</span>
                            <span class="estimate">EPS Est: $0.74</span>
                        </div>
                        <div class="earnings-actions">
                            <span class="portfolio-badge">Portfolio</span>
                            <button class="button button-small"><?php esc_html_e( 'Set Alert', 'tradepress' ); ?></button>
                        </div>
                    </div>
                    <div class="earnings-item medium-priority">
                        <div class="earnings-date">
                            <span class="day">Thu</span>
                            <span class="date">Nov 23</span>
                        </div>
                        <div class="earnings-details">
                            <span class="symbol">RBLX</span>
                            <span class="company">Roblox Corporation</span>
                            <span class="time">After Market Close</span>
                            <span class="estimate">EPS Est: -$0.38</span>
                        </div>
                        <div class="earnings-actions">
                            <span class="watchlist-badge">Watchlist</span>
                            <button class="button button-small"><?php esc_html_e( 'Set Alert', 'tradepress' ); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="earnings-other">
                <h4><?php esc_html_e( 'Other Stocks of Interest', 'tradepress' ); ?></h4>
                <div class="earnings-items other">
                    <div class="earnings-item">
                        <div class="earnings-date">
                            <span class="day">Tue</span>
                            <span class="date">Nov 21</span>
                        </div>
                        <div class="earnings-details">
                            <span class="symbol">PYPL</span>
                            <span class="company">PayPal Holdings</span>
                            <span class="time">After Market Close</span>
                            <span class="estimate">EPS Est: $1.36</span>
                        </div>
                        <div class="earnings-actions">
                            <button class="button button-small add-to-watchlist" data-symbol="PYPL"><?php esc_html_e( '+ Watchlist', 'tradepress' ); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Targets & Forecasts -->
        <div class="weekly-section price-targets">
            <h3><span class="dashicons dashicons-chart-area"></span> <?php esc_html_e( 'Price Targets & Forecasts', 'tradepress' ); ?></h3>
            <div class="targets-items">
                <div class="target-item">
                    <div class="target-symbol">NVDA</div>
                    <div class="target-details">
                        <div class="current-price">Current: $875.20</div>
                        <div class="target-range">
                            <span class="target-low">Low: $820</span>
                            <span class="target-avg">Avg: $950</span>
                            <span class="target-high">High: $1,100</span>
                        </div>
                        <div class="analyst-count">12 analysts</div>
                    </div>
                    <div class="target-potential">
                        <span class="upside positive">+8.5% upside</span>
                    </div>
                </div>
                <div class="target-item">
                    <div class="target-symbol">TSLA</div>
                    <div class="target-details">
                        <div class="current-price">Current: $235.60</div>
                        <div class="target-range">
                            <span class="target-low">Low: $180</span>
                            <span class="target-avg">Target: $275</span>
                            <span class="target-high">High: $350</span>
                        </div>
                        <div class="analyst-count">18 analysts</div>
                    </div>
                    <div class="target-potential">
                        <span class="upside positive">+16.7% upside</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Economic Events -->
        <div class="weekly-section economic-events">
            <h3><span class="dashicons dashicons-admin-site"></span> <?php esc_html_e( 'Key Economic Events', 'tradepress' ); ?></h3>
            <div class="event-items">
                <div class="event-item">
                    <div class="event-date">
                        <span class="day">Mon</span>
                        <span class="date">Nov 20</span>
                    </div>
                    <div class="event-details">
                        <span class="event-name">Manufacturing PMI</span>
                        <span class="event-time">10:00 AM EST</span>
                        <span class="event-impact high">High Impact</span>
                    </div>
                </div>
                <div class="event-item">
                    <div class="event-date">
                        <span class="day">Wed</span>
                        <span class="date">Nov 22</span>
                    </div>
                    <div class="event-details">
                        <span class="event-name">FOMC Meeting Minutes</span>
                        <span class="event-time">2:00 PM EST</span>
                        <span class="event-impact high">High Impact</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Forecast Integration (Placeholder) -->
        <div class="weekly-section ai-forecast">
            <h3><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'AI Market Forecast', 'tradepress' ); ?></h3>
            <div class="forecast-content">
                <div class="forecast-summary">
                    <h4><?php esc_html_e( 'Week Ahead Summary:', 'tradepress' ); ?></h4>
                    <p>Earnings season continues with tech focus. NVIDIA earnings Wednesday expected to drive semiconductor sector. Fed minutes may provide clarity on rate outlook.</p>
                </div>
                <div class="forecast-recommendations">
                    <h4><?php esc_html_e( 'Strategic Recommendations:', 'tradepress' ); ?></h4>
                    <ul>
                        <li>Consider reducing NVDA position size before earnings</li>
                        <li>Monitor RBLX for potential earnings play</li>
                        <li>Watch for Fed dovish signals in minutes</li>
                        <li>Tech sector may see increased volatility</li>
                    </ul>
                </div>
                <div class="forecast-status">
                    <em><?php esc_html_e( 'AI forecast integration coming soon - multiple sources will be merged', 'tradepress' ); ?></em>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tradepress-focus-weekly {
    padding: 20px 0;
}

.weekly-header {
    margin-bottom: 30px;
}

.weekly-controls {
    margin-top: 10px;
}

.weekly-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.weekly-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.weekly-section h3 {
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #333;
}

.earnings-priority h4, .earnings-other h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
}

.earnings-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 6px;
    background: #f8f9fa;
    border-left: 4px solid #ddd;
}

.earnings-item.high-priority {
    border-left-color: #dc3545;
    background: #fff5f5;
}

.earnings-item.medium-priority {
    border-left-color: #ffc107;
    background: #fffbf0;
}

.earnings-date {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 50px;
}

.earnings-date .day {
    font-size: 12px;
    color: #666;
}

.earnings-date .date {
    font-weight: bold;
    font-size: 14px;
}

.earnings-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.earnings-details .symbol {
    font-weight: bold;
    font-size: 16px;
}

.earnings-details .company {
    font-size: 13px;
    color: #666;
}

.earnings-details .time, .earnings-details .estimate {
    font-size: 12px;
    color: #999;
}

.earnings-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.portfolio-badge {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.watchlist-badge {
    background: #007cba;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
}

.target-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 6px;
    background: #f0f8ff;
    border-left: 4px solid #007cba;
}

.target-symbol {
    font-weight: bold;
    font-size: 16px;
    min-width: 60px;
}

.target-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.current-price {
    font-weight: bold;
    font-size: 14px;
}

.target-range {
    display: flex;
    gap: 15px;
    font-size: 12px;
}

.target-range span {
    color: #666;
}

.analyst-count {
    font-size: 11px;
    color: #999;
}

.target-potential .upside.positive {
    color: #28a745;
    font-weight: bold;
    font-size: 14px;
}

.event-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
    background: #f8f9fa;
    border-left: 4px solid #6f42c1;
}

.event-date {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 50px;
}

.event-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.event-name {
    font-weight: bold;
    font-size: 14px;
}

.event-time {
    font-size: 12px;
    color: #666;
}

.event-impact.high {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    align-self: flex-start;
}

.forecast-content {
    background: #f0f8ff;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #6f42c1;
}

.forecast-summary, .forecast-recommendations {
    margin-bottom: 15px;
}

.forecast-recommendations ul {
    margin: 10px 0;
    padding-left: 20px;
}

.forecast-recommendations li {
    margin-bottom: 5px;
    font-size: 13px;
}

.forecast-status {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
    color: #666;
    font-size: 12px;
}

.add-to-watchlist {
    background: #28a745 !important;
    color: white !important;
    border-color: #28a745 !important;
}
</style>