<?php
if (!defined('ABSPATH')) exit;

/**
 * Simple partner price list storage:
 * user_meta: _esavest_partner_price_list (array)
 * [
 *   ['material' => 'Cement', 'unit' => 'bag', 'price' => '550'],
 * ]
 */
$uid = get_current_user_id();
$key = '_esavest_partner_price_list';

if (!empty($_POST['esavest_save_prices']) && check_admin_referer('esavest_partner_prices')) {
    $rows = $_POST['rows'] ?? [];
    $clean = [];

    if (is_array($rows)) {
        foreach ($rows as $r) {
            $m = sanitize_text_field($r['material'] ?? '');
            $u = sanitize_text_field($r['unit'] ?? '');
            $p = sanitize_text_field($r['price'] ?? '');

            if ($m === '' && $u === '' && $p === '') continue;

            $clean[] = [
                'material' => $m,
                'unit'     => $u,
                'price'    => $p,
            ];
        }
    }

    update_user_meta($uid, $key, $clean);
    wp_safe_redirect(add_query_arg(['tab'=>'prices','saved'=>'1']));
    exit;
}

$list = get_user_meta($uid, $key, true);
if (!is_array($list)) $list = [];

$notice = (!empty($_GET['saved'])) ? '<div class="es-alert es-alert-success">Price list saved.</div>' : '';
?>

<div class="es-page-head">
    <div>
        <h2 class="es-page-title">Price List</h2>
        <p class="es-page-sub">Manage your internal pricing (customers will not see this).</p>
    </div>
</div>

<?php echo $notice; ?>

<form method="post" class="es-card">
    <?php wp_nonce_field('esavest_partner_prices'); ?>

    <div class="es-form-grid">
        <div class="es-form-help">
            <div class="es-help-title">How it works</div>
            <div class="es-help-sub">
                Save your material wise base prices. Later we can auto-suggest offer price from here.
            </div>
        </div>

        <div class="es-form-area">
            <div class="es-table-wrap">
                <table class="es-table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Unit</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody id="es-price-rows">
                        <?php
                        $i = 0;
                        foreach ($list as $row):
                            ?>
                            <tr>
                                <td><input class="es-input" name="rows[<?php echo $i; ?>][material]" value="<?php echo esc_attr($row['material'] ?? ''); ?>" placeholder="e.g. Cement"></td>
                                <td><input class="es-input" name="rows[<?php echo $i; ?>][unit]" value="<?php echo esc_attr($row['unit'] ?? ''); ?>" placeholder="e.g. bag"></td>
                                <td><input class="es-input" name="rows[<?php echo $i; ?>][price]" value="<?php echo esc_attr($row['price'] ?? ''); ?>" placeholder="e.g. 550"></td>
                            </tr>
                            <?php
                            $i++;
                        endforeach;

                        for ($j=$i; $j<max(5,$i+2); $j++): ?>
                            <tr>
                                <td><input class="es-input" name="rows[<?php echo $j; ?>][material]" value="" placeholder="e.g. Cement"></td>
                                <td><input class="es-input" name="rows[<?php echo $j; ?>][unit]" value="" placeholder="e.g. bag"></td>
                                <td><input class="es-input" name="rows[<?php echo $j; ?>][price]" value="" placeholder="e.g. 550"></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="es-form-actions">
                <button type="submit" name="esavest_save_prices" value="1" class="es-btn es-btn-primary">Save Price List</button>
                <a class="es-btn es-btn-light" href="<?php echo esc_url(add_query_arg('tab','prices')); ?>">Reset</a>
            </div>
        </div>
    </div>
</form>
