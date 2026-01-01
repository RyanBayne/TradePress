/**
 * TradePress Direct API Test JavaScript
 * 
 * @package TradePress
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Endpoint button handlers
    var endpointButtons = document.querySelectorAll('.endpoint-button');
    endpointButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var endpoint = this.getAttribute('data-endpoint');
            var mode = getActiveMode();
            window.location.href = '?endpoint=' + endpoint + '&mode=' + mode;
        });
    });
    
    // Mode button handlers
    var modeButtons = document.querySelectorAll('.mode-button');
    modeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var mode = this.getAttribute('data-mode');
            var endpoint = getActiveEndpoint();
            window.location.href = '?endpoint=' + endpoint + '&mode=' + mode;
        });
    });
    
    function getActiveEndpoint() {
        var activeButton = document.querySelector('.endpoint-button.active');
        return activeButton ? activeButton.getAttribute('data-endpoint') : 'account';
    }
    
    function getActiveMode() {
        var activeButton = document.querySelector('.mode-button.active');
        return activeButton ? activeButton.getAttribute('data-mode') : 'paper';
    }
});