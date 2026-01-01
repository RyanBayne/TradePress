<?php
/**
 * TradePress UI Library Main Container
 *
 * @package TradePress/Admin/Views/Partials
 * @version 1.0.10
 */

defined('ABSPATH') || exit;

// Define all available sections
$ui_sections = array(
    'color-palette' => __('Color Palette', 'tradepress'),
    'button-components' => __('Button Components', 'tradepress'),
    'form-components' => __('Form Components', 'tradepress'),
    'notice-components' => __('Notice Components', 'tradepress'),
    'controls-actions' => __('Controls & Actions', 'tradepress'),
    'filters-search' => __('Filters & Search', 'tradepress'),
    'pagination-controls' => __('Pagination Controls', 'tradepress'),
    'progress-indicators' => __('Progress Indicators', 'tradepress'),
    'animation-showcase' => __('Animation Showcase', 'tradepress'),
    'working-notes' => __('Working Notes', 'tradepress'),
    'accordion-components' => __('Accordion Components', 'tradepress'),
    'status-indicators' => __('Status Indicators', 'tradepress'),
    'data-analysis-components' => __('Data Analysis Components', 'tradepress'),
    'chart-visualization' => __('Chart Visualization', 'tradepress'),
    'modal-components' => __('Modal Components', 'tradepress'),
    'tooltips' => __('Tooltips', 'tradepress'),
    'pointers' => __('Pointers', 'tradepress')
);
?>

<div class="wrap tradepress-ui-library">
    <h1><?php esc_html_e('TradePress UI Library', 'tradepress'); ?></h1>
    <p class="description"><?php esc_html_e('Comprehensive showcase of TradePress UI components, styles, and interactive elements.', 'tradepress'); ?></p>
    
    <!-- Section Visibility Controls -->
    <div class="tradepress-ui-section-controls">
        <div class="tradepress-card">
            <div class="tradepress-card-header">
                <h3><?php esc_html_e('Section Visibility Controls', 'tradepress'); ?></h3>
                <div class="control-actions">
                    <button type="button" class="button button-secondary" id="show-all-sections">
                        <?php esc_html_e('Show All', 'tradepress'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="hide-all-sections">
                        <?php esc_html_e('Hide All', 'tradepress'); ?>
                    </button>
                </div>
            </div>
            <div class="tradepress-card-body">
                <p class="description">
                    <?php esc_html_e('Use these controls to show/hide specific sections while working on styles. This helps focus on one component at a time.', 'tradepress'); ?>
                </p>
                <div class="section-toggles">
                    <?php foreach ($ui_sections as $section_id => $section_name) : ?>
                        <label class="section-toggle">
                            <input type="checkbox" 
                                   id="toggle-<?php echo esc_attr($section_id); ?>" 
                                   class="section-toggle-checkbox" 
                                   data-section="<?php echo esc_attr($section_id); ?>" 
                                   checked>
                            <span class="section-toggle-label"><?php echo esc_html($section_name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Include all UI Library sections with data attributes for visibility control
    $sections = array(
        'color-palette.php',
        'button-components.php',
        'form-components.php',
        'notice-components.php',
        'controls-actions.php',
        'filters-search.php',
        'pagination-controls.php',
        'progress-indicators.php',
        'animation-showcase.php',
        'working-notes.php',
        'accordion-components.php',
        'status-indicators.php',
        'data-analysis-components.php',
        'chart-visualization.php',
        'modal-components.php',
        'tooltips.php',
        'pointers.php'
    );
    
    $partials_dir = TRADEPRESS_PLUGIN_DIR_PATH . 'admin/page/development/partials/ui-library/';
    
    foreach ($sections as $section) {
        $section_id = str_replace('.php', '', $section);
        $section_path = $partials_dir . $section;
        
        echo '<div class="ui-library-section" data-section-id="' . esc_attr($section_id) . '" id="section-' . esc_attr($section_id) . '">';
        
        if (file_exists($section_path)) {
            require_once $section_path;
        } else {
            $section_name = str_replace(array('-', '.php'), array(' ', ''), $section);
            $section_name = ucwords($section_name);
            echo '<div class="tradepress-ui-section">';
            echo '<h3>' . esc_html($section_name) . '</h3>';
            echo '<p>' . sprintf(esc_html__('Section "%s" is not yet available.', 'tradepress'), esc_html($section_name)) . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    ?>
</div>
