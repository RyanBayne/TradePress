<?php
/**
 * Admin View: Notice - Install with wizard start button.
 *
 * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated TradePress-message TradePress-connect">
	<p><?php /* Use wp_kses_post because the translated string contains safe HTML markup */ echo wp_kses_post( __( '<strong>Welcome to WordPress TradePress</strong> &#8211; You&lsquo;re almost ready to begin using the plugin. It is recommended that you now complete the Setup Wizard to configure TradePress.', 'tradepress' ) ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=tradepress-setup' ) ); ?>" class="button-primary"><?php /* Use esc_html_e for safe translated output */ esc_html_e( 'Run the Setup Wizard', 'tradepress' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'TradePress-hide-notice', 'install' ), 'TradePress_hide_notices_nonce', '_TradePress_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip Setup', 'tradepress' ); ?></a></p>
</div>
