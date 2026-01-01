/**
 * TradePress Sortable Securities V2 - AJAX and Sortable Logic
 *
 * @package TradePress/Shortcodes
 * @version 1.0.0
 */

// Ensure jQuery is available and wrap everything properly
(function($) {
    'use strict';
    
    // Wait for document ready
    $(document).ready(function() {
        let pollingInterval;
        const POLLING_RATE_MS = 3000; // Fetch new scores every 3 seconds
        const ANIMATION_DURATION_MS = 500; // Duration for slide animations

        // Initialize jQuery UI Sortable
        $("#sortable-list").sortable({
            placeholder: "ui-sortable-placeholder"
        });

        function fetchDataAndUpdate() {
            // MAJOR CHANGE: Instead of using AJAX for the demo, we'll generate random data
            // This matches the Charlie prototype's approach
            
            console.log("Generating new random scores");
            
            // Generate random data similar to what we'd get from the server
            const updatedItemsData = [
                {id: 'item-1', name: 'Alpha Stock', current_score: Math.floor(Math.random() * 30) + 70},
                {id: 'item-2', name: 'Beta Security', current_score: Math.floor(Math.random() * 35) + 60},
                {id: 'item-3', name: 'Gamma Asset', current_score: Math.floor(Math.random() * 40) + 50},
                {id: 'item-4', name: 'Delta Holding', current_score: Math.floor(Math.random() * 45) + 40},
                {id: 'item-5', name: 'Epsilon Fund', current_score: Math.floor(Math.random() * 50) + 30}
            ];
            
            // Sort the data by score (descending)
            updatedItemsData.sort((a, b) => b.current_score - a.current_score);
            
            // Update status
            $('#status').text('Data updated: ' + new Date().toLocaleTimeString());
            
            // Now animate using the FLIP technique
            animateReordering(updatedItemsData);
        }
        
        function animateReordering(updatedData) {
            const $container = $("#sortable-list");

            // 1. FIRST: Record current positions and collect current items
            const $currentDOMItems = $container.children('.sortable-item');
            const oldPositions = new Map();
            const currentItemElementsMap = new Map();

            $currentDOMItems.each(function() {
                const $el = $(this);
                const id = $el.data('id').toString();
                oldPositions.set(id, $el.position());
                currentItemElementsMap.set(id, $el);
            });

            // 2. Update content, prepare a list of items in the NEW desired order
            const newOrderedElements = [];
            const tempContainerForNewOrder = $('<div></div>');

            updatedData.forEach(function(newItemData) {
                const id = newItemData.id.toString();
                let $item = currentItemElementsMap.get(id);

                if ($item) {
                    // Update item's displayed score and data attribute
                    $item.find('.item-name').text(newItemData.name);
                    $item.find('.item-score').text('Score: ' + newItemData.current_score);
                    $item.attr('data-score', newItemData.current_score);
                    currentItemElementsMap.delete(id);
                } else {
                    // Item is new, create it
                    $item = $(`
                        <div class="sortable-item" data-id="${newItemData.id}" data-score="${newItemData.current_score}">
                            <span class="item-name">${newItemData.name}</span>
                            <span class="item-score">Score: ${newItemData.current_score}</span>
                        </div>
                    `);
                }
                tempContainerForNewOrder.append($item);
                newOrderedElements.push($item);
            });

            // Handle items that were removed
            currentItemElementsMap.forEach(function($removedItem, id) {
                if (oldPositions.has(id)) {
                    $removedItem.animate({ opacity: 0 }, ANIMATION_DURATION_MS, function() {
                        $(this).remove();
                    });
                    oldPositions.delete(id);
                } else {
                    $removedItem.remove();
                }
            });

            // 3. LAST: Apply the new DOM order
            $container.empty().append(tempContainerForNewOrder.children());

            // 4. INVERT & PLAY: Calculate deltas and animate
            newOrderedElements.forEach(function($el) {
                const id = $el.data('id').toString();
                const oldPos = oldPositions.get(id);
                const newPos = $el.position();

                if (oldPos) {
                    const deltaX = oldPos.left - newPos.left;
                    const deltaY = oldPos.top - newPos.top;

                    if (deltaX !== 0 || deltaY !== 0) {
                        $el.css({ transform: `translate(${deltaX}px, ${deltaY}px)`, transition: 'none' });
                        $el[0].offsetHeight; // Force reflow
                        $el.css({ transform: 'translate(0, 0)', transition: `transform ${ANIMATION_DURATION_MS / 1000}s ease-in-out` });
                    } else {
                        $el.css({ transform: '', transition: '' });
                    }
                } else {
                    // New item: fade it in
                    $el.css({ opacity: 0, transform: '', transition: '' })
                       .animate({ opacity: 1 }, ANIMATION_DURATION_MS);
                }
            });

            // After animations, clear transitions and refresh sortable
            setTimeout(function() {
                newOrderedElements.forEach(function($el) { $el.css({ transform: '', transition: '' }); });
                $container.sortable("refresh");
                console.log("List reordered with animation.");
            }, ANIMATION_DURATION_MS + 50);
        }

        // Event handlers for buttons
        $('#start-polling').on('click', function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
            $('#status').text('Service started. Generating data...');
            fetchDataAndUpdate(); // Initial call
            pollingInterval = setInterval(fetchDataAndUpdate, POLLING_RATE_MS);
            $(this).prop('disabled', true);
            $('#stop-polling').prop('disabled', false);
        });

        $('#stop-polling').on('click', function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
            pollingInterval = null;
            $('#status').text('Service stopped.');
            $(this).prop('disabled', true);
            $('#start-polling').prop('disabled', false);
        }).prop('disabled', true); // Initially disable stop button

        // Initial sort based on data-score attributes
        function initialSort() {
            const $container = $("#sortable-list");
            const $itemsToSort = $container.children('.sortable-item');
            const oldPositions = new Map();
            $itemsToSort.each(function() {
                const $el = $(this);
                oldPositions.set($el.data('id').toString(), $el.position());
            });

            const sortedJQueryElements = $itemsToSort.get().sort(function(a, b) {
                return parseInt($(b).data('score')) - parseInt($(a).data('score'));
            }).map(el => $(el));

            $itemsToSort.detach();
            sortedJQueryElements.forEach(function($el) {
                $container.append($el);
            });
            
            $container.children('.sortable-item').each(function() {
                const $el = $(this);
                const id = $el.data('id').toString();
                const oldPos = oldPositions.get(id);
                const newPos = $el.position();

                if (oldPos) {
                    const deltaX = oldPos.left - newPos.left;
                    const deltaY = oldPos.top - newPos.top;

                    if (deltaX !== 0 || deltaY !== 0) {
                        $el.css({ transform: `translate(${deltaX}px, ${deltaY}px)`, transition: 'none' });
                        $el[0].offsetHeight;
                        $el.css({ transform: 'translate(0, 0)', transition: `transform ${ANIMATION_DURATION_MS / 1000}s ease-in-out` });
                    } else { 
                        $el.css({ transform: '', transition: '' }); 
                    }
                }
            });

            setTimeout(function() {
                $container.children('.sortable-item').each(function() { $(this).css({ transform: '', transition: '' }); });
                $container.sortable("refresh");
                console.log("Initial sort animated and applied.");
            }, ANIMATION_DURATION_MS + 50);
        }
        
        initialSort();
        console.log('TradePress Sortable Securities V2 initialized');
    });

})(jQuery);
