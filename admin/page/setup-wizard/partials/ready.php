<?php
/**
 * Setup Wizard - Ready Step
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tradepress-setup-next-steps">
    <div class="tradepress-setup-next-steps-first">
        <h2><?php esc_html_e('TradePress System Ready!', 'tradepress'); ?></h2>
        <P><?php esc_html_e('Please visit the plugins settings page to consider the plugins full configuration.', 'tradepress'); ?></P>
        <ul>
            <li class="setup-thing"><a class="button button-primary button-large" href="<?php echo esc_url(admin_url('admin.php?page=TradePress')); ?>"><?php esc_html_e('Go to Settings', 'tradepress'); ?></a></li>
        </ul>
    </div>
    <div class="tradepress-setup-next-steps-last">
        <h2><?php esc_html_e('Support Links', 'tradepress'); ?></h2>
        
        <a href="<?php echo esc_url(TRADEPRESS_GITHUB); ?>"><?php esc_html_e('GitHub', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_DISCORD); ?>"><?php esc_html_e('Discord', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_TWITTER); ?>"><?php esc_html_e('Twitter', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_HOME); ?>"><?php esc_html_e('Blog', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_AUTHOR_DONATE); ?>"><?php esc_html_e('Patreon', 'tradepress'); ?></a>
    </div>
</div>