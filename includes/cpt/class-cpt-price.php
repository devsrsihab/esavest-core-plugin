<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Core_CPT_Price {

    const POST_TYPE = 'esavest_price';

    // Meta keys
    const META_REQUEST_ID = '_esavest_price_request_id';
    const META_PARTNER_ID = '_esavest_price_partner_id';
    const META_PRICE      = '_esavest_price_amount';
    const META_NOTE       = '_esavest_price_note';
    const META_CREATED_AT = '_esavest_price_created_at';

    public function init() {
        add_action('init', [$this, 'register_post_type']);
    }

    public function register_post_type() {

        // Admin-only in wp-admin (safe). Partner will submit via handler (no wp-admin UI needed)
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
                'name'          => 'Price Lists',
                'singular_name' => 'Price List',
                'not_found'     => 'No price lists found',
            ],
            'public'        => false,
            'show_ui'       => false, // ✅ managed via metabox + partner dashboard
            'show_in_menu'  => false,
            'supports'      => ['title'],
            'has_archive'   => false,
            'rewrite'       => false,
            'show_in_rest'  => false,
            'capabilities'  => $caps,
            'map_meta_cap'  => false,
        ]);
    }

    // -------------------------
    // Helper: prices under request
    // -------------------------
    public static function get_prices_by_request($request_id) {
        $request_id = (int) $request_id;
        if (!$request_id) return [];

        $q = new WP_Query([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'   => self::META_REQUEST_ID,
                    'value' => $request_id,
                ],
            ],
        ]);

        return $q->posts ?: [];
    }

    // ✅ Helper: prevent duplicate (partner already sent?)
    public static function partner_already_sent_for_request($request_id, $partner_id) {
        $q = new WP_Query([
            'post_type' => self::POST_TYPE,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => self::META_REQUEST_ID,
                    'value' => (int)$request_id,
                ],
                [
                    'key' => self::META_PARTNER_ID,
                    'value' => (int)$partner_id,
                ],
            ],
        ]);
        return !empty($q->posts);
    }


    // -------------------------
    // Create price (one-time)
    // -------------------------
    public static function create_price($request_id, $partner_id, $price, $note = '') {

        $request_id = (int) $request_id;
        $partner_id = (int) $partner_id;
        $price      = is_numeric($price) ? (float) $price : 0;

        if (!$request_id || !$partner_id || $price <= 0) {
            return new WP_Error('invalid_data', 'Invalid price list data.');
        }

        // ✅ Hard lock duplicate
        if (self::partner_already_sent_for_request($request_id, $partner_id)) {
            return new WP_Error('locked', 'Price already submitted for this request.');
        }

        $pid = wp_insert_post([
            'post_type'   => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title'  => 'Price #' . time(),
        ]);

        if (is_wp_error($pid)) return $pid;

        update_post_meta($pid, self::META_REQUEST_ID, $request_id);
        update_post_meta($pid, self::META_PARTNER_ID, $partner_id);
        update_post_meta($pid, self::META_PRICE, $price);
        update_post_meta($pid, self::META_NOTE, sanitize_textarea_field($note));
        update_post_meta($pid, self::META_CREATED_AT, current_time('mysql'));

        return $pid;
    }

    // -------------------------
    // Partner history pagination
    // -------------------------
    public static function get_prices_by_partner_paginated($partner_id, $page = 1, $per_page = 10) {
        $q = new WP_Query([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => max(1,$page),
            'meta_query' => [
                [
                    'key' => self::META_PARTNER_ID,
                    'value' => (int)$partner_id,
                ],
            ],
        ]);

        return [
            'posts' => $q->posts ?: [],
            'pages' => (int)$q->max_num_pages,
            'total' => (int)$q->found_posts,
        ];
    }



    // Mark a price as selected
    public static function select_price($price_id, $request_id) {
        $price_id   = (int) $price_id;
        $request_id = (int) $request_id;

        if (!$price_id || !$request_id) return false;

        // Unselect others
        $prices = self::get_prices_by_request($request_id);
        foreach ($prices as $p) {
            delete_post_meta($p->ID, '_esavest_price_selected');
        }

        // Select this one
        update_post_meta($price_id, '_esavest_price_selected', 'yes');
        update_post_meta($request_id, '_esavest_request_selected_price_id', $price_id);

        // Update request status
        update_post_meta($request_id, '_esavest_request_status', 'price_selected');

        return true;
    }

    public static function is_price_selected($price_id) {
        return get_post_meta($price_id, '_esavest_price_selected', true) === 'yes';
    }

}
