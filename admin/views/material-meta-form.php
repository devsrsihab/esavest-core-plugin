<?php
if (!defined('ABSPATH')) exit;
?>

<div class="esavest-admin">
    <div class="esavest-card">

        <div class="esavest-grid">

            <div class="esavest-col-6">
                <label class="esavest-label">Default Unit</label>
                <input
                    type="text"
                    name="esavest_material_unit"
                    class="esavest-input"
                    placeholder="kg / ton / pcs"
                    value="<?php echo esc_attr($data['unit']); ?>">
                <p class="esavest-text-muted">
                    This is the suggested unit. Customers may override it in requests.
                </p>
            </div>

            <div class="esavest-col-6">
                <label class="esavest-label">Material SKU</label>
                <input
                    type="text"
                    name="esavest_material_sku"
                    class="esavest-input"
                    placeholder="SKU-001"
                    value="<?php echo esc_attr($data['sku']); ?>">
            </div>

            <div class="esavest-col-6">
                <label class="esavest-label">Status</label>
                <select name="esavest_material_status" class="esavest-select">
                    <option value="active" <?php selected($data['status'], 'active'); ?>>Active</option>
                    <option value="inactive" <?php selected($data['status'], 'inactive'); ?>>Inactive</option>
                </select>
                <p class="esavest-text-muted">
                    Only active materials will be available for requests.
                </p>
            </div>

        </div>

    </div>
</div>
