/**
 * TradePress UI Library Animation Interactions
 *
 * @package TradePress/Assets/JS
 * @since 1.0.5
 */

jQuery(document).ready(function($) {
    console.log('TradePress Animation Showcase JS loaded');
    
    // Generic animation trigger function
    function triggerAnimation($element, animationClass, resetAfter = 1000) {
        // Remove any existing animation classes
        $element.removeClass(animationClass);
        
        // Force reflow to ensure class removal takes effect
        void $element[0].offsetHeight;
        
        // Add animation class after a brief delay
        setTimeout(function() {
            $element.addClass(animationClass);
            
            // Reset animation after specified time
            if (resetAfter > 0) {
                setTimeout(function() {
                    $element.removeClass(animationClass);
                }, resetAfter);
            }
        }, 50);
    }
    
    // Hover-triggered animations (mouseenter)
    $(document).on('mouseenter', '.fade-in-demo, .slide-down-demo, .slide-up-demo, .slide-left-demo, .slide-right-demo, .scale-in-demo', function() {
        var animationClass = $(this).data('animation');
        console.log('Hover animation triggered:', animationClass);
        if (animationClass) {
            triggerAnimation($(this), animationClass, 600);
        }
    });
    
    // Fade out demo (special handling for opacity reset)
    $(document).on('mouseenter', '.fade-out-demo', function() {
        var $element = $(this);
        triggerAnimation($element, 'tradepress-fade-out', 400);
        
        // Reset opacity after animation
        setTimeout(function() {
            $element.css('opacity', '1');
        }, 450);
    });
    
    // Scale out demo (special handling)
    $(document).on('mouseenter', '.scale-out-demo', function() {
        triggerAnimation($(this), 'tradepress-scale-out', 400);
    });
    
    // Click-triggered animations
    $(document).on('click', '.shake-demo, .highlight-demo', function() {
        var animationClass = $(this).data('animation');
        console.log('Click animation triggered:', animationClass);
        if (animationClass) {
            triggerAnimation($(this), animationClass, 800);
        }
    });
    
    // Sequence animation button
    $(document).on('click', '#sequence-trigger', function() {
        var $items = $('.sequence-item');
        console.log('Sequence animation triggered, items found:', $items.length);
        
        // Reset all items
        $items.removeClass('tradepress-fade-in').css('opacity', '0');
        
        // Trigger sequence with staggered timing
        setTimeout(function() {
            $items.each(function(index) {
                var $item = $(this);
                setTimeout(function() {
                    $item.addClass('tradepress-fade-in').css('opacity', '1');
                }, index * 200); // 200ms delay between items
            });
        }, 100);
        
        // Reset after sequence completes
        setTimeout(function() {
            $items.removeClass('tradepress-fade-in');
        }, 3000);
    });
    
    // Initialize sequence items
    setTimeout(function() {
        $('.sequence-item').css('opacity', '1');
    }, 500);
    
    // Add visual feedback for interactive elements
    $(document).on('mouseenter', '.fade-in-demo, .fade-out-demo, .slide-down-demo, .slide-up-demo, .slide-left-demo, .slide-right-demo, .scale-in-demo, .scale-out-demo', function() {
        $(this).css('cursor', 'pointer');
    });
    
    $(document).on('mouseenter', '.shake-demo, .highlight-demo', function() {
        $(this).css('cursor', 'pointer');
    });
});
