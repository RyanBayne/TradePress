<?php
/**
 * TradePress - Create Scoring Strategies Tab
 * 
 * Drag & drop interface for creating new scoring strategies
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load directives
if (!function_exists('tradepress_get_all_directives')) {
    require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
}

$all_directives = tradepress_get_all_directives();
$active_directives = array_filter($all_directives, function($directive) {
    return $directive['active'] === true;
});
?>

<div class="create-strategies-interface">
    <div class="strategy-builder">
        <div class="builder-header">
            <h3><?php esc_html_e('Strategy Builder', 'tradepress'); ?></h3>
            <p><?php esc_html_e('Drag active directives into your strategy and configure their weights.', 'tradepress'); ?></p>
        </div>
        
        <!-- Strategy Template Selection -->
        <div class="strategy-template-section">
            <h4><?php esc_html_e('Strategy Template', 'tradepress'); ?></h4>
            <div class="template-selection">
                <select id="strategy-template" class="regular-text">
                    <option value=""><?php esc_html_e('Custom Strategy (No Template)', 'tradepress'); ?></option>
                    <option value="basic_weekly_rhythm"><?php esc_html_e('Basic Weekly Rhythm Strategy', 'tradepress'); ?></option>
                    <option value="advanced_temporal"><?php esc_html_e('Advanced Temporal Strategy', 'tradepress'); ?></option>
                    <option value="quick_weekly_setup"><?php esc_html_e('Quick Weekly Setup', 'tradepress'); ?></option>
                </select>
                <p class="description" id="template-description"><?php esc_html_e('Select a pre-configured strategy template or build a custom strategy from scratch.', 'tradepress'); ?></p>
            </div>
        </div>
        
        <div class="builder-layout">
            <!-- Available Directives Panel -->
            <div class="available-directives-panel">
                <h4><?php esc_html_e('Available Directives', 'tradepress'); ?></h4>
                <div class="directives-list" id="available-directives">
                    <?php foreach ($active_directives as $directive_id => $directive): ?>
                        <div class="directive-item" data-directive-id="<?php echo esc_attr($directive_id); ?>" draggable="true">
                            <div class="directive-header">
                                <span class="directive-name"><?php echo esc_html($directive['name']); ?></span>
                                <span class="directive-code"><?php echo esc_html($directive['code'] ?? ''); ?></span>
                            </div>
                            <div class="directive-description">
                                <?php echo esc_html($directive['description'] ?? ''); ?>
                            </div>
                            <div class="directive-impact">
                                <span class="impact-badge impact-<?php echo esc_attr($directive['impact'] ?? 'low'); ?>">
                                    <?php echo esc_html(ucfirst($directive['impact'] ?? 'low')); ?> Impact
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Strategy Builder Panel -->
            <div class="strategy-builder-panel">
                <div class="strategy-form">
                    <div class="form-group">
                        <label for="strategy-name"><?php esc_html_e('Strategy Name:', 'tradepress'); ?></label>
                        <input type="text" id="strategy-name" class="regular-text" placeholder="<?php esc_attr_e('Enter strategy name', 'tradepress'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="strategy-description"><?php esc_html_e('Description:', 'tradepress'); ?></label>
                        <textarea id="strategy-description" rows="3" class="regular-text" placeholder="<?php esc_attr_e('Describe your strategy', 'tradepress'); ?>"></textarea>
                    </div>
                </div>
                
                <div class="strategy-directives-area">
                    <h4><?php esc_html_e('Strategy Directives', 'tradepress'); ?></h4>
                    <div class="drop-zone" id="strategy-drop-zone">
                        <div class="drop-zone-placeholder">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <p><?php esc_html_e('Drag directives here to build your strategy', 'tradepress'); ?></p>
                        </div>
                    </div>
                    
                    <div class="strategy-summary">
                        <div class="weight-total">
                            <span><?php esc_html_e('Total Weight:', 'tradepress'); ?></span>
                            <span id="total-weight">0%</span>
                        </div>
                        <div class="directive-count">
                            <span><?php esc_html_e('Directives:', 'tradepress'); ?></span>
                            <span id="directive-count">0</span>
                        </div>
                    </div>
                </div>
                
                <div class="strategy-actions">
                    <button type="button" class="button button-primary" id="save-strategy" disabled>
                        <?php esc_html_e('Create Strategy', 'tradepress'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="test-strategy" disabled>
                        <?php esc_html_e('Test Strategy', 'tradepress'); ?>
                    </button>
                    <button type="button" class="button" id="clear-strategy">
                        <?php esc_html_e('Clear All', 'tradepress'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.create-strategies-interface {
    margin: 20px 0;
}

.builder-layout {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.available-directives-panel {
    flex: 1;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.strategy-builder-panel {
    flex: 1;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.directive-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: grab;
    transition: all 0.2s ease;
}

.directive-item:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.directive-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.directive-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.directive-name {
    font-weight: 600;
    color: #0073aa;
}

.directive-code {
    background: #0073aa;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
}

.directive-description {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.impact-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.impact-low { background: #e8f5e8; color: #2e7d32; }
.impact-medium { background: #fff3e0; color: #f57c00; }
.impact-high { background: #ffebee; color: #c62828; }

.drop-zone {
    min-height: 200px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 20px;
    margin: 15px 0;
    transition: all 0.2s ease;
}

.drop-zone.drag-over {
    border-color: #0073aa;
    background: #f0f8ff;
}

.drop-zone-placeholder {
    text-align: center;
    color: #999;
}

.drop-zone-placeholder .dashicons {
    font-size: 48px;
    margin-bottom: 10px;
}

.strategy-directive-item {
    background: #f0f8ff;
    border: 1px solid #0073aa;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    position: relative;
}

.strategy-directive-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.remove-directive {
    background: #dc3232;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 12px;
}

.weight-input {
    width: 80px;
    text-align: center;
}

.strategy-summary {
    display: flex;
    justify-content: space-between;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    margin: 15px 0;
}

.strategy-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.strategy-template-section {
    background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
    border: 2px solid #0073aa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
}

.strategy-template-section::before {
    content: '✨';
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
}

.template-selection {
    margin-top: 10px;
}

.template-selection select {
    width: 100%;
    max-width: 400px;
}

.template-description {
    margin-top: 10px;
    padding: 10px;
    background: #f0f8ff;
    border-left: 4px solid #0073aa;
    border-radius: 4px;
    display: none;
}

.directive-item.template-recommended {
    border-color: #0073aa;
    background: #f0f8ff;
    position: relative;
}

.directive-item.template-recommended::before {
    content: 'Recommended';
    position: absolute;
    top: -8px;
    right: 10px;
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
}

.directive-item.template-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.directive-item.template-disabled::after {
    content: 'API Requirements Not Met';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(220, 50, 50, 0.9);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    z-index: 10;
}
</style>

<script>
jQuery(document).ready(function($) {
    let draggedElement = null;
    let strategyDirectives = [];
    let currentTemplate = '';
    
    // Strategy templates configuration
    const strategyTemplates = {
        basic_weekly_rhythm: {
            name: 'Basic Weekly Rhythm Strategy',
            description: 'Focuses on weekly market patterns using Monday Effect, Midweek Momentum, and Volume Rhythm directives. Ideal for swing trading strategies.',
            recommended: ['monday_effect', 'midweek_momentum', 'volume_rhythm'],
            weights: { monday_effect: 35, midweek_momentum: 35, volume_rhythm: 30 },
            apiRequirements: ['alpha_vantage']
        },
        advanced_temporal: {
            name: 'Advanced Temporal Strategy',
            description: 'Comprehensive strategy using all 7 weekly rhythm directives. Requires multiple API platforms for full functionality.',
            recommended: ['monday_effect', 'friday_positioning', 'midweek_momentum', 'volume_rhythm', 'institutional_timing', 'intraday_u_pattern', 'time_based_support'],
            weights: { monday_effect: 15, friday_positioning: 15, midweek_momentum: 15, volume_rhythm: 15, institutional_timing: 15, intraday_u_pattern: 12, time_based_support: 13 },
            apiRequirements: ['alpha_vantage', 'finnhub', 'alpaca']
        },
        quick_weekly_setup: {
            name: 'Quick Weekly Setup',
            description: 'Uses composite directives for fast configuration. Perfect for users who want pre-balanced weekly rhythm strategies.',
            recommended: ['basic_weekly_rhythm', 'advanced_weekly_rhythm'],
            weights: { basic_weekly_rhythm: 60, advanced_weekly_rhythm: 40 },
            apiRequirements: ['alpha_vantage']
        }
    };
    
    // Template selection handler
    $('#strategy-template').on('change', function() {
        const templateId = $(this).val();
        currentTemplate = templateId;
        
        if (templateId && strategyTemplates[templateId]) {
            applyTemplate(strategyTemplates[templateId]);
        } else {
            clearTemplate();
        }
    });
    
    function applyTemplate(template) {
        // Update description
        $('#template-description').html(`
            <strong>${template.name}:</strong> ${template.description}
            <br><strong>API Requirements:</strong> ${template.apiRequirements.join(', ')}
        `).addClass('template-description').show();
        
        // Clear existing strategy
        strategyDirectives = [];
        
        // Pre-populate strategy name if empty
        if (!$('#strategy-name').val().trim()) {
            $('#strategy-name').val(template.name);
        }
        
        // Highlight recommended directives
        $('.directive-item').removeClass('template-recommended template-disabled');
        template.recommended.forEach(directiveId => {
            $(`.directive-item[data-directive-id="${directiveId}"]`).addClass('template-recommended');
        });
        
        // Check API requirements and disable if not met
        if (template.apiRequirements.includes('finnhub') || template.apiRequirements.includes('alpaca')) {
            // For demo purposes, show as disabled for advanced requirements
            if (template.name.includes('Advanced')) {
                template.recommended.forEach(directiveId => {
                    if (['intraday_u_pattern', 'time_based_support'].includes(directiveId)) {
                        $(`.directive-item[data-directive-id="${directiveId}"]`)
                            .removeClass('template-recommended')
                            .addClass('template-disabled');
                    }
                });
            }
        }
        
        // Auto-add recommended directives with template weights
        template.recommended.forEach(directiveId => {
            const $directive = $(`.directive-item[data-directive-id="${directiveId}"]`);
            if ($directive.length && !$directive.hasClass('template-disabled')) {
                const directiveName = $directive.find('.directive-name').text();
                const weight = template.weights[directiveId] || 20;
                
                strategyDirectives.push({
                    id: directiveId,
                    name: directiveName,
                    weight: weight
                });
            }
        });
        
        renderStrategyDirectives();
        updateSummary();
        updateButtons();
    }
    
    function clearTemplate() {
        $('#template-description').hide().removeClass('template-description');
        $('.directive-item').removeClass('template-recommended template-disabled');
        currentTemplate = '';
    }
    
    // Drag and drop functionality
    $('.directive-item').on('dragstart', function(e) {
        draggedElement = this;
        $(this).addClass('dragging');
    });
    
    $('.directive-item').on('dragend', function(e) {
        $(this).removeClass('dragging');
        draggedElement = null;
    });
    
    $('#strategy-drop-zone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('#strategy-drop-zone').on('dragleave', function(e) {
        $(this).removeClass('drag-over');
    });
    
    $('#strategy-drop-zone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        if (draggedElement) {
            const directiveId = $(draggedElement).data('directive-id');
            const directiveName = $(draggedElement).find('.directive-name').text();
            
            // Check if already added
            if (strategyDirectives.find(d => d.id === directiveId)) {
                alert('Directive already added to strategy');
                return;
            }
            
            addDirectiveToStrategy(directiveId, directiveName);
            updateStrategyDisplay();
        }
    });
    
    function addDirectiveToStrategy(id, name) {
        // Get template weight if available
        let weight = 20; // Default weight
        if (currentTemplate && strategyTemplates[currentTemplate] && strategyTemplates[currentTemplate].weights[id]) {
            weight = strategyTemplates[currentTemplate].weights[id];
        }
        
        strategyDirectives.push({
            id: id,
            name: name,
            weight: weight
        });
        
        renderStrategyDirectives();
        updateSummary();
        updateButtons();
    }
    
    function renderStrategyDirectives() {
        const $dropZone = $('#strategy-drop-zone');
        $dropZone.empty();
        
        if (strategyDirectives.length === 0) {
            $dropZone.html(`
                <div class="drop-zone-placeholder">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <p><?php esc_html_e('Drag directives here to build your strategy', 'tradepress'); ?></p>
                </div>
            `);
            return;
        }
        
        strategyDirectives.forEach((directive, index) => {
            $dropZone.append(`
                <div class="strategy-directive-item" data-index="${index}">
                    <div class="strategy-directive-header">
                        <span class="directive-name">${directive.name}</span>
                        <button type="button" class="remove-directive" data-index="${index}">×</button>
                    </div>
                    <div class="weight-control">
                        <label>Weight: </label>
                        <input type="number" class="weight-input" value="${directive.weight}" min="1" max="100" data-index="${index}">
                        <span>%</span>
                    </div>
                </div>
            `);
        });
    }
    
    // Remove directive
    $(document).on('click', '.remove-directive', function() {
        const index = $(this).data('index');
        strategyDirectives.splice(index, 1);
        renderStrategyDirectives();
        updateSummary();
        updateButtons();
    });
    
    // Update weight
    $(document).on('input', '.weight-input', function() {
        const index = $(this).data('index');
        const weight = parseInt($(this).val()) || 0;
        strategyDirectives[index].weight = weight;
        updateSummary();
    });
    
    function updateSummary() {
        const totalWeight = strategyDirectives.reduce((sum, d) => sum + d.weight, 0);
        $('#total-weight').text(totalWeight + '%');
        $('#directive-count').text(strategyDirectives.length);
        
        // Color code total weight
        const $totalWeight = $('#total-weight');
        $totalWeight.removeClass('weight-low weight-perfect weight-high');
        if (totalWeight < 90) $totalWeight.addClass('weight-low');
        else if (totalWeight >= 90 && totalWeight <= 110) $totalWeight.addClass('weight-perfect');
        else $totalWeight.addClass('weight-high');
    }
    
    function updateButtons() {
        const hasDirectives = strategyDirectives.length > 0;
        const hasName = $('#strategy-name').val().trim().length > 0;
        
        $('#save-strategy, #test-strategy').prop('disabled', !(hasDirectives && hasName));
    }
    
    // Clear strategy
    $('#clear-strategy').on('click', function() {
        if (confirm('<?php esc_html_e('Clear all directives from strategy?', 'tradepress'); ?>')) {
            strategyDirectives = [];
            $('#strategy-template').val('').trigger('change'); // Reset template
            $('#strategy-name, #strategy-description').val(''); // Clear form
            renderStrategyDirectives();
            updateSummary();
            updateButtons();
        }
    });
    
    // Update buttons on name change
    $('#strategy-name').on('input', updateButtons);
    
    // Save strategy
    $('#save-strategy').on('click', function() {
        const $button = $(this);
        const strategyData = {
            name: $('#strategy-name').val().trim(),
            description: $('#strategy-description').val().trim(),
            directives: strategyDirectives.map((d, index) => ({
                id: d.id,
                name: d.name,
                weight: d.weight,
                sort_order: index
            }))
        };
        
        $button.prop('disabled', true).text('Creating...');
        
        $.post(ajaxurl, {
            action: 'tradepress_create_strategy',
            nonce: '<?php echo wp_create_nonce('tradepress_strategy_nonce'); ?>',
            name: strategyData.name,
            description: strategyData.description,
            template: currentTemplate,
            directives: JSON.stringify(strategyData.directives)
        })
        .done(function(response) {
            if (response.success) {
                alert('Strategy created successfully!');
                // Redirect to manage strategies
                window.location.href = '<?php echo admin_url('admin.php?page=tradepress_scoring_directives&tab=manage_strategies'); ?>';
            } else {
                alert('Error: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            $button.prop('disabled', false).text('Create Strategy');
        });
    });
    
    // Test strategy
    $('#test-strategy').on('click', function() {
        if (strategyDirectives.length === 0) {
            alert('Please add directives to test the strategy');
            return;
        }
        
        const $button = $(this);
        $button.prop('disabled', true).text('Testing...');
        
        // Create temporary strategy for testing
        const testData = {
            directives: strategyDirectives.map((d, index) => ({
                id: d.id,
                name: d.name,
                weight: d.weight,
                sort_order: index
            }))
        };
        
        // For now, show test preview
        let testResults = 'Strategy Test Preview:\n\n';
        testResults += 'Directives:\n';
        strategyDirectives.forEach(d => {
            testResults += `- ${d.name}: ${d.weight}%\n`;
        });
        testResults += `\nTotal Weight: ${strategyDirectives.reduce((sum, d) => sum + d.weight, 0)}%\n`;
        testResults += '\nNote: Full testing available after strategy is saved.';
        
        alert(testResults);
        $button.prop('disabled', false).text('Test Strategy');
    });
});
</script>

<style>
.weight-low { color: #d63638; }
.weight-perfect { color: #00a32a; font-weight: bold; }
.weight-high { color: #dba617; }
</style>