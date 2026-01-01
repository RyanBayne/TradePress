<?php
/**
 * TradePress Taxonomy Helper Functions
 *
 * Functions for working with taxonomies and terms
 *
 * @package TradePress/Functions
 * @version 1.0.0
 * @created 2024-04-26 21:45:00
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Generate and assign symbol categories from company data
 *
 * @param int $post_id The symbol post ID
 * @param array $company_data Company data array
 */
function tradepress_generate_symbol_taxonomy_terms($post_id, $company_data) {
    if (empty($post_id) || empty($company_data)) {
        return;
    }
    
    // Create sector category if it doesn't exist
    if (!empty($company_data['sector'])) {
        $sector_term = term_exists($company_data['sector'], 'symbol_category');
        
        if (!$sector_term) {
            $sector_term = wp_insert_term(
                $company_data['sector'],
                'symbol_category',
                array(
                    'description' => sprintf(__('Companies in the %s sector', 'tradepress'), $company_data['sector']),
                    'slug' => sanitize_title($company_data['sector'])
                )
            );
        }
        
        // Create industry as child of sector if it doesn't exist
        if (!empty($company_data['industry']) && !is_wp_error($sector_term)) {
            $sector_id = is_array($sector_term) ? $sector_term['term_id'] : $sector_term;
            
            $industry_term = term_exists($company_data['industry'], 'symbol_category');
            
            if (!$industry_term) {
                $industry_term = wp_insert_term(
                    $company_data['industry'],
                    'symbol_category',
                    array(
                        'description' => sprintf(__('Companies in the %s industry', 'tradepress'), $company_data['industry']),
                        'slug' => sanitize_title($company_data['industry']),
                        'parent' => $sector_id
                    )
                );
            } elseif (is_array($industry_term)) {
                // Make sure industry is a child of sector
                wp_update_term(
                    $industry_term['term_id'], 
                    'symbol_category',
                    array('parent' => $sector_id)
                );
            }
            
            // Assign industry category to post
            if (!is_wp_error($industry_term)) {
                $industry_id = is_array($industry_term) ? $industry_term['term_id'] : $industry_term;
                wp_set_object_terms($post_id, $industry_id, 'symbol_category', true);
            }
        } else {
            // If no industry, assign sector directly
            if (!is_wp_error($sector_term)) {
                $sector_id = is_array($sector_term) ? $sector_term['term_id'] : $sector_term;
                wp_set_object_terms($post_id, $sector_id, 'symbol_category', true);
            }
        }
    }
    
    // Create country term if it doesn't exist
    if (!empty($company_data['country'])) {
        $country_term = term_exists($company_data['country'], 'symbol_category');
        
        if (!$country_term) {
            $country_parent = term_exists('Countries', 'symbol_category');
            
            if (!$country_parent) {
                $country_parent = wp_insert_term(
                    'Countries',
                    'symbol_category',
                    array(
                        'description' => __('Companies grouped by country', 'tradepress'),
                        'slug' => 'countries'
                    )
                );
            }
            
            $parent_id = is_array($country_parent) ? $country_parent['term_id'] : $country_parent;
            
            $country_term = wp_insert_term(
                $company_data['country'],
                'symbol_category',
                array(
                    'description' => sprintf(__('Companies based in %s', 'tradepress'), $company_data['country']),
                    'slug' => sanitize_title($company_data['country']),
                    'parent' => $parent_id
                )
            );
        }
        
        // Assign country to post
        if (!is_wp_error($country_term)) {
            $country_id = is_array($country_term) ? $country_term['term_id'] : $country_term;
            wp_set_object_terms($post_id, $country_id, 'symbol_category', true);
        }
    }
    
    // Create market cap category
    if (!empty($company_data['market_cap_category'])) {
        $cap_term = term_exists($company_data['market_cap_category'], 'symbol_category');
        
        if (!$cap_term) {
            $cap_parent = term_exists('Market Capitalization', 'symbol_category');
            
            if (!$cap_parent) {
                $cap_parent = wp_insert_term(
                    'Market Capitalization',
                    'symbol_category',
                    array(
                        'description' => __('Companies grouped by market capitalization', 'tradepress'),
                        'slug' => 'market-capitalization'
                    )
                );
            }
            
            $parent_id = is_array($cap_parent) ? $cap_parent['term_id'] : $cap_parent;
            
            $cap_term = wp_insert_term(
                $company_data['market_cap_category'],
                'symbol_category',
                array(
                    'description' => sprintf(__('Companies with %s market capitalization', 'tradepress'), $company_data['market_cap_category']),
                    'slug' => sanitize_title($company_data['market_cap_category']),
                    'parent' => $parent_id
                )
            );
        }
        
        // Assign market cap category to post
        if (!is_wp_error($cap_term)) {
            $cap_id = is_array($cap_term) ? $cap_term['term_id'] : $cap_term;
            wp_set_object_terms($post_id, $cap_id, 'symbol_category', true);
        }
    }
    
    // Create exchange term
    if (!empty($company_data['exchange'])) {
        $exchange_term = term_exists($company_data['exchange'], 'symbol_category');
        
        if (!$exchange_term) {
            $exchange_parent = term_exists('Exchanges', 'symbol_category');
            
            if (!$exchange_parent) {
                $exchange_parent = wp_insert_term(
                    'Exchanges',
                    'symbol_category',
                    array(
                        'description' => __('Companies grouped by stock exchange', 'tradepress'),
                        'slug' => 'exchanges'
                    )
                );
            }
            
            $parent_id = is_array($exchange_parent) ? $exchange_parent['term_id'] : $exchange_parent;
            
            $exchange_term = wp_insert_term(
                $company_data['exchange'],
                'symbol_category',
                array(
                    'description' => sprintf(__('Companies listed on %s', 'tradepress'), $company_data['exchange']),
                    'slug' => sanitize_title($company_data['exchange']),
                    'parent' => $parent_id
                )
            );
        }
        
        // Assign exchange to post
        if (!is_wp_error($exchange_term)) {
            $exchange_id = is_array($exchange_term) ? $exchange_term['term_id'] : $exchange_term;
            wp_set_object_terms($post_id, $exchange_id, 'symbol_category', true);
        }
    }
    
    // Add tags based on products or other attributes
    if (!empty($company_data['products']) && is_array($company_data['products'])) {
        $tags = array();
        foreach ($company_data['products'] as $product) {
            $tags[] = $product;
        }
        
        if (!empty($tags)) {
            wp_set_object_terms($post_id, $tags, 'symbol_tag', true);
        }
    }
    
    // Add competitor relationships as tags
    if (!empty($company_data['primary_competitors']) && is_array($company_data['primary_competitors'])) {
        $competitor_tag = sprintf(__('Competes with %s', 'tradepress'), implode(', ', $company_data['primary_competitors']));
        wp_set_object_terms($post_id, $competitor_tag, 'symbol_tag', true);
    }
}
