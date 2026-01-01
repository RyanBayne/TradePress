<?php
/**
 * TradePress Scoring Directives Loader and Manager
 *
 * This file contains functions for loading, saving, and managing scoring directives.
 * It replaces functionality previously in tradepress-scoring-directives.php
 * and scoring-directives-registry.php.
 *
 * @package    TradePress
 * @subpackage TradePress/Scoring
 * @since      1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Ensure directive definitions are available.
$directives_register_file = __DIR__ . '/directives-register.php';
if ( file_exists( $directives_register_file ) ) {
    require_once $directives_register_file;
} else {
    // Fallback or error logging if the definitions file is missing.
    if ( defined('WP_DEBUG') && WP_DEBUG ) {
        error_log('TradePress Error: directives-register.php not found.');
    }
    if ( ! function_exists('tradepress_get_all_system_directives') ) {
        function tradepress_get_all_system_directives() { return array(); }
    }
}


/**
 * Get saved scoring directives from options.
 * (Originally tradepress_get_scoring_directives from tradepress-scoring-directives.php)
 *
 * @return array The scoring directives.
 */
function tradepress_get_configured_scoring_directives() {
    return apply_filters('tradepress_configured_scoring_directives', 
        get_option('tradepress_scoring_directives', array())
    );
}

/**
 * Save scoring directives with sanitization.
 * (Originally tradepress_save_scoring_directives from tradepress-scoring-directives.php)
 *
 * @param array $directives The directives to save.
 * @return bool True if successful, false otherwise.
 */
function tradepress_save_scoring_directives_config($directives) {
    $sanitized = array();
    
    foreach ($directives as $key => $directive) {
        $sanitized_directive = array(
            'enabled' => isset($directive['enabled']) ? (bool)$directive['enabled'] : false, // Retained from original for compatibility
            'parameters' => array()
        );
        
        if (isset($directive['name'])) {
            $sanitized_directive['name'] = sanitize_text_field($directive['name']);
        }
        if (isset($directive['description'])) {
            $sanitized_directive['description'] = sanitize_text_field($directive['description']);
        }
        if (isset($directive['parameters']) && is_array($directive['parameters'])) {
            foreach ($directive['parameters'] as $param_key => $param_value) {
                if (is_numeric($param_value)) {
                    $sanitized_directive['parameters'][$param_key] = floatval($param_value);
                } else {
                    $sanitized_directive['parameters'][$param_key] = sanitize_text_field($param_value);
                }
            }
        }
        if (isset($directive['technical_indicator'])) {
            $sanitized_directive['technical_indicator'] = sanitize_key($directive['technical_indicator']);
        }
        if (isset($directive['category'])) {
            $sanitized_directive['category'] = sanitize_key($directive['category']);
        }
        if (isset($directive['weight'])) {
            $sanitized_directive['weight'] = intval($directive['weight']);
        }
        if (isset($directive['bullish'])) {
            $sanitized_directive['bullish'] = sanitize_text_field($directive['bullish']);
        }
        if (isset($directive['bearish'])) {
            $sanitized_directive['bearish'] = sanitize_text_field($directive['bearish']);
        }
        if (isset($directive['active'])) {
            $sanitized_directive['active'] = (bool)$directive['active'];
        }
        // Preserve any other fields that might be part of the directive structure
        foreach ($directive as $field_key => $field_value) {
            if (!isset($sanitized_directive[$field_key])) {
                 $sanitized_directive[$field_key] = is_string($field_value) ? sanitize_text_field($field_value) : $field_value;
            }
        }
        $sanitized[$key] = $sanitized_directive;
    }
    
    $result = update_option('tradepress_scoring_directives', $sanitized);
    
    if ($result) {
        do_action('tradepress_scoring_directives_updated', $sanitized);
    }
    
    return $result;
}

/**
 * Get a specific configured scoring directive by ID from options.
 * (Originally tradepress_get_scoring_directive from tradepress-scoring-directives.php)
 *
 * @param string $id The directive ID.
 * @return array|null The directive or null if not found.
 */
function tradepress_get_configured_directive_by_id($id) {
    $directives = tradepress_get_configured_scoring_directives();
    return isset($directives[$id]) ? $directives[$id] : null;
}

/**
 * Get a specific system directive by ID.
 *
 * @param string $id The directive ID.
 * @return array|null The system directive definition or null if not found.
 */
function tradepress_get_system_directive_by_id($id) {
    $system_directives = tradepress_get_all_system_directives();
    return isset($system_directives[$id]) ? $system_directives[$id] : null;
}

/**
 * Get all directives, merging system defaults with saved configurations.
 * This is the primary function for retrieving directives for use in the plugin.
 *
 * @return array Merged directives.
 */
