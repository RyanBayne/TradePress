/**
 * TradePress Trading Platforms Comparisons
 * 
 * Handles toggle functionality for sections and providers
 *
 * @package TradePress/JS
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Section toggle functionality
        $('.section-toggle').on('click', function() {
            var sectionId = $(this).data('section');
            var isActive = $(this).hasClass('active');
            
            $(this).toggleClass('active');
            
            if (isActive) {
                $('#section-' + sectionId).hide();
            } else {
                $('#section-' + sectionId).show();
            }
        });
        

        
        // Filter button functionality
        $('.filter-button').on('click', function() {
            $(this).toggleClass('active');
            updateProviderVisibility();
        });
        
        // Update provider visibility based on active filters
        function updateProviderVisibility() {
            var activeOnly = $('#filter-active-only').hasClass('active');
            var dataOnly = $('#filter-data-only').hasClass('active');
            var tradingOnly = $('#filter-trading-only').hasClass('active');
            
            // Define provider types
            var providerTypes = {
                'alphavantage': 'data',
                'finnhub': 'data',
                'polygon': 'data',
                'twelvedata': 'data',
                'iexcloud': 'data',
                'eodhd': 'data',
                'fmp': 'data',
                'intrinio': 'data',
                'marketstack': 'data',
                'alltick': 'data',
                'alpaca': 'trading',
                'interactive_brokers': 'trading',
                'tradier': 'trading',
                'trading212': 'trading',
                'fidelity': 'trading',
                'etoro': 'trading',
                'webull': 'trading',
                'tradingview': 'trading',
                'tradingapi': 'trading'
            };
            
            // Get active providers (placeholder)
            var activeProviders = ['alphavantage', 'alpaca', 'finnhub'];
            
            // Show/hide providers based on filters
            $.each(providerTypes, function(providerId, providerType) {
                var isActive = activeProviders.indexOf(providerId) !== -1;
                var shouldShow = true;
                
                if (activeOnly && !isActive) {
                    shouldShow = false;
                }
                
                if (dataOnly && providerType !== 'data') {
                    shouldShow = false;
                }
                
                if (tradingOnly && providerType !== 'trading') {
                    shouldShow = false;
                }
                
                if (shouldShow) {
                    $('.provider-' + providerId).show();
                } else {
                    $('.provider-' + providerId).hide();
                }
            });
        }
        
    });
    
})(jQuery);