<?php
function cis_render_image_scroller($atts, $content = '', $tag = '') {
    $atts = shortcode_atts(['id' => null], $atts, $tag);

    // Validate the provided scroller ID
    if (!$atts['id']) {
        return '<p>Error: No scroller ID provided.</p>';
    }

    $scroller_id = intval($atts['id']);
    $images = get_field('scroller_images', $scroller_id);

    // Validate that images is an array
    if (!is_array($images) || empty($images)) {
        return '<p>Error: No images found for this scroller.</p>';
    }

    $direction = get_field('scrolling_direction', $scroller_id) ?? 'horizontal';
    $direction_class = ($direction === 'horizontal') ? 'scroll-horizontal' : 'scroll-vertical';

    // Begin output buffering for cleaner HTML
    $output = '<div class="scrolling-images-wrap ' . esc_attr($direction_class) . '">';
    foreach ($images as $image) {
        // Ensure the image array has the necessary keys
        if (isset($image['url']) && isset($image['alt'])) {
            $output .= '<div class="image-row">';
            $output .= '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
            $output .= '</div>';
        } else {
            $output .= '<div class="image-row"><p>Error: Invalid image data.</p></div>';
        }
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
