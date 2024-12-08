<?php
// Render Image Scroller Shortcode
function cis_render_image_scroller($atts) {
    $atts = shortcode_atts(['id' => null], $atts, 'image_scroller');
    if (!$atts['id']) return '';

    $scroller_id = intval($atts['id']);
    $images = get_field('scroller_images', $scroller_id);
    $direction = get_field('scrolling_direction', $scroller_id);
    $direction_class = ($direction === 'Horizontal') ? 'scroll-horizontal' : 'scroll-vertical';

    if (!$images) return '<p>No images found for this scroller.</p>';

    $output = '<div class="scrolling-images-wrap ' . esc_attr($direction_class) . '">';
    foreach ($images as $image) {
        $output .= '<div class="image-row">';
        $output .= '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('image_scroller', 'cis_render_image_scroller');

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
