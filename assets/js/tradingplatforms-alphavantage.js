/**
 * TradePress - Alpha Vantage API Tab Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    // Initialize the data explorer
    initAlphaVantageDataExplorer();
    
    function initAlphaVantageDataExplorer() {
        var $explorer = $('#data-explorer-section');
        var $dataTypeSelect = $('#data-explorer-datatype');
        var $symbolInput = $('#data-explorer-symbol');
        var $fetchButton = $('#data-explorer-fetch');
        var $result = $('#data-explorer-result');
        var $content = $('#data-explorer-content');
        var $loading = $('.data-explorer-loading');
        
        // Handle fetch button click
        $fetchButton.on('click', function(e) {
            e.preventDefault();
            
            var dataType = $dataTypeSelect.val();
            var symbol = $symbolInput.val();
            
            if (!symbol) {
                alert(tradepress_alphavantage_params.enter_symbol_text);
                return;
            }
            
            // Show loading indicator
            $loading.show();
            $result.show();
            $content.html('');
            
            // Prepare parameters based on data type
            var params = {
                action: 'tradepress_test_alphavantage_endpoint',
                security: tradepress_alphavantage_params.nonce,
                symbol: symbol,
                data_type: dataType
            };
            
            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: params,
                success: function(response) {
                    $loading.hide();
                    
                    if (response.success) {
                        // Format and display the response data
                        var formattedData = JSON.stringify(response.data, null, 2);
                        $content.html(escapeHtml(formattedData));
                    } else {
                        // Show error message
                        $content.html('<div class="error-message">' + escapeHtml(response.data.message) + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $loading.hide();
                    $content.html('<div class="error-message">' + tradepress_alphavantage_params.error_text + ': ' + error + '</div>');
                }
            });
        });
        
        // Helper function to escape HTML
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
});