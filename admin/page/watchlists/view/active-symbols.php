<?php
/**
 * TradePress - Active Symbols Tab View
 *
 * @package TradePress/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get active symbols data
$active_symbols = TradePress_Admin_Watchlists_Page::get_active_symbols();
?>

<div class="tradepress-active-symbols-container">
    <div class="active-symbols-actions">
        <div class="active-symbols-filters">
            <select id="activity-filter">
                <option value=""><?php esc_html_e('All Activities', 'tradepress'); ?></option>
                <option value="scored"><?php esc_html_e('Scored', 'tradepress'); ?></option>
                <option value="traded"><?php esc_html_e('Traded', 'tradepress'); ?></option>
                <option value="researched"><?php esc_html_e('Researched', 'tradepress'); ?></option>
            </select>
            <button class="button" id="apply-filters"><?php esc_html_e('Filter', 'tradepress'); ?></button>
        </div>
        <div class="active-symbols-actions-right">
            <button class="button button-primary" id="create-watchlist-btn">
                <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Create Watchlist from Selection', 'tradepress'); ?>
            </button>
            <button class="button" id="refresh-symbols">
                <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh', 'tradepress'); ?>
            </button>
        </div>
    </div>

    <?php if (empty($active_symbols)) : ?>
        <div class="empty-active-symbols">
            <p><?php esc_html_e('No active symbols found. Start researching or trading to build your active symbols list.', 'tradepress'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research')); ?>" class="button button-primary">
                <?php esc_html_e('Research Symbols', 'tradepress'); ?>
            </a>
        </div>
    <?php else : ?>
        <form id="active-symbols-form">
            <table class="widefat fixed striped tradepress-active-symbols-table" cellspacing="0">
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" id="select-all-symbols">
                        </th>
                        <th class="column-symbol"><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                        <th class="column-name"><?php esc_html_e('Company Name', 'tradepress'); ?></th>
                        <th class="column-price"><?php esc_html_e('Current Price', 'tradepress'); ?></th>
                        <th class="column-change"><?php esc_html_e('Change', 'tradepress'); ?></th>
                        <th class="column-score"><?php esc_html_e('Score', 'tradepress'); ?></th>
                        <th class="column-activity"><?php esc_html_e('Activity', 'tradepress'); ?></th>
                        <th class="column-last-activity"><?php esc_html_e('Last Activity', 'tradepress'); ?></th>
                        <th class="column-date-added"><?php esc_html_e('Date Added', 'tradepress'); ?></th>
                        <th class="column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_symbols as $symbol) : 
                        $change_class = $symbol['change_pct'] >= 0 ? 'positive' : 'negative';
                        $change_prefix = $symbol['change_pct'] >= 0 ? '+' : '';
                        $activity_labels = array(
                            'scored' => '<span class="activity-badge scored">' . esc_html__('Scored', 'tradepress') . '</span>',
                            'traded' => '<span class="activity-badge traded">' . esc_html__('Traded', 'tradepress') . '</span>',
                            'researched' => '<span class="activity-badge researched">' . esc_html__('Researched', 'tradepress') . '</span>',
                        );
                        
                        // Determine score class based on value
                        $score_class = '';
                        if (isset($symbol['score'])) {
                            if ($symbol['score'] >= 85) {
                                $score_class = 'score-high';
                            } elseif ($symbol['score'] >= 70) {
                                $score_class = 'score-medium';
                            } else {
                                $score_class = 'score-low';
                            }
                        }
                    ?>
                        <tr>
                            <td class="check-column">
                                <input type="checkbox" name="selected_symbols[]" value="<?php echo esc_attr($symbol['symbol']); ?>">
                            </td>
                            <td class="column-symbol">
                                <strong><?php echo esc_html($symbol['symbol']); ?></strong>
                            </td>
                            <td class="column-name"><?php echo esc_html($symbol['name']); ?></td>
                            <td class="column-price">$<?php echo number_format($symbol['price'], 2); ?></td>
                            <td class="column-change <?php echo esc_attr($change_class); ?>">
                                <?php echo esc_html($change_prefix . number_format($symbol['change_pct'], 2)); ?>%
                            </td>
                            <td class="column-score <?php echo esc_attr($score_class); ?>">
                                <?php 
                                if (isset($symbol['score'])) {
                                    echo '<div class="score-display">';
                                    echo '<div class="score-value">' . esc_html($symbol['score']) . '</div>';
                                    echo '<div class="score-bar"><div class="score-bar-fill" style="width:' . esc_attr($symbol['score']) . '%;"></div></div>';
                                    echo '</div>';
                                } else {
                                    echo '<em>' . esc_html__('Not scored', 'tradepress') . '</em>';
                                }
                                ?>
                            </td>
                            <td class="column-activity">
                                <?php 
                                    foreach ($symbol['activity'] as $activity) {
                                        echo wp_kses_post($activity_labels[$activity] . ' ');
                                    }
                                ?>
                            </td>
                            <td class="column-last-activity">
                                <?php echo esc_html(human_time_diff(strtotime($symbol['last_activity']), current_time('timestamp')) . ' ' . __('ago', 'tradepress')); ?>
                            </td>
                            <td class="column-date-added">
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($symbol['added_date']))); ?>
                            </td>
                            <td class="column-actions">
                                <div class="row-actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&symbol=' . $symbol['symbol'])); ?>" class="button button-small" title="<?php esc_attr_e('Research', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-chart-line"></span>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_trading&tab=manual-trade&symbol=' . $symbol['symbol'])); ?>" class="button button-small" title="<?php esc_attr_e('Trade', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-money-alt"></span>
                                    </a>
                                    <a href="#" class="button button-small score-symbol" data-symbol="<?php echo esc_attr($symbol['symbol']); ?>" title="<?php esc_attr_e('Score Symbol', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-performance"></span>
                                    </a>
                                    <a href="#" class="button button-small add-to-watchlist" data-symbol="<?php echo esc_attr($symbol['symbol']); ?>" title="<?php esc_attr_e('Add to Watchlist', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-plus"></span>
                                    </a>
                                    <a href="#" class="button button-small remove-symbol" data-symbol="<?php echo esc_attr($symbol['symbol']); ?>" title="<?php esc_attr_e('Remove', 'tradepress'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        
        <div id="create-watchlist-modal" class="tradepress-modal">
            <div class="tradepress-modal-content">
                <span class="tradepress-modal-close">&times;</span>
                <h3><?php esc_html_e('Create New Watchlist', 'tradepress'); ?></h3>
                <form id="create-watchlist-form">
                    <div class="form-field">
                        <label for="watchlist-name"><?php esc_html_e('Watchlist Name', 'tradepress'); ?></label>
                        <input type="text" id="watchlist-name" name="watchlist_name" required>
                    </div>
                    <div class="form-field">
                        <label for="watchlist-description"><?php esc_html_e('Description', 'tradepress'); ?></label>
                        <textarea id="watchlist-description" name="watchlist_description" rows="3"></textarea>
                    </div>
                    <div class="form-field">
                        <label><?php esc_html_e('Selected Symbols', 'tradepress'); ?></label>
                        <div id="selected-symbols-list"></div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="button cancel-watchlist"><?php esc_html_e('Cancel', 'tradepress'); ?></button>
                        <button type="submit" class="button button-primary"><?php esc_html_e('Create Watchlist', 'tradepress'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript moved to assets/js/tradepress-watchlists.js -->
