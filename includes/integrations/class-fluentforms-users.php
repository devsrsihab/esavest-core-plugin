<?php
if (!defined('ABSPATH')) exit;

class ESAVEST_FluentForms_Users {

    // আপনার Fluent Form ID (স্ক্রিনশট অনুযায়ী)
    const FORM_CUSTOMER = 4; // Register From (Customer)
    const FORM_PARTNER  = 7; // Partner Register

    // Partner approval meta
    const META_PARTNER_APPROVED = '_esavest_partner_approved'; // 1/0
    const META_PARTNER_STATUS   = '_esavest_partner_status';   // pending/approved/rejected

    public static function init() {

        // 1) Fluent Forms submit hook (সবচেয়ে common)
        add_action('fluentform/submission_inserted', [__CLASS__, 'on_submission_inserted'], 10, 3);

        // 2) কিছু ইনস্টলে এই hook-ও থাকে (safe fallback)
        add_action('fluentform_submission_inserted', [__CLASS__, 'on_submission_inserted_fallback'], 10, 3);

        // 3) Partner pending হলে login ব্লক
        add_filter('wp_authenticate_user', [__CLASS__, 'block_unapproved_partner_login'], 10, 2);

        // 4) Admin approve UI (Users > Edit User)
        // add_action('show_user_profile', [__CLASS__, 'partner_approval_field']);
        // add_action('edit_user_profile', [__CLASS__, 'partner_approval_field']);
        add_action('personal_options_update', [__CLASS__, 'save_partner_approval_field']);
        add_action('edit_user_profile_update', [__CLASS__, 'save_partner_approval_field']);
    }

    /**
     * Hook: fluentform/submission_inserted
     * Usually params: ($entryId, $formData, $form)
     */
    public static function on_submission_inserted($entryId, $formData, $form) {
        $form_id = is_object($form) && !empty($form->id) ? (int) $form->id : 0;
        self::handle_form($form_id, $formData, $entryId);
    }

    /**
     * Fallback hook (signature differs across versions)
     */
    public static function on_submission_inserted_fallback($entryId, $formData, $form) {
        $form_id = is_object($form) && !empty($form->id) ? (int) $form->id : 0;
        self::handle_form($form_id, $formData, $entryId);
    }

    private static function handle_form($form_id, $data, $entryId = 0) {

        if (!$form_id) return;

        // DEBUG (প্রয়োজনে দেখবেন data keys কি আসছে)
        error_log('===== ESAVEST FF SUBMISSION =====');
        error_log('Form ID: ' . $form_id);
        error_log('Entry ID: ' . (int)$entryId);
        error_log('Data Keys: ' . print_r(array_keys((array)$data), true));
        error_log('=================================');

        if ($form_id === self::FORM_CUSTOMER) {
            self::create_customer_user($data);
            return;
        }

        if ($form_id === self::FORM_PARTNER) {
            self::create_partner_user_pending($data);
            return;
        }
    }

    /**
     * Customer: auto create user + role esavest_customer
     */
    private static function create_customer_user($data) {

        $email = self::pick($data, ['email', 'email_address', 'e_mail', 'e-mail', 'e_mail_addresse']);
        $pass  = self::pick($data, ['password', 'user_password', 'pass']);
        $fname = self::pick($data, ['first_name', 'firstname', 'fname']);
        $lname = self::pick($data, ['last_name', 'lastname', 'lname']);

        $email = sanitize_email($email);
        if (!$email || !is_email($email)) return;

        // Already user?
        if (email_exists($email)) return;

        if (!$pass) {
            $pass = wp_generate_password(12, true);
        }

        $user_id = wp_insert_user([
            'user_login'   => $email,
            'user_email'   => $email,
            'user_pass'    => (string) $pass,
            'first_name'   => sanitize_text_field($fname),
            'last_name'    => sanitize_text_field($lname),
            'display_name' => trim(sanitize_text_field($fname . ' ' . $lname)) ?: $email,
            'role'         => 'esavest_customer',
        ]);

        if (is_wp_error($user_id) || !$user_id) return;

        // Optional: welcome email
        wp_mail(
            $email,
            'Welcome to ESAVEST',
            "Your account has been created.\n\nLogin Email: {$email}"
        );
    }

