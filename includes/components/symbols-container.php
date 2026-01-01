<?php
/**
 * TradePress Symbols Container Component
 *
 * Reusable component for displaying selected symbols across the plugin.
 *
 * @package TradePress/Components
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Symbols_Container {
    
    /**
     * Render symbols container
     *
     * @param array $symbols Array of symbol strings
     * @param array $options Container options
     * @return string HTML output
     */
    public static function render( $symbols = array(), $options = array() ) {
        if ( empty( $symbols ) ) {
            return '';
        }
        
        $defaults = array(
            'title' => __( 'Selected Symbols', 'tradepress' ),
            'show_count' => true,
            'show_reset' => false,
            'reset_url' => '',
            'position' => 'default' // 'default', 'floating'
        );
        
        $options = wp_parse_args( $options, $defaults );
        
        ob_start();
        ?>
        <div class="tradepress-symbols-container <?php echo esc_attr( 'position-' . $options['position'] ); ?>">
            <div class="symbols-container-header">
                <h4 class="symbols-title">
                    <?php echo esc_html( $options['title'] ); ?>
                    <?php if ( $options['show_count'] ) : ?>
                        <span class="symbols-count">(<?php echo count( $symbols ); ?>)</span>
                    <?php endif; ?>
                </h4>
                <?php if ( $options['show_reset'] && ! empty( $options['reset_url'] ) ) : ?>
                    <a href="<?php echo esc_url( $options['reset_url'] ); ?>" 
                       class="button button-secondary button-small symbols-reset"
                       onclick="return confirm('<?php esc_html_e( 'Clear all selected symbols?', 'tradepress' ); ?>')">
                        <?php esc_html_e( 'Clear', 'tradepress' ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="symbols-tags">
                <?php foreach ( $symbols as $symbol ) : ?>
                    <span class="symbol-tag"><?php echo esc_html( $symbol ); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}