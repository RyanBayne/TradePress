<?php
/**
 * TradePress - Create Watchlist Tab View
 *
 * @package TradePress/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tradepress-create-watchlist-container">
    <div class="create-watchlist-header">
        <h2><?php esc_html_e('Create New Watchlist', 'tradepress'); ?></h2>
        <p class="description">
            <?php esc_html_e('Create a new watchlist to group and track symbols together. You can add symbols from the browser on the right or search for specific symbols.', 'tradepress'); ?>
        </p>
    </div>

    <div class="create-watchlist-layout">
        <!-- Left column: Watchlist form -->
        <div class="create-watchlist-form-container">
            <form id="create-watchlist-form" method="post">
                <div class="form-section">
                    <h3><?php esc_html_e('Watchlist Details', 'tradepress'); ?></h3>
                    
                    <div class="form-field">
                        <label for="watchlist-name"><?php esc_html_e('Watchlist Name', 'tradepress'); ?> <span class="required">*</span></label>
                        <input type="text" id="watchlist-name" name="watchlist_name" required placeholder="<?php esc_attr_e('e.g., Tech Stocks, Growth Portfolio', 'tradepress'); ?>">
                        <p class="field-description"><?php esc_html_e('A descriptive name to identify your watchlist.', 'tradepress'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="watchlist-description"><?php esc_html_e('Description', 'tradepress'); ?></label>
                        <textarea id="watchlist-description" name="watchlist_description" rows="3" placeholder="<?php esc_attr_e('Optional description of this watchlist...', 'tradepress'); ?>"></textarea>
                        <p class="field-description"><?php esc_html_e('Add notes about this watchlist\'s purpose or strategy.', 'tradepress'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label for="watchlist-category"><?php esc_html_e('Category', 'tradepress'); ?></label>
                        <select id="watchlist-category" name="watchlist_category">
                            <option value=""><?php esc_html_e('-- Select Category --', 'tradepress'); ?></option>
                            <option value="tech"><?php esc_html_e('Technology', 'tradepress'); ?></option>
                            <option value="finance"><?php esc_html_e('Financial', 'tradepress'); ?></option>
                            <option value="healthcare"><?php esc_html_e('Healthcare', 'tradepress'); ?></option>
                            <option value="consumer"><?php esc_html_e('Consumer Goods', 'tradepress'); ?></option>
                            <option value="energy"><?php esc_html_e('Energy', 'tradepress'); ?></option>
                            <option value="other"><?php esc_html_e('Other', 'tradepress'); ?></option>
                        </select>
                        <p class="field-description"><?php esc_html_e('Categorize your watchlist for easier organization.', 'tradepress'); ?></p>
                    </div>
                    
                    <div class="form-field">
                        <label class="checkbox-label">
                            <input type="checkbox" id="activate-scoring" name="activate_scoring">
                            <?php esc_html_e('Activate scoring for this watchlist', 'tradepress'); ?>
                        </label>
                        <p class="field-description"><?php esc_html_e('Include this watchlist in the TradePress scoring algorithm.', 'tradepress'); ?></p>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><?php esc_html_e('Selected Symbols', 'tradepress'); ?></h3>
                    
                    <div class="form-field">
                        <div class="symbol-search-container">
                            <input type="text" id="symbol-search" placeholder="<?php esc_attr_e('Search for symbols...', 'tradepress'); ?>">
                            <button type="button" class="button" id="search-symbols-btn"><?php esc_html_e('Search', 'tradepress'); ?></button>
                        </div>
                    </div>
                    
                    <div id="symbol-search-results" class="search-results" style="display: none;">
                        <h4><?php esc_html_e('Search Results', 'tradepress'); ?></h4>
                        <div class="results-container">
                            <!-- Search results will be populated here via JavaScript -->
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label><?php esc_html_e('Your Selected Symbols', 'tradepress'); ?> <span id="selected-count">(0)</span></label>
                        <div id="selected-symbols" class="selected-symbols-container">
                            <p class="no-symbols-message"><?php esc_html_e('No symbols selected yet. Browse the symbol catalog on the right or use the search above to find and add symbols.', 'tradepress'); ?></p>
                        </div>
                        <input type="hidden" name="selected_symbols" id="selected-symbols-input" value="">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tradepress_watchlists')); ?>" class="button button-secondary"><?php esc_html_e('Cancel', 'tradepress'); ?></a>
                    <?php wp_nonce_field('tradepress_create_watchlist', 'tradepress_watchlist_nonce'); ?>
                    <button type="submit" class="button button-primary" id="create-watchlist-btn"><?php esc_html_e('Create Watchlist', 'tradepress'); ?></button>
                </div>
            </form>
        </div>
        
        <!-- Right column: Symbol browser -->
        <div class="symbol-browser-container">
            <div class="symbol-browser-header">
                <h3><?php esc_html_e('Symbol Browser', 'tradepress'); ?></h3>
                <div class="browser-filters">
                    <select id="sector-filter">
                        <option value=""><?php esc_html_e('All Sectors', 'tradepress'); ?></option>
                        <option value="technology"><?php esc_html_e('Technology', 'tradepress'); ?></option>
                        <option value="financial"><?php esc_html_e('Financial', 'tradepress'); ?></option>
                        <option value="healthcare"><?php esc_html_e('Healthcare', 'tradepress'); ?></option>
                        <option value="consumer"><?php esc_html_e('Consumer', 'tradepress'); ?></option>
                        <option value="energy"><?php esc_html_e('Energy', 'tradepress'); ?></option>
                    </select>
                    <input type="text" id="browser-search" placeholder="<?php esc_attr_e('Filter symbols...', 'tradepress'); ?>">
                </div>
            </div>
            
            <div class="symbol-categories">
                <!-- Technology Sector -->
                <div class="symbol-category" data-sector="technology">
                    <div class="category-header">
                        <h4><?php esc_html_e('Technology', 'tradepress'); ?></h4>
                        <span class="category-count"><?php esc_html_e('12 symbols', 'tradepress'); ?></span>
                    </div>
                    <div class="category-symbols">
                        <?php 
                        $tech_symbols = array(
                            array('symbol' => 'AAPL', 'name' => 'Apple Inc.', 'price' => 187.42, 'change' => 1.25, 'score' => 87),
                            array('symbol' => 'MSFT', 'name' => 'Microsoft Corp.', 'price' => 376.04, 'change' => 0.87, 'score' => 92),
                            array('symbol' => 'NVDA', 'name' => 'NVIDIA Corp.', 'price' => 952.26, 'change' => 3.14, 'score' => 95),
                            array('symbol' => 'GOOGL', 'name' => 'Alphabet Inc.', 'price' => 157.73, 'change' => -0.5, 'score' => 83),
                            array('symbol' => 'META', 'name' => 'Meta Platforms Inc.', 'price' => 485.58, 'change' => 1.2, 'score' => 78),
                            array('symbol' => 'ADBE', 'name' => 'Adobe Inc.', 'price' => 513.46, 'change' => 0.76, 'score' => 85),
                        );
                        
                        foreach ($tech_symbols as $symbol) :
                            $change_class = $symbol['change'] >= 0 ? 'positive' : 'negative';
                            $change_prefix = $symbol['change'] >= 0 ? '+' : '';
                            
                            // Score class
                            $score_class = '';
                            if ($symbol['score'] >= 85) {
                                $score_class = 'score-high';
                            } elseif ($symbol['score'] >= 70) {
                                $score_class = 'score-medium';
                            } else {
                                $score_class = 'score-low';
                            }
                        ?>
                            <div class="symbol-item" data-symbol="<?php echo esc_attr($symbol['symbol']); ?>" data-name="<?php echo esc_attr($symbol['name']); ?>">
                                <button type="button" class="add-to-watchlist" title="<?php esc_attr_e('Add to watchlist', 'tradepress'); ?>">
                                    <span class="dashicons dashicons-plus-alt"></span>
                                </button>
                                <div class="symbol-info">
                                    <div class="symbol-name"><?php echo esc_html($symbol['symbol']); ?></div>
                                    <div class="company-name"><?php echo esc_html($symbol['name']); ?></div>
                                </div>
                                <div class="symbol-score <?php echo esc_attr($score_class); ?>">
                                    <div class="score-label"><?php esc_html_e('Score', 'tradepress'); ?></div>
                                    <?php echo esc_html($symbol['score']); ?>
                                </div>
                                <div class="symbol-details">
                                    <div class="symbol-price"><?php echo esc_html(number_format($symbol['price'], 2)); ?></div>
                                    <div class="symbol-change <?php echo esc_attr($change_class); ?>">
                                        <?php echo esc_html($change_prefix . number_format($symbol['change'], 2) . '%'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Financial Sector -->
                <div class="symbol-category" data-sector="financial">
                    <div class="category-header">
                        <h4><?php esc_html_e('Financial', 'tradepress'); ?></h4>
                        <span class="category-count"><?php esc_html_e('8 symbols', 'tradepress'); ?></span>
                    </div>
                    <div class="category-symbols">
                        <?php 
                        $financial_symbols = array(
                            array('symbol' => 'JPM', 'name' => 'JPMorgan Chase & Co.', 'price' => 198.73, 'change' => 0.92, 'score' => 81),
                            array('symbol' => 'BAC', 'name' => 'Bank of America Corp.', 'price' => 37.12, 'change' => 0.45, 'score' => 76),
                            array('symbol' => 'WFC', 'name' => 'Wells Fargo & Co.', 'price' => 52.85, 'change' => -0.23, 'score' => 72),
                            array('symbol' => 'V', 'name' => 'Visa Inc.', 'price' => 278.45, 'change' => 1.13, 'score' => 89),
                        );
                        
                        foreach ($financial_symbols as $symbol) :
                            $change_class = $symbol['change'] >= 0 ? 'positive' : 'negative';
                            $change_prefix = $symbol['change'] >= 0 ? '+' : '';
                            
                            // Score class
                            $score_class = '';
                            if ($symbol['score'] >= 85) {
                                $score_class = 'score-high';
                            } elseif ($symbol['score'] >= 70) {
                                $score_class = 'score-medium';
                            } else {
                                $score_class = 'score-low';
                            }
                        ?>
                            <div class="symbol-item" data-symbol="<?php echo esc_attr($symbol['symbol']); ?>" data-name="<?php echo esc_attr($symbol['name']); ?>">
                                <button type="button" class="add-to-watchlist" title="<?php esc_attr_e('Add to watchlist', 'tradepress'); ?>">
                                    <span class="dashicons dashicons-plus-alt"></span>
                                </button>
                                <div class="symbol-info">
                                    <div class="symbol-name"><?php echo esc_html($symbol['symbol']); ?></div>
                                    <div class="company-name"><?php echo esc_html($symbol['name']); ?></div>
                                </div>
                                <div class="symbol-score <?php echo esc_attr($score_class); ?>">
                                    <div class="score-label"><?php esc_html_e('Score', 'tradepress'); ?></div>
                                    <?php echo esc_html($symbol['score']); ?>
                                </div>
                                <div class="symbol-details">
                                    <div class="symbol-price"><?php echo esc_html(number_format($symbol['price'], 2)); ?></div>
                                    <div class="symbol-change <?php echo esc_attr($change_class); ?>">
                                        <?php echo esc_html($change_prefix . number_format($symbol['change'], 2) . '%'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Healthcare Sector -->
                <div class="symbol-category" data-sector="healthcare">
                    <div class="category-header">
                        <h4><?php esc_html_e('Healthcare', 'tradepress'); ?></h4>
                        <span class="category-count"><?php esc_html_e('6 symbols', 'tradepress'); ?></span>
                    </div>
                    <div class="category-symbols">
                        <?php 
                        $healthcare_symbols = array(
                            array('symbol' => 'JNJ', 'name' => 'Johnson & Johnson', 'price' => 148.32, 'change' => -0.32, 'score' => 79),
                            array('symbol' => 'PFE', 'name' => 'Pfizer Inc.', 'price' => 26.75, 'change' => -0.87, 'score' => 71),
                            array('symbol' => 'UNH', 'name' => 'UnitedHealth Group Inc.', 'price' => 527.41, 'change' => 1.25, 'score' => 85),
                        );
                        
                        foreach ($healthcare_symbols as $symbol) :
                            $change_class = $symbol['change'] >= 0 ? 'positive' : 'negative';
                            $change_prefix = $symbol['change'] >= 0 ? '+' : '';
                            
                            // Score class
                            $score_class = '';
                            if ($symbol['score'] >= 85) {
                                $score_class = 'score-high';
                            } elseif ($symbol['score'] >= 70) {
                                $score_class = 'score-medium';
                            } else {
                                $score_class = 'score-low';
                            }
                        ?>
                            <div class="symbol-item" data-symbol="<?php echo esc_attr($symbol['symbol']); ?>" data-name="<?php echo esc_attr($symbol['name']); ?>">
                                <button type="button" class="add-to-watchlist" title="<?php esc_attr_e('Add to watchlist', 'tradepress'); ?>">
                                    <span class="dashicons dashicons-plus-alt"></span>
                                </button>
                                <div class="symbol-info">
                                    <div class="symbol-name"><?php echo esc_html($symbol['symbol']); ?></div>
                                    <div class="company-name"><?php echo esc_html($symbol['name']); ?></div>
                                </div>
                                <div class="symbol-score <?php echo esc_attr($score_class); ?>">
                                    <div class="score-label"><?php esc_html_e('Score', 'tradepress'); ?></div>
                                    <?php echo esc_html($symbol['score']); ?>
                                </div>
                                <div class="symbol-details">
                                    <div class="symbol-price"><?php echo esc_html(number_format($symbol['price'], 2)); ?></div>
                                    <div class="symbol-change <?php echo esc_attr($change_class); ?>">
                                        <?php echo esc_html($change_prefix . number_format($symbol['change'], 2) . '%'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript moved to assets/js/tradepress-watchlists.js -->
