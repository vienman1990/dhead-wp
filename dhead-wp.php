<?php

/**
 * Plugin Name: DHead WP
 * Description: A toolkit for quick setup of CPTs, Taxonomies, ACF Blocks, and Options Pages.
 * Version: 1.0.0
 * Author: Man
 * License: GPL-2.0-or-later
 */

// 1. Đảm bảo WordPress đã được load
if (! defined('ABSPATH')) {
    exit;
}

// 2. Load Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

if (! class_exists('ACF')) {
    // Define the path and URL to the Secure Custom Fields plugin.
    define('MY_SCF_PATH', __DIR__ . '/vendor/secure-custom-fields/');
    define('MY_SCF_URL', __DIR__ . '/vendor/secure-custom-fields/');
    // Include the plugin main file.
    require_once MY_SCF_PATH . 'secure-custom-fields.php';
}

// Hide the SCF admin menu item.
add_filter('acf/settings/show_admin', '__return_false');

// Hide the SCF Updates menu.
add_filter('acf/settings/show_updates', '__return_false', 100);
