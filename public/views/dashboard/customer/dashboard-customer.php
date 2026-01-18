<?php
if (!defined('ABSPATH')) exit;

$uid = get_current_user_id();
$user = wp_get_current_user();
$display_name = $user->display_name;
$current_date = date('l, d F Y');

// Counts
$total_requests = (int) esavest_count_requests_by_customer($uid);
$total_offers   = (int) esavest_count_offers_by_customer($uid);
?>

<div class="esavest-welcome">
    <h1>Hello, <?php echo esc_html($display_name); ?></h1>
    <p>Today is <?php echo esc_html($current_date); ?></p>
</div>

<!-- Main Stats Cards Grid -->
<div class="esavest-stats-grid">

    <!-- Card 1: Total Requests -->
    <div class="esavest-stat-card">
        <div class="esavest-stat-header">
            <div class="esavest-stat-icon total">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 4H20V20H4V4Z"
                          stroke="currentColor" stroke-width="2"/>
                    <path d="M4 9H20" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
        </div>
        <div class="esavest-stat-value"><?php echo esc_html($total_requests); ?></div>
        <div class="esavest-stat-label">Total Requests</div>
    </div>

    <!-- Card 2: Offers Received -->
    <div class="esavest-stat-card">
        <div class="esavest-stat-header">
            <div class="esavest-stat-icon pending">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2V12L18 15"
                          stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
        </div>
        <div class="esavest-stat-value"><?php echo esc_html($total_offers); ?></div>
        <div class="esavest-stat-label">Offers Received</div>
    </div>

</div>

<!-- Quick Actions -->
<div class="esavest-dashboard-actions">
    <h3>Quick Actions</h3>

    <div class="esavest-action-buttons">

        <a href="<?php echo esc_url(add_query_arg('tab', 'create-requests')); ?>"
           class="esavest-action-btn">
            Create New Request
        </a>

        <a href="<?php echo esc_url(add_query_arg('tab', 'my-requests')); ?>"
           class="esavest-action-btn">
            View My Requests
        </a>

        <a href="<?php echo esc_url(add_query_arg('tab', 'my-offers')); ?>"
           class="esavest-action-btn">
            View Offers
        </a>

    </div>
</div>
