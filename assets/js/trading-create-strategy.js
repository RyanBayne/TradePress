/**
 * TradePress - Create Strategy Scripts
 *
 * @package  TradePress/assets/js
 * @since    1.0.0
 */

jQuery(document).ready(function($) {
    // Check if jQuery UI is loaded
    if (!$.ui) {
        console.error('jQuery UI is not loaded. Drag and drop functionality will not work.');
        // Add a notice to the page
        $('.create-strategy-container').prepend('<div class="notice notice-error"><p>Error: jQuery UI is required but not loaded. Please contact the administrator.</p></div>');
        return;
    }

    // Initialize accordion for indicator categories
    $(".category-header").each(function() {
        $(this).on("click", function() {
            $(this).toggleClass("collapsed");
            const categoryDirectives = $(this).next(".category-directives");
            
            if ($(this).hasClass("collapsed")) {
                categoryDirectives.css("max-height", "0");
            } else {
                const scrollHeight = categoryDirectives.prop("scrollHeight");
                categoryDirectives.css("max-height", scrollHeight + "px");
            }
        });
    });
    
    // Track if strategy has changes
    let strategyHasChanges = false;
    
    // Initialize sortable for strategy directives
    $("#strategy-directives").sortable({
        placeholder: "ui-sortable-placeholder",
        handle: ".directive-drag-handle",
        cursor: "move",
        update: function() {
            updateTotalWeight();
            strategyHasChanges = true;
        }
    });
    
    // Make directive items draggable
    $(".directive-item").draggable({
        connectToSortable: "#strategy-directives",
        helper: "clone",
        revert: "invalid",
        cursor: "move",
        start: function(event, ui) {
            // Add some styling to the helper
            ui.helper.addClass("ui-draggable-dragging");
        },
        stop: function(event, ui) {
            // Handle the drop manually if needed
        }
    });
    
    // Handle dropping of directives
    $("#strategy-directives").droppable({
        accept: ".directive-item",
        drop: function(event, ui) {
            // Only add if it's a completely new item from the left panel
            if (ui.draggable.hasClass("directive-item") && ui.draggable.parent().hasClass("category-directives")) {
                addDirectiveToStrategy(ui.draggable);
            }
        }
    });
    
    // Handle clicking the add button 
    $(document).on("click", ".add-directive", function() {
        const directiveItem = $(this).closest(".directive-item");
        addDirectiveToStrategy(directiveItem);
    });
    
    // Add directive to strategy function
    function addDirectiveToStrategy(directiveItem) {
        const directiveId = directiveItem.data("id");
        const directiveName = directiveItem.data("name");
        const directiveDescription = directiveItem.data("description");
        const directiveWeight = directiveItem.data("weight") || 10;
        
        // Check if this directive is already in the strategy
        if ($("#strategy-directives").find(`[data-id="${directiveId}"]`).length > 0) {
            alert("This indicator is already in your strategy.");
            return;
        }
        
        // Clone the template
        const template = document.getElementById("strategy-directive-template");
        const directiveElement = document.importNode(template.content, true).querySelector(".strategy-directive");
        
        // Set directive data
        $(directiveElement).attr("data-id", directiveId);
        $(directiveElement).find("input[name='directive_ids[]']").val(directiveId);
        $(directiveElement).find(".directive-title").text(directiveName);
        $(directiveElement).find(".directive-description").text(directiveDescription);
        $(directiveElement).find(".directive-weight-badge").text(directiveWeight + "%");
        
        // Clone settings template
        const settingsTemplate = document.getElementById("directive-settings-template");
        const settingsPanel = document.importNode(settingsTemplate.content, true).querySelector(".directive-settings-panel");
        
        // Update ID in radio button names to make them unique
        const uniqueId = "dir_" + new Date().getTime() + "_" + Math.floor(Math.random() * 1000);
        $(settingsPanel).find("input[name^='directive_direction_']").attr("name", "directive_direction_" + uniqueId);
        
        // Set initial weight
        $(settingsPanel).find(".directive-weight-slider").val(directiveWeight);
        $(settingsPanel).find(".weight-value").text(directiveWeight + "%");
        $(settingsPanel).find(".directive-weight-input").val(directiveWeight);
        
        // Add settings panel to directive
        $(directiveElement).find(".directive-content").append(settingsPanel);
        
        // Remove empty message if present
        $(".empty-strategy-message").remove();
        
        // Add to strategy
        $("#strategy-directives").append(directiveElement);
        
        // Update total weight
        updateTotalWeight();
        
        // Mark strategy as changed
        strategyHasChanges = true;
    }
    
    // Remove directive from strategy
    $(document).on("click", ".directive-remove", function() {
        $(this).closest(".strategy-directive").remove();
        
        // Show empty message if no directives left
        if ($("#strategy-directives").children(".strategy-directive").length === 0) {
            const emptyMessage = $('<div class="empty-strategy-message"><div class="empty-icon"><span class="dashicons dashicons-arrow-left-alt"></span></div><p>Drag indicators from the left panel to build your strategy.</p></div>');
            $("#strategy-directives").append(emptyMessage);
        }
        
        updateTotalWeight();
        strategyHasChanges = true;
    });
    
    // Toggle directive settings
    $(document).on("click", ".directive-settings-toggle", function() {
        const settingsPanel = $(this).closest(".directive-content").find(".directive-settings-panel");
        settingsPanel.slideToggle(200);
    });
    
    // Weight slider change
    $(document).on("input", ".directive-weight-slider", function() {
        const weight = $(this).val();
        $(this).siblings(".weight-value").text(weight + "%");
        $(this).siblings(".directive-weight-input").val(weight);
        $(this).closest(".strategy-directive").find(".directive-weight-badge").text(weight + "%");
        updateTotalWeight();
        strategyHasChanges = true;
    });
    
    // Calculate and update total weight
    function updateTotalWeight() {
        let totalWeight = 0;
        $(".strategy-directive").each(function() {
            const weight = parseInt($(this).find(".directive-weight-input").val()) || 0;
            totalWeight += weight;
        });
        
        // Update the progress bar
        $(".weight-progress").css("width", Math.min(totalWeight, 100) + "%");
        $(".weight-value").text(totalWeight + "%");
        
        // Update status message
        const weightStatus = $(".weight-status");
        weightStatus.removeClass("warning success");
        
        if (totalWeight === 0) {
            weightStatus.find(".weight-message").text("Add indicators to your strategy");
        } else if (totalWeight < 100) {
            weightStatus.addClass("warning");
            weightStatus.find(".weight-message").text("Total weight should equal 100%");
        } else if (totalWeight > 100) {
            weightStatus.addClass("warning");
            weightStatus.find(".weight-message").text("Total weight exceeds 100%");
        } else {
            weightStatus.addClass("success");
            weightStatus.find(".weight-message").text("Perfect! Weights add up to 100%");
        }
    }
    
    // Search functionality
    $("#search-directives").on("input", function() {
        const searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === "") {
            // Show all directives and categories
            $(".directive-item").show();
            $(".directive-category").show();
            $(".category-header").removeClass("collapsed");
            $(".category-directives").css("max-height", "");
            return;
        }
        
        // Hide directives that don't match
        $(".directive-item").each(function() {
            const name = $(this).data("name").toLowerCase();
            const description = $(this).data("description").toLowerCase();
            
            if (name.includes(searchTerm) || description.includes(searchTerm)) {
                $(this).show();
                // Make sure parent category is visible
                $(this).closest(".directive-category").show();
                // Expand the category
                const header = $(this).closest(".directive-category").find(".category-header");
                header.removeClass("collapsed");
                const directives = $(this).closest(".category-directives");
                const height = directives.prop("scrollHeight");
                directives.css("max-height", height + "px");
            } else {
                $(this).hide();
            }
        });
        
        // Hide empty categories
        $(".directive-category").each(function() {
            const visibleDirectives = $(this).find(".directive-item:visible").length;
            if (visibleDirectives === 0) {
                $(this).hide();
            }
        });
    });
    
    // Show directive details in modal
    $(document).on("click", ".directive-info", function() {
        const directiveItem = $(this).closest(".directive-item");
        const id = directiveItem.data("id");
        const name = directiveItem.data("name");
        const description = directiveItem.data("description");
        const bullish = directiveItem.data("bullish");
        const bearish = directiveItem.data("bearish");
        
        // Populate modal
        $("#modal-directive-name").text(name);
        $("#modal-directive-description").text(description);
        $("#modal-directive-bullish").text(bullish || "Not specified");
        $("#modal-directive-bearish").text(bearish || "Not specified");
        
        // Store ID for potential add action
        $(".add-to-strategy").data("directive-id", id);
        
        // Show modal
        $("#directive-details-modal").show();
    });
    
    // Close modal
    $(".tradepress-close-modal, .tradepress-close-button").on("click", function() {
        $("#directive-details-modal").hide();
    });
    
    // Add to strategy from modal
    $(".add-to-strategy").on("click", function() {
        const directiveId = $(this).data("directive-id");
        const directiveItem = $(".directive-item[data-id='" + directiveId + "']");
        
        addDirectiveToStrategy(directiveItem);
        $("#directive-details-modal").hide();
    });
    
    // Also close modal if user clicks outside
    $(window).on("click", function(event) {
        if ($(event.target).is(".tradepress-modal")) {
            $(".tradepress-modal").hide();
        }
    });
    
    // Toggle strategy settings panel
    $(".strategy-settings-toggle").on("click", function() {
        $(this).toggleClass("collapsed");
        $(".strategy-settings-panel").slideToggle(200);
    });
    
    // Save strategy
    $("#save-strategy").on("click", function() {
        const strategyName = $("#strategy-name").val();
        const strategyDescription = $("#strategy-description").val();
        
        if (!strategyName) {
            alert("Please enter a name for your strategy.");
            $("#strategy-name").focus();
            return;
        }
        
        if ($("#strategy-directives").children(".strategy-directive").length === 0) {
            alert("Your strategy needs at least one indicator. Drag indicators from the left panel to build your strategy.");
            return;
        }
        
        const totalWeight = parseInt($(".weight-value").text()) || 0;
        if (totalWeight !== 100) {
            const proceed = confirm("The total weight of your indicators is " + totalWeight + "%, not 100%. Would you like to normalize the weights automatically?");
            
            if (proceed) {
                normalizeWeights();
            } else {
                return;
            }
        }
        
        // In a real implementation, we would submit the form via AJAX here
        alert("Strategy saved successfully. (Demo implementation)");
        strategyHasChanges = false;
    });
    
    // Reset strategy
    $("#reset-strategy").on("click", function() {
        if (strategyHasChanges) {
            if (!confirm("Are you sure you want to reset your strategy? All changes will be lost.")) {
                return;
            }
        }
        
        // Clear strategy
        $("#strategy-directives").empty();
        
        // Add empty message
        const emptyMessage = $('<div class="empty-strategy-message"><div class="empty-icon"><span class="dashicons dashicons-arrow-left-alt"></span></div><p>Drag indicators from the left panel to build your strategy.</p></div>');
        $("#strategy-directives").append(emptyMessage);
        
        // Reset name and description
        $("#strategy-name").val("");
        $("#strategy-description").val("");
        
        // Update weight
        updateTotalWeight();
        
        strategyHasChanges = false;
    });
    
    // Normalize weights function
    function normalizeWeights() {
        const directives = $(".strategy-directive");
        const count = directives.length;
        
        if (count === 0) return;
        
        let totalWeight = 0;
        const weights = [];
        
        // Get all current weights
        directives.each(function(index) {
            const weight = parseInt($(this).find(".directive-weight-input").val()) || 0;
            weights[index] = weight;
            totalWeight += weight;
        });
        
        // If total is 0, distribute evenly
        if (totalWeight === 0) {
            const evenWeight = Math.floor(100 / count);
            let remainder = 100 - (evenWeight * count);
            
            directives.each(function(index) {
                let newWeight = evenWeight;
                if (remainder > 0) {
                    newWeight++;
                    remainder--;
                }
                
                $(this).find(".directive-weight-slider").val(newWeight);
                $(this).find(".weight-value").text(newWeight + "%");
                $(this).find(".directive-weight-input").val(newWeight);
                $(this).find(".directive-weight-badge").text(newWeight + "%");
            });
        } else {
            // Normalize proportionally
            let newTotalWeight = 0;
            
            directives.each(function(index) {
                let newWeight = Math.floor((weights[index] / totalWeight) * 100);
                newTotalWeight += newWeight;
                
                $(this).find(".directive-weight-slider").val(newWeight);
                $(this).find(".weight-value").text(newWeight + "%");
                $(this).find(".directive-weight-input").val(newWeight);
                $(this).find(".directive-weight-badge").text(newWeight + "%");
            });
            
            // Distribute any remaining weight (due to rounding)
            let remainder = 100 - newTotalWeight;
            if (remainder > 0) {
                // Add to the first few directives
                for (let i = 0; i < remainder; i++) {
                    const directive = directives.eq(i);
                    const currentWeight = parseInt(directive.find(".directive-weight-input").val());
                    const newWeight = currentWeight + 1;
                    
                    directive.find(".directive-weight-slider").val(newWeight);
                    directive.find(".weight-value").text(newWeight + "%");
                    directive.find(".directive-weight-input").val(newWeight);
                    directive.find(".directive-weight-badge").text(newWeight + "%");
                }
            }
        }
        
        updateTotalWeight();
    }
    
    // Warn on page leave if unsaved changes
    window.addEventListener("beforeunload", function(e) {
        if (strategyHasChanges) {
            const message = "You have unsaved changes to your strategy. Are you sure you want to leave?";
            e.returnValue = message;
            return message;
        }
    });
});