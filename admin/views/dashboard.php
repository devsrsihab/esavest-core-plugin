<?php
if (!defined('ABSPATH')) {
    exit;
}

$materials_count = esavest_core_get_cpt_count('esavest_material');
$requests_count  = esavest_core_get_cpt_count('esavest_request');
$offers_count    = esavest_core_get_cpt_count('esavest_offer');
?>

<div class="wrap esavest-core-dashboard">
    <div class="container-fluid px-0">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="fw-bold mb-1">ESAVEST Core Dashboard</h1>
                <p class="text-muted mb-0">
                    Quick overview of platform data.
                </p>
            </div>
        </div>

        <!-- Cards -->
        <div class="row g-4">

            <!-- Materials -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0">Materials</h5>
                            <i class="fa-solid fa-boxes-stacked fa-lg text-primary"></i>
                        </div>

                        <h2 class="fw-bold mb-2"><?php echo esc_html($materials_count); ?></h2>
                        <p class="text-muted mb-3">Total materials available</p>

                        <div class="d-flex gap-2">
                            <a href="<?php echo admin_url('edit.php?post_type=esavest_material'); ?>"
                               class="btn btn-outline-primary btn-sm">
                                View All
                            </a>
                            <a href="<?php echo admin_url('post-new.php?post_type=esavest_material'); ?>"
                               class="btn btn-primary btn-sm">
                                Add New
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0">Requests</h5>
                            <i class="fa-solid fa-clipboard-list fa-lg text-warning"></i>
                        </div>

                        <h2 class="fw-bold mb-2"><?php echo esc_html($requests_count); ?></h2>
                        <p class="text-muted mb-3">Customer material requests</p>

                        <div class="d-flex gap-2">
                            <a href="<?php echo admin_url('edit.php?post_type=esavest_request'); ?>"
                               class="btn btn-outline-warning btn-sm">
                                View All
                            </a>
                            <a href="<?php echo admin_url('post-new.php?post_type=esavest_request'); ?>"
                               class="btn btn-warning btn-sm">
                                Add New
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Offers -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0">Offers</h5>
                            <i class="fa-solid fa-tags fa-lg text-success"></i>
                        </div>

                        <h2 class="fw-bold mb-2"><?php echo esc_html($offers_count); ?></h2>
                        <p class="text-muted mb-3">Partner submitted offers</p>

                        <div class="d-flex gap-2">
                            <a href="<?php echo admin_url('edit.php?post_type=esavest_offer'); ?>"
                               class="btn btn-outline-success btn-sm">
                                View All
                            </a>
                            <a href="<?php echo admin_url('post-new.php?post_type=esavest_offer'); ?>"
                               class="btn btn-success btn-sm">
                                Add New
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
