<?php

namespace App\Core\Cpt;

class CptRegistry
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->processCpt();
    }

    protected function processCpt(): void
    {
        foreach ($this->config['cpt'] as $key => $cpt) {
            add_action('init', function() use ($cpt, $key) {
                $args = $cpt['args'] ?? [];
                register_post_type($key, $args);
            });
        }

        foreach ($this->config['taxonomies'] as $key => $taxonomy) {
            add_action('init', function() use ($taxonomy, $key) {
                $args = $taxonomy['args'] ?? [];
                $object_type = $taxonomy['object_type'] ?? [];
                register_taxonomy($key, $object_type, $args);
            });
        }
    }
}
