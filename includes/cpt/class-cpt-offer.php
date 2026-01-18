<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Core_CPT_Offer {

    const POST_TYPE = 'esavest_offer';

    // Relations
    const META_REQUEST_ID  = '_esavest_offer_request_id';
    const META_CUSTOMER_ID = '_esavest_offer_customer_id';
    const META_PARTNER_ID  = '_esavest_offer_partner_id';

    // Offer data
    const META_NOTE          = '_esavest_offer_note';
    const META_PARTNER_PRICE = '_esavest_offer_partner_price'; // hidden
    const META_FINAL_PRICE   = '_esavest_offer_final_price';   // visible
    const META_STATUS        = '_esavest_offer_status';

    public function init() {
        add_action('init', [$this, 'register_post_type']);

        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'save_meta'], 10, 2);

        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'admin_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'admin_column_values'], 10, 2);

        add_action('restrict_manage_posts', [$this, 'admin_status_filter_dropdown']);
        add_action('pre_get_posts', [$this, 'admin_filter_query']);
    }

    /* =========================
       CPT
    ========================= */
    public function register_post_type() {

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
                'name'          => 'Offers',
                'singular_name' => 'Offer',
                'add_new_item'  => 'Create Offer',
                'edit_item'     => 'Edit Offer',
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_icon'    => 'dashicons-media-spreadsheet',
            'supports'     => ['title'],
            'menu_position'=> 24,
            'rewrite'      => false,
            'show_in_rest' => false,
            'capabilities' => $caps,
            'map_meta_cap' => false,
        ]);
    }

    public static function allowed_statuses() {
        return [
            'pending'  => 'Pending',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
        ];
    }

    /* =========================
       META BOX
    ========================= */
    public function add_meta_boxes() {
        add_meta_box(
            'esavest_offer_details',
            'Offer Details',
            [$this, 'render_meta_box'],
            self::POST_TYPE,
            'normal',
            'default'
        );
    }

    public function render_meta_box($post) {

        $data = [
            'request_id'    => (int) get_post_meta($post->ID, self::META_REQUEST_ID, true),
            'customer_id'   => (int) get_post_meta($post->ID, self::META_CUSTOMER_ID, true),
            'partner_id'    => (int) get_post_meta($post->ID, self::META_PARTNER_ID, true),
            'note'          => (string) get_post_meta($post->ID, self::META_NOTE, true),
            'partner_price' => (string) get_post_meta($post->ID, self::META_PARTNER_PRICE, true),
            'final_price'   => (string) get_post_meta($post->ID, self::META_FINAL_PRICE, true),
            'status'        => get_post_meta($post->ID, self::META_STATUS, true) ?: 'pending',

            // 🔒 STRICT SAFE ARRAYS (NO OBJECTS)
            'requests'  => $this->get_requests_dropdown(),
            'partners'  => $this->get_users_dropdown('esavest_partner'),
            'customers' => $this->get_users_dropdown('esavest_customer'),
        ];

        // ================= DEBUG LOG =================
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('===== ESAVEST OFFER META BOX =====');
            error_log('Offer ID: ' . $post->ID);
            error_log('Requests dropdown: ' . print_r($data['requests'], true));
            error_log('Customers dropdown: ' . print_r($data['customers'], true));
            error_log('Partners dropdown: ' . print_r($data['partners'], true));
            error_log('=================================');
        }
        // ============================================

        wp_nonce_field('esavest_offer_meta_nonce_action', 'esavest_offer_meta_nonce');

        include ESAVEST_CORE_PATH . 'admin/views/offer-meta-form.php';
    }

    /* =========================
       SAVE
    ========================= */
    public function save_meta($post_id, $post) {

        if (!current_user_can('manage_options')) return;

        if (
            empty($_POST['esavest_offer_meta_nonce']) ||
            !wp_verify_nonce($_POST['esavest_offer_meta_nonce'], 'esavest_offer_meta_nonce_action')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type !== self::POST_TYPE) return;

        update_post_meta($post_id, self::META_REQUEST_ID,  (int) ($_POST['esavest_offer_request_id'] ?? 0));
        update_post_meta($post_id, self::META_CUSTOMER_ID, (int) ($_POST['esavest_offer_customer_id'] ?? 0));
        update_post_meta($post_id, self::META_PARTNER_ID,  (int) ($_POST['esavest_offer_partner_id'] ?? 0));

        update_post_meta($post_id, self::META_NOTE, sanitize_textarea_field($_POST['esavest_offer_note'] ?? ''));
        update_post_meta($post_id, self::META_PARTNER_PRICE, (float) ($_POST['esavest_offer_partner_price'] ?? 0));
        update_post_meta($post_id, self::META_FINAL_PRICE,   (float) ($_POST['esavest_offer_final_price'] ?? 0));

        $status = sanitize_text_field($_POST['esavest_offer_status'] ?? 'pending');
        if (!isset(self::allowed_statuses()[$status])) {
            $status = 'pending';
        }
        update_post_meta($post_id, self::META_STATUS, $status);
    }

    /* =========================
       ADMIN LIST
    ========================= */
    public function admin_columns($columns) {
        $columns['es_request']  = 'Request';
        $columns['es_status']   = 'Status';
        $columns['es_price']    = 'Final Price';
        return $columns;
    }

    public function admin_column_values($column, $post_id) {

        if ($column === 'es_request') {
            echo esc_html('#' . (int) get_post_meta($post_id, self::META_REQUEST_ID, true));
        }

        if ($column === 'es_status') {
            $status = get_post_meta($post_id, self::META_STATUS, true) ?: 'pending';
            echo esc_html(self::allowed_statuses()[$status] ?? $status);
        }

        if ($column === 'es_price') {
            echo esc_html(get_post_meta($post_id, self::META_FINAL_PRICE, true));
        }
    }

    /* =========================
       DROPDOWN HELPERS (SAFE)
    ========================= */
    private function get_requests_dropdown() {

        if (!class_exists('ESAVEST_Core_CPT_Request')) return [];

        $q = new WP_Query([
            'post_type'      => ESAVEST_Core_CPT_Request::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 200,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $out = [];
        foreach ($q->posts as $p) {
            if ($p instanceof WP_Post) {
                $out[$p->ID] = $p->post_title ?: ('Request #' . $p->ID);
            }
        }

        wp_reset_postdata();
        return $out;
    }

    private function get_users_dropdown($role) {

        $users = get_users([
            'role'   => $role,
            'fields' => ['ID', 'display_name'],
        ]);

        $out = [];
        foreach ($users as $u) {
            $out[$u->ID] = $u->display_name;
        }

        return $out;
    }

    public function admin_status_filter_dropdown() {}
    public function admin_filter_query($query) {}
}
