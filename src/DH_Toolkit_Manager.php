<?php
// File: DH_Toolkit_Manager.php
namespace DHead_WP;

class DH_Toolkit_Manager
{
    /**
     * The single Facade method to register all structural components.
     */
    public static function register_all(): void
    {
        // Call the specialized Managers
        DH_CPT_Manager::register_all();
        DH_Options_Page_Manager::register_all();
    }
}
