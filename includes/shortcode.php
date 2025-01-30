<?php

/* ====================
SHORTCODE RENDERING
======================= */

function cis_render_image_scroller($atts, $content = '', $tag = '') {
    $atts = shortcode_atts(['id' => null], $atts, $tag);

    if (!$atts['id'] || !is_numeric($atts['id'])) {
        return '<p>Error: No valid scroller ID provided.</p>';
    }

    $scroller_id = intval($atts['id']);
    $images = get_field('scroller_images', $scroller_id);

    if (!is_array($images) || empty($images)) {
        return '<p>Error: No images found for this scroller.</p>';
    }

    /* ====================
    RETRIEVE SCROLL DIRECTION CORRECTLY
    ======================= */

    $direction = get_field('scrolling_direction', $scroller_id);

    // Default to "horizontal" if ACF field is missing
    if (!$direction || !in_array(strtolower($direction), ['horizontal', 'vertical'])) {
        $direction = 'horizontal';
    } else {
        $direction = strtolower($direction);
    }

    $direction_class = ($direction === 'horizontal') ? 'scroll-horizontal' : 'scroll-vertical';

    /* ====================
    BUILD SHORTCODE OUTPUT
    ======================= */

    $output = '<div class="scrolling-images-wrap ' . esc_attr($direction_class) . '" data-scroller-id="' . esc_attr($scroller_id) . '" data-direction="' . esc_attr($direction) . '">';
    
    foreach ($images as $image) {
        if (isset($image['url']) && isset($image['alt'])) {
            $output .= '<div class="image-row">';
            $output .= '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
            $output .= '</div>';
        }
    }

    $output .= '</div>';

    /* ====================
    DEBUGGING OUTPUT
    ======================= */

    error_log("Scroller ID: " . $scroller_id . " | Direction: " . $direction);

    return $output;
}

add_shortcode('image_scroller', 'cis_render_image_scroller');

/* ====================
ADMIN LIST VIEW COLUMN
======================= */

// Add a "Shortcode" column to the admin table
function cis_add_shortcode_column($columns) {
    $columns['shortcode'] = 'Shortcode';
    return $columns;
}
add_filter('manage_image_scroller_posts_columns', 'cis_add_shortcode_column');

// Populate the "Shortcode" column with the correct shortcode
function cis_populate_shortcode_column($column, $post_id) {
    if ($column === 'shortcode') {
        echo '<code>[image_scroller id="' . esc_attr($post_id) . '"]</code>';
    }
}
add_action('manage_image_scroller_posts_custom_column', 'cis_populate_shortcode_column', 10, 2);

/* ====================
ADMIN SHORTCODE META BOX
======================= */

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

function cis_scroller_shortcode_meta_box_callback($post) {
    $shortcode = '[image_scroller id="' . esc_attr($post->ID) . '"]';
    echo '<p>Copy the shortcode below to display this scroller:</p>';
    echo '<input type="text" readonly value="' . esc_attr($shortcode) . '" style="width: 100%; padding: 5px; font-size: 14px;">';
}

/* ====================
DEBUGGING (Optional)
======================= */

// Uncomment to log shortcode column population for debugging
// add_action('manage_image_scroller_posts_custom_column', function($column, $post_id) {
//     if ($column === 'shortcode') {
//         error_log("Shortcode column populated for post ID: " . $post_id);
//     }
// }, 10, 2);
