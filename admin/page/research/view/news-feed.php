<?php
/**
 * TradePress News Feed Tab
 *
 * Displays a merged feed of multiple discussion sources: official company news,
 * market platform news, analyst updates, blog posts, emails, tweets, RSS feeds, etc.
 *
 * @package TradePress
 * @subpackage admin/page/ResearchTabs
 * @version 1.0.0
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display the News Feed tab content
 */
function tradepress_news_feed_tab_content() {
    // Check if we're in demo mode
    $is_demo = function_exists('is_demo_mode') ? is_demo_mode() : true;
    
    // Get active symbol for filtering if available
    $active_symbol = isset($_GET['symbol']) ? sanitize_text_field($_GET['symbol']) : '';
    
    // Get filter parameters
    $source_filter = isset($_GET['source']) ? sanitize_text_field($_GET['source']) : 'all';
    $date_filter = isset($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : '7d';
    $sentiment_filter = isset($_GET['sentiment']) ? sanitize_text_field($_GET['sentiment']) : 'all';
    
    // Get feed items based on mode and filters
    $feed_items = $is_demo ? get_demo_feed_items($active_symbol, $source_filter, $date_filter, $sentiment_filter) : 
                             get_live_feed_items($active_symbol, $source_filter, $date_filter, $sentiment_filter);
    
    // Available sources for filter dropdown
    $sources = array(
        'all' => __('All Sources', 'tradepress'),
        'discord' => __('Discord', 'tradepress'),
        'twitter' => __('X.com (Twitter)', 'tradepress'),
        'news' => __('News Sites', 'tradepress'),
        'blogs' => __('Financial Blogs', 'tradepress'),
        'reddit' => __('Reddit', 'tradepress'),
        'stocktwits' => __('StockTwits', 'tradepress'),
    );
    
    // Date range options
    $date_ranges = array(
        '1d' => __('Last 24 Hours', 'tradepress'),
        '7d' => __('Last 7 Days', 'tradepress'),
        '30d' => __('Last 30 Days', 'tradepress'),
        'custom' => __('Custom Range', 'tradepress'),
    );
    
    // Sentiment options
    $sentiments = array(
        'all' => __('All Sentiment', 'tradepress'),
        'positive' => __('Positive', 'tradepress'),
        'negative' => __('Negative', 'tradepress'),
        'neutral' => __('Neutral', 'tradepress'),
    );
    ?>
    
    <div class="tradepress-news-feed-container">

        
        <div class="news-feed-header">
            <div class="feed-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="tradepress_research">
                    <input type="hidden" name="tab" value="news_feed">
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="symbol"><?php esc_html_e('Symbol:', 'tradepress'); ?></label>
                            <input type="text" id="symbol" name="symbol" placeholder="e.g., AAPL" value="<?php echo esc_attr($active_symbol); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="source"><?php esc_html_e('Source:', 'tradepress'); ?></label>
                            <select id="source" name="source">
                                <?php foreach ($sources as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($source_filter, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="date_range"><?php esc_html_e('Date Range:', 'tradepress'); ?></label>
                            <select id="date_range" name="date_range" onchange="toggleCustomDateRange(this.value)">
                                <?php foreach ($date_ranges as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($date_filter, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="custom-date-container" class="filter-group" style="<?php echo $date_filter === 'custom' ? '' : 'display: none;'; ?>">
                            <label for="start_date"><?php esc_html_e('From:', 'tradepress'); ?></label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr(isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'))); ?>">
                            
                            <label for="end_date"><?php esc_html_e('To:', 'tradepress'); ?></label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr(isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d')); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="sentiment"><?php esc_html_e('Sentiment:', 'tradepress'); ?></label>
                            <select id="sentiment" name="sentiment">
                                <?php foreach ($sentiments as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($sentiment_filter, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="button button-primary"><?php esc_html_e('Apply Filters', 'tradepress'); ?></button>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&tab=news_feed')); ?>" class="button"><?php esc_html_e('Reset', 'tradepress'); ?></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="news-feed-content">
            <?php if (empty($feed_items)): ?>
                <div class="no-feed-items">
                    <p><?php esc_html_e('No feed items found matching your criteria.', 'tradepress'); ?></p>
                </div>
            <?php else: ?>
                <div class="feed-items-container">
                    <?php foreach ($feed_items as $item): ?>
                        <div class="feed-item <?php echo esc_attr($item['source_type']); ?> sentiment-<?php echo esc_attr($item['sentiment']); ?>">
                            <div class="feed-item-header">
                                <div class="source-info">
                                    <span class="source-icon <?php echo esc_attr($item['source_icon']); ?>"></span>
                                    <span class="source-name"><?php echo esc_html($item['source_name']); ?></span>
                                </div>
                                <div class="feed-time">
                                    <span class="time-ago"><?php echo esc_html($item['time_ago']); ?></span>
                                </div>
                            </div>
                            
                            <div class="feed-item-content">
                                <?php if (!empty($item['title'])): ?>
                                    <h3 class="feed-title"><?php echo esc_html($item['title']); ?></h3>
                                <?php endif; ?>
                                
                                <div class="feed-message">
                                    <?php echo esc_html($item['message']); ?>
                                </div>
                                
                                <?php if (!empty($item['symbols'])): ?>
                                    <div class="feed-symbols">
                                        <?php foreach ($item['symbols'] as $symbol): ?>
                                            <span class="feed-symbol"><?php echo esc_html($symbol); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['image_url'])): ?>
                                    <div class="feed-image">
                                        <img src="<?php echo esc_url($item['image_url']); ?>" alt="<?php echo esc_attr($item['source_name']); ?>" />
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="feed-item-footer">
                                <div class="sentiment-indicator sentiment-<?php echo esc_attr($item['sentiment']); ?>">
                                    <span class="sentiment-label"><?php echo esc_html(ucfirst($item['sentiment'])); ?></span>
                                </div>
                                
                                <div class="feed-actions">
                                    <?php if (!empty($item['link'])): ?>
                                        <a href="<?php echo esc_url($item['link']); ?>" class="button button-small" target="_blank">
                                            <?php esc_html_e('View Original', 'tradepress'); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="button button-small save-feed-item" data-id="<?php echo esc_attr($item['id']); ?>">
                                        <?php esc_html_e('Save', 'tradepress'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="feed-pagination">
                    <button type="button" class="button load-more-feed" id="load-more-feed">
                        <?php esc_html_e('Load More', 'tradepress'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Generate sample feed items for demo mode
 * 
 * @param string $symbol Symbol filter
 * @param string $source Source filter
 * @param string $date_range Date range filter
 * @param string $sentiment Sentiment filter
 * @return array Array of feed items
 */
function get_demo_feed_items($symbol = '', $source = 'all', $date_range = '7d', $sentiment = 'all') {
    // Sample items to demonstrate the UI
    $items = array(
        array(
            'id' => '1',
            'source_type' => 'discord',
            'source_name' => 'Stock VIP Discord',
            'source_icon' => 'dashicons dashicons-discord',
            'title' => '',
            'message' => 'Buy $NVDA now at $460. This should run to $490-500 range before the conference call.',
            'symbols' => array('NVDA'),
            'image_url' => '',
            'link' => '',
            'sentiment' => 'positive',
            'time_ago' => '2 hours ago'
        ),
        array(
            'id' => '2',
            'source_type' => 'twitter',
            'source_name' => 'MarketWatch',
            'source_icon' => 'dashicons dashicons-twitter',
            'title' => '',
            'message' => 'Breaking: Fed signals rates will stay higher for longer as inflation battle continues $SPY $QQQ',
            'symbols' => array('SPY', 'QQQ'),
            'image_url' => '',
            'link' => 'https://twitter.com/MarketWatch',
            'sentiment' => 'negative',
            'time_ago' => '5 hours ago'
        ),
        array(
            'id' => '3',
            'source_type' => 'news',
            'source_name' => 'CNBC',
            'source_icon' => 'dashicons dashicons-admin-site',
            'title' => 'Microsoft beats earnings expectations, stock climbs 4%',
            'message' => 'Microsoft reported better-than-expected fiscal Q1 earnings, with cloud revenue up 23% year over year.',
            'symbols' => array('MSFT'),
            'image_url' => '',
            'link' => 'https://www.cnbc.com/',
            'sentiment' => 'positive',
            'time_ago' => '1 day ago'
        ),
        array(
            'id' => '4',
            'source_type' => 'reddit',
            'source_name' => 'r/WallStreetBets',
            'source_icon' => 'dashicons dashicons-reddit',
            'title' => '',
            'message' => 'TSLA earnings tomorrow, anyone playing calls or puts? The stock looks oversold to me but guidance could be weak.',
            'symbols' => array('TSLA'),
            'image_url' => '',
            'link' => 'https://www.reddit.com/r/wallstreetbets/',
            'sentiment' => 'neutral',
            'time_ago' => '12 hours ago'
        ),
        array(
            'id' => '5',
            'source_type' => 'stocktwits',
            'source_name' => 'StockTwits Trending',
            'source_icon' => 'dashicons dashicons-chart-area',
            'title' => '',
            'message' => '$AAPL bouncing off support level at $170. Bulls taking control after that big red candle.',
            'symbols' => array('AAPL'),
            'image_url' => 'https://picsum.photos/300/200',
            'link' => 'https://stocktwits.com/',
            'sentiment' => 'positive',
            'time_ago' => '3 hours ago'
        ),
        array(
            'id' => '6',
            'source_type' => 'blogs',
            'source_name' => 'Seeking Alpha',
            'source_icon' => 'dashicons dashicons-media-text',
            'title' => 'Why AMD is poised to outperform NVDA in the next 6 months',
            'message' => 'AMD\'s new MI300 chip series is gaining traction in the data center market, potentially taking market share from Nvidia.',
            'symbols' => array('AMD', 'NVDA'),
            'image_url' => '',
            'link' => 'https://seekingalpha.com/',
            'sentiment' => 'positive',
            'time_ago' => '8 hours ago'
        ),
    );
    
    // Apply symbol filter
    if (!empty($symbol)) {
        $filtered_items = array();
        foreach ($items as $item) {
            if (in_array(strtoupper($symbol), $item['symbols'])) {
                $filtered_items[] = $item;
            }
        }
        $items = $filtered_items;
    }
    
    // Apply source filter
    if ($source !== 'all') {
        $filtered_items = array();
        foreach ($items as $item) {
            if ($item['source_type'] === $source) {
                $filtered_items[] = $item;
            }
        }
        $items = $filtered_items;
    }
    
    // Apply sentiment filter
    if ($sentiment !== 'all') {
        $filtered_items = array();
        foreach ($items as $item) {
            if ($item['sentiment'] === $sentiment) {
                $filtered_items[] = $item;
            }
        }
        $items = $filtered_items;
    }
    
    return $items;
}

/**
 * Get live feed items from actual data sources
 * 
 * @param string $symbol Symbol filter
 * @param string $source Source filter
 * @param string $date_range Date range filter
 * @param string $sentiment Sentiment filter
 * @return array Array of feed items
 */
function get_live_feed_items($symbol = '', $source = 'all', $date_range = '7d', $sentiment = 'all') {
    // This would be implemented to fetch real data from APIs and databases
    // For now, return the same demo data
    return get_demo_feed_items($symbol, $source, $date_range, $sentiment);
}
?>
