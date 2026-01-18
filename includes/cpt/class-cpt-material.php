<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Core_CPT_Material {

    const POST_TYPE = 'esavest_material';
    const TAX_TYPE  = 'esavest_material_type';

    // Meta keys
    const META_UNIT   = '_esavest_material_unit';   // default/suggested unit
    const META_SKU    = '_esavest_material_sku';
    const META_STATUS = '_esavest_material_status';

    public static function init() {

        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_action('init', [__CLASS__, 'register_meta']);

        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [__CLASS__, 'save_meta'], 10, 2);

        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [__CLASS__, 'admin_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [__CLASS__, 'admin_column_values'], 10, 2);

        add_action('restrict_manage_posts', [__CLASS__, 'admin_status_filter_dropdown']);
        add_action('pre_get_posts', [__CLASS__, 'admin_filter_query']);
    }

    public static function register_post_type() {

        $caps = [
            'edit_post'          => 'manage_options',
            'read_post'          => 'manage_options',
            'delete_post'        => 'manage_options',
            'edit_posts'         => 'manage_options',
            'edit_others_posts'  => 'manage_options',
            'publish_posts'      => 'manage_options',
            'read_private_posts' => 'manage_options',
            'delete_posts'       => 'manage_options',
        ];

        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'Materials',
                'singular_name' => 'Material',
            ],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_icon'     => 'dashicons-hammer',
            'menu_position' => 21,
            'supports'      => ['title', 'thumbnail'],
            'rewrite'       => false,
            'show_in_rest'  => true,
            'capabilities'  => $caps,
            'map_meta_cap'  => false,
            'publicly_queryable' => true,
            // enable feature image if needed


        ]);
    }

    public static function register_taxonomies() {

        register_taxonomy(self::TAX_TYPE, [self::POST_TYPE], [
            'labels' => [
                'name'          => 'Material Types',
                'singular_name' => 'Material Type',
            ],
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'hierarchical'      => true,
            'rewrite'           => false,
            'show_in_rest'      => false,
        ]);
    }

    public static function register_meta() {

        register_post_meta(self::POST_TYPE, self::META_UNIT, [
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => fn() => current_user_can('manage_options'),
        ]);

        register_post_meta(self::POST_TYPE, self::META_SKU, [
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => fn() => current_user_can('manage_options'),
        ]);

        register_post_meta(self::POST_TYPE, self::META_STATUS, [
            'type'              => 'string',
            'single'            => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => fn() => current_user_can('manage_options'),
        ]);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'esavest_material_details',
            'Material Details',
            [__CLASS__, 'render_meta_box'],
            self::POST_TYPE,
            'normal',
            'default'
        );
    }

    public static function render_meta_box($post) {

        $data = [
            'unit'   => get_post_meta($post->ID, self::META_UNIT, true),
            'sku'    => get_post_meta($post->ID, self::META_SKU, true),
            'status' => get_post_meta($post->ID, self::META_STATUS, true) ?: 'active',
        ];

        wp_nonce_field(
            'esavest_material_meta_nonce_action',
            'esavest_material_meta_nonce'
        );

        include ESAVEST_CORE_PATH . 'admin/views/material-meta-form.php';
    }

    public static function save_meta($post_id, $post) {

        if (!current_user_can('manage_options')) return;

        if (
            !isset($_POST['esavest_material_meta_nonce']) ||
            !wp_verify_nonce($_POST['esavest_material_meta_nonce'], 'esavest_material_meta_nonce_action')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type !== self::POST_TYPE) return;

        $unit   = sanitize_text_field($_POST['esavest_material_unit'] ?? '');
        $sku    = sanitize_text_field($_POST['esavest_material_sku'] ?? '');
        $status = sanitize_text_field($_POST['esavest_material_status'] ?? 'active');

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        update_post_meta($post_id, self::META_UNIT, $unit);
        update_post_meta($post_id, self::META_SKU, $sku);
        update_post_meta($post_id, self::META_STATUS, $status);
    }

    public static function admin_columns($columns) {

        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ($key === 'title') {
                $new['es_unit']   = 'Default Unit';
                $new['es_sku']    = 'SKU';
                $new['es_status'] = 'Status';
            }
        }
        return $new;
    }

    public static function admin_column_values($column, $post_id) {

        if ($column === 'es_unit') {
            echo esc_html(get_post_meta($post_id, self::META_UNIT, true));
        }

        if ($column === 'es_sku') {
            echo esc_html(get_post_meta($post_id, self::META_SKU, true));
        }

        if ($column === 'es_status') {
            $s = get_post_meta($post_id, self::META_STATUS, true) ?: 'active';
            echo esc_html(ucfirst($s));
        }
    }

    public static function admin_status_filter_dropdown() {
        global $typenow;
        if ($typenow !== self::POST_TYPE) return;

        $current = sanitize_text_field($_GET['esavest_status'] ?? '');

        echo '<select name="esavest_status">';
        echo '<option value="">All Status</option>';
        echo '<option value="active"' . selected($current, 'active', false) . '>Active</option>';
        echo '<option value="inactive"' . selected($current, 'inactive', false) . '>Inactive</option>';
        echo '</select>';
    }

    public static function admin_filter_query($query) {
        if (!is_admin() || !$query->is_main_query()) return;
        if ($query->get('post_type') !== self::POST_TYPE) return;

        if (!empty($_GET['esavest_status'])) {
            $query->set('meta_query', [[
                'key'   => self::META_STATUS,
                'value' => sanitize_text_field($_GET['esavest_status']),
            ]]);
        }
    }
}
