<?php
/**
 * TradePress Advisor Session Management
 *
 * Handles session state management and data persistence for the advisor workflow.
 *
 * @package TradePress/Advisor
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Advisor_Session {

    /**
     * Session expiration time (24 hours)
     */
    const SESSION_EXPIRATION = 24 * HOUR_IN_SECONDS;

    /**
     * Current user ID
     *
     * @var int
     */
    private $user_id;

    /**
     * Constructor
     */
    public function __construct() {
        $this->user_id = get_current_user_id();
    }

    /**
     * Start a new advisor session
     *
     * @param string $mode The advisor mode (invest, day_trade, etc.)
     * @return bool Success status
     */
    public function start_session( $mode ) {
        $session_data = array(
            'mode' => $mode,
            'started_at' => current_time( 'timestamp' ),
            'current_step' => 1,
            'completed_steps' => array(),
            'selected_symbols' => array(),
            'technical_settings' => array()
        );

        return set_transient( 
            "tradepress_advisor_session_{$this->user_id}", 
            $session_data, 
            self::SESSION_EXPIRATION 
        );
    }

    /**
     * Get current session data
     *
     * @return array|false Session data or false if no session
     */
    public function get_session_data() {
        return get_transient( "tradepress_advisor_session_{$this->user_id}" );
    }

    /**
     * Update step data
     *
     * @param int   $step Step number
     * @param array $data Step data
     * @return bool Success status
     */
    public function update_step_data( $step, $data ) {
        // Save step-specific data
        $step_saved = set_transient(
            "tradepress_advisor_step_{$this->user_id}_{$step}",
            $data,
            self::SESSION_EXPIRATION
        );

        // Update session progress
        $session_data = $this->get_session_data();
        if ( $session_data ) {
            $session_data['completed_steps'][] = $step;
            $session_data['completed_steps'] = array_unique( $session_data['completed_steps'] );
            $session_data['current_step'] = max( $session_data['current_step'], $step + 1 );

            // Update selected symbols if provided
            if ( isset( $data['selected_symbols'] ) ) {
                $session_data['selected_symbols'] = array_merge(
                    $session_data['selected_symbols'],
                    $data['selected_symbols']
                );
                $session_data['selected_symbols'] = array_unique( $session_data['selected_symbols'] );
            }

            set_transient(
                "tradepress_advisor_session_{$this->user_id}",
                $session_data,
                self::SESSION_EXPIRATION
            );
        }

        return $step_saved;
    }

    /**
     * Get step data
     *
     * @param int $step Step number
     * @return array|false Step data or false if not found
     */
    public function get_step_data( $step ) {
        return get_transient( "tradepress_advisor_step_{$this->user_id}_{$step}" );
    }

    /**
     * Clear advisor session
     *
     * @return bool Success status
     */
    public function clear_session() {
        $session_data = $this->get_session_data();
        
        // Delete main session
        delete_transient( "tradepress_advisor_session_{$this->user_id}" );

        // Delete step data
        if ( $session_data && isset( $session_data['completed_steps'] ) ) {
            foreach ( $session_data['completed_steps'] as $step ) {
                delete_transient( "tradepress_advisor_step_{$this->user_id}_{$step}" );
            }
        }

        return true;
    }

    /**
     * Get selected symbols from all steps
     *
     * @return array Array of selected symbols
     */
    public function get_selected_symbols() {
        $session_data = $this->get_session_data();
        return $session_data ? $session_data['selected_symbols'] : array();
    }

    /**
     * Check if session exists
     *
     * @return bool True if session exists
     */
    public function has_session() {
        return $this->get_session_data() !== false;
    }

    /**
     * Get session mode
     *
     * @return string|false Session mode or false if no session
     */
    public function get_mode() {
        $session_data = $this->get_session_data();
        return $session_data ? $session_data['mode'] : false;
    }

    /**
     * Check if step is completed
     *
     * @param int $step Step number
     * @return bool True if step is completed
     */
    public function is_step_completed( $step ) {
        $session_data = $this->get_session_data();
        return $session_data && in_array( $step, $session_data['completed_steps'] );
    }
    
    /**
     * Set advisor mode
     *
     * @param string $mode Advisor mode
     * @return bool Success status
     */
    public function set_mode( $mode ) {
        $session_data = $this->get_session_data();
        if ( ! $session_data ) {
            return $this->start_session( $mode );
        }
        
        $session_data['mode'] = $mode;
        return set_transient(
            "tradepress_advisor_session_{$this->user_id}",
            $session_data,
            self::SESSION_EXPIRATION
        );
    }
    
    /**
     * Mark step as completed
     *
     * @param int $step Step number
     * @return bool Success status
     */
    public function mark_step_completed( $step ) {
        $session_data = $this->get_session_data();
        if ( ! $session_data ) {
            return false;
        }
        
        if ( ! in_array( $step, $session_data['completed_steps'] ) ) {
            $session_data['completed_steps'][] = $step;
        }
        
        return set_transient(
            "tradepress_advisor_session_{$this->user_id}",
            $session_data,
            self::SESSION_EXPIRATION
        );
    }
    
    /**
     * Set current step
     *
     * @param int $step Step number
     * @return bool Success status
     */
    public function set_current_step( $step ) {
        $session_data = $this->get_session_data();
        if ( ! $session_data ) {
            return false;
        }
        
        $session_data['current_step'] = $step;
        return set_transient(
            "tradepress_advisor_session_{$this->user_id}",
            $session_data,
            self::SESSION_EXPIRATION
        );
    }
    
    /**
     * Set selected symbols
     *
     * @param array $symbols Array of symbols
     * @return bool Success status
     */
    public function set_selected_symbols( $symbols ) {
        $session_data = $this->get_session_data();
        if ( ! $session_data ) {
            return false;
        }
        
        $session_data['selected_symbols'] = $symbols;
        return set_transient(
            "tradepress_advisor_session_{$this->user_id}",
            $session_data,
            self::SESSION_EXPIRATION
        );
    }
    
    /**
     * Set technical analysis settings
     *
     * @param array $settings Technical settings array
     * @return bool Success status
     */
    public function set_technical_settings( $settings ) {
        $session_data = $this->get_session_data();
        if ( ! $session_data ) {
            return false;
        }
        
        $session_data['technical_settings'] = $settings;
        return set_transient(
            "tradepress_advisor_session_{$this->user_id}",
            $session_data,
            self::SESSION_EXPIRATION
        );
    }
    
    /**
     * Get technical analysis settings
     *
     * @return array|false Technical settings or false if not set
     */
    public function get_technical_settings() {
        $session_data = $this->get_session_data();
        return $session_data && isset( $session_data['technical_settings'] ) ? $session_data['technical_settings'] : false;
    }
}