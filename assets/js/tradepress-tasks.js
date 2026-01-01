/**
 * TradePress Tasks Management
 * 
 * Handles the Tasks tab functionality in the Development page.
 */
(function($) {
    'use strict';
    
    // Task filtering and UI handling
    $(document).ready(function() {
        console.log('TradePress Tasks initialized');
        
        // Initialize filter functionality
        initFilters();
        
        // Initialize task detail modal
        initTaskDetailModal();
        
        // Initialize sorting
        initSorting();
    });
    
    /**
     * Initialize filter dropdowns
     */
    function initFilters() {
        // Handle filter changes
        $('.task-filter, .task-search').on('change keyup', function() {
            applyFilters();
        });
        
        // Initial filter application
        applyFilters();
    }
    
    /**
     * Apply filters to the task list
     */
    function applyFilters() {
        var sourceFilter = $('#task-source').val();
        var statusFilter = $('#task-status').val();
        var phaseFilter = $('#task-phase').val();
        var searchText = $('#task-search').val().toLowerCase();
        
        var visibleCount = 0;
        
        // Process each row
        $('.task-row').each(function() {
            var $row = $(this);
            var source = $row.data('task-source');
            var status = $row.data('status');
            var phase = $row.data('phase');
            var title = $row.find('.task-title').text().toLowerCase();
            
            // Apply source filter
            var sourceMatch = sourceFilter === 'all' || source === sourceFilter;
            
            // Apply status filter
            var statusMatch = statusFilter === 'all' || status === statusFilter;
            
            // Apply phase filter
            var phaseMatch = phaseFilter === 'all' || phase == phaseFilter;
            
            // Apply search filter
            var searchMatch = searchText === '' || title.indexOf(searchText) !== -1;
            
            // Show/hide based on combined filters
            if (sourceMatch && statusMatch && phaseMatch && searchMatch) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
        
        // Show "no tasks" message if needed
        if (visibleCount === 0) {
            $('.tradepress-no-tasks').removeClass('hidden');
        } else {
            $('.tradepress-no-tasks').addClass('hidden');
        }
    }
    
    /**
     * Initialize the task detail modal
     */
    function initTaskDetailModal() {
        // Open modal when clicking "View Details" or task title
        $('.view-task-details').on('click', function(e) {
            e.preventDefault();
            
            var taskId = $(this).data('task-id');
            openTaskDetailModal(taskId);
        });
        
        // Close modal when clicking X or outside
        $('.tradepress-modal-close, .tradepress-modal').on('click', function(e) {
            if (e.target === this) {
                closeTaskDetailModal();
            }
        });
        
        // Handle ESC key to close modal
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                closeTaskDetailModal();
            }
        });
    }
    
    /**
     * Open the task detail modal for a specific task
     */
    function openTaskDetailModal(taskId) {
        var task = findTaskById(taskId);
        
        if (!task) {
            console.error('Task not found:', taskId);
            return;
        }
        
        var $content = $('#task-detail-content');
        
        // Build task detail content
        var html = '<div class="task-detail-header">' +
                      '<h2>' + task.title + '</h2>' +
                      '<div class="task-meta">' +
                          '<span class="task-source">' + task.source_label + '</span> | ' +
                          '<span class="task-phase">Phase ' + task.phase + '</span> | ' +
                          '<span class="task-priority">Priority: ' + getPriorityLabel(task.priority) + '</span>' +
                      '</div>' +
                   '</div>';
        
        // Add description if available
        if (task.description) {
            html += '<div class="task-description">' +
                      '<h3>' + tradepressTasksStrings.strings.description + '</h3>' +
                      '<div class="description-content">' + formatTaskDescription(task.description) + '</div>' +
                   '</div>';
        }
        
        // Add notes if available
        if (task.notes) {
            html += '<div class="task-notes">' +
                      '<h3>Notes</h3>' +
                      '<div class="notes-content">' + formatTaskDescription(task.notes) + '</div>' +
                   '</div>';
        }
        
        // Add AI guidance if available
        if (task.ai_guidance) {
            html += '<div class="task-ai-guidance">' +
                      '<h3>AI Guidance</h3>' +
                      '<div class="ai-guidance-content">' + formatTaskDescription(task.ai_guidance) + '</div>' +
                   '</div>';
        }
        
        // Add subtasks if available
        if (task.subtasks && task.subtasks.length > 0) {
            html += '<div class="task-subtasks">' +
                      '<h3>' + tradepressTasksStrings.strings.subtasks + '</h3>' +
                      '<ul class="subtasks-list">';
            
            task.subtasks.forEach(function(subtask) {
                var statusClass = subtask.status === 'completed' ? 'completed' : 'pending';
                var statusIcon = subtask.status === 'completed' ? '✓' : '◯';
                
                html += '<li class="subtask-item ' + statusClass + '">' +
                          '<span class="subtask-status-icon">' + statusIcon + '</span>' +
                          '<span class="subtask-title">' + subtask.title + '</span>' +
                        '</li>';
            });
            
            html += '</ul></div>';
        }
        
        // Add link if available
        if (task.link) {
            html += '<div class="task-link">' +
                      '<a href="' + task.link + '" target="_blank" class="button">' +
                        '<span class="dashicons dashicons-external"></span> ' +
                        tradepressTasksStrings.strings.view_link +
                      '</a>' +
                   '</div>';
        }
        
        // Set the content and open the modal
        $content.html(html);
        $('#task-detail-modal').addClass('open');
    }
    
    /**
     * Close the task detail modal
     */
    function closeTaskDetailModal() {
        $('#task-detail-modal').removeClass('open');
        $('#task-detail-content').html('');
    }
    
    /**
     * Format text description with simple markdown-like syntax
     */
    function formatTaskDescription(text) {
        if (!text) return '';
        
        // Convert line breaks to paragraph breaks
        var html = '<p>' + text.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
        
        // Convert **bold** and *italic*
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert backticks to code
        html = html.replace(/`(.*?)`/g, '<code>$1</code>');
        
        // Convert URLs to links
        html = html.replace(/https?:\/\/[^\s)]+/g, '<a href="$&" target="_blank">$&</a>');
        
        return html;
    }
    
    /**
     * Get the label for a priority level
     */
    function getPriorityLabel(priority) {
        switch (parseInt(priority)) {
            case 1: return 'High';
            case 2: return 'Medium';
            case 3: return 'Low';
            default: return 'Medium';
        }
    }
    
    /**
     * Find a task by its ID in the tasks data
     */
    function findTaskById(taskId) {
        if (tradepressTasksData && tradepressTasksData.tasks) {
            for (var i = 0; i < tradepressTasksData.tasks.length; i++) {
                if (tradepressTasksData.tasks[i].id === taskId) {
                    return tradepressTasksData.tasks[i];
                }
            }
        }
        return null;
    }
    
    /**
     * Initialize sorting functionality for task table
     */
    function initSorting() {
        $('.sortable').on('click', function() {
            var column = $(this).data('sort');
            var $table = $('#tradepress-tasks-table');
            var rows = $table.find('tbody tr').get();
            
            rows.sort(function(a, b) {
                var aValue = $(a).data('task-' + column);
                var bValue = $(b).data('task-' + column);
                
                // Convert to number for numeric comparison if needed
                if (!isNaN(aValue) && !isNaN(bValue)) {
                    aValue = parseInt(aValue);
                    bValue = parseInt(bValue);
                }
                
                if (aValue < bValue) return -1;
                if (aValue > bValue) return 1;
                return 0;
            });
            
            // Toggle sort direction
            if (!$(this).hasClass('asc')) {
                rows.reverse();
                $('.sortable').removeClass('asc desc');
                $(this).addClass('asc');
            } else {
                $('.sortable').removeClass('asc desc');
                $(this).addClass('desc');
            }
            
            // Reattach sorted rows to table
            $.each(rows, function(index, row) {
                $table.children('tbody').append(row);
            });
        });
    }
    
})(jQuery);
