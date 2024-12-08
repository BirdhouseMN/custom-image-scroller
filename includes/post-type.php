<?php
// Register Image Scroller Custom Post Type
function cis_register_image_scroller_cpt() {
    $labels = [
        'name'               => 'Image Scrollers',
        'singular_name'      => 'Image Scroller',
        'menu_name'          => 'Image Scrollers',
        'name_admin_bar'     => 'Image Scroller',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Scroller',
        'edit_item'          => 'Edit Scroller',
        'view_item'          => 'View Scroller',
        'all_items'          => 'All Scrollers',
        'search_items'       => 'Search Scrollers',
        'not_found'          => 'No scrollers found',
        'not_found_in_trash' => 'No scrollers found in Trash',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'menu_icon'          => 'dashicons-images-alt2',
        'supports'           => ['title'],
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'image-scrollers'],
    ];

    register_post_type('image_scroller', $args);
}
add_action('init', 'cis_register_image_scroller_cpt');