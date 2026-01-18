<?php if (!defined('ABSPATH')) exit; ?>

<div class="esavest-admin">

    <?php if (!empty($_GET['es_price_added'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Price selected successfully. You can now send offer.</p>
        </div>
    <?php endif; ?>

    <!-- ================= Selected Price Form ================= -->
    <div class="esavest-card esavest-mb-md">
        <h4 class="esavest-mb-md">Selected Partner Price</h4>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="esavest_add_price_list">
            <input type="hidden" name="request_id" value="<?php echo esc_attr($post->ID); ?>">
            <?php wp_nonce_field('esavest_add_price_list_action', 'esavest_add_price_list_nonce'); ?>

            <!-- auto-filled -->
            <input type="hidden" name="partner_id" id="es_selected_partner_id">

            <div class="esavest-grid">
                <div class="esavest-col-6 esavest-form-group">
                    <label class="esavest-label">Partner</label>
                    <input type="text"
                           id="es_selected_partner_name"
                           class="esavest-input"
                           placeholder="Select from below list"
                           readonly>
                </div>

                <div class="esavest-col-6 esavest-form-group">
                    <label class="esavest-label">Partner Price</label>
                    <input type="number"
                           step="0.01"
                           name="partner_price"
                           id="es_selected_partner_price"
                           class="esavest-input"
                           placeholder="Select price from below"
                           required>
                </div>

                <div class="esavest-col-12 esavest-form-group">
                    <label class="esavest-label">Note (optional)</label>
                    <textarea name="partner_note"
                              id="es_selected_partner_note"
                              class="esavest-textarea"
                              rows="3"
                              placeholder="Optional note"></textarea>
                </div>
            </div>

            <button type="submit" class="esavest-btn esavest-btn-primary">
                Send Offer to Customer
            </button>
        </form>
    </div>

    <!-- ================= Received Prices ================= -->
    <div class="esavest-card">
        <h4 class="esavest-mb-md">Received Price Lists</h4>

        <?php if (empty($prices)): ?>
            <p class="esavest-text-muted">No price lists submitted yet.</p>
        <?php else: ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Partner</th>
                    <th>Price</th>
                    <th>Note</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($prices as $price_post): ?>
                    <?php
                    $partner_id = (int) get_post_meta($price_post->ID, ESAVEST_Core_CPT_Price::META_PARTNER_ID, true);
                    $amount     = get_post_meta($price_post->ID, ESAVEST_Core_CPT_Price::META_PRICE, true);
                    $note       = get_post_meta($price_post->ID, ESAVEST_Core_CPT_Price::META_NOTE, true);
                    $created_at = get_post_meta($price_post->ID, ESAVEST_Core_CPT_Price::META_CREATED_AT, true);

                    $u = $partner_id ? get_user_by('id', $partner_id) : null;
                    $partner_name = $u ? $u->display_name : '—';
                    ?>
                    <tr>
                        <td><?php echo esc_html($partner_name); ?></td>
                        <td><strong><?php echo esc_html($amount); ?></strong></td>
                        <td><?php echo $note ? esc_html($note) : '—'; ?></td>
                        <td><?php echo esc_html($created_at); ?></td>
                        <td>
                            <button
                                type="button"
                                class="button es-use-price"
                                data-partner-id="<?php echo (int)$partner_id; ?>"
                                data-partner-name="<?php echo esc_attr($partner_name); ?>"
                                data-price="<?php echo esc_attr($amount); ?>"
                                data-note="<?php echo esc_attr($note); ?>">
                                Use this price
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<!-- ================= JS ================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    let resetTimer = null;

    document.addEventListener('click', function (e) {

        const btn = e.target.closest('.es-use-price');
        if (!btn) return;

        // =========================
        // Fill upper form fields
        // =========================
        document.getElementById('es_selected_partner_id').value =
            btn.dataset.partnerId || '';

        document.getElementById('es_selected_partner_name').value =
            btn.dataset.partnerName || '';

        document.getElementById('es_selected_partner_price').value =
            btn.dataset.price || '';

        document.getElementById('es_selected_partner_note').value =
            btn.dataset.note || '';

        // =========================
        // Reset all buttons first
        // =========================
        document.querySelectorAll('.es-use-price').forEach(b => {
            b.classList.remove('button-secondary', 'es-selected');
            b.classList.add('button-primary');
            b.innerText = 'Use this price';
        });

        // =========================
        // Mark current button selected
        // =========================
        btn.classList.remove('button-primary');
        btn.classList.add('button-secondary', 'es-selected');
        btn.innerText = 'Selected ✓';

        // =========================
        // Auto reset after 2.5 sec
        // =========================
        if (resetTimer) clearTimeout(resetTimer);

        resetTimer = setTimeout(() => {
            if (btn.classList.contains('es-selected')) {
                btn.classList.remove('button-secondary', 'es-selected');
                btn.classList.add('button-primary');
                btn.innerText = 'Use this price';
            }
        }, 6500);
    });

});
</script>


<!-- ================= Optional UX CSS ================= -->
<style>
.es-use-price.is-selected {
    font-weight: 600;
}
.es-use-price.es-selected {
    background-color: #46b450 !important;
    border-color: #46b450 !important;
    color: #fff !important;
    font-weight: 600;
}

</style>
