<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Core_Public {

    /**
     * Init public hooks
     */
    public function init() {

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        add_shortcode('esavest_request_form', [$this, 'shortcode_request_form']);
        add_shortcode('esavest_my_requests', [$this, 'shortcode_my_requests']);

        // Partner shortcode
        add_shortcode('esavest_partner_offers', [$this, 'shortcode_partner_offers']);

        // Customer and Partner Dashboard shortcodes
        add_shortcode('esavest_dashboard', [$this, 'shortcode_dashboard']);

        // render request form for other uses
        add_shortcode('esavest_request_form', [$this, 'render_request_form']);


        add_action('init', [$this, 'handle_request_submit']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {

        wp_enqueue_style(
            'esavest-core-frontend',
            ESAVEST_CORE_URL . 'assets/css/frontend.css',
            ['esavest-bootstrap'],
            ESAVEST_CORE_VERSION
        );

        // Global plugin styles
        wp_register_style(
            'esavest-core-global-frontend',
            ESAVEST_CORE_URL . 'assets/css/esavest-global.css',
            [],
            ESAVEST_CORE_VERSION
        );

        wp_enqueue_script(
            'esavest-core-frontend',
            ESAVEST_CORE_URL . 'assets/js/frontend.js',
            ['jquery', 'esavest-bootstrap'],
            ESAVEST_CORE_VERSION,
            true
        );

        wp_enqueue_style('esavest-dashboard', ESAVEST_CORE_URL . 'public/assets/css/dashboard.css', [], ESAVEST_CORE_VERSION);
        wp_enqueue_script('esavest-dashboard', ESAVEST_CORE_URL . 'public/assets/js/dashboard.js', [], ESAVEST_CORE_VERSION, true);

        wp_enqueue_style('esavest-request-form-css', ESAVEST_CORE_URL . 'public/assets/css/request-form.css', [], ESAVEST_CORE_VERSION);
        wp_enqueue_script('esavest-request-form-js', ESAVEST_CORE_URL . 'public/assets/js/request-form.js', [], ESAVEST_CORE_VERSION, true);

    }

    /**
     * Check customer role
     */
    private function is_customer() {
        if (!is_user_logged_in()) return false;
        return current_user_can('esavest_customer');
    }

    private function is_partner() {
        if (!is_user_logged_in()) return false;
        return current_user_can('esavest_partner');
    }

    /**
     * Request form shortcode
     */
    public function shortcode_request_form() {

        if (!$this->is_customer()) {
            return '<div class="esavest-container"><div class="esavest-card">Please login as Customer to create request.</div></div>';
        }

        $msg = '';
        if (!empty($_GET['esavest_req'])) {
            if ($_GET['esavest_req'] === 'success') {
                $msg = '<div class="esavest-card esavest-success">Request submitted successfully.</div>';
            }
            if ($_GET['esavest_req'] === 'error') {
                $msg = '<div class="esavest-card esavest-error">Something went wrong.</div>';
            }
        }

        $materials = function_exists('esavest_core_get_active_materials')
            ? esavest_core_get_active_materials()
            : [];

        ob_start();

        $data = [
            'materials' => $materials,
            'msg'       => $msg,
        ];

        $template = ESAVEST_CORE_PATH . 'public/views/request-form.php';
        include $template;

        return ob_get_clean();
    }

    /**
     * Handle request submit
     */
    public function handle_request_submit() {

        if (
            !isset($_POST['esavest_request_action']) ||
            $_POST['esavest_request_action'] !== 'submit_request'
        ) {
            return;
        }

        // 🔐 Security
        if (
            !isset($_POST['esavest_request_nonce']) ||
            !wp_verify_nonce($_POST['esavest_request_nonce'], 'esavest_request_submit')
        ) {
            wp_die('Security check failed');
        }

        if (!is_user_logged_in()) {
            wp_die('You must be logged in.');
        }

        $user_id = get_current_user_id();

        // 🧾 Sanitize inputs
        $material_id   = (int) ($_POST['material_id'] ?? 0);
        $qty           = sanitize_text_field($_POST['request_qty'] ?? '');
        $unit          = sanitize_text_field($_POST['request_unit'] ?? '');
        $zip           = sanitize_text_field($_POST['delivery_zip'] ?? '');
        $date          = sanitize_text_field($_POST['delivery_date'] ?? '');
        $address       = sanitize_textarea_field($_POST['delivery_address'] ?? '');
        $description   = sanitize_textarea_field($_POST['request_description'] ?? '');

        if (!$material_id || !$qty || !$unit || !$zip || !$date || !$address) {
            wp_die('Required fields missing');
        }

        // 🆕 Create Request CPT
        $request_id = wp_insert_post([
            'post_type'   => 'esavest_request',
            'post_status' => 'publish',
            'post_title'  => 'Request #' . time(),
            'post_author' => $user_id,
        ]);

        if ($request_id) {
            update_post_meta($request_id, '_esavest_request_viewed', 'no');
        }

        if (is_wp_error($request_id)) {
            wp_die('Failed to create request');
        }

        // 💾 Save meta (Requirement aligned)
        update_post_meta($request_id, '_esavest_request_customer_id', $user_id);
        update_post_meta($request_id, '_esavest_request_material_id', $material_id);
        update_post_meta($request_id, '_esavest_request_qty', $qty);
        update_post_meta($request_id, '_esavest_request_unit', $unit);
        update_post_meta($request_id, '_esavest_request_delivery_zip', $zip);
        update_post_meta($request_id, '_esavest_request_delivery_date', $date);
        update_post_meta($request_id, '_esavest_request_delivery_address', $address);
        update_post_meta($request_id, '_esavest_request_description', $description);
        update_post_meta($request_id, '_esavest_request_status', 'request_received');

        // 📎 FILE UPLOAD (Requirement allowed types)
        if (!empty($_FILES['request_file']['name'])) {

            $allowed_mimes = [
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];

            $file_type = wp_check_filetype($_FILES['request_file']['name']);

            if (in_array($file_type['type'], $allowed_mimes, true)) {

                if (!function_exists('media_handle_upload')) {
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                }

                $attachment_id = media_handle_upload('request_file', $request_id);

                if (!is_wp_error($attachment_id)) {
                    update_post_meta($request_id, '_esavest_request_file_id', $attachment_id);
                    update_post_meta($request_id, '_esavest_request_file_url', wp_get_attachment_url($attachment_id));
                }
            }
        }

        // ✅ Redirect after success
        wp_redirect(
            add_query_arg('request_submitted', '1', wp_get_referer())
        );
        exit;
    }


    /**
     * Upload helper
     */
    private function handle_file_upload($key) {

        if (empty($_FILES[$key]['tmp_name'])) return [];

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = wp_handle_upload($_FILES[$key], ['test_form' => false]);
        if (empty($upload['file'])) return [];

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $upload['type'],
            'post_title'     => basename($upload['file']),
            'post_status'    => 'inherit',
        ], $upload['file']);

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $upload['file'])
        );

        return [
            'attachment_id' => $attachment_id,
            'url'           => $upload['url'],
        ];
    }

    /**
     * My Requests shortcode
     */
    public function shortcode_my_requests() {

        if (!$this->is_customer()) {
            return '<div class="esavest-card">Please login as Customer.</div>';
        }

        $query = new WP_Query([
            'post_type' => ESAVEST_Core_CPT_Request::POST_TYPE,
            'meta_query' => [
                [
                    'key'   => ESAVEST_Core_CPT_Request::META_CUSTOMER_ID,
                    'value' => get_current_user_id(),
                ],
            ],
        ]);

        ob_start();
        include ESAVEST_CORE_PATH . 'public/views/my-requests.php';
        wp_reset_postdata();

        return ob_get_clean();
    }

    private function redirect_error() {
        wp_safe_redirect(add_query_arg('esavest_req', 'error', wp_get_referer()));
        exit;
    }


    // ==============================================
    // CUSTOMER AND PARTNER DASHBOARD SHORTCODES
    // ==============================================

    public function shortcode_dashboard() {


        if (!is_user_logged_in() ) {
            return '<div class="esavest-card">Access denied.</div>';
        }

        $uid = get_current_user_id();

        $data = [
            'requests_count' => esavest_count_requests_by_customer($uid),
            'offers_count'   => esavest_count_offers_by_customer($uid),
            'recent_requests'=> esavest_get_recent_requests($uid),
            'recent_offers'  => esavest_get_recent_offers($uid),
        ];

        $sidebar = ESAVEST_CORE_PATH . 'public/views/dashboard/customer/sidebar-customer.php';
        $content = ESAVEST_CORE_PATH . 'public/views/dashboard/customer/dashboard-customer.php';

        ob_start();
        include ESAVEST_CORE_PATH . 'public/views/dashboard/dashboard-layout.php';
        return ob_get_clean();
    }



    // form  ui render 
    public function render_request_form() {

        if (!is_user_logged_in()) {
            return '<p>Please login to submit a material request.</p>';
        }

        $materials = esavest_core_get_active_materials();

        ob_start();
        include ESAVEST_CORE_PATH . 'public/views/request-form.php';
        return ob_get_clean();
    }

    









}
