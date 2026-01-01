/**
 * TradePress Common Admin JavaScript
 *
 * Provides common functionality used across all TradePress admin pages
 *
 * @package    TradePress
 * @subpackage Admin
 * @version    1.0.3
 */

jQuery(document).ready(function($) {
    // Initialize accordions with a delay to ensure DOM is ready
    setTimeout(function() {
        initAccordions();
    }, 500);
    
    // Main accordion initialization function
    function initAccordions() {
        // Handle Feature page accordions - using event delegation for dynamically loaded content
        $(document).on('click', '.feature-page-header', function(e) {
            // Prevent triggering when clicking links
            if ($(e.target).is('a') || $(e.target).closest('a').length) {
                return true; // Allow link click to proceed
            }
            
            var $content = $(this).next('.feature-page-content');
            $content.slideToggle();
            $(this).find('.toggle-section').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            return false; // Prevent event bubbling
        });
        
        // Handle Alert Decoder accordions
        $(document).on('click', '.alert-decoder-accordion-header', function(e) {
            var $content = $(this).next('.alert-decoder-accordion-content');
            $content.slideToggle();
            $(this).find('.accordion-icon').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            return false; // Prevent event bubbling
        });
        
        // Handle standard accordions
        $(document).on('click', '.tradepress-accordion-header', function(e) {
            // Store reference to this header
            var $header = $(this);
            var $content = $header.next('.tradepress-accordion-content');
            
            // Store the toggle icon for later reference
            var $toggleIcon = $header.find('.accordion-status .toggle-section');
            
            // Toggle the content with a callback to ensure icon changes after animation
            $content.slideToggle(400, function() {
                // Use a flag to check if content is now visible
                var isVisible = $content.is(':visible');
                
                // Update icon based on content visibility
                if (isVisible) {
                    $toggleIcon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                } else {
                    $toggleIcon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                }
            });
        });
    }
    
    // Initialize accordions immediately too
    initAccordions();
    
    // Expand all buttons (used in Features)
    $(document).on('click', '.expand-all', function(e) {
        e.preventDefault();
        
        // Expand standard accordions
        $('.tradepress-accordion-content').slideDown();
        $('.tradepress-accordion-header .toggle-section')
            .removeClass('dashicons-arrow-down-alt2')
            .addClass('dashicons-arrow-up-alt2');
        
        // Expand features accordions
        $('.feature-page-content').slideDown();
        $('.feature-page-header .toggle-section')
            .removeClass('dashicons-arrow-down-alt2')
            .addClass('dashicons-arrow-up-alt2');
        
        // Expand alert decoder accordions
        $('.alert-decoder-accordion-content').slideDown();
        $('.alert-decoder-accordion-header .accordion-icon')
            .removeClass('dashicons-arrow-down-alt2')
            .addClass('dashicons-arrow-up-alt2');
    });
    
    // Collapse all buttons (used in Features)
    $(document).on('click', '.collapse-all', function(e) {
        e.preventDefault();
        
        // Collapse standard accordions
        $('.tradepress-accordion-content').slideUp();
        $('.tradepress-accordion-header .toggle-section')
            .removeClass('dashicons-arrow-up-alt2')
            .addClass('dashicons-arrow-down-alt2');
        
        // Collapse features accordions
        $('.feature-page-content').slideUp();
        $('.feature-page-header .toggle-section')
            .removeClass('dashicons-arrow-up-alt2')
            .addClass('dashicons-arrow-down-alt2');
        
        // Collapse alert decoder accordions
        $('.alert-decoder-accordion-content').slideUp();
        $('.alert-decoder-accordion-header .accordion-icon')
            .removeClass('dashicons-arrow-up-alt2')
            .addClass('dashicons-arrow-down-alt2');
    });

    // Fix: Add click handler for links in accordions to prevent accordion toggling
    $(document).on('click', '.feature-page-header a', function(e) {
        e.stopPropagation(); // Don't trigger parent's click event
    });

    // Feature status filtering (used in Features tab)
    $('#feature-status-filter').on('change', function() {
        var status = $(this).val();
        
        if (status === 'all') {
            $('.feature-row').show();
        } else {
            $('.feature-row').hide();
            $('.feature-row.' + status + '-status').show();
        }
    });
    
    // Calculate and update stats for feature page
    function updateStats() {
        if ($('#feature-stats').length) {
            var totalFeatures = $('.feature-row').length;
            var liveFeatures = $('.feature-row.live-status').length;
            var demoFeatures = $('.feature-row.demo-status').length;
            var completionRate = totalFeatures > 0 ? Math.round((liveFeatures / totalFeatures) * 100) : 0;
            
            $('#total-features').text(totalFeatures);
            $('#live-features').text(liveFeatures);
            $('#demo-features').text(demoFeatures);
            $('#completion-rate').text(completionRate + '%');
        }
    }
    
    // Initial stats calculation
    updateStats();
});
