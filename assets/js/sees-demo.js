jQuery(document).ready(function ($) {
    const container = $('#sees-demo-container');
    let loadingMessage = container.find('.loading-message');

    if (loadingMessage.length === 0 && typeof tradepress_sees_demo_nonce !== 'undefined') {
        container.prepend('<p class="loading-message">' + tradepress_sees_demo_nonce.loading_text + '</p>');
        loadingMessage = container.find('.loading-message');
    }

    const refreshButton = $('#refresh-sees-data');
    const startAutoRefreshButton = $('#tradepress-start-auto-refresh-sees-data');
    const stopAutoRefreshButton = $('#tradepress-stop-auto-refresh-sees-data');
    const lastUpdated = $('#tradepress-sees-last-updated');

    let autoRefreshIntervalId = null;
    const AUTO_REFRESH_INTERVAL = 5000;
    const ANIMATION_DURATION_MS = 500;

    function setLastUpdatedStamp() {
        if (lastUpdated.length === 0) {
            return;
        }

        const now = new Date();
        lastUpdated.text('Last updated: ' + now.toLocaleTimeString());
    }

    function fetchAndDisplaySEESData() {
        if (loadingMessage.length > 0) {
            loadingMessage.show();
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tradepress_fetch_sees_demo_data',
                _ajax_nonce: tradepress_sees_demo_nonce.nonce,
            },
            success: function (response) {
                if (loadingMessage.length > 0) {
                    loadingMessage.hide();
                }

                if (response.success && response.data) {
                    response.data.sort((a, b) => parseFloat(b.score) - parseFloat(a.score));

                    if (response.data.length === 0) {
                        container.children('.sees-security-box').remove();
                        container.append('<p>' + tradepress_sees_demo_nonce.no_data_message + '</p>');
                        return;
                    }

                    container.children('p:not(.loading-message)').remove();
                    animateBoxReorder(response.data);
                    setLastUpdatedStamp();
                } else {
                    container.children('.sees-security-box').remove();
                    container.append('<p>' + (response.data || tradepress_sees_demo_nonce.error_message) + '</p>');
                }
            },
            error: function (xhr, status, error) {
                if (loadingMessage.length > 0) {
                    loadingMessage.hide();
                }

                container.children('.sees-security-box').remove();
                container.append('<p>' + tradepress_sees_demo_nonce.error_message + ' Error: ' + error + '</p>');
                console.error('SEES Demo AJAX Error:', status, error, xhr.responseText);
            },
        });
    }

    function animateBoxReorder(updatedData) {
        const currentItems = container.children('.sees-security-box');
        const oldPositions = new Map();
        const currentItemElementsMap = new Map();

        currentItems.each(function () {
            const el = $(this);
            const symbol = String(el.data('symbol'));
            oldPositions.set(symbol, el.position());
            currentItemElementsMap.set(symbol, el);
        });

        const newOrderedElements = [];
        const tempContainerForNewOrder = $('<div></div>');

        updatedData.forEach(function (stock) {
            let item = currentItemElementsMap.get(stock.symbol);

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

            if (item) {
                item.html(stockBoxHTMLContent);
                item.data('score', parseFloat(stock.score));
                currentItemElementsMap.delete(stock.symbol);
            } else {
                item = $(`<div class="sees-security-box" data-symbol="${stock.symbol}" data-score="${parseFloat(stock.score)}"></div>`);
                item.html(stockBoxHTMLContent);
            }

            tempContainerForNewOrder.append(item);
            newOrderedElements.push(item);
        });

        currentItemElementsMap.forEach(function (removedItem, symbol) {
            if (oldPositions.has(symbol)) {
                removedItem.animate({ opacity: 0 }, ANIMATION_DURATION_MS, function () {
                    $(this).remove();
                });
                oldPositions.delete(symbol);
            } else {
                removedItem.remove();
            }
        });

        container.empty().append(tempContainerForNewOrder.children());

        newOrderedElements.forEach(function (el) {
            const symbol = String(el.data('symbol'));
            const oldPos = oldPositions.get(symbol);
            const newPos = el.position();

            if (oldPos) {
                const deltaX = oldPos.left - newPos.left;
                const deltaY = oldPos.top - newPos.top;

                if (deltaX !== 0 || deltaY !== 0) {
                    el.css({
                        transform: `translate(${deltaX}px, ${deltaY}px)`,
                        transition: 'none',
                    });

                    el[0].offsetHeight;

                    el.css({
                        transform: 'translate(0, 0)',
                        transition: `transform ${ANIMATION_DURATION_MS / 1000}s ease-in-out`,
                    });
                } else {
                    el.css({ transform: '', transition: '' });
                }
            } else {
                el.css({ opacity: 0, transform: '', transition: '' })
                    .animate({ opacity: 1 }, ANIMATION_DURATION_MS);
            }
        });

        setTimeout(function () {
            newOrderedElements.forEach(function (el) {
                el.css({ transform: '', transition: '' });
            });
        }, ANIMATION_DURATION_MS + 50);
    }

    function startAutoRefresh() {
        if (autoRefreshIntervalId) {
            return;
        }

        fetchAndDisplaySEESData();
        autoRefreshIntervalId = setInterval(fetchAndDisplaySEESData, AUTO_REFRESH_INTERVAL);

        startAutoRefreshButton.addClass('tradepress-hidden');
        stopAutoRefreshButton.removeClass('tradepress-hidden');
        refreshButton.prop('disabled', true);
    }

    function stopAutoRefresh() {
        if (!autoRefreshIntervalId) {
            return;
        }

        clearInterval(autoRefreshIntervalId);
        autoRefreshIntervalId = null;

        stopAutoRefreshButton.addClass('tradepress-hidden');
        startAutoRefreshButton.removeClass('tradepress-hidden');
        refreshButton.prop('disabled', false);
    }

    fetchAndDisplaySEESData();

    refreshButton.on('click', function () {
        if (!autoRefreshIntervalId) {
            fetchAndDisplaySEESData();
        }
    });

    startAutoRefreshButton.on('click', startAutoRefresh);
    stopAutoRefreshButton.on('click', stopAutoRefresh);

    stopAutoRefreshButton.addClass('tradepress-hidden');
    startAutoRefreshButton.removeClass('tradepress-hidden');
});
