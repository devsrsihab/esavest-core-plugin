<?php
if (!defined('ABSPATH')) exit;

add_action('admin_post_esavest_partner_send_price', 'esavest_partner_send_price_handler');

function esavest_partner_send_price_handler() {

    if (!is_user_logged_in()) {
        wp_safe_redirect(wp_login_url());
        exit;
    }

    // Nonce
    if (
        !isset($_POST['esavest_partner_send_price_nonce']) ||
        !wp_verify_nonce($_POST['esavest_partner_send_price_nonce'], 'esavest_partner_send_price_action')
    ) {
        wp_die('Security check failed.');
    }

    $partner_id = get_current_user_id();
    $request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
    $price      = isset($_POST['partner_price']) ? $_POST['partner_price'] : '';
    $note       = isset($_POST['partner_note']) ? $_POST['partner_note'] : '';

    if (!$request_id) {
        wp_safe_redirect(add_query_arg(['tab'=>'requests','err'=>'1']));
        exit;
    }

    // Validate request exists
    $request = get_post($request_id);
    if (!$request || $request->post_type !== 'esavest_request') {
        wp_safe_redirect(add_query_arg(['tab'=>'requests','err'=>'1']));
        exit;
    }

    if (!class_exists('ESAVEST_Core_CPT_Price')) {
        wp_safe_redirect(add_query_arg(['tab'=>'send-price','request_id'=>$request_id,'err'=>'1']));
        exit;
    }

    // Hard lock duplicate (again, backend)
    if (ESAVEST_Core_CPT_Price::partner_already_sent_for_request($request_id, $partner_id)) {
        wp_safe_redirect(add_query_arg(['tab'=>'send-price','request_id'=>$request_id,'locked'=>'1']));
        exit;
    }

    $created = ESAVEST_Core_CPT_Price::create_price($request_id, $partner_id, $price, $note);

    if (is_wp_error($created)) {
        $url = add_query_arg(['tab'=>'send-price','request_id'=>$request_id,'err'=>'1']);
        wp_safe_redirect($url);
        exit;
    }

    // Success
    $account_url = site_url('/account/');

    $url = add_query_arg([
    'tab'        => 'send-price',
    'request_id'=> $request_id,
    'sent'       => '1'
    ], $account_url);
    wp_safe_redirect($url);
    exit;
}
