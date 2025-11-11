<?php
// File: DH_CPT_Manager.php
namespace DHead_WP;

class DH_CPT_Manager
{
    private static array $cpts = [];

    /**
     * Collects the created CPT Builder object.
     * * @param DH_CPT_Builder $cpt The CPT Builder instance to collect.
     */
    public static function add(DH_CPT_Builder $cpt): void
    {
        self::$cpts[] = $cpt;
    }

    /**
     * Registers all collected Custom Post Types.
     */
    public static function register_all(): void
    {
        if (empty(self::$cpts)) {
            return;
        }

        // Hook into 'init'. This is the main hook used for post type and taxonomy registration.
        add_action('init', [self::class, 'handle_registration']);
        // Meta fields registration is called from within the Builder's init_registration() method.
    }

    /**
     * Iterates through all collected CPTs and triggers their registration logic.
     */
    public static function handle_registration(): void
    {
        // Loop through all CPTs and call their registration function
        foreach (self::$cpts as $cpt_builder) {
            $cpt_builder->init_registration();
        }
    }
}
