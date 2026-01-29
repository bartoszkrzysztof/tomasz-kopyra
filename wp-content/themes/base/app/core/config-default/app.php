<?php

/**
 * Application Configuration
 * 
 * This file contains the configuration for application-level features
 * such as filters, editor customizations, and widgets.
 * 
 * Note: Theme support and menus are configured in theme.php
 * You can override these settings in config/app.php
 */

return [
    /**
     * Enable/disable various application features
     */
    'features' => [
        'filters' => true,
    ],

    /**
     * Excerpt configuration
     */
    'excerpt' => [
        'more_text' => 'Continued',
        'more_link' => true,
    ],

    /**
     * Block editor configuration
     */
    'editor' => [
        'inject_styles' => true,
        'inject_scripts' => true,
    ],

    /**
     * Sidebars/Widgets configuration
     */
    'sidebars' => [],

    /**
     * Widget configuration
     */
    'widget_config' => [],
];
