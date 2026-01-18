<?php
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    echo '<div class="es-card"><div class="es-empty"><div class="es-empty-title">Login required</div></div></div>';
    return;
}

if (!class_exists('ESAVEST_Core_CPT_Price')) {
    echo '<div class="es-card"><div class="es-empty"><div class="es-empty-title">Price module not loaded</div></div></div>';
    return;
}

$partner_id = get_current_user_id();
$page       = max(1, (int)($_GET['pg'] ?? 1));
$per_page   = 10;

$result = ESAVEST_Core_CPT_Price::get_prices_by_partner_paginated($partner_id, $page, $per_page);
$posts  = $result['posts'];
$total  = (int) $result['total'];
$pages  = (int) $result['pages'];
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">My Sent Prices</h2>
        <p class="es-page-sub">All quotations you have submitted (pagination enabled).</p>
    </div>
    <div class="es-head-actions">
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg(['tab'=>'requests'])); ?>">Back to Requests</a>
    </div>
</div>

<div class="es-card">
    <?php if (empty($posts)): ?>
        <div class="es-empty">
            <div class="es-empty-title">No prices sent yet</div>
            <div class="es-empty-sub">When you send prices for requests, they will appear here.</div>
        </div>
    <?php else: ?>
        <div class="es-table-wrap">
            <table class="es-table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Price</th>
                        <th>Note</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $p): ?>
                        <?php
                        $request_id = (int) get_post_meta($p->ID, ESAVEST_Core_CPT_Price::META_REQUEST_ID, true);
                        $amount     = get_post_meta($p->ID, ESAVEST_Core_CPT_Price::META_PRICE, true);
                        $note       = get_post_meta($p->ID, ESAVEST_Core_CPT_Price::META_NOTE, true);
                        $created_at = get_post_meta($p->ID, ESAVEST_Core_CPT_Price::META_CREATED_AT, true);

                        $request_title = $request_id ? get_the_title($request_id) : '—';
                        $open_send_page = $request_id ? add_query_arg(['tab'=>'send-price', 'request_id'=>$request_id]) : '#';
                        ?>
                        <tr>
                            <td>
                                <div class="es-title">
                                    <strong>#<?php echo (int)$request_id; ?></strong>
                                    <div class="es-muted"><?php echo esc_html($request_title); ?></div>
                                    <?php if ($request_id): ?>
                                        <div><a class="es-link" href="<?php echo esc_url($open_send_page); ?>">Open</a></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?php echo esc_html($amount); ?></strong></td>
                            <td><?php echo $note ? esc_html($note) : '—'; ?></td>
                            <td><?php echo $created_at ? esc_html($created_at) : esc_html(get_the_date('', $p)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <div class="es-pagination">
                <?php for ($i=1; $i<=$pages; $i++): ?>
                    <a class="es-page <?php echo $i===$page ? 'is-active' : ''; ?>"
                       href="<?php echo esc_url(add_query_arg(['tab'=>'sent-prices','pg'=>$i])); ?>">
                        <?php echo (int)$i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
