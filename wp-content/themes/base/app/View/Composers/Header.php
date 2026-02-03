<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Header extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.header',
    ];

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }

    /**
     * Retrieve the home URL.
     */
    public function homeUrl(): string
    {
        return home_url('/');
    }

    /**
     * Check if mobile menu is open.
     */
    public function isMobileMenuOpen(): bool
    {
        return false;
    }

    /**
     * Retrieve the primary navigation menu.
     */
    public function primaryMenu(): array
    {
        return wp_get_nav_menu_items('primary') ?: [];
    }
}
