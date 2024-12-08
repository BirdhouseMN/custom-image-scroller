<?php
/*
Plugin Name: Custom Image Scroller
Description: A plugin to create and manage image scrollers with ACF fields.
Version: 1.0
Author: Birdhouse Web Design
*/

// Define constants for plugin paths
define('CIS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CIS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check for ACF dependency
function cis_check_acf_dependency() {
    if (!class_exists('ACF')) {
        add_action('admin_notices', 'cis_missing_acf_notice');
        deactivate_plugins(plugin_basename(__FILE__)); // Automatically deactivate the plugin
    }
}
add_action('admin_init', 'cis_check_acf_dependency');

// Admin notice for missing ACF
function cis_missing_acf_notice() {
    echo '<div class="notice notice-error">
        <p><strong>Custom Image Scroller:</strong> This plugin requires Advanced Custom Fields (ACF) to function. Please install and activate ACF.</p>
    </div>';
}

// Include necessary files
if (class_exists('ACF')) {
    require_once CIS_PLUGIN_DIR . 'includes/post-type.php'; // Custom Post Type
    require_once CIS_PLUGIN_DIR . 'includes/shortcode.php'; // Shortcode Logic

    if (file_exists(CIS_PLUGIN_DIR . 'includes/acf-fields.php')) {
        require_once CIS_PLUGIN_DIR . 'includes/acf-fields.php'; // ACF Field Registration
    }
    
    // Enqueue CSS and JavaScript
    function cis_enqueue_assets() {
        wp_enqueue_style('cis-styles', CIS_PLUGIN_URL . 'css/style.css');
        wp_enqueue_script('cis-scripts', CIS_PLUGIN_URL . 'js/custom.js', ['jquery'], null, true);
    }
    add_action('wp_enqueue_scripts', 'cis_enqueue_assets');
}
