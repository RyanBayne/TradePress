<?php
/**
 * TradePress - User Watchlists Tab View
 *
 * @package TradePress/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get user watchlists data
$watchlists = TradePress_Admin_Watchlists_Page::get_user_watchlists();
?>

<div class="tradepress-user-watchlists-container">
    <!-- Demo Warning Notice -->
    <div class="notice notice-warning inline">
        <p>
            <strong><?php esc_html_e('Demo Mode:', 'tradepress'); ?></strong> 
            <?php esc_html_e('This is a demonstration of the TradePress Watchlists functionality. The data shown is sample data and does not reflect actual market conditions.', 'tradepress'); ?>
        </p>
    </div>

    <div class="user-watchlists-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_watchlists&tab=create-watchlist')); ?>" class="button button-primary">
            <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Create New Watchlist', 'tradepress'); ?>
        </a>
    </div>

    <?php if (empty($watchlists)) : ?>
        <div class="empty-watchlists">
            <p><?php esc_html_e('You don\'t have any watchlists yet. Create your first watchlist to start tracking stocks in groups.', 'tradepress'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_watchlists&tab=create-watchlist')); ?>" class="button button-primary">
                <?php esc_html_e('Create Your First Watchlist', 'tradepress'); ?>
            </a>
        </div>
    <?php else : ?>
        <div class="watchlists-scroll-container">
            <div class="watchlists-scroll-arrows">
                <button class="scroll-arrow scroll-left" aria-label="<?php esc_attr_e('Scroll left', 'tradepress'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <button class="scroll-arrow scroll-right" aria-label="<?php esc_attr_e('Scroll right', 'tradepress'); ?>">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
            <div class="watchlists-horizontal-container">
                <div class="watchlists-row">
                    <?php foreach ($watchlists as $watchlist) : 
                        // Determine horizontal score class
                        $h_score_class = '';
                        if ($watchlist['horizontal_score'] >= 85) {
                            $h_score_class = 'score-high';
                        } elseif ($watchlist['horizontal_score'] >= 70) {
                            $h_score_class = 'score-medium';
                        } else {
                            $h_score_class = 'score-low';
                        }
                        
                        // Get trend icon
                        $trend_icon = '';
                        $trend_class = '';
                        if ($watchlist['horizontal_trend'] === 'up') {
                            $trend_icon = 'dashicons-arrow-up-alt';
                            $trend_class = 'trend-up';
                        } elseif ($watchlist['horizontal_trend'] === 'down') {
                            $trend_icon = 'dashicons-arrow-down-alt';
                            $trend_class = 'trend-down';
                        } else {
                            $trend_icon = 'dashicons-minus';
                            $trend_class = 'trend-neutral';
                        }
                    ?>
                        <div class="watchlist-card" data-id="<?php echo esc_attr($watchlist['id']); ?>">
                            <div class="watchlist-card-header">
                                <h3 class="watchlist-name"><?php echo esc_html($watchlist['name']); ?></h3>
                                <div class="watchlist-actions">
                                    <a href="#" class="watchlist-action toggle-scoring" 
                                       data-id="<?php echo esc_attr($watchlist['id']); ?>" 
                                       data-active="<?php echo esc_attr($watchlist['scoring_active'] ? '1' : '0'); ?>"
                                       title="<?php echo $watchlist['scoring_active'] ? esc_attr__('Disable Scoring', 'tradepress') : esc_attr__('Enable Scoring', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-chart-bar scoring-icon <?php echo $watchlist['scoring_active'] ? 'scoring-active' : 'scoring-inactive'; ?>"></span>
                                    </a>
                                    <a href="#" class="watchlist-action edit-watchlist" data-id="<?php echo esc_attr($watchlist['id']); ?>" title="<?php esc_attr_e('Edit', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    <a href="#" class="watchlist-action delete-watchlist" data-id="<?php echo esc_attr($watchlist['id']); ?>" title="<?php esc_attr_e('Delete', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="watchlist-card-content">
                                <!-- Horizontal score indicator - renamed to Combined Score -->
                                <div class="horizontal-score-indicator <?php echo esc_attr($h_score_class); ?>">
                                    <div class="h-score-label"><?php esc_html_e('Score:', 'tradepress'); ?></div>
                                    <div class="h-score-value">
                                        <?php 
                                        // Calculate total score by summing individual symbol scores
                                        $total_possible = count($watchlist['symbols']) * 100;
                                        $total_score = 0;
                                        
                                        // Sum all symbol scores
                                        foreach ($watchlist['symbols'] as $symbol => $data) {
                                            $total_score += $data['score'];
                                        }
                                        
                                        echo esc_html($total_score . '/' . $total_possible); 
                                        ?>
                                    </div>
                                    <div class="h-score-trend">
                                        <span class="dashicons <?php echo esc_attr($trend_icon); ?> <?php echo esc_attr($trend_class); ?>"></span>
                                    </div>
                                    <div class="h-score-bar">
                                        <div class="h-score-bar-fill" style="width: <?php echo esc_attr(($total_score / $total_possible) * 100); ?>%;"></div>
                                    </div>
                                    <div class="score-updated-time" title="<?php echo esc_attr(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($watchlist['score_updated']))); ?>">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php 
                                            $time_diff = human_time_diff(strtotime($watchlist['score_updated']), current_time('timestamp'));
                                            printf(esc_html__('Updated %s ago', 'tradepress'), $time_diff);
                                        ?>
                                    </div>
                                </div>
                                
                                <p class="watchlist-description"><?php echo esc_html($watchlist['description']); ?></p>
                                <div class="watchlist-meta">
                                    <span class="watchlist-symbol-count">
                                        <span class="dashicons dashicons-chart-bar"></span>
                                        <?php echo esc_html(sprintf(_n('%s Symbol', '%s Symbols', $watchlist['symbol_count'], 'tradepress'), $watchlist['symbol_count'])); ?>
                                    </span>
                                    <span class="watchlist-created-date">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($watchlist['created']))); ?>
                                    </span>
                                </div>
                                
                                <div class="watchlist-symbols">
                                    <?php if (!empty($watchlist['symbols'])) : ?>
                                        <?php foreach ($watchlist['symbols'] as $symbol => $data) : 
                                            // Determine score class based on value
                                            $score_class = '';
                                            if (isset($data['score'])) {
                                                if ($data['score'] >= 85) {
                                                    $score_class = 'score-high';
                                                } elseif ($data['score'] >= 70) {
                                                    $score_class = 'score-medium';
                                                } else {
                                                    $score_class = 'score-low';
                                                }
                                            }
                                        ?>
                                            <div class="watchlist-symbol-container">
                                                <span class="watchlist-symbol-tag"><?php echo esc_html($symbol); ?></span>
                                                <div class="symbol-score <?php echo esc_attr($score_class); ?>">
                                                    <span class="score-value"><?php echo esc_html($data['score']); ?></span>
                                                    <div class="score-bar">
                                                        <div class="score-bar-fill" style="width: <?php echo esc_attr($data['score']); ?>%;"></div>
                                                    </div>
                                                    <span class="score-strategy"><?php echo esc_html($data['strategy']); ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p class="no-symbols"><?php esc_html_e('No symbols added to this watchlist yet.', 'tradepress'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="watchlist-card-footer">
                                <a href="#" class="button button-secondary view-watchlist" data-id="<?php echo esc_attr($watchlist['id']); ?>">
                                    <span class="dashicons dashicons-visibility"></span> <?php esc_html_e('View Details', 'tradepress'); ?>
                                </a>
                                <a href="#" class="button add-symbols" data-id="<?php echo esc_attr($watchlist['id']); ?>">
                                    <span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Symbols', 'tradepress'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Watchlist Details Modal -->
        <div id="watchlist-details-modal" class="tradepress-modal" style="display: none;">
            <div class="tradepress-modal-content">
                <span class="tradepress-modal-close">&times;</span>
                <div id="watchlist-details-content">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
        
        <!-- Add Symbols Modal -->
        <div id="add-symbols-modal" class="tradepress-modal" style="display: none;">
            <div class="tradepress-modal-content">
                <span class="tradepress-modal-close">&times;</span>
                <h3><?php esc_html_e('Add Symbols to Watchlist', 'tradepress'); ?></h3>
                <div class="modal-watchlist-name"></div>
                
                <div class="symbol-search-container">
                    <input type="text" id="symbol-search" placeholder="<?php esc_attr_e('Search for symbols...', 'tradepress'); ?>">
                    <button class="button button-primary" id="search-symbols-btn"><?php esc_html_e('Search', 'tradepress'); ?></button>
                </div>
                
                <div class="search-results" style="display: none;">
                    <h4><?php esc_html_e('Search Results', 'tradepress'); ?></h4>
                    <div class="symbol-search-results"></div>
                </div>
                
                <div class="selected-symbols-container">
                    <h4><?php esc_html_e('Selected Symbols', 'tradepress'); ?></h4>
                    <div class="selected-symbols"></div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="button cancel-modal"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
                    <button type="button" class="button button-primary save-symbols"><?php esc_html_e('Add to Watchlist', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
        
        <!-- Delete Confirmation Modal -->
        <div id="delete-watchlist-modal" class="tradepress-modal" style="display: none;">
            <div class="tradepress-modal-content">
                <span class="tradepress-modal-close">&times;</span>
                <h3><?php esc_html_e('Delete Watchlist', 'tradepress'); ?></h3>
                <p class="delete-confirmation-message"></p>
                
                <div class="modal-actions">
                    <button type="button" class="button cancel-modal"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
                    <button type="button" class="button button-delete confirm-delete"><?php esc_html_e('Delete Watchlist', 'tradepress'); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript moved to assets/js/tradepress-watchlists.js -->
