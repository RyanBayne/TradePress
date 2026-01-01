<?php
/**
 * Base class for all scoring directives
 *
 * @package TradePress/Classes
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TradePress_Scoring_Directive_Base Class
 */
abstract class TradePress_Scoring_Directive_Base {
    /**
     * Unique identifier for the directive
     * @var string
     */
    protected $id = '';
    
    /**
     * Display name for the directive
     * @var string
     */
    protected $name = '';
    
    /**
     * Description of how the directive works
     * @var string
     */
    protected $description = '';
    
    /**
     * Default weight of this directive as a percentage
     * @var int
     */
    protected $weight = 10;
    
    /**
     * Whether this directive is active by default
     * @var bool
     */
    protected $active = true;
    
    /**
     * Description of what values are considered bullish
     * @var string
     */
    protected $bullish_values = '';
    
    /**
     * Description of what values are considered bearish
     * @var string
     */
    protected $bearish_values = '';
    
    /**
     * Order in which to display this directive (lower = higher priority)
     * @var int
     */
    protected $priority = 10;
    
    /**
     * Maximum possible score for this directive
     * @var float
     */
    protected $max_score = 100;
    
    /**
     * Data freshness requirements for this directive
     * @var array
     */
    protected $data_freshness_requirements = array();
    
    /**
     * Get the directive's ID
     * @return string
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Get the directive's name
     * @return string
     */
    public function get_name() {
        return $this->name;
    }
    
    /**
     * Get the directive's description
     * @return string
     */
    public function get_description() {
        return $this->description;
    }
    
    /**
     * Get the directive's weight
     * @return int
     */
    public function get_weight() {
        // Get weight from option if available, otherwise use default
        $directives = get_option('tradepress_scoring_directives', array());
        if (isset($directives[$this->id]['weight'])) {
            return $directives[$this->id]['weight'];
        }
        return $this->weight;
    }
    
    /**
     * Get the directive's active status
     * @return bool
     */
    public function is_active() {
        // Get active status from option if available, otherwise use default
        $directives = get_option('tradepress_scoring_directives', array());
        if (isset($directives[$this->id]['active'])) {
            return (bool) $directives[$this->id]['active'];
        }
        return $this->active;
    }
    
    /**
     * Get the directive's bullish values description
     * @return string
     */
    public function get_bullish_values() {
        // Get bullish values from option if available, otherwise use default
        $directives = get_option('tradepress_scoring_directives', array());
        if (isset($directives[$this->id]['bullish'])) {
            return $directives[$this->id]['bullish'];
        }
        return $this->bullish_values;
    }
    
    /**
     * Get the directive's bearish values description
     * @return string
     */
    public function get_bearish_values() {
        // Get bearish values from option if available, otherwise use default
        $directives = get_option('tradepress_scoring_directives', array());
        if (isset($directives[$this->id]['bearish'])) {
            return $directives[$this->id]['bearish'];
        }
        return $this->bearish_values;
    }
    
    /**
     * Get the directive's display priority
     * @return int
     */
    public function get_priority() {
        return $this->priority;
    }
    
    /**
     * Get the directive's settings as an array
     * @return array
     */
    public function get_settings() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'weight' => $this->get_weight(),
            'active' => $this->is_active(),
            'bullish' => $this->get_bullish_values(),
            'bearish' => $this->get_bearish_values(),
            'priority' => $this->priority
        );
    }
    
    /**
     * Calculate a score for a given symbol based on the directive's logic
     * 
     * @param array $symbol_data Data for the symbol being scored
     * @param array $config Configuration parameters
     * @return array Score and details
     */
    abstract public function calculate_score($symbol_data, $config = array());
    
    /**
     * Log directive calculation if BugNet directives output is enabled
     * 
     * @param array $symbol_data Input data
     * @param array $result Calculation result
     */
    protected function log_calculation($symbol_data, $result) {
        if (get_option('bugnet_output_directives') === 'yes') {
            // Include directive logger if not already loaded
            $logger_path = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/ai-integration/testing/directive-logger.php';
            if (file_exists($logger_path)) {
                require_once $logger_path;
                
                if (class_exists('TradePress_Directive_Logger')) {
                    $symbol = isset($symbol_data['symbol']) ? $symbol_data['symbol'] : 'TEST';
                    $score = isset($result['score']) ? $result['score'] : 'N/A';
                    
                    // Extract meaningful details based on directive type
                    $details = '';
                    if ($this->id === 'adx' && isset($result['adx_value'])) {
                        $details = "ADX={$result['adx_value']}, Signal={$result['signal']}, Trend={$result['trend_strength']}";
                    } elseif (isset($result['signal'])) {
                        $details = "Signal={$result['signal']}";
                    } else {
                        $details = json_encode($result);
                    }
                    
                    $log_message = "CALC: {$this->id} | {$symbol} | Score={$score} | {$details}";
                    TradePress_Directive_Logger::log($log_message);
                }
            }
        }
    }
    
    /**
     * Get maximum possible score for this directive
     * 
     * @param array $config Configuration parameters
     * @return float Maximum possible score
     */
    abstract public function get_max_score($config = array());
    
    /**
     * Get explanation of how this directive works
     * 
     * @param array $config Configuration parameters
     * @return string Detailed explanation
     */
    abstract public function get_explanation($config = array());
}
