<?php

namespace App\Core\Blocks;

class BlockRegistry
{
    protected array $config;
    protected array $allowedBlocks = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->processBlocks();
    }

    protected function processBlocks(): void
    {
        foreach ($this->config['core_blocks'] as $blockName => $settings) {
            if ($settings['visible']) {
                $this->allowedBlocks[] = $blockName;
            }
        }
    }

    public function register(): void
    {
        add_filter('allowed_block_types_all', [$this, 'filterAllowedBlocks'], 10, 2);
    }

    public function filterAllowedBlocks($allowed_blocks, $editor_context)
    {
        if (empty($this->allowedBlocks)) {
            return $allowed_blocks;
        }

        return $this->allowedBlocks;
    }

    public function getBlockConfig(string $blockName): ?array
    {
        return $this->config['core_blocks'][$blockName] ?? null;
    }

    public function hasCallback(string $blockName): bool
    {
        $config = $this->getBlockConfig($blockName);
        if (! $config || empty($config['callback'])) {
            return false;
        }

        $callback = $config['callback'];

        // Jeśli to już callable (funkcja, closure lub [obj, method])
        if (is_callable($callback)) {
            return true;
        }

        // Jeśli podano nazwę klasy jako string i klasa istnieje,
        // to uznajemy, że jest to dozwolony callback, gdyż
        // będziemy próbować wywołać metodę `handle` lub `__invoke`.
        if (is_string($callback)) {
            if (! class_exists($callback)) {
                $classBasename = basename(str_replace('\\', '/', $callback));
                if (function_exists('get_theme_file_path')) {
                    $possible = get_theme_file_path("blocks/{$classBasename}.php");
                } else {
                    $possible = __DIR__ . '/../../../../blocks/' . $classBasename . '.php';
                }

                if ($possible && file_exists($possible)) {
                    try {
                        require_once $possible;
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }

            if (class_exists($callback)) {
                if (method_exists($callback, 'handle') || method_exists($callback, '__invoke')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function executeCallback(string $blockName, array $block): array
    {
        if ($this->hasCallback($blockName)) {
            $callback = $this->config['core_blocks'][$blockName]['callback'];

            // jeśli to callable (closure, funkcja, array(obj,method) itp.)
            if (is_callable($callback)) {
                return call_user_func($callback, $block);
            }

            // jeśli to nazwa klasy - spróbujemy zainstalować kontroler i wywołać metodę
            if (is_string($callback)) {
                if (! class_exists($callback)) {
                    $classBasename = basename(str_replace('\\', '/', $callback));
                    if (function_exists('get_theme_file_path')) {
                        $possible = get_theme_file_path("blocks/{$classBasename}.php");
                    } else {
                        $possible = __DIR__ . '/../../../../blocks/' . $classBasename . '.php';
                    }

                    if ($possible && file_exists($possible)) {
                        try {
                            require_once $possible;
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }

                if (class_exists($callback)) {
                    try {
                        $controller = new $callback();

                        if (method_exists($controller, 'handle')) {
                            return $controller->handle($block);
                        }

                        if (is_callable($controller)) { // __invoke
                            return $controller($block);
                        }
                    } catch (\Throwable $e) {
                        error_log("Block callback error ({$blockName}): {$e->getMessage()}");
                        return $block;
                    }
                }
            }
            // w innych przypadkach zwracamy oryginalne dane
            return $block;
        }
        return $block;
    }
}
