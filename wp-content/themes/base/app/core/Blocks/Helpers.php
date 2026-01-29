<?php
namespace App\Core\Blocks;

class Helpers
{
    public function getElementClasses(?array $attrs, $blockName): string
    {
        $classes = [];

        if ( $blockName ) {
            $blockClass = str_replace( 'core/', '', $blockName );
            $blockClass = str_replace( '/', '-', $blockClass );
            $classes[] = 'wp-block-' . $blockClass;
        }
        
        $verticalAlignment = $attrs['verticalAlignment'] ?? null;
        if ( $verticalAlignment ) {
            $classes[] = "align-items-{$verticalAlignment}";
        }

        $mobileStack = $attrs['isStackedOnMobile'] ?? null;
        if ( $mobileStack ) {
            $classes[] = 'is-stacked-on-mobile';
        }

        $className = $attrs['className'] ?? null;
        if ( $className ) {
            $classNames = explode(' ', $className);
            $classes = array_merge( $classes, $classNames );
        }

        $columnsStyle = $attrs['columnsStyle'] ?? null;
        if ( $columnsStyle ) {
            $classes[] = "columns-style-{$columnsStyle}";
        }

        $classes_str = implode(' ', $classes);
        return $classes_str;
    }

    public function getElementStyles(?array $attrs): string
    {
        $styles = [];
        $width = $attrs['width'] ?? null;
        if ($width) {
            $styles['width'] = $width;
        }

        $blockStyle = $attrs['style'] ?? null;
        $spacing = $blockStyle['spacing'] ?? null;
        $padding = $spacing['padding'] ?? null;
        if ($padding) {
            $top = $padding['top'] ?? null;
            $right = $padding['right'] ?? null;
            $bottom = $padding['bottom'] ?? null;
            $left = $padding['left'] ?? null;
            if ($top) {
                $styles['padding-top'] = $top;
            }
            if ($right) {
                $styles['padding-right'] = $right;
            }
            if ($bottom) {
                $styles['padding-bottom'] = $bottom;
            }
            if ($left) {
                $styles['padding-left'] = $left;
            }
        }

        $styles_str = ($styles) ? 'style="' . $this->attrsToString($styles) . '"' : '';
        return $styles_str;
    }

    public function getIdFromBlock($block) {
        $id = '';
        if ( isset( $block['attrs']['id'] ) && $block['attrs']['id'] ) {
            $id = $block['attrs']['id'];
        } 
        elseif ( isset( $block['attrs']['anchor'] ) && $block['attrs']['anchor'] ) {
            $id = 'block-' . $block['attrs']['anchor'];
        }
        elseif ( isset( $block['innerHTML'] ) && $block['innerHTML'] ) {
            preg_match( '/id=["\'](.*?)["\']/', $block['innerHTML'], $matches );
            if ( isset( $matches[1] ) ) {
                $id = $matches[1];
            }
        }
        return $id;
    }

    public function getWrapperData($attrs, $wrapperClasses = ['container'], $wrapperElem = 'div'): array
    {
        $container = true;
        $wrapper = [
            'start' => '',
            'end' => '',
        ];

        $align = $attrs['align'] ?? null;
        if ($align === 'full') {
            $wrapperClasses[] = 'align' . '-' . $align;
            $container = false;
        }

        if ($container) {
            $wrapper['start'] = '<' . $wrapperElem . ' class="' . implode(' ', $wrapperClasses) . '">';
            $wrapper['end'] = '</' . $wrapperElem . '>';
        }

        return $wrapper;
    }


    public function colClass(?string $size): string
    {
        // if (!$size) {
            // return '';
        // }
        // return 'col-' . trim($size);

        return 'test-class';
    }

    public function attrsToString(array $attrs): string
    {
        $out = [];
        foreach ($attrs as $k => $v) {
            $out[] = $k . ':' . htmlspecialchars((string)$v, ENT_QUOTES) . ';';
        }
        return implode(' ', $out);
    }

    public function renderInnerBlock($block)
    {
        if ( ! isset( $block['innerBlocks'] ) ) {
            return '';
        }

        $html = '';
        foreach ($block['innerBlocks'] as $innerBlock) {
            $blockName = $innerBlock['blockName'] ?? '';

            if (!$blockName) {
                continue;
            }
            
            // Sprawdź typ bloku: core/ lub acf/
            if (str_starts_with($blockName, 'core/')) {
                // Blok core - sprawdź callback w config/blocks.php
                $html .= $this->renderCoreBlock($innerBlock);
            } elseif (str_starts_with($blockName, 'acf/')) {
                // Blok ACF - sprawdź callback w config/acf-blocks.php
                // $html .= $this->renderAcfBlock($innerBlock);

                $html .= render_block($innerBlock);
            } else {
                // Inny typ - użyj domyślnego renderowania
                $html .= $innerBlock['innerHTML'] ?? '';
            }
        }
        
        return $html;
    }
    
