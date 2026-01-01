/**
 * TradePress Economic Calendar JavaScript
 *
 * JavaScript functionality for the Economic Calendar tab in the Research page
 *
 * @package TradePress
 * @version 1.0.0
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    // Highlight current day in calendar
    var today = new Date().toISOString().slice(0, 10);
    $('.calendar-date').each(function() {
        var dateHeading = $(this).find('.date-heading').text();
        if (dateHeading.includes(today)) {
            $(this).addClass('today');
            $(this).find('.date-heading').css('background-color', '#f8f9fa');
        }
    });
    
    // Add interactive tooltip for market reaction information
    $('.asset.complex').append('<span class="tooltip-icon dashicons dashicons-info"></span>');
    
    // Add tooltip functionality
    $('.tooltip-icon').hover(function() {
        var tooltip = $('<div class="reaction-tooltip"></div>');
        tooltip.html(tradepressEconomicCalendar.tooltipText);
        $(this).append(tooltip);
    }, function() {
        $(this).find('.reaction-tooltip').remove();
    });
    
    // Browser notifications
    function requestNotificationPermission() {
        if (!("Notification" in window)) {
            console.log("This browser does not support desktop notification");
            return;
        }
        
        if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission().then(function(permission) {
                if (permission === "granted") {
                    new Notification("TradePress Notifications Enabled", {
                        icon: tradepressEconomicCalendar.iconUrl,
                        body: "You will now receive notifications for high-impact economic events."
                    });
                }
            });
        }
    }
    
    // Test notification button
    $('#test_notification').on('click', function() {
        requestNotificationPermission();
        
        if (Notification.permission === "granted") {
            var notification = new Notification("TradePress Test Notification", {
                icon: tradepressEconomicCalendar.iconUrl,
                body: "This is a test notification for economic events."
            });
            
            // Close the notification after 5 seconds
            setTimeout(function() {
                notification.close();
            }, 5000);
        }
    });
    
    // Check for upcoming events and schedule notifications
    function scheduleEventNotifications() {
        if (!$('#enable_notifications').is(':checked') || Notification.permission !== "granted") {
            return;
        }
        
        var notificationMinutes = parseInt($('#notification_time').val());
        var now = new Date();
        var events = tradepressEconomicCalendar.todaysEvents;
        
        events.forEach(function(event) {
            // Parse event time
            var eventTime = new Date(event.date + ' ' + event.time);
            
            // Calculate notification time
            var notificationTime = new Date(eventTime.getTime() - (notificationMinutes * 60 * 1000));
            
            // Check if notification time is in the future but within 1 hour
            var timeUntilNotification = notificationTime.getTime() - now.getTime();
            if (timeUntilNotification > 0 && timeUntilNotification < 3600 * 1000) {
                // Schedule notification
                setTimeout(function() {
                    var notification = new Notification("Upcoming Economic Event", {
                        icon: tradepressEconomicCalendar.iconUrl,
                        body: event.title + " (" + event.region.toUpperCase() + ") in " + notificationMinutes + " minutes"
                    });
                    
                    // Close notification after 30 seconds
                    setTimeout(function() {
                        notification.close();
                    }, 30000);
                }, timeUntilNotification);
            }
        });
    }
    
    // Request notification permission on page load
    requestNotificationPermission();
    
    // Schedule notifications for today's events
    scheduleEventNotifications();
    
    // Update scheduling when notification options change
    $('#enable_notifications, #notification_time').on('change', function() {
        scheduleEventNotifications();
    });
});