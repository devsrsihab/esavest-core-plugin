<?php
if (!defined('ABSPATH')) exit;
?>

<div class="esavest-admin">
    <div class="esavest-card">

        <!-- ================= CUSTOMER INFO ================= -->
        <h3 class="esavest-mb-md">Customer Information</h3>

        <div class="esavest-form-group esavest-mb-md">
            <?php
            $uid  = (int) ($data['customer_id'] ?? 0);
            $user = $uid ? get_user_by('id', $uid) : null;
            ?>
            <p><strong>Name:</strong> <?php echo $user ? esc_html($user->display_name) : '—'; ?></p>
            <p><strong>Email:</strong> <?php echo $user ? esc_html($user->user_email) : '—'; ?></p>
        </div>

        <hr class="esavest-mb-md">

        <!-- ================= REQUEST DETAILS ================= -->
        <h3 class="esavest-mb-md">Request Details</h3>

        <!-- Material + Status -->
        <div class="esavest-grid esavest-mb-md">
            <div class="esavest-col-6">
                <label class="esavest-label">Material</label>
                <select name="esavest_material_id" class="esavest-select">
                    <option value="">— Select Material —</option>
                    <?php foreach ($data['materials'] as $material): ?>
                        <option value="<?php echo esc_attr($material->ID); ?>"
                            <?php selected($data['material_id'], $material->ID); ?>>
                            <?php echo esc_html($material->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="esavest-col-6">
                <label class="esavest-label">Status</label>
                <select name="esavest_request_status" class="esavest-select">
                    <?php foreach (ESAVEST_Core_CPT_Request::allowed_statuses() as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>"
                            <?php selected($data['status'], $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Quantity / Unit / Date -->
        <div class="esavest-grid esavest-mb-md">
            <div class="esavest-col-4">
                <label class="esavest-label">Quantity</label>
                <input type="number" step="0.01"
                    name="esavest_request_qty"
                    class="esavest-input"
                    value="<?php echo esc_attr($data['qty']); ?>">
            </div>

            <div class="esavest-col-4">
                <label class="esavest-label">Unit</label>
                <input type="text"
                    name="esavest_request_unit"
                    class="esavest-input"
                    value="<?php echo esc_attr($data['unit']); ?>">
            </div>

            <div class="esavest-col-4">
                <label class="esavest-label">Delivery Date</label>
                <input type="date"
                    name="esavest_request_delivery_date"
                    class="esavest-input"
                    value="<?php echo esc_attr($data['delivery_date']); ?>">
            </div>
        </div>

        <!-- Address + ZIP -->
        <div class="esavest-grid esavest-mb-md">
            <div class="esavest-col-8">
                <label class="esavest-label">Delivery Address</label>
                <textarea name="esavest_request_delivery_address"
                    class="esavest-textarea"
                    rows="4"><?php echo esc_textarea($data['address']); ?></textarea>
            </div>

            <div class="esavest-col-4">
                <label class="esavest-label">ZIP / PLZ</label>
                <input type="text"
                    name="esavest_request_delivery_zip"
                    class="esavest-input"
                    value="<?php echo esc_attr($data['zip']); ?>">
            </div>
        </div>

        <!-- Description -->
        <div class="esavest-form-group esavest-mb-md">
            <label class="esavest-label">Request Description</label>
            <textarea name="esavest_request_description"
                class="esavest-textarea"
                rows="4"><?php echo esc_textarea($data['description']); ?></textarea>
        </div>

        <hr class="esavest-mb-md">

        

            <!-- ================= ATTACHMENT ================= -->
            <div class="esavest-col-12 esavest-form-group">
                <label class="esavest-label">Attachment</label>

                <?php
                $file_url = $data['file_url'] ?? '';
                $file_id  = get_post_meta(get_the_ID(), ESAVEST_Core_CPT_Request::META_FILE_ID, true);
                $mime     = $file_id ? get_post_mime_type($file_id) : '';
                ?>

                <?php if ($file_url): ?>

                    <!-- IMAGE PREVIEW -->
                    <?php if (in_array($mime, ['image/jpeg', 'image/png'], true)): ?>
                        <div class="esavest-mb-sm">
                            <img src="<?php echo esc_url($file_url); ?>"
                                style="max-width:220px; border:1px solid #e5e7eb; border-radius:6px;">
                        </div>
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"
                        class="esavest-btn esavest-btn-secondary">
                            View Full Image
                        </a>

                    <!-- PDF PREVIEW -->
                    <?php elseif ($mime === 'application/pdf'): ?>
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"
                        class="esavest-btn esavest-btn-secondary">
                            View PDF
                        </a>

                    <!-- EXCEL / DOC -->
                    <?php else: ?>
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"
                        class="esavest-btn esavest-btn-secondary">
                            Download File
                        </a>
                    <?php endif; ?>

                <?php else: ?>
                    <p class="esavest-text-muted">No file uploaded.</p>
                <?php endif; ?>

                <input type="file"
                    name="esavest_request_file"
                    class="esavest-input"
                    accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx,.doc,.docx">

                <p class="esavest-text-muted">
                    Allowed: JPG, PNG, PDF, Excel, Word
                </p>
            </div>


    </div>
</div>
