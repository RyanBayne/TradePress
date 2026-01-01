<?php
/**
 * TradePress Step Wizard
 *
 * Reusable step-by-step process system for creating wizards throughout the plugin.
 *
 * @package TradePress/Core
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TradePress_Step_Wizard {

    /**
     * Wizard ID
     *
     * @var string
     */
    private $wizard_id;

    /**
     * Steps configuration
     *
     * @var array
     */
    private $steps = array();

    /**
     * Current step
     *
     * @var int
     */
    private $current_step = 1;

    /**
     * Session data
     *
     * @var array
     */
    private $session_data = array();

    /**
     * Constructor
     *
     * @param string $wizard_id Unique wizard identifier
     * @param array  $steps     Steps configuration
     */
    public function __construct( $wizard_id, $steps = array() ) {
        $this->wizard_id = $wizard_id;
        $this->steps = $steps;
        $this->current_step = isset( $_GET[ $wizard_id . '_step' ] ) ? intval( $_GET[ $wizard_id . '_step' ] ) : 1;
        $this->load_session_data();
    }

    /**
     * Add step to wizard
     *
     * @param int    $step_number Step number
     * @param string $title       Step title
     * @param string $callback    Callback function to render step content
     * @param array  $options     Additional step options
     */
    public function add_step( $step_number, $title, $callback, $options = array() ) {
        $this->steps[ $step_number ] = array(
            'title' => $title,
            'callback' => $callback,
            'options' => wp_parse_args( $options, array(
                'required' => true,
                'accessible' => false
            ) )
        );
    }

    /**
     * Render wizard navigation
     *
     * @return string HTML output
     */
    public function render_navigation() {
        $progress_status = $this->get_progress_status();
        
        ob_start();
        ?>
        <div class="tradepress-wizard-navigation">
            <div class="step-tabs">
                <?php foreach ( $progress_status as $step_num => $step_info ) : ?>
                    <?php 
                    $tab_class = 'step-tab step-' . $step_info['status'];
                    if ( $this->current_step === $step_num ) {
                        $tab_class .= ' active';
                    }
                    
                    $is_accessible = in_array( $step_info['status'], array( 'accessible', 'completed' ) );
                    $step_url = $is_accessible ? 
                        $this->get_step_url( $step_num ) : 
                        '#';
                    ?>
                    <a href="<?php echo esc_url( $step_url ); ?>" 
                       class="<?php echo esc_attr( $tab_class ); ?>"
                       <?php echo ! $is_accessible ? 'onclick="return false;"' : ''; ?>>
                        <span class="step-number"><?php echo $step_num; ?></span>
                        <span class="step-title"><?php echo esc_html( $step_info['title'] ); ?></span>
                        <?php if ( $step_info['status'] === 'completed' ) : ?>
                            <span class="dashicons dashicons-yes-alt"></span>
                        <?php elseif ( $step_info['status'] === 'locked' ) : ?>
                            <span class="dashicons dashicons-lock"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render current step content
     *
     * @return string HTML output
     */
    public function render_current_step() {
        if ( ! $this->validate_step_access( $this->current_step ) ) {
            return '<div class="notice notice-error"><p>' . 
                   esc_html__( 'You must complete previous steps first.', 'tradepress' ) . 
                   '</p></div>';
        }

        if ( ! isset( $this->steps[ $this->current_step ] ) ) {
            return '<div class="notice notice-error"><p>' . 
                   esc_html__( 'Invalid step number.', 'tradepress' ) . 
                   '</p></div>';
        }

        $step = $this->steps[ $this->current_step ];
        
        if ( is_callable( $step['callback'] ) ) {
            return call_user_func( $step['callback'], $this->current_step, $this->session_data );
        }

        return '<p>' . esc_html__( 'Step content not available.', 'tradepress' ) . '</p>';
    }

    /**
     * Process step form submission
     *
     * @param array $data Form data
     * @return bool Success status
     */
    public function process_step( $data ) {
        $sanitized_data = $this->sanitize_step_data( $this->current_step, $data );
        
        // Save step data
        $this->session_data['steps'][ $this->current_step ] = $sanitized_data;
        
        // Mark step as completed
        if ( ! in_array( $this->current_step, $this->session_data['completed_steps'] ) ) {
            $this->session_data['completed_steps'][] = $this->current_step;
        }
        
        // Update current step
        $this->session_data['current_step'] = $this->current_step + 1;
        
        return $this->save_session_data();
    }

    /**
     * Validate step access
     *
     * @param int $step Step number
     * @return bool True if user can access step
     */
    public function validate_step_access( $step ) {
        // Step 1 is always accessible
        if ( $step === 1 ) {
            return true;
        }

        // Check if previous step is completed
        return in_array( $step - 1, $this->session_data['completed_steps'] );
    }

    /**
     * Get progress status for all steps
     *
     * @return array Progress status array
     */
    public function get_progress_status() {
        $completed_steps = $this->session_data['completed_steps'];
        $current_step = $this->session_data['current_step'];

        $status = array();
        foreach ( $this->steps as $step_num => $step_info ) {
            if ( in_array( $step_num, $completed_steps ) ) {
                $status[ $step_num ] = array(
                    'title' => $step_info['title'],
                    'status' => 'completed'
                );
            } elseif ( $step_num <= $current_step ) {
                $status[ $step_num ] = array(
                    'title' => $step_info['title'],
                    'status' => 'accessible'
                );
            } else {
                $status[ $step_num ] = array(
                    'title' => $step_info['title'],
                    'status' => 'locked'
                );
            }
        }

        return $status;
    }

    /**
     * Get step URL
     *
     * @param int $step_num Step number
     * @return string Step URL
     */
    public function get_step_url( $step_num ) {
        $current_url = remove_query_arg( $this->wizard_id . '_step' );
        return add_query_arg( $this->wizard_id . '_step', $step_num, $current_url );
    }

    /**
     * Clear wizard session
     *
     * @return bool Success status
     */
    public function clear_session() {
        $this->session_data = $this->get_default_session_data();
        $this->current_step = 1;
        return delete_transient( $this->get_session_key() );
    }

    /**
     * Load session data
     */
    private function load_session_data() {
        $session_data = get_transient( $this->get_session_key() );
        
        if ( $session_data ) {
            $this->session_data = $session_data;
        } else {
            $this->session_data = $this->get_default_session_data();
        }
    }

    /**
     * Save session data
     *
     * @return bool Success status
     */
    private function save_session_data() {
        return set_transient( 
            $this->get_session_key(), 
            $this->session_data, 
            24 * HOUR_IN_SECONDS 
        );
    }

    /**
     * Get session key
     *
     * @return string Session key
     */
    private function get_session_key() {
        return "tradepress_wizard_{$this->wizard_id}_" . get_current_user_id();
    }

    /**
     * Get default session data
     *
     * @return array Default session data
     */
    private function get_default_session_data() {
        return array(
            'wizard_id' => $this->wizard_id,
            'current_step' => 1,
            'completed_steps' => array(),
            'steps' => array(),
            'started_at' => current_time( 'mysql' )
        );
    }

    /**
     * Sanitize step data
     *
     * @param int   $step Step number
     * @param array $data Raw form data
     * @return array Sanitized data
     */
    private function sanitize_step_data( $step, $data ) {
        // Basic sanitization - can be overridden by child classes
        $sanitized = array();
        
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
            } else {
                $sanitized[ $key ] = sanitize_text_field( $value );
            }
        }
        
        return $sanitized;
    }

    /**
     * Get session data
     *
     * @return array Session data
     */
    public function get_session_data() {
        return $this->session_data;
    }

    /**
     * Get step data
     *
     * @param int $step Step number
     * @return array|null Step data
     */
    public function get_step_data( $step ) {
        return isset( $this->session_data['steps'][ $step ] ) ? $this->session_data['steps'][ $step ] : null;
    }

    /**
     * Check if step is completed
     *
     * @param int $step Step number
     * @return bool True if step is completed
     */
    public function is_step_completed( $step ) {
        return in_array( $step, $this->session_data['completed_steps'] );
    }
}