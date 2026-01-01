<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated TradePress-message TradePress-connect">
    <p><strong><?php _e( 'TradePress Data Update', 'tradepress' ); ?></strong> &#8211; <?php _e( 'We need to update your store\'s database to the latest version.', 'tradepress' ); ?></p>
    <p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_TradePress', 'true', admin_url( 'admin.php?page=TradePress' ) ) ); ?>" class="TradePress-update-now button-primary"><?php _e( 'Run the updater', 'tradepress' ); ?></a></p>
</div>
<script type="text/javascript">
    jQuery( '.TradePress-update-now' ).click( 'click', function() {
        return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'tradepress' ) ); ?>' ); // jshint ignore:line
    });
</script>
