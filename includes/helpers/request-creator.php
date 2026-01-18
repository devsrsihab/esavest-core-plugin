<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Request_Creator {

    public static function create_from_customer($data, $user_id) {

        $post_id = wp_insert_post([
            'post_type'   => ESAVEST_Core_CPT_Request::POST_TYPE,
            'post_title'  => 'Request – ' . current_time('Y-m-d H:i'),
            'post_status' => 'publish',
        ]);

        if (!$post_id) return 0;

        update_post_meta($post_id, ESAVEST_Core_CPT_Request::META_CUSTOMER_ID, $user_id);

        // Other meta will be saved using same keys
        return $post_id;
    }
}
