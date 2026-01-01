/**
 * TradePress - Alpaca Trading Platform JavaScript
 * Handles Alpaca API data explorer functionality
 */

jQuery(document).ready(function($) {
    // Initialize the data explorer
    initAlpacaDataExplorer();
    
    function initAlpacaDataExplorer() {
        var $explorer = $('#data-explorer');
        var $dataTypeSelect = $explorer.find('.data-type-select');
        var $symbolInput = $explorer.find('.data-symbol-input');
        var $testButton = $explorer.find('.test-api-button');
        var $resultArea = $explorer.find('.result-area');
        var $loadingIndicator = $explorer.find('.loading-indicator');
        
        // Handle data type selection change
        $dataTypeSelect.on('change', function() {
            var dataType = $(this).val();
            
            // Show/hide symbol input based on data type
            if (dataType === 'watchlists') {
                $symbolInput.parent().hide();
            } else {
                $symbolInput.parent().show();
            }
        });
        
        // Trigger change event to set initial state
        $dataTypeSelect.trigger('change');
        
        // Handle test button click
        $testButton.on('click', function(e) {
            e.preventDefault();
            
            var dataType = $dataTypeSelect.val();
            var symbol = $symbolInput.val();
            var tradingMode = tradePressAlpaca.tradingMode;
            
            // Show loading indicator
            $loadingIndicator.show();
            $resultArea.empty();
            
            // Prepare parameters based on data type
            var params = {};
            var endpoint = '';
            
            switch (dataType) {
                case 'watchlists':
                    endpoint = 'watchlists';
                    break;
                case 'account':
                    endpoint = 'account';
                    break;
                case 'positions':
                    endpoint = 'positions';
                    if (symbol) {
                        endpoint = 'position';
                        params.symbol = symbol;
                    }
                    break;
                case 'orders':
                    endpoint = 'orders';
                    if (symbol) {
                        params.symbols = symbol;
                    }
                    break;
                case 'market_data':
                    endpoint = 'bars';
                    params.symbols = symbol || 'AAPL';
                    params.timeframe = '1Day';
                    params.start = tradePressAlpaca.startDate;
                    params.end = tradePressAlpaca.endDate;
                    break;
                default:
                    endpoint = 'watchlists';
                    break;
            }
            
            // Make AJAX request to test endpoint
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_test_alpaca_endpoint',
                    security: tradePressAlpaca.nonce,
                    endpoint: endpoint,
                    trading_mode: tradingMode,
                    params: params
                },
                success: function(response) {
                    $loadingIndicator.hide();
                    
                    if (response.success) {
                        // Format and display the response data
                        var formattedData = JSON.stringify(response.data, null, 2);
                        var resultHtml = '<pre class="response-data">' + escapeHtml(formattedData) + '</pre>';
                        $resultArea.html(resultHtml);
                        
                        // For debugging, log the response structure to console
                        console.log('Alpaca API Response:', response);
                        
                        // Check if data exists in the expected format
                        var watchlistsData = response.data.data;
                        
                        // If watchlists data is returned, create a visual representation
                        if (endpoint === 'watchlists' && Array.isArray(watchlistsData)) {
                            createwatchlists(watchlistsData);
                        }
                    } else {
                        // Show error message with helpful guidance for common issues
                        var errorHtml = '<div class="response-error">';
                        errorHtml += '<h4>' + tradePressAlpaca.strings.error + '</h4>';
                        
                        var errorMessage = response.data.message || 'Unknown error';
                        errorHtml += '<p>' + escapeHtml(errorMessage) + '</p>';
                        
                        // Add helpful guidance for common errors
                        if (errorMessage.includes('API credentials not configured') || 
                            errorMessage.includes('API key') || 
                            errorMessage.includes('API secret')) {
                            errorHtml += '<div class="error-guidance">';
                            errorHtml += '<h5>' + tradePressAlpaca.strings.howToFix + '</h5>';
                            errorHtml += '<ol>';
                            errorHtml += '<li>' + tradePressAlpaca.strings.goToSettings + '</li>';
                            errorHtml += '<li>' + tradePressAlpaca.strings.enterCredentials + '</li>';
                            errorHtml += '<li>' + tradePressAlpaca.strings.verifyCredentials + '</li>';
                            errorHtml += '<li>' + tradePressAlpaca.strings.saveSettings + '</li>';
                            errorHtml += '</ol>';
                            errorHtml += '</div>';
                        }
                        
                        if (response.data.details) {
                            errorHtml += '<pre>' + escapeHtml(JSON.stringify(response.data.details, null, 2)) + '</pre>';
                        }
                        
                        errorHtml += '</div>';
                        $resultArea.html(errorHtml);
                    }
                },
                error: function(xhr, status, error) {
                    $loadingIndicator.hide();
                    
                    // Show error message with connection details
                    var errorHtml = '<div class="response-error">';
                    errorHtml += '<h4>' + tradePressAlpaca.strings.connectionError + '</h4>';
                    
                    // Try to parse any JSON response if available
                    var errorDetails = '';
                    try {
                        if (xhr.responseText) {
                            var errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.data && errorResponse.data.message) {
                                errorDetails = errorResponse.data.message;
                            }
                        }
                    } catch(e) {
                        errorDetails = xhr.responseText || error;
                    }
                    
                    errorHtml += '<p>' + escapeHtml(error || 'Failed to connect to Alpaca API') + '</p>';
                    
                    if (errorDetails) {
                        errorHtml += '<p><strong>Details:</strong> ' + escapeHtml(errorDetails) + '</p>';
                    }
                    
                    // Add guidance for connection errors
                    errorHtml += '<div class="error-guidance">';
                    errorHtml += '<h5>' + tradePressAlpaca.strings.troubleshooting + '</h5>';
                    errorHtml += '<ol>';
                    errorHtml += '<li>' + tradePressAlpaca.strings.checkConnection + '</li>';
                    errorHtml += '<li>' + tradePressAlpaca.strings.verifyCredentials + '</li>';
                    errorHtml += '<li>' + tradePressAlpaca.strings.checkTradingMode + '</li>';
                    errorHtml += '<li>' + tradePressAlpaca.strings.checkStatus + '</li>';
                    errorHtml += '</ol>';
                    errorHtml += '</div>';
                    
                    errorHtml += '</div>';
                    $resultArea.html(errorHtml);
                }
            });
        });
        
        // Create a visual representation of watchlists
        function createwatchlists(watchlists) {
            if (!watchlists.length) {
                $resultArea.append('<div class="notice notice-info"><p>' + tradePressAlpaca.strings.noWatchlists + '</p></div>');
                return;
            }
            
            var html = '<div class="watchlists-container">';
            html += '<h3>' + tradePressAlpaca.strings.yourWatchlists + '</h3>';
            
            watchlists.forEach(function(watchlist) {
                html += '<div class="watchlist-card">';
                html += '<h4>' + escapeHtml(watchlist.name) + '</h4>';
                
                if (watchlist.assets && watchlist.assets.length) {
                    html += '<ul class="watchlist-symbols">';
                    watchlist.assets.forEach(function(asset) {
                        html += '<li>';
                        html += '<span class="symbol">' + escapeHtml(asset.symbol) + '</span>';
                        if (asset.name) {
                            html += '<span class="name">' + escapeHtml(asset.name) + '</span>';
                        }
                        html += '</li>';
                    });
                    html += '</ul>';
                } else {
                    html += '<p class="empty-watchlist">' + tradePressAlpaca.strings.noSymbols + '</p>';
                }
                
                html += '</div>';
            });
            
            html += '</div>';
            $resultArea.append(html);
        }
        
        // Helper function to escape HTML
        function escapeHtml(str) {
            if (typeof str !== 'string') {
                return JSON.stringify(str);
            }
            
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    }
});