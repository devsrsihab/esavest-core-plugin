<?php
if (!defined('ABSPATH')) exit;

$uid = get_current_user_id();

$post_type = 'esavest_offer';
$meta_partner = '_esavest_offer_partner_id';
$meta_status  = '_esavest_offer_status';
$meta_request = '_esavest_offer_request_id';
$meta_final   = '_esavest_offer_final_price';

$filter = sanitize_key($_GET['status'] ?? 'all');
$allowed = ['all','pending','accepted','rejected'];
if (!in_array($filter, $allowed, true)) $filter = 'all';

$meta_query = [
    [
        'key'   => $meta_partner,
        'value' => $uid,
        'compare' => '='
    ]
];

if ($filter !== 'all') {
    $meta_query[] = [
        'key'   => $meta_status,
        'value' => $filter,
        'compare' => '='
    ];
}

$q = new WP_Query([
    'post_type'      => $post_type,
    'post_status'    => 'any',
    'posts_per_page' => 20,
    'paged'          => max(1, (int)($_GET['pg'] ?? 1)),
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => $meta_query,
]);

function esavest_badge_class($status){
    if ($status === 'accepted') return 'es-badge es-badge-success';
    if ($status === 'rejected') return 'es-badge es-badge-danger';
    return 'es-badge es-badge-warning';
}
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">My Offers</h2>
        <p class="es-page-sub">Manage your submitted offers and track status.</p>
    </div>

    <div class="es-head-actions">
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg(['tab'=>'my-offers','status'=>'all'])); ?>">All</a>
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg(['tab'=>'my-offers','status'=>'pending'])); ?>">Pending</a>
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg(['tab'=>'my-offers','status'=>'accepted'])); ?>">Accepted</a>
        <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg(['tab'=>'my-offers','status'=>'rejected'])); ?>">Rejected</a>
    </div>
</div>

<div class="es-card">
    <?php if ($q->have_posts()): ?>
        <div class="es-table-wrap">
            <table class="es-table">
                <thead>
                    <tr>
                        <th>Offer</th>
                        <th>Request</th>
                        <th>Final Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="es-col-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($q->have_posts()): $q->the_post();
                    $offer_id  = get_the_ID();
                    $rid       = (int) get_post_meta($offer_id, $meta_request, true);
                    $status    = (string) get_post_meta($offer_id, $meta_status, true);
                    $status    = $status ?: 'pending';
                    $final     = get_post_meta($offer_id, $meta_final, true);
                    $final_txt = ($final !== '' && $final !== null) ? $final : '—';
                    ?>
                    <tr>
                        <td>
                            <div class="es-title">
                                <strong>#<?php echo (int)$offer_id; ?></strong>
                                <div class="es-muted"><?php echo esc_html(get_the_title($offer_id)); ?></div>
                            </div>
                        </td>
                        <td>
                            <?php if ($rid): ?>
                                <span class="es-pill">Request #<?php echo (int)$rid; ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($final_txt); ?></strong></td>
                        <td><span class="<?php echo esc_attr(esavest_badge_class($status)); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                        <td><?php echo esc_html(get_the_date('', $offer_id)); ?></td>
                        <td class="es-col-actions">
                            <a class="es-link" href="<?php echo esc_url(get_edit_post_link($offer_id)); ?>">View</a>
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
                       href="<?php echo esc_url(add_query_arg(['tab'=>'my-offers','status'=>$filter,'pg'=>$i])); ?>">
                        <?php echo (int)$i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="es-empty">
            <div class="es-empty-title">No offers found</div>
            <div class="es-empty-sub">When you submit offers, they will appear here.</div>
            <a class="es-btn es-btn-primary" href="<?php echo esc_url(add_query_arg('tab','requests')); ?>">View Requests</a>
        </div>
    <?php endif; ?>
</div>
