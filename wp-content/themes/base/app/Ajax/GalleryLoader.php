<?php

namespace App\Ajax;

use App\View\Composers\PaintArchive;

/**
 * GalleryLoader - Ajax handler for loading more gallery items
 */
class GalleryLoader
{
    /**
     * Register Ajax actions
     */
    public static function register()
    {
        // For logged-in users
        add_action('wp_ajax_load_gallery_items', [self::class, 'loadItems']);
        
        // For non-logged-in users
        add_action('wp_ajax_nopriv_load_gallery_items', [self::class, 'loadItems']);
    }

    /**
     * Load gallery items via Ajax
     */
    public static function loadItems()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gallery_load_more')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        // Get parameters
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $perPage = GALLERY_PER_PAGE;

        // Get gallery data composer
        $composer = new PaintArchive(app('view'));
        
        // Get items for requested page
        $items = $composer->getGalleryItems($page);
        
        // Check if there are more pages
        $pages = $composer->getTotalItems();
        $hasMore = $page < $pages;

        // Generate HTML for items
        $html = self::renderItems($items);

        // Send response
        wp_send_json_success([
            'html' => $html,
            'has_more' => $hasMore,
            'page' => $page,
            'total' => $pages,
        ]);
    }

    /**
     * Render gallery items HTML
     *
     * @param array $items
     * @return string
     */
    private static function renderItems(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            $imageId = $item['image_id'] ?? 0;
            
            // Render using Blade template
            $html .= view('components.masonary-gallery-item', [
                'item' => $item,
            ])->render();
        }

        return $html;
    }
}
