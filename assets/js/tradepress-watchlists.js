/**
 * TradePress Watchlists JavaScript
 *
 * @package TradePress/Admin/JS
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    // Toggle select all checkboxes
    $('#select-all-symbols').on('change', function() {
        $('input[name="selected_symbols[]"]').prop('checked', $(this).prop('checked'));
    });
    
    // Score symbol button
    $('.score-symbol').on('click', function(e) {
        e.preventDefault();
        var symbol = $(this).data('symbol');
        var button = $(this);
        
        button.addClass('button-busy');
        button.find('.dashicons').addClass('dashicons-update-spin');
        
        setTimeout(function() {
            button.removeClass('button-busy');
            button.find('.dashicons').removeClass('dashicons-update-spin');
            
            var randomScore = Math.floor(Math.random() * (95 - 60 + 1)) + 60;
            
            alert('Symbol ' + symbol + ' has been scored. New score: ' + randomScore);
            
            location.reload();
        }, 1500);
    });
    
    // Create watchlist button click
    $('#create-watchlist-btn').on('click', function() {
        var selectedSymbols = [];
        $('input[name="selected_symbols[]"]:checked').each(function() {
            selectedSymbols.push($(this).val());
        });
        if (selectedSymbols.length === 0) {
            alert('Please select at least one symbol.');
            return;
        }
        var symbolsHtml = '';
        selectedSymbols.forEach(function(symbol) {
            symbolsHtml += '<span class="selected-symbol">' + symbol + '</span> ';
        });
        $('#selected-symbols-list').html(symbolsHtml);
        $('#create-watchlist-modal').show();
    });

    // Close modal
    $('.tradepress-modal-close, .cancel-watchlist').on('click', function() {
        $('#create-watchlist-modal').hide();
    });

    // Submit watchlist form
    $('#create-watchlist-form').on('submit', function(e) {
        e.preventDefault();
        var watchlistName = $('#watchlist-name').val();
        if (!watchlistName) {
            alert('Please enter a watchlist name.');
            return;
        }
        alert('Watchlist "' + watchlistName + '" created successfully!');
        $('#create-watchlist-modal').hide();
        $('#watchlist-name').val('');
        $('#watchlist-description').val('');
        $('input[name="selected_symbols[]"]').prop('checked', false);
        $('#select-all-symbols').prop('checked', false);
    });

    // Add to watchlist button
    $('.add-to-watchlist').on('click', function(e) {
        e.preventDefault();
        var symbol = $(this).data('symbol');
        alert('Add ' + symbol + ' to watchlist functionality will be implemented here.');
    });

    // Remove symbol button
    $('.remove-symbol').on('click', function(e) {
        e.preventDefault();
        var symbol = $(this).data('symbol');
        if (confirm('Are you sure you want to remove ' + symbol + ' from your active symbols?')) {
            $(this).closest('tr').fadeOut();
        }
    });

    // Refresh button
    $('#refresh-symbols').on('click', function() {
        $(this).find('.dashicons').addClass('dashicons-update-spin');
        setTimeout(function() {
            location.reload();
        }, 1000);
    });

    // Filter button
    $('#apply-filters').on('click', function() {
        var activityFilter = $('#activity-filter').val();
        if (!activityFilter) {
            $('.tradepress-active-symbols-table tbody tr').show();
            return;
        }
        $('.tradepress-active-symbols-table tbody tr').each(function() {
            var activityText = $(this).find('.column-activity').text().toLowerCase();
            if (activityText.indexOf(activityFilter.toLowerCase()) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Create watchlist functionality
    const selectedSymbols = {};
    
    $('.add-to-watchlist').on('click', function() {
        const $symbolItem = $(this).closest('.symbol-item');
        const symbol = $symbolItem.data('symbol');
        const name = $symbolItem.data('name');
        
        addSymbolToSelection(symbol, name);
        
        $(this).html('<span class="dashicons dashicons-yes"></span>');
        setTimeout(() => {
            $(this).html('<span class="dashicons dashicons-plus-alt"></span>');
        }, 1500);
    });
    
    $('#search-symbols-btn').on('click', function() {
        var searchTerm = $('#symbol-search').val().trim();
        
        if (searchTerm.length === 0) {
            alert('Please enter a search term');
            return;
        }
        
        $('#symbol-search-results').show().find('.results-container').html('<p>Searching for symbols...</p>');
        
        setTimeout(function() {
            var results = [
                { symbol: 'AAPL', name: 'Apple Inc.' },
                { symbol: 'AMZN', name: 'Amazon.com, Inc.' },
                { symbol: 'AMD', name: 'Advanced Micro Devices, Inc.' },
                { symbol: 'ADBE', name: 'Adobe Inc.' }
            ];
            
            if (results.length === 0) {
                $('#symbol-search-results .results-container').html('<p>No symbols found matching your search.</p>');
                return;
            }
            
            var html = '';
            results.forEach(function(result) {
                html += '<div class="symbol-result-item" data-symbol="' + result.symbol + '" data-name="' + result.name + '">' +
                           '<div class="symbol-result-info">' +
                               '<span class="symbol-result-name">' + result.symbol + '</span>' +
                               '<span class="symbol-result-company">' + result.name + '</span>' +
                           '</div>' +
                           '<button type="button" class="button add-symbol-btn">Add</button>' +
                        '</div>';
            });
            
            $('#symbol-search-results .results-container').html(html);
        }, 1000);
    });
    
    $(document).on('click', '.add-symbol-btn', function() {
        var $resultItem = $(this).closest('.symbol-result-item');
        var symbol = $resultItem.data('symbol');
        var name = $resultItem.data('name');
        
        addSymbolToSelection(symbol, name);
        $resultItem.fadeOut();
    });
    
    function addSymbolToSelection(symbol, name) {
        if (selectedSymbols[symbol]) {
            return;
        }
        
        $('.no-symbols-message').hide();
        selectedSymbols[symbol] = name;
        
        var symbolTag = $('<div class="selected-symbol-tag" data-symbol="' + symbol + '" data-name="' + name + '">' +
                            symbol + ' <span class="remove-symbol">×</span>' +
                         '</div>');
        
        $('#selected-symbols').append(symbolTag);
        updateSymbolCount();
        updateSelectedSymbolsInput();
    }
    
    $(document).on('click', '.remove-symbol', function() {
        var $symbolTag = $(this).closest('.selected-symbol-tag');
        var symbol = $symbolTag.data('symbol');
        
        delete selectedSymbols[symbol];
        $symbolTag.remove();
        
        if (Object.keys(selectedSymbols).length === 0) {
            $('.no-symbols-message').show();
        }
        
        updateSymbolCount();
        updateSelectedSymbolsInput();
    });
    
    function updateSymbolCount() {
        const count = Object.keys(selectedSymbols).length;
        $('#selected-count').text('(' + count + ')');
    }
    
    function updateSelectedSymbolsInput() {
        var symbolsArray = Object.keys(selectedSymbols);
        $('#selected-symbols-input').val(symbolsArray.join(','));
    }
    
    $('#sector-filter').on('change', function() {
        const sector = $(this).val();
        
        if (sector === '') {
            $('.symbol-category').show();
        } else {
            $('.symbol-category').hide();
            $('.symbol-category[data-sector="' + sector + '"]').show();
        }
    });
    
    $('#browser-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === '') {
            $('.symbol-item').show();
            $('.symbol-category').show();
        } else {
            $('.symbol-item').each(function() {
                const symbolText = $(this).data('symbol').toLowerCase();
                const nameText = $(this).data('name').toLowerCase();
                
                if (symbolText.includes(searchTerm) || nameText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            $('.symbol-category').each(function() {
                const visibleItems = $(this).find('.symbol-item:visible').length;
                if (visibleItems === 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }
    });
    
    $('#create-watchlist-form').on('submit', function(e) {
        e.preventDefault();
        
        var watchlistName = $('#watchlist-name').val().trim();
        
        if (watchlistName === '') {
            alert('Please enter a watchlist name');
            $('#watchlist-name').focus();
            return false;
        }
        
        $('#create-watchlist-btn').prop('disabled', true).text('Creating...');
        
        setTimeout(function() {
            alert('Watchlist "' + watchlistName + '" created successfully!');
            window.location.href = window.location.origin + '/wp-admin/admin.php?page=tradepress_watchlists';
        }, 1500);
        
        return false;
    });
    
    $('#symbol-search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#search-symbols-btn').click();
        }
    });
    
    // User watchlists functionality
    const scrollContainer = $('.watchlists-horizontal-container');
    const scrollArrows = $('.watchlists-scroll-arrows');
    const scrollLeftBtn = $('.scroll-left');
    const scrollRightBtn = $('.scroll-right');
    
    function updateScrollArrows() {
        if (scrollContainer[0] && scrollContainer[0].scrollWidth > scrollContainer[0].clientWidth) {
            scrollArrows.css('display', 'flex');
            
            if (scrollContainer.scrollLeft() <= 0) {
                scrollLeftBtn.addClass('disabled');
            } else {
                scrollLeftBtn.removeClass('disabled');
            }
            
            if (scrollContainer.scrollLeft() + scrollContainer.width() >= scrollContainer[0].scrollWidth - 5) {
                scrollRightBtn.addClass('disabled');
            } else {
                scrollRightBtn.removeClass('disabled');
            }
        } else {
            scrollArrows.css('display', 'none');
        }
    }
    
    updateScrollArrows();
    $(window).on('resize', updateScrollArrows);
    
    scrollLeftBtn.on('click', function() {
        scrollContainer.animate({
            scrollLeft: scrollContainer.scrollLeft() - 420
        }, 300, function() {
            updateScrollArrows();
        });
    });
    
    scrollRightBtn.on('click', function() {
        scrollContainer.animate({
            scrollLeft: scrollContainer.scrollLeft() + 420
        }, 300, function() {
            updateScrollArrows();
        });
    });
    
    scrollContainer.on('scroll', function() {
        updateScrollArrows();
    });
    
    $('.view-watchlist').on('click', function(e) {
        e.preventDefault();
        var watchlistId = $(this).data('id');
        var watchlistName = $(this).closest('.watchlist-card').find('.watchlist-name').text();
        
        $('#watchlist-details-content').html(`
            <h3>${watchlistName}</h3>
            <div class="watchlist-details-content">
                <p>Loading watchlist details...</p>
                <p>In a real implementation, this would show detailed performance metrics and data for the watchlist symbols.</p>
                <div class="watchlist-symbols-performance">
                    <h4>Symbols Performance</h4>
                    <table class="widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Symbol</th>
                                <th>Current Price</th>
                                <th>Daily Change</th>
                                <th>Score</th>
                                <th>Strategy</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Loading...</td>
                                <td>Loading...</td>
                                <td>Loading...</td>
                                <td>Loading...</td>
                                <td>Loading...</td>
                                <td>Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `);
        
        $('#watchlist-details-modal').show();
    });
    
    $('.add-symbols').on('click', function(e) {
        e.preventDefault();
        var watchlistId = $(this).data('id');
        var watchlistName = $(this).closest('.watchlist-card').find('.watchlist-name').text();
        
        $('.modal-watchlist-name').text(`Adding symbols to: ${watchlistName}`);
        $('.selected-symbols').empty();
        $('.symbol-search-results').empty();
        $('.search-results').hide();
        
        $('#add-symbols-modal').show();
    });
    
    $(document).on('click', '.add-to-selected', function() {
        var symbol = $(this).data('symbol');
        var name = $(this).data('name');
        
        if ($('.selected-symbol-item[data-symbol="' + symbol + '"]').length > 0) {
            return;
        }
        
        var symbolItem = `
            <div class="selected-symbol-item" data-symbol="${symbol}">
                <span class="symbol-text">${symbol}</span>
                <span class="remove-symbol dashicons dashicons-no-alt"></span>
            </div>
        `;
        
        $('.selected-symbols').append(symbolItem);
        $(this).closest('.search-result-item').fadeOut();
    });
    
    $('.save-symbols').on('click', function() {
        var selectedSymbols = [];
        
        $('.selected-symbol-item').each(function() {
            selectedSymbols.push($(this).data('symbol'));
        });
        
        if (selectedSymbols.length === 0) {
            alert('Please select at least one symbol to add.');
            return;
        }
        
        alert('Symbols added to watchlist successfully!');
        $('#add-symbols-modal').hide();
    });
    
    $('.edit-watchlist').on('click', function(e) {
        e.preventDefault();
        var watchlistId = $(this).data('id');
        var watchlistName = $(this).closest('.watchlist-card').find('.watchlist-name').text();
        
        alert('Edit watchlist functionality will be implemented here for: ' + watchlistName);
    });
    
    $('.delete-watchlist').on('click', function(e) {
        e.preventDefault();
        var watchlistId = $(this).data('id');
        var watchlistName = $(this).closest('.watchlist-card').find('.watchlist-name').text();
        
        $('.delete-confirmation-message').text(`Are you sure you want to delete the watchlist "${watchlistName}"? This action cannot be undone.`);
        $('#delete-watchlist-modal').data('id', watchlistId).show();
    });
    
    $('.confirm-delete').on('click', function() {
        var watchlistId = $('#delete-watchlist-modal').data('id');
        
        $(`.watchlist-card[data-id="${watchlistId}"]`).fadeOut(function() {
            $(this).remove();
            
            if ($('.watchlist-card:visible').length === 0) {
                $('.watchlists-grid').replaceWith(`
                    <div class="empty-watchlists">
                        <p>You don't have any watchlists yet. Create your first watchlist to start tracking stocks in groups.</p>
                        <a href="${window.location.origin}/wp-admin/admin.php?page=tradepress_watchlists&tab=create-watchlist" class="button button-primary">
                            Create Your First Watchlist
                        </a>
                    </div>
                `);
            }
        });
        
        $('#delete-watchlist-modal').hide();
    });
    
    $('.toggle-scoring').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var watchlistId = $this.data('id');
        var isActive = $this.data('active') === 1;
        var $icon = $this.find('.scoring-icon');
        
        $icon.addClass('dashicons-update dashicons-update-spin');
        
        setTimeout(function() {
            $icon.removeClass('dashicons-update dashicons-update-spin');
            
            if (isActive) {
                $icon.removeClass('scoring-active').addClass('scoring-inactive');
                $this.attr('title', 'Enable Scoring');
                $this.data('active', 0);
                alert('Watchlist removed from scoring algorithm');
            } else {
                $icon.removeClass('scoring-inactive').addClass('scoring-active');
                $this.attr('title', 'Disable Scoring');
                $this.data('active', 1);
                alert('Watchlist added to scoring algorithm');
            }
        }, 1000);
    });
    
    $('.tradepress-modal-close, .cancel-modal').on('click', function() {
        $(this).closest('.tradepress-modal').hide();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('tradepress-modal')) {
            $('.tradepress-modal').hide();
        }
    });
});