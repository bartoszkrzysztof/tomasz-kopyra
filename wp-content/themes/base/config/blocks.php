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
        'core/image' => [
            'visible' => true,
            'callback' => null,
        ],
        'core/spacer' => [
            'visible' => true,
            'callback' => null,
        ],
        'acf/imagetext' => [
            'visible' => true,
            'callback' => null,
        ],
        'acf/texteditor' => [
            'visible' => true,
            'callback' => null,
        ],
        'acf/gallery' => [
            'visible' => true,
            'callback' => null,
        ],
    ],
    'acf_blocks_enabled' => true,
];