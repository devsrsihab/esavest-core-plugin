<?php if (!defined('ABSPATH')) exit; ?>
<header class="esavest-topbar">
    <div class="esavest-topbar-left">
        <label for="esavest-sidebar-toggle" class="esavest-burger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </label>

        <div class="esavest-topbar-title">
            <?php echo esc_html(ucfirst($role ?? 'Dashboard')); ?> Dashboard
        </div>
    </div>

    <div class="esavest-topbar-right">
        <div class="esavest-user-pill">
            <?php echo esc_html(wp_get_current_user()->display_name); ?>
        </div>
        <a class="esavest-btn esavest-btn-secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">
            Logout
        </a>
    </div>
</header>
