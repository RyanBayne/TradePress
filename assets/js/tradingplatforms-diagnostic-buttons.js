/**
 * TradePress - Diagnostic Buttons Partial Scripts
 *
 * @package TradePress/Admin/JS
 */

jQuery(document).ready(function($) {
    console.log('Diagnostic script loaded'); 
    
    // Test button click handlers
    $('.diagnostic-button').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        
        console.log('Diagnostic button clicked: ' + target);
        
        // Hide all sections
        $('.diagnostic-result-section').hide();
        
        // Show the target section
        $('#' + target).show();
        
        // Update active state
        $('.diagnostic-button').removeClass('active');
        $(this).addClass('active');
    });
    
    // Event listener test
    $('#event-test-button').on('click', function(e) {
        console.log('Event test button clicked');
        $('#event-test-result').html('<span style="color:green;">✓ Success! Event listener is working correctly.</span>');
    });
    
    // Log that script has fully initialized
    console.log('Diagnostic script fully initialized');
});