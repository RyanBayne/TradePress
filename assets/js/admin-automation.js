/**
 * TradePress Automation Admin JavaScript
 * 
 * This script is part of controlling the admin side automation of the TradePress Automation module.
 * It will run each attached service and display the results. Including scoring, trading signals, runtime, and trading actions. 
 * 
 * @since 1.0.0
 */
jQuery(document).ready(function($) {
    // Algorithm Start/Stop Toggle
    $('.start-algorithm-button').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var currentAction = $btn.data('action');
        
        if (currentAction === 'stop' && !confirm(tradepress_automation.confirm_stop)) {
            return;
        }
        
        $btn.prop('disabled', true);
        $btn.text(currentAction === 'start' ? 'Starting...' : 'Stopping...');
        
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_algorithm',
                algorithm_action: currentAction,
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (currentAction === 'start') {
                        $btn.data('action', 'stop').text('Stop Algorithm').removeClass('button-start').addClass('button-stop');
                        $('.status-indicator').removeClass('status-stopped').addClass('status-running');
                        $('.status-text').text('Running');
                        
                        // Start updating the metrics periodically
                        startMetricsRefresh();
                    } else {
                        $btn.data('action', 'start').text('Start Algorithm').removeClass('button-stop').addClass('button-start');
                        $('.status-indicator').removeClass('status-running').addClass('status-stopped');
                        $('.status-text').text('Stopped');
                        
                        // Stop updating metrics
                        stopMetricsRefresh();
                    }
                    
                    // Update metrics immediately
                    updateMetrics();
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
    
    // Component toggle buttons
    $('.toggle-component-button').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var component = $btn.data('component');
        var currentAction = $btn.data('action');
        
        if (currentAction === 'stop' && !confirm(tradepress_automation.confirm_stop)) {
            return;
        }
        
        $btn.prop('disabled', true);
        
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_component',
                component: component,
                action_type: currentAction,
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success) {
                    var $statusIndicator = $btn.closest('.component-card').find('.status-indicator');
                    var $statusText = $btn.closest('.component-card').find('.status-text');
                    
                    if (currentAction === 'start') {
                        $btn.data('action', 'stop').text('Stop ' + capitalizeFirstLetter(component));
                        $btn.removeClass('button-primary').addClass('button-secondary');
                        $statusIndicator.removeClass('status-stopped').addClass('status-running');
                        $statusText.text('Running');
                    } else {
                        $btn.data('action', 'start').text('Start ' + capitalizeFirstLetter(component));
                        $btn.removeClass('button-secondary').addClass('button-primary');
                        $statusIndicator.removeClass('status-running').addClass('status-stopped');
                        $statusText.text('Stopped');
                    }
                    
                    // Start updating dashboard metrics
                    startDashboardRefresh();
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
    
    // Start all processes
    $('#start-all-processes').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Starting...');
        
        // Start data import first
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_start_data_import',
                import_type: 'all',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Then start scoring process
                    $.ajax({
                        url: tradepress_automation.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'tradepress_start_scoring_process',
                            process_type: 'all',
                            nonce: tradepress_automation.nonce
                        },
                        success: function(scoringResponse) {
                            if (scoringResponse.success) {
                                // Update UI to show both processes running
                                updateProcessStatuses('running');
                                startDashboardRefresh();
                                startFallbackRuntimeTimer();
                            } else {
                                alert('Failed to start scoring process: ' + (scoringResponse.data ? scoringResponse.data.message : 'Unknown error'));
                            }
                        },
                        error: function() {
                            alert('Connection error starting scoring process.');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).text('Start Both Processes');
                        }
                    });
                } else {
                    alert('Failed to start data import: ' + (response.data ? response.data.message : 'Unknown error'));
                    $btn.prop('disabled', false).text('Start Both Processes');
                }
            },
            error: function() {
                alert('Connection error starting data import.');
                $btn.prop('disabled', false).text('Start Both Processes');
            }
        });
    });
    
    // Stop all processes
    $('#stop-all-processes').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to stop both background processes?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Stopping...');
        
        // Stop both processes
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_stop_data_import',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                $.ajax({
                    url: tradepress_automation.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tradepress_stop_scoring_process',
                        nonce: tradepress_automation.nonce
                    },
                    success: function(scoringResponse) {
                        // Update UI to show both processes stopped
                        updateProcessStatuses('stopped');
                        stopDashboardRefresh();
                        if (fallbackRuntimeTimer) {
                            clearInterval(fallbackRuntimeTimer);
                            fallbackRuntimeTimer = null;
                        }
                        lastKnownRuntimes.data_import = '00:00:00';
                        lastKnownRuntimes.scoring = '00:00:00';
                    },
                    error: function() {
                        alert('Error stopping scoring process.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Stop Both Processes');
                    }
                });
            },
            error: function() {
                alert('Error stopping data import.');
                $btn.prop('disabled', false).text('Stop Both Processes');
            }
        });
    });
    
    // Helper function to update process statuses
    function updateProcessStatuses(status) {
        $('.component-card').each(function() {
            var $card = $(this);
            var $statusIndicator = $card.find('.status-indicator');
            var $statusText = $card.find('.status-text');
            
            if (status === 'running') {
                $statusIndicator.removeClass('status-stopped').addClass('status-running');
                $statusText.text('Running');
            } else {
                $statusIndicator.removeClass('status-running').addClass('status-stopped');
                $statusText.text('Stopped');
            }
        });
        
        // Update coordination status
        var $coordStatus = $('.coordination-card .status-indicator');
        var $coordText = $('.coordination-card .status-text');
        if (status === 'running') {
            $coordStatus.removeClass('status-independent').addClass('status-coordinated');
            $coordText.text('Coordinated');
        } else {
            $coordStatus.removeClass('status-coordinated').addClass('status-independent');
            $coordText.text('Independent');
        }
    }
    
    var metricsTimer;
    
    function startMetricsRefresh() {
        // Clear any existing timer
        stopMetricsRefresh();
        
        // Update metrics every 5 seconds
        metricsTimer = setInterval(updateMetrics, 5000);
    }
    
    function stopMetricsRefresh() {
        if (metricsTimer) {
            clearInterval(metricsTimer);
            metricsTimer = null;
        }
    }
    
    function updateMetrics() {
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_get_algorithm_metrics',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#symbols-processed').text(response.data.symbols_processed || 0);
                    $('#scores-generated').text(response.data.scores_generated || 0);
                    $('#trade-signals').text(response.data.trade_signals || 0);
                    $('#algorithm-runtime').text(response.data.runtime || '00:00:00');
                }
            },
            error: function(xhr, status, error) {
                console.log('Algorithm metrics update failed:', error);
            }
        });
    }
    
    var dashboardTimer;
    
    function startDashboardRefresh() {
        // Clear any existing timer
        stopDashboardRefresh();
        
        // Update metrics right away
        updateDashboardMetrics();
        
        // Update metrics every 5 seconds
        dashboardTimer = setInterval(updateDashboardMetrics, 5000);
    }
    
    function stopDashboardRefresh() {
        if (dashboardTimer) {
            clearInterval(dashboardTimer);
            dashboardTimer = null;
        }
    }
    
    function updateDashboardMetrics() {
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_get_dashboard_metrics',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update data import runtime and store for fallback
                    var dataImportRuntime = response.data.data_import_runtime || '00:00:00';
                    $('#dashboard-data-import-runtime').text(dataImportRuntime);
                    lastKnownRuntimes.data_import = dataImportRuntime;
                    
                    // Update scoring metrics and store for fallback
                    var scoringRuntime = response.data.scoring_runtime || '00:00:00';
                    $('#dashboard-scoring-runtime').text(scoringRuntime);
                    lastKnownRuntimes.scoring = scoringRuntime;
                    
                    $('#dashboard-symbols-processed').text(response.data.symbols_processed || 0);
                    $('#dashboard-scores-generated').text(response.data.scores_generated || 0);
                    
                    // Update health scores if available
                    if (response.data.data_import_health && response.data.data_import_health.health_score) {
                        $('#dashboard-data-import-health').text(response.data.data_import_health.health_score + '%');
                    }
                    
                    if (response.data.scoring_health && response.data.scoring_health.health_score) {
                        $('#dashboard-scoring-health').text(response.data.scoring_health.health_score + '%');
                    }
                    
                    // Update overall health if available
                    if (response.data.overall_health) {
                        $('#overall-health-score').text(response.data.overall_health.score + '%');
                        $('#system-status').text(capitalizeFirstLetter(response.data.overall_health.status));
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Dashboard metrics update failed:', error);
            }
        });
    }
    
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Enhanced error handling functions
    function checkSystemHealth() {
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_get_system_health',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update health displays
                    if (response.data.data_import && response.data.data_import.health_score) {
                        $('#dashboard-data-import-health').text(response.data.data_import.health_score + '%');
                    }
                    
                    if (response.data.scoring && response.data.scoring.health_score) {
                        $('#dashboard-scoring-health').text(response.data.scoring.health_score + '%');
                    }
                    
                    if (response.data.overall) {
                        $('#overall-health-score').text(response.data.overall.score + '%');
                        $('#system-status').text(capitalizeFirstLetter(response.data.overall.status));
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Health check failed:', error);
            }
        });
    }
    
    // If algorithm is running on page load, start updating metrics
    if (tradepress_automation.is_running) {
        startMetricsRefresh();
    }
    
    // Add handler for "Start All Automation" button
    $('#start-all-automation').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Starting All...');
        
        // Start all automation components
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_all_automation',
                action_type: 'start',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateProcessStatuses('running');
                    startDashboardRefresh();
                } else {
                    alert('Failed to start all automation: ' + (response.data ? response.data.message : 'Unknown error'));
                }
            },
            error: function() {
                alert('Connection error starting all automation.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Start All Automation');
            }
        });
    });
    
    // Add handler for "Stop All Automation" button
    $('#stop-all-automation').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to stop all automation?')) {
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).text('Stopping All...');
        
        // Stop all automation components
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_toggle_all_automation',
                action_type: 'stop',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                updateProcessStatuses('stopped');
                stopDashboardRefresh();
            },
            error: function() {
                alert('Error stopping all automation.');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Stop All Automation');
            }
        });
    });
    
    // Check if data import is running on page load
    if (typeof tradepress_data_import_running !== 'undefined' && tradepress_data_import_running) {
        startDataImportRefresh();
    }
    
    // Check if scoring process is running on page load
    if (typeof tradepress_scoring_running !== 'undefined' && tradepress_scoring_running) {
        startScoringProcessRefresh();
    }
    
    // If any component is running on page load, start updating dashboard metrics
    if (tradepress_automation.any_running) {
        startDashboardRefresh();
    }
    
    // Start health monitoring
    checkSystemHealth();
    setInterval(checkSystemHealth, 30000); // Check every 30 seconds
    
    // Fallback mechanism to ensure runtime counters keep updating
    // This provides a client-side backup if AJAX calls fail
    var fallbackRuntimeTimer;
    var lastKnownRuntimes = {
        data_import: '00:00:00',
        scoring: '00:00:00'
    };
    
    function startFallbackRuntimeTimer() {
        if (fallbackRuntimeTimer) {
            clearInterval(fallbackRuntimeTimer);
        }
        
        fallbackRuntimeTimer = setInterval(function() {
            // Only update if elements exist and processes are running
            var $dataImportRuntime = $('#dashboard-data-import-runtime');
            var $scoringRuntime = $('#dashboard-scoring-runtime');
            
            if ($dataImportRuntime.length && lastKnownRuntimes.data_import !== '00:00:00') {
                var newRuntime = incrementRuntime(lastKnownRuntimes.data_import);
                $dataImportRuntime.text(newRuntime);
                lastKnownRuntimes.data_import = newRuntime;
            }
            
            if ($scoringRuntime.length && lastKnownRuntimes.scoring !== '00:00:00') {
                var newRuntime = incrementRuntime(lastKnownRuntimes.scoring);
                $scoringRuntime.text(newRuntime);
                lastKnownRuntimes.scoring = newRuntime;
            }
        }, 1000); // Update every second
    }
    
    function incrementRuntime(timeString) {
        var parts = timeString.split(':');
        var hours = parseInt(parts[0], 10);
        var minutes = parseInt(parts[1], 10);
        var seconds = parseInt(parts[2], 10);
        
        seconds++;
        if (seconds >= 60) {
            seconds = 0;
            minutes++;
            if (minutes >= 60) {
                minutes = 0;
                hours++;
            }
        }
        
        return (hours < 10 ? '0' + hours : hours) + ':' +
               (minutes < 10 ? '0' + minutes : minutes) + ':' +
               (seconds < 10 ? '0' + seconds : seconds);
    }
    
    // Start fallback timer if any processes are running
    if (tradepress_automation.any_running) {
        startFallbackRuntimeTimer();
    }
    
    // Data Import Process Controls
    $('.start-data-import-button').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var currentAction = $btn.data('action');
        var importType = $('#import-type-select').val() || 'all';
        
        $btn.prop('disabled', true);
        $btn.text(currentAction === 'start' ? 'Starting...' : 'Stopping...');
        
        var ajaxAction = currentAction === 'start' ? 'tradepress_start_data_import' : 'tradepress_stop_data_import';
        
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: ajaxAction,
                import_type: importType,
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (currentAction === 'start') {
                        $btn.data('action', 'stop').text('Stop Data Import');
                        $('.status-indicator').removeClass('status-stopped').addClass('status-running');
                        $('.status-text').text('Running');
                        startDataImportRefresh();
                    } else {
                        $btn.data('action', 'start').text('Start Data Import');
                        $('.status-indicator').removeClass('status-running').addClass('status-stopped');
                        $('.status-text').text('Stopped');
                        stopDataImportRefresh();
                    }
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
    
    // Scoring Process Controls
    $('.start-scoring-process-button').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var currentAction = $btn.data('action');
        var processType = $('#scoring-process-type-select').val() || 'all';
        
        $btn.prop('disabled', true);
        $btn.text(currentAction === 'start' ? 'Starting...' : 'Stopping...');
        
        var ajaxAction = currentAction === 'start' ? 'tradepress_start_scoring_process' : 'tradepress_stop_scoring_process';
        
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: ajaxAction,
                process_type: processType,
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (currentAction === 'start') {
                        $btn.data('action', 'stop').text('Stop Scoring Process');
                        $('.status-indicator').removeClass('status-stopped').addClass('status-running');
                        $('.status-text').text('Running');
                        startScoringProcessRefresh();
                    } else {
                        $btn.data('action', 'start').text('Start Scoring Process');
                        $('.status-indicator').removeClass('status-running').addClass('status-stopped');
                        $('.status-text').text('Stopped');
                        stopScoringProcessRefresh();
                    }
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
    
    // Data Import Status Refresh
    var dataImportTimer;
    
    function startDataImportRefresh() {
        stopDataImportRefresh();
        updateDataImportStatus();
        dataImportTimer = setInterval(updateDataImportStatus, 5000);
    }
    
    function stopDataImportRefresh() {
        if (dataImportTimer) {
            clearInterval(dataImportTimer);
            dataImportTimer = null;
        }
    }
    
    function updateDataImportStatus() {
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_get_data_import_status',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#data-import-runtime').text(response.data.runtime || '00:00:00');
                }
            }
        });
    }
    
    // Scoring Process Status Refresh
    var scoringProcessTimer;
    
    function startScoringProcessRefresh() {
        stopScoringProcessRefresh();
        updateScoringProcessStatus();
        scoringProcessTimer = setInterval(updateScoringProcessStatus, 5000);
    }
    
    function stopScoringProcessRefresh() {
        if (scoringProcessTimer) {
            clearInterval(scoringProcessTimer);
            scoringProcessTimer = null;
        }
    }
    
    function updateScoringProcessStatus() {
        $.ajax({
            url: tradepress_automation.ajax_url,
            type: 'POST',
            data: {
                action: 'tradepress_get_scoring_process_status',
                nonce: tradepress_automation.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#scoring-process-runtime').text(response.data.runtime || '00:00:00');
                    if (response.data.metrics) {
                        $('#scoring-symbols-processed').text(response.data.metrics.symbols_processed || 0);
                        $('#scoring-scores-generated').text(response.data.metrics.scores_generated || 0);
                        $('#scoring-signals-generated').text(response.data.metrics.signals_generated || 0);
                    }
                }
            }
        });
    }

    // Add new scoring directive
    $('.add-directive').on('click', function() {
        var $tbody = $(this).closest('tbody');
        var $lastRow = $tbody.find('tr').eq(-2); // Get the row before the "Add" button row
        var timestamp = Date.now();
        
        var newRow = '<tr>' +
            '<td><input type="text" name="directives[new_' + timestamp + '][name]" class="regular-text" placeholder="Indicator Name" required></td>' +
            '<td><input type="number" name="directives[new_' + timestamp + '][weight]" value="10" min="0" max="100" class="small-text directive-weight">%</td>' +
            '<td><input type="text" name="directives[new_' + timestamp + '][bullish]" class="regular-text"></td>' +
            '<td><input type="text" name="directives[new_' + timestamp + '][bearish]" class="regular-text"></td>' +
            '<td><input type="checkbox" name="directives[new_' + timestamp + '][active]" value="1" checked></td>' +
            '</tr>';
        
        $lastRow.after(newRow);
        updateTotalWeight();
    });
    
    // Update total weight when weight inputs change
    $(document).on('change', 'input[name*="[weight]"]', function() {
        updateTotalWeight();
    });
    
    // Function to calculate and update total weight
    function updateTotalWeight() {
        var total = 0;
        $('input[name*="[weight]"]').each(function() {
            total += parseInt($(this).val()) || 0;
        });
        
        $('#total-weight').text(total);
        
        if (total != 100) {
            $('.weight-warning').show();
        } else {
            $('.weight-warning').hide();
        }
    }
    
    // Automation Dashboard functionality
    // Tab switching
    $('.tradepress-diagnostic-tab').on('click', function() {
        var tab = $(this).data('tab');
        
        $('.tradepress-diagnostic-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.tradepress-diagnostic-content').hide();
        $('#tradepress-tab-' + tab).show();
    });
    
    // Schedule type change
    $('#tradepress-schedule-type').on('change', function() {
        var type = $(this).val();
        
        $('.tradepress-market-hours-options, .tradepress-daily-options').hide();
        
        if (type === 'market_hours') {
            $('.tradepress-market-hours-options').show();
        } else if (type === 'daily') {
            $('.tradepress-daily-options').show();
        }
    });
    
    // Refresh diagnostics
    $('#tradepress-refresh-diagnostics').on('click', function() {
        var $button = $(this);
        $button.addClass('spin');
        
        $.ajax({
            url: ajaxurl,
            data: {
                action: 'tradepress_refresh_diagnostics',
                security: $('#tradepress-diagnostics-nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.logs) {
                        updateLogEntries(response.data.logs);
                    }
                    if (response.data.metrics) {
                        updateMetrics(response.data.metrics);
                    }
                    if (response.data.api_calls) {
                        updateApiCalls(response.data.api_calls);
                    }
                }
                $button.removeClass('spin');
            },
            error: function() {
                $button.removeClass('spin');
                alert('Error refreshing diagnostic data');
            }
        });
    });
    
    // Algorithm control
    $('#tradepress-start-algorithm').on('click', function() {
        $(this).prop('disabled', true);
        $('#tradepress-stop-algorithm').prop('disabled', false);
        $('#tradepress-algorithm-status-text').text('Running');
        
        startRuntimeTimer();
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'tradepress_start_algorithm',
                security: $('#tradepress-algorithm-nonce').val()
            }
        });
    });
    
    $('#tradepress-stop-algorithm').on('click', function() {
        $(this).prop('disabled', true);
        $('#tradepress-start-algorithm').prop('disabled', false);
        $('#tradepress-algorithm-status-text').text('Stopping...');
        
        stopRuntimeTimer();
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'tradepress_stop_algorithm',
                security: $('#tradepress-algorithm-nonce').val()
            },
            success: function() {
                $('#tradepress-algorithm-status-text').text('Idle');
            }
        });
    });
    
    // Runtime timer
    var runtimeInterval;
    var runtimeSeconds = 0;
    
    function startRuntimeTimer() {
        runtimeSeconds = 0;
        updateRuntimeDisplay();
        
        runtimeInterval = setInterval(function() {
            runtimeSeconds++;
            updateRuntimeDisplay();
        }, 1000);
    }
    
    function stopRuntimeTimer() {
        clearInterval(runtimeInterval);
    }
    
    function updateRuntimeDisplay() {
        var hours = Math.floor(runtimeSeconds / 3600);
        var minutes = Math.floor((runtimeSeconds % 3600) / 60);
        var seconds = runtimeSeconds % 60;
        
        var display = 
            (hours < 10 ? '0' + hours : hours) + ':' +
            (minutes < 10 ? '0' + minutes : minutes) + ':' +
            (seconds < 10 ? '0' + seconds : seconds);
        
        $('#tradepress-algorithm-runtime').text(display);
    }
    
    // Helper functions for diagnostic updates
    function updateLogEntries(logs) {
        var $container = $('.tradepress-log-entries');
        $container.empty();
        
        if (logs.length === 0) {
            $container.html('<div class="tradepress-empty-logs">No log entries found.</div>');
            return;
        }
        
        logs.forEach(function(entry) {
            var html = '<div class="tradepress-log-entry ' + entry.level + '">' +
                       '<span class="tradepress-log-timestamp">' + entry.timestamp + '</span> ' +
                       '<span class="tradepress-log-level">[' + entry.level.toUpperCase() + ']</span> ' +
                       '<span class="tradepress-log-category">[' + entry.category + ']</span> ' +
                       '<span class="tradepress-log-message">' + entry.message + '</span>' +
                       '</div>';
            $container.append(html);
        });
    }
    
    function updateMetrics(metrics) {
        $('#metric-exec-time-current').text(metrics.execution_time.current);
        $('#metric-exec-time-avg').text(metrics.execution_time.average);
        $('#metric-exec-time-peak').text(metrics.execution_time.peak);
        
        $('#metric-memory-current').text(metrics.memory_usage.current);
        $('#metric-memory-avg').text(metrics.memory_usage.average);
        $('#metric-memory-peak').text(metrics.memory_usage.peak);
        
        $('#metric-queries-current').text(metrics.database_queries.current);
        $('#metric-queries-avg').text(metrics.database_queries.average);
        $('#metric-queries-peak').text(metrics.database_queries.peak);
        
        $('#metric-api-current').text(metrics.api_calls.current);
        $('#metric-api-avg').text(metrics.api_calls.average);
        $('#metric-api-peak').text(metrics.api_calls.peak);
        
        $('#stat-symbols-processed').text(metrics.algorithm.symbols_processed);
        $('#stat-directives-active').text(metrics.algorithm.directives_active);
        $('#stat-highest-score').text(metrics.algorithm.highest_score);
        $('#stat-avg-score').text(metrics.algorithm.average_score);
    }
    
    function updateApiCalls(apiCalls) {
        var $container = $('.tradepress-api-calls-list');
        $container.empty();
        
        if (apiCalls.length === 0) {
            $container.html('<div class="tradepress-empty-api-calls">No API calls recorded</div>');
            return;
        }
        
        var html = '<table class="tradepress-metrics-table">' +
                   '<tr><th>Time</th><th>API</th><th>Endpoint</th><th>Status</th><th>Duration</th></tr>';
        
        apiCalls.forEach(function(call) {
            html += '<tr>' +
                    '<td>' + call.timestamp + '</td>' +
                    '<td>' + call.api + '</td>' +
                    '<td>' + call.endpoint + '</td>' +
                    '<td>' + call.status + '</td>' +
                    '<td>' + call.duration + 'ms</td>' +
                    '</tr>';
        });
        
        html += '</table>';
        $container.html(html);
    }
});
