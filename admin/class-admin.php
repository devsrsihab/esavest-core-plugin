<?php
if (!defined('ABSPATH')) {
    exit;
}

class ESAVEST_Core_Admin {

     public function __construct() {
        add_action('admin_head', [$this, 'add_request_menu_badge']);

        // Auto mark request as viewed on edit
        add_action('load-post.php', [$this, 'mark_request_as_viewed']);
    }

    /**
     * Init admin hooks
     */
    public function init() {

        // Register global admin assets (once)
        add_action('admin_enqueue_scripts', [$this, 'register_global_assets'], 5);

        // Enqueue context based assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets'], 20);

        // Admin menu
        add_action('admin_menu', [$this, 'add_dashboard_page']);
    }

    /**
     * Register global admin assets (DO NOT enqueue here)
     * DRY principle
     */
    public function register_global_assets() {

        // Bootstrap (shared)
        wp_register_style(
            'esavest-bootstrap',
            ESAVEST_CORE_URL . 'assets/css/bootstrap.min.css',
            [],
            '5.0.2'
        );

        wp_register_script(
            'esavest-bootstrap',
            ESAVEST_CORE_URL . 'assets/js/bootstrap.bundle.min.js',
            [],
            '5.0.2',
            true
        );

        // Global plugin styles
        wp_register_style(
            'esavest-core-global-admin',
            ESAVEST_CORE_URL . 'assets/css/esavest-global.css',
            [],
            ESAVEST_CORE_VERSION
        );
    }


    /**
     * Add ESAVEST dashboard menu
     */
    public function add_dashboard_page() {

        add_menu_page(
            'ESAVEST Dashboard',
            'ESAVEST',
            'manage_options',
            'esavest-core',
            [$this, 'render_dashboard'],
            'dashicons-admin-generic',
            56
        );
    }

    /**
     * Render dashboard view
     */
    public function render_dashboard() {

        $template = ESAVEST_CORE_PATH . 'admin/views/dashboard.php';

        if (file_exists($template)) {
            require $template;
        } else {
            echo '<div class="wrap"><h1>Dashboard template not found</h1></div>';
        }
    }

    /**
     * Enqueue admin assets based on context
     */
    public function enqueue_admin_assets($hook) {

        global $post_type;

        /**
         * 1️⃣ ESAVEST Dashboard page
         */
        if ($hook === 'toplevel_page_esavest-core') {

            wp_enqueue_style('esavest-bootstrap');
            wp_enqueue_script('esavest-bootstrap');

            wp_enqueue_style(
                'esavest-core-admin',
                ESAVEST_CORE_URL . 'assets/css/admin.css',
                ['esavest-bootstrap'],
                ESAVEST_CORE_VERSION
            );
        }

        /**
         * 2️⃣ Material Add/Edit page
         */
        if (

            $post_type === 'esavest_material'
        ) {

            wp_enqueue_style('esavest-core-global-admin');
            wp_enqueue_style(
                'esavest-material-admin',
                ESAVEST_CORE_URL . 'assets/css/admin-material.css',
                [],
                ESAVEST_CORE_VERSION
            );
        }

        /**
         * 🔮 Future:
         * Requests / Offers admin assets can be added here
         */
        if ($post_type === 'esavest_request') {
            // Enqueue request admin assets here
           wp_enqueue_style('esavest-core-global-admin');

        }
        /**
         * 🔮 Future:
         * Requests / Offers admin assets can be added here
         */
        if ($post_type === 'esavest_offer') {
            // Enqueue request admin assets here
           wp_enqueue_style('esavest-core-global-admin');

        }

    }

/**
     * Add unread badge to Requests menu
     */
    public function add_request_menu_badge() {

        global $menu;

        if (!function_exists('esavest_core_get_unread_request_count')) {
            return;
        }

        $unread_count = esavest_core_get_unread_request_count();
        if (!$unread_count) return;

        foreach ($menu as $key => $item) {
            if (isset($item[2]) && $item[2] === 'edit.php?post_type=esavest_request') {

                $menu[$key][0] .= sprintf(
                    ' <span class="update-plugins count-%d"><span class="plugin-count">%d</span></span>',
                    $unread_count,
                    $unread_count
                );
                break;
            }
        }
    }

    /**
     * Mark request as viewed when admin opens edit page
     */
    public function mark_request_as_viewed() {

        if (!isset($_GET['post'], $_GET['action'])) return;
        if ($_GET['action'] !== 'edit') return;

        $post_id = (int) $_GET['post'];
        if (!$post_id) return;

        if (get_post_type($post_id) !== 'esavest_request') return;

        update_post_meta($post_id, '_esavest_request_viewed', 'yes');
    }



}
