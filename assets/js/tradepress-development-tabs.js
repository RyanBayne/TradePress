/**
 * TradePress Development Tabs JavaScript
 *
 * Handles functionality for the Development page tabs.
 *
 * @package TradePress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Store tab information in a variable to avoid multiple DOM lookups
    var activeTab = '';
    var defaultTab = 'feature-status';
    
    /**
     * Initialize Development Tabs functionality
     */
    function initdevelopment() {
        console.log('TradePress Development Tabs JS loaded');
        
        // Get active tab from URL or use default
        var urlParams = new URLSearchParams(window.location.search);
        activeTab = urlParams.get('tab') || defaultTab;
        
        // Initialize accordion functionality
        initAccordions();
        
        // Handle tab switching via click (we'll use the href instead of JavaScript)
        $('.nav-tab').on('click', function(e) {
            // Don't intercept clicks - let the browser follow the href
            // We've set up href with proper query parameters in the PHP
            
            // Just for tracking purposes
            var tabId = $(this).data('tab');
            console.log('Tab clicked: ' + tabId);
        });
        
        // Initialize tab-specific functionality
        initTabSpecificFunctions();
    }
    
    /**
     * Initialize accordions for development tabs
     */
    function initAccordions() {
        // Feature status accordions
        if ($('.tradepress-accordion-container').length > 0) {
            console.log('Initializing feature status accordions');
            
            // Remove any existing click handlers first to prevent duplication
            $('.tradepress-accordion-header').off('click');
            
            // Toggle individual accordion
            $('.tradepress-accordion-header').on('click', function() {
                var $content = $(this).next('.tradepress-accordion-content');
                $content.slideToggle();
                $(this).find('.tradepress-accordion-icon')
                       .toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            });
            
            // Expand all accordions
            $('.expand-all').off('click').on('click', function() {
                $('.tradepress-accordion-content').slideDown();
                $('.tradepress-accordion-icon')
                    .removeClass('dashicons-arrow-down-alt2')
                    .addClass('dashicons-arrow-up-alt2');
            });
            
            // Collapse all accordions
            $('.collapse-all').off('click').on('click', function() {
                $('.tradepress-accordion-content').slideUp();
                $('.tradepress-accordion-icon')
                    .removeClass('dashicons-arrow-up-alt2')
                    .addClass('dashicons-arrow-down-alt2');
            });
            
            // Calculate overall statistics
            updateFeatureStats();
        }
    }
    
    /**
     * Initialize functionality specific to certain tabs
     */
    function initTabSpecificFunctions() {
        if (activeTab === 'feature-status') {
            // Feature status tab specific functions
            updateFeatureStats();
        } else if (activeTab === 'ui-library') {
            // UI Library tab specific functions
            initUILibrary();
        } else if (activeTab === 'listener') {
            // Listener tab specific functions
            initListenerTesting();
        } else if (activeTab === 'roadmap') {
            // Roadmap tab specific functions
            // To be implemented
        } else if (activeTab === 'tasks') {
            // Tasks tab specific functions
            // Escape tab URL properly if needed
            try {
                // Tasks specific initialization if any
            } catch (e) {
                console.error('Error initializing Tasks tab:', e);
            }
        }
    }
    
    /**
     * Initialize UI Library functionality
     */
    function initUILibrary() {
        console.log('Initializing UI Library tab');
        
        // Initialize any UI Library specific functionality
        if (typeof TradePressUILibrary !== 'undefined') {
            // UI Library JavaScript is loaded
            console.log('TradePressUILibrary object detected');
        }
    }
    
    /**
     * Initialize Listener testing functionality
     */
    function initListenerTesting() {
        console.log('Initializing Listener testing tab');
        
        // Add any listener-specific initialization here
        $('.listener-test-form').on('submit', function() {
            console.log('Listener test form submitted');
        });
    }
    
    /**
     * Calculate and update feature statistics
     */
    function updateFeatureStats() {
        var totalFeatures = 0;
        var liveFeatures = 0;
        var demoFeatures = 0;
        var plannedFeatures = 0;
        
        // Find all status badges
        $('.feature-status-badge').each(function() {
            totalFeatures++;
            if ($(this).hasClass('status-live')) {
                liveFeatures++;
            } else if ($(this).hasClass('status-demo')) {
                demoFeatures++;
            } else if ($(this).hasClass('status-planned')) {
                plannedFeatures++;
            }
        });
        
        var completionRate = totalFeatures > 0 ? Math.round((liveFeatures / totalFeatures) * 100) : 0;
        
        // Update stats display
        $('#total-features').text(totalFeatures);
        $('#live-features').text(liveFeatures);
        $('#demo-features').text(demoFeatures);
        $('#completion-rate').text(completionRate + '%');
        
        console.log('Feature statistics updated:', {
            total: totalFeatures,
            live: liveFeatures,
            demo: demoFeatures,
            planned: plannedFeatures,
            completion: completionRate + '%'
        });
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        initdevelopment();
    });
    
})(jQuery);

