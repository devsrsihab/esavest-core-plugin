<?php
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    echo '<div class="esavest-container"><div class="esavest-card">Please login.</div></div>';
    return;
}

$user  = wp_get_current_user();
$roles = (array) $user->roles;

// Detect role
$role = '';
if (in_array('esavest_customer', $roles, true)) $role = 'customer';
if (in_array('esavest_partner',  $roles, true)) $role = 'partner';

if (!$role) {
    echo '<div class="esavest-container"><div class="esavest-card">Access denied.</div></div>';
    return;
}

// Current tab
$tab = sanitize_key($_GET['tab'] ?? 'dashboard');

// Base path
$base = trailingslashit(ESAVEST_CORE_PATH . 'public/views/dashboard/');

// ✅ Tab -> filename mapping (role wise)
$tab_map = [
    'customer' => [
        'dashboard'   => 'dashboard-customer.php',
        'my-requests' => 'my-requests.php',
        'create-requests' => 'create-requests.php',
        'my-offers'   => 'my-offers.php',
        'profile'     => 'profile.php',
        'settings'    => 'settings.php',
    ],
    'partner' => [
        'dashboard' => 'dashboard-partner.php',
        'my-offers' => 'my-offers.php',
        // ✅ IMPORTANT: your sidebar shows "Requests" but file is usually my-requests.php
        // so ?tab=requests will load my-requests.php
        'requests'  => 'my-requests.php',
        'prices'    => 'prices.php',
        'profile'   => 'profile.php',
        'settings'  => 'settings.php',
    ],
];

// Allowed tab fallback
if (empty($tab_map[$role][$tab])) {
    $tab = 'dashboard';
}

// Resolve sidebar + content file
$sidebar_file = $base . $role . '/sidebar-' . $role . '.php';
$content_file = $base . $role . '/' . $tab_map[$role][$tab];

// For mobile sidebar toggle
?>
<div class="esavest-dashboard">

    <input type="checkbox" id="esavest-sidebar-toggle" class="esavest-sidebar-toggle" />

    <!-- Sidebar -->
    <aside class="esavest-sidebar">
        <?php
        if (file_exists($sidebar_file)) {
            include $sidebar_file;
        } else {
            echo '<div class="esavest-card">Sidebar not found: ' . esc_html($sidebar_file) . '</div>';
        }
        ?>
    </aside>

    <!-- Main -->
    <div class="esavest-main">

        <?php
        // topbar
        $topbar = $base . 'partials/topbar.php';
        if (file_exists($topbar)) include $topbar;
        ?>

        <section class="esavest-page">
            <?php
            if (file_exists($content_file)) {
                include $content_file;
            } else {
                echo '<div class="esavest-card">Content file not found: <strong>' . esc_html(basename($content_file)) . '</strong></div>';
            }
            ?>
        </section>

    </div>

    <label for="esavest-sidebar-toggle" class="esavest-overlay"></label>

</div>
