<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Access_Guard {

    public static function init() {
        add_action('template_redirect', [__CLASS__, 'guard_routes'], 1);
    }

    public static function guard_routes() {

        // 🔓 Not logged in → public pages allowed
        if (!is_user_logged_in()) {
            return;
        }

        $user = wp_get_current_user();

        // 🔓 Admins → full access
        if (user_can($user, 'manage_options')) {
            return;
        }

        $roles = (array) $user->roles;
        $is_customer = in_array('esavest_customer', $roles, true);
        $is_partner  = in_array('esavest_partner',  $roles, true);

        // Not our portal roles → ignore
        if (!$is_customer && !$is_partner) {
            return;
        }

        $user_id = (int) $user->ID;

        $status = get_user_meta($user_id, '_esavest_user_status', true);
        $status = $status ?: 'pending';

        $current_url = home_url(add_query_arg([], $_SERVER['REQUEST_URI']));
        $account_url = home_url('/account/');
        $login_url   = home_url('/login/');

        // 🔑 Guard applies ONLY on /account/*
        $is_account_area = strpos($current_url, $account_url) === 0;

        // Not account page → no restriction at all
        if (!$is_account_area) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ESAVEST CUSTOMER
        |--------------------------------------------------------------------------
        */
        if ($is_customer) {

            // active OR pending → full account access
            if (in_array($status, ['active', 'pending'], true)) {
                return;
            }

            // blocked / inactive → dashboard only
            if (in_array($status, ['blocked', 'inactive'], true)) {

                // already on dashboard tab → allow
                if (!empty($_GET['tab']) && $_GET['tab'] === 'dashboard') {
                    return;
                }

                // force redirect to dashboard tab
                wp_safe_redirect(
                    add_query_arg(
                        [
                            'tab'    => 'dashboard',
                            'status' => $status,
                        ],
                        $account_url
                    )
                );
                exit;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ESAVEST PARTNER
        |--------------------------------------------------------------------------
        */
        if ($is_partner) {

            // active → full account access
            if ($status === 'active') {
                return;
            }

            // pending → dashboard only
            if ($status === 'pending') {

                if (!empty($_GET['tab']) && $_GET['tab'] === 'dashboard') {
                    return;
                }

                wp_safe_redirect(
                    add_query_arg(
                        [
                            'tab'    => 'dashboard',
                            'status' => 'pending',
                        ],
                        $account_url
                    )
                );
                exit;
            }

            // blocked / inactive → logout when accessing account
            if (in_array($status, ['blocked', 'inactive'], true)) {

                wp_logout();

                wp_safe_redirect(
                    add_query_arg('reason', 'account_disabled', $login_url)
                );
                exit;
            }
        }
    }
}
