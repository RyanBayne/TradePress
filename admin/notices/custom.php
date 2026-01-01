<?php
/**
* TradePress notice layout styled like WordPress core: is not dismissable
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="message" class="updated TradePress-message">
    <?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
