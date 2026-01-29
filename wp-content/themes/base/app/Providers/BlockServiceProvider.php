<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BlockServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Rejestracja serwisów jeśli potrzebne
    }

    public function boot()
    {
        // Zarejestruj kategorię bloków
        add_filter('block_categories_all', function($categories) {
            return array_merge($categories, [
                [
                    'slug' => 'theme',
                    'title' => __('Theme Blocks', 'sage'),
                    'icon' => null,
                ],
            ]);
        }, 10, 2);

        // Użyj akcji 'acf/init' aby mieć pewność, że ACF jest gotowe
        add_action('acf/init', function() {
            $this->registerAcfBlocks();
        });
    }

    protected function registerAcfBlocks()
    {
        if (!function_exists('acf_register_block_type')) {
            return;
        }

        $acfBlocks = config('acf-blocks');
        
        if (!$acfBlocks || !is_array($acfBlocks)) {
            error_log('ACF blocks config not found or not an array');
            return;
        }

        error_log('Registering ACF blocks: ' . print_r(array_keys($acfBlocks), true));

        foreach ($acfBlocks as $blockName => $config) {
            if (!($config['enabled'] ?? false)) {
                error_log("Block {$blockName} is disabled");
                continue;
            }

            error_log("Registering block: {$blockName}");

            // Rejestracja pól ACF
            if (!empty($config['fields'])) {
                $this->registerAcfFields($blockName, $config['fields']);
            }

            // Przygotowanie definicji bloku
            $blockDefinition = $config['block_definition'] ?? [];
            
            // Dodanie render callback
            if (!empty($config['callback'])) {
                $callbackClass = $config['callback'];
                $blockDefinition['render_callback'] = function($block, $content = '', $is_preview = false, $post_id = 0) use ($callbackClass) {
                    $callback = new $callbackClass();
                    $callback($block, $content, $is_preview, $post_id);
                };
            }

            // Rejestracja bloku
            acf_register_block_type($blockDefinition);
            error_log("Block {$blockName} registered successfully");
        }
    }

    protected function registerAcfFields($blockName, $fields)
    {
        acf_add_local_field_group([
            'key' => "group_{$blockName}",
            'title' => "Block: {$blockName}",
            'fields' => $fields,
            'location' => [
                [
                    [
                        'param' => 'block',
                        'operator' => '==',
                        'value' => "acf/{$blockName}",
                    ],
                ],
            ],
        ]);
    }
}