<?php
/**
 * TradePress - Load Public Assets 
 *
 * Load public-facing js, css, images and fonts. 
 *
 * @author   Ryan Bayne
 * @category Loading
 * @package  TradePress/Loading
 * @since    1.0.0
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TradePress_Public_Assets' ) ) :

/**
 * TradePress_Public_Assets Class.
 */
class TradePress_Public_Assets {

    /**
     * Hook in methods.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'public_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ) );
    }

    /**
     * Enqueue styles for the public side.
     */
    public function public_styles() {
        // Enqueue symbol template styles
        if ( is_singular( 'tradepress_symbol' ) ) {
            wp_enqueue_style(
                'tradepress-symbol',
                TRADEPRESS_PLUGIN_URL . 'assets/css/templates/symbol.css',
                array(),
                TRADEPRESS_VERSION
            );
        }
    }

    /**
     * Enqueue scripts for the public side.
     */
    public function public_scripts() {
        // Example for future public scripts
        // if ( is_singular( 'tradepress_symbol' ) ) {
        //     wp_enqueue_script( 'tradepress-symbol-script', ... );
        // }
    }
}

endif;

new TradePress_Public_Assets();
