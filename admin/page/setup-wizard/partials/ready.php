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
        <h2><?php _e('TradePress System Ready!', 'tradepress'); ?></h2>
        <P><?php _e('Please visit the plugins settings page to consider the plugins full configuration.', 'tradepress'); ?></P>
        <ul>
            <li class="setup-thing"><a class="button button-primary button-large" href="<?php echo esc_url(admin_url('admin.php?page=TradePress')); ?>"><?php _e('Go to Settings', 'tradepress'); ?></a></li>
        </ul>
    </div>
    <div class="tradepress-setup-next-steps-last">
        <h2><?php _e('Support Links', 'tradepress'); ?></h2>
        
        <a href="<?php echo esc_url(TRADEPRESS_GITHUB); ?>"><?php _e('GitHub', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_DISCORD); ?>"><?php _e('Discord', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_TWITTER); ?>"><?php _e('Twitter', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_HOME); ?>"><?php _e('Blog', 'tradepress'); ?></a>
        <a href="<?php echo esc_url(TRADEPRESS_AUTHOR_DONATE); ?>"><?php _e('Patreon', 'tradepress'); ?></a>
    </div>
</div>