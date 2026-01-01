/**
 * TradePress Admin Features JavaScript
 *
 * Handles functionality for the Admin Features page.
 *
 * @package TradePress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Admin Features functionality
     */
    function initAdminFeatures() {
        console.log('TradePress Admin Features JS loaded');

        // Initialize accordion functionality if available
        if ($('.tradepress-accordion-container').length > 0) {
            initAccordions();
        }

        // Initialize feature filtering if available
        if ($('#feature-filter').length > 0) {
            initFeatureFiltering();
        }

        // Initialize feature status statistics
        updateFeatureStats();
    }

    /**
     * Initialize accordions for feature display
     */
    function initAccordions() {
        // Toggle individual accordion
        $('.tradepress-accordion-header').click(function() {
            var $content = $(this).next('.tradepress-accordion-content');
            $content.slideToggle();
            $(this).find('.tradepress-accordion-icon')
                   .toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        });
        
        // Expand all accordions
        $('.expand-all').click(function() {
            $('.tradepress-accordion-content').slideDown();
            $('.tradepress-accordion-icon')
                .removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');
        });
        
        // Collapse all accordions
        $('.collapse-all').click(function() {
            $('.tradepress-accordion-content').slideUp();
            $('.tradepress-accordion-icon')
                .removeClass('dashicons-arrow-up-alt2')
                .addClass('dashicons-arrow-down-alt2');
        });
    }

    /**
     * Initialize feature filtering
     */
    function initFeatureFiltering() {
        var $filter = $('#feature-filter');
        var $items = $('.feature-item');

        $filter.on('change', function() {
            var selectedStatus = $(this).val();

            if (selectedStatus === '') {
                // Show all items if no filter
                $items.show();
            } else {
                // Hide all items and show only those with matching status
                $items.hide();
                $('.feature-status-' + selectedStatus).show();
            }

            // Update statistics to reflect filtered view
            updateFilteredStats(selectedStatus);
        });
    }

    /**
     * Update filtered statistics
     * 
     * @param {string} filter Selected filter option
     */
    function updateFilteredStats(filter) {
        // Implementation for filtered statistics if needed
    }

    /**
     * Calculate and update feature statistics
     */
    function updateFeatureStats() {
        var totalFeatures = 0;
        var liveFeatures = 0;
        var demoFeatures = 0;
        var plannedFeatures = 0;
        
        // Find all status badges
        $('.feature-status-badge').each(function() {
            totalFeatures++;
            if ($(this).hasClass('status-live')) {
                liveFeatures++;
            } else if ($(this).hasClass('status-demo')) {
                demoFeatures++;
            } else if ($(this).hasClass('status-planned')) {
                plannedFeatures++;
            }
        });
        
        var completionRate = totalFeatures > 0 ? Math.round((liveFeatures / totalFeatures) * 100) : 0;
        
        // Update stats display if elements exist
        if ($('#total-features').length) {
            $('#total-features').text(totalFeatures);
            $('#live-features').text(liveFeatures);
            $('#demo-features').text(demoFeatures);
            if ($('#planned-features').length) {
                $('#planned-features').text(plannedFeatures);
            }
            $('#completion-rate').text(completionRate + '%');
            
            console.log('Feature statistics updated:', {
                total: totalFeatures,
                live: liveFeatures,
                demo: demoFeatures,
                planned: plannedFeatures,
                completion: completionRate + '%'
            });
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        initAdminFeatures();
    });

})(jQuery);
