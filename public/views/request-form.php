<?php
if (!defined('ABSPATH')) exit;
?>

<div class="esavest-request-wrapper">

    <h2 class="esavest-title">Request Construction Material</h2>

    <form method="post" enctype="multipart/form-data" class="esavest-request-form">

    <?php wp_nonce_field('esavest_request_submit', 'esavest_request_nonce'); ?>
    <input type="hidden" name="esavest_request_action" value="submit_request">



        <!-- MATERIAL SELECTION -->
        <div class="esavest-material-list">
            <?php foreach ($materials as $m):
                $img   = get_the_post_thumbnail_url($m->ID, 'medium') ?: 'https://via.placeholder.com/120';
                $sku   = get_post_meta($m->ID, '_esavest_material_sku', true);
                $unit  = get_post_meta($m->ID, '_esavest_material_unit', true);
                $types = get_the_terms($m->ID, 'esavest_material_type');
            ?>
                <label class="esavest-material-card">
                    <input type="radio"   data-unit="<?php echo esc_attr($unit); ?>" name="material_id" value="<?php echo esc_attr($m->ID); ?>" required>

                    <div class="material-card-inner">
                        <div class="material-thumb">
                            <img src="<?php echo esc_url($img); ?>" alt="">
                        </div>

                        <div class="material-info">
                            <h4><?php echo esc_html($m->post_title); ?></h4>
                            <div class="meta">
                                <span><strong>SKU:</strong> <?php echo esc_html($sku); ?></span>
                                <span><strong>Unit:</strong> <?php echo esc_html($unit); ?></span>
                                <span><strong>Type:</strong>
                                    <?php echo $types ? esc_html($types[0]->name) : '—'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="material-check">
                            ✓
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- REQUEST DETAILS -->
        <div class="esavest-form-box">
            <div class="esavest-form-grid">
                <input type="number" step="0.01" name="request_qty" placeholder="Quantity" required>
                <input type="text" name="request_unit" placeholder="Unit (kg / pcs)" required>
                <input type="date" name="delivery_date" required>
                <input type="text" name="delivery_zip" placeholder="ZIP / PLZ" required>
                <textarea name="delivery_address" placeholder="Delivery Address" required></textarea>
                <textarea name="request_description" placeholder="Additional notes (optional)"></textarea>
                <!-- FILE UPLOAD -->
                <div class="esavest-file-upload">
                    <label class="esavest-label">Upload Reference File</label>

                    <input
                        type="file"
                        name="request_file"
                        id="esavest_request_file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.xls,.xlsx,.doc,.docx"
                    >

        
                    <!-- IMAGE PREVIEW -->
                    <div id="esavest-image-preview" class="esavest-image-preview" style="display:none;">
                        <img src="" alt="Preview">
                    </div>

                    <!-- FILE NAME (non-image) -->
                    <div id="esavest-file-name" class="esavest-file-name" style="display:none;"></div>
                </div>
            </div>




            <button type="submit" class="esavest-btn-submit">
                Submit Request
            </button>
        </div>

    </form>
</div>


