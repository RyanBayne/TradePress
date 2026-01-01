<?php
/**
 * Admin settings page template
 *
 * @package TradePress/Admin
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap tradepress">
    <h1>
        <?php 
        echo esc_html__('TradePress Settings', 'tradepress');
        if (isset($tabs[$current_tab])) {
            echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
            echo esc_html($tabs[$current_tab]);
            
            // Add section name if available
            if (!empty($current_section) && isset($sections[$current_section])) {
                echo ' <span class="dashicons dashicons-arrow-right-alt2" style="font-size: 0.8em; vertical-align: middle; margin: 0 5px;"></span> ';
                echo esc_html($sections[$current_section]);
            }
        }
        ?>
    </h1>
    
    <form method="post" id="mainform" action="" enctype="multipart/form-data">
        <nav class="nav-tab-wrapper tradepress-nav-tab-wrapper">
            <?php
            foreach ( $tabs as $tab_id => $tab_name ) {
                $active = $current_tab === $tab_id ? ' nav-tab-active' : '';
                echo '<a href="?page=TradePress&tab=' . esc_attr( $tab_id ) . '" class="nav-tab' . esc_attr( $active ) . '">' . esc_html( $tab_name ) . '</a>';
            }
            do_action( 'TradePress_settings_tabs' );
            ?>
        </nav>
        <h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ?? '' ); ?></h1>
        <?php
            self::show_messages();

            // This action was causing the error - we need to make sure all settings classes implement this
            do_action( 'TradePress_sections_' . $current_tab );

            do_action( 'TradePress_settings_' . $current_tab );
        ?>
        <p class="submit">
            <?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
                <button name="save" class="button-primary tradepress-save-button" type="submit" value="<?php echo esc_attr( $save_button_text ); ?>"><?php echo esc_html( $save_button_text ); ?></button>
                <?php if ( $current_tab === 'bugnet' && ( empty( $current_section ) || $current_section === 'settings' ) ) : ?>
                    <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=TradePress&tab=bugnet&action=clear_logs' ), 'tradepress_clear_logs', 'nonce' ); ?>" class="button button-secondary" onclick="return confirm('Clear logs for selected output types only?');" style="margin-left: 10px; height: 28px; padding: 0 10px; box-sizing: border-box;"><?php echo esc_html__( 'Clear Logs', 'tradepress' ); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <?php wp_nonce_field( 'TradePress-settings' ); ?>
        </p>
    </form>
</div>