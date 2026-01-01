/**
 * Technical Analysis Charts
 * 
 * Uses lightweight-charts library to render technical analysis charts
 */
jQuery(document).ready(function($) {
    'use strict';
    
    // Price Chart
    const priceChartContainer = document.getElementById('price-chart');
    if (priceChartContainer) {
        const chartData = JSON.parse(priceChartContainer.getAttribute('data-ohlc'));
        
        // Initialize chart
        const chart = LightweightCharts.createChart(priceChartContainer, {
            width: priceChartContainer.clientWidth,
            height: priceChartContainer.clientHeight,
            layout: {
                backgroundColor: '#ffffff',
                textColor: '#333',
            },
            grid: {
                vertLines: {
                    color: 'rgba(197, 203, 206, 0.5)',
                },
                horzLines: {
                    color: 'rgba(197, 203, 206, 0.5)',
                },
            },
            timeScale: {
                timeVisible: true,
                borderColor: '#D1D4DC',
            },
            rightPriceScale: {
                borderColor: '#D1D4DC',
            },
            crosshair: {
                mode: LightweightCharts.CrosshairMode.Normal,
            },
        });
        
        // Create candlestick series
        const candlestickSeries = chart.addCandlestickSeries({
            upColor: '#26a69a',
            downColor: '#ef5350',
            borderVisible: false,
            wickUpColor: '#26a69a',
            wickDownColor: '#ef5350',
        });
        
        // Add volume series below price
        const volumeSeries = chart.addHistogramSeries({
            color: '#26a69a',
            priceFormat: {
                type: 'volume',
            },
            priceScaleId: '',
            scaleMargins: {
                top: 0.8,
                bottom: 0,
            },
        });
        
        // Convert data for the chart
        const ohlcData = chartData.map(item => ({
            time: item.date,
            open: item.open,
            high: item.high,
            low: item.low,
            close: item.close
        }));
        
        const volumeData = chartData.map(item => ({
            time: item.date,
            value: item.volume,
            color: item.close >= item.open ? '#26a69a' : '#ef5350'
        }));
        
        // Set data
        candlestickSeries.setData(ohlcData);
        volumeSeries.setData(volumeData);
        
        // Fit content
        chart.timeScale().fitContent();
        
        // Handle window resize
        window.addEventListener('resize', () => {
            chart.applyOptions({
                width: priceChartContainer.clientWidth,
                height: priceChartContainer.clientHeight
            });
        });
    }
    
    // RSI Chart
    const rsiChartContainer = document.getElementById('rsi-chart');
    if (rsiChartContainer) {
        const rsiData = JSON.parse(rsiChartContainer.getAttribute('data-values'));
        
        // Initialize chart
        const rsiChart = LightweightCharts.createChart(rsiChartContainer, {
            width: rsiChartContainer.clientWidth,
            height: rsiChartContainer.clientHeight,
            layout: {
                backgroundColor: '#ffffff',
                textColor: '#333',
            },
            grid: {
                vertLines: {
                    color: 'rgba(197, 203, 206, 0.5)',
                },
                horzLines: {
                    color: 'rgba(197, 203, 206, 0.5)',
                },
            },
            rightPriceScale: {
                borderColor: '#D1D4DC',
            },
            timeScale: {
                visible: false,
            },
        });
        
        // Create RSI line series
        const rsiSeries = rsiChart.addLineSeries({
            color: '#2962FF',
            lineWidth: 2,
        });
        
        // Create overbought line
        const overboughtSeries = rsiChart.addLineSeries({
            color: 'rgba(255, 0, 0, 0.5)',
            lineWidth: 1,
            lineStyle: LightweightCharts.LineStyle.Dashed,
        });
        
        // Create oversold line
        const oversoldSeries = rsiChart.addLineSeries({
            color: 'rgba(0, 128, 0, 0.5)',
            lineWidth: 1,
            lineStyle: LightweightCharts.LineStyle.Dashed,
        });
        
        // Convert data for the chart
        const formattedRsiData = [];
        
        // Generate dates for RSI values
        const today = new Date();
        for (let i = 0; i < rsiData.length; i++) {
            const date = new Date(today);
            date.setDate(date.getDate() - (rsiData.length - i - 1));
            
            formattedRsiData.push({
                time: date.toISOString().split('T')[0],
                value: rsiData[i]
            });
        }
        
        // Set data
        rsiSeries.setData(formattedRsiData);
        
        // Add overbought (70) and oversold (30) lines
        const overboughtData = [];
        const oversoldData = [];
        
        formattedRsiData.forEach(item => {
            overboughtData.push({
                time: item.time,
                value: 70
            });
            
            oversoldData.push({
                time: item.time,
                value: 30
            });
        });
        
        overboughtSeries.setData(overboughtData);
        oversoldSeries.setData(oversoldData);
        
        // Fit content
        rsiChart.timeScale().fitContent();
        
        // Handle window resize
        window.addEventListener('resize', () => {
            rsiChart.applyOptions({
                width: rsiChartContainer.clientWidth
            });
        });
    }
    
    // MACD Chart
    const macdChartContainer = document.getElementById('macd-chart');
    if (macdChartContainer) {
        // MACD chart implementation would go here
        // Similar to RSI chart but with MACD line, signal line, and histogram
    }
    
    // Support & Resistance Toggle Details
    $('.toggle-details').on('click', function() {
        var $details = $(this).next('.detailed-levels');
        
        if ($details.is(':visible')) {
            $details.slideUp();
            $(this).text(tradepressAnalysis.hideDetailsText);
        } else {
            $details.slideDown();
            $(this).text(tradepressAnalysis.showDetailsText);
        }
    });
    
    // Volatility Analysis Accordion
    $('.tradepress-accordion-header').on('click', function() {
        $(this).next('.tradepress-accordion-content').slideToggle();
        $(this).find('.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    });
    
    // Initially hide all accordion content except the first one
    $('.tradepress-accordion-content').not(':first').hide();
    
    // Metric card hover effects
    $('.tradepress-metric-card').hover(
        function() {
            $(this).addClass('hovered');
        },
        function() {
            $(this).removeClass('hovered');
        }
    );
    
    // Highlight trading opportunity panel
    if ($('.opportunity-metric.highlighted').length > 0) {
        $('.opportunity-panel').addClass('has-opportunity');
        
        setTimeout(function() {
            $('.opportunity-panel').addClass('pulse-attention');
        }, 500);
    }
});