    protected function renderCoreBlock(array $block): string
    {
        $blockName = $block['blockName'];
        
        // Załaduj config bloków core
        $coreConfig = config('blocks.core_blocks', []);
        $blockSlug = str_replace('core/', '', $blockName);
        
        // Jeśli istnieje callback, wykonaj go
        if (isset($coreConfig[$blockName]['callback'])) {
            $block = $this->executeCallback($coreConfig[$blockName]['callback'], $block);
        }
        
        // Znajdź odpowiedni blade template
        $viewPath = "blocks.core.{$blockSlug}";
        $viewPath = str_replace(['/', '-'], ['.', '_'], $viewPath);
        
        if (view()->exists($viewPath)) {
            return view($viewPath, [
                'block' => $block,
                'attrs' => $block['attrs'] ?? [],
                'content' => $block['innerHTML'] ?? '',
                'innerBlocks' => $block['innerBlocks'] ?? [],
            ])->render();
        }
        
        // Fallback - domyślny renderer
        $defaultFile = __DIR__ . "/defaultViews/core/{$blockSlug}.blade.php";
        if (file_exists($defaultFile)) {
            return \Roots\view()->file($defaultFile, [
                'block' => $block,
                'attrs' => $block['attrs'] ?? [],
                'content' => $block['innerHTML'] ?? '',
                'innerBlocks' => $block['innerBlocks'] ?? [],
            ])->render();
        }
        
        return $block['innerHTML'] ?? '';
    }
    
    protected function renderAcfBlock(array $block): string
    {
        $blockName = $block['blockName'];
        $blockSlug = str_replace('acf/', '', $blockName);
        
        // Ścieżka do pliku konfiguracyjnego bloku
        $blockConfigPath = get_theme_file_path("config/blocks/{$blockSlug}.php");
        
        
        $blockConfig = [];
        if (file_exists($blockConfigPath)) {
            $blockConfig = require $blockConfigPath;
        }
        
        // Jeśli istnieje callback, wykonaj go (ACF callback renderuje sam)
        if (isset($blockConfig['callback'])) {
            // ACF callback renderuje bezpośrednio, nie zwraca danych
            ob_start();
            $this->executeAcfCallback($blockConfig['callback'], $block);
            return ob_get_clean();
        }
        
        // Jeśli brak callbacka - standardowe renderowanie
        $viewPath = "blocks.acf.{$blockSlug}";
        
        if (view()->exists($viewPath)) {
            return view($viewPath, [
                'block' => $block,
                'attrs' => $block['attrs'] ?? [],
                'content' => $block['innerHTML'] ?? '',
                'innerBlocks' => $block['innerBlocks'] ?? [],
            ])->render();
        }
        
        return '';
    }
    
    protected function executeCallback($callback, array $block): array
    {
        try {
            // Jeśli to callable (funkcja, closure)
            if (is_callable($callback)) {
                error_log('callable');
                $result = call_user_func($callback, $block);
                return is_array($result) ? $result : $block;
            }
            
            // Jeśli to nazwa klasy
            if (is_string($callback) && class_exists($callback)) {
                $controller = new $callback();
                
                if (method_exists($controller, 'handle')) {
                    $result = $controller->handle($block);
                    return is_array($result) ? $result : $block;
                }
                
                if (is_callable($controller)) { // __invoke
                    $result = $controller($block);
                    return is_array($result) ? $result : $block;
                }
            }
        } catch (\Exception $e) {
            error_log("Block callback error: {$e->getMessage()}");
        }
        
        return $block;
    }
    
    protected function executeAcfCallback($callback, array $block): void
    {
        try {
            $content = '';
            $is_preview = false;
            $post_id = get_the_ID() ?: 0;
            
            // Jeśli to nazwa klasy
            if (is_string($callback) && class_exists($callback)) {
                $controller = new $callback();
                
                // __invoke z 4 parametrami (ACF standard)
                if (is_callable($controller)) {
                    $controller($block, $content, $is_preview, $post_id);
                    return;
                }
                
                // handle z 4 parametrami
                if (method_exists($controller, 'handle')) {
                    $controller->handle($block, $content, $is_preview, $post_id);
                    return;
                }
            }
            
            // Jeśli to callable (funkcja, closure)
            if (is_callable($callback)) {
                call_user_func($callback, $block, $content, $is_preview, $post_id);
                return;
            }
            
            error_log("ACF callback is not callable: " . print_r($callback, true));
        } catch (\Exception $e) {
            error_log("ACF Block callback error: {$e->getMessage()}");
        }
    }
}