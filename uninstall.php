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

// Delete Custom Post Type Posts and Related Attachments
$scroller_posts = get_posts(array(
    'post_type' => 'image_scroller',
    'numberposts' => -1,
));
foreach ($scroller_posts as $post) {
    // Delete associated attachments
    $attachments = get_attached_media('', $post->ID);
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true); // Force delete attachment
    }
    wp_delete_post($post->ID, true); // Force delete post
}

// Remove ACF Field Groups
if (class_exists('ACF') && function_exists('acf_get_field_group')) {
    $field_group = acf_get_field_group('group_scroller_fields'); // ACF field group key
    if ($field_group) {
        acf_delete_field_group($field_group['ID']); // Remove ACF field group
    }
}

// Clean Up Post Meta
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'scroller_%'");

// Clean Up Options
delete_option('custom_image_scroller_settings'); // Example plugin settings
delete_option('cis_cleanup_on_delete'); // Remove cleanup preference

// Clean Up Taxonomies (if applicable)
$terms = get_terms(array(
    'taxonomy' => 'custom_taxonomy_name', // Replace with your taxonomy if applicable
    'hide_empty' => false,
));
if (!is_wp_error($terms)) {
    foreach ($terms as $term) {
        wp_delete_term($term->term_id, 'custom_taxonomy_name');
    }
}
