/**
 * TradePress Current Task Tab JavaScript
 * 
 * Handles the interactive functionality for the Current Task tab
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        console.log('TradePress Current Task script initialized');
        
        // Initialize autoselect for the task dropdown
        initTaskAutoselect();
        
        // Initialize subtask checkbox interactions
        initSubtaskInteractions();
        
        // Handle messages
        handleStatusMessages();
        
        // Initialize the accordion functionality
        initTaskAccordion();
        
        // Check for GitHub issue creation success/error notifications
        checkNotifications();
        
        // Remove the problematic click handler for "Start Working on Task" button
        // This allows the form to be submitted naturally via POST
        
        // Add a confirmation dialog for completing tasks
        $('button[name="mark_completed"]').on('click', function(e) {
            if (!confirm('Are you sure you want to mark this task as completed?')) {
                e.preventDefault();
            }
        });
        
        // Demo mode visual enhancements
        if (typeof tradepressCurrentTask !== 'undefined' && tradepressCurrentTask.isDemo) {
            $('.task-status-form button').on('click', function() {
                // Add a visual indicator that something is happening in demo mode
                var $button = $(this);
                $button.prop('disabled', true);
                
                // Get button text and add a spinner
                var originalText = $button.html();
                $button.html('<span class="spinner is-active" style="float: none; margin-top: 0;"></span> ' + originalText);
                
                // In demo mode, we'll let the form submission proceed normally
                // The server-side handler will show demo-specific messages
            });
        }
        
        // Make admin notices dismissible
        initDismissibleNotices();
    });
    
    /**
     * Initialize auto-selection when dropdown changes
     */
    function initTaskAutoselect() {
        $('#current-task-selector').on('change', function() {
            if ($(this).val()) {
                $('#select-current-task-form').submit();
            }
        });
    }
    
    /**
     * Initialize subtask checkbox interactions
     */
    function initSubtaskInteractions() {
        $('.subtask-item input[type="checkbox"]').on('change', function() {
            const $item = $(this).closest('.subtask-item');
            
            if ($(this).is(':checked')) {
                $item.addClass('completed');
            } else {
                $item.removeClass('completed');
            }
        });
    }
    
    /**
     * Handle status messages based on URL parameters
     */
    function handleStatusMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const updated = urlParams.get('updated');
        
        if (updated) {
            let message = '';
            
            switch (updated) {
                case 'subtasks':
                    message = 'Subtasks updated successfully';
                    break;
                case 'status':
                    message = 'Task status updated successfully';
                    break;
                case 'notes':
                    message = 'Working notes saved successfully';
                    break;
                default:
                    message = 'Update completed successfully';
            }
            
            if (message) {
                // Create and show the notice
                const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
                $('.current-task-header').after($notice);
                
                // Auto-remove after 3 seconds
                setTimeout(function() {
                    $notice.slideUp(function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        }
    }
    
    /**
     * Initialize task details accordion functionality
     */
    function initTaskAccordion() {
        $('.task-section-header').on('click', function() {
            $(this).toggleClass('expanded');
            $(this).next('.task-section-content').slideToggle(200);
        });
    }
    
    /**
     * Check for notification messages from server-side operations
     */
    function checkNotifications() {
        // This would typically look for URL parameters or DOM elements added by PHP
        // to indicate success/error messages from the server
        
        // Example: Check for URL params that might have been added after GitHub issue creation
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('github_issue_created')) {
            alert(tradepressCurrentTask.strings.issue_created + ' #' + urlParams.get('github_issue_created'));
        }
        if (urlParams.has('github_issue_error')) {
            alert(tradepressCurrentTask.strings.error_creating_issue + ': ' + urlParams.get('github_issue_error'));
        }
    }
    
    /**
     * Update the GitHub status indicator in the UI
     * 
     * @param {boolean} hasIssue Whether the task has a GitHub issue
     * @param {string} issueUrl GitHub issue URL (if applicable)
     * @param {number} issueNumber GitHub issue number (if applicable)
     */
    function updateGitHubStatus(hasIssue, issueUrl, issueNumber) {
        var $statusIndicator = $('.task-github-status');
        
        if (hasIssue) {
            // Update icon class
            $statusIndicator.find('.dashicons')
                .removeClass('dashicons-no')
                .addClass('dashicons-yes');
            
            // Update text
            $statusIndicator.contents().filter(function() {
                return this.nodeType === 3; // Text nodes
            }).remove();
            
            $statusIndicator.prepend(document.createTextNode('GitHub Issue #' + issueNumber + ' '));
            
            // Add/update link if not present
            if ($statusIndicator.find('.github-link').length === 0) {
                $statusIndicator.append(
                    $('<a>', {
                        'href': issueUrl,
                        'target': '_blank',
                        'class': 'github-link'
                    }).append(
                        $('<span>', {'class': 'dashicons dashicons-external'})
                    )
                );
            } else {
                $statusIndicator.find('.github-link').attr('href', issueUrl);
            }
        }
    }
    
    /**
     * Initialize dismissible notices
     */
    function initDismissibleNotices() {
        $(document).on('click', '.notice-dismiss', function() {
            $(this).closest('.notice').slideUp(function() {
                $(this).remove();
            });
        });
        
        // Add dismiss buttons to notices that don't have them
        $('.notice.is-dismissible').each(function() {
            if (!$(this).find('.notice-dismiss').length) {
                $(this).append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
            }
        });
    }
})(jQuery);
