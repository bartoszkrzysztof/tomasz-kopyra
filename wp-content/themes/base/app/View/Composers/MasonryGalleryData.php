<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class MasonryGalleryData extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'components.masonary-gallery',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'ajaxAction' => 'load_gallery_items',
            'nonce' => wp_create_nonce('gallery_load_more'),
        ];
    }
}
