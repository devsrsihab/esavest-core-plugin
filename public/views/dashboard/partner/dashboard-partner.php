<?php if (!defined('ABSPATH')) exit;

$uid = get_current_user_id();
$user = wp_get_current_user();
$display_name = $user->display_name;

// Get current date
$current_date = date('l, d F Y');

// Counts from your functions
$total_offers = (int) esavest_count_partner_offers($uid);
$pending_offers = (int) esavest_count_partner_offers($uid, 'pending');
$accepted_offers = (int) esavest_count_partner_offers($uid, 'accepted');
?>

<div class="esavest-welcome">
    <h1>Hello, <?php echo esc_html($display_name); ?></h1>
    <p>Today is <?php echo esc_html($current_date); ?></p>
</div>

<!-- Main Stats Cards Grid -->
<div class="esavest-stats-grid">
    <!-- Card 1: Total Offers -->
    <div class="esavest-stat-card">
        <div class="esavest-stat-header">
            <div class="esavest-stat-icon total">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="esavest-stat-value"><?php echo esc_html($total_offers); ?></div>
        <div class="esavest-stat-label">Total Offers</div>
    </div>

    <!-- Card 2: Pending Offers -->
    <div class="esavest-stat-card">
        <div class="esavest-stat-header">
            <div class="esavest-stat-icon pending">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="esavest-stat-value"><?php echo esc_html($pending_offers); ?></div>
        <div class="esavest-stat-label">Pending Offers</div>
    </div>

    <!-- Card 3: Accepted Offers -->
    <div class="esavest-stat-card">
        <div class="esavest-stat-header">
            <div class="esavest-stat-icon accepted">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="esavest-stat-value"><?php echo esc_html($accepted_offers); ?></div>
        <div class="esavest-stat-label">Accepted Offers</div>
    </div>
</div>

<!-- Optional: Add more sections if needed -->
<div class="esavest-dashboard-actions">
    <h3>Quick Actions</h3>
    <div class="esavest-action-buttons">
        <a href="<?php echo esc_url(add_query_arg('tab', 'my-offers')); ?>" class="esavest-action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Create New Offer
        </a>
        <a href="<?php echo esc_url(add_query_arg('tab', 'requests')); ?>" class="esavest-action-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            View Requests
        </a>
    </div>
</div>