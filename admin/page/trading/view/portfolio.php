<?php
/**
 * TradePress - Portfolio Tab View
 *
 * @package TradePress/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get portfolio data
$portfolio = array(
    array(
        'symbol' => 'AAPL',
        'shares' => 15,
        'avg_price' => 175.50,
        'current_price' => 185.92,
        'total_cost' => 2632.50,
        'market_value' => 2788.80,
        'gain_loss' => 156.30,
        'gain_loss_percent' => 5.94,
    ),
    array(
        'symbol' => 'MSFT',
        'shares' => 8,
        'avg_price' => 340.25,
        'current_price' => 376.17,
        'total_cost' => 2722.00,
        'market_value' => 3009.36,
        'gain_loss' => 287.36,
        'gain_loss_percent' => 10.56,
    ),
    array(
        'symbol' => 'NVDA',
        'shares' => 4,
        'avg_price' => 780.45,
        'current_price' => 950.02,
        'total_cost' => 3121.80,
        'market_value' => 3800.08,
        'gain_loss' => 678.28,
        'gain_loss_percent' => 21.73,
    ),
);

// Calculate portfolio totals
$total_cost = 0;
$total_value = 0;
$total_gain_loss = 0;

foreach ($portfolio as $position) {
    $total_cost += $position['total_cost'];
    $total_value += $position['market_value'];
    $total_gain_loss += $position['gain_loss'];
}

$total_gain_loss_percent = ($total_gain_loss / $total_cost) * 100;
?>

<div class="tradepress-portfolio-container">
    <div class="portfolio-header">
        <div class="portfolio-summary">
            <div class="summary-item">
                <span class="summary-label"><?php esc_html_e('Total Cost', 'tradepress'); ?></span>
                <span class="summary-value">$<?php echo number_format($total_cost, 2); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label"><?php esc_html_e('Market Value', 'tradepress'); ?></span>
                <span class="summary-value">$<?php echo number_format($total_value, 2); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label"><?php esc_html_e('Gain/Loss', 'tradepress'); ?></span>
                <span class="summary-value <?php echo ($total_gain_loss >= 0) ? 'positive' : 'negative'; ?>">
                    $<?php echo number_format($total_gain_loss, 2); ?> (<?php echo number_format($total_gain_loss_percent, 2); ?>%)
                </span>
            </div>
        </div>
    </div>

    <div class="portfolio-actions">
        <button class="button button-primary" id="refresh-portfolio">
            <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh', 'tradepress'); ?>
        </button>
        <button class="button" id="export-portfolio">
            <span class="dashicons dashicons-download"></span> <?php esc_html_e('Export', 'tradepress'); ?>
        </button>
    </div>

    <table class="widefat fixed tradepress-portfolio-table" cellspacing="0">
        <thead>
            <tr>
                <th><?php esc_html_e('Symbol', 'tradepress'); ?></th>
                <th><?php esc_html_e('Shares', 'tradepress'); ?></th>
                <th><?php esc_html_e('Avg. Cost', 'tradepress'); ?></th>
                <th><?php esc_html_e('Current Price', 'tradepress'); ?></th>
                <th><?php esc_html_e('Total Cost', 'tradepress'); ?></th>
                <th><?php esc_html_e('Market Value', 'tradepress'); ?></th>
                <th><?php esc_html_e('Gain/Loss', 'tradepress'); ?></th>
                <th><?php esc_html_e('Actions', 'tradepress'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($portfolio)) : ?>
                <tr>
                    <td colspan="8" class="no-positions">
                        <?php esc_html_e('No positions in your portfolio. Start trading to build your portfolio.', 'tradepress'); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($portfolio as $position) : 
                    $gain_loss_class = $position['gain_loss'] >= 0 ? 'positive' : 'negative';
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($position['symbol']); ?></strong>
                        </td>
                        <td><?php echo esc_html($position['shares']); ?></td>
                        <td>$<?php echo number_format($position['avg_price'], 2); ?></td>
                        <td>$<?php echo number_format($position['current_price'], 2); ?></td>
                        <td>$<?php echo number_format($position['total_cost'], 2); ?></td>
                        <td>$<?php echo number_format($position['market_value'], 2); ?></td>
                        <td class="<?php echo esc_attr($gain_loss_class); ?>">
                            $<?php echo number_format($position['gain_loss'], 2); ?> 
                            (<?php echo number_format($position['gain_loss_percent'], 2); ?>%)
                        </td>
                        <td class="actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_trading&tab=manual-trade&symbol=' . $position['symbol'])); ?>" 
                               class="button button-small">
                                <?php esc_html_e('Trade', 'tradepress'); ?>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_research&symbol=' . $position['symbol'])); ?>" 
                               class="button button-small">
                                <?php esc_html_e('Research', 'tradepress'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>