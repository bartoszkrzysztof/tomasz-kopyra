<?php

namespace App\Core\Blocks;

class BlockRenderer
{
    protected BlockRegistry $registry;
    protected string $viewPrefix;
    protected bool $renderCoreBlocks;

    public function __construct(BlockRegistry $registry, array $config)
    {
        $this->registry = $registry;
        $this->viewPrefix = $config['view_prefix'];
        $this->renderCoreBlocks = $config['render_core_blocks'];
    }

    public function register(): void
    {
        if ($this->renderCoreBlocks) {
            add_filter('render_block', [$this, 'renderBlock'], 10, 2);
        }
    }

    public function renderBlock(string $blockContent, array $block): string
    {
        $blockName = $block['blockName'] ?? '';

        if (!$blockName || !str_starts_with($blockName, 'core/')) {
            return $blockContent;
        }
        
        // Pomiń shortcode w edytorze - zwróć oryginalną zawartość
        // if ($blockName === 'core/shortcode') {
        //     return $blockContent;
        // }

        // Sprawdź czy blok ma callback do parsowania danych
        if ($this->registry->hasCallback($blockName)) {
            $block = $this->registry->executeCallback($blockName, $block);
        }

        $blockSlug = str_replace('core/', '', $blockName);

        // główny widok (resources/views/blocks/core/{block}.blade.php)
        $viewPath = $this->getViewPath($blockName);

        // jeśli istnieje widok w resources/views użyj go
        if ($this->viewExists($viewPath)) {
            return $this->renderView($viewPath, $block);
        }

        // fallback: sprawdź w domyślnych widokach pakietu/app (app/core/Blocks/defaultViews/core/{block}.blade.php)
        $defaultFile = $this->getDefaultViewFilePath($blockSlug);
        if (file_exists($defaultFile)) {
            return $this->renderView($defaultFile, $block);
        }

        return $blockContent;
    }

    protected function getViewPath(string $blockName): string
    {
        $blockSlug = str_replace('core/', '', $blockName);
        $blockSlug = str_replace(['/', '-'], ['.', '_'], $blockSlug);
        return "{$this->viewPrefix}.core.{$blockSlug}";
    }

    protected function viewExists(string $viewPath): bool
    {
        try {
            // Użyj Roots helper do sprawdzenia czy view istnieje
            $factory = app('view');
            return $factory->exists($viewPath);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function renderView(string $viewPath, array $block): string
    {
        // Ensure WP block supports know which block is being rendered.
        // Some Blade templates call get_block_wrapper_attributes(), which
        // relies on WP_Block_Supports::$block_to_render being set.
        try {
            \WP_Block_Supports::$block_to_render = $block;
            // jeśli blok posiada innerBlocks - zrenderuj je rekurencyjnie
            $renderedInnerHTML = '';
            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $renderedInnerHTML = $this->renderInnerBlocks($block['innerBlocks']);
            }
            // jeśli podano ścieżkę do pliku (fallback), użyj \Roots\view()->file()
            if (is_string($viewPath) && file_exists($viewPath)) {
                $output = \Roots\view()->file($viewPath, [
                    'block' => $block,
                    'attrs' => $block['attrs'] ?? [],
                    'content' => $renderedInnerHTML !== '' ? $renderedInnerHTML : ($block['innerHTML'] ?? ''),
                    'innerBlocks' => $block['innerBlocks'] ?? [],
                ])->render();
                return $output;
            }

            $output = \Roots\view($viewPath, [
                'block' => $block,
                'attrs' => $block['attrs'] ?? [],
                'content' => $renderedInnerHTML !== '' ? $renderedInnerHTML : ($block['innerHTML'] ?? ''),
                'innerBlocks' => $block['innerBlocks'] ?? [],
            ])->render();
            return $output;
        } catch (\Exception $e) {
            error_log("Block render error: {$e->getMessage()}");
            return '';
        } finally {
            // Reset to avoid leaking context across renders.
            \WP_Block_Supports::$block_to_render = null;
        }
    }

    protected function renderInnerBlocks(array $innerBlocks): string
    {
        $html = '';
        foreach ($innerBlocks as $inner) {
            $html .= $this->renderBlock($inner['innerHTML'] ?? '', $inner);
        }
        return $html;
    }

    protected function getDefaultViewFilePath(string $blockSlug): string
    {
        $blockSlug = str_replace(['/', '-'], ['.', '_'], $blockSlug);
        // domyślne widoki znajdują się obok tego pliku: app/core/Blocks/defaultViews/core/{block}.blade.php
        return __DIR__ . "/defaultViews/core/{$blockSlug}.blade.php";
    }
}
