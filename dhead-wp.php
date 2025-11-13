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
