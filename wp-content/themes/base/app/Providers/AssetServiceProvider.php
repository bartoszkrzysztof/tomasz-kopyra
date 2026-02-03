<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Vite;

class AssetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
    }

    /**
     * Enqueue theme scripts and styles
     *
     * @return void
     */
    public function enqueueScripts()
    {
        // Main app.js (zawiera też GSAP animations)
        wp_enqueue_script(
            'sage/app-js',
            Vite::asset('resources/scripts/app.js'),
            [],
            null,
            true
        );

        // Main app.css
        wp_enqueue_style(
            'sage/app-css',
            Vite::asset('resources/styles/app.css'),
            [],
            null
        );
    }
}
