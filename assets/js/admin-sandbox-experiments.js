/**
 * Sandbox Experiments JS
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Handle experiment button clicks
    $('.experiment-button').on('click', function() {
        var experiment = $(this).data('experiment');
        var $results = $('#experiment-results');
        var $output = $('#experiment-output');
        
        // Show loading state
        $results.show();
        $output.html('Running experiment: ' + experiment + '...');
        
        // In a real implementation, this would make an AJAX call to run the experiment
        // For now, just show some example output
        setTimeout(function() {
            switch(experiment) {
                case 'memory_profile':
                    $output.html('Memory Profile Results:\n\nMemory Limit: 256M\nCurrent Usage: 24.5MB\nPeak Usage: 28.2MB');
                    break;
                case 'database_stats':
                    $output.html('Database Statistics Results:\n\nTotal Tables: 12\nTotal Size: 8.4MB\nLargest Table: wp_posts (4.2MB)');
                    break;
                case 'performance_test':
                    $output.html('Performance Test Results:\n\nAverage Load Time: 0.842s\nQuery Time: 0.124s\nPHP Processing: 0.718s');
                    break;
                default:
                    $output.html('Unknown experiment type');
            }
        }, 1000);
    });
});
