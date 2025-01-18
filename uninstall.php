<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if the user opted for a full cleanup
$cleanup = get_option('cis_cleanup_on_delete', 'no');
if ($cleanup !== 'yes') {
    return; // Exit if user did not opt for cleanup
}

// Delete Custom Post Type Posts
$scroller_posts = get_posts(array(
    'post_type' => 'image_scroller',
    'numberposts' => -1,
));
foreach ($scroller_posts as $post) {
    wp_delete_post($post->ID, true); // Force delete
}

// Remove ACF Field Groups (if any)
if (class_exists('ACF')) {
    delete_option('acf_field_group_group_scroller_fields');
}

// Clean Up Post Meta
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'scroller_%'");

// Clean Up Options
delete_option('custom_image_scroller_settings'); // Example plugin settings

delete_option('cis_cleanup_on_delete'); // Remove cleanup preference

// Clean Up Taxonomies (if applicable)
$terms = get_terms(array(
    'taxonomy' => 'custom_taxonomy_name', // Replace with your taxonomy if any
    'hide_empty' => false,
));
foreach ($terms as $term) {
    wp_delete_term($term->term_id, 'custom_taxonomy_name');
}
