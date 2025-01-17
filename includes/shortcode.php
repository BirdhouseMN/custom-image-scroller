<?php
/* ====================
Render Image Scroller Shortcode
======================= */

// Shortcode logic to render the image scroller
function cis_render_image_scroller($atts) {
    $atts = shortcode_atts(['id' => null], $atts, 'image_scroller');
    if (!$atts['id']) return ''; // No ID provided

    $scroller_id = intval($atts['id']);
    $images = get_field('scroller_images', $scroller_id, true);
    $direction = get_field('scrolling_direction', $scroller_id);
    $direction_class = ($direction === 'Horizontal') ? 'scroll-horizontal' : 'scroll-vertical';

    // Log scroller direction for debugging
    error_log("Scroller ID: $scroller_id, Direction: " . ($direction ? $direction : 'not set'));

    if (!$images) return '<p>No images found for this scroller.</p>';

    $output = '<div class="scrolling-images-wrap ' . esc_attr($direction_class) . '" data-scroller-id="' . esc_attr($scroller_id) . '">';
    foreach ($images as $image) {
        $output .= '<div class="image-row">';
        $output .= '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('image_scroller', 'cis_render_image_scroller');

/* ====================
Admin Shortcode Meta Box
======================= */

// Add Shortcode Meta Box to Scroller Edit Page
function cis_add_scroller_shortcode_meta_box() {
    add_meta_box(
        'scroller_shortcode_meta',
        'Scroller Shortcode',
        'cis_scroller_shortcode_meta_box_callback',
        'image_scroller',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'cis_add_scroller_shortcode_meta_box');

// Callback for the Shortcode Meta Box
function cis_scroller_shortcode_meta_box_callback($post) {
    echo '<p>Copy the shortcode below to display this scroller:</p>';
    echo '<input type="text" readonly value="[image_scroller id=' . esc_attr($post->ID) . ']" style="width: 100%; padding: 5px; font-size: 14px;">';
}

/* ====================
Enqueue Admin Styles (Optional)
======================= */

// Enqueue styles for the admin area (if needed for the meta box UI)
function cis_enqueue_admin_styles($hook) {
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_style('cis-admin-styles', plugin_dir_url(__FILE__) . '../css/admin-style.css');
    }
}
add_action('admin_enqueue_scripts', 'cis_enqueue_admin_styles');
