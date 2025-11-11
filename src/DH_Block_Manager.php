<?php
// File: src/DH_Block_Manager.php
namespace DHead_WP;

class DH_Block_Manager
{
    private static array $blocks = [];

    /**
     * Collects the Block Builder instance.
     * * @param DH_Block_Builder $block The Block Builder object to collect.
     */
    public static function add(DH_Block_Builder $block): void
    {
        self::$blocks[] = $block;
    }

    /**
     * Registers all collected Blocks.
     * * This method must be called early (e.g., in dhead-wp.php) to ensure the
     * 'acf/init' hook registration is received by WordPress.
     */
    public static function register_all(): void
    {
        if (empty(self::$blocks)) {
            return;
        }

        // CREATE A SINGLE acf/init HOOK to register all blocks and their fields
        add_action('acf/init', function () {
            foreach (self::$blocks as $block) {
                // Call the init() method of each block
                $block->init();
            }
        });
    }
}
