<?php

namespace App\Blocks;

class ReviewsBlock
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets()
    {
        if (has_block('acf/reviews')) {
            wp_enqueue_script(
                'swiper',
                get_template_directory_uri() . '/node_modules/swiper/swiper-bundle.min.js',
                [],
                null,
                true
            );
            
            wp_enqueue_script(
                'reviews-block',
                get_template_directory_uri() . '/resources/scripts/block-reviews.js',
                ['swiper'],
                null,
                true
            );
        }
    }

    public function __invoke($block, $content = '', $is_preview = false, $post_id = 0)
    {
        $reviews = get_field('reviews') ?: [];

        echo \Roots\view('blocks.reviews', [
            'block' => $block,
            'reviews' => $reviews,
            'is_preview' => $is_preview,
        ])->render();
    }
}
