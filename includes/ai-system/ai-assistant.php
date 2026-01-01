<?php
/**
 * TradePress AI Assistant
 *
 * This class provides the AI assistant functionality for the plugin,
 * helping developers with proactive suggestions, code analysis,
 * and automated diagnostics.
 *
 * @package TradePress\AI
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TradePress_AI_Assistant
 * 
 * Provides AI assistant functionality including proactive suggestions,
 * code analysis, and automated diagnostics for developers.
 *
 * @since 1.0.0
 */
class TradePress_AI_Assistant {
    
    /**
     * Context manager instance
     *
     * @var TradePress_AI_Context_Manager
     */
    private $context_manager;
    
    /**
     * Code analyzer instance
     *
     * @var TradePress_AI_Code_Analyzer
     */
    private $code_analyzer;
    
    /**
     * Diagnostics instance
     *
     * @var TradePress_AI_Diagnostics
     */
    private $diagnostics;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->context_manager = new TradePress_AI_Context_Manager();
        $this->code_analyzer = new TradePress_AI_Code_Analyzer();
        $this->diagnostics = new TradePress_AI_Diagnostics($this->context_manager);
        
        // Register hooks
        add_action('admin_init', array($this, 'init'));
    }
    
    /**
     * Initialize the AI assistant
     */
    public function init() {
        // Register AJAX handlers
        add_action('wp_ajax_tradepress_ai_request', array($this, 'process_ai_request'));
        add_action('wp_ajax_tradepress_ai_file_analysis', array($this, 'process_file_analysis'));
        add_action('wp_ajax_tradepress_ai_diagnostics', array($this, 'process_diagnostics'));
        add_action('wp_ajax_tradepress_ai_suggestion_feedback', array($this, 'process_suggestion_feedback'));
        
        // Add admin footer script to monitor activity
        add_action('admin_footer', array($this, 'add_activity_monitor_script'));
    }
    
    /**
     * Process AI request
     */
    public function process_ai_request() {
        // Check nonce
        check_ajax_referer('tradepress_ai_nonce', 'nonce');
        
        $request_type = sanitize_text_field($_POST['request_type'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        $context = isset($_POST['context']) ? (array)$_POST['context'] : array();
        
        $response = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        // Update context with this request
        if (!empty($context['file_path'])) {
            $this->context_manager->update_current_file($context['file_path']);
        }
        
        $this->context_manager->add_activity('ai_request', 'User made an AI request', array(
            'request_type' => $request_type,
            'file_path' => $context['file_path'] ?? ''
        ));
        
        // Process based on request type
        switch ($request_type) {
            case 'code_analysis':
                $response = $this->handle_code_analysis_request($content, $context);
                break;
                
            case 'diagnostics':
                $response = $this->handle_diagnostics_request($context);
                break;
                
            case 'suggestion':
                $response = $this->handle_suggestion_request($content, $context);
                break;
                
            case 'documentation':
                $response = $this->handle_documentation_request($content, $context);
                break;
                
            default:
                $response['message'] = 'Unknown request type';
                break;
        }
        
        wp_send_json($response);
    }
    
    /**
     * Process file analysis request
     */
    public function process_file_analysis() {
        // Check nonce
        check_ajax_referer('tradepress_ai_nonce', 'nonce');
        
        $file_path = sanitize_text_field($_POST['file_path'] ?? '');
        
        $response = array(
            'success' => false,
            'message' => '',
            'suggestions' => array()
        );
        
        if (empty($file_path)) {
            $response['message'] = 'No file path provided';
            wp_send_json($response);
        }
        
        // Update context with this file
        $this->context_manager->update_current_file($file_path);
        $this->context_manager->add_activity('file_analysis', 'File analysis requested', array(
            'file_path' => $file_path
        ));
        
        // Analyze file
        $suggestions = $this->code_analyzer->analyze_file($file_path);
        
        $response['success'] = true;
        $response['message'] = 'File analyzed successfully';
        $response['suggestions'] = $suggestions;
        
        wp_send_json($response);
    }
    
    /**
     * Process diagnostics request
     */
    public function process_diagnostics() {
        // Check nonce
        check_ajax_referer('tradepress_ai_nonce', 'nonce');
        
        $diagnostic_type = sanitize_text_field($_POST['diagnostic_type'] ?? 'automated');
        $params = isset($_POST['params']) ? (array)$_POST['params'] : array();
        
        $response = array(
            'success' => false,
            'message' => '',
            'results' => array()
        );
        
        $this->context_manager->add_activity('diagnostics', 'Diagnostics requested', array(
            'type' => $diagnostic_type
        ));
        
        // Run diagnostics
        if ($diagnostic_type === 'automated') {
            $results = $this->diagnostics->run_automated_diagnostics();
        } else {
            $results = $this->diagnostics->run_specific_diagnostic($diagnostic_type, $params);
        }
        
        $response['success'] = true;
        $response['message'] = 'Diagnostics completed';
        $response['results'] = $results;
        
        wp_send_json($response);
    }
    
    /**
     * Process suggestion feedback
     */
    public function process_suggestion_feedback() {
        // Check nonce
        check_ajax_referer('tradepress_ai_nonce', 'nonce');
        
        $suggestion_id = sanitize_text_field($_POST['suggestion_id'] ?? '');
        $helpful = isset($_POST['helpful']) ? (bool)$_POST['helpful'] : false;
        $feedback = sanitize_textarea_field($_POST['feedback'] ?? '');
        
        $response = array(
            'success' => false,
            'message' => ''
        );
        
        if (empty($suggestion_id)) {
            $response['message'] = 'No suggestion ID provided';
            wp_send_json($response);
        }
        
        // Store feedback in user meta for now
        // In a real implementation, this would go to a dedicated table
        $user_id = get_current_user_id();
        $suggestion_feedback = get_user_meta($user_id, 'tradepress_ai_suggestion_feedback', true);
        if (!is_array($suggestion_feedback)) {
            $suggestion_feedback = array();
        }
        
        $suggestion_feedback[$suggestion_id] = array(
            'helpful' => $helpful,
            'feedback' => $feedback,
            'timestamp' => current_time('mysql')
        );
        
        update_user_meta($user_id, 'tradepress_ai_suggestion_feedback', $suggestion_feedback);
        
        $response['success'] = true;
        $response['message'] = 'Feedback recorded successfully';
        
        wp_send_json($response);
    }
    
    /**
     * Handle code analysis request
     *
     * @param string $content Code content to analyze
     * @param array $context Request context
     * @return array Response data
     */
    private function handle_code_analysis_request($content, $context) {
        $response = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        $file_path = $context['file_path'] ?? '';
        
        if (empty($file_path) && empty($content)) {
            $response['message'] = 'No code content or file path provided';
            return $response;
        }
        
        // If we have a file path, analyze that file directly
        if (!empty($file_path)) {
            $suggestions = $this->code_analyzer->analyze_file($file_path);
        } else {
            // Otherwise, analyze the provided content
            // This would require a different method that works with content directly
            // rather than a file path - just a placeholder for now
            $suggestions = array(
                array(
                    'type' => 'info',
                    'message' => 'Analyzing provided code content would be implemented here',
                    'severity' => 'low'
                )
            );
        }
        
        $response['success'] = true;
        $response['message'] = 'Code analyzed successfully';
        $response['data'] = array(
            'suggestions' => $suggestions
        );
        
        return $response;
    }
    
    /**
     * Handle diagnostics request
     *
     * @param array $context Request context
     * @return array Response data
     */
    private function handle_diagnostics_request($context) {
        $response = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        $results = $this->diagnostics->run_automated_diagnostics();
        
        $response['success'] = true;
        $response['message'] = 'Diagnostics completed';
        $response['data'] = array(
            'results' => $results
        );
        
        return $response;
    }
    
    /**
     * Handle suggestion request
     *
     * @param string $content Content to base suggestions on
     * @param array $context Request context
     * @return array Response data
     */
    private function handle_suggestion_request($content, $context) {
        $response = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        // Get relevant suggestion types based on context
        $relevant_types = $this->context_manager->get_relevant_suggestion_types();
        
        // Generate suggestions based on the relevant types
        // This would be a more complex implementation in reality
        $suggestions = array();
        
        foreach ($relevant_types as $type) {
            switch ($type) {
                case 'code_quality':
                    $suggestions[] = array(
                        'id' => 'code_quality_' . uniqid(),
                        'type' => 'code_quality',
                        'title' => 'Improve Code Quality',
                        'description' => 'Consider adding more comments to explain complex logic',
                        'actionable' => false,
                        'priority' => 'medium'
                    );
                    break;
                
                case 'api_testing':
                    $suggestions[] = array(
                        'id' => 'api_test_' . uniqid(),
                        'type' => 'api_testing',
                        'title' => 'Test API Endpoint',
                        'description' => 'Test this endpoint with various input combinations',
                        'actionable' => true,
                        'action' => 'generate_api_tests',
                        'priority' => 'high'
                    );
                    break;
                
                case 'architecture_suggestions':
                    $suggestions[] = array(
                        'id' => 'arch_' . uniqid(),
                        'type' => 'architecture',
                        'title' => 'Architecture Improvement',
                        'description' => 'Consider extracting this functionality into its own class',
                        'actionable' => false,
                        'priority' => 'medium'
                    );
                    break;
            }
        }
        
        $response['success'] = true;
        $response['message'] = 'Suggestions generated successfully';
        $response['data'] = array(
            'suggestions' => $suggestions
        );
        
        return $response;
    }
    
    /**
     * Handle documentation request
     *
     * @param string $content Content to document
     * @param array $context Request context
     * @return array Response data
     */
    private function handle_documentation_request($content, $context) {
        $response = array(
            'success' => false,
            'message' => '',
            'data' => array()
        );
        
        // This would implement documentation generation
        // Just a placeholder for now
        $documentation = "Documentation would be generated here for: " . substr($content, 0, 50) . "...";
        
        $response['success'] = true;
        $response['message'] = 'Documentation generated successfully';
        $response['data'] = array(
            'documentation' => $documentation
        );
        
        return $response;
    }
    
    /**
     * Add activity monitor script to admin footer
     */
    public function add_activity_monitor_script() {
        // Only on development and sandbox pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'tradepress_development') === false && strpos($screen->id, 'tradepress_sandbox') === false) {
            return;
        }
        
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Track page navigation
                $(document).on('click', 'a', function() {
                    var href = $(this).attr('href');
                    var text = $(this).text().trim();
                    
                    // Only track internal navigation
                    if (href && href.indexOf('#') !== 0 && href.indexOf('javascript:') !== 0) {
                        trackActivity('navigation', 'Clicked link: ' + text, {
                            'href': href,
                            'text': text
                        });
                    }
                });
                
                // Track tab changes
                $(document).on('click', '.nav-tab', function() {
                    var tabId = $(this).attr('href') || $(this).data('tab');
                    var tabText = $(this).text().trim();
                    
                    trackActivity('tab_change', 'Changed tab to: ' + tabText, {
                        'tab_id': tabId,
                        'tab_text': tabText
                    });
                });
                
                // Track form submissions
                $(document).on('submit', 'form', function() {
                    var formId = $(this).attr('id') || 'unknown';
                    
                    trackActivity('form_submission', 'Submitted form: ' + formId, {
                        'form_id': formId,
                        'form_action': $(this).attr('action') || 'unknown'
                    });
                });
                
                // Function to track activity
                function trackActivity(activity_type, description, metadata) {
                    // This would actually send the data to the server in a real implementation
                    console.log('Activity tracked:', activity_type, description, metadata);
                    
                    // For now, just log to console
                    // In a real implementation, this would be an AJAX call to store activity
                }
            });
        </script>
        <?php
    }
    
    /**
     * Get proactive suggestions based on current context
     *
     * @return array Array of suggestions
     */
    public function get_proactive_suggestions() {
        // Get current context
        $context = $this->context_manager->get_context();
        
        // Generate suggestions based on context
        $suggestions = array();
        
        // If we have a current file, analyze it
        if (!empty($context['current_file'])) {
            $file_suggestions = $this->code_analyzer->analyze_file($context['current_file']);
            
            // Convert to our suggestion format
            foreach ($file_suggestions as $fs) {
                if (isset($fs['type']) && isset($fs['message'])) {
                    $suggestions[] = array(
                        'id' => 'file_' . uniqid(),
                        'type' => $fs['type'],
                        'title' => isset($fs['severity']) ? ucfirst($fs['severity']) . ' ' . ucfirst($fs['type']) : ucfirst($fs['type']),
                        'description' => $fs['message'],
                        'actionable' => isset($fs['actionable']) ? $fs['actionable'] : false,
                        'action' => isset($fs['action']) ? $fs['action'] : '',
                        'priority' => isset($fs['severity']) ? $fs['severity'] : 'medium'
                    );
                }
            }
        }
        
        // Add architecture suggestions based on active development area
        $most_active_area = $this->context_manager->get_most_active_area();
        if ($most_active_area) {
            $suggestions[] = array(
                'id' => 'arch_' . uniqid(),
                'type' => 'architecture',
                'title' => $most_active_area . ' Architecture Review',
                'description' => 'You\'ve been working extensively in the ' . $most_active_area . ' area. Consider reviewing the architecture for optimization opportunities.',
                'actionable' => false,
                'priority' => 'medium'
            );
        }
        
        // Add diagnostic suggestions if we have enough context
        if (!empty($context['current_feature'])) {
            $diagnostic_results = $this->diagnostics->run_automated_diagnostics();
            
            // Convert any warnings or errors to suggestions
            foreach ($diagnostic_results as $dr) {
                if ((isset($dr['type']) && ($dr['type'] === 'warning' || $dr['type'] === 'error')) && isset($dr['message'])) {
                    $suggestions[] = array(
                        'id' => 'diag_' . uniqid(),
                        'type' => 'diagnostic',
                        'title' => isset($dr['component']) ? ucfirst($dr['component']) . ' ' . ucfirst($dr['type']) : ucfirst($dr['type']),
                        'description' => $dr['message'] . (isset($dr['suggestion']) ? "\n\nSuggestion: " . $dr['suggestion'] : ''),
                        'actionable' => isset($dr['actionable']) ? $dr['actionable'] : false,
                        'action' => isset($dr['action']) ? $dr['action'] : '',
                        'priority' => isset($dr['severity']) ? $dr['severity'] : 'medium'
                    );
                }
            }
        }
        
        return $suggestions;
    }
}
