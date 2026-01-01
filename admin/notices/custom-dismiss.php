<?php
/**
* TradePress notice layout styled like WordPress core: is dismissable
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated TradePress-message">
    <a class="TradePress-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'TradePress-hide-notice', $notice ), 'TradePress_hide_notices_nonce', '_TradePress_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'tradepress' ); ?></a>
    <?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
