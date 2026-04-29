jQuery(document).ready(function($) {
    // Initialize the current CRON accordion structure (tp-accordion)
    var $cronAccordion = $('#cron .tradepress-cron-accordion');
    if ($cronAccordion.length) {
        var $headers = $cronAccordion.find('.tp-accordion-header');
        var $panels = $cronAccordion.find('.tp-accordion-panel');

        $panels.hide(); // Start collapsed

        $headers.each(function(index) {
            var $header = $(this);
            var panelId = 'tradepress-cron-panel-' + index;

            $header.attr({
                role: 'button',
                tabindex: '0',
                'aria-expanded': 'false',
                'aria-controls': panelId
            });

            $header.next('.tp-accordion-panel').attr({
                id: panelId,
                'aria-hidden': 'true'
            });
        });

        function togglePanel($header) {
            var $panel = $header.next('.tp-accordion-panel');
            var isOpen = $header.hasClass('is-open');

            $headers.removeClass('is-open').attr('aria-expanded', 'false');
            $panels.stop(true, true).slideUp(160).attr('aria-hidden', 'true');

            if (!isOpen) {
                $header.addClass('is-open').attr('aria-expanded', 'true');
                $panel.stop(true, true).slideDown(180).attr('aria-hidden', 'false');
            }
        }

        $headers.on('click.tradepressCronAccordion', function() {
            togglePanel($(this));
        });

        $headers.on('keydown.tradepressCronAccordion', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                togglePanel($(this));
            }
        });
    }

    // Legacy accordion support for the older cron tab markup.
    if ($('#tradepress-cron-accordion').length && typeof $.fn.accordion === 'function') {
        $('#tradepress-cron-accordion').accordion({
            collapsible: true,
            heightStyle: 'content',
            active: false
        });
    }
    
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
