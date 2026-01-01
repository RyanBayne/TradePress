/**
 * TradePress - Discord Settings Scripts
 *
 * @package  TradePress/assets/js
 * @since    1.0.0
 */

jQuery(document).ready(function($) {
    // Toggle password visibility for sensitive fields
    $('.form-table input[type="password"]').each(function() {
        const id = $(this).attr('id');
        $(this).after('<button type="button" class="toggle-password button button-small" data-target="' + id + '"><span class="dashicons dashicons-visibility"></span></button>');
    });
    
    $('.toggle-password').click(function(e) {
        e.preventDefault();
        const targetId = $(this).data('target');
        const input = $('#' + targetId);
        const icon = $(this).find('.dashicons');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });
    
    // Refresh status panel
    $('#refresh_discord_status').click(function() {
        refreshStatusPanel();
    });
    
    // Test connection
    $('#test_discord_connection').click(function() {
        const botToken = $('#discord_bot_token').val();
        if (!botToken) {
            alert(TRADEPRESS_DISCORD_settings.strings.enter_token);
            return;
        }
        
        $(this).prop('disabled', true);
        $(this).find('.dashicons').addClass('dashicons-rotation');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_test_discord_connection',
                nonce: TRADEPRESS_DISCORD_settings.nonces.test,
                bot_token: botToken
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.valid) {
                        let message = TRADEPRESS_DISCORD_settings.strings.connection_success;
                        if (response.data.bot_info) {
                            message += '\n\n' + TRADEPRESS_DISCORD_settings.strings.bot_name + ' ' + response.data.bot_info.name;
                            message += '\n' + TRADEPRESS_DISCORD_settings.strings.bot_id + ' ' + response.data.bot_info.id;
                        }
                        alert(message);
                    } else {
                        alert(TRADEPRESS_DISCORD_settings.strings.connection_failed + ' ' + response.data.message);
                    }
                } else {
                    alert(TRADEPRESS_DISCORD_settings.strings.error_testing);
                }
                
                refreshStatusPanel();
            },
            complete: function() {
                $('#test_discord_connection').prop('disabled', false);
                $('#test_discord_connection').find('.dashicons').removeClass('dashicons-rotation');
            }
        });
    });
    
    function refreshStatusPanel() {
        const button = $('#refresh_discord_status');
        button.prop('disabled', true);
        
        // Add spinning animation to the refresh icon
        button.find('.dashicons').addClass('dashicons-rotation');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_get_discord_status',
                nonce: TRADEPRESS_DISCORD_settings.nonces.status
            },
            success: function(response) {
                if (response.success) {
                    // Replace the entire status container with new HTML
                    $('#discord_status_container').html(response.data.html);
                    
                    // Reattach click event to the new button
                    $('#refresh_discord_status').click(function() {
                        refreshStatusPanel();
                    });
                    
                    $('#test_discord_connection').click(function() {
                        const botToken = $('#discord_bot_token').val();
                        if (!botToken) {
                            alert(TRADEPRESS_DISCORD_settings.strings.enter_token);
                            return;
                        }
                        
                        $(this).prop('disabled', true);
                        $(this).find('.dashicons').addClass('dashicons-rotation');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'tradepress_test_discord_connection',
                                nonce: TRADEPRESS_DISCORD_settings.nonces.test,
                                bot_token: botToken
                            },
                            success: function(response) {
                                if (response.success) {
                                    if (response.data.valid) {
                                        let message = TRADEPRESS_DISCORD_settings.strings.connection_success;
                                        if (response.data.bot_info) {
                                            message += '\n\n' + TRADEPRESS_DISCORD_settings.strings.bot_name + ' ' + response.data.bot_info.name;
                                            message += '\n' + TRADEPRESS_DISCORD_settings.strings.bot_id + ' ' + response.data.bot_info.id;
                                        }
                                        alert(message);
                                    } else {
                                        alert(TRADEPRESS_DISCORD_settings.strings.connection_failed + ' ' + response.data.message);
                                    }
                                } else {
                                    alert(TRADEPRESS_DISCORD_settings.strings.error_testing);
                                }
                                
                                refreshStatusPanel();
                            },
                            complete: function() {
                                $('#test_discord_connection').prop('disabled', false);
                                $('#test_discord_connection').find('.dashicons').removeClass('dashicons-rotation');
                            }
                        });
                    });
                } else {
                    alert(TRADEPRESS_DISCORD_settings.strings.error_refreshing);
                }
            },
            complete: function() {
                button.prop('disabled', false);
                button.find('.dashicons').removeClass('dashicons-rotation');
            }
        });
    }
    
    // Real-time validation of bot token as user types
    let typingTimer;
    const doneTypingInterval = 1000; // wait 1 second after user stops typing
    
    $('#discord_bot_token').on('keyup', function() {
        clearTimeout(typingTimer);
        if ($(this).val()) {
            typingTimer = setTimeout(validateBotToken, doneTypingInterval);
        }
    });
    
    function validateBotToken() {
        const token = $('#discord_bot_token').val();
        if (!token) return;
        
        // Simple client-side format validation first
        const validFormat = /^[NM][a-zA-Z0-9_-]{23,}\.[\\w-]{6}\.[\\w-]{27}$/;
        if (!validFormat.test(token)) {
            // Add inline warning
            if (!$('#token-format-warning').length) {
                $('#discord_bot_token').after('<p id="token-format-warning" class="description" style="color: #dc3232;">' + TRADEPRESS_DISCORD_settings.strings.token_warning + '</p>');
            }
            return;
        } else {
            // Remove warning if format is valid
            $('#token-format-warning').remove();
        }
    }
    
    // Accordion functionality
    $('.accordion-header').on('click', function() {
        const content = $(this).next('.accordion-content');
        const toggle = $(this).find('.accordion-toggle');
        const isExpanded = toggle.attr('aria-expanded') === 'true';
        
        if (isExpanded) {
            content.slideUp();
            toggle.attr('aria-expanded', 'false').find('.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        } else {
            content.slideDown();
            toggle.attr('aria-expanded', 'true').find('.dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        }
    });
});