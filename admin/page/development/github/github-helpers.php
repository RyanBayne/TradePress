<?php
/**
 * GitHub helper functions for TradePress
 * 
 * This file serves as a bridge to the main GitHub API functionality.
 * No functions are defined here to prevent duplication - all GitHub
 * functionality is centralized in the main API class file.
 * 
 * @package TradePress/Admin/development
 * @version 1.0.1
 * @date    2024-08-15
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the GitHub API functionality - this makes all GitHub functions available
require_once TRADEPRESS_PLUGIN_DIR_PATH . 'api/github/github-api.php';

// No function declarations in this file - all GitHub functions now come from the API class