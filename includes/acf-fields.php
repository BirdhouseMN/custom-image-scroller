<?php
// Register ACF fields programmatically
function cis_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_672e6f6644cc1',
        'title' => 'Scroller Fields',
        'fields' => [
            [
                'key' => 'field_672e6f670139b',
                'label' => 'Scroller Images',
                'name' => 'scroller_images',
                'type' => 'gallery',
                'return_format' => 'array',
                'insert' => 'append',
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
                'default_value' => '',
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
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
}
add_action('acf/init', 'cis_register_acf_fields');
