<?php
/**
 * Social Platform Tab Helper Functions
 * 
 * Common helper functions used across social platform tab templates
 *
 * @created April 22, 2025
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Determine CSS class for status color based on status value
 *
 * @param string $status Status value (active, inactive, operational, etc.)
 * @return string CSS class name for the status color
 */
function get_status_color($status) {
    switch ($status) {
        case 'active':
        case 'operational':
            return 'status-green';
        case 'disruption':
        case 'maintenance':
            return 'status-orange';
        case 'inactive':
        case 'outage':
            return 'status-red';
        default:
            return 'status-grey';
    }
}

/**
 * Format JSON for readable display in alerts
 *
 * @param mixed $json The data to format as JSON
 * @return string Formatted JSON string with escaped quotes
 */
function format_json_for_display($json) {
    if (empty($json)) {
        return '';
    }
    
    $formatted = json_encode($json, JSON_PRETTY_PRINT);
    $formatted = str_replace('"', '\"', $formatted); // Escape quotes for JS
    
    return $formatted;
}