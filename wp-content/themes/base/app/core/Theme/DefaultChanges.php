<?php

namespace App\Core\Theme;

class DefaultChanges
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        add_action('admin_menu', [$this, 'maybe_remove_admin_menus']);
        add_filter('wp_nav_menu_objects', [$this, 'filter_nav_menu_objects'], 10, 2);
        add_action('template_redirect', [$this, 'maybe_block_frontend']);
    }
    

    /**
     * Remove posts/pages from the admin menu when configured.
     * Items remain accessible by direct URL.
     */
    public function maybe_remove_admin_menus(): void
    {
        $hide = $this->config['hide_admin'] ?? [];
        if (!is_array($hide) || empty($hide)) {
            return;
        }

        if (in_array('post', $hide, true)) {
            remove_menu_page('edit.php');
        }

        if (in_array('page', $hide, true)) {
            remove_menu_page('edit.php?post_type=page');
        }

        if (in_array('comment', $hide, true)) {
            remove_menu_page('edit-comments.php');
        }
    }

    /**
     * Remove menu items that point to hidden post types.
     */
    public function filter_nav_menu_objects(array $items, $args): array
    {
        $hide = $this->config['hide_front'] ?? [];
        if (!is_array($hide) || empty($hide)) {
            return $items;
        }

        $result = [];
        foreach ($items as $item) {
            if (isset($item->type) && $item->type === 'post_type' && isset($item->object) && in_array($item->object, $hide, true)) {
                continue;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Block front-end single views for configured post types (return 404).
     */
    public function maybe_block_frontend(): void
    {
        $hide = $this->config['hide_front'] ?? [];
        if (!is_array($hide) || empty($hide)) {
            return;
        }

        if (is_singular() && in_array(get_post_type(), $hide, true)) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_query_template('404');
            exit;
        }
    }
}