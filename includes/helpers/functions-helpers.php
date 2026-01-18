<?php
if (!defined('ABSPATH')) exit;

/**
 * Get CPT count safely (all statuses)
 */
function esavest_core_get_cpt_count($post_type) {

    if (!post_type_exists($post_type)) {
        return 0;
    }

    $counts = wp_count_posts($post_type);

    if (!$counts || !is_object($counts)) {
        return 0;
    }

    $total = 0;

    foreach ($counts as $status => $count) {
        $total += (int) $count;
    }

    return $total;
}




/**
 * Get only ACTIVE materials (for request form dropdown etc.)
 *
 * @return WP_Post[]
 */
function esavest_core_get_active_materials() {

    return get_posts([
        'post_type'      => ESAVEST_Core_CPT_Material::POST_TYPE,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => ESAVEST_Core_CPT_Material::META_STATUS,
                'value'   => 'active',
                'compare' => '=',
            ],
        ],
    ]);
}

/**
 * Convenience: get material status (active/inactive) with default = active.
 */
function esavest_core_get_material_status($material_id) {
    $status = get_post_meta($material_id, ESAVEST_Core_CPT_Material::META_STATUS, true);
    return $status !== '' ? $status : 'active';
}


function esavest_count_requests_by_customer($uid) {
    return (int) count(get_posts([
        'post_type'  => 'esavest_request',
        'meta_key'   => '_esavest_request_customer_id',
        'meta_value' => $uid,
        'fields'     => 'ids',
    ]));
}

function esavest_count_offers_by_customer($uid) {
    return (int) count(get_posts([
        'post_type'  => 'esavest_offer',
        'meta_key'   => '_esavest_offer_customer_id',
        'meta_value' => $uid,
        'fields'     => 'ids',
    ]));
}

function esavest_get_recent_requests($uid, $limit = 5) {
    return get_posts([
        'post_type'  => 'esavest_request',
        'numberposts'=> $limit,
        'meta_key'   => '_esavest_request_customer_id',
        'meta_value' => $uid,
    ]);
}

function esavest_get_recent_offers($uid, $limit = 5) {
    return get_posts([
        'post_type'  => 'esavest_offer',
        'numberposts'=> $limit,
        'meta_key'   => '_esavest_offer_customer_id',
        'meta_value' => $uid,
    ]);
}

// =============================================
// Partner Dashboard Helpers (ADD BELOW)
// =============================================

if (!function_exists('esavest_count_partner_offers')) {
    function esavest_count_partner_offers($partner_id, $status = '') {

        $args = [
            'post_type'   => 'esavest_offer',
            'fields'      => 'ids',
            'numberposts' => -1,
            'meta_query'  => [
                [
                    'key'   => '_esavest_offer_partner_id',
                    'value' => (int) $partner_id,
                ],
            ],
        ];

        if (!empty($status)) {
            $args['meta_query'][] = [
                'key'   => '_esavest_offer_status',
                'value' => sanitize_text_field($status),
            ];
        }

        return (int) count(get_posts($args));
    }
}

if (!function_exists('esavest_get_partner_recent_offers')) {
    function esavest_get_partner_recent_offers($partner_id, $limit = 5) {

        return get_posts([
            'post_type'   => 'esavest_offer',
            'numberposts' => (int) $limit,
            'orderby'     => 'date',
            'order'       => 'DESC',
            'meta_key'    => '_esavest_offer_partner_id',
            'meta_value'  => (int) $partner_id,
        ]);
    }
}



function esavest_core_get_unread_request_count() {

    $q = new WP_Query([
        'post_type'      => 'esavest_request',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => '_esavest_request_viewed',
                'value'   => 'no',
                'compare' => '='
            ]
        ]
    ]);

    return (int) $q->found_posts;
}
