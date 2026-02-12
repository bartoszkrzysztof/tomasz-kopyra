<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class LayoutData extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'layouts.app',
    ];

    /**
     * Retrieve the main classes for the layout.
     */
    public function mainClasses(): string
    {
        $classes = ['site-main'];
        if (is_front_page()) {
            $classes[] = '-front';
        }
        if (is_post_type_archive('paint') || is_singular('paint')) {
            $classes[] = '-archive-paint';
        }

        
        return implode(' ', $classes);
    }

    public function entryPointsJS(): array
    {
        $entrypoints = [
            'resources/scripts/app.js',
        ];

        // Add gallery.js only on paint archive
        if (is_post_type_archive('paint')) {
            $entrypoints[] = 'resources/scripts/gallery.js';
        }
        if (is_singular('paint')) {
            $entrypoints[] = 'resources/scripts/single-slider.js';
        }

        return $entrypoints;
    }

    public function entryPointsCSS(): array
    {
        $entrypoints = [
            'resources/styles/app.css',
        ];

        return $entrypoints;
    }
}
