<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Core {

    public function run() {

        // Load everything on plugins_loaded (SAFE)
        add_action('plugins_loaded', [$this, 'load']);
    }

    public function load() {

        // Helpers
        require_once ESAVEST_CORE_PATH . 'includes/helpers/functions-helpers.php';
        require_once ESAVEST_CORE_PATH . 'includes/helpers/functions-access.php';

        // CPTs
        require_once ESAVEST_CORE_PATH . 'includes/cpt/class-cpt-material.php';
        require_once ESAVEST_CORE_PATH . 'includes/cpt/class-cpt-request.php';
        require_once ESAVEST_CORE_PATH . 'includes/cpt/class-cpt-offer.php';

        // Init CPTs (they hook into init internally)
        ESAVEST_Core_CPT_Material::init();

        (new ESAVEST_Core_CPT_Request())->init();
        (new ESAVEST_Core_CPT_Offer())->init();

        // Admin
        require_once ESAVEST_CORE_PATH . 'admin/class-admin.php';
        (new ESAVEST_Core_Admin())->init();

        require_once ESAVEST_CORE_PATH . 'public/class-public.php';
        (new ESAVEST_Core_Public())->init();

        // Fluent Forms user auto-create integration
        require_once ESAVEST_CORE_PATH . 'includes/integrations/class-fluentforms-users.php';
        ESAVEST_FluentForms_Users::init();

        // User status management
        require_once ESAVEST_CORE_PATH . 'includes/admin/class-user-status.php';
        ESAVEST_User_Status::init();

        // Login guard
        require_once ESAVEST_CORE_PATH . 'includes/security/class-login-guard.php';
        ESAVEST_Login_Guard::init();

        // Access guard
        require_once ESAVEST_CORE_PATH . 'includes/security/class-access-guard.php';
        ESAVEST_Access_Guard::init();

 


    }

    /**
     * Runs on plugin activation
     */
    public static function activate() {

        // Load CPT once so rewrite works
        require_once ESAVEST_CORE_PATH . 'includes/cpt/class-cpt-material.php';
        ESAVEST_Core_CPT_Material::register_post_type();
        ESAVEST_Core_CPT_Material::register_taxonomies();

        // Customer role
        add_role(
            'esavest_customer',
            'Customer',
            [
                'read' => true,
            ]
        );

        // Partner role
        add_role(
            'esavest_partner',
            'Partner',
            [
                'read' => true,
            ]
        );

        flush_rewrite_rules();
    }
}
