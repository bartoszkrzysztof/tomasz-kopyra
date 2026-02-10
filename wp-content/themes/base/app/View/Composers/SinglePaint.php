<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class SinglePaint extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'single-paint',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        $post_id = get_the_ID();
        return [
            'title' => get_the_title(),
            'text' => get_the_content(),
            'gallery' => $this->getGalleryItems($post_id),
            'backLink' => get_post_type_archive_link('paint'),
        ];
    }

    public function getGalleryItems($post_id)
    {
        $items = [];
        $thumb = get_post_thumbnail_id($post_id);
        if ($thumb) {
            $items[] = $thumb;
        }

        $gallery = get_field('gallery', $post_id);
        if ($gallery) {
            $items = array_merge($items, $gallery);
        }
        

        return array_unique($items); 
    }

}
