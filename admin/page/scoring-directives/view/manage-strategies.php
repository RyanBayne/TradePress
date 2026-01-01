<?php
/**
 * TradePress - Manage Scoring Strategies Tab
 * 
 * Edit and configure existing scoring strategies
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load strategies from database
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/class-scoring-strategies-db.php';

$strategies = TradePress_Scoring_Strategies_DB::get_strategies(array(
    'limit' => 20,
    'orderby' => 'updated_at',
    'order' => 'DESC'
));

// Convert to array format and add directive data
$sample_strategies = array();
foreach ($strategies as $strategy) {
    $directives = TradePress_Scoring_Strategies_DB::get_strategy_directives($strategy->id);
    
    $directive_array = array();
    foreach ($directives as $directive) {
        $directive_array[] = array(
            'id' => $directive->directive_id,
            'name' => $directive->directive_name,
            'weight' => $directive->weight
        );
    }
    
    $sample_strategies[] = array(
        'id' => $strategy->id,
        'name' => $strategy->name,
        'description' => $strategy->description,
        'directives' => $directive_array,
        'created' => date('Y-m-d', strtotime($strategy->created_at)),
        'last_used' => $strategy->last_test_date ? human_time_diff(strtotime($strategy->last_test_date)) . ' ago' : 'Never',
        'status' => $strategy->status,
        'total_tests' => $strategy->total_tests,
        'success_rate' => $strategy->success_rate
    );
}
?>

<div class="manage-strategies-interface">
    <div class="strategies-header">
        <h3><?php esc_html_e('Manage Scoring Strategies', 'tradepress'); ?></h3>
        <p><?php esc_html_e('Edit existing strategies, modify directive weights, and configure per-strategy settings.', 'tradepress'); ?></p>
        
        <div class="header-actions">
            <a href="<?php echo admin_url('admin.php?page=tradepress_scoring_directives&tab=create_strategies'); ?>" class="button button-primary">
                <?php esc_html_e('Create New Strategy', 'tradepress'); ?>
            </a>
        </div>
    </div>
    
    <div class="strategies-list">
        <?php foreach ($sample_strategies as $strategy): ?>
            <div class="strategy-card" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                <div class="strategy-header">
                    <div class="strategy-info">
                        <h4 class="strategy-name"><?php echo esc_html($strategy['name']); ?></h4>
                        <p class="strategy-description"><?php echo esc_html($strategy['description']); ?></p>
                    </div>
                    <div class="strategy-status">
                        <span class="status-badge status-<?php echo esc_attr($strategy['status']); ?>">
                            <?php echo esc_html(ucfirst($strategy['status'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="strategy-meta">
                    <div class="meta-item">
                        <span class="meta-label"><?php esc_html_e('Created:', 'tradepress'); ?></span>
                        <span class="meta-value"><?php echo esc_html($strategy['created']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label"><?php esc_html_e('Last Used:', 'tradepress'); ?></span>
                        <span class="meta-value"><?php echo esc_html($strategy['last_used']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label"><?php esc_html_e('Directives:', 'tradepress'); ?></span>
                        <span class="meta-value"><?php echo count($strategy['directives']); ?></span>
                    </div>
                </div>
                
                <div class="strategy-directives">
                    <h5><?php esc_html_e('Directive Weights:', 'tradepress'); ?></h5>
                    <div class="directives-grid">
                        <?php foreach ($strategy['directives'] as $directive): ?>
                            <div class="directive-weight-item">
                                <span class="directive-name"><?php echo esc_html($directive['name']); ?></span>
                                <span class="directive-weight"><?php echo esc_html($directive['weight']); ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="strategy-actions">
                    <button type="button" class="button button-primary edit-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                        <?php esc_html_e('Edit Strategy', 'tradepress'); ?>
                    </button>
                    <button type="button" class="button test-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                        <?php esc_html_e('Test Strategy', 'tradepress'); ?>
                    </button>
                    <button type="button" class="button duplicate-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                        <?php esc_html_e('Duplicate', 'tradepress'); ?>
                    </button>
                    <?php if ($strategy['status'] === 'active'): ?>
                        <button type="button" class="button deactivate-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                            <?php esc_html_e('Deactivate', 'tradepress'); ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="button button-secondary activate-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                            <?php esc_html_e('Activate', 'tradepress'); ?>
                        </button>
                    <?php endif; ?>
                    <button type="button" class="button button-link-delete delete-strategy" data-strategy-id="<?php echo esc_attr($strategy['id']); ?>">
                        <?php esc_html_e('Delete', 'tradepress'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($sample_strategies)): ?>
        <div class="no-strategies">
            <div class="no-strategies-content">
                <span class="dashicons dashicons-chart-line"></span>
                <h3><?php esc_html_e('No Strategies Found', 'tradepress'); ?></h3>
                <p><?php esc_html_e('You haven\'t created any scoring strategies yet.', 'tradepress'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=tradepress_scoring_directives&tab=create_strategies'); ?>" class="button button-primary">
                    <?php esc_html_e('Create Your First Strategy', 'tradepress'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.manage-strategies-interface {
    margin: 20px 0;
}

.strategies-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.strategies-header h3 {
    margin: 0 0 10px 0;
}

.strategies-list {
    display: grid;
    gap: 20px;
}

.strategy-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s ease;
}

.strategy-card:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.strategy-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.strategy-name {
    margin: 0 0 8px 0;
    color: #0073aa;
}

.strategy-description {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #e8f5e8;
    color: #2e7d32;
}

.status-draft {
    background: #fff3e0;
    color: #f57c00;
}

.status-inactive {
    background: #f5f5f5;
    color: #666;
}

.strategy-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    padding: 10px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.meta-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    font-weight: 600;
}

.meta-value {
    font-size: 14px;
    color: #333;
}

.strategy-directives h5 {
    margin: 0 0 10px 0;
    font-size: 14px;
}

.directives-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 8px;
}

.directive-weight-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 10px;
    background: #f9f9f9;
    border-radius: 4px;
    font-size: 13px;
}

.directive-weight {
    font-weight: 600;
    color: #0073aa;
}

.strategy-actions {
    display: flex;
    gap: 8px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
    flex-wrap: wrap;
}

.no-strategies {
    text-align: center;
    padding: 60px 20px;
}

.no-strategies-content .dashicons {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-strategies-content h3 {
    color: #666;
    margin-bottom: 10px;
}

.no-strategies-content p {
    color: #999;
    margin-bottom: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Edit strategy
    $('.edit-strategy').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        // TODO: Implement edit functionality
        alert('Edit functionality will be implemented in next phase. Strategy ID: ' + strategyId);
    });
    
    // Test strategy
    $('.test-strategy').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        // TODO: Implement test functionality
        alert('Test functionality will be implemented in next phase. Strategy ID: ' + strategyId);
    });
    
    // Duplicate strategy
    $('.duplicate-strategy').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        const $button = $(this);
        
        $button.prop('disabled', true).text('Duplicating...');
        
        $.post(ajaxurl, {
            action: 'tradepress_duplicate_strategy',
            nonce: '<?php echo wp_create_nonce('tradepress_strategy_nonce'); ?>',
            strategy_id: strategyId
        })
        .done(function(response) {
            if (response.success) {
                alert('Strategy duplicated successfully!');
                location.reload(); // Refresh to show new strategy
            } else {
                alert('Error: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            $button.prop('disabled', false).text('Duplicate');
        });
    });
    
    // Activate/Deactivate strategy
    $('.activate-strategy, .deactivate-strategy').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        const action = $(this).hasClass('activate-strategy') ? 'activate' : 'deactivate';
        
        if (confirm('Are you sure you want to ' + action + ' this strategy?')) {
            // TODO: Implement activate/deactivate functionality
            alert(action.charAt(0).toUpperCase() + action.slice(1) + ' functionality will be implemented in next phase. Strategy ID: ' + strategyId);
        }
    });
    
    // Delete strategy
    $('.delete-strategy').on('click', function() {
        const strategyId = $(this).data('strategy-id');
        const $button = $(this);
        
        if (confirm('Are you sure you want to delete this strategy? This action cannot be undone.')) {
            $button.prop('disabled', true).text('Deleting...');
            
            $.post(ajaxurl, {
                action: 'tradepress_delete_strategy',
                nonce: '<?php echo wp_create_nonce('tradepress_strategy_nonce'); ?>',
                strategy_id: strategyId
            })
            .done(function(response) {
                if (response.success) {
                    $button.closest('.strategy-card').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error: ' + response.data);
                    $button.prop('disabled', false).text('Delete');
                }
            })
            .fail(function() {
                alert('Network error occurred');
                $button.prop('disabled', false).text('Delete');
            });
        }
    });
});
</script>