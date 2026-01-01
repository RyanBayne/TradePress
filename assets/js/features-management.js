/**
 * TradePress Features Management Script
 */
(function($) {
    'use strict';
    
    // Feature management functionality
    var TradePressFeaturesManager = {
        
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initializeStats();
        },
        
        cacheDom: function() {
            this.$container = $('.tradepress-features-container');
            this.$pageSections = $('.feature-page-section');
            this.$featuresRows = $('.feature-row');
            this.$expandAll = $('.expand-all');
            this.$collapseAll = $('.collapse-all');
            this.$statusFilter = $('#feature-status-filter');
            this.$statusSelects = $('.feature-status select');
            this.$tabToggles = $('.tab-toggle input[type="checkbox"]');
            this.$featureToggles = $('.feature-enabled input[type="checkbox"]');
            
            // Stats elements
            this.$totalFeatures = $('#total-features');
            this.$liveFeatures = $('#live-features');
            this.$demoFeatures = $('#demo-features');
            this.$completionRate = $('#completion-rate');
        },
        
        bindEvents: function() {
            var self = this;
            
            // Section toggling
            this.$pageSections.find('.feature-page-header').on('click', function() {
                self.toggleSection($(this));
            });
            
            // Expand/Collapse all
            this.$expandAll.on('click', function(e) {
                e.preventDefault();
                self.expandAll();
            });
            
            this.$collapseAll.on('click', function(e) {
                e.preventDefault();
                self.collapseAll();
            });
            
            // Status filtering
            this.$statusFilter.on('change', function() {
                self.filterByStatus($(this).val());
            });
            
            // Status change
            this.$statusSelects.on('change', function() {
                self.updateRowStatus($(this));
                self.updateStats();
            });
            
            // Tab toggle change
            this.$tabToggles.on('change', function() {
                self.toggleTab($(this));
            });
            
            // Feature toggle change
            this.$featureToggles.on('change', function() {
                self.toggleFeature($(this));
            });
        },
        
        toggleSection: function($header) {
            var $content = $header.closest('.feature-page-section').find('.feature-page-content');
            var $icon = $header.find('.toggle-section');
            
            $content.slideToggle(300);
            $icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        },
        
        expandAll: function() {
            this.$pageSections.find('.feature-page-content').slideDown(300);
            this.$pageSections.find('.toggle-section')
                .removeClass('dashicons-arrow-down-alt2')
                .addClass('dashicons-arrow-up-alt2');
        },
        
        collapseAll: function() {
            this.$pageSections.find('.feature-page-content').slideUp(300);
            this.$pageSections.find('.toggle-section')
                .removeClass('dashicons-arrow-up-alt2')
                .addClass('dashicons-arrow-down-alt2');
        },
        
        filterByStatus: function(status) {
            if (status === 'all') {
                this.$featuresRows.show();
            } else {
                this.$featuresRows.hide();
                this.$featuresRows.filter('.' + status + '-status').show();
            }
        },
        
        updateRowStatus: function($select) {
            var $row = $select.closest('tr');
            var status = $select.val();
            
            $row.removeClass('live-status demo-status');
            $row.addClass(status + '-status');
        },
        
        toggleTab: function($checkbox) {
            var $tabCell = $checkbox.closest('.tab-cell');
            var enabled = $checkbox.prop('checked');
            
            // Get all features in this tab (all rows with this tab)
            var $features = $tabCell.closest('tr').nextUntil('tr:has(.tab-cell)').addBack();
            
            if (enabled) {
                $features.removeClass('disabled');
                $features.find('.feature-enabled input[type="checkbox"]').prop('disabled', false);
            } else {
                $features.addClass('disabled');
                $features.find('.feature-enabled input[type="checkbox"]').prop('disabled', true);
            }
        },
        
        toggleFeature: function($checkbox) {
            var $row = $checkbox.closest('tr');
            var enabled = $checkbox.prop('checked');
            
            if (enabled) {
                $row.removeClass('disabled');
            } else {
                $row.addClass('disabled');
            }
        },
        
        initializeStats: function() {
            this.updateStats();
            
            // Apply initial states to features
            this.$featureToggles.each(function() {
                var $checkbox = $(this);
                var enabled = $checkbox.prop('checked');
                var $row = $checkbox.closest('tr');
                
                if (!enabled) {
                    $row.addClass('disabled');
                }
            });
        },
        
        updateStats: function() {
            var total = this.$featuresRows.length;
            var live = this.$featuresRows.filter('.live-status').length;
            var demo = total - live;
            var completionRate = Math.round((live / total) * 100);
            
            this.$totalFeatures.text(total);
            this.$liveFeatures.text(live);
            this.$demoFeatures.text(demo);
            this.$completionRate.text(completionRate + '%');
        }
    };
    
    $(document).ready(function() {
        TradePressFeaturesManager.init();
    });
    
})(jQuery);
