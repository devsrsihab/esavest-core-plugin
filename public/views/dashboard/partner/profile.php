<?php
if (!defined('ABSPATH')) exit;

$uid  = get_current_user_id();
$user = wp_get_current_user();

$notice = '';

if (!empty($_POST['esavest_save_profile']) && check_admin_referer('esavest_partner_profile')) {
    $display = sanitize_text_field($_POST['display_name'] ?? $user->display_name);
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $company = sanitize_text_field($_POST['company'] ?? '');
    $address = sanitize_textarea_field($_POST['address'] ?? '');

    wp_update_user([
        'ID'           => $uid,
        'display_name' => $display,
    ]);

    update_user_meta($uid, '_esavest_phone', $phone);
    update_user_meta($uid, '_esavest_company', $company);
    update_user_meta($uid, '_esavest_address', $address);

    wp_safe_redirect(add_query_arg(['tab'=>'profile','saved'=>'1']));
    exit;
}

if (!empty($_GET['saved'])) {
    $notice = '<div class="es-alert es-alert-success">Profile updated.</div>';
}

$phone   = get_user_meta($uid, '_esavest_phone', true);
$company = get_user_meta($uid, '_esavest_company', true);
$address = get_user_meta($uid, '_esavest_address', true);
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">Profile</h2>
        <p class="es-page-sub">Update your partner information.</p>
    </div>
</div>

<?php echo $notice; ?>

<form method="post" class="es-card">
    <?php wp_nonce_field('esavest_partner_profile'); ?>

    <div class="es-form-grid">
        <div class="es-form-area">
            <div class="es-field">
                <label class="es-label">Display Name</label>
                <input class="es-input" name="display_name" value="<?php echo esc_attr($user->display_name); ?>">
            </div>

            <div class="es-field es-field-2">
                <div>
                    <label class="es-label">Company</label>
                    <input class="es-input" name="company" value="<?php echo esc_attr($company); ?>" placeholder="Company name">
                </div>
                <div>
                    <label class="es-label">Phone</label>
                    <input class="es-input" name="phone" value="<?php echo esc_attr($phone); ?>" placeholder="+880...">
                </div>
            </div>

            <div class="es-field">
                <label class="es-label">Address</label>
                <textarea class="es-textarea" name="address" rows="4" placeholder="Full address"><?php echo esc_textarea($address); ?></textarea>
            </div>

            <div class="es-form-actions">
                <button type="submit" name="esavest_save_profile" value="1" class="es-btn es-btn-primary">Save Profile</button>
            </div>
        </div>

        <div class="es-form-help">
            <div class="es-help-title">Tip</div>
            <div class="es-help-sub">
                Keep your company & contact details updated so customers can reach you quickly after an offer is accepted.
            </div>
        </div>
    </div>
</form>
