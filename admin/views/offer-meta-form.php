<?php
if (!defined('ABSPATH')) exit;
?>


<div class="esavest-admin esavest-container">

    <div class="esavest-card">

        <div class="esavest-grid">

            <!-- Request -->
            <div class="esavest-col-6 esavest-form-group">
                <label class="esavest-label">Request</label>
            <select name="esavest_offer_request_id" class="esavest-select">
                <option value="">— Select Request —</option>
                <?php if (!empty($data['requests'])): ?>
                    <?php foreach ($data['requests'] as $rid => $rlabel): ?>
                        <option value="<?php echo esc_attr($rid); ?>" <?php selected($data['request_id'], $rid); ?>>
                            <?php echo esc_html($rlabel); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

                <p class="esavest-text-muted">Offer must be linked to a Request.</p>
            </div>

            <!-- Status -->
            <div class="esavest-col-6 esavest-form-group">
                <label class="esavest-label">Status</label>
                <select name="esavest_offer_status" class="esavest-select">
                    <?php foreach (ESAVEST_Core_CPT_Offer::allowed_statuses() as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>"
                            <?php selected($data['status'], $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Customer -->
            <div class="esavest-col-6 esavest-form-group">
                <label class="esavest-label">Customer</label>
                <select name="esavest_offer_customer_id" class="esavest-select">
                    <option value="">— Select Customer —</option>
                    <?php foreach ($data['customers'] as $user): ?>
                        <option value="<?php echo esc_attr($user->ID); ?>"
                            <?php selected($data['customer_id'], $user->ID); ?>>
                            <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="esavest-text-muted">Customer will see only final price.</p>
            </div>

            <!-- Partner -->
            <div class="esavest-col-6 esavest-form-group">
                <label class="esavest-label">Partner</label>
                <select name="esavest_offer_partner_id" class="esavest-select">
                    <option value="">— Select Partner —</option>
                    <?php foreach ($data['partners'] as $user): ?>
                        <option value="<?php echo esc_attr($user->ID); ?>"
                            <?php selected($data['partner_id'], $user->ID); ?>>
                            <?php echo esc_html($user->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="esavest-text-muted">Partner sees internal price only.</p>
            </div>

            <!-- Partner Price -->
            <div class="esavest-col-6 esavest-form-group">
                <label class="esavest-label">Partner Price (Hidden)</label>
                <input type="number" step="0.01"
                       name="esavest_offer_partner_price"
                       class="esavest-input"
                       value="<?php echo esc_attr($data['partner_price']); ?>">
            </div>

            <!-- Final Price -->
            <div class="esavest-col-6 esavest-form-group">
                <label class="esavest-label">Final Price (Customer)</label>
                <input type="number" step="0.01"
                       name="esavest_offer_final_price"
                       class="esavest-input"
                       value="<?php echo esc_attr($data['final_price']); ?>">
            </div>

            <!-- Notes -->
            <div class="esavest-col-12 esavest-form-group">
                <label class="esavest-label">Offer Notes / Message</label>
                <textarea name="esavest_offer_notes"
                          rows="4"
                          class="esavest-textarea"
                          placeholder="Visible to customer (no partner prices)">
                    <?php echo esc_textarea($data['note']); ?>
                </textarea>
            </div>

        </div>

    </div>

</div>
