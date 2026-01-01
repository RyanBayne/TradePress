<?php
/**
 * TradePress AI Context Manager
 *
 * This class manages context for the AI assistant to provide
 * more relevant and personalized suggestions based on the user's
 * current development activities.
 *
 * @package TradePress\AI
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_AI_Context_Manager
 * 
 * Manages context information for the AI Assistant to provide
 * more relevant suggestions based on the user's current work.
 *
 * @since 1.0.0
 */
class TradePress_AI_Context_Manager {
    
    /**
     * Current context data
     *
     * @var array
     */
    private $context = array();
    
    /**
     * History of contexts
     *
     * @var array
     */
    private $context_history = array();
    
    /**
     * Maximum history size
     *
     * @var int
     */
    private $max_history_size = 10;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_context();
    }
    
    /**
     * Load context from storage
     */
    private function load_context() {
        // Load from user meta or transient
        $user_id = get_current_user_id();
        $saved_context = get_user_meta($user_id, 'tradepress_ai_context', true);
        
        if (!empty($saved_context)) {
            $this->context = $saved_context;
        } else {
            $this->initialize_default_context();
        }
        
        $saved_history = get_user_meta($user_id, 'tradepress_ai_context_history', true);
        if (!empty($saved_history)) {
            $this->context_history = $saved_history;
        }
    }
    
    /**
     * Save context to storage
     */
    private function save_context() {
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'tradepress_ai_context', $this->context);
        update_user_meta($user_id, 'tradepress_ai_context_history', $this->context_history);
    }
    
    /**
     * Initialize default context
     */
    private function initialize_default_context() {
        $this->context = array(
            'current_file' => '',
            'current_feature' => '',
            'recently_viewed_files' => array(),
            'recent_activities' => array(),
            'active_development_areas' => array(),
            'last_updated' => current_time('mysql')
        );
    }
    
    /**
     * Update current file context
     *
     * @param string $file_path Path to the current file
     */
    public function update_current_file($file_path) {
        // Save previous context to history
        $this->add_to_context_history($this->context);
        
        // Update current file
        $this->context['current_file'] = $file_path;
        
        // Add to recently viewed files
        $recently_viewed = $this->context['recently_viewed_files'];
        // Remove if already in the list
        $key = array_search($file_path, $recently_viewed);
        if ($key !== false) {
            unset($recently_viewed[$key]);
        }
        
        // Add to the beginning and keep only the last 10
        array_unshift($recently_viewed, $file_path);
        $this->context['recently_viewed_files'] = array_slice($recently_viewed, 0, 10);
        
        // Detect feature based on file path
        $this->detect_feature_from_file($file_path);
        
        $this->context['last_updated'] = current_time('mysql');
        $this->save_context();
    }
    
    /**
     * Add activity to context
     *
     * @param string $activity_type Type of activity (e.g., 'edit', 'view', 'debug')
     * @param string $description Description of the activity
     * @param array $metadata Additional metadata about the activity
     */
    public function add_activity($activity_type, $description, $metadata = array()) {
        $activity = array(
            'type' => $activity_type,
            'description' => $description,
            'timestamp' => current_time('mysql'),
            'metadata' => $metadata
        );
        
        array_unshift($this->context['recent_activities'], $activity);
        $this->context['recent_activities'] = array_slice($this->context['recent_activities'], 0, 20);
        
        // Update active development areas based on activity
        $this->update_active_development_areas($activity);
        
        $this->context['last_updated'] = current_time('mysql');
        $this->save_context();
    }
    
    /**
     * Update active development areas based on activity
     *
     * @param array $activity Activity data
     */
    private function update_active_development_areas($activity) {
        // Extract development area from activity
        $area = '';
        
        if (isset($activity['metadata']['area'])) {
            $area = $activity['metadata']['area'];
        } elseif (isset($activity['metadata']['file_path'])) {
            // Try to determine area from file path
            $file_path = $activity['metadata']['file_path'];
            if (strpos($file_path, '/api/') !== false) {
                $area = 'API';
            } elseif (strpos($file_path, '/admin/') !== false) {
                $area = 'Admin UI';
            } elseif (strpos($file_path, '/includes/') !== false) {
                $area = 'Core';
            }
        }
        
        if (!empty($area)) {
            // Add to active areas if not already present
            if (!isset($this->context['active_development_areas'][$area])) {
                $this->context['active_development_areas'][$area] = array(
                    'count' => 1,
                    'first_seen' => current_time('mysql'),
                    'last_seen' => current_time('mysql')
                );
            } else {
                $this->context['active_development_areas'][$area]['count']++;
                $this->context['active_development_areas'][$area]['last_seen'] = current_time('mysql');
            }
        }
    }
    
    /**
     * Detect which feature a file belongs to
     *
     * @param string $file_path Path to the file
     */
    private function detect_feature_from_file($file_path) {
        // This would be implemented by analyzing the file path and contents
        // to determine which feature it's associated with
        
        // For now, just a placeholder implementation
        if (strpos($file_path, 'api') !== false) {
            $this->context['current_feature'] = 'API Integration';
        } elseif (strpos($file_path, 'trading') !== false) {
            $this->context['current_feature'] = 'Trading';
        } elseif (strpos($file_path, 'ai') !== false) {
            $this->context['current_feature'] = 'AI Assistant';
        } else {
            $this->context['current_feature'] = '';
        }
    }
    
    /**
     * Add current context to history
     *
     * @param array $context Context to add to history
     */
    private function add_to_context_history($context) {
        // Only save if we have a meaningful context
        if (empty($context['current_file']) && empty($context['current_feature'])) {
            return;
        }
        
        // Add timestamp if not present
        if (!isset($context['timestamp'])) {
            $context['timestamp'] = current_time('mysql');
        }
        
        // Add to history
        array_unshift($this->context_history, $context);
        
        // Limit history size
        $this->context_history = array_slice($this->context_history, 0, $this->max_history_size);
    }
    
    /**
     * Get current context
     *
     * @return array Current context data
     */
    public function get_context() {
        return $this->context;
    }
    
    /**
     * Get context history
     *
     * @return array Context history
     */
    public function get_context_history() {
        return $this->context_history;
    }
    
    /**
     * Clear context and history
     */
    public function clear_context() {
        $this->initialize_default_context();
        $this->context_history = array();
        $this->save_context();
    }
    
    /**
     * Determine relevant suggestions based on current context
     *
     * @return array Array of suggestion types that are relevant
     */
    public function get_relevant_suggestion_types() {
        $relevant_types = array();
        
        // Determine based on current file
        if (!empty($this->context['current_file'])) {
            $file_path = $this->context['current_file'];
            $extension = pathinfo($file_path, PATHINFO_EXTENSION);
            
            switch ($extension) {
                case 'php':
                    $relevant_types[] = 'code_quality';
                    $relevant_types[] = 'php_best_practices';
                    break;
                case 'js':
                    $relevant_types[] = 'javascript_optimization';
                    break;
                case 'css':
                    $relevant_types[] = 'css_best_practices';
                    break;
            }
            
            // Check file content type
            if (strpos($file_path, 'api') !== false) {
                $relevant_types[] = 'api_testing';
            }
            
            if (strpos($file_path, 'test') !== false) {
                $relevant_types[] = 'test_data_generation';
            }
        }
        
        // Determine based on current feature
        if (!empty($this->context['current_feature'])) {
            $feature = $this->context['current_feature'];
            
            if ($feature === 'API Integration') {
                $relevant_types[] = 'api_documentation';
                $relevant_types[] = 'api_error_handling';
            }
            
            if ($feature === 'Trading') {
                $relevant_types[] = 'trading_best_practices';
                $relevant_types[] = 'security_considerations';
            }
        }
        
        // Add general suggestion types
        $relevant_types[] = 'architecture_suggestions';
        $relevant_types[] = 'performance_optimization';
        
        return array_unique($relevant_types);
    }
    
    /**
     * Get the most active development area
     *
     * @return string|null The most active area or null if none
     */
    public function get_most_active_area() {
        $areas = $this->context['active_development_areas'];
        
        if (empty($areas)) {
            return null;
        }
        
        $max_count = 0;
        $most_active = null;
        
        foreach ($areas as $area => $data) {
            if ($data['count'] > $max_count) {
                $max_count = $data['count'];
                $most_active = $area;
            }
        }
        
        return $most_active;
    }
}
