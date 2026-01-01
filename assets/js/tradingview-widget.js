/**
 * TradingView Advanced Chart Widget Initialization
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        const container = document.getElementById('tradingview-chart-container');
        
        if (!container) return;
        
        const symbol = container.getAttribute('data-symbol');
        
        if (!symbol) return;
        
        // Initialize TradingView Advanced Chart Widget
        new TradingView.widget({
            "container_id": "tradingview-chart-container",
            "autosize": true,
            "symbol": "NASDAQ:" + symbol,
            "interval": "D",
            "timezone": "Etc/UTC",
            "theme": "light",
            "style": "1",
            "locale": "en",
            "toolbar_bg": "#f1f3f6",
            "withdateranges": true,
            "allow_symbol_change": false,
            "details": true,
            "calendar": false,
            "studies": [
                "STD;MACD"
            ],
            "support_host": "https://www.tradingview.com"
        });
    });
})(jQuery);
