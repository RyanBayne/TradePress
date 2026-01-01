/**
 * TradePress UI Library JavaScript
 * 
 * Interactive functionality for the UI Library page
 * 
 * @package TradePress/Assets/JS
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // UI Library object
    window.TradePressUILibrary = {
        
        /**
         * Initialize UI Library functionality
         */
        init: function() {
            this.initColorPalette();
            this.initAccordion();
            this.initModal();
            this.initAnimations();
        },

        /**
         * Color palette functionality
         */
        initColorPalette: function() {
            // Bind click events to color items
            $('.tradepress-color-item').on('click', function() {
                var $item = $(this);
                var colorName = $item.find('.tradepress-color-name').text();
                var colorValue = $item.find('.tradepress-color-value').text();
                var colorClass = $item.find('.tradepress-color-swatch').attr('class').split(' ').find(cls => cls.startsWith('tradepress-color-'));
                
                TradePressUILibrary.showColorInfo($item[0], colorName, colorValue, colorClass);
            });
        },

        /**
         * Show color information
         */
        showColorInfo: function(element, colorName, hexValue, cssClass) {
            var panel = $('#color-info-display');
            var details = $('#color-details');
            
            if (panel.length === 0) {
                // Create the panel if it doesn't exist
                panel = $('<div id="color-info-display" class="tradepress-color-info-panel" style="display:none;"></div>');
                details = $('<div id="color-details"></div>');
                panel.append('<h4>Color Information</h4>').append(details);
                $('body').append(panel);
            }
            
            details.html(`
                <p><strong>Color Name:</strong> ${colorName}</p>
                <p><strong>Hex Value:</strong> ${hexValue}</p>
                <p><strong>CSS Class:</strong> .${cssClass || 'N/A'}</p>
            `);
            
            panel.fadeIn();
            
            // Hide after 3 seconds
            setTimeout(() => {
                panel.fadeOut();
            }, 3000);
        },

        /**
         * Initialize accordion functionality
         */
        initAccordion: function() {
            $('.tp-accordion-header').on('click', function() {
                var $header = $(this);
                var $item = $header.closest('.tp-accordion-item');
                var $content = $item.find('.tp-accordion-content');
                var $icon = $header.find('.tp-accordion-icon');
                
                // Toggle content
                $content.slideToggle(300);
                
                // Toggle icon rotation
                $icon.toggleClass('tp-accordion-icon-rotated');
                
                // Toggle active state
                $item.toggleClass('tp-accordion-active');
            });
        },

        /**
         * Initialize modal functionality
         */
        initModal: function() {
            // Open demo modal
            $('#open-demo-modal').on('click', function() {
                $('#ui-library-demo-modal').show();
            });
            
            // Close demo modal
            $('.tp-modal-close, .close-demo-modal').on('click', function() {
                $('#ui-library-demo-modal').hide();
            });
            
            // Close modal on background click
            $('#ui-library-demo-modal').on('click', function(e) {
                if (e.target === this) {
                    $(this).hide();
                }
            });
        },

        /**
         * Initialize animation demonstrations
         */
        initAnimations: function() {
            // Shake animation demo
            $('.shake-demo').on('click', function() {
                $(this).addClass('tp-shake');
                setTimeout(() => {
                    $(this).removeClass('tp-shake');
                }, 500);
            });

            // Highlight animation demo
            $('.highlight-demo').on('click', function() {
                $(this).addClass('tp-highlight');
                setTimeout(() => {
                    $(this).removeClass('tp-highlight');
                }, 2000);
            });

            // Sequence animation demo
            $('#sequence-trigger').on('click', function() {
                $('.sequence-item').removeClass('tp-fade-in');
                setTimeout(() => {
                    $('.sequence-item').addClass('tp-fade-in');
                }, 100);
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        TradePressUILibrary.init();
    });

})(jQuery);
