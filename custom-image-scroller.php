<?php
/**
 * Plugin Name: Custom Image Scroller
 * Description: A plugin to create and manage image scrollers with ACF fields.
 * Version: 3.5.4
 * Author: Birdhouse Web Design
 */

// Define constants for plugin paths
define('CIS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CIS_PLUGIN_URL', plugin_dir_url(__FILE__));

/* ====================
LOAD REQUIRED FILES
======================= */
require_once CIS_PLUGIN_DIR . 'includes/post-type.php';
require_once CIS_PLUGIN_DIR . 'includes/shortcode.php';

/* ====================
ENQUEUE CSS & JS
======================= */
function cis_enqueue_assets() {
    $plugin_url = plugin_dir_url(__FILE__);

    // Enqueue CSS
    wp_enqueue_style('custom-scroller-style', $plugin_url . 'css/style.css', [], time(), 'all');

    // Enqueue JavaScript
    wp_enqueue_script('custom-scroller-js', $plugin_url . 'js/custom.js', ['jquery'], time(), true);
}
add_action('wp_enqueue_scripts', 'cis_enqueue_assets');

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
ACF FIELD LOADING & SYNC
======================= */
add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = CIS_PLUGIN_DIR . 'acf-json';
    return $paths;
});

// Sync ACF Fields Manually
add_action('admin_post_cis_sync_fields', function () {
    if (isset($_POST['cis_sync_fields']) && check_admin_referer('cis_sync_fields_action', 'cis_sync_fields_nonce')) {
        $json_path = CIS_PLUGIN_DIR . 'acf-json';
        $imported = 0;

        if (is_dir($json_path)) {
            foreach (glob($json_path . '/*.json') as $file) {
                $field_group = json_decode(file_get_contents($file), true);

                if (json_last_error() === JSON_ERROR_NONE && isset($field_group['key'])) {
                    acf_import_field_group($field_group);
                    $imported++;
                }
            }
        }

        if ($imported > 0) {
            add_action('admin_notices', function () use ($imported) {
                echo '<div class="notice notice-success is-dismissible"><p>' . $imported . ' ACF field groups synchronized successfully!</p></div>';
            });
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error is-dismissible"><p>No valid ACF field groups were found to sync.</p></div>';
            });
        }
    }
});

/* ====================
ENSURE ACF FIELDS LOAD CORRECTLY
======================= */
add_action('acf/init', function () {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key' => 'group_scroller_fields',
            'title' => 'Scroller Fields',
            'fields' => [
                [
                    'key' => 'field_672e6f670139b',
                    'label' => 'Scroller Images',
                    'name' => 'scroller_images',
                    'type' => 'gallery',
                    'return_format' => 'array',
                    'library' => 'all',
                    'preview_size' => 'medium',
                ],
                [
                    'key' => 'field_672e6f9b0139c',
                    'label' => 'Scrolling Direction',
                    'name' => 'scrolling_direction',
                    'type' => 'radio',
                    'choices' => [
                        'Horizontal' => 'Horizontal',
                        'Vertical' => 'Vertical',
                    ],
                    'layout' => 'vertical',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'image_scroller',
                    ],
                ],
            ],
        ]);
    }
});

/* ====================
ADD SETTINGS PAGE
======================= */
function cis_add_settings_page() {
    add_submenu_page(
        'edit.php?post_type=image_scroller',
        'Image Scroller Settings',
        'Settings',
        'manage_options',
        'custom-image-scroller-settings',
        'cis_render_settings_page'
    );
}
add_action('admin_menu', 'cis_add_settings_page');

function cis_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom Image Scroller Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('cis_sync_fields_action', 'cis_sync_fields_nonce'); ?>
            <p><input type="submit" name="cis_sync_fields" class="button button-primary" value="Sync ACF Fields"></p>
        </form>
        <hr>
        <form method="post" action="options.php">
            <?php
            settings_fields('cis_settings_group');
            do_settings_sections('custom-image-scroller-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function cis_register_settings() {
    register_setting(
        'cis_settings_group',
        'cis_cleanup_on_delete',
        [
            'type' => 'string',
            'default' => 'no',
            'sanitize_callback' => function ($input) {
                return ($input === 'yes') ? 'yes' : 'no';
            },
        ]
    );

    add_settings_section('cis_general_settings', 'General Settings', null, 'custom-image-scroller-settings');

    add_settings_field(
        'cis_cleanup_on_delete',
        'Remove Data on Plugin Delete',
        function () {
            $value = get_option('cis_cleanup_on_delete', 'no');
            echo '<input type="checkbox" name="cis_cleanup_on_delete" value="yes" ' . checked('yes', $value, false) . '> Yes, delete all data when the plugin is removed.';
        },
        'custom-image-scroller-settings',
        'cis_general_settings'
    );
}
add_action('admin_init', 'cis_register_settings');

/* ====================
UNINSTALL CLEANUP
======================= */
register_uninstall_hook(__FILE__, 'cis_handle_uninstall');

function cis_handle_uninstall() {
    $cleanup = get_option('cis_cleanup_on_delete', 'no');
    if ($cleanup === 'yes') {
        $scrollers = get_posts(['post_type' => 'image_scroller', 'numberposts' => -1]);
        foreach ($scrollers as $scroller) {
            wp_delete_post($scroller->ID, true);
        }
        delete_option('cis_cleanup_on_delete');
        if (class_exists('ACF') && function_exists('acf_delete_field_group')) {
            $field_group = acf_get_field_group('group_scroller_fields');
            if ($field_group) {
                acf_delete_field_group($field_group['ID']);
            }
        }
    }
}
