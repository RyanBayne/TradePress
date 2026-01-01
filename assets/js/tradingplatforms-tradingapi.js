/**
 * TradePress - Trading API Tab Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    // Tab navigation
    $('.api-subtabs-wrapper a').on('click', function(e) {
        e.preventDefault();
        
        // Update active tab
        $('.api-subtabs-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target content
        var target = $(this).attr('href');
        $('.api-tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Toggle custom endpoint field based on provider selection
    $('#api_provider').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom_endpoint_row').show();
        } else {
            $('#custom_endpoint_row').hide();
        }
    });
    
    // Toggle auth method fields
    $('#api_auth_method').on('change', function() {
        var selectedAuth = $(this).val();
        $('.auth-method-fields').hide();
        $('.auth-' + selectedAuth).show();
    });
    
    // Copy webhook URL button
    $('.copy-webhook').on('click', function() {
        var webhookUrl = document.getElementById('webhook-url');
        var range = document.createRange();
        range.selectNode(webhookUrl);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        
        var $button = $(this);
        $button.text(tradepress_tradingapi_params.copied_text);
        setTimeout(function() {
            $button.text(tradepress_tradingapi_params.copy_text);
        }, 2000);
    });
    
    // Copy OAuth redirect URI
    $('.copy-uri').on('click', function() {
        var uriField = document.getElementById('api_redirect_uri');
        uriField.select();
        document.execCommand('copy');
        
        var $button = $(this);
        $button.text(tradepress_tradingapi_params.copied_text);
        setTimeout(function() {
            $button.text(tradepress_tradingapi_params.copy_text);
        }, 2000);
    });
    
    // Generate webhook secret
    $('#generate_webhook_secret').on('click', function() {
        var secret = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        for (var i = 0; i < 32; i++) {
            secret += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        $('#api_webhook_secret').val(secret);
    });
    
    // Test API connection
    $('#test_api_connection').on('click', function() {
        var $button = $(this);
        var $results = $('#api-test-results');
        
        $button.prop('disabled', true).text(tradepress_tradingapi_params.testing_text);
        
        // Simulate API test (replace with actual AJAX call)
        setTimeout(function() {
            $button.prop('disabled', false).text(tradepress_tradingapi_params.test_connection_text);
            
            // Demo result - would be replaced with actual API test
            $results.show().find('.test-result-content').html(
                '<div class="demo-indicator" style="margin-bottom: 15px;">' +
                '<div class="demo-icon dashicons dashicons-info"></div>' +
                '<div class="demo-text">' +
                '<h4>Demo Response</h4>' +
                '<p>This is a simulated test response.</p>' +
                '</div></div>' +
                '<div class="notice notice-success"><p><strong>' + tradepress_tradingapi_params.success_text + '</strong> ' +
                tradepress_tradingapi_params.connection_success_text + '</p>' +
                '<p><strong>' + tradepress_tradingapi_params.api_version_text + '</strong> v2.3.4</p>' +
                '<p><strong>' + tradepress_tradingapi_params.server_time_text + '</strong> ' + new Date().toISOString() + '</p>' +
                '<p><strong>' + tradepress_tradingapi_params.response_time_text + '</strong> 245ms</p></div>'
            );
        }, 1500);
    });
    
    // Log filter functionality
    $('#log_level').on('change', function() {
        var level = $(this).val();
        if (level === 'all') {
            $('.log-entry').show();
        } else if (level === 'error') {
            $('.log-entry').hide();
            $('.log-error').show();
        } else if (level === 'warning') {
            $('.log-entry').hide();
            $('.log-error, .log-warning').show();
        } else if (level === 'info') {
            $('.log-entry').hide();
            $('.log-error, .log-warning, .log-info').show();
        }
    });
    
    // Refresh logs button - simulate refresh
    $('#refresh_logs').on('click', function() {
        var $button = $(this);
        $button.prop('disabled', true);
        
        setTimeout(function() {
            $button.prop('disabled', false);
        }, 500);
    });
    
    // Clear logs button - simulate clearing
    $('#clear_logs').on('click', function() {
        if (confirm(tradepress_tradingapi_params.confirm_clear_logs_text)) {
            var $button = $(this);
            $button.prop('disabled', true);
            
            setTimeout(function() {
                $button.prop('disabled', false);
                $('#api-logs-body tr:not(:first)').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 500);
        }
    });
});