<div class="wrap tradepress-research">
    <h1><?php echo get_admin_page_title(); ?></h1>
    <p class="description">
        Research and analysis tools to visualize data used in the TradePress algorithm.
    </p>

    <h2 class="nav-tab-wrapper">
        <?php
        $tabs = $this->get_tabs();
        foreach ($tabs as $tab_id => $tab) {
            $tab_title = isset($tab['title']) ? $tab['title'] : $tab;
            $active_class = ($this->active_tab === $tab_id) ? 'nav-tab-active' : '';
            echo '<a href="?page=tradepress_research&tab=' . esc_attr($tab_id) . '" class="nav-tab ' . esc_attr($active_class) . '">' . esc_html($tab_title) . '</a>';
        }
        ?>
    </h2>

    <div class="tab-content">
        <?php
        // Get the active tab
        $active_tab = $this->active_tab;
        
        // Check if the tab exists and has a callback
        if (isset($tabs[$active_tab]) && isset($tabs[$active_tab]['callback']) && is_callable($tabs[$active_tab]['callback'])) {
            call_user_func($tabs[$active_tab]['callback']);
        } else {
            // Default to first tab if active tab doesn't exist
            $tab_ids = array_keys($tabs);
            if (!empty($tab_ids) && isset($tabs[$tab_ids[0]]['callback']) && is_callable($tabs[$tab_ids[0]]['callback'])) {
                call_user_func($tabs[$tab_ids[0]]['callback']);
            }
        }
        ?>
    </div>
</div>
