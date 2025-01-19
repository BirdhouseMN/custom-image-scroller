<?php
/**
 * Plugin Name: Custom Image Scroller
 * Description: A plugin to create and manage image scrollers with ACF fields.
 * Version: 3.5.2
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
    }
}
add_action('plugins_loaded', 'cis_check_acf_dependency');

function cis_missing_acf_notice() {
    echo '<div class="notice notice-error">
        <p><strong>Custom Image Scroller:</strong> This plugin requires <a href="https://www.advancedcustomfields.com/pro/" target="_blank">Advanced Custom Fields Pro</a> to work. Please install and activate ACF Pro.</p>
    </div>';
}

/* ====================
PRE-PACKAGED ACF FIELDS
======================= */
add_filter('acf/settings/save_json', function($path) {
    return CIS_PLUGIN_DIR . 'acf-json';
});

add_filter('acf/settings/load_json', function($paths) {
    $acf_json_path = CIS_PLUGIN_DIR . 'acf-json';
    if (is_dir($acf_json_path)) {
        $paths[] = $acf_json_path;
    } else {
        error_log('ACF JSON folder not found: ' . $acf_json_path);
    }
    return $paths;
});

add_action('admin_init', function () {
    $groups = acf_get_field_groups();
    $sync = [];

    foreach ($groups as $group) {
        $local = acf_maybe_get($group, 'local', false);
        $modified = acf_maybe_get($group, 'modified', 0);
        $private = acf_maybe_get($group, 'private', false);

        if ($local === 'json' && !$private) {
            if (!$group['ID'] || ($modified && $modified > get_post_modified_time('U', true, $group['ID'], true))) {
                $sync[$group['key']] = $group;
            }
        }
    }

    foreach ($sync as $key => $group) {
        acf_import_field_group($group);
        error_log('ACF Field Group Synced: ' . $group['title']);
    }
});

/* ====================
ADMIN SETTINGS FOR PLUGIN CLEANUP
======================= */
function cis_add_settings_to_cpt() {
    add_submenu_page(
        'edit.php?post_type=image_scroller', // Parent menu slug
        'Image Scroller Settings', // Page title
        'Settings', // Menu title
        'manage_options', // Capability
        'custom-image-scroller', // Menu slug
        'cis_render_settings_page' // Callback function
    );
}
add_action('admin_menu', 'cis_add_settings_to_cpt');

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
        <hr>
        <h2>Notice</h2>
        <p>ACF Pro is required for this plugin. Field groups are automatically managed and pre-packaged within the plugin. You do not need to manually configure fields in ACF.</p>
    </div>
    <?php
}

function cis_register_settings() {
    register_setting(
        'cis_settings_group', // Settings group
        'cis_cleanup_on_delete', // Option name
        [
            'type' => 'string',
            'default' => 'no',
            'sanitize_callback' => function ($input) {
                return ($input === 'yes') ? 'yes' : 'no';
            },
        ]
    );

    add_settings_section(
        'cis_general_settings', // Section ID
        'General Settings', // Section title
        null, // Callback
        'custom-image-scroller' // Page slug
    );

    add_settings_field(
        'cis_cleanup_on_delete', // Field ID
        'Remove Data on Plugin Delete', // Field title
        function () {
            $value = get_option('cis_cleanup_on_delete', 'no');
            echo '<input type="checkbox" name="cis_cleanup_on_delete" value="yes" ' . checked('yes', $value, false) . '> Yes, delete all data when the plugin is removed.';
        },
        'custom-image-scroller', // Page slug
        'cis_general_settings' // Section ID
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

        // Remove ACF field group entries
        if (class_exists('ACF') && function_exists('acf_delete_field_group')) {
            $field_group = acf_get_field_group('group_scroller_fields');
            if ($field_group) {
                acf_delete_field_group($field_group['ID']);
            }
        }
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

/* ====================
DEBUG TESTING CODE
======================= */
add_action('acf/init', function () {
    $json_paths = apply_filters('acf/settings/load_json', []);
    error_log('ACF JSON Paths: ' . print_r($json_paths, true));

    $field_groups = acf_get_field_groups();
    error_log('ACF Field Groups: ' . print_r($field_groups, true));

    $json_files = glob(CIS_PLUGIN_DIR . 'acf-json/*.json');
    error_log('ACF JSON Files Found: ' . print_r($json_files, true));

    if (!empty($json_files)) {
        foreach ($json_files as $file) {
            $json_data = file_get_contents($file);
            $fields = json_decode($json_data, true);

            if (!empty($fields)) {
                acf_add_local_field_group($fields);
                error_log('Field group registered programmatically: ' . ($fields['title'] ?? 'Untitled'));
            } else {
                error_log('Failed to decode JSON: ' . $file);
            }
        }
    }
});
