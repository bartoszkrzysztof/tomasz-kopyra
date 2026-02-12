<?php

namespace App\Blocks;

class TextEditorBlock
{
    /**
     * Custom render callback dla bloku Hero Banner
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
        $text = get_field('text') ?: '';
        
        $data = [
            'block' => $block,
            'is_preview' => $is_preview,
            'post_id' => $post_id,
            'text' => $text,
        ];

        try {
            echo \Roots\view('blocks.acf.text_editor', $data)->render();
        } catch (\Exception $e) {
            if ($is_preview) {
                echo '<div style="padding: 20px; background: #f0f0f0; border: 1px solid #ccc;">';
                echo '<strong>Section error:</strong> ' . esc_html($e->getMessage());
                echo '</div>';
            }
            error_log("Section render error: " . $e->getMessage());
        }
    }
}