function tradepress_get_all_directives() {
    $system_directives = tradepress_get_all_system_directives();
    $configured_directives = tradepress_get_configured_scoring_directives();
    $merged_directives = array();

    // Start with system directives and override with configured ones
    foreach ($system_directives as $id => $system_directive) {
        $merged_directives[$id] = $system_directive; // Base
        if (isset($configured_directives[$id])) {
            // Override with specifically configured values
            foreach ($configured_directives[$id] as $key => $value) {
                $merged_directives[$id][$key] = $value;
            }
        }
        // Ensure 'active' status is explicitly set, defaulting from system if not configured
        if (!isset($configured_directives[$id]['active']) && isset($system_directive['active'])) {
            $merged_directives[$id]['active'] = $system_directive['active'];
        } elseif (!isset($merged_directives[$id]['active'])) {
            $merged_directives[$id]['active'] = false; // Default to inactive if no info
        }
    }

    // Add any directives that are in configured_directives but not in system_directives (e.g. custom/user-added)
    foreach ($configured_directives as $id => $configured_directive) {
        if (!isset($merged_directives[$id])) {
            $merged_directives[$id] = $configured_directive;
            if (empty($merged_directives[$id]['name'])) {
                $merged_directives[$id]['name'] = ucfirst(str_replace('_', ' ', $id)); // Default name
            }
            if (!isset($merged_directives[$id]['active'])) {
                $merged_directives[$id]['active'] = false; // Default to inactive
            }
        }
    }
    return $merged_directives;
}

/**
 * Get a specific directive by ID, merging system defaults with saved configuration.
 *
 * @param string $id The directive ID.
 * @return array|null The merged directive or null if not found.
 */
function tradepress_get_directive_by_id($id) {
    $all_directives = tradepress_get_all_directives();
    return isset($all_directives[$id]) ? $all_directives[$id] : null;
}

/**
 * Get available technical indicators that aren't yet represented as scoring directives in the saved options.
 * (Originally tradepress_get_missing_technical_indicators from tradepress-scoring-directives.php)
 *
 * @return array New technical indicators to be added.
 */
function tradepress_get_missing_configured_technical_indicators() {
    $all_system_directives = tradepress_get_all_system_directives();
    $saved_directives = tradepress_get_configured_scoring_directives();
    
    $missing_indicators = array();
    foreach ($all_system_directives as $id => $directive) {
        if (!isset($saved_directives[$id])) {
            $missing_indicators[$id] = $directive;
        }
    }
    return $missing_indicators;
}

/**
 * Ensures a directive with default settings exists in the saved options.
 * (Replaces tradepress_register_scoring_directive from tradepress-scoring-directives.php)
 *
 * @param string $id The directive ID.
 * @param array $directive_defaults Default settings for the directive.
 * @return bool True if saved/updated, false otherwise.
 */
function tradepress_ensure_directive_in_options($id, $directive_defaults) {
    $directives = tradepress_get_configured_scoring_directives();
    
    if (!isset($directives[$id])) {
        $directives[$id] = $directive_defaults;
        return tradepress_save_scoring_directives_config($directives);
    }
    return false; // Already exists
}

/**
 * Apply all active directives to calculate a score.
 * (Originally apply_directives from TradePress_Scoring_Directives_Registry)
 *
 * @param int $score The current score.
 * @param array $symbol_data Data for the symbol being scored.
 * @return int The final score.
 */
function tradepress_apply_score_directives_to_symbol($score, $symbol_data) {
    $total_weight = 0;
    $weighted_score = 0;
    $active_directives = tradepress_get_all_directives();

    foreach ($active_directives as $id => $directive) {
        if (!empty($directive['active']) && $directive['active']) {
            // In a real implementation, we'd call the actual scoring function
            // for this directive based on $symbol_data and $directive parameters.
            // For now, using a placeholder.
            $directive_score_value = mt_rand(0, 100); // Placeholder for actual directive calculation
            $weight = isset($directive['weight']) ? (int)$directive['weight'] : 0;
            
            $weighted_score += $directive_score_value * $weight;
            $total_weight += $weight;
        }
    }
    
    if ($total_weight > 0) {
        $final_score = round($weighted_score / $total_weight);
    } else {
        $final_score = $score; // Or a default neutral score like 50 if $score is 0
    }
    
    return $final_score;
}

/**
 * Get directive settings in a specific format for saving to options or for UI.
 * (Originally get_directive_settings from TradePress_Scoring_Directives_Registry)
 *
 * @return array
 */
function tradepress_get_simplified_directive_settings() {
    $settings = array();
    $all_directives = tradepress_get_all_directives();
    
    foreach ($all_directives as $id => $directive) {
        $settings[$id] = array(
            'weight' => isset($directive['weight']) ? intval($directive['weight']) : 0,
            'active' => !empty($directive['active']) && $directive['active']
        );
    }
    return $settings;
}
