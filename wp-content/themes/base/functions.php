<?php
use Roots\Acorn\Application;
use App\Core\Bootstrap;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/
$autoload = get_theme_file_path('/vendor/autoload.php');
if (file_exists($autoload)) {
    require_once $autoload;
}

/*
|--------------------------------------------------------------------------
| Bootstrap Core
|--------------------------------------------------------------------------
|
| Bootstrap the core functionality from sage-base package.
| This includes theme setup, cleanup, and block rendering.
|
*/

Bootstrap::getInstance(__DIR__);

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        App\Providers\ThemeServiceProvider::class,
        App\Providers\BlockServiceProvider::class,
    ])
    ->boot();