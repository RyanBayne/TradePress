jQuery(document).ready(function($) {
    $('.assets-tab').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');
        
        $('.assets-tab').removeClass('active');
        $('.assets-tab-content').removeClass('active');
        
        $(this).addClass('active');
        $('#' + tab).addClass('active');
    });
});
