<?php
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    echo '<div class="es-card"><div class="es-empty"><div class="es-empty-title">Login required</div></div></div>';
    return;
}

$partner_id = get_current_user_id();
$request_id = isset($_GET['request_id']) ? (int) $_GET['request_id'] : 0;

if (!$request_id) {
    echo '<div class="es-card"><div class="es-empty"><div class="es-empty-title">Invalid request</div></div></div>';
    return;
}

$request = get_post($request_id);
if (!$request || $request->post_type !== 'esavest_request') {
    echo '<div class="es-card"><div class="es-empty"><div class="es-empty-title">Request not found</div></div></div>';
    return;
}

// Meta
$mid   = (int) get_post_meta($request_id, '_esavest_request_material_id', true);
$qty   = get_post_meta($request_id, '_esavest_request_qty', true);
$unit  = get_post_meta($request_id, '_esavest_request_unit', true);
$zip   = get_post_meta($request_id, '_esavest_request_delivery_zip', true);
$date  = get_post_meta($request_id, '_esavest_request_delivery_date', true);
$addr  = get_post_meta($request_id, '_esavest_request_delivery_address', true);
$desc  = get_post_meta($request_id, '_esavest_request_description', true);

$material_title = $mid ? get_the_title($mid) : '—';

$already_sent = class_exists('ESAVEST_Core_CPT_Price')
    ? ESAVEST_Core_CPT_Price::partner_already_sent_for_request($request_id, $partner_id)
    : false;
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">Send Price</h2>
        <p class="es-page-sub">Submit your quotation for this request (one time only).</p>
    </div>
    <div class="es-head-actions">
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg('tab','requests')); ?>">Back to Requests</a>
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg('tab','sent-prices')); ?>">My Sent Prices</a>
    </div>
</div>

<div class="es-card es-send-price-wrap">

    <!-- LEFT : REQUEST SUMMARY -->
    <div class="es-send-price-left">
        <h4 class="es-box-title">Request Summary</h4>

        <ul class="es-summary-list">
            <li><strong>Request:</strong> #<?php echo (int)$request_id; ?></li>
            <li><strong>Material:</strong> <?php echo esc_html($material_title); ?></li>
            <li><strong>Qty:</strong> <?php echo esc_html($qty); ?> <?php echo esc_html($unit); ?></li>
            <li><strong>ZIP:</strong> <?php echo esc_html($zip ?: '—'); ?></li>
            <li><strong>Delivery:</strong> <?php echo esc_html($date ?: '—'); ?></li>
        </ul>

        <?php if ($addr): ?>
            <div class="es-summary-block">
                <strong>Address</strong>
                <p><?php echo nl2br(esc_html($addr)); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($desc): ?>
            <div class="es-summary-block">
                <strong>Notes</strong>
                <p><?php echo nl2br(esc_html($desc)); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT : FORM -->
    <div class="es-send-price-right">
        <h4 class="es-box-title">Your Price</h4>

        <?php if ($already_sent): ?>
                <div class="es-price-status-card es-price-locked">
                <div class="es-price-icon">🔒</div>
                <h3>Price Already Sent</h3>
                <p>You have already submitted your price for this request.</p>

                <a class="es-btn es-btn-primary"
                    href="<?php echo esc_url(add_query_arg('tab','send-prices', site_url('/account/'))); ?>">
                    View My Sent Prices
                </a>
                </div>

        <?php else: ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="es-send-price-form">
                <input type="hidden" name="action" value="esavest_partner_send_price">
                <input type="hidden" name="request_id" value="<?php echo (int)$request_id; ?>">
                <?php wp_nonce_field('esavest_partner_send_price_action','esavest_partner_send_price_nonce'); ?>

                <div class="es-form-group">
                    <label class="es-label">Price Amount</label>
                    <input class="es-input" type="number" step="0.01" name="partner_price" placeholder="e.g. 1200.50" required>
                </div>

                <div class="es-form-group">
                    <label class="es-label">Currency / Unit</label>
                    <input class="es-input" type="text" name="price_unit" placeholder="e.g. USD / ton">
                </div>

                <div class="es-form-group">
                    <label class="es-label">Message / Note</label>
                    <textarea class="es-input" name="partner_note" rows="4" placeholder="Delivery, terms, etc."></textarea>
                </div>

                <div class="es-form-actions">
                    <button type="submit" class="es-btn es-btn-primary">Send Price</button>
                    <a href="<?php echo esc_url(add_query_arg('tab','requests')); ?>" class="es-btn es-btn-light">Cancel</a>
                </div>
            </form>

        <?php endif; ?>
    </div>

</div>
