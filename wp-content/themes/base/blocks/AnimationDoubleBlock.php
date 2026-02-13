<?php

namespace App\Blocks;

class AnimationDoubleBlock
{
    /**
     * Custom render callback dla bloku PaneFlow
     * Dodaje dodatkową logikę biznesową
     *
     * @param array $block Block settings
     * @param string $content Block content
     * @param bool $is_preview Whether in preview mode
     * @param int $post_id Current post ID
     */
    public function __invoke($block, $content = '', $is_preview = false, $post_id = 0)
    {
        
        // Pobierz pola ACF
        $header = get_field('header') ?: '';
        $main_content = get_field('main_content') ?: '';
        $bg = get_field('main_image') ?: '';
        $bg_position = get_field('main_image_position') ?: '';
        $left_section = get_field('left_section') ?: [];
        $right_section = get_field('right_section') ?: [];

        $animation_enable = get_field('animation_enable') ?: false;

        $animation_settings = '';
        $section_classes = '';
        $wrapper_classes = '';
        $left_section_settings = '';
        $left_section_classes = '';
        $right_section_settings = '';
        $right_section_classes = '';

        if ($animation_enable) {
            $section_classes .= 'js-gsap-double-block';
            $animation_settings = $this->animationSettings();

            $wrapper_classes .= 'js-pinned-section';

            $left_section_classes .= 'js-left-box';
            if (isset($left_section['animation_type']) && $left_section['animation_type']) {
                $left_section_settings .= ' data-animation-type="' . esc_attr($left_section['animation_type']) . '" ';
                $left_section_classes .= ' -' . esc_attr($left_section['animation_type']);
            }
            if (isset($left_section['animation_time']) && $left_section['animation_time']) {
                $left_section_settings .= ' data-animation-time="' . esc_attr($left_section['animation_time']) . '" ';
            }

            $right_section_classes .= 'js-right-box';
            if (isset($right_section['animation_type']) && $right_section['animation_type']) {
                $right_section_settings .= ' data-animation-type="' . esc_attr($right_section['animation_type']) . '" ';
                $right_section_classes .= ' -' . esc_attr($right_section['animation_type']);
            }
            if (isset($right_section['animation_time']) && $right_section['animation_time']) {
                $right_section_settings .= ' data-animation-time="' . esc_attr($right_section['animation_time']) . '" ';
            }
        }
        
        $data = [
            'block' => $block,
            'is_preview' => $is_preview,
            'post_id' => $post_id,
            'header' => $header,
            'main_content' => $main_content,
            'bg' => $bg,
            'bg_position' => $bg_position,
            'left_section' => $left_section,
            'right_section' => $right_section,
            'animation_enable' => $animation_enable,
            'animation_settings' => $animation_settings,

            'section_classes' => $section_classes,
            'wrapper_classes' => $wrapper_classes,
            'left_section_settings' => $left_section_settings,
            'left_section_classes' => $left_section_classes,
            'right_section_settings' => $right_section_settings,
            'right_section_classes' => $right_section_classes,
        ];

        try {
            echo \Roots\view('blocks.acf.animation_double', $data)->render();
        } catch (\Exception $e) {
            if ($is_preview) {
                echo '<div style="padding: 20px; background: #f0f0f0; border: 1px solid #ccc;">';
                echo '<strong>Section error:</strong> ' . esc_html($e->getMessage());
                echo '</div>';
            }
            error_log("Section render error: " . $e->getMessage());
        }
    }

    private function animationSettings()
    {
        $data = '';

        $scroll_length = get_field('animation_scroll_length') ?: 2000;
        if ($scroll_length) {
            $data .= ' data-scroll-length="' . esc_attr($scroll_length) . '" ';
        }

        $scene_order = get_field('scene_order') ?: false;
        if ($scene_order) {
            $data .= ' data-scene-order="' . esc_attr($scene_order) . '" ';

        }

        return $data;
    }
}