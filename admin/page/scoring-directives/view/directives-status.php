<?php
/**
 * TradePress - Scoring Directives Development Status Tab
 * 
 * DIRECTIVE DEVELOPMENT MONITORING
 * =============================
 * This view provides technical monitoring and management of individual scoring directives.
 * Unlike the main Directives Manager which handles global configuration, this tab focuses
 * on the technical aspects of directive management:
 * 
 * - Monitor directive versions and update status
 * - Track development readiness (some directives may be in development)
 * - View technical implementation details and dependencies
 * - Preview upcoming directives that are planned but not yet available
 * - Favorite frequently-used directives for quick access
 * - Hide directives that are not currently needed (if not used in active strategies)
 * 
 * This tab is primarily for:
 * - Developers monitoring directive implementation progress
 * - Users who want to see what's coming in future updates
 * - Technical users who need detailed directive status information
 * - Managing the visibility of directives in other interfaces
 * - Enhancing support for backwards compatibility with existing strategies
 * 
 * Future Feature: Individual directive testing capabilities will be added to this tab
 * to allow testing directive logic against specific symbols and market conditions.
 *
 * @package TradePress/Admin/ScoringDirectives
 * @version 1.0.0
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure the directives loader is available
if (!function_exists('tradepress_get_all_directives')) {
    $loader_path = TRADEPRESS_PLUGIN_DIR_PATH . 'includes/scoring-system/directives-loader.php';
    if (file_exists($loader_path)) {
        require_once $loader_path;
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__('Directives loader not found.', 'tradepress') . '</p></div>';
        return;
    }
}

// Get all directives using the loader function
$all_directives = tradepress_get_all_directives();

// Transform the directive data to include status information
$all_directives_with_status = array();
foreach ($all_directives as $directive_id => $directive) {
    // Add status-related fields that the view expects
    $directive['id'] = $directive_id;
    $directive['version'] = isset($directive['version']) ? $directive['version'] : '1.0.0';
    $directive['author'] = isset($directive['author']) ? $directive['author'] : 'TradePress';
    $directive['last_updated'] = isset($directive['last_updated']) ? $directive['last_updated'] : date('Y-m-d');
    $directive['is_favorite'] = false; // Default to not favorite
    $directive['is_hidden'] = false; // Default to not hidden
    $directive['can_hide'] = !isset($directive['used_in_strategies']) || !$directive['used_in_strategies']; // Can hide if not used in strategies
    
    // Determine status based on existing fields
    if (isset($directive['ready']) && !$directive['ready']) {
        $directive['status'] = 'development';
    } elseif (isset($directive['active']) && $directive['active']) {
        $directive['status'] = 'active';
    } else {
        $directive['status'] = 'inactive';
    }
    
    $all_directives_with_status[$directive_id] = $directive;
}

// Group directives by category
$grouped_directives = [];
foreach ($all_directives_with_status as $directive_id => $directive) {
    $category = !empty($directive['category']) ? $directive['category'] : __('Uncategorized', 'tradepress');
    $grouped_directives[$category][$directive_id] = $directive;
}
ksort($grouped_directives);
?>
<div class="tradepress-directives-status-tab">
    <div class="directives-toolbar">
        <input type="search" id="directive-search" placeholder="<?php esc_attr_e('Search directives...', 'tradepress'); ?>" class="regular-text">
        <select id="directive-filter-status">
            <option value="all"><?php esc_html_e('All Statuses', 'tradepress'); ?></option>
            <option value="active"><?php esc_html_e('Active', 'tradepress'); ?></option>
            <option value="inactive"><?php esc_html_e('Inactive', 'tradepress'); ?></option>
            <option value="development"><?php esc_html_e('In Development', 'tradepress'); ?></option>
        </select>
        <button class="button" id="toggle-all-descriptions"><?php esc_html_e('Toggle Descriptions', 'tradepress'); ?></button>
    </div>

    <?php if (empty($all_directives_with_status)) : ?>
        <div class="notice notice-info inline">
            <p><?php esc_html_e('No scoring directives are currently defined.', 'tradepress'); ?></p>
        </div>
    <?php else : ?>
        <?php foreach ($grouped_directives as $category_name => $directives_in_category) : ?>
            <div class="directive-category-group">
                <h3 class="directive-category-title"><?php echo esc_html($category_name); ?> (<?php echo count($directives_in_category); ?>)</h3>
                <table class="wp-list-table widefat fixed striped tradepress-directives-status-table">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
                            <th scope="col" class="manage-column column-name"><?php esc_html_e('Directive Name', 'tradepress'); ?></th>
                            <th scope="col" class="manage-column column-version"><?php esc_html_e('Version', 'tradepress'); ?></th>
                            <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'tradepress'); ?></th>
                            <th scope="col" class="manage-column column-last-updated"><?php esc_html_e('Last Updated', 'tradepress'); ?></th>
                            <th scope="col" class="manage-column column-actions"><?php esc_html_e('Actions', 'tradepress'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="the-list-<?php echo esc_attr(sanitize_title($category_name)); ?>">
                        <?php foreach ($directives_in_category as $directive_id => $directive) :
                            $status_class = '';
                            $status_text = '';
                            switch ($directive['status']) {
                                case 'active':
                                    $status_class = 'status-active';
                                    $status_text = __('Active', 'tradepress');
                                    break;
                                case 'inactive':
                                    $status_class = 'status-inactive';
                                    $status_text = __('Inactive', 'tradepress');
                                    break;
                                case 'development':
                                    $status_class = 'status-development';
                                    $status_text = __('In Development', 'tradepress');
                                    break;
                                default:
                                    $status_text = esc_html(ucfirst($directive['status']));
                            }
                        ?>
                            <tr id="directive-<?php echo esc_attr($directive_id); ?>" class="directive-row <?php echo $directive['is_hidden'] ? 'is-hidden' : ''; ?>" data-name="<?php echo esc_attr(strtolower($directive['name'])); ?>" data-status="<?php echo esc_attr($directive['status']); ?>">
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="directive_bulk[]" value="<?php echo esc_attr($directive_id); ?>" />
                                </th>
                                <td class="column-name">
                                    <strong><?php echo esc_html($directive['name']); ?></strong>
                                    <div class="row-actions">
                                        <span class="edit"><a href="#"><?php esc_html_e('Configure', 'tradepress'); ?></a> | </span>
                                        <span class="view"><a href="#" class="toggle-description"><?php esc_html_e('View Details', 'tradepress'); ?></a></span>
                                    </div>
                                    <p class="directive-description" style="display:none;"><?php echo esc_html($directive['description']); ?><br/>
                                    <em><?php printf(esc_html__('Author: %s', 'tradepress'), esc_html($directive['author'])); ?></em>
                                    </p>
                                </td>
                                <td class="column-version"><?php echo esc_html($directive['version']); ?></td>
                                <td class="column-status">
                                    <span class="directive-status-badge <?php echo esc_attr($status_class); ?>">
                                        <?php echo esc_html($status_text); ?>
                                    </span>
                                </td>
                                <td class="column-last-updated">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($directive['last_updated']))); ?>
                                </td>
                                <td class="column-actions">
                                    <button class="button-link favorite-directive <?php echo $directive['is_favorite'] ? 'is-favorite' : ''; ?>" data-directive-id="<?php echo esc_attr($directive_id); ?>" title="<?php echo $directive['is_favorite'] ? esc_attr__('Remove from favorites', 'tradepress') : esc_attr__('Add to favorites', 'tradepress'); ?>">
                                        <span class="dashicons <?php echo $directive['is_favorite'] ? 'dashicons-star-filled' : 'dashicons-star-empty'; ?>"></span>
                                    </button>
                                    <?php if ($directive['can_hide']) : ?>
                                        <button class="button-link hide-directive <?php echo $directive['is_hidden'] ? 'is-hidden' : ''; ?>" data-directive-id="<?php echo esc_attr($directive_id); ?>" title="<?php echo $directive['is_hidden'] ? esc_attr__('Show directive', 'tradepress') : esc_attr__('Hide directive', 'tradepress'); ?>">
                                            <span class="dashicons <?php echo $directive['is_hidden'] ? 'dashicons-hidden' : 'dashicons-visibility'; ?>"></span>
                                        </button>
                                    <?php else : ?>
                                        <button class="button-link" disabled="disabled" title="<?php esc_attr_e('This directive cannot be hidden as it is used in an active strategy.', 'tradepress'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div> <!-- .directive-category-group -->
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Styles moved to assets/css/pages/directives-status.css -->
