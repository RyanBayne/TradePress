jQuery(document).ready(function($) {
    const container = $('#sees-demo-container');
    // Ensure loadingMessage is correctly selected or created if not present in initial HTML
    let loadingMessage = container.find('.loading-message'); 
    if (loadingMessage.length === 0 && typeof tradepress_sees_demo_nonce !== 'undefined') {
         // Prepend it if it's missing and we have the text for it.
        container.prepend('<p class="loading-message">' + tradepress_sees_demo_nonce.loading_text + '</p>');
        loadingMessage = container.find('.loading-message'); // Re-select it
    }

    const refreshButton = $('#refresh-sees-data');
    const startAutoRefreshButton = $('#tradepress-start-auto-refresh-sees-data');
    const stopAutoRefreshButton = $('#tradepress-stop-auto-refresh-sees-data');

    let autoRefreshIntervalId = null;
    const AUTO_REFRESH_INTERVAL = 5000; // Update interval to 5 seconds
    const ANIMATION_DURATION_MS = 500; // Animation duration for smooth transitions

    function fetchAndDisplaySEESData() {
        if (loadingMessage.length > 0) {
            loadingMessage.show();
        }
        // Do not clear all boxes here, we will update them selectively.

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'tradepress_fetch_sees_demo_data',
                _ajax_nonce: tradepress_sees_demo_nonce.nonce 
            },
            success: function(response) {
                if (loadingMessage.length > 0) {
                    loadingMessage.hide();
                }
                if (response.success && response.data) {
                    // Sort data by score descending (this is the desired order)
                    response.data.sort((a, b) => parseFloat(b.score) - parseFloat(a.score));

                    if (response.data.length === 0) {
                        container.children('.sees-security-box').remove(); // Remove existing boxes
                        container.append('<p>' + tradepress_sees_demo_nonce.no_data_message + '</p>');
                        return;
                    }
                    // Remove any "no data" message if present
                    container.children('p:not(.loading-message)').remove();


                    // FLIP Animation Implementation
                    animateBoxReorder(response.data);

                } else {
                    // Handle case where response.success is false or response.data is missing
                    container.children('.sees-security-box').remove();
                    container.append('<p>' + (response.data || tradepress_sees_demo_nonce.error_message) + '</p>');
                }
            },
            error: function(xhr, status, error) {
                if (loadingMessage.length > 0) {
                    loadingMessage.hide();
                }
                container.children('.sees-security-box').remove();
                container.append('<p>' + tradepress_sees_demo_nonce.error_message + ' Error: ' + error + '</p>');
                console.error("SEES Demo AJAX Error:", status, error, xhr.responseText);
            }
        });
    }

    function animateBoxReorder(updatedData) {
        // 1. FIRST: Record current positions and collect current items
        const $currentDOMItems = container.children('.sees-security-box');
        const oldPositions = new Map();
        const currentItemElementsMap = new Map();

        $currentDOMItems.each(function() {
            const $el = $(this);
            const symbol = $el.data('symbol').toString();
            oldPositions.set(symbol, $el.position());
            currentItemElementsMap.set(symbol, $el);
        });

        // 2. Update content and prepare new ordered elements
        const newOrderedElements = [];
        const tempContainerForNewOrder = $('<div></div>');
        const currentSymbols = new Set();

        updatedData.forEach(function(stock) {
            currentSymbols.add(stock.symbol);
            let $item = currentItemElementsMap.get(stock.symbol);

            let priceChangeClass = '';
            let priceChangeSign = '';
            const changePercentNum = parseFloat(stock.change_percent);

            if (changePercentNum > 0) {
                priceChangeClass = 'price-up';
                priceChangeSign = '+';
            } else if (changePercentNum < 0) {
                priceChangeClass = 'price-down';
            }

            const stockBoxHTMLContent = `
                <div class="sees-box-header">
                    <span class="sees-symbol">${stock.symbol}</span>
                    <span class="sees-name">${stock.name || 'N/A'}</span>
                    <span class="sees-score">Score: ${stock.score}</span>
                </div>
                <div class="sees-box-body">
                    <p class="sees-price">Price: $${parseFloat(stock.price).toFixed(2)}</p>
                    <p class="sees-change ${priceChangeClass}">
                        Change: ${priceChangeSign}${changePercentNum.toFixed(2)}%
                    </p>
                </div>
                <div class="sees-box-footer">
                    <small>Industry: ${stock.industry || 'N/A'}</small>
                </div>
            `;

            if ($item) {
                // Update existing box
                $item.html(stockBoxHTMLContent);
                $item.data('score', parseFloat(stock.score));
                currentItemElementsMap.delete(stock.symbol);
            } else {
                // Create new box
                $item = $(`<div class="sees-security-box" data-symbol="${stock.symbol}" data-score="${parseFloat(stock.score)}"></div>`);
                $item.html(stockBoxHTMLContent);
            }
            
            tempContainerForNewOrder.append($item);
            newOrderedElements.push($item);
        });

        // Handle removed items
        currentItemElementsMap.forEach(function($removedItem, symbol) {
            if (oldPositions.has(symbol)) {
                $removedItem.animate({ opacity: 0 }, ANIMATION_DURATION_MS, function() {
                    $(this).remove();
                });
                oldPositions.delete(symbol);
            } else {
                $removedItem.remove();
            }
        });

        // 3. LAST: Apply new DOM order
        container.empty().append(tempContainerForNewOrder.children());

        // 4. INVERT & PLAY: Calculate deltas and animate
        newOrderedElements.forEach(function($el) {
            const symbol = $el.data('symbol').toString();
            const oldPos = oldPositions.get(symbol);
            const newPos = $el.position();

            if (oldPos) {
                // Item existed before, animate position change
                const deltaX = oldPos.left - newPos.left;
                const deltaY = oldPos.top - newPos.top;

                if (deltaX !== 0 || deltaY !== 0) {
                    $el.css({ 
                        transform: `translate(${deltaX}px, ${deltaY}px)`, 
                        transition: 'none' 
                    });
                    $el[0].offsetHeight; // Force reflow
                    $el.css({ 
                        transform: 'translate(0, 0)', 
                        transition: `transform ${ANIMATION_DURATION_MS / 1000}s ease-in-out` 
                    });
                } else {
                    $el.css({ transform: '', transition: '' });
                }
            } else {
                // New item: fade it in
                $el.css({ opacity: 0, transform: '', transition: '' })
                   .animate({ opacity: 1 }, ANIMATION_DURATION_MS);
            }
        });

        // Clean up after animations
        setTimeout(function() {
            newOrderedElements.forEach(function($el) { 
                $el.css({ transform: '', transition: '' }); 
            });
            
            // Reinitialize sortable if it exists
            if (container.hasClass('ui-sortable')) {
                container.sortable("refresh");
            }
            console.log("SEES boxes reordered with smooth animation.");
        }, ANIMATION_DURATION_MS + 50);
    }

    function startAutoRefresh() {
        if (autoRefreshIntervalId) return; // Already running

        fetchAndDisplaySEESData(); // Fetch immediately
        autoRefreshIntervalId = setInterval(fetchAndDisplaySEESData, AUTO_REFRESH_INTERVAL);

        startAutoRefreshButton.addClass('tradepress-hidden');
        stopAutoRefreshButton.removeClass('tradepress-hidden');
        refreshButton.prop('disabled', true); // Disable manual refresh while auto is on
        // Optionally, add a status message for auto-refresh started
    }

    function stopAutoRefresh() {
        if (!autoRefreshIntervalId) return; // Not running

        clearInterval(autoRefreshIntervalId);
        autoRefreshIntervalId = null;

        stopAutoRefreshButton.addClass('tradepress-hidden');
        startAutoRefreshButton.removeClass('tradepress-hidden');
        refreshButton.prop('disabled', false); // Re-enable manual refresh
        // Optionally, add a status message for auto-refresh stopped
    }

    // Initial load
    fetchAndDisplaySEESData();

    // Event Listeners
    refreshButton.on('click', function() {
        if (!autoRefreshIntervalId) { // Only allow manual refresh if auto is off
            fetchAndDisplaySEESData();
        }
    });
    startAutoRefreshButton.on('click', startAutoRefresh);
    stopAutoRefreshButton.on('click', stopAutoRefresh);

    // Ensure stop button is hidden initially if not already handled by CSS
    stopAutoRefreshButton.addClass('tradepress-hidden');
    startAutoRefreshButton.removeClass('tradepress-hidden'); 
});
