<?php

namespace App\Core;

use App\Core\Cpt\CptRegistry;
use App\Core\Blocks\BlockRegistry;
use App\Core\Blocks\BlockRenderer;
use App\Core\Theme\Setup;
use App\Core\Theme\Cleanup;
use App\Core\Theme\DefaultChanges;
use App\Core\Theme\Users;

class Bootstrap
{
    protected static ?Bootstrap $instance = null;
    protected array $themeConfig;
    protected array $blocksConfig;
    protected array $appConfig;
    protected array $cptConfig;
    protected string $coreConfigPath;
    protected string $themeConfigPath;

    private function __construct(string $themeDir)
    {
        $this->coreConfigPath = $themeDir . '/app/core/config-default';
        $this->themeConfigPath = $themeDir . '/config';
        
        $this->loadConfig();
        $this->boot();

        require_once __DIR__ . '/helpers/required.php';
    }

    public static function getInstance(?string $themeDir = null): Bootstrap
    {
        if (self::$instance === null) {
            if ($themeDir === null) {
                $themeDir = get_template_directory();
            }
            self::$instance = new self($themeDir);
        }
        return self::$instance;
    }

    protected function loadConfig(): void
    {
        // Ładuj bazową konfigurację z core
        $this->themeConfig = require $this->coreConfigPath . '/theme.php';
        $this->blocksConfig = require $this->coreConfigPath . '/blocks.php';
        $this->appConfig = require $this->coreConfigPath . '/app.php';
        $this->cptConfig = require $this->coreConfigPath . '/cpt.php';
        
        // Nadpisz konfiguracją z theme, jeśli istnieje
        if (file_exists($this->themeConfigPath . '/theme.php')) {
            $this->themeConfig = array_replace_recursive(
                $this->themeConfig,
                require $this->themeConfigPath . '/theme.php'
            );
        }
        
        if (file_exists($this->themeConfigPath . '/blocks.php')) {
            $this->blocksConfig = array_replace_recursive(
                $this->blocksConfig,
                require $this->themeConfigPath . '/blocks.php'
            );
        }

        if (file_exists($this->themeConfigPath . '/app.php')) {
            $this->appConfig = array_replace_recursive(
                $this->appConfig,
                require $this->themeConfigPath . '/app.php'
            );
        }
        
        if (file_exists($this->themeConfigPath . '/cpt.php')) {
            $this->cptConfig = array_replace_recursive(
                $this->cptConfig,
                require $this->themeConfigPath . '/cpt.php'
            );
        }
    }

    protected function boot(): void
    {
        // Theme Setup
        $setup = new Setup($this->themeConfig);
        $setup->register();

        // Default Changes
        $defaultChanges = new DefaultChanges($this->themeConfig);

        // Users and Roles
        $users = new Users($this->themeConfig);

        // Theme Cleanup
        $cleanup = new Cleanup($this->themeConfig['cleanup']);
        $cleanup->register();

        // Blocks
        $blockRegistry = new BlockRegistry($this->blocksConfig);
        $blockRegistry->register();

        $blockRenderer = new BlockRenderer($blockRegistry, $this->blocksConfig);
        $blockRenderer->register();

        // Custom Post Types and Taxonomies
        $cptRegistry = new CptRegistry($this->cptConfig);
    }

    public function getConfig(?string $key = null)
    {
        if ($key === 'theme') {
            return $this->themeConfig;
        }
        if ($key === 'blocks') {
            return $this->blocksConfig;
        }
        if ($key === 'app') {
            return $this->appConfig;
        }
        if ($key === 'cpt') {
            return $this->cptConfig;
        }
        return [
            'theme' => $this->themeConfig,
            'blocks' => $this->blocksConfig,
            'app' => $this->appConfig,
            'cpt' => $this->cptConfig,
        ];
    }
}
