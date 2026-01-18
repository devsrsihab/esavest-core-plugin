<?php
if (!defined('ABSPATH')) exit;

$post_type = 'esavest_request';
$meta_status   = '_esavest_request_status';
$meta_customer = '_esavest_request_customer_id';
$meta_qty      = '_esavest_request_qty';
$meta_unit     = '_esavest_request_unit';
$meta_zip      = '_esavest_request_delivery_zip';
$meta_date     = '_esavest_request_delivery_date';

$filter = sanitize_key($_GET['status'] ?? 'all');
$allowed = ['all','pending','publish','completed'];
if (!in_array($filter, $allowed, true)) $filter = 'all';

$args = [
    'post_type'      => $post_type,
    'post_status'    => 'publish',
    'posts_per_page' => 20,
    'paged'          => max(1, (int)($_GET['pg'] ?? 1)),
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$q = new WP_Query($args);

function esavest_req_badge($status){
    if ($status === 'completed') return 'es-badge es-badge-success';
    if ($status === 'pending') return 'es-badge es-badge-warning';
    return 'es-badge es-badge-light';
}
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">Requests</h2>
        <p class="es-page-sub">Browse customer requests and create offers.</p>
    </div>

    <div class="es-head-actions">
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg(['tab'=>'requests'])); ?>">Refresh</a>
    </div>
</div>

<div class="es-card">
    <?php if ($q->have_posts()): ?>
        <div class="es-table-wrap">
            <table class="es-table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Customer</th>
                        <th>Qty</th>
                        <th>ZIP</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th class="es-col-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($q->have_posts()): $q->the_post();
                    $rid      = get_the_ID();
                    $cust_id  = (int) get_post_meta($rid, $meta_customer, true);
                    $cust     = $cust_id ? get_user_by('id', $cust_id) : null;
                    $qty      = get_post_meta($rid, $meta_qty, true);
                    $unit     = get_post_meta($rid, $meta_unit, true);
                    $zip      = get_post_meta($rid, $meta_zip, true);
                    $d        = get_post_meta($rid, $meta_date, true);
                    $status   = (string) get_post_meta($rid, $meta_status, true);
                    $status   = $status ?: 'pending';
                    ?>
                    <tr>
                        <td>
                            <div class="es-title">
                                <strong>#<?php echo (int)$rid; ?></strong>
                                <div class="es-muted"><?php echo esc_html(get_the_title($rid)); ?></div>
                            </div>
                        </td>
                        <td><?php echo $cust ? esc_html($cust->display_name) : '—'; ?></td>
                        <td><strong><?php echo esc_html($qty ? $qty : '—'); ?></strong> <span class="es-muted"><?php echo esc_html($unit); ?></span></td>
                        <td><?php echo esc_html($zip ?: '—'); ?></td>
                        <td><?php echo esc_html($d ?: '—'); ?></td>
                        <td><span class="<?php echo esc_attr(esavest_req_badge($status)); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                        <td class="es-col-actions">
                            <a class="es-link" href="<?php echo esc_url(get_edit_post_link($rid)); ?>">View</a>
                            <span class="es-dot">•</span>
                            <a class="es-link" href="<?php echo esc_url(admin_url('post-new.php?post_type=esavest_offer')); ?>">Create Offer</a>
                        </td>
                    </tr>
                <?php endwhile; wp_reset_postdata(); ?>
                </tbody>
            </table>
        </div>

        <?php
        $total_pages = (int) $q->max_num_pages;
        $current     = max(1, (int)($_GET['pg'] ?? 1));
        if ($total_pages > 1): ?>
            <div class="es-pagination">
                <?php for ($i=1; $i<=$total_pages; $i++): ?>
                    <a class="es-page <?php echo $i===$current?'is-active':''; ?>"
                       href="<?php echo esc_url(add_query_arg(['tab'=>'requests','pg'=>$i])); ?>">
                        <?php echo (int)$i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="es-empty">
            <div class="es-empty-title">No requests available</div>
            <div class="es-empty-sub">When customers submit requests, they will show up here.</div>
        </div>
    <?php endif; ?>
</div>
