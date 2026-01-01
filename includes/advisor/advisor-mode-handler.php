<?php
/**
 * TradePress Advisor Mode Handler
 *
 * Handles different advisor modes and their configurations.
 *
 * @package TradePress/Advisor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Advisor_Mode_Handler {

    /**
     * Get available advisor modes
     *
     * @return array Available modes with configurations
     */
    public function get_available_modes() {
        return array(
            'invest' => array(
                'title' => __( 'Invest Mode', 'tradepress' ),
                'description' => __( 'Investment trading analysis focusing on short-term opportunities. Buy and sell shares around earnings events and price targets.', 'tradepress' ),
                'steps' => array( 1, 2, 3, 4, 5, 6 ),
                'priority' => 1,
                'enabled' => true
            ),
            'day_trade' => array(
                'title' => __( 'Day Trade (CFD) Mode', 'tradepress' ),
                'description' => __( 'Short-term trading analysis emphasizing technical indicators, volatility, and support/resistance levels. (Coming Soon)', 'tradepress' ),
                'steps' => array( 1, 2, 3, 6 ),
                'priority' => 2,
                'enabled' => false
            ),
            'portfolio_review' => array(
                'title' => __( 'Portfolio Review Mode', 'tradepress' ),
                'description' => __( 'Analyze existing investments for hold/sell decisions and portfolio optimization. (Coming Soon)', 'tradepress' ),
                'steps' => array( 1, 3, 4, 5, 6 ),
                'priority' => 2,
                'enabled' => false
            ),
            'strategy_suggest' => array(
                'title' => __( 'Strategy Suggestion Mode', 'tradepress' ),
                'description' => __( 'Questionnaire-based approach to recommend trading strategies based on experience and goals. (Coming Soon)', 'tradepress' ),
                'steps' => array( 1 ),
                'priority' => 3,
                'enabled' => false
            ),
            'short_selling' => array(
                'title' => __( 'Short Selling Mode', 'tradepress' ),
                'description' => __( 'Identify declining stocks with negative catalysts for short selling opportunities. (Coming Soon)', 'tradepress' ),
                'steps' => array( 1, 2, 3, 5, 6 ),
                'priority' => 3,
                'enabled' => false
            )
        );
    }

    /**
     * Set advisor mode
     *
     * @param string $mode Mode identifier
     * @return bool Success status
     */
    public function set_mode( $mode ) {
        $available_modes = $this->get_available_modes();
        
        if ( ! isset( $available_modes[ $mode ] ) ) {
            return false;
        }

        // Mode is set through session management
        return true;
    }

    /**
     * Get steps for selected mode
     *
     * @param string $mode Mode identifier
     * @return array Array of step numbers for the mode
     */
    public function get_mode_steps( $mode ) {
        $available_modes = $this->get_available_modes();
        
        if ( ! isset( $available_modes[ $mode ] ) ) {
            return array( 1, 2, 3, 4, 5, 6 ); // Default to all steps
        }

        return $available_modes[ $mode ]['steps'];
    }

    /**
     * Get mode-specific settings
     *
     * @param string $mode Mode identifier
     * @return array Mode configuration
     */
    public function get_mode_settings( $mode ) {
        $available_modes = $this->get_available_modes();
        
        return isset( $available_modes[ $mode ] ) ? $available_modes[ $mode ] : array();
    }

    /**
     * Get mode display name
     *
     * @param string $mode Mode identifier
     * @return string Mode display name
     */
    public function get_mode_title( $mode ) {
        $settings = $this->get_mode_settings( $mode );
        return isset( $settings['title'] ) ? $settings['title'] : ucfirst( $mode );
    }

    /**
     * Check if mode is available
     *
     * @param string $mode Mode identifier
     * @return bool True if mode is available
     */
    public function is_mode_available( $mode ) {
        $available_modes = $this->get_available_modes();
        return isset( $available_modes[ $mode ] );
    }

    /**
     * Get step title for mode
     *
     * @param string $mode Mode identifier
     * @param int    $step Step number
     * @return string Step title
     */
    public function get_step_title( $mode, $step ) {
        $step_titles = array(
            1 => __( 'Mode Selection', 'tradepress' ),
            2 => __( 'Earnings Calendar', 'tradepress' ),
            3 => __( 'News Analysis', 'tradepress' ),
            4 => __( 'Price Forecasts', 'tradepress' ),
            5 => __( 'Economic Impact', 'tradepress' ),
            6 => __( 'Technical Analysis', 'tradepress' )
        );

        // Mode-specific step titles
        if ( $mode === 'day_trade' && $step === 6 ) {
            return __( 'Support & Resistance', 'tradepress' );
        }

        if ( $mode === 'short_selling' && $step === 3 ) {
            return __( 'Negative News', 'tradepress' );
        }

        return isset( $step_titles[ $step ] ) ? $step_titles[ $step ] : __( 'Unknown Step', 'tradepress' );
    }
}