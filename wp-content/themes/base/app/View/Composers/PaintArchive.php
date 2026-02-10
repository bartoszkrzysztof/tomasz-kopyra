<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class PaintArchive extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'archive-paint',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        $pages = $this->getTotalItems();
        return [
            'title' => get_field('paint_archive_title', 'option') ?: '',
            'text' => get_field('paint_archive_text', 'option') ?: '',
            'items' => $this->getGalleryItems(1),
            'hasMore' => $pages > 1,
            'maxPages' => $pages,
        ];
    }

    public function getGalleryItems($page = 1)
    {
        $per_page = GALLERY_PER_PAGE;
        
        //temp 
        if ($page == 2) {
            $page = 1;
        }
        if ($page == 3) {
            $page = 1;
            $per_page = 6;
        }

        $args = [
            'post_type' => 'paint',
            'posts_per_page' => $per_page,
            'paged' => $page,
        ];


        $query = new \WP_Query($args);
        
        $items = [];
        if ($query->posts) {
            foreach ($query->posts as $post) {
                $items[] = [
                    'id' => $post->ID,
                    'title' => get_the_title($post),
                    'image_id' => get_post_thumbnail_id($post),
                    'link' => get_permalink($post),
                ];
            }
        }

        /** temp */
        $items = array_merge($items, $items);

        return $items;
    }

    public function getTotalItems()
    {
        $args = [
            'post_type' => 'paint',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];
        $query = new \WP_Query($args);
        $items = count($query->posts);
        $pages = ceil($items / GALLERY_PER_PAGE);
        
        $pages = 3; // temp

        return $pages;
    }

}
