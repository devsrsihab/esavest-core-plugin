<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Core_CPT_Request {

    const POST_TYPE = 'esavest_request';

    // Existing Meta keys
    const META_VIEWED = '_esavest_request_viewed';
    const META_CUSTOMER_ID   = '_esavest_request_customer_id';
    const META_MATERIAL_ID   = '_esavest_request_material_id';
    const META_DESCRIPTION   = '_esavest_request_description';
    const META_STATUS        = '_esavest_request_status';
    const META_FILE_ID       = '_esavest_request_file_id';
    const META_FILE_URL      = '_esavest_request_file_url';

    // ✅ NEW Meta keys (Requirement)
    const META_QTY           = '_esavest_request_qty';
    const META_UNIT          = '_esavest_request_unit';
    const META_ADDRESS       = '_esavest_request_delivery_address';
    const META_ZIP           = '_esavest_request_delivery_zip';
    const META_DELIVERY_DATE = '_esavest_request_delivery_date';

    public function init() {
        add_action('init', [$this, 'register_post_type']);

        add_filter('post_class', [$this, 'add_row_class'], 10, 3);
    
        // ✅ IMPORTANT: enable file upload in admin edit form
        add_action('post_edit_form_tag', function () {
            echo ' enctype="multipart/form-data"';
        });

        // Admin UI
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'save_meta'], 10, 2);

        // Admin columns + filter
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'admin_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'admin_column_values'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'admin_status_filter_dropdown']);
        add_action('pre_get_posts', [$this, 'admin_filter_query']);

        add_action('restrict_manage_posts', [$this, 'admin_seen_filter_dropdown']);

    }

    public function admin_seen_filter_dropdown() {

        global $typenow;
        if ($typenow !== self::POST_TYPE) return;

        $current = $_GET['esavest_seen'] ?? '';

        echo '<select name="esavest_seen">';
        echo '<option value="">All</option>';
        echo '<option value="no" ' . selected($current, 'no', false) . '>Unread</option>';
        echo '<option value="yes" ' . selected($current, 'yes', false) . '>Read</option>';
        echo '</select>';
    }


    public function add_meta_boxes() {
        add_meta_box(
            'esavest_request_details',
            'Request Details',
            [$this, 'render_meta_box'],
            self::POST_TYPE,
            'normal',
            'default'
        );
    }

    public function render_meta_box($post) {

        $data = [
            'customer_id' => get_post_meta($post->ID, self::META_CUSTOMER_ID, true),
            'material_id' => get_post_meta($post->ID, self::META_MATERIAL_ID, true),
            'description' => get_post_meta($post->ID, self::META_DESCRIPTION, true),
            'status'      => get_post_meta($post->ID, self::META_STATUS, true) ?: 'request_received',
            'file_url'    => get_post_meta($post->ID, self::META_FILE_URL, true),

            // ✅ NEW fields
            'qty'         => get_post_meta($post->ID, self::META_QTY, true),
            'unit'        => get_post_meta($post->ID, self::META_UNIT, true),
            'address'     => get_post_meta($post->ID, self::META_ADDRESS, true),
            'zip'         => get_post_meta($post->ID, self::META_ZIP, true),
            'delivery_date' => get_post_meta($post->ID, self::META_DELIVERY_DATE, true),

            // Materials dropdown list
            'materials'   => function_exists('esavest_core_get_active_materials')
                ? esavest_core_get_active_materials()
                : [],
        ];

        wp_nonce_field(
            'esavest_request_meta_nonce_action',
            'esavest_request_meta_nonce'
        );

        // Admin meta form view
        include ESAVEST_CORE_PATH . 'admin/views/request-meta-form.php';
    }

    public function save_meta($post_id, $post) {

        // Auto set customer ID
        if (!get_post_meta($post_id, self::META_CUSTOMER_ID, true)) {

            // If created from admin → admin is owner (temporary)
            if (is_admin()) {
                update_post_meta($post_id, self::META_CUSTOMER_ID, 0);
            }

            // Future: customer dashboard submit hook will override this
        }


        if (!current_user_can('manage_options')) return;

        if (
            !isset($_POST['esavest_request_meta_nonce']) ||
            !wp_verify_nonce($_POST['esavest_request_meta_nonce'], 'esavest_request_meta_nonce_action')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if ($post->post_type !== self::POST_TYPE) return;

        // Existing fields
        $material = isset($_POST['esavest_material_id']) ? (int) $_POST['esavest_material_id'] : 0;
        $status   = isset($_POST['esavest_request_status']) ? sanitize_text_field($_POST['esavest_request_status']) : 'request_received';
        $desc     = isset($_POST['esavest_request_description']) ? wp_kses_post($_POST['esavest_request_description']) : '';

        // ✅ NEW fields
        $qty   = isset($_POST['esavest_request_qty']) ? sanitize_text_field($_POST['esavest_request_qty']) : '';
        $unit  = isset($_POST['esavest_request_unit']) ? sanitize_text_field($_POST['esavest_request_unit']) : '';
        $addr  = isset($_POST['esavest_request_delivery_address']) ? sanitize_textarea_field($_POST['esavest_request_delivery_address']) : '';
        $zip   = isset($_POST['esavest_request_delivery_zip']) ? sanitize_text_field($_POST['esavest_request_delivery_zip']) : '';
        $date  = isset($_POST['esavest_request_delivery_date']) ? sanitize_text_field($_POST['esavest_request_delivery_date']) : '';

        // Status validation
        $allowed_status = array_keys(self::allowed_statuses());
        if (!in_array($status, $allowed_status, true)) {
            $status = 'request_received';
        }

        update_post_meta($post_id, self::META_MATERIAL_ID, $material);
        update_post_meta($post_id, self::META_STATUS, $status);
        update_post_meta($post_id, self::META_DESCRIPTION, $desc);

        // ✅ Save new meta
        update_post_meta($post_id, self::META_QTY, $qty);
        update_post_meta($post_id, self::META_UNIT, $unit);
        update_post_meta($post_id, self::META_ADDRESS, $addr);
        update_post_meta($post_id, self::META_ZIP, $zip);
        update_post_meta($post_id, self::META_DELIVERY_DATE, $date);


        // ================= FILE UPLOAD (ADMIN EDIT SUPPORT) =================
        if (!empty($_FILES['esavest_request_file']['name'])) {

            $allowed_mimes = [
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];

            $file_type = wp_check_filetype($_FILES['esavest_request_file']['name']);

            if (!in_array($file_type['type'], $allowed_mimes, true)) {
                return; // silently reject invalid file
            }

            if (!function_exists('media_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }

            $file_id = media_handle_upload('esavest_request_file', $post_id);

            if (!is_wp_error($file_id)) {
                update_post_meta($post_id, self::META_FILE_ID, $file_id);
                update_post_meta($post_id, self::META_FILE_URL, wp_get_attachment_url($file_id));
            }
        }

    }

    public function register_post_type() {

        $caps = [
            // Admin only in wp-admin listing/edit (requests created from frontend)
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
                'name'          => 'Requests',
                'singular_name' => 'Request',
                'add_new_item'  => 'Add New Request',
                'edit_item'     => 'Edit Request',
                'not_found'     => 'No requests found',
            ],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'menu_icon'     => 'dashicons-clipboard',
            'supports'      => ['title'],
            'menu_position' => 23,
            'has_archive'   => false,
            'rewrite'       => false,
            'show_in_rest'  => false,
            'capabilities'  => $caps,
            'map_meta_cap'  => false,
        ]);
    }

    // ✅ FULL status list (Requirement aligned)
    public static function allowed_statuses() {
        return [
            'request_received' => 'Request Received',
            'processing'       => 'Processing',
            'waiting_info'     => 'Waiting for More Info',
            'offer_sent'       => 'Offer Sent',
            'offer_accepted'   => 'Offer Accepted',
            'offer_rejected'   => 'Offer Rejected',
            'completed'        => 'Completed',
        ];
    }

    public function admin_columns($columns) {
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;

            if ($key === 'title') {
                 $new['es_seen']     = 'Seen';
                $new['es_customer'] = 'Customer';
                $new['es_status']   = 'Status';
                $new['es_material'] = 'Material';

                // ✅ Optional helpful columns
                $new['es_qty']      = 'Qty';
                $new['es_zip']      = 'ZIP';
                $new['es_date']     = 'Delivery Date';
            }
        }
        return $new;
    }

    public function admin_column_values($column, $post_id) {


        if ($column === 'es_seen') {

            $viewed = get_post_meta($post_id, self::META_VIEWED, true);

            if ($viewed === 'no') {
                echo '<span class="esavest-seen-badge esavest-seen-unread">Unread</span>';
            } else {
                echo '<span class="esavest-seen-badge esavest-seen-read">Read</span>';
            }
            return;
        }


        if ($column === 'es_customer') {
            $uid = (int) get_post_meta($post_id, self::META_CUSTOMER_ID, true);
            if ($uid) {
                $u = get_user_by('id', $uid);
                echo $u ? esc_html($u->display_name) : '—';
            } else {
                echo '—';
            }
            return;
        }

        if ($column === 'es_status') {
            $status = get_post_meta($post_id, self::META_STATUS, true);
            $status = $status ?: 'request_received';
            $labels = self::allowed_statuses();
            echo isset($labels[$status]) ? esc_html($labels[$status]) : esc_html($status);
            return;
        }

        if ($column === 'es_material') {
            $mid = (int) get_post_meta($post_id, self::META_MATERIAL_ID, true);
            echo $mid ? esc_html(get_the_title($mid)) : '—';
            return;
        }

        // ✅ Optional columns
        if ($column === 'es_qty') {
            $qty  = get_post_meta($post_id, self::META_QTY, true);
            $unit = get_post_meta($post_id, self::META_UNIT, true);
            $out  = trim($qty . ' ' . $unit);
            echo $out !== '' ? esc_html($out) : '—';
            return;
        }

        if ($column === 'es_zip') {
            $zip = get_post_meta($post_id, self::META_ZIP, true);
            echo $zip !== '' ? esc_html($zip) : '—';
            return;
        }

        if ($column === 'es_date') {
            $date = get_post_meta($post_id, self::META_DELIVERY_DATE, true);
            echo $date !== '' ? esc_html($date) : '—';
            return;
        }
    }

    public function admin_status_filter_dropdown() {
        global $typenow;
        if ($typenow !== self::POST_TYPE) return;

        $current = isset($_GET['esavest_req_status']) ? sanitize_text_field($_GET['esavest_req_status']) : '';
        $labels  = self::allowed_statuses();

        echo '<select name="esavest_req_status">';
        echo '<option value="">All Status</option>';
        foreach ($labels as $k => $lbl) {
            echo '<option value="' . esc_attr($k) . '" ' . selected($current, $k, false) . '>' . esc_html($lbl) . '</option>';
        }
        echo '</select>';
    }

    public function admin_filter_query($query) {
        if (!is_admin() || !$query->is_main_query()) return;
        if ($query->get('post_type') !== self::POST_TYPE) return;

        if (!empty($_GET['esavest_seen'])) {

            $seen = sanitize_text_field($_GET['esavest_seen']);
            $meta_query = (array) $query->get('meta_query');
            $meta_query[] = [
                'key'   => self::META_VIEWED,
                'value' => $seen,
            ];
            $query->set('meta_query', $meta_query);
        }


        if (!empty($_GET['esavest_req_status'])) {
            $status  = sanitize_text_field($_GET['esavest_req_status']);
            $allowed = array_keys(self::allowed_statuses());
            if (in_array($status, $allowed, true)) {
                $query->set('meta_query', [
                    [
                        'key'   => self::META_STATUS,
                        'value' => $status,
                    ],
                ]);
            }
        }
    }

    public function add_row_class($classes, $class, $post_id) {

        if (get_post_type($post_id) !== self::POST_TYPE) {
            return $classes;
        }

        $viewed = get_post_meta($post_id, self::META_VIEWED, true);

        if ($viewed === 'no') {
            $classes[] = 'esavest-unread-request';
        }

        return $classes;
    }

}
