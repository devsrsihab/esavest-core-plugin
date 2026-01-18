<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_User_Status {

    public static function init() {
        add_action('show_user_profile', [self::class, 'profile_field']);
        add_action('edit_user_profile', [self::class, 'profile_field']);
        add_action('personal_options_update', [self::class, 'save_status']);
        add_action('edit_user_profile_update', [self::class, 'save_status']);
    }

    public static function profile_field($user) {
        if (!current_user_can('manage_options')) return;

        $status = get_user_meta($user->ID, '_esavest_user_status', true) ?: 'pending';
        ?>
        <h3>ESAVEST Account Status</h3>
        <table class="form-table">
            <tr>
                <th>Status</th>
                <td>
                    <select name="esavest_user_status">
                        <option value="active"   <?php selected($status, 'active'); ?>>Active</option>
                        <option value="pending"  <?php selected($status, 'pending'); ?>>Pending</option>
                        <option value="inactive" <?php selected($status, 'inactive'); ?>>Inactive</option>
                        <option value="blocked"  <?php selected($status, 'blocked'); ?>>Blocked</option>
                    </select>
                    <p class="description">
                        Active = full access<br>
                        Pending = dashboard only<br>
                        Inactive / Blocked = no access
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_status($user_id) {
        if (!current_user_can('manage_options')) return;
        if (!isset($_POST['esavest_user_status'])) return;

        update_user_meta(
            $user_id,
            '_esavest_user_status',
            sanitize_text_field($_POST['esavest_user_status'])
        );
    }
}
