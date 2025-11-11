<?php
// File: src/Block_Auto_Registrar.php
namespace DHead_WP;

class Block_Auto_Registrar
{
    /**
     * Static method for easy calling by the user.
     * Scans subdirectories to find 'block.php' files and requires them.
     *
     * @param string $relative_path Relative path from the theme directory (Example: 'resources/blocks').
     */
    public static function path(string $relative_path): void
    {
        // 1. Build the absolute path
        $full_path = get_template_directory() . '/' . trim($relative_path, '/');

        if (!is_dir($full_path)) {
            // Log an error if the directory does not exist
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("DHead_WP Block Registrar: Directory not found at $full_path");
            }
            return;
        }

        // 2. Scan all 'block.php' files within subdirectories
        // Glob syntax: /path/to/blocks/*/block.php
        $block_php_files = glob($full_path . '/*/block.php');

        if (empty($block_php_files)) {
            return;
        }

        // 3. Require each file. Each 'block.php' file must contain the DH_Block_Builder::make('name')... syntax.
        foreach ($block_php_files as $block_php_file) {
            require_once $block_php_file;
        }
    }
}
