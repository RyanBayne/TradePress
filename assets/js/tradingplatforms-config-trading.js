/**
 * TradePress - Trading API Configuration Partial Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    // Tab navigation
    $('.api-settings-tabs a').on('click', function(e) {
        e.preventDefault();
        
        // Update active tab
        $('.api-settings-tabs a').removeClass('active');
        $(this).addClass('active');
        
        // Show active content
        var target = $(this).attr('href');
        $('.api-settings-tab-content .tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        
        $(this).find('.dashicons')
            .toggleClass('dashicons-visibility')
            .toggleClass('dashicons-hidden');
    });
    
    // Test live API connection
    $('.test-live-api').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var apiId = $button.data('api');
        var apiKey = $('#' + apiId + '_api_key').val();
        var apiSecret = $('#' + apiId + '_api_secret').val();
        
        if (!apiKey || !apiSecret) {
            alert(tradepress_config_trading_params.enter_api_credentials_text);
            return;
        }
        
        $button.prop('disabled', true).text(tradepress_config_trading_params.testing_text);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_' + apiId + '_live_connection',
                api_key: apiKey,
                api_secret: apiSecret,
                nonce: tradepress_config_trading_params.nonce
            },
            success: function(response) {
                $button.prop('disabled', false).text(tradepress_config_trading_params.test_live_api_text);
                
                if (response.success) {
                    alert(tradepress_config_trading_params.live_api_success_text + 
                          (response.data.message ? '\n\n' + response.data.message : ''));
                } else {
                    alert(tradepress_config_trading_params.live_api_failed_text + response.data.message);
                }
            },
            error: function() {
                $button.prop('disabled', false).text(tradepress_config_trading_params.test_live_api_text);
                alert(tradepress_config_trading_params.connection_error_text);
            }
        });
    });
    
    // Test paper API connection
    $('.test-paper-api').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var apiId = $button.data('api');
        var apiKey = $('#' + apiId + '_paper_api_key').val();
        var apiSecret = $('#' + apiId + '_paper_api_secret').val();
        
        if (!apiKey || !apiSecret) {
            alert(tradepress_config_trading_params.enter_paper_api_credentials_text);
            return;
        }
        
        $button.prop('disabled', true).text(tradepress_config_trading_params.testing_text);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_' + apiId + '_paper_connection',
                api_key: apiKey,
                api_secret: apiSecret,
                nonce: tradepress_config_trading_params.nonce
            },
            success: function(response) {
                $button.prop('disabled', false).text(tradepress_config_trading_params.test_paper_api_text);
                
                if (response.success) {
                    alert(tradepress_config_trading_params.paper_api_success_text + 
                          (response.data.message ? '\n\n' + response.data.message : ''));
                } else {
                    alert(tradepress_config_trading_params.paper_api_failed_text + response.data.message);
                }
            },
            error: function() {
                $button.prop('disabled', false).text(tradepress_config_trading_params.test_paper_api_text);
                alert(tradepress_config_trading_params.connection_error_text);
            }
        });
    });
    
    // Update form based on trading mode selection
    $('[id$="_trading_mode"]').on('change', function() {
        var mode = $(this).val();
        
        if (mode === 'live') {
            $('.api-settings-tabs a[href="#live-trading"]').click();
        } else {
            $('.api-settings-tabs a[href="#paper-trading"]').click();
        }
    });
    
    // Test API connection for data-only APIs
    $('.test-api-connection').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var apiId = $button.data('api');
        var apiKey = $('#' + apiId + '_api_key').val();
        
        if (!apiKey) {
            alert('Please enter an API key before testing the connection.');
            return;
        }
        
        $button.prop('disabled', true).text('Testing...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_' + apiId + '_connection',
                api_key: apiKey,
                nonce: tradepress_config_trading_params.nonce
            },
            success: function(response) {
                $button.prop('disabled', false).text('Test Connection');
                
                if (response.success) {
                    alert('Connection successful! API is working properly.');
                } else {
                    alert('Connection failed: ' + response.data.message);
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Test Connection');
                alert('Connection test failed due to a server error. Please check your logs.');
            }
        });
    });
});