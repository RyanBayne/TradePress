jQuery(document).ready(function($) {
    if (typeof wp !== 'undefined' && wp.pointer) {
        // Check if previous pointer was dismissed, then show this one
        function showTestingPointer() {
            $('#tradepress-testing-pointer-target').pointer({
                content: '<h3>' + tradepress_testing_pointer.title + '</h3><p>' + tradepress_testing_pointer.content + '</p>',
                position: 'top',
                close: function() {
                    $.post(ajaxurl, {
                        action: 'dismiss-wp-pointer',
                        pointer: 'tradepress_testing_pointer'
                    });
                }
            }).pointer('open');
        }
        
        // Listen for directive configuration pointer dismissal
        $(document).on('wp-pointer-close', function(e, pointerId) {
            if (pointerId === 'tradepress_directive_configuration') {
                setTimeout(showTestingPointer, 500);
            }
        });
        
        // Check if directive configuration pointer was already dismissed
        $.post(ajaxurl, {
            action: 'tradepress_check_pointer_status',
            pointer: 'tradepress_directive_configuration',
            nonce: tradepress_testing_pointer.nonce
        }, function(response) {
            if (response.dismissed) {
                showTestingPointer();
            }
        });
    }
});