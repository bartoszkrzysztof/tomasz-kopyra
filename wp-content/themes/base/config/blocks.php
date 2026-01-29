<?php

/**
 * Blocks configuration
 * 
 * Nadpisuje bazową konfigurację z packages/sage-base/config/blocks.php
 */

return [
    // Modyfikuj tylko to, co chcesz zmienić
    // Reszta zostanie pobrana z bazowej konfiguracji
    
    'core_blocks' => [
        'core/paragraph' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/heading' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/columns' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/column' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/image' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/separator' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/spacer' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/shortcode' => [
            'visible' => true,
            'callback' => null,
        ],
    ],
    'acf_blocks_enabled' => true,
];