    /**
     * Partner: create user কিন্তু pending approval + login blocked until approve
     */
    private static function create_partner_user_pending($data) {

        $email   = self::pick($data, ['email', 'e_mail', 'e_mail_addresse']);
        $pass    = self::pick($data, ['password', 'user_password', 'pass']);
        $fname   = self::pick($data, ['first_name', 'firstname', 'fname']);
        $lname   = self::pick($data, ['last_name', 'lastname', 'lname']);
        $company = self::pick($data, ['company_name', 'company', 'business_name']);

        // Logo/file upload field name আপনার ফর্মে যেটা আছে সেটাতে মিলিয়ে দিন
        $logo    = self::pick($data, ['company_logo', 'logo', 'company_logo_upload']);

        $email = sanitize_email($email);
        if (!$email || !is_email($email)) return;

        // Already user?
        if (email_exists($email)) return;

        if (!$pass) {
            $pass = wp_generate_password(12, true);
        }

        $user_id = wp_insert_user([
            'user_login'   => $email,
            'user_email'   => $email,
            'user_pass'    => (string) $pass,
            'first_name'   => sanitize_text_field($fname),
            'last_name'    => sanitize_text_field($lname),
            'display_name' => trim(sanitize_text_field($company ?: ($fname . ' ' . $lname))) ?: $email,
            'role'         => 'esavest_partner',
        ]);

        if (is_wp_error($user_id) || !$user_id) return;

        // Partner pending meta
        update_user_meta($user_id, self::META_PARTNER_APPROVED, 0);
        update_user_meta($user_id, self::META_PARTNER_STATUS, 'pending');

        if ($company) {
            update_user_meta($user_id, '_esavest_partner_company', sanitize_text_field($company));
        }

        // Logo url store (Fluent Forms file upload অনেক সময় array/json দেয়)
        if ($logo) {
            update_user_meta($user_id, '_esavest_partner_logo', maybe_serialize($logo));
        }

        // Notify admin
        $admin_email = get_option('admin_email');
        wp_mail(
            $admin_email,
            'New Partner Registration (Pending)',
            "A new partner registered:\n\nEmail: {$email}\nCompany: {$company}\n\nApprove from: Users > Edit User"
        );

        // Partner message
        wp_mail(
            $email,
            'Partner registration received',
            "Thanks! Your partner account is created but needs admin approval before login."
        );
    }

    /**
     * Block partner login until approved
     */
    public static function block_unapproved_partner_login($user, $password) {
        if (is_wp_error($user)) return $user;

        if (!($user instanceof WP_User)) return $user;

        if (in_array('esavest_partner', (array)$user->roles, true)) {
            $approved = (int) get_user_meta($user->ID, self::META_PARTNER_APPROVED, true);
            if ($approved !== 1) {
                return new WP_Error(
                    'esavest_partner_pending',
                    __('Your partner account is pending admin approval.', 'esavest')
                );
            }
        }

        return $user;
    }

    /**
     * Admin UI: partner approval checkbox
     */
    public static function partner_approval_field($user) {

        if (!current_user_can('manage_options')) return;

        $is_partner = in_array('esavest_partner', (array)$user->roles, true);
        if (!$is_partner) return;

        $approved = (int) get_user_meta($user->ID, self::META_PARTNER_APPROVED, true);
        $status   = (string) get_user_meta($user->ID, self::META_PARTNER_STATUS, true);
        if (!$status) $status = $approved ? 'approved' : 'pending';
        ?>
        <h2>ESAVEST Partner Approval</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="esavest_partner_approved">Approved</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="esavest_partner_approved" id="esavest_partner_approved" value="1" <?php checked($approved, 1); ?>>
                        Allow this partner to login
                    </label>
                    <p class="description">If unchecked, partner cannot login.</p>
                    <p class="description">Current status: <strong><?php echo esc_html($status); ?></strong></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_partner_approval_field($user_id) {

        if (!current_user_can('manage_options')) return;

        $user = get_user_by('id', (int)$user_id);
        if (!$user) return;

        if (!in_array('esavest_partner', (array)$user->roles, true)) return;

        $approved = isset($_POST['esavest_partner_approved']) ? 1 : 0;
        update_user_meta($user_id, self::META_PARTNER_APPROVED, $approved);
        update_user_meta($user_id, self::META_PARTNER_STATUS, $approved ? 'approved' : 'pending');
    }

    /**
     * Helper: pick value from possible field keys
     */
    private static function pick($data, $keys) {
        $arr = (array) $data;
        foreach ($keys as $k) {
            if (isset($arr[$k]) && $arr[$k] !== '') return $arr[$k];
        }
        return '';
    }
}
