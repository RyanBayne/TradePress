/**
 * TradePress Accordion Functionality
 * 
 * Standard accordion implementation for TradePress plugin
 * 
 * @package TradePress
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Initialize accordion functionality for elements with class 'tradepress-accordion'
     */
    function initAccordions() {
        // Standard accordion initialization using jQuery UI
        $('.tradepress-accordion').each(function() {
            // Check if accordion is already initialized
            if (!$(this).hasClass('ui-accordion')) {
                $(this).accordion({
                    header: '> .accordion-header',
                    collapsible: true,
                    heightStyle: 'content',
                    animate: 200
                });
            }
        });
        
        // Custom accordions (without jQuery UI)
        $('.tradepress-custom-accordion .accordion-header').on('click', function() {
            var $header = $(this);
            var $content = $header.next('.accordion-content');
            var $parent = $header.parent('.tradepress-custom-accordion');
            
            // Toggle the active class
            $header.toggleClass('active');
            
            // Toggle the content visibility
            if ($header.hasClass('active')) {
                $content.slideDown(200);
            } else {
                $content.slideUp(200);
            }
            
            // If set to exclusive, close other sections
            if ($parent.hasClass('exclusive')) {
                $parent.find('.accordion-header').not($header).removeClass('active');
                $parent.find('.accordion-content').not($content).slideUp(200);
            }
        });
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initAccordions();
        
        // Re-initialize accordions when content is dynamically added
        $(document).on('tradepress_content_updated', function() {
            initAccordions();
        });
    });
    
})(jQuery);
