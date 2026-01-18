<?php
if (!defined('ABSPATH')) exit;

/**
 * Role helpers
 */
function esavest_core_is_admin_user($user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return false;
    return user_can($user_id, 'manage_options');
}

function esavest_core_is_customer_user($user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return false;
    $u = get_user_by('id', $user_id);
    if (!$u) return false;
    return in_array('esavest_customer', (array) $u->roles, true);
}

function esavest_core_is_partner_user($user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) return false;
    $u = get_user_by('id', $user_id);
    if (!$u) return false;
    return in_array('esavest_partner', (array) $u->roles, true);
}

/**
 * Offer visibility rules
 * - Admin: সব offer full view
 * - Partner: শুধু assigned offers full view
 * - Customer: শুধু নিজের offers view (price may be hidden)
 */
function esavest_core_user_can_view_offer($offer_id, $user_id = 0) {
    $user_id = $user_id ?: get_current_user_id();
    if (!$offer_id || !$user_id) return false;

    if (esavest_core_is_admin_user($user_id)) return true;

    // Partner: must match assigned partner id
    if (esavest_core_is_partner_user($user_id)) {
        $partner_id = (int) get_post_meta($offer_id, ESAVEST_Core_CPT_Offer::META_PARTNER_ID, true);
        return $partner_id && $partner_id === (int) $user_id;
    }

    // Customer: must match assigned customer id
    if (esavest_core_is_customer_user($user_id)) {
        $customer_id = (int) get_post_meta($offer_id, ESAVEST_Core_CPT_Offer::META_CUSTOMER_ID, true);
        return $customer_id && $customer_id === (int) $user_id;
    }

    return false;
}

/**
 * Customer price visibility control:
 * - partner price: NEVER visible to customer
 * - final price: only if admin sets META_SHOW_PRICE = '1'
 */
function esavest_core_offer_customer_can_see_price($offer_id) {
    if (!$offer_id) return false;
    $flag = get_post_meta($offer_id, ESAVEST_Core_CPT_Offer::META_SHOW_PRICE, true);
    return (string) $flag === '1';
}
