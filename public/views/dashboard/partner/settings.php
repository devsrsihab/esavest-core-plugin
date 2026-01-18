<?php
if (!defined('ABSPATH')) exit;

$uid = get_current_user_id();
$notice = '';

if (!empty($_POST['esavest_save_settings']) && check_admin_referer('esavest_partner_settings')) {

    $notif = !empty($_POST['notifications']) ? '1' : '0';
    $theme = sanitize_text_field($_POST['theme'] ?? 'light');

    update_user_meta($uid, '_esavest_notify', $notif);
    update_user_meta($uid, '_esavest_theme', $theme);

    wp_safe_redirect(add_query_arg(['tab'=>'settings','saved'=>'1']));
    exit;
}

if (!empty($_GET['saved'])) {
    $notice = '<div class="es-alert es-alert-success">Settings saved.</div>';
}

$notif = get_user_meta($uid, '_esavest_notify', true);
$theme = get_user_meta($uid, '_esavest_theme', true) ?: 'light';
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">Settings</h2>
        <p class="es-page-sub">Configure dashboard preferences.</p>
    </div>
</div>

<?php echo $notice; ?>

<form method="post" class="es-card">
    <?php wp_nonce_field('esavest_partner_settings'); ?>

    <div class="es-form-grid">
        <div class="es-form-area">

            <div class="es-setting-row">
                <div>
                    <div class="es-setting-title">Notifications</div>
                    <div class="es-setting-sub">Enable dashboard notifications (future ready).</div>
                </div>
                <label class="es-switch">
                    <input type="checkbox" name="notifications" value="1" <?php checked($notif, '1'); ?>>
                    <span class="es-slider"></span>
                </label>
            </div>

            <div class="es-setting-row">
                <div>
                    <div class="es-setting-title">Theme</div>
                    <div class="es-setting-sub">Light/Dark (future ready via CSS variables).</div>
                </div>
                <select class="es-input" name="theme" style="max-width:220px;">
                    <option value="light" <?php selected($theme, 'light'); ?>>Light</option>
                    <option value="dark"  <?php selected($theme, 'dark'); ?>>Dark</option>
                </select>
            </div>

            <div class="es-form-actions">
                <button type="submit" name="esavest_save_settings" value="1" class="es-btn es-btn-primary">Save Settings</button>
            </div>

        </div>

        <div class="es-form-help">
            <div class="es-help-title">Note</div>
            <div class="es-help-sub">
                These settings are stored per-user in user_meta. Later we can expand with security, 2FA, etc.
            </div>
        </div>
    </div>
</form>