jQuery(document).ready(function($) {
    // Initialize any dynamic behaviors for tabs here
    
    // Add a click handler to refresh GitHub data
    $('.refresh-github-data').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var originalText = $button.text();
        
        $button.text('Refreshing...').prop('disabled', true);
        
        // AJAX call to refresh GitHub data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_refresh_github_data',
                nonce: tradepress_dev_tabs.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert('Error refreshing data: ' + response.data);
                    $button.text(originalText).prop('disabled', false);
                }
            },
            error: function() {
                alert('Network error while refreshing data.');
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // GitHub Create Issue - Markdown preview functionality
    var timeoutId;
    $('#issue_body').on('input', function() {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(function() {
            var markdown = $('#issue_body').val();
            if (markdown) {
                $('#markdown-preview').html('<div class="loading">Loading preview...</div>');
                $.ajax({
                    url: 'https://api.github.com/markdown',
                    type: 'POST',
                    data: JSON.stringify({
                        text: markdown,
                        mode: 'markdown'
                    }),
                    contentType: 'application/json',
                    success: function(html) {
                        $('#markdown-preview').html(html);
                    },
                    error: function() {
                        $('#markdown-preview').html('<p class="error">Error generating preview.</p>');
                    }
                });
            } else {
                $('#markdown-preview').html('<p class="description">Enter content in the description field to see a preview.</p>');
            }
        }, 500);
    });
    
    // UI Library section visibility controls
    $('.section-toggle-checkbox').on('change', function() {
        var sectionId = $(this).data('section');
        var isChecked = $(this).is(':checked');
        var targetSection = $('#section-' + sectionId);
        
        if (isChecked) {
            targetSection.show();
        } else {
            targetSection.hide();
        }
        
        // Save state to localStorage
        var sectionStates = {};
        $('.section-toggle-checkbox').each(function() {
            sectionStates[$(this).data('section')] = $(this).is(':checked');
        });
        localStorage.setItem('tradepress_ui_library_sections', JSON.stringify(sectionStates));
    });
    
    // Show all sections
    $('#show-all-sections').on('click', function() {
        $('.section-toggle-checkbox').prop('checked', true);
        $('.ui-library-section').show();
        
        // Save state
        var sectionStates = {};
        $('.section-toggle-checkbox').each(function() {
            sectionStates[$(this).data('section')] = true;
        });
        localStorage.setItem('tradepress_ui_library_sections', JSON.stringify(sectionStates));
    });
    
    // Hide all sections
    $('#hide-all-sections').on('click', function() {
        $('.section-toggle-checkbox').prop('checked', false);
        $('.ui-library-section').hide();
        
        // Save state
        var sectionStates = {};
        $('.section-toggle-checkbox').each(function() {
            sectionStates[$(this).data('section')] = false;
        });
        localStorage.setItem('tradepress_ui_library_sections', JSON.stringify(sectionStates));
    });
    
    // Restore section visibility state from localStorage
    var savedStates = localStorage.getItem('tradepress_ui_library_sections');
    if (savedStates) {
        try {
            var sectionStates = JSON.parse(savedStates);
            $.each(sectionStates, function(sectionId, isVisible) {
                var checkbox = $('[data-section="' + sectionId + '"]');
                var targetSection = $('#section-' + sectionId);
                
                checkbox.prop('checked', isVisible);
                if (isVisible) {
                    targetSection.show();
                } else {
                    targetSection.hide();
                }
            });
        } catch (e) {
            console.log('Error parsing saved section states');
        }
    }
    
    // Assets Tracker functionality
    // Tab switching
    $('.assets-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('tab');
        
        $('.assets-tab').removeClass('active').attr('aria-selected', 'false');
        $('.assets-tab-content').removeClass('active');
        
        $(this).addClass('active').attr('aria-selected', 'true');
        $('#' + target).addClass('active');
    });
    
    // CSS Assets filters
    function applyCSSFilters() {
        var showAvailable = $('#filter-css-available').is(':checked');
        var showMissing = $('#filter-css-missing').is(':checked');
        var issuesOnly = $('#filter-css-issues-only').is(':checked');
        
        $('#css-assets .asset-row').each(function() {
            var $row = $(this);
            var status = $row.data('status');
            var show = true;
            
            if ((status === 'success' && !showAvailable) || (status === 'error' && !showMissing)) {
                show = false;
            }
            
            if (issuesOnly && status !== 'error') {
                show = false;
            }
            
            $row.toggle(show);
        });
    }
    
    // JS Assets filters
    function applyJSFilters() {
        var showAvailable = $('#filter-js-available').is(':checked');
        var showMissing = $('#filter-js-missing').is(':checked');
        var issuesOnly = $('#filter-js-issues-only').is(':checked');
        
        $('#js-assets .asset-row').each(function() {
            var $row = $(this);
            var status = $row.data('status');
            var show = true;
            
            if ((status === 'success' && !showAvailable) || (status === 'error' && !showMissing)) {
                show = false;
            }
            
            if (issuesOnly && status !== 'error') {
                show = false;
            }
            
            $row.toggle(show);
        });
    }
    
    // Bind filter events
    $('#filter-css-available, #filter-css-missing, #filter-css-issues-only').on('change', applyCSSFilters);
    $('#filter-js-available, #filter-js-missing, #filter-js-issues-only').on('change', applyJSFilters);
    
    // Toggle filter buttons
    $('#toggle-css-issues').on('click', function() {
        $('#filter-css-issues-only').prop('checked', !$('#filter-css-issues-only').is(':checked')).trigger('change');
    });
    
    $('#toggle-js-issues').on('click', function() {
        $('#filter-js-issues-only').prop('checked', !$('#filter-js-issues-only').is(':checked')).trigger('change');
    });
    
    // Refresh scan buttons
    $('#refresh-css-scan, #refresh-js-scan').on('click', function() {
        location.reload();
    });
    
    // Move to assets functionality
    $('.move-to-assets').on('click', function() {
        var $btn = $(this);
        var fileName = $btn.data('name');
        var filePath = $btn.data('file');
        
        if (confirm(tradepressData.confirmMoveFile || 'Are you sure you want to move this file to the assets folder?')) {
            $btn.prop('disabled', true).text(tradepressData.movingText || 'Moving...');
            
            setTimeout(function() {
                alert(tradepressData.moveFileMessage || 'File move functionality would be implemented here');
                $btn.prop('disabled', false).text(tradepressData.moveToAssetsText || 'Move to Assets');
            }, 1000);
        }
    });
    
    // Delete file functionality
    $('.delete-file').on('click', function() {
        var $btn = $(this);
        var fileName = $btn.data('name');
        var filePath = $btn.data('file');
        
        if (confirm(tradepressData.confirmDeleteFile || 'Are you sure you want to delete this file? This action cannot be undone.')) {
            $btn.prop('disabled', true).text(tradepressData.deletingText || 'Deleting...');
            
            setTimeout(function() {
                alert(tradepressData.deleteFileMessage || 'File delete functionality would be implemented here');
                $btn.prop('disabled', false).text(tradepressData.deleteText || 'Delete');
            }, 1000);
        }
    });
    
    // Feature Status functionality
    function updateFeatureStats() {
        var totalFeatures = 0;
        var liveFeatures = 0;
        var demoFeatures = 0;
        
        $('.feature-status-badge').each(function() {
            totalFeatures++;
            if ($(this).hasClass('status-live')) {
                liveFeatures++;
            } else if ($(this).hasClass('status-demo')) {
                demoFeatures++;
            }
        });
        
        var completionRate = totalFeatures > 0 ? Math.round((liveFeatures / totalFeatures) * 100) : 0;
        
        $('#total-features').text(totalFeatures);
        $('#live-features').text(liveFeatures);
        $('#demo-features').text(demoFeatures);
        $('#completion-rate').text(completionRate + '%');
    }
    
    // Run feature stats calculation if on feature status tab
    if ($('.feature-status-tab').length > 0) {
        updateFeatureStats();
        
        // Initialize tp-accordion functionality for feature status
        $('.tp-accordion-header').on('click', function() {
            var $content = $(this).next('.tp-accordion-content');
            $content.slideToggle();
            $(this).find('.tp-accordion-icon')
                   .toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        });
        
        // Expand all accordions
        $('.expand-all').on('click', function() {
            $('.tp-accordion-content').slideDown();
            $('.tp-accordion-icon')
                .removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');
        });
        
        // Collapse all accordions
        $('.collapse-all').on('click', function() {
            $('.tp-accordion-content').slideUp();
            $('.tp-accordion-icon')
                .removeClass('dashicons-arrow-up-alt2')
                .addClass('dashicons-arrow-down-alt2');
        });
    }
    
    // jQuery UI Demo functionality
    if ($('#demo-tabs').length > 0) {
        // Initialize main demo tabs
        $('#demo-tabs').tabs();
        
        // Initialize Accordion
        $('#demo-accordion').accordion();
        
        // Initialize nested Tabs widget
        $('#demo-tabs-widget').tabs();
        
        // Initialize Dialog
        $('#demo-dialog').dialog({
            autoOpen: false,
            width: 400,
            modal: true
        });
        $('#dialog-opener').click(function() {
            $('#demo-dialog').dialog('open');
        });
        
        // Initialize Datepicker
        $('#demo-datepicker').datepicker();
        
        // Initialize Sortable
        $('#demo-sortable').sortable({
            placeholder: 'ui-state-highlight'
        });
        $('#demo-sortable').disableSelection();
        
        // Initialize Draggable
        $('#demo-draggable').draggable();
        
        // Initialize Draggable and Droppable
        $('#demo-draggable2').draggable();
        $('#demo-droppable').droppable({
            drop: function(event, ui) {
                $(this).addClass('ui-state-highlight').find('p').html('Dropped!');
            }
        });
        
        // Initialize Resizable
        $('#demo-resizable').resizable();
        
        // Initialize Progressbar
        $('#demo-progressbar').progressbar({
            value: 37
        });
        
        $('#progressbar-start').click(function() {
            var progressbar = $('#demo-progressbar');
            var val = 0;
            
            function progress() {
                val += Math.floor(Math.random() * 3) + 1;
                progressbar.progressbar('value', val);
                
                if (val < 99) {
                    setTimeout(progress, 80);
                } else {
                    progressbar.progressbar('value', 100);
                    setTimeout(function() {
                        progressbar.progressbar('value', 0);
                    }, 2000);
                }
            }
            
            progress();
        });
        
        // Initialize Slider
        $('#demo-slider').slider({
            range: 'min',
            value: 50,
            min: 1,
            max: 100,
            slide: function(event, ui) {
                $('#slider-value').text(ui.value);
            }
        });
        
        // Initialize Spinner
        $('#demo-spinner').spinner();
        
        // Initialize Autocomplete
        var availableTags = [
            'ActionScript', 'AppleScript', 'Asp', 'BASIC', 'C', 'C++', 'Clojure',
            'COBOL', 'ColdFusion', 'Erlang', 'Fortran', 'Groovy', 'Haskell',
            'Java', 'JavaScript', 'Lisp', 'Perl', 'PHP', 'Python', 'Ruby', 'Scala', 'Scheme'
        ];
        $('#demo-autocomplete').autocomplete({
            source: availableTags
        });
    }
    
    // Log Viewer functionality
    $('#load-log-file').on('click', function() {
        var logFile = $('#log-file-select').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_load_log_file',
                log_file: logFile,
                security: tradepress_dev_tabs.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#log-content').html('<pre>' + response.data.content + '</pre>');
                } else {
                    $('#log-content').html(
                        '<div class="notice notice-error inline">' +
                        '<p>' + response.data.message + '</p>' +
                        '</div>'
                    );
                }
            },
            error: function() {
                $('#log-content').html(
                    '<div class="notice notice-error inline">' +
                    '<p>Failed to load log file. Please try again.</p>' +
                    '</div>'
                );
            }
        });
    });
    
    // Tasks functionality
    if (typeof tradepressTasksData !== 'undefined') {
        $('.start-task').on('click', function(e) {
            e.preventDefault();
            var taskId = $(this).data('task-id');
            var taskSource = $(this).data('task-source');
            
            localStorage.setItem('tradepress_current_task_id', taskId);
            localStorage.setItem('tradepress_current_task_source', taskSource);
            
            $(this).closest('tr').attr('data-status', 'in-progress');
            
            if (taskSource !== 'github') {
                var taskData = null;
                for (var i = 0; i < tradepressTasksData.tasks.length; i++) {
                    if (tradepressTasksData.tasks[i].id === taskId) {
                        taskData = tradepressTasksData.tasks[i];
                        break;
                    }
                }
                
                if (taskData) {
                    $(this).addClass('disabled').html('<span class="dashicons dashicons-update-alt spin"></span> Creating issue...');
                    
                    var description = '';
                    if (taskData.description) {
                        description += taskData.description + '\n\n';
                    }
                    if (taskData.section) {
                        description += '**Section:** ' + taskData.section + '\n';
                    }
                    if (taskData.subtasks && taskData.subtasks.length > 0) {
                        description += '\n**Subtasks:**\n';
                        taskData.subtasks.forEach(function(subtask) {
                            description += '- [ ] ' + subtask.title + '\n';
                        });
                    }
                    
                    var labels = ['phase:' + taskData.phase];
                    switch(parseInt(taskData.priority)) {
                        case 1: labels.push('priority:high'); break;
                        case 2: labels.push('priority:medium'); break;
                        case 3: labels.push('priority:low'); break;
                    }
                    labels.push('source:' + taskData.source);
                    
                    $.ajax({
                        url: tradepress_dev_tabs.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tradepress_create_github_issue',
                            nonce: tradepress_dev_tabs.nonce,
                            title: taskData.title,
                            body: description,
                            labels: labels,
                            original_task_id: taskId,
                            original_task_source: taskSource
                        },
                        success: function(response) {
                            if (response.success) {
                                localStorage.setItem('TRADEPRESS_GITHUB_issue_number', response.data.number);
                                localStorage.setItem('TRADEPRESS_GITHUB_issue_url', response.data.html_url);
                                window.location.href = 'admin.php?page=tradepress_development&tab=current_task';
                            } else {
                                alert('Error creating GitHub issue: ' + response.data.message);
                            }
                        },
                        error: function() {
                            alert('Error creating GitHub issue. Please try again.');
                        }
                    });
                } else {
                    window.location.href = 'admin.php?page=tradepress_development&tab=current_task';
                }
            } else {
                window.location.href = 'admin.php?page=tradepress_development&tab=current_task';
            }
        });
        
        // Add CSS for spinner
        $('<style>.spin { animation: spin 2s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');
    }
});
