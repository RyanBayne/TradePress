/**
 * TradePress Calculators JavaScript
 *
 * Handle all calculator functionality for the Trading Calculators tab
 *
 * @package TradePress
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    // Initialize tabs
    $(".calculator-tabs").tabs();
    
    // Averaging Down Calculator
    $("#averaging-down-form").on("submit", function(e) {
        e.preventDefault();
        
        // Get inputs
        const currentShares = parseFloat($("#current_shares").val());
        const currentPrice = parseFloat($("#current_price").val());
        const targetPrice = parseFloat($("#target_price").val());
        const newPrice = parseFloat($("#new_price").val());
        
        // Validate inputs
        if (targetPrice >= currentPrice && newPrice >= targetPrice) {
            $("#result_note").html('Target average price must be lower than the current price, and the new purchase price must be lower than the target average.');
            $("#averaging-down-results").show();
            return;
        }
        
        // Calculate required shares to average down
        let additionalShares = 0;
        let note = "";
        
        if (newPrice >= targetPrice && targetPrice < currentPrice) {
            // Calculate how many shares needed when new price is higher than target
            additionalShares = Math.ceil(currentShares * (currentPrice - targetPrice) / (targetPrice - newPrice));
            
        } else if (newPrice < targetPrice && targetPrice < currentPrice) {
            // Calculate when new price is lower than target
            additionalShares = Math.floor(currentShares * (targetPrice - currentPrice) / (newPrice - targetPrice));
            note = "Your new purchase price is lower than your target average. Consider raising your target or buying fewer shares.";
            
        } else if (targetPrice >= currentPrice) {
            note = "Your target average price must be lower than your current price for averaging down.";
            $("#result_note").html(note);
            $("#averaging-down-results").show();
            return;
        }
        
        // Calculate results
        const additionalInvestment = additionalShares * newPrice;
        const totalShares = currentShares + additionalShares;
        const totalInvestment = (currentShares * currentPrice) + additionalInvestment;
        const newAvgPrice = totalInvestment / totalShares;
        
        // Display results
        $("#additional_shares_result").text(additionalShares.toLocaleString());
        $("#additional_investment_result").text('$' + additionalInvestment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $("#total_shares_result").text(totalShares.toLocaleString());
        $("#total_investment_result").text('$' + totalInvestment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $("#new_avg_price_result").text('$' + newAvgPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $("#result_note").html(note);
        
        // Show results
        $("#averaging-down-results").show();
    });
});

/**
 * TradePress Admin Trading Page JavaScript
 *
 * Handles functionality for the Trading page in the admin area.
 *
 * @package TradePress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Trading Page functionality
     */
    function initTradingPage() {
        console.log('TradePress Trading Page JS loaded');
        
        // Initialize tab functionality if tabs exist
        if ($('.tradepress-tabs-container').length > 0) {
            initTabs();
        }
        
        // Initialize portfolio view if exists
        if ($('#tradepress-portfolio').length > 0) {
            initPortfolioView();
        }
        
        // Initialize trade history if exists
        if ($('#tradepress-trade-history').length > 0) {
            initTradeHistory();
        }
        
        // Initialize manual trading form if exists
        if ($('#tradepress-manual-trade-form').length > 0) {
            initManualTradeForm();
        }
        
        // Initialize order management if exists
        if ($('#tradepress-orders').length > 0) {
            initOrderManagement();
        }
    }
    
    /**
     * Initialize tabs for the Trading page
     */
    function initTabs() {
        // Handle tab switching
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).data('tab');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show selected tab content
            $('.tab-content').removeClass('active');
            $('#' + tabId).addClass('active');
            
            // Update URL without refreshing page
            var newUrl = updateQueryStringParameter(window.location.href, 'tab', tabId);
            window.history.pushState({ path: newUrl }, '', newUrl);
        });
        
        // Initialize active tab from URL
        var urlParams = new URLSearchParams(window.location.search);
        var activeTab = urlParams.get('tab');
        
        if (activeTab) {
            $('.nav-tab[data-tab="' + activeTab + '"]').trigger('click');
        } else {
            // Default to first tab
            $('.nav-tab').first().trigger('click');
        }
    }
    
    /**
     * Initialize portfolio view functionality
     */
    function initPortfolioView() {
        // Refresh portfolio data periodically
        var portfolioRefreshInterval = 60000; // 1 minute
        
        function refreshPortfolioData() {
            $.ajax({
                url: tradepress_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_refresh_portfolio',
                    nonce: tradepress_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update portfolio display with new data
                        updatePortfolioDisplay(response.data);
                    }
                },
                error: function() {
                    console.error('Failed to refresh portfolio data');
                }
            });
        }
        
        // Set up periodic refresh
        setInterval(refreshPortfolioData, portfolioRefreshInterval);
        
        // Initial refresh
        refreshPortfolioData();
        
        /**
         * Update portfolio display with new data
         */
        function updatePortfolioDisplay(data) {
            // Implementation would update DOM with new portfolio data
            if (!data || !data.positions) return;
            
            // Update positions
            $.each(data.positions, function(index, position) {
                var $row = $('#position-' + position.id);
                
                if ($row.length) {
                    // Update existing position row
                    $row.find('.position-value').text(position.current_value);
                    $row.find('.position-pl').text(position.unrealized_pl);
                    
                    // Update class based on P&L
                    if (parseFloat(position.unrealized_pl) >= 0) {
                        $row.find('.position-pl').removeClass('negative').addClass('positive');
                    } else {
                        $row.find('.position-pl').removeClass('positive').addClass('negative');
                    }
                }
            });
            
            // Update totals
            if (data.totals) {
                $('#portfolio-total-value').text(data.totals.value);
                $('#portfolio-total-pl').text(data.totals.unrealized_pl);
                
                if (parseFloat(data.totals.unrealized_pl) >= 0) {
                    $('#portfolio-total-pl').removeClass('negative').addClass('positive');
                } else {
                    $('#portfolio-total-pl').removeClass('positive').addClass('negative');
                }
            }
        }
    }
    
    /**
     * Initialize trade history functionality
     */
    function initTradeHistory() {
        // Initialize date range picker if available
        if ($.fn.daterangepicker && $('#trade-history-date-range').length) {
            $('#trade-history-date-range').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                startDate: moment().subtract(29, 'days'),
                endDate: moment()
            }, function(start, end) {
                // Update displayed date range
                $('#trade-history-date-range span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                
                // Fetch trade history for selected range
                fetchTradeHistory(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            });
        }
        
        // Handle filter form submission
        $('#trade-history-filters').on('submit', function(e) {
            e.preventDefault();
            
            var filters = $(this).serialize();
            fetchTradeHistory(null, null, filters);
        });
        
        /**
         * Fetch trade history data with filters
         */
        function fetchTradeHistory(startDate, endDate, additionalFilters) {
            var data = {
                action: 'tradepress_fetch_trade_history',
                nonce: tradepress_ajax.nonce
            };
            
            if (startDate && endDate) {
                data.start_date = startDate;
                data.end_date = endDate;
            }
            
            if (additionalFilters) {
                // Merge additional filters
                var filterData = new URLSearchParams(additionalFilters);
                filterData.forEach(function(value, key) {
                    data[key] = value;
                });
            }
            
            $.ajax({
                url: tradepress_ajax.ajax_url,
                type: 'POST',
                data: data,
                beforeSend: function() {
                    $('#trade-history-table').addClass('loading');
                },
                success: function(response) {
                    $('#trade-history-table').removeClass('loading');
                    
                    if (response.success) {
                        // Update trade history table with new data
                        updateTradeHistoryTable(response.data);
                    } else {
                        console.error('Error fetching trade history:', response.data);
                    }
                },
                error: function() {
                    $('#trade-history-table').removeClass('loading');
                    console.error('Failed to fetch trade history');
                }
            });
        }
        
        /**
         * Update trade history table with new data
         */
        function updateTradeHistoryTable(data) {
            var $tableBody = $('#trade-history-table tbody');
            $tableBody.empty();
            
            if (!data || data.length === 0) {
                $tableBody.append('<tr><td colspan="6">No trades found for the selected period.</td></tr>');
                return;
            }
            
            $.each(data, function(index, trade) {
                var row = '<tr>';
                row += '<td>' + trade.date + '</td>';
                row += '<td>' + trade.symbol + '</td>';
                row += '<td>' + trade.type + '</td>';
                row += '<td>' + trade.quantity + '</td>';
                row += '<td>' + trade.price + '</td>';
                row += '<td class="' + (parseFloat(trade.pl) >= 0 ? 'positive' : 'negative') + '">' + trade.pl + '</td>';
                row += '</tr>';
                
                $tableBody.append(row);
            });
        }
    }
    
    /**
     * Initialize manual trade form functionality
     */
    function initManualTradeForm() {
        // Symbol lookup autocomplete
        if ($.fn.autocomplete && $('#manual-trade-symbol').length) {
            $('#manual-trade-symbol').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: tradepress_ajax.ajax_url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'tradepress_symbol_lookup',
                            term: request.term,
                            nonce: tradepress_ajax.nonce
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    // Populate hidden fields with selected symbol data
                    $('#manual-trade-symbol-id').val(ui.item.id);
                    
                    // Fetch current price for the selected symbol
                    fetchSymbolPrice(ui.item.id);
                }
            });
        }
        
        // Calculate order total when quantity or price changes
        $('#manual-trade-quantity, #manual-trade-price').on('input', calculateOrderTotal);
        
        // Show appropriate fields based on order type
        $('#manual-trade-order-type').on('change', function() {
            var orderType = $(this).val();
            
            // Hide all conditional fields first
            $('.conditional-field').hide();
            
            // Show fields relevant to the selected order type
            switch (orderType) {
                case 'market':
                    $('.field-market-price').show();
                    break;
                case 'limit':
                    $('.field-limit-price').show();
                    break;
                case 'stop':
                    $('.field-stop-price').show();
                    break;
                case 'stop_limit':
                    $('.field-stop-price').show();
                    $('.field-limit-price').show();
                    break;
            }
            
            // Recalculate order total
            calculateOrderTotal();
        });
        
        // Handle form submission
        $('#manual-trade-form').on('submit', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to place this order?')) {
                // Submit order
                $.ajax({
                    url: tradepress_ajax.ajax_url,
                    type: 'POST',
                    data: $(this).serialize() + '&action=tradepress_place_order&nonce=' + tradepress_ajax.nonce,
                    beforeSend: function() {
                        $('#manual-trade-submit').prop('disabled', true).addClass('loading');
                    },
                    success: function(response) {
                        $('#manual-trade-submit').prop('disabled', false).removeClass('loading');
                        
                        if (response.success) {
                            // Show success message
                            showNotice('success', 'Order placed successfully!');
                            
                            // Reset form
                            $('#manual-trade-form')[0].reset();
                            
                            // Update orders list if it exists
                            if ($('#tradepress-orders').length) {
                                refreshOrders();
                            }
                        } else {
                            // Show error message
                            showNotice('error', 'Failed to place order: ' + response.data);
                        }
                    },
                    error: function() {
                        $('#manual-trade-submit').prop('disabled', false).removeClass('loading');
                        showNotice('error', 'Failed to place order due to a server error.');
                    }
                });
            }
        });
        
        /**
         * Fetch current price for a symbol
         */
        function fetchSymbolPrice(symbolId) {
            $.ajax({
                url: tradepress_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_get_symbol_price',
                    symbol_id: symbolId,
                    nonce: tradepress_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update price field
                        $('#manual-trade-price').val(response.data.price);
                        
                        // Recalculate order total
                        calculateOrderTotal();
                    }
                }
            });
        }
        
        /**
         * Calculate order total based on quantity and price
         */
        function calculateOrderTotal() {
            var quantity = parseFloat($('#manual-trade-quantity').val()) || 0;
            var price = parseFloat($('#manual-trade-price').val()) || 0;
            var total = quantity * price;
            
            // Format to 2 decimal places
            total = total.toFixed(2);
            
            // Update total display
            $('#manual-trade-total').text(total);
        }
    }
    
    /**
     * Initialize order management functionality
     */
    function initOrderManagement() {
        // Refresh orders periodically
        var ordersRefreshInterval = 30000; // 30 seconds
        setInterval(refreshOrders, ordersRefreshInterval);
        
        // Initial refresh
        refreshOrders();
        
        // Handle cancel order buttons
        $('#tradepress-orders').on('click', '.cancel-order', function(e) {
            e.preventDefault();
            
            var orderId = $(this).data('order-id');
            
            if (confirm('Are you sure you want to cancel this order?')) {
                cancelOrder(orderId);
            }
        });
        
        /**
         * Refresh orders list
         */
        function refreshOrders() {
            $.ajax({
                url: tradepress_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_get_orders',
                    nonce: tradepress_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateOrdersTable(response.data);
                    } else {
                        console.error('Error fetching orders:', response.data);
                    }
                },
                error: function() {
                    console.error('Failed to fetch orders');
                }
            });
        }
        
        /**
         * Update orders table with new data
         */
        function updateOrdersTable(data) {
            var $tableBody = $('#tradepress-orders tbody');
            $tableBody.empty();
            
            if (!data || data.length === 0) {
                $tableBody.append('<tr><td colspan="7">No open orders.</td></tr>');
                return;
            }
            
            $.each(data, function(index, order) {
                var row = '<tr data-order-id="' + order.id + '">';
                row += '<td>' + order.symbol + '</td>';
                row += '<td>' + order.type + '</td>';
                row += '<td>' + (order.side === 'buy' ? 'Buy' : 'Sell') + '</td>';
                row += '<td>' + order.quantity + '</td>';
                row += '<td>' + (order.price || 'Market') + '</td>';
                row += '<td>' + order.status + '</td>';
                row += '<td class="order-actions">';
                
                if (order.status === 'open' || order.status === 'pending') {
                    row += '<button class="button cancel-order" data-order-id="' + order.id + '">Cancel</button>';
                }
                
                row += '</td>';
                row += '</tr>';
                
                $tableBody.append(row);
            });
        }
        
        /**
         * Cancel an order
         */
        function cancelOrder(orderId) {
            $.ajax({
                url: tradepress_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_cancel_order',
                    order_id: orderId,
                    nonce: tradepress_ajax.nonce
                },
                beforeSend: function() {
                    $('tr[data-order-id="' + orderId + '"]').addClass('loading');
                },
                success: function(response) {
                    $('tr[data-order-id="' + orderId + '"]').removeClass('loading');
                    
                    if (response.success) {
                        showNotice('success', 'Order cancelled successfully');
                        refreshOrders();
                    } else {
                        showNotice('error', 'Failed to cancel order: ' + response.data);
                    }
                },
                error: function() {
                    $('tr[data-order-id="' + orderId + '"]').removeClass('loading');
                    showNotice('error', 'Failed to cancel order due to a server error');
                }
            });
        }
    }
    
    /**
     * Update URL parameter
     *
     * @param {string} uri Current URL
     * @param {string} key Parameter key to update
     * @param {string} value Parameter value
     * @return {string} Updated URL
     */
    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }
    
    /**
     * Show notice message
     *
     * @param {string} type Notice type (success, error, warning, info)
     * @param {string} message Notice message
     */
    function showNotice(type, message) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Add dismiss button
        var $button = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
        $button.on('click', function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        });
        
        $notice.append($button);
        
        // Insert at the top of the page
        $('.wrap').first().prepend($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, 5000);
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        initTradingPage();
    });
    
})(jQuery);