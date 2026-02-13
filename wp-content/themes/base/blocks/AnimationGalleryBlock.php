<?php

namespace App\Blocks;

class AnimationGalleryBlock
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
        // Skrypt ładowany dynamicznie w app.js gdy blok istnieje
        
        // Pobierz pola ACF
        $header = get_field('header') ?: '';
        $main_content = get_field('main_content') ?: '';
        $bg = get_field('main_image') ?: '';
        $bg_position = get_field('main_image_position') ?: '';

        $paints_array = get_field('paints') ?: [];
        $paints = $this->get_paints($paints_array);
        $link = [
            'url' => get_post_type_archive_link('paint'),
            'title' => __('zobacz więcej', 'tkopera'),
        ];

        $animation_enable = get_field('animation_enable') ?: false;
        $animation_settings = '';
        $section_classes = ' js-masonary-gallery-block';

        if ($animation_enable) {
            $section_classes .= ' js-gsap-gallery-block';
        }
        
        $data = [
            'block' => $block,
            'is_preview' => $is_preview,
            'post_id' => $post_id,
            'header' => $header,
            'main_content' => $main_content,
            'bg' => $bg,
            'bg_position' => $bg_position,
            'animation_enable' => $animation_enable,
            'animation_settings' => $animation_settings,
            'section_classes' => $section_classes,
            'link' => $link,
            'paints' => $paints,
        ];

        try {
            echo \Roots\view('blocks.acf.animation_gallery', $data)->render();
        } catch (\Exception $e) {
            if ($is_preview) {
                echo '<div style="padding: 20px; background: #f0f0f0; border: 1px solid #ccc;">';
                echo '<strong>Section error:</strong> ' . esc_html($e->getMessage());
                echo '</div>';
            }
            error_log("Section render error: " . $e->getMessage());
        }
    }

    public function get_paints($paints) {
        $paint_data = [];
        foreach ($paints as $paint) {
            $paint_data[] = [
                'id' => $paint,
                'title' => get_the_title($paint),
                'image_id' => get_post_thumbnail_id ($paint),
                'link' => get_permalink($paint),
            ];
        }
        return $paint_data;
    }
}