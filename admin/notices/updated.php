<?php
/**
 * Admin View: Notice - Updated
 *
 * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated TradePress-message TradePress-connect">
	<a class="TradePress-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'TradePress-hide-notice', 'update', remove_query_arg( 'do_update_TradePress' ) ), 'TradePress_hide_notices_nonce', '_TradePress_notice_nonce' ) ); ?>"><?php /* Use esc_html_e for safe translated output */ esc_html_e( 'Dismiss', 'tradepress' ); ?></a>

	<p><?php esc_html_e( 'TradePress data update complete. Thank you for updating to the latest version!', 'tradepress' ); ?></p>
</div>
