<?php
if (!defined('ABSPATH')) exit;

// expected: $data['query'], $data['mode'], $data['labels']
?>

<div class="esavest-admin esavest-container">

    <div class="esavest-card">
        <h3 class="esavest-title">
            <?php echo ($data['mode'] === 'partner') ? 'My Offers (Partner)' : 'My Offers (Customer)'; ?>
        </h3>

        <?php if (!$data['query']->have_posts()): ?>
            <div class="esavest-card esavest-mt-md">No offers found.</div>
        <?php else: ?>

            <div class="esavest-table-wrap esavest-mt-md">
                <table class="esavest-table">
                    <thead>
                    <tr>
                        <th>Offer</th>
                        <th>Status</th>
                        <th>Final Price</th>
                        <?php if ($data['mode'] === 'partner'): ?>
                            <th>Partner Price</th>
                        <?php endif; ?>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php while ($data['query']->have_posts()): $data['query']->the_post(); ?>
                        <?php
                        $offer_id = get_the_ID();
                        $status   = get_post_meta($offer_id, ESAVEST_Core_CPT_Offer::META_STATUS, true) ?: 'pending';
                        $final    = get_post_meta($offer_id, ESAVEST_Core_CPT_Offer::META_FINAL_PRICE, true);
                        $partner  = get_post_meta($offer_id, ESAVEST_Core_CPT_Offer::META_PARTNER_PRICE, true);

                        $status_label = $data['labels'][$status] ?? $status;

                        // Single offer view link (shortcode uses query arg)
                        $view_url = add_query_arg(['esavest_offer_id' => $offer_id], remove_query_arg(['esavest_offer_id']));
                        ?>
                        <tr>
                            <td><?php echo esc_html(get_the_title()); ?></td>
                            <td><?php echo esc_html($status_label); ?></td>
                            <td><?php echo ($final !== '') ? esc_html($final) : '—'; ?></td>

                            <?php if ($data['mode'] === 'partner'): ?>
                                <td><?php echo ($partner !== '') ? esc_html($partner) : '—'; ?></td>
                            <?php endif; ?>

                            <td>
                                <a class="esavest-btn esavest-btn--primary" href="<?php echo esc_url($view_url); ?>">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>

</div>
