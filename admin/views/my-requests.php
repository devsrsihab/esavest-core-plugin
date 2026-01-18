<?php
if (!defined('ABSPATH')) exit;

$labels = (array) ($data['labels'] ?? []);
$q      = $data['query'];
?>

<div class="esavest-admin esavest-container">
    <div class="esavest-card">
        <h3 class="esavest-mb-md">My Requests</h3>

        <?php if (!$q->have_posts()): ?>
            <div class="esavest-text-muted">No requests found.</div>
        <?php else: ?>

            <div class="esavest-grid">
                <?php while ($q->have_posts()): $q->the_post(); ?>

                    <?php
                    $pid     = get_the_ID();
                    $status  = get_post_meta($pid, ESAVEST_Core_CPT_Request::META_STATUS, true) ?: 'pending';
                    $mid     = (int) get_post_meta($pid, ESAVEST_Core_CPT_Request::META_MATERIAL_ID, true);
                    $desc    = get_post_meta($pid, ESAVEST_Core_CPT_Request::META_DESCRIPTION, true);
                    $fileUrl = get_post_meta($pid, ESAVEST_Core_CPT_Request::META_FILE_URL, true);
                    ?>

                    <div class="esavest-col-12">
                        <div class="esavest-card">

                            <div class="esavest-mb-sm">
                                <strong><?php echo esc_html(get_the_title($mid)); ?></strong>
                                <span class="esavest-text-muted"> — <?php echo esc_html(get_the_date()); ?></span>
                            </div>

                            <div class="esavest-mb-sm">
                                <span class="esavest-badge <?php echo ($status === 'closed') ? 'esavest-badge-inactive' : 'esavest-badge-active'; ?>">
                                    <?php echo esc_html($labels[$status] ?? ucfirst($status)); ?>
                                </span>
                            </div>

                            <?php if (!empty($desc)): ?>
                                <div class="esavest-text-muted esavest-mb-md">
                                    <?php echo wp_kses_post(nl2br($desc)); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($fileUrl)): ?>
                                <a class="esavest-btn esavest-btn-secondary" href="<?php echo esc_url($fileUrl); ?>" target="_blank" rel="noopener">
                                    View File
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>

                <?php endwhile; ?>
            </div>

        <?php endif; ?>
    </div>
</div>
