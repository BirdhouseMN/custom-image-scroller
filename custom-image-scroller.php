<?php
/**
 * Plugin Name: Custom Image Scroller
 * Description: A plugin to create and manage image scrollers with ACF fields.
 * Version: 2.1.2
 * Author: Birdhouse Web Design
 */

// Define constants for plugin paths
define('CIS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CIS_PLUGIN_URL', plugin_dir_url(__FILE__));

/* ====================
ACF DEPENDENCY CHECK
======================= */
function cis_check_acf_dependency() {
    if (!class_exists('ACF')) {
        add_action('admin_notices', 'cis_missing_acf_notice');
    } else {
        // Register ACF fields directly in this file
        cis_register_acf_fields();
    }
}
add_action('plugins_loaded', 'cis_check_acf_dependency');

function cis_missing_acf_notice() {
    echo '<div class="notice notice-error">
        <p><strong>Custom Image Scroller:</strong> Advanced Custom Fields (ACF) is required for this plugin to work. Please install and activate ACF.</p>
    </div>';
}

/* ====================
REGISTER ACF FIELDS
======================= */
function cis_register_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_scroller_fields',
            'title' => 'Scroller Fields',
            'fields' => array(
                array(
                    'key' => 'field_scroller_images',
                    'label' => 'Scroller Images',
                    'name' => 'scroller_images',
                    'type' => 'gallery',
                ),
                array(
                    'key' => 'field_scrolling_direction',
                    'label' => 'Scrolling Direction',
                    'name' => 'scrolling_direction',
                    'type' => 'radio',
                    'choices' => array(
                        'horizontal' => 'Horizontal',
                        'vertical' => 'Vertical',
                    ),
                    'default_value' => 'horizontal',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'image_scroller',
                    ),
                ),
            ),
        ));
    }
}

/* ====================
ADMIN SETTINGS FOR PLUGIN CLEANUP
======================= */
function cis_add_settings_menu() {
    add_options_page(
        'Custom Image Scroller Settings',
        'Image Scroller',
        'manage_options',
        'custom-image-scroller',
        'cis_render_settings_page'
    );
}
add_action('admin_menu', 'cis_add_settings_menu');

function cis_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom Image Scroller Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cis_settings_group');
            do_settings_sections('custom-image-scroller');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function cis_register_settings() {
    register_setting('cis_settings_group', 'cis_cleanup_on_delete');
    add_settings_section(
        'cis_general_settings',
        'General Settings',
        null,
        'custom-image-scroller'
    );
    add_settings_field(
        'cis_cleanup_on_delete',
        'Remove Data on Plugin Delete',
        function () {
            $value = get_option('cis_cleanup_on_delete', 'no');
            echo '<input type="checkbox" name="cis_cleanup_on_delete" value="yes" ' . checked('yes', $value, false) . '> Yes, delete all data when the plugin is removed.';
        },
        'custom-image-scroller',
        'cis_general_settings'
    );
}
add_action('admin_init', 'cis_register_settings');

/* ====================
ENQUEUE PLUGIN ASSETS
======================= */
function cis_enqueue_assets() {
    wp_enqueue_style('cis-styles', CIS_PLUGIN_URL . 'css/style.css');
    wp_enqueue_script('cis-scripts', CIS_PLUGIN_URL . 'js/custom.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'cis_enqueue_assets');

// Define dynamic CSS variable for the SVG cursor
add_action('wp_head', function () {
    ?>
    <style>
        :root {
            --white-star-svg: url('<?php echo plugin_dir_url(__FILE__) . 'assets/images/white-star-2-01.svg'; ?>');
        }
    </style>
    <?php
});

/* ====================
INCLUDE NECESSARY FILES
======================= */
if (class_exists('ACF')) {
    require_once CIS_PLUGIN_DIR . 'includes/post-type.php'; // Custom Post Type logic
    require_once CIS_PLUGIN_DIR . 'includes/shortcode.php'; // Shortcode logic
}

/* ====================
PLUGIN UPDATE CHECKER
======================= */
require_once plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Initialize the update checker
$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/BirdhouseMN/custom-image-scroller', // GitHub repository URL
    __FILE__,                                              // Main plugin file
    'custom-image-scroller'                                // Plugin slug
);

// Optional: Set the branch to use for updates
$updateChecker->setBranch('main'); // Ensure this matches your release branch

// Ensure ACF fields are registered on plugin activation or when ACF becomes active
function cis_setup_acf_fields() {
    if (class_exists('ACF') && function_exists('acf_add_local_field_group')) {
        cis_register_acf_fields();
    }
}
add_action('init', 'cis_setup_acf_fields');
