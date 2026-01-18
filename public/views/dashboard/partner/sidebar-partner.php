<?php if (!defined('ABSPATH')) exit;

$u = wp_get_current_user();
$status = get_user_meta($u->ID, '_esavest_user_status', true) ?: 'pending';

// Get initials for avatar
$initials = '';
$name_parts = explode(' ', $u->display_name);
if (count($name_parts) > 0) {
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (count($name_parts) > 1) {
        $initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
}

function esavest_dash_link($tab){
    return esc_url(add_query_arg('tab', $tab));
}

$current_tab = sanitize_key($_GET['tab'] ?? 'dashboard');
?>

<div class="esavest-sidebar-head">
    <div class="esavest-avatar"><?php echo esc_html($initials); ?></div>
    <div class="esavest-sidebar-user">
        <div class="name"><?php echo esc_html($u->display_name); ?></div>
        <div class="mail"><?php echo esc_html($u->user_email); ?></div>
        <span class="esavest-pill esavest-pill-<?php echo esc_attr($status); ?>">
            <?php echo esc_html(ucfirst($status)); ?>
        </span>
    </div>
</div>

<div class="esavest-dashboard-items">
    <div class="esavest-section-header">
        <h3 class="esavest-section-title">Dashboard</h3>
    </div>
    
    <a class="esavest-dashboard-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>" href="<?php echo esavest_dash_link('dashboard'); ?>">
        <div class="esavest-dashboard-item-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 13L12 2L21 13V22H3V13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="esavest-dashboard-item-title">Dashboard</div>
    </a>
    
    <a class="esavest-dashboard-item <?php echo $current_tab === 'my-offers' ? 'active' : ''; ?>" href="<?php echo esavest_dash_link('my-offers'); ?>">
        <div class="esavest-dashboard-item-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="esavest-dashboard-item-title">My Offers</div>
    </a>
    
    <a class="esavest-dashboard-item <?php echo $current_tab === 'requests' ? 'active' : ''; ?>" href="<?php echo esavest_dash_link('requests'); ?>">
        <div class="esavest-dashboard-item-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 13H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 17H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="esavest-dashboard-item-title">Requests</div>
    </a>
    
    <a class="esavest-dashboard-item <?php echo $current_tab === 'prices' ? 'active' : ''; ?>" href="<?php echo esavest_dash_link('prices'); ?>">
        <div class="esavest-dashboard-item-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1V23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6313 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6313 13.6815 18 14.5717 18 15.5C18 16.4283 17.6313 17.3185 16.9749 17.9749C16.3185 18.6313 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="esavest-dashboard-item-title">Price List</div>
    </a>
    
    <a class="esavest-dashboard-item <?php echo $current_tab === 'profile' ? 'active' : ''; ?>" href="<?php echo esavest_dash_link('profile'); ?>">
        <div class="esavest-dashboard-item-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="esavest-dashboard-item-title">Profile</div>
    </a>
    
    <a class="esavest-dashboard-item <?php echo $current_tab === 'settings' ? 'active' : ''; ?>" href="<?php echo esavest_dash_link('settings'); ?>">
        <div class="esavest-dashboard-item-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M19.4 15C19.2663 15.3054 19.1305 15.6088 18.9928 15.9102C18.9928 15.9102 18.9928 15.9102 18.9928 15.9102L21.7 19.7273L19.7273 21.7L15.9102 18.9928C15.6088 19.1305 15.3054 19.2663 19 19.4C18.6755 20.0605 18.3203 20.707 17.9354 21.3373L14.5 21.9C14.5 21.9 14.5 21.9 14.5 21.9L13.1 18.3C12.7355 18.2079 12.3675 18.1035 12 18.0016V21.5H10V18.0016C9.63251 18.1035 9.26445 18.2079 8.9 18.3L7.5 21.9H2.1L4.0646 21.3373C3.67974 20.707 3.32448 20.0605 3 19.4C2.69461 19.2663 2.39122 19.1305 2.08982 18.9928L0.3 21.7L2.2737 19.7273L5.0872 18.9928C5.0872 18.9928 5.0872 18.9928 5.0872 18.9928C4.86954 18.6088 4.73374 18.3054 4.6 18C3.93946 18.3245 3.29299 18.6797 2.66274 19.0646L2.1 14.5L6.7 13.1C6.79205 12.7355 6.89645 12.3675 6.99839 12H3.5V10H6.99839C7.10033 9.63251 7.20473 9.26445 7.3 8.9L2.1 7.5L2.66274 2.66274C3.29299 3.0476 3.93946 3.40286 4.6 3.7273C4.73374 3.42291 4.86954 3.11952 5.0872 2.08982L2.2737 0.3L0.3 2.2737L2.08982 5.0872C2.39122 4.86954 2.69461 4.73374 3 4.6C3.32448 3.93946 3.67974 3.29299 4.0646 2.66274L7.5 2.1L8.9 6.7C9.26445 6.79205 9.63251 6.89645 10 6.99839V3.5H12V6.99839C12.3675 6.89645 12.7355 6.79205 13.1 6.7L14.5 2.1L17.3373 2.66274C16.707 3.0476 16.0605 3.40286 15.4 3.7273C15.2663 3.42291 15.1305 3.11952 14.9128 2.08982L17.7263 0.3L19.7 2.2737L17.9102 5.0872C17.6088 4.86954 17.3054 4.73374 17 4.6C17.3245 3.93946 17.6797 3.29299 18.0646 2.66274L21.3373 2.66274L21.9 7.5L17.3 8.9C17.2079 9.26445 17.1035 9.63251 17.0016 10H20.5V12H17.0016C16.8997 12.3675 16.7953 12.7355 16.7 13.1L21.9 14.5L21.3373 17.3373C20.707 16.9524 20.0605 16.5971 19.4 16.2727Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="esavest-dashboard-item-title">Settings</div>
    </a>
</div>

<!-- Logout button at bottom -->
<div class="esavest-sidebar-footer">
    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="esavest-logout-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Logout
    </a>
</div>