<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_Login_Guard {

    public static function init() {
        add_filter('authenticate', [self::class, 'check_user_status'], 30, 3);
    }

    public static function check_user_status($user, $username, $password) {

        if (!$user || is_wp_error($user)) return $user;

        $status = get_user_meta($user->ID, '_esavest_user_status', true);

        if ($status === 'blocked' || $status === 'inactive') {
            return new WP_Error(
                'esavest_blocked',
                __('Your account is not active. Please contact support.', 'esavest')
            );
        }

        return $user;
    }
}
