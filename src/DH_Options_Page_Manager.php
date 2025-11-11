<?php
// File: DH_Options_Page_Manager.php
namespace DHead_WP;

class DH_Options_Page_Manager
{
    private static array $pages = [];

    /**
     * Collects the Options Page Builder instance.
     * * @param DH_Options_Page_Builder $page The Options Page Builder object.
     */
    public static function add(DH_Options_Page_Builder $page): void
    {
        self::$pages[] = $page;
    }

    /**
     * Registers all collected Options Pages.
     */
    public static function register_all(): void
    {
        if (empty(self::$pages)) {
            return;
        }

        // CREATE A SINGLE acf/init HOOK to register all pages
        add_action('acf/include_fields', function () {
            foreach (self::$pages as $page) {
                $page->init_registration();
            }
        });
    }
}
