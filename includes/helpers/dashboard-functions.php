<?php
if (!defined('ABSPATH')) exit;

/**
 * Dashboard helper functions
 * Used by customer/partner dashboard shortcodes
 */

/** ---------------------------
 * CUSTOMER
 * --------------------------*/

function esavest_count_requests_by_customer($customer_id) {
    if (!class_exists('ESAVEST_Core_CPT_Request')) return 0;

    $q = new WP_Query([
        'post_type'      => ESAVEST_Core_CPT_Request::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => ESAVEST_Core_CPT_Request::META_CUSTOMER_ID,
                'value' => (int) $customer_id,
            ],
        ],
    ]);

    return (int) $q->found_posts;
}

function esavest_count_offers_by_customer($customer_id) {
    if (!class_exists('ESAVEST_Core_CPT_Offer')) return 0;

    $q = new WP_Query([
        'post_type'      => ESAVEST_Core_CPT_Offer::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'   => ESAVEST_Core_CPT_Offer::META_CUSTOMER_ID,
                'value' => (int) $customer_id,
            ],
        ],
    ]);

    return (int) $q->found_posts;
}

function esavest_get_recent_requests($customer_id, $limit = 5) {
    if (!class_exists('ESAVEST_Core_CPT_Request')) return [];

    $q = new WP_Query([
        'post_type'      => ESAVEST_Core_CPT_Request::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => (int) $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'   => ESAVEST_Core_CPT_Request::META_CUSTOMER_ID,
                'value' => (int) $customer_id,
            ],
        ],
    ]);

    $posts = $q->posts ?: [];
    wp_reset_postdata();
    return $posts;
}

function esavest_get_recent_offers($customer_id, $limit = 5) {
    if (!class_exists('ESAVEST_Core_CPT_Offer')) return [];

    $q = new WP_Query([
        'post_type'      => ESAVEST_Core_CPT_Offer::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => (int) $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'   => ESAVEST_Core_CPT_Offer::META_CUSTOMER_ID,
                'value' => (int) $customer_id,
            ],
        ],
    ]);

    $posts = $q->posts ?: [];
    wp_reset_postdata();
    return $posts;
}

/** ---------------------------
 * PARTNER
 * --------------------------*/

function esavest_count_partner_offers($partner_id, $status = '') {
    if (!class_exists('ESAVEST_Core_CPT_Offer')) return 0;

    $meta_query = [
        [
            'key'   => ESAVEST_Core_CPT_Offer::META_PARTNER_ID,
            'value' => (int) $partner_id,
        ],
    ];

    if (!empty($status)) {
        $meta_query[] = [
            'key'   => ESAVEST_Core_CPT_Offer::META_STATUS,
            'value' => sanitize_text_field($status),
        ];
    }

    $q = new WP_Query([
        'post_type'      => ESAVEST_Core_CPT_Offer::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => $meta_query,
    ]);

    return (int) $q->found_posts;
}

function esavest_get_partner_recent_offers($partner_id, $limit = 5) {
    if (!class_exists('ESAVEST_Core_CPT_Offer')) return [];

    $q = new WP_Query([
        'post_type'      => ESAVEST_Core_CPT_Offer::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => (int) $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'   => ESAVEST_Core_CPT_Offer::META_PARTNER_ID,
                'value' => (int) $partner_id,
            ],
        ],
    ]);

    $posts = $q->posts ?: [];
    wp_reset_postdata();
    return $posts;
}
