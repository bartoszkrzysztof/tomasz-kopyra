<?php

namespace App\Core\Theme;
use Vite;

class Setup
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        add_action('init', [$this, 'loadTextdomain']);
        add_action('admin_enqueue_scripts', [$this, 'enqueque_admin_scripts']);
    }

    public function loadTextdomain() {
        load_theme_textdomain('sage', get_template_directory() . '/lang');
    }

    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'setupTheme']);
    }

    public function setupTheme(): void
    {
        $this->registerSupport();
        $this->registerImageSizes();
        $this->registerMenus();
    }

    protected function registerSupport(): void
    {
        foreach ($this->config['support'] as $key => $value) {
            if (is_numeric($key)) {
                add_theme_support($value);
            } else {
                add_theme_support($key, $value);
            }
        }
    }

    protected function registerImageSizes(): void
    {
        foreach ($this->config['image_sizes'] as $name => $size) {
            add_image_size($name, $size[0], $size[1], $size[2] ?? false);
        }
    }

    protected function registerMenus(): void
    {
        register_nav_menus($this->config['menus']);
    }

    public function enqueque_admin_scripts(): void
    {
        // $editor_js = asset('scripts/editor.js');
        // $editor_css = asset('styles/editor.css');

        // error_log(print_r($editor_js->uri(), true));

        // // Rejestracja JS
        // if ($editor_js->exists()) {
            wp_enqueue_script(
                'sage/editor-js', 
                Vite::asset('resources/scripts/editor.js'), 
                ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'], // Zależności
                null, // Wersja (Vite dodaje hash do URL, więc można dać null)
                true  // W stopce
            );
        // }

        // // Rejestracja CSS
        // if ($editor_css->exists()) {
            wp_enqueue_style(
                'sage/editor-css', 
                Vite::asset('resources/styles/editor.css'),
                ['wp-edit-blocks'], 
                null
            );
        // }
    }
}
