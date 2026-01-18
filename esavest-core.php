<?php
/**
 * Plugin Name: ESAVEST Core
 * Plugin URI: https://esavest.com
 * Description: Core functionality for the ESAVEST platform including custom post types, business logic, and integrations.
 * Version: 1.0.0
 * Author: Md. Sohanur Rohman Sihab
 * Text Domain: esavest-core-td
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define plugin constants
 */
define('ESAVEST_CORE_VERSION', '1.0.0');
define('ESAVEST_CORE_PATH', plugin_dir_path(__FILE__));
define('ESAVEST_CORE_URL', plugin_dir_url(__FILE__));

/**
 * Include core class
 */
require_once ESAVEST_CORE_PATH . 'includes/core/class-esavest-core.php';

/**
 * Run plugin
 */
$esavest_core = new ESAVEST_Core();
$esavest_core->run();
/**
 * Activation hook MUST be here
 */
register_activation_hook(__FILE__, ['ESAVEST_Core', 'activate']);

/**
 * Add settings link on plugins page
 */
function esavest_core_add_settings_link($links) {

    $settings_link = '<a href="' . admin_url('admin.php?page=esavest-core') . '">' . __('Settings', 'esavest-core') . '</a>';
    array_unshift($links, $settings_link);

    return $links;
}

add_filter(
    'plugin_action_links_' . plugin_basename(__FILE__),
    'esavest_core_add_settings_link'
);

