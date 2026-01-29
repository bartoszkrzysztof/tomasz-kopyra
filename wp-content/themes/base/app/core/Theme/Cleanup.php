<?php

namespace App\Core\Theme;

class Cleanup
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function register(): void
    {
        if ($this->config['remove_emoji']) {
            $this->removeEmoji();
        }

        if ($this->config['remove_jquery_migrate']) {
            add_action('wp_default_scripts', [$this, 'removeJqueryMigrate']);
        }

        if ($this->config['remove_meta_generators']) {
            $this->removeMetaGenerators();
        }

        if ($this->config['disable_comments']) {
            $this->disableComments();
        }
    }

    protected function removeEmoji(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }

    public function removeJqueryMigrate($scripts): void
    {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) {
                $script->deps = array_diff($script->deps, ['jquery-migrate']);
            }
        }
    }

    protected function removeMetaGenerators(): void
    {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
    }

    protected function disableComments(): void
    {
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('comments_array', '__return_empty_array', 10, 2);
    }
}
