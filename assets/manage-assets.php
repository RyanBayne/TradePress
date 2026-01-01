<?php
/**
 * TradePress Asset Management
 * 
 * Central library for managing asset paths and metadata.
 * Provides organized access to all CSS and JS files with their purposes.
 * 
 * @package TradePress/Assets
 * @since 1.0.0
 * @created 2024-12-19 16:30:00
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TradePress Asset Manager
 * 
 * Manages all plugin assets with metadata and organized access
 */
class TradePress_Asset_Manager {
    
    /**
     * Plugin assets directory
     */
    private $assets_dir;
    
    /**
     * Plugin assets URL
     */
    public $assets_url;
    
    /**
     * Asset registry with metadata
     */
    private $assets = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->assets_dir = plugin_dir_path(__FILE__);
        $this->assets_url = plugin_dir_url(__FILE__);
        $this->init_assets();
    }
    
    /**
     * Initialize asset registry with metadata
     */
    private function init_assets() {
        $this->assets = array(
            'css' => require_once dirname(__FILE__) . '/style-assets.php',
            'js' => require_once dirname(__FILE__) . '/script-assets.php'
        );
    }
    
    /**
     * Get asset path
     * 
     * @param string $type Asset type (css|js)
     * @param string $name Asset name
     * @return string|false Asset path or false if not found
     */
    public function tp_include($type, $name) {
        if (!isset($this->assets[$type])) {
            return false;
        }
        
        // Search in all categories for the asset
        foreach ($this->assets[$type] as $category => $assets) {
            if (isset($assets[$name])) {
                return $this->assets_dir . $assets[$name]['path'];
            }
        }
        
        return false;
    }
    
    /**
     * Get asset URL
     * 
     * @param string $type Asset type (css|js)
     * @param string $name Asset name
     * @return string|false Asset URL or false if not found
     */
    public function get_asset_url($type, $name) {
        if (!isset($this->assets[$type])) {
            return false;
        }
        
        // Search in all categories for the asset
        foreach ($this->assets[$type] as $category => $assets) {
            if (isset($assets[$name])) {
                return $this->assets_url . $assets[$name]['path'];
            }
        }
        
        return false;
    }
    
    /**
     * Get all assets by type
     * 
     * @param string $type Asset type (css|js)
     * @return array Array of all assets of specified type
     */
    public function get_all_assets($type = null) {
        if ($type && isset($this->assets[$type])) {
            $all_assets = array();
            foreach ($this->assets[$type] as $category => $assets) {
                $all_assets = array_merge($all_assets, $assets);
            }
            return $all_assets;
        }
        
        return $this->assets;
    }
    
    /**
     * Get assets by page
     * 
     * @param string $page Page identifier
     * @param string $type Asset type (css|js) or null for all
     * @return array Assets used by the specified page
     */
    public function get_assets_by_page($page, $type = null) {
        $page_assets = array();
        $types_to_check = $type ? array($type) : array('css', 'js');
        
        foreach ($types_to_check as $asset_type) {
            if (!isset($this->assets[$asset_type])) continue;
            
            // Handle case where assets might be a flat array instead of categorized
            if (is_array($this->assets[$asset_type])) {
                // Check if this is a categorized structure (has sub-arrays)
                $first_key = array_key_first($this->assets[$asset_type]);
                if ($first_key && is_array($this->assets[$asset_type][$first_key])) {
                    // Categorized structure
                    foreach ($this->assets[$asset_type] as $category => $assets) {
                        if (!is_array($assets)) continue;
                        
                        foreach ($assets as $name => $asset) {
                            if (!is_array($asset) || !isset($asset['pages'])) continue;
                            
                            if (in_array('all', $asset['pages']) || in_array($page, $asset['pages'])) {
                                $page_assets[$asset_type][$name] = $asset;
                            }
                        }
                    }
                } else {
                    // Flat structure
                    foreach ($this->assets[$asset_type] as $name => $asset) {
                        if (!is_array($asset) || !isset($asset['pages'])) continue;
                        
                        if (in_array('all', $asset['pages']) || in_array($page, $asset['pages'])) {
                            $page_assets[$asset_type][$name] = $asset;
                        }
                    }
                }
            }
        }
        
        return $page_assets;
    }
    
    /**
     * Get asset dependencies
     * 
     * @param string $type Asset type (css|js)
     * @param string $name Asset name
     * @return array Asset dependencies
     */
    public function get_dependencies($type, $name) {
        if (!isset($this->assets[$type])) {
            return array();
        }
        
        foreach ($this->assets[$type] as $category => $assets) {
            if (isset($assets[$name])) {
                return $assets[$name]['dependencies'];
            }
        }
        
        return array();
    }
    
    /**
     * Get asset metadata
     * 
     * @param string $type Asset type (css|js)
     * @param string $name Asset name
     * @return array|false Asset metadata or false if not found
     */
    public function get_asset_metadata($type, $name) {
        if (!isset($this->assets[$type])) {
            return false;
        }
        
        foreach ($this->assets[$type] as $category => $assets) {
            if (isset($assets[$name])) {
                return $assets[$name];
            }
        }
        
        return false;
    }
    
    /**
     * Check if asset exists
     * 
     * @param string $type Asset type (css|js)
     * @param string $name Asset name
     * @return bool True if asset exists
     */
    public function asset_exists($type, $name) {
        if (!isset($this->assets[$type])) {
            return false;
        }
        
        foreach ($this->assets[$type] as $category => $assets) {
            if (isset($assets[$name])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get assets by category
     * 
     * @param string $type Asset type (css|js)
     * @param string $category Category name
     * @return array Assets in the specified category
     */
    public function get_assets_by_category($type, $category) {
        if (isset($this->assets[$type][$category])) {
            return $this->assets[$type][$category];
        }
        
        return array();
    }
    
    /**
     * Search assets by purpose
     * 
     * @param string $search Search term
     * @param string $type Asset type (css|js) or null for all
     * @return array Matching assets
     */
    public function search_assets($search, $type = null) {
        $results = array();
        $types_to_search = $type ? array($type) : array('css', 'js');
        
        foreach ($types_to_search as $asset_type) {
            if (!isset($this->assets[$asset_type])) continue;
            
            foreach ($this->assets[$asset_type] as $category => $assets) {
                foreach ($assets as $name => $asset) {
                    if (stripos($asset['purpose'], $search) !== false || 
                        stripos($name, $search) !== false) {
                        $results[$asset_type][$name] = $asset;
                    }
                }
            }
        }
        
        return $results;
    }
}

// Initialize the asset manager
global $tradepress_assets;
if (!isset($tradepress_assets) || !is_object($tradepress_assets)) {
    $tradepress_assets = new TradePress_Asset_Manager();
}

/**
 * Helper function to get asset path
 * 
 * @param string $type Asset type (css|js)
 * @param string $name Asset name
 * @return string|false Asset path or false if not found
 */
function tradepress_get_asset($type, $name) {
    global $tradepress_assets;
    return $tradepress_assets->tp_include($type, $name);
}

/**
 * Helper function to get asset URL
 * 
 * @param string $type Asset type (css|js)
 * @param string $name Asset name
 * @return string|false Asset URL or false if not found
 */
function tradepress_get_asset_url($type, $name) {
    global $tradepress_assets;
    return $tradepress_assets->get_asset_url($type, $name);
}