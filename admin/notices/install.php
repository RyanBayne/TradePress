<?php
/**
 * Admin View: Notice - Install with wizard start button.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated TradePress-message TradePress-connect">
    <p><?php _e( '<strong>Welcome to WordPress TradePress</strong> &#8211; You&lsquo;re almost ready to begin using the plugin. It is recommended that you now complete the Setup Wizard to configure TradePress.', 'tradepress' ); ?></p>
    <p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress-setup' ) ); ?>" class="button-primary"><?php _e( 'Run the Setup Wizard', 'tradepress' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'TradePress-hide-notice', 'install' ), 'TradePress_hide_notices_nonce', '_TradePress_notice_nonce' ) ); ?>"><?php _e( 'Skip Setup', 'tradepress' ); ?></a></p>
</div>
