jQuery(document).ready(function($) {
    // Initialize the CRON accordion with jQuery UI
    $("#tradepress-cron-accordion").accordion({
        collapsible: true,
        heightStyle: "content",
        active: false // Start with all sections collapsed
    });
    
    // Pre-select recurrence dropdown if a schedule exists
    if (typeof tradepress_cron_schedule !== 'undefined') {
        $('#recurrence').val(tradepress_cron_schedule);
    }
    
    // Show job details when selecting a CRON job
    $('#cron_job').on('change', function() {
        var selectedJob = $(this).val();
        $('.job-details').hide();
        $('#' + selectedJob + '_details').show();
    });
    
    // Trigger the change event to show the correct job details on page load
    $('#cron_job').trigger('change');
});
