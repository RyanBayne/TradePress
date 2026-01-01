/**
 * TradePress Calculators JavaScript
 *
 * Handles functionality for trading calculators.
 *
 * @package TradePress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize calculators functionality
     */
    function initCalculators() {
        console.log('TradePress Calculators JS loaded');
        
        // Initialize jQuery UI tabs if not already initialized
        if ($('.calculator-tabs').length && !$('.calculator-tabs').hasClass('ui-tabs')) {
            $('.calculator-tabs').tabs({
                heightStyle: "content",
                activate: function(event, ui) {
                    // Reset forms when switching tabs
                    ui.newPanel.find('form')[0]?.reset();
                    ui.newPanel.find('.calculator-results').hide();
                }
            });
        }
        
        // Initialize calculator forms
        initAveragingDownCalculator();
        initPositionSizeCalculator();
        initRiskRewardCalculator();
        initProfitLossCalculator();
        initFibonacciCalculator();
    }
    
    /**
     * Initialize Averaging Down Calculator
     */
    function initAveragingDownCalculator() {
        $('#averaging-down-form').off('submit').on('submit', function(e) {
            e.preventDefault();
            
            // Get inputs
            const currentShares = parseFloat($('#current_shares').val()) || 0;
            const currentPrice = parseFloat($('#current_price').val()) || 0;
            const targetPrice = parseFloat($('#target_price').val()) || 0;
            const newPrice = parseFloat($('#new_price').val()) || 0;
            
            // Validate inputs
            if (currentShares <= 0 || currentPrice <= 0 || targetPrice <= 0 || newPrice <= 0) {
                showCalculatorError('Please enter valid positive numbers for all fields.');
                return;
            }
            
            if (targetPrice >= currentPrice) {
                showCalculatorNote('Target average price must be lower than the current price for averaging down.');
                $('#averaging-down-results').show();
                return;
            }
            
            if (newPrice >= targetPrice) {
                showCalculatorNote('New purchase price should be lower than target average for effective averaging down.');
                $('#averaging-down-results').show();
                return;
            }
            
            // Calculate required shares to average down
            const currentInvestment = currentShares * currentPrice;
            let additionalShares = 0;
            let note = "";
            
            // Formula: (current_investment + additional_shares * new_price) / (current_shares + additional_shares) = target_price
            // Solving for additional_shares:
            // additional_shares = (current_shares * (current_price - target_price)) / (target_price - new_price)
            
            if (targetPrice > newPrice) {
                additionalShares = Math.ceil((currentShares * (currentPrice - targetPrice)) / (targetPrice - newPrice));
                
                if (additionalShares < 0) {
                    additionalShares = 0;
                    note = "Cannot achieve target average with these parameters.";
                }
            } else {
                note = "New purchase price must be lower than target average price.";
                $('#result_note').html(note);
                $('#averaging-down-results').show();
                return;
            }
            
            // Calculate results
            const additionalInvestment = additionalShares * newPrice;
            const totalShares = currentShares + additionalShares;
            const totalInvestment = currentInvestment + additionalInvestment;
            const newAvgPrice = totalInvestment / totalShares;
            
            // Verify calculation
            if (Math.abs(newAvgPrice - targetPrice) > 0.01) {
                note = `Actual new average will be $${newAvgPrice.toFixed(2)} (closest achievable to target).`;
            }
            
            // Display results
            $('#additional_shares_result').text(additionalShares.toLocaleString());
            $('#additional_investment_result').text(formatCurrency(additionalInvestment));
            $('#total_shares_result').text(totalShares.toLocaleString());
            $('#total_investment_result').text(formatCurrency(totalInvestment));
            $('#new_avg_price_result').text(formatCurrency(newAvgPrice));
            $('#result_note').html(note);
            
            // Show results
            $('#averaging-down-results').show();
        });
        
        // Reset form
        $('#averaging-down-form').off('reset').on('reset', function() {
            $('#averaging-down-results').hide();
        });
    }
    
    /**
     * Initialize Position Size Calculator
     */
    function initPositionSizeCalculator() {
        // Placeholder for future implementation
        console.log('Position Size Calculator ready for implementation');
    }
    
    /**
     * Initialize Risk/Reward Calculator
     */
    function initRiskRewardCalculator() {
        // Placeholder for future implementation
        console.log('Risk/Reward Calculator ready for implementation');
    }
    
    /**
     * Initialize Profit/Loss Calculator
     */
    function initProfitLossCalculator() {
        // Placeholder for future implementation
        console.log('Profit/Loss Calculator ready for implementation');
    }
    
    /**
     * Initialize Fibonacci Calculator
     */
    function initFibonacciCalculator() {
        // Placeholder for future implementation
        console.log('Fibonacci Calculator ready for implementation');
    }
    
    /**
     * Show calculator error
     */
    function showCalculatorError(message) {
        alert('Error: ' + message);
    }
    
    /**
     * Show calculator note
     */
    function showCalculatorNote(message) {
        $('#result_note').html(message);
    }
    
    /**
     * Format currency with proper localization
     */
    function formatCurrency(amount) {
        return '$' + amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    /**
     * Format number
     */
    function formatNumber(number) {
        return number.toLocaleString();
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initCalculators();
    });
    
})(jQuery);
