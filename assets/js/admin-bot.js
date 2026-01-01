/**
 * TradePress Trading Bot Admin JavaScript
 * 
 * This script is used for the admin interface of the TradePress Trading Bot plugin.
 * It is not for scoring, only for trading actions.
 * 
 * @version 1.0.0
 */
jQuery(document).ready(function($) {
    // Algorithm Start/Stop Toggle
    $('#toggle-algorithm').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var action = $btn.data('action');
        
        if (action === 'stop' && !confirm(tradepress_bot.confirm_stop)) {
            return;
        }
        
        $btn.prop('disabled', true).text(action === 'start' ? 'Starting...' : 'Stopping...');
        
        $.ajax({
            url: tradepress_bot.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_algorithm',
                action_type: action,
                nonce: tradepress_bot.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (action === 'start') {
                        $btn.data('action', 'stop').text(tradepress_bot.stop_text).removeClass('button-start').addClass('button-stop');
                        $('.status-indicator').removeClass('status-stopped').addClass('status-running');
                        $('.status-text').text('Running');
                        
                        // Start runtime timer
                        startRuntimeTimer();
                    } else {
                        $btn.data('action', 'start').text(tradepress_bot.start_text).removeClass('button-stop').addClass('button-start');
                        $('.status-indicator').removeClass('status-running').addClass('status-stopped');
                        $('.status-text').text('Stopped');
                        
                        // Stop runtime timer
                        stopRuntimeTimer();
                    }
                    
                    // Update runtime display if provided
                    if (response.data && response.data.runtime) {
                        $('#algorithm-runtime').text(response.data.runtime);
                    }
                    
                    // Auto-refresh metrics after a short delay
                    setTimeout(refreshMetrics, 1000);
                } else {
                    alert(response.data.message || 'An error occurred');
                }
            },
            error: function() {
                alert('Connection error. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Market hours toggle
    $('#market_hours_only').on('change', function() {
        if ($(this).is(':checked')) {
            $('#market-hours-settings').show();
        } else {
            $('#market-hours-settings').hide();
        }
    }).trigger('change');
    
    // Auto-refresh log
    if ($('#auto-refresh-log').length && $('#auto-refresh-log').is(':checked')) {
        setInterval(refreshLog, 30000); // 30 seconds
    }
    
    // Filter messages in Discord tab
    $('#message-filter').on('change', function() {
        if ($(this).val() === 'contains-symbol') {
            $('#filter-symbol').show();
        } else {
            $('#filter-symbol').hide();
        }
    });
    
    $('#apply-filter').on('click', function() {
        var filter = $('#message-filter').val();
        var $messages = $('.discord-message');
        
        if (filter === 'all') {
            $messages.show();
        } else if (filter === 'signals') {
            $messages.hide();
            $('.discord-message.is-signal').show();
        } else if (filter === 'contains-symbol') {
            var symbol = $('#filter-symbol').val().toUpperCase();
            if (!symbol) {
                return;
            }
            
            $messages.hide();
            $('.discord-message .detected-symbol').each(function() {
                if ($(this).text().toUpperCase().indexOf(symbol) !== -1) {
                    $(this).closest('.discord-message').show();
                }
            });
        }
    });
    
    // Algorithm metrics refresh
    function refreshMetrics() {
        if ($('#symbols-processed').length) {
            $.ajax({
                url: tradepress_bot.ajax_url,
                type: 'GET',
                data: {
                    action: 'tradepress_get_algorithm_stats',
                    nonce: tradepress_bot.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        $('#symbols-processed').text(response.data.symbols_processed || 0);
                        $('#api-calls').text(response.data.api_calls || 0);
                        $('#scores-generated').text(response.data.scores_generated || 0);
                        $('#trade-signals').text(response.data.trade_signals || 0);
                    }
                }
            });
        }
    }
    
    // Refresh log content
    function refreshLog() {
        $.ajax({
            url: tradepress_bot.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_refresh_log',
                level: $('#log-filter-level').val(),
                component: $('#log-filter-component').val(),
                _wpnonce: tradepress_bot.log_nonce
            },
            success: function(response) {
                $('#log-content').html(response);
            }
        });
    }
    
    // Runtime timer
    var runtimeTimer;
    var runtimeStart;
    
    function startRuntimeTimer() {
        stopRuntimeTimer(); // Clear any existing timer
        
        var currentRuntime = $('#algorithm-runtime').text();
        var timeParts = currentRuntime.split(':');
        var seconds = parseInt(timeParts[0]) * 3600 + parseInt(timeParts[1]) * 60 + parseInt(timeParts[2]);
        
        runtimeStart = new Date().getTime() - (seconds * 1000);
        
        runtimeTimer = setInterval(function() {
            var now = new Date().getTime();
            var distance = now - runtimeStart;
            
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            $('#algorithm-runtime').text(hours + ':' + minutes + ':' + seconds);
            
            // Refresh metrics every minute
            if (seconds === '00' && minutes !== '00') {
                refreshMetrics();
            }
        }, 1000);
    }
    
    function stopRuntimeTimer() {
        if (runtimeTimer) {
            clearInterval(runtimeTimer);
            runtimeTimer = null;
        }
    }
    
    // Initialize runtime timer if algorithm is running
    if (tradepress_bot.is_running) {
        startRuntimeTimer();
    }
    
    // Initialize tooltips
    if ($.fn.tooltip) {
        $('.tradepress-tooltip').tooltip();
    }
    
    // Handle tab persistence
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }
    
    // Score breakdown expandable sections
    $('.score-section-header').on('click', function() {
        $(this).next('.score-section-content').slideToggle();
        $(this).find('.toggle-icon').toggleClass('dashicons-arrow-down dashicons-arrow-up');
    });
    
    // Date range picker for trades
    if ($.fn.daterangepicker) {
        $('.date-range-picker').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            }
        });
        
        $('.date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        
        $('.date-range-picker').on('cancel.daterangepicker', function() {
            $(this).val('');
        });
    }
    
    // Initialize any charts if Chart.js is loaded
    if (typeof Chart !== 'undefined' && $('#performance-chart').length) {
        var ctx = document.getElementById('performance-chart').getContext('2d');
        
        // Sample data - would be replaced with real data from the backend
        var performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Algorithm',
                    data: [10, 15, 13, 17, 20, 25],
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Benchmark',
                    data: [10, 12, 11, 14, 16, 18],
                    borderColor: '#d63638',
                    backgroundColor: 'rgba(214, 54, 56, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Performance (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    }
    
    // Handle position details modal
    $('.view-position-details').on('click', function(e) {
        e.preventDefault();
        var symbolId = $(this).data('symbol-id');
        
        $.ajax({
            url: tradepress_bot.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_get_position_details',
                symbol_id: symbolId,
                nonce: tradepress_bot.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#position-details-content').html(response.data.html);
                    $('#position-details-modal').show();
                } else {
                    alert(response.data.message || 'Failed to load position details');
                }
            }
        });
    });
    
    // Close modal
    $('.modal-close').on('click', function() {
        $(this).closest('.modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });
    
    // Set initial active tab from URL
    var activeTab = getUrlParameter('tab');
    if (activeTab) {
        $('.nav-tab[href="#' + activeTab + '"]').addClass('nav-tab-active');
        $('#' + activeTab).show().siblings('.tab-content').hide();
    }
});
