<?php
/**
 * Admin View: Notice - Updating
 *
 * @version 1.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated TradePress-message TradePress-connect">
    <p><strong><?php /* Use esc_html_e for safe translated output */ esc_html_e( 'TradePress Data Update', 'tradepress' ); ?></strong> &#8211; <?php esc_html_e( 'Your database is being updated in the background.', 'tradepress' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_update_TradePress', 'true', admin_url( 'admin.php?page=TradePress' ) ) ); ?>"><?php esc_html_e( 'Taking a while? Click here to run it now.', 'tradepress' ); ?></a></p>
</div>
