/**
 * TradePress Data Page JavaScript
 * 
 * Handles functionality for the data management pages
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // API Activity - View details modal
    $('.view-details').on('click', function() {
        var logId = $(this).data('id');
        
        $('#api-details-content').html('<div class="loading">Loading details...</div>');
        $('#api-details-modal').show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_get_api_call_details',
                log_id: logId,
                security: tradepressData.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#api-details-content').html(response.data);
                } else {
                    $('#api-details-content').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                }
            },
            error: function() {
                $('#api-details-content').html('<div class="notice notice-error"><p>Error loading details. Please try again.</p></div>');
            }
        });
    });
    
    // Close modal functionality
    $('.modal-close, .modal-close-button').on('click', function() {
        $('#api-details-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).hasClass('tradepress-modal')) {
            $('.tradepress-modal').hide();
        }
    });
    
    // API Endpoints - View details functionality
    $('.view-details').on('click', function() {
        var endpointId = $(this).data('endpoint-id');
        alert('Endpoint details view will be implemented here for endpoint ID: ' + endpointId);
    });
    
    // Manage Sources functionality
    // Delete source
    $('.delete-source').on('click', function(e) {
        e.preventDefault();
        
        if (confirm(tradepressData.confirmDelete)) {
            var sourceId = $(this).data('id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_delete_source',
                    source_id: sourceId,
                    nonce: tradepressData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }
    });
    
    // Archive source
    $('.archive-source').on('click', function(e) {
        e.preventDefault();
        
        if (confirm(tradepressData.confirmArchive)) {
            var sourceId = $(this).data('id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tradepress_archive_source',
                    source_id: sourceId,
                    nonce: tradepressData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }
    });
    
    // Toggle source status
    $('.toggle-status').on('click', function(e) {
        e.preventDefault();
        
        var sourceId = $(this).data('id');
        var action = $(this).data('action');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_source_status',
                source_id: sourceId,
                status_action: action,
                nonce: tradepressData.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Fetch now
    $('.fetch-now').on('click', function(e) {
        e.preventDefault();
        
        var sourceId = $(this).data('id');
        var $button = $(this);
        
        $button.text(tradepressData.fetchingText).prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_fetch_source',
                source_id: sourceId,
                nonce: tradepressData.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.data.message);
                    $button.text(tradepressData.fetchNowText).prop('disabled', false);
                }
            }
        });
    });
    
    // Source form functionality
    if (typeof tradepressData.sourceTypes !== 'undefined') {
        var sourceTypes = tradepressData.sourceTypes;
        
        function updateSourceTypeInfo() {
            var selectedType = $('#source_type').val();
            var typeInfo = sourceTypes[selectedType];
            
            $('.source-type-description-text').text(typeInfo.description);
            
            if (!typeInfo.ready) {
                $('.source-type-notice').html('<p><strong>Note:</strong> ' + typeInfo.notice + '</p>').show();
                $('#submit-source').prop('disabled', true);
                $('#source_url').prop('disabled', true);
            } else {
                $('.source-type-notice').hide();
                $('#submit-source').prop('disabled', false);
                $('#source_url').prop('disabled', false);
            }
        }
        
        updateSourceTypeInfo();
        
        $('#source_type').on('change', function() {
            var sourceType = $(this).val();
            
            updateSourceTypeInfo();
            
            $('.source-url-description').hide();
            $('.source-url-description[data-type="' + sourceType + '"]').show();
            
            $('.source-settings-row').hide();
            $('.' + sourceType + '-settings').show();
        });
        
        $('#api_auth_type').on('change', function() {
            var authType = $(this).val();
            
            $('#api_key_fields, #bearer_fields, #basic_auth_fields').hide();
            
            if (authType === 'api_key') {
                $('#api_key_fields').show();
            } else if (authType === 'bearer') {
                $('#bearer_fields').show();
            } else if (authType === 'basic') {
                $('#basic_auth_fields').show();
            }
        });
        
        // Test source
        $('#test-source').on('click', function(e) {
            e.preventDefault();
            
            var selectedType = $('#source_type').val();
            if (!sourceTypes[selectedType].ready) {
                alert(tradepressData.sourceNotAvailable);
                return false;
            }
            
            var $button = $(this);
            var formData = $('#tradepress-source-form').serialize();
            
            $button.prop('disabled', true).html(tradepressData.testingText);
            
            $('#source-test-results .source-test-content').empty();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData + '&action=tradepress_test_source',
                success: function(response) {
                    $button.prop('disabled', false).html(tradepressData.testSourceText);
                    
                    if (response.success) {
                        var resultHtml = '<div class="test-success">✓ Source tested successfully!</div>';
                        resultHtml += '<div class="test-sample-heading">Sample Data:</div>';
                        
                        if (typeof response.data.sample === 'object') {
                            resultHtml += '<pre>' + JSON.stringify(response.data.sample, null, 2) + '</pre>';
                        } else {
                            resultHtml += '<pre>' + response.data.sample + '</pre>';
                        }
                        
                        if (response.data.notes) {
                            resultHtml += '<div class="test-notes"><strong>Notes:</strong><br>' + response.data.notes + '</div>';
                        }
                        
                        $('#source-test-results .source-test-content').html(resultHtml);
                    } else {
                        var errorHtml = '<div class="test-error">✗ Source test failed</div>';
                        errorHtml += '<div class="test-error-message"><strong>Error:</strong> ' + response.data.message + '</div>';
                        
                        if (response.data.trace) {
                            errorHtml += '<div class="test-error-trace"><strong>Details:</strong><br>' + response.data.trace + '</div>';
                        }
                        
                        if (response.data.suggestion) {
                            errorHtml += '<div class="test-suggestion"><strong>Suggestion:</strong><br>' + response.data.suggestion + '</div>';
                        }
                        
                        $('#source-test-results .source-test-content').html(errorHtml);
                    }
                    
                    $('#source-test-results').show();
                },
                error: function(xhr, status, error) {
                    $button.prop('disabled', false).html(tradepressData.testSourceText);
                    
                    $('#source-test-results .source-test-content').html('<div class="test-error">✗ Source test failed</div><div class="test-error-message">' + error + '</div>');
                    $('#source-test-results').show();
                }
            });
        });
        
        $('#save-tested-source').on('click', function() {
            $('#tradepress-source-form').submit();
        });
        
        $('#close-test-results').on('click', function() {
            $('#source-test-results').hide();
        });
    }
    
    // Transient Caches functionality
    // Refresh data
    $('.action-refresh').on('click', function() {
        location.reload();
    });
    
    // View cache contents
    $('.view-cache').on('click', function() {
        const cacheKey = $(this).data('key');
        const cacheName = $(this).closest('tr').find('td:nth-child(2) strong').text();
        
        $('#modal-cache-name').text(cacheName);
        $('.cache-details-loader').show();
        $('#cache-contents-container').hide();
        $('.purge-cache-modal').data('key', cacheKey);
        $('#cache-details-modal').show();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_get_cache_contents',
                nonce: tradepressData.nonce,
                cache_key: cacheKey
            },
            success: function(response) {
                $('.cache-details-loader').hide();
                if (response.success) {
                    displayCacheContents(response.data, $('#cache-contents-container'));
                } else {
                    $('#cache-contents-container').html('<p class="error">' + response.data + '</p>').show();
                }
            },
            error: function() {
                $('.cache-details-loader').hide();
                $('#cache-contents-container').html('<p class="error">Error loading cache contents.</p>').show();
            }
        });
    });
    
    // Purge single cache
    $('.purge-cache, .purge-cache-modal').on('click', function() {
        const cacheKey = $(this).data('key');
        if (!cacheKey) return;
        
        if (!confirm(tradepressData.confirmPurgeCache || 'Are you sure you want to purge this cache?')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_purge_cache',
                nonce: tradepressData.nonce,
                cache_key: cacheKey
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });
    
    // Purge all caches
    $('.action-purge-all').on('click', function() {
        if (!confirm(tradepressData.confirmPurgeAllCaches || 'Are you sure you want to purge ALL TradePress caches?')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_purge_all_caches',
                nonce: tradepressData.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });
    
    // Display cache contents based on data type
    function displayCacheContents(data, container) {
        container.empty();
        
        if (typeof data === 'object' && data !== null) {
            if (Array.isArray(data)) {
                container.append('<p><strong>Array</strong> (Items: ' + data.length + ')</p>');
                data.forEach(function(item, index) {
                    const itemDiv = $('<div class="cache-item"></div>');
                    itemDiv.append('<div class="cache-item-key">[' + index + ']</div>');
                    itemDiv.append('<div class="cache-item-value">' + formatValue(item) + '</div>');
                    container.append(itemDiv);
                });
            } else {
                container.append('<p><strong>Object</strong></p>');
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        const itemDiv = $('<div class="cache-item"></div>');
                        itemDiv.append('<div class="cache-item-key">' + key + '</div>');
                        itemDiv.append('<div class="cache-item-value">' + formatValue(data[key]) + '</div>');
                        container.append(itemDiv);
                    }
                }
            }
        } else {
            container.append('<p>' + formatValue(data) + '</p>');
        }
        
        container.show();
    }
    
    // Format value based on type
    function formatValue(value) {
        if (value === null) return '<em>null</em>';
        if (value === undefined) return '<em>undefined</em>';
        
        switch (typeof value) {
            case 'object':
                if (Array.isArray(value)) {
                    return '<em>Array</em> (' + value.length + ' items)';
                }
                return '<em>Object</em>';
            case 'string':
                if (value.length > 100) {
                    return $('<div></div>').text(value.substring(0, 100) + '...').html();
                }
                return $('<div></div>').text(value).html();
            case 'number':
            case 'boolean':
                return String(value);
            default:
                return typeof value;
        }
    }
});