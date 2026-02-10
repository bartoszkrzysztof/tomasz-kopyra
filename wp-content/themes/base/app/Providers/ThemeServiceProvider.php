<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;
use App\Ajax\GalleryLoader;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        
        // Register Ajax handlers
        GalleryLoader::register();
    }
}
