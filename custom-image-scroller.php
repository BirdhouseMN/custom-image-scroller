<?php
/**
 * Plugin Name: Custom Image Scroller
 * Description: A plugin to create and manage image scrollers with ACF fields.
 * Version: 3.0.4
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
        // Register ACF fields dynamically
        cis_register_acf_fields();
    }
}
add_action('plugins_loaded', 'cis_check_acf_dependency');

function cis_missing_acf_notice() {
    echo '<div class="notice notice-error">
        <p><strong>Custom Image Scroller:</strong> This plugin requires <a href="https://www.advancedcustomfields.com/pro/" target="_blank">Advanced Custom Fields Pro</a> to work. Please install and activate ACF Pro.</p>
    </div>';
}

/* ====================
REGISTER ACF FIELDS
======================= */
function cis_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return; // Abort if ACF Pro is not active
    }

    acf_add_local_field_group(array(
        'key' => 'group_scroller_fields',
        'title' => 'Scroller Fields',
        'fields' => array(
            array(
                'key' => 'field_scroller_images',
                'label' => 'Scroller Images',
                'name' => 'scroller_images',
                'type' => 'gallery',
                'instructions' => 'Upload the images for your scroller.',
                'required' => 1,
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
                'layout' => 'horizontal',
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

// Debug logging for ACF field registration
add_action('acf/init', function () {
    if (function_exists('acf_add_local_field_group')) {
        error_log('ACF field groups are being registered.');
        cis_register_acf_fields();
    } else {
        error_log('ACF Pro is not active, fields cannot be registered.');
    }
});

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
        <form method="post" action="">
            <?php
            settings_fields('cis_settings_group');
            do_settings_sections('custom-image-scroller');
            submit_button();
            ?>
        </form>
        <hr>
        <h2>ACF Field Management</h2>
        <form method="post" action="">
            <?php
            if (isset($_POST['register_acf_fields'])) {
                cis_register_acf_fields();
                echo '<div class="notice notice-success"><p>ACF fields have been updated successfully.</p></div>';
            }
            ?>
            <input type="hidden" name="register_acf_fields" value="1">
            <button type="submit" class="button button-primary">Update ACF Fields</button>
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
UNINSTALL HOOK
======================= */
register_uninstall_hook(__FILE__, 'cis_handle_uninstall');

function cis_handle_uninstall() {
    $cleanup = get_option('cis_cleanup_on_delete', 'no');
    if ($cleanup === 'yes') {
        // Remove custom post types, options, and metadata
        $scrollers = get_posts(array('post_type' => 'image_scroller', 'numberposts' => -1));
        foreach ($scrollers as $scroller) {
            wp_delete_post($scroller->ID, true);
        }
        delete_option('cis_cleanup_on_delete');
    }
}

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

